<?php
/**
 * specials sidebox - displays a random product "on special"
 *
 * @package templateSystem
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: specials.php 6475 2007-06-08 21:10:33Z ajeh $
 */

// test if box should display
  $show_specials= false;

  if (isset($_GET['products_id'])) {
    $show_specials= false;
  } else {
    $show_specials= true;
  }

  if ($show_specials == true) {
    $random_specials_sidebox_product_query = "select p.products_id, pd.products_name, p.products_price,
                                    p.products_tax_class_id, p.products_image,
                                    s.specials_new_products_price,
                                    p.master_categories_id
                             from " . TABLE_PRODUCTS . " p, " .
                                      TABLE_PRODUCTS_DESCRIPTION . " pd, " .
                                      TABLE_SPECIALS . " s
                             where p.products_status = 1
                             and p.products_id = s.products_id
                             and pd.products_id = s.products_id
                             and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                             and s.status = 1";

//    $random_specials_sidebox_product = zen_random_select($random_specials_sidebox_product_query);
    $random_specials_sidebox_product = $db->ExecuteRandomMulti($random_specials_sidebox_product_query, MAX_RANDOM_SELECT_SPECIALS);

    if ($random_specials_sidebox_product->RecordCount() > 0)  {
      require($template->get_template_dir('tpl_specials.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_specials.php');
      $title =  BOX_HEADING_SPECIALS;
      $title_link = FILENAME_SPECIALS;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
?>