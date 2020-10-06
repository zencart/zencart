<?php
/**
 * functions used by payment module class for Paypal IPN payment method
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright 2004 DevosC.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 22 Modified in v1.5.7 $
 */

// Functions for paypal processing
  function datetime_to_sql_format($paypalDateTime) {
    //Copyright (c) 2004 DevosC.com
    $months = array('Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04', 'May' => '05',  'Jun' => '06',  'Jul' => '07', 'Aug' => '08', 'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12');
    $hour = substr($paypalDateTime, 0, 2);$minute = substr($paypalDateTime, 3, 2);$second = substr($paypalDateTime, 6, 2);
    $month = $months[substr($paypalDateTime, 9, 3)];
    $day = (strlen($day = preg_replace("/,/" , '' , substr($paypalDateTime, 13, 2))) < 2) ? '0'.$day: $day;
    $year = substr($paypalDateTime, -8, 4);
    if (strlen($day)<2) $day = '0'.$day;
    return ($year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second);
  }

  function ipn_debug_email($message, $email_address = '', $always_send = false, $subjecttext = 'IPN DEBUG message') {
    static $paypal_error_counter;
    static $paypal_instance_id;
    $logfile = '';
    if ($email_address == '') $email_address = (defined('MODULE_PAYMENT_PAYPAL_DEBUG_EMAIL_ADDRESS') ? MODULE_PAYMENT_PAYPAL_DEBUG_EMAIL_ADDRESS : STORE_OWNER_EMAIL_ADDRESS);
    if(!isset($paypal_error_counter)) $paypal_error_counter = 0;
    if(!isset($paypal_instance_id)) $paypal_instance_id = time() . '_' . zen_create_random_value(4);
    if ((defined('MODULE_PAYMENT_PAYPALWPP_DEBUGGING') && MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log and Email') || (defined('MODULE_PAYMENT_PAYPAL_IPN_DEBUG') && MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Log and Email') || $always_send) {
      $paypal_error_counter ++;
      zen_mail(STORE_OWNER, $email_address, $subjecttext . ' (' . $paypal_instance_id . ') #' . $paypal_error_counter, $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>$message), 'debug');
    }
    if ((defined('MODULE_PAYMENT_PAYPAL_IPN_DEBUG') && (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Log and Email' || MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Log File' || MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes')) || (defined('MODULE_PAYMENT_PAYPALWPP_DEBUGGING') && (MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log File' || MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log and Email'))) $logfile = ipn_add_error_log($message, $paypal_instance_id);
    return $logfile;
  }

  function ipn_get_stored_session($session_stuff) {
    global $db;
    if (!is_array($session_stuff)) {
      ipn_debug_email('IPN FATAL ERROR :: Could not find Zen Cart custom variable in POST, cannot validate or re-create session as a transaction initiated from this store. Might be from another source such as eBay or another PayPal store using this PayPal account.');
      return false;
    }
    $sql = "SELECT *
            FROM " . TABLE_PAYPAL_SESSION . "
            WHERE session_id = :sessionID";
    $sql = $db->bindVars($sql, ':sessionID', $session_stuff[1], 'string');
    $stored_session = $db->Execute($sql);
    if ($stored_session->recordCount() < 1) {
      global $isECtransaction, $isDPtransaction;
      if (isset($_POST['payment_type']) && $_POST['payment_type'] == 'instant' && $isDPtransaction && ((isset($_POST['auth_status']) && $_POST['auth_status'] == 'Completed') || $_POST['payment_status'] == 'Completed')) {
        $session_stuff[1] = '(EC/DP transaction)';
      }
      ipn_debug_email('IPN ERROR :: Could not find stored session {' . $session_stuff[1] . '} in DB; thus cannot validate or re-create session as a transaction awaiting PayPal Website Payments Standard confirmation initiated by this store. Might be an Express Checkout or eBay transaction or some other action that triggers PayPal IPN notifications.');
      return false;
    }
    $_SESSION = unserialize(base64_decode($stored_session->fields['saved_session']));
    return true;
  }
/**
 * look up parent/original transaction record data and return matching order info if found, along with txn_type
 */
  function ipn_lookup_transaction($postArray) {
    global $db;
    // find Zen Cart order number from the transactionID in the IPN
    $ordersID = 0;
    $paypalipnID = 0;
    $transType = 'unknown';

    $sql = "SELECT order_id, paypal_ipn_id, payment_status, txn_type, pending_reason
                FROM " . TABLE_PAYPAL . "
                WHERE txn_id = :transactionID: OR invoice = :transactionID:
                ORDER BY order_id DESC LIMIT 1 ";

    if (isset($postArray['parent_txn_id']) && trim($postArray['parent_txn_id']) != '') {
      $sqlParent = $db->bindVars($sql, ':transactionID:', $postArray['parent_txn_id'], 'string');
      $ipn_id = $db->Execute($sqlParent);
      if($ipn_id->RecordCount() > 0) {
        ipn_debug_email('IPN NOTICE :: This transaction HAS a parent record. Thus this is an update of some sort.');
        $transType = ($ipn_id->fields['pending_reason'] == 'paymentreview') ? 'reviewed' : 'parent';
        $ordersID = $ipn_id->fields['order_id'];
        $paypalipnID = $ipn_id->fields['paypal_ipn_id'];
      }
    } else {
      $sqlTxn = $db->bindVars($sql, ':transactionID:', $postArray['txn_id'], 'string');
      $ipn_id = $db->Execute($sqlTxn);
      if ($ipn_id->RecordCount() <= 0) {
        ipn_debug_email('IPN NOTICE :: Could not find matched txn_id record in DB. Therefore is new to us. ');
        $transType = 'unique';
      } else {
        while(!$ipn_id->EOF) {
          switch ($ipn_id->fields['pending_reason']) {
            case 'address':
              ipn_debug_email('IPN NOTICE :: Found pending-address record in database');
              if ($postArray['payment_status'] == 'Completed') $transType = 'cleared-address';
              if ($postArray['payment_status'] == 'Denied')    $transType = 'denied-address';
              if ($postArray['payment_status'] == 'Pending')   $transType = 'pending-address';
            break;
            case 'multi_currency':
              ipn_debug_email('IPN NOTICE :: Found pending-multicurrency record in database');
              if ($postArray['payment_status'] == 'Completed') $transType = 'cleared-multicurrency';
              if ($postArray['payment_status'] == 'Denied')    $transType = 'denied-multicurrency';
              if ($postArray['payment_status'] == 'Pending')   $transType = 'pending-multicurrency';
            break;
            case 'echeck':
              ipn_debug_email('IPN NOTICE :: Found pending-echeck record in database');
              if ($postArray['payment_status'] == 'Completed') $transType = 'cleared-echeck';
              if ($postArray['payment_status'] == 'Completed' && $postArray['txn_type'] == 'web_accept') $transType = 'cleared-echeck';
              if ($postArray['payment_status'] == 'Denied')    $transType = 'denied-echeck';
              if ($postArray['payment_status'] == 'Failed')    $transType = 'failed-echeck';
              if ($postArray['payment_status'] == 'Pending')   $transType = 'pending-echeck';
            break;
            case 'authorization':
              ipn_debug_email('IPN NOTICE :: Found pending-authorization record in database');
              $transType = 'cleared-authorization';
              if ($postArray['payment_status'] == 'Voided') $transType = 'voided';
              if ($postArray['payment_status'] == 'Pending') $transType = 'pending-authorization';
              if ($postArray['payment_status'] == 'Captured') $transType = 'captured';
              if ($postArray['payment_status'] == 'Completed') $transType = 'cleared-authorization';
              if ($postArray['auth_status'] == 'In_Progress') $transType = 'partial-authorization';
            break;
            case 'verify':
              ipn_debug_email('IPN NOTICE :: Found pending-verify record in database');
              $transType = 'cleared-verify';
            break;
            case 'paymentreview':
              ipn_debug_email('IPN NOTICE :: Found pending-review record in database');
              $transType = 'pending-paymentreview';
              if ($postArray['payment_status'] == 'Completed') $transType = 'cleared-review';
            break;
            case 'intl':
              ipn_debug_email('IPN NOTICE :: Found pending-intl record in database');
              if ($postArray['payment_status'] == 'Completed') $transType = 'cleared-intl';
              if ($postArray['payment_status'] == 'Denied')    $transType = 'denied-intl';
              if ($postArray['payment_status'] == 'Pending')   $transType = 'pending-intl';
            break;
            case 'unilateral':
              ipn_debug_email('IPN NOTICE :: Found record in database.' . "\n" . '*** NOTE: TRANSACTION IS IN *unilateral* STATUS pending creation of a PayPal account for this receiver_email address.' . "\n" . 'Please create the account, or make sure the account is *Verified*.');
              $transType = 'pending-unilateral';
            break;
          }
          if ($transType != 'unknown') {
            $ordersID = $ipn_id->fields['order_id'];
            $paypalipnID = $ipn_id->fields['paypal_ipn_id'];
          }
          $ipn_id->MoveNext();
        }
      }
    }
    return array('order_id' => $ordersID, 'paypal_ipn_id' => $paypalipnID, 'txn_type' => $transType);
  }
/**
 * IPN Validation
 * - match email addresses
 * - ensure that "VERIFIED" has been returned (otherwise somebody is trying to spoof)
 */
  function ipn_validate_transaction($info, $postArray, $mode='IPN') {
    if ($mode == 'IPN' && !preg_match("/VERIFIED/i", $info) && !preg_match("/SUCCESS/i", $info)) {
      ipn_debug_email('IPN WARNING :: Transaction was NOT marked as VERIFIED. Keep this report for potential use in fraud investigations.' . "\n" . 'IPN Info: ' . "\n" . $info);
      return false;
    } elseif ($mode == 'PDT' && (!preg_match("/SUCCESS/i", $info) || preg_match("/FAIL/i", $info))) {
      ipn_debug_email('IPN WARNING :: PDT Transaction was NOT marked as SUCCESS. Keep this report for potential use in fraud investigations.' . "\n" . 'IPN Info: ' . "\n" . $info);
      return false;
    }
    $ppBusEmail = false;
    $ppRecEmail = false;
    if (defined('MODULE_PAYMENT_PAYPAL_BUSINESS_ID')) {
      if (strtolower(trim($postArray['business'])) == strtolower(trim(MODULE_PAYMENT_PAYPAL_BUSINESS_ID))) $ppBusEmail = true;
      if (strtolower(trim($postArray['receiver_email'])) == strtolower(trim(MODULE_PAYMENT_PAYPAL_BUSINESS_ID))) $ppRecEmail = true;
      if (!$ppBusEmail && !$ppRecEmail) {
        ipn_debug_email('IPN WARNING :: Transaction email address NOT matched.' . "\n" . 'From IPN = ' . $postArray['business'] . ' | ' . $postArray['receiver_email'] . "\n" . 'From CONFIG = ' .  MODULE_PAYMENT_PAYPAL_BUSINESS_ID);
        return false;
      }
      ipn_debug_email('IPN INFO :: Transaction email details.' . "\n" . 'From IPN = ' . $postArray['business'] . ' | ' . $postArray['receiver_email'] . "\n" . 'From CONFIG = ' .  MODULE_PAYMENT_PAYPAL_BUSINESS_ID);
    }
    return true;
  }

  // determine acceptable currencies
  function select_pp_currency() {
    if (MODULE_PAYMENT_PAYPAL_CURRENCY == 'Selected Currency') {
      $my_currency = $_SESSION['currency'];
    } else {
      $my_currency = substr(MODULE_PAYMENT_PAYPAL_CURRENCY, 5);
    }
    $pp_currencies = array('CAD', 'EUR', 'GBP', 'JPY', 'USD', 'AUD', 'CHF', 'CZK', 'DKK', 'HKD', 'HUF', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'THB', 'MXN', 'ILS', 'PHP', 'TWD', 'BRL', 'MYR', 'INR');
    if (!in_array($my_currency, $pp_currencies)) {
      $my_currency = 'USD';
    }
    return $my_currency;
  }

  function valid_payment($amount, $currency, $mode = 'IPN') {
    global $currencies;
    $my_currency = select_pp_currency();
    $exchanged_amount = ($mode == 'IPN' ? ($amount * $currencies->get_value($my_currency)) : $amount);
    $transaction_amount = preg_replace('/[^0-9.]/', '', number_format($exchanged_amount, $currencies->get_decimal_places($my_currency), '.', ''));
    if ($_POST['mc_currency'] != $my_currency || ($_POST['mc_gross'] != $transaction_amount && $_POST['mc_gross'] != -0.01) && (!defined('MODULE_PAYMENT_PAYPAL_TESTING') || MODULE_PAYMENT_PAYPAL_TESTING != 'Test') ) {
      ipn_debug_email('IPN WARNING :: Currency/Amount Mismatch.  Details: ' . "\n" . 'PayPal email address = ' . $_POST['business'] . "\n" . ' | mc_currency = ' . $_POST['mc_currency'] . "\n" . ' | submitted_currency = ' . $my_currency . "\n" . ' | order_currency = ' . $currency . "\n" . ' | mc_gross = ' . $_POST['mc_gross'] . "\n" . ' | converted_amount = ' . $transaction_amount . "\n" . ' | order_amount = ' . $amount );
      return false;
    }
    ipn_debug_email('IPN INFO :: Currency/Amount Details: ' . "\n" . 'PayPal email address = ' . $_POST['business'] . "\n" . ' | mc_currency = ' . $_POST['mc_currency'] . "\n" . ' | submitted_currency = ' . $my_currency . "\n" . ' | order_currency = ' . $currency . "\n" . ' | mc_gross = ' . $_POST['mc_gross'] . "\n" . ' | converted_amount = ' . $transaction_amount . "\n" . ' | order_amount = ' . $amount );
    return true;
  }

/**
 *  is this an existing transaction?
 *    (1) we find a matching record in the "paypal" table
 *    (2) we check for valid txn_types or payment_status such as Denied, Refunded, Partially-Refunded, Reversed, Voided, Expired
 */
  function ipn_determine_txn_type($postArray, $txn_type = 'unknown') {
    global $db, $parentLookup;
    if (substr($txn_type,0,8) == 'cleared-') return $txn_type;
    if ($postArray['txn_type'] == 'send_money') return $postArray['txn_type'];
    if ($postArray['txn_type'] == 'express_checkout' || $postArray['txn_type'] == 'cart') $txn_type = $postArray['txn_type'];
// if it's not unique or linked to a parent, then:
// 1. could be an e-check denied / cleared
// 2. could be an express-checkout "pending" transaction which has been Accepted in the merchant's PayPal console and needs activation in Zen Cart
    if ($postArray['payment_status']=='Completed' && $txn_type=='express_checkout' && $postArray['payment_type']=='echeck') {
      $txn_type = 'express-checkout-cleared';
      return $txn_type;
    }
    if ($postArray['payment_status']=='Completed' && $postArray['payment_type']=='echeck') {
      $txn_type = 'echeck-cleared';
      return $txn_type;
    }
    if (($postArray['payment_status']=='Denied' || $postArray['payment_status']=='Failed') && $postArray['payment_type']=='echeck') {
      $txn_type = 'echeck-denied';
      return $txn_type;
    }
    if ($postArray['payment_status']=='Denied') {
      $txn_type = 'denied';
      return $txn_type;
    }
    if (($postArray['payment_status']=='Pending') && $postArray['pending_reason']=='echeck') {
      $txn_type = 'pending-echeck';
      return $txn_type;
    }
    if (($postArray['payment_status']=='Pending') && $postArray['pending_reason']=='address') {
      $txn_type = 'pending-address';
      return $txn_type;
    }
    if (($postArray['payment_status']=='Pending') && $postArray['pending_reason']=='intl') {
      $txn_type = 'pending-intl';
      return $txn_type;
    }
    if (($postArray['payment_status']=='Pending') && $postArray['pending_reason']=='multi_currency') {
      $txn_type = 'pending-multicurrency';
      return $txn_type;
    }
    if (($postArray['payment_status']=='Pending') && $postArray['pending_reason']=='paymentreview') {
      $txn_type = 'pending-paymentreview';
      return $txn_type;
    }
    if (($postArray['payment_status']=='Pending') && $postArray['pending_reason']=='verify') {
      $txn_type = 'pending-verify';
      return $txn_type;
    }
    if ($parentLookup == 'parent' && $postArray['payment_status']=='Completed' && $postArray['payment_type']=='instant') {
      $txn_type = 'cleared-authorization';
      return $txn_type;
    }
    if (($postArray['payment_status']=='Voided') && $postArray['payment_type']=='instant') {
      $txn_type = 'voided';
      return $txn_type;
    }
    return $txn_type;
  }
/**
 * Create order record from IPN data
 */
  function ipn_create_order_array($new_order_id, $txn_type) {
    $sql_data_array = array('order_id' => $new_order_id,
                            'txn_type' => $txn_type,
                            'module_name' => 'paypal (ipn-handler)',
                            'module_mode' => 'IPN',
                            'reason_code' => $_POST['reason_code'],
                            'payment_type' => $_POST['payment_type'],
                            'payment_status' => $_POST['payment_status'],
                            'pending_reason' => $_POST['pending_reason'],
                            'invoice' => $_POST['invoice'],
                            'mc_currency' => $_POST['mc_currency'],
                            'first_name' => $_POST['first_name'],
                            'last_name' => $_POST['last_name'],
                            'payer_business_name' => $_POST['payer_business_name'],
                            'address_name' => $_POST['address_name'],
                            'address_street' => $_POST['address_street'],
                            'address_city' => $_POST['address_city'],
                            'address_state' => $_POST['address_state'],
                            'address_zip' => $_POST['address_zip'],
                            'address_country' => $_POST['address_country'],
                            'address_status' => $_POST['address_status'],
                            'payer_email' => $_POST['payer_email'],
                            'payer_id' => $_POST['payer_id'],
                            'payer_status' => $_POST['payer_status'],
                            'payment_date' => datetime_to_sql_format($_POST['payment_date']),
                            'business' => $_POST['business'],
                            'receiver_email' => $_POST['receiver_email'],
                            'receiver_id' => $_POST['receiver_id'],
                            'txn_id' => $_POST['txn_id'],
                            'parent_txn_id' => $_POST['parent_txn_id'],
                            'num_cart_items' => (int)$_POST['num_cart_items'],
                            'mc_gross' => $_POST['mc_gross'],
                            'mc_fee' => $_POST['mc_fee'],
                            'settle_amount' => (isset($_POST['settle_amount']) && $_POST['settle_amount'] != '' ? $_POST['settle_amount'] : 0),
                            'settle_currency' => $_POST['settle_currency'],
                            'exchange_rate' => (isset($_POST['exchange_rate']) && $_POST['exchange_rate'] != '' ? $_POST['exchange_rate'] : 1),
                            'notify_version' => $_POST['notify_version'],
                            'verify_sign' => $_POST['verify_sign'],
                            'date_added' => 'now()',
                            'memo' => '{Record generated by IPN}'
                             );
    if (isset($_POST['protection_eligibility']) && $_POST['protection_eligibility'] != '') $sql_data_array['memo'] .= ' [ProtectionEligibility:' . $_POST['protection_eligibility'] .']';
    if (isset($_POST['memo']) && $_POST['memo'] != '') $sql_data_array['memo'] .= ' [Customer Comments:' . $_POST['memo'] .']';
     return $sql_data_array;
  }
/**
 * Create order-history record from IPN data
 */
  function ipn_create_order_history_array($insert_id) {
    $sql_data_array = array ('paypal_ipn_id' => (int)$insert_id,
                             'txn_id' => $_POST['txn_id'],
                             'parent_txn_id' => $_POST['parent_txn_id'],
                             'payment_status' => $_POST['payment_status'],
                             'pending_reason' => $_POST['pending_reason'],
                             'date_added' => 'now()'
                             );
    return $sql_data_array;
  }
/**
 * Create order-update from IPN data
 */
  function ipn_create_order_update_array($txn_type) {
    $sql_data_array = array('payment_type' => $_POST['payment_type'],
                            'txn_type' => $txn_type,
                            'parent_txn_id' => $_POST['parent_txn_id'],
                            'payment_status' => $_POST['payment_status'],
                            'pending_reason' => $_POST['pending_reason'],
                            'payer_email' => $_POST['payer_email'],
                            'payer_id' => $_POST['payer_id'],
                            'business' => $_POST['business'],
                            'receiver_email' => $_POST['receiver_email'],
                            'receiver_id' => $_POST['receiver_id'],
                            'notify_version' => $_POST['notify_version'],
                            'verify_sign' => $_POST['verify_sign'],
                            'last_modified' => 'now()'
                         );
    if (isset($_POST['address_street']) && $_POST['address_street'] != '')
       $sql_data_array = array_merge($sql_data_array,
                    array('address_name' => $_POST['address_name'],
                          'address_street' => $_POST['address_street'],
                          'address_city' => $_POST['address_city'],
                          'address_state' => $_POST['address_state'],
                          'address_zip' => $_POST['address_zip'],
                          'address_country' => $_POST['address_country']));
    if (isset($_POST['payer_business_name']) && $_POST['payer_business_name'] != '') $sql_data_array['payer_business_name'] = $_POST['payer_business_name'];
    if (isset($_POST['reason_code']) && $_POST['reason_code'] != '') $sql_data_array['reason_code'] = $_POST['reason_code'];
    if (isset($_POST['invoice']) && $_POST['invoice'] != '') $sql_data_array['invoice'] = $_POST['invoice'];
    if (isset($_POST['mc_gross']) && $_POST['mc_gross'] > 0) $sql_data_array['mc_gross'] = $_POST['mc_gross'];
    if (isset($_POST['mc_fee']) && $_POST['mc_fee'] > 0) $sql_data_array['mc_fee'] = $_POST['mc_fee'];
    if (isset($_POST['settle_amount']) && $_POST['settle_amount'] > 0) $sql_data_array['settle_amount'] = $_POST['settle_amount'];
    if (isset($_POST['first_name']) && $_POST['first_name'] != '') $sql_data_array['first_name'] = $_POST['first_name'];
    if (isset($_POST['last_name']) && $_POST['last_name'] != '') $sql_data_array['last_name'] = $_POST['last_name'];
    if (isset($_POST['mc_currency']) && $_POST['mc_currency'] != '') $sql_data_array['mc_currency'] = $_POST['mc_currency'];
    if (isset($_POST['settle_currency']) && $_POST['settle_currency'] != '') $sql_data_array['settle_currency'] = $_POST['settle_currency'];
    if (isset($_POST['num_cart_items']) && $_POST['num_cart_items'] > 0) $sql_data_array['num_cart_items'] = $_POST['num_cart_items'];
    if (isset($_POST['exchange_rate']) && $_POST['exchange_rate'] > 0) $sql_data_array['exchange_rate'] = $_POST['exchange_rate'];
    $sql_data_array['memo'] = '{Record generated by IPN}';
    if (isset($_POST['protection_eligibility']) && $_POST['protection_eligibility'] != '') $sql_data_array['memo'] .= ' [ProtectionEligibility:' . $_POST['protection_eligibility'] .']';
    if (isset($_POST['memo']) && $_POST['memo'] != '') $sql_data_array['memo'] .= ' [Customer Comments:' . $_POST['memo'] .']';
    return $sql_data_array;
  }
/**
 * Debug to file
 */
  function ipn_fopen($filename) {
    $response = '';
    $fp = @fopen($filename,'rb');
    if ($fp) {
      $response = getRequestBodyContents($fp);
      fclose($fp);
    }
    return $response;
  }
  function getRequestBodyContents(&$handle) {
    if ($handle) {
      $line = '';
      while(!feof($handle)) {
        $line .= @fgets($handle, 1024);
      }
      return $line;
    }
    return false;
  }
/**
 * Verify IPN by sending it back to PayPal for confirmation
 */
  function ipn_postback($mode = 'IPN', $pdtTX = '') {
    $postdata = '';
    $postback = '';
    $postback_array = array();

    //build postback string
    if ($mode == 'PDT') {
      if ($pdtTX == '') return FALSE; // TX value not supplied, therefore PDT is disabled on merchant's PayPal profile.
      ipn_debug_email('PDT PROCESSING INITIATED.' . "\n" . 'Preparing to verify transaction via PDT.' . "\n\n" . 'The TX token for verification is: ' . print_r($_GET, TRUE));
      $postback .= "cmd=_notify-synch";
      $postback .= "&tx=" . $_GET['tx'];
      $postback .= "&at=" . trim(MODULE_PAYMENT_PAYPAL_PDTTOKEN);
      $postback .= "&";
      $postback_array['cmd'] = "_notify-sync";
      $postback_array['tx'] = $_GET['tx'];
      $postback_array['at'] = substr(MODULE_PAYMENT_PAYPAL_PDTTOKEN, 0, 5) . '**********' . substr(MODULE_PAYMENT_PAYPAL_PDTTOKEN,-5);
    } elseif ($mode == 'IPN') {
      $postback .= "cmd=_notify-validate";
      $postback .= "&";
      $postback_array['cmd'] = "_notify-validate";
    }
    foreach($_POST as $key=>$value) {
      $postdata .= $key . "=" . urlencode(stripslashes($value)) . "&";
      $postback .= $key . "=" . urlencode(stripslashes($value)) . "&";
      $postback_array[$key] = $value;
    }
    if (substr($postdata, -2) == '=&') {
      ipn_debug_email('IPN NOTICE :: No POST data to process -- Bad IPN data');
      return $postdata;
    }
    $postback = rtrim($postback, '&');
    $postdata = rtrim($postdata, '&');
    $postdata_array = $_POST;
    ksort($postdata_array);

    if ($mode == 'IPN') {
      ipn_debug_email('IPN INFO - POST VARS received (sorted):' . "\n" . stripslashes(urldecode(print_r($postdata_array, true))));
      if (sizeof($postdata_array) == 0) die('Nothing to process. Please return to home page.');
    }

    // send received data back to PayPal for validation
    $scheme = 'https://';
    //Parse url
    $web = parse_url($scheme . 'ipnpb.paypal.com/cgi-bin/webscr');
    if ((isset($_POST['test_ipn']) && $_POST['test_ipn'] == 1) || (defined('MODULE_PAYMENT_PAYPAL_HANDLER') && MODULE_PAYMENT_PAYPAL_HANDLER == 'sandbox')) {
      $web = parse_url($scheme . 'ipnpb.sandbox.paypal.com/cgi-bin/webscr');
    }
    //Set the port number
    if($web['scheme'] == "https") {
      $web['port']="443";  $ssl = "ssl://";
    } else {
      $web['port']="80";   $ssl = "";
    }

    $result = '';
    if (function_exists('curl_init')) {
      $result = doPayPalIPNCurlPostback($web, $postback, $postback_array, $mode);
    }
    if ($mode == 'PDT') {
      $info = $result['info'];
      $result = $result['status'];
    }
    //DEBUG ONLY: ipn_debug_email('After CURL: $result='.$result);
    if (!in_array(trim($result), array('VERIFIED', 'SUCCESS', 'INVALID', 'FAIL'))) {
      ipn_debug_email('IPN NOTICE: Could not get usable response via CURL. Trying fsockopen() as fallback.' . ($result != '' ? ' ['.$result.']' : ''));
      $result = doPayPalIPNFsockopenPostback($web, $postback, $postback_array, $ssl, $mode);
      if ($mode == 'PDT') {
        $info = $result['info'];
        $result = $result['status'];
      }
    }
    return ($mode == 'PDT') ? array('status' => $result, 'info' => $info) : trim($result);
  }

  function doPayPalIPNFsockopenPostback($web, $postback, $postback_array, $ssl, $mode = 'IPN') {
    $header  = "POST " . $web['path'] . " HTTP/1.1\r\n";
    $header .= "Host: " . $web['host'] . "\r\n";
    $header .= "Content-type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-length: " . strlen($postback) . "\r\n";
    $header .= "Connection: close\r\n\r\n";
    $errnum = 0;
    $errstr = '';

    ipn_debug_email('IPN INFO - POST VARS to be sent back (unsorted) for validation (using fsockopen): ' . "\n" . 'To: ' . $ssl . $web['host'] . ':' . $web['port'] . "\n" . $header . stripslashes(print_r($postback_array, true)));

    //Create paypal connection
    if (defined('MODULE_PAYMENT_PAYPAL_IPN_DEBUG') && MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') {
      $fp=fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    } else {
      $fp=@fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp && $ssl == 'ssl://') {
      ipn_debug_email('IPN ERROR :: Could not establish fsockopen: ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n Trying again with HTTPS over 443 ...");
      $ssl = 'https://';
      $web['port'] = '443';
      $fp=@fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp && $ssl == 'https://') {
      ipn_debug_email('IPN ERROR :: Could not establish fsockopen: ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n Trying again directly over 443 ...");
      $ssl = '';
      $web['port'] = '443';
      $fp=@fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp) {
      ipn_debug_email('IPN ERROR :: Could not establish fsockopen: ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n Trying again with HTTP over port 80 ...");
      $ssl = 'http://';
      $web['port'] = '80';
      $fp=@fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp) {
      ipn_debug_email('IPN ERROR :: Could not establish fsockopen: ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n Trying again without any specified protocol, using port 80 ...");
      $ssl = '';
      $web['port'] = '80';
      $fp=@fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp) {
      ipn_debug_email('IPN FATAL ERROR :: Could not establish fsockopen. ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\nABORTED.");
      die();
    }
    $info = array();
    fputs($fp, $header . $postback . "\r\n\r\n");
    $header_data = '';
    $headerdone = false;
    //loop through the response from the server
    while(!feof($fp)) {
      $line = @fgets($fp, 1024);
      if (strcmp($line, "\r\n") == 0) {
        // this is a header row
        $headerdone = true;
        $header_data .= $line;
      } else if ($headerdone) {
        // header has been read. now read the contents
        $info[] = $line;
      }
    }
    //close $fp - we are done with it
    fclose($fp);
    //break up results into a string
    $info = implode("", $info);
    $firstline = trim(substr($info, 0, 20));
    $status = '';
    if ($status == '' && substr($firstline, 0, 8) == 'VERIFIED') $status = 'VERIFIED';
    if ($status == '' && substr($firstline, 0, 7) == 'SUCCESS') $status = 'SUCCESS';
    if ($status == '' && substr($firstline, 0, 4) == 'FAIL') $status = 'FAIL';
    if ($status == '' && substr($firstline, 0, 7) == 'INVALID') $status = 'INVALID';
    if ($status == '' && substr($firstline, 0, 12) == 'UNDETERMINED') $status = 'UNDETERMINED';
    ipn_debug_email('IPN INFO (fs) - Confirmation/Validation response ' . ($status != '' ? $status : $header_data . $info));

    return ($mode == 'PDT') ? array('status' => $status, 'info' => $info) : $status;
  }

  function doPayPalIPNCurlPostback($url, $vars, $varsArray, $mode = 'IPN') {
    ipn_debug_email('IPN INFO - POST VARS to be sent back (unsorted) for validation (using CURL): ' . "\n" . 'To: ' . $url['host'] . ':' . $url['port'] . "\n" . stripslashes(print_r($varsArray, true)));
    $curlOpts = array(CURLOPT_URL => 'https://' . $url['host'] . $url['path'],
                      CURLOPT_POST => TRUE,
                      CURLOPT_POSTFIELDS => $vars,
                      CURLOPT_TIMEOUT => 45,
                      CURLOPT_CONNECTTIMEOUT => 30,
                      CURLOPT_VERBOSE => FALSE,
                      CURLOPT_HEADER => FALSE,
                      CURLOPT_FOLLOWLOCATION => FALSE,
                      CURLOPT_RETURNTRANSFER => TRUE,
                      //CURLOPT_SSL_VERIFYPEER => FALSE, // Leave this line commented out! This should never be set to FALSE on a live site!
                      //CURLOPT_CAINFO => '/local/path/to/cacert.pem', // for offline testing, this file can be obtained from http://curl.haxx.se/docs/caextract.html ... should never be used in production!
                      CURLOPT_FORBID_REUSE => TRUE,
                      CURLOPT_FRESH_CONNECT => TRUE,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_USERAGENT => 'Zen Cart(R) - IPN Postback',
                      );
    if (CURL_PROXY_REQUIRED == 'True') {
      $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
      $curlOpts[CURLOPT_HTTPPROXYTUNNEL] = $proxy_tunnel_flag;
      $curlOpts[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
      $curlOpts[CURLOPT_PROXY] = CURL_PROXY_SERVER_DETAILS;
    }
    $ch = curl_init();
    curl_setopt_array($ch, $curlOpts);
    $response = curl_exec($ch);
    $commError = curl_error($ch);
    $commErrNo = curl_errno($ch);
    if ($commErrNo == 35) {
      curl_setopt($ch, CURLOPT_SSLVERSION, 6);
      $response = curl_exec($ch);
      $commError = curl_error($ch);
      $commErrNo = curl_errno($ch);
    }
    $commInfo = @curl_getinfo($ch);
    curl_close($ch);

    $errors = ($commErrNo != 0 ? "CURL communication ERROR: (" . $commErrNo . ') ' . $commError : '');
    $response .= ($commErrNo != 0 ? '&CURL_ERRORS=' . urlencode('(' . $commErrNo . ') ' . $commError) : '') ;
//    $response .=  ($commErrNo != 0 ? '&CURL_INFO=' . urlencode($commInfo) : '');

    ipn_debug_email('CURL OPTS: ' . print_r($curlOpts, true));
    ipn_debug_email('CURL response: ' . $response);

    if ($errors != '') {
      ipn_debug_email('CURL errors: ' . $errors, print_r($commInfo, true));
    }
    //echo 'INFO: <pre>'; print_r($commInfo); echo '</pre><br />';
    //echo 'ERROR: ' . $errors . '<br />';
    //print_r($response) ;

    if (($response == '' || $errors != '') && ($url['scheme'] != 'http')) {
      $url['scheme'] = 'http';
      $url['port'] = '80';
      ipn_debug_email('CURL ERROR: ' . $errors . "\n" . 'Trying direct HTTP on port 80 instead ... ' . $url['scheme'] . '://' . $url['host'] . $url['path'] . "\n");
      $ch = curl_init();
      $curlOpts[CURLOPT_URL] = $url['scheme'] . '://' . $url['host'] . $url['path'];
      $curlOpts[CURLOPT_FOLLOWLOCATION] = TRUE; // allow to follow redirects since PP usually redirects all non-SSL to SSL etc, do a redirect is almost certain to occur
      curl_setopt_array($ch, $curlOpts);
      curl_setopt($ch, CURLOPT_PORT, $url['port']);
      $response = curl_exec($ch);
      $commError = curl_error($ch);
      $commErrNo = curl_errno($ch);
      $commInfo = @curl_getinfo($ch);
      curl_close($ch);
      ipn_debug_email('CURL OPTS: ' . print_r($curlOpts, true));
      ipn_debug_email('CURL response: ' . $response);
      $errors = ($commErrNo != 0 ? "\n(" . $commErrNo . ') ' . $commError : '');
      if ($errors != '') {
        ipn_debug_email (nl2br('CURL ERROR: ' . $errors . "\n" . 'ABORTING CURL METHOD ...' . "\n\n"));
      }
    }
    $firstline = trim(substr($response, 0, 20));
    $status = '';
    if ($status == '' && substr($firstline, 0, 8) == 'VERIFIED') $status = 'VERIFIED';
    if ($status == '' && substr($firstline, 0, 7) == 'SUCCESS') $status = 'SUCCESS';
    if ($status == '' && substr($firstline, 0, 4) == 'FAIL') $status = 'FAIL';
    if ($status == '' && substr($firstline, 0, 7) == 'INVALID') $status = 'INVALID';
    if ($status == '' && substr($firstline, 0, 12) == 'UNDETERMINED') $status = 'UNDETERMINED';
    ipn_debug_email('IPN INFO (cl) - Confirmation/Validation response ' . ($status != '' ? $status : $response));

    if ($response != '') {
      return ($mode == 'PDT') ? array('status' => $status, 'info' => $response) : $response;

    } else {
      return $errors;
    }
  }

/**
 * Write order-history update to ZC tables denoting the update supplied by the IPN
 */
  function ipn_update_orders_status_and_history($ordersID, $new_status = 1, $txn_type = '') {
    global $db;

    ipn_debug_email('IPN NOTICE :: Updating order #' . (int)$ordersID . ' to status: ' . (int)$new_status . ' (txn_type: ' . $txn_type . ')');

    $comments = 'PayPal status: ' . $_POST['payment_status'] . ' ' . ' @ ' . $_POST['payment_date'] . (($_POST['parent_txn_id'] !='') ? "\n" . ' Parent Trans ID:' . $_POST['parent_txn_id'] : '') . "\n" . ' Trans ID:' . $_POST['txn_id'] . "\n" . ' Amount: ' . $_POST['mc_gross'] . ' ' . $_POST['mc_currency'];
    zen_update_orders_history($ordersID, $comments, null, $new_status, 0);

    ipn_debug_email('IPN NOTICE :: Update complete.');

/**
 * Activate any downloads associated with an order which has now been cleared
 */
    if ($txn_type=='echeck-cleared' || $txn_type == 'express-checkout-cleared' || substr($txn_type,0,8) == 'cleared-') {
      $check_status = $db->Execute("SELECT date_purchased FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$ordersID . "'");
      $zc_max_days = zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + (int)DOWNLOAD_MAX_DAYS;
      ipn_debug_email('IPN NOTICE :: Updating order #' . (int)$ordersID . ' downloads (if any).  New max days: ' . (int)$zc_max_days . ', New count: ' . (int)DOWNLOAD_MAX_COUNT);
      $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " SET download_maxdays='" . (int)$zc_max_days . "', download_count='" . (int)DOWNLOAD_MAX_COUNT . "' WHERE orders_id='" . (int)$ordersID . "'";
      $db->Execute($update_downloads_query);
    }
  }

  /**
   * Prepare subtotal and line-item detail content to send to PayPal
   */
  function ipn_getLineItemDetails($restrictedCurrency) {
    global $order, $currencies, $order_totals, $order_total_modules;

    // if not default currency, do not send subtotals or line-item details
    if (DEFAULT_CURRENCY != $order->info['currency'] || $restrictedCurrency != DEFAULT_CURRENCY) {
      ipn_logging('getLineItemDetails 1', 'Not using default currency. Thus, no line-item details can be submitted.');
      return array();
    }
    if ($currencies->currencies[$_SESSION['currency']]['value'] != 1 || $currencies->currencies[$order->info['currency']]['value'] != 1) {
      ipn_logging('getLineItemDetails 2', 'currency val not equal to 1.0000 - cannot proceed without coping with currency conversions. Aborting line-item details.');
      return array();
    }

    $optionsST = array();
    $optionsLI = array();
    $optionsNB = array();
    $numberOfLineItemsProcessed = 0;
    $creditsApplied = 0;
    $surcharges = 0;
    $sumOfLineItems = 0;
    $sumOfLineTax = 0;
    $optionsST['amount'] = 0;
    $optionsST['subtotal'] = 0;
    $optionsST['tax_cart'] = 0;
    $optionsST['shipping'] = 0;
    $flagSubtotalsUnknownYet = true;
    $subTotalLI = 0;
    $subTotalTax = 0;
    $subTotalShipping = 0;
    $subtotalPRE = array('no data');
    $discountProblemsFlag = FALSE;
    $flag_treat_as_partial = FALSE;

    if (sizeof($order_totals)) {
      // prepare subtotals
      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        if ($order_totals[$i]['code'] == '') continue;
        if (in_array($order_totals[$i]['code'], array('ot_total','ot_subtotal','ot_tax','ot_shipping')) || strstr($order_totals[$i]['code'], 'insurance')) {
          if ($order_totals[$i]['code'] == 'ot_shipping') $optionsST['shipping'] = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_total')    $optionsST['amount']   = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_tax')      $optionsST['tax_cart']+= round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_subtotal') $optionsST['subtotal'] = round($order_totals[$i]['value'],2);
        } else {
          // handle other order totals:
          global ${$order_totals[$i]['code']};
          if ((substr($order_totals[$i]['text'], 0, 1) == '-') || (isset(${$order_totals[$i]['code']}->credit_class) && ${$order_totals[$i]['code']}->credit_class == true)) {
            // handle credits
            $creditsApplied += round($order_totals[$i]['value'], 2);
          } else {
            // treat all other OT's as if they're related to handling fees or other extra charges to be added/included
            $surcharges += $order_totals[$i]['value'];
          }
        }
      }

      if ($creditsApplied > 0) $optionsST['subtotal'] -= $creditsApplied;
      if ($surcharges > 0) $optionsST['subtotal'] += $surcharges;

      $optionsNB['creditsExist'] = ($creditsApplied > 0) ? TRUE : FALSE;

      // Handle tax-included scenario
      if (DISPLAY_PRICE_WITH_TAX == 'true') $optionsST['tax_cart'] = 0;

      $subtotalPRE = $optionsST;
      // Move shipping tax amount from Tax subtotal into Shipping subtotal for submission to PayPal, since PayPal applies tax to each line-item individually
      $module = strpos($_SESSION['shipping']['id'], '_') > 0 ? substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_')) : $_SESSION['shipping']['id'];
      if (isset($GLOBALS[$module]) && zen_not_null($order->info['shipping_method']) && DISPLAY_PRICE_WITH_TAX != 'true') {
        if ($GLOBALS[$module]->tax_class > 0) {
          $shipping_tax_basis = (!isset($GLOBALS[$module]->tax_basis)) ? STORE_SHIPPING_TAX_BASIS : $GLOBALS[$module]->tax_basis;
          $shippingOnBilling = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
          $shippingOnDelivery = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
          if ($shipping_tax_basis == 'Billing') {
            $shipping_tax = $shippingOnBilling;
          } elseif ($shipping_tax_basis == 'Shipping') {
            $shipping_tax = $shippingOnDelivery;
          } else {
            if (STORE_ZONE == $order->billing['zone_id']) {
              $shipping_tax = $shippingOnBilling;
            } elseif (STORE_ZONE == $order->delivery['zone_id']) {
              $shipping_tax = $shippingOnDelivery;
            } else {
              $shipping_tax = 0;
            }
          }
          $taxAdjustmentForShipping = zen_round(zen_calculate_tax($order->info['shipping_cost'], $shipping_tax), $currencies->currencies[$_SESSION['currency']]['decimal_places']);
          $optionsST['shipping'] += $taxAdjustmentForShipping;
          $optionsST['tax_cart'] -= $taxAdjustmentForShipping;
        }
      }
      $flagSubtotalsUnknownYet = (($optionsST['shipping'] + $optionsST['amount'] + $optionsST['tax_cart'] + $optionsST['subtotal']) == 0);
    } else {
      // if we get here, we don't have any order-total information yet because the customer has clicked Express before starting normal checkout flow
      // thus, we must make a note to manually calculate subtotals, rather than relying on the more robust order-total infrastructure
      $flagSubtotalsUnknownYet = TRUE;
    }

    $decimals = $currencies->get_decimal_places($_SESSION['currency']);

    // loop thru all products to prepare details of quantity and price.
    for ($i=0, $n=sizeof($order->products), $k=0; $i<$n; $i++) {
      // PayPal is inconsistent in how it handles zero-value line-items, so skip this entry if price is zero
      if ($order->products[$i]['final_price'] == 0) {
        continue;
      } else {
        $k++;
      }

      $optionsLI["item_number_$k"] = $order->products[$i]['model'];
      $optionsLI["item_name_$k"]   = $order->products[$i]['name'] . ' [' . (int)$order->products[$i]['id'] . ']';
      // Append *** if out-of-stock.
      $optionsLI["item_name_$k"]  .= ((zen_get_products_stock($order->products[$i]['id']) - $order->products[$i]['qty']) < 0 ? STOCK_MARK_PRODUCT_OUT_OF_STOCK : '');
      // if there are attributes, loop thru them and add to description
      if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0 ) {
        for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
          $optionsLI["item_name_$k"] .= "\n " . $order->products[$i]['attributes'][$j]['option'] .
                                        ': ' . $order->products[$i]['attributes'][$j]['value'];
        } // end loop
      } // endif attribute-info

      // PayPal can't handle fractional-quantity values, so convert it to qty 1 here
      if (is_float($order->products[$i]['qty']) && ($order->products[$i]['qty'] != (int)$order->products[$i]['qty'] || $flag_treat_as_partial)) {
        $optionsLI["item_name_$k"] = '('.$order->products[$i]['qty'].' x ) ' . $optionsLI["item_name_$k"];
        // zen_add_tax already handles whether DISPLAY_PRICES_WITH_TAX is set
        $optionsLI["amount_$k"] = zen_round(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $decimals) * $order->products[$i]['qty'], $decimals);
        $optionsLI["quantity_$k"] = 1;
        // no line-item tax component
      } else {
        $optionsLI["quantity_$k"] = $order->products[$i]['qty'];
        $optionsLI["amount_$k"] = zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $decimals);
      }

      $subTotalLI += ($optionsLI["quantity_$k"] * $optionsLI["amount_$k"]);
//      $subTotalTax += ($optionsLI["quantity_$k"] * $optionsLI["tax_$k"]);

      // add line-item for one-time charges on this product
      if ($order->products[$i]['onetime_charges'] != 0 ) {
        $k++;
        $optionsLI["item_name_$k"]   = MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX . substr(htmlentities($order->products[$i]['name'], ENT_QUOTES, 'UTF-8'), 0, 120);
        $optionsLI["amount_$k"]    = zen_round(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), $decimals);
        $optionsLI["quantity_$k"]    = 1;
//        $optionsLI["tax_$k"] = zen_round(zen_calculate_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), $decimals);
        $subTotalLI += $optionsLI["amount_$k"];
//        $subTotalTax += $optionsLI["tax_$k"];
      }
      $numberOfLineItemsProcessed = $k;
    }  // end for loopthru all products

    // add line items for any surcharges added by order-total modules
    if ($surcharges > 0) {
      $numberOfLineItemsProcessed++;
      $k = $numberOfLineItemsProcessed;
      $optionsLI["item_name_$k"]   = MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_SURCHARGES_SHORT;
      $optionsLI["amount_$k"]    = $surcharges;
      $optionsLI["quantity_$k"]    = 1;
      $subTotalLI += $surcharges;
    }

    // add line items for discounts such as gift certificates and coupons
    if ($creditsApplied > 0) {
      $numberOfLineItemsProcessed++;
      $k = $numberOfLineItemsProcessed;
      $optionsLI["item_name_$k"]   = MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_DISCOUNTS_SHORT;
      $optionsLI["amount_$k"]    = (-1 * $creditsApplied);
      $optionsLI["quantity_$k"]    = 1;
      $subTotalLI -= $creditsApplied;
    }

    // Reformat properly
    // Replace & and = and % with * if found.
    // reformat properly according to API specs
    // Remove HTML markup from name if found
    for ($k=1, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
      $optionsLI["item_name_$k"] = str_replace(array('&','=','%'), '*', $optionsLI["item_name_$k"]);
      $optionsLI["item_name_$k"] = zen_clean_html($optionsLI["item_name_$k"], 'strong');
      $optionsLI["item_name_$k"]   = substr($optionsLI["item_name_$k"], 0, 127);
      $optionsLI["amount_$k"] = round($optionsLI["amount_$k"], 2);

      if (isset($optionsLI["item_number_$k"])) {
        if ($optionsLI["item_number_$k"] == '') {
          unset($optionsLI["item_number_$k"]);
        } else {
          $optionsLI["item_number_$k"] = str_replace(array('&','=','%'), '*', $optionsLI["item_number_$k"]);
          $optionsLI["item_number_$k"] = substr($optionsLI["item_number_$k"], 0, 127);
        }
      }

//      if (isset($optionsLI["tax_$k"]) && ($optionsLI["tax_$k"] != '' || $optionsLI["tax_$k"] > 0)) {
//        $optionsLI["tax_$k"] = round($optionsLI["tax_$k"], 2);
//      }
    }

    // Sanity Check of line-item subtotals
    $optionsLI['num_cart_items'] = 0;
    for ($j=1; $j<$k; $j++) {
      $itemAMT = $optionsLI["amount_$j"];
      $itemQTY = $optionsLI["quantity_$j"];
      $itemTAX = (isset($optionsLI["tax_$j"]) ? $optionsLI["tax_$j"] : 0);
      $sumOfLineItems += ($itemQTY * $itemAMT);
      $sumOfLineTax += ($itemQTY * $itemTAX);
      $optionsLI['num_cart_items']++;
    }
    $sumOfLineItems = round($sumOfLineItems, 2);
    $sumOfLineTax = round($sumOfLineTax, 2);

    if ($sumOfLineItems == 0) {
      $sumOfLineTax = 0;
      $optionsLI = array();
      $discountProblemsFlag = TRUE;
      if ($optionsST['shipping'] == $optionsST['amount']) {
        $optionsST['shipping'] = 0;
      }
    }

//    // Sanity check -- if tax-included pricing is causing problems, remove the numbers and put them in a comment instead:
//    $stDiffTaxOnly = (strval($sumOfLineItems - $sumOfLineTax - round($optionsST['amount'], 2)) + 0);
//    if (DISPLAY_PRICE_WITH_TAX == 'true' && $stDiffTaxOnly == 0 && ($optionsST['tax_cart'] != 0 && $sumOfLineTax != 0)) {
//      $optionsNB['DESC'] = 'Tax included in prices: ' . $sumOfLineTax . ' (' . $optionsST['tax_cart'] . ') ';
//      $optionsST['tax_cart'] = 0;
//      for ($k=1, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
//        if (isset($optionsLI["tax_$k"])) unset($optionsLI["tax_$k"]);
//      }
//    }

//    // Do sanity check -- if any of the line-item subtotal math doesn't add up properly, skip line-item details,
//    // so that the order can go through even though PayPal isn't being flexible to handle Zen Cart's diversity
//    if ((strval($subTotalTax) - strval($sumOfLineTax)) > 0.02) {
//      $ipn_logging('getLineItemDetails 3', 'Tax Subtotal does not match sum of taxes for line-items. Tax details are being removed from line-item submission data.' . "\n" . $sumOfLineTax . ' ' . $subTotalTax . print_r(array_merge($optionsST, $optionsLI), true));
//      for ($k=1, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
//        if (isset($optionsLI["tax_$k"])) unset($optionsLI["tax_$k"]);
//      }
//      $subTotalTax = 0;
//      $sumOfLineTax = 0;
//    }

//    // If coupons exist and there's a calculation problem, then it's likely that taxes are incorrect, so reset L_TAXAMTn values
//    if ($creditsApplied > 0 && (strval($optionsST['tax_cart']) != strval($sumOfLineTax))) {
//      $pre = $optionsLI;
//      for ($k=1, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
//        if (isset($optionsLI["tax_$k"])) unset($optionsLI["tax_$k"]);
//      }
//      $ipn_logging('getLineItemDetails 4', 'Coupons/Discounts have affected tax calculations, so tax details are being removed from line-item submission data.' . "\n" . $sumOfLineTax . ' ' . $optionsST['tax_cart'] . "\n" . print_r(array_merge($optionsST, $pre, $optionsNB), true) . "\nAFTER:" . print_r(array_merge($optionsST, $optionsLI, $optionsNB), TRUE));
//      $subTotalTax = 0;
//      $sumOfLineTax = 0;
//    }

    // disable line-item tax details, leaving only TAXAMT subtotal as tax indicator
    for ($k=1, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
      if (isset($optionsLI["tax_$k"])) unset($optionsLI["tax_$k"]);
    }

    // check subtotals
    if ((strval($optionsST['subtotal']) > 0 && strval($subTotalLI) > 0 && strval($subTotalLI) != strval($optionsST['subtotal'])) || strval($subTotalLI) - strval($sumOfLineItems) != 0) {
      ipn_logging('getLineItemDetails 5', 'Line-item subtotals do not add up properly. Line-item-details skipped.' . "\n" . strval($sumOfLineItems) . ' ' . strval($subTotalLI) . ' ' . print_r(array_merge($optionsST, $optionsLI), true));
      $optionsLI = array();
      $optionsLI["item_name_0"] = MODULE_PAYMENT_PAYPAL_PURCHASE_DESCRIPTION_TITLE;
      $optionsLI["amount_0"]  = $sumOfLineItems = $subTotalLI = $optionsST['subtotal'];
    }

    // check whether discounts are causing a problem
    if (strval($optionsST['subtotal']) < 0) {
      $pre = (array_merge($optionsST, $optionsLI));
      $optionsST['subtotal'] = $optionsST['amount'];
      $optionsLI = array();
      $optionsLI["item_name_0"] = MODULE_PAYMENT_PAYPAL_PURCHASE_DESCRIPTION_TITLE;
      $optionsLI["amount_0"]  = $sumOfLineItems = $subTotalLI = $optionsST['subtotal'];
      if ($optionsST['amount'] < $optionsST['tax_cart']) $optionsST['tax_cart'] = 0;
      if ($optionsST['amount'] < $optionsST['shipping']) $optionsST['shipping'] = 0;
      $discountProblemsFlag = TRUE;
      ipn_logging('getLineItemDetails 6', 'Discounts have caused the subtotal to calculate incorrectly. Line-item-details cannot be submitted.' . "\nBefore:" . print_r($pre, TRUE) . "\nAfter:" . print_r(array_merge($optionsST, $optionsLI), true));
    }

    // if amount or subtotal values are 0 (ie: certain OT modules disabled), we have to get subtotals manually
    if ((!isset($optionsST['amount']) || $optionsST['amount'] == 0 || $flagSubtotalsUnknownYet == TRUE || $optionsST['subtotal'] == 0) && $discountProblemsFlag != TRUE) {
      $optionsST['subtotal'] = $sumOfLineItems;
      $optionsST['tax_cart'] = $sumOfLineTax;
      if ($subTotalShipping > 0) $optionsST['shipping'] = $subTotalShipping;
      $optionsST['amount'] = $sumOfLineItems + $optionsST['tax_cart'] + $optionsST['shipping'];
    }
    ipn_logging('getLineItemDetails 7 - subtotal comparisons', 'BEFORE line-item calcs: ' . print_r($subtotalPRE, true) . ' - AFTER doing line-item calcs: ' . print_r(array_merge($optionsST, $optionsLI, $optionsNB), true));

    // if subtotals are not adding up correctly, then skip sending any line-item or subtotal details to PayPal
    $stAll = round(strval($optionsST['subtotal'] + $optionsST['tax_cart'] + $optionsST['shipping']), 2);
    $stDiff = strval($optionsST['amount'] - $stAll);
    $stDiffRounded = (strval($stAll - round($optionsST['amount'], 2)) + 0);

    // unset any subtotal values that are zero
    if (isset($optionsST['subtotal']) && $optionsST['subtotal'] == 0) unset($optionsST['subtotal']);
    if (isset($optionsST['tax_cart']) && $optionsST['tax_cart'] == 0) unset($optionsST['tax_cart']);
    if (isset($optionsST['shipping']) && $optionsST['shipping'] == 0) unset($optionsST['shipping']);

    // tidy up all values so that they comply with proper format (rounded to 2 decimals for PayPal US use )
    if (!defined('PAYPALWPP_SKIP_LINE_ITEM_DETAIL_FORMATTING') || PAYPALWPP_SKIP_LINE_ITEM_DETAIL_FORMATTING != 'true' || in_array($order->info['currency'], array('JPY', 'NOK', 'HUF', 'TWD'))) {
      if (is_array($optionsST)) foreach ($optionsST as $key=>$value) {
        $optionsST[$key] = round($value, ((int)$currencies->get_decimal_places($restrictedCurrency) == 0 ? 0 : 2));
      }
      if (is_array($optionsLI)) foreach ($optionsLI as $key=>$value) {
        if (substr($key, 0, 8) == 'tax_' && ($optionsLI[$key] == '' || $optionsLI[$key] == 0)) {
          unset($optionsLI[$key]);
        } else {
          if (strstr($key, 'amount')) $optionsLI[$key] = round($value, ((int)$currencies->get_decimal_places($restrictedCurrency) == 0 ? 0 : 2));
        }
      }
    }

    ipn_logging('getLineItemDetails 8', 'checking subtotals... ' . "\n" . print_r(array_merge(array('calculated total'=>round($stAll, ((int)$currencies->get_decimal_places($restrictedCurrency) == 0 ? 0 : 2))), $optionsST), true) . "\n-------------------\ndifference: " . ($stDiff + 0) . '  (abs+rounded: ' . ($stDiffRounded + 0) . ')');

    if ( $stDiffRounded != 0) {
      ipn_logging('getLineItemDetails 9', 'Subtotals Bad. Skipping line-item/subtotal details');
      return array();
    }

    ipn_logging('getLineItemDetails 10', 'subtotals balance - okay' . "\nSubmitting:   " . print_r(array_merge($optionsST, $optionsLI, $optionsNB), true));

    // Send Subtotal and LineItem results back to be submitted to PayPal
    return array_merge($optionsST, $optionsLI, $optionsNB);
  }

/**
 * Debug logging
 */
  function ipn_logging($stage, $message = '') {
    if (defined('IPN_EXTRA_DEBUG_DETAILS') && IPN_EXTRA_DEBUG_DETAILS != '')
    ipn_add_error_log($stage . ($message != '' ? ': ' . $message : ''));
  }
  function ipn_add_error_log($message, $paypal_instance_id = '') {
    if ($paypal_instance_id == '') $paypal_instance_id = date('mdYGi');
    $logfilename = 'includes/modules/payment/paypal/logs/ipn_' . $paypal_instance_id . '.log';
    if (defined('DIR_FS_LOGS')) $logfilename = DIR_FS_LOGS . '/ipn_' . $paypal_instance_id . '.log';
    $fp = @fopen($logfilename, 'a');
    if ($fp) {
      fwrite($fp, date('M d Y G:i') . ' -- ' . $message . "\n\n");
      fclose($fp);
    }
    return $logfilename;
  }

