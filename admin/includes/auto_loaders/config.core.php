<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jun 18 Modified in v2.1.0-alpha1 $
 */
if (!defined('USE_PCONNECT')) {
    define('USE_PCONNECT', 'false');
}
/**
 * autoloader array for admin application_top.php
 * Where DIR_WS_CLASSES is used alone in commented text, the file loads relative
 *   to the admin side folder.
 **/
/**
 * require DIR_FS_CATALOG . DIR_WS_INCLUDES . 'version.php';
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php';
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php';
 * $zco_notifier = new notifier();
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'sniffer.php';
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'shopping_cart.php';
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'products.php';
 * require DIR_WS_CLASSES . 'table_block.php';
 * require DIR_WS_CLASSES . 'box.php';
 * require DIR_WS_CLASSES . 'message_stack.php';
 * require DIR_WS_CLASSES . 'object_info.php';
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.phpmailer.php';
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'upload.php';
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php';
 * zcPassword = new zcPassword();
 * require DIR_WS_CLASSES . VersionServer.php';
 * require DIR_FS_CATALOG . DIR_WS_CLASSES . 'zcDate.php';
 */
$autoLoadConfig[0][] = [
    'autoType' => 'require',
    'loadFile' => DIR_FS_CATALOG . DIR_WS_INCLUDES . 'version.php',
];

//- notifier class loaded via psr4Autoload.php
$autoLoadConfig[0][] = [
    'autoType' => 'classInstantiate',
    'className' => 'notifier',
    'objectName' => 'zco_notifier',
];
$autoLoadConfig[0][] = [
    'autoType' => 'class',
    'loadFile' => 'class.phpmailer.php',
];

//- zcPassword class loaded via psr4Autoload.php
$autoLoadConfig[0][] = [
    'autoType' => 'classInstantiate',
    'className' => 'zcPassword',
    'objectName' => 'zcPassword',
];
/**
 * Breakpoint 5.
 *
 * $zcDate = new zcDate();
 *
 */
//- zcDate class loaded via psr4Autoload.php
$autoLoadConfig[5][] = [
    'autoType' => 'classInstantiate',
    'className' => 'zcDate',
    'objectName' => 'zcDate',
];
/**
 * Breakpoint 10.
 *
 * require 'includes/init_includes/init_file_db_names.php';
 * require 'includes/init_includes/init_database.php';
 *
 */
//$autoLoadConfig[10][] = [
//    'autoType' => 'init_script',
//    'loadFile' => 'init_file_db_names.php',
//];
//$autoLoadConfig[10][] = [
//    'autoType' => 'init_script',
//    'loadFile' => 'init_database.php',
//];
/**
 * Breakpoint 20.
 *
 * require 'includes/init_includes/init_db_config_read.php';
 *
 */
$autoLoadConfig[20][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_db_config_read.php',
];
/**
 * Breakpoint 25.
 *
 * require 'includes/init_includes/init_non_db_settings_admin.php';
 *
 */
$autoLoadConfig[25][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_non_db_settings_admin.php',
];
/**
 * Breakpoint 27
 *
 * require 'includes/init_includes/init_split_page_results.php';
 */
$autoLoadConfig[27][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_split_page_results.php',
];
/**
 * Breakpoint 30.
 *
 * require 'includes/init_includes/init_gzip.php';
 * $sniffer = new sniffer();
 *
 */
$autoLoadConfig[30][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_gzip.php',
];

//- sniffer class loaded via psr4Autoload.php
$autoLoadConfig[30][] = [
    'autoType' => 'classInstantiate',
    'className' => 'sniffer',
    'objectName' => 'sniffer',
];
/**
 * Breakpoint 32.
 *
 * $messageStack = new messageStack();
 *
 */
//- messageStack class loaded via psr4Autoload.php
$autoLoadConfig[32][] = [
    'autoType' => 'classInstantiate',
    'className' => 'messageStack',
    'objectName' => 'messageStack',
];
/**
 * Breakpoint 35.
 *
 * require DIR_WS_FUNCTIONS . 'admin_access.php';
 *
 */
$autoLoadConfig[35][] = [
    'autoType' => 'require',
    'loadFile' => DIR_WS_FUNCTIONS . 'admin_access.php',
];

/**
 * Breakpoint 38.
 *
 * require DIR_WS_FUNCTIONS . 'functions_help.php';
 *
 */
$autoLoadConfig[38][] = [
    'autoType' => 'require',
    'loadFile' => DIR_WS_FUNCTIONS . 'functions_help.php',
];

/**
 * Breakpoint 40.
 *
 * require 'includes/init_includes/init_general_funcs.php';
 * require 'includes/init_includes/init_tlds.php';
 *
 */
$autoLoadConfig[40][] = [
    'autoType' => 'require',
    'loadFile' => DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_osh_update.php',
];
$autoLoadConfig[40][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_general_funcs.php',
];
$autoLoadConfig[40][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_tlds.php',
];
/**
 * Breakpoint 50.
 *
 * require 'includes/init_includes/init_cache_key_check.php';
 *
 */
$autoLoadConfig[50][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_cache_key_check.php',
];
/**
 * Breakpoint 60.
 *
 * require 'includes/init_includes/init_sessions.php';
 *
 */
$autoLoadConfig[60][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_sessions.php',
];
/**
 * Breakpoint 65.
 *
 * require 'includes/init_includes/init_languages.php';
 */
$autoLoadConfig[65][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_languages.php',
];
/**
 * Expecting nothing loaded before init_sanitize to require $_POST/$_GET sanitization.
 *
 * Breakpoint 70.
 *
 * require 'includes/init_includes/init_sanitize.php';
 *
 */
$autoLoadConfig[70][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_sanitize.php',
];
/**
 * Breakpoint 80.
 *
 * require 'includes/init_includes/init_templates.php';
 *
 */
$autoLoadConfig[80][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_templates.php',
];
/**
 * Breakpoint 90.
 *
 * $zc_products = new products(); // deprecated v2.1.0
 * require DIR_WS_FUNCTIONS . 'datepicker.php';
 *
 */
// zc_products deprecated v2.1.0; use Product class instead.
//- products class loaded via psr4Autoload.php
$autoLoadConfig[90][] = [
    'autoType' => 'classInstantiate',
    'className' => 'products',
    'objectName' => 'zc_products',
];

$autoLoadConfig[90][] = [
    'autoType' => 'require',
    'loadFile' => DIR_WS_FUNCTIONS . 'datepicker.php',
];
$autoLoadConfig[90][] = [
    'autoType' => 'require',
    'loadFile' => DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_exchange_rates.php',
];
/**
 * Breakpoint 100.
 *
 * $messageStack->add_from_session();
 *
 */
$autoLoadConfig[100][] = [
    'autoType' => 'objectMethod',
    'objectName' => 'messageStack',
    'methodName' => 'add_from_session',
];
/**
 * Breakpoint 120.
 *
 * require 'includes/init_includes/init_special_funcs.php';
 *
 */
$autoLoadConfig[120][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_special_funcs.php',
];

/**
 * Breakpoint 130.
 *
 * require 'includes/init_includes/init_category_path.php';
 *
 */
$autoLoadConfig[130][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_category_path.php',
];
/**
 * Breakpoint 150.
 *
 * require 'includes/init_includes/init_admin_auth.php';
 *
 */
$autoLoadConfig[150][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_admin_auth.php',
];
/**
 * Breakpoint 160.
 *
 * require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'audience.php';
 *
 */
$autoLoadConfig[160][] = [
    'autoType' => 'require',
    'loadFile' => DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'audience.php',
];
/**
 * Breakpoint 170.
 *
 * require 'includes/init_includes/init_admin_history.php';
 *
 */
$autoLoadConfig[170][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_admin_history.php',
];
/**
 * Breakpoint 175.
 *
 * require DIR_WS_CLASSES . 'configurationValidation';
 * require DIR_FS_CATALOG . 'includes/init_includes/init_observers.php';
 *
 */
//- configurationValidation class loaded via psr4Autoload.php
$autoLoadConfig[175][] = [
    'autoType' => 'classInstantiate',
    'className' => 'configurationValidation',
    'objectName' => 'configurationValidation',
];
$autoLoadConfig[175][] = [
    'autoType' => 'include',
    'loadFile' => DIR_FS_CATALOG . 'includes/init_includes/init_observers.php',
];
/**
 * Breakpoint 180.
 *
 * require 'includes/init_includes/init_html_editor.php;
 *
 */

$autoLoadConfig[180][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_html_editor.php',
];
/**
 * Breakpoint 181.
 *
 * require 'includes/init_includes/init_errors.php';
 *
 */
$autoLoadConfig[181][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_errors.php',
];

/**
 * NOTE: Most plugins should be added from point 200 onward.
 */
