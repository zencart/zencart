<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 18235 2010-11-23 22:41:05Z drbyte $
 */

  @define('SQL_CACHE_METHOD', 'none');
  $zc_install->error = false;

  if (!isset($_POST['store_name'])) $_POST['store_name'] = '';
  if (!isset($_POST['store_owner'])) $_POST['store_owner'] = '';
  if (!isset($_POST['store_owner_email'])) $_POST['store_owner_email'] = '';
  if (!isset($_POST['store_country'])) $_POST['store_country'] = '223';
  if (!isset($_POST['store_zone'])) $_POST['store_zone'] = '';
  if (!isset($_POST['store_address'])) $_POST['store_address'] = STORE_ADDRESS_DEFAULT_VALUE;
  if (!isset($_POST['store_default_language'])) $_POST['store_default_language'] = '';
  if (!isset($_POST['store_default_currency'])) $_POST['store_default_currency'] = '';

  @require_once('../includes/configure.php');
  if (!defined('DB_TYPE') || DB_TYPE=='') {
    die('Database Type Invalid. Did your configure.php file get written correctly?');
    $zc_install->setError('Database Type Invalid', 27);
  }

  if ($za_dir = @dir(DIR_FS_SQL_CACHE)) {
    while ($zv_file = $za_dir->read()) {
      if (preg_match('/^zcInstall.*\.log$/', $zv_file)) {
        unlink(DIR_FS_SQL_CACHE . '/' . $zv_file);
      }
    }
    $za_dir->close();
    unset($za_dir);
  }

  if (isset($_POST['submit'])) {
    $zc_install->validateStoreSetup($_POST);

    if ($_POST['demo_install'] == 'true') {
      $zc_install->fileExists('demo/' . DB_TYPE . '_demo.sql', ERROR_TEXT_DEMO_SQL_NOTEXIST, ERROR_CODE_DEMO_SQL_NOTEXIST);
    }

    if ($zc_install->error == false) {
      if ($_POST['demo_install'] == 'true') {
        $zc_install->dbDemoDataInstall();
      }

      $zc_install->dbStoreSetup();
      // Close the database connection
      $zc_install->db->Close();

      header('location: index.php?main_page=admin_setup' . zcInstallAddSID() );
      exit;
    }
  }

  require('../includes/classes/db/' . DB_TYPE . '/query_factory.php');
  $db = new queryFactory;
  $db->Connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE) or die("Unable to connect to database");


  //if not submit, set some defaults
  $sql = "select countries_id, countries_name from " . DB_PREFIX . "countries order by countries_name";
  $country = $db->Execute($sql);
  $country_string = '';
  while (!$country->EOF) {
    $country_string .= '<option value="' . $country->fields['countries_id'] . '"' . setSelected($country->fields['countries_id'], $_POST['store_country']) . '>' . $country->fields['countries_name'] . '</option>';
    $country->MoveNext();
  }
  $sql = "select zone_id, zone_name from " . DB_PREFIX . "zones";  // order by zone_country_id, zone_name
  $zone = $db->Execute($sql);
  $zone_string = '';
  $zone_string .= '<option value="-1"' . setSelected('-1', $_POST['store_zone']) . '>' . '-- Please Select --' . '</option>';
  $zone_string .= '<option value="0"' . setSelected('0', $_POST['store_zone']) . '>' . '-None-' . '</option>';
  while (!$zone->EOF) {
    $zone_string .= '<option value="' . $zone->fields['zone_id'] . '"' . setSelected($zone->fields['zone_id'], $_POST['store_zone']) . '>' . $zone->fields['zone_name'] . '</option>';
    $zone->MoveNext();
  }
  $sql = "select code, name from " . DB_PREFIX . "languages";
  $store_language = $db->Execute($sql);
  $language_string = '';
  while (!$store_language->EOF) {
    $language_string .= '<option value="' . $store_language->fields['code'] . '"' . setSelected($store_language->fields['code'], $_POST['store_default_language']) . '>' . $store_language->fields['name'] . '</option>';
    $store_language->MoveNext();
  }
  $sql = "select title, code from " . DB_PREFIX . "currencies";
  $currency = $db->Execute($sql) or die("error in $sql" . $db->ErrorMsg());
  $currency_string = '';
  while (!$currency->EOF) {
    $currency_string .= '<option value="' . $currency->fields['code'] . '"' . setSelected($currency->fields['code'], $_POST['store_default_currency']) . '>' . $currency->fields['title'] . '</option>';
    $currency->MoveNext();
  }

  $db->Close();

  if (!isset($_POST['demo_install'])) $_POST['demo_install']=false;

  // quick sanitization
  foreach($_POST as $key=>$val) {
    if(is_array($val)){
      foreach($val as $key2 => $val2){
        $_POST[$key][$key2] = htmlspecialchars($val2, ENT_COMPAT, CHARSET, FALSE);
      }
    } else {
      $_POST[$key] = htmlspecialchars($val, ENT_COMPAT, CHARSET, FALSE);
    }
  }

  setInputValue($_POST['store_name'], 'STORE_NAME_VALUE', '');
  setInputValue($_POST['store_owner'], 'STORE_OWNER_VALUE', '');
  setInputValue($_POST['store_owner_email'], 'STORE_OWNER_EMAIL_VALUE', '');
  setInputValue($_POST['store_address'], 'STORE_ADDRESS_VALUE', STORE_ADDRESS_DEFAULT_VALUE);
  setRadioChecked($_POST['demo_install'], 'DEMO_INSTALL', 'false');

// this sets the first field to email address on login - setting in /common/tpl_main_page.php
  $zc_first_field= 'onload="document.getElementById(\'store_name\').focus()"';

