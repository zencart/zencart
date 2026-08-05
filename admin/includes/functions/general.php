<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 Modified in v2.2.1 $
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
 * @since ZC v1.0.3
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


/**
 * @since ZC v1.0.3
 */
function zen_tax_classes_pull_down(string $parameters, string $selected = ''): string
{
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $classes = $db->Execute(
        "SELECT tax_class_id, tax_class_title
           FROM " . TABLE_TAX_CLASS . "
          ORDER BY tax_class_title"
    );

    foreach ($classes as $class) {
        $select_string .= '<option value="' . $class['tax_class_id'] . '"';
        if ((int)$selected === (int)$class['tax_class_id']) {
            $select_string .= ' SELECTED';
        }
        $select_string .= '>' . $class['tax_class_title'] . '</option>';
    }
    $select_string .= '</select>';

    return $select_string;
}


/**
 * @since ZC v1.0.3
 */
function zen_geo_zones_pull_down(string $parameters, string $selected = ''): string
{
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $zones = $db->Execute(
        "SELECT geo_zone_id, geo_zone_name
           FROM " . TABLE_GEO_ZONES . "
          ORDER BY geo_zone_name"
    );

    foreach ($zones as $zone) {
        $select_string .= '<option value="' . $zone['geo_zone_id'] . '"';
        if ((int)$selected === (int)$zone['geo_zone_id']) {
            $select_string .= ' SELECTED';
        }
        $select_string .= '>' . $zone['geo_zone_name'] . '</option>';
    }
    $select_string .= '</select>';

    return $select_string;
}


/**
 * @since ZC v1.0.3
 */
function zen_get_geo_zone_name(string|int $geo_zone_id): string
{
    global $db;
    $zones = $db->Execute(
        "SELECT geo_zone_name
           FROM " . TABLE_GEO_ZONES . "
          WHERE geo_zone_id = " . (int)$geo_zone_id
    );

    if ($zones->EOF) {
        $geo_zone_name = (string)$geo_zone_id;
    } else {
        $geo_zone_name = $zones->fields['geo_zone_name'];
    }

    return $geo_zone_name;
}

/**
 * proxy into language class to get list of configured languages and their settings
 * @since ZC v1.0.3
 */
function zen_get_languages(): array
{
    /** @var language $lng */
    global $lng;
    if ($lng === null) {
        $lng = new language();
    }
    return array_values($lng->get_languages_by_code());
}

/**
 * @since ZC v1.1.0
 */
function zen_cfg_select_coupon_id(string $coupon_id, string $key = ''): string
{
    $coupon_array = [];
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $coupons = Coupon::getAllCouponsByName();
    $coupon_array[] = [
        'id' => '0',
        'text' => TEXT_NONE,
    ];

    foreach ($coupons as $coupon) {
        $coupon_array[] = [
            'id' => $coupon['coupon_id'],
            'text' => $coupon['coupon_name'],
        ];
    }

    return zen_draw_pull_down_menu($name, $coupon_array, $coupon_id, 'class="form-control"');
}

/**
 * @since ZC v1.0.3
 */
function zen_cfg_pull_down_country_list(string $country_id, string $key = ''): string
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, zen_get_countries_for_admin_pulldown(), $country_id, 'class="form-control"');
}

/**
 * @since ZC v1.1.1
 */
function zen_cfg_pull_down_country_list_none(string $country_id, string $key = ''): string
{
    $country_array = zen_get_countries_for_admin_pulldown(TEXT_NONE);
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, $country_array, $country_id, 'class="form-control"');
}

/**
 * @since ZC v1.0.3
 */
function zen_cfg_pull_down_zone_list(string $zone_id, string $key = ''): string
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $none = [['id' => 0, 'text' => TEXT_NONE]];
    $zones = zen_get_country_zones(zen_config('STORE_COUNTRY', ''));
    return zen_draw_pull_down_menu($name, array_merge($none, $zones), $zone_id, 'class="form-control"');
}

/**
 * @TODO - is there a tax class query function already?
 *
 * @since ZC v1.0.3
 */
function zen_cfg_pull_down_tax_classes(string $tax_class_id, string $key = ''): string
{
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $tax_class_array = [['id' => '0', 'text' => TEXT_NONE]];
    $tax_classes = $db->Execute(
        "SELECT tax_class_id AS `id`, tax_class_title AS `text`
           FROM " . TABLE_TAX_CLASS . "
          ORDER BY tax_class_title"
    );

    foreach ($tax_classes as $tax_class) {
        $tax_class_array[] = $tax_class;
    }

    return zen_draw_pull_down_menu($name, $tax_class_array, $tax_class_id, 'class="form-control"');
}

/**
 * @since ZC v1.0.3
 */
function zen_cfg_textarea(string $text, string $key = ''): string
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_textarea_field($name, false, 60, 5, htmlspecialchars($text, ENT_COMPAT, CHARSET, false), 'class="form-control"');
}

/**
 * @since ZC v1.1.0
 */
function zen_cfg_textarea_small(string $text, string $key = ''): string
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_textarea_field($name, false, 35, 1, htmlspecialchars($text, ENT_COMPAT, CHARSET, false), 'class="noEditor form-control"');
}

/**
 * @TODO - is there a zone lookup query already?
 *      There's zen_get_zone_name, but it requires a countries_id, too.
 *
 * @since ZC v1.0.3
 */
function zen_cfg_get_zone_name(string $zone_id): string
{
    global $db;
    $zone = $db->Execute(
        "SELECT zone_name
           FROM " . TABLE_ZONES . "
          WHERE zone_id = " . (int)$zone_id
    );

    if ($zone->EOF) {
        return $zone_id;
    } else {
        return $zone->fields['zone_name'];
    }
}

/**
 * @since ZC v1.3.6
 */
function zen_cfg_pull_down_htmleditors(string $html_editor, ?string $index = null): string
{
    global $editors_list;
    $name = $index ? 'configuration[' . $index . ']' : 'configuration_value';

    $editors_pulldown = [];
    foreach ($editors_list as $key => $value) {
        $editors_pulldown[] = ['id' => $key, 'text' => $value['desc']];
    }
    return zen_draw_pull_down_menu($name, $editors_pulldown, $html_editor, 'class="form-control"');
}

/**
 * @since ZC v1.5.5
 */
function zen_cfg_pull_down_exchange_rate_sources(string $source, string $key = ''): string
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $pulldown = [['id' => TEXT_NONE, 'text' => TEXT_NONE]];
    $funcs = get_defined_functions();
    $funcs = $funcs['user'];
    sort($funcs);
    foreach ($funcs as $func) {
        if (preg_match('/quote_(.*)_currency/', $func, $regs)) {
            $pulldown[] = ['id' => $regs[1], 'text' => $regs[1]];
        }
    }
    return zen_draw_pull_down_menu($name, $pulldown, $source);
}

/**
 * @since ZC v1.3.7
 */
function zen_cfg_password_input(string $value, string $key = ''): string
{
    return zen_draw_password_field('configuration[' . $key . ']', $value, false, 'class="form-control"');
}

/**
 * @since ZC v1.3.7
 */
function zen_cfg_password_display(string $value): string
{
    return str_repeat('*', (int)min(strlen($value), 16));
}

/**
 * @since ZC v1.0.3
 */
function zen_cfg_select_option(array $select_array, string $key_value, string $key = ''): string
{
    // -----
    // Display selections as a dropdown list if more than 2 selections to
    // reduce screen real-estate required.
    //
    if (count($select_array) > 2) {
        $dropdown_array = [];
        foreach ($select_array as $selection) {
            $dropdown_array[] = ['id' => $selection, 'text' => $selection];
        }
        return zen_cfg_select_drop_down($dropdown_array, $key_value, $key);
    }

    $string = '';
    foreach ($select_array as $selection) {
        $name = (!empty($key)) ? 'configuration[' . $key . ']' : 'configuration_value';
        $element_id = preg_replace('/[^a-z0-9_-]/', '-', strtolower($selection . '-' . $name));
        $string .= '<div class="radio"><label>' . zen_draw_radio_field($name, $selection, ($key_value === $selection), '', 'id="' . $element_id . '" class="inputSelect"') . $selection . '</label></div>';
    }

    return $string;
}

/**
 * @since ZC v1.2.0d
 */
function zen_cfg_select_drop_down(array $select_array, string $key_value, string $key = ''): string
{
    $name = (!empty($key)) ? 'configuration[' . $key . ']' : 'configuration_value';
    return zen_draw_pull_down_menu($name, $select_array, $key_value, 'class="form-control"');
}

/**
 * @since ZC v3.0.0
 */
function zen_render_config_set_function(string $set_function, string $configuration_value, string $configuration_key = ''): ?string
{
    $spec = zen_parse_config_set_function($set_function);
    if ($spec === null) {
        zen_record_admin_activity('Blocked invalid configuration set_function syntax.', 'warning');
        return null;
    }

    $renderers = zen_get_config_set_function_renderers();
    $function = $spec['function'];
    if (!isset($renderers[$function])) {
        zen_record_admin_activity('Blocked unregistered configuration set_function [' . preg_replace('/[^\w.\-]/', '*', $function) . '].', 'warning');
        return null;
    }

    $input_field = $renderers[$function]($spec, $configuration_value, $configuration_key);
    if ($configuration_key !== '' && strpos($input_field, 'configuration_value') !== false) {
        $input_field = preg_replace('/name=[\'"]configuration_value(\[\])?[\'"]/', 'name="configuration[' . $configuration_key . ']$1"', $input_field);
        $input_field = preg_replace('/id=[\'"]configuration_value[\'"]/', 'id="' . $configuration_key . '"', $input_field);
    }

    return $input_field;
}

/**
 * @since ZC v3.0.0
 */
function zen_get_config_set_function_renderers(): array
{
    $renderers = [
        'zen_cfg_select_coupon_id' => static fn(array $spec, string $value, string $key): string => zen_cfg_select_coupon_id($value, $key),
        'zen_cfg_pull_down_country_list' => static fn(array $spec, string $value, string $key): string => zen_cfg_pull_down_country_list($value, $key),
        'zen_cfg_pull_down_country_list_none' => static fn(array $spec, string $value, string $key): string => zen_cfg_pull_down_country_list_none($value, $key),
        'zen_cfg_pull_down_zone_list' => static fn(array $spec, string $value, string $key): string => zen_cfg_pull_down_zone_list($value, $key),
        'zen_cfg_pull_down_tax_classes' => static fn(array $spec, string $value, string $key): string => zen_cfg_pull_down_tax_classes($value, $key),
        'zen_cfg_pull_down_zone_classes' => static fn(array $spec, string $value, string $key): string => zen_cfg_pull_down_zone_classes($value, $key),
        'zen_cfg_pull_down_order_statuses' => static fn(array $spec, string $value, string $key): string => zen_cfg_pull_down_order_statuses($value, $key),
        'zen_cfg_textarea' => static fn(array $spec, string $value, string $key): string => zen_cfg_textarea($value, $key),
        'zen_cfg_textarea_small' => static fn(array $spec, string $value, string $key): string => zen_cfg_textarea_small($value, $key),
        'zen_cfg_pull_down_htmleditors' => static fn(array $spec, string $value, string $key): string => zen_cfg_pull_down_htmleditors($value, $key ?: null),
        'zen_cfg_pull_down_exchange_rate_sources' => static fn(array $spec, string $value, string $key): string => zen_cfg_pull_down_exchange_rate_sources($value, $key),
        'zen_cfg_password_input' => static fn(array $spec, string $value, string $key): string => zen_cfg_password_input($value, $key),
        'zen_cfg_select_option' => static fn(array $spec, string $value, string $key): string => zen_cfg_select_option(zen_config_set_function_choices($spec), $value, $key),
        'zen_cfg_select_drop_down' => static fn(array $spec, string $value, string $key): string => zen_cfg_select_drop_down(zen_config_set_function_choices($spec), $value, $key),
        'zen_cfg_select_multioption' => static fn(array $spec, string $value, string $key): string => zen_cfg_select_multioption(zen_config_set_function_choices($spec), $value, $key),
        'zen_cfg_select_multioption_pairs' => static fn(array $spec, string $value, string $key): string => zen_cfg_select_multioption_pairs(zen_config_set_function_choices($spec), $value, $key),
        'zen_cfg_read_only' => static fn(array $spec, string $value, string $key): string => zen_cfg_read_only($value, $key),
    ];

    $new_renderers = [];
    if (isset($GLOBALS['zco_notifier'])) {
        $GLOBALS['zco_notifier']->notify('NOTIFY_ADMIN_CONFIGURATION_SET_FUNCTION_RENDERERS', $renderers, $new_renderers);
    }
    if (is_array($new_renderers)) {
        $renderers += $new_renderers;
    }

    return array_filter($renderers, static fn($renderer): bool => is_callable($renderer));
}

/**
 * @since ZC v3.0.0
 */
function zen_parse_config_set_function(string $set_function): ?array
{
    $set_function = trim($set_function);
    if ($set_function === '') {
        return null;
    }

    if (str_starts_with($set_function, '{')) {
        try {
            $spec = json_decode($set_function, true, 4, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
        if (!is_array($spec) || !isset($spec['function']) || !is_string($spec['function'])) {
            return null;
        }
        if (!preg_match('/\A[A-Za-z_][A-Za-z0-9_]*\z/', $spec['function'])) {
            return null;
        }
        if (isset($spec['params']) && !is_array($spec['params'])) {
            return null;
        }
        $params = $spec['params'] ?? [];
        if (array_key_exists('choices', $spec)) {
            $params['choices'] = $spec['choices'];
        }
        return [
            'function' => $spec['function'],
            'params' => $params,
        ];
    }

    if (!preg_match('/\A\s*([A-Za-z_][A-Za-z0-9_]*)\s*\((.*)\z/s', $set_function, $matches)) {
        return null;
    }

    $remaining = trim($matches[2]);
    if ($remaining === '') {
        return [
            'function' => $matches[1],
            'params' => [],
        ];
    }

    $tokens = token_get_all('<?php ' . $remaining);
    $offset = 1;
    $choices = zen_parse_config_set_function_php_literal($tokens, $offset);
    if ($choices === zen_config_set_function_parse_failed() || !is_array($choices)) {
        return null;
    }

    $token = zen_config_set_function_next_token($tokens, $offset);
    if ($token !== ',') {
        return null;
    }
    while (($token = zen_config_set_function_next_token($tokens, $offset)) !== null) {
        if ($token !== ',') {
            return null;
        }
    }

    return [
        'function' => $matches[1],
        'params' => ['choices' => $choices],
    ];
}

/**
 * @since ZC v3.0.0
 */
function zen_config_set_function_choices(array $spec): array
{
    return is_array($spec['params']['choices'] ?? null) ? $spec['params']['choices'] : [];
}

/**
 * @since ZC v3.0.0
 */
function zen_parse_config_set_function_php_literal(array $tokens, int &$offset): mixed
{
    $token = zen_config_set_function_next_token($tokens, $offset);
    if ($token === '[') {
        return zen_parse_config_set_function_php_array($tokens, $offset, ']');
    }
    if (is_array($token) && $token[0] === T_ARRAY) {
        if (zen_config_set_function_next_token($tokens, $offset) !== '(') {
            return zen_config_set_function_parse_failed();
        }
        return zen_parse_config_set_function_php_array($tokens, $offset, ')');
    }
    if (is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
        return zen_config_set_function_decode_php_string($token[1]);
    }
    if (is_array($token) && $token[0] === T_LNUMBER) {
        return (int)$token[1];
    }
    if (is_array($token) && $token[0] === T_DNUMBER) {
        return (float)$token[1];
    }
    if (is_array($token) && $token[0] === T_STRING) {
        return match (strtolower($token[1])) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => zen_config_set_function_parse_failed(),
        };
    }
    return zen_config_set_function_parse_failed();
}

/**
 * @since ZC v3.0.0
 */
function zen_parse_config_set_function_php_array(array $tokens, int &$offset, string $close_token): ?array
{
    $array = [];
    while (true) {
        $token = zen_config_set_function_next_token($tokens, $offset);
        if ($token === $close_token) {
            return $array;
        }
        if ($token === null) {
            return null;
        }
        $offset--;

        $value = zen_parse_config_set_function_php_literal($tokens, $offset);
        if ($value === zen_config_set_function_parse_failed()) {
            return null;
        }
        $token = zen_config_set_function_next_token($tokens, $offset);
        if (is_array($token) && $token[0] === T_DOUBLE_ARROW) {
            $key = $value;
            if (!is_int($key) && !is_string($key)) {
                return null;
            }
            $value = zen_parse_config_set_function_php_literal($tokens, $offset);
            if ($value === zen_config_set_function_parse_failed()) {
                return null;
            }
            $array[$key] = $value;
            $token = zen_config_set_function_next_token($tokens, $offset);
        } else {
            $array[] = $value;
        }

        if ($token === $close_token) {
            return $array;
        }
        if ($token !== ',') {
            return null;
        }
    }
}

/**
 * @since ZC v3.0.0
 */
function zen_config_set_function_parse_failed(): object
{
    static $failed;

    return $failed ??= new stdClass();
}

/**
 * @since ZC v3.0.0
 */
function zen_config_set_function_next_token(array $tokens, int &$offset): mixed
{
    while ($offset < count($tokens)) {
        $token = $tokens[$offset++];
        if (is_array($token) && in_array($token[0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }
        return $token;
    }
    return null;
}

/**
 * @since ZC v3.0.0
 */
function zen_config_set_function_decode_php_string(string $string): string
{
    $quote = $string[0];
    $inner = substr($string, 1, -1);
    return $quote === "'" ? str_replace(["\\\\", "\\'"], ["\\", "'"], $inner) : stripcslashes($inner);
}

/**
 * @TODO: Is this still used?  It's nowhere in core.
 *
 * @since ZC v1.0.3
 */
function zen_mod_select_option(array $select_array, string $key_name, string $key_value): string
{
    $string = '';
    foreach ($select_array as $key => $value) {
        if (is_int($key)) {
            $key = $value;
        }
        $string .= '<div class="radio"><label>' . zen_draw_radio_field('configuration[' . $key_name . ']', $key, ($key_value == $key)) . $value . '</label></div>';
    }

    return $string;
}

/**
 * Collect server information
 *
 * @since ZC v1.0.3
 */
function zen_get_system_information(bool $privacy = false): array
{
    global $db;

    // determine database size stats
    $indsize = 0;
    $datsize = 0;
    $results = $db->Execute("SHOW TABLE STATUS" . (DB_PREFIX === '' ? '' : " LIKE '" . str_replace('_', '\_', DB_PREFIX) . "%'"));
    foreach ($results as $result) {
        $datsize += $result['Data_length'];
        $indsize += $result['Index_length'];
    }

    $result = $db->Execute("SHOW VARIABLES LIKE 'sql\_mode'");
    $mysql_mode = $result->fields['Value'] ?? '';
    $strictmysql = str_contains($mysql_mode, 'strict_');

    $mysql_slow_query_log_status = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'slow\_query\_log'");
    if (!$result->EOF) {
        $mysql_slow_query_log_status = '0';
        if (in_array($result->fields['Value'] ?? '', ['On', 'ON', '1',], false)) {
            $mysql_slow_query_log_status = '1';
        }
    }
    $result = $db->Execute("SHOW VARIABLES LIKE 'slow\_query\_log\_file'");
    $mysql_slow_query_log_file = $result->fields['Value'] ?? '';

    $result = $db->Execute("select now() as datetime");
    $mysql_date = $result->fields['datetime'] ?? '';

    $errnum = 0;
    $system = $host = $kernel = $output = '';
    $uptime = (zen_config('DISPLAY_SERVER_UPTIME') === 'true') ? 'Unsupported' : 'Disabled/Unavailable';

    // check to see if "exec()" is disabled in PHP -- if not, get additional info via command line
    $exec_disabled = false;
    $php_disabled_functions = @ini_get("disable_functions");
    if ($php_disabled_functions !== '') {
        if (in_array('exec', preg_split('/,/', str_replace(' ', '', $php_disabled_functions)))) {
            $exec_disabled = true;
        }
    }
    if (!$exec_disabled) {
        [$system, $host, $kernel] = ['', $_SERVER['SERVER_NAME'] ?? '', php_uname()];
        @exec('uname -a 2>&1', $output, $errnum);
        if ($errnum == 0 && count($output)) {
            [$system, $host, $kernel] = preg_split('/[\s,]+/', $output[0], 5);
        }
        $output = '';
        if (zen_config('DISPLAY_SERVER_UPTIME') === 'true') {
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
        'http_server' => $_SERVER['SERVER_SOFTWARE'] ?? '',
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
        unset($systemInfo['mysql_slow_query_log_file']);
    }

    return $systemInfo;
}

/**
 * @deprecated @v2.2.0 Moved to non-admin includes since v2.2.0 - Use $order->delete() instead.
 * @param int $order_id Contains the order number of the order to be deleted.
 * @param bool|string $restock Should the items within the order be restocked into inventory. (Old method used 'on', now can be set to true.)
 * @return void
 * @since ZC v1.0.3
*/
function zen_remove_order($order_id, $restock = false): void
{
    $order = new order($order_id);
    $order->delete($restock);
}

/**
 * @since ZC v1.0.3
 */
function zen_call_function(string $function, mixed $parameter, object|string $object = ''): mixed
{
    if ($object === '') {
        return $function($parameter);
    }

    return call_user_func([$object, $function], $parameter);
}

/**
 * @since ZC v3.0.0
 */
function zen_call_config_use_function(string $function, mixed $parameter, object|string $object = ''): mixed
{
    if ($object !== '') {
        if (!zen_is_valid_config_use_function_method($object, $function)) {
            zen_record_admin_activity('Blocked unregistered configuration use_function [' . preg_replace('/[^\w.\-]/', '*', get_class($object) . '->' . $function) . '].', 'warning');
            return $parameter;
        }

        return zen_call_function($function, $parameter, $object);
    }

    if (!zen_is_valid_config_use_function($function)) {
        zen_record_admin_activity('Blocked unregistered configuration use_function [' . preg_replace('/[^\w.\-]/', '*', $function) . '].', 'warning');
        return $parameter;
    }

    return zen_call_function($function, $parameter);
}

/**
 * @since ZC v3.0.0
 */
function zen_is_valid_config_use_function(string $function): bool
{
    $functions = [
        'zen_get_country_name',
        'zen_cfg_get_zone_name',
        'zen_cfg_password_display',
        'zen_get_tax_class_title',
        'zen_get_zone_class_title',
        'zen_get_order_status_name',
        'zen_get_orders_status_name',
    ];

    $new_functions = [];
    if (isset($GLOBALS['zco_notifier'])) {
        $GLOBALS['zco_notifier']->notify('NOTIFY_ADMIN_CONFIGURATION_USE_FUNCTIONS', $functions, $new_functions);
    }
    if (is_array($new_functions)) {
        foreach ($new_functions as $new_function) {
            if (is_string($new_function) && !in_array($new_function, $functions, true)) {
                $functions[] = $new_function;
            }
        }
    }

    return in_array($function, array_filter($functions, 'is_string'), true) && is_callable($function);
}

/**
 * Validates a class/method pair used for the object-based form of `use_function`
 * (e.g. `currencies->format`) before it's dispatched via zen_call_function(). The
 * class name and method both come from the `configuration` table, so an unchecked
 * dispatch here would let a compromised row invoke an arbitrary method on any
 * instantiated object.
 *
 * @since ZC v3.0.0
 */
function zen_is_valid_config_use_function_method(object $object, string $method): bool
{
    $methods = zen_get_config_use_function_class_methods();
    $class = get_class($object);

    return isset($methods[$class]) && in_array($method, $methods[$class], true) && is_callable([$object, $method]);
}

/**
 * Validates the class name in the object-based form of `use_function` (e.g.
 * `currencies->format`) BEFORE it's used to build an include path or instantiated.
 * The class name comes straight from the `configuration` table, so this must run
 * ahead of any `include`/`new $class()`, not just ahead of the method call --
 * otherwise a compromised row could still force an arbitrary file include (via
 * DIR_WS_CLASSES . $class . '.php', including path traversal) and run that
 * class's constructor even though the method call itself would later be blocked.
 *
 * @since ZC v3.0.0
 */
function zen_is_valid_config_use_function_class(string $class): bool
{
    if (!preg_match('/\A[A-Za-z_][A-Za-z0-9_]*\z/', $class)) {
        return false;
    }

    return array_key_exists($class, zen_get_config_use_function_class_methods());
}

/**
 * @since ZC v3.0.0
 */
function zen_get_config_use_function_class_methods(): array
{
    $methods = [
        'currencies' => ['format'],
    ];

    $new_methods = [];
    if (isset($GLOBALS['zco_notifier'])) {
        $GLOBALS['zco_notifier']->notify('NOTIFY_ADMIN_CONFIGURATION_USE_FUNCTION_METHODS', $methods, $new_methods);
    }
    if (is_array($new_methods)) {
        foreach ($new_methods as $new_class => $new_class_methods) {
            if (is_string($new_class) && is_array($new_class_methods)) {
                $methods[$new_class] = array_values(array_unique(array_merge(
                    $methods[$new_class] ?? [],
                    array_filter($new_class_methods, 'is_string')
                )));
            }
        }
    }

    return $methods;
}

/**
 * @todo - is there a function already for this query?
 *      There's zen_get_geo_zone_name, but it returns the input zone-id if not found.
 *
 * @since ZC v1.0.3
 */
function zen_get_zone_class_title(int|string $zone_class_id): string
{
    global $db;
    if ($zone_class_id == '0') {
        return TEXT_NONE;
    }

    $classes = $db->Execute(
        "SELECT geo_zone_name
           FROM " . TABLE_GEO_ZONES . "
          WHERE geo_zone_id = " . (int)$zone_class_id
    );
    return ($classes->EOF) ? '' : $classes->fields['geo_zone_name'];
}

/**
 * @todo - is there a function already for this query? See the one above
 *
 * @since ZC v1.0.3
 */
function zen_cfg_pull_down_zone_classes(string $zone_class_id, string $key = ''): string
{
    global $db;
    $name = ($key) ? 'configuration[' . $key . ']' : 'configuration_value';

    $zone_class_array = [['id' => '0', 'text' => TEXT_NONE]];
    $zone_classes = $db->Execute(
        "SELECT geo_zone_id AS `id`, geo_zone_name AS `text`
          FROM " . TABLE_GEO_ZONES . "
         ORDER BY geo_zone_name"
    );

    foreach ($zone_classes as $zone_class) {
        $zone_class_array[] = $zone_class;
    }

    return zen_draw_pull_down_menu($name, $zone_class_array, $zone_class_id, 'class="form-control"');
}

/**
 * @since ZC v1.0.3
 */
function zen_cfg_pull_down_order_statuses(string $order_status_id, string $key = ''): string
{
    $name = ($key) ? 'configuration[' . $key . ']' : 'configuration_value';
    return zen_draw_order_status_dropdown($name, $order_status_id, ['id' => 0, 'text' => TEXT_DEFAULT], 'class="form-control"');
}

/**
 * Return a pull-down menu of the available order-status values,
 * optionally prefixed by a "please choose" selection.
 * @since ZC v1.5.7
 */
function zen_draw_order_status_dropdown(string $field_name, $default_value, string|array $first_selection = '', $parms = ''): string
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
            'text' => "{$status['text']} [{$status['id']}]",
        ];
    }
    return zen_draw_pull_down_menu($field_name, $statuses_array, $default_value, $parms);
}


/**
 * @TODO - move to language class
 * Lookup Languages Icon by id or code
 * @param $lookup
 * @return bool|string
 * @since ZC v1.0.3
 */
function zen_get_language_icon(string|int $lookup): string
{
    global $db;
    $languages_icon = $db->Execute(
        "
        SELECT directory, image FROM " . TABLE_LANGUAGES . "
         WHERE languages_id = " . (int)$lookup . "
            OR code = '" . zen_db_input((string)$lookup) . "'
         LIMIT 1"
    );
    if ($languages_icon->EOF) {
        return '';
    }
    return zen_image(DIR_WS_CATALOG_LANGUAGES . $languages_icon->fields['directory'] . '/images/' . $languages_icon->fields['image'], $languages_icon->fields['directory']);
}


/**
 * lookup language directory name by id or code
 * @todo move to lang class
 *
 * @param $lookup
 * @return mixed|string
 * @since ZC v1.0.3
 */
function zen_get_language_name(string|int $lookup): string
{
    global $db;
    $check_language = $db->Execute(
        "SELECT directory FROM " . TABLE_LANGUAGES . "
          WHERE languages_id = " . (int)$lookup . "
             OR code = '" . zen_db_input((string)$lookup) . "'
          LIMIT 1"
    );

    if ($check_language->EOF) {
        return '';
    }
    return $check_language->fields['directory'];
}


/**
 * @since ZC v1.5.5
 */
function zen_get_configuration_group_value(int|string $lookup): int|string
{
    global $db;
    $r = $db->Execute(
        "SELECT configuration_group_title FROM " . TABLE_CONFIGURATION_GROUP .
        " WHERE configuration_group_id = " . (int)$lookup . " LIMIT 1"
    );
    return $r->EOF ? (int)$lookup : $r->fields['configuration_group_title'];
}


/**
 * Sets the status of a product review
 * @TODO move to a class
 * @todo DRY
 * @since ZC v1.2.0d
 */
function zen_set_reviews_status(int|string $review_id, mixed $status): int
{
    global $db;
    if ($status == '1') {
        $db->Execute(
            "UPDATE " . TABLE_REVIEWS . "
                SET status = 1
              WHERE reviews_id = " . (int)$review_id
        );
        return 1;
    }

    if ($status == '0') {
        $db->Execute(
            "UPDATE " . TABLE_REVIEWS . "
                SET status = 0
              WHERE reviews_id = " . (int)$review_id
        );
        return 1;
    }

    return -1;
}


/**
 * master category selection
 * @param int $product_id
 * @param bool $fullpath
 * @return array
 * @since ZC v1.2.0d
 */
function zen_get_master_categories_pulldown(int|string $product_id, bool $fullpath = false): array
{
    global $db;
    $master_category_array = [];
    $master_categories_query = $db->Execute(
        "SELECT ptc.products_id, cd.categories_name, cd.categories_id
           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = ptc.categories_id
          WHERE ptc.products_id = " . (int)$product_id . "
            AND cd.language_id = " . (int)$_SESSION['languages_id']
    );
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
 * Alias functions for Store configuration values in the Administration Tool
 * adapted from USPS-related contributions by Brad Waite and Fritz Clapp
 * @since ZC v1.2.0d
 */
function zen_cfg_select_multioption(array $choices_array, string $stored_value, string $config_key_name = ''): string
{
    $string = '';
    $name = ($config_key_name) ? 'configuration[' . $config_key_name . '][]' : 'configuration_value';
    $chosen_already = explode(', ', $stored_value);
    foreach ($choices_array as $value) {
        $ticked = in_array($value, $chosen_already, true);
        $string .= '<div class="checkbox"><label>' . zen_draw_checkbox_field($name, $value, $ticked, 'id="' . strtolower($value . '-' . $name) . '"') . $value . '</label></div>' . "\n";
    }
    $string .= zen_draw_hidden_field($name, '--none--');
    return $string;
}

/**
 * @since ZC v2.2.0
 */
function zen_cfg_select_multioption_pairs(array $choices_array, string $stored_value, string $config_key_name = ''): string
{
    $string = '';
    $name = (($config_key_name) ? 'configuration[' . $config_key_name . '][]' : 'configuration_value');
    $chosen_already = explode(", ", $stored_value);

    foreach ($choices_array as $value) {
        // Account for cases where an = sign is used to allow key->value pairs where the value is friendly display text
        $beforeEquals = strstr($value, '=', true);

        // this entry's checkbox should be pre-selected if the key matches
        $ticked = (in_array($value, $chosen_already, true) || in_array($beforeEquals, $chosen_already, true));

        // determine the value to show (the part after the =; if no =, just the whole string)
        $display_value = strpos($value, '=') !== false ? explode('=', $value, 2)[1] : $value;

        $string .= '<div class="checkbox"><label>' . zen_draw_checkbox_field($name, $value, $ticked, 'id="' . strtolower($value . '-' . $name) . '"') . $display_value . '</label></div>' . "\n";
    }

    $string .= zen_draw_hidden_field($name, '--none--');
    return $string;
}

/**
 * Function for configuration values that are read-only, e.g. a plugin's version number
 * @since ZC v1.5.8
 */
function zen_cfg_read_only(string $text, string $key = ''): string
{
    $name = (!empty($key)) ? 'configuration[' . $key . ']' : 'configuration_value';
    $text = htmlspecialchars_decode($text, ENT_COMPAT);

    return $text . zen_draw_hidden_field($name, $text);
}

/**
 * @TODO can this be merged with another pulldown, not specific to coupon admin?
 *      Similar to zen_cfg_pull_down_zone_classes, except for the way $selected is used.
 * @since ZC v1.3.6
 */
function zen_geo_zones_pull_down_coupon(string $parameters, $selected = ''): string
{
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $zones = $db->Execute(
        "SELECT geo_zone_id, geo_zone_name
           FROM " . TABLE_GEO_ZONES . "
          ORDER BY geo_zone_name"
    );

    if ($selected == 0) {
        $select_string .= '<option value="0" SELECTED>' . TEXT_NONE . '</option>';
    } else {
        $select_string .= '<option value="0">' . TEXT_NONE . '</option>';
    }

    foreach ($zones as $zone) {
        $select_string .= '<option value="' . $zone['geo_zone_id'] . '"';
        if ($selected == $zone['geo_zone_id']) {
            $select_string .= ' SELECTED';
        }
        $select_string .= '>' . $zone['geo_zone_name'] . '</option>';
    }
    $select_string .= '</select>';

    return $select_string;
}

/**
 * get first customer comment record for an order (usually contains their special instructions)
 * @since ZC v1.3.8
 */
function zen_get_orders_comments(int|string $orders_id): string
{
    global $db;
    $orders_comments_query =
        "SELECT osh.comments
           FROM " . TABLE_ORDERS_STATUS_HISTORY . " osh
          WHERE osh.orders_id = " . (int)$orders_id . "
          ORDER BY osh.orders_status_history_id
          LIMIT 1";
    $orders_comments = $db->Execute($orders_comments_query);
    return ($orders_comments->EOF) ? '' : (string)$orders_comments->fields['comments'];
}


/**
 * Toggle ezpage to specified status
 *
 * @param int $pages_id
 * @param int $status 0|1
 * @param string $status_field
 * @since ZC v1.3.0
 */
function zen_set_ezpage_status(int $pages_id, int $status, string $status_field): void
{
    global $db, $sniffer;

    // Use the $sniffer class to check if the field exists in the table
    if (!$sniffer->field_exists(TABLE_EZPAGES, $status_field)) {
        return; // invalid field, do not proceed
    }

    if ($status === 1 || $status === 0) {
        zen_record_admin_activity('EZ-Page ID ' . (int)$pages_id . ' [' . $status_field . '] changed to ' . $status, 'info');
        $db->Execute(
            "UPDATE " . TABLE_EZPAGES . "
                SET " . zen_db_input($status_field) . " = " . (int)$status . "
              WHERE pages_id = " . (int)$pages_id
        );
    }
}


/**
 * Retrieve a list of order-status names for a pulldown menu
 * @TODO Refactor code that is buiding this dropdown array inline, to use this function instead
 * @since ZC v1.5.8
 */
function zen_get_orders_status_pulldown_array()
{
    $ordersStatus = zen_getOrdersStatuses();
    return $ordersStatus['orders_statuses'];
}

/**
 * @since ZC v2.0.0
 */
function zen_getOrdersStatuses(bool $keyed = false): array
{
    global $db;
    $orders_statuses = [];
    $orders_status_array = [];
    $orders_status_colors = [];
    $orders_status_query = $db->Execute(
        "SELECT orders_status_id AS `id`, orders_status_name AS `name`, orders_status_color_code
           FROM " . TABLE_ORDERS_STATUS . "
          WHERE language_id = " . (int)$_SESSION['languages_id'] . "
          ORDER BY sort_order, orders_status_id"
    );
    foreach ($orders_status_query as $next_status) {
        $orders_status_colors[$next_status['id']] = $next_status['orders_status_color_code'];
        if (!$keyed) {
            $orders_statuses[] = [
                'id' => $next_status['id'],
                'text' => $next_status['name'] . ' [' . $next_status['id'] . ']',
            ];
            $orders_status_array[$next_status['id']] = $next_status['name'] . ' [' . $next_status['id'] . ']';
        } else {
            $orders_statuses[$next_status['id']] = $next_status['name'];
            $orders_status_array[$next_status['id']] = $next_status['name'];
        }
    }
    return ['orders_statuses' => $orders_statuses, 'orders_status_array' => $orders_status_array, 'orders_status_colors' => $orders_status_colors,];
}

/**
 * @since ZC v1.5.8
 */
function zen_get_customer_email_from_id(int|string $cid): string
{
    global $db;
    $query = $db->Execute("SELECT customers_email_address FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . (int)$cid);
    return ($query->EOF) ? '' : $query->fields['customers_email_address'];
}
