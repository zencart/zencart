<?php
/**
 * functions_general.php
 * General functions used throughout Zen Cart
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Fri Apr 22 22:16:43 2015 +0000 Modified in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Stop from parsing any further PHP code
*/
  function zen_exit() {
   session_write_close();
   exit();
  }

/**
 * Redirect to another page or site
 * @param string The url to redirect to
*/
  function zen_redirect($url, $httpResponseCode = '') {
    global $request_type;
    // Are we loading an SSL page?
    if ( (ENABLE_SSL == 'true') && ($request_type == 'SSL') ) {
      // yes, but a NONSSL url was supplied
      if (substr($url, 0, strlen(HTTP_SERVER . DIR_WS_CATALOG)) == HTTP_SERVER . DIR_WS_CATALOG) {
        // So, change it to SSL, based on site's configuration for SSL
        $url = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . substr($url, strlen(HTTP_SERVER . DIR_WS_CATALOG));
      }
    }

  // clean up URL before executing it
    $url = preg_replace('/&{2,}/', '&', $url);
    $url = preg_replace('/(&amp;)+/', '&amp;', $url);
    // header locates should not have the &amp; in the address it breaks things
    $url = preg_replace('/(&amp;)+/', '&', $url);

    if ($httpResponseCode == '') {
      session_write_close();
      header('Location: ' . $url);
    } else {
      session_write_close();
      header('Location: ' . $url, TRUE, (int)$httpResponseCode);
    }

    exit();
  }

/**
 * Parse the data used in the html tags to ensure the tags will not break.
 * Basically just an extension to the php strtr function
 * @param string The string to be parsed
 * @param string The needle to find
*/
  function zen_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }

/**
 * Returns a string with conversions for security.
 * @param string The string to be parsed
 * @param string contains a string to be translated, otherwise just quote is translated
 * @param boolean Do we run htmlspecialchars over the string
*/
  function zen_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string, ENT_COMPAT, CHARSET, TRUE);
    } else {
      if ($translate == false) {
        return zen_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return zen_parse_input_field_data($string, $translate);
      }
    }
  }

/**
 * Returns a string with conversions for security.
 *
 * Simply calls the zen_ouput_string function
 * with parameters that run htmlspecialchars over the string
 * and converts quotes to html entities
 *
 * @param string The string to be parsed
*/
  function zen_output_string_protected($string) {
    return zen_output_string($string, false, true);
  }

/**
 * Returns a string with conversions for security.
 *
 * @param string The string to be parsed
*/

  function zen_sanitize_string($string) {
    $string = preg_replace('/ +/', ' ', $string);
    return preg_replace("/[<>]/", '_', $string);
  }


/**
 * Break a word in a string if it is longer than a specified length ($len)
 *
 * @param string The string to be broken up
 * @param int The maximum length allowed
 * @param string The character to use at the end of the broken line
*/
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

/**
 * Return all HTTP GET variables, except those passed as a parameter
 *
 * The return is a urlencoded string
 *
 * @param mixed either a single or array of parameter names to be excluded from output
*/
  function zen_get_all_get_params($exclude_array = array(), $search_engine_safe = true) {
    if (!is_array($exclude_array)) $exclude_array = array();
    $exclude_array = array_merge($exclude_array, array(zen_session_name(), 'main_page', 'error', 'x', 'y'));
    $get_url = '';
    if (is_array($_GET) && (sizeof($_GET) > 0)) {
      reset($_GET);
      while (list($key, $value) = each($_GET)) {
        if (!in_array($key, $exclude_array)) {
          if (!is_array($value)) {
            if (strlen($value) > 0) {
              $get_url .= zen_sanitize_string($key) . '=' . rawurlencode(stripslashes($value)) . '&';
            }
          } else {
            foreach(array_filter($value) as $arr){
              $get_url .= zen_sanitize_string($key) . '[]=' . rawurlencode(stripslashes($arr)) . '&';
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
    $exclude_array = array_merge($exclude_array, array(zen_session_name(), 'error', 'x', 'y'));
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

////
// Returns the clients browser
  function zen_browser_detect($component) {
    global $HTTP_USER_AGENT;

    return stristr($HTTP_USER_AGENT, $component);
  }


////
// Wrapper function for round()
  function zen_round($value, $precision) {
    $value =  round($value *pow(10,$precision),0);
    $value = $value/pow(10,$precision);
    return $value;
  }


////
// default filler is a 0 or pass filler to be used
  function zen_row_number_format($number, $filler='0') {
    if ( ($number < 10) && (substr($number, 0, 1) != '0') ) $number = $filler . $number;

    return $number;
  }


// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
  function zen_date_long($raw_date) {
    if ( ($raw_date == '0001-01-01 00:00:00') || ($raw_date == '') ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    return strftime(DATE_FORMAT_LONG, mktime($hour,$minute,$second,$month,$day,$year));
  }


////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function zen_date_short($raw_date) {
    if ( ($raw_date == '0001-01-01 00:00:00') || empty($raw_date) ) return false;

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

////
// Parse search string into indivual objects
  function zen_parse_search_string($search_str = '', &$objects) {
    $search_str = trim(strtolower($search_str));

// Break up $search_str on whitespace; quoted string will be reconstructed later
    $pieces = preg_split('/[[:space:]]+/', $search_str);
    $objects = array();
    $tmpstring = '';
    $flag = '';

    for ($k=0; $k<count($pieces); $k++) {
      while (substr($pieces[$k], 0, 1) == '(') {
        $objects[] = '(';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 1);
        } else {
          $pieces[$k] = '';
        }
      }

      $post_objects = array();

      while (substr($pieces[$k], -1) == ')')  {
        $post_objects[] = ')';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 0, -1);
        } else {
          $pieces[$k] = '';
        }
      }

// Check individual words

      if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
        $objects[] = trim($pieces[$k]);

        for ($j=0; $j<count($post_objects); $j++) {
          $objects[] = $post_objects[$j];
        }
      } else {
/* This means that the $piece is either the beginning or the end of a string.
   So, we'll slurp up the $pieces and stick them together until we get to the
   end of the string or run out of pieces.
*/

// Add this word to the $tmpstring, starting the $tmpstring
        $tmpstring = trim(preg_replace('/"/', ' ', $pieces[$k]));

// Check for one possible exception to the rule. That there is a single quoted word.
        if (substr($pieces[$k], -1 ) == '"') {
// Turn the flag off for future iterations
          $flag = 'off';

          $objects[] = trim($pieces[$k]);

          for ($j=0; $j<count($post_objects); $j++) {
            $objects[] = $post_objects[$j];
          }

          unset($tmpstring);

// Stop looking for the end of the string and move onto the next word.
          continue;
        }

// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
        $flag = 'on';

// Move on to the next word
        $k++;

// Keep reading until the end of the string as long as the $flag is on

        while ( ($flag == 'on') && ($k < count($pieces)) ) {
          while (substr($pieces[$k], -1) == ')') {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
              $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
              $pieces[$k] = '';
            }
          }

// If the word doesn't end in double quotes, append it to the $tmpstring.
          if (substr($pieces[$k], -1) != '"') {
// Tack this word onto the current string entity
            $tmpstring .= ' ' . $pieces[$k];

// Move on to the next word
            $k++;
            continue;
          } else {
/* If the $piece ends in double quotes, strip the double quotes, tack the
   $piece onto the tail of the string, push the $tmpstring onto the $haves,
   kill the $tmpstring, turn the $flag "off", and return.
*/
            $tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));

// Push the $tmpstring onto the array of stuff to search for
            $objects[] = trim($tmpstring);

            for ($j=0; $j<count($post_objects); $j++) {
              $objects[] = $post_objects[$j];
            }

            unset($tmpstring);

// Turn off the flag to exit the loop
            $flag = 'off';
          }
        }
      }
    }

// add default logical operators if needed
    $temp = array();
    for($i=0; $i<(count($objects)-1); $i++) {
      $temp[] = $objects[$i];
      if ( ($objects[$i] != 'and') &&
           ($objects[$i] != 'or') &&
           ($objects[$i] != '(') &&
           ($objects[$i+1] != 'and') &&
           ($objects[$i+1] != 'or') &&
           ($objects[$i+1] != ')') ) {
        $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
      }
    }
    $temp[] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for($i=0; $i<count($objects); $i++) {
      if ($objects[$i] == '(') $balance --;
      if ($objects[$i] == ')') $balance ++;
      if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
        $operator_count ++;
      } elseif ( (is_string($objects[$i]) && $objects[$i] == '0') || ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
        $keyword_count ++;
      }
    }

    if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
      return true;
    } else {
      return false;
    }
  }


////
// Check date
  function zen_checkdate($date_to_check, $format_string, &$date_array) {
    $separator_idx = -1;

    $separators = array('-', ' ', '/', '.');
    $month_abbr = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
    $no_of_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    $format_string = strtolower($format_string);

    if (strlen($date_to_check) != strlen($format_string)) {
      return false;
    }

    $size = sizeof($separators);
    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($date_to_check, $separators[$i]);
      if ($pos_separator != false) {
        $date_separator_idx = $i;
        break;
      }
    }

    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($format_string, $separators[$i]);
      if ($pos_separator != false) {
        $format_separator_idx = $i;
        break;
      }
    }

    if ($date_separator_idx != $format_separator_idx) {
      return false;
    }

    if ($date_separator_idx != -1) {
      $format_string_array = explode( $separators[$date_separator_idx], $format_string );
      if (sizeof($format_string_array) != 3) {
        return false;
      }

      $date_to_check_array = explode( $separators[$date_separator_idx], $date_to_check );
      if (sizeof($date_to_check_array) != 3) {
        return false;
      }

      $size = sizeof($format_string_array);
      for ($i=0; $i<$size; $i++) {
        if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];
        if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];
        if ( ($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa') ) $year = $date_to_check_array[$i];
      }
    } else {
      if (strlen($format_string) == 8 || strlen($format_string) == 9) {
        $pos_month = strpos($format_string, 'mmm');
        if ($pos_month != false) {
          $month = substr( $date_to_check, $pos_month, 3 );
          $size = sizeof($month_abbr);
          for ($i=0; $i<$size; $i++) {
            if ($month == $month_abbr[$i]) {
              $month = $i;
              break;
            }
          }
        } else {
          $month = substr($date_to_check, strpos($format_string, 'mm'), 2);
        }
      } else {
        return false;
      }

      $day = substr($date_to_check, strpos($format_string, 'dd'), 2);
      $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);
    }

    if (strlen($year) != 4) {
      return false;
    }

    if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {
      return false;
    }

    if ($month > 12 || $month < 1) {
      return false;
    }

    if ($day < 1) {
      return false;
    }

    if (zen_is_leap_year($year)) {
      $no_of_days[1] = 29;
    }

    if ($day > $no_of_days[$month - 1]) {
      return false;
    }

    $date_array = array($year, $month, $day);

    return true;
  }


////
// Check if year is a leap year
  function zen_is_leap_year($year) {
    if ($year % 100 == 0) {
      if ($year % 400 == 0) return true;
    } else {
      if (($year % 4) == 0) return true;
    }

    return false;
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
// Return a product ID with attributes
/*
  function zen_get_uprid_OLD($prid, $params) {
    $uprid = $prid;
    if ( (is_array($params)) && (!strstr($prid, '{')) ) {
      while (list($option, $value) = each($params)) {
        $uprid = $uprid . '{' . $option . '}' . $value;
      }
    }

    return $uprid;
  }
*/


////
// Return a product ID with attributes
  function zen_get_uprid($prid, $params) {
//print_r($params);
    $uprid = $prid;
    if ( (is_array($params)) && (!strstr($prid, ':')) ) {
      while (list($option, $value) = each($params)) {
        if (is_array($value)) {
          while (list($opt, $val) = each($value)) {
            $uprid = $uprid . '{' . $option . '}' . trim($opt);
          }
        } else {
        //CLR 030714 Add processing around $value. This is needed for text attributes.
            $uprid = $uprid . '{' . $option . '}' . trim($value);
        }
      }      //CLR 030228 Add else stmt to process product ids passed in by other routines.
      $md_uprid = '';

      $md_uprid = md5($uprid);
      return $prid . ':' . $md_uprid;
    } else {
      return $prid;
    }
  }


////
// Return a product ID from a product ID with attributes
  function zen_get_prid($uprid) {
    $pieces = explode(':', $uprid);

    return $pieces[0];
  }



////
// Get the number of times a word/character is present in a string
  function zen_word_count($string, $needle) {
    $temp_array = preg_split('/'.$needle.'/', $string);

    return sizeof($temp_array);
  }


////
  function zen_count_modules($modules = '') {
    $count = 0;

    if (empty($modules)) return $count;

    $modules_array = preg_split('/;/', $modules);

    for ($i=0, $n=sizeof($modules_array); $i<$n; $i++) {
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
      while (list($key, $value) = each($array)) {
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


////
// Checks to see if the currency code exists as a currency
// TABLES: currencies
  function zen_currency_exists($code, $getFirstDefault = false) {
    global $db;
    $code = zen_db_prepare_input($code);

    $currency_code = "select code
                      from " . TABLE_CURRENCIES . "
                      where code = '" . zen_db_input($code) . "' LIMIT 1";

    $currency_first = "select code
                      from " . TABLE_CURRENCIES . "
                      order by value ASC LIMIT 1";

    $currency = $db->Execute(($getFirstDefault == false) ? $currency_code : $currency_first);

    if ($currency->RecordCount()) {
      return strtoupper($currency->fields['code']);
    } else {
      return false;
    }
  }

////
  function zen_string_to_int($string) {
    return (int)$string;
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

////
  function zen_get_top_level_domain($url) {
    if (strpos($url, '://')) {
      $url = parse_url($url);
      $url = $url['host'];
    }
//echo $url;

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

////
  function zen_setcookie($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = 0) {
    setcookie($name, $value, $expire, $path, $domain, $secure);
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
    $ip = preg_replace('~[^a-fA-F0-9.:%/,]~', '', $ip);

    /**
     *  if it's still blank, set to a single dot
     */
    if (trim($ip) == '') $ip = '.';

    return $ip;
  }

  function zen_convert_linefeeds($from, $to, $string) {
    return str_replace($from, $to, $string);
  }


////
  function is_product_valid($product_id, $coupon_id) {
    global $db;
    $coupons_query = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
                      WHERE coupon_id = '" . (int)$coupon_id . "'
                      ORDER BY coupon_restrict ASC";

    $coupons = $db->Execute($coupons_query);

    $product_query = "SELECT products_model FROM " . TABLE_PRODUCTS . "
                      WHERE products_id = '" . (int)$product_id . "'";

    $product = $db->Execute($product_query);

    if (preg_match('/^GIFT/', $product->fields['products_model'])) {
      return false;
    }

// modified to manage restrictions better - leave commented for now
    if ($coupons->RecordCount() == 0) return true;
    if ($coupons->RecordCount() == 1) {
// If product is restricted(deny) and is same as tested prodcut deny
      if (($coupons->fields['product_id'] != 0) && $coupons->fields['product_id'] == (int)$product_id && $coupons->fields['coupon_restrict']=='Y') return false;
// If product is not restricted(allow) and is not same as tested prodcut deny
      if (($coupons->fields['product_id'] != 0) && $coupons->fields['product_id'] != (int)$product_id && $coupons->fields['coupon_restrict']=='N') return false;
// if category is restricted(deny) and product in category deny
      if (($coupons->fields['category_id'] !=0) && (zen_product_in_category($product_id, $coupons->fields['category_id'])) && ($coupons->fields['coupon_restrict']=='Y')) return false;
// if category is not restricted(allow) and product not in category deny
      if (($coupons->fields['category_id'] !=0) && (!zen_product_in_category($product_id, $coupons->fields['category_id'])) && ($coupons->fields['coupon_restrict']=='N')) return false;
      return true;
    }
    $allow_for_category = validate_for_category($product_id, $coupon_id);
    $allow_for_product = validate_for_product($product_id, $coupon_id);
//    echo '#'.$product_id . '#' . $allow_for_category;
//    echo '#'.$product_id . '#' . $allow_for_product;
    if ($allow_for_category == 'none') {
      if ($allow_for_product === 'none') return true;
      if ($allow_for_product === true) return true;
      if ($allow_for_product === false) return false;
    }
    if ($allow_for_category === true) {
      if ($allow_for_product === 'none') return true;
      if ($allow_for_product === true) return true;
      if ($allow_for_product === false) return false;
    }
    if ($allow_for_category === false) {
      if ($allow_for_product === 'none') return false;
      if ($allow_for_product === true) return true;
      if ($allow_for_product === false) return false;
    }
    return false; //should never get here
  }
  function validate_for_category($product_id, $coupon_id) {
    global $db;
    $retVal = 'none';
    $productCatPath = zen_get_product_path($product_id);
    $catPathArray = array_reverse(explode('_', $productCatPath));
    $sql = "SELECT count(*) AS total
            FROM " . TABLE_COUPON_RESTRICT . "
            WHERE category_id = -1
            AND coupon_restrict = 'Y'
            AND coupon_id = " . (int)$coupon_id . " LIMIT 1";
    $checkQuery = $db->execute($sql);
    foreach ($catPathArray as $catPath) {
      $sql = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
              WHERE category_id = " . (int)$catPath . "
              AND coupon_id = " . (int)$coupon_id;
      $result = $db->execute($sql);
      if ($result->recordCount() > 0 && $result->fields['coupon_restrict'] == 'N') return true;
      if ($result->recordCount() > 0 && $result->fields['coupon_restrict'] == 'Y') return false;
    }
    if ($checkQuery->fields['total'] > 0) {
      return false;
    } else {
      return 'none';
    }
  }
  function validate_for_product($product_id, $coupon_id) {
    global $db;
    $sql = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
            WHERE product_id = " . (int)$product_id . "
            AND coupon_id = " . (int)$coupon_id . " LIMIT 1";
    $result = $db->execute($sql);
    if ($result->recordCount() > 0) {
      if ($result->fields['coupon_restrict'] == 'N') return true;
      if ($result->fields['coupon_restrict'] == 'Y') return false;
    } else {
      return 'none';
    }
  }

////
// is coupon valid for specials and sales
  function is_coupon_valid_for_sales($product_id, $coupon_id) {
    global $db;
    $sql = "SELECT coupon_id, coupon_is_valid_for_sales
            FROM " . TABLE_COUPONS . "
            WHERE coupon_id = '" . (int)$coupon_id . "'";

    $result = $db->Execute($sql);

    // check whether coupon has been flagged for not valid with sales
    if ($result->fields['coupon_is_valid_for_sales']) {
      return true;
    }

    // check for any special on $product_id
    $chk_product_on_sale = zen_get_products_special_price($product_id, true);
    if (!$chk_product_on_sale) {
      // check for any sale on $product_id
      $chk_product_on_sale = zen_get_products_special_price($product_id, false);
    }
    if ($chk_product_on_sale) {
      return false;
    }
    return true; // is on special or sale
  }

////
  function zen_db_input($string) {
    global $db;
    return $db->prepareInput($string);
  }

////
  function zen_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(zen_sanitize_string(stripslashes($string)));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = zen_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

////
  function zen_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    global $db;
    reset($data);
    if (strtolower($action) == 'insert') {
      $query = 'INSERT INTO ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') VALUES (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'NULL':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . zen_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif (strtolower($action) == 'update') {
      $query = 'UPDATE ' . $table . ' SET ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'NULL':
            $query .= $columns . ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . zen_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' WHERE ' . $parameters;
    }

    return $db->Execute($query);
  }

////
  function zen_db_output($string) {
    return htmlspecialchars($string);
  }


// function to return field type
// uses $tbl = table name, $fld = field name

  function zen_field_type($tbl, $fld) {
    global $db;
    $rs = $db->MetaColumns($tbl);
    $type = $rs[strtoupper($fld)]->type;
    return $type;
  }

// function to return field length
// uses $tbl = table name, $fld = field name
  function zen_field_length($tbl, $fld) {
    global $db;
    $rs = $db->MetaColumns($tbl);
    $length = $rs[strtoupper($fld)]->max_length;
    return $length;
  }

////
// return the size and maxlength settings in the form size="blah" maxlength="blah" based on maximum size being 70
// uses $tbl = table name, $fld = field name
// example: zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name')
  function zen_set_field_length($tbl, $fld, $max=70) {
    $field_length= zen_field_length($tbl, $fld);
    switch (true) {
      case ($field_length > $max):
        $length= 'size = "' . ($max+1) . '" maxlength= "' . $field_length . '"';
        break;
      default:
        $length= 'size = "' . ($field_length+1) . '" maxlength = "' . $field_length . '"';
        break;
    }
    return $length;
  }


////
// Set back button
  function zen_back_link($link_only = false) {
    if (sizeof($_SESSION['navigation']->path)-2 >= 0) {
      $back = sizeof($_SESSION['navigation']->path)-2;
      $link = zen_href_link($_SESSION['navigation']->path[$back]['page'], zen_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']);
    } else {
      if (isset($_SERVER['HTTP_REFERER']) && preg_match("~^".HTTP_SERVER."~i", $_SERVER['HTTP_REFERER']) ) {
      //if (isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], str_replace(array('http://', 'https://'), '', HTTP_SERVER) ) ) {
        $link= $_SERVER['HTTP_REFERER'];
      } else {
        $link = zen_href_link(FILENAME_DEFAULT);
      }
      $_SESSION['navigation'] = new navigationHistory;
    }

    if ($link_only == true) {
      return $link;
    } else {
      return '<a class="btn-backlink" href="' . $link . '">';
    }
  }


////
// Return a random row from a database query
  function zen_random_select($query) {
    global $db;
    $random_product = '';
    $random_query = $db->Execute($query);
    $num_rows = $random_query->RecordCount();
    if ($num_rows > 1) {
      $random_row = zen_rand(0, ($num_rows - 1));
      $random_query->Move($random_row);
    }
    return $random_query;
  }


////
// Truncate a string
  function zen_trunc_string($str = "", $len = 150, $more = 'true') {
    if ($str == "") return $str;
    if (is_array($str)) return $str;
    $str = trim($str);
    $len = (int)$len;
    if ($len == 0) return '';
    // if it's les than the size given, then return it
    if (strlen($str) <= $len) return $str;
    // else get that size of text
    $str = substr($str, 0, $len);
    // backtrack to the end of a word
    if ($str != "") {
      // check to see if there are any spaces left
      if (!substr_count($str , " ")) {
        if ($more == 'true') $str .= "...";
        return $str;
      }
      // backtrack
      while(strlen($str) && ($str[strlen($str)-1] != " ")) {
        $str = substr($str, 0, -1);
      }
      $str = substr($str, 0, -1);
      if ($more == 'true') $str .= "...";
      if ($more != 'true' and $more != 'false') $str .= $more;
    }
    return $str;
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

// show case only superceeds all other settings
    if (STORE_STATUS != '0') {
      return '<a class="btn-contactus" href="' . zen_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' .  TEXT_SHOWCASE_ONLY . '</a>';
    }

// 0 = normal shopping
// 1 = Login to shop
// 2 = Can browse but no prices
    // verify display of prices
      switch (true) {
        case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
        // customer must be logged in to browse
        $login_for_price = '<a class="btn-login" href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' .  TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE . '</a>';
        return $login_for_price;
        break;
        case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
        if (TEXT_LOGIN_FOR_PRICE_PRICE == '') {
          // show room only
          return TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE;
        } else {
          // customer may browse but no prices
          $login_for_price = '<a class="btn-login" href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' .  TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE . '</a>';
        }
        return $login_for_price;
        break;
        // show room only
        case (CUSTOMERS_APPROVAL == '3'):
          $login_for_price = TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM;
          return $login_for_price;
        break;
        case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customer_id'] == ''):
        // customer must be logged in to browse
        $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
        return $login_for_price;
        break;
        case ((CUSTOMERS_APPROVAL_AUTHORIZATION == '3') and $_SESSION['customer_id'] == ''):
        // customer must be logged in and approved to add to cart
        $login_for_price = '<a class="btn-login" href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' .  TEXT_LOGIN_TO_SHOP_BUTTON_REPLACE . '</a>';
        return $login_for_price;
        break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and $_SESSION['customers_authorization'] > '0'):
        // customer must be logged in to browse
        $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
        return $login_for_price;
        break;
        case ((int)$_SESSION['customers_authorization'] >= 2):
        // customer is logged in and was changed to must be approved to buy
        $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
        return $login_for_price;
        break;
        default:
        // proceed normally
        break;
      }

    $button_check = $db->Execute("select product_is_call, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    switch (true) {
// cannot be added to the cart
    case (zen_get_products_allow_add_to_cart($product_id) == 'N'):
      return $additional_link;
      break;
    case ($button_check->fields['product_is_call'] == '1'):
      $return_button = '<a class="btn-callforprice" href="' . zen_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' . TEXT_CALL_FOR_PRICE . '</a>';
      $return_button = '';
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


////
// find module directory
// include template specific immediate /modules files
// new_products, products_new_listing, featured_products, featured_products_listing, product_listing, specials_index, upcoming,
// products_all_listing, products_discount_prices, also_purchased_products
  function zen_get_module_directory($check_file, $dir_only = 'false') {
    global $template_dir;

    $zv_filename = $check_file;
    if (!strstr($zv_filename, '.php')) $zv_filename .= '.php';

    if (file_exists(DIR_WS_MODULES . $template_dir . '/' . $zv_filename)) {
      $template_dir_select = $template_dir . '/';
    } else if (file_exists(DIR_WS_MODULES . 'shared' . '/' . $zv_filename)) {
      $template_dir_select = 'shared/'; 
    } else {
      $template_dir_select = '';
    }

    if ($dir_only == 'true') {
      return $template_dir_select;
    } else {
      return $template_dir_select . $zv_filename;
    }
  }


////
// find template or default file
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

// check to see if database stored GET terms are in the URL as $_GET parameters
  function zen_check_url_get_terms() {
    global $db;
    $zp_sql = "select * from " . TABLE_GET_TERMS_TO_FILTER;
    $zp_filter_terms = $db->Execute($zp_sql);
    $zp_result = false;
    while (!$zp_filter_terms->EOF) {
      if (isset($_GET[$zp_filter_terms->fields['get_term_name']]) && zen_not_null($_GET[$zp_filter_terms->fields['get_term_name']])) $zp_result = true;
      $zp_filter_terms->MoveNext();
    }
    return $zp_result;
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
  function zen_truncate_paragraph($paragraph, $size = 100, $word = ' ') {
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
 * return an array with zones defined for the specified country
 */
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
    $countries = $db->Execute("select distinct zone_country_id
                               from " . TABLE_ZONES . "
                               order by zone_country_id");
    $num_country = 1;
    $output_string = '';
    while (!$countries->EOF) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      }

      $states = $db->Execute("select zone_name, zone_id
                              from " . TABLE_ZONES . "
                              where zone_country_id = '" . $countries->fields['zone_country_id'] . "'
                              order by zone_name");
      $num_state = 1;
      while (!$states->EOF) {
        if ($num_state == '1') $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
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



////
// compute the days between two dates
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
//       if ($make_unwritable) set_unwritable($filepath);
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
  function zen_update_music_artist_clicked($artistId, $languageId)
  {
    global $db;
    $sql = "UPDATE " . TABLE_RECORD_ARTISTS_INFO . " set url_clicked = url_clicked +1, date_last_click = NOW() WHERE artists_id = :artistId: AND languages_id = :languageId:";
    $sql = $db->bindVars($sql, ':artistId:', $artistId, 'integer');
    $sql = $db->bindVars($sql, ':languageId:', $languageId, 'integer');
    $db->execute($sql);
  }
  function zen_update_record_company_clicked($recordCompanyId, $languageId)
  {
    global $db;
    $sql = "UPDATE " . TABLE_RECORD_COMPANY_INFO . " set url_clicked = url_clicked +1, date_last_click = NOW() WHERE record_company_id = :rcId: AND languages_id = :languageId:";
    $sql = $db->bindVars($sql, ':rcId:', $recordCompanyId, 'integer');
    $sql = $db->bindVars($sql, ':languageId:', $languageId, 'integer');
    $db->execute($sql);
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
   * @param $mixed_value
   * @param int $flags
   * @param string $encoding
   * @param bool $double_encode
   * @return array|string
   */
  function htmlentities_recurse($mixed_value, $flags = ENT_QUOTES, $encoding = 'utf-8', $double_encode = true) {
      $result = array();
      if (!is_array ($mixed_value)) {
          return htmlentities ((string)$mixed_value, $flags, $encoding, $double_encode);
      }
      if (is_array($mixed_value)) {
          $result = array ();
          foreach ($mixed_value as $key => $value) {
              $result[$key] = htmlentities_recurse ($value, $flags, $encoding, $double_encode);
          }
      }
      return $result;
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

