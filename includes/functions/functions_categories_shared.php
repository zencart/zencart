<?php
/**
 * functions_categories_shared.php
 *
 * @copyright Copyright 2003-2021 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2021 May 01 Modified in v1.5.7d $
 */

/**
 * Generate a cPath string from current category conditions
 */
function zen_get_path($current_category_id = '') {
  global $cPath_array, $db;

  if ($current_category_id === '' || empty($cPath_array)) {
    return 'cPath=' . (!empty($cPath_array) ? implode('_', $cPath_array) : $current_category_id);
  }

  // make copy so we can manipulate it later
  $cPath_categories = $cPath_array;

  $last_category_query = "SELECT parent_id
                            FROM " . TABLE_CATEGORIES . "
                            WHERE categories_id = " . (int)$cPath_categories[count($cPath_categories)-1];
  $last_category = $db->Execute($last_category_query);

  $current_category_query = "SELECT parent_id
                               FROM " . TABLE_CATEGORIES . "
                               WHERE categories_id = " . (int)$current_category_id;
  $current_category = $db->Execute($current_category_query);

  // Eject last category from array if not found or same as current
  if (!isset($last_category->fields['parent_id'], $current_category->fields['parent_id'])) {
    array_pop($cPath_categories);
  } elseif ($last_category->fields['parent_id'] == $current_category->fields['parent_id']) {
    array_pop($cPath_categories);
  }

  $cPath_new = implode('_', $cPath_categories) . '_' . $current_category_id;

  unset($cPath_categories);
  return 'cPath=' . trim($cPath_new, '_');
}



////
// Return true if the category has subcategories
// TABLES: categories
function zen_has_category_subcategories($category_id) {
  global $db;
  $child_category_query = "select count(*) as count
                           from " . TABLE_CATEGORIES . "
                           where parent_id = " . (int)$category_id;

  $child_category = $db->Execute($child_category_query);

  if ($child_category->fields['count'] > 0) {
    return true;
  } else {
    return false;
  }
}

////
function zen_get_categories($categories_array = array(), $parent_id = '0', $indent = '', $status_setting = '')
{
  global $db;

  if (!is_array($categories_array)) {
    $categories_array = array();
  }

  // show based on status
  if ($status_setting != '') {
    $zc_status = " c.categories_status=" . (int)$status_setting . " AND ";
  } else {
    $zc_status = '';
  }
  $categories_query = "SELECT c.categories_id, cd.categories_name, c.categories_status, c.sort_order
                         FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                         WHERE " . $zc_status . "
                         parent_id = " . (int)$parent_id . "
                         AND c.categories_id = cd.categories_id
                         AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                         ORDER BY c.sort_order, cd.categories_name";
  $results = $db->Execute($categories_query);

  foreach ($results as $result) {
    $categories_array[] = [
        'id' => $result['categories_id'],
        'text' => $indent . $result['categories_name'],
    ];
    if ($result['categories_id'] != $parent_id) {
      $status_setting = IS_ADMIN_FLAG ? $status_setting : '1';
      $categories_array = zen_get_categories($categories_array, $result['categories_id'], $indent . '&nbsp;&nbsp;', $status_setting);
    }
  }
  return $categories_array;
}


////
// Recursively go through the categories and retreive all parent categories IDs
// TABLES: categories
function zen_get_parent_categories(&$categories, $categories_id) {
  global $db;
  $parent_categories_query = "select parent_id
                              from " . TABLE_CATEGORIES . "
                              where categories_id = " . (int)$categories_id;

  $parent_categories = $db->Execute($parent_categories_query);

  while (!$parent_categories->EOF) {
    if ($parent_categories->fields['parent_id'] == 0) return true;
    $categories[sizeof($categories)] = $parent_categories->fields['parent_id'];
    if ($parent_categories->fields['parent_id'] != $categories_id) {
      zen_get_parent_categories($categories, $parent_categories->fields['parent_id']);
    }
    $parent_categories->MoveNext();
  }
}

////
// Construct a category path to the product
// TABLES: products_to_categories
function zen_get_product_path($products_id) {
  global $db;
  $cPath = '';

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


////
// products with name, model and price pulldown
function zen_draw_products_pull_down($name, $parameters = '', $exclude = '', $show_id = false, $set_selected = false, $show_model = false, $show_current_category = false) {
  global $currencies, $db, $current_category_id, $prev_next_order;

  // $prev_next_order set by products_previous_next.php, if category navigation in use
  $order_by = $db->prepare_input(!empty($prev_next_order) ? $prev_next_order : ' ORDER BY products_name');

  if ($exclude == '') {
    $exclude = array();
  }

  $select_string = '<select name="' . $name . '"';

  if ($parameters) {
    $select_string .= ' ' . $parameters;
  }

  $select_string .= '>';

  if (IS_ADMIN_FLAG && $show_current_category) {
// only show $current_categories_id
      $products = $db->Execute("SELECT p.products_id, pd.products_name, p.products_sort_order, p.products_price, p.products_model, ptc.categories_id
                              FROM " . TABLE_PRODUCTS . " p
                              LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc ON ptc.products_id = p.products_id, " .
                              TABLE_PRODUCTS_DESCRIPTION . " pd
                              where p.products_id = pd.products_id
                              and pd.language_id = " . (int)$_SESSION['languages_id'] . "
                              and ptc.categories_id = " . (int)$current_category_id .
                              $order_by);
  } else {
      $products = $db->Execute("SELECT p.products_id, pd.products_name, p.products_sort_order, p.products_price, p.products_model
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                where p.products_id = pd.products_id
                                and pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                order by products_name");
  }

  while (!$products->EOF) {
    if (!in_array($products->fields['products_id'], $exclude)) {
      $display_price = zen_get_products_base_price($products->fields['products_id']);
      $select_string .= '<option value="' . $products->fields['products_id'] . '"';
      if (IS_ADMIN_FLAG && ($set_selected == $products->fields['products_id'])) $select_string .= ' SELECTED';
      $select_string .= '>' . $products->fields['products_name'] . ' (' . $currencies->format($display_price) . ')' . (IS_ADMIN_FLAG ? ($show_model ? ' [' . $products->fields['products_model'] . '] ' : '') . ($show_id ? ' - ID# ' . $products->fields['products_id'] : '') : '') . '</option>';
    }
    $products->MoveNext();
  }

  $select_string .= '</select>';

  return $select_string;
}

////
// product pulldown with attributes
function zen_draw_products_pull_down_attributes($name, $parameters = '', $exclude = '', $order_by = 'name', $filter_by_option_name = '') {
  global $db, $currencies;

  if ($exclude == '') {
      $exclude = [];
  }

  $select_string = '<select name="' . $name . '"';

  if ($parameters) {
      $select_string .= ' ' . $parameters;
  }

  $select_string .= '>';

  $new_fields = ', p.products_model';

  switch ($order_by) {
      case ('model'):
          $order_by = 'p.products_model';
          $output_string = '<option value="%1$u"> %3$s - %2$s (%4$s)</option>'; // format string with model first
          break;
      default:
          $order_by = 'pd.products_name';
          $output_string = '<option value="%1$u">%2$s (%3$s) (%4$s)</option>';// format string with name first
          break;
  }

  switch (true) {
      case ($filter_by_option_name === -1): // no selection made: do not list any products
          // no selection made yet
          break;
      case ((int)$filter_by_option_name > 0) : // an Option Name was selected: show only products using attributes with this Option Name
          $products = $db->Execute("SELECT distinct p.products_id, pd.products_name, p.products_price, pa.options_id" . $new_fields .
              " FROM " . TABLE_PRODUCTS . " p, " .
              TABLE_PRODUCTS_DESCRIPTION . " pd, " .
              TABLE_PRODUCTS_ATTRIBUTES . " pa " . " 
          WHERE p.products_id= pa.products_id 
          AND p.products_id = pd.products_id 
          AND pd.language_id = " . (int)$_SESSION['languages_id'] . " 
          AND pa.options_id = " . (int)$filter_by_option_name . " 
          ORDER BY " . $order_by);
          break;
      default: //legacy: show all products with attributes
          $products = $db->Execute("SELECT distinct p.products_id, pd.products_name, p.products_price" . $new_fields .
              " FROM " . TABLE_PRODUCTS . " p, " .
              TABLE_PRODUCTS_DESCRIPTION . " pd, " .
              TABLE_PRODUCTS_ATTRIBUTES . " pa " . " 
          WHERE p.products_id = pa.products_id 
          AND p.products_id = pd.products_id 
          AND pd.language_id = " . (int)$_SESSION['languages_id'] . "    
          ORDER BY " . $order_by);
          break;
  }

  if (isset($products) && is_object($products)) {
      foreach ($products as $product) {
          if (!in_array($product['products_id'], $exclude, false)) {
              $display_price = zen_get_products_base_price($product['products_id']);
              $select_string .= sprintf($output_string, $product['products_id'], $product['products_name'], $product['products_model'], $currencies->format($display_price));
          }
      }
  }
  $select_string .= '</select>';

  return $select_string;
}


////
// categories pulldown with products
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

  $categories = $db->Execute("select distinct c.categories_id, cd.categories_name " ."
                              from " . TABLE_CATEGORIES . " c, " .
                                       TABLE_CATEGORIES_DESCRIPTION . " cd, " .
                                       TABLE_PRODUCTS_TO_CATEGORIES . " ptoc " ."
                              where ptoc.categories_id = c.categories_id
                              and c.categories_id = cd.categories_id
                              and cd.language_id = " . (int)$_SESSION['languages_id'] . "
                              order by categories_name");

  while (!$categories->EOF) {
    if (!in_array($categories->fields['categories_id'], $exclude)) {
      $parent = '';
      if (IS_ADMIN_FLAG && $show_parent == true) {
        $parent = zen_get_products_master_categories_name($categories->fields['categories_id']);
        if ($parent != '') {
          $parent = ' : in ' . $parent;
        }
      }
      $select_string .= '<option value="' . $categories->fields['categories_id'] . '">' . $categories->fields['categories_name'] . $parent . (IS_ADMIN_FLAG && $show_id ? ' - ID#' . $categories->fields['categories_id'] : '') . '</option>';
    }
    $categories->MoveNext();
  }

  $select_string .= '</select>';

  return $select_string;
}

////
// categories pulldown with products with attributes
function zen_draw_products_pull_down_categories_attributes($name, $parameters = '', $exclude = '', $show_full_path = false, $filter_by_option_name = null) {
  global $db, $currencies;

  if ($exclude == '') {
      $exclude = [];
  }

  $select_string = '<select name="' . $name . '"';

  if ($parameters) {
      $select_string .= ' ' . $parameters;
  }

  $select_string .= '>';

  switch (true) {
      case (IS_ADMIN_FLAG && $filter_by_option_name === ''): // no selection made: do not list any categories
          // no selection made yet
          break;
      case (IS_ADMIN_FLAG && $filter_by_option_name > 0) : // an Option Name was selected: show only categories with products using attributes with this Option Name
          $categories = $db->Execute("SELECT DISTINCT c.categories_id, cd.categories_name " .
              " FROM " . TABLE_CATEGORIES . " c, " .
              TABLE_CATEGORIES_DESCRIPTION . " cd, " .
              TABLE_PRODUCTS_TO_CATEGORIES . " ptoc, " .
              TABLE_PRODUCTS_ATTRIBUTES . " pa " . " 
              WHERE pa.products_id= ptoc.products_id 
              AND ptoc.categories_id= c.categories_id 
              AND c.categories_id = cd.categories_id 
              AND cd.language_id = " . (int)$_SESSION['languages_id'] . " 
              AND pa.options_id =" . (int)$filter_by_option_name . " 
              ORDER BY categories_name");
          break;
      default: //legacy: show all categories with products with attributes
          $categories = $db->Execute("SELECT DISTINCT c.categories_id, cd.categories_name " .
              " FROM " . TABLE_CATEGORIES . " c, " .
              TABLE_CATEGORIES_DESCRIPTION . " cd, " .
              TABLE_PRODUCTS_TO_CATEGORIES . " ptoc, " .
              TABLE_PRODUCTS_ATTRIBUTES . " pa " . " 
              WHERE pa.products_id= ptoc.products_id 
              AND ptoc.categories_id= c.categories_id 
              AND c.categories_id = cd.categories_id 
              AND cd.language_id = " . (int)$_SESSION['languages_id'] . " 
              ORDER BY categories_name");
          break;
  }
  if (isset($categories) && is_object($categories)) {
      foreach ($categories as $category) {
          if (!in_array($category['categories_id'], $exclude, false)) {
              $select_string .= '<option value="' . $category['categories_id'] . '">';
              if (IS_ADMIN_FLAG && $show_full_path) {
                  $select_string .= zen_output_generated_category_path($category['categories_id']);
              } else {
                  $select_string .= $category['categories_name'];
              }
              $select_string .= '</option>';
          }
      }
  }
  $select_string .= '</select>';

  return $select_string;
}

////
// Get all products_id in a Category and its SubCategories
// use as:
// $my_products_id_list = array();
// $my_products_id_list = zen_get_categories_products_list($categories_id)
function zen_get_categories_products_list($categories_id, $include_deactivated = false, $include_child = true, $parent_category = '0', $display_limit = '') {
  global $db;
  global $categories_products_id_list;

  if (!isset($categories_products_id_list) || !is_array($categories_products_id_list)) {
    $categories_products_id_list = array();
  }

  $childCatID = str_replace('_', '', substr($categories_id, strrpos($categories_id, '_')));

  $current_cPath = ($parent_category != '0' ? $parent_category . '_' : '') . $categories_id;

  $sql = "select p.products_id
          from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
          where p.products_id = p2c.products_id
          and p2c.categories_id = " . (int)$childCatID .
          ((IS_ADMIN_FLAG ? !$include_deactivated : $include_deactivated) ? " and p.products_status = 1" : "") .
          $display_limit;

  $products = $db->Execute($sql);
  while (!$products->EOF) {
    if (IS_ADMIN_FLAG) {  
      $categories_products_id_list[] = $products->fields['products_id'];
    } else {
      $categories_products_id_list[$products->fields['products_id']] = $current_cPath;
    }
    $products->MoveNext();
  }

  if ($include_child) {
    $sql = "select categories_id from " . TABLE_CATEGORIES . "
            where parent_id = '" . (int)$childCatID . "'";

    $childs = $db->Execute($sql);
    if ($childs->RecordCount() > 0 ) {
      if (IS_ADMIN_FLAG) {
        $current_cPath = '0';
        $include_child = true;
      }
      while (!$childs->EOF) {
        zen_get_categories_products_list($childs->fields['categories_id'], $include_deactivated, $include_child, $current_cPath, $display_limit);
        $childs->MoveNext();
      }
    }
  }
  return $categories_products_id_list;
}

//// bof: manage master_categories_id vs cPath
function zen_generate_category_path($id, $from = 'category', $categories_array = array(), $index = 0) {
  global $db;

  if (!is_array($categories_array)) $categories_array = array();

  if ($from == 'product') {
    $categories = $db->Execute("select categories_id
                                from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                where products_id = " . (int)$id);

    while (!$categories->EOF) {
      if ($categories->fields['categories_id'] == '0') {
        $categories_array[$index][] = array('id' => '0', 'text' => TEXT_TOP);
      } else {
        $category = $db->Execute("select cd.categories_name, c.parent_id
                                  from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                  where c.categories_id = " . (int)$categories->fields['categories_id'] . "
                                  and c.categories_id = cd.categories_id
                                  and cd.language_id = " . (int)$_SESSION['languages_id']);

        $categories_array[$index][] = array('id' => $categories->fields['categories_id'], 'text' => $category->fields['categories_name']);
        if ( (zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0') ) $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
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
    if (!IS_ADMIN_FLAG || !$category->EOF) {
      $categories_array[$index][] = array('id' => $id, 'text' => $category->fields['categories_name']);
      if ( (zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0') ) $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
    }
  }

  return $categories_array;
}

function zen_output_generated_category_path($id, $from = 'category') {
  $calculated_category_path_string = '';
  $calculated_category_path = zen_generate_category_path($id, $from);
  for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
    for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
//      $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
      if (!IS_ADMIN_FLAG || $from == 'category') {
        $calculated_category_path_string = $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;' . $calculated_category_path_string;
      } else {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'];
        $calculated_category_path_string .= ' [ ' . TEXT_INFO_ID . $calculated_category_path[$i][$j]['id'] . ' ] ';
        $calculated_category_path_string .= '<br>';
        $calculated_category_path_string .= '&nbsp;&nbsp;';
//           $calculated_category_path_string .= '&nbsp;&gt;&nbsp;';
      }
    }
    if (!IS_ADMIN_FLAG) {
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . '<br>';
    } elseif ($from == 'product') {
      $calculated_category_path_string .= '<br>';
    }
  }
  if (!IS_ADMIN_FLAG) {
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -4);
    } else {
      $calculated_category_path_string = preg_replace('/&nbsp;&gt;&nbsp;$/', '', $calculated_category_path_string);
    }

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
      if (!IS_ADMIN_FLAG) {
        $calculated_category_path_string = substr($calculated_category_path_string, 0, -1) . '<br>';
      } else {
        $calculated_category_path_string = rtrim($calculated_category_path_string, '_') . '<br>';
      }
    }
    if (!IS_ADMIN_FLAG) {
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -4);
    } else {
      $calculated_category_path_string = preg_replace('/<br>$/', '', $calculated_category_path_string);
    }

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


