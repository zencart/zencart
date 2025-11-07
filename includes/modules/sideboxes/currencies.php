<?php
/**
 * currencies sidebox - allows customer to select from available currencies
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 16 Modified in v2.2.0 $
 */

// test if box should display; it's not displayed on checkout-related pages
$show_currencies = (strpos($current_page, 'checkout') !== 0);

if ($show_currencies === true && isset($currencies) && is_object($currencies)) {
    $currencies_array = [];
    foreach ($currencies->getAllCurrencies() as $key => $value) {
        $currencies_array[] = ['id' => $key, 'text' => $value['title']];
    }

    $hidden_get_variables = zen_post_all_get_params(['currency']);

    require $template->get_template_dir('tpl_currencies.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_currencies.php';

    $title =  BOX_HEADING_CURRENCIES;
    $title_link = false;
    require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
}
