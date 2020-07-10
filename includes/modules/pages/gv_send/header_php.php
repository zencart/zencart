<?php
/**
 * GV Send
 *
 * Used to allow customer to send GV to their friends/family by way of email.
 * They can send up to the amount of GV accumlated in their account by way of purchased GV's or GV's sent to them.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 05 Modified in v1.5.7 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_GV_SEND');
if (isset($_POST['message'])) $_POST['message'] = zen_output_string_protected($_POST['message']);

require_once('includes/classes/http_client.php');

if (!isset($_GET['action'])) $_GET['action'] = '';  
// verify no timeout has occurred on the send or process
if (!zen_is_logged_in() && isset($_GET['action']) && ($_GET['action'] == 'send' or $_GET['action'] == 'process')) {
  zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}

// if the customer is not logged on, redirect them to the login page
if (!zen_is_logged_in()) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
  $_GET['action'] = 'send';
}

// extract sender's name+email from database, since logged-in customer is the one who is sending this GV email
  $account_query = "SELECT customers_firstname, customers_lastname, customers_email_address
                    FROM " . TABLE_CUSTOMERS . "
                    WHERE customers_id = :customersID";
  $account_query = $db->bindVars($account_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $account = $db->Execute($account_query);
  $send_name = $account->fields['customers_firstname'] . ' ' . $account->fields['customers_lastname'];
  $send_firstname = $account->fields['customers_firstname'];

$gv_query = "SELECT amount
             FROM " . TABLE_COUPON_GV_CUSTOMER . "
             WHERE customer_id = :customersID";

$gv_query = $db->bindVars($gv_query, ':customersID', $_SESSION['customer_id'], 'integer');
$gv_result = $db->Execute($gv_query);

// Sanity Check
// Some stuff for debugging
// First let's get the local and base for how much the customer has in his GV account
// The customer_gv account is always stored in the store's base currency
//   $local_customer_gv = $currencies->value($gv_result->fields['amount']);
//   $base_customer_gv = $gv_result->fields['amount'];
// Now let's get the amount that the customer wants to send.
//   $local_customer_send = $_POST['amount'];
//   $base_customer_send = $currencies->value($_POST['amount'], true, DEFAULT_CURRENCY);


if ($_GET['action'] == 'send') {
  $_SESSION['complete'] = '';
  $error = false;

  if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
    $error = true;
  }
  if (!isset($_POST['to_name']) || trim($_POST['to_name']=='')) {
    $error = true;
    $messageStack->add('gv_send', ERROR_ENTRY_TO_NAME_CHECK, 'error');
  }
  if (!zen_validate_email(trim($_POST['email']))) {
    $error = true;
    $messageStack->add('gv_send', ERROR_ENTRY_EMAIL_ADDRESS_CHECK, 'error');
  }

  $customer_amount = $gv_result->fields['amount'];

  $_POST['amount'] = str_replace('$', '', $_POST['amount']);

  $gv_amount = trim($_POST['amount']);
  if (preg_match('/[^0-9\.,]/', $gv_amount)) {
    $error = true;
    $messageStack->add('gv_send', ERROR_ENTRY_AMOUNT_CHECK, 'error');
  }
  $gv_amount = $currencies->normalizeValue($gv_amount);
  if ( $currencies->value($gv_amount, true,DEFAULT_CURRENCY) > $customer_amount || $gv_amount == 0) {
    //echo $currencies->value($customer_amount, true,DEFAULT_CURRENCY);
    $error = true;
    $messageStack->add('gv_send', ERROR_ENTRY_AMOUNT_CHECK, 'error');
  }
}

if ($_GET['action'] == 'process') {
  if (!isset($_POST['back'])) { // customer didn't click the back button
    $id1 = zen_create_coupon_code($account->fields['customers_email_address']);
    // sanitize and remove non-numeric characters
    $_POST['amount'] = preg_replace('/[^0-9.,%]/', '', $_POST['amount']);

    $new_amount = $gv_result->fields['amount'] - $currencies->value($_POST['amount'], true, DEFAULT_CURRENCY);
    $new_db_amount = $gv_result->fields['amount'] - $currencies->value($_POST['amount'], true, DEFAULT_CURRENCY);
    if ($new_amount < 0) {
      $error= true;
      $messageStack->add('gv_send', ERROR_ENTRY_AMOUNT_CHECK, 'error');
      $_GET['action'] = 'send';
    } else {
      $_GET['action'] = 'complete';
      $gv_query="UPDATE " . TABLE_COUPON_GV_CUSTOMER . "
                 SET amount = '" .  $new_amount . "'
                 WHERE customer_id = :customersID";

      $gv_query = $db->bindVars($gv_query, ':customersID', $_SESSION['customer_id'], 'integer');
      $db->Execute($gv_query);

      $gv_query="INSERT INTO " . TABLE_COUPONS . " (coupon_type, coupon_code, date_created, coupon_amount)
                 VALUES ('G', :couponCode, NOW(), :amount)";

      $gv_query = $db->bindVars($gv_query, ':couponCode', $id1, 'string');
      $gv_query = $db->bindVars($gv_query, ':amount', $currencies->value($_POST['amount'], true, DEFAULT_CURRENCY), 'currency');
      $gv = $db->Execute($gv_query);

      $insert_id = $db->Insert_ID();

      $gv_query="INSERT INTO " . TABLE_COUPON_EMAIL_TRACK . "(coupon_id, customer_id_sent, sent_firstname, sent_lastname, emailed_to, date_sent)
                 VALUES (:insertID, :customersID, :firstname, :lastname, :email, now())";

      $gv_query = $db->bindVars($gv_query, ':insertID', $insert_id, 'integer');
      $gv_query = $db->bindVars($gv_query, ':customersID', $_SESSION['customer_id'], 'integer');
      $gv_query = $db->bindVars($gv_query, ':firstname', $account->fields['customers_firstname'], 'string');
      $gv_query = $db->bindVars($gv_query, ':lastname', $account->fields['customers_lastname'], 'string');
      $gv_query = $db->bindVars($gv_query, ':email', $_POST['email'], 'string');
      $db->Execute($gv_query);

      // build email content:
      $gv_email = STORE_NAME . "\n" .
      EMAIL_SEPARATOR . "\n" .
      sprintf(EMAIL_GV_TEXT_HEADER, $currencies->format($_POST['amount'], false)) . "\n" .
      EMAIL_SEPARATOR . "\n\n" .
      sprintf(EMAIL_GV_FROM, $send_name) . "\n";

      $html_msg['EMAIL_GV_TEXT_HEADER'] =  sprintf(EMAIL_GV_TEXT_HEADER, '');
      $html_msg['EMAIL_GV_AMOUNT'] =  $currencies->format($_POST['amount'], false);
      $html_msg['EMAIL_GV_FROM'] =  sprintf(EMAIL_GV_FROM, $send_name) ;

      if (isset($_POST['message'])) {
        $gv_email .= EMAIL_GV_MESSAGE . "\n\n";
        $html_msg['EMAIL_GV_MESSAGE'] = EMAIL_GV_MESSAGE . '<br />';

        if (isset($_POST['to_name'])) {
          $gv_email .= sprintf(EMAIL_GV_SEND_TO, $_POST['to_name']) . "\n\n";
          $html_msg['EMAIL_GV_SEND_TO'] = '<tt>'.sprintf(EMAIL_GV_SEND_TO, $_POST['to_name']). '</tt><br />';
        }
        $gv_email .= stripslashes($_POST['message']) . "\n\n";
        $gv_email .= EMAIL_SEPARATOR . "\n\n";
        $html_msg['EMAIL_MESSAGE_HTML'] = stripslashes($_POST['message']);
      }

      $html_msg['GV_REDEEM_HOW'] = sprintf(EMAIL_GV_REDEEM, '<strong>' . $id1 . '</strong>');
      $html_msg['GV_REDEEM_URL'] = '<a href="'.zen_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $id1, 'NONSSL', false).'">'.EMAIL_GV_LINK.'</a>';
      $html_msg['GV_REDEEM_CODE'] = $id1;

      $gv_email .= sprintf(EMAIL_GV_REDEEM, $id1) . "\n\n";
      $gv_email .= EMAIL_GV_LINK . ' ' . zen_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $id1, 'NONSSL', false);
      $gv_email .= "\n\n";
      $gv_email .= EMAIL_GV_FIXED_FOOTER . "\n\n";
      $gv_email .= EMAIL_GV_SHOP_FOOTER;

      $gv_email_subject = sprintf(EMAIL_GV_TEXT_SUBJECT, $send_name);

      // include disclaimer
      $gv_email .= "\n\n" . EMAIL_ADVISORY . "\n\n";

      $html_msg['EMAIL_GV_FIXED_FOOTER'] = str_replace(array("\r\n", "\n", "\r", "-----"), '', EMAIL_GV_FIXED_FOOTER);
      $html_msg['EMAIL_GV_SHOP_FOOTER'] =	EMAIL_GV_SHOP_FOOTER;

      // send the email
      zen_mail($_POST['to_name'], $_POST['email'], $gv_email_subject, nl2br($gv_email), STORE_NAME, EMAIL_FROM, $html_msg, 'gv_send');

      // send additional emails
      if (SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS == '1' and SEND_EXTRA_GV_CUSTOMER_EMAILS_TO !='') {
        $extra_info = email_collect_extra_info(ENTRY_NAME . $_POST['to_name'], ENTRY_EMAIL . $_POST['email'], $send_name , $account->fields['customers_email_address']);
        $html_msg['EXTRA_INFO'] = $extra_info['HTML'];
        zen_mail('', SEND_EXTRA_GV_CUSTOMER_EMAILS_TO, SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_SUBJECT . ' ' . $gv_email_subject,
        $gv_email . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg,'gv_send_extra');
      }

      // do a fresh calculation after sending an email
      $gv_query = "SELECT amount
                   FROM " . TABLE_COUPON_GV_CUSTOMER . "
                   WHERE customer_id = :customersID";

      $gv_query = $db->bindVars($gv_query, ':customersID', $_SESSION['customer_id'], 'integer');
      $gv_result = $db->Execute($gv_query);
    }
  } else { // customer DID click the back button
    $_GET['action'] = '';
  }
}

$gv_current_balance = $currencies->format($gv_result->fields['amount']);

if ($_GET['action'] == 'complete') zen_redirect(zen_href_link(FILENAME_GV_SEND, 'action=doneprocess'));

$breadcrumb->add(NAVBAR_TITLE);

// validate entries
if (empty($gv_amount)) $gv_amount = 0; 
$gv_amount = (float)$gv_amount;

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_GV_SEND');
