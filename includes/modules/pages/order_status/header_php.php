<?php
/**
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: J_Schilz for Integrated COWOA - 2007
 * @version $Id: JT of GTI Custom Modified for Integrated COWOA 02-July-2010
 * @version $Id: Integrated COWOA v2.2 - 2007 - 2012
 * @version $Id: New in V1.6.0 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ORDER_STATUS');

if (!isset($_POST['order_id']) || (isset($_POST['order_id']) && !is_numeric($_POST['order_id']))) {
  $errorInvalidID=TRUE;
}
if(!isset($_POST['query_email_address']) || zen_validate_email($_POST['query_email_address']) == false) {
  $errorInvalidEmail=TRUE;
}
if(!$errorInvalidID && !$errorInvalidEmail)
{

  $customer_info_query = "SELECT customers_email_address, customers_id
                          FROM   " . TABLE_ORDERS . "
                          WHERE  orders_id = :ordersID";

  $customer_info_query = $db->bindVars($customer_info_query, ':ordersID', $_POST['order_id'], 'integer');
  $customer_info = $db->Execute($customer_info_query);

  if (isset($_POST['query_email_address']) && $customer_info->fields['customers_email_address'] != $_POST['query_email_address']) {
    $errorNoMatch=TRUE;
  } else {
    $statuses_query = "SELECT os.orders_status_name, osh.date_added, osh.comments, osh.customer_notified
                       FROM   " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh
                       WHERE      osh.orders_id = :ordersID
                       AND        osh.orders_status_id = os.orders_status_id
                       AND        os.language_id = :languagesID
                       AND        osh.customer_notified >= 0
                       ORDER BY   osh.date_added";

    $statuses_query = $db->bindVars($statuses_query, ':ordersID', $_POST['order_id'], 'integer');
    $statuses_query = $db->bindVars($statuses_query, ':languagesID', $_SESSION['languages_id'], 'integer');
    $statuses = $db->Execute($statuses_query);

    while (!$statuses->EOF) {

      $statusArray[] = array('date_added'=>$statuses->fields['date_added'],
                             'orders_status_name'=>$statuses->fields['orders_status_name'],
                             'comments'=>$statuses->fields['comments']);

      $statuses->MoveNext();
    }

    require(DIR_WS_CLASSES . 'order.php');
    $order = new order($_POST['order_id']);
  }
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));


// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ORDER_STATUS');
