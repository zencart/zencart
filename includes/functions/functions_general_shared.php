<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Oct 17 Modified in v2.1.0 $
 */

function zen_get_zcversion()
{
    return PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
}

/**
 * Set timeout for the current script.
 * @param int $limit seconds
 */
function zen_set_time_limit($limit)
{
    @set_time_limit((int)$limit);
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


////
// Wrapper function for round()
function zen_round($value, $precision)
{
    $value = round($value * pow(10, $precision), 0);
    $value = $value / pow(10, $precision);
    return $value;
}


/**
 * replacement for fmod to manage values < 1
 */
function fmod_round($x, $y)
{
    if ($y == 0) {
        return 0;
    }
    $x = (string)$x;
    $y = (string)$y;
    $zc_round = ($x * 1000) / ($y * 1000);
    $zc_round_ceil = round($zc_round, 0);
    $multiplier = $zc_round_ceil * $y;
    $results = abs(round($x - $multiplier, 6));
    return $results;
}

/**
 * Cast an input to a desired type.
 * (Note: does not operate recursively on arrays)
 */
function zen_cast($input, ?string $cast_to): mixed
{
    return match ($cast_to) {
        'string' => (string)$input,
        'boolean', 'bool' => (bool)$input,
        'int', 'integer' => (int)$input,
        'double', 'float' => (float)$input,
        'array' => (is_array($input)) ? $input : [$input],
        default => $input,
    };
}

/**
 * Convert value to a float/int -- mainly used for sanitizing and returning non-empty strings or nulls
 * @param int|float|string $input
 * @return float|int
 */
function convertToFloat($input = 0): float|int
{
    if ($input === null) return 0;
    if (is_float($input) || is_int($input)) return $input;
    $val = preg_replace('/[^0-9,\.\-]/', '', (string)$input);
    // do a non-strict compare here:
    if ($val == 0 || empty($val)) return 0;
    return (float)$val;
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
 * Get a shortened filename to fit within the db field constraints
 *
 * @param string $filename (could also be a URL)
 * @param string $table_name
 * @param string $field_name
 * @param string $extension String to denote the extension. The right-most "." is used as a fallback.
 * @return string
 */
function zen_limit_image_filename($filename, $table_name, $field_name, $extension = '.')
{
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


/**
 * Get field type from database
 */
function zen_field_type(string $table_name, string $field_name): string
{
    global $db;
    $query = $db->MetaColumns($table_name);
    return $query[strtoupper($field_name)]->type;
}


/**
 * Get field length from database
 */
function zen_field_length(string $table_name, string $field_name): int
{
    global $db;
    $query = $db->MetaColumns($table_name);
    return (int)$query[strtoupper($field_name)]->max_length;
}

/**
 * Generate HTML FORM attributes for size="foo" maxlength="bar" based on maximum size (default 50)
 * example: zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name')
 */
function zen_set_field_length(string $table_name, string $field_name, $max = null, bool $override = false): string
{
    if (is_null($max)) {
        $max = 70;
        if (IS_ADMIN_FLAG === true) {
            $max = 50;
        }
    }
    $max = (int)$max;

    $field_length = zen_field_length($table_name, $field_name);
    $size = $field_length + 1;

    if ($override !== true && $field_length > $max) {
        $size = $max + 1;
    }

    return 'size="' . $size . '" maxlength="' . $field_length . '"';
}


/**
 * Return all HTTP GET variables, except those passed as a parameter
 *
 * The return is a urlencoded string
 *
 * @param mixed $exclude_array either a single or array of parameter names to be excluded from output
 * @return string url_encoded string of GET params
 */
function zen_get_all_get_params($exclude_array = array())
{
    if (!is_array($exclude_array)) $exclude_array = array();
    $exclude_array = array_merge($exclude_array, array('main_page', 'error', 'x', 'y', 'cmd'));
    if (function_exists('zen_session_name')) {
        $exclude_array[] = zen_session_name();
    }
    $get_url = '';
    if (is_array($_GET) && (count($_GET) > 0)) {
        foreach ($_GET as $key => $value) {
            if (!in_array($key, $exclude_array)) {
                if (!is_array($value)) {
                    if (!empty($value)) {
                        $get_url .= rawurlencode(stripslashes((string)$key)) . '=' . rawurlencode(stripslashes((string)$value)) . '&';
                    }
                } else {
                    if (IS_ADMIN_FLAG) continue; // admin (and maybe catalog?) doesn't support passing arrays by GET, so skipping any arrays here
                    foreach (array_filter($value) as $arr) {
                        if (is_array($arr)) continue;
                        $get_url .= rawurlencode(stripslashes((string)$key)) . '[]=' . rawurlencode(stripslashes((string)$arr)) . '&';
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
function zen_post_all_get_params($exclude_array = array(), $hidden = true, $parameters = '')
{
    if (!is_array($exclude_array)) $exclude_array = array((string)$exclude_array);
    $exclude_array = array_merge($exclude_array, array('error', 'x', 'y'));
    if (function_exists('zen_session_name')) {
        $exclude_array[] = zen_session_name();
    }
    $fields = '';
    if (is_array($_GET) && (count($_GET) > 0)) {
        foreach ($_GET as $key => $value) {
            if (!in_array($key, $exclude_array)) {
                if (!is_array($value)) {
                    if (!empty($value)) {
                        if ($hidden) {
                            $fields .= zen_draw_hidden_field($key, $value);
                        } else {
                            $fields .= zen_draw_input_field($key, $value, $parameters);
                        }
                    }
                } else {
                    foreach (array_filter($value) as $arr) {
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
    if (!in_array($order1, array(SORT_ASC, SORT_DESC))) $order1 = SORT_ASC;
    if ($columnName2 == '') {
        $columnName2 = $keys[1];
    }
    if (!in_array($order2, array(SORT_ASC, SORT_DESC))) $order2 = SORT_ASC;

    // prepare sub-arrays for aiding in sorting
    foreach ($data as $key => $val) {
        $sort1[] = $val[$columnName1];
        $sort2[] = $val[$columnName2];
    }
    // do actual sort based on specified fields.
    array_multisort($sort1, $order1, $sort2, $order2, $data);
    return $data;
}


/**
 * check to see if free shipping rules allow the specified shipping module to be enabled or to disable it in lieu of being free
 * @param $shipping_module
 * @return bool
 */
function zen_get_shipping_enabled(string $shipping_module): bool
{
    global $PHP_SELF;

    // for admin always true
    if (IS_ADMIN_FLAG && strstr($PHP_SELF, FILENAME_MODULES)) {
        return true;
    }

    $check_cart_free = $_SESSION['cart']->in_cart_check('product_is_always_free_shipping', '1');
    $check_cart_cnt = $_SESSION['cart']->count_contents();
    $check_cart_weight = $_SESSION['cart']->show_weight();

    // Free Shipping when 0 weight - enable freeshipper - ORDER_WEIGHT_ZERO_STATUS must be on
    if (ORDER_WEIGHT_ZERO_STATUS == '1' && ($check_cart_weight == 0 && $shipping_module == 'freeshipper')) {
        return true;
    }

    // Free Shipping when 0 weight - disable everyone - ORDER_WEIGHT_ZERO_STATUS must be on
    if (ORDER_WEIGHT_ZERO_STATUS == '1' && ($check_cart_weight == 0 && $shipping_module != 'freeshipper')) {
        return false;
    }

    if ($_SESSION['cart']->free_shipping_items() == $check_cart_cnt && $shipping_module == 'freeshipper') {
        return true;
    }

    if ($_SESSION['cart']->free_shipping_items() == $check_cart_cnt && $shipping_module != 'freeshipper') {
        return false;
    }

    // Always free shipping only true - enable freeshipper
    if ($check_cart_free == $check_cart_cnt && $shipping_module == 'freeshipper') {
        return true;
    }

    // Always free shipping only true - disable everyone
    if ($check_cart_free == $check_cart_cnt && $shipping_module != 'freeshipper') {
        return false;
    }

    // Always free shipping only is false - disable freeshipper
    if ($check_cart_free != $check_cart_cnt && $shipping_module == 'freeshipper') {
        return false;
    }
    return true;
}


/**
 * @param $from
 * @param $to
 * @param $string
 * @return string|string[]
 * @deprecated
 */
function zen_convert_linefeeds($from, $to, $string)
{
    trigger_error('Call to deprecated function zen_convert_linefeeds.', E_USER_DEPRECATED);

    return str_replace($from, $to, $string);
}

/**
 * Return a random value
 */
function zen_rand(?int $min = null, ?int $max = null): int
{
    static $seeded;

    if (!isset($seeded)) {
        // -----
        // By default, microtime returns a string value.  To increase the precision of the
        // random seed, have it return a float to be multiplied and then convert the value
        // to an integer, as required by the mt_srand function.
        //
        mt_srand((int)(microtime(true) * 1000000));
        $seeded = true;
    }

    if (isset($min) && isset($max)) {
        if ($min >= $max) {
            return $min;
        }

        return random_int($min, $max);
    }

    return mt_rand();
}


// debug utility only
function utilDumpRequest($mode = 'p', $out = 'log')
{
    if ($mode == 'p') {
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

/**
 * Convert a truthy/falsey string to boolean.
 * Recognizes words like Yes, No, Off, On, True/False (both string and native types); and is not case-sensitive
 * Also recognizes numbers both as strings and integers ('0', '1') as booleans
 * Blank (empty string) is treated as false.
 *
 * By default, will return null if the passed value is neither truthy/falsey (ie: 'red', or '2')
 */
function zen_to_boolean(mixed $value, bool $null_on_failure = true): bool|null
{
    if ($null_on_failure) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

/**
 * this function will need to be removed if
 * we ever revert to a full laravel install
 */

function request()
{
    return \Zencart\Request\Request::getInstance();
}

function zen_updated_by_admin($admin_id = null): string
{
    if (empty($admin_id) && empty($_SESSION['admin_id'])) {
        return '';
    }
    if (empty($admin_id)) {
        $admin_id = $_SESSION['admin_id'];
    }
    $name = zen_get_admin_name($admin_id);
    return ($name ?? 'Unknown Name') . " [$admin_id]";
}

/**
 * Lookup admin user name based on admin id
 * @param int $id
 * @return string
 */
function zen_get_admin_name($id = null)
{
    global $db;
    if (empty($id)) $id = $_SESSION['admin_id'];
    $sql = "SELECT admin_name FROM " . TABLE_ADMIN . " WHERE admin_id = :adminid: LIMIT 1";
    $sql = $db->bindVars($sql, ':adminid:', $id, 'integer');
    $result = $db->Execute($sql);
    return $result->RecordCount() ? $result->fields['admin_name'] : null;
}

// Compatibility

function zen_draw_products_pull_down($field_name, $parameters = '', $exclude = [], $show_id = false, $set_selected = 0, $show_model = false, $show_current_category = false, $order_by = '', $filter_by_option_name = null)
{
   trigger_error('Call to deprecated function; please use new names', E_USER_DEPRECATED);
   return zen_draw_pulldown_products($field_name, $parameters, $exclude, $show_id, $set_selected, $show_model, $show_current_category, $order_by, $filter_by_option_name);
}

function zen_draw_products_pull_down_attributes($field_name, $parameters = '', $exclude = [], $order_by = 'name', $filter_by_option_name = null)
{
   trigger_error('Call to deprecated function; please use new names', E_USER_DEPRECATED);
   return zen_draw_pulldown_products_having_attributes($field_name, $parameters, $exclude, $order_by, $filter_by_option_name);
}

function zen_draw_products_pull_down_categories($field_name, $parameters = '', $exclude = [], $show_id = false, $show_parent = false) {
   trigger_error('Call to deprecated function; please use new names', E_USER_DEPRECATED);
   return zen_draw_pulldown_categories_having_products($field_name, $parameters, $exclude, $show_id, $show_parent);
}

function zen_draw_products_pull_down_categories_attributes($field_name, $parameters = '', $exclude = [], $show_full_path = false, $filter_by_option_name = null){
   trigger_error('Call to deprecated function; please use new names', E_USER_DEPRECATED);
   return zen_draw_pulldown_categories_having_products_with_attributes($field_name, $parameters, $exclude, $show_full_path, $filter_by_option_name);
}

function zen_get_orders_status()
{
   trigger_error('Call to deprecated function; please use new names', E_USER_DEPRECATED);
   return zen_get_orders_status_pulldown_array();
}

