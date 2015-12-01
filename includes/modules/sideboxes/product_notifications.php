<?php
/**
 * product_notifications sidebox - displays a box inviting the customer to sign up for notifications of updates to current product
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: product_notifications.php 2993 2006-02-08 07:14:52Z birdbrain $
 */

// test if box should show
  $show_product_notifications = false;

  if (isset($_GET['products_id']) and zen_products_id_valid($_GET['products_id'])) {
    if (isset($_SESSION['customer_id'])) {
      $check_query = "select count(*) as count
                      from " . TABLE_CUSTOMERS_INFO . "
                      where customers_info_id = '" . (int)$_SESSION['customer_id'] . "'
                      and global_product_notifications = '1'";

      $check = $db->Execute($check_query);

      if ($check->fields['count'] <= 0) {
        $show_product_notifications= true;
      }
    } else {
      $show_product_notifications= true;
    }
  }

if ($show_product_notifications == true) {
  if (isset($_GET['products_id'])) {
    if (isset($_SESSION['customer_id'])) {
      $check_query = "select count(*) as count
                      from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                      where products_id = '" . (int)$_GET['products_id'] . "'
                      and customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $check = $db->Execute($check_query);

      $notification_exists = (($check->fields['count'] > 0) ? true : false);
    } else {
      $notification_exists = false;
    }

    if ($notification_exists == true) {
      require($template->get_template_dir('tpl_yes_notifications.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_yes_notifications.php');
    } else {
      require($template->get_template_dir('tpl_no_notifications.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_no_notifications.php');
    }
    $title =  BOX_HEADING_NOTIFICATIONS;
    $box_id = 'productnotifications';
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
}
?>