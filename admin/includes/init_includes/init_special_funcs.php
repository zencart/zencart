<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_special_funcs.php 3001 2006-02-09 21:45:06Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// set a default time limit
  zen_set_time_limit(GLOBAL_SET_TIME_LIMIT);

// auto activate and expire banners
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'banner.php');
  zen_activate_banners();
  zen_expire_banners();

// auto expire special products
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'specials.php');
  zen_start_specials();
  zen_expire_specials();

// auto expire featured products
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'featured.php');
  zen_start_featured();
  zen_expire_featured();

// auto expire salemaker sales
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'salemaker.php');
  zen_start_salemaker();
  zen_expire_salemaker();

?>