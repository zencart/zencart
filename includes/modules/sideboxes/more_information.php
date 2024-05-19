<?php
/**
 * more_information sidebox - displays list of links to additional pages on the site.  Must separately build those pages' content.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 May 14 Modified in v2.0.1 $
 */

// initialize
$more_information = [];

// test if links should display
if (DEFINE_PAGE_2_STATUS <= 1) {
    $more_information[] = '<a href="' . zen_href_link(FILENAME_PAGE_2) . '">' . BOX_INFORMATION_PAGE_2 . '</a>';
}
if (DEFINE_PAGE_3_STATUS <= 1) {
    $more_information[] = '<a href="' . zen_href_link(FILENAME_PAGE_3) . '">' . BOX_INFORMATION_PAGE_3 . '</a>';
}
if (DEFINE_PAGE_4_STATUS <= 1) {
    $more_information[] = '<a href="' . zen_href_link(FILENAME_PAGE_4) . '">' . BOX_INFORMATION_PAGE_4 . '</a>';
}

// insert additional links below to add to the more_information box
// Example:
//    $more_information[] = '<a href="' . zen_href_link(FILENAME_DEFAULT) . '">' . 'TESTING' . '</a>';

// -----
// ... or create an observer-class file that monitors the following notification.
//
$zco_notifier->notify('NOTIFY_MORE_INFORMATION_SIDEBOX_ADDITIONS', [], $more_information);


// only show if links are active
if (count($more_information) > 0) {
    require $template->get_template_dir('tpl_more_information.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_more_information.php';

    $title =  BOX_HEADING_MORE_INFORMATION;
    $title_link = false;
    require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
}
