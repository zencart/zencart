<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Sep 17 Modified in v2.1.0-beta1 $
 */


/**
 * Returns a string with conversions for security.
 * @param string $string The string to be parsed
 * @param string|bool $translate contains a string to be translated, otherwise just quote is translated
 * @param bool $protected Do we run htmlspecialchars over the string
 * @return string
 */
function zen_output_string($string, $translate = false, $protected = false): string
{
    if (is_null($string) === true) {
        return '';
    }

    if ($protected === true) {
        $double_encode = (IS_ADMIN_FLAG ? FALSE : TRUE);
        return htmlspecialchars($string, ENT_COMPAT, CHARSET, $double_encode);
    }

    if ($translate === false) {
        return strtr($string, ['"' => '&quot;']);
    }

    return strtr($string, $translate);
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
function zen_output_string_protected($string)
{
    return zen_output_string($string, false, true);
}


/**
 * Returns a string with conversions for security.
 *
 * @param string The string to be parsed
 */

function zen_sanitize_string($string)
{
    $string = preg_replace('/ +/', ' ', $string);
    return preg_replace("/[<>]/", '_', $string);
}


/**
 * Checks whether a string/array is null/blank/empty or uppercase string 'NULL'
 * Differs from empty() in that it doesn't test for boolean false or '0' string/int
 * @param string|array|Countable $value
 * @return bool
 */
function zen_not_null($value)
{
    if (null === $value) {
        return false;
    }
    if (is_countable($value)) {
        return count($value) > 0;
    }
    return trim($value) !== '' && $value !== 'NULL';
}

/**
 * Break a word in a string if it is longer than a specified length ($len)
 *
 * @param string The string to be broken up
 * @param int The maximum length allowed
 * @param string The character to use at the end of the broken line
 * @return string
 */
function zen_break_string($string, $len, $break_char = '-')
{
    if (is_null($string) === true) {
        return '';
    }
    $l = 0;
    $output = '';
    for ($i = 0, $n = strlen($string); $i < $n; $i++) {
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
 * Truncate a string at length specified, optionally adding a "more" suffix
 * @param string $str
 * @param int $len
 * @param string $more
 * @return array|false|mixed|string
 */
function zen_trunc_string($str = "", $len = 150, $more = 'true')
{
    if (is_null($str) === true) {
        return '';
    }
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
        if (!substr_count($str, " ")) {
            if ($more == 'true') $str .= "...";
            return $str;
        }
        // backtrack
        while (strlen($str) && ($str[strlen($str) - 1] != " ")) {
            $str = substr($str, 0, -1);
        }
        $str = substr($str, 0, -1);
        if ($more == 'true') $str .= "...";
        if ($more != 'true' and $more != 'false') $str .= $more;
    }
    return $str;
}

/**
 * Truncate a paragraph after $size words
 * @param string $paragraph
 * @param int $size
 * @return string
 */
function zen_truncate_paragraph($paragraph, $size = 100)
{
    $zv_paragraph = "";
    $word = explode(" ", $paragraph);
    $zv_total = count($word);
    if ($zv_total > $size) {
        for ($x = 0; $x < $size; $x++) {
            $zv_paragraph = $zv_paragraph . $word[$x] . " ";
        }
        $zv_paragraph = trim($zv_paragraph);
    } else {
        $zv_paragraph = trim($paragraph);
    }
    return $zv_paragraph;
}


/**
 * Get the number of times a word/character is present in a string
 * @param string $string
 * @param string $needle
 * @return int
 */
function zen_word_count(string $string, string $needle)
{
    $temp_array = preg_split('/' . $needle . '/', $string);

    return count($temp_array);
}


/**
 * Collapse an array into a string
 * A sort of pseudo-serialize function
 * Used mainly by the Navigation class to store historical info
 * @param array $array
 * @param array|string $exclude
 * @param string $equals
 * @param string $separator
 * @return string
 */
function zen_array_to_string($array, $exclude = '', $equals = '=', $separator = '&')
{
    if (!is_array($exclude)) $exclude = [];
    if (!is_array($array)) $array = [];

    $get_string = '';
    unset($array['x'], $array['y']);
    if (count($array) > 0) {
        foreach ($array as $key => $value) {
            if (!in_array($key, $exclude)) {
                $get_string .= $key . $equals . $value . $separator;
            }
        }
        $remove_chars = strlen($separator);
        $get_string = substr($get_string, 0, -$remove_chars);
    }

    return $get_string;
}



/**
 * convert supplied string to UTF-8, dropping any symbols which cannot be translated easily
 * useful for submitting cleaned-up data to payment gateways or other external services, esp if the data was copy+pasted from windows docs via windows browser to store in database
 *
 * @param string $string
 */
function charsetConvertWinToUtf8($string)
{
    if (function_exists('iconv')) $string = iconv("Windows-1252", "ISO-8859-1//IGNORE", $string);
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
    return $string;
}

/**
 * Convert supplied string to/from entities between charsets, to sanitize data from inputs, especially APIs and gateways
 * @param $string
 * @return string
 */
function charsetClean($string)
{
    if (preg_replace('/[^a-z0-9]/', '', strtolower(CHARSET)) == 'utf8') return $string;
    if (function_exists('iconv')) $string = iconv("Windows-1252", CHARSET . "//IGNORE", $string);
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
    $string = html_entity_decode($string, ENT_QUOTES, CHARSET);
    return $string;
}

/**
 * strip out accented characters to reasonable approximations of english equivalents
 */
function replace_accents($s)
{
    $skipPreg = (defined('OVERRIDE_REPLACE_ACCENTS_WITH_HTMLENTITIES') && OVERRIDE_REPLACE_ACCENTS_WITH_HTMLENTITIES == 'TRUE');
    $s = htmlentities($s, ENT_COMPAT, CHARSET);
    if (!$skipPreg) {
        $s = preg_replace('/&([a-zA-Z])(uml|acute|elig|grave|circ|tilde|cedil|ring|quest|slash|caron);/', '$1', $s);
    }
    $s = html_entity_decode($s);
    return $s;
}

/**
 * @param string $given_html
 * @param int $quote_style
 * @return string
 */
function zen_html_entity_decode($given_html, $quote_style = ENT_QUOTES)
{
    $trans_table = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style));
    $trans_table['&#39;'] = "'";
    return (strtr($given_html, $trans_table));
}

/**
 * Decode string encoded with htmlspecialchars()
 */
function zen_decode_specialchars($string)
{
    $string = str_replace('&gt;', '>', $string);
    $string = str_replace('&lt;', '<', $string);
    $string = str_replace('&#039;', "'", $string);
    $string = str_replace('&quot;', "\"", $string);
    $string = str_replace('&amp;', '&', $string);

    return $string;
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
function htmlentities_recurse($mixed_value, $flags = ENT_QUOTES, $encoding = 'utf-8', $double_encode = true)
{
    $result = array();
    if (!is_array($mixed_value)) {
        return htmlentities((string)$mixed_value, $flags, $encoding, $double_encode);
    }
    if (is_array($mixed_value)) {
        $result = array();
        foreach ($mixed_value as $key => $value) {
            $result[$key] = htmlentities_recurse($value, $flags, $encoding, $double_encode);
        }
    }
    return $result;
}

/**
 * @param mixed $mixed_value
 * @return array|false|string
 *
 * Deprecated after Zen Cart 1.5.8a
 */
function utf8_encode_recurse($mixed_value)
{
    trigger_error('Function utf8_encode_recurse is deprecated for Zen Cart versions after 1.5.8a.', E_USER_DEPRECATED);
    return $mixed_value;
}

/**
 * Remove common HTML from text for display as paragraph
 * @param string $clean_it
 * @param string|array $extraTags
 * @return string
 */
function zen_clean_html($clean_it, $extraTags = '')
{
    if (!is_array($extraTags)) $extraTags = [$extraTags];

    // remove any embedded javascript
    $clean_it = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $clean_it ?? '');

    $clean_it = preg_replace('/\r/', ' ', $clean_it);
    $clean_it = preg_replace('/\t/', ' ', $clean_it);
    $clean_it = preg_replace('/\n/', ' ', $clean_it);

    $clean_it = nl2br($clean_it);

    // update breaks with a space for text displays in all listings with descriptions
    $clean_it = preg_replace('~(<br ?/?>|</?p>)~', ' ', $clean_it);

    // temporary fix more for reviews than anything else
    $clean_it = str_replace('<span class="smallText">', ' ', $clean_it);
    $clean_it = str_replace('</span>', ' ', $clean_it);

    // clean general and specific tags:
    $taglist = ['strong', 'b', 'u', 'i', 'em'];
    $taglist = array_merge($taglist, (is_array($extraTags) ? $extraTags : [$extraTags]));
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
 * @param string $url
 * @return string
 */
function fixup_url($url)
{
    global $request_type;

    if (!preg_match('#^https?://#', $url)) {
        $url = '//' . $url;
    }
    return $url;
}


/**
 * Parse the data used in html tags to ensure the tags will not break.
 * Basically just an extension to the php strtr function
 * @param string The string to be parsed
 * @param string The needle to find
 * @return string
 * @deprecated alias to strtr()
 */
function zen_parse_input_field_data($data, $parse)
{
    trigger_error('Call to deprecated function zen_parse_input_field_data. Use strtr() instead', E_USER_DEPRECATED);
    return strtr(trim($data), $parse);
}

/** @deprecated  */
function zen_string_to_int($string) {
    trigger_error('Call to deprecated function zen_string_to_int. Use a closure instead', E_USER_DEPRECATED);
    return (int)$string;
}

/**
 * Converts a numeric string to int or float depending on whether it is a whole number or not.
 * Basically performs PHP's coercive string conversion to float or int based on its content,
 * to accommodate what strict_types mode cannot do.
 *
 * @param mixed $string
 * @return int|float
 */
function zen_str_to_numeric($string) {
    if (is_null($string)) {
        return 0;
    }
    if (is_int($string) || is_float($string)) {
        return $string;
    }
    if (is_bool($string)) {
        return (int)$string;
    }
    if (! is_string($string)) {
        throw new TypeError('Value is not a string.');
    }
    if (! is_numeric($string)) {
        throw new TypeError('Value is not a numeric string.');
    }
    if (strpos($string, '.') === false) {
        return (int)$string;
    }

    return (float)$string;
}

/**
 * Find a language define for displaying translated output for a configuration_key's configuration_value.
 * Given a configuration key, look up its configuration_value and find a language-define for it, using $prefix.$value.
 * eg: key: SHIPPING_WEIGHT_UNITS, prefix: TEXT_SHIPPING_, key's value: 'lbs', get lang define of TEXT_SHIPPING_LBS
 * Or if not found, fallback to supplied default; if no default, attempt to just return the configuration_value
 *
 * @param string $config_key Name of configuration_key constant
 * @param string|null $lang_define_prefix Language define prefix to be prepended for lookup
 * @param string|null $fallback Value to return if failures occur
 * @return string
 */
function zen_get_translated_config_setting(string $config_key, ?string $lang_define_prefix = null, ?string $fallback = null): string
{
    // Get current configuration_value for the specified key.
    // It would already be defined as a constant at this point, so if not defined, then it's invalid, so we'll return the fallback.
    if (!defined($config_key)) {
        return $fallback ?? $config_key;
    }
    $value = constant($config_key);

    $lookup = strtoupper($lang_define_prefix . $value);

    if (defined($lookup)) {
        return constant($lookup);
    }

    return $fallback ?? $value;
}
