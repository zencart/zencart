<?php
/**
 * ipn_main_handler.php callback handler for PayPal IPN notifications
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Tue Oct 13 15:33:13 2015 -0400 Modified in v1.5.5 $
 */
if (!defined('TEXT_RESELECT_SHIPPING')) define('TEXT_RESELECT_SHIPPING', 'You have changed the items in your cart since shipping was last calculated, and costs may have changed. Please verify/re-select your shipping method.');

/**
 * handle Express Checkout processing:
 */
if (isset($_GET['type']) && $_GET['type'] == 'ec') {
  // this is an EC handler request
  require('includes/application_top.php');

// Validate Cart for checkout
  $_SESSION['valid_to_checkout'] = true;
  $_SESSION['cart']->get_products(true);
  if ($_SESSION['valid_to_checkout'] == false || $_SESSION['cart']->count_contents() <= 0) {
    $messageStack->add_session('shopping_cart', ERROR_CART_UPDATE, 'error');
    zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
  }

  // Stock Check to prevent checkout if cart contents rules violations exist
  if ( STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT != 'true' && isset($_SESSION['cart']) ) {
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $qtyAvailable = zen_get_products_stock($products[$i]['id']);
      if ($qtyAvailable - $products[$i]['quantity'] < 0 || $qtyAvailable - $_SESSION['cart']->in_cart_mixed($products[$i]['id']) < 0) {
        zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
        break;
      }
    }
  }
  // if cart contents has changed since last pass, reset
  if (isset($_SESSION['cart']->cartID)) {
    if (isset($_SESSION['cartID'])) {  // This will only be set if customer has been to the checkout_shipping page. Will *not* be set if starting via EC Shortcut button, so don't want to redirect in that case.
      if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
        if (isset($_SESSION['shipping'])) {
          unset($_SESSION['shipping']);
          $messageStack->add_session('checkout_shipping', TEXT_RESELECT_SHIPPING, 'error');
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }
      }
    }
//  } else {
//    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
  }

  require(DIR_WS_CLASSES . 'payment.php');
  // See if we were sent a request to clear the session for PayPal.
  if (isset($_GET['clearSess']) || isset($_GET['amp;clearSess']) || isset($_GET['ec_cancel']) || isset($_GET['amp;ec_cancel'])) {
    // Unset the PayPal EC information.
    unset($_SESSION['paypal_ec_temp']);
    unset($_SESSION['paypal_ec_token']);
    unset($_SESSION['paypal_ec_payer_id']);
    unset($_SESSION['paypal_ec_payer_info']);
  }
  // See if the paypalwpp module is enabled.
  if (defined('MODULE_PAYMENT_PAYPALWPP_STATUS') && MODULE_PAYMENT_PAYPALWPP_STATUS == 'True') {
    $paypalwpp_module = 'paypalwpp';
    // init the payment object
    $payment_modules = new payment($paypalwpp_module);
    // set the payment, if they're hitting us here then we know
    // the payment method selected right now.
    $_SESSION['payment'] = $paypalwpp_module;
    // check to see if we have a token sent back from PayPal.
    if (!isset($_SESSION['paypal_ec_token']) || empty($_SESSION['paypal_ec_token'])) {
      // We have not gone to PayPal's website yet in order to grab
      // a token at this time.  This will send the customer over to PayPal's
      // website to login and return a token
      $$paypalwpp_module->ec_step1();
    } else {
      // This will push on the second step of the paypal ec payment
      // module, as we already have a PayPal express checkout token
      // at this point.
      $$paypalwpp_module->ec_step2();
    }
  }
?>
<html>
Processing...
</html>
  <?php

  /**
   * If we got here, we are an IPN transaction (not Express Checkout):
   */

} else {
  /**
   * detect odd cases of extra-url-encoded POST data coming back from PayPal
   */
  foreach(array('receiver_email', 'payer_email', 'business', 'txn_type', 'transaction_subject', 'custom', 'payment_date', 'item_number', 'item_name', 'first_name', 'last_name') as $key) {
    if (isset($_POST[$key]) && strstr($_POST[$key], '%')) {
      $_POST[$key] = urldecode($_POST[$key]);
    }
  }
  /**
   * detect type of transaction
   */
  $isECtransaction = ((isset($_POST['txn_type']) && $_POST['txn_type']=='express_checkout') || (isset($_POST['custom']) && in_array(substr($_POST['custom'], 0, 3), array('EC-', 'DP-', 'WPP')))); /*|| $_POST['txn_type']=='cart'*/
  $isDPtransaction = (isset($_POST['custom']) && in_array(substr($_POST['custom'], 0, 3), array('DP-', 'WPP')));
  /**
   * set paypal-specific application_top parameters
   */
  $current_page_base = 'paypalipn';
  $loaderPrefix = 'paypal_ipn';
  $show_all_errors = FALSE;
  require('includes/application_top.php');

  $extraDebug = (defined('IPN_EXTRA_DEBUG_DETAILS') && IPN_EXTRA_DEBUG_DETAILS == 'All');

  if (  (defined('MODULE_PAYMENT_PAYPALWPP_DEBUGGING') && strstr(MODULE_PAYMENT_PAYPALWPP_DEBUGGING, 'Log')) ||
      (defined('MODULE_PAYMENT_PAYPAL_IPN_DEBUG') && strstr(MODULE_PAYMENT_PAYPAL_IPN_DEBUG, 'Log')) ||
      ($_REQUEST['ppdebug'] == 'on' && strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) || $extraDebug  ) {
    $show_all_errors = true;
    $debug_logfile_path = ipn_debug_email('Breakpoint: 0 - Initializing debugging.');
    $logdir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : 'includes/modules/payment/paypal/logs';
    if ($debug_logfile_path == '') $debug_logfile_path = $logdir . '/ipn_debug_php_errors-'.time().'.log';
    @ini_set('log_errors', 1);
    @ini_set('log_errors_max_len', 0);
    @ini_set('display_errors', 0); // do not output errors to screen/browser/client (only to log file)
    @ini_set('error_log', DIR_FS_CATALOG . $debug_logfile_path);
    error_reporting(version_compare(PHP_VERSION, 5.3, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE : version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT : E_ALL & ~E_NOTICE);
  }

  ipn_debug_email('Breakpoint: Flag Status:' . "\nisECtransaction = " . (int)$isECtransaction . "\nisDPtransaction = " . (int)$isDPtransaction);
  /**
   * do confirmation post-back to PayPal and extract the results for subsequent use
   */
  $info  = ipn_postback();
  $new_status = 1;
  ipn_debug_email('Breakpoint: 1 - Collected data from PayPal notification');

  /**
   * validate transaction -- email address, matching txn record, etc
   */
  if (!ipn_validate_transaction($info, $_POST, 'IPN') === true) {
    if (!$isECtransaction && $_POST['txn_type'] != '') {
      ipn_debug_email('IPN FATAL ERROR :: Transaction did not validate. ABORTED.');
      die();
    }
  }

  if ($isDPtransaction) {
    ipn_debug_email('IPN NOTICE :: This is a Website Payments Pro transaction.  The rest of this log file is INFORMATION ONLY, and is not used for real processing.');
  }

  ipn_debug_email('Breakpoint: 2 - Validated transaction components');
  if ($_POST['exchange_rate'] == '')  $_POST['exchange_rate'] = 1;
  if ($_POST['num_cart_items'] == '') $_POST['num_cart_items'] = 1;
  if ($_POST['settle_amount'] == '')  $_POST['settle_amount'] = 0;

  /**
   * is this a sandbox transaction?
   */
  if (isset($_POST['test_ipn']) && $_POST['test_ipn'] == 1) {
    ipn_debug_email('IPN NOTICE :: Processing SANDBOX transaction.');
  }
  if (isset($_POST['test_internal']) && $_POST['test_internal'] == 1) {
    ipn_debug_email('IPN NOTICE :: Processing INTERNAL TESTING transaction.');
  }
  if (isset($_POST['pending_reason']) && $_POST['pending_reason'] == 'unilateral') {
    ipn_debug_email('*** NOTE: TRANSACTION IS IN *unilateral* STATUS, pending creation of a PayPal account for this receiver_email address.' . "\n" . 'Please create the account, or make sure the PayPal account is *Verified*.');
  }

  ipn_debug_email('Breakpoint: 3 - Communication method verified');
  /**
   * Lookup transaction history information in preparation for matching and relevant updates
   */
  $lookupData  = ipn_lookup_transaction($_POST);
  $ordersID    = $lookupData['order_id'];
  $paypalipnID = $lookupData['paypal_ipn_id'];
  $txn_type    = $lookupData['txn_type'];
  $parentLookup = $txn_type;

  ipn_debug_email('Breakpoint: 4 - ' . 'Details:  txn_type=' . $txn_type . '    ordersID = '. $ordersID . '  IPN_id=' . $paypalipnID . "\n\n" . '   Relevant data from POST:' . "\n     " . 'txn_type = ' . $txn_type . "\n     " . 'parent_txn_id = ' . ($_POST['parent_txn_id'] =='' ? 'None' : $_POST['parent_txn_id']) . "\n     " . 'txn_id = ' . $_POST['txn_id']);

  if (!$isECtransaction && !isset($_POST['parent_txn_id']) && $txn_type != 'cleared-echeck') {
    if (defined('MODULE_PAYMENT_PAYPAL_PDTTOKEN') && MODULE_PAYMENT_PAYPAL_PDTTOKEN != '') {
      ipn_debug_email('IPN NOTICE :: IPN pausing: waiting for PDT to process. Sleeping 10 seconds ...');
      sleep(10);
    }
    if (ipn_get_stored_session($session_stuff) === false) {
      ipn_debug_email('IPN ERROR :: No pending Website Payments Standard session data available.  Might be a duplicate transaction already entered via PDT.');
      $ipnFoundSession = false;
    }
  }

  if ($ipnFoundSession == FALSE && !$isECtransaction && !$isDPtransaction && $txn_type != 'cleared-echeck') {
    ipn_debug_email('NOTICE: IPN Processing Aborted due to missing matching transaction data, as per earlier debug message. Perhaps this transaction was already entered via PDT? Thus there is no need to process this incoming IPN notification.');
    die();
  }

  // this is used to determine whether a record needs insertion. ie: original echeck notice failed, but now we have cleared, so need parent record established:
  $new_record_needed = ($txn_type == 'unique' ? true : false);
  /**
   * evaluate what type of transaction we're processing
   */
  $txn_type = ipn_determine_txn_type($_POST, $txn_type);
  ipn_debug_email('Breakpoint: 5 - Transaction type (txn_type) = ' . $txn_type . '   [parentLookup='.$parentLookup.']');

  if ($_POST['payment_type'] == 'instant' && $isDPtransaction && ((isset($_POST['auth_status']) && $_POST['auth_status'] == 'Completed') || $_POST['payment_status'] == 'Completed')) {
    ipn_debug_email('IPN NOTICE :: DP/Website Payments Pro notice -- IPN Ignored');
    die();
  }

  /**
   * take action based on transaction type and corresponding requirements
   */
  switch ($txn_type) {
    case ($_POST['txn_type'] == 'send_money'):
    case ($_POST['txn_type'] == 'merch_payment'):
    case ($_POST['txn_type'] == 'new_case'):
    case ($_POST['txn_type'] == 'masspay'):
      // these types are irrelevant to ZC transactions
      ipn_debug_email('IPN NOTICE :: Transaction txn_type not relevant to Zen Cart processing. IPN handler aborted.' . $_POST['txn_type']);
      die();
      break;
    case (substr($_POST['txn_type'],0,7) == 'subscr_'):
      // For now we filter out subscription payments
      ipn_debug_email('IPN NOTICE :: Subscription payment - Not currently supported by Zen Cart. IPN handler aborted.');
      die();
      break;

    case 'pending-unilateral':
      // cannot process this order because the merchant's PayPal account isn't valid yet
      ipn_debug_email('IPN NOTICE :: Please create a valid PayPal account and follow the steps to *Verify* it. IPN handler aborted.');
      die();
      break;
    case 'pending-address':
    case 'pending-intl':
    case 'pending-multicurrency':
    case 'pending-verify':
      if (!$isECtransaction) {
        ipn_debug_email('IPN NOTICE :: '.$txn_type.' transaction -- inserting initial record for reference purposes');
        $sql_data_array = ipn_create_order_array($ordersID, $txn_type);
        zen_db_perform(TABLE_PAYPAL, $sql_data_array);
        $sql_data_array = ipn_create_order_history_array($paypalipnID);
        zen_db_perform(TABLE_PAYPAL_PAYMENT_STATUS_HISTORY, $sql_data_array);
        die();
        break;
      }
    case (($txn_type == 'express_checkout' || $isECtransaction) && !strstr($txn_type, 'cleared') && $parentLookup != 'parent'):
      if ($_POST['payment_status'] == 'Completed') {
        // This is an express-checkout transaction -- IPN may not be needed
        if (isset($_POST['auth_status']) && $_POST['auth_status'] == 'Completed') {
          ipn_debug_email('IPN NOTICE :: Express Checkout payment notice on completed order -- IPN Ignored');
          die();
        }
      }
      if ($_POST['payment_type'] == 'instant' && isset($_POST['auth_status']) && $_POST['auth_status'] == 'Pending') {
        ipn_debug_email('IPN NOTICE :: EC/DP notice on pre-auth order -- IPN Ignored');
        die();
      }
      ipn_debug_email('Breakpoint: 5 - midstream checkpoint');
      if (!(substr($txn_type,0,8) == 'pending-' && (int)$ordersID <= 0) && !($new_record_needed && $txn_type == 'echeck-cleared') && $txn_type != 'unique' && $txn_type != 'echeck-denied' && $txn_type != 'voided') {
        ipn_debug_email('Breakpoint: 5 - Record does not need to be processed since it is not new and is not an update. See earlier notices. Processing aborted.');
        break;
      }

    case ($txn_type == 'cart'):
      ipn_debug_email('IPN NOTICE :: This is a detailed-cart transaction');

    case ($txn_type == 'cart' && !$isECtransaction):
      ipn_debug_email('IPN NOTICE :: This is a detailed-cart transaction (i)');

    case (substr($txn_type,0,8) == 'pending-' && (int)$ordersID <= 0):
    case ($new_record_needed && $txn_type == 'echeck-cleared'):
    case 'unique':
      /**
       * delete IPN session from PayPal table -- housekeeping
       */
      $db->Execute("delete from " . TABLE_PAYPAL_SESSION . " where session_id = '" . zen_db_input(str_replace('zenid=', '', $_POST['custom'])) . "'");
      /**
       * require shipping class
       */
      require(DIR_WS_CLASSES . 'shipping.php');
      /**
       * require payment class
       */
      require(DIR_WS_CLASSES . 'payment.php');
      $payment_modules = new payment($_SESSION['payment']);
      $shipping_modules = new shipping($_SESSION['shipping']);
      /**
       * require order class
       */
      require(DIR_WS_CLASSES . 'order.php');
      $order = new order;
      /**
       * require order_total class
       */
      require(DIR_WS_CLASSES . 'order_total.php');
      $order_total_modules = new order_total();
      $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_BEFORE_ORDER_TOTALS_PROCESS');
      $order_totals = $order_total_modules->process();
      $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_TOTALS_PROCESS');

      if (valid_payment($order->info['total'], $_SESSION['currency']) === false && !$isECtransaction && !$isDPtransaction) {
        ipn_debug_email('IPN NOTICE :: Failed because of currency mismatch.');
        die();
      }
      if ($ipnFoundSession === false && !$isECtransaction && !$isDPtransaction) {
        ipn_debug_email('IPN NOTICE :: Unique but no session - Assumed to be a personal payment, rather than a new Website Payments Standard transaction. Ignoring.');
        die();
      }
      if (!strstr($txn_type, 'denied') && !strstr($txn_type, 'failed') && !strstr($txn_type, 'voided')) {
        $insert_id = $order->create($order_totals);
        $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE');
        ipn_debug_email('Breakpoint: 5a - built order -- OID: ' . $insert_id);
        $sql_data_array = ipn_create_order_array($insert_id, $txn_type);
        ipn_debug_email('Breakpoint: 5b - PP table OID: ' . print_r($sql_data_array, true));
        zen_db_perform(TABLE_PAYPAL, $sql_data_array);
        ipn_debug_email('Breakpoint: 5c - PP table OID saved');
        $pp_hist_id = $db->Insert_ID();
        $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_PAYMENT_MODULES_AFTER_ORDER_CREATE');
        ipn_debug_email('Breakpoint: 5d - PP hist ID: ' . $pp_hist_id);
        $sql_data_array = ipn_create_order_history_array($pp_hist_id);
        ipn_debug_email('Breakpoint: 5e - PP hist_data:' . print_r($sql_data_array, true));
        zen_db_perform(TABLE_PAYPAL_PAYMENT_STATUS_HISTORY, $sql_data_array);
        ipn_debug_email('Breakpoint: 5f - PP hist saved');
        $new_status = MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID;
        ipn_debug_email('Breakpoint: 5g - new status code: ' . $new_status);
        if ($_POST['payment_status'] =='Pending') {
          $new_status = (defined('MODULE_PAYMENT_PAYPAL_PROCESSING_STATUS_ID') && (int)MODULE_PAYMENT_PAYPAL_PROCESSING_STATUS_ID > 0 ? (int)MODULE_PAYMENT_PAYPAL_PROCESSING_STATUS_ID : 2);
          ipn_debug_email('Breakpoint: 5h - newer status code: ' . (int)$new_status);
          $sql = "UPDATE " . TABLE_ORDERS  . "
                  SET orders_status = " . (int)$new_status . "
                  WHERE orders_id = '" . (int)$insert_id . "'";
          $db->Execute($sql);
          ipn_debug_email('Breakpoint: 5i - order table updated');
        }
        $sql_data_array = array('orders_id' => (int)$insert_id,
                                'orders_status_id' => (int)$new_status,
                                'date_added' => 'now()',
                                'comments' => 'PayPal status: ' . $_POST['payment_status'] . ' ' . $_POST['pending_reason']. ' @ '.$_POST['payment_date'] . (($_POST['parent_txn_id'] !='') ? "\n" . ' Parent Trans ID:' . $_POST['parent_txn_id'] : '') . "\n" . ' Trans ID:' . $_POST['txn_id'] . "\n" . ' Amount: ' . $_POST['mc_gross'] . ' ' . $_POST['mc_currency'],
                                'customer_notified' => 0
                                );
        ipn_debug_email('Breakpoint: 5j - order stat hist update:' . print_r($sql_data_array, true));
        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        if (MODULE_PAYMENT_PAYPAL_ADDRESS_OVERRIDE == '1') {
          $sql_data_array['comments'] = '**** ADDRESS OVERRIDE ALERT!!! **** CHECK PAYPAL ORDER DETAILS FOR ACTUAL ADDRESS SELECTED BY CUSTOMER!!';
          $sql_data_array['customer_notified'] = -1;
          zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
        ipn_debug_email('Breakpoint: 5k - OSH update done');
        $order->create_add_products($insert_id, 2);
        ipn_debug_email('Breakpoint: 5L - adding products');
        $_SESSION['order_number_created'] = $insert_id;
        $GLOBALS[$_SESSION['payment']]->transaction_id = $_POST['txn_id'];
        $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE_ADD_PRODUCTS');
        $order->send_order_email($insert_id, 2);
        ipn_debug_email('Breakpoint: 5m - emailing customer');
        $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_SEND_ORDER_EMAIL');

        /** Prepare sales-tracking data for use by notifier class **/
        $ototal = $order_subtotal = $credits_applied = 0;
        for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
          if ($order_totals[$i]['code'] == 'ot_subtotal') $order_subtotal = $order_totals[$i]['value'];
          if (${$order_totals[$i]['code']}->credit_class == true) $credits_applied += $order_totals[$i]['value'];
          if ($order_totals[$i]['code'] == 'ot_total') $ototal = $order_totals[$i]['value'];
          if ($order_totals[$i]['code'] == 'ot_tax') $otax = $order_totals[$i]['value'];
          if ($order_totals[$i]['code'] == 'ot_shipping') $oshipping = $order_totals[$i]['value'];
        }
        $commissionable_order = ($order_subtotal - $credits_applied);
        $commissionable_order_formatted = $currencies->format($commissionable_order);
        $_SESSION['order_summary']['order_number'] = $insert_id;
        $_SESSION['order_summary']['order_subtotal'] = $order_subtotal;
        $_SESSION['order_summary']['credits_applied'] = $credits_applied;
        $_SESSION['order_summary']['order_total'] = $ototal;
        $_SESSION['order_summary']['commissionable_order'] = $commissionable_order;
        $_SESSION['order_summary']['commissionable_order_formatted'] = $commissionable_order_formatted;
        $_SESSION['order_summary']['coupon_code'] = urlencode($order->info['coupon_code']);
        $_SESSION['order_summary']['currency_code'] = $order->info['currency'];
        $_SESSION['order_summary']['currency_value'] = $order->info['currency_value'];
        $_SESSION['order_summary']['payment_module_code'] = $order->info['payment_module_code'];
        $_SESSION['order_summary']['shipping_method'] = $order->info['shipping_method'];
        $_SESSION['order_summary']['orders_status'] = $order->info['orders_status'];
        $_SESSION['order_summary']['tax'] = $otax;
        $_SESSION['order_summary']['shipping'] = $oshipping;
        $products_array = array();
        foreach ($order->products as $key=>$val) {
          $products_array[urlencode($val['id'])] = urlencode($val['model']);
        }
        $_SESSION['order_summary']['products_ordered_ids'] = implode('|', array_keys($products_array));
        $_SESSION['order_summary']['products_ordered_models'] = implode('|', array_values($products_array));

        $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_HANDLE_AFFILIATES', 'paypalipn');
        $_SESSION['cart']->reset(true);
        ipn_debug_email('Breakpoint: 5n - emptying cart');
        $ordersID = $insert_id;
        $paypalipnID = $pp_hist_id;
        ipn_debug_email('Breakpoint: 6 - Completed IPN order add.' . '    ordersID = '. $ordersID . '  IPN tracking record = ' . $paypalipnID);
        if (!($new_record_needed && $txn_type == 'echeck-cleared'))  break;
      }
    case 'parent':
    case 'cleared-address':
    case 'cleared-multicurrency':
    case 'cleared-echeck':
    case 'cleared-authorization':
    case 'cleared-verify':
    case 'cleared-intl':
    case 'cleared-review':
    case 'echeck-denied':
    case 'echeck-cleared':
    case 'denied-address':
    case 'denied-multicurrency':
    case 'denied-echeck':
    case 'failed-echeck':
    case 'denied-intl':
    case 'denied':
    case 'voided':
    case 'express-checkout-cleared':
      ipn_debug_email('IPN NOTICE :: Storing order/update details for order #' . $ordersID . ' txn_id: ' . $_POST['txn_id'] . ' PP IPN ID: ' . $paypalipnID);
      if ($txn_type == 'parent') {
        $sql_data_array = ipn_create_order_array($ordersID, $txn_type);
        zen_db_perform(TABLE_PAYPAL, $sql_data_array);
        $paypalipnID = $db->Insert_ID();
      } else {
        $sql_data_array = ipn_create_order_update_array($txn_type);
        zen_db_perform(TABLE_PAYPAL, $sql_data_array, 'update', "txn_id='" . ($txn_type == 'cleared-authorization' ? $_POST['parent_txn_id'] : $_POST['txn_id']) . "'");
        $sql = "select paypal_ipn_id from " . TABLE_PAYPAL . " where txn_id=:txn:";
        $sql = $db->bindVars($sql, ':txn:', $_POST['txn_id'], 'string');
        $result = $db->Execute($sql);
        $paypalipnID = $result->fields['paypal_ipn_id'];
      }
      $sql_data_array = ipn_create_order_history_array($paypalipnID);
      zen_db_perform(TABLE_PAYPAL_PAYMENT_STATUS_HISTORY, $sql_data_array);
      ipn_debug_email('IPN NOTICE :: Added PP status-history record for order #' . $ordersID . ' txn_id: ' . $_POST['txn_id'] . ' (updated/child) PP IPN ID: ' . $paypalipnID);

      switch ($txn_type) {
        case 'voided':
        case ($_POST['payment_status'] == 'Refunded' || $_POST['payment_status'] == 'Reversed' || $_POST['payment_status'] == 'Voided'):
          //payment_status=Refunded or payment_status=Voided
          $new_status = MODULE_PAYMENT_PAYPALWPP_REFUNDED_STATUS_ID;
          if (defined('MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID > 0 && !$isECtransaction) $new_status = MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID;
          break;
        case 'echeck-denied':
        case 'denied-echeck':
        case 'failed-echeck':
          //payment_status=Denied or failed
          $new_status = ($isECtransaction ? MODULE_PAYMENT_PAYPALWPP_REFUNDED_STATUS_ID : MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID);
          break;
        case 'echeck-cleared':
          $new_status = (defined('MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID') ? MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID : 2);
          break;
        case ($txn_type=='express-checkout-cleared' || substr($txn_type,0,8) == 'cleared-'):
          //express-checkout-cleared
          $new_status = ($isECtransaction && defined('MODULE_PAYMENT_PAYPALWPP_ORDER_STATUS_ID') ? MODULE_PAYMENT_PAYPALWPP_ORDER_STATUS_ID : MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID);
          if ((int)$new_status == 0) $new_status = 2;
          break;
        case 'pending-auth':
          // pending authorization
          $new_status = ($isECtransaction ? MODULE_PAYMENT_PAYPALWPP_REFUNDED_STATUS_ID : MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID);
          break;
        case (substr($txn_type,0,7) == 'denied-'):
          // denied for any other reason - treat as pending for now
        case (substr($txn_type,0,8) == 'pending-'):
          // pending anything
          $new_status = ($isECtransaction ? MODULE_PAYMENT_PAYPALWPP_ORDER_PENDING_STATUS_ID : MODULE_PAYMENT_PAYPAL_PROCESSING_STATUS_ID);
          break;
      }
      // update order status history with new information
      ipn_debug_email('IPN NOTICE :: Set new status ' . $new_status . " for order ID = " .  $ordersID . ($_POST['pending_reason'] != '' ? '.   Reason_code = ' . $_POST['pending_reason'] : '') );
      if ((int)$new_status == 0) $new_status = 1;
      if (in_array($_POST['payment_status'], array('Refunded', 'Reversed', 'Denied', 'Failed'))
           || substr($txn_type,0,8) == 'cleared-' || $txn_type=='echeck-cleared' || $txn_type == 'express-checkout-cleared') {
        ipn_update_orders_status_and_history($ordersID, $new_status, $txn_type);
        $zco_notifier->notify('NOTIFY_PAYPALIPN_STATUS_HISTORY_UPDATE', array($ordersID, $new_status, $txn_type));
      }
      break;
    default:
      // can't understand result found. Thus, logging and aborting.
      ipn_debug_email('IPN WARNING :: Could not process for txn type: ' . $txn_type . "\n" . ' postdata=' . str_replace('&', " \n&", urldecode(print_r($_POST, TRUE))));
  }
  // debug info only
  switch (TRUE) {
    case ($txn_type == 'pending-echeck' && (int)$ordersID > 0):
      ipn_debug_email('IPN NOTICE :: Pending echeck transaction for existing order. No action required. Waiting for echeck to clear.');
      break;
    case ($txn_type == 'pending-multicurrency' && (int)$ordersID > 0):
      ipn_debug_email('IPN NOTICE :: Pending multicurrency transaction for existing order. No action required. Waiting for merchant to "accept" the order via PayPal account console.');
      break;
    case ($txn_type == 'pending-address' && (int)$ordersID > 0):
      ipn_debug_email('IPN NOTICE :: "Pending address" transaction for existing order. No action required. Waiting for address approval by store owner via PayPal account console.');
      break;
    case ($txn_type == 'pending-paymentreview' && (int)$ordersID > 0):
      ipn_debug_email('IPN NOTICE :: "Pending payment review" transaction for existing order. No action required. Waiting for PayPal to complete their Payment Review. Do not ship order until review is completed.');
      break;
  }
}

