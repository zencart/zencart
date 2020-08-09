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

/**
 *  Check if product has attributes
 */
  function zen_has_product_attributes($products_id, $not_readonly = 'true') {
    global $db;

    if (PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' and $not_readonly == 'true') {
      // don't include READONLY attributes to determin if attributes must be selected to add to cart
      $attributes_query = "select pa.products_attributes_id
                           from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_OPTIONS . " po on pa.options_id = po.products_options_id
                           where pa.products_id = '" . (int)$products_id . "' and po.products_options_type != '" . PRODUCTS_OPTIONS_TYPE_READONLY . "' limit 1";
    } else {
      // regardless of READONLY attributes no add to cart buttons
      $attributes_query = "select pa.products_attributes_id
                           from " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                           where pa.products_id = '" . (int)$products_id . "' limit 1";
    }

    $attributes = $db->Execute($attributes_query);

    return $attributes->recordCount() > 0 && $attributes->fields['products_attributes_id'] > 0;
  }

/**
 *  Check if specified product has attributes which require selection before adding product to the cart.
 *  This is used by various parts of the code to determine whether to allow for add-to-cart actions
 *  since adding a product without selecting attributes could lead to undesired basket contents.
 *
 *  @param integer $products_id
 *  @return integer
 */
  function zen_requires_attribute_selection($products_id) {
    global $db, $zco_notifier;

    $noDoubles = array();
    $noDoubles[] = PRODUCTS_OPTIONS_TYPE_RADIO;
    $noDoubles[] = PRODUCTS_OPTIONS_TYPE_SELECT;

    $noSingles = array();
    $noSingles[] = PRODUCTS_OPTIONS_TYPE_CHECKBOX;
    $noSingles[] = PRODUCTS_OPTIONS_TYPE_FILE;
    $noSingles[] = PRODUCTS_OPTIONS_TYPE_TEXT;
    if (PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '0') {
      $noSingles[] = PRODUCTS_OPTIONS_TYPE_READONLY;
    }

    $query = "select products_options_id, count(pa.options_values_id) as number_of_choices, po.products_options_type as options_type
              from " . TABLE_PRODUCTS_ATTRIBUTES . " pa
              left join " . TABLE_PRODUCTS_OPTIONS . " po on pa.options_id = po.products_options_id
              where pa.products_id = " . (int)$products_id . "
              and po.language_id = " . (int)$_SESSION['languages_id'] . "
              group by products_options_id, options_type";

    $zco_notifier->notify('NOTIFY_FUNCTIONS_LOOKUPS_REQUIRES_ATTRIBUTES_SELECTION', '', $query, $noSingles, $noDoubles);

    $result = $db->Execute($query);

    // if no attributes found, return false
    if ($result->RecordCount() == 0) return false;

    // loop through the results, auditing for whether each kind of attribute requires "selection" or not
    // return whether selections must be made, so a more-info button needs to be presented, if true
    foreach($result as $row => $field) {
      // if there's more than one for any $noDoubles type, can't add from listing
      if (in_array($field['options_type'], $noDoubles) && $field['number_of_choices'] > 1) {
        return true;
      }
      // if there's any type from $noSingles, can't add from listing
      if (in_array($field['options_type'], $noSingles)) {
        return true;
      }
    }

    // return false to indicate that defaults can be automatically added by just using a buy-now button
    return false;
  }

/*
 *  Check if option name is not expected to have an option value (ie. text field, or File upload field)
 */
  function zen_option_name_base_expects_no_values($option_name_id) {
    global $db, $zco_notifier;

    $option_name_no_value = true;
    if (!is_array($option_name_id)) {
      $option_name_id = array($option_name_id);
    }

    $sql = "SELECT products_options_type FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_id :option_name_id:";
    if (sizeof($option_name_id) > 1 ) {
      $sql2 = 'in (';
      foreach($option_name_id as $option_id) {
        $sql2 .= ':option_id:,';
        $sql2 = $db->bindVars($sql2, ':option_id:', $option_id, 'integer');
      }
      $sql2 = rtrim($sql2, ','); // Need to remove the final comma off of the above.
      $sql2 .= ')';
    } else {
      $sql2 = ' = :option_id:';
      $sql2 = $db->bindVars($sql2, ':option_id:', $option_name_id[0], 'integer');
    }

    $sql = $db->bindVars($sql, ':option_name_id:', $sql2, 'noquotestring');

    $sql_result = $db->Execute($sql);

    foreach($sql_result as $opt_type) {

      $test_var = true; // Set to false in observer if the name is not supposed to have a value associated
      $zco_notifier->notify('FUNCTIONS_LOOKUPS_OPTION_NAME_NO_VALUES_OPT_TYPE', $opt_type, $test_var);

      if ($test_var && $opt_type['products_options_type'] != PRODUCTS_OPTIONS_TYPE_TEXT && $opt_type['products_options_type'] != PRODUCTS_OPTIONS_TYPE_FILE) {
        $option_name_no_value = false;
        break;
      }
    }

    return $option_name_no_value;
  }

/**
 *  Check if product has attributes values
 */
  function zen_has_product_attributes_values($products_id) {
    global $db;

    // -----
    // Allow a watching observer to override this function's return value.
    //
    $value_to_return = '';
    $GLOBALS['zco_notifier']->notify('NOTIFY_ZEN_HAS_PRODUCT_ATTRIBUTES_VALUES', $products_id, $value_to_return);
    if ($value_to_return !== '') {
        return $value_to_return;
    }

    $attributes_query = "select count(options_values_price) as total
                         from " . TABLE_PRODUCTS_ATTRIBUTES . "
                         where products_id = " . (int)$products_id . "
                         and options_values_price <> 0";

    $attributes = $db->Execute($attributes_query);

    return ($attributes->fields['total'] != 0);
  }


/**
 * check if Product is set to use downloads
 * does not validate download filename
 */
function zen_has_product_attributes_downloads_status($products_id) {
    if (!defined('DOWNLOAD_ENABLED') || DOWNLOAD_ENABLED != 'true') {
        return false;
    }

    $query = "select pad.products_attributes_id
              from " . TABLE_PRODUCTS_ATTRIBUTES . " pa
              inner join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
              on pad.products_attributes_id = pa.products_attributes_id
              where pa.products_id = " . (int) $products_id;

    global $db;
    return ($db->Execute($query)->RecordCount() > 0);
}


/*
 * Return attributes products_options_sort_order
 * TABLE: PRODUCTS_ATTRIBUTES
 */
  function zen_get_attributes_sort_order($products_id, $options_id, $options_values_id) {
    global $db;
      $check = $db->Execute("select products_options_sort_order
                             from " . TABLE_PRODUCTS_ATTRIBUTES . "
                             where products_id = '" . (int)$products_id . "'
                             and options_id = '" . (int)$options_id . "'
                             and options_values_id = '" . (int)$options_values_id . "' limit 1");

      return $check->fields['products_options_sort_order'];
  }

/*
 *  return attributes products_options_sort_order
 *  TABLES: PRODUCTS_OPTIONS, PRODUCTS_ATTRIBUTES
 */
  function zen_get_attributes_options_sort_order($products_id, $options_id, $options_values_id, $lang_num = '') {
    global $db;
      if ($lang_num == '') $lang_num = (int)$_SESSION['languages_id'];
      $check = $db->Execute("select products_options_sort_order
                             from " . TABLE_PRODUCTS_OPTIONS . "
                             where products_options_id = '" . (int)$options_id . "' and language_id = '" . $lang_num . "' limit 1");

      $check_options_id = $db->Execute("select products_id, options_id, options_values_id, products_options_sort_order
                             from " . TABLE_PRODUCTS_ATTRIBUTES . "
                             where products_id='" . (int)$products_id . "'
                             and options_id='" . (int)$options_id . "'
                             and options_values_id = '" . (int)$options_values_id . "' limit 1");


      return $check->fields['products_options_sort_order'] . '.' . str_pad($check_options_id->fields['products_options_sort_order'],5,'0',STR_PAD_LEFT);
  }

/*
 *  check if attribute is display only
 */
  function zen_get_attributes_valid($product_id, $option, $value) {
    global $db;

// regular attribute validation
    $check_attributes = $db->Execute("select attributes_display_only, attributes_required from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . (int)$product_id . "' and options_id='" . (int)$option . "' and options_values_id='" . (int)$value . "'");

    $check_valid = true;

// display only cannot be selected
    if (!$check_attributes->EOF && $check_attributes->fields['attributes_display_only'] == '1') {
      $check_valid = false;
    }

// text required validation
    if (preg_match('/^txt_/', $option)) {
      $check_attributes = $db->Execute("select attributes_display_only, attributes_required from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . (int)$product_id . "' and options_id='" . (int)preg_replace('/txt_/', '', $option) . "' and options_values_id='0'");
// text cannot be blank
      if ($check_attributes->fields['attributes_required'] == '1' && (empty($value) && !is_numeric($value))) {
        $check_valid = false;
      }
    }

    return $check_valid;
  }

/*
 * Return Options_Name from ID
 */

  function zen_options_name($options_id) {
    global $db;

    $options_id = str_replace('txt_','',$options_id);

    $options_values = $db->Execute("select products_options_name
                                    from " . TABLE_PRODUCTS_OPTIONS . "
                                    where products_options_id = '" . (int)$options_id . "'
                                    and language_id = '" . (int)$_SESSION['languages_id'] . "'");

    return $options_values->fields['products_options_name'];
  }

/*
 * Return Options_values_name from value-ID
 */
  function zen_values_name($values_id) {
    global $db;

    $values_values = $db->Execute("select products_options_values_name
                                   from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   where products_options_values_id = '" . (int)$values_id . "'
                                   and language_id = '" . (int)$_SESSION['languages_id'] . "'");

    return $values_values->fields['products_options_values_name'];
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
