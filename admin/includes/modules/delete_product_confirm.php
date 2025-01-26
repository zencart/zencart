<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 18 Modified in v2.1.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// NOTE: Debug code left in to help with creating additional product type delete-scripts

$do_delete_flag = false;
//echo 'products_id=' . $_POST['products_id'] . '<br>';
if (isset($_POST['products_id'], $_POST['product_categories']) && is_array($_POST['product_categories'])) {
  $product_id = (int)$_POST['products_id'];
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
  for ($k = 0, $m = count($product_categories); $k < $m; $k++) {
      zen_unlink_product_from_category((int)$product_id, $product_categories[$k]);
  }
  // confirm that product is no longer linked to any categories
  $count_categories = zen_get_linked_categories_for_product((int)$product_id);
  // echo 'count of category links for this product=' . count($count_categories . '<br>';
  // if not linked to any categories, do delete:
  if (empty($count_categories)) {
    zen_remove_product($product_id, $delete_linked);
  }
} // endif $do_delete_flag
