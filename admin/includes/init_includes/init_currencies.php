<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_currencies.php 3001 2006-02-09 21:45:06Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.currencies.php');
  $currencies = new currencies();
  $_SESSION['currency'] = DEFAULT_CURRENCY;
  if (isset($_GET['pID'])) {
    $at_product_info_array = productPricing::buildPricingResultSet($_GET['pID']);
    $at_product_info = productPricing::factory($at_product_info_array, true);
  }
?>