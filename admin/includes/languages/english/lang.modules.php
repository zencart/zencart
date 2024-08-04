<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 27 Modified in v2.1.0-alpha1 $
*/

$define = [
    'HEADING_TITLE_MODULES_PAYMENT' => 'Payment Modules',
    'HEADING_TITLE_MODULES_SHIPPING' => 'Shipping Modules',
    'HEADING_TITLE_MODULES_ORDER_TOTAL' => 'Order Total Modules',
    'TABLE_HEADING_ORDERS_STATUS' => 'Orders Status',
    'TEXT_MODULE_DIRECTORY' => 'Module Directory:',
    'ERROR_MODULE_FILE_NOT_FOUND' => 'ERROR: module not loaded due to missing language file: ',
    'TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED' => 'ALERT: Admin settings have been changed in "' . STORE_NAME . '"',
    'TEXT_EMAIL_MESSAGE_ADMIN_SETTINGS_CHANGED' => 'This is an automated email from "' . STORE_NAME . '" to alert you of a change that was just made to your administrative settings: ' . "\n\n" . 'NOTE: Admin settings have been CHANGED for the [%1$s] module, by Zen Cart admin user %2$s.' . "\n\n" . 'If you did not initiate these changes, it is advisable that you verify the settings immediately.' . "\n\n" . 'If you are already aware of these changes, you can ignore this automated email.',
    'TEXT_EMAIL_MESSAGE_ADMIN_MODULE_INSTALLED' => 'This is an automated email from "' . STORE_NAME . '" to alert you of a change that was just made to your administrative settings: ' . "\n\n" . 'NOTE: Admin settings have been changed. The [%1$s] module has been INSTALLED by Zen Cart admin user %2$s.' . "\n\n" . 'If you did not initiate these changes, it is advisable that you verify the settings immediately.' . "\n\n" . 'If you are already aware of these changes, you can ignore this automated email.',
    'TEXT_EMAIL_MESSAGE_ADMIN_MODULE_REMOVED' => 'This is an automated email from "' . STORE_NAME . '" to alert you of a change that was just made to your administrative settings: ' . "\n\n" . 'NOTE: Admin settings have been changed. The [%1$s] module has been REMOVED by Zen Cart admin user %2$s.' . "\n\n" . 'If you did not initiate these changes, it is advisable that you verify the settings immediately.' . "\n\n" . 'If you are already aware of these changes, you can ignore this automated email.',
    'TEXT_DELETE_INTRO' => 'Are you sure you want to remove this module?',
    'TEXT_WARNING_SSL_EDIT' => 'ALERT: <a href="https://docs.zen-cart.com/user/installing/enable_ssl/" rel="noopener" target="_blank">For security reasons, Editing of this module is disabled until your Admin is configured for SSL</a>.',
    'TEXT_WARNING_SSL_INSTALL' => 'ALERT: <a href="https://docs.zen-cart.com/user/installing/enable_ssl/" rel="noopener" target="_blank">For security reasons, Installation of this module is disabled until your Admin is configured for SSL</a>.',
    'TEXT_POSITIVE_INT' => '%s must be an integer greater than or equal to 0',
    'TEXT_POSITIVE_FLOAT' => '%s must be a decimal greater than or equal to 0',
];

return $define;
