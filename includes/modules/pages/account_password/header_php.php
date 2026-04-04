<?php
/**
 * Header code file for the Account Password page
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piloujp 2025 Jun 30 Modified in v2.2.0 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ACCOUNT_PASSWORD');

if (!zen_is_logged_in()) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
  $password_current = zen_db_prepare_input($_POST['password_current']);
  $password_new = zen_db_prepare_input($_POST['password_new']);
  $password_confirmation = zen_db_prepare_input($_POST['password_confirmation']);

  $error = false;

  if (mb_strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
    $error = true;

    $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR);
  } elseif ($password_new != $password_confirmation) {
    $error = true;

    $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
  }

  if ($error == false) {
    $check_customer_query = "SELECT customers_password, customers_nick
                             FROM   " . TABLE_CUSTOMERS . "
                             WHERE  customers_id = :customersID";

    $check_customer_query = $db->bindVars($check_customer_query, ':customersID',$_SESSION['customer_id'], 'integer');
    $check_customer = $db->Execute($check_customer_query);

    if (zen_validate_password($password_current, $check_customer->fields['customers_password'])) {
        $customer = new Customer();     //- Note, customer-id is set from session value
        $customer->setPassword($password_new);

      // handle 3rd-party integrations
      $zco_notifier->notify('NOTIFY_HEADER_ACCOUNT_PASSWORD_CHANGED', $_SESSION['customer_id'], $password_new, $check_customer->fields['customers_nick']);

      $messageStack->add_session('account', SUCCESS_PASSWORD_UPDATED, 'success');

      zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    } else {
      $error = true;

      $messageStack->add('account_password', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
    }
  }
}

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT_PASSWORD');
