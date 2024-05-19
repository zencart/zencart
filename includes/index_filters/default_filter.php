<?php

/**
 * default_filter.php for index filters
 *
 * index filter for the default product type
 * show the products of a specified manufacturer
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @todo Need to add/fine-tune ability to override or insert entry-points on a per-product-type basis
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Apr 07 Modified in v2.0.1 $
 */
/**
 * @var queryFactory $db
 * @var notifier $zco_notifier
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (isset($_GET['sort']) && strlen($_GET['sort']) > 3) {
    $_GET['sort'] = substr($_GET['sort'], 0, 3);
}
if (isset($_GET['alpha_filter_id']) && (int)$_GET['alpha_filter_id'] > 0) {
    $alpha_sort = " AND pd.products_name LIKE '" . chr((int)$_GET['alpha_filter_id']) . "%' ";
} else {
    $alpha_sort = '';
}
if (!isset($select_column_list)) {
    $select_column_list = '';
}
if (!isset($do_filter_list)) {
    $do_filter_list = false;
}

$and = $and ?? '';
$sql_joins = $sql_joins ?? '';

// show the products of a specified manufacturer
if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
    // We show them all
    $and .= " AND m.manufacturers_id = " . (int)$_GET['manufacturers_id'] . " ";
    if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id'])) {
        // We are asked to show only a specific category
        $sql_joins .= " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id ";
        $and .= " AND p2c.categories_id = " . (int)$_GET['filter_id'] . " ";
    } else {
        $sql_joins .= " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id ";
        $and .= ' AND p2c.categories_id = p.master_categories_id ';
    }
} else {
    if (empty($and) && !empty($current_category_id)) {
        // show the products in a given category
        // We show them all
        $sql_joins .= " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id ";
        $and .= " AND p2c.categories_id = " . (int)$current_category_id . " ";
    }
    if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id'])) {
        // We are asked to show only specific category
        $and .= " AND m.manufacturers_id = " . (int)$_GET['filter_id'] . " ";
    }
}

$listing_sql = "SELECT " . $select_column_list . " p.products_id, p.products_type, p.master_categories_id,
                       p.manufacturers_id, p.products_price, p.products_tax_class_id, pd.products_description,
                       IF(s.status = 1, s.specials_new_products_price, NULL) AS specials_new_products_price,
                       IF(s.status = 1, s.specials_new_products_price, p.products_price) AS final_price,
                       p.products_sort_order, p.product_is_call, p.product_is_always_free_shipping, p.products_qty_box_status
                FROM " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_SPECIALS . " s ON s.products_id = p.products_id
                LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = p.products_id AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id = p.manufacturers_id
                ";
$listing_sql .= $sql_joins ?? ' ';
$where_str = "
                WHERE p.products_status = 1
                " . $and . "
                " . $alpha_sort;


// $default_sort_order could be set in header_php or main_template_vars before we get here
$order_by = $default_sort_order ?? '';
if (empty($order_by) || !empty($_GET['disp_order'])) {
    // Build ORDER BY sort chosen from dropdown, or apply defaults
    $order_by_backup = $order_by;
    require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_LISTING_DISPLAY_ORDER));
    if (empty($order_by)) {
        $order_by = $order_by_backup;
    }
}

// Legacy $_GET['sort'] which was used for sort-by-clicking-column-heading
if (isset($column_list) && !empty($_GET['sort'])) {
    if (!isset($_GET['sort']) && PRODUCT_LISTING_DEFAULT_SORT_ORDER !== '') {
        $_GET['sort'] = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
    }

    if ((!isset($_GET['sort']))
        || !preg_match('/[1-8][ad]/', $_GET['sort'])
        || (substr($_GET['sort'], 0, 1) > count($column_list))) {
        for ($i = 0, $n = count($column_list); $i < $n; $i++) {
            if (isset($column_list[$i]) && $column_list[$i] === 'PRODUCT_LIST_NAME') {
                $_GET['sort'] = $i + 1 . 'a';
                $order_by = " ORDER BY p.products_sort_order, pd.products_name";
                break;
            } else {
                // sort by products_sort_order when PRODUCT_LISTING_DEFAULT_SORT_ORDER is left blank
                // for reverse, descending order use:
                // $order_by = " order by p.products_sort_order desc, pd.products_name";
                $order_by = " ORDER BY p.products_sort_order, pd.products_name";
                break;
            }
        }
        // if set to nothing use products_sort_order and PRODUCTS_LIST_NAME is off
        if (PRODUCT_LISTING_DEFAULT_SORT_ORDER === '') {
            $_GET['sort'] = '20a';
        }
    } else {
        $sort_col = substr($_GET['sort'], 0, 1);
        $sort_order = substr($_GET['sort'], -1);
        switch ($column_list[$sort_col - 1]) {
            case 'PRODUCT_LIST_MODEL':
                $order_by = " ORDER BY p.products_model " . ($sort_order === 'd' ? 'DESC' : '') . ", pd.products_name";
                break;
            case 'PRODUCT_LIST_NAME':
                $order_by = " ORDER BY pd.products_name " . ($sort_order === 'd' ? 'DESC' : '');
                break;
            case 'PRODUCT_LIST_MANUFACTURER':
                $order_by = " ORDER BY m.manufacturers_name " . ($sort_order === 'd' ? 'DESC' : '') . ", pd.products_name";
                break;
            case 'PRODUCT_LIST_QUANTITY':
                $order_by = " ORDER BY p.products_quantity " . ($sort_order === 'd' ? 'DESC' : '') . ", pd.products_name";
                break;
            case 'PRODUCT_LIST_IMAGE':
                $order_by = " ORDER BY pd.products_name";
                break;
            case 'PRODUCT_LIST_WEIGHT':
                $order_by = " ORDER BY p.products_weight " . ($sort_order === 'd' ? 'DESC' : '') . ", pd.products_name";
                break;
            case 'PRODUCT_LIST_PRICE':
                $order_by = " ORDER BY p.products_price_sorter " . ($sort_order === 'd' ? 'DESC' : '') . ", pd.products_name";
                break;
        }
    }
}

$zco_notifier->notify('NOTIFY_PRODUCT_LISTING_QUERY_STRING', ['default'], $listing_sql, $where_str, $order_by);
$listing_sql .= ' ' . $where_str . ' ' . $order_by;


// optional Product List Filter
if (PRODUCT_LIST_FILTER > 0) {
    if (!empty($_GET['manufacturers_id'])) {
        $filterlist_sql = "SELECT c.categories_id AS id, cd.categories_name AS name
                       FROM " . TABLE_PRODUCTS . " p
                       LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id
                       LEFT JOIN " . TABLE_CATEGORIES . " c ON c.categories_id = p2c.categories_id
                       LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = p2c.categories_id
                         AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                       WHERE p.products_status = 1
                       AND p.manufacturers_id = " . (int)$_GET['manufacturers_id'] . "
                       GROUP BY c.categories_id, cd.categories_name
                       ORDER BY cd.categories_name";
    } else {
        $filterlist_sql = "SELECT m.manufacturers_id AS id, m.manufacturers_name AS name
                       FROM " . TABLE_PRODUCTS . " p
                       LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id
                       JOIN " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id = p.manufacturers_id
                       WHERE p.products_status = 1
                       AND p2c.categories_id = " . (int)$current_category_id . "
                       GROUP BY m.manufacturers_id, m.manufacturers_name
                       ORDER BY m.manufacturers_name";
    }
    $do_filter_list = false;
    $filterlist = $db->Execute($filterlist_sql);
    if ($filterlist->RecordCount() > 1) {
        $do_filter_list = true;
        if (isset($_GET['manufacturers_id'])) {
            $getoption_set = true;
            $get_option_variable = 'manufacturers_id';
            $options = [
                [
                    'id' => '',
                    'text' => TEXT_ALL_CATEGORIES,
                ],
            ];
        } else {
            $options = [
                [
                    'id' => '',
                    'text' => TEXT_ALL_MANUFACTURERS,
                ],
            ];
        }
        foreach ($filterlist as $item) {
            $options[] = [
                'id' => $item['id'],
                'text' => $item['name'],
            ];
        }
    }
}
