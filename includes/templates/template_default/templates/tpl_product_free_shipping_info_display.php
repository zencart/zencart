<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=product_free_shipping_info.
 * Displays details of a "free-shipping" product (provided it is assigned to the product-free-shipping product type)
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 17 Modified in v2.1.0-beta1 $
 */
// -----
// Set variables used by the 'common' product-information display template
// and then bring that module in to render the page.
//
$product_info_html_id = 'productFreeShipdisplay';
$product_info_class = 'freeShip';

require $template->get_template_dir('/tpl_product_info_display.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_product_info_display.php';
