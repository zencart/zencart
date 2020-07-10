<?php
/**
 * Header code file for the Account Notifications page
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 20 Modified in v1.5.7 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ACCOUNT_NOTIFICATION');

if (!zen_is_logged_in()) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

$global_query = "SELECT global_product_notifications
                 FROM   " . TABLE_CUSTOMERS_INFO . "
                 WHERE  customers_info_id = :customersID";

$global_query = $db->bindVars($global_query, ':customersID',$_SESSION['customer_id'], 'integer');
$global = $db->Execute($global_query);

if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
  if (isset($_POST['product_global']) && is_numeric($_POST['product_global'])) {
    $product_global = zen_db_prepare_input($_POST['product_global']);
  } else {
    $product_global = '0';
  }

  (array)$products = $_POST['notify'];

  if ($product_global != $global->fields['global_product_notifications']) {
    $product_global = (($global->fields['global_product_notifications'] == '1') ? '0' : '1');

    $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
            SET    global_product_notifications = :globalProductNotifications
            WHERE  customers_info_id = :customersID";

    $sql = $db->bindVars($sql, ':globalProductNotifications',$product_global, 'integer');
    $sql = $db->bindVars($sql, ':customersID',$_SESSION['customer_id'], 'integer');
    $db->Execute($sql);

  } elseif (!empty($products)) {
    $products_parsed = array();

    foreach ($products as $parse_entry) {
      if (is_numeric($parse_entry)) {
        $products_parsed[] = $parse_entry;
      }
    }

    if (sizeof($products_parsed) > 0) {
      $check_query = "SELECT count(*) AS total
                      FROM   " . TABLE_PRODUCTS_NOTIFICATIONS . "
                      WHERE  customers_id = :customersID
                      AND    products_id NOT IN (:productsParsed)";

      $check_query = $db->bindVars($check_query, ':customersID',$_SESSION['customer_id'], 'integer');
      $check_query = $db->bindVars($check_query, ':productsParsed',implode(',', $products_parsed), 'csv');
      $check = $db->Execute($check_query);

      if ($check->fields['total'] > 0) {
        $sql = "DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
                WHERE       customers_id = :customersID
                AND         products_id NOT IN (:productsParsed)";

        $sql = $db->bindVars($sql, ':customersID',$_SESSION['customer_id'], 'integer');
        $sql = $db->bindVars($sql, ':productsParsed',implode(',', $products_parsed), 'csv');
        $db->Execute($sql);
      }
    }
  } else {
    $check_query = "SELECT count(*) AS total
                    FROM   " . TABLE_PRODUCTS_NOTIFICATIONS . "
                    WHERE  customers_id = :customersID";

    $check_query = $db->bindVars($check_query, ':customersID',$_SESSION['customer_id'], 'integer');
    $check = $db->Execute($check_query);

    if ($check->fields['total'] > 0) {
      $sql = "DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
              WHERE       customers_id = :customersID";

      $sql = $db->bindVars($sql, ':customersID',$_SESSION['customer_id'], 'integer');
      $db->Execute($sql);
    }
  }

  $messageStack->add_session('account', SUCCESS_NOTIFICATIONS_UPDATED, 'success');

  zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

/*
$products_check_query = "SELECT count(*) AS total
                         FROM   " . TABLE_PRODUCTS_NOTIFICATIONS . "
                         WHERE  customers_id = :customersID";

$products_check_query = $db->bindVars($products_check_query, ':customersID',$_SESSION['customer_id'], 'integer');
$products_check = $db->Execute($products_check_query);
if ($products_check->fields['total'] > 0) $flag_products_check = true;
*/

$counter = 0;
$notificationsArray = array();
$products_query = "SELECT pd.products_id, pd.products_name
                   FROM   " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                          " . TABLE_PRODUCTS_NOTIFICATIONS . " pn
                   WHERE  pn.customers_id = :customersID
                   AND    pn.products_id = pd.products_id
                   AND    pd.language_id = :languagesID
                   ORDER BY pd.products_name";

$products_query = $db->bindVars($products_query, ':customersID',$_SESSION['customer_id'], 'integer');
$products_query = $db->bindVars($products_query, ':languagesID',$_SESSION['languages_id'], 'integer');
$products = $db->Execute($products_query);
while (!$products->EOF) {
  $notificationsArray[] = array('counter'=>$counter,
                                'products_id'=>$products->fields['products_id'],
                                'products_name'=>$products->fields['products_name']);
  $counter++;
  $products->MoveNext();
}
$flag_products_check = sizeof($notificationsArray);


$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT_NOTIFICATION');
?>
