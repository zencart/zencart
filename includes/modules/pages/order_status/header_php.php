<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 28 New in v1.5.8-alpha $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ORDER_STATUS');

if (zen_is_logged_in() && !zen_in_guest_checkout()) {
    zen_redirect(zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
}

// Until this page is fully implemented, redirect the customer to their My Account History
zen_redirect(zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));


require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');





// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ORDER_STATUS');
