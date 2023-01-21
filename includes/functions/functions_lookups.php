<?php
/**
 * functions_lookups.php
 * Lookup Functions for various core activities related to countries, prices, products, product types, etc
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Aug 09 Modified in v1.5.8-alpha $
 */


/**
 * get the type_handler value for the specified product_type
 * @param int $product_type
 */
function zen_get_handler_from_type($product_type)
{
    global $db;

    $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = " . (int)$product_type;
    $handler = $db->Execute($sql);
    if ($handler->EOF) return 'ERROR: Invalid type_handler. Your product_type settings are wrong, incomplete, or damaged.';
    return $handler->fields['type_handler'];
}


/*
 * List manufacturers (returned in an array)
 */
function zen_get_manufacturers($manufacturers_array = array(), $have_products = false)
{
    global $db;
    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    if ($have_products == true) {
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
    if ($configuration_query->EOF) {
      return '<span class="lookupAttention">' . $lookup . '</span>';
    }
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
 *  Look up whether to show prices, based on customer-authorization levels
 */
function zen_check_show_prices(): bool
{
    if (
        !(CUSTOMERS_APPROVAL == '2' && !zen_is_logged_in())
        && !(
            (CUSTOMERS_APPROVAL_AUTHORIZATION > 0 && CUSTOMERS_APPROVAL_AUTHORIZATION < 3)
            && ($_SESSION['customers_authorization'] > '0' || !zen_is_logged_in())
        )
        && STORE_STATUS != '1'
    ) {
        return true;
    }

    return false;
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

