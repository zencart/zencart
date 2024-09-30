<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=document_product_info.
 * Displays template according to "document-product" product-type needs
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
$product_info_html_id = 'docProductDisplay';
$product_info_class = 'docProduct';

require $template->get_template_dir('/tpl_product_info_display.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_product_info_display.php';