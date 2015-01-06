<?php
/**
 * FirstData/Linkpoint/Yourpay API Payment Module
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright 2003 Jason LeBaron
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Aug 28 16:48:39 2012 -0400 Modified in v1.5.1 $
 */
  if (!defined('TABLE_LINKPOINT_API')) define('TABLE_LINKPOINT_API', DB_PREFIX . 'linkpoint_api');
  @define('MODULE_PAYMENT_LINKPOINT_API_CODE_DEBUG' ,'off'); // debug for programmer use only

class linkpoint_api extends base {
  var $code, $title, $description, $enabled, $payment_status, $auth_code, $transaction_id;
  var $_logDir = DIR_FS_SQL_CACHE;

  // class constructor
  function __construct() {
    global $order, $messageStack;
    $this->code = 'linkpoint_api';
    $this->enabled = ((MODULE_PAYMENT_LINKPOINT_API_STATUS == 'True') ? true : false); // Whether the module is installed or not
    if ($_GET['main_page'] != '' && !IS_ADMIN_FLAG === true) {
      $this->title = MODULE_PAYMENT_LINKPOINT_API_TEXT_CATALOG_TITLE; // Payment module title in Catalog
    } else {
      $this->title = MODULE_PAYMENT_LINKPOINT_API_TEXT_ADMIN_TITLE; // Payment module title in Admin
      if ($this->enabled && !function_exists('curl_init')) $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_ERROR_CURL_NOT_FOUND, 'error');
    }
    $this->description = MODULE_PAYMENT_LINKPOINT_API_TEXT_DESCRIPTION;  // Descriptive Info about module in Admin
    $this->sort_order = MODULE_PAYMENT_LINKPOINT_API_SORT_ORDER; // Sort Order of this payment option on the customer payment page
    $this->form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false); // Page to go to upon submitting page info

    $this->order_status = (int)DEFAULT_ORDERS_STATUS_ID;
    if ((int)MODULE_PAYMENT_LINKPOINT_API_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_LINKPOINT_API_ORDER_STATUS_ID;
    }
    if (MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE == 'Authorize Only' && (int)MODULE_PAYMENT_LINKPOINT_API_PREAUTH_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_LINKPOINT_API_PREAUTH_ORDER_STATUS_ID;
    }
    $this->zone = (int)MODULE_PAYMENT_LINKPOINT_API_ZONE;

    if (is_object($order)) $this->update_status();

    $this->code_debug = (MODULE_PAYMENT_LINKPOINT_API_CODE_DEBUG=='debug') ? true : false;

    // set error messages if misconfigured
    if (MODULE_PAYMENT_LINKPOINT_API_STATUS == 'True') {
      if (MODULE_PAYMENT_LINKPOINT_API_LOGIN == 'EnterYourStoreNumber') {
        $this->title .= MODULE_PAYMENT_LINKPOINT_API_TEXT_NOT_CONFIGURED;
      } elseif (MODULE_PAYMENT_LINKPOINT_API_LOGIN != '' && !file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/linkpoint_api/' . MODULE_PAYMENT_LINKPOINT_API_LOGIN . '.pem') ) {
        $this->title .= MODULE_PAYMENT_LINKPOINT_API_TEXT_PEMFILE_MISSING;
      } elseif (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE != 'LIVE: Production') {
        $this->title .= MODULE_PAYMENT_LINKPOINT_API_TEXT_TEST_MODE;
      }
    }

    $this->_logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;
  }


  // class methods

  function update_status() {
    global $order, $db;
    if (IS_ADMIN_FLAG === false) {
      // if store is not running in SSL, cannot offer credit card module, for PCI reasons
      if (!defined('ENABLE_SSL') || ENABLE_SSL != 'true') $this->enabled = FALSE;
    }
    // check other reasons for the module to be deactivated:
    if ($this->enabled && (int)$this->zone > 0 && isset($order->billing['country']['id'])) {
      $check_flag = false;
      $sql = "SELECT zone_id
              FROM " . TABLE_ZONES_TO_GEO_ZONES . "
              WHERE geo_zone_id = :zoneId
              AND zone_country_id = :countryId
              ORDER BY zone_id";
      $sql = $db->bindVars($sql, ':zoneId', $this->zone, 'integer');
      $sql = $db->bindVars($sql, ':countryId', $order->billing['country']['id'], 'integer');
      $check = $db->Execute($sql);
      while (!$check->EOF) {
        if ($check->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
        $check->MoveNext();
      }

      if (!$check_flag) {
        $this->enabled = false;
      }
    }
    // if in code-debug mode and IP address is in the down-for-maint list, enable the module (leaves it invisible to non-testers)
    if (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) {
      if ($this->code_debug) $this->enabled=true;
    }

      // other status checks?
    if ($this->enabled) {
      // other checks here
    }
  }

  // Validate the credit card information via javascript (Number, Owner, and CVV Lengths)
  function javascript_validation() {
    $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
          '    var cc_owner = document.checkout_payment.linkpoint_api_cc_owner.value;' . "\n" .
          '    var cc_number = document.checkout_payment.linkpoint_api_cc_number.value;' . "\n" .
          '    var cc_cvv = document.checkout_payment.linkpoint_api_cc_cvv.value;' . "\n" .
          '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
          '      error_message = error_message + "' . MODULE_PAYMENT_LINKPOINT_API_TEXT_JS_CC_OWNER . '";' . "\n" .
          '      error = 1;' . "\n" .
          '    }' . "\n" .
          '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
          '      error_message = error_message + "' . MODULE_PAYMENT_LINKPOINT_API_TEXT_JS_CC_NUMBER . '";' . "\n" .
          '      error = 1;' . "\n" .
          '    }' . "\n" .
          '         if (cc_cvv == "" || cc_cvv.length < "3") {' . "\n".
          '           error_message = error_message + "' . MODULE_PAYMENT_LINKPOINT_API_TEXT_JS_CC_CVV . '";' . "\n" .
          '           error = 1;' . "\n" .
          '         }' . "\n" .
          '  }' . "\n";

    return $js;
  }

  // Display Credit Card Information Submission Fields on the Checkout Payment Page
  function selection() {
    global $order;

    for ($i=1; $i<13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
    }

    $today = getdate();
    for ($i=$today['year']; $i < $today['year']+15; $i++) {
      $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }

    $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

    $selection = array('id' => $this->code,
                       'module' => $this->title,
                       'fields' => array(array('title' => MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_OWNER,
                                               'field' => zen_draw_input_field('linkpoint_api_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="'.$this->code.'-cc-owner"'. $onFocus . ' autocomplete="off"'),
                                                 'tag' => $this->code.'-cc-owner'),
                                         array('title' => MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_NUMBER,
                                               'field' => zen_draw_input_field('linkpoint_api_cc_number', $ccnum, 'id="'.$this->code.'-cc-number"' . $onFocus . ' autocomplete="off"'),
                                                 'tag' => $this->code.'-cc-number'),
                                         array('title' => MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_EXPIRES,
                                               'field' => zen_draw_pull_down_menu('linkpoint_api_cc_expires_month', $expires_month, strftime('%m'), 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('linkpoint_api_cc_expires_year', $expires_year, '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
                                                 'tag' => $this->code.'-cc-expires-month'),
                                         array('title' => MODULE_PAYMENT_LINKPOINT_API_TEXT_CVV,
                                               'field' => zen_draw_input_field('linkpoint_api_cc_cvv', '', 'size="4" maxlength="4"'. ' id="'.$this->code.'-cc-cvv"' . $onFocus . ' autocomplete="off"') . ' ' . '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . MODULE_PAYMENT_LINKPOINT_API_TEXT_POPUP_CVV_LINK . '</a>',
                                                 'tag' => $this->code.'-cc-cvv')));

    return $selection;
  }


  // Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
  function pre_confirmation_check() {
    global $db, $messageStack;

    include(DIR_WS_CLASSES . 'cc_validation.php');

    $cc_validation = new cc_validation();
    $result = $cc_validation->validate($_POST['linkpoint_api_cc_number'], $_POST['linkpoint_api_cc_expires_month'], $_POST['linkpoint_api_cc_expires_year']);
    $error = '';
    switch ($result) {
      case -1:
        $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
        break;
      case -2:
      case -3:
      case -4:
        $error = TEXT_CCVAL_ERROR_INVALID_DATE;
        break;
      case false:
        $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
        break;
    }

    // save record of errors which occur during checkout_payment validation phase but haven't been sent to gateway yet
    if ( ($result == false) || ($result < 1) ) {
      $payment_error_return = 'payment_error=' . $this->code ;
      $error_info2 = '&error=' . urlencode($error) . '&linkpoint_api_cc_owner=' . urlencode($_POST['linkpoint_api_cc_owner']) . '&linkpoint_api_cc_expires_month=' . $_POST['linkpoint_api_cc_expires_month'] . '&linkpoint_api_cc_expires_year=' . $_POST['linkpoint_api_cc_expires_year'];
      $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->', 'error');

      if (MODULE_PAYMENT_LINKPOINT_API_STORE_DATA == 'True') {
        $cc_type = $cc_validation->cc_type;
        $cc_number_clean = $cc_validation->cc_number;
        $cc_expiry_month = $_POST['linkpoint_api_cc_expires_month'];
        $cc_expiry_year = $_POST['linkpoint_api_cc_expires_year'];
        $error_returned = $payment_error_return . $error_info2;

        $cc_number = (strlen($cc_number_clean) > 8) ? substr($cc_number_clean, 0, 4) . str_repeat('X', (strlen($cc_number_clean) - 8)) . substr($cc_number_clean, -4) : substr($cc_number_clean, 0, 3) . '**short**';

        while (strstr($error_returned, '%3A')) $error_returned = str_replace('%3A', ' ', $error_returned);
        while (strstr($error_returned, '%2C')) $error_returned = str_replace('%2C', ' ', $error_returned);
        while (strstr($error_returned, '+')) $error_returned = str_replace('+', ' ', $error_returned);
        $error_returned = str_replace('&', ' &amp;', $error_returned);
        $cust_info =  $error_returned;

        $message = addslashes($message);
        $cust_info = addslashes($cust_info);
        $all_response_info = addslashes($all_response_info);


        //  Store Transaction history in Database
        $sql_data_array= array(array('fieldName'=>'lp_trans_num', 'value'=>'', 'type'=>'string'),
                               array('fieldName'=>'order_id', 'value'=>0, 'type'=>'integer'),
                               array('fieldName'=>'approval_code', 'value'=>'N/A', 'type'=>'string'),
                               array('fieldName'=>'transaction_response_time', 'value'=>'N/A', 'type'=>'string'),
                               array('fieldName'=>'r_error', 'value'=>'**CC Info Failed Validation during pre-processing**', 'type'=>'string'),
                               array('fieldName'=>'customer_id', 'value'=>$_SESSION['customer_id'] , 'type'=>'integer'),
                               array('fieldName'=>'avs_response', 'value'=>'', 'type'=>'string'),
                               array('fieldName'=>'transaction_result', 'value'=>'*CUSTOMER ERROR*', 'type'=>'string'),
                               array('fieldName'=>'message', 'value'=>$message . ' -- ' . $all_response_info, 'type'=>'string'),
                               array('fieldName'=>'transaction_time', 'value'=>time(), 'type'=>'string'),
                               array('fieldName'=>'transaction_reference_number', 'value'=>'', 'type'=>'string'),
                               array('fieldName'=>'fraud_score', 'value'=>0, 'type'=>'integer'),
                               array('fieldName'=>'cc_number', 'value'=>$cc_number, 'type'=>'string'),
                               array('fieldName'=>'cust_info', 'value'=>$cust_info, 'type'=>'string'),
                               array('fieldName'=>'chargetotal', 'value'=>0, 'type'=>'string'),
                               array('fieldName'=>'cc_expire', 'value'=>$cc_month . '/' . $cc_year, 'type'=>'string'),
                               array('fieldName'=>'ordertype', 'value'=>'N/A', 'type'=>'string'),
                               array('fieldName'=>'date_added', 'value'=>'now()', 'type'=>'noquotestring'));
        $db->perform(TABLE_LINKPOINT_API, $sql_data_array);
      }
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    // if no error, continue with validated data:
    $this->cc_card_type = $cc_validation->cc_type;
    $this->cc_card_number = $cc_validation->cc_number;
    $this->cc_expiry_month = $cc_validation->cc_expiry_month;
    $this->cc_expiry_year = $cc_validation->cc_expiry_year;
  }

  // Display Credit Card Information on the Checkout Confirmation Page
  function confirmation() {
    $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                          'fields' => array(array('title' => MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_OWNER,
                                                  'field' => $_POST['linkpoint_api_cc_owner']),
                                            array('title' => MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_NUMBER,
                                                  'field' => str_repeat('X', (strlen($this->cc_card_number) - 4)) . substr($this->cc_card_number, -4)),
                                            array('title' => MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_EXPIRES,
                                                  'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['linkpoint_api_cc_expires_month'], 1, '20' . $_POST['linkpoint_api_cc_expires_year'])))));

    return $confirmation;
  }
  /**
   * Prepare the hidden fields comprising the parameters for the Submit button on the checkout confirmation page
   */
  function process_button() {
    // These are hidden fields on the checkout confirmation page
    $process_button_string = zen_draw_hidden_field('cc_owner', $_POST['linkpoint_api_cc_owner']) .
                             zen_draw_hidden_field('cc_expires', $this->cc_expiry_month . substr($this->cc_expiry_year, -2)) .
                             zen_draw_hidden_field('cc_expires_month', $this->cc_expiry_month) .
                             zen_draw_hidden_field('cc_expires_year', substr($this->cc_expiry_year, -2)) .
                             zen_draw_hidden_field('cc_type', $this->cc_card_type) .
                             zen_draw_hidden_field('cc_number', $this->cc_card_number) .
                             zen_draw_hidden_field('cc_cvv', $_POST['linkpoint_api_cc_cvv']);
    $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

    return $process_button_string;
  }

  /**
   * Prepare and submit the authorization to the gateway
   */
  function before_process() {
    global $order, $order_totals, $db, $messageStack, $lp_avs, $lp_trans_num;
    $myorder = array();

    // Calculate the next expected order id
    $last_order_id = $db->Execute("select orders_id from " . TABLE_ORDERS . " order by orders_id desc limit 1");
    $new_order_id = $last_order_id->fields['orders_id'];
    $new_order_id = ($new_order_id + 1);
    // add randomized suffix to order id to produce uniqueness ... since it's unwise to submit the same order-number twice to the gateway
    $new_order_id = (string)$new_order_id . '-' . zen_create_random_value(6);

    // Build Info to send to Gateway
    $myorder["result"] = "LIVE";
    switch (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE) {
      case "TESTING: Successful": $myorder["result"] = "GOOD"; break;
      case "TESTING: Decline"   : $myorder["result"] = "DECLINE"; break;
      case "TESTING: Duplicate" : $myorder["result"] = "DUPLICATE"; break;
    }

    // "oid" - Order ID number must be unique. If not set, gateway will assign one.
    //$oid = zen_create_random_value(16, 'digits'); // Create a UID for the order
    $myorder["oid"] = $new_order_id; //"";    // time(); ????

    // prepare totals for submission
    $surcharges = 0;
    $creditsApplied = 0;
    $this->notify('NOTIFY_PAYMENT_LINKPOINT_BEFORE_ORDER_TOTALS', array(), $myorder);
    reset($order_totals);
    $myorder['subtotal'] = $myorder['tax'] = $myorder['shipping'] = $myorder['chargetotal'] = 0;
    for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
      if ($order_totals[$i]['code'] == '') continue;
      if (in_array($order_totals[$i]['code'], array('ot_total','ot_subtotal','ot_tax','ot_shipping'))) {
        if ($order_totals[$i]['code'] == 'ot_subtotal') $myorder["subtotal"]    = round($order_totals[$i]['value'],2);
        if ($order_totals[$i]['code'] == 'ot_tax')      $myorder["tax"]        += round($order_totals[$i]['value'],2);
        if ($order_totals[$i]['code'] == 'ot_shipping') $myorder["shipping"]    = round($order_totals[$i]['value'],2);
        if ($order_totals[$i]['code'] == 'ot_total')    $myorder["chargetotal"] = round($order_totals[$i]['value'],2);
      } else {
        global $$order_totals[$i]['code'];
        if (substr($order_totals[$i]['text'], 0, 1) == '-' || (isset($$order_totals[$i]['code']->credit_class) && $$order_totals[$i]['code']->credit_class == true)) {
          $creditsApplied += round($order_totals[$i]['value'],2);
        } else {
          $surcharges += round($order_totals[$i]['value'],2);
        }
      }
    }
    foreach(array('subtotal', 'tax', 'chargetotal', 'shipping') as $i) {
      if (isset($myorder[$i])) $myorder[$i] = number_format($myorder[$i], 2, '.', '');
    }

    $this->include_item_details = TRUE;
    $this->notify('NOTIFY_PAYMENT_LINKPOINT_BEFORE_LINEITEM_DETAILS', array(), $myorder, $order, $order_totals);

    if ($this->include_item_details && $surcharges == 0 && $creditsApplied == 0 && $order->info['total'] >= $order->info['subtotal'] && sizeof($order->products) <= 20) {
    // itemized contents
      $num_line_items = 0;
      reset($order->products);
      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        $num_line_items++;
        $myorder["items"][$num_line_items]['id']          = $order->products[$i]['id'];

        $myorder["items"][$num_line_items]['description'] = substr(htmlentities($order->products[$i]['name'], ENT_QUOTES, 'UTF-8'), 0, 128);
        $myorder["items"][$num_line_items]['quantity']    = $order->products[$i]['qty'];
        $myorder["items"][$num_line_items]['price']       = number_format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), 2, '.', '');
        // check and adjust for fractional quantities, which cannot be submitted as line-item details
        $q = $order->products[$i]['qty']; $q1 = strval($q); $q2 = (int)$q; $q3 = strval($q2);
        if ($q1 != $q3 || $myorder["items"][$num_line_items]['quantity'] * $myorder["items"][$num_line_items]['price'] != number_format($order->products[$i]['qty'] * $order->products[$i]['final_price'], 2, '.', '')) {
          $myorder["items"][$num_line_items]['quantity']    = 1;
          $myorder["items"][$num_line_items]['price']       = number_format(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $decimals) * $order->products[$i]['qty'], 2, '.', '');
          $myorder["items"][$num_line_items]['description'] = '(' . $order->products[$i]['qty'] . ' x )' . substr($myorder["items"][$num_line_items]['description'], 115);
        }

        if (isset($order->products[$i]['attributes'])) {
          $options_text_length = 0;
          for ($j=0, $m=sizeof($order->products[$i]['attributes']); $j<$m; $j++) {
            $options_text_length += strlen($order->products[$i]['attributes'][$j]['option'] . $order->products[$i]['attributes'][$j]['value']);
          }
          if ($options_text_length < 128) {
            for ($j=0, $m=sizeof($order->products[$i]['attributes']); $j<$m; $j++) {
              $myorder["items"][$num_line_items]['options' . $j]['name'] = substr(htmlentities($order->products[$i]['attributes'][$j]['option'], ENT_QUOTES, 'UTF-8'), 0, 128);
              $myorder["items"][$num_line_items]['options' . $j]['value'] = substr(htmlentities($order->products[$i]['attributes'][$j]['value'], ENT_QUOTES, 'UTF-8'), 0, 128);
            }
          }
        }
        // track one-time charges
        if ($order->products[$i]['onetime_charges'] != 0 ) {
          $num_line_items++;
          $myorder["items"][$num_line_items]['id']          = 'OTC';
          $myorder["items"][$num_line_items]['description'] = 'One Time Charges';
          $myorder["items"][$num_line_items]['quantity']    = 1;
          $myorder["items"][$num_line_items]['price']       = number_format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), 2, '.', '');
        }
      }
/*
        // deal with surcharges/fees
        $num_line_items++;
        $myorder["items"][$num_line_items]['id']          = 'Surcharge';
        $myorder["items"][$num_line_items]['description'] = $order_totals[$i]['title'];
        $myorder["items"][$num_line_items]['quantity']    = 1;
        $myorder["items"][$num_line_items]['price']       = number_format($order_totals[$i]['value'], 2, '.', '');
        $myorder["subtotal"] += $surcharges;
*/

     // FirstData can't accept more than 20 line-item submissions per transaction
      if ($num_line_items > 20) {
        unset($myorder["items"]);
        $num_line_items = 0;
      }
      // Verify that the line-item math works
      for ($i=1, $n=$num_line_items; $i <= $n; $i++) {
        $sum2 += ($myorder["items"][$i]['quantity'] * $myorder["items"][$i]['price']);
      }
      if (strval($sum2) != strval($myorder['subtotal'])) {
        unset($myorder['items']);
        $num_line_items = 0;
      }
    }

    // Subtotal Sanity Check in case there are addon modules affecting calculations
    $sum1 = strval($myorder['subtotal'] + $myorder['shipping'] + $myorder['tax']);
    if ($sum1 > $myorder['chargetotal']) {
      foreach(array('subtotal', 'tax', 'shipping', 'items') as $i) {
        if (isset($myorder[$i])) unset($myorder[$i]);
      }
    } elseif ($sum1 < $myorder['chargetotal']) {
      if ($num_line_items > 0 && $num_line_items < 20 && isset($myorder['items'])) {
        $num_line_items++;
        $myorder["items"][$num_line_items]['id']          = 'Adj';
        $myorder["items"][$num_line_items]['description'] = 'Rounding Adjustment';
        $myorder["items"][$num_line_items]['quantity']    = 1;
        $myorder["items"][$num_line_items]['price']       = number_format($myorder['chargetotal'] - $sum1, 2, '.', '');
        $myorder['subtotal'] += round($myorder['chargetotal'] - $sum1, 2);
        $myorder['subtotal'] = number_format($myorder['subtotal'], 2, '.', '');
      } else {
        foreach(array('subtotal', 'tax', 'shipping', 'items') as $i) {
          if (isset($myorder[$i])) unset($myorder[$i]);
        }
      }
    }
    // clean up zeros
    foreach(array('subtotal', 'tax', 'shipping') as $i) {
      if (isset($myorder[$i]) && $myorder[$i] == 0) unset($myorder[$i]);
    }

    $myorder["ip"]  = current(explode(':', str_replace(',', ':', zen_get_ip_address())));
    $myorder["ponumber"]    = "";

    // CARD INFO
    $myorder["cardnumber"]   = $_POST['cc_number'];
    $myorder["cardexpmonth"] = $_POST['cc_expires_month'];
    $myorder["cardexpyear"]  = $_POST['cc_expires_year'];
    $myorder["cvmindicator"] = "provided";
    $myorder["cvmvalue"]     = $_POST['cc_cvv'];

    // BILLING INFO
    $myorder["userid"]   = $_SESSION['customer_id'];
    $myorder["customerid"] = $_SESSION['customer_id'];
    $myorder["name"]     = htmlentities($_POST['cc_owner'], ENT_QUOTES, 'UTF-8'); //$order->billing['firstname'] . ' ' . $order->billing['lastname']);
    $myorder["company"]  = htmlentities($order->billing['company'], ENT_QUOTES, 'UTF-8');
    $myorder["address1"] = htmlentities($order->billing['street_address'], ENT_QUOTES, 'UTF-8');
    $myorder["address2"] = htmlentities($order->billing['suburb'], ENT_QUOTES, 'UTF-8');
    $myorder["city"]     = $order->billing['city'];
    $myorder["state"]    = $order->billing['state'];
    $myorder["country"]  = $order->billing['country']['iso_code_2'];
    $myorder["phone"]    = $order->customer['telephone'];
    //$myorder["fax"]      = $order->customer['fax'];
    $myorder["email"]    = $order->customer['email_address'];
    $myorder["addrnum"]  = $order->billing['street_address'];   // Required for AVS. If not provided, transactions will downgrade.
    $myorder["zip"]      = $order->billing['postcode']; // Required for AVS. If not provided, transactions will downgrade.

    // SHIPPING INFO
    $myorder["sname"]     = htmlentities($order->delivery['firstname'] . ' ' . $order->delivery['lastname'], ENT_QUOTES, 'UTF-8');
    $myorder["saddress1"] = htmlentities($order->delivery['street_address'], ENT_QUOTES, 'UTF-8');
    $myorder["saddress2"] = htmlentities($order->delivery['suburb'], ENT_QUOTES, 'UTF-8');
    $myorder["scity"]     = $order->delivery['city'];
    $myorder["sstate"]    = $order->delivery['state'];
    $myorder["szip"]      = $order->delivery['postcode'];
    $myorder["scountry"]  = $order->delivery['country']['iso_code_2'];

    // MISC
    $myorder["comments"] = "Website Order";
    // $myorder["referred"] = "";
    $myorder["ordertype"]  = (MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE == 'Authorize Only' ? 'PREAUTH': 'SALE');
    $this->payment_status = $myorder["ordertype"];

    $this->submit_extras = array();
    $this->notify('NOTIFY_PAYMENT_LINKPOINT_PRESUBMIT_HOOK', array(), $myorder);
    if (sizeof($this->submit_extras)) $myorder = array_merge($myorder, $this->submit_extras);

    // send request to gateway
    $result = $this->_sendRequest($myorder);

    // alert to customer if communication failure
    if (!is_array($result)) {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_LINKPOINT_API_TEXT_FAILURE_MESSAGE, 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    // resubmit without subtotals if subtotal error occurs
    if ($result["r_approved"] != "APPROVED" && !($result["r_approved"] == "SUBMITTED" && $result["r_message"] == 'APPROVED')) {
      if (in_array(substr($result['r_error'],0,10), array('SGS-002301', 'SGS-010503', 'SGS-005003'))) {
        foreach(array('items', 'subtotal', 'tax', 'shipping') as $i) {
          if (isset($myorder[$i])) unset($myorder[$i]);
        }
        $myorder["oid"] .= '-b';
        $myorder["chargetotal"] = ($myorder["chargetotal"] - 0.01);
        $result = $this->_sendRequest($myorder);
        $this->notify('NOTIFY_PAYMENT_LINKPOINT_POSTSUBMIT_HOOK', array(), $result, $myorder);

        if (!is_array($result)) {
          $messageStack->add_session('checkout_payment', MODULE_PAYMENT_LINKPOINT_API_TEXT_FAILURE_MESSAGE, 'error');
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }
      }
    }

// PARSE Results
    $all_response_info = '';
    foreach($result as $key=>$value) {
      $all_response_info .= ' ' .$key . '='.$value;
    }
    if ($this->code_debug) $messageStack->add_session('header', $all_response_info, 'caution');

    $chargetotal = $myorder["chargetotal"];

// prepare transaction logging info
    $cust_info = '';
    $cc_number = substr($myorder["cardnumber"], 0, 4) . str_repeat('X', abs(strlen($myorder["cardnumber"]) - 8)) . substr($myorder["cardnumber"], -4);
    foreach($myorder as $key=>$value) {
      if ($key != 'cardnumber') {
        if ($key == 'cvmvalue') {
          $value = '****';
        }
        if ($key == 'cardexpmonth') {
          $cc_month = $value;
        }
        if ($key == 'cardexpyear') {
          $cc_year = $value;
        }
        if (is_array($value)) $value = print_r($value, true);
        if (!in_array($key, array('keyfile', 'configfile', 'transactionorigin', 'terminaltype', 'host', 'port'))) $cust_info .= ' ' .$key . '=' . $value . ';';
      } else {
        $cust_info .= ' ' .$key . '=' . $cc_number . ';';
      }
    }
    // store last 4 digits of CC number
//    $order->info['cc_number'] = str_repeat('X', (strlen($myorder["cardnumber"]) - 4)) . substr($myorder["cardnumber"], -4);
    // store first and last 4 digits of CC number ... which is the Visa-standards-compliant approach, same as observed by Linkpoint's services
    $order->info['cc_number'] = $cc_number;

    $order->info['cc_type'] = $_POST['cc_type'];
    $order->info['cc_owner'] = $_POST['cc_owner'];
    $order->info['cc_cvv'] = '***';
    $order->info['cc_expires'] = '';// $_POST['cc_expires'];


    $lp_trans_num = $result['r_ordernum'];
    $transaction_tax = $result['r_tax']; // The calculated tax for the order, when the ordertype is calctax.
    $transaction_shipping = $result['r_shipping']; // The calculated shipping charges for the order, when the ordertype is calcshipping.
    $this->response_codes = $result['r_avs']; // AVS Response for transaction

    // these are used to update the order-status-history upon order completion
    $this->transaction_id = $result['r_tdate'] . ' Order Number/Code: ' . $result['r_ordernum'];
    $this->auth_code = $result['r_code']; // The approval code for this transaction.

//  Store Transaction history in Database
    $sql_data_array= array(array('fieldName'=>'lp_trans_num', 'value' => $result['r_ordernum'], 'type'=>'string'), // The order number associated with this transaction.
                           array('fieldName'=>'order_id', 'value' => $result['r_ordernum'], 'type'=>'integer'),
                           array('fieldName'=>'approval_code', 'value' => $result['r_code'], 'type'=>'string'), // The approval code for this transaction.
                           array('fieldName'=>'transaction_response_time', 'value' => $result['r_time'], 'type'=>'string'), // The time+date of the transaction server response.
                           array('fieldName'=>'r_error', 'value' => $result['r_error'], 'type'=>'string'),
                           array('fieldName'=>'customer_id', 'value' => $_SESSION['customer_id'] , 'type'=>'integer'),
                           array('fieldName'=>'avs_response', 'value' => $result['r_avs'], 'type'=>'string'), // AVS Response for transaction
                           array('fieldName'=>'transaction_result', 'value' => $result['r_approved'], 'type'=>'string'), // Transaction result: APPROVED, DECLINED, or FRAUD.
                           array('fieldName'=>'message', 'value' => $result['r_message'] . "\n" . $all_response_info, 'type'=>'string'), // Any message returned by the processor; e.g., CALL VOICE CENTER.
                           array('fieldName'=>'transaction_time', 'value' => $result['r_tdate'], 'type'=>'string'), // A server time-date stamp for this transaction.
                           array('fieldName'=>'transaction_reference_number', 'value' => $result['r_ref'], 'type'=>'string'), // Reference number returned by the CC processor.
                           array('fieldName'=>'fraud_score', 'value' => $result['r_score'], 'type'=>'integer'), // LinkShield fraud risk score.
                           array('fieldName'=>'cc_number', 'value' => $cc_number, 'type'=>'string'),
                           array('fieldName'=>'cust_info', 'value' => $cust_info, 'type'=>'string'),
                           array('fieldName'=>'chargetotal', 'value' => $chargetotal, 'type'=>'string'),
//                            array('fieldName'=>'cc_expire', 'value' => $cc_month . '/' . $cc_year, 'type'=>'string'),
                           array('fieldName'=>'ordertype', 'value' => $myorder['ordertype'], 'type'=>'string'), // transaction type: PREAUTH or SALE
                           array('fieldName'=>'date_added', 'value' => 'now()', 'type'=>'noquotestring'));
    if (MODULE_PAYMENT_LINKPOINT_API_STORE_DATA == 'True') {
      $db->perform(TABLE_LINKPOINT_API, $sql_data_array);
    }

  //  Begin check of specific error conditions
    if ($result["r_approved"] != "APPROVED" && !($result["r_approved"] == "SUBMITTED" && $result["r_message"] == 'APPROVED')) {
      if (substr($result['r_error'],0,10) == 'SGS-020005') $messageStack->add_session('checkout_payment', $result['r_error'], 'error');  // Error (Merchant config file is missing, empty or cannot be read)
      if (substr($result['r_error'],0,10) == 'SGS-005000') $messageStack->add_session('checkout_payment', MODULE_PAYMENT_LINKPOINT_API_TEXT_GENERAL_ERROR . '<br />' . $result['r_error'], 'error'); // The server encountered a database error
      if (substr($result['r_error'],0,10) == 'SGS-000001' || strstr($result['r_error'], 'D:Declined') || strstr($result['r_error'], 'R:Referral')) $messageStack->add_session('checkout_payment', MODULE_PAYMENT_LINKPOINT_API_TEXT_DECLINED_MESSAGE . '<br />' . $result['r_error'], 'error');
      if (substr($result['r_error'],0,10) == 'SGS-005005' || strstr($result['r_error'], 'Duplicate transaction')) $messageStack->add_session('checkout_payment', MODULE_PAYMENT_LINKPOINT_API_TEXT_DUPLICATE_MESSAGE . '<br />' . $result['r_error'], 'error');
      if (substr($result['r_error'],0,10) == 'SGS-002301') $messageStack->add_session('checkout_payment', 'Subtotal miscalculation. Please notify the storeowner.' . '<br />' . $result['r_error'], 'error');
    }
  //  End specific error conditions

  //  Begin Transaction Status does not equal APPROVED
    if ($result["r_approved"] != "APPROVED") {
      // alert to customer:
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_LINKPOINT_API_TEXT_DECLINED_MESSAGE, 'caution');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }
  //  End Transaction Status does not equal APPROVED

    $avs_meanings = array();
    $avs_meanings['YY'] = ' - Street Address and Zip Code match.';
    $avs_meanings['YN'] = ' - Street Address matches but Zip Code does NOT match.';
    $avs_meanings['YX'] = ' - Street Address matches, but Zip Code comparison unavailable.';
    $avs_meanings['NY'] = ' - Street Address DOES NOT match, but Zip Code matches.';
    $avs_meanings['XY'] = ' - Street Address check not available, but Zip Code matches.';
    $avs_meanings['NN'] = ' - Street Address DOES NOT MATCH and Zip Code DOES NOT MATCH.';
    $avs_meanings['NX'] = ' - Street Address DOES NOT MATCH and Zip Code comparison unavailable.';
    $avs_meanings['XN'] = ' - Street Address check not available. Zip Code DOES NOT MATCH.';
    $avs_meanings['XX'] = ' - No validation for address or zip code could be performed (not available from issuing bank).';

    // Possible Fraud order. Allow transaction to process, but notify shop for owner to take appropriate action on order
    if (($result["r_approved"] == "APPROVED") && (substr($result['r_code'], 17, 2) != "YY")  && MODULE_PAYMENT_LINKPOINT_API_FRAUD_ALERT == 'Yes') {
      //DEBUG: $messageStack->add_session('header', 'possible fraud situation--> ' . $result['r_code'], 'caution');
      $message = 'Potential Fraudulent Order - Bad Address - Action Required' . "\n" .
                 'This alert occurs because the "Approval Code" below does not contain the expected YY response.' . "\n" .
                 'Thus, you might want to verify the address with the customer prior to shipping, or be sure to use Registered Mail with Signature Required in case they file a chargeback.' . "\n\n" .
                 'Customer Name: ' . $order->customer['firstname'] . ' ' . $order->customer['lastname'] . "\n\n" .
                 'AVS Result: ' . $result['r_avs'] . $avs_meanings[substr($result['r_avs'],0,2)] . "\n\n" .
                 'Order Number: ' . $lp_trans_num . "\n" .
                 'Transaction Date and Time: ' . $result['r_time'] . "\n" .
                 'Approval Code: ' . $result['r_code'] . "\n" .
                 'Reference Number: ' . $result['r_ref'] . "\n\n" .
                 'Error Message: ' . $result['r_error'] . "\n\n" .
                 'Transaction Result: ' . $result['r_approved'] . "\n\n" .
                 'Message: ' . $result['r_message'] . "\n\n" .
                 'Fraud Score: ' . ($result['r_score'] == '' ? 'Not Enabled' : $result['r_score']) . "\n\n" .
                 'AVS CODE MEANINGS: ' . "\n" .
                 'YY** = Street Address and Zip Code match.' . "\n" .
                 'YN** = Street Address matches but Zip Code does NOT match.' . "\n" .
                 'YX** = Street Address matches, but Zip Code comparison unavailable.' . "\n" .
                 'NY** = Street Address DOES NOT match, but Zip Code matches.' . "\n" .
                 'XY** = Street Address check not available, but Zip Code matches.' . "\n" .
                 'NN** = Street Address DOES NOT MATCH and Zip Code DOES NOT MATCH.' . "\n" .
                 'NX** = Street Address DOES NOT MATCH and Zip Code comparison unavailable.' . "\n" .
                 'XN** = Street Address check not available. Zip Code DOES NOT MATCH.' . "\n" .
                 'XX** = Neither validation is available.' . "\n";
      $html_msg['EMAIL_MESSAGE_HTML'] = nl2br($result['r_message']);
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Potential Fraudulent Order - Bad Address - Action Required - ' . $lp_trans_num, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'fraudalert');
    }
  // end fraud alert
  }

  function after_process() {
    global $insert_id, $db;
    $comments = (MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE == 'Authorize Only' ? ALERT_LINKPOINT_API_PREAUTH_TRANS : '');

    switch (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE) {
      case "LIVE: Production": $comments .= ''; break;
      case "TESTING: Successful": $comments .= ' ' . ALERT_LINKPOINT_API_TEST_FORCED_SUCCESSFUL; break;
      case "TESTING: Decline": $comments .= ' ' . ALERT_LINKPOINT_API_TEST_FORCED_DECLINED; break;
    }

    if ($this->auth_code && $this->transaction_id) $comments .= " " . $this->cc_card_type . " AUTH: " . $this->auth_code . ". TransID: " . $this->transaction_id;

    $sql = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, customer_notified, date_added) values (:orderComments, :orderID, :orderStatus, -1, now() )";
    $sql = $db->bindVars($sql, ':orderComments', 'Credit Card payment. ' . $comments, 'string');
    $sql = $db->bindVars($sql, ':orderID', $insert_id, 'integer');
    $sql = $db->bindVars($sql, ':orderStatus', $this->order_status, 'integer');
    $db->Execute($sql);
    return false;
  }

  function after_order_create($zf_order_id) {
    global $db, $lp_avs, $lp_trans_num;
    $db->execute("update "  . TABLE_ORDERS . " set lp_avs ='" . $lp_avs . "' where orders_id = '" . $zf_order_id ."'");
    $db->execute("update "  . TABLE_ORDERS . " set lp_trans_num ='" . $lp_trans_num . "' where orders_id = '" . $zf_order_id ."'");
    $db->execute("update "  . TABLE_LINKPOINT_API . " set order_id ='" . $zf_order_id . "' where lp_trans_num = '" . $lp_trans_num ."'");
  }

   function admin_notification($zf_order_id) {
     global $db;
     if (MODULE_PAYMENT_LINKPOINT_API_STORE_DATA=='False') return '';
     $output = '';
     $sql = "select * from " . TABLE_LINKPOINT_API . " where order_id = '" . $zf_order_id . "' and transaction_result = 'APPROVED' order by date_added";
     $lp_api = $db->Execute($sql);
     if (!$lp_api->EOF && $lp_api->RecordCount() > 0) require(DIR_FS_CATALOG. DIR_WS_MODULES . 'payment/linkpoint_api/linkpoint_api_admin_notification.php');
     return $output;
   }

  function get_error() {
    $error = array('title' => MODULE_PAYMENT_LINKPOINT_API_TEXT_ERROR,
                   'error' => stripslashes(urldecode($_GET['error'])));
    return $error;
  }

  function check() {
    global $db;
    if (IS_ADMIN_FLAG === true) {
      global $sniffer;
      if ($sniffer->table_exists(TABLE_LINKPOINT_API)) {
        if ($sniffer->field_exists(TABLE_LINKPOINT_API, 'zen_order_id'))  $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " CHANGE COLUMN zen_order_id order_id int(11) NOT NULL default '0'");
        if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'ordertype'))   $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD ordertype varchar(8) NOT NULL default '' after cc_expire");
      }
    }
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_LINKPOINT_API_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }

  function install() {
    global $db, $messageStack;
    if (defined('MODULE_PAYMENT_LINKPOINT_API_STATUS')) {
      $messageStack->add_session('FirstData/Linkpoint module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=linkpoint_api', 'NONSSL'));
      return 'failed';
    }
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable FirstData/Linkpoint Module', 'MODULE_PAYMENT_LINKPOINT_API_STATUS', 'True', 'Do you want to accept FirstData/Linkpoint credit card payments?', '6', 121, 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FirstData/Linkpoint/YourPay Merchant ID', 'MODULE_PAYMENT_LINKPOINT_API_LOGIN', 'EnterYourStoreNumber', 'Please enter your FirstData/Linkpoint/YourPay Merchant ID.<br />This is the same as the number in the PEM digital certificate filename for your account.', '6', 121, now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('FirstData/LinkPoint Transaction Mode Response', 'MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE', 'LIVE: Production', '<strong>Production:</strong> Use this for live stores.<br />or, select these options if you wish to test the module:<br /><strong>Successful:</strong> Use to TEST by forcing a Successful transaction<br /><strong>Decline:</strong> Use to TEST forcing a Failed transaction', '6', 121, 'zen_cfg_select_option(array(\'LIVE: Production\', \'TESTING: Successful\', \'TESTING: Decline\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Authorization Type', 'MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE', 'Authorize Only', 'Do you want submitted credit card transactions to be authorized only, or immediately charged/captured?<br />In most cases you will want to do an <strong>Immediate Charge</strong> to capture payment immediately. In some situations, you may prefer to simply <strong>Authorize</strong> transactions, and then manually use your Merchant Terminal to formally capture the payments (esp if payment amounts may fluctuate between placing the order and shipping it)', '6', 121, 'zen_cfg_select_option(array(\'Authorize Only\', \'Immediate Charge/Capture\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_LINKPOINT_API_ORDER_STATUS_ID', 2, 'Set the status of orders made with this payment module to this value<br />(this affects all Captured / Charged / Approved orders)<br />Recommended: <strong>Processing</strong>', '6', 121, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('PREAUTH Order Status', 'MODULE_PAYMENT_LINKPOINT_API_PREAUTH_ORDER_STATUS_ID', 1, 'When this module is set to PREAUTH mode (Authorization), which order-status do you want the purchase to be set to?<br />Recommended: <strong>Pending</strong>', '6', 121, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Refund/Void Order Status', 'MODULE_PAYMENT_LINKPOINT_API_REFUNDED_ORDER_STATUS_ID', '1', 'When orders are refunded or voided from this Admin area, which order-status do you want the transaction to be set to?<br />Recommended: <strong>Pending</strong> or cancelled/refunded', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_LINKPOINT_API_SORT_ORDER', '0', 'Any value greater than zero will cause this payment method to appear in the specified sort order on the checkout-payment page.', '6', 121, now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone (restrict to)', 'MODULE_PAYMENT_LINKPOINT_API_ZONE', '0', 'If you want only customers from a particular zone to be able to use this payment module, select that zone here.', '6', 121, 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Fraud Alerts', 'MODULE_PAYMENT_LINKPOINT_API_FRAUD_ALERT', 'Yes', 'Do you want to be notified by email of suspected fraudulent Credit Card activity?<br />(sends to Store Owner Email Address)', '6', 121, 'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Database Storage', 'MODULE_PAYMENT_LINKPOINT_API_STORE_DATA', 'True', 'If you enable this option, extended details of each transaction will be stored, enabling you to more effectively conduct audits of fraudulent activity or even track/match order information between Zen Cart and your FirstData/LinkPoint records. You can view this data in Admin->Customers->Linkpoint CC Review.', '6', 121, 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Debug Mode', 'MODULE_PAYMENT_LINKPOINT_API_DEBUG', 'Off', 'Would you like to enable debug mode?  Choosing Alert mode will email logs of failed transactions to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Failure Alerts Only\', \'Log File\', \'Log and Email\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE', 'Production', 'Transaction mode used for processing orders', '6', 121, 'zen_cfg_select_option(array(\'Production\',\'DevelopersTest\'), ', now())");

    $this->notify('NOTIFY_PAYMENT_LINKPOINT_API_INSTALLED');

    // Now do database-setup:
    global $sniffer;
    if (!$sniffer->table_exists(TABLE_LINKPOINT_API)) {
      $sql = "CREATE TABLE " . TABLE_LINKPOINT_API . " (
                  id int(11) unsigned NOT NULL auto_increment,
                  customer_id varchar(11) NOT NULL default '',
                  lp_trans_num varchar(64) NOT NULL default '',
                  order_id int(11) NOT NULL default '0',
                  avs_response varchar(4) NOT NULL default '',
                  r_error varchar(250) NOT NULL default '',
                  approval_code varchar(254) NOT NULL default '',
                  transaction_result varchar(25) NOT NULL default '',
                  message text NOT NULL,
                  transaction_response_time varchar(25) NOT NULL default '',
                  transaction_time varchar(50) NOT NULL default '',
                  transaction_reference_number varchar(64) NOT NULL default '',
                  fraud_score int(11) NOT NULL default '0',
                  cc_number varchar(20) NOT NULL default '',
                  cc_expire varchar(12) NOT NULL default '',
                  ordertype varchar(8) NOT NULL default '',
                  cust_info text,
                  chargetotal decimal(15,4) NOT NULL default '0.0000',
                  date_added datetime NOT NULL default '1970-01-01 00:00:01',
                  PRIMARY KEY  (id),
                  KEY idx_customer_id_zen (customer_id)     )";
      $db->Execute($sql);
    } else {
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'customer_id')) $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD customer_id varchar(11) NOT NULL default '' after id");
      if ($sniffer->field_exists(TABLE_LINKPOINT_API, 'lp_order_id'))  $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " CHANGE COLUMN lp_order_id lp_trans_num varchar(64) NOT NULL default ''");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'r_error'))     $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD r_error varchar(250) NOT NULL default '' after avs_response");
      if ($sniffer->field_exists(TABLE_LINKPOINT_API, 'approval_code')) $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " CHANGE COLUMN approval_code approval_code varchar(254) NOT NULL default ''");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'transaction_result')) $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD transaction_result varchar(25) NOT NULL default '' after approval_code");
      if ($sniffer->field_exists(TABLE_LINKPOINT_API, 'message'))      $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " CHANGE COLUMN message message text NOT NULL");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'transaction_response_time')) $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD transaction_response_time varchar(25) NOT NULL default '' after message");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'transaction_time')) $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD transaction_time varchar(50) NOT NULL default '' after transaction_response_time");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'transaction_reference_number')) $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD transaction_reference_number varchar(64) NOT NULL default '' after transaction_time");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'fraud_score')) $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD fraud_score int(11) NOT NULL default '0' after transaction_reference_number");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'cc_number'))   $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD cc_number varchar(20) NOT NULL default '' after fraud_score");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'cc_expire'))   $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD cc_expire varchar(12) NOT NULL default '' after cc_number");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'ordertype'))   $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD ordertype varchar(8) NOT NULL default '' after cc_expire");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'cust_info'))   $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD cust_info text after ordertype");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'chargetotal')) $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD chargetotal decimal(15,4) NOT NULL default '0.0000' after cust_info");
      if (!$sniffer->field_exists(TABLE_LINKPOINT_API, 'date_added'))  $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " ADD date_added datetime NOT NULL default '1970-01-01 00:00:01' after chargetotal");
      if ($sniffer->field_exists(TABLE_LINKPOINT_API, 'zen_order_id'))  $db->Execute("ALTER TABLE " . TABLE_LINKPOINT_API . " CHANGE COLUMN zen_order_id order_id int(11) NOT NULL default '0'");
    }

    if (!$sniffer->field_exists(TABLE_ORDERS, 'lp_avs')) {
      $sql = "ALTER TABLE " . TABLE_ORDERS . " ADD lp_avs VARCHAR( 25 ) NOT NULL DEFAULT ''";
      $db->Execute($sql);
    }
    if (!$sniffer->field_exists(TABLE_ORDERS, 'lp_trans_num')) {
      $sql = "ALTER TABLE " . TABLE_ORDERS . " ADD lp_trans_num varchar(64) NOT NULL DEFAULT ''";
      $db->Execute($sql);
    }
    if ($sniffer->field_type(TABLE_ORDERS, 'lp_trans_num', 'varchar(64)', true) !== true) {
      $sql = "ALTER TABLE " . TABLE_ORDERS . " CHANGE lp_trans_num lp_trans_num varchar(64) NOT NULL DEFAULT ''";
      $db->Execute($sql);
    }
  }

  function remove() {
    global $db, $sniffer;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_PAYMENT\_LINKPOINT\_API\_%'");
    // cleanup database if contains no data
    if ($sniffer->table_exists(TABLE_LINKPOINT_API)) {
      $result = $db->Execute("select count(id) as count from " . TABLE_LINKPOINT_API);
      if ($result->RecordCount() == 0) $db->Execute("DROP TABLE " . TABLE_LINKPOINT_API);
    }
    $this->notify('NOTIFY_PAYMENT_LINKPOINT_API_UNINSTALLED');
  }

  function keys() {
    $keys_list = array(
      'MODULE_PAYMENT_LINKPOINT_API_STATUS',
      'MODULE_PAYMENT_LINKPOINT_API_LOGIN',
      'MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE',
      'MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE',
      'MODULE_PAYMENT_LINKPOINT_API_ORDER_STATUS_ID',
      'MODULE_PAYMENT_LINKPOINT_API_PREAUTH_ORDER_STATUS_ID',
      'MODULE_PAYMENT_LINKPOINT_API_REFUNDED_ORDER_STATUS_ID',
      'MODULE_PAYMENT_LINKPOINT_API_SORT_ORDER',
      'MODULE_PAYMENT_LINKPOINT_API_ZONE',
      'MODULE_PAYMENT_LINKPOINT_API_FRAUD_ALERT',
      'MODULE_PAYMENT_LINKPOINT_API_STORE_DATA'  );
    $keys_list[] = 'MODULE_PAYMENT_LINKPOINT_API_DEBUG';
    if (IS_ADMIN_FLAG === true && isset($_GET['debug']) && $_GET['debug']=='on' && MODULE_PAYMENT_LINKPOINT_API_CODE_DEBUG=='debug') $keys_list[] = 'MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE';
    return $keys_list;
  }

  function _log($msg, $suffix = '') {
    static $key;
    if (!isset($key) || $key == '') $key = time() . '_' . zen_create_random_value(4);
    $file = $this->_logDir . '/' . 'Linkpoint_Debug_' . $suffix . '_' . $key . '.log';
    if ($fp = @fopen($file, 'a')) {
      @fwrite($fp, $msg);
      @fclose($fp);
    }
  }

  /**
   * Send transaction to gateway
   */
  function _sendRequest($myorder) {
    $myorder["host"] = "secure.linkpt.net";
    if (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE == 'DevelopersTest') {
      $myorder["host"]     = "staging.linkpt.net";
    }
    $myorder["port"]       = "1129";
    $myorder["keyfile"]    =(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/linkpoint_api/' . MODULE_PAYMENT_LINKPOINT_API_LOGIN . '.pem');
    $myorder["configfile"] = MODULE_PAYMENT_LINKPOINT_API_LOGIN;        // This is your store number
    // set to ECI and UNSPECIFIED for ecommerce transactions:
    $myorder["transactionorigin"] = "ECI";
    $myorder["terminaltype"]      = "UNSPECIFIED";
    // debug - for testing communication only
    if (MODULE_PAYMENT_LINKPOINT_API_DEBUG != 'Off') {
    }
    if (MODULE_PAYMENT_LINKPOINT_API_CODE_DEBUG=='debug') {
      $myorder["debugging"] = "true";  // for development only - not intended for production use
      $myorder["debug"]     = "true";  // for development only - not intended for production use
      $myorder["webspace"]  = "true";  // for development only - not intended for production use
    }

    include(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/linkpoint_api/class.linkpoint_api.php');
    $mylphp = new lphp;

    // Send transaction, using cURL
    $result = $mylphp->curl_process($myorder);

    // do debug output
    $errorMessage = date('M-d-Y h:i:s') . "\n=================================\n\n" . ($mylphp->commError !='' ? $mylphp->commError . "\n\n" : '') . 'Response Code: ' . $result["r_approved"] . ' ' . $result["r_error"] . "\n\n=================================\n\n" . 'Sending to Gateway: ' . "\n" . $mylphp->sendData . "\n\n" . 'Result: ' . substr(print_r($result, true), 5) . "\n\n";
    if ($mylphp->commError != '') $errorMessage .= $mylphp->commError . "\n" . 'CURL info: ' . print_r($mylphp->commInfo, true) . "\n";
    if (CURL_PROXY_REQUIRED == 'True') $errorMessage .= 'Using CURL Proxy: [' . CURL_PROXY_SERVER_DETAILS . ']  with Proxy Tunnel: ' .($proxy_tunnel_flag ? 'On' : 'Off') . "\n";
    $failure = (!is_array($result) || $result["r_approved"] != "APPROVED") ? true : false;

    // handle logging
    if (strstr(MODULE_PAYMENT_LINKPOINT_API_DEBUG, 'Log')) {
      $this->_log($errorMessage, $myorder["oid"] . ($failure ? '_FAILED' : ''));
//      $this->_log($errorMessage . print_r($myorder, true) . print_r($mylphp->xmlString, true), $myorder["oid"] . ($failure ? '_FAILED' : ''));
    }
    if (strstr(MODULE_PAYMENT_LINKPOINT_API_DEBUG, 'Email') || ($failure && strstr(MODULE_PAYMENT_LINKPOINT_API_DEBUG, 'Alert'))) {
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Linkpoint Debug Data' . ($failure ? ' - FAILURE' : ''), $errorMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorMessage)), 'debug');
    }
    //DEBUG ONLY:$this->_log($errorMessage /*. print_r($myorder, true) . print_r($mylphp->xmlString, true)*/, $myorder["oid"]);
    if ($myorder['debugging'] == 'true') exit;
    return $result;
  }

  /**
   * Update order status and order status history based on admin changes sent to gateway
   */
  function _updateOrderStatus($oID, $new_order_status, $comments) {
    global $db;
    $sql_data_array= array(array('fieldName'=>'orders_id', 'value' => $oID, 'type'=>'integer'),
                           array('fieldName'=>'orders_status_id', 'value' => $new_order_status, 'type'=>'integer'),
                           array('fieldName'=>'date_added', 'value' => 'now()', 'type'=>'noquotestring'),
                           array('fieldName'=>'comments', 'value' => $comments, 'type'=>'string'),
                           array('fieldName'=>'customer_notified', 'value' => 0, 'type'=>'integer'));
    $db->perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    $db->Execute("update " . TABLE_ORDERS  . "
                  set orders_status = '" . (int)$new_order_status . "'
                  where orders_id = '" . (int)$oID . "'");
  }

  /**
   * Used to submit a refund for a given transaction.
   */
  function _doRefund($oID, $amount = 0) {
    global $db, $messageStack;
    $_POST['refamt'] = preg_replace('/[^0-9.%]/', '', $_POST['refamt']);
    $new_order_status = (int)MODULE_PAYMENT_LINKPOINT_API_REFUNDED_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;
    $proceedToRefund = true;
    $refundNote = strip_tags(zen_db_input($_POST['refnote']));
    if (isset($_POST['refconfirm']) && $_POST['refconfirm'] != 'on') {
      $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_CONFIRM_ERROR, 'error');
      $proceedToRefund = false;
    }
    if (isset($_POST['buttonrefund']) && $_POST['buttonrefund'] == MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_BUTTON_TEXT) {
      $refundAmt = (float)$_POST['refamt'];
      $new_order_status = (int)MODULE_PAYMENT_LINKPOINT_API_REFUNDED_ORDER_STATUS_ID;
      if ($refundAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_INVALID_REFUND_AMOUNT, 'error');
        $proceedToRefund = false;
      }
    }
    if (isset($_POST['cc_number']) && (int)trim($_POST['cc_number']) == 0) {
      $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_CC_NUM_REQUIRED_ERROR, 'error');
    }
    if (isset($_POST['trans_id']) && (int)trim($_POST['trans_id']) == 0) {
      $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
      $proceedToRefund = false;
    }

    $sql = "select lp_trans_num, transaction_time from " . TABLE_LINKPOINT_API . " where order_id = " . (int)$oID . " and transaction_result = 'APPROVED' order by transaction_time DESC";
    $query = $db->Execute($sql);
    if ($query->RecordCount() < 1) {
      $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_NO_MATCHING_ORDER_FOUND, 'error');
      $proceedToRefund = false;
    }
    /**
     * Submit refund request to gateway
     */
    if ($proceedToRefund) {
      unset($myorder);
      $myorder["ordertype"] = 'CREDIT';
      $myorder["oid"] = $query->fields['lp_trans_num'];
      if ($_POST['trans_id'] != '') $myorder["tdate"] = $_POST['trans_id'];
      $myorder["chargetotal"] = number_format($refundAmt, 2, '.', '');
      $myorder["comments"]  = htmlentities($refundNote, ENT_QUOTES, 'UTF-8');

      $result = $this->_sendRequest($myorder);
      $response_alert = $result['r_approved'] . ' ' . $result['r_error'] . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
      $this->reportable_submit_data['Note'] = $refundNote;
      $failure = ($result["r_approved"] != "APPROVED");
      if ($failure) {
        $messageStack->add_session($response_alert, 'error');
      } else {
        // Success, so save the results
        $this->_updateOrderStatus($oID, $new_order_status, 'REFUND INITIATED. Order ID:' . $result['r_ordernum'] . ' - ' . 'Trans ID: ' . $result['r_tdate'] . "\n" . 'Amount: ' . $myorder["chargetotal"] . "\n" . $refundNote);
        $messageStack->add_session(sprintf(MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_INITIATED, $result['r_tdate'], $result['r_ordernum']), 'success');
        return true;
      }
    }
    return false;
  }

  /**
   * Used to capture part or all of a given previously-authorized transaction.
   */
  function _doCapt($oID, $amt = 0, $currency = 'USD') {
    global $db, $messageStack;
    $_POST['captamt'] = preg_replace('/[^0-9.%]/', '', $_POST['captamt']);

    //@TODO: Read current order status and determine best status to set this to
    $new_order_status = (int)MODULE_PAYMENT_LINKPOINT_API_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;

    $proceedToCapture = true;
    $captureNote = strip_tags(zen_db_input($_POST['captnote']));
    if (isset($_POST['captconfirm']) && $_POST['captconfirm'] == 'on') {
    } else {
      $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPTURE_CONFIRM_ERROR, 'error');
      $proceedToCapture = false;
    }

    $lp_trans_num = (isset($_POST['captauthid']) && $_POST['captauthid'] != '') ? strip_tags(zen_db_input($_POST['captauthid'])) : '';
    $sql = "select lp_trans_num, chargetotal from " . TABLE_LINKPOINT_API . " where order_id = " . (int)$oID . " and transaction_result = 'APPROVED' order by date_added";
    if ($lp_trans_num != '') $sql = "select lp_trans_num, chargetotal from " . TABLE_LINKPOINT_API . " where lp_trans_num = :trans_num: and transaction_result = 'APPROVED' order by date_added";
    $sql = $db->bindVars($sql, ':trans_num:', $lp_trans_num, 'string');
    $query = $db->Execute($sql);
    if ($query->RecordCount() < 1) {
      $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_NO_MATCHING_ORDER_FOUND, 'error');
      $proceedToCapture = false;
    }
    $captureAmt = (isset($_POST['captamt']) && $_POST['captamt'] != '') ? (float)strip_tags(zen_db_input($_POST['captamt'])) : $query->fields['chargetotal'];
    if (isset($_POST['btndocapture']) && $_POST['btndocapture'] == MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_BUTTON_TEXT) {
      if ($captureAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_INVALID_CAPTURE_AMOUNT, 'error');
        $proceedToCapture = false;
      }
    }
    /**
     * Submit capture request to Gateway
     */
    if ($proceedToCapture) {
      unset($myorder);
      $myorder["ordertype"] = 'POSTAUTH';
      $myorder["oid"] = $query->fields['lp_trans_num'];
      $myorder["chargetotal"] = number_format($captureAmt, 2, '.', '');
      $myorder["comments"]  = htmlentities($captureNote, ENT_QUOTES, 'UTF-8');

      $result = $this->_sendRequest($myorder);
      $response_alert = $result['r_approved'] . ' ' . $result['r_error'] . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
      $failure = ($result["r_approved"] != "APPROVED");
      if ($failure) {
        $messageStack->add_session($response_alert, 'error');
      } else {
        // Success, so save the results
        $this->_updateOrderStatus($oID, $new_order_status, 'FUNDS COLLECTED. Auth Code: ' . substr($result['r_code'], 0, 6) . ' - ' . 'Trans ID: ' . $result['r_tdate'] . "\n" . ' Amount: ' . number_format($captureAmt, 2) . "\n" . $captureNote);
        $messageStack->add_session(sprintf(MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPT_INITIATED, $captureAmt, $result['r_tdate'], substr($result['r_code'], 0, 6)), 'success');
        return true;
      }
    }
    return false;
  }
  /**
   * Used to void a given previously-authorized transaction.
   */
  function _doVoid($oID, $note = '') {
    global $db, $messageStack;

    $new_order_status = (int)MODULE_PAYMENT_LINKPOINT_API_REFUNDED_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;
    $voidNote = strip_tags(zen_db_input($_POST['voidnote'] . $note));
    $voidAuthID = trim(strip_tags(zen_db_input($_POST['voidauthid'])));
    $proceedToVoid = true;
    if (isset($_POST['ordervoid']) && $_POST['ordervoid'] == MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_BUTTON_TEXT) {
      if (isset($_POST['voidconfirm']) && $_POST['voidconfirm'] != 'on') {
        $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_CONFIRM_ERROR, 'error');
        $proceedToVoid = false;
      }
    }
    if ($voidAuthID == '') {
      $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
      $proceedToVoid = false;
    }
    $sql = "select lp_trans_num, transaction_time from " . TABLE_LINKPOINT_API . " where order_id = " . (int)$oID . " and transaction_result = 'APPROVED' order by date_added";
    $query = $db->Execute($sql);
    if ($query->RecordCount() < 1) {
      $messageStack->add_session(MODULE_PAYMENT_LINKPOINT_API_TEXT_NO_MATCHING_ORDER_FOUND, 'error');
      $proceedToVoid = false;
    }
    /**
     * Submit void request to Gateway
     */
    if ($proceedToVoid) {
      unset($myorder);
      $myorder["ordertype"] = 'VOID';
      $myorder["oid"] = $query->fields['lp_trans_num'];
      if ($voidAuthID != '') $myorder["tdate"] = $voidAuthID;
      $myorder["comments"]  = htmlentities($voidNote, ENT_QUOTES, 'UTF-8');

      $result = $this->_sendRequest($myorder);
      $response_alert = $result['r_approved'] . ' ' . $result['r_error'] . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
      $failure = ($result["r_approved"] != "APPROVED");
      if ($failure) {
        $messageStack->add_session($response_alert, 'error');
      } else {
        // Success, so save the results
        $this->_updateOrderStatus($oID, $new_order_status, 'VOIDED. OrderNo: ' . $result['r_ordernum'] . ' - Trans ID: ' . $result['r_tdate'] . "\n" . $voidNote);
        $messageStack->add_session(sprintf(MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_INITIATED, $result['r_tdate'], $result['r_ordernum']), 'success');
        return true;
      }
    }
    return false;
  }
}
