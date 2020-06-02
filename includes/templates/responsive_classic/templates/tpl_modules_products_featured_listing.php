<?php
/**
 * Module Template
 *
 * Loaded automatically by index.php?main_page=featured_products.<br />
 * Displays listing of Featured Products
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 26 Modified in v1.5.7 $
 */
?>
<div id="product-listing">
      
<?php
  $group_id = zen_get_configuration_key_value('PRODUCT_FEATURED_LIST_GROUP_ID');

  if ($featured_products_split->number_of_rows > 0) {
    $featured_products = $db->Execute($featured_products_split->sql_query);

    while (!$featured_products->EOF) {


      if (PRODUCT_FEATURED_LIST_IMAGE != '0') {
        if ($featured_products->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) {
          $display_products_image = str_repeat('<br/>', substr(PRODUCT_FEATURED_LIST_IMAGE, 3, 1));
        } else {
          $display_products_image = '<a href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($featured_products->fields['master_categories_id']) . '&products_id=' . $featured_products->fields['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $featured_products->fields['products_image'], $featured_products->fields['products_name'], IMAGE_FEATURED_PRODUCTS_LISTING_WIDTH, IMAGE_FEATURED_PRODUCTS_LISTING_HEIGHT) . '</a>' . str_repeat('<br  />', substr(PRODUCT_FEATURED_LIST_IMAGE, 3, 1));
        }
      } else {
        $display_products_image = '';
      }

      if (PRODUCT_FEATURED_LIST_NAME != '0') {
        $display_products_name = '<div class="itemTitle"><a href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($featured_products->fields['master_categories_id']) . '&products_id=' . $featured_products->fields['products_id']) . '">' . $featured_products->fields['products_name'] . '</a></div>' . str_repeat('<br />', substr(PRODUCT_FEATURED_LIST_NAME, 3, 1));
      } else {
        $display_products_name = '';
      }

      if (PRODUCT_FEATURED_LIST_MODEL != '0' and zen_get_show_product_switch($featured_products->fields['products_id'], 'model')) {
        $display_products_model = '<b>' . TEXT_PRODUCT_MODEL . '</b>' . $featured_products->fields['products_model'] . str_repeat('<br  />', substr(PRODUCT_FEATURED_LIST_MODEL, 3, 1));
      } else {
        $display_products_model = '';
      }

      if (PRODUCT_FEATURED_LIST_WEIGHT != '0' and zen_get_show_product_switch($featured_products->fields['products_id'], 'weight')) {
        $display_products_weight = '<b>' . TEXT_PRODUCTS_WEIGHT . '</b>' . $featured_products->fields['products_weight'] . TEXT_SHIPPING_WEIGHT . str_repeat('<br />', substr(PRODUCT_FEATURED_LIST_WEIGHT, 3, 1));
      } else {
        $display_products_weight = '';
      }

      if (PRODUCT_FEATURED_LIST_QUANTITY != '0' and zen_get_show_product_switch($featured_products->fields['products_id'], 'quantity')) {
        if ($featured_products->fields['products_quantity'] <= 0) {
          $display_products_quantity = TEXT_OUT_OF_STOCK . str_repeat('<br />', substr(PRODUCT_FEATURED_LIST_QUANTITY, 3, 1));
        } else {
          $display_products_quantity = '<b>' . TEXT_PRODUCTS_QUANTITY . '</b>' . $featured_products->fields['products_quantity'] . str_repeat('<br />', substr(PRODUCT_FEATURED_LIST_QUANTITY, 3, 1));
        }
      } else {
        $display_products_quantity = '';
      }

      if (PRODUCT_FEATURED_LIST_DATE_ADDED != '0' and zen_get_show_product_switch($featured_products->fields['products_id'], 'date_added')) {
        $display_products_date_added = '<b>' . TEXT_DATE_ADDED . '</b> ' . zen_date_long($featured_products->fields['products_date_added']) . str_repeat('<br  />', substr(PRODUCT_FEATURED_LIST_DATE_ADDED, 3, 1));
      } else {
        $display_products_date_added = '';
      }

      if (PRODUCT_FEATURED_LIST_MANUFACTURER != '0' and zen_get_show_product_switch($featured_products->fields['products_id'], 'manufacturer')) {
        $display_products_manufacturers_name = ($featured_products->fields['manufacturers_name'] != '' ? '<b>' . TEXT_MANUFACTURER . '</b> ' . $featured_products->fields['manufacturers_name'] . str_repeat('<br />', substr(PRODUCT_FEATURED_LIST_MANUFACTURER, 3, 1)) : '');
      } else {
        $display_products_manufacturers_name = '';
      }

      if ((PRODUCT_FEATURED_LIST_PRICE != '0' and zen_get_products_allow_add_to_cart($featured_products->fields['products_id']) == 'Y')  and zen_check_show_prices() == true) {
        $products_price = zen_get_products_display_price($featured_products->fields['products_id']);
        $display_products_price = '<b>' . TEXT_PRICE . '</b> ' . $products_price . str_repeat('<br />', substr(PRODUCT_FEATURED_LIST_PRICE, 3, 1)) . (zen_get_show_product_switch($featured_products->fields['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH') ? (zen_get_product_is_always_free_shipping($featured_products->fields['products_id']) ? TEXT_PRODUCT_FREE_SHIPPING_ICON . '<br />' : '') : '');
      } else {
        $display_products_price = '';
      }

// more info in place of buy now
      if (PRODUCT_FEATURED_BUY_NOW != '0' and zen_get_products_allow_add_to_cart($featured_products->fields['products_id']) == 'Y') {
        if (zen_has_product_attributes($featured_products->fields['products_id'])) {
          $link = '<a class="list-more" href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($featured_products->fields['master_categories_id']) . '&products_id=' . $featured_products->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        } else {
//          $link= '<a href="' . zen_href_link(FILENAME_FEATURED_PRODUCTS, zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $featured_products->fields['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT) . '</a>';
          if (PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART > 0 && $featured_products->fields['products_qty_box_status'] != 0) {
//            $how_many++;
            $link = TEXT_PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART . '<input type="text" name="products_id["' . $featured_products->fields['products_id'] . ']" value="0" size="4" aria-label="' . ARIA_QTY_ADD_TO_CART . '">';
          } else {
            $link = '<a href="' . zen_href_link(FILENAME_FEATURED_PRODUCTS, zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $featured_products->fields['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT) . '</a>&nbsp;';
          }
        }

        $the_button = $link;
        $products_link = '<a class="list-more" href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($featured_products->fields['master_categories_id']) . '&products_id=' . $featured_products->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        $display_products_button = zen_get_buy_now_button($featured_products->fields['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($featured_products->fields['products_id']) . str_repeat('<br />', substr(PRODUCT_FEATURED_BUY_NOW, 3, 1));
      } else {
        $link = '<a class="list-more" href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($featured_products->fields['master_categories_id']) . '&products_id=' . $featured_products->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        $the_button = $link;
        $products_link = '<a class="list-more" href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($featured_products->fields['master_categories_id']) . '&products_id=' . $featured_products->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        $display_products_button = zen_get_buy_now_button($featured_products->fields['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($featured_products->fields['products_id']) . str_repeat('<br />', substr(PRODUCT_FEATURED_BUY_NOW, 3, 1));
      }

      if (PRODUCT_FEATURED_LIST_DESCRIPTION > '0') {
        $disp_text = zen_get_products_description($featured_products->fields['products_id']);
        $disp_text = zen_clean_html($disp_text);

        $display_products_description = stripslashes(zen_trunc_string($disp_text, PRODUCT_FEATURED_LIST_DESCRIPTION, '<a href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($featured_products->fields['master_categories_id']) . '&products_id=' . $featured_products->fields['products_id']) . '"> ' . MORE_INFO_TEXT . '</a>'));
      } else {
        $display_products_description = '';
      }

?>
          <div class="listing-wrapper group">
            <div class="listing-left back">
              <?php
                $disp_sort_order = $db->Execute("select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id='" . $group_id . "' and (configuration_value >= 1000 and configuration_value <= 1999) order by LPAD(configuration_value,11,0)");
                while (!$disp_sort_order->EOF) {
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_IMAGE') {
                    echo $display_products_image;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_QUANTITY') {
                    echo $display_products_quantity;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_BUY_NOW') {
                    echo $display_products_button;
                  }

                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_NAME') {
                    echo $display_products_name;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_MODEL') {
                    echo $display_products_model;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_MANUFACTURER') {
                    echo $display_products_manufacturers_name;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_PRICE') {
                    echo $display_products_price;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_WEIGHT') {
                    echo $display_products_weight;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_DATE_ADDED') {
                    echo $display_products_date_added;
                  }
                  $disp_sort_order->MoveNext();
                }
              ?>
            </div>
            <div class="back listing-right">
              <?php
                $disp_sort_order = $db->Execute("select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id='" . $group_id . "' and (configuration_value >= 2000 and configuration_value <= 2999) order by LPAD(configuration_value,11,0)");
                while (!$disp_sort_order->EOF) {
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_IMAGE') {
                    echo $display_products_image;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_QUANTITY') {
                    echo $display_products_quantity;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_BUY_NOW') {
                    echo $display_products_button;
                  }

                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_NAME') {
                    echo $display_products_name;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_MODEL') {
                    echo $display_products_model;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_MANUFACTURER') {
                    echo $display_products_manufacturers_name;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_PRICE') {
                    echo $display_products_price;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_WEIGHT') {
                    echo $display_products_weight;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_FEATURED_LIST_DATE_ADDED') {
                    echo $display_products_date_added;
                  }
                  $disp_sort_order->MoveNext();
                }
              ?>
            </div>

<?php if (PRODUCT_FEATURED_LIST_DESCRIPTION > '0') { ?>
          <div class="clearBoth listings-description">
              <?php
                echo $display_products_description;
              ?>
          </div>
<?php } ?>

</div>

<?php
      $featured_products->MoveNext();
    }
  } else {
?>
          <div id="no-products" class="clearBoth">
            <?php echo TEXT_NO_FEATURED_PRODUCTS; ?>
          </div>
<?php
  }
?>
</div>
