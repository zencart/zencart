<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 27 New in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    exit('Invalid Access');
}

/**
 * @param array $log_filename_prefix_patterns Used by /admin/store_manager.php for purging debug logs
 *
 * Future use: could also be used by DisplayLogs plugin
 */
$log_filename_prefix_patterns = [
     'myDEBUG-',
     'upsoauth-',
     'fedexrest-',
     'Bambora_Debug_',
     'Square_',
     'SquareWebPay_',
     'AIM_Debug_',
     'SIM_Debug_',
     'FirstData_Debug_',
     'Linkpoint_Debug_',
     'Paypal',
     'paypal',
     'ipn_',
     'zcInstall',
     'SHIP_',
     'PAYMENT_',
     'usps_',
     '.*debug',
];
