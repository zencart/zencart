<?php

/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Mon Nov 12 20:38:09 2018 -0500 Modified in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// NOTE: Debug code left in to help with creating additional product type delete-scripts

$do_delete_flag = false;
//echo 'products_id=' . $_POST['products_id'] . '<br />';
if (isset($_POST['products_id']) && isset($_POST['product_categories']) && is_array($_POST['product_categories'])) {
  $product_id = zen_db_prepare_input($_POST['products_id']);
  $product_categories = $_POST['product_categories'];
  $do_delete_flag = true;
  if (!isset($delete_linked)) {
    $delete_linked = 'true';
  }
}

if (!empty($cascaded_prod_id_for_delete) && !empty($cascaded_prod_cat_for_delete)) {
  $product_id = $cascaded_prod_id_for_delete;
  $product_categories = $cascaded_prod_cat_for_delete;
  $do_delete_flag = true;
  // no check for $delete_linked here, because it should already be passed from categories.php
}

if ($do_delete_flag) {
  //--------------PRODUCT_TYPE_SPECIFIC_INSTRUCTIONS_GO__BELOW_HERE--------------------------------------------------------
  //--------------PRODUCT_TYPE_SPECIFIC_INSTRUCTIONS_GO__ABOVE__HERE--------------------------------------------------------
  // now do regular non-type-specific delete:
  // remove product from all its categories:
  for ($k = 0, $m = sizeof($product_categories); $k < $m; $k++) {
    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                  WHERE products_id = " . (int)$product_id . "
                  AND categories_id = " . (int)$product_categories[$k]);
  }
  // confirm that product is no longer linked to any categories
  $count_categories = $db->Execute("SELECT COUNT(categories_id) AS total
                                    FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                    WHERE products_id = " . (int)$product_id);
  // echo 'count of category links for this product=' . $count_categories->fields['total'] . '<br />';
  // if not linked to any categories, do delete:
  if ($count_categories->fields['total'] == '0') {
    zen_remove_product($product_id, $delete_linked);
  }
} // endif $do_delete_flag
// if this is a single-product delete, redirect to categories page
// if not, then this file was called by the cascading delete initiated by the category-delete process
if ($action == 'delete_product_confirm') {
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath));
}
