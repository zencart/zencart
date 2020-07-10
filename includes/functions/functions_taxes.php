<?php
/**
 * functions_taxes
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Dec 16 Modified in v1.5.7 $
 */

////
// Returns the tax rate for a zone / class
// TABLES: tax_rates, zones_to_geo_zones
  function zen_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
    global $db;
    // -----
    // Give an observer a chance to override this function's return.
    //
    $tax_rate = false;
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_GET_TAX_RATE_OVERRIDE', 
        array(
            'class_id' => $class_id, 
            'country_id' => $country_id, 
            'zone_id' => $zone_id
        ), 
        $tax_rate
    );
    if ($tax_rate !== false) {
        return $tax_rate;
    }

    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (zen_is_logged_in()) {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      } else {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      }
    }

    if (STORE_PRODUCT_TAX_BASIS == 'Store') {
      if ($zone_id != STORE_ZONE) return 0;
    }

    $tax_query = "select sum(tax_rate) as tax_rate
                  from (" . TABLE_TAX_RATES . " tr
                  left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id)
                  left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) )
                  where (za.zone_country_id is null
                  or za.zone_country_id = 0
                  or za.zone_country_id = '" . (int)$country_id . "')
                  and (za.zone_id is null
                  or za.zone_id = 0
                  or za.zone_id = '" . (int)$zone_id . "')
                  and tr.tax_class_id = '" . (int)$class_id . "'
                  group by tr.tax_priority";

    $tax = $db->Execute($tax_query);

    if ($tax->RecordCount() > 0) {
      $tax_multiplier = 1.0;
      while (!$tax->EOF) {
        $tax_multiplier *= 1.0 + ($tax->fields['tax_rate'] / 100);
        $tax->MoveNext();
      }
      return ($tax_multiplier - 1.0) * 100;
    } else {
      return 0;
    }
  }

////
// Return the tax description for a zone / class
// TABLES: tax_rates;
  function zen_get_tax_description($class_id, $country_id = -1, $zone_id = -1) {
    global $db;
    
    // -----
    // Give an observer the chance to override this function's return.
    //
    $tax_description = '';
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_GET_TAX_DESCRIPTION_OVERRIDE',
        array(
            'class_id' => $class_id,
            'country_id' => $country_id,
            'zone_id' => $zone_id
        ),
        $tax_description
    );
    if ($tax_description != '') {
        return $tax_description;
    }
    
    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (zen_is_logged_in()) {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      } else {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      }
    }

    $tax_query = "select tax_description
                  from (" . TABLE_TAX_RATES . " tr
                  left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id)
                  left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) )
                  where (za.zone_country_id is null or za.zone_country_id = 0
                  or za.zone_country_id = '" . (int)$country_id . "')
                  and (za.zone_id is null
                  or za.zone_id = 0
                  or za.zone_id = '" . (int)$zone_id . "')
                  and tr.tax_class_id = '" . (int)$class_id . "'
                  order by tr.tax_priority";

    $tax = $db->Execute($tax_query);

    if ($tax->RecordCount() > 0) {
      $tax_description = '';
      while (!$tax->EOF) {
        $tax_description .= $tax->fields['tax_description'] . ' + ';
        $tax->MoveNext();
      }
      $tax_description = substr($tax_description, 0, -3);

      return $tax_description;
    } else {
      return TEXT_UNKNOWN_TAX_RATE;
    }
  }
////
// Return the tax rates for each defined tax for the given class and zone
// @returns array(description => tax_rate)
  function zen_get_multiple_tax_rates($class_id, $country_id, $zone_id, $tax_description=array()) {
    global $db;
    // -----
    // Give an observer the chance to override this function's return.
    // It is *intended* to be an empty string; this is not a bug.
    $rates_array = '';
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_GET_MULTIPLE_TAX_RATES_OVERRIDE',
        array(
            'class_id' => $class_id,
            'country_id' => $country_id,
            'zone_id' => $zone_id,
            'tax_description' => $tax_description
        ),
        $rates_array
    );
    if (is_array($rates_array)) {
        return $rates_array;
    }
    
    $rates_array = array();
    
    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (zen_is_logged_in()) {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      } else {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      }
    }

    $tax_query = "select tax_description, tax_rate, tax_priority
                  from (" . TABLE_TAX_RATES . " tr
                  left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id)
                  left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) )
                  where (za.zone_country_id is null or za.zone_country_id = 0
                  or za.zone_country_id = '" . (int)$country_id . "')
                  and (za.zone_id is null
                  or za.zone_id = 0
                  or za.zone_id = '" . (int)$zone_id . "')
                  and tr.tax_class_id = '" . (int)$class_id . "'
                  order by tr.tax_priority";
    $tax = $db->Execute($tax_query);

    // calculate appropriate tax rate respecting priorities and compounding
    if ($tax->RecordCount() > 0) {
      $tax_aggregate_rate = 1;
      $tax_rate_factor = 1;
      $tax_prior_rate = 1;
      $tax_priority = 0;
      while (!$tax->EOF) {
        if ((int)$tax->fields['tax_priority'] > $tax_priority) {
          $tax_priority = $tax->fields['tax_priority'];
          $tax_prior_rate = $tax_aggregate_rate;
          $tax_rate_factor = 1 + ($tax->fields['tax_rate'] / 100);
          $tax_rate_factor *= $tax_aggregate_rate;
          $tax_aggregate_rate = 1;
        } else {
          $tax_rate_factor = $tax_prior_rate * ( 1 + ($tax->fields['tax_rate'] / 100));
        }
        $rates_array[$tax->fields['tax_description']] = 100 * ($tax_rate_factor - $tax_prior_rate);
        $tax_aggregate_rate += $tax_rate_factor - 1;
        $tax->MoveNext();
      }
    } else {
      // no tax at this level, set rate to 0 and description of unknown
      $rates_array[TEXT_UNKNOWN_TAX_RATE] = 0;
    }
    return $rates_array;
  }
////
// Add tax to a products price based on whether we are displaying tax "in" the price
  function zen_add_tax($price, $tax = 0) {
    global $currencies;

    if ( (DISPLAY_PRICE_WITH_TAX == 'true') && ($tax > 0) ) {
      return $price + zen_calculate_tax($price, $tax);
    } else {
      return $price;
    }
  }

 // Calculates Tax rounding the result
  function zen_calculate_tax($price, $tax = 0) {
    global $currencies;
    return $price * $tax / 100;
  }
////
// Output the tax percentage with optional padded decimals
  function zen_display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {
    if (strpos($value, '.')) {
      $loop = true;
      while ($loop) {
        if (substr($value, -1) == '0') {
          $value = substr($value, 0, -1);
        } else {
          $loop = false;
          if (substr($value, -1) == '.') {
            $value = substr($value, 0, -1);
          }
        }
      }
    }

    if ($padding > 0) {
      if ($decimal_pos = strpos($value, '.')) {
        $decimals = strlen(substr($value, ($decimal_pos+1)));
        for ($i=$decimals; $i<$padding; $i++) {
          $value .= '0';
        }
      } else {
        $value .= '.';
        for ($i=0; $i<$padding; $i++) {
          $value .= '0';
        }
      }
    }

    return $value;
  }

////
// Get tax rate from tax description
 function zen_get_tax_rate_from_desc($tax_desc) {
    global $db;
    $tax_rate = 0.00;

    $tax_descriptions = explode(' + ', $tax_desc);
    foreach ($tax_descriptions as $tax_description) {
      $tax_query = "SELECT tax_rate
                    FROM " . TABLE_TAX_RATES . "
                    WHERE tax_description = :taxDescLookup";
      $tax_query = $db->bindVars($tax_query, ':taxDescLookup', $tax_description, 'string'); 

      $tax = $db->Execute($tax_query);

      $tax_rate += $tax->fields['tax_rate'];
    }

    return $tax_rate;
  }

 function zen_get_tax_locations($store_country = -1, $store_zone = -1) {
    // -----
    // Give an observer the chance to modify the function's output.
    //
    $tax_address = false;
    $GLOBALS['zco_notifier']->notify(
        'ZEN_GET_TAX_LOCATIONS',
        array(
            'country' => $store_country,
            'zone' => $store_zone
        ),
        $tax_address
    );
    if (is_array($tax_address)) {
        return $tax_address;
    }
    
    $tax_address = array();
    global $db;
    switch (STORE_PRODUCT_TAX_BASIS) {

      case 'Shipping':
        $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                and ab.address_book_id = '" . (int)$_SESSION['sendto'] . "'";
        $tax_address_result = $db->Execute($tax_address_query);
      break;
      case 'Billing':

        $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                and ab.address_book_id = '" . (int)$_SESSION['billto'] . "'";
        $tax_address_result = $db->Execute($tax_address_query);
      break;
      case 'Store':
        $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                and ab.address_book_id = '" . (int)$_SESSION['billto'] . "'";
        $tax_address_result = $db->Execute($tax_address_query);

        if ($tax_address_result ->fields['entry_zone_id'] == STORE_ZONE) {

        } else {
          $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                  from " . TABLE_ADDRESS_BOOK . " ab
                                  left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                  where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                  and ab.address_book_id = '" . (int)$_SESSION['sendto'] . "'";
        $tax_address_result = $db->Execute($tax_address_query);
       }
     }
     $tax_address['zone_id'] = $tax_address_result->fields['entry_zone_id'];
     $tax_address['country_id'] = $tax_address_result->fields['entry_country_id'];
     return $tax_address;
 }
 function zen_get_all_tax_descriptions($country_id = -1, $zone_id = -1) 
 {
   global $db;
    // -----
    // Give an observer the chance to override this function's return.
    //
    $tax_descriptions = '';
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_GET_ALL_TAX_DESCRIPTIONS_OVERRIDE',
        array(
            'country_id' => $country_id,
            'zone_id' => $zone_id
        ),
        $tax_descriptions
    );
    if (is_array($tax_descriptions)) {
        return $tax_descriptions;
    }
    
    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (zen_is_logged_in()) {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      } else {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      }
    }
    
   $sql = "select tr.* 
           from (" . TABLE_TAX_RATES . " tr
           left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id)
           left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) )
           where (za.zone_country_id is null
           or za.zone_country_id = 0
           or za.zone_country_id = '" . (int)$country_id . "')
           and (za.zone_id is null
           or za.zone_id = 0
           or za.zone_id = '" . (int)$zone_id . "')";
   $result = $db->Execute($sql);
   $taxDescriptions =array();
   while (!$result->EOF)
   {
     $taxDescriptions[] = $result->fields['tax_description'];
     $result->moveNext();
   }
   return $taxDescriptions;
 }
 
