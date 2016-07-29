<?php
/**
 * functions_lookups.php
 * Lookup Functions for various core activities related to countries, prices, products, product types, etc
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: functions_lookups.php ajeh  Modified in v1.6.0 $
 */

/**
 * Returns an array with countries
 *
 * @param int If set limits to a single country
 * @param boolean If true adds the iso codes to the array
 * @return array
 */
  function zen_get_countries($countries_id = '', $with_iso_codes = false, $activeOnly = TRUE) {
    global $db;
    $countries_array = array();
    if (zen_not_null($countries_id)) {
      $countries_array['countries_name'] = '';
      $countries = "select cn.countries_name, c.countries_iso_code_2, c.countries_iso_code_3
                    from " . TABLE_COUNTRIES . " c, " . TABLE_COUNTRIES_NAME . " cn
                    where c.countries_id = '" . (int)$countries_id . "'
                    and cn.countries_id = c.countries_id
                    and cn.language_id = " . (int)$_SESSION['languages_id'];
      if ($activeOnly) $countries .= " and c.status != 0 ";
      $countries .= " order by cn.countries_name";
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
      $countries = "select c.countries_id, cn.countries_name
                    from " . TABLE_COUNTRIES . " c, " . TABLE_COUNTRIES_NAME . " cn
                    where cn.countries_id = c.countries_id
                    and cn.language_id = " . (int)$_SESSION['languages_id'];
      if ($activeOnly) $countries .= " and c.status != 0 ";
      $countries .= " order by cn.countries_name";
      $countries_values = $db->Execute($countries);
      while (!$countries_values->EOF) {
        $countries_array[] = array('countries_id' => $countries_values->fields['countries_id'],
                                   'countries_name' => $countries_values->fields['countries_name']);
        $countries_values->MoveNext();
      }
    }

    return $countries_array;
  }

/**
 * Returns an array with countries, suitable for zen_draw_pull_down()
 *
 * @param string $default
 * @return array
 */
function zen_get_countries_for_pulldown($default = '') {
    global $db;
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '', 'text' => $default);
    }
    $countries = $db->Execute("select c.countries_id, cn.countries_name
                               from " . TABLE_COUNTRIES . " c, " . TABLE_COUNTRIES_NAME . " cn
                               where cn.countries_id = c.countries_id
                               and cn.language_id = " . (int)$_SESSION['languages_id'] . "
                               order by cn.countries_name");

    while (!$countries->EOF) {
      $countries_array[] = array('id' => $countries->fields['countries_id'],
                                 'text' => $countries->fields['countries_name']);
      $countries->MoveNext();
    }

    return $countries_array;
  }

/*
 * Run zen_get_countries() and return the name for the specified id
 *
 * @param int $country_id
 * @param bool $activeOnly
 * @return string
 */
function zen_get_country_name($country_id, $activeOnly = TRUE) {
    $country_array = zen_get_countries($country_id, FALSE, $activeOnly);
    return $country_array['countries_name'];
  }

/**
 * Run zen_get_countries, but also return the country iso codes
 *
 * @param $countries_id limit to a single country
 * @param bool $activeOnly
 * @return array
 */
  function zen_get_countries_with_iso_codes($countries_id, $activeOnly = TRUE) {
    return zen_get_countries($countries_id, true, $activeOnly);
  }

/**
 * Return the zone (State/Province) name
 *
 * @param int $country_id
 * @param int $zone_id
 * @param string $default_zone Return this string if no result found
 * @return
 */
  function zen_get_zone_name($country_id, $zone_id, $default_zone) {
    global $db;
    $zone_query = "select zone_name
                   from " . TABLE_ZONES . "
                   where zone_country_id = " . (int)$country_id . "
                   and zone_id = " . (int)$zone_id;

    $zone = $db->Execute($zone_query);

    if ($zone->RecordCount()) {
      return $zone->fields['zone_name'];
    } else {
      return $default_zone;
    }
  }

/**
 * Function to retrieve the state/province code (as in FL for Florida etc)
 *
 * @param int $country_id
 * @param int $zone_id
 * @param string $default_zone String returned if no zone found
 * @return string
 */
  function zen_get_zone_code($country_id, $zone_id, $default_zone) {
    global $db;
    $zone_query = "select zone_code
                   from " . TABLE_ZONES . "
                   where zone_country_id = " . (int)$country_id . "
                   and zone_id = " . (int)$zone_id;

    $zone = $db->Execute($zone_query);

    if ($zone->RecordCount() > 0) {
      return $zone->fields['zone_code'];
    } else {
      return $default_zone;
    }
  }

/**
 * Get defined zone name for specified id
 * @param int $geo_zone_id
 * @return string
 */
function zen_get_geo_zone_name($geo_zone_id) {
    global $db;
    $zones = $db->Execute("select geo_zone_name
                           from " . TABLE_GEO_ZONES . "
                           where geo_zone_id = " . (int)$geo_zone_id);

    if ($zones->RecordCount() < 1) {
      $geo_zone_name = $geo_zone_id;
    } else {
      $geo_zone_name = $zones->fields['geo_zone_name'];
    }

    return $geo_zone_name;
  }

/**
 * Return zone class name/title for specified id
 * @param int $zone_class_id
 * @return string
 */
function zen_get_zone_class_title($zone_class_id) {
    global $db;
    if ($zone_class_id == '0') {
      return TEXT_NONE;
    } else {
      $classes = $db->Execute("select geo_zone_name
                               from " . TABLE_GEO_ZONES . "
                               where geo_zone_id = " . (int)$zone_class_id);
      if ($classes->EOF) return '';
      return $classes->fields['geo_zone_name'];
    }
  }

/**
 *  Return whether the specified product id exists in the database
 *
 * @param int $valid_id
 * @return bool
 */
  function zen_products_id_valid($valid_id) {
    global $db;
    $check_valid = $db->Execute("select p.products_id
                                 from " . TABLE_PRODUCTS . " p
                                 where products_id=" . (int)$valid_id . " limit 1");
    return (!$check_valid->EOF);
  }

/**
 * Return a product's name.
 *
 * @param int $product_id id of the product whose name we want
 * @param int $language (optional) defaults to session language
 * @return string
 */
  function zen_get_products_name($product_id, $language = 0) {
    global $db;

    if ($language == 0) $language = $_SESSION['languages_id'];

    $product_query = "select products_name
                      from " . TABLE_PRODUCTS_DESCRIPTION . "
                      where products_id = " . (int)$product_id . "
                      and language_id = " . (int)$language;

    $product = $db->Execute($product_query);
    if ($product->EOF) return '';
    return $product->fields['products_name'];
  }


/**
 * Return a product's stock-on-hand
 *
 * @param int $products_id The product id of the product whose stock we want
 * @return int
*/
  function zen_get_products_stock($products_id) {
    global $db;
    $products_id = zen_get_prid($products_id);
    $stock_query = "select products_quantity
                    from " . TABLE_PRODUCTS . "
                    where products_id = " . (int)$products_id;

    $stock_values = $db->Execute($stock_query);

    return $stock_values->fields['products_quantity'];
  }

/**
 * Check if the required stock is available.
 * If insufficient stock is available return an out of stock message
 *
 * @param int $products_id        product whose stock is to be checked
 * @param int $products_quantity  Quantity to compare against
 * @return string status message
*/
  function zen_check_stock($products_id, $products_quantity) {
    $stock_left = zen_get_products_stock($products_id) - $products_quantity;

    return ($stock_left < 0) ? '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>' : '';
  }

/**
 * List manufacturers (returned in an array)
 *
 * @param array $manufacturers_array
 * @param bool $have_products
 * @return array|string
 */
  function zen_get_manufacturers($manufacturers_array = array(), $have_products = false) {
    global $db;
    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    $sql = "select manufacturers_id, manufacturers_name
            from " . TABLE_MANUFACTURERS . " order by manufacturers_name";
    if ($have_products == true) {
      $sql = "select distinct m.manufacturers_id, m.manufacturers_name
              from " . TABLE_MANUFACTURERS . " m
              left join " . TABLE_PRODUCTS . " p on m.manufacturers_id = p.manufacturers_id
              where p.manufacturers_id = m.manufacturers_id
              and (p.products_status = 1
              and p.products_quantity > 0)
              order by m.manufacturers_name";
    }
    $result = $db->Execute($sql);

    foreach($result as $mfg) {
      $manufacturers_array[] = array('id' => $mfg['manufacturers_id'], 'text' => $mfg['manufacturers_name']);
    }

    return $manufacturers_array;
  }

/**
 * Check whether any attributes are defined for the specified product
 * Usually used to inform whether to display the add-to-cart button or to require MoreInfo to make selections
 *
 * @param int $products_id
 * @param bool $not_readonly This should be true when determining add-to-cart based on attributes
 * @return bool
 */
  function zen_has_product_attributes($products_id, $not_readonly = true) {
    global $db;

    $sql = "select pa.products_attributes_id
            from " . TABLE_PRODUCTS_ATTRIBUTES . " pa
            where pa.products_id = " . (int)$products_id . " limit 1";

    if (PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' and $not_readonly == true) {
      $sql = "select pa.products_attributes_id
              from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_OPTIONS . " po on pa.options_id = po.products_options_id
              where pa.products_id = " . (int)$products_id . " and po.products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_READONLY . " 
              limit 1";
    }

    $attributes = $db->Execute($sql);

    return !($attributes->EOF);
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
    global $db;

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

    // advanced query
    $query = "select products_options_id, count(pa.options_values_id) as number_of_choices, po.products_options_type as options_type
              from " . TABLE_PRODUCTS_ATTRIBUTES . " pa
              left join " . TABLE_PRODUCTS_OPTIONS . " po on pa.options_id = po.products_options_id
              where pa.products_id = " . (int)$products_id . "
              and po.language_id = " . (int)$_SESSION['languages_id'] . "
              group by products_options_id, options_type";
    $result = $db->Execute($query);

    // if no attributes found, return 0
    if ($result->RecordCount() == 0) return 0;

    // loop through the results, auditing for whether each kind of attribute requires "selection" or not
    $fail = false;
    foreach($result as $row=>$field) {
      // if there's more than 1 for any $noDoubles type, we fail
      if (in_array($field['options_type'], $noDoubles) && $field['number_of_choices'] > 1) {
        $fail = true;
        break;
      }
      // if there's any type from $noSingles, we fail
      if (in_array($field['options_type'], $noSingles)) {
        $fail = true;
        break;
      }
    }

    // return 1 to indicate selections must be made, so a more-info button needs to be presented
    if ($fail) return 1;

    // return -1 to indicate that defaults can be automatically added by just using a buy-now button
    return -1;
  }

/**
 *  Check if option name is not expected to have an option value (ie. text field, or File upload field)
 *
 * @param int $option_name_id
 * @return bool
 */
  function zen_option_name_base_expects_no_values($option_name_id = array()) {
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
      $sql2 = ')';
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
 *
 * @param int $products_id
 * @return bool
 */
  function zen_has_product_attributes_values($products_id) {
    global $db;
    $attributes_query = "select sum(options_values_price) as total
                         from " . TABLE_PRODUCTS_ATTRIBUTES . "
                         where products_id = " . (int)$products_id;

    $attributes = $db->Execute($attributes_query);

    return ($attributes->fields['total'] != 0);
  }

/**
 * Find category name from ID, in indicated language
 *
 * @param int $category_id
 * @param int $language_id
 * @return string
 */
  function zen_get_category_name($category_id, $language_id) {
    global $db;
    $category = $db->Execute("select categories_name
                              from " . TABLE_CATEGORIES_DESCRIPTION . "
                              where categories_id = " . (int)$category_id . "
                              and language_id = " . (int)$language_id );
    if ($category->EOF) return '';
    return $category->fields['categories_name'];
  }


/**
 * Find category description, from category ID, in given language
 *
 * @param int $category_id
 * @param int $language_id
 * @return string
 */
  function zen_get_category_description($category_id, $language_id) {
    global $db;
    $category = $db->Execute("select categories_description
                              from " . TABLE_CATEGORIES_DESCRIPTION . "
                              where categories_id = " . (int)$category_id . "
                              and language_id = " . (int)$language_id);
    if ($category->EOF) return '';
    return $category->fields['categories_description'];
  }


/**
 * Return a product's category
 *
 * @param int $products_id
 * @return int
 */
  function zen_get_products_category_id($products_id) {
    global $db;

    $sql = "select master_categories_id from " . TABLE_PRODUCTS . " where products_id = " . (int)$products_id;
    $result = $db->Execute($sql);
    if ($result->EOF) return '';
    return $result->fields['master_categories_id'];
  }

/**
 * Return category's image
 *
 * @param int $category_id
 * @return string
 */
  function zen_get_categories_image($category_id) {
    global $db;

    $sql = "select categories_image from " . TABLE_CATEGORIES . " where categories_id= " . (int)$category_id;
    $result = $db->Execute($sql);

    return $result->fields['categories_image'];
  }

/**
 *  Return category's name from ID, assuming current language
 *
 * @param int $category_id
 * @return string
 */
  function zen_get_categories_name($category_id) {
    global $db;
    $sql = "select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id= " . (int)$category_id . " and language_id= " . (int)$_SESSION['languages_id'];

    $result = $db->Execute($sql);

    return $result->fields['categories_name'];
  }


/**
 * get product type for specified $product_id
 *
 * @param int $product_id
 * @return int
 */
  function zen_get_products_type($product_id) {
    global $db;

    $check_products_type = $db->Execute("select products_type from " . TABLE_PRODUCTS . " where products_id=" . (int)$product_id);
    if ($check_products_type->EOF) return '';
    return $check_products_type->fields['products_type'];
  }


/**
 * lookup product model
 *
 * @param int $products_id
 * @return string
 */
  function zen_get_products_model($products_id) {
    global $db;
    $check = $db->Execute("select products_model
                    from " . TABLE_PRODUCTS . "
                    where products_id=" . (int)$products_id);
    if ($check->EOF) return '';
    return $check->fields['products_model'];
  }

/**
 * Return a product's manufacturer's name, from ID
 *
 * @param int $product_id
 * @return string
 */
  function zen_get_products_manufacturers_name($product_id) {
    global $db;

    $product_query = "select m.manufacturers_name
                      from " . TABLE_PRODUCTS . " p, " .
                            TABLE_MANUFACTURERS . " m
                      where p.products_id = " . (int)$product_id . "
                      and p.manufacturers_id = m.manufacturers_id";

    $product =$db->Execute($product_query);

    return ($product->RecordCount() > 0) ? $product->fields['manufacturers_name'] : "";
  }

/**
 * Return a product's manufacturer's image, from Prod ID
 *
 * @param int $product_id
 * @return string
 */
  function zen_get_products_manufacturers_image($product_id) {
    global $db;

    $product_query = "select m.manufacturers_image
                      from " . TABLE_PRODUCTS . " p, " .
                            TABLE_MANUFACTURERS . " m
                      where p.products_id = " . (int)$product_id . "
                      and p.manufacturers_id = m.manufacturers_id";

    $product =$db->Execute($product_query);

    return $product->fields['manufacturers_image'];
  }

/**
 * Return a product's manufacturer's id, from Prod ID
 *
 * @param int $product_id
 * @return int
 */
  function zen_get_products_manufacturers_id($product_id) {
    global $db;

    $product_query = "select p.manufacturers_id
                      from " . TABLE_PRODUCTS . " p
                      where p.products_id = " . (int)$product_id;

    $product =$db->Execute($product_query);

    return $product->fields['manufacturers_id'];
  }

/**
 * Return attributes products_options_sort_order
 *
 * @param int $products_id
 * @param int $options_id
 * @param int $options_values_id
 * @return int
 */
  function zen_get_attributes_sort_order($products_id, $options_id, $options_values_id) {
    global $db;
      $check = $db->Execute("select products_options_sort_order
                             from " . TABLE_PRODUCTS_ATTRIBUTES . "
                             where products_id = " . (int)$products_id . "
                             and options_id = " . (int)$options_id . "
                             and options_values_id = " . (int)$options_values_id . " limit 1");

      return $check->fields['products_options_sort_order'];
  }

/**
 *  return attributes products_options_sort_order
 *
 * @param int $products_id
 * @param int $options_id
 * @param int $options_values_id
 * @param string $lang_num
 * @return string (int padded with 0's)
 */
  function zen_get_attributes_options_sort_order($products_id, $options_id, $options_values_id, $lang_num = '') {
    global $db;
      if ($lang_num == '') $lang_num = (int)$_SESSION['languages_id'];
      $check = $db->Execute("select products_options_sort_order
                             from " . TABLE_PRODUCTS_OPTIONS . "
                             where products_options_id = " . (int)$options_id . " and language_id = " . (int)$lang_num . " limit 1");

      $check_options_id = $db->Execute("select products_id, options_id, options_values_id, products_options_sort_order
                             from " . TABLE_PRODUCTS_ATTRIBUTES . "
                             where products_id=" . (int)$products_id . "
                             and options_id=" . (int)$options_id . "
                             and options_values_id = " . (int)$options_values_id . " limit 1");


      return $check->fields['products_options_sort_order'] . '.' . str_pad($check_options_id->fields['products_options_sort_order'],5,'0',STR_PAD_LEFT);
  }

/**
 *  check if attribute is display only
 *
 * @param int $product_id
 * @param int $option
 * @param int $value
 * @return bool
 */
  function zen_get_attributes_valid($product_id, $option, $value) {
    global $db;

// regular attribute validation
    $check_attributes = $db->Execute("select attributes_display_only, attributes_required from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id=" . (int)$product_id . " and options_id=" . (int)$option . " and options_values_id=" . (int)$value);

    $check_valid = true;

// display only cannot be selected
    if ($check_attributes->fields['attributes_display_only'] == '1') {
      $check_valid = false;
    }

// text required validation
    if (preg_match('/^txt_/', $option)) {
      $check_attributes = $db->Execute("select attributes_display_only, attributes_required from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id=" . (int)$product_id . " and options_id=" . (int)preg_replace('/txt_/', '', $option) . " and options_values_id=0");
// text cannot be blank
      if ($check_attributes->fields['attributes_required'] == '1' && (!zen_not_null($value) && !is_numeric($value))) {
        $check_valid = false;
      }
    }

    return $check_valid;
  }

/**
 * Validate Option Name and Option Type Match
 *
 * @param int $products_options_id
 * @param int $products_options_values_id
 * @return bool
 */
  function zen_validate_options_to_options_value($products_options_id, $products_options_values_id) {
    global $db;
    $result = $db->Execute("select products_options_id
                            from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                            where products_options_id= " . (int)$products_options_id . "
                            and products_options_values_id=" . (int)$products_options_values_id . "
                            limit 1");

    return ($result->RecordCount() == 0);
  }

/**
 * look-up Attributes Options Name products_options_values_to_products_options
 *
 * @param int $lookup
 * @return string
 */
  function zen_get_products_options_name_from_value($lookup) {
    global $db;

    if ($lookup==0) {
      return 'RESERVED FOR TEXT/FILES ONLY ATTRIBUTES';
    }

    $result = $db->Execute("select products_options_id
                    from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                    where products_options_values_id=" . (int)$lookup);
    if ($result->EOF) return '';

    $check_options = $db->Execute("select products_options_name
                      from " . TABLE_PRODUCTS_OPTIONS . "
                      where products_options_id=" . (int)$result->fields['products_options_id'] . "
                      and language_id=" . (int)$_SESSION['languages_id']);
    if ($check_options->EOF) return '';
    return $check_options->fields['products_options_name'];
  }

/**
 * Return Options_Name from ID
 *
 * @param string|int $options_id
 * @return string
 */
  function zen_options_name($options_id) {
    global $db;

    $options_id = str_replace('txt_','',$options_id);

    $result = $db->Execute("select products_options_name
                            from " . TABLE_PRODUCTS_OPTIONS . "
                            where products_options_id = " . (int)$options_id . "
                            and language_id = " . (int)$_SESSION['languages_id']);
    if ($result->EOF) return '';
    return $result->fields['products_options_name'];
  }

/**
 * Return Options_values_name from value-ID
 *
 * @param int $values_id
 * @return string
 */
  function zen_values_name($values_id) {
    global $db;

    $result = $db->Execute("select products_options_values_name
                            from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                            where products_options_values_id = " . (int)$values_id . "
                            and language_id = " . (int)$_SESSION['languages_id']);
    if ($result->EOF) return '';
    return $result->fields['products_options_values_name'];
  }

/**
 * configuration key value lookup
 *
 * @deprecated Obsolete since 1.2. Use constant($lookup) or just CONSTANT_NAME instead
 * @param $lookup
 * @return string
 */
  function zen_get_configuration_key_value($lookup) {
    global $db;
    $configuration_query= $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . zen_db_input($lookup) . "'");
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( $configuration_query->RecordCount() == 0 ) {
      $lookup_value='<span class="lookupAttention">' . $lookup . '</span>';
    }
    return $lookup_value;
  }


/**
 * Get title of config group by its number
 * @param int $lookup
 * @return string
 */
  function zen_get_configuration_group_value($lookup) {
    global $db;
    $configuration_query= $db->Execute("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id =" . (int)$lookup);
    if ( $configuration_query->RecordCount() == 0 ) {
      return (int)$lookup;
    }
    return $configuration_query->fields['configuration_group_title'];
  }

/**
 * Return products description, based on specified language (or current lang if not specified)
 *
 * @param int $product_id
 * @param string $language
 * @return string
 */
  function zen_get_products_description($product_id, $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_query = "select products_description
                      from " . TABLE_PRODUCTS_DESCRIPTION . "
                      where products_id = " . (int)$product_id . "
                      and language_id = " . (int)$language;

    $product = $db->Execute($product_query);

    return $product->fields['products_description'];
  }

/**
 * Return the product info pagehandler name for the specified product-type
 *
 * @param int $product_id
 * @return string
 */
  function zen_get_info_page($product_id) {
    global $db;
    $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id = " . (int)$product_id;
    $zp_type = $db->Execute($sql);
    if ($zp_type->RecordCount() == 0) {
      return 'product_info';
    } else {
      $product_type = $zp_type->fields['products_type'];
      $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = " . (int)$product_type;
      $result = $db->Execute($sql);
      return $result->fields['type_handler'] . '_info';
    }
  }

/**
 * Get accepted credit cards
 * There needs to be a define on the accepted credit card in the language file credit_cards.php example: TEXT_CC_ENABLED_VISA
 *
 * @param string $text_image
 * @param string $cc_separator
 * @param int $cc_make_columns Number of columns in table
 * @return string
 */
  function zen_get_cc_enabled($text_image = 'TEXT_', $cc_separator = ' ', $cc_make_columns = 0) {
    global $db;
    $cc_check_accepted_query = $db->Execute(SQL_CC_ENABLED);
    $cc_check_accepted = '';
    $cc_counter = 0;
    if ($cc_make_columns == 0) {
      while (!$cc_check_accepted_query->EOF) {
        $check_it = $text_image . $cc_check_accepted_query->fields['configuration_key'];
        if (defined($check_it)) {
          $cc_check_accepted .= constant($check_it) . $cc_separator;
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
 * Get categories_name from products_id
 *
 * @param int $product_id
 * @return string
 */
  function zen_get_categories_name_from_product($product_id) {
    global $db;

//    $check_products_category= $db->Execute("select products_id, categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $product_id . "' limit 1");
    $check_products_category = $db->Execute("select products_id, master_categories_id from " . TABLE_PRODUCTS . " where products_id = " . (int)$product_id);
    $the_categories_name= $db->Execute("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id= " . (int)$check_products_category->fields['master_categories_id'] . " and language_id= " . (int)$_SESSION['languages_id']);
    if ($the_categories_name->EOF) return '';
    return $the_categories_name->fields['categories_name'];
  }

/**
 * configuration key value lookup in TABLE_PRODUCT_TYPE_LAYOUT
 * Used to determine keys/flags used on a per-product-type basis for template-use, etc
 *
 * @param string $lookup
 * @param int $type
 * @return string
 */
  function zen_get_configuration_key_value_layout($lookup, $type = 1) {
    global $db;
    $configuration_query= $db->Execute("select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . zen_db_input($lookup) . "' and product_type_id=". (int)$type);
    if ($configuration_query->EOF) return '';
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( !($lookup_value) ) {
      $lookup_value='<span class="lookupAttention">' . $lookup . '</span>';
    }
    return $lookup_value;
  }

/**
 * Generate <IMG> HTML tag for product image of the specified product
 *
 * @param int $product_id
 * @param int $width img width
 * @param int $height img height
 * @return string HTML
 */
  function zen_get_products_image($product_id, $width = SMALL_IMAGE_WIDTH, $height = SMALL_IMAGE_HEIGHT) {
    global $db;
    $image_name = zen_get_products_image_name($product_id);
    if ($image_name == '') return ''; // ALTERNATIVELY could maybe use the no_picture default image??
    return zen_image(DIR_WS_IMAGES . $image_name, zen_get_products_name($product_id), $width, $height);
  }

/**
 * get product image name
 *
 * @param int $product_id
 * @return string
 */
  function zen_get_products_image_name($product_id) {
    global $db;
    $sql = "select p.products_image from " . TABLE_PRODUCTS . " p  where products_id=" . (int)$product_id;
    $result = $db->Execute($sql);
    if ($result->EOF) return '';
    return $result->fields['products_image'];
  }

/**
 * Get the product's extra-details-URL entry from the db
 * @param int $product_id
 * @param int $language_id
 * @return string
 */
  function zen_get_products_url($product_id, $language_id) {
    global $db;
    $product = $db->Execute("select products_url
                             from " . TABLE_PRODUCTS_DESCRIPTION . "
                             where products_id = " . (int)$product_id . "
                             and language_id = " . (int)$language_id);
    if ($product->EOF) return '';
    return $product->fields['products_url'];
  }

/**
 * look up whether a product is virtual
 *
 * @param int $product_id
 * @return bool
 */
  function zen_get_products_virtual($product_id) {
    global $db;

    $sql = "select p.products_virtual from " . TABLE_PRODUCTS . " p  where p.products_id=" . (int)$product_id;
    $look_up = $db->Execute($sql);

    return ($look_up->fields['products_virtual'] == '1');
  }

/**
 * Look up whether the given product ID is allowed to be added to cart,
 * according to product-type switches set in Admin
 *
 * @param int $product_id
 * @return string Y|N
 */
  function zen_get_products_allow_add_to_cart($product_id) {
    global $db;

    $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id=" . (int)$product_id;
    $result = $db->Execute($sql);

    $sql = "select allow_add_to_cart from " . TABLE_PRODUCT_TYPES . " where type_id = " . (int)$result->fields['products_type'];
    $allow_add_to_cart = $db->Execute($sql);

    return $allow_add_to_cart->fields['allow_add_to_cart'];
  }

/**
 * Look up SHOW_XXX_INFO switch for product ID and product type
 *
 * @param $product_id
 * @param string $field
 * @param string $prefix
 * @param string $suffix
 * @param string $field_prefix
 * @param string $field_suffix
 * @return string
 */
    function zen_get_show_product_switch_name($product_id, $field, $prefix= 'SHOW_', $suffix= '_INFO', $field_prefix= '_', $field_suffix='') {
      global $db;
      $type_lookup = 0;
      $type_handler = '';
      $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id=" . (int)$product_id;
      $result = $db->Execute($sql);
      if (!$result->EOF) $type_lookup = $result->fields['products_type'];

      $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = " . (int)$type_lookup;
      $result = $db->Execute($sql);
      if (!$result->EOF) $type_handler = $result->fields['type_handler'];
      $zv_key = strtoupper($prefix . $type_handler . $suffix . $field_prefix . $field . $field_suffix);

      return $zv_key;
    }

/**
 * build configuration_key based on product type and return its value
 * example: To get the settings for metatags_products_name_status for a product use:
 * zen_get_show_product_switch($_GET['pID'], 'metatags_products_name_status')
 * the product is looked up for the products_type which then builds the configuration_key example:
 * SHOW_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS
 * the value of the configuration_key is then returned
 * NOTE: keys are looked up first in the product_type_layout table and if not found looked up in the configuration table.
 *
 * @param int $product_id
 * @param string $field
 * @param string $prefix
 * @param string $suffix
 * @param string $field_prefix
 * @param string $field_suffix
 * @return string
 */
    function zen_get_show_product_switch($product_id, $field, $prefix= 'SHOW_', $suffix= '_INFO', $field_prefix= '_', $field_suffix='') {
      global $db;
      $zv_key = zen_get_show_product_switch_name($product_id, $field, $prefix, $suffix, $field_prefix, $field_suffix);
      $sql = "select configuration_key, configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . zen_db_input($zv_key) . "'";
      $zv_key_value = $db->Execute($sql);

      if ($zv_key_value->RecordCount() > 0) {
        return $zv_key_value->fields['configuration_value'];
      } else {
        $sql = "select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . zen_db_input($zv_key) . "'";
        $zv_key_value = $db->Execute($sql);
        if ($zv_key_value->RecordCount() > 0) {
          return $zv_key_value->fields['configuration_value'];
        } else {
          return $zv_key_value->fields['configuration_value'];
        }
      }
    }

/**
 *  Look up whether a product is always free shipping
 *
 * @param int $product_id
 * @return bool
 */
  function zen_get_product_is_always_free_shipping($product_id) {
    global $db;

    $sql = "select p.product_is_always_free_shipping from " . TABLE_PRODUCTS . " p  where p.products_id=" . (int)$product_id;
    $look_up = $db->Execute($sql);

    return ($look_up->fields['product_is_always_free_shipping'] == '1');
  }

/**
 * Determine: Should we run as "normal"?
 *  Stop regular execution behavior based on customer/store settings
 *  Used to disable various activities if store is in an operating mode that should prevent those activities
 * @return bool
 */
  function zen_run_normal() {
    $zc_run = false;
    switch (true) {
      case (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])):
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
      case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
      // customer must be logged in to browse
        $zc_run = false;
        break;
      case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
      // show room only
      // customer may browse but no prices
        $zc_run = false;
        break;
      case (CUSTOMERS_APPROVAL == '3'):
      // show room only
        $zc_run = false;
        break;
      case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and $_SESSION['customer_id'] == ''):
      // customer must be logged in to browse
        $zc_run = false;
        break;
      case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and $_SESSION['customers_authorization'] > '0'):
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
 * @return bool
 */
  function zen_check_show_prices() {
    if (!(CUSTOMERS_APPROVAL == '2' and (int)$_SESSION['customer_id'] == 0)
        and !((CUSTOMERS_APPROVAL_AUTHORIZATION > 0 and CUSTOMERS_APPROVAL_AUTHORIZATION < 3)
            and ($_SESSION['customers_authorization'] > 0 or (int)$_SESSION['customer_id'] == 0)
            )
        and STORE_STATUS != 1)
    {
      return true;
    }
    return false;
  }

/**
 * Return any field from products or products_description table
 * Example: zen_products_lookup('3', 'products_date_added');
 *
 * @param int $product_id
 * @param string $what_field
 * @param int $language
 * @return string
 */
  function zen_products_lookup($product_id, $what_field = 'products_name', $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_lookup = $db->Execute("select " . zen_db_input($what_field) . " as lookup_field
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where  p.products_id ='" . (int)$product_id . "'
                              and pd.products_id = p.products_id
                              and pd.language_id = " . (int)$language);
    $return_field = $product_lookup->fields['lookup_field'];
    if ($return_field->EOF) return '';
    return $return_field;
  }

/**
 * Return any field from categories or categories_description table
 * Example: zen_categories_lookup('10', 'parent_id');
 *
 * @param int $categories_id
 * @param string $what_field
 * @param int $language
 * @return string
 */
  function zen_categories_lookup($categories_id, $what_field = 'categories_name', $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $category_lookup = $db->Execute("select " . $what_field . " as lookup_field
                              from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                              where c.categories_id =" . (int)$categories_id . "
                              and c.categories_id = cd.categories_id
                              and cd.language_id = " . (int)$language);

    $return_field = $category_lookup->fields['lookup_field'];

    return $return_field;
  }

/**
 * Find index_filters directory
 * suitable for including template-specific immediate /modules files, such as:
 * new_products, products_new_listing, featured_products, featured_products_listing, product_listing, specials_index, upcoming,
 * products_all_listing, products_discount_prices, also_purchased_products
 *
 * @param string $check_file
 * @param string $dir_only
 * @return string
 */
  function zen_get_index_filters_directory($check_file, $dir_only = 'false') {
    global $template_dir;
    $zv_filename = $check_file;
    if (!strstr($zv_filename, '.php')) $zv_filename .= '.php';
    $checkArray = array();
    $checkArray[] = DIR_WS_INCLUDES . 'index_filters/' . $template_dir . '/' . $zv_filename;
    $checkArray[] = DIR_WS_INCLUDES . 'index_filters/' . 'shared' . '/' . $zv_filename;
    $checkArray[] = DIR_WS_INCLUDES . 'index_filters/' . $zv_filename;
    $checkArray[] = DIR_WS_INCLUDES . 'index_filters/' . $template_dir . '/' . 'default_filter.php';
    foreach($checkArray as $key => $val) {
      if (file_exists($val)) {
        return ($dir_only == 'true') ? $val = substr($val, 0, strpos($val, '/')) : $val;
      }
    }
    return DIR_WS_INCLUDES . 'index_filters/' . 'default_filter.php';
  }

/**
 * return the SQL date-filter string based on the Time Limit for what qualifies as New Products
 * @param int $time_limit
 * @return string
 */
  function zen_get_products_new_timelimit($time_limit = -1) {
    if ($time_limit == -1) {
      $time_limit = SHOW_NEW_PRODUCTS_LIMIT;
    }
    switch (true) {
      case ($time_limit == '0'):
        return '';
      case ($time_limit == '1'):
        return " and date_format(p.products_date_added, '%Y%m') >= date_format(now(), '%Y%m')";
      case ($time_limit == '7'):
        return ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 7';
      case ($time_limit == '14'):
        return ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 14';
      case ($time_limit == '30'):
        return ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 30';
      case ($time_limit == '60'):
        return ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 60';
      case ($time_limit == '90'):
        return ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 90';
      case ($time_limit == '120'):
        return ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 120';
    }
    return '';
  }

/**
 * Return whether there are downloads for the specified product
 *
 * @param int $products_id
 * @param bool $check_valid
 * @return string
 */
function zen_has_product_attributes_downloads($products_id, $check_valid=false) {
    global $db;
    if (DOWNLOAD_ENABLED == 'true') {
      $download_display_query_raw ="select pa.products_attributes_id, pad.products_attributes_filename
                                    from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                    where pa.products_id=" . (int)$products_id . "
                                      and pad.products_attributes_id= pa.products_attributes_id";
      $download_display = $db->Execute($download_display_query_raw);
      if ($check_valid == true) {
        $valid_downloads = '';
        while (!$download_display->EOF) {
          if (!zen_verify_download_file_is_valid($download_display->fields['products_attributes_filename'])) {
            $valid_downloads .= '<br />&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . ' Invalid: ' . $download_display->fields['products_attributes_filename'];
            // break;
          } else {
            $valid_downloads .= '<br />&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . ' Valid&nbsp;&nbsp;: ' . $download_display->fields['products_attributes_filename'];
          }
          $download_display->MoveNext();
        }
      } else {
        if ($download_display->RecordCount() != 0) {
          $valid_downloads = $download_display->RecordCount() . ' files';
        } else {
          $valid_downloads = 'none';
        }
      }
    } else {
      $valid_downloads = 'disabled';
    }
    return $valid_downloads;
  }

  /**
   * check if Product is set to use downloads
   * (does not validate download filename)
   *
   * @param  int $products_id
   * @return bool
   * @todo   $downloadsRepository->countForProductId($products_id); #DDD
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


/**
 * check that the specified download filename exists on the filesystem
 *
 * @param string $check_filename
 * @return bool
 */
  function zen_verify_download_file_is_valid($check_filename) {
    global $zco_notifier;

    $handler = zen_get_download_handler($check_filename);

    if ($handler == 'local') {
      return file_exists(DIR_FS_DOWNLOAD . $check_filename);
    }

    /**
     * An observer hooking this notifier should set $handler to blank if it tries a validation and fails.
     * Or, if validation passes, simply set $handler to the service name (first chars before first colon in filename)
     * Or, or there is no way to verify, do nothing to $handler.
     */
    $zco_notifier->notify('NOTIFY_TEST_DOWNLOADABLE_FILE_EXISTS', $check_filename, $handler);

    // if handler is set but isn't local (internal) then we simply return true since there's no way to "test"
    if ($handler != '') return true;

    // else if the notifier caused $handler to be empty then that means it failed verification, so we return false
    return false;
  }

/**
 * check if the specified download filename matches a handler for an external download service
 * If yes, it will be because the filename contains colons as delimiters ... service:filename:filesize
 *
 * @param string $filename
 * @return string handler to use for downloading this file
 */
  function zen_get_download_handler($filename) {
    $file_parts = explode(':', $filename);

    // if the filename doesn't contain any colons, then there's no delimiter to return, so must be using built-in file handling
    if (sizeof($file_parts) < 2) {
      return 'local';
    }

    return $file_parts[0];
  }


/**
 * build SQL to specify date range for new products
 *
 * @param int $time_limit
 * @return string
 */
  function zen_get_new_date_range($time_limit = -1) {
    if ($time_limit < 0) {
      $time_limit = SHOW_NEW_PRODUCTS_LIMIT;
    }
    // 120 days; 24 hours; 60 mins; 60secs
    $date_range = time() - ($time_limit * 24 * 60 * 60);
    $upcoming_mask_range = time();
    $upcoming_mask = date('Ymd', $upcoming_mask_range);

// echo 'Now:      '. date('Y-m-d') ."<br />";
// echo $time_limit . ' Days: '. date('Ymd', $date_range) ."<br />";
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
 * build date range for upcoming products
 * @return string
 */
  function zen_get_upcoming_date_range() {
    // 120 days; 24 hours; 60 mins; 60secs
    $date_range = time();
    $zc_new_date = date('Ymd', $date_range);
// @TODO need to check speed on this for larger sites
//    $new_range = ' and date_format(p.products_date_available, \'%Y%m%d\') >' . $zc_new_date;
    $new_range = ' and p.products_date_available >' . $zc_new_date . '235959';

    return $new_range;
  }


/**
 * Return the manufacturers URL in the needed language
 * @param int $manufacturer_id
 * @param int $language_id
 * @return string
 */
  function zen_get_manufacturer_url($manufacturer_id, $language_id) {
    global $db;
    $manufacturer = $db->Execute("select manufacturers_url
                                  from " . TABLE_MANUFACTURERS_INFO . "
                                  where manufacturers_id = " . (int)$manufacturer_id . "
                                  and languages_id = " . (int)$language_id);
    if ($manufacturer->EOF) return '';
    return $manufacturer->fields['manufacturers_url'];
  }

/**
 * Get the status of a category
 *
 * @param int $categories_id
 * @return bool 0|1
 */
  function zen_get_categories_status($categories_id) {
    global $db;
    $sql = "select categories_status from " . TABLE_CATEGORIES . (zen_not_null($categories_id) ? " where categories_id=" . (int)$categories_id : "");
    $check_status = $db->Execute($sql);
    if ($check_status->EOF) return '';
    return $check_status->fields['categories_status'];
  }

/**
 * Get the status of a product
 *
 * @param int $product_id
 * @return bool 0|1
 */
  function zen_get_products_status($product_id) {
    global $db;
    $sql = "select products_status from " . TABLE_PRODUCTS . (zen_not_null($product_id) ? " where products_id=" . (int)$product_id : "");
    $check_status = $db->Execute($sql);
    if ($check_status->EOF) return '';
    return $check_status->fields['products_status'];
  }

/**
 * Build HTML for specified catalog-side image
 * @param string $image
 * @param string $alt
 * @param int $width
 * @param int $height
 * @return bool|string
 */
function zen_info_image($image, $alt, $width = '', $height = '') {
    if (zen_not_null($image) && (file_exists(DIR_FS_CATALOG_IMAGES . $image)) ) {
      $image = zen_image(DIR_WS_CATALOG_IMAGES . $image, $alt, $width, $height);
    } else {
      $image = TEXT_IMAGE_NONEXISTENT;
    }

    return $image;
  }


/**
 * @param int $order_status_id
 * @param int $language_id
 * @return string
 */
function zen_get_order_status_name($order_status_id, $language_id = '') {
    global $db;

    if ($order_status_id < 1) return TEXT_DEFAULT;

    if (!is_numeric($language_id)) $language_id = $_SESSION['languages_id'];

    $status = $db->Execute("select orders_status_name
                            from " . TABLE_ORDERS_STATUS . "
                            where orders_status_id = " . (int)$order_status_id . "
                            and language_id = " . (int)$language_id);
    if ($status->EOF) return 'ERROR: INVALID STATUS ID: ' . (int)$order_status_id;
    return $status->fields['orders_status_name'] . ' [' . (int)$order_status_id . ']';
  }


/**
 * Build and return list of order-statuses in the current session language
 * @deprecated since v1.6.0 - Use the static method in the order class instead
 * @return array
 */
function zen_get_orders_status() {
    global $db;

    $orders_status_array = array();
    $orders_status = $db->Execute("select orders_status_id, orders_status_name
                                   from " . TABLE_ORDERS_STATUS . "
                                   where language_id = " . (int)$_SESSION['languages_id'] . "
                                   order by orders_status_id");
    foreach($orders_status as $status) {
      $orders_status_array[] = array('id' => $status['orders_status_id'],
                                     'text' => $status['orders_status_name']);
    }
    return $orders_status_array;
  }

/**
 * get the type_handler value for the specified product_type
 *
 * @param int $product_type
 * @return string
 */
  function zen_get_handler_from_type($product_type) {
    global $db;
    global $messageStack;

    $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = " . (int)$product_type;
    $handler = $db->Execute($sql);
    if ($handler->EOF) {
          $messageStack->add('ERROR: Invalid product_type specified.', 'error');
          return -1;
    }
    return $handler->fields['type_handler'];
  }


/**
 * check if product has quantity-discounts defined
 *
 * @param int $product_id
 * @return bool
 */
  function zen_has_product_discounts($product_id) {
    global $db;

    $sql = "select products_id from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id=" . (int)$product_id;
    $check_discount = $db->Execute($sql);

    return ($check_discount->RecordCount() > 0);
  }


/**
 * get customer order comments
 * @deprecated use $order->status_history[0]['comments'] instead, after instantiating the order, since this data is already in the order object and saves re-querying the db
 */
  function zen_get_orders_comments($orders_id) {
    global $db;
    $orders_comments_query = "SELECT osh.comments from " .
                              TABLE_ORDERS_STATUS_HISTORY . " osh
                              where osh.orders_id = " . (int)$orders_id . "
                              order by osh.orders_status_history_id
                              limit 1";
    $orders_comments = $db->Execute($orders_comments_query);
    if ($orders_comments->EOF) return '';
    return $orders_comments->fields['comments'];
  }

/**
 * Get the Option Name in a particular language
 *
 * @param int $option
 * @param int $language
 * @return string
 */
  function zen_get_option_name_language($option, $language) {
    global $db;
    $lookup = $db->Execute("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id= " . (int)$option . " and language_id = " . (int)$language);
    if ($lookup->EOF) return '';
    return $lookup->fields['products_options_name'];
  }

/**
 * Get the Option Name sort order for a particular language
 *
 * @param int $option
 * @param int $language
 * @return int
 */
  function zen_get_option_name_language_sort_order($option, $language) {
    global $db;
    $lookup = $db->Execute("select products_options_id, products_options_name, products_options_sort_order from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id=" . (int)$option . " and language_id = " . (int)$language);
    if ($lookup->EOF) return '';
    return $lookup->fields['products_options_sort_order'];
  }

