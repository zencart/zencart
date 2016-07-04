<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:
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
  error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
} else {
  error_reporting(0);
}
// set php_self in the local scope
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['SCRIPT_NAME'];
$PHP_SELF = htmlspecialchars($PHP_SELF);
// Suppress html from error messages
@ini_set("html_errors","0");
@ini_set("session.use_trans_sid","0");
/*
 * Get time zone info from PHP config
*/
@date_default_timezone_set(date_default_timezone_get());
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
if (!defined('DIR_FS_CATALOG') || !is_dir(DIR_FS_CATALOG.'includes/classes') || !defined('DB_TYPE') || DB_TYPE == '') {
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
 * check for and load system defined path constants
 */
if (file_exists('includes/defined_paths.php')) {
  /**
   * load the system-defined path constants
   */
  require('includes/defined_paths.php');
} else {
  die('ERROR: /includes/defined_paths.php file not found. Cannot continue.');
  exit;
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
/**
 * Defined for backwards compatibility only.
 * THESE SHOULD NOT BE EDITED HERE! THEY SHOULD ONLY BE SET IN YOUR CONFIGURE.PHP FILE!
 */
if (!defined('HTT'.'PS_SERVER')) {
  define('HTT'.'PS_SERVER', HTTP_SERVER);
}

// load the default autoload config
$autoloadNamespaces = require DIR_FS_CATALOG . DIR_WS_INCLUDES .  '/autoload_namespaces.php';

/**
 * include the list of extra configure files
 */
if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_configures')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('~^[^\._].*\.php$~i', $zv_file) > 0) {
      /**
       * load any user/contribution specific configuration files.
       */
      include DIR_WS_INCLUDES . 'extra_configures/' . $zv_file;
    }
  }
  $za_dir->close();
}

require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
$loader = new \Aura\Autoload\Loader;
$loader->register();

foreach ($autoloadNamespaces as $autoloadNamespace => $autoloadBaseDir) {
  $loader->addPrefix($autoloadNamespace, $autoloadBaseDir);
}

/**
 * init some vars
 */
$systemContext = 'admin';
$template_dir = '';
if (!defined('DIR_WS_TEMPLATES')) {
  define('DIR_WS_TEMPLATES', DIR_WS_INCLUDES . 'templates/');
}
