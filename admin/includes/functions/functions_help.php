<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Aug 10 Modified in v1.5.7a $
 */

function page_has_help()
{
    global $PHP_SELF;

    $page = basename($PHP_SELF, '.php');

    $configuration_pagelist = array(
      1 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_mystore', 
      2 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_minimumvalues', 
      3 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_maximumvalues', 
      4 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_images', 
      5 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_customerdetails', 
      // 6 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_moduleoptions', 
      7 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_shippingpackaging', 
      8 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_productlisting', 
      9 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_stock', 
      10 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_logging', 
      11 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_regulations', 
      12 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_emailoptions', 
      13 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_attributesettings', 
      14 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_gzipcompression', 
      15 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_sessions', 
      16 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_gvcoupons', 
      17 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_creditcards', 
      18 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_productinfo', 
      19 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_layoutsettings', 
      20 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_websitemaintenance', 
      21 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_newlisting', 
      22 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_featuredlisting', 
      23 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_alllisting', 
      24 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_indexlisting', 
      25 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_definepagestatus', 
      30 => 'https://docs.zen-cart.com/user/admin_pages/configuration/configuration_ezpagessettings', 
    ); 

    if ($page == FILENAME_CONFIGURATION) {
      $fallback = 'https://docs.zen-cart.com/user/admin_pages/configuration/'; 

      if (isset($_GET['gID'])) { 
         $gID = (int)$_GET['gID']; 
         if ($gID == 6) return false; // No help for hidden config page
         if (isset($configuration_pagelist[$gID])) {
            return $configuration_pagelist[$gID];
         }
      }
      return $fallback; 
    }

    // Go to correct help file - category or product level. 
    if ($page == FILENAME_CATEGORY_PRODUCT_LISTING) {
       $show_category_help = false; 
       if (!isset($_GET['cPath'])) {
          $show_category_help = true; 
       } else {
          $cPath_array = zen_parse_category_path($_GET['cPath']);
          $cPath = implode('_', $cPath_array);
          $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
          if (zen_products_in_category_count($current_category_id, true, false) == 0) {
             $show_category_help = true; 
          }
       }
       if ($show_category_help) {
           return 'https://docs.zen-cart.com/user/admin_pages/catalog/categories/';
       } else {
           return 'https://docs.zen-cart.com/user/admin_pages/catalog/categories_products/';
       }
    }

    // These pages have subpages - point to correct helpfile 
    if ($page == FILENAME_PRODUCT_TYPES) {
       if (isset($_GET['action']) && $_GET['action'] == 'layout') {
          return 'https://docs.zen-cart.com/user/admin_pages/catalog/product_types_edit_layout/'; 
       }
    }
    if ($page == FILENAME_CATEGORIES) {
       if (isset($_GET['action']) && $_GET['action'] == 'edit_category_meta_tags') {
          return 'https://docs.zen-cart.com/user/admin_pages/catalog/categories_meta_tags_editor/'; 
       }
    }
    if ($page == FILENAME_PRODUCT) {
       if (isset($_GET['action']) && $_GET['action'] == 'new_product_meta_tags') {
          return 'https://docs.zen-cart.com/user/admin_pages/catalog/products_meta_tags_editor/'; 
       }
    }

    $pagelist = array(
        FILENAME_CONFIGURATION => 'https://docs.zen-cart.com/user/admin_pages/configuration/',
        FILENAME_CATEGORIES => 'https://docs.zen-cart.com/user/admin_pages/catalog/categories/',
        FILENAME_CATEGORY_PRODUCT_LISTING => 'https://docs.zen-cart.com/user/admin_pages/catalog/categories_products/',
        FILENAME_PRODUCT_TYPES => 'https://docs.zen-cart.com/user/admin_pages/catalog/product_types/',
        FILENAME_PRODUCTS_PRICE_MANAGER => 'https://docs.zen-cart.com/user/admin_pages/catalog/products_price_manager/',
        FILENAME_OPTIONS_NAME_MANAGER => 'https://docs.zen-cart.com/user/admin_pages/catalog/option_name_manager/',
        FILENAME_OPTIONS_VALUES_MANAGER => 'https://docs.zen-cart.com/user/admin_pages/catalog/option_value_manager/',
        FILENAME_ATTRIBUTES_CONTROLLER => 'https://docs.zen-cart.com/user/admin_pages/catalog/attributes_controller/',
        FILENAME_DOWNLOADS_MANAGER => 'https://docs.zen-cart.com/user/admin_pages/catalog/downloads_manager/',
        FILENAME_PRODUCTS_OPTIONS_NAME => 'https://docs.zen-cart.com/user/admin_pages/catalog/option_name_sorter/',
        FILENAME_PRODUCTS_OPTIONS_VALUES => 'https://docs.zen-cart.com/user/admin_pages/catalog/option_value_sorter/',
        FILENAME_MANUFACTURERS => 'https://docs.zen-cart.com/user/admin_pages/catalog/manufacturers/',
        FILENAME_REVIEWS => 'https://docs.zen-cart.com/user/admin_pages/catalog/reviews/',
        FILENAME_SPECIALS => 'https://docs.zen-cart.com/user/admin_pages/catalog/specials/',
        FILENAME_FEATURED => 'https://docs.zen-cart.com/user/admin_pages/catalog/featured/',
        FILENAME_SALEMAKER => 'https://docs.zen-cart.com/user/admin_pages/catalog/salemaker/',
        FILENAME_PRODUCTS_EXPECTED => 'https://docs.zen-cart.com/user/admin_pages/catalog/products_expected/',
        FILENAME_PRODUCT => 'https://docs.zen-cart.com/user/products/product_management_admin/', 
        FILENAME_PRODUCTS_TO_CATEGORIES => 'https://docs.zen-cart.com/user/admin_pages/catalog/products_to_categories/',
        FILENAME_MODULES => 'https://docs.zen-cart.com/user/admin_pages/modules/',
        FILENAME_PLUGIN_MANAGER=> 'https://docs.zen-cart.com/user/admin_pages/modules/plugin_manager',
        FILENAME_CUSTOMERS => 'https://docs.zen-cart.com/user/admin_pages/customers/customers/',
        FILENAME_ORDERS => 'https://docs.zen-cart.com/user/admin_pages/customers/orders/',
        FILENAME_GROUP_PRICING => 'https://docs.zen-cart.com/user/admin_pages/customers/group_pricing/',
        FILENAME_PAYPAL => '',
        FILENAME_ORDERS_INVOICE => 'https://docs.zen-cart.com/user/admin_pages/customers/orders_invoice/',
        FILENAME_ORDERS_PACKINGSLIP => 'https://docs.zen-cart.com/user/admin_pages/customers/orders_packingslip/',
        FILENAME_COUNTRIES => 'https://docs.zen-cart.com/user/admin_pages/locations/countries/',
        FILENAME_ZONES => 'https://docs.zen-cart.com/user/admin_pages/locations/zones/',
        FILENAME_GEO_ZONES => 'https://docs.zen-cart.com/user/admin_pages/locations/zones_definitions/',
        FILENAME_TAX_CLASSES => 'https://docs.zen-cart.com/user/admin_pages/locations/tax_classes/',
        FILENAME_TAX_RATES => 'https://docs.zen-cart.com/user/admin_pages/locations/tax_rates/',
        FILENAME_CURRENCIES => 'https://docs.zen-cart.com/user/admin_pages/localization/currencies/',
        FILENAME_LANGUAGES => 'https://docs.zen-cart.com/user/admin_pages/localization/languages/',
        FILENAME_ORDERS_STATUS => 'https://docs.zen-cart.com/user/admin_pages/localization/orders_status/',
        FILENAME_STATS_CUSTOMERS => 'https://docs.zen-cart.com/user/admin_pages/reports/customer_orders_total/',
        FILENAME_STATS_CUSTOMERS_REFERRALS => 'https://docs.zen-cart.com/user/admin_pages/reports/customers_referral/',
        FILENAME_STATS_PRODUCTS_LOWSTOCK => 'https://docs.zen-cart.com/user/admin_pages/reports/products_low_stock/',
        FILENAME_STATS_PRODUCTS_PURCHASED => 'https://docs.zen-cart.com/user/admin_pages/reports/products_purchased/',
        FILENAME_STATS_PRODUCTS_VIEWED => 'https://docs.zen-cart.com/user/admin_pages/reports/products_viewed/',
        FILENAME_TEMPLATE_SELECT => 'https://docs.zen-cart.com/user/admin_pages/tools/template_selection/',
        FILENAME_LAYOUT_CONTROLLER => 'https://docs.zen-cart.com/user/admin_pages/tools/layout_boxes_controller/',
        FILENAME_BANNER_MANAGER => 'https://docs.zen-cart.com/user/admin_pages/tools/banner_manager/',
        FILENAME_MAIL => 'https://docs.zen-cart.com/user/admin_pages/tools/send_email/',
        FILENAME_NEWSLETTERS => 'https://docs.zen-cart.com/user/admin_pages/tools/newsletter/',
        FILENAME_SERVER_INFO => 'https://docs.zen-cart.com/user/admin_pages/tools/server_info/',
        FILENAME_WHOS_ONLINE => 'https://docs.zen-cart.com/user/admin_pages/tools/whos_online/',
        FILENAME_STORE_MANAGER => 'https://docs.zen-cart.com/user/admin_pages/tools/store_manager/',
        FILENAME_DEVELOPERS_TOOL_KIT => 'https://docs.zen-cart.com/user/admin_pages/tools/developers_tool_kit/',
        FILENAME_EZPAGES_ADMIN => 'https://docs.zen-cart.com/user/admin_pages/tools/ezpages/',
        FILENAME_DEFINE_PAGES_EDITOR => 'https://docs.zen-cart.com/user/admin_pages/tools/define_pages/',
        FILENAME_SQLPATCH => 'https://docs.zen-cart.com/user/admin_pages/tools/install_sql_patches/',
        FILENAME_COUPON_ADMIN => 'https://docs.zen-cart.com/user/admin_pages/discounts/coupon_admin/',
        FILENAME_COUPON_RESTRICT => 'https://docs.zen-cart.com/user/admin_pages/discounts/coupon_restrictions/',
        FILENAME_GV_QUEUE => 'https://docs.zen-cart.com/user/admin_pages/discounts/gift_certificate_queue/',
        FILENAME_GV_MAIL => 'https://docs.zen-cart.com/user/admin_pages/discounts/send_gift_certificate/',
        FILENAME_GV_SENT => 'https://docs.zen-cart.com/user/admin_pages/discounts/gift_certificates_sent/',
        FILENAME_PROFILES => 'https://docs.zen-cart.com/user/admin_pages/admins/admin_profiles/',
        FILENAME_USERS => 'https://docs.zen-cart.com/user/admin_pages/admins/admin_users/',
        FILENAME_ADMIN_PAGE_REGISTRATION => 'https://docs.zen-cart.com/user/admin_pages/admins/admin_page_registration/',
        FILENAME_ADMIN_ACTIVITY => 'https://docs.zen-cart.com/user/admin_pages/admins/admin_activity_logs/',
        FILENAME_RECORD_ARTISTS => 'https://docs.zen-cart.com/user/admin_pages/extras/record_artists/',
        FILENAME_RECORD_COMPANY => 'https://docs.zen-cart.com/user/admin_pages/extras/record_companies/',
        FILENAME_MUSIC_GENRE => 'https://docs.zen-cart.com/user/admin_pages/extras/music_genre/',
        FILENAME_MEDIA_MANAGER => 'https://docs.zen-cart.com/user/admin_pages/extras/media_manager/',
        FILENAME_MEDIA_TYPES => 'https://docs.zen-cart.com/user/admin_pages/extras/media_types/',
        FILENAME_STATS_SALES_REPORT_GRAPHS => 'https://docs.zen-cart.com/user/admin_pages/reports/graphical_sales_report/',
    );

    if (isset($pagelist[$page])) {
        return $pagelist[$page];
    }
    return false;
}
