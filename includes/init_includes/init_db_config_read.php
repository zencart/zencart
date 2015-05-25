<?php
/**
 * read the configuration settings from the db
 *
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_db_config_read.php 2753 2005-12-31 19:17:17Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$use_cache = (isset($_GET['nocache']) ? false : true ) ;
$configuration = $db->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                                 from ' . TABLE_CONFIGURATION, '', $use_cache, 150);
while (!$configuration->EOF) {
  /**
 * dynamic define based on info read from DB
 */
  define(strtoupper($configuration->fields['cfgkey']), $configuration->fields['cfgvalue']);
  $configuration->MoveNext();
}
$configuration = $db->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                               from ' . TABLE_PRODUCT_TYPE_LAYOUT, '', $use_cache, 150);

foreach ($configuration as $index->$row) {
  /**
 * dynamic define based on info read from DB
 * @ignore
 */
  define(strtoupper($row['cfgkey']), $row['cfgvalue']);
}
unset($configuration);
if (file_exists(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php')) {
  /**
 * Load the database dependant query defines
 */
  include(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php');
}
