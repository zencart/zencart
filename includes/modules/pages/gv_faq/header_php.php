<?php
/**
 * GV FAQ
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 03 Modified in v2.0.0-alpha1 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_GV_FAQ');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

$customer_has_gv_balance = false;
$customer_gv_balance = 0;

if (zen_is_logged_in() && !zen_in_guest_checkout()) {
    $customer = new Customer;
    $gv_balance = $customer->getData('gv_balance');
    $customer_has_gv_balance = !empty($gv_balance);
    $customer_gv_balance = !is_null($gv_balance) ? $currencies->format($gv_balance) : false;
}

$gv_faq_item =  (empty($_GET['faq_item'])) ? 0 : (int)$_GET['faq_item'];

$subHeadingText = 'SUB_HEADING_TEXT_' . $gv_faq_item;
$subHeadingTitle = 'SUB_HEADING_TITLE_' . $gv_faq_item;
if (!defined($subHeadingText)) $subHeadingText = 'SUB_HEADING_TEXT_0';
if (!defined($subHeadingTitle)) $subHeadingTitle = 'SUB_HEADING_TITLE_0';
$subHeadingText = constant($subHeadingText);
$subHeadingTitle = constant($subHeadingTitle);

$breadcrumb->add(NAVBAR_TITLE);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_GV_FAQ');
