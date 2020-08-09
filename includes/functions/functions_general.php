<?php
/**
 * functions_general.php
 * General functions used throughout Zen Cart
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 23 Modified in v1.5.8 $
 */


/**
 * Parse the data used in the html tags to ensure the tags will not break.
 * Basically just an extension to the php strtr function
 * @param string The string to be parsed
 * @param string The needle to find
*/
  function zen_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }


////
// Return table heading with sorting capabilities
  function zen_create_sort_heading($sortby, $colnum, $heading) {
    $sort_prefix = '';
    $sort_suffix = '';

    if ($sortby) {
      $sort_prefix = '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('page', 'info', 'sort')) . 'page=1&sort=' . $colnum . ($sortby == $colnum . 'a' ? 'd' : 'a')) . '" title="' . zen_output_string(TEXT_SORT_PRODUCTS . ($sortby == $colnum . 'd' || substr($sortby, 0, 1) != $colnum ? TEXT_ASCENDINGLY : TEXT_DESCENDINGLY) . TEXT_BY . $heading) . '" class="productListing-heading">' ;
      $sort_suffix = (substr($sortby, 0, 1) == $colnum ? (substr($sortby, 1, 1) == 'a' ? PRODUCT_LIST_SORT_ORDER_ASCENDING : PRODUCT_LIST_SORT_ORDER_DESCENDING) : '') . '</a>';
    }

    return $sort_prefix . $heading . $sort_suffix;
  }


////
// Get the number of times a word/character is present in a string
  function zen_word_count($string, $needle) {
    $temp_array = preg_split('/'.$needle.'/', $string);

    return count($temp_array);
  }


////
  function zen_count_modules($modules = '') {
    $count = 0;

    if (empty($modules)) return $count;

    $modules_array = preg_split('/;/', $modules);

    for ($i=0, $n=count($modules_array); $i<$n; $i++) {
      $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));

      if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {
        if ($GLOBALS[$class]->enabled) {
          $count++;
        }
      }
    }
    return $count;
  }

////
  function zen_count_payment_modules() {
    return zen_count_modules(MODULE_PAYMENT_INSTALLED);
  }

////
  function zen_count_shipping_modules() {
    return zen_count_modules(MODULE_SHIPPING_INSTALLED);
  }

////
  function zen_array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
    if (!is_array($exclude)) $exclude = array();
    if (!is_array($array)) $array = array();

    $get_string = '';
    if (sizeof($array) > 0) {
      foreach($array as $key => $value) {
        if ( (!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y') ) {
          $get_string .= $key . $equals . $value . $separator;
        }
      }
      $remove_chars = strlen($separator);
      $get_string = substr($get_string, 0, -$remove_chars);
    }

    return $get_string;
  }


////
// Checks to see if the currency code exists as a currency
// TABLES: currencies
  function zen_currency_exists($code, $getFirstDefault = false) {
    global $db;
    $code = zen_db_prepare_input($code);

    $currency_code = "SELECT code
                      FROM " . TABLE_CURRENCIES . "
                      WHERE code = '" . zen_db_input($code) . "' LIMIT 1";

    $currency_first = "SELECT code
                      FROM " . TABLE_CURRENCIES . "
                      ORDER BY value ASC LIMIT 1";

    $currency = $db->Execute(($getFirstDefault == false) ? $currency_code : $currency_first);

    if ($currency->RecordCount()) {
      return strtoupper($currency->fields['code']);
    }
    return false;
  }

////
// Return a random value
  function zen_rand($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
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

  function zen_convert_linefeeds($from, $to, $string) {
    return str_replace($from, $to, $string);
  }

/**
 * return the size and maxlength settings in the form size="blah" maxlength="blah" based on maximum size being 70
 * uses $tbl = table name, $fld = field name
 * example: zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name')
 * @param string $tbl
 * @param string $fld
 * @param int $max
 * @return string
 */
function zen_set_field_length($tbl, $fld, $max = 70)
{
    $field_length = zen_field_length($tbl, $fld);
    switch (true) {
        case ($field_length > $max):
            $length = 'size="' . ($max + 1) . '" maxlength="' . $field_length . '"';
            break;
        default:
            $length = 'size="' . ($field_length + 1) . '"maxlength="' . $field_length . '"';
            break;
    }
    return $length;
}



////
// set current box id
  function zen_get_box_id($box_id) {
    $box_id = str_replace('_', '', $box_id);
    $box_id = str_replace('.php', '', $box_id);
    return $box_id;
  }


////
// Switch buy now button based on call for price sold out etc.
  function zen_get_buy_now_button($product_id, $link, $additional_link = false) {
    global $db;

// show case only supercedes all other settings
    if (STORE_STATUS != '0') {
      return '<a href="' . zen_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' .  TEXT_SHOWCASE_ONLY . '</a>';
    }

// 0 = normal shopping
// 1 = Login to shop
// 2 = Can browse but no prices
    // verify display of prices
      switch (true) {
        case (CUSTOMERS_APPROVAL == '1' && !zen_is_logged_in()):
        // customer must be logged in to browse
        $login_for_price = '<a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' .  TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE . '</a>';
        return $login_for_price;
        break;
        case (CUSTOMERS_APPROVAL == '2' && !zen_is_logged_in()):
        if (TEXT_LOGIN_FOR_PRICE_PRICE == '') {
          // show room only
          return TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE;
        } else {
          // customer may browse but no prices
          $login_for_price = '<a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' .  TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE . '</a>';
        }
        return $login_for_price;
        break;
        // show room only
        case (CUSTOMERS_APPROVAL == '3'):
          $login_for_price = TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM;
          return $login_for_price;
        break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && CUSTOMERS_APPROVAL_AUTHORIZATION != '3' && !zen_is_logged_in()):
        // customer must be logged in to browse
        $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
        return $login_for_price;
        break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION == '3' && !zen_is_logged_in()):
        // customer must be logged in and approved to add to cart
        $login_for_price = '<a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' .  TEXT_LOGIN_TO_SHOP_BUTTON_REPLACE . '</a>';
        return $login_for_price;
        break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && isset($_SESSION['customers_authorization']) && (int)$_SESSION['customers_authorization'] > 0):
        // customer must be logged in to browse
        $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
        return $login_for_price;
        break;
        case (isset($_SESSION['customers_authorization']) && (int)$_SESSION['customers_authorization'] >= 2):
        // customer is logged in and was changed to must be approved to buy
        $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
        return $login_for_price;
        break;
        default:
        // proceed normally
        break;
      }

    $button_check = $db->Execute("SELECT product_is_call, products_quantity FROM " . TABLE_PRODUCTS . " WHERE products_id = " . (int)$product_id);
    switch (true) {
// cannot be added to the cart
    case (zen_get_products_allow_add_to_cart($product_id) == 'N'):
      return $additional_link;
      break;
    case ($button_check->fields['product_is_call'] == '1'):
      $return_button = '<a href="' . zen_href_link(FILENAME_ASK_A_QUESTION, 'pid='.(int)$product_id, 'SSL') . '">' . TEXT_CALL_FOR_PRICE . '</a>';
      break;
    case ($button_check->fields['products_quantity'] <= 0 and SHOW_PRODUCTS_SOLD_OUT_IMAGE == '1'):
      if ($_GET['main_page'] == zen_get_info_page($product_id)) {
        $return_button = zen_image_button(BUTTON_IMAGE_SOLD_OUT, BUTTON_SOLD_OUT_ALT);
      } else {
        $return_button = zen_image_button(BUTTON_IMAGE_SOLD_OUT_SMALL, BUTTON_SOLD_OUT_SMALL_ALT);
      }
      break;
    default:
      $return_button = $link;
      break;
    }
    if ($return_button != $link and $additional_link != false) {
      return $additional_link . '<br />' . $return_button;
    } else {
      return $return_button;
    }
  }


////
// enable shipping
  function zen_get_shipping_enabled($shipping_module) {
    global $PHP_SELF, $order;

    // for admin always true if installed
    if (strstr($PHP_SELF, FILENAME_MODULES)) {
      return true;
    }

    $check_cart_free = $_SESSION['cart']->in_cart_check('product_is_always_free_shipping','1');
    $check_cart_cnt = $_SESSION['cart']->count_contents();
    $check_cart_weight = $_SESSION['cart']->show_weight();

    switch(true) {
      // for admin always true if installed
      case (strstr($PHP_SELF, FILENAME_MODULES)):
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


////
  function zen_html_entity_decode($given_html, $quote_style = ENT_QUOTES) {
    $trans_table = array_flip(get_html_translation_table( HTML_SPECIALCHARS, $quote_style ));
    $trans_table['&#39;'] = "'";
    return ( strtr( $given_html, $trans_table ) );
  }

////
//CLR 030228 Add function zen_decode_specialchars
// Decode string encoded with htmlspecialchars()
  function zen_decode_specialchars($string){
    $string=str_replace('&gt;', '>', $string);
    $string=str_replace('&lt;', '<', $string);
    $string=str_replace('&#039;', "'", $string);
    $string=str_replace('&quot;', "\"", $string);
    $string=str_replace('&amp;', '&', $string);

    return $string;
  }

////
// remove common HTML from text for display as paragraph
  function zen_clean_html($clean_it, $extraTags = '') {
    if (!is_array($extraTags)) $extraTags = array($extraTags);

    // remove any embedded javascript
    $clean_it = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $clean_it);

    $clean_it = preg_replace('/\r/', ' ', $clean_it);
    $clean_it = preg_replace('/\t/', ' ', $clean_it);
    $clean_it = preg_replace('/\n/', ' ', $clean_it);

    $clean_it= nl2br($clean_it);

// update breaks with a space for text displays in all listings with descriptions
    $clean_it = preg_replace('~(<br ?/?>|</?p>)~', ' ', $clean_it);

// temporary fix more for reviews than anything else
    $clean_it = str_replace('<span class="smallText">', ' ', $clean_it);
    $clean_it = str_replace('</span>', ' ', $clean_it);

// clean general and specific tags:
    $taglist = array('strong','b','u','i','em');
    $taglist = array_merge($taglist, (is_array($extraTags) ? $extraTags : array($extraTags)));
    foreach ($taglist as $tofind) {
      if ($tofind != '') $clean_it = preg_replace("/<[\/\!]*?" . $tofind . "[^<>]*?>/si", ' ', $clean_it);
    }

// remove any double-spaces created by cleanups:
    $clean_it = preg_replace('/[ ]+/', ' ', $clean_it);

// remove other html code to prevent problems on display of text
    $clean_it = strip_tags($clean_it);
    return $clean_it;
  }



/**
 * check to see if database stored GET terms are in the URL as $_GET parameters
 * This is used to determine which filters should be applied
 * @return bool
 */
  function zen_check_url_get_terms() {
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


  // replacement for fmod to manage values < 1
  function fmod_round($x, $y) {
    if ($y == 0) {
      return 0;
    }
    $x = strval($x);
    $y = strval($y);
    $zc_round = ($x*1000)/($y*1000);
    $zc_round_ceil = round($zc_round,0);
    $multiplier = $zc_round_ceil * $y;
    $results = abs(round($x - $multiplier, 6));
    return $results;
  }

////
// return truncated paragraph
  function zen_truncate_paragraph($paragraph, $size = 100) {
    $zv_paragraph = "";
    $word = explode(" ", $paragraph);
    $zv_total = count($word);
    if ($zv_total > $size) {
      for ($x=0; $x < $size; $x++) {
        $zv_paragraph = $zv_paragraph . $word[$x] . " ";
      }
      $zv_paragraph = trim($zv_paragraph);
    } else {
      $zv_paragraph = trim($paragraph);
    }
    return $zv_paragraph;
  }


/**
 * returns a pulldown array with zones defined for the specified country
 * used by zen_prepare_country_zones_pull_down()
 *
 * @param int $country_id
 * @return array for pulldown
 */
  function zen_get_country_zones($country_id) {
    global $db;
    $zones_array = array();
    $zones = $db->Execute("SELECT zone_id, zone_name
                           FROM " . TABLE_ZONES . "
                           WHERE zone_country_id = " . (int)$country_id . "
                           ORDER BY zone_name");
    foreach ($zones as $zone) {
      $zones_array[] = array('id' => $zone['zone_id'], 'text' => $zone['zone_name']);
    }

    return $zones_array;
  }

/**
 * return an array with country names and matching zones to be used in pulldown menus
 */
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

/**
 * supplies javascript to dynamically update the states/provinces list when the country is changed
 * TABLES: zones
 *
 * return string
 */
  function zen_js_zone_list($country, $form, $field) {
    global $db;
    $countries = $db->Execute("SELECT DISTINCT zone_country_id
                               FROM " . TABLE_ZONES . "
                               ORDER BY zone_country_id");
    $num_country = 1;
    $output_string = '';
    while (!$countries->EOF) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      }

      $states = $db->Execute("SELECT zone_name, zone_id
                              FROM " . TABLE_ZONES . "
                              WHERE zone_country_id = '" . $countries->fields['zone_country_id'] . "'
                              order by zone_name");
      $num_state = 1;
      while (!$states->EOF) {
        if ($num_state == 1) $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
        $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $states->fields['zone_name'] . '", "' . $states->fields['zone_id'] . '");' . "\n";
        $num_state++;
        $states->MoveNext();
      }
      $num_country++;
      $countries->MoveNext();
      $output_string .= '    hideStateField(' . $form . ');' . "\n" ;
    }
    $output_string .= '  } else {' . "\n" .
                      '    ' . $form . '.' . $field . '.options[0] = new Option("' . TYPE_BELOW . '", "");' . "\n" .
                      '    showStateField(' . $form . ');' . "\n" .
                      '  }' . "\n";
    return $output_string;
  }





/**
 * strip out accented characters to reasonable approximations of english equivalents
 */
  function replace_accents($s) {
    $skipPreg = (defined('OVERRIDE_REPLACE_ACCENTS_WITH_HTMLENTITIES') && OVERRIDE_REPLACE_ACCENTS_WITH_HTMLENTITIES == 'TRUE') ? TRUE : FALSE;
    $s = htmlentities($s, ENT_COMPAT, CHARSET);
    if ($skipPreg == FALSE) {
      $s = preg_replace ('/&([a-zA-Z])(uml|acute|elig|grave|circ|tilde|cedil|ring|quest|slash|caron);/', '$1', $s);
    }
    $s = html_entity_decode($s);
    return $s;
  }

/**
 * convert supplied string to UTF-8, dropping any symbols which cannot be translated easily
 * useful for submitting cleaned-up data to payment gateways or other external services, esp if the data was copy+pasted from windows docs via windows browser to store in database
 *
 * @param string $string
 */
  function charsetConvertWinToUtf8($string) {
    if (function_exists('iconv')) $string = iconv("Windows-1252", "ISO-8859-1//IGNORE", $string);
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
    return $string;
  }

/**
 * Convert supplied string to/from entities between charsets, to sanitize data from payment gateway
 * @param $string
 * @return string
 */
  function charsetClean($string) {
    if (preg_replace('/[^a-z0-9]/', '', strtolower(CHARSET)) == 'utf8') return $string;
    if (function_exists('iconv')) $string = iconv("Windows-1252", CHARSET . "//IGNORE", $string);
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
    $string = html_entity_decode($string, ENT_QUOTES, CHARSET);
    return $string;
  }

  // Helper function to check whether the current instance is using SSL or not.
  // Returns SSL or NONSSL
  function getConnectionType() {
    global $request_type;
    return $request_type;
  }

  // debug utility only
  function utilDumpRequest($mode='p', $out = 'log') {
    if ($mode =='p') {
      $val = '<pre>DEBUG request: ' . print_r($_REQUEST, TRUE);
    } else {
      @ob_start();
      var_dump('DEBUG request: ', $_REQUEST);
      $val = @ob_get_contents();
      @ob_end_clean();
    }
    if ($out == 'log' || $out == 'l') {
      error_log($val);
    } else if ($out == 'die' || $out == 'd') {
      die($val);
    } else if ($out == 'echo' || $out == 'e') {
      echo $val;
    }
  }
  function fixup_url($url)
  {
    if (!preg_match('#^https?://#', $url)) {
      $url = 'http://' . $url;
    }
    return $url;
  }


  /////////////////////////////////////////////
////
// call additional function files
// prices and quantities
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_prices.php');
// taxes
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_taxes.php');
// gv and coupons
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_gvcoupons.php');
// categories, paths, pulldowns
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_categories.php');
// customers and addresses
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_customers.php');
// lookup information
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_lookups.php');
////
/////////////////////////////////////////////

