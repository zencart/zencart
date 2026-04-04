<?php
/**
 * read the configuration settings from the db
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 Modified in v2.2.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
use Zencart\DbRepositories\ConfigurationRepository;
use Zencart\DbRepositories\ProductTypeLayoutRepository;

// need to enable caching in eloquent. for now, no caching @todo
$use_cache = (isset($_GET['nocache']) ? false : true ) ;
global $db;

$configurationRepository = new ConfigurationRepository($db);
$configurationRepository->loadConfigSettings();

$productTypeLayoutRepository = new ProductTypeLayoutRepository($db);
$productTypeLayoutRepository->loadConfigSettings();

if (file_exists(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php')) {
  /**
 * Load the database dependant query defines
 */
  include(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php');
}
