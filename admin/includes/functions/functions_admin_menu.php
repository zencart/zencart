<?php
/**
 * functions for admin configuration
 *
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
  */


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
    return zen_draw_pull_down_menu($name, zen_get_countries_for_pulldown(), $country_id);
  }


////
  function zen_cfg_pull_down_country_list_none($country_id, $key = '') {
    $country_array = zen_get_countries_for_pulldown('None');
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


/**
 * Check if restricted-use demo mode is active
 */
  function zen_admin_demo() {
    return (ADMIN_DEMO == '1') ? TRUE : FALSE;
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
 * Output a day/month/year dropdown selector
 */
if (!function_exists('zen_draw_date_selector')) {
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
}
