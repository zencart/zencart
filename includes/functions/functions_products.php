<?php
/**
 * Functions related to products
 * Note: Several product-related lookup functions are located in functions_lookups.php
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 07 New in v1.5.7 $
 */

/**
 * Query product details, returning a db QueryFactory response to iterate through
 *
 * @param int $product_id
 * @param int $language_id (optional)
 * @return queryFactoryResult
 */
function zen_get_product_details($product_id, $language_id = null)
{
    global $db;

    if ($language_id === null) $language_id = $_SESSION['languages_id'];

    $sql = "SELECT p.*, pd.*
            FROM " . TABLE_PRODUCTS . " p
            LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd USING (products_id)
            WHERE p.products_id = " . (int)$product_id . "
            AND pd.language_id = " . (int)$language_id;
    return $db->Execute($sql, 1, true, 900);
}

/**
 * @param int $product_id
 * @param null $product_info
 */
function zen_product_set_header_response($product_id, $product_info = null)
{
    global $zco_notifier, $breadcrumb, $robotsNoIndex;

    // make sure we got a dbResponse
    if ($product_info === null || !isset($product_info->EOF)) {
        $product_info = zen_get_product_details($product_id);
    }
    // make sure it's for the current product
    if (!isset($product_info->fields['products_id'], $product_info->fields['products_status']) || $product_info->fields['products_id'] !== $product_id) {
        $product_info = zen_get_product_details($product_id);
    }

    $response_code = 200;

    $should_throw_404 = $product_not_found = $product_info->EOF;
    if ($should_throw_404) {
        $response_code = 404;
    }

    global $product_status;
    $product_status = !$product_info->EOF && $product_info->fields['products_status'] ? (int)$product_info->fields['products_status'] : 0;

    if ($product_status === 0) {
        $response_code = 410;
    }

    if (defined('PRODUCT_THROWS_200_WHEN_DISABLED') && PRODUCT_THROWS_200_WHEN_DISABLED === true) {
        $response_code = 200;
    }

    if ($product_status === -1) {
        $response_code = 410;
    }

    $use_custom_response_code = false;
    /**
     * optionally update the $product_status, $should_throw_404, $response_code vars via the observer
     */
    $zco_notifier->notify('NOTIFY_PRODUCT_INFO_PRODUCT_STATUS_CHECK', $product_info->fields, $product_status, $should_throw_404, $response_code, $use_custom_response_code);

    if ($use_custom_response_code) {
        // skip this function's processing and leave all header handling to the observer.
        // Note: the observer should do all the 404 stuff from below too
        return;
    }

    if ($should_throw_404) {
        // if specified product_id doesn't exist, ensure that metatags and breadcrumbs don't share bad data or inappropriate information
        unset($_GET['products_id']);
        unset($breadcrumb->_trail[count($breadcrumb->_trail) - 1]['title']);
        $robotsNoIndex = true;
        header('HTTP/1.1 404 Not Found');
        return;
    }

    if ($response_code === 410) {
        $robotsNoIndex = true;
        header('HTTP/1.1 410 Gone');
        return;
    }

    if ($response_code === 200) return;
}

/**
 * @param int $products_id
 * @param int $status
 */
function zen_set_disabled_upcoming_status($products_id, $status)
{
    global $db;

    $sql = "UPDATE " . TABLE_PRODUCTS . "
            SET products_status = " . (int)$status . ", products_date_available = NULL
            WHERE products_id = " . (int)$products_id;

    $db->Execute($sql);
}

/**
 * Enable all disabled products whose date_available is prior to the specified date
 * @param int $datetime optional timestamp
 */
function zen_enable_disabled_upcoming($datetime = null)
{
    global $db;

    if (empty($datetime)) $datetime = time();

    $zc_disabled_upcoming_date = date('Ymd', $datetime);

    $sql = "SELECT products_id
            FROM " . TABLE_PRODUCTS . "
            WHERE products_status = 0
            AND products_date_available <= " . $zc_disabled_upcoming_date . "
            AND products_date_available >= '0001-01-01'
            AND products_date_available IS NOT NULL
            ";

    $results = $db->Execute($sql);

    foreach ($results as $result) {
        zen_set_disabled_upcoming_status($result['products_id'], 1);
    }
}

/**
 * build date range for "upcoming products" query
 */
function zen_get_upcoming_date_range() {
    // 120 days; 24 hours; 60 mins; 60secs
    $date_range = time();
    $zc_new_date = date('Ymd', $date_range);
// need to check speed on this for larger sites
//    $new_range = ' and date_format(p.products_date_available, \'%Y%m%d\') >' . $zc_new_date;
    $new_range = ' and p.products_date_available >' . $zc_new_date . '235959';

    return $new_range;
}

/**
 * build date range for "new products" query
 */
function zen_get_new_date_range($time_limit = false) {
    if ($time_limit == false) {
        $time_limit = SHOW_NEW_PRODUCTS_LIMIT;
    }
    // 120 days; 24 hours; 60 mins; 60secs
    $date_range = time() - ($time_limit * 24 * 60 * 60);
    $upcoming_mask_range = time();
    $upcoming_mask = date('Ymd', $upcoming_mask_range);

    $zc_new_date = date('Ymd', $date_range);
    switch (true) {
        case (SHOW_NEW_PRODUCTS_LIMIT == 0):
            $new_range = '';
            break;
        case (SHOW_NEW_PRODUCTS_LIMIT == 1):
            $zc_new_date = date('Ym', time()) . '01';
            $new_range = ' and p.products_date_added >=' . $zc_new_date;
            break;
        default:
            $new_range = ' and p.products_date_added >=' . $zc_new_date;
    }

    if (SHOW_NEW_PRODUCTS_UPCOMING_MASKED == 0) {
        // do nothing upcoming shows in new
    } else {
        // do not include upcoming in new
        $new_range .= " and (p.products_date_available <=" . $upcoming_mask . " or p.products_date_available IS NULL)";
    }
    return $new_range;
}

/**
 * build New Products query clause
 */
function zen_get_products_new_timelimit($time_limit = false) {
    if ($time_limit == false) {
        $time_limit = SHOW_NEW_PRODUCTS_LIMIT;
    }
    switch (true) {
        case ($time_limit == '0'):
            $display_limit = '';
            break;
        case ($time_limit == '1'):
            $display_limit = " and date_format(p.products_date_added, '%Y%m') >= date_format(now(), '%Y%m')";
            break;
        case ($time_limit == '7'):
            $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 7';
            break;
        case ($time_limit == '14'):
            $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 14';
            break;
        case ($time_limit == '30'):
            $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 30';
            break;
        case ($time_limit == '60'):
            $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 60';
            break;
        case ($time_limit == '90'):
            $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 90';
            break;
        case ($time_limit == '120'):
            $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 120';
            break;
    }
    return $display_limit;
}


/**
 * Return a product's category (master_categories_id)
 * @param int $product_id
 * @return int|string
 */
function zen_get_products_category_id($product_id)
{
    $result = zen_get_product_details($product_id);
    if ($result->EOF) return '';
    return $result->fields['master_categories_id'];
}

/**
 * Reset master_categories_id for all products linked to the specified $category_id
 * @param int $category_id
 */
function zen_reset_products_category_as_master($category_id)
{
    global $db;
    $sql = "SELECT p.products_id, p.master_categories_id, ptoc.categories_id
            FROM " . TABLE_PRODUCTS . " p
            LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc USING (products_id)
            WHERE ptoc.categories_id = " . (int)$category_id;

    $results = $db->Execute($sql);
    foreach ($results as $item) {
        zen_set_product_master_categories_id($item['products_id'], $category_id);
    }
}

function zen_reset_all_products_master_categories_id()
{
    global $db;
    $sql = "SELECT products_id FROM " . TABLE_PRODUCTS;
    $products = $db->Execute($sql);
    foreach ($products as $product) {
        // Note: "USE INDEX ()" is intentional, to retrieve results in original insert order
        $sql = "SELECT products_id, categories_id
                FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                USE INDEX ()
                WHERE products_id=" . (int)$product['products_id'] . "
                LIMIT 1";
        $check_category = $db->Execute($sql);

        zen_set_product_master_categories_id($product['products_id'], $check_category->fields['categories_id']);
    }
}

/**
 * Update master_categories_id for specified product
 * Also updates cache of lowest sale price based on the category change
 * @param int $product_id
 * @param int $category_id
 */
function zen_set_product_master_categories_id($product_id, $category_id)
{
    global $db;
    $sql = "UPDATE " . TABLE_PRODUCTS . "
            SET master_categories_id = " . (int)$category_id . "
            WHERE products_id = " . (int)$product_id . " LIMIT 1";
    $db->Execute($sql);

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter($product_id);
}

/**
 * @param int $product_id
 * @param array $exclude
 * @return array of categories_id
 */
function zen_get_linked_categories_for_product($product_id, $exclude = [])
{
    global $db;
    $exclude = array_filter($exclude, function($record) {return is_numeric($record) ? (int)$record : null;});
    $sql = "SELECT categories_id
            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE products_id = " . (int)$product_id;
    if (!empty($exclude) && is_array($exclude)) {
        $sql .= " AND categories_id NOT IN (" . implode(',', $exclude) . ")";
    }
    $results = $db->Execute($sql);
    $categories = [];
    foreach ($results as $result) {
        $categories[] = $result['categories_id'];
    }
    return $categories;
}

/**
 * @param int $category_id
 * @param bool $first_only if true, return only the first result (string)
 * @return array|string Array of categories_id or empty string if $first-only specified but record not found
 */
function zen_get_linked_products_for_category($category_id, $first_only = false)
{
    global $db;
    $sql = "SELECT products_id
            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE categories_id = " . (int)$category_id . "
            ORDER BY products_id";
    $results = $db->Execute($sql);

    if ($first_only) {
        if ($results->RecordCount()) {
            return $results->fields['products_id'];
        }
        return '';
    }

    $products = [];
    foreach ($results as $result) {
        $products[] = $result['products_id'];
    }
    return $products;
}

/**
 * @param int $product_id
 * @param int $category_id
 */
function zen_link_product_to_category($product_id, $category_id)
{
    global $db;
    $sql = "INSERT IGNORE INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
            VALUES (" . (int)$product_id . ", " . (int)$category_id . ")";
    $db->Execute($sql);
}

/**
 * @param int $product_id
 * @param int $category_id
 */
function zen_unlink_product_from_category($product_id, $category_id)
{
    global $db;
    $sql = "DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE products_id = " . (int)$product_id . "
            AND categories_id = " . (int)$category_id . "
            LIMIT 1";
    $db->Execute($sql);
}

/**
 * Reset by removing all links-to-other-categories for this product, other than its master_categories_id
 * @param int $product_id
 * @param int $master_category_id
 */
function zen_unlink_product_from_all_linked_categories($product_id, $master_category_id = null)
{
    global $db;
    if ($master_category_id === null) {
        $master_category_id = zen_get_products_category_id($product_id);
    }
    if (empty($master_category_id)) return;

    $sql = "DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE products_id = " . (int)$product_id . "
            AND categories_id != " . (int)$master_category_id;
    $db->Execute($sql);
}


/**
 * Return a product ID with attributes hash
 * @param string|int $prid
 * @param array|string $params
 * @return string
 */
function zen_get_uprid($prid, $params) {
    $uprid = $prid;
    if (!is_array($params) || empty($params) || strstr($prid, ':')) return $prid;

    foreach($params as $option => $value) {
        if (is_array($value)) {
            foreach($value as $opt => $val) {
                $uprid .= '{' . $option . '}' . trim($opt);
            }
        } else {
            $uprid .= '{' . $option . '}' . trim($value);
        }
    }

    $md_uprid = md5($uprid);
    return $prid . ':' . $md_uprid;
}


/**
 * Return a product ID from a product ID with attributes
 * Alternate: simply (int) the product id
 * @param string $uprid   ie: '11:abcdef12345'
 * @return mixed
 */
function zen_get_prid($uprid) {
    $pieces = explode(':', $uprid);
    return (int)$pieces[0];
}








/*
 *  validate products_id
 */
function zen_products_id_valid($valid_id) {
    global $db;
    $check_valid = $db->Execute("select p.products_id
                                 from " . TABLE_PRODUCTS . " p
                                 where products_id='" . (int)$valid_id . "' limit 1");
    if ($check_valid->EOF) {
        return false;
    } else {
        return true;
    }
}

/**
 * Return a product's name.
 *
 * @param int The product id of the product who's name we want
 * @param int The language id to use. If this is not set then the current language is used
 */
function zen_get_products_name($product_id, $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_query = "select products_name
                      from " . TABLE_PRODUCTS_DESCRIPTION . "
                      where products_id = '" . (int)$product_id . "'
                      and language_id = '" . (int)$language . "'";

    $product = $db->Execute($product_query);

    return $product->fields['products_name'];
}


/**
 * Return a product's stock-on-hand
 *
 * @param int $products_id The product id of the product whose stock we want
 */
function zen_get_products_stock($products_id) {
    global $db;

    // -----
    // Give an observer the chance to modify this function's return value.
    //
    $products_quantity = 0;
    $quantity_handled = false;
    $GLOBALS['zco_notifier']->notify(
        'ZEN_GET_PRODUCTS_STOCK',
        $products_id,
        $products_quantity,
        $quantity_handled
    );
    if ($quantity_handled) {
        return $products_quantity;
    }
    $products_id = zen_get_prid($products_id);
    $stock_query = "select products_quantity
                    from " . TABLE_PRODUCTS . "
                    where products_id = " . (int)$products_id . " LIMIT 1";

    $stock_values = $db->Execute($stock_query);

    return $stock_values->fields['products_quantity'];
}

/**
 * Check if the required stock is available.
 * If insufficent stock is available return an out of stock message
 *
 * @param int $products_id        The product id of the product whose stock is to be checked
 * @param int $products_quantity  Quantity to compare against
 */
function zen_check_stock($products_id, $products_quantity) {
    $stock_left = zen_get_products_stock($products_id) - $products_quantity;

    // -----
    // Give an observer the opportunity to change the out-of-stock message.
    //
    $the_message = '';
    if ($stock_left < 0) {
        $out_of_stock_message = STOCK_MARK_PRODUCT_OUT_OF_STOCK;
        $GLOBALS['zco_notifier']->notify(
            'ZEN_CHECK_STOCK_MESSAGE',
            array(
                $products_id,
                $products_quantity
            ),
            $out_of_stock_message
        );
        $the_message = '<span class="markProductOutOfStock">' . $out_of_stock_message . '</span>';
    }
    return $the_message;
}



/**
 * Return a product's manufacturer's name, from ID
 */
function zen_get_products_manufacturers_name($product_id) {
    global $db;

    $product_query = "select m.manufacturers_name
                      from " . TABLE_PRODUCTS . " p, " .
        TABLE_MANUFACTURERS . " m
                      where p.products_id = '" . (int)$product_id . "'
                      and p.manufacturers_id = m.manufacturers_id";

    $product =$db->Execute($product_query);

    return ($product->RecordCount() > 0) ? $product->fields['manufacturers_name'] : "";
}

/*
 * Return a product's manufacturer's image, from Prod ID
 * TABLES: products, manufacturers
 */
function zen_get_products_manufacturers_image($product_id) {
    global $db;

    $product_query = "select m.manufacturers_image
                      from " . TABLE_PRODUCTS . " p, " .
        TABLE_MANUFACTURERS . " m
                      where p.products_id = '" . (int)$product_id . "'
                      and p.manufacturers_id = m.manufacturers_id";

    $product =$db->Execute($product_query);
    if ($product->EOF) return '';
    return $product->fields['manufacturers_image'];
}

/*
 * Return a product's manufacturer's id, from Prod ID
 * TABLES: products
 */
function zen_get_products_manufacturers_id($product_id) {
    global $db;

    $product_query = "select p.manufacturers_id
                      from " . TABLE_PRODUCTS . " p
                      where p.products_id = '" . (int)$product_id . "'";

    $product =$db->Execute($product_query);

    return $product->fields['manufacturers_id'];
}


/*
 *  Return products description, based on specified language (or current lang if not specified)
 */
function zen_get_products_description($product_id, $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_query = "select products_description
                      from " . TABLE_PRODUCTS_DESCRIPTION . "
                      where products_id = '" . (int)$product_id . "'
                      and language_id = '" . (int)$language . "'";

    $product = $db->Execute($product_query);

    return $product->fields['products_description'];
}

/*
 * look up the product type from product_id and return an info page name (for template/page handling)
 */
function zen_get_info_page($zf_product_id) {
    global $db;
    $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id = '" . (int)$zf_product_id . "'";
    $zp_type = $db->Execute($sql);
    if ($zp_type->RecordCount() == 0) {
        return 'product_info';
    } else {
        $zp_product_type = $zp_type->fields['products_type'];
        $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = '" . (int)$zp_product_type . "'";
        $zp_handler = $db->Execute($sql);
        return $zp_handler->fields['type_handler'] . '_info';
    }
}


////
// TABLES: categories_name from products_id
function zen_get_categories_name_from_product($product_id) {
    global $db;

//    $check_products_category= $db->Execute("select products_id, categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $product_id . "' limit 1");
    $check_products_category = $db->Execute("select products_id, master_categories_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    $the_categories_name= $db->Execute("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id= '" . $check_products_category->fields['master_categories_id'] . "' and language_id= '" . $_SESSION['languages_id'] . "'");

    return $the_categories_name->fields['categories_name'];
}



/**
 * look up a products image and send back the image's HTML \<IMG...\> tag
 */
function zen_get_products_image($product_id, $width = SMALL_IMAGE_WIDTH, $height = SMALL_IMAGE_HEIGHT) {
    global $db;

    $sql = "select p.products_image from " . TABLE_PRODUCTS . " p  where products_id='" . (int)$product_id . "'";
    $look_up = $db->Execute($sql);

    if ($look_up->EOF) {
        return false;
    }

    return zen_image(DIR_WS_IMAGES . $look_up->fields['products_image'], zen_get_products_name($product_id), $width, $height);
}

/*
 * look up whether a product is virtual
 */
function zen_get_products_virtual($lookup) {
    global $db;

    $sql = "select p.products_virtual from " . TABLE_PRODUCTS . " p  where p.products_id='" . (int)$lookup . "'";
    $look_up = $db->Execute($sql);

    if ($look_up->fields['products_virtual'] == '1') {
        return true;
    } else {
        return false;
    }
}

/**
 * Look up whether the given product ID is allowed to be added to cart, according to product-type switches set in Admin
 * @param int $product_id
 * @return string Y|N
 */
function zen_get_products_allow_add_to_cart($product_id) {
    global $db, $zco_notifier;

    $sql = "select products_type, products_model from " . TABLE_PRODUCTS . " where products_id='" . (int)$product_id. "'";
    $type_lookup = $db->Execute($sql);

    $sql = "select allow_add_to_cart from " . TABLE_PRODUCT_TYPES . " where type_id = '" . (int)$type_lookup->fields['products_type'] . "'";
    $allow_add_to_cart = $db->Execute($sql);

    if (preg_match('/^GIFT/', addslashes($type_lookup->fields['products_model'])) && ($allow_add_to_cart->fields['allow_add_to_cart'] == 'Y')) {
        if (MODULE_ORDER_TOTAL_GV_STATUS !== 'true') {
            $allow_add_to_cart->fields['allow_add_to_cart'] = 'N';
        }
    }

    $response = $allow_add_to_cart->fields['allow_add_to_cart'];

    $zco_notifier->notify('NOTIFY_GET_PRODUCT_ALLOW_ADD_TO_CART', $product_id, $response);

    return $response;
}

/*
 * Look up SHOW_XXX_INFO switch for product ID and product type
 */
function zen_get_show_product_switch_name($lookup, $field, $prefix= 'SHOW_', $suffix= '_INFO', $field_prefix= '_', $field_suffix='') {
    global $db;

    $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id='" . (int)$lookup . "'";
    $type_lookup = $db->Execute($sql);

    $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = '" . (int)$type_lookup->fields['products_type'] . "'";
    $show_key = $db->Execute($sql);


    $zv_key = strtoupper($prefix . $show_key->fields['type_handler'] . $suffix . $field_prefix . $field . $field_suffix);

    return $zv_key;
}

/*
 * Look up SHOW_XXX_INFO switch for product ID and product type
 */
function zen_get_show_product_switch($lookup, $field, $prefix= 'SHOW_', $suffix= '_INFO', $field_prefix= '_', $field_suffix='') {
    global $db;

    $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id='" . $lookup . "'";
    $type_lookup = $db->Execute($sql);

    if ($type_lookup->RecordCount() == 0) {
        return false;
    }

    $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = '" . $type_lookup->fields['products_type'] . "'";
    $show_key = $db->Execute($sql);


    $zv_key = strtoupper($prefix . $show_key->fields['type_handler'] . $suffix . $field_prefix . $field . $field_suffix);

    $sql = "select configuration_key, configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . $zv_key . "'";
    $zv_key_value = $db->Execute($sql);
    if ($zv_key_value->RecordCount() > 0) {
        return $zv_key_value->fields['configuration_value'];
    } else {
        $sql = "select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . $zv_key . "'";
        $zv_key_value = $db->Execute($sql);
        if ($zv_key_value->RecordCount() > 0) {
            return $zv_key_value->fields['configuration_value'];
        } else {
            return false;
        }
    }
}

/**
 *  Look up whether a product is always free shipping
 */
function zen_get_product_is_always_free_shipping($lookup): bool
{
    global $db;

    $sql = "select p.product_is_always_free_shipping from " . TABLE_PRODUCTS . " p  where p.products_id='" . (int)$lookup . "'";
    $look_up = $db->Execute($sql);

    if ($look_up->fields['product_is_always_free_shipping'] == '1') {
        return true;
    }

    return false;
}




/**
 * @TODO - refactor to product object? or at least leverage zen_get_product_details() instead.
 * Return any field from products or products_description table
 * Example: zen_products_lookup('3', 'products_date_added');
 */
function zen_products_lookup($product_id, $what_field = 'products_name', $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_lookup = $db->Execute("select " . $what_field . " as lookup_field
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where p.products_id ='" . (int)$product_id . "'
                              and pd.products_id = p.products_id
                              and pd.language_id = '" . (int)$language . "'");

    $return_field = $product_lookup->fields['lookup_field'];

    return $return_field;
}
