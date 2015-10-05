<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (isset($_GET['product_type'])) { 
  $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = '" . (int)$_GET['product_type'] . "'";
  $products_type_check = $db->Execute($sql); 
  if ($products_type_check->EOF) {
      unset($_GET['product_type']); 
  }
}
