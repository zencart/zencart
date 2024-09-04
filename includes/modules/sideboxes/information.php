<?php
/**
 * information sidebox - displays list of general info links, as defined in this file
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 26 Modified in v2.1.0-alpha2 $
 */

$information = [];

// -----
// The following flag is set by /includes/init_includes/init_common_elements.php; refer to that module's
// comments for the way to override this setting.
//
if ($flag_show_about_us_sidebox_link === true) {
    $information[] = '<a href="' . zen_href_link(FILENAME_ABOUT_US) . '">' . BOX_INFORMATION_ABOUT_US . '</a>';
}

// -----
// The following flag is set by /includes/init_includes/init_common_elements.php; refer to that module's
// comments for the way to override this setting.
//
if ($flag_show_brand_sidebox_link === true) {
    $information[] = '<a href="' . zen_href_link(FILENAME_BRANDS) . '">' . BOX_HEADING_BRANDS . '</a>';
}

if (DEFINE_SHIPPINGINFO_STATUS <= 1) {
    $information[] = '<a href="' . zen_href_link(FILENAME_SHIPPING) . '">' . BOX_INFORMATION_SHIPPING . '</a>';
}
if (DEFINE_PRIVACY_STATUS <= 1) {
    $information[] = '<a href="' . zen_href_link(FILENAME_PRIVACY) . '">' . BOX_INFORMATION_PRIVACY . '</a>';
}
if (DEFINE_CONDITIONS_STATUS <= 1) {
    $information[] = '<a href="' . zen_href_link(FILENAME_CONDITIONS) . '">' . BOX_INFORMATION_CONDITIONS . '</a>';
}

// -----
// The following flag is set by /includes/init_includes/init_common_elements.php; refer to that module's
// comments for the way to override this setting.
//
if ($flag_show_accessibility_sidebox_link === true) {
    $information[] = '<a href="' . zen_href_link(FILENAME_ACCESSIBILITY) . '">' . BOX_INFORMATION_ACCESSIBILITY . '</a>';
}
if (DEFINE_CONTACT_US_STATUS <= 1) {
    $information[] = '<a href="' . zen_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' . BOX_INFORMATION_CONTACT . '</a>';
}

// -----
// The following flag is set by /includes/init_includes/init_common_elements.php; refer to that module's
// comments for the way to override the setting.
//
if ($show_order_status_sidebox_link === true) {
    $information[] = '<a href="' . zen_href_link(FILENAME_ORDER_STATUS, '', 'SSL') . '">' . BOX_INFORMATION_ORDER_STATUS . '</a>';
}

// forum/bb link:
if (!empty($external_bb_url) && !empty($external_bb_text)) {
    $information[] = '<a href="' . $external_bb_url . '" rel="noopener" target="_blank">' . $external_bb_text . '</a>';
}

if (DEFINE_SITE_MAP_STATUS <= 1) {
    $information[] = '<a href="' . zen_href_link(FILENAME_SITE_MAP) . '">' . BOX_INFORMATION_SITE_MAP . '</a>';
}

// only show GV FAQ when feature enabled
if (defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true') {
    $information[] = '<a href="' . zen_href_link(FILENAME_GV_FAQ) . '">' . BOX_INFORMATION_GV . '</a>';
}
// only show Discount Coupon FAQ when feature enabled
if (DEFINE_DISCOUNT_COUPON_STATUS <= 1 && defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && MODULE_ORDER_TOTAL_COUPON_STATUS == 'true') {
    $information[] = '<a href="' . zen_href_link(FILENAME_DISCOUNT_COUPON) . '">' . BOX_INFORMATION_DISCOUNT_COUPONS . '</a>';
}

if (SHOW_NEWSLETTER_UNSUBSCRIBE_LINK == 'true') {
    $information[] = '<a href="' . zen_href_link(FILENAME_UNSUBSCRIBE) . '">' . BOX_INFORMATION_UNSUBSCRIBE . '</a>';
}

$zco_notifier->notify('NOTIFY_INFORMATION_SIDEBOX_ADDITIONS', [], $information);

require($template->get_template_dir('tpl_information.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_information.php');

$title = BOX_HEADING_INFORMATION;
$title_link = false;

require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default);
