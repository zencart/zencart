<?php
/**
 * load the system wide functions
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2020 Apr 27 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
/**
 * require the whos online functions and update
 */
require DIR_WS_FUNCTIONS . 'whos_online.php';
zen_update_whos_online();
/**
 * require the banner functions, auto-activate and auto-expire
 */
require DIR_WS_FUNCTIONS . 'banner.php';
zen_activate_banners();
zen_expire_banners();
/**
 * require product functions one time such that if previously loaded will not cause an error here.
 */
require_once DIR_WS_FUNCTIONS . 'functions_products.php';
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
if ($_SESSION['today_is'] != date('Y-m-d')) {
    $_SESSION['today_is'] = date('Y-m-d');
    $_SESSION['updateExpirations'] = false;
}
if (!isset($_SESSION['updateExpirations']) || $_SESSION['updateExpirations'] !== true) {
    /**
     * enable disabled product that have a historical products_available_date to become active
     *   in advance of other product handlers to prepare the product for use in those handlers.
     */
    if (defined('ENABLE_DISABLED_UPCOMING_PRODUCT') && ENABLE_DISABLED_UPCOMING_PRODUCT == 'Automatic') {
        zen_enable_disabled_upcoming();
    }
    /**
     * require the specials products functions, auto-activate and auto-expire
     */
    require DIR_WS_FUNCTIONS . 'specials.php';
    zen_start_specials();
    zen_expire_specials();
    /**
     * require the featured products functions, auto-activate and auto-expire
     */
    require DIR_WS_FUNCTIONS . 'featured.php';
    zen_start_featured();
    zen_expire_featured();
    /**
     * require the salemaker functions, auto-activate and auto-expire
     */
    require DIR_WS_FUNCTIONS . 'salemaker.php';
    zen_start_salemaker();
    zen_expire_salemaker();

    $_SESSION['updateExpirations'] = true;
}
