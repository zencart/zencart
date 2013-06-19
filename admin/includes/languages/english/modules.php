<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: modules.php 19287 2011-07-28 15:51:25Z drbyte $
 */

define('HEADING_TITLE_MODULES_PAYMENT', 'Payment Modules');
define('HEADING_TITLE_MODULES_SHIPPING', 'Shipping Modules');
define('HEADING_TITLE_MODULES_ORDER_TOTAL', 'Order Total Modules');
define('HEADING_TITLE_MODULES_PRODUCT_TYPES', 'Product Type Modules');

define('TABLE_HEADING_MODULES', 'Modules');
define('TABLE_HEADING_SORT_ORDER', 'Sort Order');
define('TABLE_HEADING_ORDERS_STATUS','Orders Status');
define('TABLE_HEADING_ACTION', 'Action');
define('TEXT_MODULE_STATE', 'Module State');
define('TEXT_MODULE_STATUS_ENABLED', ' <span style="color:#5FC000";>Enabled</span>');
define('TEXT_MODULE_STATUS_AMBER', ' <span style="color:#FFBB00";>Enabled</span>');
define('TEXT_MODULE_STATUS_DISABLED', ' <span style="color:red";>Disabled</span>');


define('TEXT_MODULE_DIRECTORY', 'Module Directory:');
define('WARNING_MODULES_SORT_ORDER','WARNING: YOU HAVE DUPLICATE SORT ORDERS WHICH WILL RESULT IN CALCULATION ERRORS<br />PLEASE CORRECT THESE ISSUES NOW!');
define('ERROR_MODULE_FILE_NOT_FOUND', 'ERROR: module not loaded due to missing language file: ');
define('TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED', 'ALERT: Your Admin settings have been changed in your online store.');
define('TEXT_EMAIL_MESSAGE_ADMIN_SETTINGS_CHANGED', 'This is an automated email from your Zen Cart store to alert you of a change that was just made to your administrative settings: ' . "\n\n" . 'NOTE: Admin settings have been CHANGED for the [%s] module, by your Zen Cart admin user %s.' . "\n\n" . 'If you did not initiate these changes, it is advisable that you verify the settings immediately.' . "\n\n" . 'If you are already aware of these changes, you can ignore this automated email.');
define('TEXT_EMAIL_MESSAGE_ADMIN_MODULE_INSTALLED', 'This is an automated email from your Zen Cart store to alert you of a change that was just made to your administrative settings: ' . "\n\n" . 'NOTE: Admin settings have been changed. The [%s] module has been INSTALLED by your Zen Cart admin user %s.' . "\n\n" . 'If you did not initiate these changes, it is advisable that you verify the settings immediately.' . "\n\n" . 'If you are already aware of these changes, you can ignore this automated email.');
define('TEXT_EMAIL_MESSAGE_ADMIN_MODULE_REMOVED', 'This is an automated email from your Zen Cart store to alert you of a change that was just made to your administrative settings: ' . "\n\n" . 'NOTE: Admin settings have been changed. The [%s] module has been REMOVED by your Zen Cart admin user %s.' . "\n\n" . 'If you did not initiate these changes, it is advisable that you verify the settings immediately.' . "\n\n" . 'If you are already aware of these changes, you can ignore this automated email.');
define('TEXT_DELETE_INTRO', 'Are you sure you want to remove this module?');
define('TEXT_WARNING_SSL_EDIT', 'ALERT: <a href="http://www.zen-cart.com/content.php?56" target="_blank">For security reasons, Editing of this module is disabled until your Admin is configured for SSL</a>.');
define('TEXT_WARNING_SSL_INSTALL', 'ALERT: <a href="http://www.zen-cart.com/content.php?56" target="_blank">For security reasons, Installation of this module is disabled until your Admin is configured for SSL</a>.');
define('TEXT_ERROR_NO_COMMTEST_OPTION_AVAILABLE', 'ERROR: This module does not have a communications-test option. Try a regular transaction via checkout instead.');
