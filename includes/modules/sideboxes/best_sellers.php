<?php
/**
 * best_sellers sidebox - displays selected number of (usually top ten) best selling products
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 09 Modified in v1.5.7 $
 */

// test if box should display
  $show_best_sellers= false;

  if (isset($_GET['products_id'])) {
    if (zen_is_logged_in()) {
      $check_query = "SELECT COUNT(*) AS count
                      FROM " . TABLE_CUSTOMERS_INFO . "
                      WHERE customers_info_id = '" . (int)$_SESSION['customer_id'] . "'
                      AND global_product_notifications = '1'";

      $check = $db->Execute($check_query);

      if ($check->fields['count'] > 0) {
        $show_best_sellers= true;
      }
    }
  } else {
    $show_best_sellers= true;
  }

  if ($show_best_sellers == true) {
    $limit = (trim(MAX_DISPLAY_BESTSELLERS) == "") ? "" : " LIMIT " . (int)MAX_DISPLAY_BESTSELLERS;
  	if (isset($current_category_id) && ($current_category_id > 0)) {
      $best_sellers_query = "SELECT distinct p.products_id, pd.*, p.*
                             FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, "
                                    . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c
                             WHERE p.products_status = '1'
                             AND p.products_ordered > 0
                             AND p.products_id = pd.products_id
                             AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                             AND p.products_id = p2c.products_id
                             AND p2c.categories_id = c.categories_id
                             AND '" . (int)$current_category_id . "' IN (c.categories_id, c.parent_id)
                             ORDER BY p.products_ordered desc, pd.products_name";

      $best_sellers_query .= $limit;
      $best_sellers = $db->Execute($best_sellers_query);
    } else {
      $best_sellers_query = "SELECT DISTINCT p.products_id, pd.*, p.*
                             FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                             WHERE p.products_status = '1'
                             AND p.products_ordered > 0
                             AND p.products_id = pd.products_id
                             AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                             ORDER BY p.products_ordered desc, pd.products_name";

      $best_sellers_query .= $limit;
      $best_sellers = $db->Execute($best_sellers_query);
    }
if ($best_sellers->RecordCount() >= MIN_DISPLAY_BESTSELLERS) {
      $title =  BOX_HEADING_BESTSELLERS;
      $box_id =  'bestsellers';
      $rows = 0;
      while (!$best_sellers->EOF) {
        $rows++;
        $bestsellers_list[$rows]['id'] = $best_sellers->fields['products_id'];
        $bestsellers_list[$rows]['name']  = $best_sellers->fields['products_name'];
        $bestsellers_list[$rows]['image'] = zen_image(DIR_WS_IMAGES . $best_sellers->fields['products_image'], $best_sellers->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);;
        $bestsellers_list[$rows]['href'] = zen_href_link(zen_get_info_page($best_sellers->fields["products_id"]), 'cPath=' . zen_get_generated_category_path_rev($best_sellers->fields["master_categories_id"]) . '&products_id=' . $best_sellers->fields["products_id"]);
        $bestsellers_list[$rows]['price'] = zen_get_products_display_price((int)$best_sellers->fields['products_id']);
        $bestsellers_list[$rows]['model']  = $best_sellers->fields['products_model'];
        $bestsellers_list[$rows]['description']  = $best_sellers->fields['products_description'];
        $best_sellers->MoveNext();
      }

      $title_link = false;
      require($template->get_template_dir('tpl_best_sellers.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_best_sellers.php');
      $title =  BOX_HEADING_BESTSELLERS;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
