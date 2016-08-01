<?php
/**
 * functions_categories.php
 *
 * @package functions
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: functions_categories.php  Modified in v1.6.0 $
 */

////
// Generate a path to categories
  function zen_get_path($current_category_id = '') {
    global $cPath_array, $db;

    if (zen_not_null($current_category_id)) {
      $cp_size = sizeof($cPath_array);
      if ($cp_size == 0) {
        $cPath_new = $current_category_id;
      } else {
        $cPath_new = '';
        $last_category_query = "select parent_id
                                from " . TABLE_CATEGORIES . "
                                where categories_id = " . (int)$cPath_array[($cp_size-1)];

        $last_category = $db->Execute($last_category_query);

        $current_category_query = "select parent_id
                                   from " . TABLE_CATEGORIES . "
                                   where categories_id = " . (int)$current_category_id;

        $current_category = $db->Execute($current_category_query);

        if ($last_category->fields['parent_id'] == $current_category->fields['parent_id']) {
          for ($i=0; $i<($cp_size-1); $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        } else {
          for ($i=0; $i<$cp_size; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        }
        $cPath_new .= '_' . $current_category_id;

        if (substr($cPath_new, 0, 1) == '_') {
          $cPath_new = substr($cPath_new, 1);
        }
      }
    } else {
      $cPath_new = implode('_', $cPath_array);
    }

    return 'cPath=' . $cPath_new;
  }

/**
 * Return the number of products in a category
 *
 * @param int $categories_id
 * @param bool $include_deactivated
 * @param bool $include_child
 * @param bool $limit
 * @return int
 */
  function zen_count_products_in_category($categories_id, $include_deactivated = false, $include_child = true, $limit = false) {
    global $db;
    $products_count = 0;

    if ($limit) {
      $limit_count = ' limit 1';
    } else {
      $limit_count = '';
    }

    $products = $db->Execute("select count(*) as total
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                              where p.products_id = p2c.products_id " .
                              ($include_deactivated ? ' and p.products_status = 1 ' : '') . "
                              and p2c.categories_id = " . (int)$categories_id . $limit_count);

    $products_count += $products->fields['total'];

    if ($include_child) {
      $childs = $db->Execute("select categories_id from " . TABLE_CATEGORIES . "
                              where parent_id = " . (int)$categories_id);
      if ($childs->RecordCount() > 0 ) {
        while (!$childs->EOF) {
          $products_count += zen_count_products_in_category($childs->fields['categories_id'], $include_deactivated);
          $childs->MoveNext();
        }
      }
    }
    return $products_count;
  }

/**
 * Returns true if the category has subcategories
 *
 * @param int $category_id
 * @return bool
 */
  function zen_has_category_subcategories($category_id) {
    global $db;
    $child_category_query = "select count(*) as count
                             from " . TABLE_CATEGORIES . "
                             where parent_id = " . (int)$category_id;

    $child_category = $db->Execute($child_category_query);

    return ($child_category->fields['count'] > 0);
  }

/**
 * @param array $categories_array
 * @param int $parent_id
 * @param string $indent
 * @param string $status_setting
 * @return array|string
 */
  function zen_get_categories($categories_array = '', $parent_id = TOPMOST_CATEGORY_PARENT_ID, $indent = '', $status_setting = '') {
    global $db;

    if (!is_array($categories_array)) $categories_array = array();

    // show based on status
    if ($status_setting != '') {
      $zc_status = " c.categories_status='" . (int)$status_setting . "' and ";
    } else {
      $zc_status = '';
    }

    $categories_query = "select c.categories_id, cd.categories_name, c.categories_status
                         from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                         where " . $zc_status . "
                         parent_id = " . (int)$parent_id . "
                         and c.categories_id = cd.categories_id
                         and cd.language_id = " . (int)$_SESSION['languages_id'] . "
                         order by sort_order, cd.categories_name";

    $categories = $db->Execute($categories_query);

    while (!$categories->EOF) {
      $categories_array[] = array('id' => $categories->fields['categories_id'],
                                  'text' => $indent . $categories->fields['categories_name']);

      if ($categories->fields['categories_id'] != $parent_id) {
        $categories_array = zen_get_categories($categories_array, $categories->fields['categories_id'], $indent . '&nbsp;&nbsp;', '1');
      }
      $categories->MoveNext();
    }

    return $categories_array;
  }

////
// Return all subcategory IDs
/**
 * @param array $subcategories_array
 * @param int $parent_id
 */
  function zen_get_subcategories(&$subcategories_array, $parent_id = TOPMOST_CATEGORY_PARENT_ID) {
    global $db;
    $subcategories_query = "select categories_id
                            from " . TABLE_CATEGORIES . "
                            where parent_id = " . (int)$parent_id;

    $subcategories = $db->Execute($subcategories_query);

    while (!$subcategories->EOF) {
      $subcategories_array[sizeof($subcategories_array)] = $subcategories->fields['categories_id'];
      if ($subcategories->fields['categories_id'] != $parent_id) {
        zen_get_subcategories($subcategories_array, $subcategories->fields['categories_id']);
      }
      $subcategories->MoveNext();
    }
  }


/**
 * Recursively go through the categories and retrieve all parent categories IDs
 *
 * @param array $categories
 * @param int $categories_id
 * @return bool
 */
  function zen_get_parent_categories(&$categories, $categories_id) {
    global $db;
    $parent_categories_query = "select parent_id
                                from " . TABLE_CATEGORIES . "
                                where categories_id = " . (int)$categories_id;

    $parent_categories = $db->Execute($parent_categories_query);

    while (!$parent_categories->EOF) {
      if ($parent_categories->fields['parent_id'] == (int)TOPMOST_CATEGORY_PARENT_ID) return true;
      $categories[sizeof($categories)] = $parent_categories->fields['parent_id'];
      if ($parent_categories->fields['parent_id'] != $categories_id) {
        zen_get_parent_categories($categories, $parent_categories->fields['parent_id']);
      }
      $parent_categories->MoveNext();
    }
  }

/**
 * Construct a category path to the product
 * TABLES: products_to_categories
 */
  function zen_get_product_path($products_id, $status_override = '1') {
    global $db;
    $cPath = '';

/*
    $category_query = "select p2c.categories_id
                       from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                       where p.products_id = '" . (int)$products_id . "' " .
                       ($status_override == 1 ? " and p.products_status = 1 " : '') . "
                       and p.products_id = p2c.products_id limit 1";
*/

    $category_query = "select p.products_id, p.master_categories_id
                       from " . TABLE_PRODUCTS . " p
                       where p.products_id = " . (int)$products_id . " limit 1";


    $category = $db->Execute($category_query);

    if ($category->RecordCount() > 0) {

      $categories = array();
      zen_get_parent_categories($categories, $category->fields['master_categories_id']);

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);

      if (zen_not_null($cPath)) $cPath .= '_';
      $cPath .= $category->fields['master_categories_id'];
    }

    return $cPath;
  }

////
// Parse and secure the cPath parameter values
  function zen_parse_category_path($cPath) {
// make sure the category IDs are integers
    $cPath_array = array_map('zen_string_to_int', explode('_', $cPath));

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($cPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($cPath_array[$i], $tmp_array)) {
        $tmp_array[] = $cPath_array[$i];
      }
    }

    return $tmp_array;
  }

  function zen_product_in_category($product_id, $cat_id) {
    global $db;
    $in_cat=false;
    $category_query_raw = "select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                           where products_id = " . (int)$product_id;

    $category = $db->Execute($category_query_raw);

    while (!$category->EOF) {
      if ($category->fields['categories_id'] == $cat_id) $in_cat = true;
      if (!$in_cat) {
        $parent_categories_query = "select parent_id from " . TABLE_CATEGORIES . "
                                    where categories_id = " . $category->fields['categories_id'];

        $parent_categories = $db->Execute($parent_categories_query);
//echo 'cat='.$category->fields['categories_id'].'#'. $cat_id;

        while (!$parent_categories->EOF) {
          if (($parent_categories->fields['parent_id'] != (int)TOPMOST_CATEGORY_PARENT_ID) ) {
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
//echo $cat_id . '#' . $parent_cat_id;
    if ($cat_id == $parent_cat_id) {
      $in_cat = true;
    } else {
      $parent_categories_query = "select parent_id from " . TABLE_CATEGORIES . "
                                  where categories_id = " . (int)$parent_cat_id;

      $parent_categories = $db->Execute($parent_categories_query);

      while (!$parent_categories->EOF) {
        if ($parent_categories->fields['parent_id'] != (int)TOPMOST_CATEGORY_PARENT_ID && !$in_cat) {
          $in_cat = zen_product_in_parent_category($product_id, $cat_id, $parent_categories->fields['parent_id']);
        }
        $parent_categories->MoveNext();
      }
    }
    return $in_cat;
  }


////
// products with name, model and price pulldown
  function zen_draw_products_pull_down($name, $parameters = '', $exclude = '', $show_id = false, $set_selected = false, $show_model = false, $show_current_category = false) {
    global $currencies, $db, $current_category_id;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    if ($show_current_category) {
// only show $current_categories_id
      $products = $db->Execute("select p.products_id, pd.products_name, p.products_price, p.products_model, ptc.categories_id
                                from " . TABLE_PRODUCTS . " p
                                left join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc on ptc.products_id = p.products_id, " .
                                TABLE_PRODUCTS_DESCRIPTION . " pd
                                where p.products_id = pd.products_id
                                and pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                and ptc.categories_id = " . (int)$current_category_id . "
                                order by products_name");
    } else {
      $products = $db->Execute("select p.products_id, pd.products_name, p.products_price, p.products_model
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where p.products_id = pd.products_id
                              and pd.language_id = " . (int)$_SESSION['languages_id'] . "
                              order by products_name");
    }

    while (!$products->EOF) {
      if (!in_array($products->fields['products_id'], $exclude)) {
        $display_price = zen_get_products_base_price($products->fields['products_id']);
        $select_string .= '<option value="' . $products->fields['products_id'] . '"';
        if ($set_selected == $products->fields['products_id']) $select_string .= ' SELECTED';
        $select_string .= '>' . $products->fields['products_name'] . ' (' . $currencies->format($display_price) . ')' . ($show_model ? ' [' . $products->fields['products_model'] . '] ' : '') . ($show_id ? ' - ID# ' . $products->fields['products_id'] : '') . '</option>';
      }
      $products->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }

/**
 * product pulldown with attributes
 */
  function zen_draw_products_pull_down_attributes($name, $parameters = '', $exclude = '') {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $new_fields=', p.products_model';

    $products = $db->Execute("select distinct p.products_id, pd.products_name, p.products_price" . $new_fields ."
                              from " . TABLE_PRODUCTS . " p, " .
                                       TABLE_PRODUCTS_DESCRIPTION . " pd, " .
                                       TABLE_PRODUCTS_ATTRIBUTES . " pa " ."
                              where p.products_id= pa.products_id and p.products_id = pd.products_id
                              and pd.language_id = " . (int)$_SESSION['languages_id'] . "
                              order by products_name");

    while (!$products->EOF) {
      if (!in_array($products->fields['products_id'], $exclude)) {
        $display_price = zen_get_products_base_price($products->fields['products_id']);
        $select_string .= '<option value="' . $products->fields['products_id'] . '">' . $products->fields['products_name'] . ' (' . TEXT_MODEL . ' ' . $products->fields['products_model'] . ') (' . $currencies->format($display_price) . ')</option>';
      }
      $products->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }


/**
 * categories pulldown with products
 */
  function zen_draw_products_pull_down_categories($name, $parameters = '', $exclude = '', $show_id = false, $show_parent = false) {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $categories = $db->Execute("select distinct c.categories_id, cd.categories_name
                                from " . TABLE_CATEGORIES . " c, " .
                                         TABLE_CATEGORIES_DESCRIPTION . " cd, " .
                                         TABLE_PRODUCTS_TO_CATEGORIES . " ptoc
                                where ptoc.categories_id = c.categories_id
                                and c.categories_id = cd.categories_id
                                and cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                order by categories_name");

    while (!$categories->EOF) {
      if (!in_array($categories->fields['categories_id'], $exclude)) {
        if ($show_parent == true) {
          $parent = zen_get_categories_parent_name($categories->fields['categories_id']);
          if ($parent != '') {
            $parent = ' : in ' . $parent;
          }
        } else {
          $parent = '';
        }
        $select_string .= '<option value="' . $categories->fields['categories_id'] . '">' . $categories->fields['categories_name'] . $parent . ($show_id ? ' - ID#' . $categories->fields['categories_id'] : '') . '</option>';
      }
      $categories->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }

/**
 * categories pulldown with products with attributes
 */
  function zen_draw_products_pull_down_categories_attributes($name, $parameters = '', $exclude = '') {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $categories = $db->Execute("select distinct c.categories_id, cd.categories_name 
                                from " . TABLE_CATEGORIES . " c, " .
                                         TABLE_CATEGORIES_DESCRIPTION . " cd, " .
                                         TABLE_PRODUCTS_TO_CATEGORIES . " ptoc, " .
                                         TABLE_PRODUCTS_ATTRIBUTES . " pa 
                                where pa.products_id= ptoc.products_id
                                and ptoc.categories_id= c.categories_id
                                and c.categories_id = cd.categories_id
                                and cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                order by categories_name");

    while (!$categories->EOF) {
      if (!in_array($categories->fields['categories_id'], $exclude)) {
        $select_string .= '<option value="' . $categories->fields['categories_id'] . '">' . $categories->fields['categories_name'] . '</option>';
      }
      $categories->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }

////
// look up categories product_type
  function zen_get_product_types_to_category($lookup) {
    global $db;

    $lookup = str_replace('cPath=','',$lookup);

    $sql = "select product_type_id from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " where category_id=" . (int)$lookup;
    $look_up = $db->Execute($sql);

    if ($look_up->RecordCount() > 0) {
      return $look_up->fields['product_type_id'];
    } else {
      return false;
    }
  }

/**
 * look up parent category name
 */
  function zen_get_categories_parent_name($categories_id) {
    global $db;

    $lookup_query = "select parent_id from " . TABLE_CATEGORIES . " where categories_id=" . (int)$categories_id;
    $lookup = $db->Execute($lookup_query);

    return zen_get_category_name($lookup->fields['parent_id'], (int)$_SESSION['languages_id']);
  }

/**
 * Get all products_id in a Category and its SubCategories
 * use as:
 * $my_products_id_list = array();
 * $my_products_id_list = zen_get_categories_products_list($categories_id)
 */
  function zen_get_categories_products_list($categories_id, $include_deactivated = false, $include_child = true, $parent_category = TOPMOST_CATEGORY_PARENT_ID, $display_limit = '') {
    global $db;
    global $categories_products_id_list;
    $childCatID = str_replace('_', '', substr($categories_id, strrpos($categories_id, '_')));

    $current_cPath = ($parent_category != (int)TOPMOST_CATEGORY_PARENT_ID ? $parent_category . '_' : '') . $categories_id;

    $sql = "select p.products_id
            from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
            where p.products_id = p2c.products_id
            and p2c.categories_id = " . (int)$childCatID .
            ($include_deactivated ? " and p.products_status = 1" : "") .
            $display_limit;

    $products = $db->Execute($sql);
    while (!$products->EOF) {
      $categories_products_id_list[$products->fields['products_id']] = $current_cPath;
      $products->MoveNext();
    }

    if ($include_child) {
      $sql = "select categories_id from " . TABLE_CATEGORIES . "
              where parent_id = " . (int)$childCatID;

      $childs = $db->Execute($sql);
      if ($childs->RecordCount() > 0 ) {
        while (!$childs->EOF) {
          zen_get_categories_products_list($childs->fields['categories_id'], $include_deactivated, $include_child, $current_cPath, $display_limit);
          $childs->MoveNext();
        }
      }
    }
    return $categories_products_id_list;
  }

//// bof: manage master_categories_id vs cPath
  function zen_generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
    global $db;

    if (!is_array($categories_array)) $categories_array = array();

    if ($from == 'product') {
      $categories = $db->Execute("select categories_id
                                  from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                  where products_id = " . (int)$id);

      while (!$categories->EOF) {
        if ($categories->fields['categories_id'] == (int)TOPMOST_CATEGORY_PARENT_ID) {
          $categories_array[$index][] = array('id' => (int)TOPMOST_CATEGORY_PARENT_ID, 'text' => TEXT_TOP);
        } else {
          $category = $db->Execute("select cd.categories_name, c.parent_id
                                    from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                    where c.categories_id = " . (int)$categories->fields['categories_id'] . "
                                    and c.categories_id = cd.categories_id
                                    and cd.language_id = " . (int)$_SESSION['languages_id']);

          $categories_array[$index][] = array('id' => $categories->fields['categories_id'], 'text' => $category->fields['categories_name']);
          if (zen_not_null($category->fields['parent_id']) && $category->fields['parent_id'] != (int)TOPMOST_CATEGORY_PARENT_ID) {
            $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
          }
          $categories_array[$index] = array_reverse($categories_array[$index]);
        }
        $index++;
        $categories->MoveNext();
      }
    } elseif ($from == 'category') {
      $category = $db->Execute("select cd.categories_name, c.parent_id
                                from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                where c.categories_id = " . (int)$id . "
                                and c.categories_id = cd.categories_id
                                and cd.language_id = " . (int)$_SESSION['languages_id']);
      if (!$category->EOF) {
        $categories_array[$index][] = array('id' => $id, 'text' => $category->fields['categories_name']);
        if (zen_not_null($category->fields['parent_id']) && $category->fields['parent_id'] != (int)TOPMOST_CATEGORY_PARENT_ID) {
          $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
        }
      }
    }

    return $categories_array;
  }

  function zen_output_generated_category_path($id, $from = 'category') {
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($id, $from);

    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        if ($from == 'category') {
          $calculated_category_path_string = $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;' . $calculated_category_path_string;
        } else {
          $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'];
          $calculated_category_path_string .= ' [ ' . TEXT_INFO_ID . $calculated_category_path[$i][$j]['id'] . ' ] ';
          $calculated_category_path_string .= '<br>';
          $calculated_category_path_string .= '&nbsp;&nbsp;';
//           $calculated_category_path_string .= '&nbsp;&gt;&nbsp;';
        }
      }
      if ($from == 'product') {
        $calculated_category_path_string .= '<br>';
      }
    }
    $calculated_category_path_string = preg_replace('/&nbsp;&gt;&nbsp;$/', '', $calculated_category_path_string);
    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
  }

  function zen_get_generated_category_path_ids($id, $from = 'category') {
    global $db;
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['id'] . '_';
      }
      $calculated_category_path_string = rtrim($calculated_category_path_string, '_') . '<br>';
    }
    $calculated_category_path_string = preg_replace('/<br>$/', '', $calculated_category_path_string);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
  }

  function zen_get_generated_category_path_rev($this_categories_id) {
    $categories = array();
    zen_get_parent_categories($categories, $this_categories_id);

    $categories = array_reverse($categories);

    $categories_imploded = implode('_', $categories);

    if (zen_not_null($categories_imploded)) $categories_imploded .= '_';
    $categories_imploded .= $this_categories_id;

    return $categories_imploded;
  }

// manage master_categories_id vs cPath
  function zenGetLeafCategory($cpath)
  {
    return (str_replace('_', '', substr($cpath, strrpos($cpath, '_'))));
  }
  function zenGetCategoryArrayWithChildren($categoryId, $categories = array())
  {
    global $db;

    $categories[] = $categoryId;

    $sql = "SELECT categories_id
            FROM " . TABLE_CATEGORIES . "
            WHERE parent_id = " . (int) $categoryId;
    $result = $db->Execute($sql);
    if ($result->RecordCount() > 0)
    {
      while (!$result->EOF)
      {
        $categories = zenGetCategoryArrayWithChildren($result->fields['categories_id'], $categories);
        $result->MoveNext();
      }
    }
    return $categories;
  }


  function zen_get_category_tree($parent_id = TOPMOST_CATEGORY_PARENT_ID, $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $category_has_products = false, $limit = false) {
    global $db;

    if ($limit) {
      $limit_count = " limit 1";
    } else {
      $limit_count = '';
    }

    if (!is_array($category_tree_array)) $category_tree_array = array();
    if ( (sizeof($category_tree_array) < 1) && ($exclude != TOPMOST_CATEGORY_PARENT_ID) ) {
      $category_tree_array[] = array('id' => TOPMOST_CATEGORY_PARENT_ID, 'text' => TEXT_TOP);
    }

    if ($include_itself) {
      $category = $db->Execute("select cd.categories_name
                                from " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                where cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                and cd.categories_id = " . (int)$parent_id . $limit_count);

      $category_tree_array[] = array('id' => $parent_id, 'text' => $category->fields['categories_name']);
    }

    $categories = $db->Execute("select c.categories_id, cd.categories_name, c.parent_id
                                from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                where c.categories_id = cd.categories_id
                                and cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                and c.parent_id = " . (int)$parent_id . "
                                order by c.sort_order, cd.categories_name" . $limit_count);

    while (!$categories->EOF) {
      if ($category_has_products == true and zen_count_products_in_category($categories->fields['categories_id'], '', false, true) >= 1) {
        $mark = '*';
      } else {
        $mark = '&nbsp;&nbsp;';
      }
      if ($exclude != $categories->fields['categories_id']) $category_tree_array[] = array('id' => $categories->fields['categories_id'], 'text' => $spacing . $categories->fields['categories_name'] . $mark);
      $category_tree_array = zen_get_category_tree($categories->fields['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, '', $category_has_products);
      $categories->MoveNext();
    }

    return $category_tree_array;
  }

  function zen_count_products_in_cats($category_id) {
    global $db;
    $c_array = array();
    $cat_products_query = "select count(if (p.products_status=1,1,NULL)) as pr_on, count(*) as total
                           from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = " . (int)$category_id;

    $pr_count = $db->Execute($cat_products_query);
//    echo $pr_count->RecordCount();
    $c_array['this_count'] += $pr_count->fields['total'];
    $c_array['this_count_on'] += $pr_count->fields['pr_on'];

    $cat_child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = " . (int)$category_id;

    $cat_child_categories = $db->Execute($cat_child_categories_query);

    if ($cat_child_categories->RecordCount() > 0) {
      while (!$cat_child_categories->EOF) {
          $m_array = zen_count_products_in_cats($cat_child_categories->fields['categories_id']);
          $c_array['this_count'] += $m_array['this_count'];
          $c_array['this_count_on'] += $m_array['this_count_on'];

//          $this_count_on += $pr_count->fields['pr_on'];
        $cat_child_categories->MoveNext();
      }
    }
    return $c_array;
 }

/**
 * Return the number of products in a category
 * TABLES: products, products_to_categories, categories
 * syntax for count: zen_get_products_to_categories($categories->fields['categories_id'], true)
 * syntax for linked products: zen_get_products_to_categories($categories->fields['categories_id'], true, 'products_active')
 */
  function zen_get_products_to_categories($category_id, $include_inactive = false, $counts_what = 'products') {
    global $db;

    $cat_products_count = 0;
    $products_linked = '';
    switch ($counts_what) {
      case ('products'):
      $cat_products_query = "select count(*) as total
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id " .
                         ($include_inactive ? '' :  ' and p.products_status = 1 ') . "
                         and p2c.categories_id = " . (int)$category_id;
      break;
      case ('products_active'):
      $cat_products_query = "select p.products_id
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                         where p.products_id = p2c.products_id" .
                         ($include_inactive ? '' :  ' and p.products_status = 1 ') . "
                         and p2c.categories_id = " . (int)$category_id;
      break;
    }
    $cat_products = $db->Execute($cat_products_query);
    switch ($counts_what) {
      case ('products'):
        if (!$cat_products->EOF) $cat_products_count += $cat_products->fields['total'];
        break;
      case ('products_active'):
        while (!$cat_products->EOF) {
          if (zen_get_product_is_linked($cat_products->fields['products_id']) == 'true') {
            return true;
          }
          $cat_products->MoveNext();
        }
        break;
    }

    $cat_child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = '" . (int)$category_id . "'";

    $cat_child_categories = $db->Execute($cat_child_categories_query);

    if ($cat_child_categories->RecordCount() > 0) {
      while (!$cat_child_categories->EOF) {
      switch ($counts_what) {
        case ('products'):
          $cat_products_count += zen_get_products_to_categories($cat_child_categories->fields['categories_id'], $include_inactive);
          break;
        case ('products_active'):
          if (zen_get_products_to_categories($cat_child_categories->fields['categories_id'], true, 'products_active') === true) {
            return true;
          }
          break;
        }
        $cat_child_categories->MoveNext();
      }
    }

    switch ($counts_what) {
      case ('products'):
        return $cat_products_count;
        break;
      case ('products_active'):
        return $products_linked;
        break;
    }
  }

/**
 * check if linked
 * NOTE: returns stringified boolean, until legacy code using these string responses is rewritten
 *
 * @param int $product_id
 * @param bool $show_count
 * @return int|string
 */
  function zen_get_product_is_linked($product_id, $show_count = false) {
    global $db;

    $sql = "select * from " . TABLE_PRODUCTS_TO_CATEGORIES . (zen_not_null($product_id) ? " where products_id=" . (int)$product_id : "");
    $check_linked = $db->Execute($sql);
    if ($check_linked->RecordCount() > 1) {
      if ($show_count == true) {
        return $check_linked->RecordCount();
      } else {
        return 'true';
      }
    } else {
      return 'false';
    }
  }

/**
 * Lookup and return product's master_categories_id
 *
 * @param int $product_id
 * @return string
 */
  function zen_get_parent_category_id($product_id) {
    global $db;

    $categories_lookup = $db->Execute("select master_categories_id
                                from " . TABLE_PRODUCTS . "
                                where products_id = " . (int)$product_id);
    if ($categories_lookup->EOF) return '';
    return $categories_lookup->fields['master_categories_id'];
  }

/**
 * Count how many subcategories exist in a category
 *
 * @param int $categories_id
 * @return int
 */
  function zen_childs_in_category_count($categories_id) {
    global $db;
    $categories_count = 0;

    $categories = $db->Execute("select categories_id
                                from " . TABLE_CATEGORIES . "
                                where parent_id = " . (int)$categories_id);

    while (!$categories->EOF) {
      $categories_count++;
      $categories_count += zen_childs_in_category_count($categories->fields['categories_id']);
      $categories->MoveNext();
    }

    return $categories_count;
  }

