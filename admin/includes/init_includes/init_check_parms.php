<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (isset($_GET['product_type']) && (zen_get_handler_from_type((int)$_GET['product_type']) == -1)) {
      unset($_GET['product_type']); 
}
