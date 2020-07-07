<?php
/**
 * functions_categories.php
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte  Modified in v1.5.8 $
 */

/**
 * Generate a cPath string from current category conditions
 * @param int $current_category_id
 * @return string
 */
function zen_get_path($current_category_id = '')
{
    global $cPath_array, $db;

    if ($current_category_id === '' || empty($cPath_array)) {
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
    global $db;
    $products_count = 0;

    $sql = "SELECT count(*) as total
            FROM " . TABLE_PRODUCTS . " p
             LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c USING (products_id)
            WHERE p2c.categories_id = " . (int)$category_id;

    if ($include_inactive) {
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
 * @return array|mixed
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
                         LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd USING (categories_id)
                         WHERE parent_id = " . (int)$parent_id . "
                         " . $status_filter . "
                         AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                         ORDER BY c.sort_order, cd.categories_name";
    $results = $db->Execute($categories_query);

    foreach ($results as $result) {
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
 * @param int $categories_id
 * @return bool
 */
function zen_get_parent_categories(&$categories, $categories_id)
{
    global $db;
    $sql = "SELECT parent_id
            FROM " . TABLE_CATEGORIES . "
            WHERE categories_id = " . (int)$categories_id;

    $results = $db->Execute($sql);

    foreach ($results as $result) {

        if ($result['parent_id'] == TOPMOST_CATEGORY_PARENT_ID) return true;

        $categories[count($categories)] = $result['parent_id'];
        if ($result['parent_id'] != $categories_id) {
            zen_get_parent_categories($categories, $result['parent_id']);
        }
    }
}

/**
 * Construct a category path to the product
 * @param int $products_id
 * @return string
 */
function zen_get_product_path($products_id)
{
    global $db;
    $cPath = '';

    $category_query = "SELECT p.products_id, p.master_categories_id
                       FROM " . TABLE_PRODUCTS . " p
                       WHERE p.products_id = " . (int)$products_id . " LIMIT 1";

    $category = $db->Execute($category_query);

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
    $cPath_array = array_map('zen_string_to_int', explode('_', $cPath));

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
    $category = $db->Execute($sql);

    while (!$category->EOF) {
        if ($category->fields['categories_id'] == $cat_id) {
            $in_cat = true;
        }
        if (!$in_cat) {
            $parent_categories_query = "SELECT parent_id
                                        FROM " . TABLE_CATEGORIES . "
                                        WHERE categories_id = " . (int)$category->fields['categories_id'];

            $parent_categories = $db->Execute($parent_categories_query);

            while (!$parent_categories->EOF) {
                if ($parent_categories->fields['parent_id'] != TOPMOST_CATEGORY_PARENT_ID) {
                    if (!$in_cat) $in_cat = zen_product_in_parent_category($product_id, $cat_id, $parent_categories->fields['parent_id']);
                }
                $parent_categories->MoveNext();
            }
        }
        $category->MoveNext();
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
        $in_cat = true;
    } else {
        $parent_categories_query = "SELECT parent_id
                                    FROM " . TABLE_CATEGORIES . "
                                    WHERE categories_id = " . (int)$parent_cat_id;

        $parent_categories = $db->Execute($parent_categories_query);

        while (!$parent_categories->EOF) {
            if ($parent_categories->fields['parent_id'] != TOPMOST_CATEGORY_PARENT_ID && !$in_cat) {
                $in_cat = zen_product_in_parent_category($product_id, $cat_id, $parent_categories->fields['parent_id']);
            }
            $parent_categories->MoveNext();
        }
    }
    return $in_cat;
}


/**
 * products with name, model and price pulldown
 * @param string $field_name
 * @param string $parameters
 * @param array $exclude array of ids to exclude
 * @param bool $show_id include ID #
 * @param int $set_selected default product id to be selected
 * @param bool $show_model
 * @param bool $show_current_category
 * @return string
 */
function zen_draw_products_pull_down($field_name, $parameters = '', $exclude = [], $show_id = false, $set_selected = 0, $show_model = false, $show_current_category = false)
{
    global $currencies, $db, $current_category_id, $prev_next_order;

    // $prev_next_order set by products_previous_next.php, if category navigation in use
    $order_by = $db->prepare_input(!empty($prev_next_order) ? $prev_next_order : ' ORDER BY products_name');

    if (!is_array($exclude)) {
        $exclude = [];
    }

    $select_string = '<select name="' . $field_name . '"';

    if ($parameters) {
        $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    if ($show_current_category) {
        // only show $current_categories_id
        $sql = "SELECT p.products_id, pd.products_name, p.products_sort_order, p.products_price, p.products_model, ptc.categories_id
                FROM " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc USING (products_id)
                LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd USING (products_id)
                WHERE pd.language_id = " . (int)$_SESSION['languages_id'] . "
                AND ptc.categories_id = " . (int)$current_category_id .
                $order_by;
    } else {
        $sql = "SELECT p.products_id, pd.products_name, p.products_sort_order, p.products_price, p.products_model
                FROM " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd USING (products_id)
                WHERE pd.language_id = " . (int)$_SESSION['languages_id'] .
                $order_by;
    }
    $results = $db->Execute($sql);

    foreach ($results as $result) {
        if (!in_array($result['products_id'], $exclude)) {
            $display_price = zen_get_products_base_price($result['products_id']);
            $select_string .= '<option value="' . $result['products_id'] . '"';
            if ($set_selected == $result['products_id']) {
                $select_string .= ' SELECTED';
            }
            $select_string .= '>' . $result['products_name']
                . ' (' . $currencies->format($display_price) . ')'
                . ($show_model ? ' [' . $result['products_model'] . '] ' : '')
                . ($show_id ? ' - ID# ' . $result['products_id'] : '')
                . '</option>';
        }
    }

    $select_string .= '</select>';

    return $select_string;
}


/**
 * products with attributes pulldown
 * @param string $field_name
 * @param string $parameters
 * @param array $exclude to exclude
 * @param string $order_by model|name
 * @param int $filter_by_option_name -1|0|option_name_id
 * @return string
 */
function zen_draw_products_pull_down_attributes($field_name, $parameters = '', $exclude = [], $order_by = 'name', $filter_by_option_name = null)
{
    global $db, $currencies;

    if (!is_array($exclude)) {
        $exclude = [];
    }

    $select_string = '<select name="' . $field_name . '"';
    if ($parameters) {
        $select_string .= ' ' . $parameters;
    }
    $select_string .= '>';

    $new_fields = ', p.products_model';

    if ($order_by == 'model') {
        $order_by = 'p.products_model';
        $output_string = '<option value="%1$u"> %3$s - %2$s (%4$s)</option>'; // format string with model first
    } else {
        $order_by = 'pd.products_name';
        $output_string = '<option value="%1$u">%2$s (%3$s) (%4$s)</option>';// format string with name first
    }

    switch (true) {
        case ($filter_by_option_name === -1): // no selection made: do not list any products
            // no selection made yet
            break;
        case ((int)$filter_by_option_name > 0) : // an Option Name was selected: show only products using attributes with this Option Name
            $sql = "SELECT distinct p.products_id, pd.products_name, p.products_price" . $new_fields . "
                    FROM " . TABLE_PRODUCTS . " p
                    LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd USING (products_id)
                    LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa USING (products_id)
                    WHERE pd.language_id = " . (int)$_SESSION['languages_id'] . "
                    AND pa.options_id = " . (int)$filter_by_option_name . "
                    ORDER BY " . $order_by;
            break;
        default: //legacy: show all products with attributes
            $sql = "SELECT distinct p.products_id, pd.products_name, p.products_price" . $new_fields . "
                    FROM " . TABLE_PRODUCTS . " p
                    LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd USING (products_id)
                    LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa USING (products_id)
                    WHERE pd.language_id = " . (int)$_SESSION['languages_id'] . "
                    ORDER BY " . $order_by;
            break;
    }

    if (isset($sql)) {
        $products = $db->Execute($sql);
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

/**
 * categories pulldown with products
 * @param string $field_name
 * @param string $parameters
 * @param array $exclude to exclude
 * @param bool $show_id include ID #
 * @param bool $show_parent
 * @return string
 */
function zen_draw_products_pull_down_categories($field_name, $parameters = '', $exclude = [], $show_id = false, $show_parent = false)
{
    global $db, $currencies;

    if (!is_array($exclude)) {
        $exclude = [];
    }

    $select_string = '<select name="' . $field_name . '"';

    if ($parameters) {
        $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $sql = "SELECT DISTINCT c.categories_id, cd.categories_name
            FROM " . TABLE_CATEGORIES . " c,
            LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd USING (categories_id)
            LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc USING (categories_id)
            WHERE cd.language_id = " . (int)$_SESSION['languages_id'] . "
            ORDER BY categories_name";
    $results = $db->Execute($sql);
    foreach ($results as $result) {
        if (!in_array($result['categories_id'], $exclude)) {
            if ($show_parent) {
                $parent = zen_get_products_master_categories_name($result['categories_id']);
                if ($parent != '') {
                    $parent = ' : in ' . $parent;
                }
            } else {
                $parent = '';
            }
            $select_string .= '<option value="' . $result['categories_id'] . '">'
                . $result['categories_name']
                . $parent
                . ($show_id ? ' - ID#' . $result['categories_id'] : '')
                . '</option>';
        }
    }

    $select_string .= '</select>';

    return $select_string;
}

/**
 * categories pulldown for products with attributes
 * @param string $field_name
 * @param string $parameters
 * @param array $exclude
 * @param bool $show_full_path
 * @param string $filter_by_option_name
 * @return string
 */
function zen_draw_products_pull_down_categories_attributes($field_name, $parameters = '', $exclude = [], $show_full_path = false, $filter_by_option_name = '')
{
    global $db, $currencies;

    if (!is_array($exclude)) {
        $exclude = [];
    }

    $select_string = '<select name="' . $field_name . '"';
    if ($parameters) {
        $select_string .= ' ' . $parameters;
    }
    $select_string .= '>';

    switch (true) {
        case ($filter_by_option_name === ''): // no selection made: do not list any categories
            // no selection made yet
            break;
        case ($filter_by_option_name > 0) : // an Option Name was selected: show only categories with products using attributes with this Option Name
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
                if ($show_full_path) {
                    $select_string .= '<option value="' . $category['categories_id'] . '">' . zen_output_generated_category_path($category['categories_id']) . '</option>';
                } else {
                    $select_string .= '<option value="' . $category['categories_id'] . '">' . $category['categories_name'] . '</option>';
                }
            }
        }
    }
    $select_string .= '</select>';

    return $select_string;
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
    $result = $db->Execute($sql);

    if ($result->RecordCount()) {
        return $result->fields['product_type_id'];
    }

    return false;
}

/**
 * look up parent categories name
 * @param int $categories_id
 * @return string name of parent category, or blank if none
 */
function zen_get_categories_parent_name($categories_id)
{
    global $db;

    $sql = "SELECT categories_name
            FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd
            LEFT JOIN " . TABLE_CATEGORIES . " c ON c.parent_id = cd.categories_id
            WHERE categories_id=" . (int)$categories_id . "
            AND language_id= " . $_SESSION['languages_id'];
    $result = $db->Execute($sql);

    return $result->fields['categories_name'];
}

/**
 * Get all products_id in a Category and its SubCategories
 * use as:
 * $my_products_id_list = array();
 * $my_products_id_list = zen_get_categories_products_list($categories_id)
 * @param int $categories_id
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
        ($include_deactivated ? " AND p.products_status = 1" : '') .
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
                $category = $db->Execute("SELECT cd.categories_name, c.parent_id
                                    FROM " . TABLE_CATEGORIES . " c
                                    LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd USING (categories_id)
                                    WHERE c.categories_id = " . (int)$p2cResult['categories_id'] . "
                                    AND cd.language_id = " . (int)$_SESSION['languages_id']);

                $categories_array[$index][] = ['id' => $p2cResult['categories_id'], 'text' => $category->fields['categories_name']];
                if (zen_not_null($category->fields['parent_id']) && $category->fields['parent_id'] != TOPMOST_CATEGORY_PARENT_ID) {
                    $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
                }
                $categories_array[$index] = array_reverse($categories_array[$index]);
            }
            $index++;
        }
    } elseif ($from == 'category') {
        $category = $db->Execute("SELECT cd.categories_name, c.parent_id
                                FROM " . TABLE_CATEGORIES . " c
                                LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd USING (categories_id)
                                WHERE c.categories_id = " . (int)$id . "
                                AND cd.language_id = " . (int)$_SESSION['languages_id']);

        if (!$category->EOF) {
            $categories_array[$index][] = ['id' => $id, 'text' => $category->fields['categories_name']];
            if (zen_not_null($category->fields['parent_id']) && $category->fields['parent_id'] != TOPMOST_CATEGORY_PARENT_ID) {
                $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
            }
        }
    }

    return $categories_array;
}

/**
 * @param int $id
 * @param string $from
 * @return string|string[]|null
 */
function zen_output_generated_category_path($id, $from = 'category')
{
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($id, $from);

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
    $calculated_category_path_string = preg_replace('/<br ?/?>$/', '', $calculated_category_path_string);

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
           LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd USING (categories_id)
           WHERE cd.language_id = " . (int)$_SESSION['languages_id'] . "
           AND c.parent_id = " . (int)$parent_id . "
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
 * Get the status of a category
 * @param int $categories_id
 * @return mixed|string
 */
function zen_get_categories_status($categories_id)
{
    global $db;
    $sql = "SELECT categories_status
            FROM " . TABLE_CATEGORIES .
            (zen_not_null($categories_id) ? " WHERE categories_id=" . (int)$categories_id : "");
    $check_status = $db->Execute($sql);
    if ($check_status->EOF) return '';
    return $check_status->fields['categories_status'];
}
