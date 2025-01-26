<?php
/**
 * functions_categories.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Nov 04 Modified in v2.1.0 $
 */

/**
 * Generate a cPath string from current category conditions
 * @param int $current_category_id
 * @return string
 */
function zen_get_path($current_category_id = null)
{
    global $cPath_array, $db;

    if ($current_category_id === null || empty($cPath_array) || $current_category_id === '') { // empty string check is deprecated in 1.5.8
        return 'cPath=' . (!empty($cPath_array) ? implode('_', $cPath_array) : $current_category_id);
    }

    // make copy so we can manipulate it later
    $cPath_categories = $cPath_array;

    $last_category_query = "SELECT parent_id
                            FROM " . TABLE_CATEGORIES . "
                            WHERE categories_id = " . (int)$cPath_categories[count($cPath_categories) - 1];
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

    $cPath_categories[] = $current_category_id;
    $cPath_new = implode('_', $cPath_categories);

    return 'cPath=' . trim($cPath_new, '_');
}


/**
 * Return the number of products in a category
 * @param int $category_id
 * @param bool $include_inactive
 * @return int|mixed
 */
function zen_count_products_in_category($category_id, $include_inactive = false)
{
//  Check if only want to count distinct products in a category
    $distinct = defined('COUNT_DISTINCT_PRODUCTS') ? COUNT_DISTINCT_PRODUCTS : false;
    if ($distinct === true) {
        return zen_count_distinct_products_in_category($category_id, $include_inactive);
    }

    global $db;
    $products_count = 0;

    $sql = "SELECT count(*) as total
            FROM " . TABLE_PRODUCTS . " p
            LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c USING (products_id)
            WHERE p2c.categories_id = " . (int)$category_id;

    if (!$include_inactive) {
        $sql .= " AND p.products_status = 1";

    }
    $products = $db->Execute($sql);
    $products_count += $products->fields['total'];

    $sql = "SELECT categories_id
            FROM " . TABLE_CATEGORIES . "
            WHERE parent_id = " . (int)$category_id;

    $child_categories = $db->Execute($sql);

    foreach ($child_categories as $result) {
        $products_count += zen_count_products_in_category($result['categories_id'], $include_inactive);
    }

    return $products_count;
}

/**
 * Return the count of distinct products in a category and its sub categories
 */
function zen_count_distinct_products_in_category($category_id, $include_inactive = false)
{
    global $db;
    $products_count = 0;
    $subcategories_array[] = $category_id;
    zen_get_subcategories($subcategories_array, $category_id);
    $category_list = str_replace(['[',']'], ['(',')'], json_encode($subcategories_array));
    $sql = "SELECT count(DISTINCT p.products_id) as total " .
        "FROM " . TABLE_PRODUCTS . " p " .
        "LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c USING (products_id) " .
        "WHERE p2c.categories_id in " . $category_list;
     if (!$include_inactive) {
        $sql .= " AND p.products_status = 1";
    }
    $products = $db->Execute($sql);
    $products_count += $products->fields['total'];
    return $products_count;
}

/**
 * Return true if the category has subcategories
 * @param int $category_id
 * @return bool
 */
function zen_has_category_subcategories($category_id)
{
    global $db;
    $sql = "SELECT count(*) as count
            FROM " . TABLE_CATEGORIES . "
            WHERE parent_id = " . (int)$category_id;

    $result = $db->Execute($sql);

    return ($result->RecordCount() && $result->fields['count'] > 0);
}

/**
 * Get categories array suitable for pulldown
 * @param array $categories_array
 * @param int $parent_id
 * @param string $indent
 * @param int $status_flag
 * @return array
 */
function zen_get_categories($categories_array = array(), $parent_id = TOPMOST_CATEGORY_PARENT_ID, $indent = '', $status_flag = null)
{
    global $db;

    if (!is_array($categories_array)) {
        $categories_array = array();
    }

    // filter on status if requested
    $status_filter = '';
    if ($status_flag !== null) {
        $status_filter = " AND c.categories_status=" . (int)$status_flag;
    }

    $categories_query = "SELECT c.categories_id, cd.categories_name, c.categories_status, c.sort_order
                         FROM " . TABLE_CATEGORIES . " c
                         LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id'] . ")
                         WHERE parent_id = " . (int)$parent_id . "
                         " . $status_filter . "
                         ORDER BY c.sort_order, cd.categories_name";
    $results = $db->Execute($categories_query);

    foreach ($results as $result) {
        if ($status_flag !== null) {
            $count = zen_products_in_category_count($result['categories_id']);
            if ($count === 0) {
                continue;
            }
        }
        $categories_array[] = [
            'id' => $result['categories_id'],
            'text' => $indent . $result['categories_name'],
        ];
        if ($result['categories_id'] != (int)$parent_id) {
            $categories_array = zen_get_categories($categories_array, $result['categories_id'], $indent . '&nbsp;&nbsp;', $status_flag);
        }
    }
    return $categories_array;
}

/**
 * Return all subcategory IDs
 * @param array $subcategories_array recursive
 * @param int $parent_id
 */
function zen_get_subcategories(&$subcategories_array, $parent_id = TOPMOST_CATEGORY_PARENT_ID)
{
    global $db;
    $subcategories_query = "SELECT categories_id
                            FROM " . TABLE_CATEGORIES . "
                            WHERE parent_id = " . (int)$parent_id;

    $subcategories = $db->Execute($subcategories_query);

    foreach ($subcategories as $result) {
        $subcategories_array[count($subcategories_array)] = $result['categories_id'];
        if ($result['categories_id'] != $parent_id) {
            zen_get_subcategories($subcategories_array, $result['categories_id']);
        }
    }
}


/**
 * Recursively go through the categories and retreive all parent categories IDs
 * @param array $categories passed by reference
 * @param int $category_id
 * @return bool
 */
function zen_get_parent_categories(&$categories, $category_id)
{
    global $db;
    $sql = "SELECT parent_id
            FROM " . TABLE_CATEGORIES . "
            WHERE categories_id = " . (int)$category_id;

    $results = $db->Execute($sql);

    foreach ($results as $result) {

        if ($result['parent_id'] == TOPMOST_CATEGORY_PARENT_ID) return true;

        $categories[count($categories)] = $result['parent_id'];
        if ($result['parent_id'] != $category_id) {
            zen_get_parent_categories($categories, $result['parent_id']);
        }
    }
}

/**
 * Construct a category path to the product
 * @param int $product_id
 * @return string
 */
function zen_get_product_path($product_id)
{
    global $db;
    $cPath = '';

    $category_query = "SELECT p.products_id, p.master_categories_id
                       FROM " . TABLE_PRODUCTS . " p
                       WHERE p.products_id = " . (int)$product_id;

    $category = $db->Execute($category_query, 1);

    if ($category->RecordCount()) {
        $categories = [];
        zen_get_parent_categories($categories, $category->fields['master_categories_id']);

        $categories = array_reverse($categories);

        $categories[] = $category->fields['master_categories_id'];

        $cPath = implode('_', $categories);
    }

    return $cPath;
}

/**
 * Parse and sanitize the cPath parameter values
 * @param string $cPath
 * @return array
 */
function zen_parse_category_path($cPath)
{
    // make sure the category IDs are integers
    $cPath_array = array_map(function($value) {return (int)trim($value);}, explode('_', $cPath));

    // make sure no duplicate category IDs exist which could lock us into a loop
    $tmp_array = [];
    foreach ($cPath_array as $value) {
        if (!in_array($value, $tmp_array)) {
            $tmp_array[] = $value;
        }
    }

    return $tmp_array;
}

/**
 * Determine whether the product_id is associated with the category
 * @param int $product_id
 * @param int $cat_id
 * @return bool
 */
function zen_product_in_category($product_id, $cat_id)
{
    global $db;
    $in_cat = false;
    $sql = "SELECT categories_id
            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE products_id = " . (int)$product_id;
    $categories = $db->Execute($sql);

    foreach ($categories as $category) {
        if ($category['categories_id'] == $cat_id) {
            return true;
        }
        $sql = "SELECT parent_id
                    FROM " . TABLE_CATEGORIES . "
                    WHERE categories_id = " . (int)$category['categories_id'];

        $parent_categories = $db->Execute($sql);

        foreach ($parent_categories as $parent) {
            if ($parent['parent_id'] != TOPMOST_CATEGORY_PARENT_ID) {
                if (!$in_cat) {
                    $in_cat = zen_product_in_parent_category($product_id, $cat_id, $parent['parent_id']);
                }
                if ($in_cat) {
                    return $in_cat;
                }
            }
        }
    }
    return $in_cat;
}

/**
 * @param int $product_id
 * @param int $cat_id
 * @param int $parent_cat_id
 * @return bool
 */
function zen_product_in_parent_category($product_id, $cat_id, $parent_cat_id)
{
    global $db;

    $in_cat = false;
    if ($cat_id == $parent_cat_id) {
        return true;
    }
    $sql = "SELECT parent_id
                FROM " . TABLE_CATEGORIES . "
                WHERE categories_id = " . (int)$parent_cat_id;

    $results = $db->Execute($sql);

    foreach ($results as $result) {
        if ($result['parent_id'] != TOPMOST_CATEGORY_PARENT_ID && !$in_cat) {
            $in_cat = zen_product_in_parent_category($product_id, $cat_id, $result['parent_id']);
        }
        if ($in_cat) {
            return $in_cat;
        }
    }
    return $in_cat;
}


/**
 * pulldown menu for products, containing name, model and price
 * @param string $field_name
 * @param string $parameters
 * @param array $exclude array of ids to exclude
 * @param bool $show_id include ID #
 * @param int $set_selected default product id to be selected
 * @param bool $show_model
 * @param bool $show_current_category
 * @return string
 */
function zen_draw_pulldown_products($field_name, $parameters = '', $exclude = [], $show_id = false, $set_selected = 0, $show_model = false, $show_current_category = false, $order_by = '', $filter_by_option_name = null, bool $includeAttributes = false)
{
    global $current_category_id;

    $only_active = false;

    if (!is_array($exclude)) {
        $exclude = [];
    }

    if (empty($order_by)) {
        $order_by = str_replace(['pd.', 'p.'], '', zen_products_sort_order(false));
    }

    $sort_array = array_map('trim', array_filter(explode(',', str_ireplace('order by ', '', $order_by))));

    $pulldown = new productPulldown();

    if ($show_current_category) {
        $pulldown->setCategory($current_category_id);
    }

    $pulldown->includeAttributes($includeAttributes);

    if ((int) $filter_by_option_name > 0) {
        $pulldown->setOptionFilter((int) $filter_by_option_name);
    }

    $pulldown->setSort($sort_array)->exclude($exclude)->showModel($show_model)->setDefault((int)$set_selected)->onlyActive($only_active)->showID($show_id);

    return $pulldown->generatePulldownHtml($field_name, $parameters, false);
}


/**
 * pulldown for products that have attributes
 * @param string $field_name
 * @param string $parameters
 * @param array $exclude to exclude
 * @param string $order_by model|name
 * @param int $filter_by_option_name -1|0|option_name_id
 * @return string
 */
function zen_draw_pulldown_products_having_attributes($field_name, $parameters = '', $exclude = [], $order_by = 'name', $filter_by_option_name = null)
{

    if ($order_by == 'model') {
        $order_by = 'products_model';
    } else {
        $order_by = 'products_name';
    }

    return zen_draw_pulldown_products($field_name, $parameters, $exclude, false, 0, true, false, $order_by, $filter_by_option_name, true);
}

/**
 * categories pulldown for categories that have products
 * @param string $field_name
 * @param string $parameters
 * @param array $exclude to exclude
 * @param bool $show_id include ID #
 * @param bool $show_parent
 * @return string
 */
function zen_draw_pulldown_categories_having_products($field_name, $parameters = '', $exclude = [], $show_id = false, $show_parent = false, $show_full_path = false, $filter_by_option_name = null, bool $includeAttributes = false)
{
    if (!is_array($exclude)) {
        $exclude = [];
    }

    $pulldown = new categoryPulldown();

    $pulldown->showID($show_id)->showParent($show_parent)->showFullPath($show_full_path)->exclude($exclude)->includeAttributes($includeAttributes || $show_full_path);

    if ((int) $filter_by_option_name > 0) {
        $pulldown->setOptionFilter((int) $filter_by_option_name);
    }

    return $pulldown->generatePulldownHtml($field_name, $parameters, false);
}

/**
 * categories pulldown for categories having products with attributes
 * @param string $field_name
 * @param string $parameters
 * @param array $exclude
 * @param bool $show_full_path
 * @param string|null $filter_by_option_name
 * @return string
 */
function zen_draw_pulldown_categories_having_products_with_attributes($field_name, $parameters = '', $exclude = [], $show_full_path = false, $filter_by_option_name = null)
{
    return zen_draw_pulldown_categories_having_products($field_name, $parameters , $exclude, false, false, $show_full_path, $filter_by_option_name, true);

}

/**
 * look up the product_type that a category has been restricted to
 * @param int|string $lookup
 * @return bool|mixed false if not restricted; product_type_id if restricted
 */
function zen_get_product_types_to_category($lookup)
{
    global $db;

    $lookup = str_replace('cPath=', '', $lookup);

    $sql = "SELECT product_type_id
            FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
            WHERE category_id=" . (int)$lookup;
    $result = $db->Execute($sql, 1);

    if ($result->RecordCount()) {
        return $result->fields['product_type_id'];
    }

    return false;
}

/**
 * look up parent category's name
 * @param int $categories_id
 * @return string name of parent category, or blank if none
 */
function zen_get_categories_parent_name($categories_id)
{
    global $db;

    $sql = "SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE categories_id='" . (int)$categories_id . "'";
    $result = $db->Execute($sql, 1);
    if ($result->EOF) return '';

    $sql = "SELECT categories_name FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE categories_id=" . (int)$result->fields['parent_id'] . " AND language_id= " . $_SESSION['languages_id'];
    $result = $db->Execute($sql, 1);

    return $result->EOF ? '' : $result->fields['categories_name'];
}

/**
 * Get all products_id in a Category and its SubCategories
 * use as:
 * $my_products_id_list = array();
 * $my_products_id_list = zen_get_categories_products_list($categories_id)
 * @param int|string $categories_id (may be a cPath)
 * @param bool $include_deactivated
 * @param bool $include_child
 * @param string $parent_category
 * @param string $display_limit
 * @return array|null
 */
function zen_get_categories_products_list($categories_id, $include_deactivated = false, $include_child = true, $parent_category = TOPMOST_CATEGORY_PARENT_ID, $display_limit = '')
{
    global $db;
    global $categories_products_id_list;
    $categories_id = (string)$categories_id;

    if (!empty($display_limit)) {
        $display_limit = $db->prepare_input($display_limit);
    }

    if (!isset($categories_products_id_list) || !is_array($categories_products_id_list)) {
        $categories_products_id_list = array();
    }

    $childCatID = str_replace('_', '', substr($categories_id, strrpos($categories_id, '_')));

    $current_cPath = ($parent_category != TOPMOST_CATEGORY_PARENT_ID ? $parent_category . '_' : '') . $categories_id;

    $sql = "SELECT p.products_id
            FROM " . TABLE_PRODUCTS . " p
            LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c USING (products_id)
            WHERE p2c.categories_id = " . (int)$childCatID .
        (!$include_deactivated ? " AND p.products_status = 1" : '') .
        $display_limit;

    $results = $db->Execute($sql);
    foreach ($results as $result) {
        $categories_products_id_list[$result['products_id']] = $current_cPath;
    }

    if ($include_child) {
        $sql = "SELECT categories_id
                FROM " . TABLE_CATEGORIES . "
                WHERE parent_id = " . (int)$childCatID;

        $results = $db->Execute($sql);
        foreach ($results as $result) {
            zen_get_categories_products_list($result['categories_id'], $include_deactivated, $include_child, $current_cPath, $display_limit);
        }
    }
    return $categories_products_id_list;
}

/**
 * @param int $id product_id or category_id
 * @param string $from category|product
 * @param array $categories_array
 * @param int $index
 * @return array|mixed
 */
function zen_generate_category_path($id, $from = 'category', $categories_array = [], $index = 0)
{
    global $db;

    if (!is_array($categories_array)) $categories_array = [];

    if ($from == 'product') {
        $sql = "SELECT categories_id
                FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                WHERE products_id = " . (int)$id;
        $categories = $db->Execute($sql);

        foreach ($categories as $p2cResult) {
            if ($p2cResult['categories_id'] == TOPMOST_CATEGORY_PARENT_ID) {
                $categories_array[$index][] = ['id' => TOPMOST_CATEGORY_PARENT_ID, 'text' => TEXT_TOP];
            } else {
                $sql = "SELECT cd.categories_name, c.parent_id
                        FROM " . TABLE_CATEGORIES . " c
                        LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id'] . ")
                        WHERE c.categories_id = " . (int)$p2cResult['categories_id'];
                $category = $db->Execute($sql);

                $categories_array[$index][] = [
                    'id' => $p2cResult['categories_id'],
                    'text' => $category->fields['categories_name'],
                    ];

                if (zen_not_null($category->fields['parent_id']) && $category->fields['parent_id'] != TOPMOST_CATEGORY_PARENT_ID) {
                    $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
                }
                $categories_array[$index] = array_reverse($categories_array[$index]);
            }
            $index++;
        }
    } elseif ($from == 'category') {
        $sql = "SELECT cd.categories_name, c.parent_id
                FROM " . TABLE_CATEGORIES . " c
                LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id'] . ")
                WHERE c.categories_id = " . (int)$id;
        $category = $db->Execute($sql);

        if (!$category->EOF) {
            $categories_array[$index][] = [
                'id' => $id,
                'text' => $category->fields['categories_name'],
            ];
            if (zen_not_null($category->fields['parent_id']) && $category->fields['parent_id'] != TOPMOST_CATEGORY_PARENT_ID) {
                $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
            }
        }
    }

    return $categories_array;
}

/**
 * @param int $category_id
 * @param string $from 'category'|'product'
 * @return string|string[]|null
 */
function zen_output_generated_category_path($category_id, $from = 'category')
{
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($category_id, $from);

    foreach ($calculated_category_path as $outerKey => $outerValue) {
        foreach ($outerValue as $innerKey => $innerValue) {
            if ($from == 'category') {
                $calculated_category_path_string = $innerValue['text'] . '&nbsp;&gt;&nbsp;' . $calculated_category_path_string;
            } else {
                $calculated_category_path_string .= $calculated_category_path[$outerKey][$innerKey]['text'];
                $calculated_category_path_string .= ' [ ' . TEXT_INFO_ID . $innerValue['id'] . ' ] ';
                $calculated_category_path_string .= '<br>';
                $calculated_category_path_string .= '&nbsp;&nbsp;';
//           $calculated_category_path_string .= '&nbsp;&gt;&nbsp;';
            }
        }
        if ($from == 'product') {
            $calculated_category_path_string .= '<br>';
        }
    }
    $calculated_category_path_string = preg_replace('/&nbsp;(&gt;)?&nbsp;$/', '', $calculated_category_path_string);
    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
}

function zen_get_generated_category_path_ids($id, $from = 'category')
{
    global $db;
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($id, $from);
    foreach ($calculated_category_path as $outerValue) {
        foreach ($outerValue as $innerValue) {
            $calculated_category_path_string .= $innerValue['id'] . '_';
        }
        $calculated_category_path_string = rtrim($calculated_category_path_string, '_') . '<br>';
    }
    $calculated_category_path_string = preg_replace('~<br ?/?>$~', '', $calculated_category_path_string);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
}

/**
 * @param int $this_categories_id
 * @return string
 */
function zen_get_generated_category_path_rev($this_categories_id)
{
    $categories = [];
    zen_get_parent_categories($categories, $this_categories_id);

    $categories = array_reverse($categories);
    $categories[] = $this_categories_id;

    return implode('_', $categories);
}

/**
 * @param int $parent_id to start from
 * @param string $spacing characters
 * @param int $exclude category to exclude
 * @param array $category_tree_array
 * @param bool $include_itself
 * @param bool $check_if_cat_has_prods add a '*' markup if category has products in it
 * @param bool $limit
 * @return array
 */
function zen_get_category_tree($parent_id = TOPMOST_CATEGORY_PARENT_ID, $spacing = '', $exclude = '', $category_tree_array = [], $include_itself = false, $check_if_cat_has_prods = false, $limit = false)
{
    global $db;

    $limit_count = $limit ? " limit 1" : '';

    if (!is_array($category_tree_array)) $category_tree_array = [];

    // init pulldown with Top category if list is empty and top cat not marked as excluded
    if (count($category_tree_array) < 1 && $exclude != TOPMOST_CATEGORY_PARENT_ID) {
        $category_tree_array[] = ['id' => TOPMOST_CATEGORY_PARENT_ID, 'text' => TEXT_TOP];
    }

    if ($include_itself) {
        $sql = "SELECT cd.categories_name
                FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd
                WHERE cd.language_id = " . (int)$_SESSION['languages_id'] . "
                AND cd.categories_id = " . (int)$parent_id . "
                LIMIT 1";
        $results = $db->Execute($sql);
        if ($results->RecordCount()) {
            $category_tree_array[] = ['id' => $parent_id, 'text' => $results->fields['categories_name']];
        }
    }

    $sql = "SELECT c.categories_id, cd.categories_name, c.parent_id
            FROM " . TABLE_CATEGORIES . " c
            LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id'] . ")
            WHERE c.parent_id = " . (int)$parent_id . "
            ORDER BY c.sort_order, cd.categories_name";
    $results = $db->Execute($sql);
    foreach ($results as $result) {
        if ($check_if_cat_has_prods && zen_products_in_category_count($result['categories_id'], '', false, true) >= 1) {
            $mark = '*';
        } else {
            $mark = '&nbsp;&nbsp;';
        }
        if ($exclude != $result['categories_id']) {
            $category_tree_array[] = ['id' => $result['categories_id'], 'text' => $spacing . $result['categories_name'] . $mark];
        }
        $category_tree_array = zen_get_category_tree($result['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, false, $check_if_cat_has_prods);
    }

    return $category_tree_array;
}


/**
 * @TODO - replace these calls with a class call
 * @param int $category_id
 * @param int $language_id
 * @return string
 */
function zen_get_category_name($category_id, $language_id = null)
{
    global $db;
    if (empty($language_id)) {
        $language_id = (int)$_SESSION['languages_id'];
    }
    switch (true) {
        case ($category_id === null):
            return '';
        case ((int)($category_id) < 1):
            return TEXT_TOP;
        default:
            $category = $db->Execute(
                "SELECT categories_name
                              FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                              WHERE categories_id = " . (int)$category_id . "
                              AND language_id = " . (int)$language_id
            );
            if ($category->EOF) {
                return '';
            }
            return $category->fields['categories_name'];
    }
}


/**
 * Find category description, from category ID, in given language
 * @param int $category_id
 * @param int $language_id
 * @return string
 */
function zen_get_category_description($category_id, $language_id = null): string
{
    global $db, $zco_notifier;
    if (empty($language_id)) $language_id = (int)$_SESSION['languages_id'];
    $category = $db->Execute("SELECT categories_description
                              FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                              WHERE categories_id = " . (int)$category_id . "
                              AND language_id = " . (int)$language_id);
    if ($category->EOF) return '';
    $zco_notifier->notify('NOTIFY_GET_CATEGORY_DESCRIPTION', $category_id, $category->fields['categories_description']);
    return $category->fields['categories_description'];
}


/**
 * Return category's image
 * @param $category_id
 * @return string
 */
function zen_get_categories_image($category_id): string
{
    global $db;

    $sql = "SELECT categories_image FROM " . TABLE_CATEGORIES . " WHERE categories_id = " . (int)$category_id;
    $result = $db->Execute($sql, 1);

    if ($result->EOF) {
        return '';
    }

    return (string)$result->fields['categories_image'];
}

/**
 * @deprecated Alias of zen_get_category_name
 * @param int $category_id
 */
function zen_get_categories_name($category_id) {
    trigger_error('Call to deprecated function zen_get_categories_name. Use zen_get_category_name() instead', E_USER_DEPRECATED);

    return zen_get_category_name($category_id, null);
}


/**
 * Get the status of a category
 * @param int $categories_id
 * @return mixed|string
 */
function zen_get_categories_status($categories_id)
{
    global $db;
    $sql = "SELECT categories_status
            FROM " . TABLE_CATEGORIES .
            (!empty($categories_id) ? " WHERE categories_id=" . (int)$categories_id : "");
    $check_status = $db->Execute($sql);
    if ($check_status->EOF) return ''; // empty string means does not exist in zen_validate_categories()
    return $check_status->fields['categories_status'];
}

/**
 * validate the user-entered categories from the Global Tools
 * @param int $ref_category_id
 * @param int $target_category_id
 * @param bool $reset_master_category
 * @return bool
 */
function zen_validate_categories($ref_category_id, $target_category_id = 0, $reset_master_category = false)
{
    global $db, $messageStack;

    $categories_valid = true;
    if ($ref_category_id === '' || zen_get_categories_status($ref_category_id) === '') {//REF does not exist
        $categories_valid = false;
        $messageStack->add_session(sprintf(WARNING_CATEGORY_SOURCE_NOT_EXIST, (int)$ref_category_id), 'warning');
    }
    if (!$reset_master_category && ($target_category_id === '' || zen_get_categories_status($target_category_id) === '')) {//TARGET does not exist
        $categories_valid = false;
        $messageStack->add_session(sprintf(WARNING_CATEGORY_TARGET_NOT_EXIST, (int)$target_category_id), 'warning');
    }
    if (!$reset_master_category && ($categories_valid && $ref_category_id === $target_category_id)) {//category IDs are the same
        $categories_valid = false;
        $messageStack->add_session(sprintf(WARNING_CATEGORY_IDS_DUPLICATED, (int)$ref_category_id), 'warning');
    }

    if ($categories_valid) {
        $check_category_from = zen_get_linked_products_for_category((int)$ref_category_id);
        // check if REF has any products
        if (count($check_category_from) < 1) {//there are no products in the FROM category: invalid
            $categories_valid = false;
            $messageStack->add_session(sprintf(WARNING_CATEGORY_NO_PRODUCTS, (int)$ref_category_id), 'warning');
        }
        // check that TARGET has no subcategories
        if (!$reset_master_category && zen_childs_in_category_count($target_category_id) > 0) {//subcategories exist in the TO category: invalid
            $categories_valid = false;
            $messageStack->add_session(sprintf(WARNING_CATEGORY_SUBCATEGORIES, (int)$target_category_id), 'warning');
        }
    }
    return $categories_valid;
}

// the following two similar functions are a reduction from three similar functions...and can probably be further reduced/integrated with a revamped core function in the future, so have not been reduced here
/**
 * Updates a global variable, $categories_info, with a list of all the categories and subcategories
 * of the specified parent category. Code is organised so that the list is in ascending alphabetical
 * order, for the entire path of the category (not simply ordered by individual subcategory
 * names).
 *
 * @param int $parent_id The ID of the parent category.
 * @param string $category_path_string The full path of the names of all the parent categories being included in the path for the (sub)categories info being generated.
 * @return void
 */
function zen_get_categories_info($parent_id = 0, $category_path_string = '')
{
    global $db, $categories_info;

    $sql = "SELECT cd.categories_id, cd.categories_name
            FROM " . TABLE_CATEGORIES . " c
            LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id'] . ")
            WHERE c.parent_id = " . (int)$parent_id . "
            ORDER BY cd.categories_name";
    $results = $db->Execute($sql);
    foreach ($results as $result) {
        $category_id = $result['categories_id'];
        $category_name = ($category_path_string !== '' ? $category_path_string . ' > ' : '') . $result['categories_name'];
        // Does this category have subcategories?
        $sql = "SELECT c.categories_id FROM " . TABLE_CATEGORIES . " c WHERE c.parent_id = " . (int)$category_id;
        $subcategories = $db->Execute($sql);

        if ($subcategories->EOF) {
            // no subcategories
            $categories_info[] = [
                'categories_id' => $category_id,
                'categories_name' => $category_name,
            ];
        } else {
            // category has subcategories, get the info for them
            zen_get_categories_info((int)$category_id, $category_name);
        }
    }
}

/**
 * Builds a list of all the subcategories / subcategories: products of a specified parent category.
 *
 * @param int $parent_id The ID of the parent category.
 * @param string $spacing HTML to be prepended to the names of the categories/products for the specified parent category. Aids a hierarchical display of categories/products when information is used in a select gadget.
 * @param array $category_product_tree_array The array of categories and products being generated. Passed in function parameters so that it can be appended to when used recursively.
 * @param string $type category or product: to determine the array structure
 * @return array
 */
function zen_get_target_categories_products($parent_id = 0, $spacing = '', $category_product_tree_array = [], $type = 'category')
{
    global $db, $products_filter;
    $sql = "SELECT cd.categories_id, cd.categories_name, c.parent_id
            FROM " . TABLE_CATEGORIES . " c
            LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id'] . ")
            WHERE c.parent_id = " . (int)$parent_id . "
            ORDER BY cd.categories_name";
    $categories = $db->Execute($sql);
    foreach ($categories as $category) {
        // Get all subcategories for the current category
        $sql = "SELECT c.categories_id FROM " . TABLE_CATEGORIES . " c WHERE c.parent_id = " . (int)$category['categories_id'];
        $sub_categories_result = $db->Execute($sql);

        if (!$sub_categories_result->EOF) {
            if ($type === 'product') {
                $category_product_tree_array = zen_get_target_categories_products((int)$category['categories_id'], $spacing . $category['categories_name'] . ' > ', $category_product_tree_array, 'product');
            } else {//type is category
                $category_product_tree_array[] = [
                    'id' => $category['categories_id'],
                    'text' => $spacing . $category['categories_name'],
                ];
                $category_product_tree_array = zen_get_target_categories_products((int)$category['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $category_product_tree_array);
            }
        }
        if ($type === 'product') {
            $sql = "SELECT p.products_model, pd.products_id, pd.products_name
                    FROM " . TABLE_PRODUCTS . " p
                    LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id AND pd.language_id = " . (int)$_SESSION['languages_id'] . ")
                    WHERE p.master_categories_id = " . (int)$category['categories_id'] . "
                    ORDER BY p.products_model";

            $products = $db->Execute($sql);

            foreach ($products as $product) {
                if ((int)$product['products_id'] !== $products_filter) {
                    $category_product_tree_array[] = [
                        'id' => $product['products_id'],
                        'text' => $spacing .
                            htmlentities($category['categories_name'], ENT_COMPAT) . ': ' .
                            htmlentities($product['products_model'], ENT_COMPAT) . ' - ' .
                            htmlentities($product['products_name'], ENT_COMPAT) . ' (#' . $product['products_id'] . ')',
                    ];
                }
            }
        }
    }
    return $category_product_tree_array;
}

/**
 * Recursive algorithm to restrict all sub_categories of a specified category to a specified product_type
 * @param int $category_id
 * @param int $product_type_id
 */
function zen_restrict_sub_categories($category_id, $product_type_id) {
    global $db;
    $sql = "SELECT categories_id FROM " . TABLE_CATEGORIES . " WHERE parent_id = " . (int)$category_id;
    $results = $db->Execute($sql);
    foreach ($results as $result) {
        $sql = "SELECT * FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                         WHERE category_id = " . (int)$result['categories_id'] . "
                         AND product_type_id = " . (int)$product_type_id;

        $zq_type_to_cat = $db->Execute($sql);

        if ($zq_type_to_cat->RecordCount() < 1) {
            $za_insert_sql_data = [
                'category_id' => (int)$result['categories_id'],
                'product_type_id' => (int)$product_type_id,
            ];
            zen_db_perform(TABLE_PRODUCT_TYPES_TO_CATEGORY, $za_insert_sql_data);
        }
        zen_restrict_sub_categories($result['categories_id'], $product_type_id);
    }
}


/**
 * Recursive algorithm to UNDO restriction from all sub_categories of a specified category for a specified product_type
 * @param int $category_id
 * @param int $product_type_id
 */
function zen_remove_restrict_sub_categories($category_id, $product_type_id) {
    global $db;
    $sql = "SELECT categories_id FROM " . TABLE_CATEGORIES . " WHERE parent_id = " . (int)$category_id;
    $results = $db->Execute($sql);
    foreach($results as $result) {
        $sql = "DELETE FROM " .  TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                WHERE category_id = " . (int)$result['categories_id'] . "
                AND product_type_id = " . (int)$product_type_id;

        $db->Execute($sql);
        zen_remove_restrict_sub_categories($result['categories_id'], $product_type_id);
    }
}

/**
 * Get an array of product types that the category is restricted to
 * @param int $category_id
 * @return array
 */
function zen_get_category_restricted_product_types($category_id)
{
    global $db;
    $sql = "SELECT ptc.product_type_id as type_id, pt.type_name, pt.type_handler
             FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc
             LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON (pt.type_id = ptc.product_type_id)
             WHERE ptc.category_id = " . (int)$category_id;
    $results = $db->Execute($sql);

    $return = [];
    foreach($results as $result) {
        $return[] = $result;
    }
    return $return;
}

/**
 * @param int $category_id
 * @param int $status
 */
function zen_set_category_status($category_id, $status)
{
    global $db;
    $sql = "UPDATE " . TABLE_CATEGORIES . "
            SET categories_status = " . (int)$status . "
            WHERE categories_id = " . (int)$category_id;
    $db->Execute($sql);
}

/**
 * @param int $category_id
 * @param string $image_name
 */
function zen_set_category_image($category_id, $image_name = '')
{
    global $db;
    $sql = "UPDATE " . TABLE_CATEGORIES . "
            SET categories_image = :image_name
            WHERE categories_id = " . (int)$category_id;
    $sql = $db->bindVars($sql, ':image_name', $image_name, 'stringIgnoreNull');
    $db->Execute($sql);
}


/**
 * @deprecated 2.1.0 use Category class object instead
 * Return any field from categories or categories_description table
 * Example: zen_categories_lookup('10', 'parent_id');
 */
function zen_categories_lookup($categories_id, $what_field = 'categories_name', $language = '') {
    trigger_error('Call to deprecated function zen_categories_lookup. Use Category class object instead', E_USER_DEPRECATED);

    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $category_lookup = $db->Execute("select " . $what_field . " as lookup_field
                              from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                              where c.categories_id ='" . (int)$categories_id . "'
                              and c.categories_id = cd.categories_id
                              and cd.language_id = '" . (int)$language . "'");

    $return_field = $category_lookup->fields['lookup_field'];

    return $return_field;
}


/**
 * @param int $category_id
 */
function zen_remove_category($category_id)
{
    if ((int)$category_id == TOPMOST_CATEGORY_PARENT_ID) return;
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_CATEGORY', array(), $category_id);

    // delete from salemaker - sale_categories_selected
    $chk_sale_categories_selected = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . "
        WHERE
        sale_categories_selected = " . (int)$category_id . "
        OR sale_categories_selected LIKE '%," . (int)$category_id . ",%'
        OR sale_categories_selected LIKE '%," . (int)$category_id . "'
        OR sale_categories_selected LIKE '" . (int)$category_id . ",%'");

    // delete from salemaker - sale_categories_all
    $chk_sale_categories_all = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . "
        WHERE
        sale_categories_all = " . (int)$category_id . "
        OR sale_categories_all LIKE '%," . (int)$category_id . ",%'
        OR sale_categories_all LIKE '%," . (int)$category_id . "'
        OR sale_categories_all LIKE '" . (int)$category_id . ",%'");

//echo 'WORKING ON: ' . (int)$category_id . ' chk_sale_categories_selected: ' . $chk_sale_categories_selected->RecordCount() . ' chk_sale_categories_all: ' . $chk_sale_categories_all->RecordCount() . '<br>';
    while (!$chk_sale_categories_selected->EOF) {
        $skip_cats = false; // used when deleting
        $skip_sale_id = 0;
//echo '<br>FIRST LOOP: sale_id ' . $chk_sale_categories_selected->fields['sale_id'] . ' sale_categories_selected: ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
        // 9 or ,9 or 9,
        // delete record if sale_categories_selected = 9 and  sale_categories_all = ,9,
        if ($chk_sale_categories_selected->fields['sale_categories_selected'] == (int)$category_id and $chk_sale_categories_selected->fields['sale_categories_all'] == ',' . (int)$category_id . ',') { // delete record
//echo 'A: I should delete this record sale_id: ' . $chk_sale_categories_selected->fields['sale_id'] . '<br><br>';
            $skip_cats = true;
            $skip_sale_id = $chk_sale_categories_selected->fields['sale_id'];
            $salemakerdelete = "DELETE from " . TABLE_SALEMAKER_SALES . " WHERE sale_id="  . (int)$skip_sale_id;
        }

        // if in the front - remove 9,
        //  if ($chk_sale_categories_selected->fields['sale_categories_selected'] == (int)$category_id . ',') { // front
        if (!$skip_cats && (preg_match('/^' . (int)$category_id . ',/', $chk_sale_categories_selected->fields['sale_categories_selected'])) ) { // front
//echo 'B: I need to remove - ' . (int)$category_id . ', - from the front of ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
            $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], strlen((int)$category_id . ','));
//echo 'B: new_sale_categories_selected: ' . $new_sale_categories_selected . '<br><br>';
        }

        // if in the middle or end - remove ,9,
        if (!$skip_cats && (strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',')) ) { // middle or end
//echo 'C: I need to remove - ,' . (int)$category_id . ', - from the middle or end ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
            $start_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',') + strlen(',' . (int)$category_id . ',');
            $end_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',', $start_cat+strlen(',' . (int)$category_id . ','));
            $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1)) . substr($chk_sale_categories_selected->fields['sale_categories_selected'], $start_cat);
//echo 'C: new_sale_categories_selected: ' . $new_sale_categories_selected. '<br><br>';
            $skip_cat_last = true;
        }


// not needed in loop 1 if middle does end
        // if on the end - remove ,9 skip if middle cleaned it
        if (!$skip_cats && !$skip_cat_last && (strripos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id)) ) { // end
            $start_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id) + strlen(',' . (int)$category_id);
//echo 'D: I need to remove - ,' . (int)$category_id . ' - from the end ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
            $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1));
//echo 'D: new_sale_categories_selected: ' . $new_sale_categories_selected. '<br><br>';
        }

        if (!$skip_cats) {
            $salemakerupdate =
                "UPDATE " . TABLE_SALEMAKER_SALES . "
                 SET sale_categories_selected='" . $new_sale_categories_selected . "'
                 WHERE sale_id = " . (int)$chk_sale_categories_selected->fields['sale_id'];
//echo 'Update new_sale_categories_selected: ' . $salemakerupdate . '<br>';
            $db->Execute($salemakerupdate);
        } else {
//echo 'Record was deleted sale_id ' . $skip_sale_id . '<br>' . $salemakerdelete;
            $db->Execute($salemakerdelete);
        }

        $chk_sale_categories_selected->MoveNext();
    }

    while (!$chk_sale_categories_all->EOF) {
//echo '<br><br>SECOND LOOP: sale_id ' . $chk_sale_categories_all->fields['sale_id'] . ' sale_categories_all: ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br><br>';
        // remove ,9 if on front as ,9, - remove ,9 if in the middle as ,9, - remove ,9 if on the end as ,9,
        // beware of ,79, or ,98, or ,99, when cleaning 9
        // if ($chk_sale_categories_all->fields['sale_categories_all'] == ',9') { // front
        // if (something for the middle) { // middle
        // if (right($chk_sale_categories_all->fields['sale_categories_all']) == ',9,') { // end

        $skip_cats = false;
        if ($skip_sale_id == $chk_sale_categories_all->fields['sale_id']) { // was deleted
//echo 'A: I should delete this record sale_id: ' . $chk_sale_categories_all->fields['sale_id'] . ' but already done' . '<br><br>';
            $skip_cats = true;
        }

        // if in the front - remove 9,
        //  if ($chk_sale_categories_all->fields['sale_categories_all'] == (int)$category_id . ',') { // front
        if (!$skip_cats && (preg_match('/^' . ',' . (int)$category_id . ',/', $chk_sale_categories_all->fields['sale_categories_all'])) ) { // front
//echo 'B: I need to remove - ' . (int)$category_id . ', - from the front of ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
            $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], strlen(',' . (int)$category_id));
//echo 'B: new_sale_categories_all: ' . $new_sale_categories_all . '<br><br>';
        }

        // if in the middle or end - remove ,9,
        if (!$skip_cats && (strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',')) ) { // middle
//echo 'C: I need to remove - ,' . (int)$category_id . ', - from the middle or end ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
            $start_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',') + strlen(',' . (int)$category_id . ',');
            $end_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',', $start_cat+strlen(',' . (int)$category_id . ','));
            $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1)) . substr($chk_sale_categories_all->fields['sale_categories_all'], $start_cat);
//echo 'C: new_sale_categories_all: ' . $new_sale_categories_all. '<br><br>';
        }

        /*
        // not needed in loop 2
          // if on the end - remove ,9,
          if (!$skip_cats && (strripos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',')) ) { // end
            $start_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id) + strlen(',' . (int)$category_id . ',');
            echo 'D: I need to remove from the end - ,' . (int)$category_id . ', - from the end ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
            $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1));
            echo 'D: new_sale_categories_all: ' . $new_sale_categories_all. '<br><br>';
          }
        */
        if (!empty($new_sale_categories_all)) {
            $salemakerupdate = "UPDATE " . TABLE_SALEMAKER_SALES . " SET sale_categories_all='" . $new_sale_categories_all . "' WHERE sale_id = " . (int)$chk_sale_categories_all->fields['sale_id'];
            $db->Execute($salemakerupdate);
//echo 'Update sale_categories_all: ' . $salemakerupdate . '<br>';
        }

        $chk_sale_categories_all->MoveNext();
    }

//die('DONE TESTING');

    $category_image = $db->Execute("SELECT categories_image
                                    FROM " . TABLE_CATEGORIES . "
                                    WHERE categories_id = " . (int)$category_id);

    $duplicate_image = $db->Execute("SELECT count(*) as total
                                     FROM " . TABLE_CATEGORIES . "
                                     WHERE categories_image = '" . zen_db_input($category_image->fields['categories_image']) . "'");
    if ($duplicate_image->fields['total'] < 2) {
        if (file_exists(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image'])) {
            @unlink(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image']);
        }
    }

    $db->Execute("DELETE FROM " . TABLE_CATEGORIES . "
                  WHERE categories_id = " . (int)$category_id);

    $db->Execute("DELETE FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                  WHERE categories_id = " . (int)$category_id);

    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                  WHERE categories_id = " . (int)$category_id);

    $db->Execute("DELETE FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                  WHERE categories_id = " . (int)$category_id);

    $db->Execute("DELETE FROM " . TABLE_COUPON_RESTRICT . "
                  WHERE category_id = " . (int)$category_id);

    $db->Execute("DELETE FROM " . TABLE_FEATURED_CATEGORIES . "
                  WHERE categories_id = " . (int)$category_id);

    zen_record_admin_activity('Deleted category ' . (int)$category_id . ' from database via admin console.', 'warning');
}


/**
 * Count how many products exist in a category
 * @param int $category_id
 * @param bool $include_deactivated
 * @param bool $include_child
 * @param bool $limit
 * @return int
 */
function zen_products_in_category_count($category_id, $include_deactivated = false, $include_child = true, $limit = false) {
    global $db;
    $products_count = 0;

    $sql = "SELECT COUNT(*) AS total
                FROM " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c USING (products_id)
                WHERE p2c.categories_id = " . (int)$category_id;

    if (!$include_deactivated) {
        $sql .= " AND products_status = 1";
    }

    $products = $db->Execute($sql, ($limit ? 1 : false));

    $products_count += $products->fields['total'];

    if ($include_child) {
        $childs = $db->Execute("SELECT categories_id FROM " . TABLE_CATEGORIES . "
                                WHERE parent_id = " . (int)$category_id);
        if ($childs->RecordCount() > 0 ) {
            foreach ($childs as $child) {
                $products_count += zen_products_in_category_count($child['categories_id'], $include_deactivated);
            }
        }
    }
    return $products_count;
}


/**
 * Count how many subcategories exist in a category
 * @param int $category_id
 * @return int
 */
function zen_childs_in_category_count($category_id) {
    global $db;
    $categories_count = 0;

    $categories = $db->Execute("SELECT categories_id
                                FROM " . TABLE_CATEGORIES . "
                                WHERE parent_id = " . (int)$category_id);

    foreach ($categories as $result) {
        $categories_count++;
        $categories_count += zen_childs_in_category_count($result['categories_id']);
    }

    return $categories_count;
}


/**
 * get categories_name for product
 * @param int $product_id
 * @return string
 * @deprecated Use zen_get_product_details()
 * @TODO - delete from core in v2.2.0 or later
 */
function zen_get_categories_name_from_product($product_id) {
    trigger_error('Call to deprecated function zen_get_categories_name_from_product. Use zen_get_product_details() instead', E_USER_DEPRECATED);

    global $db;

    $check_products_category = $db->Execute("SELECT products_id, master_categories_id
                                             FROM " . TABLE_PRODUCTS . "
                                             WHERE products_id = " . (int)$product_id
    );
    if ($check_products_category->EOF) return '';
    $the_categories_name= $db->Execute("SELECT categories_name
                                        FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                                        WHERE categories_id= " . (int)$check_products_category->fields['master_categories_id'] . "
                                        AND language_id= " . (int)$_SESSION['languages_id']
    );
    if ($the_categories_name->EOF) return '';
    return $the_categories_name->fields['categories_name'];
}

/**
 * @TODO - is this even used?
 * @param int $category_id
 * @return array
 */
function zen_count_products_in_cats($category_id) {
    global $db;
    $c_array = [];
    $cat_products_query = "SELECT COUNT(IF (p.products_status=1,1,NULL)) AS pr_on, COUNT(*) AS total
                           FROM " . TABLE_PRODUCTS . " p
                           LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c USING (products_id)
                           WHERE p2c.categories_id = " . (int)$category_id;

    $pr_count = $db->Execute($cat_products_query);
//    echo $pr_count->RecordCount();
    $c_array['this_count'] += $pr_count->fields['total'];
    $c_array['this_count_on'] += $pr_count->fields['pr_on'];

    $child_categories_query = "SELECT categories_id
                               FROM " . TABLE_CATEGORIES . "
                               WHERE parent_id = " . (int)$category_id;

    $results = $db->Execute($child_categories_query);

    if ($results->RecordCount() > 0) {
        foreach ($results as $result) {
            $m_array = zen_count_products_in_cats($result['categories_id']);
            $c_array['this_count'] += $m_array['this_count'];
            $c_array['this_count_on'] += $m_array['this_count_on'];

//          $this_count_on += $pr_count->fields['pr_on'];
        }
    }
    return $c_array;
}

/**
 * Return the number of products in a category
 * TABLES: products, products_to_categories, categories
 * syntax for count: zen_get_products_to_categories($categories->fields['categories_id'], true)
 * syntax for linked products: zen_get_products_to_categories($categories->fields['categories_id'], true, 'products_active')
 *
 * @TODO - refactor to use only a boolean response instead of string 'true'
 *
 * @param int $category_id
 * @param bool $include_inactive
 * @param string $counts_what products|products_active
 * @return bool|string
 */
function zen_get_products_to_categories($category_id, $include_inactive = false, $counts_what = 'products') {
    global $db;

    $products_count = $cat_products_count = 0;
    $products_linked = '';
    if ($include_inactive == true) {
        switch ($counts_what) {
            case ('products'):
                $cat_products_query = "SELECT count(*) as total
                           FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           WHERE p.products_id = p2c.products_id
                           AND p2c.categories_id = " . (int)$category_id;
                break;
            case ('products_active'):
                $cat_products_query = "SELECT p.products_id
                           FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           WHERE p.products_id = p2c.products_id
                           AND p2c.categories_id = " . (int)$category_id;
                break;
        }

    } else {
        switch ($counts_what) {
            case ('products'):
                $cat_products_query = "SELECT count(*) as total
                             FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                             WHERE p.products_id = p2c.products_id
                             AND p.products_status = 1
                             AND p2c.categories_id = " . (int)$category_id;
                break;
            case ('products_active'):
                $cat_products_query = "SELECT p.products_id
                             FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                             WHERE p.products_id = p2c.products_id
                             AND p.products_status = 1
                             AND p2c.categories_id = " . (int)$category_id;
                break;
        }
    }
    $cat_products = $db->Execute($cat_products_query);
    switch ($counts_what) {
        case ('products'):
            if (!$cat_products->EOF) $cat_products_count += $cat_products->fields['total'];
            break;
        case ('products_active'):
            while (!$cat_products->EOF) {
                if (zen_get_product_is_linked($cat_products->fields['products_id']) == 'true') {
                    return $products_linked = 'true';
                }
                $cat_products->MoveNext();
            }
            break;
    }

    $child_categories_query = "SELECT categories_id
                               FROM " . TABLE_CATEGORIES . "
                               WHERE parent_id = " . (int)$category_id;

    $cat_child_categories = $db->Execute($child_categories_query);

    if ($cat_child_categories->RecordCount() > 0) {
        while (!$cat_child_categories->EOF) {
            switch ($counts_what) {
                case ('products'):
                    $cat_products_count += zen_get_products_to_categories($cat_child_categories->fields['categories_id'], $include_inactive);
                    break;
                case ('products_active'):
                    if (zen_get_products_to_categories($cat_child_categories->fields['categories_id'], true, 'products_active') == 'true') {
                        return $products_linked = 'true';
                    }
                    break;
            }
            $cat_child_categories->MoveNext();
        }
    }

    if ($counts_what === 'products') {
        return $cat_products_count;
    }

    return $products_linked;
}
