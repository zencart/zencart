<?php
/**
 * whats_new sidebox - displays a random "new" product
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: whats_new.php 15880 2010-04-11 16:24:30Z wilt $
 */

// display limits
// $display_limit = zen_get_products_new_timelimit();
  $display_limit = zen_get_new_date_range();
  $random_whats_new_sidebox_product_query = "select p.products_id, p.products_image, p.products_tax_class_id, p.products_price, pd.products_name,
                                              p.master_categories_id
                           from (" . TABLE_PRODUCTS . " p
                           left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id )
                           where p.products_id = pd.products_id
                           and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                           and p.products_status = 1 " . $display_limit;

//  $random_whats_new_sidebox_product = zen_random_select($random_whats_new_sidebox_product_query);
  $random_whats_new_sidebox_product = $db->ExecuteRandomMulti($random_whats_new_sidebox_product_query, MAX_RANDOM_SELECT_NEW);

  if ($random_whats_new_sidebox_product->RecordCount() > 0 ) {
    require($template->get_template_dir('tpl_whats_new.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_whats_new.php');
    $title =  BOX_HEADING_WHATS_NEW;
    $title_link = FILENAME_PRODUCTS_NEW;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }