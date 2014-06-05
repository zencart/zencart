<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Mon May 5 12:49 2014 -0400 Modified in v1.5.3 $
 */
if (!defined('USE_PCONNECT')) define('USE_PCONNECT', 'false');
/**
 * autoloader array for admin application_top.php
**/

/**
 *
 * require(DIR_WS_CLASSES . 'class.base.php');
 * require(DIR_WS_CLASSES . 'class.notifier.php');
 * $zco_notifier = new notifier()'
 * require(DIR_WS_CLASSES . 'sniffer.php');
 * require(DIR_WS_CLASSES . 'logger.php');
 * require(DIR_WS_CLASSES . 'shopping_cart.php');
 * require(DIR_WS_CLASSES . 'products.php');
 * require(DIR_WS_CLASSES . 'table_block.php');
 * require(DIR_WS_CLASSES . 'box.php');
 * require(DIR_WS_CLASSES . 'message_stack.php');
 * require(DIR_WS_CLASSES . 'split_page_results.php');
 * require(DIR_WS_CLASSES . 'object_info.php');
 * require(DIR_WS_CLASSES . 'class.phpmailer.php');
 * require(DIR_WS_CLASSES . 'upload.php');
 *
 */
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'class.base.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'class.notifier.php');
  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate',
                               'className'=>'notifier',
                               'objectName'=>'zco_notifier');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'sniffer.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'logger.php',
                               'classPath'=>DIR_WS_CLASSES);
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
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'class.zcPassword.php');
  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate',
                               'className'=>'zcPassword',
                               'objectName'=>'zcPassword');

/**
 * Breakpoint 10.
 *
 * require('includes/init_includes/init_file_db_names.php');
 * require('includes/init_includes/init_database.php');
 * require('includes/version.php');
 *
 */
  $autoLoadConfig[10][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_file_db_names.php');
  $autoLoadConfig[10][] = array('autoType'=>'init_script',
                                'loadFile'=>'init_database.php');
  $autoLoadConfig[10][] = array('autoType'=>'require',
                                'loadFile'=> DIR_FS_CATALOG . DIR_WS_INCLUDES .  'version.php');
/**
 * Breakpoint 20.
 *
 * require('includes/init_includes/init_db_config_read.php');
 *
 */
  $autoLoadConfig[20][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_db_config_read.php');
  $autoLoadConfig[20][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_sanitize.php');
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
 * Breakpoint 35.
 *
 * require(DIR_WS_FUNCTIONS . 'admin_access.php');
 *
 */
  $autoLoadConfig[35][] = array('autoType'=>'require',
                                'loadFile'=> DIR_WS_FUNCTIONS . 'admin_access.php');
/**
 * Breakpoint 40.
 *
 * require('includes/init_includes/init_general_funcs.php');
 * require('includes/init_includes/init_tlds.php');
 *
 */
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
 * Breakpoint 70.
 *
 * require(DIR_WS_FUNCTIONS . 'admin_access.php');
 * require('includes/init_includes/init_languages.php');
 *
 */
  $autoLoadConfig[70][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_languages.php');
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
 * require(DIRWS_FUNCTIONS . 'localization.php');
 * require(DIRWS_FUNCTIONS . 'validations.php');
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
 * $messageStack = new messageStack();
 *
 */
  $autoLoadConfig[100][] = array('autoType'=>'classInstantiate',
                                'classPath'=>DIR_WS_CLASSES,
                                 'className'=>'messageStack',
                                 'objectName'=>'messageStack');
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
 * Breakpoint 140.
 *
 * require('includes/init_includes/init_errors.php');
 *
 */
  $autoLoadConfig[140][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_errors.php');
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
 * require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'logging.php');
 *
 */
  $autoLoadConfig[160][] = array('autoType'=>'require',
                                 'loadFile'=> DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'audience.php');
  $autoLoadConfig[160][] = array('autoType'=>'require',
                                 'loadFile'=> DIR_WS_FUNCTIONS . 'logging.php');
/**
 * Breakpoint 170.
 *
 * require('includes/init_includes/init_admin_history.php');
 *
 */
  $autoLoadConfig[170][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_admin_history.php');
/**
 * Breakpoint 180.
 *
 * require('includes/init_includes/init_html_editor.php);
 *
 */

  $autoLoadConfig[180][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_html_editor.php');
