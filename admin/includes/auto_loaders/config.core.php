<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 17 Modified in v1.5.7 $
 */
if (!defined('USE_PCONNECT')) define('USE_PCONNECT', 'false');
/**
 * autoloader array for admin application_top.php
 * Where DIR_WS_CLASSES is used alone in commented text, the file loads relative
 *   to the admin side folder.
**/
/**
 * require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'version.php');
 * require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php');
 * require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php');
 * $zco_notifier = new notifier();
 * require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'sniffer.php');
 * require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'shopping_cart.php');
 * require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'products.php');
 * require(DIR_WS_CLASSES . 'table_block.php');
 * require(DIR_WS_CLASSES . 'box.php');
 * require(DIR_WS_CLASSES . 'message_stack.php');
 * require(DIR_WS_CLASSES . 'split_page_results.php');
 * require(DIR_WS_CLASSES . 'object_info.php');
 * require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.phpmailer.php');
 * require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'upload.php');
 * require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php');
 * zcPassword = new zcPassword();
 * require(DIR_WS_CLASSES . VersionServer.php');
 */
  $autoLoadConfig[0][] = array('autoType'=>'require',
                               'loadFile'=> DIR_FS_CATALOG . DIR_WS_INCLUDES .  'version.php');
//  $autoLoadConfig[0][] = array('autoType'=>'class',
//                               'loadFile'=>'class.base.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'class.notifier.php');
  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate',
                               'className'=>'notifier',
                               'objectName'=>'zco_notifier');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'sniffer.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'shopping_cart.php',
                               );
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'products.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=> 'table_block.php',
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=> 'box.php',
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'message_stack.php',
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'split_page_results.php',
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'object_info.php',
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'class.phpmailer.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'upload.php',
                               'classPath'=>DIR_FS_CATALOG . DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'class.zcPassword.php');
  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate',
                               'className'=>'zcPassword',
                               'objectName'=>'zcPassword');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=> 'VersionServer.php',
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=> 'configurationValidation.php',
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=> 'WhosOnline.php',
                               'classPath'=>DIR_WS_CLASSES);

/**
 * Breakpoint 10.
 *
 * require('includes/init_includes/init_file_db_names.php');
 * require('includes/init_includes/init_database.php');
 *
 */
//  $autoLoadConfig[10][] = array('autoType'=>'init_script',
//                                'loadFile'=> 'init_file_db_names.php');
//  $autoLoadConfig[10][] = array('autoType'=>'init_script',
//                                'loadFile'=>'init_database.php');
/**
 * Breakpoint 20.
 *
 * require('includes/init_includes/init_db_config_read.php');
 *
 */
  $autoLoadConfig[20][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_db_config_read.php');
/**
 * Breakpoint 30.
 *
 * require('includes/init_includes/init_gzip.php');
 * $sniffer = new sniffer();
 *
 */
  $autoLoadConfig[30][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_gzip.php');
  $autoLoadConfig[30][] = array('autoType'=>'classInstantiate',
                                'className'=>'sniffer',
                                'objectName'=>'sniffer');
/**
 * Breakpoint 32.
 *
 * $messageStack = new messageStack();
 *
 */
  $autoLoadConfig[32][] = array('autoType'=>'classInstantiate',
                                 'className'=>'messageStack',
                                 'objectName'=>'messageStack');
/**
 * Breakpoint 35.
 *
 * require(DIR_WS_FUNCTIONS . 'admin_access.php');
 *
 */
  $autoLoadConfig[35][] = array('autoType'=>'require',
                                'loadFile'=> DIR_WS_FUNCTIONS . 'admin_access.php');

/**
 * Breakpoint 38.
 *
 * require(DIR_WS_FUNCTIONS . 'functions_help.php');
 *
 */
  $autoLoadConfig[38][] = array('autoType'=>'require',
                                'loadFile'=> DIR_WS_FUNCTIONS . 'functions_help.php');

/**
 * Breakpoint 40.
 *
 * require('includes/init_includes/init_general_funcs.php');
 * require('includes/init_includes/init_tlds.php');
 *
 */
  $autoLoadConfig[40][] = array('autoType'=>'require',
                                'loadFile'=> DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_osh_update.php');
  $autoLoadConfig[40][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_general_funcs.php');
  $autoLoadConfig[40][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_tlds.php');
/**
 * Breakpoint 50.
 *
 * require('includes/init_includes/init_cache_key_check.php');
 *
 */
  $autoLoadConfig[50][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_cache_key_check.php');
/**
 * Breakpoint 60.
 *
 * require('includes/init_includes/init_sessions.php');
 *
 */
  $autoLoadConfig[60][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_sessions.php');
/**
 * Breakpoint 65.
 *
 * require('includes/init_includes/init_languages.php');
 * Expecting nothing loaded before init_sanitize to require $_POST/$_GET sanitization.
 */
  $autoLoadConfig[65][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_languages.php');
/**
 * Breakpoint 70.
 *
 * require('includes/init_includes/init_sanitize.php');
 *
 */
  $autoLoadConfig[70][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_sanitize.php');
/**
 * Breakpoint 80.
 *
 * require('includes/init_includes/init_templates.php');
 *
 */
  $autoLoadConfig[80][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_templates.php');
/**
 * Breakpoint 90.
 *
 * $zc_products = new products();
 * require(DIR_WS_FUNCTIONS . 'localization.php');
 *
 */
  $autoLoadConfig[90][] = array('autoType'=>'classInstantiate',
                                'className'=>'products',
                                'objectName'=>'zc_products');
  $autoLoadConfig[90][] = array('autoType'=>'require',
                                 'loadFile'=> DIR_WS_FUNCTIONS . 'localization.php');
/**
 * Breakpoint 100.
 *
 * $messageStack->add_from_session();
 *
 */
  $autoLoadConfig[100][] = array('autoType'=>'objectMethod',
                                 'objectName'=>'messageStack',
                                 'methodName'=>'add_from_session');
/**
 * Breakpoint 120.
 *
 * require('includes/init_includes/init_special_funcs.php');
 *
 */
  $autoLoadConfig[120][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_special_funcs.php');

/**
 * Breakpoint 130.
 *
 * require('includes/init_includes/init_category_path.php');
 *
 */
  $autoLoadConfig[130][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_category_path.php');
/**
 * Breakpoint 150.
 *
 * require('includes/init_includes/init_admin_auth.php');
 *
 */
  $autoLoadConfig[150][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_admin_auth.php');
/**
 * Breakpoint 160.
 *
 * require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'audience.php');
 *
 */
  $autoLoadConfig[160][] = array('autoType'=>'require',
                                 'loadFile'=> DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'audience.php');
/**
 * Breakpoint 170.
 *
 * require('includes/init_includes/init_admin_history.php');
 *
 */
  $autoLoadConfig[170][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_admin_history.php');
/**
 * Breakpoint 175.
 *
 * require(DIR_WS_CLASSES . 'configurationValidation');
 * require(DIR_FS_CATALOG . 'includes/init_includes/init_observers.php');
 *
 */
  $autoLoadConfig[175][] = array('autoType'=>'classInstantiate',
                                 'className'=>'configurationValidation',
                                 'objectName'=>'configurationValidation');
  $autoLoadConfig[175][] = array('autoType'=>'include',
                                 'loadFile'=> DIR_FS_CATALOG . 'includes/init_includes/init_observers.php');
/**
 * Breakpoint 180.
 *
 * require('includes/init_includes/init_html_editor.php);
 *
 */

  $autoLoadConfig[180][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_html_editor.php');
/**
 * Breakpoint 181.
 *
 * require('includes/init_includes/init_errors.php');
 *
 */
  $autoLoadConfig[181][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_errors.php');

/**
 * NOTE: Most plugins should be added from point 200 onward.
 */
