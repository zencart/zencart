<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// set a default time limit
zen_set_time_limit(GLOBAL_SET_TIME_LIMIT);

// auto activate and expire banners
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'banner.php';
zen_activate_banners();
zen_expire_banners();

// -----
// Functions are still needed for specials/featured/salemaker tools'
// processing!
//
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'specials.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'featured.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'salemaker.php';

/**
 * only process once per session do not include banners as banners expire per click as well as per date
 * require the banner functions, auto-activate and auto-expire.
 *
 * this is processed in the admin for dates that expire while being worked on
 */
// check if a reset on one time sessions settings should occur due to the midnight hour happening
if (!isset($_SESSION['today_is'])) {
    $_SESSION['today_is'] = date('Y-m-d');
}

if ($_SESSION['today_is'] !== date('Y-m-d')) {
    $_SESSION['today_is'] = date('Y-m-d');
    $_SESSION['expirationsNeedUpdate'] = true;
}

if (!isset($_SESSION['expirationsNeedUpdate']) || $_SESSION['expirationsNeedUpdate'] === true) {
    // auto expire special products
    zen_start_specials();
    zen_expire_specials();

    // auto expire featured products
    zen_start_featured();
    zen_expire_featured();

    // auto expire salemaker sales
    zen_start_salemaker();
    zen_expire_salemaker();

    $_SESSION['expirationsNeedUpdate'] = false;
}
