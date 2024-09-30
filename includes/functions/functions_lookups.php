<?php
/**
 * functions_lookups.php
 * Lookup Functions for various core activities related to countries, prices, products, product types, etc
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 24 Modified in v2.1.0-beta1 $
 */


/**
 * get the type_handler value for the specified product_type
 * @param int $product_type
 */
function zen_get_handler_from_type($product_type): string
{
    global $db;

    // this is a fallback safety to protect against damaged (inaccessible) data caused by incorrect code in custom product types
    if ((int)$product_type === 0) {
        $product_type = 1;
    }

    $sql = "SELECT type_handler FROM " . TABLE_PRODUCT_TYPES . " WHERE type_id = " . (int)$product_type;
    $handler = $db->Execute($sql);
    if ($handler->EOF) {
        throw new ValueError('ERROR: Invalid type_handler. Your product_type settings are wrong, incomplete, or damaged.');
    }
    return $handler->fields['type_handler'];
}


/*
 * List manufacturers (returned in an array)
 */
function zen_get_manufacturers($manufacturers_array = [], $only_those_with_products = false)
{
    global $db;
    if (!is_array($manufacturers_array)) $manufacturers_array = [];

    if (!empty($only_those_with_products)) {
        $manufacturers_query = "SELECT DISTINCT m.manufacturers_id, m.manufacturers_name
                              FROM " . TABLE_MANUFACTURERS . " m
                              LEFT JOIN " . TABLE_PRODUCTS . " p ON m.manufacturers_id = p.manufacturers_id
                              WHERE p.products_status = 1
                              AND p.products_quantity > 0
                              ORDER BY m.manufacturers_name";
    } else {
        $manufacturers_query = "SELECT manufacturers_id, manufacturers_name
                              FROM " . TABLE_MANUFACTURERS . "
                              ORDER BY manufacturers_name";
    }

    $manufacturers = $db->Execute($manufacturers_query);

    foreach ($manufacturers as $manufacturer) {
        $manufacturers_array[] = array(
            'id' => $manufacturer['manufacturers_id'],
            'text' => $manufacturer['manufacturers_name']
        );
    }

    return $manufacturers_array;
}

////
// Return the manufacturers URL in the needed language
// TABLES: manufacturers_info
function zen_get_manufacturer_url($manufacturer_id, $language_id)
{
    global $db;
    $manufacturer = $db->Execute("SELECT manufacturers_url
                                  FROM " . TABLE_MANUFACTURERS_INFO . "
                                  WHERE manufacturers_id = " . (int)$manufacturer_id . "
                                  AND languages_id = " . (int)$language_id);
    if ($manufacturer->EOF) return '';
    return $manufacturer->fields['manufacturers_url'];
}


/**
 *  configuration key value lookup
 */
function zen_get_configuration_key_value($lookup)
{
    global $db;
    $configuration_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . zen_db_input($lookup) . "' LIMIT 1");
    $lookup_value = ($configuration_query->EOF) ? '' : $configuration_query->fields['configuration_value'];
    if (empty($lookup_value)) {
        $lookup_value = '<span class="lookupAttention">' . $lookup . '</span>';
    }
    return $lookup_value;
}

/**
 * Product Types -- configuration key value lookup in TABLE_PRODUCT_TYPE_LAYOUT
 * Used to determine keys/flags used on a per-product-type basis for template-use, etc
 */
function zen_get_configuration_key_value_layout($lookup, $type = 1)
{
    global $db;
    $configuration_query = $db->Execute("select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . zen_db_input($lookup) . "' and product_type_id='" . (int)$type . "'");
    $lookup_value = $configuration_query->fields['configuration_value'];
    if (!($lookup_value)) {
        $lookup_value = '<span class="lookupAttention">' . $lookup . '</span>';
    }
    return $lookup_value;
}

/*
 * Get accepted credit cards
 * There needs to be a define on the accepted credit card in the language file credit_cards.php example: TEXT_CC_ENABLED_VISA
 */
function zen_get_cc_enabled($text_image = 'TEXT_', $cc_seperate = ' ', $cc_make_columns = 0)
{
    global $db;
    $cc_check_accepted_query = $db->Execute(SQL_CC_ENABLED);
    $cc_check_accepted = '';
    $cc_counter = 0;
    if ($cc_make_columns == 0) {
        while (!$cc_check_accepted_query->EOF) {
            $check_it = $text_image . $cc_check_accepted_query->fields['configuration_key'];
            if (defined($check_it)) {
                $cc_check_accepted .= constant($check_it) . $cc_seperate;
            }
            $cc_check_accepted_query->MoveNext();
        }
    } else {
        // build a table
        $cc_check_accepted = '<table class="ccenabled">' . "\n";
        $cc_check_accepted .= '<tr class="ccenabled">' . "\n";
        while (!$cc_check_accepted_query->EOF) {
            $check_it = $text_image . $cc_check_accepted_query->fields['configuration_key'];
            if (defined($check_it)) {
                $cc_check_accepted .= '<td class="ccenabled">' . constant($check_it) . '</td>' . "\n";
            }
            $cc_check_accepted_query->MoveNext();
            $cc_counter++;
            if ($cc_counter >= $cc_make_columns) {
                $cc_check_accepted .= '</tr>' . "\n" . '<tr class="ccenabled">' . "\n";
                $cc_counter = 0;
            }
        }
        $cc_check_accepted .= '</tr>' . "\n" . '</table>' . "\n";
    }
    return $cc_check_accepted;
}


/**
 *  stop regular behavior based on customer/store settings
 *  Used to disable various activities if store is in an operating mode that should prevent those activities
 */
function zen_run_normal(): bool
{
    $zc_run = false;
    switch (true) {
        case (zen_is_whitelisted_admin_ip()):
            // down for maintenance not for ADMIN
            $zc_run = true;
            break;
        case (DOWN_FOR_MAINTENANCE == 'true'):
            // down for maintenance
            $zc_run = false;
            break;
        case (STORE_STATUS >= 1):
            // showcase no prices
            $zc_run = false;
            break;
        case (CUSTOMERS_APPROVAL == '1' && !zen_is_logged_in()):
            // customer must be logged in to browse
            $zc_run = false;
            break;
        case (CUSTOMERS_APPROVAL == '2' && !zen_is_logged_in()):
            // show room only
            // customer may browse but no prices
            $zc_run = false;
            break;
        case (CUSTOMERS_APPROVAL == '3'):
            // show room only
            $zc_run = false;
            break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && !zen_is_logged_in()):
            // customer must be logged in to browse
            $zc_run = false;
            break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && isset($_SESSION['customers_authorization']) && (int)$_SESSION['customers_authorization'] > 0):
            // customer must be logged in to browse
            $zc_run = false;
            break;
        default:
            // proceed normally
            $zc_run = true;
            break;
    }
    return $zc_run;
}

/**
 * Look up whether to show prices, based on customer-authorization levels
 *
 * Prices are NOT shown if
 *
 * 1. The site's 'Store Status' is '1' (Showcase no prices).
 * 2. 'Customer Shop Status - View Shop and Prices' is '2' (must login to see prices) and a customer is not logged in.
 * 3. 'Customer Approval Status - Authorization Pending' is '1' (Must be Authorized to Browse) or '2' (May browse but no prices unless Authorized) and either
 *    a. A customer IS NOT logged in
 *    b. A customer IS logged in, but their authorization status is neither '0' (Approved) nor '3' (Pending Approval - May browse with prices but may not buy)
 */
function zen_check_show_prices(): bool
{
    if (STORE_STATUS === '1') {
        return false;
    }
    if (CUSTOMERS_APPROVAL === '2' && zen_is_logged_in() === false) {
        return false;
    }
    if (CUSTOMERS_APPROVAL_AUTHORIZATION !== '1' && CUSTOMERS_APPROVAL_AUTHORIZATION !== '2') {
        return true;
    }
    if (zen_is_logged_in() === false || ((int)$_SESSION['customers_authorization'] !== 0 && (int)$_SESSION['customers_authorization'] !== 3)) {
        return false;
    }
    return true;
}

/**
 * check to see if database stored GET terms are in the URL as $_GET parameters
 * This is used to determine which filters should be applied
 * @return bool
 */
function zen_check_url_get_terms()
{
    global $db;
    $sql = "SELECT * FROM " . TABLE_GET_TERMS_TO_FILTER;
    $query_result = $db->Execute($sql);

    foreach ($query_result as $row) {
        if (isset($_GET[$row['get_term_name']]) && zen_not_null($_GET[$row['get_term_name']])) {
            return true;
        }
    }
    return false;
}


/**
 * Returns the "name" associated with the specified orders_status_id.
 * @param int $order_status_id
 * @param int $language_id
 * @return string
 */
function zen_get_orders_status_name(int $order_status_id, int $language_id = 0)
{
    global $db;
    if (empty($language_id)) $language_id = $_SESSION['languages_id'];

    $sql = "SELECT orders_status_name
            FROM " . TABLE_ORDERS_STATUS . "
            WHERE orders_status_id = " . (int)$order_status_id . "
            AND language_id = " . (int)$language_id;
    $result = $db->Execute($sql);

    if ($result->EOF) return '';
    return $result->fields['orders_status_name'];
}

/**
 * @TODO collapse with zen_get_orders_status_name()
 * @param int $order_status_id
 * @param int $language_id
 * @return string
 */
function zen_get_order_status_name(int $order_status_id, int $language_id = 0)
{
    global $db;

    if ($order_status_id < 1) return TEXT_DEFAULT;

    if (empty($language_id)) $language_id = $_SESSION['languages_id'];

    $sql = "SELECT orders_status_name
            FROM " . TABLE_ORDERS_STATUS . "
            WHERE orders_status_id = " . (int)$order_status_id . "
            AND language_id = " . (int)$language_id;
    $result = $db->Execute($sql);
    if ($result->EOF) return 'ERROR: INVALID STATUS ID: ' . (int)$order_status_id;
    return $result->fields['orders_status_name'] . ' [' . (int)$order_status_id . ']';
}


function zen_lookup_admin_menu_language_override(string $lookup_type, ?string $lookup_key, ?string $fallback): ?string
{
    switch ($lookup_type) {
        case 'product_type_name':
            $lookup = strtoupper('PRODUCT_TYPE_NAME_FOR_HANDLER_' . $lookup_key);
            break;
        case 'product_type_layout_title':
            $lookup = strtoupper('PRODUCT_TYPE_LAYOUT_TITLE_FOR_' . $lookup_key);
            break;
        case 'product_type_layout_description':
            $lookup = strtoupper('PRODUCT_TYPE_LAYOUT_DESC_FOR_' . $lookup_key);
            break;
        case 'configuration_key_title':
            $lookup = strtoupper('CFGTITLE_' . $lookup_key);
            break;
        case 'configuration_key_description':
            $lookup = strtoupper('CFGDESC_' . $lookup_key);
            break;
        case 'configuration_group_title':
            $str = $lookup_key;
            $str = preg_replace('/[\s ]+/', '_', $str);
            $str = preg_replace('/[^a-zA-Z0-9_\x80-\xff]/', '', $str);
            $lookup = strtoupper('CFG_GRP_TITLE_' . $str);
            break;
        case 'plugin_name':
            $str = $lookup_key;
            $str = preg_replace('/[\s -]+/', '_', $str);
            $str = preg_replace('/[^a-zA-Z0-9_\x80-\xff]/', '', $str);
            $str = preg_replace('/_+/', '_', $str);
            $lookup = strtoupper('ADMIN_PLUGIN_MANAGER_NAME_FOR_' . $str);
            break;
        case 'plugin_description':
            $str = $lookup_key;
            $str = preg_replace('/[\s -]+/', '_', $str);
            $str = preg_replace('/[^a-zA-Z0-9_\x80-\xff]/', '', $str);
            $str = preg_replace('/_+/', '_', $str);
            $lookup = strtoupper('ADMIN_PLUGIN_MANAGER_DESCRIPTION_FOR_' . $str);
            break;
    }

    if (isset($lookup) && defined($lookup)) {
        return constant($lookup);
    }

    return $fallback;
}
