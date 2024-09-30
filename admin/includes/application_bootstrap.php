<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 19 Modified in v2.1.0-beta1 $
 */

use App\Models\PluginControl;
use App\Models\PluginControlVersion;
use Zencart\FileSystem\FileSystem;
use Zencart\PluginManager\PluginManager;
use Zencart\PageLoader\PageLoader;
/**
 * boolean if true the autoloader scripts will be parsed and their output shown. For debugging purposes only.
 */
if (!defined('DEBUG_AUTOLOAD')) define('DEBUG_AUTOLOAD', false);
/**
 * boolean used to see if we are in the admin script, obviously set to false here.
 * DO NOT REMOVE THE define BELOW. WILL BREAK ADMIN
 */
define('IS_ADMIN_FLAG', true);
/**
 * integer saves the time at which the script started.
 */
define('PAGE_PARSE_START_TIME', microtime());
// set php_self in the local scope
$serverScript = basename($_SERVER['SCRIPT_NAME']);
$PHP_SELF = isset($_SERVER['SCRIPT_NAME']) ? $serverScript : 'home.php';
if (basename($PHP_SELF, '.php') === 'index') {
    $PHP_SELF = isset($_GET['cmd']) ? basename($_GET['cmd'] . '.php') : $PHP_SELF;
}
$PHP_SELF = htmlspecialchars($PHP_SELF, ENT_COMPAT);
$_SERVER['SCRIPT_NAME'] = str_replace($serverScript, '', $_SERVER['SCRIPT_NAME']) . $PHP_SELF;
// Suppress html from error messages
@ini_set("html_errors","0");
/*
 * Get time zone info from PHP config
*/
date_default_timezone_set(date_default_timezone_get());

/*
 * Check for a valid system locale, and override if invalid or set to 'C' which means 'unconfigured'
 * It will be overridden later via language-selection operations anyway, but a valid default must be set for zcDate class methods to work
 */
$detected_locale = setlocale(LC_TIME, 0);
if ($detected_locale === false || $detected_locale === 'C') {
    setlocale(LC_TIME, ['en_US', 'en_US.UTF-8', 'en-US', 'en']);
}

if (!defined('DIR_FS_ADMIN')) define('DIR_FS_ADMIN', preg_replace('#/includes/$#', '/', realpath(__DIR__ . '/../') . '/'));

/**
 * set the level of error reporting
 *
 * Note STRICT_ERROR_REPORTING should never be set to true on a production site.
 * It is mainly there to show php warnings during testing/bug fixing phases.
 * note for strict error reporting we also turn on show_errors as this may be disabled
 * in php.ini. Otherwise we respect the php.ini setting
 *
 */
if ((defined('DEBUG_AUTOLOAD') && DEBUG_AUTOLOAD === true) || (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING === true)) {
    @ini_set('display_errors', TRUE);
    error_reporting(defined('STRICT_ERROR_REPORTING_LEVEL') ? STRICT_ERROR_REPORTING_LEVEL : E_ALL);
} else {
    error_reporting(0);
}

/**
 * Ensure minimum PHP version.
 * This is intended to run before any dependencies are required
 * See https://www.zen-cart.com/requirements or run zc_install to see actual requirements!
 */
if (PHP_VERSION_ID < 80002) {
    // redirect to catalog to display the PHP version compatibility message
    chdir(realpath(__DIR__ . '/../'));
    require 'includes/application_top.php';
    exit(0);
}
/**
 * Set the local configuration parameters - mainly for developers
 */
if (file_exists('includes/local/configure.php')) {
    /**
     * load any local(user created) configure file.
     */
    include('includes/local/configure.php');
}

if (file_exists('../not_for_release/testFramework/Support/application_testing.php')) {
    require('../not_for_release/testFramework/Support/application_testing.php');
}
/**
 * check for and load application configuration parameters
 */
if (!defined('ZENCART_TESTFRAMEWORK_RUNNING')) {
    if (file_exists('includes/configure.php')) {
        /**
         * load the main configure file.
         */
        include('includes/configure.php');
    }
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

if (file_exists($file = DIR_FS_CATALOG . 'laravel/vendor/symfony/polyfill-mbstring/bootstrap80.php')) {
    include $file;
}
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'php_polyfills.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'zen_define_default.php';

/**
 * ignore version-check if INI file setting has been set
 */
$file = DIR_FS_ADMIN . 'includes/local/skip_version_check.ini';
if (file_exists($file) && $lines = @file($file)) {
    if (is_array($lines)) {
        foreach($lines as $line) {
            if (substr($line,0,14)=='admin_configure_php_check=') $check_cfg=substr(trim(strtolower(str_replace('admin_configure_php_check=','',$line))),0,3);
        }
    }
}

/**
 * Register error-handling functions
 */
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_error_handling.php';
zen_enable_error_logging();

/**
 * include the extra_configures files
 */
foreach (glob(DIR_WS_INCLUDES . 'extra_configures/*.php') ?? [] as $file) {
    include($file);
}
/**
 * init some vars
 */
$template_dir = '';
zen_define_default('DIR_WS_TEMPLATES', DIR_WS_INCLUDES . 'templates/');
/**
 * psr-4 autoloading
 */
require DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/AuraAutoload/src/Loader.php';
require DIR_FS_CATALOG . 'laravel/vendor/autoload.php';
$psr4Autoloader = new \Aura\Autoload\Loader;
$psr4Autoloader->register();
require DIR_FS_CATALOG . 'includes/psr4Autoload.php';
require DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php';

require 'includes/classes/AdminRequestSanitizer.php';
require 'includes/init_includes/init_file_db_names.php';
require 'includes/init_includes/init_database.php';
require (DIR_FS_CATALOG . 'includes/application_laravel.php');

$pluginManager = new PluginManager(new PluginControl, new PluginControlVersion);
$installedPlugins = $pluginManager->getInstalledPlugins();

$pageLoader = PageLoader::getInstance();
$pageLoader->init($installedPlugins, $PHP_SELF, new FileSystem);

$fs = new FileSystem;
$fs->loadFilesFromPluginsDirectory($installedPlugins, 'admin/includes/extra_configures', '~^[^\._].*\.php$~i');
$fs->loadFilesFromPluginsDirectory($installedPlugins, 'admin/includes/extra_datafiles', '~^[^\._].*\.php$~i');
$fs->loadFilesFromPluginsDirectory($installedPlugins, 'admin/includes/functions/extra_functions', '~^[^\._].*\.php$~i');

foreach ($installedPlugins as $plugin) {
    $namespaceAdmin = 'Zencart\\Plugins\\Admin\\' . ucfirst($plugin['unique_key']);
    $namespaceCatalog = 'Zencart\\Plugins\\Catalog\\' . ucfirst($plugin['unique_key']);
    $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/';
    $filePathAdmin = $filePath . 'admin/includes/classes/';
    $filePathCatalog = $filePath . 'catalog/includes/classes/';
    $psr4Autoloader->addPrefix($namespaceAdmin, $filePathAdmin);
    $psr4Autoloader->addPrefix($namespaceCatalog, $filePathCatalog);
}
