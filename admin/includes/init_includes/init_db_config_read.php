<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 12 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

  $sql = "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'GLOBAL_AUTH_KEY'";
  $authkey = $db->Execute($sql);
  if (!$authkey->EOF && $authkey->fields['configuration_value'] == '') {
      $hashable = hash('sha256', openssl_random_pseudo_bytes(64));
      $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = :hash: WHERE configuration_key = 'GLOBAL_AUTH_KEY'";
      $sql = $db->bindVars($sql, ':hash:', $hashable, 'string');
      $db->Execute($sql);
  }
// Determine the DATABASE patch level
  $project_db_info= $db->Execute("SELECT * FROM " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Database' ");
  define('PROJECT_DB_VERSION_MAJOR',$project_db_info->fields['project_version_major']);
  define('PROJECT_DB_VERSION_MINOR',$project_db_info->fields['project_version_minor']);
  define('PROJECT_DB_VERSION_PATCH1',$project_db_info->fields['project_version_patch1']);
  define('PROJECT_DB_VERSION_PATCH2',$project_db_info->fields['project_version_patch2']);
  define('PROJECT_DB_VERSION_PATCH1_SOURCE',$project_db_info->fields['project_version_patch1_source']);
  define('PROJECT_DB_VERSION_PATCH2_SOURCE',$project_db_info->fields['project_version_patch2_source']);

// set application wide parameters
  $configuration = $db->Execute('SELECT configuration_key AS cfgKey, configuration_value AS cfgValue
                                 FROM ' . TABLE_CONFIGURATION);
  while (!$configuration->EOF) {
    define(strtoupper($configuration->fields['cfgKey']), $configuration->fields['cfgValue']);
    $configuration->MoveNext();
  }

// set product type layout paramaters
  $configuration = $db->Execute('SELECT configuration_key AS cfgKey, configuration_value AS cfgValue
                          FROM ' . TABLE_PRODUCT_TYPE_LAYOUT);

  while (!$configuration->EOF) {
    define(strtoupper($configuration->fields['cfgKey']), $configuration->fields['cfgValue']);
    $configuration->MoveNext();
  }
