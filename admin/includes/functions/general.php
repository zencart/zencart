<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2024 Apr 16 Modified in v2.0.1 $
 */


/**
 * @TODO - deprecate in favor of an HR or bordered/borderless DIV
 *
 * @param $image
 * @param $alt
 * @param string $width
 * @param string $height
 * @param string $params
 * @return false|string
 */
function zen_info_image($image, $alt, $width = '', $height = '', $params = '')
{
    if (!empty($image) && (file_exists(DIR_FS_CATALOG_IMAGES . $image))) {
        $image = zen_image(DIR_WS_CATALOG_IMAGES . $image, $alt, $width, $height, $params);
    } else {
        $image = TEXT_IMAGE_NONEXISTENT;
    }

    return $image;
}


function zen_tax_classes_pull_down($parameters, $selected = '')
{
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $classes = $db->Execute("SELECT tax_class_id, tax_class_title
                             FROM " . TABLE_TAX_CLASS . "
                             ORDER BY tax_class_title");

    while (!$classes->EOF) {
        $select_string .= '<option value="' . $classes->fields['tax_class_id'] . '"';
        if ($selected == $classes->fields['tax_class_id']) $select_string .= ' SELECTED';
        $select_string .= '>' . $classes->fields['tax_class_title'] . '</option>';
        $classes->MoveNext();
    }
    $select_string .= '</select>';

    return $select_string;
}


function zen_geo_zones_pull_down($parameters, $selected = '')
{
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $zones = $db->Execute("SELECT geo_zone_id, geo_zone_name
                                 FROM " . TABLE_GEO_ZONES . "
                                 ORDER BY geo_zone_name");

    while (!$zones->EOF) {
        $select_string .= '<option value="' . $zones->fields['geo_zone_id'] . '"';
        if ($selected == $zones->fields['geo_zone_id']) $select_string .= ' SELECTED';
        $select_string .= '>' . $zones->fields['geo_zone_name'] . '</option>';
        $zones->MoveNext();
    }
    $select_string .= '</select>';

    return $select_string;
}


function zen_get_geo_zone_name($geo_zone_id)
{
    global $db;
    $zones = $db->Execute("SELECT geo_zone_name
                           FROM " . TABLE_GEO_ZONES . "
                           WHERE geo_zone_id = " . (int)$geo_zone_id);

    if ($zones->RecordCount() < 1) {
        $geo_zone_name = $geo_zone_id;
    } else {
        $geo_zone_name = $zones->fields['geo_zone_name'];
    }

    return $geo_zone_name;
}

/**
 * proxy into language class to get list of configured languages and their settings
 */
function zen_get_languages(): array
{
    /**
     * @var language $lng
     */
    global $lng;
    if ($lng === null) {
        $lng = new language();
    }
    return array_values($lng->get_languages_by_code());
}


function zen_cfg_select_coupon_id($coupon_id, $key = '')
{
    $coupon_array = [];
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $coupons = Coupon::getAllCouponsByName();
    $coupon_array[] = [
        'id' => '0',
        'text' => 'None'
    ];

    foreach ($coupons as $coupon) {
        $coupon_array[] = [
            'id' => $coupon['coupon_id'],
            'text' => $coupon['coupon_name']
        ];
    }

    return zen_draw_pull_down_menu($name, $coupon_array, $coupon_id, 'class="form-control"');
}


////
// Alias function for Store configuration values in the Administration Tool
function zen_cfg_pull_down_country_list($country_id, $key = '')
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, zen_get_countries_for_admin_pulldown(), $country_id, 'class="form-control"');
}


////
function zen_cfg_pull_down_country_list_none($country_id, $key = '')
{
    $country_array = zen_get_countries_for_admin_pulldown('None');
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, $country_array, $country_id, 'class="form-control"');
}


////
function zen_cfg_pull_down_zone_list($zone_id, $key = '')
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $none = [['id' => 0, 'text' => TEXT_NONE]];
    $zones = zen_get_country_zones(STORE_COUNTRY);
    return zen_draw_pull_down_menu($name, array_merge($none, $zones), $zone_id, 'class="form-control"');
}


//// @TODO - is there a tax class query function already?
function zen_cfg_pull_down_tax_classes($tax_class_id, $key = '')
{
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class = $db->Execute("SELECT tax_class_id, tax_class_title
                               FROM " . TABLE_TAX_CLASS . "
                               ORDER BY tax_class_title");

    while (!$tax_class->EOF) {
        $tax_class_array[] = array(
            'id' => $tax_class->fields['tax_class_id'],
            'text' => $tax_class->fields['tax_class_title']
        );
        $tax_class->MoveNext();
    }

    return zen_draw_pull_down_menu($name, $tax_class_array, $tax_class_id, 'class="form-control"');
}


////
// Function to read in text area in admin
function zen_cfg_textarea($text, $key = '')
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_textarea_field($name, false, 60, 5, htmlspecialchars($text, ENT_COMPAT, CHARSET, FALSE), 'class="form-control"');
}


////
// Function to read in text area in admin
function zen_cfg_textarea_small($text, $key = '')
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_textarea_field($name, false, 35, 1, htmlspecialchars($text, ENT_COMPAT, CHARSET, FALSE), 'class="noEditor form-control" autofocus');
}

// @TODO - is there a zone lookup query already?
function zen_cfg_get_zone_name($zone_id)
{
    global $db;
    $zone = $db->Execute("SELECT zone_name
                          FROM " . TABLE_ZONES . "
                          WHERE zone_id = " . (int)$zone_id);

    if ($zone->RecordCount() < 1) {
        return $zone_id;
    } else {
        return $zone->fields['zone_name'];
    }
}

function zen_cfg_pull_down_htmleditors($html_editor, $index = null)
{
    global $editors_list;
    $name = $index ? 'configuration[' . $index . ']' : 'configuration_value';

    $editors_pulldown = array();
    foreach ($editors_list as $key => $value) {
        $editors_pulldown[] = array('id' => $key, 'text' => $value['desc']);
    }
    return zen_draw_pull_down_menu($name, $editors_pulldown, $html_editor, 'class="form-control"');
}

function zen_cfg_pull_down_exchange_rate_sources($source, $key = '')
{
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

function zen_cfg_password_input($value, $key = '')
{
    if (function_exists('dbenc_is_encrypted_value_key') && dbenc_is_encrypted_value_key($key)) {
        $value = dbenc_decrypt($value);
    }
    return zen_draw_password_field('configuration[' . $key . ']', $value, false, 'class="form-control"');
}

function zen_cfg_password_display($value)
{
    $length = strlen($value);
    return str_repeat('*', ($length > 16 ? 16 : $length));
}

////
// Alias function for Store configuration values in the Administration Tool
function zen_cfg_select_option($select_array, $key_value, $key = '')
{
    $string = '';

    for ($i = 0, $n = count($select_array); $i < $n; $i++) {
        $name = (zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value';

        $string .= '<div class="radio"><label>' . zen_draw_radio_field($name, $select_array[$i], ($key_value == $select_array[$i] ? true : false), '', 'id="' . strtolower($select_array[$i] . '-' . $name) . '" class="inputSelect"') . $select_array[$i] . '</label></div>';
    }

    return $string;
}


function zen_cfg_select_drop_down($select_array, $key_value, $key = '')
{
    $string = '';

    $name = (zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value';
    return zen_draw_pull_down_menu($name, $select_array, (int)$key_value, 'class="form-control"');
}

////
// Alias function for module configuration keys
function zen_mod_select_option($select_array, $key_name, $key_value)
{
    $string = '';
    foreach ($select_array as $key => $value) {
        if (is_int($key)) $key = $value;
        $string .= '<div class="radio"><label>' . zen_draw_radio_field('configuration[' . $key_name . ']', $key, ($key_value == $key ? true : false)) . $value . '</label></div>';
    }

    return $string;
}

////
// Collect server information
function zen_get_system_information($privacy = false)
{
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
       $mysql_slow_query_log_status = '0';
       if (in_array($result->fields['Value'], ['On', 'ON', '1',])) {
         $mysql_slow_query_log_status = '1';
       }
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
    $uptime = (DISPLAY_SERVER_UPTIME == 'true') ? 'Unsupported' : 'Disabled/Unavailable';

    // check to see if "exec()" is disabled in PHP -- if not, get additional info via command line
    $exec_disabled = false;
    $php_disabled_functions = @ini_get("disable_functions");
    if ($php_disabled_functions != '') {
        if (in_array('exec', preg_split('/,/', str_replace(' ', '', $php_disabled_functions)))) {
            $exec_disabled = true;
        }
    }
    if (!$exec_disabled) {
        [$system, $host, $kernel] = array('', $_SERVER['SERVER_NAME'], php_uname());
        @exec('uname -a 2>&1', $output, $errnum);
        if ($errnum == 0 && count($output)) [$system, $host, $kernel] = preg_split('/[\s,]+/', $output[0], 5);
        $output = '';
        if (DISPLAY_SERVER_UPTIME == 'true') {
            @exec('uptime 2>&1', $output, $errnum);
            if ($errnum == 0 && isset($output[0])) {
                $uptime = $output[0];
            }
        }
    }

    $timezone = date_default_timezone_get();

    $systemInfo = [
        'date' => zen_datetime_short(date('Y-m-d H:i:s')),
        'timezone' => $timezone,
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
    ];

    if ($privacy) {
        unset ($systemInfo['mysql_slow_query_log_file']);
    }

    return $systemInfo;
}


//@TODO move to Order class
function zen_remove_order($order_id, $restock = false)
{
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_ORDER', array(), $order_id, $restock);
    if ($restock == 'on') {
        $order = $db->Execute("select products_id, products_quantity
                             from " . TABLE_ORDERS_PRODUCTS . "
                             where orders_id = " . (int)$order_id);

        while (!$order->EOF) {
            $db->Execute("update " . TABLE_PRODUCTS . "
                      set products_quantity = products_quantity + " . $order->fields['products_quantity'] . ", products_ordered = products_ordered - " . $order->fields['products_quantity'] . " where products_id = " . (int)$order->fields['products_id']);
            $order->MoveNext();
        }
    }

    $db->Execute("delete from " . TABLE_ORDERS . " where orders_id = " . (int)$order_id);
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_ORDERS_STATUS_HISTORY . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_ORDERS_TOTAL . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_COUPON_GV_QUEUE . "
                  where order_id = " . (int)$order_id . " and release_flag = 'N'");

    zen_record_admin_activity('Deleted order ' . (int)$order_id . ' from database via admin console.', 'warning');
}


function zen_call_function($function, $parameter, $object = '')
{
    if ($object === '') {
        return $function($parameter);
    }

    return call_user_func([$object, $function], $parameter);
}

//@todo - is there a function already for this query?
function zen_get_zone_class_title($zone_class_id)
{
    global $db;
    if ($zone_class_id == '0') {
        return TEXT_NONE;
    }

    $classes = $db->Execute("select geo_zone_name
                               from " . TABLE_GEO_ZONES . "
                               where geo_zone_id = " . (int)$zone_class_id);
    if ($classes->EOF) return '';
    return $classes->fields['geo_zone_name'];
}

//// @todo - is there a function already for this query? See the one above
function zen_cfg_pull_down_zone_classes($zone_class_id, $key = '')
{
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $zone_class_array = [['id' => '0', 'text' => TEXT_NONE]];
    $zone_class = $db->Execute("select geo_zone_id, geo_zone_name
                                from " . TABLE_GEO_ZONES . "
                                order by geo_zone_name");

    while (!$zone_class->EOF) {
        $zone_class_array[] = [
            'id' => $zone_class->fields['geo_zone_id'],
            'text' => $zone_class->fields['geo_zone_name']
        ];
        $zone_class->MoveNext();
    }

    return zen_draw_pull_down_menu($name, $zone_class_array, $zone_class_id, 'class="form-control"');
}


////
function zen_cfg_pull_down_order_statuses($order_status_id, $key = '')
{
    $name = ($key) ? 'configuration[' . $key . ']' : 'configuration_value';
    return zen_draw_order_status_dropdown($name, $order_status_id, array('id' => 0, 'text' => TEXT_DEFAULT), 'class="form-control"');
}

/**
 * Return a pull-down menu of the available order-status values,
 * optionally prefixed by a "please choose" selection.
 */
function zen_draw_order_status_dropdown($field_name, $default_value, $first_selection = '', $parms = '')
{
    global $db;
    $statuses = $db->Execute(
        "SELECT orders_status_id AS `id`, orders_status_name AS `text`
            FROM " . TABLE_ORDERS_STATUS . "
            WHERE language_id = " . (int)$_SESSION['languages_id'] . "
            ORDER BY sort_order ASC, orders_status_id ASC"
    );
    $statuses_array = [];
    if (is_array($first_selection)) {
        $statuses_array[] = $first_selection;
    }
    foreach ($statuses as $status) {
        $statuses_array[] = [
            'id' => $status['id'],
            'text' => "{$status['text']} [{$status['id']}]"
        ];
    }
    return zen_draw_pull_down_menu($field_name, $statuses_array, $default_value, $parms);
}


/**
 * @TODO - move to language class
 * Lookup Languages Icon by id or code
 * @param $lookup
 * @return bool|string
 */
function zen_get_language_icon($lookup)
{
    global $db;
    $languages_icon = $db->Execute("SELECT directory, image FROM " . TABLE_LANGUAGES . "
        WHERE
        languages_id = " . (int)$lookup . "
        OR
        code = '" . zen_db_input($lookup) . "'
        LIMIT 1");
    if ($languages_icon->EOF) {
        return '';
    }
    return zen_image(DIR_WS_CATALOG_LANGUAGES . $languages_icon->fields['directory'] . '/images/' . $languages_icon->fields['image'], $languages_icon->fields['directory']);
}


/**
 * @param $lookup
 * @return mixed|string
 * @todo move to lang class
 * lookup language directory name by id or code
 */
function zen_get_language_name($lookup)
{
    global $db;
    $check_language = $db->Execute("SELECT directory FROM " . TABLE_LANGUAGES . "
        WHERE
        languages_id = " . (int)$lookup . "
        OR
        code = '" . zen_db_input($lookup) . "'
        LIMIT 1");

    if ($check_language->EOF) {
        return '';
    }
    return $check_language->fields['directory'];
}


function zen_get_configuration_group_value($lookup)
{
    // @todo could also do this as a dynamic scope
    $r = \App\Models\ConfigurationGroup::select('configuration_group_title')->where('configuration_group_id', '=', $lookup)->first();
    return $r['configuration_group_title'] ?? (int)$lookup;
}


/**
 * @TODO move to a class
 * @todo DRY
 * Sets the status of a product review
 */
function zen_set_reviews_status($review_id, $status)
{
    global $db;
    if ($status == '1') {
        return $db->Execute("update " . TABLE_REVIEWS . "
                           set status = 1
                           where reviews_id = " . (int)$review_id);

    } elseif ($status == '0') {
        return $db->Execute("update " . TABLE_REVIEWS . "
                           set status = 0
                           where reviews_id = " . (int)$review_id);

    } else {
        return -1;
    }
}


/**
 * master category selection
 * @param int $product_id
 * @param bool $fullpath
 * @return array
 */
function zen_get_master_categories_pulldown($product_id, $fullpath = false)
{
    global $db;
    $master_category_array = [];
    $master_categories_query = $db->Execute("SELECT ptc.products_id, cd.categories_name, cd.categories_id
                                             FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                             LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = ptc.categories_id
                                             WHERE ptc.products_id = " . (int)$product_id . "
                                             AND cd.language_id = " . (int)$_SESSION['languages_id']);
    $master_category_array[] = [
        'id' => '0',
        'text' => TEXT_INFO_SET_MASTER_CATEGORIES_ID,
    ];
    foreach ($master_categories_query as $item) {
        $master_category_array[] = [
            'id' => $item['categories_id'],
            'text' => ($fullpath ? zen_output_generated_category_path($item['categories_id']) : $item['categories_name']) . ' (' . TEXT_INFO_ID . $item['categories_id'] . ')',
        ];
    }
    return $master_category_array;
}

/**
 * Alias function for Store configuration values in the Administration Tool
 * adapted from USPS-related contributions by Brad Waite and Fritz Clapp
 */
function zen_cfg_select_multioption($select_array, $key_value, $key = '')
{
    $string = '';
    for ($i = 0, $n = count($select_array); $i < $n; $i++) {
        $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
        $key_values = explode(", ", $key_value);
        $string .= '<div class="checkbox"><label>' . zen_draw_checkbox_field($name, $select_array[$i], (in_array($select_array[$i], $key_values) ? true : false), 'id="' . strtolower($select_array[$i] . '-' . $name) . '"') . $select_array[$i] . '</label></div>' . "\n";
    }
    $string .= zen_draw_hidden_field($name, '--none--');
    return $string;
}

/**
 * Function for configuration values that are read-only, e.g. a plugin's version number
 */
function zen_cfg_read_only($text, $key = '')
{
    $name = (!empty($key)) ? 'configuration[' . $key . ']' : 'configuration_value';
    $text = htmlspecialchars_decode($text, ENT_COMPAT);

    return $text . zen_draw_hidden_field($name, $text);
}

// @TODO can this be merged with another pulldown, not specific to coupon admin?
function zen_geo_zones_pull_down_coupon($parameters, $selected = '')
{
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
 * get first customer comment record for an order (usually contains their special instructions)
 */
function zen_get_orders_comments($orders_id)
{
    global $db;
    $orders_comments_query = "SELECT osh.comments
                              FROM " . TABLE_ORDERS_STATUS_HISTORY . " osh
                              WHERE osh.orders_id = " . (int)$orders_id . "
                              ORDER BY osh.orders_status_history_id
                              LIMIT 1";
    $orders_comments = $db->Execute($orders_comments_query);
    if ($orders_comments->EOF) return '';
    return $orders_comments->fields['comments'];
}


/**
 * Toggle ezpage to specified status
 *
 * @param int $pages_id
 * @param int $status 0|1
 * @param string $status_field
 */
function zen_set_ezpage_status(int $pages_id, int $status, string $status_field)
{
    global $db;
    if ($status == '1' || $status == '0') {
        zen_record_admin_activity('EZ-Page ID ' . (int)$pages_id . ' [' . $status_field . '] changed to ' . $status, 'info');
        $db->Execute("UPDATE " . TABLE_EZPAGES . "
                      SET " . zen_db_input($status_field) . " = " . (int)$status . "
                      WHERE pages_id = " . (int)$pages_id);
    }
}


/**
 * Retrieve a list of order-status names for a pulldown menu
 * @TODO Refactor code that is buiding this dropdown array inline, to use this function instead
 */
function zen_get_orders_status_pulldown_array()
{
    $ordersStatus = zen_getOrdersStatuses();
    return $ordersStatus['orders_statuses'];
}

function zen_getOrdersStatuses(bool $keyed = false): array
{
    global $db;
    $orders_statuses = [];
    $orders_status_array = [];
    $orders_status_query = $db->Execute('SELECT orders_status_id, orders_status_name FROM ' . TABLE_ORDERS_STATUS . '
                                 WHERE language_id = ' . (int)$_SESSION['languages_id'] . ' ORDER BY sort_order, orders_status_id');
    foreach ($orders_status_query as $next_status) {
        if (!$keyed) {
            $orders_statuses[] = [
                'id' => $next_status['orders_status_id'],
                'text' => $next_status['orders_status_name'] . ' [' . $next_status['orders_status_id'] . ']',
            ];
            $orders_status_array[$next_status['orders_status_id']] = $next_status['orders_status_name'] . ' [' . $next_status['orders_status_id'] . ']';
        } else {
            $orders_statuses[$next_status['orders_status_id']] = $next_status['orders_status_name'];
            $orders_status_array[$next_status['orders_status_id']] = $next_status['orders_status_name'];
        }
    }
    return ['orders_statuses' => $orders_statuses, 'orders_status_array' => $orders_status_array,];
}

function zen_get_customer_email_from_id($cid) {
   global $db;
   $query = $db->Execute("SELECT customers_email_address FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . (int)$cid);
   if ($query->EOF) return '';
   return $query->fields['customers_email_address'];
}
