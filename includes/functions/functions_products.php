<?php
/**
 * Functions related to products
 * Note: Several product-related lookup functions are located in functions_lookups.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 16 Modified in v2.1.0 $
 */

/**
 * Query product details, returning a db QueryFactory response to iterate through
 *
 * @param int $product_id
 * @param int $language_id (optional)
 * @return Product
 */
function zen_get_product_details($product_id, $language_id = null): Product
{
    return (new Product((int)$product_id))->forLanguage((int)$language_id);
}

function zen_product_set_header_response(int|string $product_id, ?Product $product_info = null): void
{
    global $zco_notifier, $breadcrumb, $robotsNoIndex;

    // make sure we got a dbResponse
    if ($product_info === null || !isset($product_info->EOF)) {
        $product_info = new Product((int)$product_id);
    }

    // make sure it's for the current product
    if (!isset($product_info->fields['products_id'], $product_info->fields['products_status']) || (int)$product_info->fields['products_id'] !== (int)$product_id) {
        $product_info = new Product((int)$product_id);
    }

    $product = $product_info->getData();

    $response_code = 200;

    $product_not_found = empty($product);
    $should_throw_404 = $product_not_found;

    if ($should_throw_404 === true) {
        $response_code = 404;
    }

    global $product_status;

    $product_status = (int)($product_not_found === false && $product['products_status'] !== '0') ? $product['products_status'] : 0;
    if ($product_status === 0) {
        $response_code = 410;
    }

    if (defined('DISABLED_PRODUCTS_TRIGGER_HTTP200') && DISABLED_PRODUCTS_TRIGGER_HTTP200 === 'true') {
        $response_code = 200;
    }

    if ($product_status === -1) {
        $response_code = 410;
    }

    $use_custom_response_code = false;
    /**
     * optionally update the $product_status, $should_throw_404, $response_code vars via the observer
     */
    $zco_notifier->notify('NOTIFY_PRODUCT_INFO_PRODUCT_STATUS_CHECK', $product_info, $product_status, $should_throw_404, $response_code, $use_custom_response_code);

    if ($use_custom_response_code) {
        // skip this function's processing and leave all header handling to the observer.
        // Note: the observer should do all the 404 stuff from below too
        return;
    }

    if ($should_throw_404) {
        // if specified product_id doesn't exist, ensure that metatags and breadcrumbs don't share bad data or inappropriate information
        unset($_GET['products_id']);
        $breadcrumb->removeLast();
        $robotsNoIndex = true;
        header('HTTP/1.1 404 Not Found');
        return;
    }

    if ($response_code === 410) {
        $robotsNoIndex = true;
        header('HTTP/1.1 410 Gone');
        return;
    }
}

/**
 * @param int $products_id
 * @param int $status
 */
function zen_set_disabled_upcoming_status($products_id, $status): void
{
    global $db;

    $sql = "UPDATE " . TABLE_PRODUCTS . "
            SET products_status = " . (int)$status . ", products_date_available = NULL
            WHERE products_id = " . (int)$products_id;

    $db->Execute($sql, 1);
}

/**
 * Enable all disabled products whose date_available is prior to the specified date
 * @param int $datetime optional timestamp
 */
function zen_enable_disabled_upcoming($datetime = null): void
{
    global $db;

    if (empty($datetime)) {
        $datetime = time();
    }

    $zc_disabled_upcoming_date = date('Ymd', $datetime);

    $sql = "SELECT products_id
            FROM " . TABLE_PRODUCTS . "
            WHERE products_status = 0
            AND products_date_available <= " . $zc_disabled_upcoming_date . "
            AND products_date_available != '0001-01-01'
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
function zen_get_upcoming_date_range(): string
{
    // 120 days; 24 hours; 60 mins; 60secs
    $date_range = time();
    $zc_new_date = date('Ymd', $date_range);
// need to check speed on this for larger sites
//    $new_range = ' AND date_format(p.products_date_available, \'%Y%m%d\') >' . $zc_new_date;
    $new_range = ' AND p.products_date_available >' . $zc_new_date . '235959';

    return $new_range;
}

/**
 * build date range for "new products" query
 * @param int $time_limit
 * @return string
 */
function zen_get_new_date_range($time_limit = false): string
{
    if ($time_limit == false) {
        $time_limit = (int)SHOW_NEW_PRODUCTS_LIMIT;
    }
    // 120 days; 24 hours; 60 mins; 60secs
    $date_range = time() - ($time_limit * 24 * 60 * 60);
    $upcoming_mask_range = time();
    $upcoming_mask = date('Ymd', $upcoming_mask_range);

    $zc_new_date = date('Ymd', $date_range);
    switch (true) {
        case (SHOW_NEW_PRODUCTS_LIMIT === 0):
            $new_range = '';
            break;
        case (SHOW_NEW_PRODUCTS_LIMIT === 1):
            $zc_new_date = date('Ym', time()) . '01';
            $new_range = ' AND p.products_date_added >= ' . $zc_new_date;
            break;
        default:
            $new_range = ' AND p.products_date_added >= ' . $zc_new_date;
            break;
    }

    if (SHOW_NEW_PRODUCTS_UPCOMING_MASKED !== '0') {
        // do not include upcoming in new
        $new_range .= " AND (p.products_date_available <= " . $upcoming_mask . " OR p.products_date_available IS NULL)";
    }
    return $new_range;
}

/**
 * Return a product's category (master_categories_id)
 * @param int $product_id
 * @return int|string
 */
function zen_get_products_category_id($product_id): int|string
{
    return (new Product((int)$product_id))->get('master_categories_id') ?? '';
}

/**
 * Reset master_categories_id for all products linked to the specified $category_id
 * @param int $category_id
 */
function zen_reset_products_category_as_master($category_id): void
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

function zen_reset_all_products_master_categories_id(): void
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
function zen_set_product_master_categories_id($product_id, $category_id): void
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
function zen_get_linked_categories_for_product($product_id, $exclude = []): array
{
    global $db;
    $exclude = array_filter($exclude, function ($record) {
        return is_numeric($record) ? (int)$record : null;
    });
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
 * @return array|integer Array of products_id, or if $first-only true, a single products_id/0 if record not found
 */
function zen_get_linked_products_for_category($category_id, bool $first_only = false): int|array
{
    global $db;
    $sql = "SELECT products_id
            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE categories_id = " . (int)$category_id . "
            ORDER BY products_id";
    if ($first_only) {
        $sql .= ' LIMIT 1';
    }
    $results = $db->Execute($sql);

    if ($first_only) {
        return $results->EOF ? 0 : (int)$results->fields['products_id'];
    }

    $products = [];
    foreach ($results as $result) {
        $products[] = (int)$result['products_id'];
    }
    return $products;
}

/**
 * @param int $product_id
 * @param int $category_id
 */
function zen_link_product_to_category($product_id, $category_id): void
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
function zen_unlink_product_from_category($product_id, $category_id): void
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
function zen_unlink_product_from_all_linked_categories($product_id, $master_category_id = null): void
{
    global $db;
    if ($master_category_id === null) {
        $master_category_id = zen_get_products_category_id($product_id);
    }
    if (empty($master_category_id)) {
        return;
    }

    $sql = "DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE products_id = " . (int)$product_id . "
            AND categories_id != " . (int)$master_category_id;
    $db->Execute($sql);
}

/**
 * Return a product ID with attributes hash
 *
 * @param int|string $prid
 * @param array|string $params
 * @return string
 */
function zen_get_uprid(int|string $prid, array|string $params): string
{
    // -----
    // The string version of the supplied $prid is returned if:
    //
    // 1. The supplied $params is not an array or is an empty array, implying
    //    that no attributes are associated with the product-selection.
    // 2. The supplied $prid is already in uprid-format (ppp:xxxx), where
    //    ppp is the product's id and xxx is a hash of the associated attributes.
    //
    $prid = (string)$prid;
    if (!is_array($params) || $params === [] || strpos($prid, ':') !== false) {
        return $prid;
    }

    // -----
    // Otherwise, the $params array is expected to contain option/value
    // pairs which are concatenated to the supplied $prid, hashed and then
    // appended to the supplied $prid.
    //
    $uprid = $prid;
    foreach ($params as $option => $value) {
        if (is_array($value)) {
            foreach ($value as $opt => $val) {
                $uprid .= '{' . $option . '}' . trim((string)$opt);
            }
        } else {
            $uprid .= '{' . $option . '}' . trim((string)$value);
        }
    }

    $md_uprid = hash('md5', $uprid);
    return $prid . ':' . $md_uprid;
}

/**
 * Return a product ID from a product ID with attributes
 * Alternate: simply (int) the product id
 * @param string|int $uprid ie: '11:abcdef12345'
 * @return int
 */
function zen_get_prid(string|int $uprid): int
{
    return (int)$uprid;
//    $pieces = explode(':', $uprid);
//    return (int)$pieces[0];
}

/**
 * @param int|string $product_id (while a hashed string is accepted, only the (int) portion is used)
 * Check if product_id exists in database
 */
function zen_products_id_valid(int|string $product_id): bool
{
    return (new Product((int)$product_id))->isValid();
}

/**
 * Return a product's name.
 *
 * @param int $product_id The product id of the product who's name we want
 * @param int $language_id The language id to use. Defaults to current language
 */
function zen_get_products_name($product_id, $language_id = null): string
{
    $product = (new Product((int)$product_id))->getDataForLanguage($language_id);
    return $product['products_name'] ?? '';
}

/**
 * lookup attributes model
 * @param int $product_id
 */
function zen_get_products_model($product_id): string
{
    return (new Product((int)$product_id))->get('products_model') ?? '';
}

/**
 * Get the status of a product
 * @param int $product_id
 */
function zen_get_products_status($product_id): int
{
    return (new Product((int)$product_id))->status();
}

/**
 * check if linked
 * @TODO - check to see whether 'true'/'false' string responses can be changed to boolean
 *
 * @param int $product_id
 */
function zen_get_product_is_linked($product_id, $show_count = 'false')
{
    if ($show_count === true || $show_count === 'true') {
        return (new Product((int)$product_id))->get('linked_categories_count');
    }
    return (new Product((int)$product_id))->isLinked() ? 'true' : 'false';
}

/**
 * Return a product's stock-on-hand
 *
 * @param int $products_id The product id of the product whose stock we want
 */
function zen_get_products_stock($products_id): int|float
{
    global $zco_notifier;

    // Give an observer the chance to modify this function's return value.
    $products_quantity = 0;
    $quantity_handled = false;
    $zco_notifier->notify(
        'ZEN_GET_PRODUCTS_STOCK',
        $products_id,
        $products_quantity,
        $quantity_handled
    );
    if ($quantity_handled) {
        return $products_quantity;
    }

    return (new Product(zen_get_prid($products_id)))->getProductQuantity();
}

/**
 * Check if the required stock is available.
 * If insufficent stock is available return an out of stock message
 *
 * @param int $products_id The product id of the product whose stock is to be checked
 * @param int $products_quantity Quantity to compare against
 */
function zen_check_stock($products_id, $products_quantity): string
{
    global $zco_notifier;

    $stock_left = zen_get_products_stock($products_id) - $products_quantity;

    // Give an observer the opportunity to change the out-of-stock message.
    $the_message = '';
    if ($stock_left < 0) {
        $out_of_stock_message = STOCK_MARK_PRODUCT_OUT_OF_STOCK;
        $zco_notifier->notify(
            'ZEN_CHECK_STOCK_MESSAGE',
            [
                $products_id,
                $products_quantity
            ],
            $out_of_stock_message
        );
        $the_message = '<span class="markProductOutOfStock">' . $out_of_stock_message . '</span>';
    }
    return $the_message;
}

/**
 * Return a product's manufacturer's name, from ID
 * @param int $product_id
 * @return string
 */
function zen_get_products_manufacturers_name($product_id): string
{
    return (new Product((int)$product_id))->get('manufacturers_name') ?? '';
}

/**
 * Return a product's manufacturer's image, from Prod ID
 * @param int $product_id
 * @return string
 */
function zen_get_products_manufacturers_image($product_id): string
{
    return (new Product((int)$product_id))->get('manufacturers_image') ?? '';
}

/**
 * Return a product's manufacturer's id
 * @param int $product_id
 * @return int
 */
function zen_get_products_manufacturers_id($product_id): int
{
    return (new Product((int)$product_id))->get('manufacturers_id') ?? 0;
}

/**
 * @param int $product_id
 * @param int $language_id
 * @return string
 */
function zen_get_products_url($product_id, $language_id): string
{
    $product = (new Product((int)$product_id))->getDataForLanguage($language_id);
    return $product['products_url'] ?? '';
}

/**
 * Return product description, based on specified language (or current lang if not specified)
 * @param int $product_id
 * @param int $language_id
 * @return string
 */
function zen_get_products_description($product_id, $language_id = null): string
{
    global $zco_notifier;

    $product = new Product((int)$product_id);
    $data = $product->getDataForLanguage($language_id);

    //Allow an observer to modify the description
    $zco_notifier->notify('NOTIFY_GET_PRODUCTS_DESCRIPTION', $product_id, $data);
    return $data['products_description'] ?? '';
}

/**
 * look up the product type from product_id and return an info page name (for template/page handling)
 * @param int $product_id
 * @return string
 */
function zen_get_info_page($product_id): string
{
    return (new Product((int)$product_id))->getInfoPage();
}

/**
 * get products_type for specified $product_id
 * @param int $product_id
 * @return int
 */
function zen_get_products_type($product_id): int
{
    return (new Product((int)$product_id))->get('products_type') ?? 1;
}

/**
 * look up a products image and send back the image's IMG tag
 * @param int $product_id
 * @param int $width
 * @param int $height
 * @return string
 */
function zen_get_products_image($product_id, $width = SMALL_IMAGE_WIDTH, $height = SMALL_IMAGE_HEIGHT): string
{
    $image = (new Product((int)$product_id))->get('products_image') ?? '';
    if (empty($image)) {
        return '';
    }

    if (IS_ADMIN_FLAG === true) {
        return $image;
    }
    return zen_image(DIR_WS_IMAGES . $image, zen_get_products_name($product_id), $width, $height);
}

/**
 * look up whether a product is virtual
 * @param int $product_id
 * @return bool
 */
function zen_get_products_virtual($product_id): bool
{
    return (new Product((int)$product_id))->isVirtual();
}

/**
 * Look up whether the given product ID is allowed to be added to cart, according to product-type switches set in Admin
 * @param int|string $product_id  (while a hashed string is accepted, only the (int) portion is used)
 * @return string Y|N
 */
function zen_get_products_allow_add_to_cart($product_id): string
{
    return (new Product((int)$product_id))->allowsAddToCart() ? 'Y' : 'N';
}

/**
 * build configuration_key based on product type and return its value
 * example: To get the settings for metatags_products_name_status for a product use:
 * zen_get_show_product_switch($_GET['pID'], 'metatags_products_name_status')
 * the product is looked up for the products_type which then builds the configuration_key example:
 * SHOW_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS
 * the value of the configuration_key is then returned
 * NOTE: keys are looked up first in the product_type_layout table and if not found looked up in the configuration table.
 */
function zen_get_show_product_switch($lookup, $field, $prefix = 'SHOW_', $suffix = '_INFO', $field_prefix = '_', $field_suffix = ''): string
{
    global $db;
    $keyName = zen_get_show_product_switch_name($lookup, $field, $prefix, $suffix, $field_prefix, $field_suffix);
    $sql = "SELECT configuration_key, configuration_value FROM " . TABLE_PRODUCT_TYPE_LAYOUT . " WHERE configuration_key = '" . zen_db_input($keyName) . "'";
    $zv_key_value = $db->Execute($sql, 1);

    if (!$zv_key_value->EOF) {
        return $zv_key_value->fields['configuration_value'];
    }

    $sql = "SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . zen_db_input($keyName) . "'";
    $zv_key_value = $db->Execute($sql, 1);
    if (!$zv_key_value->EOF) {
        return $zv_key_value->fields['configuration_value'];
    }
    return '';
}

/**
 * return switch name
 */
function zen_get_show_product_switch_name($lookup, $field, $prefix = 'SHOW_', $suffix = '_INFO', $field_prefix = '_', $field_suffix = ''): string
{
    $type_handler = (new Product((int)$lookup))->getTypeHandler();

    return strtoupper($prefix . $type_handler . $suffix . $field_prefix . $field . $field_suffix);
}

/**
 * Look up whether a product is always free shipping
 * @param int $product_id
 */
function zen_get_product_is_always_free_shipping($product_id): bool
{
    return (new Product((int)$product_id))->isAlwaysFreeShipping();
}

/**
 * Return any field from products or products_description table.
 *
 * @param int $product_id
 * @param string $what_field
 * @param int $language ID
 *
 * @deprecated use Product class ->get($what_field) instead
 */
function zen_products_lookup($product_id, $what_field = 'products_name', $language = null): mixed
{
    $product = new Product((int)$product_id);
    $data = $product->getDataForLanguage($language);
    if (empty($data) || !array_key_exists($what_field, $data)) {
        return '';
    }
    return $data[$what_field];
}

/**
 * Lookup and return product's master_categories_id
 * @param int $product_id
 * @return mixed|int
 */
function zen_get_parent_category_id($product_id): int|string
{
    return (new Product((int)$product_id))->get('master_categories_id') ?? '';
}

/**
 * @TODO - check to see whether true/false string responses can be changed to boolean
 * check if products has quantity-discounts defined
 * @param int $product_id
 * @return string
 */
function zen_has_product_discounts($product_id)
{
    global $db;

    $check_discount_query = "SELECT products_id FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE products_id=" . (int)$product_id;
    $check_discount = $db->Execute($check_discount_query, 1);

    // @TODO - check calling references in application code to see whether true/false string responses can be changed to boolean
    return (!$check_discount->EOF) ? 'true' : 'false';
}

/**
 * Check if a product in the catalogue has a special price defined or not.
 *
 * @param int $product_id
 * @return boolean
 */
function zen_has_product_specials(int $product_id): bool
{
    global $db;
    $result = $db->Execute('SELECT products_id FROM ' . TABLE_SPECIALS . " WHERE products_id = $product_id", 1);
    return !$result->EOF;
}

/**
 * Set the status of a product.
 * Used for toggling
 *
 * @param int $product_id
 * @param int $status
 */
function zen_set_product_status($product_id, $status): void
{
    global $db;
    $db->Execute(
        "UPDATE " . TABLE_PRODUCTS . "
            SET products_status = " . (int)$status . ",
                products_last_modified = now()
          WHERE products_id = " . (int)$product_id . "
          LIMIT 1"
    );
}

/**
 * @TODO - can the ptc string 'true' be changed to boolean?
 * @param int $product_id
 * @param string $ptc
 */
function zen_remove_product($product_id, $ptc = 'true'): void
{
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT', [], $product_id, $ptc);

    $product_id = (int)$product_id;
    $product_image = $db->Execute(
        "SELECT products_image
           FROM " . TABLE_PRODUCTS . "
          WHERE products_id = $product_id
            AND products_image IS NOT NULL
            AND products_image != ''
            AND products_image NOT LIKE '%" . zen_db_input(PRODUCTS_IMAGE_NO_IMAGE) . "'
          LIMIT 1"
    );

    if (!$product_image->EOF) {
        $duplicate_image = $db->Execute(
            "SELECT COUNT(*) as total
               FROM " . TABLE_PRODUCTS . "
              WHERE products_image = '" . zen_db_input($product_image->fields['products_image']) . "'"
        );

        if ($duplicate_image->fields['total'] < 2) {
            $products_image = $product_image->fields['products_image'];
            $image_parts = pathinfo($products_image);
            $products_image_extension = '.' . $image_parts['extension'];
            $products_image_base = $image_parts['dirname'] . DIRECTORY_SEPARATOR  . $image_parts['filename'];

            $filename_medium = 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extension;
            $filename_large = 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extension;

            if (file_exists(DIR_FS_CATALOG_IMAGES . $products_image)) {
                @unlink(DIR_FS_CATALOG_IMAGES . $products_image);
            }
            if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_medium)) {
                @unlink(DIR_FS_CATALOG_IMAGES . $filename_medium);
            }
            if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_large)) {
                @unlink(DIR_FS_CATALOG_IMAGES . $filename_large);
            }
        }
    }

    $db->Execute("DELETE FROM " . TABLE_SPECIALS . " WHERE products_id = $product_id");

    $db->Execute("DELETE FROM " . TABLE_PRODUCTS . " WHERE products_id = $product_id LIMIT 1");

//    if ($ptc == 'true') {
    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = $product_id");
//    }

    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE products_id = $product_id");

    $db->Execute("DELETE FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE products_id = $product_id");

    zen_products_attributes_download_delete($product_id);

    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = $product_id");

    $db->Execute("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE products_id LIKE '$product_id:%'");

    $db->Execute("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE products_id LIKE '$product_id:%'");

    $product_reviews = $db->Execute(
        "SELECT reviews_id
           FROM " . TABLE_REVIEWS . "
          WHERE products_id = $product_id"
    );
    foreach ($product_reviews as $row) {
        $db->Execute("DELETE FROM " . TABLE_REVIEWS_DESCRIPTION . " WHERE reviews_id = " . $row['reviews_id']);
    }

    $db->Execute("DELETE FROM " . TABLE_REVIEWS . " WHERE products_id = $product_id");

    $db->Execute("DELETE FROM " . TABLE_FEATURED . " WHERE products_id = $product_id");

    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE products_id = $product_id");

    $db->Execute("DELETE FROM " . TABLE_COUPON_RESTRICT . " WHERE product_id = $product_id");

    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . " WHERE products_id = $product_id");

    $db->Execute("DELETE FROM " . TABLE_COUNT_PRODUCT_VIEWS . " WHERE product_id = $product_id");

    zen_record_admin_activity("Deleted product $product_id from database via admin console.", 'warning');
}

/**
 * Remove downloads (if any) from specified product
 *
 * @param int $product_id
 */
function zen_products_attributes_download_delete($product_id): void
{
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_PRODUCTS_ATTRIBUTES_DOWNLOAD_DELETE', [], $product_id);

    $results = $db->Execute("SELECT products_attributes_id FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id= " . (int)$product_id);
    foreach ($results as $row) {
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE products_attributes_id= " . (int)$row['products_attributes_id']);
    }
}

/**
 * Copy specials pricing from one product to another.
 *
 * @param int $copy_from Source products_id
 * @param int $copy_to   Target products_id
 * @return bool Indicates whether there was a special on $copy_from or not.
 */
function zen_copy_specials_to_product(int $copy_from, int $copy_to): bool
{
    global $db;

    // Fetch existing special for $copy_from, if any.
    $from_result = $db->Execute('SELECT * FROM ' . TABLE_SPECIALS . " WHERE products_id = $copy_from");
    if ($from_result->EOF) {
        return false;
    }

    // Take the data row, modified ready to insert/update
    $sql_data = $from_result->fields;
    unset($sql_data['specials_id']);
    $sql_data['products_id'] = $copy_to;

    // Test for existing special for $copy_to, and insert/update as required.
    $result = $db->Execute('SELECT products_id FROM ' . TABLE_SPECIALS . " WHERE products_id = $copy_to", 1);
    if ($result->EOF) {
        // Insert new specials row
        zen_db_perform(TABLE_SPECIALS, $sql_data);
    } else {
        // Update existing specials row
        zen_db_perform(TABLE_SPECIALS, $sql_data, 'update', "products_id = $copy_to");
    }

    return true;
}

/**
 * copy quantity-discounts from one product to another
 * @param int $copy_from
 * @param int $copy_to
 * @return false on failure
 */
function zen_copy_discounts_to_product($copy_from, $copy_to): bool
{
    global $db;

    $copy_from = (int)$copy_from;
    $check_discount_type_query = "SELECT products_discount_type, products_discount_type_from, products_mixed_discount_quantity FROM " . TABLE_PRODUCTS . " WHERE products_id = $copy_from";
    $check_discount_type = $db->Execute($check_discount_type_query, 1);
    if ($check_discount_type->EOF) {
        return false;
    }

    $copy_to = (int)$copy_to;
    $db->Execute(
        "UPDATE " . TABLE_PRODUCTS . "
            SET products_discount_type = " . $check_discount_type->fields['products_discount_type'] . ",
                products_discount_type_from = " . $check_discount_type->fields['products_discount_type_from'] . ",
                products_mixed_discount_quantity = " . $check_discount_type->fields['products_mixed_discount_quantity'] . "
          WHERE products_id = $copy_to",
         1
    );

    $check_discount_query = "SELECT * FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE products_id = $copy_from ORDER BY discount_id";
    $results = $db->Execute($check_discount_query);
    $cnt_discount = 1;
    foreach ($results as $result) {
        $db->Execute(
            "INSERT INTO " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                (discount_id, products_id, discount_qty, discount_price, discount_price_w)
             VALUES
                ($cnt_discount, $copy_to, " . $result['discount_qty'] . ", " . $result['discount_price'] . ", '" . $result['discount_price_w'] . "')"
        );
        $cnt_discount++;
    }

    return true;
}

function zen_products_sort_order($includeOrderBy = true): string
{
    switch (PRODUCT_INFO_PREVIOUS_NEXT_SORT) {
        case (0):
            $productSort = "LPAD(p.products_id,11,'0')";
            $productSort = 'p.products_id';
            break;
        case (1):
            $productSort = 'pd.products_name';
            break;
        case (2):
            $productSort = 'p.products_model';
            break;
        case (3):
            $productSort = 'p.products_price_sorter, pd.products_name';
            break;
        case (4):
            $productSort = 'p.products_price_sorter, p.products_model';
            break;
        case (5):
            $productSort = 'pd.products_name, p.products_model';
            break;
        case (6):
            $productSort = "LPAD(p.products_sort_order,11,'0'), pd.products_name";
            $productSort = 'products_sort_order, pd.products_name';
            break;
        default:
            $productSort = 'pd.products_name';
            break;
    }
    if ($includeOrderBy) {
        return ' ORDER BY ' . $productSort;
    }
    return $productSort;
}
