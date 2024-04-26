<?php
/**
 * whats_new sidebox - displays a random "new" product
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */

// display limits
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
