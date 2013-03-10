<?php
/**
 * DatabaseCache
 *
 * @package    Database
 * @subpackage Cache
 * @copyright  Copyright 2003-2013 Zen Cart Development Team
 * @license    http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version    1.6.0
 */

namespace ZenCart\Database\Cache;

use ZenCart\Cache\AbstractDatabaseCache;
use ZenCart\Database\ConnectionInterface;

/**
 * A database-backed result cache
 *
 * @package    Database
 * @subpackage Cache
 */
class DatabaseCache extends AbstractDatabaseCache {

  /**
   * Receive query events and process select statements
   *
   * @param ConnectionInterface $subject the notifier
   * @param string              $event   the event
   * @return void
   */
  public function update($subject, $event) {
    if ($subject instanceof ConnectionInterface) {
      $params = func_get_arg(2);
      if (
        !isset($params)
        || !isset($params['sql'])
        || !$this->isSelectStatement($params['sql'])
      ) {
        return;
      }

      $sql = $params['sql'];
      if ($event == ConnectionInterface::EVENT_QUERY_BEGIN) {
        if ($data = $this->read($sql)) {
          $subject->setResult($data, $this);
        }
      } elseif ($event == ConnectionInterface::EVENT_QUERY_END) {
        if ($this->isExpired($sql)) {
          $this->store($sql, $subject->getResult());
        }
      }
    }
  }

  /**
   * Verify select statement
   *
   * @param string $sql the sql statement
   * @return boolean
   */
  protected function isSelectStatement($sql) {
    $table = $this->getTable();
    return (
      is_string($sql)
      && preg_match('/^select/i', $sql) > 0
      && preg_match("/{$table}/i", $sql) == 0
    );
  }

}
