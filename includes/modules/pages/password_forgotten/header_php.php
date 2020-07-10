<?php
/**
 * Password Forgotten
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Aug 22 Modified in v1.5.7 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_PASSWORD_FORGOTTEN');


require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// remove from snapshot
$_SESSION['navigation']->remove_current_page();

if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
// Slam prevention:
  if (isset($_SESSION['login_attempt']) && $_SESSION['login_attempt'] > 9) {
    header('HTTP/1.1 406 Not Acceptable');
    exit(0);
  }
  // BEGIN SLAM PREVENTION
  if (!empty($_POST['email_address'])) {
    if (! isset($_SESSION['login_attempt'])) $_SESSION['login_attempt'] = 0;
    $_SESSION['login_attempt'] ++;
  } // END SLAM PREVENTION

  $email_address = zen_db_prepare_input(trim($_POST['email_address']));

  $check_customer_query = "SELECT customers_firstname, customers_lastname, customers_password, customers_id
                           FROM " . TABLE_CUSTOMERS . "
                           WHERE customers_email_address = :emailAddress";

  $check_customer_query = $db->bindVars($check_customer_query, ':emailAddress', $email_address, 'string');
  $check_customer = $db->Execute($check_customer_query);

  if ($check_customer->RecordCount() > 0) {

    $zco_notifier->notify('NOTIFY_PASSWORD_FORGOTTEN_VALIDATED', $email_address);

    $new_password = zen_create_PADSS_password( (ENTRY_PASSWORD_MIN_LENGTH > 0 ? ENTRY_PASSWORD_MIN_LENGTH : 5) );
    $crypted_password = zen_encrypt_password($new_password);

    $sql = "UPDATE " . TABLE_CUSTOMERS . "
            SET customers_password = :password
            WHERE customers_id = :customersID";

    $sql = $db->bindVars($sql, ':password', $crypted_password, 'string');
    $sql = $db->bindVars($sql, ':customersID', $check_customer->fields['customers_id'], 'integer');
    $db->Execute($sql);

    $html_msg['EMAIL_CUSTOMERS_NAME'] = $check_customer->fields['customers_firstname'] . ' ' . $check_customer->fields['customers_lastname'];
    $html_msg['EMAIL_MESSAGE_HTML'] = sprintf(EMAIL_PASSWORD_REMINDER_BODY, $new_password);

    // send the email
    zen_mail($check_customer->fields['customers_firstname'] . ' ' . $check_customer->fields['customers_lastname'], $email_address, EMAIL_PASSWORD_REMINDER_SUBJECT, sprintf(EMAIL_PASSWORD_REMINDER_BODY, $new_password), STORE_NAME, EMAIL_FROM, $html_msg,'password_forgotten');

    // handle 3rd-party integrations
    $zco_notifier->notify('NOTIFY_PASSWORD_FORGOTTEN_CHANGED', $email_address, $check_customer->fields['customers_id'], $new_password);

  } else {
    $zco_notifier->notify('NOTIFY_PASSWORD_FORGOTTEN_NOT_FOUND', $email_address);
  }

    $messageStack->add_session('login', SUCCESS_PASSWORD_SENT, 'success');

    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_LOGIN, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_PASSWORD_FORGOTTEN');
