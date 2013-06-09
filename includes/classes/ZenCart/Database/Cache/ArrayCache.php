<?php
/**
 * ArrayCache
 *
 * @package    Database
 * @subpackage Cache
 * @copyright  Copyright 2003-2013 Zen Cart Development Team
 * @license    http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version    1.6.0
 */

namespace ZenCart\Database\Cache;

use ZenCart\Cache\AbstractArrayCache;
use ZenCart\Database\ConnectionInterface;

/**
 * An in-memory array result cache
 *
 * @package    Database
 * @subpackage Cache
 */
class ArrayCache extends AbstractArrayCache {

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
    return is_string($sql) && preg_match('/^select/i', $sql) > 0;
  }

}
