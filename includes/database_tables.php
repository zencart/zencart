<?php
/**
 * database_tables.php
 * Defines the database table names used in the project
 *
 * @package initSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: database_tables.php 16976 2010-07-24 23:33:45Z kuroi $
 * @private
 */

if (!defined('DB_PREFIX')) define('DB_PREFIX', '');
define('TABLE_ADDRESS_BOOK', DB_PREFIX . 'address_book');
define('TABLE_ADDRESS_FORMAT', DB_PREFIX . 'address_format');
define('TABLE_ADMIN', DB_PREFIX . 'admin');
define('TABLE_ADMIN_ACTIVITY_LOG', DB_PREFIX . 'admin_activity_log');
define('TABLE_ADMIN_MENUS', DB_PREFIX . 'admin_menus');
define('TABLE_ADMIN_PAGES', DB_PREFIX . 'admin_pages');
define('TABLE_ADMIN_PAGES_TO_PROFILES', DB_PREFIX . 'admin_pages_to_profiles');
define('TABLE_ADMIN_PROFILES', DB_PREFIX . 'admin_profiles');
define('TABLE_AUTHORIZENET', DB_PREFIX . 'authorizenet');
define('TABLE_BANNERS', DB_PREFIX . 'banners');
define('TABLE_BANNERS_HISTORY', DB_PREFIX . 'banners_history');
define('TABLE_CATEGORIES', DB_PREFIX . 'categories');
define('TABLE_CATEGORIES_DESCRIPTION', DB_PREFIX . 'categories_description');
define('TABLE_CONFIGURATION', DB_PREFIX . 'configuration');
define('TABLE_CONFIGURATION_GROUP', DB_PREFIX . 'configuration_group');
define('TABLE_COUNTER', DB_PREFIX . 'counter');
define('TABLE_COUNTER_HISTORY', DB_PREFIX . 'counter_history');
define('TABLE_COUNTRIES', DB_PREFIX . 'countries');
define('TABLE_COUPON_GV_QUEUE', DB_PREFIX . 'coupon_gv_queue');
define('TABLE_COUPON_GV_CUSTOMER', DB_PREFIX . 'coupon_gv_customer');
define('TABLE_COUPON_EMAIL_TRACK', DB_PREFIX . 'coupon_email_track');
define('TABLE_COUPON_REDEEM_TRACK', DB_PREFIX . 'coupon_redeem_track');
define('TABLE_COUPON_RESTRICT', DB_PREFIX . 'coupon_restrict');
define('TABLE_COUPONS', DB_PREFIX . 'coupons');
define('TABLE_COUPONS_DESCRIPTION', DB_PREFIX . 'coupons_description');
define('TABLE_CURRENCIES', DB_PREFIX . 'currencies');
define('TABLE_CUSTOMERS', DB_PREFIX . 'customers');
define('TABLE_CUSTOMERS_BASKET', DB_PREFIX . 'customers_basket');
define('TABLE_CUSTOMERS_BASKET_ATTRIBUTES', DB_PREFIX . 'customers_basket_attributes');
define('TABLE_CUSTOMERS_INFO', DB_PREFIX . 'customers_info');
define('TABLE_DB_CACHE', DB_PREFIX . 'db_cache');
define('TABLE_EMAIL_ARCHIVE', DB_PREFIX . 'email_archive');
define('TABLE_EZPAGES', DB_PREFIX . 'ezpages');
define('TABLE_FEATURED', DB_PREFIX . 'featured');
define('TABLE_FILES_UPLOADED', DB_PREFIX . 'files_uploaded');
define('TABLE_GROUP_PRICING', DB_PREFIX . 'group_pricing');
define('TABLE_GET_TERMS_TO_FILTER', DB_PREFIX . 'get_terms_to_filter');
define('TABLE_LANGUAGES', DB_PREFIX . 'languages');
define('TABLE_LAYOUT_BOXES', DB_PREFIX . 'layout_boxes');
define('TABLE_MANUFACTURERS', DB_PREFIX . 'manufacturers');
define('TABLE_MANUFACTURERS_INFO', DB_PREFIX . 'manufacturers_info');
define('TABLE_META_TAGS_PRODUCTS_DESCRIPTION', DB_PREFIX . 'meta_tags_products_description');
define('TABLE_METATAGS_CATEGORIES_DESCRIPTION', DB_PREFIX . 'meta_tags_categories_description');
define('TABLE_NEWSLETTERS', DB_PREFIX . 'newsletters');
define('TABLE_ORDERS', DB_PREFIX . 'orders');
define('TABLE_ORDERS_PRODUCTS', DB_PREFIX . 'orders_products');
define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', DB_PREFIX . 'orders_products_attributes');
define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', DB_PREFIX . 'orders_products_download');
define('TABLE_ORDERS_STATUS', DB_PREFIX . 'orders_status');
define('TABLE_ORDERS_STATUS_HISTORY', DB_PREFIX . 'orders_status_history');
define('TABLE_ORDERS_TYPE', DB_PREFIX . 'orders_type');
define('TABLE_ORDERS_TOTAL', DB_PREFIX . 'orders_total');
define('TABLE_PAYPAL', DB_PREFIX . 'paypal');
define('TABLE_PAYPAL_SESSION', DB_PREFIX . 'paypal_session');
define('TABLE_PAYPAL_PAYMENT_STATUS', DB_PREFIX . 'paypal_payment_status');
define('TABLE_PAYPAL_PAYMENT_STATUS_HISTORY', DB_PREFIX . 'paypal_payment_status_history');
define('TABLE_PRODUCTS', DB_PREFIX . 'products');
define('TABLE_PRODUCT_TYPES', DB_PREFIX . 'product_types');
define('TABLE_PRODUCT_TYPE_LAYOUT', DB_PREFIX . 'product_type_layout');
define('TABLE_PRODUCT_TYPES_TO_CATEGORY', DB_PREFIX . 'product_types_to_category');
define('TABLE_PRODUCTS_ATTRIBUTES', DB_PREFIX . 'products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', DB_PREFIX . 'products_attributes_download');
define('TABLE_PRODUCTS_DESCRIPTION', DB_PREFIX . 'products_description');
define('TABLE_PRODUCTS_DISCOUNT_QUANTITY', DB_PREFIX . 'products_discount_quantity');
define('TABLE_PRODUCTS_NOTIFICATIONS', DB_PREFIX . 'products_notifications');
define('TABLE_PRODUCTS_OPTIONS', DB_PREFIX . 'products_options');
define('TABLE_PRODUCTS_OPTIONS_VALUES', DB_PREFIX . 'products_options_values');
define('TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS', DB_PREFIX . 'products_options_values_to_products_options');
define('TABLE_PRODUCTS_OPTIONS_TYPES', DB_PREFIX . 'products_options_types');
define('TABLE_PRODUCTS_TO_CATEGORIES', DB_PREFIX . 'products_to_categories');
define('TABLE_PROJECT_VERSION', DB_PREFIX . 'project_version');
define('TABLE_PROJECT_VERSION_HISTORY', DB_PREFIX . 'project_version_history');
define('TABLE_QUERY_BUILDER', DB_PREFIX . 'query_builder');
define('TABLE_REVIEWS', DB_PREFIX . 'reviews');
define('TABLE_REVIEWS_DESCRIPTION', DB_PREFIX . 'reviews_description');
define('TABLE_SALEMAKER_SALES', DB_PREFIX . 'salemaker_sales');
define('TABLE_SESSIONS', DB_PREFIX . 'sessions');
define('TABLE_SPECIALS', DB_PREFIX . 'specials');
define('TABLE_TEMPLATE_SELECT', DB_PREFIX . 'template_select');
define('TABLE_TAX_CLASS', DB_PREFIX . 'tax_class');
define('TABLE_TAX_RATES', DB_PREFIX . 'tax_rates');
define('TABLE_GEO_ZONES', DB_PREFIX . 'geo_zones');
define('TABLE_ZONES_TO_GEO_ZONES', DB_PREFIX . 'zones_to_geo_zones');
define('TABLE_UPGRADE_EXCEPTIONS', DB_PREFIX . 'upgrade_exceptions');
define('TABLE_WISHLIST', DB_PREFIX . 'customers_wishlist');
define('TABLE_WHOS_ONLINE', DB_PREFIX . 'whos_online');
define('TABLE_ZONES', DB_PREFIX . 'zones');
