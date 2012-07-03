<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: application_top.php 19731 2011-10-09 17:20:30Z wilt $
 */
/**
 * File contains just application_top code
 *
 * Initializes common classes & methods. Controlled by an array which describes
 * the elements to be initialised and the order in which that happens.
 *
 * @package admin
 */
/**
 * boolean if true the autoloader scripts will be parsed and their output shown. For debugging purposes only.
 */
define('DEBUG_AUTOLOAD', false);
/**
 * boolean used to see if we are in the admin script, obviously set to false here.
 * DO NOT REMOVE THE define BELOW. WILL BREAK ADMIN
 */
define('IS_ADMIN_FLAG', true);
/**
 * integer saves the time at which the script started.
 */
// Start the clock for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());
/**
 * set the level of error reporting
 *
 * Note STRICT_ERROR_REPORTING should never be set to true on a production site. <br />
 * It is mainly there to show php warnings during testing/bug fixing phases.<br />
 * note for strict error reporting we also turn on show_errors as this may be disabled<br />
 * in php.ini. Otherwise we respect the php.ini setting
 *
 */
if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
  @ini_set('display_errors', TRUE);
  error_reporting(version_compare(PHP_VERSION, 5.3, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE : version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT : E_ALL & ~E_NOTICE);
} else {
  error_reporting(0);
}
/*
 * turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
if (version_compare(PHP_VERSION, 5.4, '<') && @ini_get('magic_quotes_sybase') != 0) @ini_set('magic_quotes_sybase', 0);
// set php_self in the local scope
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

/**
 * Set the local configuration parameters - mainly for developers
 */
if (file_exists('includes/local/configure.php')) {
  /**
   * load any local(user created) configure file.
   */
  include('includes/local/configure.php');
}
/**
 * check for and load application configuration parameters
 */
if (file_exists('includes/configure.php')) {
  /**
   * load the main configure file.
   */
  include('includes/configure.php');
}
if (!defined('DIR_FS_CATALOG') || !is_dir(DIR_FS_CATALOG.'/includes/classes') || !defined('DB_TYPE') || DB_TYPE == '') {
  if (file_exists('../includes/templates/template_default/templates/tpl_zc_install_suggested_default.php')) {
    require('../includes/templates/template_default/templates/tpl_zc_install_suggested_default.php');
    exit;
  } elseif (file_exists('../zc_install/index.php')) {
    echo 'ERROR: Admin configure.php not found. Suggest running install? <a href="../zc_install/index.php">Click here for installation</a>';
  } else {
    die('ERROR: admin/includes/configure.php file not found. Suggest running zc_install/index.php?');
  }
}
/**
 * ignore version-check if INI file setting has been set
 */
if (file_exists(DIR_FS_ADMIN . 'includes/local/skip_version_check.ini')) {
  $lines = @file(DIR_FS_ADMIN . 'includes/local/skip_version_check.ini');
  if (is_array($lines)) {
    foreach($lines as $line) {
      if (substr($line,0,14)=='admin_configure_php_check=') $check_cfg=substr(trim(strtolower(str_replace('admin_configure_php_check=','',$line))),0,3);
    }
  }
}
/*
// turned off for now
  if ($check_cfg != 'off') {
    // if the admin/includes/configure.php file doesn't contain admin-related content, throw error
    $zc_pagepath = str_replace(basename($PHP_SELF),'',__FILE__); //remove page name from full path of current page
    $zc_pagepath = str_replace(array('\\','\\\\'),'/',$zc_pagepath); // convert '\' marks to '/'
    $zc_pagepath = str_replace('//','/',$zc_pagepath); //convert doubles to single
    $zc_pagepath = str_replace(strrchr($zc_pagepath,'/'),'',$zc_pagepath); // remove trailing '/'
    $zc_adminpage = str_replace('\\','/',DIR_FS_ADMIN); //convert "\" to '/'
    $zc_adminpage = str_replace('//','/',$zc_adminpage); // remove doubles
    $zc_adminpage = str_replace(strrchr($zc_adminpage,'/'),'',$zc_adminpage); // remove trailing '/'
    if (!defined('DIR_WS_ADMIN') || $zc_pagepath != $zc_adminpage ) {
      echo ('ERROR: The admin/includes/configure.php file has invalid configuration. Please rebuild, or verify specified paths.');
      if (file_exists('../zc_install/index.php')) {
        echo '<br /><a href="../zc_install/index.php">Click here for installation</a>';
      }
      echo '<br /><br /><br /><br />['.$zc_pagepath.']&nbsp;&nbsp;&nbsp;&laquo;&raquo;&nbsp;&nbsp;&nbsp;[' .$zc_adminpage.']<br />';
    }
  }
*/
/**
 * include the list of extra configure files
 */
if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_configures')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('/\.php$/', $zv_file) > 0) {
      /**
       * load any user/contribution specific configuration files.
       */
      include(DIR_WS_INCLUDES . 'extra_configures/' . $zv_file);
    }
  }
  $za_dir->close();
}
/**
 * init some vars
 */
$template_dir = '';
define('DIR_WS_TEMPLATES', DIR_WS_INCLUDES . 'templates/');
/**
 * Prepare init-system
 */
unset($loaderPrefix); // admin doesn't need this override
$autoLoadConfig = array();
if (isset($loaderPrefix)) {
 $loaderPrefix = preg_replace('/[^a-z_]/', '', $loaderPrefix);
} else {
  $loaderPrefix = 'config';
}
$loader_file = $loaderPrefix . '.core.php';
require('includes/initsystem.php');
/**
 * load the autoloader interpreter code.
 */
  require(DIR_FS_CATALOG . 'includes/autoload_func.php');
