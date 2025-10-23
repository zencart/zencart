<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Sep 17 Modified in v2.1.0-beta1 $
 */

/**
 * Returns a string with conversions for security.
 *
 * @param ?string $string The string to be parsed
 * @param array|bool $translate contains a string to be translated, otherwise just quote is translated
 * @param bool $protected Do we run htmlspecialchars over the string
 * @return string
 * @since ZC v1.0.3
 */
function zen_output_string(?string $string, array|bool $translate = false, bool $protected = false): string
{
    if (is_null($string) === true) {
        return '';
    }

    if ($protected === true) {
        $double_encode = !IS_ADMIN_FLAG;
        return htmlspecialchars($string, ENT_COMPAT, CHARSET, $double_encode);
    }

    if ($translate === false) {
        return strtr($string, ['"' => '&quot;']);
    }
    return strtr($string, $translate);
}

/**
 * Returns a string with quotes converted to html entities
 * so that they can be passed through from page to page
 * without mistakenly being converted to specialchars or go "missing"
 * @since ZC v2.2.0
 */
function zen_preserve_search_quotes(?string $search_string): string
{
    return urlencode($search_string);
}

/**
 * Returns a string with conversions for security.
 *
 * Simply calls the zen_output_string function
 * with parameters that run htmlspecialchars over the string
 * and converts quotes to html entities
 *
 * @param  string  $string  The string to be parsed
 * @since ZC v1.0.3
 */
function zen_output_string_protected(?string $string): string
{
    return zen_output_string($string, false, true);
}

/**
 * Returns a string with conversions for security.
 *
 * @param  string  $string  The string to be parsed
 * @since ZC v1.0.3
 */
function zen_sanitize_string(string $string): string|null
{
    $string = preg_replace('/ +/', ' ', $string);
    return preg_replace("/[<>]/", '_', $string);
}

/**
 * Checks whether a string/array is null/blank/empty or uppercase string 'NULL'
 * Differs from empty() in that it doesn't test for boolean false or '0' string/int
 *
 * @param string|array|Countable|null|int|float|bool $value
 * @since ZC v1.0.3
 */
function zen_not_null(mixed $value): bool
{
    if (null === $value) {
        return false;
    }
    if (is_countable($value)) {
        return count($value) > 0;
    }
    if (is_string($value)) {
        return trim($value) !== '' && $value !== 'NULL';
    }
    // anything else (int, float, bool, object, resource, etc) is treated as not null
    return true;
}

/**
 * Break a word in a string if it is longer than a specified length ($len)
 *
 * @param  string  $string  The string to be broken up
 * @param  int  $len  The maximum length allowed
 * @param  ?string  $break_char  The character to use at the end of the broken line
 * @return string
 * @since ZC v1.0.3
 */
function zen_break_string(?string $string, int $len, string $break_char = '-'): string
{
    if (is_null($string) === true) {
        return '';
    }
    $l = 0;
    $output = '';
    for ($i = 0, $n = mb_strlen($string); $i < $n; $i++) {
        $char = mb_substr($string, $i, 1);
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
 * Truncate a string to the specified length, optionally using a custom "more" suffix.
 * Note: the $more parameter still supports providing string 'true' to mean appending "...".
 *
 * @param  ?string  $str
 * @param  int|string  $len
 * @param  string  $more
 * @return string
 * @since ZC v1.0.3
 */
function zen_trunc_string(?string $str = '', int|string $len = 150, string $more = '...'): string
{
    if (empty($str)) {
        return '';
    }

    $len = (int)$len;
    if ($len <= 0) {
        return '';
    }

    $str = trim($str);
    // if text is less than the limit
    if (mb_strlen($str) <= $len) {
        return $str;
    }

    // get limited text block
    $str = mb_substr($str, 0, $len);

    if ($str === '') {
        return $str;
    }

    if ($more === 'true') { // backward compatibility for older versions and plugins
        $more = '...';
    }
    if ($more === 'false') { // this was never officially supported, but added for clarity in case.
        $more = '';
    }

    // check for no spaces at all
    if (!substr_count($str, ' ')) {
        return $str . $more;
    }

    // remove final chars (of a partial word) and the preceding space
    $str = preg_replace('/(\s\w+$)|(\s+$)/u', '', $str);
    return $str . $more;
}

/**
 * Truncate a paragraph after $size words
 *
 * @param  string  $paragraph
 * @param  int  $size
 * @return string
 * @since ZC v1.3.0
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
 *
 * @param string $string
 * @param string $needle
 * @return int
 * @since ZC v1.0.3
 */
function zen_word_count(string $string, string $needle): int
{
    $temp_array = preg_split('/' . $needle . '/', $string);
    return count($temp_array);
}

/**
 * Collapse an array into a string
 * A sort of pseudo-serialize function
 * Used mainly by the Navigation class to store historical info
 *
 * @param  array  $array
 * @param  array|string  $exclude
 * @param  string  $equals
 * @param  string  $separator
 * @return string
 * @since ZC v1.0.3
 */
function zen_array_to_string(array $array, array|string $exclude = '', string $equals = '=', string $separator = '&'): string
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
 * @param  string  $string
 * @return string
 * @since ZC v1.3.9a
 */
function charsetConvertWinToUtf8(string $string): string
{
    if (function_exists('iconv')) $string = iconv("Windows-1252", "ISO-8859-1//IGNORE", $string);
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
    return $string;
}

/**
 * Convert supplied string to/from entities between charsets, to sanitize data from inputs, especially APIs and gateways
 *
 * @param $string
 * @return string
 * @since ZC v1.3.9a
 */
function charsetClean($string): string
{
    if (preg_replace('/[^a-z0-9]/', '', strtolower(CHARSET)) == 'utf8') return $string;
    if (function_exists('iconv')) $string = iconv("Windows-1252", CHARSET . "//IGNORE", $string);
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
    $string = html_entity_decode($string, ENT_QUOTES, CHARSET);
    return $string;
}

/**
 * Strip out accented characters to reasonable approximations of english equivalents
 *
 * @since ZC v1.3.7.1
 */
function replace_accents($s): string
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
 * @param  string  $given_html
 * @param  int  $quote_style
 * @return string
 * @since ZC v1.1.2
 */
function zen_html_entity_decode(string $given_html, int $quote_style = ENT_QUOTES): string
{
    $trans_table = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style));
    $trans_table['&#39;'] = "'";
    return (strtr($given_html, $trans_table));
}

/**
 * Decode string encoded with htmlspecialchars()
 *
 * @since ZC v1.1.0
 */
function zen_decode_specialchars(array|string $string): array|string
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
 * @param  array|string  $mixed_value
 * @param  int  $flags
 * @param  string  $encoding
 * @param  bool  $double_encode
 * @return array|string
 * @since ZC v1.5.7
 */
function htmlentities_recurse(array|string $mixed_value, int $flags = ENT_QUOTES, string $encoding = 'utf-8', bool $double_encode = true): array|string
{
    $result = [];
    if (!is_array($mixed_value)) {
        return htmlentities($mixed_value, $flags, $encoding, $double_encode);
    }
    if (is_array($mixed_value)) {
        $result = [];
        foreach ($mixed_value as $key => $value) {
            $result[$key] = htmlentities_recurse($value, $flags, $encoding, $double_encode);
        }
    }
    return $result;
}

/**
 * Recursively apply utf8_encode on the passed string or array.
 * But was only relevant when not passing UTF8 data. It was used in ajax context.
 * Now that UTF8 is standard, this function is deprecated and does nothing.
 *
 * @param mixed $mixed_value
 * @return array|false|string
 *
 * @deprecated after Zen Cart 1.5.8a
 * @since ZC v1.5.5
 * @deleting in ZC v3.0.0
 */
function utf8_encode_recurse($mixed_value)
{
    trigger_error('Function utf8_encode_recurse is deprecated for Zen Cart versions after 1.5.8a.', E_USER_DEPRECATED);
    return $mixed_value;
}

/**
 * Remove common HTML from text for display as paragraph
 *
 * @param  string  $clean_it
 * @param  array|string  $extraTags
 * @return string
 * @since ZC v1.2.0d
 */
function zen_clean_html(string $clean_it, array|string $extraTags = ''): string
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
 * Ensure a URL is prefixed with a valid scheme (http/https) or is protocol-relative (//).
 * If null, returns empty string.
 * @since ZC v1.5.3
 */
function fixup_url(?string $url): string
{
    if (empty($url)) {
        return '';
    }
    if (!preg_match('#^https?://#', $url)) {
        $url = '//' . $url;
    }
    return $url;
}

/**
 * Alias to strtr().
 * Parse the data used in html tags to ensure the tags will not break.
 * Basically just an extension to the php strtr function
 *
 * @param string  $data  The string to be parsed
 * @param string  $parse  The needle to find
 * @return string
 * @deprecated in v1.5.8: Use strtr() instead
 * @since ZC v1.0.3
 * @deleting in ZC v3.0.0
 */
function zen_parse_input_field_data(string $data, $parse): string
{
    trigger_error('Call to deprecated function zen_parse_input_field_data. Use strtr() instead', E_USER_DEPRECATED);
    return strtr(trim($data), $parse);
}

/**
 * Convert a string to an integer
 * @since ZC v1.0.3
 * @deprecated in v1.5.8: Just cast to int directly.
 * @deleting in ZC v3.0.0
 */
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
 * @since ZC v2.0.0
 */
function zen_str_to_numeric(mixed $string): float|int
{
    if (is_null($string)) {
        return 0;
    }
    if (is_int($string) || is_float($string)) {
        return $string;
    }
    if (is_bool($string)) {
        return (int)$string;
    }
    if (!is_string($string)) {
        throw new TypeError('Value is not a string.');
    }
    if (!is_numeric($string)) {
        throw new TypeError('Value is not a numeric string.');
    }
    if (!str_contains($string, '.')) {
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
 * @since ZC v2.0.0
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
