<?php
/**
 * listing_display_order module 
 *
 * @package templateSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: listing_display_order.php 3012 2006-02-11 16:34:02Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (!isset($_GET['main_page']) || !zen_not_null($_GET['main_page'])) $_GET['main_page'] = 'index';
if (!isset($_GET['disp_order'])) {
  $_GET['disp_order'] = $disp_order_default;
  $disp_order = $disp_order_default;
} else {
  $disp_order = $_GET['disp_order'];
}

switch (true) {
  case ($_GET['disp_order'] == 0):
  // reset and let reset continue
  $_GET['disp_order'] = $disp_order_default;
  $disp_order = $disp_order_default;
  case ($_GET['disp_order'] == 1):
  $order_by = " order by pd.products_name";
  break;
  case ($_GET['disp_order'] == 2):
  $order_by = " order by pd.products_name DESC";
  break;
  case ($_GET['disp_order'] == 3):
  $order_by = " order by p.products_price_sorter, pd.products_name";
  break;
  case ($_GET['disp_order'] == 4):
  $order_by = " order by p.products_price_sorter DESC, pd.products_name";
  break;
  case ($_GET['disp_order'] == 5):
  $order_by = " order by p.products_model";
  break;
  case ($_GET['disp_order'] == 6):
  $order_by = " order by p.products_date_added DESC, pd.products_name";
  break;
  case ($_GET['disp_order'] == 7):
  $order_by = " order by p.products_date_added, pd.products_name";
  break;
  default:
  $order_by = " order by p.products_sort_order";
  break;
}
?>