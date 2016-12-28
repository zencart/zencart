<?php
/**
 * authorize.net echeck payment method class
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Modified in v1.6.0 $
 */
/**
 * Authorize.net echeck Payment Module
 * You must have SSL active on your server to be compliant with merchant TOS
 *
 */
class authorizenet_echeck extends base {
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
  /**
   * debug content var
   */
  var $reportable_submit_data = array();
  /**
   * Constructor
   */
  function __construct() {
    global $order, $messageStack;
    $this->code = 'authorizenet_echeck';
    $this->enabled = ((MODULE_PAYMENT_AUTHORIZENET_ECHECK_STATUS == 'True') ? true : false); // Whether the module is installed or not
    if (IS_ADMIN_FLAG === true) {
      // Payment module title in Admin
      $this->title = MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_ADMIN_TITLE;
      if ($this->enabled) {
        if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_STATUS == 'True' && (MODULE_PAYMENT_AUTHORIZENET_ECHECK_LOGIN == 'testing' || MODULE_PAYMENT_AUTHORIZENET_ECHECK_TXNKEY == 'Test')) {
          $this->title .=  '<span class="alert"> (Not Configured)</span>';
        } elseif (MODULE_PAYMENT_AUTHORIZENET_ECHECK_TESTMODE == 'Test') {
          $this->title .= '<span class="alert"> (in Testing mode)</span>';
        }
        if ($this->enabled && !function_exists('curl_init')) $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_ERROR_CURL_NOT_FOUND, 'error');
        if (strlen(MODULE_PAYMENT_AUTHORIZENET_ECHECK_MD5HASH) > 20) $this->title .= '<span class="alert"> (NOTE: MD5 Hash key too long)</span>';
      }
    } else {
      $this->title = MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_CATALOG_TITLE; // Payment module title in Catalog
    }
    $this->description = MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_DESCRIPTION; // Descriptive Info about module in Admin
    $this->sort_order = MODULE_PAYMENT_AUTHORIZENET_ECHECK_SORT_ORDER; // Sort Order of this payment option on the customer payment page
    $this->form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false); // Page to go to upon submitting page info
    $this->order_status = (int)DEFAULT_ORDERS_STATUS_ID;
    if ((int)MODULE_PAYMENT_AUTHORIZENET_ECHECK_ORDER_STATUS_ID > 0) {
      $this->order_status = (int)MODULE_PAYMENT_AUTHORIZENET_ECHECK_ORDER_STATUS_ID;
    }

    $this->_logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;

    if (is_object($order)) $this->update_status();

    // verify table structure
    if (IS_ADMIN_FLAG === true) $this->tableCheckup();
  }
  /**
   * calculate zone matches and flag settings to determine whether this module should display to customers or not
   *
   */
  function update_status() {
    global $order, $db;
    if (IS_ADMIN_FLAG === false) {
      // if store is not running in SSL, cannot offer bank module, for PCI reasons
      if (!defined('ENABLE_SSL') || ENABLE_SSL != 'true') $this->enabled = FALSE;
    }
    // check other reasons for the module to be deactivated:
    if ($this->enabled && (int)MODULE_PAYMENT_AUTHORIZENET_ECHECK_ZONE > 0 && isset($order->billing['country']['id'])) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_AUTHORIZENET_ECHECK_ZONE . "' and zone_country_id = '" . (int)$order->billing['country']['id'] . "' order by zone_id");
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
    $js .= '    var echeck_custname = document.checkout_payment.authorizenet_echeck_bank_accountholder.value;' . "\n";
    $js .= '    var echeck_bank_aba = document.checkout_payment.authorizenet_echeck_bank_aba_code.value;' . "\n";
    $js .= '    var echeck_bank_acctnum = document.checkout_payment.authorizenet_echeck_bank_acct_num.value;' . "\n";
    $js .= '    var echeck_bank_name = document.checkout_payment.authorizenet_echeck_bank_name.value;' . "\n";
    $js .= '    if (echeck_custname == "" || echeck_custname.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_JS_ACCT_OWNER . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" .
    '    if (echeck_bank_aba == "" || echeck_bank_aba.length < ' . 6 . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_JS_ROUTING_CODE . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" .
    '    if (echeck_bank_acctnum == "" || echeck_bank_acctnum.length < ' . 6 . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_JS_ACCT_NUMBER . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" .
    '    if (echeck_bank_name == "" || echeck_bank_name.length < ' . 6 . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_JS_BANK_NAME . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n";


    if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_WFSS_ENABLED == 'True') {
      //MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_JS_CUST_TAX_ID
      //MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_JS_DL_NUMBER
      //MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_JS_DL_DOB
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
    $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';
    $bank_acct_types = array();
    $echeck_customer_types = array();

    $bank_acct_types[] = array('id' => 'CHECKING', 'text' => 'Checking');
    $bank_acct_types[] = array('id' => 'BUSINESSCHECKING', 'text' => 'Business Checking');
    $bank_acct_types[] = array('id' => 'SAVINGS', 'text' => 'Savings');

    $selection = array('id' => $this->code,
                       'module' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_CATALOG_TITLE,
                       'fields' => array(
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_ROUTING_CODE,
                                               'field' => zen_draw_input_field('authorizenet_echeck_bank_aba_code', '', 'maxlength="9" id="'.$this->code.'-echeck-routing-code"' . $onFocus . ' autocomplete="off"'),
                                               'tag' => $this->code.'-echeck-routing-code'),
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_ACCOUNT_NUM,
                                               'field' => zen_draw_input_field('authorizenet_echeck_bank_acct_num', '', 'maxlength="20" id="'.$this->code.'-echeck-bank-acct-num"'. $onFocus . ' autocomplete="off"'),
                                               'tag' => $this->code.'-echeck-bank-acct-num'),
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_NAME,
                                               'field' => zen_draw_input_field('authorizenet_echeck_bank_name', '', 'maxlength="50" id="'.$this->code.'-echeck-bank-name"' . $onFocus . ' autocomplete="off"'),
                                               'tag' => $this->code.'-echeck-bank-name'),
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_ACCOUNT_TYPE,
                                               'field' => zen_draw_pull_down_menu('authorizenet_echeck_bank_acct_type', $bank_acct_types, '', 'id="'.$this->code.'-echeck-bank-acct-type"' . $onFocus . ' autocomplete="off"'),
                                               'tag' => $this->code.'-echeck-bank-acct-type'),
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_ACCOUNTHOLDER,
                                               'field' => zen_draw_input_field('authorizenet_echeck_bank_accountholder', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'maxlength="100" id="'.$this->code.'-echeck-bank-acctholder"' . $onFocus . ' autocomplete="off"'),
                                               'tag' => $this->code.'-echeck-bank-acctholder')  ));

    if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_WFSS_ENABLED == 'True') {
      $echeck_customer_types[] = array('id' => 'I', 'text' => 'Individual');
      $echeck_customer_types[] = array('id' => 'B', 'text' => 'Business');
      $dl_states = array();
      global $db;
      $sql = "select zone_code, zone_name
                     from " . TABLE_ZONES . "
                     where zone_country_id = 223";
      $result = $db->Execute($sql);
      while (!$result->EOF) {
        $dl_states[] = array('id' => $result->fields['zone_code'], 'text' => $result->fields['zone_name']);
        $result->MoveNext();
      }
      $selection['fields'] = array_merge($selection['fields'], array(
                                     array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_CUST_TYPE,
                                           'field' => zen_draw_pull_down_menu('echeck_customer_type', $echeck_customer_types, '', 'id="'.$this->code.'-echeck-cust-type"' . $onFocus . ' autocomplete="off"'),
                                           'tag' => $this->code.'-echeck-cust-type'),
                                     array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_CUST_TAX_ID,
                                           'field' => zen_draw_input_field('echeck_customer_tax_id', '', 'maxlength="9" id="'.$this->code.'-echeck-tax-id"' . $onFocus . ' autocomplete="off"'),
                                           'tag' => $this->code.'-echeck-tax-id'),
                                     array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_DL_NUMBER,
                                           'field' => zen_draw_input_field('echeck_dl_num', '', 'maxlength="50" id="'.$this->code.'-echeck-dl-num"' . $onFocus . ' autocomplete="off"'),
                                           'tag' => $this->code.'-echeck-dl-num'),
                                     array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_DL_STATE,
                                           'field' => zen_draw_pull_down_menu('echeck_dl_state', $dl_states, '', 'id="'.$this->code.'-echeck-dl-state"' . $onFocus . ' autocomplete="off"'),
                                           'tag' => $this->code.'-echeck-dl-state'),
                                     array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_DL_DOB_TEXT,
                                           'field' => zen_draw_input_field('echeck_dl_dob', '', 'maxlength="11" id="'.$this->code.'-echeck-dl-dob"' . $onFocus . ' autocomplete="off"') . ' ' . MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_DL_DOB_FORMAT,
                                           'tag' => $this->code.'-echeck-dl-dob') ));
    }
    return $selection;
  }
  /**
   * Evaluates the collected data for acceptance and the validity of the type of data supplied
   *
   */
  function pre_confirmation_check() {
    return true;
  }
  /**
   * Display Account Information on the Checkout Confirmation Page
   *
   * @return array
   */
  function confirmation() {
    global $order;
    $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_NAME,
                                                  'field' => $_POST['authorizenet_echeck_bank_name']),
                                            array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_ROUTING_CODE,
                                                  'field' => $_POST['authorizenet_echeck_bank_aba_code']),
                                            array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_ACCOUNT_TYPE,
                                                  'field' => $_POST['authorizenet_echeck_bank_acct_type']),
                                            array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_BANK_ACCOUNT_NUM,
                                                  'field' => $_POST['authorizenet_echeck_bank_acct_num']),
                                            array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_AUTHORIZATION_TITLE,
                                                  'field' => sprintf(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_AUTHORIZATION_NOTICE, strtolower(zen_db_prepare_input($_POST['authorizenet_echeck_bank_acct_type'])), zen_date_short(date("Y-m-d")), $order->info['total']))
                                            ));
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
    $process_button_string = zen_draw_hidden_field('bank_aba_code', substr(zen_db_prepare_input($_POST['authorizenet_echeck_bank_aba_code']), 0, 9) ) .
                             zen_draw_hidden_field('bank_acct_num', substr(zen_db_prepare_input($_POST['authorizenet_echeck_bank_acct_num']), 0, 20) ) .
                             zen_draw_hidden_field('bank_acct_type', zen_db_prepare_input($_POST['authorizenet_echeck_bank_acct_type']) ) .
                             zen_draw_hidden_field('bank_name', substr(zen_db_prepare_input($_POST['authorizenet_echeck_bank_name']), 0, 50) ) .
                             zen_draw_hidden_field('bank_acct_name', substr(zen_db_prepare_input($_POST['authorizenet_echeck_bank_accountholder']), 0, 100) );

    if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_WFSS_ENABLED == 'True') {
      $process_button_string .= zen_draw_hidden_field('echeck_customer_type', substr(zen_db_prepare_input($_POST['echeck_customer_type']), 0, 10) );
      $process_button_string .= zen_draw_hidden_field('echeck_customer_tax_id', substr(zen_db_prepare_input($_POST['echeck_customer_tax_id']), 0, 9) );
      $process_button_string .= zen_draw_hidden_field('echeck_dl_num', substr(zen_db_prepare_input($_POST['echeck_dl_num']), 0, 50) );
      $process_button_string .= zen_draw_hidden_field('echeck_dl_state', substr(zen_db_prepare_input($_POST['echeck_dl_state']), 0, 2) );
      $process_button_string .= zen_draw_hidden_field('echeck_dl_dob', substr(zen_db_prepare_input($_POST['echeck_dl_dob']), 0, 16) );
    }

    $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

    return $process_button_string;
  }
  /**
   * Store the CC info to the order and process any results that come back from the payment gateway
   *
   */
  function before_process() {
    global $response, $db, $order, $messageStack;

    $order->info['cc_owner']    = zen_db_prepare_input($_POST['bank_acct_name']);
    $order->info['cc_type']    = 'eCheck';
    $order->info['cc_number'] = zen_db_prepare_input($_POST['bank_aba_code'] . '-' . str_pad(substr($_POST['bank_acct_num'], -4), strlen($_POST['bank_acct_num']), "X", STR_PAD_LEFT));
    $sessID = zen_session_id();

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

    // Calculate the next expected order id
    $last_order_id = $db->Execute("select orders_id from " . TABLE_ORDERS . " order by orders_id desc limit 1");
    $new_order_id = $last_order_id->fields['orders_id'];
    $new_order_id = ($new_order_id + 1);
    $new_order_id = (string)$new_order_id . '-' . zen_create_random_value(6, 'chars');

    // Populate an array that contains all of the data to be sent to Authorize.net
    $submit_data = array(
                         'x_login' => trim(MODULE_PAYMENT_AUTHORIZENET_ECHECK_LOGIN),
                         'x_tran_key' => trim(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TXNKEY),
                         'x_relay_response' => 'FALSE', // AIM uses direct response, not relay response
                         'x_delim_data' => 'TRUE',
                         'x_delim_char' => $this->delimiter,  // The default delimiter is a comma
                         'x_encap_char' => $this->encapChar,  // The divider to encapsulate response fields
                         'x_version' => '3.1',  // 3.1 is required to use CVV codes
                         'x_type' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_AUTHORIZATION_TYPE == 'Authorize' ? 'AUTH_ONLY': 'AUTH_CAPTURE',
                         'x_amount' => number_format($order->info['total'], 2),
                         'x_currency_code' => $order->info['currency'],
                         'x_method' => 'ECHECK',
                         'x_bank_aba_code' => $_POST['bank_aba_code'],
                         'x_bank_acct_num' => $_POST['bank_acct_num'],
                         'x_bank_acct_type' => $_POST['bank_acct_type'],
                         'x_bank_name' => $_POST['bank_name'],
                         'x_bank_acct_name' => $_POST['bank_acct_name'],
                         'x_echeck_type' => 'WEB',
                         'x_recurring_billing' => 'NO',
                         'x_email_customer' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_EMAIL_CUSTOMER == 'True' ? 'TRUE': 'FALSE',
                         'x_email_merchant' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_EMAIL_MERCHANT == 'True' ? 'TRUE': 'FALSE',
                         'x_cust_id' => $_SESSION['customer_id'],
                         'x_invoice_num' => (MODULE_PAYMENT_AUTHORIZENET_ECHECK_TESTMODE == 'Test' ? 'TEST-' : '') . $new_order_id,
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
                         'x_customer_ip' => zen_get_ip_address(),
                         'x_po_num' => date('M-d-Y h:i:s'), //$order->info['po_number'],
                         'x_freight' => number_format((float)$order->info['shipping_cost'],2),
                         'x_tax_exempt' => 'FALSE', /* 'TRUE' or 'FALSE' */
                         'x_tax' => number_format((float)$order->info['tax'],2),
                         'x_duty' => '0',

                         // Additional Merchant-defined variables go here
                         'Date' => $order_time,
                         'IP' => zen_get_ip_address(),
                         'Session' => $sessID );
    // process Wells-Fargo-SecureSource-specific parameters
    if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_WFSS_ENABLED == 'True') {
      $submit_data['x_customer_organization_type'] = zen_db_prepare_input($_POST['echeck_customer_type']);
      if (zen_db_prepare_input($_POST['echeck_customer_tax_id']) != '') {
        $submit_data['x_customer_tax_id'] = zen_db_prepare_input($_POST['echeck_customer_tax_id']);
      } else {
        $submit_data = array_merge($submit_data,
                   array('x_drivers_license_num' => zen_db_prepare_input($_POST['echeck_dl_num']),
                         'x_drivers_license_state' => zen_db_prepare_input($_POST['echeck_dl_state']),
                         'x_drivers_license_dob' => zen_db_prepare_input($_POST['echeck_dl_dob'])  ));
      }
    }

    // force conversion to USD
    if ($order->info['currency'] != 'USD') {
      global $currencies;
      $submit_data['x_amount'] = number_format($order->info['total'] * $currencies->get_value('USD'), 2);
      $submit_data['x_currency_code'] = 'USD';
      unset($submit_data['x_tax'], $submit_data['x_freight']);
    }

    unset($response);
    $response = $this->_sendRequest($submit_data);
    $response_code = $response[0];
    $response_text = $response[3];
    $this->auth_code = $response[4];
    $this->transaction_id = $response[6];
    $response_msg_to_customer = $response_text . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');

    $response['Expected-MD5-Hash'] = $this->calc_md5_response($response[6], $response[9]);
    $response['HashMatchStatus'] = ($response[37] == $response['Expected-MD5-Hash']) ? 'PASS' : 'FAIL';

    $this->_debugActions($response, $order_time, $sessID);

    // If the MD5 hash doesn't match, then this transaction's authenticity cannot be verified.
    // Thus, order will be placed in Pending status
    if ($response['HashMatchStatus'] != 'PASS' && defined('MODULE_PAYMENT_AUTHORIZENET_ECHECK_MD5HASH') && MODULE_PAYMENT_AUTHORIZENET_ECHECK_MD5HASH != '') {
      $this->order_status = 1;
      $messageStack->add_session('header', MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_AUTHENTICITY_WARNING, 'caution');
    }

    // If the response code is not 1 (approved) then redirect back to the payment page with the appropriate error message
    if ($response_code != '1') {
      $messageStack->add_session('checkout_payment', $response_msg_to_customer . ' - ' . MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_DECLINED_MESSAGE, 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }
  }
  /**
   * Post-process activities. Updates the order-status history data with the auth code from the transaction.
   *
   * @return boolean
   */
  function after_process() {
    global $insert_id, $db;
    $sql = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, date_added) values (:orderComments, :orderID, :orderStatus, now() )";
    $sql = $db->bindVars($sql, ':orderComments', 'eCheck payment.  AUTH: ' . $this->auth_code . '. TransID: ' . $this->transaction_id . '.', 'string');
    $sql = $db->bindVars($sql, ':orderID', $insert_id, 'integer');
    $sql = $db->bindVars($sql, ':orderStatus', $this->order_status, 'integer');
    $db->Execute($sql);
    return false;
  }
  /**
    * Build admin-page components
    *
    * @param int $zf_order_id
    * @return string
    */
  function RENAME_admin_notification($zf_order_id) {
    global $db;
    $output = '';
    $echeckdata->fields = array();
    require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/authorizenet/authorizenet_admin_notification.php');
    return $output;
  }
  /**
   * Used to display error message details
   *
   * @return array
   */
  function get_error() {
    $error = array('title' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_ERROR,
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
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }
  /**
   * Install the payment module and its configuration settings
   *
   */
  function install() {
    global $db, $messageStack;
    if (defined('MODULE_PAYMENT_AUTHORIZENET_ECHECK_STATUS')) {
      $messageStack->add_session('Authorize.net (eCheck) module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=authorizenet_echeck', 'NONSSL'));
      return 'failed';
    }
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Authorize.net (eCheck) Module', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_STATUS', 'True', 'Do you want to accept eCheck payments via Authorize.net?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Login ID', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_LOGIN', 'testing', 'The API Login ID used for the Authorize.net service', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_TXNKEY', 'Test', 'Transaction Key used for encrypting TP data<br />(See your Authorizenet Account->Security Settings->API Login ID and Transaction Key for details.)', '6', '0', now(), 'zen_cfg_password_display')");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('MD5 Hash', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_MD5HASH', '*Set A Hash Value at AuthNet Admin*', 'Encryption key used for validating received transaction data (MAX 20 CHARACTERS, exactly as you entered in Authorize.net account settings). Or leave blank.', '6', '0', now(), 'zen_cfg_password_display')");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_TESTMODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'zen_cfg_select_option(array(\'Test\', \'Production\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Authorization Type', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_AUTHORIZATION_TYPE', 'Authorize', 'Do you want submitted credit card transactions to be authorized only, or authorized and captured?', '6', '0', 'zen_cfg_select_option(array(\'Authorize\', \'Authorize+Capture\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Database Storage', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_STORE_DATA', 'True', 'Do you want to save the gateway communications data to the database?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Customer Notifications', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_EMAIL_CUSTOMER', 'False', 'Should Authorize.Net email a receipt to the customer?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Merchant Notifications', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_EMAIL_MERCHANT', 'False', 'Should Authorize.Net email a receipt to the merchant?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_ORDER_STATUS_ID', '1', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Debug Mode', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING', 'Off', 'Would you like to enable debug mode?  A complete detailed log of failed transactions may be emailed to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Log File\', \'Log and Email\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Wells Fargo SecureSource Merchant', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_WFSS_ENABLED', 'False', 'Are you a Wells Fargo SecureSource merchant?  eCheck transactions will collect additional information from customers. Set to True only if your account has been configured to use Wells Fargo SecureSource.', '6', '0', 'zen_cfg_select_option(array(\'False\', \'True\'), ', now())");
  }
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }

  /**
   * Test whether the module is able to communicate with the gateway
   * @return multitype:string
   */
  function testCommunications() {
    $retVal = array();
    $result = $this->_sendRequest(array(), 'testcomm');
//  die('result=<pre>'.var_export($result, true));
    if ($result == TRUE) {
      $retVal['type'] = 'success';
      $retVal['text'] = 'Communications Test Successful: ' . $this->code;
    } else {
      $retVal['type'] = 'error';
      $retVal['text'] = 'Communications Test FAILED: ' . $this->code;
    }
    return $retVal;
  }

  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_PAYMENT_AUTHORIZENET_ECHECK_STATUS', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_LOGIN', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_TXNKEY', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_MD5HASH', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_TESTMODE', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_AUTHORIZATION_TYPE', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_STORE_DATA', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_EMAIL_CUSTOMER', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_EMAIL_MERCHANT', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_SORT_ORDER', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_ZONE', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_ORDER_STATUS_ID', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_WFSS_ENABLED', 'MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING'); //'MODULE_PAYMENT_AUTHORIZENET_ECHECK_METHOD'
  }
  /**
   * Send communication request
   */
  function _sendRequest($submit_data, $mode = 'normal') {
    // Populate an array that contains all of the data to be sent to Authorize.net
    $submit_data = array_merge(array(
                         'x_login' => trim(MODULE_PAYMENT_AUTHORIZENET_ECHECK_LOGIN),
                         'x_tran_key' => trim(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TXNKEY),
                         'x_relay_response' => 'FALSE',
                         'x_delim_data' => 'TRUE',
                         'x_delim_char' => $this->delimiter,  // The default delimiter is a comma
                         'x_encap_char' => $this->encapChar,  // The divider to encapsulate response fields
                         'x_version' => '3.1',  // 3.1 is required to use CVV codes
                         ), $submit_data);

    if(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TESTMODE == 'Test') {
      $submit_data['x_test_request'] = 'TRUE';
    }

    // set URL
    $url = 'https://secure2.authorize.net/gateway/transact.dll';
    $devurl = 'https://test.authorize.net/gateway/transact.dll';
    $dumpurl = 'https://developer.authorize.net/param_dump.asp';
    $certurl = 'https://certification.authorize.net/gateway/transact.dll';
    if (defined('AUTHORIZENET_DEVELOPER_MODE')) {
      if (AUTHORIZENET_DEVELOPER_MODE == 'on') $url = $devurl;
      if (AUTHORIZENET_DEVELOPER_MODE == 'echo' || MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING == 'echo') $url = $dumpurl;
      if (AUTHORIZENET_DEVELOPER_MODE == 'certify') $url = $certurl;
    }
    if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING == 'echo') $url = $dumpurl;

    // concatenate the submission data into $data variable after sanitizing to protect delimiters
    $data = '';
    while(list($key, $value) = each($submit_data)) {
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
      trigger_error('ALERT: Could not process Authorize.net echeck transaction via normal CURL communications. Your server is encountering connection problems using TLS 1.2 ... because your hosting company cannot autonegotiate a secure protocol with modern security protocols. We will try the transaction again, but this is resulting in a very long delay for your customers, and could result in them attempting duplicate purchases. Get your hosting company to update their TLS capabilities ASAP.', E_USER_NOTICE);
      curl_setopt($ch, CURLOPT_SSLVERSION, 6); // Using the defined value of 6 instead of CURL_SSLVERSION_TLSv1_2 since these outdated hosts also don't properly implement this constant either.
      $this->authorize = curl_exec($ch);
      $this->commError = curl_error($ch);
      $this->commErrNo = curl_errno($ch);
    }

    $this->commInfo = @curl_getinfo($ch);
    curl_close ($ch);

    // handle "communications test only" mode:
    if ($mode == 'testcomm') {
      return ($this->commInfo['http_code'] == 200);
    }

    // if in 'echo' mode, dump the returned data to the browser and stop execution
    if ((defined('AUTHORIZENET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'echo') || MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING == 'echo') {
      echo $this->authorize . ($this->commErrNo != 0 ? '<br />' . $this->commErrNo . ' ' . $this->commError : '') . '<br />';
      die('Press the BACK button in your browser to return to the previous page.');
    }

    // parse the data received back from the gateway, taking into account the delimiters and encapsulation characters
    $stringToParse = $this->authorize;
    if (substr($stringToParse,0,1) == $this->encapChar) $stringToParse = substr($stringToParse,1);
    $stringToParse = preg_replace('/.{*}' . $this->encapChar . '$/', '', $stringToParse);
    $response = explode($this->encapChar . $this->delimiter . $this->encapChar, $stringToParse);

    return $response;
  }
  /**
   * Calculate validity of response
   */
  function calc_md5_response($trans_id = '', $amount = '') {
    if ($amount == '' || $amount == '0') $amount = '0.00';
    $validating = md5(MODULE_PAYMENT_AUTHORIZENET_ECHECK_MD5HASH . MODULE_PAYMENT_AUTHORIZENET_ECHECK_LOGIN . $trans_id . $amount);
    return strtoupper($validating);
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

      if (strstr(MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING, 'Log') || strstr(MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING, 'All') || (defined('AUTHORIZENET_DEVELOPER_MODE') && in_array(AUTHORIZENET_DEVELOPER_MODE, array('on', 'certify')))) {
        $key = $response[6] . '_' . time() . '_' . zen_create_random_value(4);
        $file = $this->_logDir . '/' . 'AuthNetECheck_Debug_' . $key . '.log';
        if ($fp = @fopen($file, 'a')) {
          fwrite($fp, $errorMessage);
          fclose($fp);
        }
     }
      if (($response[0] != '1' && stristr(MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING, 'Alerts')) || strstr(MODULE_PAYMENT_AUTHORIZENET_ECHECK_DEBUGGING, 'Email')) {
        zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Authorizenet-eCheck Alert ' . $response[7] . ' ' . date('M-d-Y h:i:s') . ' ' . $response[6], $errorMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorMessage)), 'debug');
      }

    // DATABASE SECTION
    // Insert the send and receive response data into the database.
    // This can be used for testing or for implementation in other applications
    // This can be turned on and off if the Admin Section
    if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_STORE_DATA == 'True'){
      $db_response_text = $response[3] . ($this->commError !='' ? ' - Comm results: ' . $this->commErrNo . ' ' . $this->commError : '');
      $db_response_text .= ($response[0] == 2 && $response[2] == 4) ? ' NOTICE: Card should be picked up - possibly stolen ' : '';
      $db_response_text .= ($response[0] == 3 && $response[2] == 11) ? ' DUPLICATE TRANSACTION ATTEMPT ' : '';

      // Insert the data into the database
      $sql = "insert into " . TABLE_AUTHORIZENET . "  (id, customer_id, order_id, response_code, response_text, authorization_type, transaction_id, sent, received, time, session_id) values (NULL, :custID, :orderID, :respCode, :respText, :authType, :transID, :sentData, :recvData, :orderTime, :sessID )";
      $sql = $db->bindVars($sql, ':custID', $_SESSION['customer_id'], 'integer');
      $sql = $db->bindVars($sql, ':orderID', preg_replace('/[^0-9]/', '', $response[7]), 'integer');
      $sql = $db->bindVars($sql, ':respCode', $response[0], 'integer');
      $sql = $db->bindVars($sql, ':respText', $db_response_text, 'string');
      $sql = $db->bindVars($sql, ':authType', $response[11], 'string');
      if (trim($this->transaction_id) != '') {
        $sql = $db->bindVars($sql, ':transID', $this->transaction_id, 'string');
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
    $fieldOkay1 = (method_exists($sniffer, 'field_type')) ? $sniffer->field_type(TABLE_AUTHORIZENET, 'transaction_id', 'varchar(32)', true) : -1;
    if ($fieldOkay1 !== true) {
      $db->Execute("ALTER TABLE " . TABLE_AUTHORIZENET . " CHANGE transaction_id transaction_id varchar(32) default NULL");
    }
  }
  /**
   * Used to submit a refund for a given transaction.
   */
  function _doRefund($oID, $amount = 0) {
    global $db, $messageStack;
    $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_ECHECK_REFUNDED_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;
    $proceedToRefund = true;
    $refundNote = strip_tags(zen_db_input($_POST['refnote']));
    if (isset($_POST['refconfirm']) && $_POST['refconfirm'] != 'on') {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_REFUND_CONFIRM_ERROR, 'error');
      $proceedToRefund = false;
    }
    if (isset($_POST['buttonrefund']) && $_POST['buttonrefund'] == MODULE_PAYMENT_AUTHORIZENET_ECHECK_ENTRY_REFUND_BUTTON_TEXT) {
      $refundAmt = (float)$_POST['refamt'];
      $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_ECHECK_REFUNDED_ORDER_STATUS_ID;
      if ($refundAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_INVALID_REFUND_AMOUNT, 'error');
        $proceedToRefund = false;
      }
    }
    if (isset($_POST['cc_number']) && trim($_POST['cc_number']) == '') {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_CC_NUM_REQUIRED_ERROR, 'error');
    }
    if (isset($_POST['trans_id']) && trim($_POST['trans_id']) == '') {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
      $proceedToRefund = false;
    }

    /**
     * Submit refund request to gateway
     */
    if ($proceedToRefund) {
      $submit_data = array('x_type' => 'CREDIT',
                           'x_card_num' => trim($_POST['cc_number']),
                           'x_amount' => number_format($refundAmt, 2),
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
        $sql_data_array = array('orders_id' => $oID,
                                'orders_status_id' => (int)$new_order_status,
                                'date_added' => 'now()',
                                'comments' => 'REFUND INITIATED. Trans ID:' . $response[6] . ' ' . $response[4]. "\n" . ' Gross Refund Amt: ' . $response[9] . "\n" . $refundNote,
                                'customer_notified' => 0
                             );
        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $db->Execute("update " . TABLE_ORDERS  . "
                      set orders_status = '" . (int)$new_order_status . "'
                      where orders_id = '" . (int)$oID . "'");
        $messageStack->add_session(sprintf(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_REFUND_INITIATED, $response[9], $response[6]), 'success');
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

    //@TODO: Read current order status and determine best status to set this to
    $new_order_status = MODULE_PAYMENT_AUTHORIZENET_ECHECK_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;

    $proceedToCapture = true;
    $captureNote = strip_tags(zen_db_input($_POST['captnote']));
    if (isset($_POST['captconfirm']) && $_POST['captconfirm'] == 'on') {
    } else {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_CAPTURE_CONFIRM_ERROR, 'error');
      $proceedToCapture = false;
    }
    if (isset($_POST['btndocapture']) && $_POST['btndocapture'] == MODULE_PAYMENT_AUTHORIZENET_ECHECK_ENTRY_CAPTURE_BUTTON_TEXT) {
      $captureAmt = (float)$_POST['captamt'];
/*
      if ($captureAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_INVALID_CAPTURE_AMOUNT, 'error');
        $proceedToCapture = false;
      }
*/
    }
    if (isset($_POST['captauthid']) && trim($_POST['captauthid']) != '') {
      // okay to proceed
    } else {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
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
                           'x_amount' => number_format($captureAmt, 2),
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
        $sql_data_array = array('orders_id' => (int)$oID,
                                'orders_status_id' => (int)$new_order_status,
                                'date_added' => 'now()',
                                'comments' => 'FUNDS COLLECTED. Auth Code: ' . $response[4] . "\n" . 'Trans ID: ' . $response[6] . "\n" . ' Amount: ' . ($response[9] == 0.00 ? 'Full Amount' : $response[9]) . "\n" . 'Time: ' . date('Y-m-D h:i:s') . "\n" . $captureNote,
                                'customer_notified' => 0
                             );
        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $db->Execute("update " . TABLE_ORDERS  . "
                      set orders_status = '" . (int)$new_order_status . "'
                      where orders_id = '" . (int)$oID . "'");
        $messageStack->add_session(sprintf(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_CAPT_INITIATED, ($response[9] == 0.00 ? 'Full Amount' : $response[9]), $response[6], $response[4]), 'success');
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

    $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_ECHECK_REFUNDED_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;
    $voidNote = strip_tags(zen_db_input($_POST['voidnote'] . $note));
    $voidAuthID = trim(strip_tags(zen_db_input($_POST['voidauthid'])));
    $proceedToVoid = true;
    if (isset($_POST['ordervoid']) && $_POST['ordervoid'] == MODULE_PAYMENT_AUTHORIZENET_ECHECK_ENTRY_VOID_BUTTON_TEXT) {
      if (isset($_POST['voidconfirm']) && $_POST['voidconfirm'] != 'on') {
        $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_VOID_CONFIRM_ERROR, 'error');
        $proceedToVoid = false;
      }
    }
    if ($voidAuthID == '') {
      $messageStack->add_session(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
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
        $sql_data_array = array('orders_id' => (int)$oID,
                                'orders_status_id' => (int)$new_order_status,
                                'date_added' => 'now()',
                                'comments' => 'VOIDED. Trans ID: ' . $response[6] . ' ' . $response[4] . "\n" . $voidNote,
                                'customer_notified' => 0
                             );
        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $db->Execute("update " . TABLE_ORDERS  . "
                      set orders_status = '" . (int)$new_order_status . "'
                      where orders_id = '" . (int)$oID . "'");
        $messageStack->add_session(sprintf(MODULE_PAYMENT_AUTHORIZENET_ECHECK_TEXT_VOID_INITIATED, $response[6], $response[4]), 'success');
        return true;
      }
    }
    return false;
  }

}
