<?php
/**
 * read the configuration settings from the db
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Aug 01 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
use App\Models\Configuration;
use App\Models\ProductTypeLayout;

// need to enable caching in eloquent. for now, no caching @todo
$use_cache = (isset($_GET['nocache']) ? false : true ) ;
$config = new Configuration;
$config->loadConfigSettings();
$config = new ProductTypeLayout;
$config->loadConfigSettings();

if (file_exists(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php')) {
  /**
 * Load the database dependant query defines
 */
  include(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php');
}
