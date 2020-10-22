<?php
/**
 * authorize.net AIM payment method class
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
/**
 * Authorize.net Payment Module (AIM version)
 * Ref: https://www.authorize.net/content/dam/authorize/documents/AIM_guide.pdf
 */
class authorizenet_aim extends base {
  /**
   * $code determines the internal 'code' name used to designate "this" payment module
   *
   * @var string
   */
  var $code;
  /**
   * $title is the displayed name for this payment method
   *
   * @var string
   */
  var $title;
  /**
   * $description is a soft name for this payment method
   *
   * @var string
   */
  var $description;
  /**
   * $enabled determines whether this module shows or not... in catalog.
   *
   * @var boolean
   */
  var $enabled;
  /**
   * Do we display specific info about CVV and/or Expiry Date errors?
   * The default is no, as Authorize.net also defaults to no.
   * Be aware that fraudsters may use your site to test stolen cards, and displaying these details gives them too much info. It also costs you in service charges for each attempt they make.
   * If you want to be more specific, and give customers (and fraudsters) more details, set the following line to true.
   */
  var $display_specific_failure_reason_for_cvv_and_date = false;
  /**
   * $delimiter determines what separates each field of returned data from authorizenet
   *
   * @var string (single char)
   */
  var $delimiter = '|';
  /**
   * $encapChar denotes what character is used to encapsulate the response fields
   *
   * @var string (single char)
   */
  var $encapChar = '*';
  /**
   * log file folder
   *
   * @var string
   */
  var $_logDir = '';
  /**
   * communication vars
   */
  var $authorize = '';
  var $commErrNo = 0;
  var $commError = '';
  var $transaction_id = null;
  /**
   * this module collects card-info onsite
   */
  var $collectsCardDataOnsite = TRUE;
  /**
   * debug content var
   */
  var $reportable_submit_data = array();
  /**
   * Given that this module can be used to interact with other gateways (authnet emulators),
   * this var is used to declare which gateway to work with
   */
  private $mode = 'AIM';
  /**
   * @var string the currency enabled in this gateway's merchant account
   */
  private $gateway_currency;

  /**
   * Constructor
   */
  function __construct() {
    global $order, $messageStack;
    $this->code = 'authorizenet_aim';
    $this->enabled = (defined('MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS') && MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS == 'True'); // Whether the module is installed or not
    if (IS_ADMIN_FLAG === true) {
      // Payment module title in Admin
      $this->title = MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_ADMIN_TITLE;
      if ($this->enabled) {
        if (MODULE_PAYMENT_AUTHORIZENET_AIM_LOGIN == 'testing' || MODULE_PAYMENT_AUTHORIZENET_AIM_TXNKEY == 'Test') {
          $this->title .=  '<span class="alert"> (Not Configured)</span>';
        } elseif (MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Test') {
          $this->title .= '<span class="alert"> (in Testing mode)</span>';
        } elseif (MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Sandbox') {
          $this->title .= '<span class="alert"> (in Sandbox Developer mode)</span>';
        }
        if (!function_exists('curl_init')) $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_ERROR_CURL_NOT_FOUND, 'error');
      }
    } else {
      $this->title = MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CATALOG_TITLE; // Payment module title in Catalog
    }
    $this->description = MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_DESCRIPTION; // Descriptive Info about module in Admin
    $this->sort_order = defined('MODULE_PAYMENT_AUTHORIZENET_AIM_SORT_ORDER') ? MODULE_PAYMENT_AUTHORIZENET_AIM_SORT_ORDER : null; // Sort Order of this payment option on the customer payment page

    if (null === $this->sort_order) return false;

    $this->form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false); // Page to go to upon submitting page info
    $this->order_status = (int)DEFAULT_ORDERS_STATUS_ID;
    if ((int)MODULE_PAYMENT_AUTHORIZENET_AIM_ORDER_STATUS_ID > 0) {
      $this->order_status = (int)MODULE_PAYMENT_AUTHORIZENET_AIM_ORDER_STATUS_ID;
    }
    // Reset order status to pending if capture pending:
    if (MODULE_PAYMENT_AUTHORIZENET_AIM_AUTHORIZATION_TYPE == 'Authorize') $this->order_status = 1;

    $this->_logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;

    if (is_object($order)) $this->update_status();

    // verify table structure
    if (IS_ADMIN_FLAG === true) $this->tableCheckup();

    // set the currency for the gateway (others will be converted to this one before submission)
    $this->gateway_currency = MODULE_PAYMENT_AUTHORIZENET_AIM_CURRENCY;
  }
  /**
   * calculate zone matches and flag settings to determine whether this module should display to customers or not
   *
   */
  function update_status() {
    global $order, $db;
    if (IS_ADMIN_FLAG === false) {
      // if store is not running in SSL, cannot offer credit card module, for PCI reasons
      if (MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Production' && substr(HTTP_SERVER, 6) != 'https:' && (!defined('ENABLE_SSL') || ENABLE_SSL != 'true')) $this->enabled = FALSE;
    }
    // check other reasons for the module to be deactivated:
    if ($this->enabled && (int)MODULE_PAYMENT_AUTHORIZENET_AIM_ZONE > 0 && isset($order->billing['country']['id'])) {
      $check_flag = false;
      $check = $db->Execute("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_PAYMENT_AUTHORIZENET_AIM_ZONE . "' AND zone_country_id = '" . (int)$order->billing['country']['id'] . "' ORDER BY zone_id");
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

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }

    // other status checks?
    if ($this->enabled) {
      // other checks here
    }
  }
  /**
   * JS validation which does error-checking of data-entry if this module is selected for use
   * (Number, Owner, and CVV Lengths)
   *
   * @return string
   */
  function javascript_validation() {
    $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
    '    var cc_owner = document.checkout_payment.authorizenet_aim_cc_owner.value;' . "\n" .
    '    var cc_number = document.checkout_payment.authorizenet_aim_cc_number.value;' . "\n";
    if (MODULE_PAYMENT_AUTHORIZENET_AIM_USE_CVV == 'True')  {
      $js .= '    var cc_cvv = document.checkout_payment.authorizenet_aim_cc_cvv.value;' . "\n";
    }
    $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_JS_CC_OWNER . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" .
    '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_JS_CC_NUMBER . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n";
    if (MODULE_PAYMENT_AUTHORIZENET_AIM_USE_CVV == 'True')  {
      $js .= '    if (cc_cvv == "" || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n".
      '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_JS_CC_CVV . '";' . "\n" .
      '      error = 1;' . "\n" .
      '    }' . "\n" ;
    }
    $js .= '  }' . "\n";

    return $js;
  }
  /**
   * Display Credit Card Information Submission Fields on the Checkout Payment Page
   *
   * @return array
   */
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
                       'module' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CATALOG_TITLE,
                       'fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CREDIT_CARD_OWNER,
                                               'field' => zen_draw_input_field('authorizenet_aim_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="'.$this->code.'-cc-owner"'. $onFocus . ' autocomplete="off"'),
                                               'tag' => $this->code.'-cc-owner'),
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CREDIT_CARD_NUMBER,
                                               'field' => zen_draw_input_field('authorizenet_aim_cc_number', '', 'id="'.$this->code.'-cc-number"' . $onFocus . ' autocomplete="off"'),
                                               'tag' => $this->code.'-cc-number'),
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CREDIT_CARD_EXPIRES,
                                               'field' => zen_draw_pull_down_menu('authorizenet_aim_cc_expires_month', $expires_month, strftime('%m'), 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('authorizenet_aim_cc_expires_year', $expires_year, '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
                                               'tag' => $this->code.'-cc-expires-month')));
    if (MODULE_PAYMENT_AUTHORIZENET_AIM_USE_CVV == 'True') {
      $selection['fields'][] = array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CVV,
                                   'field' => zen_draw_input_field('authorizenet_aim_cc_cvv', '', 'size="4" maxlength="4"' . ' id="'.$this->code.'-cc-cvv"' . $onFocus . ' autocomplete="off"') . ' ' . '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_POPUP_CVV_LINK . '</a>',
                                   'tag' => $this->code.'-cc-cvv');
    }
    return $selection;
  }
  /**
   * Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   *
   */
  function pre_confirmation_check() {
    global $messageStack;

    include(DIR_WS_CLASSES . 'cc_validation.php');

    $cc_validation = new cc_validation();
    $result = $cc_validation->validate($_POST['authorizenet_aim_cc_number'], $_POST['authorizenet_aim_cc_expires_month'], $_POST['authorizenet_aim_cc_expires_year'], $_POST['authorizenet_aim_cc_cvv']);
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

    if ( ($result == false) || ($result < 1) ) {
      $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    $this->cc_card_type = $cc_validation->cc_type;
    $this->cc_card_number = $cc_validation->cc_number;
    $this->cc_expiry_month = $cc_validation->cc_expiry_month;
    $this->cc_expiry_year = $cc_validation->cc_expiry_year;
  }
  /**
   * Display Credit Card Information on the Checkout Confirmation Page
   *
   * @return array
   */
  function confirmation() {
    $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CREDIT_CARD_TYPE,
                                                  'field' => $this->cc_card_type),
                                            array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CREDIT_CARD_OWNER,
                                                  'field' => $_POST['authorizenet_aim_cc_owner']),
                                            array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CREDIT_CARD_NUMBER,
                                                  'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                            array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CREDIT_CARD_EXPIRES,
                                                  'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['authorizenet_aim_cc_expires_month'], 1, '20' . $_POST['authorizenet_aim_cc_expires_year']))) ));
    return $confirmation;
  }
  /**
   * Build the data and actions to process when the "Submit" button is pressed on the order-confirmation screen.
   * This sends the data to the payment gateway for processing.
   * (These are hidden fields on the checkout confirmation page)
   *
   * @return string
   */
  function process_button() {
    $process_button_string = zen_draw_hidden_field('cc_owner', $_POST['authorizenet_aim_cc_owner']) .
                             zen_draw_hidden_field('cc_expires', $this->cc_expiry_month . substr($this->cc_expiry_year, -2)) .
                             zen_draw_hidden_field('cc_type', $this->cc_card_type) .
                             zen_draw_hidden_field('cc_number', $this->cc_card_number);
    if (MODULE_PAYMENT_AUTHORIZENET_AIM_USE_CVV == 'True') {
      $process_button_string .= zen_draw_hidden_field('cc_cvv', $_POST['authorizenet_aim_cc_cvv']);
    }
    $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

    return $process_button_string;
  }
  function process_button_ajax() {
    $processButton = array('ccFields'=>array('cc_number'=>'authorizenet_aim_cc_number', 'cc_owner'=>'authorizenet_aim_cc_owner', 'cc_cvv'=>'authorizenet_aim_cc_cvv', 'cc_expires'=>array('name'=>'concatExpiresFields', 'args'=>"['authorizenet_aim_cc_expires_month','authorizenet_aim_cc_expires_year']"), 'cc_expires_month'=>'authorizenet_aim_cc_expires_month', 'cc_expires_year'=>'authorizenet_aim_cc_expires_year', 'cc_type' => $this->cc_card_type), 'extraFields'=>array(zen_session_name()=>zen_session_id()));
        return $processButton;
  }
  /**
   * Store the CC info to the order and process any results that come back from the payment gateway
   */
  function before_process() {
    global $response, $db, $order, $messageStack;
    $order->info['cc_owner']   = $_POST['cc_owner'];
    $order->info['cc_number']  = str_pad(substr($_POST['cc_number'], -4), strlen($_POST['cc_number']), "X", STR_PAD_LEFT);
    $order->info['cc_expires'] = '';  // $_POST['cc_expires'];
    $order->info['cc_cvv']     = '***';
    $sessID = zen_session_id();

    $this->include_x_type = TRUE;

    // DATA PREPARATION SECTION
    unset($submit_data);  // Cleans out any previous data stored in the variable

    // Create a string that contains a listing of products ordered for the description field
    $description = '';
    for ($i=0; $i<sizeof($order->products); $i++) {
      $description .= $order->products[$i]['name'] . ' (qty: ' . $order->products[$i]['qty'] . ') + ';
    }
    // Remove the last "\n" from the string
    $description = substr($description, 0, -2);

    // Create a variable that holds the order time
    $order_time = date("F j, Y, g:i a");

    // Calculate the next expected order id (adapted from code written by Eric Stamper - 01/30/2004 Released under GPL)
    $last_order_id = $db->Execute("SELECT orders_id FROM " . TABLE_ORDERS . " ORDER BY orders_id desc LIMIT 1");
    $new_order_id = $last_order_id->fields['orders_id'];
    $new_order_id = ($new_order_id + 1);

    // add randomized suffix to order id to produce uniqueness ... since it's unwise to submit the same order-number twice to authorize.net
    $new_order_id = (string)$new_order_id . '-' . zen_create_random_value(6, 'chars');


    // Populate an array that contains all of the data to be sent to Authorize.net
    $submit_data = array(
                         'x_login' => trim(MODULE_PAYMENT_AUTHORIZENET_AIM_LOGIN),
                         'x_tran_key' => trim(MODULE_PAYMENT_AUTHORIZENET_AIM_TXNKEY),
                         'x_relay_response' => 'FALSE', // AIM uses direct response, not relay response
                         'x_delim_char' => $this->delimiter,  // The default delimiter is a comma
                         'x_encap_char' => $this->encapChar,  // The divider to encapsulate response fields
                         'x_version' => '3.1',  // 3.1 is required to use CVV codes
                         'x_method' => 'CC',
                         'x_amount' => round($order->info['total'], 2),
                         'x_currency_code' => $order->info['currency'],
                         'x_market_type' => 0,
                         'x_card_num' => $_POST['cc_number'],
                         'x_exp_date' => $_POST['cc_expires'],
                         'x_card_code' => $_POST['cc_cvv'],
                         'x_email_customer' => MODULE_PAYMENT_AUTHORIZENET_AIM_EMAIL_CUSTOMER == 'True' ? 'TRUE': 'FALSE',
                         'x_email_merchant' => MODULE_PAYMENT_AUTHORIZENET_AIM_EMAIL_MERCHANT == 'True' ? 'TRUE': 'FALSE',
                         'x_cust_id' => $_SESSION['customer_id'],
                         'x_invoice_num' => (MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Test' ? 'TEST-' : (MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Sandbox' ? 'SANDBOX-' : '')) . $new_order_id,
                         'x_first_name' => $order->billing['firstname'],
                         'x_last_name' => $order->billing['lastname'],
                         'x_company' => $order->billing['company'],
                         'x_address' => $order->billing['street_address'],
                         'x_city' => $order->billing['city'],
                         'x_state' => $order->billing['state'],
                         'x_zip' => $order->billing['postcode'],
                         'x_country' => $order->billing['country']['title'],
                         'x_phone' => $order->customer['telephone'],
                         'x_email' => $order->customer['email_address'],
                         'x_ship_to_first_name' => $order->delivery['firstname'],
                         'x_ship_to_last_name' => $order->delivery['lastname'],
                         'x_ship_to_address' => $order->delivery['street_address'],
                         'x_ship_to_city' => $order->delivery['city'],
                         'x_ship_to_state' => $order->delivery['state'],
                         'x_ship_to_zip' => $order->delivery['postcode'],
                         'x_ship_to_country' => $order->delivery['country']['title'],
                         'x_description' => $description,
                         'x_recurring_billing' => 'NO',
                         'x_customer_ip' => zen_get_ip_address(),
                         'x_po_num' => date('M-d-Y h:i:s'), //$order->info['po_number'],
                         'x_freight' => round((float)$order->info['shipping_cost'],2),
                         'x_tax_exempt' => 'FALSE', /* 'TRUE' or 'FALSE' */
                         'x_tax' => round((float)$order->info['tax'],2),
                         'x_duty' => '0',
                         'x_device_type' => 8,
                         'x_allow_partial_Auth' => 'FALSE', // unable to accept partial authorizations at this time

                         // Additional Merchant-defined variables go here
                         'Date' => $order_time,
                         'IP' => zen_get_ip_address(),
                         'Session' => $sessID );

    $this->notify('NOTIFY_PAYMENT_AUTHNET_EMULATOR_CHECK', $this->code, $submit_data);
    if ($this->include_x_type) {
      $submit_data['x_type'] = MODULE_PAYMENT_AUTHORIZENET_AIM_AUTHORIZATION_TYPE == 'Authorize' ? 'AUTH_ONLY': 'AUTH_CAPTURE';
    }

    // force conversion to supported currencies: USD, GBP, CAD, EUR, AUD, NZD
    if ($order->info['currency'] != $this->gateway_currency) {
      global $currencies;
      $submit_data['x_amount'] = round($order->info['total'] * $currencies->get_value($this->gateway_currency), 2);
      $submit_data['x_currency_code'] = $this->gateway_currency;
      $submit_data['x_description'] .= ' (Converted from: ' . number_format($order->info['total'] * $order->info['currency_value'], 2) . ' ' . $order->info['currency'] . ')';
      unset($submit_data['x_tax'], $submit_data['x_freight']);
    }

    $this->submit_extras = array();
    $this->notify('NOTIFY_PAYMENT_AUTHNET_PRESUBMIT_HOOK', $this->code, $submit_data);
    unset($this->submit_extras['x_login'], $this->submit_extras['x_tran_key']);
    if (sizeof($this->submit_extras)) $submit_data = array_merge($submit_data, $this->submit_extras);

    unset($response);
    $response = $this->_sendRequest($submit_data);
    $this->notify('NOTIFY_PAYMENT_AUTHNET_POSTSUBMIT_HOOK', $this->code, $response);
    $response_code = $response[0];
    $response_text = $response[3];
    $this->auth_code = $response[4];
    $this->transaction_id = $response[6];
    $this->avs_response= $response[5];
    $this->ccv_response= $response[38];
    $response_msg_to_customer = $response_text . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');

    if ($this->display_specific_failure_reason_for_cvv_and_date) {
        if (($response_code == '2' && in_array($response[2], array('44', '45', '65'))) || ($response_code == '3' && $response[2] == '78')) {
          $response_msg_to_customer = 'CVV problem ';
        }
        if ($response_code == '3' && in_array($response[2], array('7', '8'))) {
          $response_msg_to_customer = 'Problem with expiration date ';
        }
    }

    if ($response[0] == '3' && $response[2] == '103') $response['ErrorDetails'] = 'Invalid Transaction Key in AIM configuration.';
    if ($response[0] == '2' && $response[2] == '44') $response['ErrorDetails'] = 'Declined due to CVV refusal by issuing bank.';
    if ($response[0] == '2' && $response[2] == '45') $response['ErrorDetails'] = 'Declined due to AVS/CVV filters.';
    if ($response[0] == '2' && $response[2] == '65') $response['ErrorDetails'] = 'Declined due to custom CVV filters.';
    if ($response[0] == '3' && $response[2] == '78') $response['ErrorDetails'] = 'Failed due to incorrect CVV format.';
    if ($response[0] == '3' && $response[2] == '7') $response['ErrorDetails'] = 'Expired card.';
    if ($response[0] == '3' && $response[2] == '8') $response['ErrorDetails'] = 'Expiry date invalid.';
    if ($response[0] == '3' && $response[2] == '66') $response['ErrorDetails'] = 'Transaction did not meet security guideline requirements.';
    if ($response[0] == '3' && $response[2] == '128') $response['ErrorDetails'] = 'Refused by customers bank.';
    if ($response[0] == '2' && $response[2] == '250') $response['ErrorDetails'] = 'Transaction submitted from a blocked IP address.';
    if ($response[0] == '2' && $response[2] == '251') $response['ErrorDetails'] = 'Declined by Fraud Detection Suite filter.';
    if ($response[0] == '4' && in_array($response[2], array('193', '252', '253'))) {
      $this->order_status = 1;
      $this->transaction_id .= ' ***NOTE: Held for review by merchant.';
      $response['ErrorDetails'] = 'Transaction held for review by merchant or fraud detection suite.';
    }

    $this->_debugActions($response, $order_time, $sessID);

    if ($this->commError != '') {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_COMM_ERROR . ' (' . $this->commErrNo . ')', 'caution');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    // If the response code is not 1 (approved) or 4 (held for review) then redirect back to the payment page with the appropriate error message
    if ($response_code != '1' && $response_code != '4') {
      $messageStack->add_session('checkout_payment', $response_msg_to_customer . ' - ' . MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_DECLINED_MESSAGE, 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }
    if ($response_code == '4'){
        $this->order_status = (int)MODULE_PAYMENT_AUTHORIZENET_AIM_REVIEW_ORDER_STATUS_ID;
    }
    if ($response[88] != '') {
      $_SESSION['payment_method_messages'] = $response[88];
    }
    $order->info['cc_type'] = $response[51];
  }
  /**
   * Post-process activities. Updates the order-status history data with the auth code from the transaction.
   *
   * @return boolean
   */
  function after_process() {
    global $insert_id, $order, $currencies;

    $comments = 'Credit Card payment.  AUTH: ' . $this->auth_code . ' TransID: ' . $this->transaction_id;
    if ($order->info['currency'] != $this->gateway_currency) {
      $comments .= ' (' . number_format($order->info['total'] * $currencies->get_value($this->gateway_currency), 2) . ' ' . $this->gateway_currency . ')';
    }
    zen_update_orders_history($insert_id, $comments, null, $this->order_status, -1);

    return false;
  }
  /**
    * Build admin-page components
    *
    * @param int $zf_order_id
    * @return string
    */
  function admin_notification($zf_order_id) {
    $output = '';
    require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/authorizenet/authorizenet_admin_notification.php');
    return $output;
  }
  /**
   * Used to display error message details
   *
   * @return array
   */
  function get_error() {
    $error = array('title' => MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_ERROR,
                   'error' => stripslashes(urldecode($_GET['error'])));
    return $error;
  }
  /**
   * Check to see whether module is installed
   *
   * @return boolean
   */
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    if ($this->_check > 0) $this->keys(); // install any missing keys
    return $this->_check;
  }
  /**
   * Install the payment module and its configuration settings
   *
   */
  function install() {
    global $db, $messageStack;
    if (defined('MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS')) {
      $messageStack->add_session('Authorize.net (AIM) module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=authorizenet_aim', 'NONSSL'));
      return 'failed';
    }
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Authorize.net (AIM) Module', 'MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS', 'True', 'Do you want to accept Authorize.net payments via the AIM Method?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Login ID', 'MODULE_PAYMENT_AUTHORIZENET_AIM_LOGIN', 'testing', 'The API Login ID used for the Authorize.net service', '6', '0', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_AIM_TXNKEY', 'Test', 'Transaction Key from Authorize.net account<br>See <a href=\"https://support.authorize.net/s/article/How-do-I-obtain-my-API-Login-ID-and-Transaction-Key\" rel=\"noopener\" target=\"_blank\">How-do-I-obtain-my-API-Login-ID-and-Transaction-Key</a>', '6', '0', now(), 'zen_cfg_password_display')");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE', 'Test', 'Transaction mode used for processing orders.<br><strong>Production</strong>=Live processing with real account credentials<br><strong>Test</strong>=Simulations with real account credentials<br><strong>Sandbox</strong>=use special sandbox transaction key to do special testing of success/fail transaction responses (obtain sandbox credentials via <a href=\"https://developer.authorize.net/hello_world/sandbox/\">developer.authorize.net</a>)', '6', '0', 'zen_cfg_select_option(array(\'Test\', \'Production\', \'Sandbox\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Authorization Type', 'MODULE_PAYMENT_AUTHORIZENET_AIM_AUTHORIZATION_TYPE', 'Authorize', 'Do you want submitted credit card transactions to be authorized only, or authorized and captured?', '6', '0', 'zen_cfg_select_option(array(\'Authorize\', \'Authorize+Capture\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Database Storage', 'MODULE_PAYMENT_AUTHORIZENET_AIM_STORE_DATA', 'True', 'Do you want to save the gateway communications data to the database?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Notifications', 'MODULE_PAYMENT_AUTHORIZENET_AIM_EMAIL_CUSTOMER', 'False', 'Should Authorize.Net email a receipt to the customer?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Merchant Notifications', 'MODULE_PAYMENT_AUTHORIZENET_AIM_EMAIL_MERCHANT', 'False', 'Should Authorize.Net email a receipt to the merchant?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Request CVV Number', 'MODULE_PAYMENT_AUTHORIZENET_AIM_USE_CVV', 'True', 'Do you want to ask the customer for the card\'s CVV number', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_AUTHORIZENET_AIM_SORT_ORDER', '0', 'Sort order of display of payment modules to the customer. Lowest is displayed first.', '6', '0', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_AIM_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Completed Order Status', 'MODULE_PAYMENT_AUTHORIZENET_AIM_ORDER_STATUS_ID', '2', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refunded Order Status', 'MODULE_PAYMENT_AUTHORIZENET_AIM_REFUNDED_ORDER_STATUS_ID', '1', 'Set the status of refunded orders to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Held-For-Review Order Status', 'MODULE_PAYMENT_AUTHORIZENET_AIM_REVIEW_ORDER_STATUS_ID', '1', 'Set the status of orders made with this payment module, BUT are needing to be reviewed for processing', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Mode', 'MODULE_PAYMENT_AUTHORIZENET_AIM_DEBUGGING', 'Off', 'Would you like to enable debug mode?  A complete detailed log of failed transactions may be emailed to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Log File\', \'Log and Email\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Supported', 'MODULE_PAYMENT_AUTHORIZENET_AIM_CURRENCY', 'USD', 'Which currency is your Authnet Gateway Account configured to accept?<br>(Purchases in any other currency will be pre-converted to this currency before submission using the exchange rates in your store admin.)', '6', '0', 'zen_cfg_select_option(array(\'USD\', \'CAD\', \'GBP\', \'EUR\', \'AUD\', \'NZD\'), ', now())");

  }
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $db;
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_AUTHORIZENET\_AIM\_%'");
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    if (defined('MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS')) {
      global $db;
      if (!defined('MODULE_PAYMENT_AUTHORIZENET_AIM_REVIEW_ORDER_STATUS_ID')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Held-For-Review Order Status', 'MODULE_PAYMENT_AUTHORIZENET_AIM_REVIEW_ORDER_STATUS_ID', '1', 'Set the status of orders made with this payment module, BUT are needing to be reviewed for processing', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
      }
      if (!defined('MODULE_PAYMENT_AUTHORIZENET_AIM_CURRENCY')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Supported', 'MODULE_PAYMENT_AUTHORIZENET_AIM_CURRENCY', 'USD', 'Which currency is your Authnet Gateway Account configured to accept?<br>(Purchases in any other currency will be pre-converted to this currency before submission using the exchange rates in your store admin.)', '6', '0', 'zen_cfg_select_option(array(\'USD\', \'CAD\', \'GBP\', \'EUR\', \'AUD\', \'NZD\'), ', now())");
      }
    }
    return array('MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS', 'MODULE_PAYMENT_AUTHORIZENET_AIM_LOGIN', 'MODULE_PAYMENT_AUTHORIZENET_AIM_TXNKEY', 'MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE', 'MODULE_PAYMENT_AUTHORIZENET_AIM_CURRENCY', 'MODULE_PAYMENT_AUTHORIZENET_AIM_AUTHORIZATION_TYPE', 'MODULE_PAYMENT_AUTHORIZENET_AIM_STORE_DATA', 'MODULE_PAYMENT_AUTHORIZENET_AIM_EMAIL_CUSTOMER', 'MODULE_PAYMENT_AUTHORIZENET_AIM_EMAIL_MERCHANT', 'MODULE_PAYMENT_AUTHORIZENET_AIM_USE_CVV', 'MODULE_PAYMENT_AUTHORIZENET_AIM_SORT_ORDER', 'MODULE_PAYMENT_AUTHORIZENET_AIM_ZONE', 'MODULE_PAYMENT_AUTHORIZENET_AIM_ORDER_STATUS_ID', 'MODULE_PAYMENT_AUTHORIZENET_AIM_REFUNDED_ORDER_STATUS_ID', 'MODULE_PAYMENT_AUTHORIZENET_AIM_REVIEW_ORDER_STATUS_ID', 'MODULE_PAYMENT_AUTHORIZENET_AIM_DEBUGGING');
  }
  /**
   * Send communication request
   */
  function _sendRequest($submit_data) {
    global $request_type;

    // Populate an array that contains all of the data to be sent to Authorize.net
    $submit_data = array_merge(array(
                         'x_login' => trim(MODULE_PAYMENT_AUTHORIZENET_AIM_LOGIN),
                         'x_tran_key' => trim(MODULE_PAYMENT_AUTHORIZENET_AIM_TXNKEY),
                         'x_relay_response' => 'FALSE',
                         'x_delim_data' => 'TRUE',
                         'x_delim_char' => $this->delimiter,  // The default delimiter is a comma
                         'x_encap_char' => $this->encapChar,  // The divider to encapsulate response fields
                         'x_version' => '3.1',  // 3.1 is required to use CVV codes
                         'x_solution_id' => 'A1000003', // used by authorize.net
                         ), $submit_data);

    if(MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Test') {
      $submit_data['x_test_request'] = 'TRUE';
    }

    // set URL
    $this->mode = 'AIM';
    $this->notify('NOTIFY_PAYMENT_AUTHNET_MODE_SELECTION', $this->mode, $submit_data);

    switch ($this->mode) {
      case 'eProcessing':
        //eProcessing sometimes uses an AIM emulator, in which case if you're using this module for that purpose, use the notifier point above to have an observer class change the $class->mode to 'eProcessing', which will trigger the correct URL to be used.
        $url = 'https://www.eprocessingnetwork.com/cgi-bin/an/order.pl';
        break;
      case (MODULE_PAYMENT_AUTHORIZENET_AIM_DEBUGGING == 'echo'):
      case (defined('AUTHORIZENET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'echo'):
      case 'dump':
        $url = 'https://developer.authorize.net/param_dump.asp';
        break;
      default:
      case 'AIM':
        $url = 'https://secure2.authorize.net/gateway/transact.dll';
        $devurl = 'https://test.authorize.net/gateway/transact.dll';
        $certurl = 'https://certification.authorize.net/gateway/transact.dll';
        if (MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Sandbox') $url = $devurl;
        if (defined('AUTHORIZENET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'certify') $url = $certurl;
      break;
    }

    // concatenate the submission data into $data variable after sanitizing to protect delimiters
    $data = '';
    foreach($submit_data as $key => $value) {
      if ($key != 'x_delim_char' && $key != 'x_encap_char') {
        $value = str_replace(array($this->delimiter, $this->encapChar,'"',"'",'&amp;','&', '='), '', $value);
      }
      $data .= $key . '=' . urlencode($value) . '&';
    }
    // Remove the last "&" from the string
    $data = substr($data, 0, -1);


    // prepare a copy of submitted data for error-reporting purposes
    $this->reportable_submit_data = $submit_data;
    $this->reportable_submit_data['x_login'] = '*******';
    $this->reportable_submit_data['x_tran_key'] = '*******';
    if (isset($this->reportable_submit_data['x_card_num'])) $this->reportable_submit_data['x_card_num'] = str_repeat('X', strlen($this->reportable_submit_data['x_card_num'] - 4)) . substr($this->reportable_submit_data['x_card_num'], -4);
    if (isset($this->reportable_submit_data['x_exp_date'])) $this->reportable_submit_data['x_exp_date'] = '****';
    if (isset($this->reportable_submit_data['x_card_code'])) $this->reportable_submit_data['x_card_code'] = '****';
    $this->reportable_submit_data['url'] = $url;


    // Post order info data to Authorize.net via CURL - Requires that PHP has CURL support installed

    // Send CURL communication
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, ($request_type == 'SSL' ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ));
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

    $this->authorize = curl_exec($ch);
    $this->commError = curl_error($ch);
    $this->commErrNo = curl_errno($ch);

    if ($this->commErrNo == 35) {
      trigger_error('ALERT: Could not process Authorize.net AIM transaction via normal CURL communications. Your server is encountering connection problems using TLS 1.2 ... because your hosting company cannot autonegotiate a secure protocol with modern security protocols. We will try the transaction again, but this is resulting in a very long delay for your customers, and could result in them attempting duplicate purchases. Get your hosting company to update their TLS capabilities ASAP.', E_USER_NOTICE);
      curl_setopt($ch, CURLOPT_SSLVERSION, 6); // Using the defined value of 6 instead of CURL_SSLVERSION_TLSv1_2 since these outdated hosts also don't properly implement this constant either.
      $this->authorize = curl_exec($ch);
      $this->commError = curl_error($ch);
      $this->commErrNo = curl_errno($ch);
    }

    $this->commInfo = @curl_getinfo($ch);
    curl_close ($ch);

    // if in 'echo' mode, dump the returned data to the browser and stop execution
    if ((defined('AUTHORIZENET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'echo') || MODULE_PAYMENT_AUTHORIZENET_AIM_DEBUGGING == 'echo') {
      echo $this->authorize . ($this->commErrNo != 0 ? '<br />' . $this->commErrNo . ' ' . $this->commError : '') . '<br />';
      die('Press the BACK button in your browser to return to the previous page.');
    }

    $this->notify('NOTIFY_PAYMENT_AUTHNET_ENCAPSULATION_CHECK');
    // parse the data received back from the gateway, taking into account the delimiters and encapsulation characters
    $stringToParse = $this->authorize;
    if (substr($stringToParse,0,1) == $this->encapChar) $stringToParse = substr($stringToParse,1);
    $stringToParse = preg_replace('/.{*}' . $this->encapChar . '$/', '', $stringToParse);
    $response = explode($this->encapChar . $this->delimiter . $this->encapChar, $stringToParse);

    return $response;
  }
  /**
   * Used to do any debug logging / tracking / storage as required.
   */
  function _debugActions($response, $order_time= '', $sessID = '') {
    global $db;
    if ($order_time == '') $order_time = date("F j, Y, g:i a");
    // convert output to 1-based array for easier understanding:
    $resp_output = $response;
    array_unshift($resp_output, 'Response from gateway' . (isset($response['ErrorDetails']) ? ': ' . $response['ErrorDetails'] : ''));

    // DEBUG LOGGING
      $errorMessage = date('M-d-Y h:i:s') .
                      "\n=================================\n\n" .
                      ($this->commError !='' ? 'Comm results: ' . $this->commErrNo . ' ' . $this->commError . "\n\n" : '') .
                      'Response Code: ' . $response[0] . ".\nResponse Text: " . $response[3] . "\n\n" .
                      'Sending to Authorizenet: ' . print_r($this->reportable_submit_data, true) . "\n\n" .
                      'Results Received back from Authorizenet: ' . print_r($resp_output, true) . "\n\n" .
                      'CURL communication info: ' . print_r($this->commInfo, true) . "\n";
      if (CURL_PROXY_REQUIRED == 'True') $errorMessage .= 'Using CURL Proxy: [' . CURL_PROXY_SERVER_DETAILS . ']  with Proxy Tunnel: ' .($this->proxy_tunnel_flag ? 'On' : 'Off') . "\n";
      $errorMessage .= "\nRAW data received: \n" . $this->authorize . "\n\n";

      if (strstr(MODULE_PAYMENT_AUTHORIZENET_AIM_DEBUGGING, 'Log') || strstr(MODULE_PAYMENT_AUTHORIZENET_AIM_DEBUGGING, 'All') || MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Sandbox' || (defined('AUTHORIZENET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'certify')) {
        $key = $response[6] . '_' . time() . '_' . zen_create_random_value(4);
        $file = $this->_logDir . '/' . 'AIM_Debug_' . $key . '.log';
        if ($fp = @fopen($file, 'a')) {
          fwrite($fp, $errorMessage);
          fclose($fp);
        }
      }
      if (($response[0] != '1' && stristr(MODULE_PAYMENT_AUTHORIZENET_AIM_DEBUGGING, 'Alerts')) || strstr(MODULE_PAYMENT_AUTHORIZENET_AIM_DEBUGGING, 'Email')) {
        zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'AuthorizenetAIM Alert ' . $response[7] . ' ' . date('M-d-Y h:i:s') . ' ' . $response[6], $errorMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorMessage)), 'debug');
      }

    // DATABASE SECTION
    // Insert the send and receive response data into the database.
    // This can be used for testing or for implementation in other applications
    // This can be turned on and off if the Admin Section
    if (MODULE_PAYMENT_AUTHORIZENET_AIM_STORE_DATA == 'True'){
      $db_response_text = $response[3] . ($this->commError !='' ? ' - Comm results: ' . $this->commErrNo . ' ' . $this->commError : '');
      $db_response_text .= ($response[0] == 2 && $response[2] == 4) ? ' NOTICE: Card should be picked up - possibly stolen ' : '';
      $db_response_text .= ($response[0] == 3 && $response[2] == 11) ? ' DUPLICATE TRANSACTION ATTEMPT ' : '';

      // Insert the data into the database
      $sql = "INSERT INTO " . TABLE_AUTHORIZENET . "  (id, customer_id, order_id, response_code, response_text, authorization_type, transaction_id, sent, received, time, session_id) VALUES (NULL, :custID, :orderID, :respCode, :respText, :authType, :transID, :sentData, :recvData, :orderTime, :sessID )";
      $sql = $db->bindVars($sql, ':custID', $_SESSION['customer_id'], 'integer');
      $sql = $db->bindVars($sql, ':orderID', preg_replace('/[^0-9]/', '', $response[7]), 'integer');
      $sql = $db->bindVars($sql, ':respCode', $response[0], 'integer');
      $sql = $db->bindVars($sql, ':respText', $db_response_text, 'string');
      $sql = $db->bindVars($sql, ':authType', $response[11], 'string');
      if (trim($this->transaction_id) != '') {
        $sql = $db->bindVars($sql, ':transID', substr($this->transaction_id, 0, strpos($this->transaction_id, ' ')), 'string');
      } else {
        $sql = $db->bindVars($sql, ':transID', 'NULL', 'passthru');
      }
      $sql = $db->bindVars($sql, ':sentData', print_r($this->reportable_submit_data, true), 'string');
      $sql = $db->bindVars($sql, ':recvData', print_r($response, true), 'string');
      $sql = $db->bindVars($sql, ':orderTime', $order_time, 'string');
      $sql = $db->bindVars($sql, ':sessID', $sessID, 'string');
      $db->Execute($sql);
    }
  }
  /**
   * Check and fix table structure if appropriate
   */
  function tableCheckup() {
    global $db, $sniffer;
    $fieldOkay1 = (method_exists($sniffer, 'field_type')) ? $sniffer->field_type(TABLE_AUTHORIZENET, 'transaction_id', 'varchar(64)', false) : false;
    if ($fieldOkay1 !== true) {
      $db->Execute("ALTER TABLE " . TABLE_AUTHORIZENET . " MODIFY transaction_id varchar(64) default NULL");
    }
  }
 /**
   * Used to submit a refund for a given transaction.
   */
  function _doRefund($oID, $amount = 0) {
    global $messageStack;
    $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_AIM_REFUNDED_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;
    $proceedToRefund = true;
    $refundNote = strip_tags(zen_db_input($_POST['refnote']));
    if (isset($_POST['refconfirm']) && $_POST['refconfirm'] != 'on') {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_REFUND_CONFIRM_ERROR, 'error');
      $proceedToRefund = false;
    }
    if (isset($_POST['buttonrefund']) && $_POST['buttonrefund'] == MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_BUTTON_TEXT) {
      $refundAmt = (float)$_POST['refamt'];
      $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_AIM_REFUNDED_ORDER_STATUS_ID;
      if ($refundAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_INVALID_REFUND_AMOUNT, 'error');
        $proceedToRefund = false;
      }
    }
    if (isset($_POST['cc_number']) && trim($_POST['cc_number']) == '') {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CC_NUM_REQUIRED_ERROR, 'error');
    }
    if (isset($_POST['trans_id']) && trim($_POST['trans_id']) == '') {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
      $proceedToRefund = false;
    }

    /**
     * Submit refund request to gateway
     */
    if ($proceedToRefund) {
      $submit_data = array('x_type' => 'CREDIT',
                           'x_card_num' => trim($_POST['cc_number']),
                           'x_amount' => round($refundAmt, 2),
                           'x_trans_id' => trim($_POST['trans_id'])
                           );
      unset($response);
      $response = $this->_sendRequest($submit_data);
      $response_code = $response[0];
      $response_text = $response[3];
      $response_alert = $response_text . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
      $this->reportable_submit_data['Note'] = $refundNote;
      $this->_debugActions($response);

      if ($response_code != '1') {
        $messageStack->add_session($response_alert, 'error');
      } else {
        // Success, so save the results
        $comments = 'REFUND INITIATED. Trans ID:' . $response[6] . ' ' . $response[4]. "\n" . ' Gross Refund Amt: ' . $response[9] . "\n" . $refundNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_REFUND_INITIATED, $response[9], $response[6]), 'success');
        return true;
      }
    }
    return false;
  }

  /**
   * Used to capture part or all of a given previously-authorized transaction.
   */
  function _doCapt($oID, $amt = 0, $currency = 'USD') {
    global $messageStack;

    //@TODO: Read current order status and determine best status to set this to
    $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_AIM_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;

    $proceedToCapture = true;
    $captureNote = strip_tags(zen_db_input($_POST['captnote']));
    if (isset($_POST['captconfirm']) && $_POST['captconfirm'] == 'on') {
    } else {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CAPTURE_CONFIRM_ERROR, 'error');
      $proceedToCapture = false;
    }
    if (isset($_POST['btndocapture']) && $_POST['btndocapture'] == MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_BUTTON_TEXT) {
      $captureAmt = (float)$_POST['captamt'];
/*
      if ($captureAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_INVALID_CAPTURE_AMOUNT, 'error');
        $proceedToCapture = false;
      }
*/
    }
    if (isset($_POST['captauthid']) && trim($_POST['captauthid']) != '') {
      // okay to proceed
    } else {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
      $proceedToCapture = false;
    }
    /**
     * Submit capture request to Authorize.net
     */
    if ($proceedToCapture) {
      // Populate an array that contains all of the data to be sent to Authorize.net
      unset($submit_data);
      $submit_data = array(
                           'x_type' => 'PRIOR_AUTH_CAPTURE',
                           'x_amount' => round($captureAmt, 2),
                           'x_trans_id' => strip_tags(trim($_POST['captauthid'])),
//                         'x_invoice_num' => $new_order_id,
//                         'x_po_num' => $order->info['po_number'],
//                         'x_freight' => $order->info['shipping_cost'],
//                         'x_tax_exempt' => 'FALSE', /* 'TRUE' or 'FALSE' */
//                         'x_tax' => $order->info['tax'],
                           );

      $response = $this->_sendRequest($submit_data);
      $response_code = $response[0];
      $response_text = $response[3];
      $response_alert = $response_text . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
      $this->reportable_submit_data['Note'] = $captureNote;
      $this->_debugActions($response);

      if ($response_code != '1' || ($response[0]==1 && $response[2] == 311) ) {
        $messageStack->add_session($response_alert, 'error');
      } else {
        // Success, so save the results
        $comments = 'FUNDS COLLECTED. Auth Code: ' . $response[4] . "\n" . 'Trans ID: ' . $response[6] . "\n" . ' Amount: ' . ($response[9] == 0.00 ? 'Full Amount' : $response[9]) . "\n" . 'Time: ' . date('Y-m-D h:i:s') . "\n" . $captureNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CAPT_INITIATED, ($response[9] == 0.00 ? 'Full Amount' : $response[9]), $response[6], $response[4]), 'success');
        return true;
      }
    }
    return false;
  }
  /**
   * Used to void a given previously-authorized transaction.
   */
  function _doVoid($oID, $note = '') {
    global $messageStack;

    $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_AIM_REFUNDED_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;
    $voidNote = strip_tags(zen_db_input($_POST['voidnote'] . $note));
    $voidAuthID = trim(strip_tags(zen_db_input($_POST['voidauthid'])));
    $proceedToVoid = true;
    if (isset($_POST['ordervoid']) && $_POST['ordervoid'] == MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_VOID_BUTTON_TEXT) {
      if (isset($_POST['voidconfirm']) && $_POST['voidconfirm'] != 'on') {
        $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_VOID_CONFIRM_ERROR, 'error');
        $proceedToVoid = false;
      }
    }
    if ($voidAuthID == '') {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
      $proceedToVoid = false;
    }
    // Populate an array that contains all of the data to be sent to gateway
    $submit_data = array('x_type' => 'VOID',
                         'x_trans_id' => trim($voidAuthID) );
    /**
     * Submit void request to Gateway
     */
    if ($proceedToVoid) {
      $response = $this->_sendRequest($submit_data);
      $response_code = $response[0];
      $response_text = $response[3];
      $response_alert = $response_text . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
      $this->reportable_submit_data['Note'] = $voidNote;
      $this->_debugActions($response);

      if ($response_code != '1' || ($response[0]==1 && $response[2] == 310) ) {
        $messageStack->add_session($response_alert, 'error');
      } else {
        // Success, so save the results
        $comments = 'VOIDED. Trans ID: ' . $response[6] . ' ' . $response[4] . "\n" . $voidNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_VOID_INITIATED, $response[6], $response[4]), 'success');
        return true;
      }
    }
    return false;
  }

}
