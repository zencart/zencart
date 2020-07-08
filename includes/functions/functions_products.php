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

    $sql = "SELECT p.products_status, p.*, pd.*
            FROM " . TABLE_PRODUCTS . " p,
            LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd USING (products_id)
            WHERE p.products_id = " . (int)$product_id . "
            AND pd.language_id = " . (int)$language_id . "
            LIMIT 1";
    return $db->Execute($sql);
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
            AND products_date_available != '0001-01-01'
            AND products_date_available IS NOT NULL
            ";

    $results = $db->Execute($sql);

    foreach ($results as $result) {
        zen_set_disabled_upcoming_status($result['products_id'], 1);
    }
}

/**
 * Return a product's category (master_categories_id)
 * @param int $products_id
 * @return int|string
 */
function zen_get_products_category_id($products_id)
{
    global $db;

    $sql = "SELECT products_id, master_categories_id
            FROM " . TABLE_PRODUCTS . "
            WHERE products_id = " . (int)$products_id . "
            LIMIT 1";
    $result = $db->Execute($sql);
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
    $sql = "SELECT categories_id
            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE products_id = " . $product_id;
    if (!empty($exclude) && is_array($exclude)) {
        $sql .= " AND categories_id NOT IN (" . implode(',', $exclude);
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
            WHERE categories_id = " . $category_id . "
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
            WHERE products_id = " . $product_id . "
            AND categories_id != " . $master_category_id;
    $db->Execute($sql);
}
