<?php
/**
 * functions_categories.php
 *
 * @copyright Copyright 2003-2021 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2021 Jan 09 Modified in v1.5.7c $
 */

/**
 * Generate a cPath string from current category conditions
 */


////
// Return the number of products in a category
// TABLES: products, products_to_categories, categories
  function zen_count_products_in_category($category_id, $include_inactive = false) {
    global $db;
    $products_count = 0;
    if ($include_inactive == true) {
      $products_query = "select count(*) as total
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id
                         and p2c.categories_id = '" . (int)$category_id . "'";

    } else {
      $products_query = "select count(*) as total
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id
                         and p.products_status = '1'
                         and p2c.categories_id = '" . (int)$category_id . "'";

    }
    $products = $db->Execute($products_query);
    $products_count += $products->fields['total'];

    $child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = '" . (int)$category_id . "'";

    $child_categories = $db->Execute($child_categories_query);

    if ($child_categories->RecordCount() > 0) {
      while (!$child_categories->EOF) {
        $products_count += zen_count_products_in_category($child_categories->fields['categories_id'], $include_inactive);
        $child_categories->MoveNext();
      }
    }

    return $products_count;
  }

////
// Return all subcategory IDs
// TABLES: categories
  function zen_get_subcategories(&$subcategories_array, $parent_id = 0) {
    global $db;
    $subcategories_query = "select categories_id
                            from " . TABLE_CATEGORIES . "
                            where parent_id = '" . (int)$parent_id . "'";

    $subcategories = $db->Execute($subcategories_query);

    while (!$subcategories->EOF) {
      $subcategories_array[sizeof($subcategories_array)] = $subcategories->fields['categories_id'];
      if ($subcategories->fields['categories_id'] != $parent_id) {
        zen_get_subcategories($subcategories_array, $subcategories->fields['categories_id']);
      }
      $subcategories->MoveNext();
    }
  }

  function zen_product_in_category($product_id, $cat_id) {
    global $db;
    $in_cat=false;
    $category_query_raw = "select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                           where products_id = '" . (int)$product_id . "'";

    $category = $db->Execute($category_query_raw);

    while (!$category->EOF) {
      if ($category->fields['categories_id'] == $cat_id) $in_cat = true;
      if (!$in_cat) {
        $parent_categories_query = "select parent_id from " . TABLE_CATEGORIES . "
                                    where categories_id = '" . $category->fields['categories_id'] . "'";

        $parent_categories = $db->Execute($parent_categories_query);
//echo 'cat='.$category->fields['categories_id'].'#'. $cat_id;

        while (!$parent_categories->EOF) {
          if (($parent_categories->fields['parent_id'] !=0) ) {
            if (!$in_cat) $in_cat = zen_product_in_parent_category($product_id, $cat_id, $parent_categories->fields['parent_id']);
          }
          $parent_categories->MoveNext();
        }
      }
      $category->MoveNext();
    }
    return $in_cat;
  }

  function zen_product_in_parent_category($product_id, $cat_id, $parent_cat_id) {
    global $db;

    $in_cat = false;
    if ($cat_id == $parent_cat_id) {
      $in_cat = true;
    } else {
      $parent_categories_query = "select parent_id from " . TABLE_CATEGORIES . "
                                  where categories_id = '" . (int)$parent_cat_id . "'";

      $parent_categories = $db->Execute($parent_categories_query);

      while (!$parent_categories->EOF) {
        if ($parent_categories->fields['parent_id'] !=0 && !$in_cat) {
          $in_cat = zen_product_in_parent_category($product_id, $cat_id, $parent_categories->fields['parent_id']);
        }
        $parent_categories->MoveNext();
      }
    }
    return $in_cat;
  }

////
// look up categories product_type
  function zen_get_product_types_to_category($lookup) {
    global $db;

    $lookup = str_replace('cPath=','',$lookup);

    $sql = "select product_type_id from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " where category_id='" . (int)$lookup . "'";
    $look_up = $db->Execute($sql);

    if ($look_up->RecordCount() > 0) {
      return $look_up->fields['product_type_id'];
    } else {
      return false;
    }
  }

//// look up parent categories name
  function zen_get_categories_parent_name($categories_id) {
    global $db;

    $lookup_query = "select parent_id from " . TABLE_CATEGORIES . " where categories_id='" . (int)$categories_id . "'";
    $lookup = $db->Execute($lookup_query, 1);

    $lookup_query = "select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id='" . (int)$lookup->fields['parent_id'] . "' and language_id= " . $_SESSION['languages_id'];
    $lookup = $db->Execute($lookup_query, 1);

    return $lookup->EOF ? '' : $lookup->fields['categories_name'];
  }
//// bof: manage master_categories_id vs cPath

?>
