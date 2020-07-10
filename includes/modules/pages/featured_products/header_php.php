<?php
/**
 * Featured Products
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 14 Modified in v1.5.7 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_FEATURED_PRODUCTS');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE);
// display order dropdown
$disp_order_default = PRODUCT_FEATURED_LIST_SORT_DEFAULT;

require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_LISTING_DISPLAY_ORDER));

$featured_products_array = array();

$featured_products_query_raw = "SELECT p.products_id, p.products_type, pd.products_name, p.products_image, p.products_price, p.products_tax_class_id, p.products_date_added, m.manufacturers_name, p.products_model, p.products_quantity, p.products_weight, p.product_is_call,
                                  p.product_is_always_free_shipping, p.products_qty_box_status,
                                  p.master_categories_id
                                  FROM (" . TABLE_PRODUCTS . " p
                                  LEFT JOIN " . TABLE_MANUFACTURERS . " m on (p.manufacturers_id = m.manufacturers_id), " .
TABLE_PRODUCTS_DESCRIPTION . " pd
                                  LEFT JOIN " . TABLE_FEATURED . " f on pd.products_id = f.products_id )
                                  WHERE p.products_status = 1 and p.products_id = f.products_id and f.status = 1
                                  AND p.products_id = pd.products_id and pd.language_id = :languagesID " .
$order_by;

$featured_products_query_raw = $db->bindVars($featured_products_query_raw, ':languagesID', $_SESSION['languages_id'], 'integer');

$count_key = '*';
$zco_notifier->notify('NOTIFY_FEATURED_PRODUCTS_SQL_STRING', array(), $featured_products_query_raw, $count_key);

$featured_products_split = new splitPageResults($featured_products_query_raw, MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS, $count_key);

//check to see if we are in normal mode ... not showcase, not maintenance, etc
$show_submit = zen_run_normal();

// check whether to use multiple-add-to-cart, and whether top or bottom buttons are displayed
if (PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART > 0 and $show_submit == true and $featured_products_split->number_of_rows > 0) {

  // check how many rows
  $check_products_all = $db->Execute($featured_products_split->sql_query);
  $how_many = 0;
  while (!$check_products_all->EOF) {
    if (zen_has_product_attributes($check_products_all->fields['products_id'])) {
    } else {
      // needs a better check v1.3.1
      if ($check_products_all->fields['products_qty_box_status'] != 0) {
        if (zen_get_products_allow_add_to_cart($check_products_all->fields['products_id']) !='N') {
          if ($check_products_all->fields['product_is_call'] == 0) {
            if ((SHOW_PRODUCTS_SOLD_OUT_IMAGE == 1 and $check_products_all->fields['products_quantity'] > 0) or SHOW_PRODUCTS_SOLD_OUT_IMAGE == 0) {
              if ($check_products_all->fields['products_type'] != 3) {
                if (zen_has_product_attributes($check_products_all->fields['products_id']) < 1) {
                  $how_many++;
                }
              }
            }
          }
        }
      }
    }
    $check_products_all->MoveNext();
  }

  if ( (($how_many > 0 and $show_submit == true and $featured_products_split->number_of_rows > 0) and (PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART == 1 or  PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART == 3)) ) {
    $show_top_submit_button = true;
  } else {
    $show_top_submit_button = false;
  }
  if ( (($how_many > 0 and $show_submit == true and $featured_products_split->number_of_rows > 0) and (PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART >= 2)) ) {
    $show_bottom_submit_button = true;
  } else {
    $show_bottom_submit_button = false;
  }
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_FEATURED_PRODUCTS', $how_many);
//EOF
