<?php
/**
 * init_sanitize
 *
 * @package initSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Mar 7 21:54:17 2014 +0000 Modified in v1.5.5 $
 */

require_once(DIR_WS_CLASSES . 'AdminRequestSanitizer.php');


if (!defined('DO_STRICT_SANITIZATION')) {
    DEFINE('DO_STRICT_SANITIZATION', true);
}

if (!isset($adminSanitizationConfig)) {
    $adminSanitizationConfig = array();
}
if (!isset($adminSanitizerTypes)) {
    $adminSanitizerTypes = array();
}

$adminSanitizerTypes = array_merge(array(
    'SIMPLE_ALPHANUM_PLUS' => array('type' => 'builtin', 'strict' => false),
    'CONVERT_INT' => array('type' => 'builtin', 'strict' => false),
    'FILE_DIR_REGEX' => array('type' => 'builtin', 'strict' => false),
    'ALPHANUM_DASH_UNDERSCORE' => array('type' => 'builtin', 'strict' => false),
    'WORDS_AND_SYMBOLS_REGEX' => array('type' => 'builtin', 'strict' => false),
    'META_TAGS' => array('type' => 'builtin', 'strict' => false),
    'SANITIZE_EMAIL' => array('type' => 'builtin', 'strict' => false),
    'PRODUCT_DESC_REGEX' => array('type' => 'builtin', 'strict' => false),
    'PRODUCT_URL_REGEX' => array('type' => 'builtin', 'strict' => false),
    'CURRENCY_VALUE_REGEX' => array('type' => 'builtin', 'strict' => false),
    'PRODUCT_NAME_DEEP_REGEX' => array('type' => 'builtin', 'strict' => false),
    'STRICT_SANITIZE_VALUES' => array('type' => 'builtin', 'strict' => true),
    'STRICT_SANITIZE_KEYS' => array('type' => 'builtin', 'strict' => true)
), $adminSanitizerTypes);

$sanitizer = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, DO_STRICT_SANITIZATION);

$group = array(
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
    'flagbanners_on_ssl',
    'flagbanners_open_new_windows',
    'gID',
    'gid',
    'global',
    'go_back',
    'id',
    'info',
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
    'selected_box',
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
    'zpage'
);
$sanitizer->addSanitizationGroup('SIMPLE_ALPHANUM_PLUS', $group);

$group = array(
    'current_master_categories_id',
    'categories_id',
    'cID',
    'pID',
    'attributes_id',
    'id',
    'padID',
    'coupon_uses_coupon',
    'coupon_uses_user',
    'coupon_zone_restriction'
);
$sanitizer->addSanitizationGroup('CONVERT_INT', $group);

$group = array('img_dir', 'products_previous_image', 'products_image_manual', 'products_attributes_filename');
$sanitizer->addSanitizationGroup('FILE_DIR_REGEX', $group);

$group = array(
    'handler',
    'type_name',
    'action',
    'product_attribute_is_free',
    'attributes_default',
    'attributes_price_base_included',
    'products_attribute_maxdays',
    'products_filter',
    'module',
    'page',
    'attribute_page',
    'cPath',
);
$sanitizer->addSanitizationGroup('ALPHANUM_DASH_UNDERSCORE', $group);

$group = array('title', 'coupon_name', 'banners_title', 'coupon_code', 'group_name', 'geo_zone_name', 'geo_zone_description',
               'tax_class_description', 'tax_class_title', 'tax_description', 'entry_company', 'customers_firstname',
               'customers_lastname', 'entry_street_address', 'entry_suburb', 'entry_city', 'entry_state', 'customers_referral',
               'symbol_left', 'symbol_right');
$sanitizer->addSanitizationGroup('WORDS_AND_SYMBOLS_REGEX', $group);

$group = array('metatags_title', 'metatags_keywords', 'metatags_description');
$sanitizer->addSanitizationGroup('META_TAGS', $group);

$group = array('customers_email_address');
$sanitizer->addSanitizationGroup('SANITIZE_EMAIL', $group);

$group = array('products_description', 'coupon_desc', 'file_contents', 'categories_description', 'message_html', 'banners_html_text'. 'pages_html_text', 'comments');
$sanitizer->addSanitizationGroup('PRODUCT_DESC_REGEX', $group);

$group = array('products_url');
$sanitizer->addSanitizationGroup('ALPHANUM_DASH_UNDERSCORE', $group);

$group = array('coupon_min_order');
$sanitizer->addSanitizationGroup('CURRENCY_VALUE_REGEX', $group);

$group = array('products_name', 'orders_status_name', 'configuration');
$sanitizer->addSanitizationGroup('PRODUCT_NAME_DEEP_REGEX', $group);

$group = array('configuration_value', 'configuration_key', 'search', 'query_string');
$sanitizer->addSanitizationGroup('STRICT_SANITIZE_VALUES', $group);

$group = array(); // $group is ignored for the following group
$sanitizer->addSanitizationGroup('STRICT_SANITIZE_KEYS', $group);

$sanitizer->runSanitizers();
