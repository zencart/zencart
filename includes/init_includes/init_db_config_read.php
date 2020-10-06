<?php
/**
 * read the configuration settings from the db
 *
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 09 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$use_cache = (isset($_GET['nocache']) ? false : true ) ;
$configuration = $db->Execute('SELECT configuration_key AS cfgkey, configuration_value AS cfgvalue, configuration_group_id  
                                 FROM ' . TABLE_CONFIGURATION, '', $use_cache, 150);
while (!$configuration->EOF) {
  /**
 * dynamic define based on info read from DB
 */
  if ($configuration->fields['configuration_group_id'] == 2 || $configuration->fields['configuration_group_id'] == 3) {
    define(strtoupper($configuration->fields['cfgkey']), (int)$configuration->fields['cfgvalue']);
  } else { 
    define(strtoupper($configuration->fields['cfgkey']), $configuration->fields['cfgvalue']);
  }
  $configuration->MoveNext();
}
$configuration = $db->Execute('SELECT configuration_key AS cfgkey, configuration_value AS cfgvalue
                               FROM ' . TABLE_PRODUCT_TYPE_LAYOUT, '', $use_cache, 150);

while (!$configuration->EOF) {
  /**
 * dynamic define based on info read from DB
 * @ignore
 */
  define(strtoupper($configuration->fields['cfgkey']), $configuration->fields['cfgvalue']);
  $configuration->MoveNext();
}
if (file_exists(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php')) {
  /**
 * Load the database dependant query defines
 */
  include(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php');
}
