<?php
/**
 * init_sanitize
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 04 Modified in v2.1.0-beta1 $
 */

use Zencart\Request\Request;

if (!defined('DO_STRICT_SANITIZATION')) {
    DEFINE('DO_STRICT_SANITIZATION', true);
}

if (!defined('DO_DEBUG_SANITIZATION')) {
    DEFINE('DO_DEBUG_SANITIZATION', false);
}

$sanitizer = AdminRequestSanitizer::getInstance();
$sanitizer->setDebug(DO_DEBUG_SANITIZATION);
$sanitizer->setDoStrictSanitization(DO_STRICT_SANITIZATION);

$adminSanitizerTypes = [
    'SIMPLE_ALPHANUM_PLUS' => ['type' => 'builtin'],
    'CONVERT_INT' => ['type' => 'builtin'],
    'FILE_DIR_REGEX' => ['type' => 'builtin'],
    'ALPHANUM_DASH_UNDERSCORE' => ['type' => 'builtin'],
    'WORDS_AND_SYMBOLS_REGEX' => ['type' => 'builtin'],
    'META_TAGS' => ['type' => 'builtin'],
    'SANITIZE_EMAIL' => ['type' => 'builtin'],
    'SANITIZE_EMAIL_AUDIENCE' => ['type' => 'builtin'],
    'PRODUCT_DESC_REGEX' => ['type' => 'builtin'],
    'PRODUCT_URL_REGEX' => ['type' => 'builtin'],
    'FILE_PATH_OR_URL' => ['type' => 'builtin'],
    'CURRENCY_VALUE_REGEX' => ['type' => 'builtin'],
    'FLOAT_VALUE_REGEX' => ['type' => 'builtin'],
    'PRODUCT_NAME_DEEP_REGEX' => ['type' => 'builtin'],
    'NULL_ACTION' => ['type' => 'builtin'],
    'MULTI_DIMENSIONAL' => ['type' => 'builtin'],
    'SIMPLE_ARRAY' => ['type' => 'builtin'],
    'STRICT_SANITIZE_VALUES' => ['type' => 'builtin'],
];

$sanitizer->addSanitizerTypes($adminSanitizerTypes);

$group = [
    'action',
    'add_products_id',
    'attribute_id',
    'attribute_page',
    'attributes_id',
    'banner',
    'bID',
    'box_name',
    'build_cat',
    'came_from',
    'categories_update_id',
    'cID',
    'cid',
    'configuration_key_lookup',
    'copy_attributes',
    'cpage',
    'cPath',
    'current_category_id',
    'current',
    'customer',
    'debug',
    'debug2',
    'debug3',
    'define_it',
    'download_reset_off',
    'download_reset_on',
    'end_date',
    'ezID',
    'fID',
    'filename',
    'flag',
    'flagbanners_open_new_windows',
    'gID',
    'gid',
    'global',
    'go_back',
    'id',
    'info',
    'inspect',
    'ipnID',
    'keepslashes',
    'layout_box_name',
    'lID',
    'list_order',
    'language',
    'lng_id',
    'lngdir',
    'mail_sent_to',
    'manual',
    'master_category',
    'mID',
    'mode',
    'module',
    'month',
    'na',
    'nID',
    'nogrants',
    'ns',
    'number_of_uploads',
    'oID',
    'oldaction',
    'option_id',
    'option_order_by',
    'option_page',
    'options_id_from',
    'options_id',
    'order_by',
    'order',
    'origin',
    'p',
    'padID',
    'page',
    'pages_id',
    'payment_status',
    'paypal_ipn_sort_order',
    'pID',
    'ppage',
    'product_type',
    'products_filter_name_model',
    'products_filter',
    'products_id',
    'products_options_id_all',
    'products_update_id',
    'profile',
    'ptID',
    'q',
    'read',
    'recip_count',
    'referral_code',
    'reports_page',
    'reset_categories_products_sort_order',
    'reset_editor',
    'reset_ez_sort_order',
    'reset_option_names_values_copier',
    'rID',
    's',
    'saction',
    'set',
    'set_display_categories_dropdown',
    'sID',
    'spage',
    'start_date',
    'status',
    't',
    'tID',
    'type',
    'uid',
    'update_action',
    'update_to',
    'user',
    'value_id',
    'value_page',
    'vcheck',
    'year',
    'za_lookup',
    'zID',
    'zone',
    'zpage',
];
$sanitizer->addSimpleSanitization('SIMPLE_ALPHANUM_PLUS', $group);

$group = [
    'current_master_categories_id',
    'categories_id',
    'cID',
    'pID',
    'attributes_id',
    'id',
    'padID',
    'coupon_id',
    'coupon_uses_coupon',
    'coupon_uses_user',
    'coupon_zone_restriction',
    'coupon_copy_to_count',
    'coupon_product_count',
    'coupon_calc_base',
    'coupon_order_limit',
    'coupon_is_valid_for_sales',
];
$sanitizer->addSimpleSanitization('CONVERT_INT', $group);

$group = ['img_dir', 'products_previous_image', 'products_image_manual', 'manufacturers_image_manual'];
$sanitizer->addSimpleSanitization('FILE_DIR_REGEX', $group);

$group = [
    'handler',
    'action',
    'oldaction',
    'product_attribute_is_free',
    'attributes_default',
    'attributes_price_base_included',
    'products_attribute_maxdays',
    'products_filter',
    'module',
    'page',
    'attribute_page',
    'cPath',
];
$sanitizer->addSimpleSanitization('ALPHANUM_DASH_UNDERSCORE', $group);

$group = [
    'pages_title', 'page_params', 'music_genre_name', 'artists_name', 'record_company_name', 'countries_name', 'name', 'type_name', 'manufacturers_name',
    'title', 'coupon_name', 'coupon_copy_to_dup_name', 'banners_title', 'coupon_code', 'coupon_delete_duplicate_code', 'coupon_type',
    'group_name', 'geo_zone_name', 'geo_zone_description',
    'tax_class_description', 'tax_class_title', 'tax_description', 'entry_company', 'customers_firstname',
    'customers_lastname', 'entry_street_address', 'entry_suburb', 'entry_city', 'entry_state', 'customers_referral',
    'symbol_left', 'symbol_right', 'products_model', 'alt_url', 'email_to_name', 'zone_page', 'search',
    'referrer_domain',
];
$sanitizer->addSimpleSanitization('WORDS_AND_SYMBOLS_REGEX', $group);

$group = ['metatags_title', 'metatags_keywords', 'metatags_description'];
$sanitizer->addSimpleSanitization('META_TAGS', $group);

$group = ['customers_email_address' => ['sanitizerType' => 'SANITIZE_EMAIL_AUDIENCE', 'method' => 'post', 'pages' => ['coupon_admin', 'gv_mail', 'mail']]];
$sanitizer->addComplexSanitization($group);

$group = ['customers_email_address', 'email_to'];
$sanitizer->addSimpleSanitization('SANITIZE_EMAIL', $group);

$group = ['products_description', 'coupon_desc', 'file_contents', 'categories_description', 'message_html', 'banners_html_text', 'pages_html_text', 'comments', 'products_options_comment'];
$sanitizer->addSimpleSanitization('PRODUCT_DESC_REGEX', $group);

$group = ['products_url', 'manufacturers_url'];
$sanitizer->addSimpleSanitization('PRODUCT_URL_REGEX', $group);

$group = ['products_attributes_filename'];
$sanitizer->addSimpleSanitization('FILE_PATH_OR_URL', $group);

$group = ['coupon_min_order'];
$sanitizer->addSimpleSanitization('CURRENCY_VALUE_REGEX', $group);

$group = ['categories_name', 'products_name', 'orders_status_name', 'configuration'];
$sanitizer->addSimpleSanitization('PRODUCT_NAME_DEEP_REGEX', $group);

$group = ['configuration_key', 'query_string'];
$sanitizer->addSimpleSanitization('STRICT_SANITIZE_VALUES', $group);

$group = ['configuration_key' => ['sanitizerType' => 'NULL_ACTION', 'method' => 'post', 'pages' => ['developers_tool_kit']]];
$sanitizer->addComplexSanitization($group);

// Determine correct treatment of configuration_value settings
if (!empty($_GET['cID'])) {
    $cID = (int)$_GET['cID'];

    $configs_with_special_characters = [
        'BREAD_CRUMBS_SEPARATOR',
        'BEST_SELLERS_FILLER',
        'CATEGORIES_SEPARATOR',
        'CATEGORIES_SEPARATOR_SUBS',
        'CATEGORIES_COUNT_PREFIX',
        'CATEGORIES_SUBCATEGORIES_INDENT',
        'EZPAGES_SEPARATOR_HEADER',
        'EZPAGES_SEPARATOR_FOOTER',
        'CURRENCIES_TRANSLATIONS',
        'STOCK_MARK_PRODUCT_OUT_OF_STOCK',
        'EMAIL_SMTPAUTH_PASSWORD',
        'CATEGORIES_COUNT_SUFFIX',
        'STORE_NAME_ADDRESS',
        'PRODUCT_LIST_SORT_ORDER_ASCENDING',
        'PRODUCT_LIST_SORT_ORDER_DESCENDING',
    ];
    $extra_configs_with_special_characters = false;
    $zco_notifier->notify('NOTIFY_ADMIN_CONFIGURATION_SPECIAL_CHARACTERS', [], $extra_configs_with_special_characters);
    if (is_array($extra_configs_with_special_characters)) {
        $configs_with_special_characters = $configs_with_special_characters + $extra_configs_with_special_characters;
    }
    $checks = $db->Execute("SELECT configuration_key, val_function FROM " . TABLE_CONFIGURATION . " WHERE configuration_id = " . (int)$cID);
    if (!$checks->EOF) {
        if (!empty($checks->fields['val_function'])) {
            $group = ['configuration_value' => ['sanitizerType' => 'NULL_ACTION', 'method' => 'post']];
        } elseif (in_array($checks->fields['configuration_key'], $configs_with_special_characters)) {
            $group = ['configuration_value' => ['sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX', 'method' => 'post']];
        }
        $sanitizer->addComplexSanitization($group);
    } else {
        $group = ['configuration_value'];
        $sanitizer->addSimpleSanitization('STRICT_SANITIZE_VALUES', $group);
    }
}

$group = ['report', 'startDate', 'endDate', 'filter'];
$sanitizer->addSimpleSanitization('FLOAT_VALUE_REGEX', $group);

$group = ['products_name' => ['sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX', 'method' => 'post', 'pages' => ['reviews']]];
$sanitizer->addComplexSanitization($group);

$group = ['query_string' => ['sanitizerType' => 'NULL_ACTION', 'method' => 'post', 'pages' => ['sqlpatch']]];
$sanitizer->addComplexSanitization($group);

$group = [
    'password' => ['sanitizerType' => 'NULL_ACTION', 'method' => 'post', 'pages' => ['admin_account', 'users']],
    'confirm' => ['sanitizerType' => 'NULL_ACTION', 'method' => 'post', 'pages' => ['admin_account', 'users']],
    'admin_pass' => ['sanitizerType' => 'NULL_ACTION', 'method' => 'post', 'pages' => ['login']],
    'newpassword' => ['sanitizerType' => 'NULL_ACTION', 'method' => 'post', 'pages' => ['customers']],
    'newpasswordConfirm' => ['sanitizerType' => 'NULL_ACTION', 'method' => 'post', 'pages' => ['customers']],
];
$sanitizer->addComplexSanitization($group);

$sanitizer->runSanitizers();

$sanitizedRequest = Request::capture();
