<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.5.8 $
 */

function zen_get_zcversion()
{
    return PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
}

/**
 * Set timeout for the current script.
 * @param int $limit seconds
 */
function zen_set_time_limit($limit) {
    @set_time_limit($limit);
}

/**
 * @param string $ip
 * @return boolean
 */
function zen_is_whitelisted_admin_ip($ip = null)
{
    if (empty($ip)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return strpos(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $ip) !== false;
}

/**
 * Returns a string with conversions for security.
 * @param string $string The string to be parsed
 * @param string|bool $translate contains a string to be translated, otherwise just quote is translated
 * @param bool $protected Do we run htmlspecialchars over the string
 * @return string
 */
  function zen_output_string($string, $translate = false, $protected = false): string
  {
    if ($protected === true) {
      $double_encode = (IS_ADMIN_FLAG ? FALSE : TRUE);
      return htmlspecialchars($string, ENT_COMPAT, CHARSET, $double_encode);
    }

    if ($translate === false) {
      return zen_parse_input_field_data($string, array('"' => '&quot;'));
    }

    return zen_parse_input_field_data($string, $translate);
  }

/**
 * Returns a string with conversions for security.
 *
 * Simply calls the zen_output_string function
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


////
// Wrapper function for round()
  function zen_round($value, $precision) {
    $value =  round($value *pow(10,$precision),0);
    $value = $value/pow(10,$precision);
    return $value;
  }


/**
 * Checks whether a string/array is null/blank/empty or uppercase string 'NULL'
 * Differs from empty() in that it doesn't test for boolean false or '0' string/int
 * @param string|array|Countable $value
 * @return bool
 */
  function zen_not_null($value) {
    if (null === $value) {
        return false;
    }
    if (is_array($value)) {
      return count($value) > 0;
    }
    if (is_a($value, \Countable::class)) {
      return count($value) > 0;
    }
    return trim($value) !== '' && $value !== 'NULL';
  }

////
  function zen_string_to_int($string) {
    return (int)$string;
  }



/**
 * Get a shortened filename to fit within the db field constraints
 *
 * @param string $filename (could also be a URL)
 * @param string $table_name
 * @param string $field_name
 * @param string $extension String to denote the extension. The right-most "." is used as a fallback.
 * @return string
 */
  function zen_limit_image_filename($filename, $table_name, $field_name, $extension = '.') {
      if ($filename === 'none') return $filename;

      $max_length = zen_field_length($table_name, $field_name);
      $filename_length = function_exists('mb_strlen') ? mb_strlen($filename) : strlen($filename);

      if ($filename_length <= $max_length) return $filename;
      $divider_position = function_exists('mb_strrpos') ? mb_strrpos($filename, $extension) : strrpos($filename, $extension);
      $base = substr($filename, 0, $divider_position);
      $original_suffix = substr($filename, $divider_position);
      $suffix_length = function_exists('mb_strlen') ? mb_strlen($original_suffix) : strlen($original_suffix);
      $chop_length = $filename_length - $max_length;
      $shorter_length = $filename_length - $suffix_length - $chop_length;
      $shorter_base = substr($base, 0, $shorter_length);

      return $shorter_base . $original_suffix;
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
function issetorArray(array $array, $key, $default = null)
{
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Recursively apply htmlentities on the passed string
 * Useful for preparing json output and ajax responses
 *
 * @param string|array $mixed_value
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

function utf8_encode_recurse($mixed_value)
{
    if (strtolower(CHARSET) == 'utf-8') {
        return $mixed_value;
    } elseif (!is_array($mixed_value)) {
        return utf8_encode((string)$mixed_value);
    } else {
        $result = array();
        foreach ($mixed_value as $key => $value) {
            $result[$key] = utf8_encode($value);
        }
        return $result;
    }
}

/**
 * Return all HTTP GET variables, except those passed as a parameter
 *
 * The return is a urlencoded string
 *
 * @param mixed $exclude_array either a single or array of parameter names to be excluded from output
 * @return string url_encoded string of GET params
 */
function zen_get_all_get_params($exclude_array = array()) {
    if (!is_array($exclude_array)) $exclude_array = array();
    $exclude_array = array_merge($exclude_array, array('main_page', 'error', 'x', 'y', 'cmd'));
    if (function_exists('zen_session_name')) {
        $exclude_array[] = zen_session_name();
    }
    $get_url = '';
    if (is_array($_GET) && (count($_GET) > 0)) {
        foreach($_GET as $key => $value) {
            if (!in_array($key, $exclude_array)) {
                if (!is_array($value)) {
                    if (strlen($value) > 0) {
                        $get_url .= zen_sanitize_string($key) . '=' . rawurlencode(stripslashes($value)) . '&';
                    }
                } else {
                    if (IS_ADMIN_FLAG) continue; // admin (and maybe catalog?) doesn't support passing arrays by GET, so skipping any arrays here
                    foreach (array_filter($value) as $arr){
                        if (is_array($arr)) continue;
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
 * @param array $exclude_array GET keys to exclude from generated output
 * @param boolean $hidden generate hidden fields instead of regular input fields
 * @param string $parameters optional 'class="foo"' markup to include in non-hidden input fields
 * @return string HTML string of input fields
 */
function zen_post_all_get_params($exclude_array = array(), $hidden = true, $parameters = '') {
    if (!is_array($exclude_array)) $exclude_array = array((string)$exclude_array);
    $exclude_array = array_merge($exclude_array, array('error', 'x', 'y'));
    if (function_exists('zen_session_name')) {
        $exclude_array[] = zen_session_name();
    }
    $fields = '';
    if (is_array($_GET) && (count($_GET) > 0)) {
        foreach($_GET as $key => $value) {
            if (!in_array($key, $exclude_array)) {
                if (!is_array($value)) {
                    if (strlen($value) > 0) {
                        if ($hidden) {
                            $fields .= zen_draw_hidden_field($key, $value);
                        } else {
                            $fields .= zen_draw_input_field($key, $value, $parameters);
                        }
                    }
                } else {
                    foreach(array_filter($value) as $arr){
                        if (is_array($arr)) continue;
                        if ($hidden) {
                            $fields .= zen_draw_hidden_field($key . '[]', $arr);
                        } else {
                            $fields .= zen_draw_input_field($key . '[]', $arr, $parameters);
                        }
                    }
                }
            }
        }
    }
    return $fields;
}
