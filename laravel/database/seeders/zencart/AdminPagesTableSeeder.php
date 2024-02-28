<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminPagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('admin_pages')->truncate();

        DB::table('admin_pages')->insert(array(
            0 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_ADMIN_ACCESS_LOGS',
                    'main_page' => 'FILENAME_ADMIN_ACTIVITY',
                    'menu_key' => 'access',
                    'page_key' => 'adminlogs',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            1 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_CATEGORIES_ATTRIBUTES_CONTROLLER',
                    'main_page' => 'FILENAME_ATTRIBUTES_CONTROLLER',
                    'menu_key' => 'catalog',
                    'page_key' => 'attributes',
                    'page_params' => '',
                    'sort_order' => 6,
                ),
            2 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_BANNER_MANAGER',
                    'main_page' => 'FILENAME_BANNER_MANAGER',
                    'menu_key' => 'tools',
                    'page_key' => 'banners',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            3 =>
                array(
                    'display_on_menu' => 'N',
                    'language_key' => 'BOX_CATALOG_CATEGORY',
                    'main_page' => 'FILENAME_CATEGORIES',
                    'menu_key' => 'catalog',
                    'page_key' => 'categories',
                    'page_params' => '',
                    'sort_order' => 18,
                ),
            4 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_CATEGORIES_PRODUCTS',
                    'main_page' => 'FILENAME_CATEGORY_PRODUCT_LISTING',
                    'menu_key' => 'catalog',
                    'page_key' => 'categoriesProductListing',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            5 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_ALL_LISTING',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configAllListing',
                    'page_params' => 'gID=23',
                    'sort_order' => 22,
                ),
            6 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_ATTRIBUTE_OPTIONS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configAttributes',
                    'page_params' => 'gID=13',
                    'sort_order' => 11,
                ),
            7 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_CREDIT_CARDS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configCreditCards',
                    'page_params' => 'gID=17',
                    'sort_order' => 16,
                ),
            8 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_CUSTOMER_DETAILS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configCustomerDetails',
                    'page_params' => 'gID=5',
                    'sort_order' => 5,
                ),
            9 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_DEFINE_PAGE_STATUS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configDefinePageStatus',
                    'page_params' => 'gID=25',
                    'sort_order' => 24,
                ),
            10 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_EMAIL_OPTIONS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configEmail',
                    'page_params' => 'gID=12',
                    'sort_order' => 10,
                ),
            11 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_EZPAGES_SETTINGS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configEzPagesSettings',
                    'page_params' => 'gID=30',
                    'sort_order' => 25,
                ),
            12 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_FEATURED_LISTING',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configFeaturedListing',
                    'page_params' => 'gID=22',
                    'sort_order' => 21,
                ),
            13 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_GV_COUPONS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configGvCoupons',
                    'page_params' => 'gID=16',
                    'sort_order' => 15,
                ),
            14 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_GZIP_COMPRESSION',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configGzipCompression',
                    'page_params' => 'gID=14',
                    'sort_order' => 12,
                ),
            15 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_IMAGES',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configImages',
                    'page_params' => 'gID=4',
                    'sort_order' => 4,
                ),
            16 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_INDEX_LISTING',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configIndexListing',
                    'page_params' => 'gID=24',
                    'sort_order' => 23,
                ),
            17 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_LAYOUT_SETTINGS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configLayoutSettings',
                    'page_params' => 'gID=19',
                    'sort_order' => 18,
                ),
            18 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_LOGGING',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configLogging',
                    'page_params' => 'gID=10',
                    'sort_order' => 9,
                ),
            19 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_MAXIMUM_VALUES',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configMaximumValues',
                    'page_params' => 'gID=3',
                    'sort_order' => 3,
                ),
            20 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_MINIMUM_VALUES',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configMinimumValues',
                    'page_params' => 'gID=2',
                    'sort_order' => 2,
                ),
            21 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_MY_STORE',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configMyStore',
                    'page_params' => 'gID=1',
                    'sort_order' => 1,
                ),
            22 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_NEW_LISTING',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configNewListing',
                    'page_params' => 'gID=21',
                    'sort_order' => 20,
                ),
            23 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_PRODUCT_INFO',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configProductInfo',
                    'page_params' => 'gID=18',
                    'sort_order' => 17,
                ),
            24 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_PRODUCT_LISTING',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configProductListing',
                    'page_params' => 'gID=8',
                    'sort_order' => 7,
                ),
            25 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_REGULATIONS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configRegulations',
                    'page_params' => 'gID=11',
                    'sort_order' => 14,
                ),
            26 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_SESSIONS',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configSessions',
                    'page_params' => 'gID=15',
                    'sort_order' => 13,
                ),
            27 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_SHIPPING_PACKAGING',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configShipping',
                    'page_params' => 'gID=7',
                    'sort_order' => 6,
                ),
            28 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_STOCK',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configStock',
                    'page_params' => 'gID=9',
                    'sort_order' => 8,
                ),
            29 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CONFIGURATION_WEBSITE_MAINTENANCE',
                    'main_page' => 'FILENAME_CONFIGURATION',
                    'menu_key' => 'configuration',
                    'page_key' => 'configWebsiteMaintenance',
                    'page_params' => 'gID=20',
                    'sort_order' => 19,
                ),
            30 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TAXES_COUNTRIES',
                    'main_page' => 'FILENAME_COUNTRIES',
                    'menu_key' => 'taxes',
                    'page_key' => 'countries',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            31 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_COUPON_ADMIN',
                    'main_page' => 'FILENAME_COUPON_ADMIN',
                    'menu_key' => 'gv',
                    'page_key' => 'couponAdmin',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            32 =>
                array(
                    'display_on_menu' => 'N',
                    'language_key' => 'BOX_COUPON_RESTRICT',
                    'main_page' => 'FILENAME_COUPON_RESTRICT',
                    'menu_key' => 'gv',
                    'page_key' => 'couponRestrict',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            33 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_LOCALIZATION_CURRENCIES',
                    'main_page' => 'FILENAME_CURRENCIES',
                    'menu_key' => 'localization',
                    'page_key' => 'currencies',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            34 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CUSTOMERS_CUSTOMER_GROUPS',
                    'main_page' => 'FILENAME_CUSTOMER_GROUPS',
                    'menu_key' => 'customers',
                    'page_key' => 'customerGroups',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            35 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CUSTOMERS_CUSTOMERS',
                    'main_page' => 'FILENAME_CUSTOMERS',
                    'menu_key' => 'customers',
                    'page_key' => 'customers',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            36 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_DEFINE_PAGES_EDITOR',
                    'main_page' => 'FILENAME_DEFINE_PAGES_EDITOR',
                    'menu_key' => 'tools',
                    'page_key' => 'definePagesEditor',
                    'page_params' => '',
                    'sort_order' => 12,
                ),
            37 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_DEVELOPERS_TOOL_KIT',
                    'main_page' => 'FILENAME_DEVELOPERS_TOOL_KIT',
                    'menu_key' => 'tools',
                    'page_key' => 'developersToolKit',
                    'page_params' => '',
                    'sort_order' => 10,
                ),
            38 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_CATEGORIES_ATTRIBUTES_DOWNLOADS_MANAGER',
                    'main_page' => 'FILENAME_DOWNLOADS_MANAGER',
                    'menu_key' => 'catalog',
                    'page_key' => 'downloads',
                    'page_params' => '',
                    'sort_order' => 7,
                ),
            39 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_EZPAGES',
                    'main_page' => 'FILENAME_EZPAGES_ADMIN',
                    'menu_key' => 'tools',
                    'page_key' => 'ezpages',
                    'page_params' => '',
                    'sort_order' => 11,
                ),
            40 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_FEATURED',
                    'main_page' => 'FILENAME_FEATURED',
                    'menu_key' => 'catalog',
                    'page_key' => 'featured',
                    'page_params' => '',
                    'sort_order' => 13,
                ),
            41 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TAXES_GEO_ZONES',
                    'main_page' => 'FILENAME_GEO_ZONES',
                    'menu_key' => 'taxes',
                    'page_key' => 'geoZones',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            42 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CUSTOMERS_GROUP_PRICING',
                    'main_page' => 'FILENAME_GROUP_PRICING',
                    'menu_key' => 'customers',
                    'page_key' => 'groupPricing',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            43 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_GV_ADMIN_MAIL',
                    'main_page' => 'FILENAME_GV_MAIL',
                    'menu_key' => 'gv',
                    'page_key' => 'gvMail',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            44 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_GV_ADMIN_QUEUE',
                    'main_page' => 'FILENAME_GV_QUEUE',
                    'menu_key' => 'gv',
                    'page_key' => 'gvQueue',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
            45 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_GV_ADMIN_SENT',
                    'main_page' => 'FILENAME_GV_SENT',
                    'menu_key' => 'gv',
                    'page_key' => 'gvSent',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            46 =>
                array(
                    'display_on_menu' => 'N',
                    'language_key' => 'BOX_CUSTOMERS_INVOICE',
                    'main_page' => 'FILENAME_ORDERS_INVOICE',
                    'menu_key' => 'customers',
                    'page_key' => 'invoice',
                    'page_params' => '',
                    'sort_order' => 5,
                ),
            47 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_LOCALIZATION_LANGUAGES',
                    'main_page' => 'FILENAME_LANGUAGES',
                    'menu_key' => 'localization',
                    'page_key' => 'languages',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
            48 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_LAYOUT_CONTROLLER',
                    'main_page' => 'FILENAME_LAYOUT_CONTROLLER',
                    'menu_key' => 'tools',
                    'page_key' => 'layoutController',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
            49 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_MAIL',
                    'main_page' => 'FILENAME_MAIL',
                    'menu_key' => 'tools',
                    'page_key' => 'mail',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            50 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_MANUFACTURERS',
                    'main_page' => 'FILENAME_MANUFACTURERS',
                    'menu_key' => 'catalog',
                    'page_key' => 'manufacturers',
                    'page_params' => '',
                    'sort_order' => 10,
                ),
            51 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_MEDIA_MANAGER',
                    'main_page' => 'FILENAME_MEDIA_MANAGER',
                    'menu_key' => 'extras',
                    'page_key' => 'mediaManager',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            52 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_MEDIA_TYPES',
                    'main_page' => 'FILENAME_MEDIA_TYPES',
                    'menu_key' => 'extras',
                    'page_key' => 'mediaTypes',
                    'page_params' => '',
                    'sort_order' => 5,
                ),
            53 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_MUSIC_GENRE',
                    'main_page' => 'FILENAME_MUSIC_GENRE',
                    'menu_key' => 'extras',
                    'page_key' => 'musicGenre',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            54 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_NEWSLETTER_MANAGER',
                    'main_page' => 'FILENAME_NEWSLETTERS',
                    'menu_key' => 'tools',
                    'page_key' => 'newsletters',
                    'page_params' => '',
                    'sort_order' => 5,
                ),
            55 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_CATEGORIES_OPTIONS_NAME_MANAGER',
                    'main_page' => 'FILENAME_OPTIONS_NAME_MANAGER',
                    'menu_key' => 'catalog',
                    'page_key' => 'optionNames',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            56 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_PRODUCT_OPTIONS_NAME',
                    'main_page' => 'FILENAME_PRODUCTS_OPTIONS_NAME',
                    'menu_key' => 'catalog',
                    'page_key' => 'optionNameSorter',
                    'page_params' => '',
                    'sort_order' => 8,
                ),
            57 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_CATEGORIES_OPTIONS_VALUES_MANAGER',
                    'main_page' => 'FILENAME_OPTIONS_VALUES_MANAGER',
                    'menu_key' => 'catalog',
                    'page_key' => 'optionValues',
                    'page_params' => '',
                    'sort_order' => 5,
                ),
            58 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_PRODUCT_OPTIONS_VALUES',
                    'main_page' => 'FILENAME_PRODUCTS_OPTIONS_VALUES',
                    'menu_key' => 'catalog',
                    'page_key' => 'optionValueSorter',
                    'page_params' => '',
                    'sort_order' => 9,
                ),
            59 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CUSTOMERS_ORDERS',
                    'main_page' => 'FILENAME_ORDERS',
                    'menu_key' => 'customers',
                    'page_key' => 'orders',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
            60 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_LOCALIZATION_ORDERS_STATUS',
                    'main_page' => 'FILENAME_ORDERS_STATUS',
                    'menu_key' => 'localization',
                    'page_key' => 'ordersStatus',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            61 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_MODULES_ORDER_TOTAL',
                    'main_page' => 'FILENAME_MODULES',
                    'menu_key' => 'modules',
                    'page_key' => 'orderTotal',
                    'page_params' => 'set=ordertotal',
                    'sort_order' => 3,
                ),
            62 =>
                array(
                    'display_on_menu' => 'N',
                    'language_key' => 'BOX_CUSTOMERS_PACKING_SLIP',
                    'main_page' => 'FILENAME_ORDERS_PACKINGSLIP',
                    'menu_key' => 'customers',
                    'page_key' => 'packingslip',
                    'page_params' => '',
                    'sort_order' => 6,
                ),
            63 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_ADMIN_ACCESS_PAGE_REGISTRATION',
                    'main_page' => 'FILENAME_ADMIN_PAGE_REGISTRATION',
                    'menu_key' => 'access',
                    'page_key' => 'pageRegistration',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            64 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_MODULES_PAYMENT',
                    'main_page' => 'FILENAME_MODULES',
                    'menu_key' => 'modules',
                    'page_key' => 'payment',
                    'page_params' => 'set=payment',
                    'sort_order' => 1,
                ),
            65 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CUSTOMERS_PAYPAL',
                    'main_page' => 'FILENAME_PAYPAL',
                    'menu_key' => 'customers',
                    'page_key' => 'paypal',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            66 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_MODULES_PLUGINS',
                    'main_page' => 'FILENAME_PLUGIN_MANAGER',
                    'menu_key' => 'modules',
                    'page_key' => 'plugins',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            67 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_PRODUCTS_PRICE_MANAGER',
                    'main_page' => 'FILENAME_PRODUCTS_PRICE_MANAGER',
                    'menu_key' => 'catalog',
                    'page_key' => 'priceManager',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            68 =>
                array(
                    'display_on_menu' => 'N',
                    'language_key' => 'BOX_CATALOG_PRODUCT',
                    'main_page' => 'FILENAME_PRODUCT',
                    'menu_key' => 'catalog',
                    'page_key' => 'product',
                    'page_params' => '',
                    'sort_order' => 16,
                ),
            69 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_PRODUCTS_EXPECTED',
                    'main_page' => 'FILENAME_PRODUCTS_EXPECTED',
                    'menu_key' => 'catalog',
                    'page_key' => 'productsExpected',
                    'page_params' => '',
                    'sort_order' => 15,
                ),
            70 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_PRODUCTS_TO_CATEGORIES',
                    'main_page' => 'FILENAME_PRODUCTS_TO_CATEGORIES',
                    'menu_key' => 'catalog',
                    'page_key' => 'productsToCategories',
                    'page_params' => '',
                    'sort_order' => 17,
                ),
            71 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_PRODUCT_TYPES',
                    'main_page' => 'FILENAME_PRODUCT_TYPES',
                    'menu_key' => 'catalog',
                    'page_key' => 'productTypes',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
            72 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_ADMIN_ACCESS_PROFILES',
                    'main_page' => 'FILENAME_PROFILES',
                    'menu_key' => 'access',
                    'page_key' => 'profiles',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            73 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_RECORD_ARTISTS',
                    'main_page' => 'FILENAME_RECORD_ARTISTS',
                    'menu_key' => 'extras',
                    'page_key' => 'recordArtists',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            74 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_RECORD_COMPANY',
                    'main_page' => 'FILENAME_RECORD_COMPANY',
                    'menu_key' => 'extras',
                    'page_key' => 'recordCompanies',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
            75 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_REPORTS_ORDERS_TOTAL',
                    'main_page' => 'FILENAME_STATS_CUSTOMERS',
                    'menu_key' => 'reports',
                    'page_key' => 'reportCustomers',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            76 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_REPORTS_PRODUCTS_LOWSTOCK',
                    'main_page' => 'FILENAME_STATS_PRODUCTS_LOWSTOCK',
                    'menu_key' => 'reports',
                    'page_key' => 'reportLowStock',
                    'page_params' => '',
                    'sort_order' => 3,
                ),
            77 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_REPORTS_PRODUCTS_PURCHASED',
                    'main_page' => 'FILENAME_STATS_PRODUCTS_PURCHASED',
                    'menu_key' => 'reports',
                    'page_key' => 'reportProductsSold',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            78 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_REPORTS_PRODUCTS_VIEWED',
                    'main_page' => 'FILENAME_STATS_PRODUCTS_VIEWED',
                    'menu_key' => 'reports',
                    'page_key' => 'reportProductsViewed',
                    'page_params' => '',
                    'sort_order' => 5,
                ),
            79 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_REPORTS_CUSTOMERS_REFERRALS',
                    'main_page' => 'FILENAME_STATS_CUSTOMERS_REFERRALS',
                    'menu_key' => 'reports',
                    'page_key' => 'reportReferrals',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
            80 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_REVIEWS',
                    'main_page' => 'FILENAME_REVIEWS',
                    'menu_key' => 'catalog',
                    'page_key' => 'reviews',
                    'page_params' => '',
                    'sort_order' => 11,
                ),
            81 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_SALEMAKER',
                    'main_page' => 'FILENAME_SALEMAKER',
                    'menu_key' => 'catalog',
                    'page_key' => 'salemaker',
                    'page_params' => '',
                    'sort_order' => 14,
                ),
            82 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_SERVER_INFO',
                    'main_page' => 'FILENAME_SERVER_INFO',
                    'menu_key' => 'tools',
                    'page_key' => 'server',
                    'page_params' => '',
                    'sort_order' => 6,
                ),
            83 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_MODULES_SHIPPING',
                    'main_page' => 'FILENAME_MODULES',
                    'menu_key' => 'modules',
                    'page_key' => 'shipping',
                    'page_params' => 'set=shipping',
                    'sort_order' => 2,
                ),
            84 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_CATALOG_SPECIALS',
                    'main_page' => 'FILENAME_SPECIALS',
                    'menu_key' => 'catalog',
                    'page_key' => 'specials',
                    'page_params' => '',
                    'sort_order' => 12,
                ),
            85 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_SQLPATCH',
                    'main_page' => 'FILENAME_SQLPATCH',
                    'menu_key' => 'tools',
                    'page_key' => 'sqlPatch',
                    'page_params' => '',
                    'sort_order' => 13,
                ),
            86 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_STORE_MANAGER',
                    'main_page' => 'FILENAME_STORE_MANAGER',
                    'menu_key' => 'tools',
                    'page_key' => 'storeManager',
                    'page_params' => '',
                    'sort_order' => 9,
                ),
            87 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TAXES_TAX_CLASSES',
                    'main_page' => 'FILENAME_TAX_CLASSES',
                    'menu_key' => 'taxes',
                    'page_key' => 'taxClasses',
                    'page_params' => '',
                    'sort_order' => 4,
                ),
            88 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TAXES_TAX_RATES',
                    'main_page' => 'FILENAME_TAX_RATES',
                    'menu_key' => 'taxes',
                    'page_key' => 'taxRates',
                    'page_params' => '',
                    'sort_order' => 5,
                ),
            89 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_TEMPLATE_SELECT',
                    'main_page' => 'FILENAME_TEMPLATE_SELECT',
                    'menu_key' => 'tools',
                    'page_key' => 'templateSelect',
                    'page_params' => '',
                    'sort_order' => 1,
                ),
            90 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_ADMIN_ACCESS_USERS',
                    'main_page' => 'FILENAME_USERS',
                    'menu_key' => 'access',
                    'page_key' => 'users',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
            91 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TOOLS_WHOS_ONLINE',
                    'main_page' => 'FILENAME_WHOS_ONLINE',
                    'menu_key' => 'tools',
                    'page_key' => 'whosOnline',
                    'page_params' => '',
                    'sort_order' => 7,
                ),
            92 =>
                array(
                    'display_on_menu' => 'Y',
                    'language_key' => 'BOX_TAXES_ZONES',
                    'main_page' => 'FILENAME_ZONES',
                    'menu_key' => 'taxes',
                    'page_key' => 'zones',
                    'page_params' => '',
                    'sort_order' => 2,
                ),
        ));


    }
}
