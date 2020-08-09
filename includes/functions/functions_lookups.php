<?php
/**
 * functions_lookups.php
 * Lookup Functions for various core activities related to countries, prices, products, product types, etc
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 */

/**
 * Returns an array with countries
 *
 * @param int If set limits to a single country
 * @param boolean If true adds the iso codes to the array
 */
  function zen_get_countries($countries_id = '', $with_iso_codes = false, $activeOnly = TRUE) {
    global $db;
    $countries_array = array();
    if (zen_not_null($countries_id)) {
      $countries_array['countries_name'] = '';
      $countries = "select countries_name, countries_iso_code_2, countries_iso_code_3
                    from " . TABLE_COUNTRIES . "
                    where countries_id = '" . (int)$countries_id . "'";
      if ($activeOnly) $countries .= " and status != 0 ";
      $countries .= " order by countries_name";
      $countries_values = $db->Execute($countries);

      if ($with_iso_codes == true) {
        $countries_array['countries_iso_code_2'] = '';
        $countries_array['countries_iso_code_3'] = '';
        if (!$countries_values->EOF) {
          $countries_array = array('countries_name' => $countries_values->fields['countries_name'],
                                   'countries_iso_code_2' => $countries_values->fields['countries_iso_code_2'],
                                   'countries_iso_code_3' => $countries_values->fields['countries_iso_code_3']);
        }
      } else {
        if (!$countries_values->EOF) $countries_array = array('countries_name' => $countries_values->fields['countries_name']);
      }
    } else {
      $countries = "select countries_id, countries_name
                    from " . TABLE_COUNTRIES . " ";
      if ($activeOnly) $countries .= " where status != 0 ";
      $countries .= " order by countries_name";
      $countries_values = $db->Execute($countries);
      while (!$countries_values->EOF) {
        $countries_array[] = array('countries_id' => $countries_values->fields['countries_id'],
                                   'countries_name' => $countries_values->fields['countries_name']);
        $countries_values->MoveNext();
      }
    }

    return $countries_array;
  }

/*
 * List manufacturers (returned in an array)
 */
  function zen_get_manufacturers($manufacturers_array = array(), $have_products = false) {
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

/*
 *  configuration key value lookup
 *  TABLE: configuration
 */
function zen_get_configuration_key_value($lookup)
{
    global $db;
    $configuration_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . $lookup . "' LIMIT 1");
    $lookup_value = ($configuration_query->EOF) ? '' : $configuration_query->fields['configuration_value'];
    if (empty($lookup_value)) {
        $lookup_value = '<span class="lookupAttention">' . $lookup . '</span>';
    }
    return $lookup_value;
}

/*
 * Get accepted credit cards
 * There needs to be a define on the accepted credit card in the language file credit_cards.php example: TEXT_CC_ENABLED_VISA
 */
  function zen_get_cc_enabled($text_image = 'TEXT_', $cc_seperate = ' ', $cc_make_columns = 0) {
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


/*
 * configuration key value lookup in TABLE_PRODUCT_TYPE_LAYOUT
 * Used to determine keys/flags used on a per-product-type basis for template-use, etc
 */
  function zen_get_configuration_key_value_layout($lookup, $type=1) {
    global $db;
    $configuration_query= $db->Execute("select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . $lookup . "' and product_type_id='". (int)$type . "'");
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( !($lookup_value) ) {
      $lookup_value='<span class="lookupAttention">' . $lookup . '</span>';
    }
    return $lookup_value;
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



/*
 * This function, added to the storefront in zc1.5.6, mimics the like-named admin function in
 * support of plugins that "span" both the storefront and admin.
 *
 * Returns the "name" associated with the specified orders_status_id.
 *
 */
function zen_get_orders_status_name($orders_status_id, $language_id = '')
{
    if ($language_id == '') {
        $language_id = $_SESSION['languages_id'];
    }
    $orders_status = $GLOBALS['db']->Execute(
        "SELECT orders_status_name
           FROM " . TABLE_ORDERS_STATUS . "
          WHERE orders_status_id = " . (int)$orders_status_id . "
            AND language_id = " . (int)$language_id . "
          LIMIT 1"
    );
    return ($orders_status->EOF) ? '' : $orders_status->fields['orders_status_name'];
}
