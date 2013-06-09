<?php
/**
 * Initialize the cache
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package   initSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   1.6.0
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

use ZenCart\Cache\CacheInterface;
use ZenCart\Database\ConnectionInterface as Database;
use ZenCart\Database\Cache\ArrayCache;
use ZenCart\Database\Cache\DatabaseCache;
use ZenCart\Database\Cache\FileCache;

if (defined('SQL_CACHE_METHOD')) {
  $method   = strtolower(trim(SQL_CACHE_METHOD));
  $events   = array(Database::EVENT_QUERY_BEGIN, Database::EVENT_QUERY_END);
  $zc_cache = null;

  if ($method == 'file' && defined('DIR_FS_SQL_CACHE')) {
    $zc_cache = new FileCache(DIR_FS_SQL_CACHE);
  } elseif ($method == 'db') {
    $zc_cache = new DatabaseCache($db);
  } elseif ($method == 'memory' || $method == 'none') {
    // default to in-memory array cache, even if 'none', to compensate for
    // duplicated queries
    $zc_cache = new ArrayCache;
  } else {
    include_once DIR_WS_CLASSES . 'cache.php';
    include_once DIR_WS_CLASSES . 'query_cache.php';

    $zc_cache   = new cache;
    $queryCache = new QueryCache;
  }

  if ($zc_cache instanceof CacheInterface && $db instanceof Database) {
    $db->attach($zc_cache, $events);
  }

  unset($method, $events);
}
