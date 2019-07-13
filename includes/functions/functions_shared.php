<?php

/**
 * Returns a string with conversions for security.
 * @param string The string to be parsed
 * @param string contains a string to be translated, otherwise just quote is translated
 * @param boolean Do we run htmlspecialchars over the string
*/
  function zen_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      $double_encode = (IS_ADMIN_FLAG ? FALSE : TRUE); 
      return htmlspecialchars($string, ENT_COMPAT, CHARSET, $double_encode);
    } else {
      if ($translate === false) {
        return zen_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return zen_parse_input_field_data($string, $translate);
      }
    }
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
 * Return a product ID from a product ID with attributes
 * Alternate: simply (int) the product id
 * @param string $uprid   ie: '11:abcdef12345'
 * @return mixed
 */
  function zen_get_prid($uprid) {
    $pieces = explode(':', $uprid);
    return (int)$pieces[0];
  }

////
  function zen_not_null($value) {
    if (null === $value) {
        return false;
    }
    if (is_array($value)) {
      return count($value) > 0;
    }
    if (is_a($value, 'queryFactoryResult')) {
      return count($value->result) > 0;
    }
    return trim($value) !== '' && $value != 'NULL';
  }

////
  function zen_string_to_int($string) {
    return (int)$string;
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


/**
 * find template or default file
 */
  function zen_get_file_directory($check_directory, $check_file, $dir_only = 'false') {
    global $template_dir;

    $zv_filename = $check_file;
    if (!strstr($zv_filename, '.php')) $zv_filename .= '.php';

    if (file_exists($check_directory . $template_dir . '/' . $zv_filename)) {
      $zv_directory = $check_directory . $template_dir . '/';
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
