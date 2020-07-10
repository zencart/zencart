<?php
/**
 * Time out page
 *
 * @package page
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Wed Feb 19 15:57:35 2014 +0000 Modified in v1.5.3 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_LOGIN');
$zco_notifier->notify('NOTIFY_HEADER_START_LOGIN_TIMEOUT');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));


$error = false;
if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
  $email_address = zen_db_prepare_input($_POST['email_address']);
  $password = zen_db_prepare_input($_POST['password']);

  if ((!isset($_SESSION['securityToken']) || !isset($_POST['securityToken'])) || ($_SESSION['securityToken'] !== $_POST['securityToken'])) {
    $error = true;
    $messageStack->add('login', ERROR_SECURITY_ERROR);
  } else {

    // Check if email exists
      $check_customer_query = "SELECT customers_id, customers_firstname, customers_lastname, customers_password,
                                  customers_email_address, customers_default_address_id,
                                  customers_authorization, customers_referral
                               FROM " . TABLE_CUSTOMERS . "
                               WHERE customers_email_address = :emailAddress";

      $check_customer_query  =$db->bindVars($check_customer_query, ':emailAddress', $email_address, 'string');
      $check_customer = $db->Execute($check_customer_query);

      if (!$check_customer->RecordCount()) {
        $error = true;
      $messageStack->add('login', TEXT_LOGIN_ERROR);
      } else {
        $newPassword = $check_customer->fields['customers_password'];
        // Check that password is good
        if (!zen_validate_password($password, $newPassword)) {
          $error = true;
          $messageStack->add('login', TEXT_LOGIN_ERROR);
        } else {
          if (password_needs_rehash($newPassword, PASSWORD_DEFAULT)) {
            $newPassword = zcPassword::getInstance(PHP_VERSION)->updateNotLoggedInCustomerPassword($password, $email_address);
          }
          if (SESSION_RECREATE == 'True') {
            zen_session_recreate();
          }

          $check_country_query = "SELECT entry_country_id, entry_zone_id
                                  FROM " . TABLE_ADDRESS_BOOK . "
                                  WHERE customers_id = :customersID
                                  AND address_book_id = :addressBookID";

          $check_country_query = $db->bindVars($check_country_query, ':customersID', $check_customer->fields['customers_id'], 'integer');
          $check_country_query = $db->bindVars($check_country_query, ':addressBookID', $check_customer->fields['customers_default_address_id'], 'integer');
          $check_country = $db->Execute($check_country_query);

          $_SESSION['customer_id'] = $check_customer->fields['customers_id'];
          $_SESSION['customer_default_address_id'] = $check_customer->fields['customers_default_address_id'];
          $_SESSION['customers_authorization'] = $check_customer->fields['customers_authorization'];
          $_SESSION['customer_first_name'] = $check_customer->fields['customers_firstname'];
          $_SESSION['customer_last_name'] = $check_customer->fields['customers_lastname'];
          $_SESSION['customer_country_id'] = $check_country->fields['entry_country_id'];
          $_SESSION['customer_zone_id'] = $check_country->fields['entry_zone_id'];

          $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
              SET customers_info_date_of_last_logon = now(),
                  customers_info_number_of_logons = customers_info_number_of_logons+1
              WHERE customers_info_id = :customersID";

          $sql = $db->bindVars($sql, ':customersID',  $_SESSION['customer_id'], 'integer');
          $db->Execute($sql);
          $zco_notifier->notify('NOTIFY_LOGIN_SUCCESS');

        // restore cart contents
        $_SESSION['cart']->restore_contents();
        /*
        if ($_SESSION['cart']->count_contents() > 0) {
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING));
        }
        */
        if (sizeof($_SESSION['navigation']->snapshot) > 0) {
          //    $back = sizeof($_SESSION['navigation']->path)-2;
          //if (isset($_SESSION['navigation']->path[$back]['page'])) {
          //    if (sizeof($_SESSION['navigation']->path)-2 > 0) {
          $origin_href = zen_href_link($_SESSION['navigation']->snapshot['page'], zen_array_to_string($_SESSION['navigation']->snapshot['get'], array(zen_session_name())), $_SESSION['navigation']->snapshot['mode']);
          //            $origin_href = zen_back_link_only(true);
          $_SESSION['navigation']->clear_snapshot();
          zen_redirect($origin_href);
        } else {
          zen_redirect(zen_href_link(FILENAME_DEFAULT));
        }
      }
    }
  }
}

if ($error == true) {
  $zco_notifier->notify('NOTIFY_LOGIN_FAILURE');
}

$breadcrumb->add(NAVBAR_TITLE);
// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_LOGIN_TIMEOUT');
$zco_notifier->notify('NOTIFY_HEADER_END_LOGIN');