<?php
/**
 * Initializes non-database constants that were previously set in language or template files,
 * overridable via site-specific /init_includes processing.  See
 * /includes/init_includes/dist-init_site_specific_non_db_settings.php.
 *
 * Note: These settings apply to both the storefront and the admin!
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 26 Modified in v2.1.0-alpha2 $
 */
// -----
// If the site has provided a set of overrides for these base values, they will
// be used.
//
$site_specific_non_db_settings = [];
if (is_file(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'init_includes/init_site_specific_non_db_settings.php')) {
    require DIR_FS_CATALOG . DIR_WS_INCLUDES . 'init_includes/init_site_specific_non_db_settings.php';
}

$non_db_settings = [
    // -----
    // Storefront settings.
    //
    'CART_SHIPPING_METHOD_ZIP_REQUIRED' => 'true',  //- Either 'true' or 'false'.  Used by tpl_modules_shipping_estimator.php

    'ORDER_STATUS_DISPLAY_PAYMENT' => 'true',       //- Either 'true' or 'false'. Used by tpl_order_status_default.php
    'ORDER_STATUS_DISPLAY_SHIPPING' => 'true',      //- "
    'ORDER_STATUS_DISPLAY_PRODUCTS' => 'true',      //- "
    'ORDER_STATUS_SLAM_COUNT' => '3',               //- A numeric string (defaults to '3'). Used by order_status/header_php.php

    // Shared - Storefront and Admin 
    'TOPMOST_CATEGORY_PARENT_ID' => '0',

    // -----
    // Admin settings.
    //
    'MAX_DISPLAY_RESTRICT_ENTRIES' => 10,           //- Note, an integer value!.  Used by /admin/coupon_restrict.php
    'WARN_DATABASE_VERSION_PROBLEM' => 'true',      //- Either 'true' or 'false'.  Used by /admin/init_includes/init_errors.php
];
$non_db_settings = array_merge($non_db_settings, $site_specific_non_db_settings);

foreach ($non_db_settings as $key => $value) {
    zen_define_default($key, $value);
}
