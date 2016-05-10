<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */

////
// Redirect to another page or site
  function zen_redirect($url) {
    global $logger;

// clean up URL before executing it
    $url = preg_replace('/&{2,}/', '&', $url);
    $url = preg_replace('/(&amp;)+/', '&amp;', $url);
    // header locates should not have the &amp; in the address it breaks things
    $url = str_replace('&amp;', '&', $url);

    if (STORE_PAGE_PARSE_TIME == 'true') {
      if (!is_object($logger)) $logger = new logger;
      $logger->timer_stop();
    }
    session_write_close();
    header('Location: ' . $url);
    exit;
  }

////
// Parse the data used in the html tags to ensure the tags will not break
  function zen_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }


  function zen_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string, ENT_COMPAT, CHARSET, FALSE);
    } else {
      if ($translate == false) {
        return zen_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return zen_parse_input_field_data($string, $translate);
      }
    }
  }


  function zen_output_string_protected($string) {
    return zen_output_string($string, false, true);
  }


  function zen_sanitize_string($string) {
    $string = preg_replace('/ +/', ' ', $string);

    return preg_replace("/[<>]/", '_', $string);
  }


  function zen_customers_name($customers_id) {
    global $db;
    $customers_values = $db->Execute("select customers_firstname, customers_lastname
                               from " . TABLE_CUSTOMERS . "
                               where customers_id = '" . (int)$customers_id . "'");
    if ($customers_values->EOF) return '';
    return $customers_values->fields['customers_firstname'] . ' ' . $customers_values->fields['customers_lastname'];
  }


  function zen_get_path($current_category_id = '') {
    global $cPath_array, $db;
// set to 0 if Top Level
    if ($current_category_id == '') {
      if (empty($cPath_array)) {
        $cPath_new= '';
      } else {
        $cPath_new = implode('_', $cPath_array);
      }
    } else {
      if (sizeof($cPath_array) == 0) {
        $cPath_new = $current_category_id;
      } else {
        $cPath_new = '';
        $last_category = $db->Execute("select parent_id
                                       from " . TABLE_CATEGORIES . "
                                       where categories_id = '" . (int)$cPath_array[(sizeof($cPath_array)-1)] . "'");

        $current_category = $db->Execute("select parent_id
                                          from " . TABLE_CATEGORIES . "
                                           where categories_id = '" . (int)$current_category_id . "'");

        if ($last_category->fields['parent_id'] == $current_category->fields['parent_id']) {
          for ($i = 0, $n = sizeof($cPath_array) - 1; $i < $n; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        } else {
          for ($i = 0, $n = sizeof($cPath_array); $i < $n; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        }

        $cPath_new .= '_' . $current_category_id;

        if (substr($cPath_new, 0, 1) == '_') {
          $cPath_new = substr($cPath_new, 1);
        }
      }
    }

    return 'cPath=' . $cPath_new;
  }

  function zen_get_all_get_params($exclude_array = array()) {
    if (!is_array($exclude_array)) $exclude_array = array();
    $exclude_array = array_merge($exclude_array, array('cmd', 'error', 'x', 'y')); // de-duplicating this is less performant than just letting it repeat the loop on duplicates
    if (function_exists('zen_session_name')) {
      $exclude_array[] = zen_session_name();
    }
    $get_url = '';
    if (is_array($_GET) && (sizeof($_GET) > 0)) {
      reset($_GET);
      while (list($key, $value) = each($_GET)) {
        if (!in_array($key, $exclude_array)) {
          if (!is_array($value)) {
//             if (is_numeric($value) || (is_string($value) && strlen($value) > 0)) {
            if (strlen($value) > 0) {
              $get_url .= zen_output_string_protected($key) . '=' . rawurlencode(stripslashes($value)) . '&';
            }
          } else {
            continue; // legacy code doesn't support passing arrays by GET, so skipping any arrays
            foreach(array_filter($value) as $arr){
              $get_url .= zen_output_string_protected($key) . '[]=' . rawurlencode(stripslashes($arr)) . '&';
            }
          }
        }
      }
    }
    $get_url = preg_replace('/&{2,}/', '&', $get_url);
    $get_url = preg_replace('/(&amp;)+/', '&amp;', $get_url);

    return $get_url;
  }

  /**
   * Return all GET params as (usually hidden) POST params
   * @param array $exclude_array
   * @param boolean $hidden
   * @return string
   */
  function zen_post_all_get_params($exclude_array = array(), $hidden = true) {
    if (!is_array($exclude_array)) $exclude_array = array();
    $exclude_array = array_merge($exclude_array, array(zen_session_name(), 'cmd', 'error', 'x', 'y'));
    $fields = '';
    if (is_array($_GET) && (sizeof($_GET) > 0)) {
      reset($_GET);
      while (list($key, $value) = each($_GET)) {
        if (!in_array($key, $exclude_array)) {
          if (!is_array($value)) {
            if (strlen($value) > 0) {
              if ($hidden) {
                $fields .= zen_draw_hidden_field($key, $value);
              } else {
                $fields .= zen_draw_input_field($key, $value);
              }
            }
          } else {
            foreach(array_filter($value) as $arr){
              if ($hidden) {
                $fields .= zen_draw_hidden_field($key . '[]', $arr);
              } else {
                $fields .= zen_draw_input_field($key . '[]', $arr);
              }
            }
          }
        }
      }
    }
    return $fields;
  }

  function zen_date_long($raw_date) {
    if ( ($raw_date == '0001-01-01 00:00:00') || ($raw_date == '') ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    return strftime(DATE_FORMAT_LONG, mktime($hour, $minute, $second, $month, $day, $year));
  }


////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function zen_date_short($raw_date) {
    if ( ($raw_date == '0001-01-01 00:00:00') || ($raw_date == '') ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

// error on 1969 only allows for leap year
    if ($year != 1969 && @date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return preg_replace('/2037$/', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }

  }


  function zen_datetime_short($raw_datetime) {
    if ( ($raw_datetime == '0001-01-01 00:00:00') || ($raw_datetime == '') ) return false;

    $year = (int)substr($raw_datetime, 0, 4);
    $month = (int)substr($raw_datetime, 5, 2);
    $day = (int)substr($raw_datetime, 8, 2);
    $hour = (int)substr($raw_datetime, 11, 2);
    $minute = (int)substr($raw_datetime, 14, 2);
    $second = (int)substr($raw_datetime, 17, 2);

    return strftime(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
  }


  function zen_get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $category_has_products = false, $limit = false) {
    global $db;

    if ($limit) {
      $limit_count = " limit 1";
    } else {
      $limit_count = '';
    }

    if (!is_array($category_tree_array)) $category_tree_array = array();
    if ( (sizeof($category_tree_array) < 1) && ($exclude != '0') ) $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

    if ($include_itself) {
      $category = $db->Execute("select cd.categories_name
                                from " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                where cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                and cd.categories_id = '" . (int)$parent_id . "'");

      $category_tree_array[] = array('id' => $parent_id, 'text' => $category->fields['categories_name']);
    }

    $categories = $db->Execute("select c.categories_id, cd.categories_name, c.parent_id
                                from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                where c.categories_id = cd.categories_id
                                and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                and c.parent_id = '" . (int)$parent_id . "'
                                order by c.sort_order, cd.categories_name");

    while (!$categories->EOF) {
      if ($category_has_products == true and zen_products_in_category_count($categories->fields['categories_id'], '', false, true) >= 1) {
        $mark = '*';
      } else {
        $mark = '&nbsp;&nbsp;';
      }
      if ($exclude != $categories->fields['categories_id']) $category_tree_array[] = array('id' => $categories->fields['categories_id'], 'text' => $spacing . $categories->fields['categories_name'] . $mark);
      $category_tree_array = zen_get_category_tree($categories->fields['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, '', $category_has_products);
      $categories->MoveNext();
    }

    return $category_tree_array;
  }


////
// products with name, model and price pulldown
  function zen_draw_products_pull_down($name, $parameters = '', $exclude = '', $show_id = false, $set_selected = false, $show_model = false, $show_current_category = false) {
    global $currencies, $db, $current_category_id;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    if ($show_current_category) {
// only show $current_categories_id
      $products = $db->Execute("select p.products_id, pd.products_name, p.products_price, p.products_model, ptc.categories_id
                                from " . TABLE_PRODUCTS . " p
                                left join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc on ptc.products_id = p.products_id, " .
                                TABLE_PRODUCTS_DESCRIPTION . " pd
                                where p.products_id = pd.products_id
                                and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                and ptc.categories_id = '" . (int)$current_category_id . "'
                                order by products_name");
    } else {
      $products = $db->Execute("select p.products_id, pd.products_name, p.products_price, p.products_model
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                where p.products_id = pd.products_id
                                and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                order by products_name");
    }

    while (!$products->EOF) {
      if (!in_array($products->fields['products_id'], $exclude)) {
        $display_price = zen_get_products_base_price($products->fields['products_id']);
        $select_string .= '<option value="' . $products->fields['products_id'] . '"';
        if ($set_selected == $products->fields['products_id']) $select_string .= ' SELECTED';
        $select_string .= '>' . $products->fields['products_name'] . ' (' . $currencies->format($display_price) . ')' . ($show_model ? ' [' . $products->fields['products_model'] . '] ' : '') . ($show_id ? ' - ID# ' . $products->fields['products_id'] : '') . '</option>';
      }
      $products->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }


  function zen_options_name($options_id) {
    global $db;

    $options_id = str_replace('txt_','',$options_id);

    $options_values = $db->Execute("select products_options_name
                                    from " . TABLE_PRODUCTS_OPTIONS . "
                                    where products_options_id = '" . (int)$options_id . "'
                                    and language_id = '" . (int)$_SESSION['languages_id'] . "'");
    if ($options_values->EOF) return '';
    return $options_values->fields['products_options_name'];
  }


  function zen_values_name($values_id) {
    global $db;

    $values_values = $db->Execute("select products_options_values_name
                                   from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   where products_options_values_id = '" . (int)$values_id . "'
                                   and language_id = '" . (int)$_SESSION['languages_id'] . "'");
    if ($values_values->EOF) return '';
    return $values_values->fields['products_options_values_name'];
  }


  function zen_info_image($image, $alt, $width = '', $height = '') {
    if (zen_not_null($image) && (file_exists(DIR_FS_CATALOG_IMAGES . $image)) ) {
      $image = zen_image(DIR_WS_CATALOG_IMAGES . $image, $alt, $width, $height);
    } else {
      $image = TEXT_IMAGE_NONEXISTENT;
    }

    return $image;
  }


  function zen_break_string($string, $len, $break_char = '-') {
    $l = 0;
    $output = '';
    for ($i=0, $n=strlen($string); $i<$n; $i++) {
      $char = substr($string, $i, 1);
      if ($char != ' ') {
        $l++;
      } else {
        $l = 0;
      }
      if ($l > $len) {
        $l = 1;
        $output .= $break_char;
      }
      $output .= $char;
    }

    return $output;
  }


  function zen_get_country_name($country_id) {
    global $db;
    $country = $db->Execute("select countries_name
                             from " . TABLE_COUNTRIES . "
                             where countries_id = '" . (int)$country_id . "'");

    if ($country->RecordCount() < 1) {
      return $country_id;
    } else {
      return $country->fields['countries_name'];
    }
  }


  function zen_get_country_name_cfg() {
    global $db;
    $country = $db->Execute("select countries_name
                             from " . TABLE_COUNTRIES . "
                             where countries_id = '" . (int)$country_id . "'");

    if ($country->RecordCount() < 1) {
      return $country_id;
    } else {
      return $country->fields['countries_name'];
    }
  }


  function zen_get_zone_name($country_id, $zone_id, $default_zone) {
    global $db;
    $zone = $db->Execute("select zone_name
                                from " . TABLE_ZONES . "
                                where zone_country_id = '" . (int)$country_id . "'
                                and zone_id = '" . (int)$zone_id . "'");

    if ($zone->RecordCount() > 0) {
      return $zone->fields['zone_name'];
    } else {
      return $default_zone;
    }
  }


  function zen_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } elseif( is_a( $value, 'queryFactoryResult' ) ) {
      if (sizeof($value->result) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if ($value != '' && $value != 'NULL' && strlen(trim($value)) > 0) {
        return true;
      } else {
        return false;
      }
    }
  }


  function zen_browser_detect($component) {

    return stristr($_SERVER['HTTP_USER_AGENT'], $component);
  }


  function zen_tax_classes_pull_down($parameters, $selected = '') {
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $classes = $db->Execute("select tax_class_id, tax_class_title
                             from " . TABLE_TAX_CLASS . "
                             order by tax_class_title");

    while (!$classes->EOF) {
      $select_string .= '<option value="' . $classes->fields['tax_class_id'] . '"';
      if ($selected == $classes->fields['tax_class_id']) $select_string .= ' SELECTED';
      $select_string .= '>' . $classes->fields['tax_class_title'] . '</option>';
      $classes->MoveNext();
    }
    $select_string .= '</select>';

    return $select_string;
  }


  function zen_geo_zones_pull_down($parameters, $selected = '') {
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $zones = $db->Execute("select geo_zone_id, geo_zone_name
                                 from " . TABLE_GEO_ZONES . "
                                 order by geo_zone_name");

    while (!$zones->EOF) {
      $select_string .= '<option value="' . $zones->fields['geo_zone_id'] . '"';
      if ($selected == $zones->fields['geo_zone_id']) $select_string .= ' SELECTED';
      $select_string .= '>' . $zones->fields['geo_zone_name'] . '</option>';
      $zones->MoveNext();
    }
    $select_string .= '</select>';

    return $select_string;
  }


  function zen_get_geo_zone_name($geo_zone_id) {
    global $db;
    $zones = $db->Execute("select geo_zone_name
                           from " . TABLE_GEO_ZONES . "
                           where geo_zone_id = '" . (int)$geo_zone_id . "'");

    if ($zones->RecordCount() < 1) {
      $geo_zone_name = $geo_zone_id;
    } else {
      $geo_zone_name = $zones->fields['geo_zone_name'];
    }

    return $geo_zone_name;
  }


// USED FROM functions_customers
/*
  function zen_address_format($address_format_id, $address, $html, $boln, $eoln) {
    global $db;
    $address_format = $db->Execute("select address_format as format
                             from " . TABLE_ADDRESS_FORMAT . "
                             where address_format_id = '" . (int)$address_format_id . "'");

    $company = zen_output_string_protected($address['company']);
    if (isset($address['firstname']) && zen_not_null($address['firstname'])) {
      $firstname = zen_output_string_protected($address['firstname']);
      $lastname = zen_output_string_protected($address['lastname']);
    } elseif (isset($address['name']) && zen_not_null($address['name'])) {
      $firstname = zen_output_string_protected($address['name']);
      $lastname = '';
    } else {
      $firstname = '';
      $lastname = '';
    }
    $street = zen_output_string_protected($address['street_address']);
    $suburb = zen_output_string_protected($address['suburb']);
    $city = zen_output_string_protected($address['city']);
    $state = zen_output_string_protected($address['state']);
    if (isset($address['country_id']) && zen_not_null($address['country_id'])) {
      $country = zen_get_country_name($address['country_id']);

      if (isset($address['zone_id']) && zen_not_null($address['zone_id'])) {
        $state = zen_get_zone_code($address['country_id'], $address['zone_id'], $state);
      }
    } elseif (isset($address['country']) && zen_not_null($address['country'])) {
      $country = zen_output_string_protected($address['country']);
    } else {
      $country = '';
    }
    $postcode = zen_output_string_protected($address['postcode']);
    $zip = $postcode;

    if ($html) {
// HTML Mode
      $HR = '<hr />';
      $hr = '<hr />';
      if ( ($boln == '') && ($eoln == "\n") ) { // Values not specified, use rational defaults
        $CR = '<br />';
        $cr = '<br />';
        $eoln = $cr;
      } else { // Use values supplied
        $CR = $eoln . $boln;
        $cr = $CR;
      }
    } else {
// Text Mode
      $CR = $eoln;
      $cr = $CR;
      $HR = '----------------------------------------';
      $hr = '----------------------------------------';
    }

    $statecomma = '';
    $streets = $street;
    if ($suburb != '') $streets = $street . $cr . $suburb;
    if ($country == '') $country = zen_output_string_protected($address['country']);
    if ($state != '') $statecomma = $state . ', ';

    $fmt = $address_format->fields['format'];
    eval("\$address = \"$fmt\";");

    if ( (ACCOUNT_COMPANY == 'true') && (zen_not_null($company)) ) {
      $address = $company . $cr . $address;
    }

    return $address;
  }
*/

  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : zen_get_zone_code
  //
  // Arguments   : country_id           country code string
  //               zone_id              state/province zone_id
  //               default_zone         default string if zone==0
  //
  // Return      : state_prov_code   s  tate/province code
  //
  // Description : Function to retrieve the state/province code (as in FL for Florida etc)
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
  function zen_get_zone_code($country_id, $zone_id, $default_zone) {
    global $db;
    $zone_query = "select zone_code
                   from " . TABLE_ZONES . "
                   where zone_country_id = '" . (int)$country_id . "'
                   and zone_id = '" . (int)$zone_id . "'";

    $zone = $db->Execute($zone_query);

    if ($zone->RecordCount() > 0) {
      return $zone->fields['zone_code'];
    } else {
      return $default_zone;
    }
  }

  function zen_get_uprid($prid, $params) {
    $uprid = $prid;
    if ( (is_array($params)) && (!strstr($prid, '{')) ) {
      while (list($option, $value) = each($params)) {
        $uprid = $uprid . '{' . $option . '}' . $value;
      }
    }

    return $uprid;
  }


  function zen_get_prid($uprid) {
    $pieces = explode('{', $uprid);

    return $pieces[0];
  }

  /**
   * helper function to access language::available_languages object
   * @return array
   */
  function zen_get_languages() {
    global $lng;

    if (!isset($lng)) {
      $lng = new language();
    }

    return $lng->get_available_languages();
  }

  function zen_get_category_name($category_id, $language_id) {
    global $db;
    $category = $db->Execute("select categories_name
                              from " . TABLE_CATEGORIES_DESCRIPTION . "
                              where categories_id = '" . (int)$category_id . "'
                              and language_id = '" . (int)$language_id . "'");
    if ($category->EOF) return '';
    return $category->fields['categories_name'];
  }


  function zen_get_category_description($category_id, $language_id) {
    global $db;
    $category = $db->Execute("select categories_description
                              from " . TABLE_CATEGORIES_DESCRIPTION . "
                              where categories_id = '" . (int)$category_id . "'
                              and language_id = '" . (int)$language_id . "'");
    if ($category->EOF) return '';
    return $category->fields['categories_description'];
  }


  function zen_get_orders_status_name($orders_status_id, $language_id = '') {
    global $db;

    if (!$language_id) $language_id = $_SESSION['languages_id'];
    $orders_status = $db->Execute("select orders_status_name
                                   from " . TABLE_ORDERS_STATUS . "
                                   where orders_status_id = '" . (int)$orders_status_id . "'
                                   and language_id = '" . (int)$language_id . "'");
    if ($orders_status->EOF) return '';
    return $orders_status->fields['orders_status_name'];
  }


  function zen_get_orders_status() {
    global $db;

    $orders_status_array = array();
    $orders_status = $db->Execute("select orders_status_id, orders_status_name
                                   from " . TABLE_ORDERS_STATUS . "
                                   where language_id = '" . (int)$_SESSION['languages_id'] . "'
                                   order by orders_status_id");

    while (!$orders_status->EOF) {
      $orders_status_array[] = array('id' => $orders_status->fields['orders_status_id'],
                                     'text' => $orders_status->fields['orders_status_name']);
      $orders_status->MoveNext();
    }

    return $orders_status_array;
  }


  function zen_get_products_name($product_id, $language_id = 0) {
    global $db;

    if ($language_id == 0) $language_id = $_SESSION['languages_id'];
    $product = $db->Execute("select products_name
                             from " . TABLE_PRODUCTS_DESCRIPTION . "
                             where products_id = '" . (int)$product_id . "'
                             and language_id = '" . (int)$language_id . "'");
    if ($product->EOF) return '';
    return $product->fields['products_name'];
  }


  function zen_get_products_description($product_id, $language_id) {
    global $db;
    $product = $db->Execute("select products_description
                             from " . TABLE_PRODUCTS_DESCRIPTION . "
                             where products_id = '" . (int)$product_id . "'
                             and language_id = '" . (int)$language_id . "'");
    if ($product->EOF) return '';
    return $product->fields['products_description'];
  }


  function zen_get_products_url($product_id, $language_id) {
    global $db;
    $product = $db->Execute("select products_url
                             from " . TABLE_PRODUCTS_DESCRIPTION . "
                             where products_id = '" . (int)$product_id . "'
                             and language_id = '" . (int)$language_id . "'");
    if ($product->EOF) return '';
    return $product->fields['products_url'];
  }


////
// Return the manufacturers URL in the needed language
// TABLES: manufacturers_info
  function zen_get_manufacturer_url($manufacturer_id, $language_id) {
    global $db;
    $manufacturer = $db->Execute("select manufacturers_url
                                  from " . TABLE_MANUFACTURERS_INFO . "
                                  where manufacturers_id = '" . (int)$manufacturer_id . "'
                                  and languages_id = '" . (int)$language_id . "'");
    if ($manufacturer->EOF) return '';
    return $manufacturer->fields['manufacturers_url'];
  }


////
// Count how many products exist in a category
// TABLES: products, products_to_categories, categories
  function zen_products_in_category_count($categories_id, $include_deactivated = false, $include_child = true, $limit = false) {
    global $db;
    $products_count = 0;

    if ($limit) {
      $limit_count = ' limit 1';
    } else {
      $limit_count = '';
    }

    if ($include_deactivated) {

      $products = $db->Execute("select count(*) as total
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                where p.products_id = p2c.products_id
                                and p2c.categories_id = '" . (int)$categories_id . "'" . $limit_count);
    } else {
      $products = $db->Execute("select count(*) as total
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                where p.products_id = p2c.products_id
                                and p.products_status = 1
                                and p2c.categories_id = '" . (int)$categories_id . "'" . $limit_count);

    }

    $products_count += $products->fields['total'];

    if ($include_child) {
      $childs = $db->Execute("select categories_id from " . TABLE_CATEGORIES . "
                              where parent_id = '" . (int)$categories_id . "'");
      if ($childs->RecordCount() > 0 ) {
        while (!$childs->EOF) {
          $products_count += zen_products_in_category_count($childs->fields['categories_id'], $include_deactivated);
          $childs->MoveNext();
        }
      }
    }
    return $products_count;
  }


////
// Count how many subcategories exist in a category
// TABLES: categories
  function zen_childs_in_category_count($categories_id) {
    global $db;
    $categories_count = 0;

    $categories = $db->Execute("select categories_id
                                from " . TABLE_CATEGORIES . "
                                where parent_id = '" . (int)$categories_id . "'");

    while (!$categories->EOF) {
      $categories_count++;
      $categories_count += zen_childs_in_category_count($categories->fields['categories_id']);
      $categories->MoveNext();
    }

    return $categories_count;
  }


////
// Returns an array with countries
// TABLES: countries
  function zen_get_countries($default = '') {
    global $db;
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }
    $countries = $db->Execute("select countries_id, countries_name
                               from " . TABLE_COUNTRIES . "
                               order by countries_name");

    while (!$countries->EOF) {
      $countries_array[] = array('id' => $countries->fields['countries_id'],
                                 'text' => $countries->fields['countries_name']);
      $countries->MoveNext();
    }

    return $countries_array;
  }


////
// return an array with country zones
  function zen_get_country_zones($country_id) {
    global $db;
    $zones_array = array();
    $zones = $db->Execute("select zone_id, zone_name
                           from " . TABLE_ZONES . "
                           where zone_country_id = '" . (int)$country_id . "'
                           order by zone_name");

    while (!$zones->EOF) {
      $zones_array[] = array('id' => $zones->fields['zone_id'],
                             'text' => $zones->fields['zone_name']);
      $zones->MoveNext();
    }

    return $zones_array;
  }


  function zen_prepare_country_zones_pull_down($country_id = '') {
// preset the width of the drop-down for Netscape
    $pre = '';
    if ( (!zen_browser_detect('MSIE')) && (zen_browser_detect('Mozilla/4')) ) {
      for ($i=0; $i<45; $i++) $pre .= '&nbsp;';
    }

    $zones = zen_get_country_zones($country_id);

    if (sizeof($zones) > 0) {
      $zones_select = array(array('id' => '', 'text' => PLEASE_SELECT));
      $zones = array_merge($zones_select, $zones);
    } else {
      $zones = array(array('id' => '', 'text' => TYPE_BELOW));
// create dummy options for Netscape to preset the height of the drop-down
      if ( (!zen_browser_detect('MSIE')) && (zen_browser_detect('Mozilla/4')) ) {
        for ($i=0; $i<9; $i++) {
          $zones[] = array('id' => '', 'text' => $pre);
        }
      }
    }

    return $zones;
  }


////
// Get list of address_format_id's
  function zen_get_address_formats() {
    global $db;
    $address_format_values = $db->Execute("select address_format_id
                                           from " . TABLE_ADDRESS_FORMAT . "
                                           order by address_format_id");

    $address_format_array = array();
    while (!$address_format_values->EOF) {
      $address_format_array[] = array('id' => $address_format_values->fields['address_format_id'],
                                      'text' => $address_format_values->fields['address_format_id']);
      $address_format_values->MoveNext();
    }
    return $address_format_array;
  }


////
  function zen_cfg_select_coupon_id($coupon_id, $key = '') {
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $coupons = $db->execute("select cd.coupon_name, c.coupon_id from " . TABLE_COUPONS ." c, ". TABLE_COUPONS_DESCRIPTION . " cd where cd.coupon_id = c.coupon_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
    $coupon_array[] = array('id' => '0',
                            'text' => 'None');

    while (!$coupons->EOF) {
      $coupon_array[] = array('id' => $coupons->fields['coupon_id'],
                              'text' => $coupons->fields['coupon_name']);
      $coupons->MoveNext();
    }
    return zen_draw_pull_down_menu($name, $coupon_array, $coupon_id);
  }


////
// Alias function for Store configuration values in the Administration Tool
  function zen_cfg_pull_down_country_list($country_id, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, zen_get_countries(), $country_id);
  }


////
  function zen_cfg_pull_down_country_list_none($country_id, $key = '') {
    $country_array = zen_get_countries('None');
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, $country_array, $country_id);
  }


////
  function zen_cfg_pull_down_zone_list($zone_id, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, zen_get_country_zones(STORE_COUNTRY), $zone_id);
  }


////
  function zen_cfg_pull_down_tax_classes($tax_class_id, $key = '') {
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class = $db->Execute("select tax_class_id, tax_class_title
                               from " . TABLE_TAX_CLASS . "
                               order by tax_class_title");

    while (!$tax_class->EOF) {
      $tax_class_array[] = array('id' => $tax_class->fields['tax_class_id'],
                                 'text' => $tax_class->fields['tax_class_title']);
      $tax_class->MoveNext();
    }

    return zen_draw_pull_down_menu($name, $tax_class_array, $tax_class_id);
  }


////
// Function to read in text area in admin
 function zen_cfg_textarea($text, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_textarea_field($name, false, 60, 5, htmlspecialchars($text, ENT_COMPAT, CHARSET, FALSE));
  }


////
// Function to read in text area in admin
 function zen_cfg_textarea_small($text, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_textarea_field($name, false, 35, 1, htmlspecialchars($text, ENT_COMPAT, CHARSET, FALSE), 'class="noEditor" autofocus');
  }


  function zen_cfg_get_zone_name($zone_id) {
    global $db;
    $zone = $db->Execute("select zone_name
                          from " . TABLE_ZONES . "
                          where zone_id = '" . (int)$zone_id . "'");

    if ($zone->RecordCount() < 1) {
      return $zone_id;
    } else {
      return $zone->fields['zone_name'];
    }
  }

  function zen_cfg_pull_down_htmleditors($html_editor, $key = '') {
    global $editors_list;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $editors_pulldown = array();
    foreach($editors_list as $key=>$value) {
      $editors_pulldown[] = array('id' => $key, 'text' => $value['desc']);
    }
    return zen_draw_pull_down_menu($name, $editors_pulldown, $html_editor);
  }

  function zen_cfg_pull_down_exchange_rate_sources($source, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $pulldown = array();
    $pulldown[] = array('id' => TEXT_NONE, 'text' => TEXT_NONE);
    $funcs = get_defined_functions();
    $funcs = $funcs['user'];
    sort($funcs);
    foreach ($funcs as $func) {
      if (preg_match('/quote_(.*)_currency/', $func, $regs)) {
        $pulldown[] = array('id' => $regs[1], 'text' => $regs[1]);
      }
    }
    return zen_draw_pull_down_menu($name, $pulldown, $source);
  }

  function zen_cfg_password_input($value, $key = '') {
    if (function_exists('dbenc_is_encrypted_value_key') && dbenc_is_encrypted_value_key($key)) {
      $value = dbenc_decrypt($value);
    }
    return zen_draw_password_field('configuration[' . $key . ']', $value);
  }

  function zen_cfg_password_display($value) {
    $length = strlen($value);
    return str_repeat('*', ($length > 16 ? 16 : $length));
  }

////
// Sets the status of a product
  function zen_set_product_status($products_id, $status) {
    global $db;
    if ($status == '1') {
      return $db->Execute("update " . TABLE_PRODUCTS . "
                           set products_status = 1, products_last_modified = now()
                           where products_id = '" . (int)$products_id . "'");

    } elseif ($status == '0') {
      return $db->Execute("update " . TABLE_PRODUCTS . "
                           set products_status = 0, products_last_modified = now()
                           where products_id = '" . (int)$products_id . "'");

    } else {
      return -1;
    }
  }


////
// Sets timeout for the current script.
  function zen_set_time_limit($limit) {
    @set_time_limit($limit);
  }


////
// Alias function for Store configuration values in the Administration Tool
  function zen_cfg_select_option($select_array, $key_value, $key = '') {
    $string = '';

    for ($i=0, $n=sizeof($select_array); $i<$n; $i++) {
      $name = ((zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');

      $string .= '<br><input type="radio" name="' . $name . '" value="' . $select_array[$i] . '"';

      if ($key_value == $select_array[$i]) $string .= ' CHECKED';

      $string .= ' id="' . strtolower($select_array[$i] . '-' . $name) . '"> ' . '<label for="' . strtolower($select_array[$i] . '-' . $name) . '" class="inputSelect">' . $select_array[$i] . '</label>';
    }

    return $string;
  }


  function zen_cfg_select_drop_down($select_array, $key_value, $key = '') {
    $string = '';

    $name = ((zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, $select_array, (int)$key_value);
  }

////
// Alias function for module configuration keys
  function zen_mod_select_option($select_array, $key_name, $key_value) {
    reset($select_array);
    while (list($key, $value) = each($select_array)) {
      if (is_int($key)) $key = $value;
      $string .= '<br><input type="radio" name="configuration[' . $key_name . ']" value="' . $key . '"';
      if ($key_value == $key) $string .= ' CHECKED';
      $string .= '> ' . $value;
    }

    return $string;
  }

////
// Retreive server information
  function zen_get_system_information() {
    global $db;

    // determine database size stats
    $indsize = 0;
    $datsize = 0;
    $result = $db->Execute("SHOW TABLE STATUS" . (DB_PREFIX == '' ? '' : " LIKE '" . str_replace('_', '\_', DB_PREFIX) . "%'"));
    while (!$result->EOF) {
      $datsize += $result->fields['Data_length'];
      $indsize += $result->fields['Index_length'];
      $result->MoveNext();
    }

    $strictmysql = false;
    $mysql_mode = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'sql\_mode'");
    if (!$result->EOF) {
      $mysql_mode = $result->fields['Value'];
      if (strstr($result->fields['Value'], 'strict_')) $strictmysql = true;
    }
    $mysql_slow_query_log_status = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'slow\_query\_log'");
    if (!$result->EOF) {
      $mysql_slow_query_log_status = $result->fields['Value'];
    }
    $mysql_slow_query_log_file = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'slow\_query\_log\_file'");
    if (!$result->EOF) {
      $mysql_slow_query_log_file = $result->fields['Value'];
    }
    $result = $db->Execute("select now() as datetime");
    $mysql_date = $result->fields['datetime'];

    $errnum = 0;
    $system = $host = $kernel = $output = '';
    list($system, $host, $kernel) = array('', $_SERVER['SERVER_NAME'], php_uname());
    $uptime = (DISPLAY_SERVER_UPTIME == 'true') ? 'Unsupported' : 'Disabled/Unavailable';

    // check to see if "exec()" is disabled in PHP -- if not, get additional info via command line
    $php_disabled_functions = '';
    $exec_disabled = false;
    $php_disabled_functions = @ini_get("disable_functions");
    if ($php_disabled_functions != '') {
      if (in_array('exec', preg_split('/,/', str_replace(' ', '', $php_disabled_functions)))) {
        $exec_disabled = true;
      }
    }
    if (!$exec_disabled) {
      @exec('uname -a 2>&1', $output, $errnum);
      if ($errnum == 0 && sizeof($output)) list($system, $host, $kernel) = preg_split('/[\s,]+/', $output[0], 5);
      $output = '';
      if (DISPLAY_SERVER_UPTIME == 'true') {
        @exec('uptime 2>&1', $output, $errnum);
        if ($errnum == 0) {
          $uptime = $output[0];
        }
      }
    }

    return array('date' => zen_datetime_short(date('Y-m-d H:i:s')),
                 'system' => $system,
                 'kernel' => $kernel,
                 'host' => $host,
                 'ip' => gethostbyname($host),
                 'uptime' => $uptime,
                 'http_server' => $_SERVER['SERVER_SOFTWARE'],
                 'php' => PHP_VERSION,
                 'zend' => (function_exists('zend_version') ? zend_version() : ''),
                 'db_server' => DB_SERVER,
                 'db_ip' => gethostbyname(DB_SERVER),
                 'db_version' => 'MySQL ' . $db->get_server_info(),
                 'db_date' => zen_datetime_short($mysql_date),
                 'php_memlimit' => @ini_get('memory_limit'),
                 'php_file_uploads' => strtolower(@ini_get('file_uploads')),
                 'php_uploadmaxsize' => @ini_get('upload_max_filesize'),
                 'php_postmaxsize' => @ini_get('post_max_size'),
                 'database_size' => $datsize,
                 'index_size' => $indsize,
                 'mysql_strict_mode' => $strictmysql,
                 'mysql_mode' => $mysql_mode,
                 'mysql_slow_query_log_status' => $mysql_slow_query_log_status,
                 'mysql_slow_query_log_file' => $mysql_slow_query_log_file,
                 );
  }

  function zen_generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
    global $db;

    if (!is_array($categories_array)) $categories_array = array();

    if ($from == 'product') {
      $categories = $db->Execute("select categories_id
                                  from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                  where products_id = '" . (int)$id . "'");

      while (!$categories->EOF) {
        if ($categories->fields['categories_id'] == '0') {
          $categories_array[$index][] = array('id' => '0', 'text' => TEXT_TOP);
        } else {
          $category = $db->Execute("select cd.categories_name, c.parent_id
                                    from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                    where c.categories_id = '" . (int)$categories->fields['categories_id'] . "'
                                    and c.categories_id = cd.categories_id
                                    and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");

          $categories_array[$index][] = array('id' => $categories->fields['categories_id'], 'text' => $category->fields['categories_name']);
          if ( (zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0') ) $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
          $categories_array[$index] = array_reverse($categories_array[$index]);
        }
        $index++;
        $categories->MoveNext();
      }
    } elseif ($from == 'category') {
      $category = $db->Execute("select cd.categories_name, c.parent_id
                                from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                where c.categories_id = '" . (int)$id . "'
                                and c.categories_id = cd.categories_id
                                and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
      if (!$category->EOF) {
        $categories_array[$index][] = array('id' => $id, 'text' => $category->fields['categories_name']);
        if ( (zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0') ) $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
      }
    }

    return $categories_array;
  }

  function zen_output_generated_category_path($id, $from = 'category') {
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($id, $from);

    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        if ($from == 'category') {
          $calculated_category_path_string = $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;' . $calculated_category_path_string;
        } else {
          $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'];
          $calculated_category_path_string .= ' [ ' . TEXT_INFO_ID . $calculated_category_path[$i][$j]['id'] . ' ] ';
          $calculated_category_path_string .= '<br>';
          $calculated_category_path_string .= '&nbsp;&nbsp;';
//           $calculated_category_path_string .= '&nbsp;&gt;&nbsp;';
        }
      }
      if ($from == 'product') {
        $calculated_category_path_string .= '<br>';
      }
    }
    $calculated_category_path_string = preg_replace('/&nbsp;&gt;&nbsp;$/', '', $calculated_category_path_string);
    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
  }

  function zen_get_generated_category_path_ids($id, $from = 'category') {
    global $db;
    $calculated_category_path_string = '';
    $calculated_category_path = zen_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['id'] . '_';
      }
      $calculated_category_path_string = rtrim($calculated_category_path_string, '_') . '<br>';
    }
    $calculated_category_path_string = preg_replace('/<br>$/', '', $calculated_category_path_string);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
  }

  function zen_remove_category($category_id) {
    if ((int)$category_id == 0) return;
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_CATEGORY', array(), $category_id);

    // delete from salemaker - sale_categories_selected
    $chk_sale_categories_selected = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . "
    WHERE
    sale_categories_selected = '" . (int)$category_id . "'
    OR sale_categories_selected LIKE '%," . (int)$category_id . ",%'
    OR sale_categories_selected LIKE '%," . (int)$category_id . "'
    OR sale_categories_selected LIKE '" . (int)$category_id . ",%'");

    // delete from salemaker - sale_categories_all
    $chk_sale_categories_all = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . "
    WHERE
    sale_categories_all = '" . (int)$category_id . "'
    OR sale_categories_all LIKE '%," . (int)$category_id . ",%'
    OR sale_categories_all LIKE '%," . (int)$category_id . "'
    OR sale_categories_all LIKE '" . (int)$category_id . ",%'");

//echo 'WORKING ON: ' . (int)$category_id . ' chk_sale_categories_selected: ' . $chk_sale_categories_selected->RecordCount() . ' chk_sale_categories_all: ' . $chk_sale_categories_all->RecordCount() . '<br>';
while (!$chk_sale_categories_selected->EOF) {
  $skip_cats = false; // used when deleting
  $skip_sale_id = 0;
//echo '<br>FIRST LOOP: sale_id ' . $chk_sale_categories_selected->fields['sale_id'] . ' sale_categories_selected: ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
  // 9 or ,9 or 9,
  // delete record if sale_categories_selected = 9 and  sale_categories_all = ,9,
  if ($chk_sale_categories_selected->fields['sale_categories_selected'] == (int)$category_id and $chk_sale_categories_selected->fields['sale_categories_all'] == ',' . (int)$category_id . ',') { // delete record
//echo 'A: I should delete this record sale_id: ' . $chk_sale_categories_selected->fields['sale_id'] . '<br><br>';
    $skip_cats = true;
    $skip_sale_id = $chk_sale_categories_selected->fields['sale_id'];
    $salemakerdelete = "DELETE from " . TABLE_SALEMAKER_SALES . " WHERE sale_id='"  . $skip_sale_id . "'";
  }

  // if in the front - remove 9,
  //  if ($chk_sale_categories_selected->fields['sale_categories_selected'] == (int)$category_id . ',') { // front
  if (!$skip_cats && (preg_match('/^' . (int)$category_id . ',/', $chk_sale_categories_selected->fields['sale_categories_selected'])) ) { // front
//echo 'B: I need to remove - ' . (int)$category_id . ', - from the front of ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], strlen((int)$category_id . ','));
//echo 'B: new_sale_categories_selected: ' . $new_sale_categories_selected . '<br><br>';
  }

  // if in the middle or end - remove ,9,
  if (!$skip_cats && (strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',')) ) { // middle or end
//echo 'C: I need to remove - ,' . (int)$category_id . ', - from the middle or end ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $start_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',') + strlen(',' . (int)$category_id . ',');
    $end_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',', $start_cat+strlen(',' . (int)$category_id . ','));
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1)) . substr($chk_sale_categories_selected->fields['sale_categories_selected'], $start_cat);
//echo 'C: new_sale_categories_selected: ' . $new_sale_categories_selected. '<br><br>';
    $skip_cat_last = true;
  }


// not needed in loop 1 if middle does end
  // if on the end - remove ,9 skip if middle cleaned it
  if (!$skip_cats && !$skip_cat_last && (strripos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id)) ) { // end
    $start_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id) + strlen(',' . (int)$category_id);
//echo 'D: I need to remove - ,' . (int)$category_id . ' - from the end ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1));
//echo 'D: new_sale_categories_selected: ' . $new_sale_categories_selected. '<br><br>';
  }

  if (!$skip_cats) {
    $salemakerupdate =
    "UPDATE " . TABLE_SALEMAKER_SALES . "
    SET sale_categories_selected='" . $new_sale_categories_selected . "'
    WHERE sale_id = '" . $chk_sale_categories_selected->fields['sale_id'] . "'";
//echo 'Update new_sale_categories_selected: ' . $salemakerupdate . '<br>';
    $db->Execute($salemakerupdate);
  } else {
//echo 'Record was deleted sale_id ' . $skip_sale_id . '<br>' . $salemakerdelete;
    $db->Execute($salemakerdelete);
  }

  $chk_sale_categories_selected->MoveNext();
}

while (!$chk_sale_categories_all->EOF) {
//echo '<br><br>SECOND LOOP: sale_id ' . $chk_sale_categories_all->fields['sale_id'] . ' sale_categories_all: ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br><br>';
  // remove ,9 if on front as ,9, - remove ,9 if in the middle as ,9, - remove ,9 if on the end as ,9,
  // beware of ,79, or ,98, or ,99, when cleaning 9
  // if ($chk_sale_categories_all->fields['sale_categories_all'] == ',9') { // front
  // if (something for the middle) { // middle
  // if (right($chk_sale_categories_all->fields['sale_categories_all']) == ',9,') { // end

  $skip_cats = false;
  if ($skip_sale_id == $chk_sale_categories_all->fields['sale_id']) { // was deleted
//echo 'A: I should delete this record sale_id: ' . $chk_sale_categories_all->fields['sale_id'] . ' but already done' . '<br><br>';
    $skip_cats = true;
  }

  // if in the front - remove 9,
  //  if ($chk_sale_categories_all->fields['sale_categories_all'] == (int)$category_id . ',') { // front
  if (!$skip_cats && (preg_match('/^' . ',' . (int)$category_id . ',/', $chk_sale_categories_all->fields['sale_categories_all'])) ) { // front
//echo 'B: I need to remove - ' . (int)$category_id . ', - from the front of ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], strlen(',' . (int)$category_id));
//echo 'B: new_sale_categories_all: ' . $new_sale_categories_all . '<br><br>';
  }

  // if in the middle or end - remove ,9,
  if (!$skip_cats && (strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',')) ) { // middle
//echo 'C: I need to remove - ,' . (int)$category_id . ', - from the middle or end ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $start_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',') + strlen(',' . (int)$category_id . ',');
    $end_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',', $start_cat+strlen(',' . (int)$category_id . ','));
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1)) . substr($chk_sale_categories_all->fields['sale_categories_all'], $start_cat);
//echo 'C: new_sale_categories_all: ' . $new_sale_categories_all. '<br><br>';
  }

/*
// not needed in loop 2
  // if on the end - remove ,9,
  if (!$skip_cats && (strripos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',')) ) { // end
    $start_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id) + strlen(',' . (int)$category_id . ',');
    echo 'D: I need to remove from the end - ,' . (int)$category_id . ', - from the end ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1));
    echo 'D: new_sale_categories_all: ' . $new_sale_categories_all. '<br><br>';
  }
*/
      $salemakerupdate = "UPDATE " . TABLE_SALEMAKER_SALES . " SET sale_categories_all='" . $new_sale_categories_all . "' WHERE sale_id = '" . $chk_sale_categories_all->fields['sale_id'] . "'";

//echo 'Update sale_categories_all: ' . $salemakerupdate . '<br>';

      $db->Execute($salemakerupdate);

      $chk_sale_categories_all->MoveNext();
}

//die('DONE TESTING');

    $category_image = $db->Execute("select categories_image
                                    from " . TABLE_CATEGORIES . "
                                    where categories_id = '" . (int)$category_id . "'");

    $duplicate_image = $db->Execute("select count(*) as total
                                     from " . TABLE_CATEGORIES . "
                                     where categories_image = '" .
                                           zen_db_input($category_image->fields['categories_image']) . "'");
    if ($duplicate_image->fields['total'] < 2) {
      if (file_exists(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image']);
      }
    }

    $db->Execute("delete from " . TABLE_CATEGORIES . "
                  where categories_id = '" . (int)$category_id . "'");

    $db->Execute("delete from " . TABLE_CATEGORIES_DESCRIPTION . "
                  where categories_id = '" . (int)$category_id . "'");

    $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                  where categories_id = '" . (int)$category_id . "'");

    $db->Execute("delete from " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                  where categories_id = '" . (int)$category_id . "'");

    $db->Execute("delete from " . TABLE_COUPON_RESTRICT . "
                  where category_id = '" . (int)$category_id . "'");

    zen_record_admin_activity('Deleted category ' . (int)$category_id . ' from database via admin console.', 'warning');
  }

  function zen_remove_product($product_id, $ptc = 'true') {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT', array(), $product_id, $ptc);

    $product_image = $db->Execute("select products_image
                                   from " . TABLE_PRODUCTS . "
                                   where products_id = '" . (int)$product_id . "'");

    $duplicate_image = $db->Execute("select count(*) as total
                                     from " . TABLE_PRODUCTS . "
                                     where products_image = '" . zen_db_input($product_image->fields['products_image']) . "'");

    if ($duplicate_image->fields['total'] < 2 and $product_image->fields['products_image'] != '' && PRODUCTS_IMAGE_NO_IMAGE != substr($product_image->fields['products_image'], strrpos($product_image->fields['products_image'], '/')+1)) {
      $products_image = $product_image->fields['products_image'];
      $products_image_extension = substr($products_image, strrpos($products_image, '.'));
      $products_image_base = preg_replace('/' . $products_image_extension . '/', '', $products_image);

      $filename_medium = 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extension;
      $filename_large = 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extension;

      if (file_exists(DIR_FS_CATALOG_IMAGES . $product_image->fields['products_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $product_image->fields['products_image']);
      }
      if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_medium)) {
        @unlink(DIR_FS_CATALOG_IMAGES . $filename_medium);
      }
      if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_large)) {
        @unlink(DIR_FS_CATALOG_IMAGES . $filename_large);
      }
    }

    $db->Execute("delete from " . TABLE_SPECIALS . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_PRODUCTS . "
                  where products_id = '" . (int)$product_id . "'");

//    if ($ptc == 'true') {
      $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                    where products_id = '" . (int)$product_id . "'");
//    }

    $db->Execute("delete from " . TABLE_PRODUCTS_DESCRIPTION . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                  where products_id = '" . (int)$product_id . "'");

    zen_products_attributes_download_delete($product_id);

    $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                  where products_id = '" . (int)$product_id . "'");


    $product_reviews = $db->Execute("select reviews_id
                                     from " . TABLE_REVIEWS . "
                                     where products_id = '" . (int)$product_id . "'");

    while (!$product_reviews->EOF) {
      $db->Execute("delete from " . TABLE_REVIEWS_DESCRIPTION . "
                    where reviews_id = '" . (int)$product_reviews->fields['reviews_id'] . "'");
      $product_reviews->MoveNext();
    }
    $db->Execute("delete from " . TABLE_REVIEWS . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_FEATURED . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_COUPON_RESTRICT . "
                  where product_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                  where products_id = '" . (int)$product_id . "'");

    zen_record_admin_activity('Deleted product ' . (int)$product_id . ' from database via admin console.', 'warning');
  }

  function zen_products_attributes_download_delete($product_id) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_PRODUCTS_ATTRIBUTES_DOWNLOAD_DELETE', array(), $product_id);

  // remove downloads if they exist
    $remove_downloads= $db->Execute("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id= '" . (int)$product_id . "'");
    while (!$remove_downloads->EOF) {
      $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " where products_attributes_id= '" . $remove_downloads->fields['products_attributes_id'] . "'");
      $remove_downloads->MoveNext();
    }
  }

  function zen_remove_order($order_id, $restock = false) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_ORDER', array(), $order_id, $restock);
    if ($restock == 'on') {
      $order = $db->Execute("select products_id, products_quantity
                             from " . TABLE_ORDERS_PRODUCTS . "
                             where orders_id = '" . (int)$order_id . "'");

      while (!$order->EOF) {
        $db->Execute("update " . TABLE_PRODUCTS . "
                      set products_quantity = products_quantity + " . $order->fields['products_quantity'] . ", products_ordered = products_ordered - " . $order->fields['products_quantity'] . " where products_id = '" . (int)$order->fields['products_id'] . "'");
        $order->MoveNext();
      }
    }

    $db->Execute("delete from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_ORDERS_STATUS_HISTORY . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_ORDERS_TOTAL . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_COUPON_GV_QUEUE . "
                  where order_id = '" . (int)$order_id . "' and release_flag = 'N'");

    zen_record_admin_activity('Deleted order ' . (int)$order_id . ' from database via admin console.', 'warning');
  }

  function zen_get_file_permissions($mode) {
// determine type
    if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
      $type = 's';
    } elseif ( ($mode & 0x4000) == 0x4000) { // directory
      $type = 'd';
    } elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
      $type = 'l';
    } elseif ( ($mode & 0x8000) == 0x8000) { // regular file
      $type = '-';
    } elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
      $type = 'b';
    } elseif ( ($mode & 0x2000) == 0x2000) { // character special file
      $type = 'c';
    } elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
      $type = 'p';
    } else { // unknown
      $type = '?';
    }

// determine permissions
    $owner['read']    = ($mode & 00400) ? 'r' : '-';
    $owner['write']   = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read']    = ($mode & 00040) ? 'r' : '-';
    $group['write']   = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read']    = ($mode & 00004) ? 'r' : '-';
    $world['write']   = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';

// adjust for SUID, SGID and sticky bit
    if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

    return $type .
           $owner['read'] . $owner['write'] . $owner['execute'] .
           $group['read'] . $group['write'] . $group['execute'] .
           $world['read'] . $world['write'] . $world['execute'];
  }

  function zen_remove($source) {
    global $messageStack, $zen_remove_error;

    if (isset($zen_remove_error)) $zen_remove_error = false;

    if (is_dir($source)) {
      $dir = dir($source);
      while ($file = $dir->read()) {
        if ( ($file != '.') && ($file != '..') ) {
          if (is_writeable($source . '/' . $file)) {
            zen_remove($source . '/' . $file);
          } else {
            $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source . '/' . $file), 'error');
            $zen_remove_error = true;
          }
        }
      }
      $dir->close();

      if (is_writeable($source)) {
        rmdir($source);
        zen_record_admin_activity('Removed directory from server: [' . $source . ']', 'notice');
      } else {
        $messageStack->add(sprintf(ERROR_DIRECTORY_NOT_REMOVEABLE, $source), 'error');
        $zen_remove_error = true;
      }
    } else {
      if (is_writeable($source)) {
        unlink($source);
        zen_record_admin_activity('Deleted file from server: [' . $source . ']', 'notice');
      } else {
        $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source), 'error');
        $zen_remove_error = true;
      }
    }
  }

/**
 * Output the tax percentage with optional padded decimals
 */
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


  function zen_get_tax_class_title($tax_class_id) {
    global $db;
    if ($tax_class_id == '0') {
      return TEXT_NONE;
    } else {
      $classes = $db->Execute("select tax_class_title
                               from " . TABLE_TAX_CLASS . "
                               where tax_class_id = '" . (int)$tax_class_id . "'");
      if ($classes->EOF) return '';
      return $classes->fields['tax_class_title'];
    }
  }

  function zen_supported_image_extension() {
    if (function_exists('imagetypes')) {
      if (imagetypes() & IMG_PNG) {
        return 'png';
      } elseif (imagetypes() & IMG_JPG) {
        return 'jpg';
      } elseif (imagetypes() & IMG_GIF) {
        return 'gif';
      }
    } elseif (function_exists('imagecreatefrompng') && function_exists('imagepng')) {
      return 'png';
    } elseif (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
      return 'jpg';
    } elseif (function_exists('imagecreatefromgif') && function_exists('imagegif')) {
      return 'gif';
    }

    return false;
  }

  function zen_round($value, $precision) {
    $value =  round($value *pow(10,$precision),0);
    $value = $value/pow(10,$precision);
    return $value;
  }

/**
 * Add tax to a products price
 */
  function zen_add_tax($price, $tax) {
    global $currencies;

    if (DISPLAY_PRICE_WITH_TAX_ADMIN == 'true') {
      return zen_round($price, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']) + zen_calculate_tax($price, $tax);
    } else {
      return zen_round($price, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
    }
  }

/**
 * Calculates Tax rounding the result
 */
  function zen_calculate_tax($price, $tax) {
    return $price * $tax / 100;
  }

/**
 * Returns the tax rate for a zone / class
 * TABLES: tax_rates, zones_to_geo_zones
 */
  function zen_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
    global $db;
    global $customer_zone_id, $customer_country_id;

    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (!$_SESSION['customer_id']) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } else {
        $country_id = $customer_country_id;
        $zone_id = $customer_zone_id;
      }
    }

    $tax = $db->Execute("select SUM(tax_rate) as tax_rate
                         from (" . TABLE_TAX_RATES . " tr
                         left join " . TABLE_ZONES_TO_GEO_ZONES . " za
                         ON tr.tax_zone_id = za.geo_zone_id
                         left join " . TABLE_GEO_ZONES . " tz ON tz.geo_zone_id = tr.tax_zone_id )
                         WHERE (za.zone_country_id IS NULL
                         OR za.zone_country_id = 0
                         OR za.zone_country_id = '" . (int)$country_id . "')
                         AND (za.zone_id IS NULL OR za.zone_id = 0
                         OR za.zone_id = '" . (int)$zone_id . "')
                         AND tr.tax_class_id = '" . (int)$class_id . "'
                         GROUP BY tr.tax_priority");

    if ($tax->RecordCount() > 0) {
      $tax_multiplier = 0;
      while (!$tax->EOF) {
        $tax_multiplier += $tax->fields['tax_rate'];
    $tax->MoveNext();
      }
      return $tax_multiplier;
    } else {
      return 0;
    }
  }

/**
 * Returns the tax rate for a tax class
 * TABLES: tax_rates
 */
  function zen_get_tax_rate_value($class_id) {
    return zen_get_tax_rate($class_id);
  }

  function zen_call_function($function, $parameter, $object = '') {
    if ($object == '') {
      return call_user_func($function, $parameter);
    } else {
      return call_user_func(array($object, $function), $parameter);
    }
  }

  function zen_get_zone_class_title($zone_class_id) {
    global $db;
    if ($zone_class_id == '0') {
      return TEXT_NONE;
    } else {
      $classes = $db->Execute("select geo_zone_name
                               from " . TABLE_GEO_ZONES . "
                               where geo_zone_id = '" . (int)$zone_class_id . "'");
      if ($classes->EOF) return '';
      return $classes->fields['geo_zone_name'];
    }
  }

////
  function zen_cfg_pull_down_zone_classes($zone_class_id, $key = '') {
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $zone_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $zone_class = $db->Execute("select geo_zone_id, geo_zone_name
                                from " . TABLE_GEO_ZONES . "
                                order by geo_zone_name");

    while (!$zone_class->EOF) {
      $zone_class_array[] = array('id' => $zone_class->fields['geo_zone_id'],
                                  'text' => $zone_class->fields['geo_zone_name']);
      $zone_class->MoveNext();
    }

    return zen_draw_pull_down_menu($name, $zone_class_array, $zone_class_id);
  }


////
  function zen_cfg_pull_down_order_statuses($order_status_id, $key = '') {
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
    $statuses = $db->Execute("select orders_status_id, orders_status_name
                              from " . TABLE_ORDERS_STATUS . "
                              where language_id = '" . (int)$_SESSION['languages_id'] . "'
                              order by orders_status_id");

    while (!$statuses->EOF) {
      $statuses_array[] = array('id' => $statuses->fields['orders_status_id'],
                                'text' => $statuses->fields['orders_status_name'] . ' [' . $statuses->fields['orders_status_id'] . ']');
      $statuses->MoveNext();
    }

    return zen_draw_pull_down_menu($name, $statuses_array, $order_status_id);
  }

  function zen_get_order_status_name($order_status_id, $language_id = '') {
    global $db;

    if ($order_status_id < 1) return TEXT_DEFAULT;

    if (!is_numeric($language_id)) $language_id = $_SESSION['languages_id'];

    $status = $db->Execute("select orders_status_name
                            from " . TABLE_ORDERS_STATUS . "
                            where orders_status_id = '" . (int)$order_status_id . "'
                            and language_id = '" . (int)$language_id . "'");
    if ($status->EOF) return 'ERROR: INVALID STATUS ID: ' . (int)$order_status_id;
    return $status->fields['orders_status_name'] . ' [' . (int)$order_status_id . ']';
  }

/**
 * Return a random value
 */
  function zen_rand($min = null, $max = null) {
    static $seeded;

    if (!$seeded) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

  /**
   * @deprecated - use nl2br() instead
   */
  function zen_convert_linefeeds($from, $to, $string) {
    return str_replace($from, $to, $string);
  }

  function zen_string_to_int($string) {
    return (int)$string;
  }

/**
 * Parse and secure the cPath parameter values
 */
  function zen_parse_category_path($cPath) {
// make sure the category IDs are integers
    $cPath_array = array_map('zen_string_to_int', explode('_', $cPath));

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($cPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($cPath_array[$i], $tmp_array)) {
        $tmp_array[] = $cPath_array[$i];
      }
    }

    return $tmp_array;
  }
////
/**
 * Create a Coupon Code. Returns blank if cannot generate a unique code using the passed criteria.
 * @param string $salt - this is an optional string to help seed the random code with greater entropy
 * @param int $length - this is the desired length of the generated code
 * @param string $prefix - include a prefix string if you want to force the generated code to start with a specific string
 * @return string (new coupon code) (will be blank if the function failed)
 */
  function create_coupon_code($salt="secret", $length=SECURITY_CODE_LENGTH, $prefix = '') {
    global $db;
    $length = (int)$length;
    static $max_db_length;
    if (!isset($max_db_length)) $max_db_length = zen_field_length(TABLE_COUPONS, 'coupon_code');  // schema is normally max 32 chars for this field
    if ($length > $max_db_length) $length = $max_db_length;
    if (strlen($prefix) > $max_db_length) return ''; // if prefix is already too long for the db, we can't generate a new code
    if (strlen($prefix) + (int)$length > $max_db_length) $length = $max_db_length - strlen($prefix);
    if ($length < 4) return ''; // if the recalculated length (esp in respect to prefixes) is less than 4 (for very basic entropy) then abort
    $ccid = md5(uniqid("",$salt));
    $ccid .= md5(uniqid("",$salt));
    $ccid .= md5(uniqid("",$salt));
    $ccid .= md5(uniqid("",$salt));
    srand((double)microtime()*1000000); // seed the random number generator
    $good_result = 0;
    while ($good_result == 0) {
      $random_start = @rand(0, (128-$length));
      $id1=substr($ccid, $random_start, $length);
      $query = $db->Execute("select coupon_code
                             from " . TABLE_COUPONS . "
                             where coupon_code = '" . $prefix . $id1 . "'");
      if ($query->RecordCount() < 1 ) $good_result = 1;
    }
    return ($good_result == 1) ? $prefix . $id1 : ''; // blank means couldn't generate a unique code (typically because the max length was encountered before being able to generate unique)
  }

/**
 * Update the Customers GV account
 */
  function zen_gv_account_update($customer_id, $gv_id) {
    global $db;
    $customer_gv = $db->Execute("select amount
                                 from " . TABLE_COUPON_GV_CUSTOMER . "
                                 where customer_id = '" . (int)$customer_id . "'");

    $coupon_gv = $db->Execute("select coupon_amount
                               from " . TABLE_COUPONS . "
                               where coupon_id = '" . (int)$gv_id . "'");

    if ($customer_gv->RecordCount() > 0) {
      $new_gv_amount = $customer_gv->fields['amount'] + $coupon_gv->fields['coupon_amount'];
      $gv_query = $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . "
                                set amount = '" . $new_gv_amount . "'
                                where customer_id = '" . (int)$customer_id . "'");
    } else {
      $db->Execute("insert into " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) values ('" . (int)$customer_id . "', '" . $coupon_gv->fields['coupon_amount'] . "')");
    }
  }

/**
 * Output a day/month/year dropdown selector
 */
  function zen_draw_date_selector($prefix, $date='') {
    $month_array = array();
    $month_array[1] =_JANUARY;
    $month_array[2] =_FEBRUARY;
    $month_array[3] =_MARCH;
    $month_array[4] =_APRIL;
    $month_array[5] =_MAY;
    $month_array[6] =_JUNE;
    $month_array[7] =_JULY;
    $month_array[8] =_AUGUST;
    $month_array[9] =_SEPTEMBER;
    $month_array[10] =_OCTOBER;
    $month_array[11] =_NOVEMBER;
    $month_array[12] =_DECEMBER;
    $usedate = getdate($date);
    $day = $usedate['mday'];
    $month = $usedate['mon'];
    $year = $usedate['year'];
    $date_selector = '<select name="'. $prefix .'_day">';
    for ($i=1;$i<32;$i++){
      $date_selector .= '<option value="' . $i . '"';
      if ($i==$day) $date_selector .= 'selected';
      $date_selector .= '>' . $i . '</option>';
    }
    $date_selector .= '</select>';
    $date_selector .= '<select name="'. $prefix .'_month">';
    for ($i=1;$i<13;$i++){
      $date_selector .= '<option value="' . $i . '"';
      if ($i==$month) $date_selector .= 'selected';
      $date_selector .= '>' . $month_array[$i] . '</option>';
    }
    $date_selector .= '</select>';
    $date_selector .= '<select name="'. $prefix .'_year">';
    for ($i = date('Y') - 5, $j = date('Y') + 11; $i < $j; $i++) {
      $date_selector .= '<option value="' . $i . '"';
      if ($i==$year) $date_selector .= 'selected';
      $date_selector .= '>' . $i . '</option>';
    }
    $date_selector .= '</select>';
    return $date_selector;
  }

/**
 * Validate Option Name and Option Type Match
 */
  function zen_validate_options_to_options_value($products_options_id, $products_options_values_id) {
    global $db;
    $check_options_to_values_query= $db->Execute("select products_options_id
                                                  from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                                  where products_options_id= '" . (int)$products_options_id . "'
                                                  and products_options_values_id='" . (int)$products_options_values_id .
                                                  "' limit 1");

    if ($check_options_to_values_query->RecordCount() != 1) {
      return false;
    } else {
      return true;
    }
  }

/**
 * look-up Attributues Options Name products_options_values_to_products_options
 */
  function zen_get_products_options_name_from_value($lookup) {
    global $db;

    if ($lookup==0) {
      return 'RESERVED FOR TEXT/FILES ONLY ATTRIBUTES';
    }

    $check_options_to_values = $db->Execute("select products_options_id
                    from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                    where products_options_values_id='" . (int)$lookup . "'");
    if ($check_options_to_values->EOF) return '';

    $check_options = $db->Execute("select products_options_name
                      from " . TABLE_PRODUCTS_OPTIONS . "
                      where products_options_id='" . (int)$check_options_to_values->fields['products_options_id']
                      . "' and language_id='" . (int)$_SESSION['languages_id'] . "'");
    if ($check_options->EOF) return '';
    return $check_options->fields['products_options_name'];
  }


/**
 * lookup attributes model
 */
  function zen_get_products_model($products_id) {
    global $db;
    $check = $db->Execute("select products_model
                    from " . TABLE_PRODUCTS . "
                    where products_id='" . (int)$products_id . "'");
    if ($check->EOF) return '';
    return $check->fields['products_model'];
  }

/**
 * Check if product has attributes
 */
  function zen_has_product_attributes($products_id, $not_readonly = 'true') {
    global $db;

    if (PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' and $not_readonly == 'true') {
      // don't include READONLY attributes to determine if attributes must be selected to add to cart
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

    return !($attributes->EOF);
  }

/**
 * Check if product_id is valid
 */
  function zen_products_id_valid($products_id) {
    global $db;
    $products_valid_query = "select count(*) as count
                         from " . TABLE_PRODUCTS . "
                         where products_id = '" . (int)$products_id . "'";

    $products_valid = $db->Execute($products_valid_query);

    if ($products_valid->fields['count'] > 0) {
      return true;
    } else {
      return false;
    }
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


function zen_copy_products_attributes($products_id_from, $products_id_to) {
  global $db;
  global $messageStack;
  global $copy_attributes_delete_first, $copy_attributes_duplicates_skipped, $copy_attributes_duplicates_overwrite, $copy_attributes_include_downloads, $copy_attributes_include_filename;

// Check for errors in copy request
  if ( (!zen_has_product_attributes($products_id_from, 'false') or !zen_products_id_valid($products_id_to)) or $products_id_to == $products_id_from ) {
    if ($products_id_to == $products_id_from) {
      // same products_id
      $messageStack->add_session('<b>WARNING: Cannot copy from Product ID #' . $products_id_from . ' to Product ID # ' . $products_id_to . ' ... No copy was made' . '</b>', 'caution');
    } else {
      if (!zen_has_product_attributes($products_id_from, 'false')) {
        // no attributes found to copy
        $messageStack->add_session('<b>WARNING: No Attributes to copy from Product ID #' . $products_id_from . ' for: ' . zen_get_products_name($products_id_from) . ' ... No copy was made' . '</b>', 'caution');
      } else {
        // invalid products_id
        $messageStack->add_session('<b>WARNING: There is no Product ID #' . $products_id_to . ' ... No copy was made' . '</b>', 'caution');
      }
    }
  } else {
// FIX HERE - remove once working

// check if product already has attributes
    $check_attributes = zen_has_product_attributes($products_id_to, 'false');

    if ($copy_attributes_delete_first=='1' and $check_attributes == true) {
// die('DELETE FIRST - Copying from ' . $products_id_from . ' to ' . $products_id_to . ' Do I delete first? ' . $copy_attributes_delete_first);
      // delete all attributes first from products_id_to
      zen_products_attributes_download_delete($products_id_to);
      $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$products_id_to . "'");
    }

// get attributes to copy from
    $products_copy_from= $db->Execute("select * from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . (int)$products_id_from . "'" . " order by products_attributes_id");

    while ( !$products_copy_from->EOF ) {
// This must match the structure of your products_attributes table

      $update_attribute = false;
      $add_attribute = true;
      $check_duplicate = $db->Execute("select * from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . (int)$products_id_to . "'" . " and options_id= '" . (int)$products_copy_from->fields['options_id'] . "' and options_values_id='" . (int)$products_copy_from->fields['options_values_id'] .  "'");
      if ($check_attributes == true) {
        if ($check_duplicate->RecordCount() == 0) {
          $update_attribute = false;
          $add_attribute = true;
        } else {
          if ($check_duplicate->RecordCount() == 0) {
            $update_attribute = false;
            $add_attribute = true;
          } else {
            $update_attribute = true;
            $add_attribute = false;
          }
        }
      } else {
        $update_attribute = false;
        $add_attribute = true;
      }

// die('UPDATE/IGNORE - Checking Copying from ' . $products_id_from . ' to ' . $products_id_to . ' Do I delete first? ' . ($copy_attributes_delete_first == '1' ? TEXT_YES : TEXT_NO) . ' Do I add? ' . ($add_attribute == true ? TEXT_YES : TEXT_NO) . ' Do I Update? ' . ($update_attribute == true ? TEXT_YES : TEXT_NO) . ' Do I skip it? ' . ($copy_attributes_duplicates_skipped=='1' ? TEXT_YES : TEXT_NO) . ' Found attributes in From: ' . $check_duplicate->RecordCount());

      if ($copy_attributes_duplicates_skipped == '1' and $check_duplicate->RecordCount() != 0) {
        // skip it
          $messageStack->add_session(TEXT_ATTRIBUTE_COPY_SKIPPING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
      } else {
        if ($add_attribute == true) {
          // New attribute - insert it
          $db->Execute("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_attributes_id, products_id, options_id, options_values_id, options_values_price, price_prefix, products_options_sort_order, product_attribute_is_free, products_attributes_weight, products_attributes_weight_prefix, attributes_display_only, attributes_default, attributes_discounted, attributes_image, attributes_price_base_included, attributes_price_onetime, attributes_price_factor, attributes_price_factor_offset, attributes_price_factor_onetime, attributes_price_factor_onetime_offset, attributes_qty_prices, attributes_qty_prices_onetime, attributes_price_words, attributes_price_words_free, attributes_price_letters, attributes_price_letters_free, attributes_required) values (0, '" . (int)$products_id_to . "',
          '" . $products_copy_from->fields['options_id'] . "',
          '" . $products_copy_from->fields['options_values_id'] . "',
          '" . $products_copy_from->fields['options_values_price'] . "',
          '" . $products_copy_from->fields['price_prefix'] . "',
          '" . $products_copy_from->fields['products_options_sort_order'] . "',
          '" . $products_copy_from->fields['product_attribute_is_free'] . "',
          '" . $products_copy_from->fields['products_attributes_weight'] . "',
          '" . $products_copy_from->fields['products_attributes_weight_prefix'] . "',
          '" . $products_copy_from->fields['attributes_display_only'] . "',
          '" . $products_copy_from->fields['attributes_default'] . "',
          '" . $products_copy_from->fields['attributes_discounted'] . "',
          '" . $products_copy_from->fields['attributes_image'] . "',
          '" . $products_copy_from->fields['attributes_price_base_included'] . "',
          '" . $products_copy_from->fields['attributes_price_onetime'] . "',
          '" . $products_copy_from->fields['attributes_price_factor'] . "',
          '" . $products_copy_from->fields['attributes_price_factor_offset'] . "',
          '" . $products_copy_from->fields['attributes_price_factor_onetime'] . "',
          '" . $products_copy_from->fields['attributes_price_factor_onetime_offset'] . "',
          '" . $products_copy_from->fields['attributes_qty_prices'] . "',
          '" . $products_copy_from->fields['attributes_qty_prices_onetime'] . "',
          '" . $products_copy_from->fields['attributes_price_words'] . "',
          '" . $products_copy_from->fields['attributes_price_words_free'] . "',
          '" . $products_copy_from->fields['attributes_price_letters'] . "',
          '" . $products_copy_from->fields['attributes_price_letters_free'] . "',
          '" . $products_copy_from->fields['attributes_required'] . "')");
          $messageStack->add_session(TEXT_ATTRIBUTE_COPY_INSERTING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
        }
        if ($update_attribute == true) {
          // Update attribute - Just attribute settings not ids
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set
          options_values_price='" . $products_copy_from->fields['options_values_price'] . "',
          price_prefix='" . $products_copy_from->fields['price_prefix'] . "',
          products_options_sort_order='" . $products_copy_from->fields['products_options_sort_order'] . "',
          product_attribute_is_free='" . $products_copy_from->fields['product_attribute_is_free'] . "',
          products_attributes_weight='" . $products_copy_from->fields['products_attributes_weight'] . "',
          products_attributes_weight_prefix='" . $products_copy_from->fields['products_attributes_weight_prefix'] . "',
          attributes_display_only='" . $products_copy_from->fields['attributes_display_only'] . "',
          attributes_default='" . $products_copy_from->fields['attributes_default'] . "',
          attributes_discounted='" . $products_copy_from->fields['attributes_discounted'] . "',
          attributes_image='" . $products_copy_from->fields['attributes_image'] . "',
          attributes_price_base_included='" . $products_copy_from->fields['attributes_price_base_included'] . "',
          attributes_price_onetime='" . $products_copy_from->fields['attributes_price_onetime'] . "',
          attributes_price_factor='" . $products_copy_from->fields['attributes_price_factor'] . "',
          attributes_price_factor_offset='" . $products_copy_from->fields['attributes_price_factor_offset'] . "',
          attributes_price_factor_onetime='" . $products_copy_from->fields['attributes_price_factor_onetime'] . "',
          attributes_price_factor_onetime_offset='" . $products_copy_from->fields['attributes_price_factor_onetime_offset'] . "',
          attributes_qty_prices='" . $products_copy_from->fields['attributes_qty_prices'] . "',
          attributes_qty_prices_onetime='" . $products_copy_from->fields['attributes_qty_prices_onetime'] . "',
          attributes_price_words='" . $products_copy_from->fields['attributes_price_words'] . "',
          attributes_price_words_free='" . $products_copy_from->fields['attributes_price_words_free'] . "',
          attributes_price_letters='" . $products_copy_from->fields['attributes_price_letters'] . "',
          attributes_price_letters_free='" . $products_copy_from->fields['attributes_price_letters_free'] . "',
          attributes_required='" . $products_copy_from->fields['attributes_required'] . "'"
           . " where products_id='" . (int)$products_id_to . "'" . " and options_id= '" . $products_copy_from->fields['options_id'] . "' and options_values_id='" . $products_copy_from->fields['options_values_id'] . "'");
//           . " where products_id='" . $products_id_to . "'" . " and options_id= '" . $products_copy_from->fields['options_id'] . "' and options_values_id='" . $products_copy_from->fields['options_values_id'] . "' and attributes_image='" . $products_copy_from->fields['attributes_image'] . "' and attributes_price_base_included='" . $products_copy_from->fields['attributes_price_base_included'] .  "'");
          $messageStack->add_session(TEXT_ATTRIBUTE_COPY_UPDATING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
        }
      }

      $products_copy_from->MoveNext();
    } // end of products attributes while loop

     // reset products_price_sorter for searches etc.
     zen_update_products_price_sorter($products_id_to);
  } // end of no attributes or other errors
} // eof: zen_copy_products_attributes


/**
 * function to return field type
 * uses $tbl = table name, $fld = field name
 */
  function zen_field_type($tbl, $fld) {
    global $db;
    $rs = $db->MetaColumns($tbl);
    $type = $rs[strtoupper($fld)]->type;
    return $type;
  }

/**
 * function to return field length
 * uses $tbl = table name, $fld = field name
 */
  function zen_field_length($tbl, $fld) {
    global $db;
    $rs = $db->MetaColumns($tbl);
    $length = $rs[strtoupper($fld)]->max_length;
    return $length;
  }

/**
 * return the size and maxlength settings in the form size="blah" maxlength="blah" based on maximum size being 50
 * uses $tbl = table name, $fld = field name
 * example: zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name')
 */
  function zen_set_field_length($tbl, $fld, $max=50, $override=false) {
    $field_length= zen_field_length($tbl, $fld);
    switch (true) {
      case (($override == false and $field_length > $max)):
        $length= 'size = "' . ($max+1) . '" maxlength= "' . $field_length . '"';
        break;
      default:
        $length= 'size = "' . ($field_length+1) . '" maxlength = "' . $field_length . '"';
        break;
    }
    return $length;
  }


/**
 * Get the Option Name for a particular language
 */
  function zen_get_option_name_language($option, $language) {
    global $db;
    $lookup = $db->Execute("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id= '" . (int)$option . "' and language_id = '" . (int)$language . "'");
    if ($lookup->EOF) return '';
    return $lookup->fields['products_options_name'];
  }

/**
 * Get the Option Name for a particular language
 */
  function zen_get_option_name_language_sort_order($option, $language) {
    global $db;
    $lookup = $db->Execute("select products_options_id, products_options_name, products_options_sort_order from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id= '" . (int)$option . "' and language_id = '" . (int)$language . "'");
    if ($lookup->EOF) return '';
    return $lookup->fields['products_options_sort_order'];
  }

/**
 * Delete all product attributes
 */
  function zen_delete_products_attributes($delete_product_id) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_DELETE_PRODUCTS_ATTRIBUTES', array(), $delete_product_id);

    // first delete associated downloads
    $products_delete_from = $db->Execute("select pa.products_id, pad.products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad  where pa.products_id='" . (int)$delete_product_id . "' and pad.products_attributes_id= pa.products_attributes_id");
    while (!$products_delete_from->EOF) {
      $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " where products_attributes_id = '" . $products_delete_from->fields['products_attributes_id'] . "'");
      $products_delete_from->MoveNext();
    }

    $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$delete_product_id . "'");
}

/**
 * Set Product Attributes Sort Order to Products Option Value Sort Order
 */
  function zen_update_attributes_products_option_values_sort_order($products_id) {
    global $db;
    $attributes_sort_order = $db->Execute("select distinct pa.products_attributes_id, pa.options_id, pa.options_values_id, pa.products_options_sort_order, pov.products_options_values_sort_order from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$products_id . "' and pa.options_values_id = pov.products_options_values_id");
    while (!$attributes_sort_order->EOF) {
      $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set products_options_sort_order = '" . $attributes_sort_order->fields['products_options_values_sort_order'] . "' where products_id = '" . (int)$products_id . "' and products_attributes_id = '" . $attributes_sort_order->fields['products_attributes_id'] . "'");
      $attributes_sort_order->MoveNext();
    }
  }

/**
 * product pulldown with attributes
 */
  function zen_draw_products_pull_down_attributes($name, $parameters = '', $exclude = '') {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $new_fields=', p.products_model';

    $products = $db->Execute("select distinct p.products_id, pd.products_name, p.products_price" . $new_fields .
        " from " . TABLE_PRODUCTS . " p, " .
        TABLE_PRODUCTS_DESCRIPTION . " pd, " .
        TABLE_PRODUCTS_ATTRIBUTES . " pa " .
        " where p.products_id= pa.products_id and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by products_name");

    while (!$products->EOF) {
      if (!in_array($products->fields['products_id'], $exclude)) {
        $display_price = zen_get_products_base_price($products->fields['products_id']);
        $select_string .= '<option value="' . $products->fields['products_id'] . '">' . $products->fields['products_name'] . ' (' . TEXT_MODEL . ' ' . $products->fields['products_model'] . ') (' . $currencies->format($display_price) . ')</option>';
      }
      $products->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }


/**
 * categories pulldown with products
 */
  function zen_draw_products_pull_down_categories($name, $parameters = '', $exclude = '', $show_id = false, $show_parent = false) {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $categories = $db->Execute("select distinct c.categories_id, cd.categories_name " .
        " from " . TABLE_CATEGORIES . " c, " .
        TABLE_CATEGORIES_DESCRIPTION . " cd, " .
        TABLE_PRODUCTS_TO_CATEGORIES . " ptoc " .
        " where ptoc.categories_id = c.categories_id and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by categories_name");

    while (!$categories->EOF) {
      if (!in_array($categories->fields['categories_id'], $exclude)) {
        if ($show_parent == true) {
          $parent = zen_get_products_master_categories_name($categories->fields['categories_id']);
          if ($parent != '') {
            $parent = ' : in ' . $parent;
          }
        } else {
          $parent = '';
        }
        $select_string .= '<option value="' . $categories->fields['categories_id'] . '">' . $categories->fields['categories_name'] . $parent . ($show_id ? ' - ID#' . $categories->fields['categories_id'] : '') . '</option>';
      }
      $categories->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }

/**
 * categories pulldown with products with attributes
 */
  function zen_draw_products_pull_down_categories_attributes($name, $parameters = '', $exclude = '') {
    global $db, $currencies;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $categories = $db->Execute("select distinct c.categories_id, cd.categories_name " .
        " from " . TABLE_CATEGORIES . " c, " .
        TABLE_CATEGORIES_DESCRIPTION . " cd, " .
        TABLE_PRODUCTS_TO_CATEGORIES . " ptoc, " .
        TABLE_PRODUCTS_ATTRIBUTES . " pa " .
        " where pa.products_id= ptoc.products_id and ptoc.categories_id= c.categories_id and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by categories_name");
    while (!$categories->EOF) {
      if (!in_array($categories->fields['categories_id'], $exclude)) {
        $select_string .= '<option value="' . $categories->fields['categories_id'] . '">' . $categories->fields['categories_name'] . '</option>';
      }
      $categories->MoveNext();
    }

    $select_string .= '</select>';

    return $select_string;
  }

  function zen_get_top_level_domain($url) {
    if (strpos($url, '://')) {
      $url = parse_url($url);
      $url = $url['host'];
    }
    $domain_array = explode('.', $url);
    $domain_size = sizeof($domain_array);
    if ($domain_size > 1) {
      if (SESSION_USE_FQDN == 'True') return $url;
      if (is_numeric($domain_array[$domain_size-2]) && is_numeric($domain_array[$domain_size-1])) {
        return false;
      } else {
        $tld = "";
        foreach ($domain_array as $dPart)
        {
          if ($dPart != "www") $tld = $tld . "." . $dPart;
        }
        return substr($tld, 1);
      }
    } else {
      return false;
    }
  }

/**
 * Check if restricted-use demo mode is active
 */
  function zen_admin_demo() {
    return (ADMIN_DEMO == '1') ? TRUE : FALSE;
  }


  function zen_has_product_attributes_downloads($products_id, $check_valid=false) {
    global $db;
    if (DOWNLOAD_ENABLED == 'true') {
      $download_display_query_raw ="select pa.products_attributes_id, pad.products_attributes_filename
                                    from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                    where pa.products_id='" . (int)$products_id . "'
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
 * Construct a category path to the product
 * TABLES: products_to_categories
 */
  function zen_get_product_path($products_id, $status_override = '1') {
    global $db;
    $cPath = '';

/*
    $category_query = "select p2c.categories_id
                       from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                       where p.products_id = '" . (int)$products_id . "' " .
                       ($status_override == 1 ? " and p.products_status = 1 " : '') . "
                       and p.products_id = p2c.products_id limit 1";
*/

    $category_query = "select p.products_id, p.master_categories_id
                       from " . TABLE_PRODUCTS . " p
                       where p.products_id = '" . (int)$products_id . "' limit 1";


    $category = $db->Execute($category_query);

    if ($category->RecordCount() > 0) {

      $categories = array();
      zen_get_parent_categories($categories, $category->fields['master_categories_id']);

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);

      if (zen_not_null($cPath)) $cPath .= '_';
      $cPath .= $category->fields['master_categories_id'];
    }

    return $cPath;
  }

/**
 * Recursively go through the categories and retreive all parent categories IDs
 * TABLES: categories
 */
  function zen_get_parent_categories(&$categories, $categories_id) {
    global $db;
    $parent_categories_query = "select parent_id
                                from " . TABLE_CATEGORIES . "
                                where categories_id = '" . (int)$categories_id . "'";

    $parent_categories = $db->Execute($parent_categories_query);

    while (!$parent_categories->EOF) {
      if ($parent_categories->fields['parent_id'] == 0) return true;
      $categories[sizeof($categories)] = $parent_categories->fields['parent_id'];
      if ($parent_categories->fields['parent_id'] != $categories_id) {
        zen_get_parent_categories($categories, $parent_categories->fields['parent_id']);
      }
      $parent_categories->MoveNext();
    }
  }

/**
 * Return a product's category
 * TABLES: products_to_categories
 */
  function zen_get_products_category_id($products_id) {
    global $db;

    $the_products_category_query = "select products_id, master_categories_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'";
    $the_products_category = $db->Execute($the_products_category_query);
    if ($the_products_category->EOF) return '';
    return $the_products_category->fields['master_categories_id'];
  }


/**
 * Count how many subcategories exist in a category
 * TABLES: categories
 * old v1.2 name zen_get_parent_category_name
 */
  function zen_get_products_master_categories_name($categories_id) {
    global $db;

    $categories_lookup = $db->Execute("select parent_id
                                from " . TABLE_CATEGORIES . "
                                where categories_id = '" . (int)$categories_id . "'");

    $parent_name = zen_get_category_name($categories_lookup->fields['parent_id'], (int)$_SESSION['languages_id']);

    return $parent_name;
  }


/**
 * configuration key value lookup
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

  function zen_get_configuration_group_value($lookup) {
    global $db;
    $configuration_query= $db->Execute("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id ='" . (int)$lookup . "'");
    if ( $configuration_query->RecordCount() == 0 ) {
      return (int)$lookup;
    }
    return $configuration_query->fields['configuration_group_title'];
  }


/**
 * check to see if free shipping rules allow the specified shipping module to be enabled or to disable it in lieu of being free
 */
  function zen_get_shipping_enabled($shipping_module) {
    global $zcRequest;

    // for admin always true if installed
    if (IS_ADMIN_FLAG === true && $zcRequest->readGet('cmd') == FILENAME_MODULES) {
      return true;
    }

    $check_cart_free = $_SESSION['cart']->in_cart_check('product_is_always_free_shipping','1');
    $check_cart_cnt = $_SESSION['cart']->count_contents();
    $check_cart_weight = $_SESSION['cart']->show_weight();

    switch(true) {
      // for admin always true if installed
      // left for future expansion
      case (IS_ADMIN_FLAG === true && $zcRequest->readGet('cmd') == FILENAME_MODULES):
        return true;
        break;
      // Free Shipping when 0 weight - enable freeshipper - ORDER_WEIGHT_ZERO_STATUS must be on
      case (ORDER_WEIGHT_ZERO_STATUS == '1' and ($check_cart_weight == 0 and $shipping_module == 'freeshipper')):
        return true;
        break;
      // Free Shipping when 0 weight - disable everyone - ORDER_WEIGHT_ZERO_STATUS must be on
      case (ORDER_WEIGHT_ZERO_STATUS == '1' and ($check_cart_weight == 0 and $shipping_module != 'freeshipper')):
        return false;
        break;
      case (($_SESSION['cart']->free_shipping_items() == $check_cart_cnt) and $shipping_module == 'freeshipper'):
        return true;
        break;
      case (($_SESSION['cart']->free_shipping_items() == $check_cart_cnt) and $shipping_module != 'freeshipper'):
        return false;
        break;
      // Always free shipping only true - enable freeshipper
      case (($check_cart_free == $check_cart_cnt) and $shipping_module == 'freeshipper'):
        return true;
        break;
      // Always free shipping only true - disable everyone
      case (($check_cart_free == $check_cart_cnt) and $shipping_module != 'freeshipper'):
        return false;
        break;
      // Always free shipping only is false - disable freeshipper
      case (($check_cart_free != $check_cart_cnt) and $shipping_module == 'freeshipper'):
        return false;
        break;
      default:
        return true;
        break;
    }
  }

/**
 * get the type_handler value for the specified product_type
 * @param int $product_type
 */
  function zen_get_handler_from_type($product_type) {
    global $db;
    global $messageStack;
    
    $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = '" . (int)$product_type . "'";
    $handler = $db->Execute($sql);
    if ($handler->EOF) { 
          $messageStack->add('ERROR: Invalid product_type specified.', 'error');
          return -1;
    }
    return $handler->fields['type_handler'];
  }

/*
////
// Sets the status of a featured product
  function zen_set_featured_status($featured_id, $status) {
    global $db;
    if ($status == '1') {
      return $db->Execute("update " . TABLE_FEATURED . "
                           set status = '1', expires_date = NULL, date_status_change = NULL
                           where featured_id = '" . (int)$featured_id . "'");

    } elseif ($status == '0') {
      return $db->Execute("update " . TABLE_FEATURED . "
                           set status = '0', date_status_change = now()
                           where featured_id = '" . (int)$featured_id . "'");

    } else {
      return -1;
    }
  }
*/

/**
 * Sets the status of a product review
 */
  function zen_set_reviews_status($review_id, $status) {
    global $db;
    if ($status == '1') {
      return $db->Execute("update " . TABLE_REVIEWS . "
                           set status = 1
                           where reviews_id = '" . (int)$review_id . "'");

    } elseif ($status == '0') {
      return $db->Execute("update " . TABLE_REVIEWS . "
                           set status = 0
                           where reviews_id = '" . (int)$review_id . "'");

    } else {
      return -1;
    }
  }

/**
 * recalculate and set the products_price_sorter field for the specified $product_id
 */
  function zen_update_products_price_sorter($product_id) {
    global $db;

    $products_price_sorter = zen_get_products_actual_price($product_id);
    $db->Execute("update " . TABLE_PRODUCTS . " set
         products_price_sorter='" . zen_db_prepare_input($products_price_sorter) . "'
         where products_id='" . (int)$product_id . "'");
  }

/**
 * Product Types -- configuration key value lookup in TABLE_PRODUCT_TYPE_LAYOUT
 */
  function zen_get_configuration_key_value_layout($lookup, $type=1) {
    global $db;
    $configuration_query= $db->Execute("select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . zen_db_input($lookup) . "' and product_type_id='". (int)$type . "'");
    if ($configuration_query->EOF) return '';
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( !($lookup_value) ) {
      $lookup_value='<span class="lookupAttention">' . $lookup . '</span>';
    }
    return $lookup_value;
  }

/**
 * Returns true if the category has subcategories
 * TABLES: categories
 */
  function zen_has_category_subcategories($category_id) {
    global $db;
    $child_category_query = "select count(*) as count
                             from " . TABLE_CATEGORIES . "
                             where parent_id = '" . (int)$category_id . "'";

    $child_category = $db->Execute($child_category_query);

    if ($child_category->fields['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }

////
  function zen_get_categories($categories_array = '', $parent_id = '0', $indent = '') {
    global $db;

    if (!is_array($categories_array)) $categories_array = array();

    $categories_query = "select c.categories_id, cd.categories_name
                         from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                         where parent_id = '" . (int)$parent_id . "'
                         and c.categories_id = cd.categories_id
                         and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                         order by sort_order, cd.categories_name";

    $categories = $db->Execute($categories_query);

    while (!$categories->EOF) {
      $categories_array[] = array('id' => $categories->fields['categories_id'],
                                  'text' => $indent . $categories->fields['categories_name']);

      if ($categories->fields['categories_id'] != $parent_id) {
        $categories_array = zen_get_categories($categories_array, $categories->fields['categories_id'], $indent . '&nbsp;&nbsp;');
      }
      $categories->MoveNext();
    }

    return $categories_array;
  }


/**
 * Get the status of a category
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
 */
  function zen_get_products_status($product_id) {
    global $db;
    $sql = "select products_status from " . TABLE_PRODUCTS . (zen_not_null($product_id) ? " where products_id=" . (int)$product_id : "");
    $check_status = $db->Execute($sql);
    if ($check_status->EOF) return '';
    return $check_status->fields['products_status'];
  }

/**
 * check if linked
 */
  function zen_get_product_is_linked($product_id, $show_count = 'false') {
    global $db;

    $sql = "select * from " . TABLE_PRODUCTS_TO_CATEGORIES . (zen_not_null($product_id) ? " where products_id=" . (int)$product_id : "");
    $check_linked = $db->Execute($sql);
    if ($check_linked->RecordCount() > 1) {
      if ($show_count == 'true') {
        return $check_linked->RecordCount();
      } else {
        return 'true';
      }
    } else {
      return 'false';
    }
  }


/**
 * TABLES: categories_name from products_id
 */
  function zen_get_categories_name_from_product($product_id) {
    global $db;

//    $check_products_category= $db->Execute("select products_id, categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $product_id . "' limit 1");
    $check_products_category = $db->Execute("select products_id, master_categories_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    $the_categories_name= $db->Execute("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id= '" . $check_products_category->fields['master_categories_id'] . "' and language_id= '" . (int)$_SESSION['languages_id'] . "'");
    if ($the_categories_name->EOF) return '';
    return $the_categories_name->fields['categories_name'];
  }

  function zen_count_products_in_cats($category_id) {
    global $db;
    $cat_products_query = "select count(if (p.products_status=1,1,NULL)) as pr_on, count(*) as total
                           from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = '" . (int)$category_id . "'";

    $pr_count = $db->Execute($cat_products_query);
//    echo $pr_count->RecordCount();
    $c_array['this_count'] += $pr_count->fields['total'];
    $c_array['this_count_on'] += $pr_count->fields['pr_on'];

    $cat_child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = '" . (int)$category_id . "'";

    $cat_child_categories = $db->Execute($cat_child_categories_query);

    if ($cat_child_categories->RecordCount() > 0) {
      while (!$cat_child_categories->EOF) {
          $m_array = zen_count_products_in_cats($cat_child_categories->fields['categories_id']);
          $c_array['this_count'] += $m_array['this_count'];
          $c_array['this_count_on'] += $m_array['this_count_on'];

//          $this_count_on += $pr_count->fields['pr_on'];
        $cat_child_categories->MoveNext();
      }
    }
    return $c_array;
 }

/**
 * Return the number of products in a category
 * TABLES: products, products_to_categories, categories
 * syntax for count: zen_get_products_to_categories($categories->fields['categories_id'], true)
 * syntax for linked products: zen_get_products_to_categories($categories->fields['categories_id'], true, 'products_active')
 */
  function zen_get_products_to_categories($category_id, $include_inactive = false, $counts_what = 'products') {
    global $db;

    $products_count = $cat_products_count = 0;
    $products_linked = '';
    if ($include_inactive == true) {
      switch ($counts_what) {
        case ('products'):
        $cat_products_query = "select count(*) as total
                           from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = '" . (int)$category_id . "'";
        break;
        case ('products_active'):
        $cat_products_query = "select p.products_id
                           from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = '" . (int)$category_id . "'";
        break;
      }

    } else {
      switch ($counts_what) {
        case ('products'):
          $cat_products_query = "select count(*) as total
                             from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                             where p.products_id = p2c.products_id
                             and p.products_status = 1
                             and p2c.categories_id = '" . (int)$category_id . "'";
        break;
        case ('products_active'):
          $cat_products_query = "select p.products_id
                             from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                             where p.products_id = p2c.products_id
                             and p.products_status = 1
                             and p2c.categories_id = '" . (int)$category_id . "'";
        break;
      }
    }
    $cat_products = $db->Execute($cat_products_query);
    switch ($counts_what) {
      case ('products'):
        if (!$cat_products->EOF) $cat_products_count += $cat_products->fields['total'];
        break;
      case ('products_active'):
        while (!$cat_products->EOF) {
          if (zen_get_product_is_linked($cat_products->fields['products_id']) == 'true') {
            return $products_linked = 'true';
          }
          $cat_products->MoveNext();
        }
        break;
    }

    $cat_child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = '" . (int)$category_id . "'";

    $cat_child_categories = $db->Execute($cat_child_categories_query);

    if ($cat_child_categories->RecordCount() > 0) {
      while (!$cat_child_categories->EOF) {
      switch ($counts_what) {
        case ('products'):
          $cat_products_count += zen_get_products_to_categories($cat_child_categories->fields['categories_id'], $include_inactive);
          break;
        case ('products_active'):
          if (zen_get_products_to_categories($cat_child_categories->fields['categories_id'], true, 'products_active') == 'true') {
            return $products_linked = 'true';
          }
          break;
        }
        $cat_child_categories->MoveNext();
      }
    }

    switch ($counts_what) {
      case ('products'):
        return $cat_products_count;
        break;
      case ('products_active'):
        return $products_linked;
        break;
    }
  }

/**
 * master category selection
 */
  function zen_get_master_categories_pulldown($product_id) {
    global $db;

    $master_category_array = array();

    $master_categories_query = $db->Execute("select ptc.products_id, cd.categories_name, cd.categories_id
                                    from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                    left join " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                    on cd.categories_id = ptc.categories_id
                                    where ptc.products_id='" . (int)$product_id . "'
                                    and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                    ");

    $master_category_array[] = array('id' => '0', 'text' => TEXT_INFO_SET_MASTER_CATEGORIES_ID);
    while (!$master_categories_query->EOF) {
      $master_category_array[] = array('id' => $master_categories_query->fields['categories_id'], 'text' => $master_categories_query->fields['categories_name'] . TEXT_INFO_ID . $master_categories_query->fields['categories_id']);
      $master_categories_query->MoveNext();
    }

    return $master_category_array;
  }

/**
 * get products_type for specified $product_id
 */
  function zen_get_products_type($product_id) {
    global $db;

    $check_products_type = $db->Execute("select products_type from " . TABLE_PRODUCTS . " where products_id='" . (int)$product_id . "'");
    if ($check_products_type->EOF) return '';
    return $check_products_type->fields['products_type'];
  }

/**
 * Alias function for Store configuration values in the Administration Tool
 * adapted from USPS-related contributions by Brad Waite and Fritz Clapp
 */
  function zen_cfg_select_multioption($select_array, $key_value, $key = '') {
    for ($i=0; $i<sizeof($select_array); $i++) {
      $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
      $string .= '<br><input type="checkbox" name="' . $name . '" value="' . $select_array[$i] . '"';
      $key_values = explode( ", ", $key_value);
      if ( in_array($select_array[$i], $key_values) ) $string .= ' CHECKED';
      $string .= ' id="' . strtolower($select_array[$i] . '-' . $name) . '"> ' . '<label for="' . strtolower($select_array[$i] . '-' . $name) . '" class="inputSelect">' . $select_array[$i] . '</label>' . "\n";
    }
    $string .= '<input type="hidden" name="' . $name . '" value="--none--">';
    return $string;
  }

/**
 * get products image
 */
  function zen_get_products_image($product_id) {
    global $db;
    $product_image = $db->Execute("select products_image
                                   from " . TABLE_PRODUCTS . "
                                   where products_id = '" . (int)$product_id . "'");
    if ($product_image->EOF) return '';
    return $product_image->fields['products_image'];
  }


/**
 * remove common HTML from text for display as paragraph
 */
  function zen_clean_html($clean_it) {

    // remove any embedded javascript
    $clean_it = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $clean_it);

    $clean_it = preg_replace('/\r/', ' ', $clean_it);
    $clean_it = preg_replace('/\t/', ' ', $clean_it);
    $clean_it = preg_replace('/\n/', ' ', $clean_it);

    $clean_it= nl2br($clean_it);

// update breaks with a space for text displays in all listings with descriptions
    $clean_it = preg_replace('~(<br ?/?>|</?p>)~', ' ', $clean_it);
    $clean_it = preg_replace('/[ ]+/', ' ', $clean_it);

// remove other html code to prevent problems on display of text
    $clean_it = strip_tags($clean_it);
    return $clean_it;
  }


/**
 * find template or default file
 */
  function zen_get_file_directory($check_directory, $check_file, $dir_only = 'false') {
    global $template_dir;

    $zv_filename = $check_file;
    if (!strstr($zv_filename, '.php')) $zv_filename .= '.php';

    if (file_exists($check_directory . $template_dir . '/' . $zv_filename)) {
      $zv_directory = $check_directory . $template_dir . '/';
    } else if (file_exists($check_directory . 'shared' . '/' . $zv_filename)) {
      $zv_directory = $check_directory . 'shared' . '/';
    } else {
      $zv_directory = $check_directory;
    }

    if ($dir_only == 'true') {
      return $zv_directory;
    } else {
      return $zv_directory . $zv_filename;
    }
  }

  /**
   * build a list of directories in a specified parent folder
   * (formatted in id/text pairs for SELECT boxes)
   *
   * @todo convert to a directory-iterator instead
   * @todo - this will be deprecated after converting remaining admin pages to LEAD format
   *
   * @return array (id/text pairs)
   */
  function zen_build_subdirectories_array($parent_folder = '', $default_text = 'Main Directory') {
    if ($parent_folder == '') $parent_folder = DIR_FS_CATALOG_IMAGES;
    $dir_info[] = array('id' => '', 'text' => $default_text);

    $dir = @dir($parent_folder);
    while ($file = $dir->read()) {
      if (is_dir($parent_folder . $file) && $file != "." && $file != "..") {
        $dir_info[] = array('id' => $file . '/', 'text' => $file);
      }
    }
    $dir->close();
    sort($dir_info);
    return $dir_info;
  }
/**
 * Recursive algorithm to restrict all sub_categories of a specified category to a specified product_type
 */
  function zen_restrict_sub_categories($zf_cat_id, $zf_type) {
    global $db;
    $zp_sql = "select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$zf_cat_id . "'";
    $zq_sub_cats = $db->Execute($zp_sql);
    while (!$zq_sub_cats->EOF) {
      $zp_sql = "select * from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                         where category_id = '" . (int)$zq_sub_cats->fields['categories_id'] . "'
                         and product_type_id = '" . (int)$zf_type . "'";

      $zq_type_to_cat = $db->Execute($zp_sql);

      if ($zq_type_to_cat->RecordCount() < 1) {
        $za_insert_sql_data = array('category_id' => (int)$zq_sub_cats->fields['categories_id'],
                                    'product_type_id' => (int)$zf_type);
        zen_db_perform(TABLE_PRODUCT_TYPES_TO_CATEGORY, $za_insert_sql_data);
      }
      zen_restrict_sub_categories($zq_sub_cats->fields['categories_id'], $zf_type);
      $zq_sub_cats->MoveNext();
    }
  }


/**
 * Recursive algorithm to UNDO restriction from all sub_categories of a specified category for a specified product_type
 */
  function zen_remove_restrict_sub_categories($zf_cat_id, $zf_type) {
    global $db;
    $zp_sql = "select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$zf_cat_id . "'";
    $zq_sub_cats = $db->Execute($zp_sql);
    while (!$zq_sub_cats->EOF) {
        $sql = "delete from " .  TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                where category_id = '" . (int)$zq_sub_cats->fields['categories_id'] . "'
                and product_type_id = '" . (int)$zf_type . "'";

        $db->Execute($sql);
      zen_remove_restrict_sub_categories($zq_sub_cats->fields['categories_id'], $zf_type);
      $zq_sub_cats->MoveNext();
    }
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
    function zen_get_show_product_switch($lookup, $field, $prefix= 'SHOW_', $suffix= '_INFO', $field_prefix= '_', $field_suffix='') {
      global $db;
      $zv_key = zen_get_show_product_switch_name($lookup, $field, $prefix, $suffix, $field_prefix, $field_suffix);
      $sql = "select configuration_key, configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . zen_db_input($zv_key) . "'";
      $zv_key_value = $db->Execute($sql);
//echo 'I CAN SEE - look ' . $lookup . ' - field ' . $field . ' - key ' . $zv_key . ' value ' . $zv_key_value->fields['configuration_value'] .'<br>';

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
 * return switch name
 */
    function zen_get_show_product_switch_name($lookup, $field, $prefix= 'SHOW_', $suffix= '_INFO', $field_prefix= '_', $field_suffix='') {
      global $db;
      $type_lookup = 0;
      $type_handler = '';
      $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id='" . (int)$lookup . "'";
      $result = $db->Execute($sql);
      if (!$result->EOF) $type_lookup = $result->fields['products_type'];

      $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = '" . (int)$type_lookup . "'";
      $result = $db->Execute($sql);
      if (!$result->EOF) $type_handler = $result->fields['type_handler'];
      $zv_key = strtoupper($prefix . $type_handler . $suffix . $field_prefix . $field . $field_suffix);

      return $zv_key;
    }

/**
 * compute the days between two dates
 */
  function zen_date_diff($date1, $date2) {
  //$date1  today, or any other day
  //$date2  date to check against

    $d1 = explode("-", $date1);
    $y1 = $d1[0];
    $m1 = $d1[1];
    $d1 = $d1[2];

    $d2 = explode("-", $date2);
    $y2 = $d2[0];
    $m2 = $d2[1];
    $d2 = $d2[2];

    $date1_set = mktime(0,0,0, $m1, $d1, $y1);
    $date2_set = mktime(0,0,0, $m2, $d2, $y2);

    return(round(($date2_set-$date1_set)/(60*60*24)));
  }

/**
 * check that the specified download filename exists on the filesystem
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
 * salemaker categories array
 */
  function zen_parse_salemaker_categories($clist) {
    $clist_array = explode(',', $clist);

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($clist_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($clist_array[$i], $tmp_array)) {
        $tmp_array[] = $clist_array[$i];
      }
    }
    return $tmp_array;
  }

/**
 * update salemaker product prices per category per product for the specified $salemaker_id
 */
  function zen_update_salemaker_product_prices($salemaker_id) {
    global $db;
    $zv_categories = $db->Execute("select sale_categories_selected from " . TABLE_SALEMAKER_SALES . " where sale_id = '" . (int)$salemaker_id . "'");
    if ($zv_categories->EOF) return FALSE;
    $za_salemaker_categories = zen_parse_salemaker_categories($zv_categories->fields['sale_categories_selected']);
    $n = sizeof($za_salemaker_categories);
    for ($i=0; $i<$n; $i++) {
      $update_products_price = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . (int)$za_salemaker_categories[$i] . "'");
      while (!$update_products_price->EOF) {
        zen_update_products_price_sorter($update_products_price->fields['products_id']);
        $update_products_price->MoveNext();
      }
    }
  }

/**
 * check if products has quantity-discounts defined
 */
  function zen_has_product_discounts($look_up) {
    global $db;

    $check_discount_query = "select products_id from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id='" . (int)$look_up . "'";
    $check_discount = $db->Execute($check_discount_query);

    if ($check_discount->RecordCount() > 0) {
      return 'true';
    } else {
      return 'false';
    }
  }

/**
 * copy quantity-discounts from one product to another
 */
  function zen_copy_discounts_to_product($copy_from, $copy_to) {
    global $db;

    $check_discount_type_query = "select products_discount_type, products_discount_type_from, products_mixed_discount_quantity from " . TABLE_PRODUCTS . " where products_id='" . (int)$copy_from . "'";
    $check_discount_type = $db->Execute($check_discount_type_query);
    if ($check_discount_type->EOF) return FALSE;

    $db->Execute("update " . TABLE_PRODUCTS . " set products_discount_type='" . $check_discount_type->fields['products_discount_type'] . "', products_discount_type_from='" . $check_discount_type->fields['products_discount_type_from'] . "', products_mixed_discount_quantity='" . $check_discount_type->fields['products_mixed_discount_quantity'] . "' where products_id='" . (int)$copy_to . "'");

    $check_discount_query = "select * from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id='" . (int)$copy_from . "' order by discount_id";
    $check_discount = $db->Execute($check_discount_query);
    $cnt_discount=1;
    while (!$check_discount->EOF) {
      $db->Execute("insert into " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                  (discount_id, products_id, discount_qty, discount_price )
                  values ('" . (int)$cnt_discount . "', '" . (int)$copy_to . "', '" . $check_discount->fields['discount_qty'] . "', '" . $check_discount->fields['discount_price'] . "')");
      $cnt_discount++;
      $check_discount->MoveNext();
    }
  }


/**
 * Lookup and return product's master_categories_id
 * TABLES: categories
 */
  function zen_get_parent_category_id($product_id) {
    global $db;

    $categories_lookup = $db->Execute("select master_categories_id
                                from " . TABLE_PRODUCTS . "
                                where products_id = '" . (int)$product_id . "'");
    if ($categories_lookup->EOF) return '';
    return $categories_lookup->fields['master_categories_id'];
  }

/**
 * replacement for fmod to manage values < 1
 */
  function fmod_round($x, $y) {
    if ($y == 0) {
      return 0;
    }
    $x = strval($x);
    $y = strval($y);
    $zc_round = ($x*1000)/($y*1000);
    $zc_round_ceil = (int)($zc_round);
    $multiplier = $zc_round_ceil * $y;
    $results = abs(round($x - $multiplier, 6));
     return $results;
  }

/**
 * return any field from products or products_description table
 * Example: zen_products_lookup('3', 'products_date_added');
 */
  function zen_products_lookup($product_id, $what_field = 'products_name', $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_lookup = $db->Execute("select " . zen_db_input($what_field) . " as lookup_field
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where  p.products_id ='" . (int)$product_id . "'
                              and pd.products_id = p.products_id
                              and pd.language_id = '" . (int)$language . "'");
    $return_field = $product_lookup->fields['lookup_field'];
    if ($return_field->EOF) return '';
    return $return_field;
  }

  function zen_count_days($start_date, $end_date, $lookup = 'm') {
    if ($lookup == 'd') {
    // Returns number of days
      $start_datetime = gmmktime (0, 0, 0, substr ($start_date, 5, 2), substr ($start_date, 8, 2), substr ($start_date, 0, 4));
      $end_datetime = gmmktime (0, 0, 0, substr ($end_date, 5, 2), substr ($end_date, 8, 2), substr ($end_date, 0, 4));
      $days = (($end_datetime - $start_datetime) / 86400) + 1;
      $d = $days % 7;
      $w = date("w", $start_datetime);
      $result = floor ($days / 7) * 5;
      $counter = $result + $d - (($d + $w) >= 7) - (($d + $w) >= 8) - ($w == 0);
    }
    if ($lookup == 'm') {
    // Returns whole-month-count between two dates
    // courtesy of websafe<at>partybitchez<dot>org
      $start_date_unixtimestamp = strtotime($start_date);
      $start_date_month = date("m", $start_date_unixtimestamp);
      $end_date_unixtimestamp = strtotime($end_date);
      $end_date_month = date("m", $end_date_unixtimestamp);
      $calculated_date_unixtimestamp = $start_date_unixtimestamp;
      $counter=0;
      while ($calculated_date_unixtimestamp < $end_date_unixtimestamp) {
        $counter++;
        $calculated_date_unixtimestamp = strtotime($start_date . " +{$counter} months");
      }
      if ( ($counter==1) && ($end_date_month==$start_date_month)) $counter=($counter-1);
    }
    return $counter;
  }

/**
 * Get all products_id in a Category and its SubCategories
 * use as:
 * $my_products_id_list = array();
 * $my_products_id_list = zen_get_categories_products_list($categories_id)
 */
  function zen_get_categories_products_list($categories_id, $include_deactivated = false, $include_child = true) {
    global $db;
    global $categories_products_id_list;

    if ($include_deactivated) {

      $products = $db->Execute("select p.products_id
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                where p.products_id = p2c.products_id
                                and p2c.categories_id = '" . (int)$categories_id . "'");
    } else {
      $products = $db->Execute("select p.products_id
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                where p.products_id = p2c.products_id
                                and p.products_status = '1'
                                and p2c.categories_id = '" . (int)$categories_id . "'");
    }

    while (!$products->EOF) {
// categories_products_id_list keeps resetting when category changes ...
//      echo 'Products ID: ' . $products->fields['products_id'] . '<br>';
      $categories_products_id_list[] = $products->fields['products_id'];
      $products->MoveNext();
    }

    if ($include_child) {
      $childs = $db->Execute("select categories_id from " . TABLE_CATEGORIES . "
                              where parent_id = '" . (int)$categories_id . "'");
      if ($childs->RecordCount() > 0 ) {
        while (!$childs->EOF) {
          zen_get_categories_products_list($childs->fields['categories_id'], $include_deactivated);
          $childs->MoveNext();
        }
      }
    }
    $products_id_listing = $categories_products_id_list;
    return $products_id_listing;
  }

  function zen_geo_zones_pull_down_coupon($parameters, $selected = '') {
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $zones = $db->Execute("select geo_zone_id, geo_zone_name
                                 from " . TABLE_GEO_ZONES . "
                                 order by geo_zone_name");

    if ($selected == 0) {
      $select_string .= '<option value=0 SELECTED>' . TEXT_NONE . '</option>';
    } else {
      $select_string .= '<option value=0>' . TEXT_NONE . '</option>';
    }

    while (!$zones->EOF) {
      $select_string .= '<option value="' . $zones->fields['geo_zone_id'] . '"';
      if ($selected == $zones->fields['geo_zone_id']) $select_string .= ' SELECTED';
      $select_string .= '>' . $zones->fields['geo_zone_name'] . '</option>';
      $zones->MoveNext();
    }
    $select_string .= '</select>';

    return $select_string;
  }

/**
 * customer lookup of address book
 */
  function zen_get_customers_address_book($customer_id) {
    global $db;

    $customer_address_book_count_query = "SELECT c.*, ab.* from " .
                                          TABLE_CUSTOMERS . " c
                                          left join " . TABLE_ADDRESS_BOOK . " ab on c.customers_id = ab.customers_id
                                          WHERE c.customers_id = '" . (int)$customer_id . "'";

    $customer_address_book_count = $db->Execute($customer_address_book_count_query);
    return $customer_address_book_count;
  }

/**
 * get customer comments
 */
  function zen_get_orders_comments($orders_id) {
    global $db;
    $orders_comments_query = "SELECT osh.comments from " .
                              TABLE_ORDERS_STATUS_HISTORY . " osh
                              where osh.orders_id = '" . (int)$orders_id . "'
                              order by osh.orders_status_history_id
                              limit 1";
    $orders_comments = $db->Execute($orders_comments_query);
    if ($orders_comments->EOF) return '';
    return $orders_comments->fields['comments'];
  }

/**
 * manufacturers name
 */
  function zen_get_products_manufacturers_name($product_id) {
    global $db;

    $product_query = "select m.manufacturers_name
                      from " . TABLE_PRODUCTS . " p, " .
                            TABLE_MANUFACTURERS . " m
                      where p.products_id = '" . (int)$product_id . "'
                      and p.manufacturers_id = m.manufacturers_id";
    $product = $db->Execute($product_query);

    return ($product->RecordCount() > 0) ? $product->fields['manufacturers_name'] : "";
  }

  function zen_user_has_gv_balance($c_id) {
    global $db;
      $gv_result = $db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . (int)$c_id . "'");
      if ($gv_result->RecordCount() > 0) {
        if ($gv_result->fields['amount'] > 0) {
          return $gv_result->fields['amount'];
        }
      }
      return 0;
  }

function zen_format_date_raw($date, $formatOut = 'mysql', $formatIn = DATE_FORMAT_DATEPICKER_ADMIN)
{
  if ($date == 'null' || $date == '') return $date;
  $mpos = strpos($formatIn, 'm');
  $dpos = strpos($formatIn, 'd');
  $ypos = strpos($formatIn, 'y');
  $d = substr($date, $dpos, 2);
  $m = substr($date, $mpos, 2);
  $y = substr($date, $ypos, 4);
  switch ($formatOut)
  {
    case 'raw':
      $mdate = $y . $m . $d;
      break;
    case 'raw-reverse':
      $mdate = $d . $m . $y;
      break;
    case 'mysql':
     $mdate = $y . '-' . $m . '-' . $d;

  }
  return $mdate;
}

/**
 * Determine visitor's IP address, resolving any proxies where possible.
 *
 * @return string
 */
function zen_get_ip_address() {
  $ip = '';
  /**
   * resolve any proxies
   */
  if (isset($_SERVER)) {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
      $ip = $_SERVER['HTTP_FORWARDED'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
  }
  if (trim($ip) == '') {
    if (getenv('HTTP_X_FORWARDED_FOR')) {
      $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_CLIENT_IP')) {
      $ip = getenv('HTTP_CLIENT_IP');
    } else {
      $ip = getenv('REMOTE_ADDR');
    }
  }

  /**
   * sanitize for validity as an IPv4 or IPv6 address
   */
  $ip = preg_replace('~[^a-fA-F0-9.:%/]~', '', $ip);

  /**
   *  if it's still blank, set to a single dot
   */
  if (trim($ip) == '') $ip = '.';

  return $ip;
}

/**
 * Perform an array multisort, based on 1 or 2 columns being passed
 * (defaults to sorting by first column ascendingly then second column ascendingly unless otherwise specified)
 *
 * @param $data        multidimensional array to be sorted
 * @param $columnName1 string representing the named column to sort by as first criteria
 * @param $order1      either SORT_ASC or SORT_DESC (default SORT_ASC)
 * @param $columnName2 string representing named column as second criteria
 * @param $order2      either SORT_ASC or SORT_DESC (default SORT_ASC)
 * @return array   Original array sorted as specified
 */
function zen_sort_array($data, $columnName1 = '', $order1 = SORT_ASC, $columnName2 = '', $order2 = SORT_ASC)
{
  // simple validations
  $keys = array_keys($data);
  if ($columnName1 == '') {
    $columnName1 = $keys[0];
  }
  if (!in_array($order1, array(SORT_ASC, SORT_DESC))) $order1=SORT_ASC;
  if ($columnName2 == '') {
    $columnName2 = $keys[1];
  }
  if (!in_array($order2, array(SORT_ASC, SORT_DESC))) $order2=SORT_ASC;

  // prepare sub-arrays for aiding in sorting
  foreach($data as $key=>$val)
  {
    $sort1[] = $val[$columnName1];
    $sort2[] = $val[$columnName2];
  }
  // do actual sort based on specified fields.
  array_multisort($sort1, $order1, $sort2, $order2, $data);
  return $data;
}

/**
 * Obtain a list of .log/.xml files from the /logs/ folder
 * (and also /cache/ folder for backward compatibility of older modules which store logs there)
 *
 * If $maxToList == 'count' then it returns the total number of files found
 * If an integer is passed, then an array of files is returned, including paths, filenames, and datetime details
 *
 * @param $maxToList mixed (integer or 'count')
 * @return array or integer
 *
 * inspired by log checking suggestion from Steve Sherratt (torvista)
 */
function get_logs_data($maxToList = 'count') {
  if (!defined('DIR_FS_LOGS')) define('DIR_FS_LOGS', DIR_FS_CATALOG . 'logs');
  if (!defined('DIR_FS_SQL_CACHE')) define('DIR_FS_SQL_CACHE', DIR_FS_CATALOG . 'cache');
  $logs = array();
  $file = array();
  $i = 0;
  foreach(array(DIR_FS_LOGS, DIR_FS_SQL_CACHE) as $purgeFolder) {
    $purgeFolder = rtrim($purgeFolder, '/');
    if (!file_exists($purgeFolder) || !is_dir($purgeFolder)) continue;

    $dir = dir($purgeFolder);
    while ($logfile = $dir->read()) {
      if (substr($logfile, 0, 1) == '.') continue;
      if (!preg_match('/.*(\.log|\.xml)$/', $logfile)) continue; // xml allows for usps debug

      if ($maxToList != 'count') {
        $filename = $purgeFolder . '/' . $logfile;
        $logs[$i]['path'] = $purgeFolder . "/";
        $logs[$i]['filename'] = $logfile;
        $logs[$i]['filesize'] = @filesize($filename);
        $logs[$i]['unixtime'] = @filemtime($filename);
        $logs[$i]['datetime'] = strftime(DATE_TIME_FORMAT, $logs[$i]['unixtime']);
      }
      $i++;
      if ($maxToList != 'count' && $i >= $maxToList) break;
    }
    $dir->close();
    unset($dir);
  }

  if ($maxToList == 'count') return $i;

  $logs = zen_sort_array($logs, 'unixtime', SORT_DESC);
  return $logs;
}

/**
 * function to override PHP's is_writable() which can occasionally be unreliable due to O/S and F/S differences
 * attempts to open the specified file for writing. Returns true if successful, false if not.
 * if a directory is specified, uses PHP's is_writable() anyway
 *
 * @var string
 * @return boolean
 */
  function is__writeable($filepath, $make_unwritable = true) {
    if (is_dir($filepath)) return is_writable($filepath);
    $fp = @fopen($filepath, 'a');
    if ($fp) {
      @fclose($fp);
      if ($make_unwritable) set_unwritable($filepath);
      $fp = @fopen($filepath, 'a');
      if ($fp) {
        @fclose($fp);
        return true;
      }
    }
    return false;
  }
/**
 * attempts to make the specified file read-only
 *
 * @var string
 * @return boolean
 */
  function set_unwritable($filepath) {
    return @chmod($filepath, 0444);
  }
  
/**
 * function issetorArray
 *
 * returns an array[key] or default value if key does not exist
 *
 * @param array $array
 * @param $key
 * @param null $default
 * @return mixed
 */
function issetorArray(array $array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Lookup session-specific language file, and load
 *
 * @param string $file filename of language file
 */
function zen_load_language_file($file) {
    if (file_exists(DIR_WS_LANGUAGES . $_SESSION ['language'] . '/' . $file)) {
        include (DIR_WS_LANGUAGES . $_SESSION ['language'] . '/' . $file);
        return;
    }
    if (file_exists(DIR_WS_LANGUAGES . 'english' . '/' . $file)) {
        include (DIR_WS_LANGUAGES . 'english' . '/' . $file);
    }
}
