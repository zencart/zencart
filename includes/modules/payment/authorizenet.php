<?php
/**
 * authorize.net SIM payment method class
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
/**
 * authorize.net SIM payment method class
 * Ref: https://www.authorize.net/content/dam/authorize/documents/SIM_guide.pdf
 */
class authorizenet extends base {
  /**
   * $code determines the internal 'code' name used to designate "this" payment module
   *
   * @var string
   */
  var $code = 'authorizenet'; // SIM
  /**
   * Internal module version string
   * @var string
   */
  var $version = '2019-02-26';
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
  var $enabled = false;
  /**
   * log file folder
   *
   * @var string
   */
  var $_logDir = '';
  /**
   * vars
   */
  var $gateway_mode;
  var $reportable_submit_data;
  var $authorize;
  var $auth_code;
  var $transaction_id;
  var $order_status;
  /**
   * @var string the currency enabled in this gateway's merchant account
   */
  private $gateway_currency;
  /**
   * What order this module displays in relation to other enabled modules
   * @var int $sort_order
   */
  var $sort_order = 0;


  /**
   * Constructor
   */
  function __construct() {
    global $order;

    $this->title = MODULE_PAYMENT_AUTHORIZENET_TEXT_CATALOG_TITLE; // Payment module title in Catalog
    $this->description = MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION;

    if (IS_ADMIN_FLAG === true) {
      $this->title = MODULE_PAYMENT_AUTHORIZENET_TEXT_ADMIN_TITLE; // Payment module title in Admin
    }

    $this->sort_order = defined('MODULE_PAYMENT_AUTHORIZENET_SORT_ORDER') ? MODULE_PAYMENT_AUTHORIZENET_SORT_ORDER : null;
    $this->enabled = defined('MODULE_PAYMENT_AUTHORIZENET_STATUS') && MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True' && $this->hashingMode() !== false;

    if (null === $this->sort_order) return;

    if (IS_ADMIN_FLAG === true) {
      if (MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True'
          && (MODULE_PAYMENT_AUTHORIZENET_LOGIN == 'testing'
              || MODULE_PAYMENT_AUTHORIZENET_TXNKEY == 'Test'
              || $this->hashingMode() === false
          )) {
        $this->title .=  '<span class="alert"> (Not Configured)</span>';
      } elseif (MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Test') {
        $this->title .= '<span class="alert"> (in Testing mode)</span>';
      } elseif (MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Sandbox') {
        $this->title .= '<span class="alert"> (in Sandbox Developer mode)</span>';
      }
    }

    if (defined('MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID;

      // Reset order status to pending if capture pending:
      if (MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE == 'Authorize') $this->order_status = 1;
    }

    if (is_object($order)) $this->update_status();

    $this->form_action_url = 'https://secure2.authorize.net/gateway/transact.dll';
    if (MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Sandbox') $this->form_action_url = 'https://test.authorize.net/gateway/transact.dll';

//     $this->form_action_url = 'https://www.eprocessingnetwork.com/cgi-bin/an/order.pl';

    if ((defined('AUTHORIZNET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'echo') || MODULE_PAYMENT_AUTHORIZENET_DEBUGGING == 'echo') $this->form_action_url = 'https://developer.authorize.net/param_dump.asp';
    if (defined('AUTHORIZNET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'certify') $this->form_action_url = 'https://certification.authorize.net/gateway/transact.dll';

    $this->gateway_mode = MODULE_PAYMENT_AUTHORIZENET_GATEWAY_MODE;

    $this->_logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;

    // verify table structure
    if (IS_ADMIN_FLAG === true) $this->tableCheckup();

    // set the currency for the gateway (others will be converted to this one before submission)
    $this->gateway_currency = MODULE_PAYMENT_AUTHORIZENET_CURRENCY;
  }

  /**
   * Calculate zone matches and flag settings to determine whether this module should display to customers or not
   */
  function update_status() {
    global $order, $db;

    if ($this->enabled && (int)MODULE_PAYMENT_AUTHORIZENET_ZONE > 0 && isset($order->billing['country']['id'])) {
      $check_flag = false;
      $check = $db->Execute("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_AUTHORIZENET_ZONE . "' AND zone_country_id = '" . (int)$order->billing['country']['id'] . "' ORDER BY zone_id");
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
   * (Number, Owner Lengths)
   *
   * @return string
   */
  function javascript_validation() {
    if ($this->gateway_mode == 'offsite') return '';
    $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
          '    var cc_owner = document.checkout_payment.authorizenet_cc_owner.value;' . "\n" .
          '    var cc_number = document.checkout_payment.authorizenet_cc_number.value;' . "\n";
    if (MODULE_PAYMENT_AUTHORIZENET_USE_CVV == 'True')  {
      $js .= '    var cc_cvv = document.checkout_payment.authorizenet_cc_cvv.value;' . "\n";
    }
    $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
          '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_OWNER . '";' . "\n" .
          '      error = 1;' . "\n" .
          '    }' . "\n" .
          '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
          '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_NUMBER . '";' . "\n" .
          '      error = 1;' . "\n" .
          '    }' . "\n";
    if (MODULE_PAYMENT_AUTHORIZENET_USE_CVV == 'True')  {
      $js .= '    if (cc_cvv == "" || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n".
      '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_CVV . '";' . "\n" .
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
    for ($i=$today['year']; $i < $today['year']+10; $i++) {
      $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }

    $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

    if ($this->gateway_mode == 'offsite') {
      $selection = array('id' => $this->code,
                         'module' => $this->title);
    } else {
      $selection = array('id' => $this->code,
                         'module' => $this->title,
                         'fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER,
                                                 'field' => zen_draw_input_field('authorizenet_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="'.$this->code.'-cc-owner"' . $onFocus . ' autocomplete="off"'),
                                                 'tag' => $this->code.'-cc-owner'),
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER,
                                               'field' => zen_draw_input_field('authorizenet_cc_number', '', 'id="'.$this->code.'-cc-number"' . $onFocus . ' autocomplete="off"'),
                                               'tag' => $this->code.'-cc-number'),
                                         array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES,
                                               'field' => zen_draw_pull_down_menu('authorizenet_cc_expires_month', $expires_month, strftime('%m'), 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('authorizenet_cc_expires_year', $expires_year, '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
                                               'tag' => $this->code.'-cc-expires-month')));
      if (MODULE_PAYMENT_AUTHORIZENET_USE_CVV == 'True') {
        $selection['fields'][] = array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CVV,
                                       'field' => zen_draw_input_field('authorizenet_cc_cvv', '', 'size="4" maxlength="4"' . ' id="'.$this->code.'-cc-cvv"' . $onFocus . ' autocomplete="off"') . ' ' . '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . MODULE_PAYMENT_AUTHORIZENET_TEXT_POPUP_CVV_LINK . '</a>',
                                       'tag' => $this->code.'-cc-cvv');
      }
    }
    return $selection;
  }
  /**
   * Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   *
   */
  function pre_confirmation_check() {
    global $messageStack;
    if (isset($_POST['authorizenet_cc_number'])) {
      include(DIR_WS_CLASSES . 'cc_validation.php');

      $cc_validation = new cc_validation();
      $result = $cc_validation->validate($_POST['authorizenet_cc_number'], $_POST['authorizenet_cc_expires_month'], $_POST['authorizenet_cc_expires_year']);
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
  }
  /**
   * Display Credit Card Information on the Checkout Confirmation Page
   *
   * @return array
   */
  function confirmation() {
    if (isset($_POST['authorizenet_cc_number'])) {
      $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                            'fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['authorizenet_cc_owner']),
                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['authorizenet_cc_expires_month'], 1, '20' . $_POST['authorizenet_cc_expires_year'])))));
    } else {
      $confirmation = array(); //array('title' => $this->title);
    }
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
    global $order;

    $sequence = rand(1, 1000);
    $submit_data_core = array(
      'x_login' => MODULE_PAYMENT_AUTHORIZENET_LOGIN,
      'x_amount' => round($order->info['total'], 2),
      'x_currency_code' => $_SESSION['currency'],
      'x_method' => ((MODULE_PAYMENT_AUTHORIZENET_METHOD == 'Credit Card') ? 'CC' : 'ECHECK'),
      'x_type' => MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE == 'Authorize' ? 'AUTH_ONLY': 'AUTH_CAPTURE',
      'x_cust_ID' => $_SESSION['customer_id'],
      'x_email_customer' => ((MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER == 'True') ? 'TRUE': 'FALSE'),
      'x_company' => $order->billing['company'],
      'x_first_name' => $order->billing['firstname'],
      'x_last_name' => $order->billing['lastname'],
      'x_address' => $order->billing['street_address'],
      'x_city' => $order->billing['city'],
      'x_state' => $order->billing['state'],
      'x_zip' => $order->billing['postcode'],
      'x_country' => $order->billing['country']['title'],
      'x_phone' => $order->customer['telephone'],
      'x_fax' => $order->customer['fax'],
      'x_email' => $order->customer['email_address'],
      'x_ship_to_company' => $order->delivery['company'],
      'x_ship_to_first_name' => $order->delivery['firstname'],
      'x_ship_to_last_name' => $order->delivery['lastname'],
      'x_ship_to_address' => $order->delivery['street_address'],
      'x_ship_to_city' => $order->delivery['city'],
      'x_ship_to_state' => $order->delivery['state'],
      'x_ship_to_zip' => $order->delivery['postcode'],
      'x_ship_to_country' => $order->delivery['country']['title'],
      'x_solution_id' => 'A1000003', // used by authorize.net
      'x_version' => '3.1',
      'x_Customer_IP' => zen_get_ip_address(),
      'x_relay_response' => 'TRUE',
      'x_relay_URL' => zen_href_link(FILENAME_CHECKOUT_PROCESS, 'action=confirm', 'SSL', true, false),
      'x_invoice_num' => '',
      'x_duplicate_window' => '120',
      'x_allow_partial_Auth' => 'FALSE', // unable to accept partial authorizations at this time
      'x_description' => 'Website Purchase from ' . str_replace('"',"'", STORE_NAME),
    );

    // force conversion to supported currencies: USD, GBP, CAD, EUR, AUD, NZD
    if ($order->info['currency'] != $this->gateway_currency) {
      global $currencies;
      $submit_data_core['x_amount'] = round($order->info['total'] * $currencies->get_value($this->gateway_currency), 2);
      $submit_data_core['x_currency_code'] = $this->gateway_currency;
      $submit_data_core['x_description'] .= ' (Converted from: ' . number_format($order->info['total'] * $order->info['currency_value'], 2) . ' ' . $order->info['currency'] . ')';
    }

    $this->submit_extras = array();
    $this->notify('NOTIFY_PAYMENT_AUTHNETSIM_PRESUBMIT_HOOK');
    unset($this->submit_extras['x_login']);
    if (sizeof($this->submit_extras)) $submit_data_core = array_merge($submit_data_core, $this->submit_extras);

    $submit_data_security = $this->getHmacFingerprintFields($submit_data_core['x_amount'], $sequence, $submit_data_core['x_currency_code']);

    $submit_data_offline = array(
      'x_show_form' => 'PAYMENT_FORM',
      'x_receipt_link_method' => 'POST',
      'x_receipt_link_text' => 'Click here to complete your order.',
      'x_receipt_link_url' => zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false)
       );

//The following can (and SHOULD) be set in the authnet account admin area instead of here
    $submit_data_extras = array(
//      'x_header_email_receipt' => '',
//      'x_footer_email_receipt' => '',
//      'x_header_html_payment_form' => '',
//      'x_footer_html_payment_form' => '',
//      'x_header_html_receipt' => '',
//      'x_footer_html_receipt' => '',
        'x_logo_url' => (defined('MODULE_PAYMENT_AUTHORIZENET_LOGO_URL') ? MODULE_PAYMENT_AUTHORIZENET_LOGO_URL : ''),
//      'x_background_url' => '',
//      'x_color_link' => '',
//      'x_color_background' => '',
//      'x_color_text' => ''
        );

    $submit_data_onsite = array(
      'x_card_num' => $this->cc_card_number,
      'x_exp_date' => $this->cc_expiry_month . substr($this->cc_expiry_year, -2));

    if (MODULE_PAYMENT_AUTHORIZENET_USE_CVV == 'True') {
      if ($this->gateway_mode == 'onsite') $submit_data_onsite['x_card_code'] = $_POST['authorizenet_cc_cvv'];
    }

    if ($this->gateway_mode == 'onsite') {
      $submit_data = array_merge($submit_data_core, $submit_data_security, $submit_data_onsite);
    } else {
      $submit_data = array_merge($submit_data_core, $submit_data_security, $submit_data_offline, $submit_data_extras);
    }
    if (MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Test') $submit_data['x_Test_Request'] = 'TRUE';
    $submit_data[zen_session_name()] = zen_session_id();

    $process_button_string = "\n";
    foreach($submit_data as $key => $value) {
      $process_button_string .= zen_draw_hidden_field($key, $value) . "\n";
    }

    // prepare a copy of submitted data for error-reporting purposes
    $this->reportable_submit_data = $submit_data;
    $this->reportable_submit_data['x_login'] = '*******';
    if (isset($this->reportable_submit_data['x_tran_key'])) $this->reportable_submit_data['x_tran_key'] = '*******';
    if (isset($this->reportable_submit_data['x_card_num'])) $this->reportable_submit_data['x_card_num'] = str_repeat('X', strlen($this->reportable_submit_data['x_card_num'] - 4)) . substr($this->reportable_submit_data['x_card_num'], -4);
    if (isset($this->reportable_submit_data['x_exp_date'])) $this->reportable_submit_data['x_exp_date'] = '****';
    if (isset($this->reportable_submit_data['x_card_code'])) $this->reportable_submit_data['x_card_code'] = '*******';

    $this->_debugActions($this->reportable_submit_data, 'Submit-Data', '', zen_session_id());

    return $process_button_string;
  }

  /**
   * Store the CC info to the order and process any results that come back from the payment gateway
   *
   */
  function before_process() {
    global $messageStack, $order;
    $this->authorize = $_POST;
    unset($this->authorize['btn_submit_x'], $this->authorize['btn_submit_y']);
    $this->authorize['HashValidationValue'] = $this->getHashForResponseData($this->authorize);
    $this->authorize['HashMatchStatus'] = hash_equals($this->authorize['x_SHA2_Hash'], $this->authorize['HashValidationValue']) ? 'PASS' : 'FAIL';

    $this->notify('NOTIFY_PAYMENT_AUTHNETSIM_POSTSUBMIT_HOOK', $this->authorize);
    $this->_debugActions($this->authorize, 'Response-Data', '', zen_session_id());

    // if in 'echo' mode, dump the returned data to the browser and stop execution
    if ((defined('AUTHORIZENET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'echo') || MODULE_PAYMENT_AUTHORIZENET_DEBUGGING == 'echo') {
      echo 'Returned Response Codes:<br /><pre>' . print_r($_POST, true) . '</pre><br />';
      die('Press the BACK button in your browser to return to the previous page.');
    }

    if ($this->authorize['x_response_code'] == '1'
//       && $this->authorize['x_type'] == $this->submit_data['x_type']
//       && $this->authorize['x_method'] == $this->submit_data['x_method']
//       && $this->authorize['x_amount'] == $this->submit_data['x_amount']
//       && $this->authorize['x_invoice_num'] == $this->submit_data['x_invoice_num']
//       && $this->authorize['x_description'] == $this->submit_data['x_description']
       && $this->authorize['HashMatchStatus'] == 'PASS'
     ){
      $order->info['cc_type'] = $this->authorize['x_card_type'];
      $order->info['cc_number'] = $this->authorize['x_account_number'];
      $order->info['cc_owner'] = $this->authorize['x_first_name'] . ' ' . $this->authorize['x_last_name'];
      $this->auth_code = $this->authorize['x_auth_code'];
      $this->transaction_id = $this->authorize['x_trans_id'];
      return;
    }
    if ($this->authorize['x_response_code'] == '2') {
      $messageStack->add_session('checkout_payment', $this->authorize['x_response_reason_text'] . MODULE_PAYMENT_AUTHORIZENET_TEXT_DECLINED_MESSAGE, 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }
    // Code 3 or anything else is an error
    $messageStack->add_session('checkout_payment', MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR_MESSAGE, 'error');
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
  }
  /**
   * Post-processing activities
   *
   * @return boolean
   */
  function after_process() {
    global $insert_id, $order, $currencies;
    $this->notify('NOTIFY_PAYMENT_AUTHNETSIM_POSTPROCESS_HOOK');

    $comments = 'Credit Card payment.  AUTH: ' . $this->auth_code . ' TransID: ' . $this->transaction_id;
    if ($order->info['currency'] != $this->gateway_currency) {
      $comments .= ' (' . number_format($order->info['total'] * $currencies->get_value($this->gateway_currency), 2) . ' ' . $this->gateway_currency . ')';
    }
    zen_update_orders_history($insert_id, $comments, null, $this->order_status, -1);

    return false;
  }
  /**
   * Check to see whether module is installed
   *
   * @return boolean
   */
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_AUTHORIZENET_STATUS'");
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
    if (defined('MODULE_PAYMENT_AUTHORIZENET_STATUS')) {
      $messageStack->add_session('Authorize.net (SIM) module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=authorizenet', 'SSL'));
      return 'failed';
    }
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Authorize.net Module', 'MODULE_PAYMENT_AUTHORIZENET_STATUS', 'True', 'Do you want to accept Authorize.net payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Login ID', 'MODULE_PAYMENT_AUTHORIZENET_LOGIN', 'testing', 'The API Login ID used for your Authorize.net account.', '6', '0', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY', 'Test', 'Transaction Key used for sending transaction data.<br>See <a href=\"https://support.authorize.net/s/article/How-do-I-obtain-my-API-Login-ID-and-Transaction-Key\" rel=\"noopener\" target=\"_blank\">How-do-I-obtain-my-API-Login-ID-and-Transaction-Key</a>', '6', '0', now(), 'zen_cfg_password_display')");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Security Key', 'MODULE_PAYMENT_AUTHORIZENET_SECURITYKEY', '*Get from Authorizenet Account*', 'Security Key used for validating transactions (128 characters).<br>See <a href=\"https://support.authorize.net/s/article/What-is-a-Signature-Key\" rel=\"noopener\" target=\"_blank\">What-is-a-Signature-Key</a> for instructions.', '6', '0', now(), 'zen_cfg_password_display')");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE', 'Test', 'Transaction mode used for processing orders.<br><strong>Production</strong>=Live processing with real account credentials<br><strong>Test</strong>=Simulations with real account credentials<br><strong>Sandbox</strong>=use special sandbox transaction key to do special testing of success/fail transaction responses (obtain sandbox credentials via <a href=\"https://developer.authorize.net/hello_world/sandbox/\">developer.authorize.net</a>)', '6', '0', 'zen_cfg_select_option(array(\'Test\', \'Production\', \'Sandbox\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Method', 'MODULE_PAYMENT_AUTHORIZENET_METHOD', 'Credit Card', 'Transaction method used for processing orders', '6', '0', 'zen_cfg_select_option(array(\'Credit Card\'), ', now())");//, \'eCheck\'
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Authorization Type', 'MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE', 'Authorize', 'Do you want submitted credit card transactions to be authorized only, or captured immediately?', '6', '0', 'zen_cfg_select_option(array(\'Authorize\', \'Capture\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Request CVV Number', 'MODULE_PAYMENT_AUTHORIZENET_USE_CVV', 'True', 'Do you want to ask the customer for the card\'s CVV number', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Notifications', 'MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER', 'False', 'Should Authorize.Net email a receipt to the customer?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_AUTHORIZENET_SORT_ORDER', '0', 'Sort order of displaying payment options to the customer. Lowest is displayed first.', '6', '0', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID', '2', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Gateway Mode', 'MODULE_PAYMENT_AUTHORIZENET_GATEWAY_MODE', 'offsite', 'Where should customer credit card info be collected?<br /><b>onsite</b> = here (requires SSL)<br /><b>offsite</b> = authorize.net site', '6', '0', 'zen_cfg_select_option(array(\'onsite\', \'offsite\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Database Storage', 'MODULE_PAYMENT_AUTHORIZENET_STORE_DATA', 'True', 'Do you want to save the gateway communications data to the database?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Mode', 'MODULE_PAYMENT_AUTHORIZENET_DEBUGGING', 'Alerts Only', 'Would you like to enable debug mode?  A  detailed log of failed transactions may be emailed to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Alerts Only\', \'Log File\', \'Log and Email\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Supported', 'MODULE_PAYMENT_AUTHORIZENET_CURRENCY', 'USD', 'Which currency is your Authnet Gateway Account configured to accept?<br>(Purchases in any other currency will be pre-converted to this currency before submission using the exchange rates in your store admin.)', '6', '0', 'zen_cfg_select_option(array(\'USD\', \'CAD\', \'GBP\', \'EUR\', \'AUD\', \'NZD\'), ', now())");
  }
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $db;
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    if (defined('MODULE_PAYMENT_AUTHORIZENET_STATUS')) {
      global $db;
      if (!defined('MODULE_PAYMENT_AUTHORIZENET_CURRENCY')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Supported', 'MODULE_PAYMENT_AUTHORIZENET_CURRENCY', 'USD', 'Which currency is your Authnet Gateway Account configured to accept?<br>(Purchases in any other currency will be pre-converted to this currency before submission using the exchange rates in your store admin.)', '6', '0', 'zen_cfg_select_option(array(\'USD\', \'CAD\', \'GBP\', \'EUR\', \'AUD\', \'NZD\'), ', now())");
      }
      if (!defined('MODULE_PAYMENT_AUTHORIZENET_SECURITYKEY')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Security Key', 'MODULE_PAYMENT_AUTHORIZENET_SECURITYKEY', '*Get from Authorizenet Account*', 'REQUIRED Security Key used for validating transactions (128 characters). See <a href=\"https://support.authorize.net/s/article/What-is-a-Signature-Key\" rel=\"noopener\" target=\"_blank\">What-is-a-Signature-Key</a> for instructions.', '6', '0', now(), 'zen_cfg_password_display')");
      }
      if (defined('MODULE_PAYMENT_AUTHORIZENET_MD5HASH')) {
        // update description
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_AUTHORIZENET_MD5HASH'");
      }
    }
    return array('MODULE_PAYMENT_AUTHORIZENET_STATUS', 'MODULE_PAYMENT_AUTHORIZENET_LOGIN', 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY', 'MODULE_PAYMENT_AUTHORIZENET_SECURITYKEY', 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE', 'MODULE_PAYMENT_AUTHORIZENET_CURRENCY', 'MODULE_PAYMENT_AUTHORIZENET_METHOD', 'MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE', 'MODULE_PAYMENT_AUTHORIZENET_USE_CVV', 'MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER', 'MODULE_PAYMENT_AUTHORIZENET_ZONE', 'MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID', 'MODULE_PAYMENT_AUTHORIZENET_SORT_ORDER', 'MODULE_PAYMENT_AUTHORIZENET_GATEWAY_MODE', 'MODULE_PAYMENT_AUTHORIZENET_STORE_DATA', 'MODULE_PAYMENT_AUTHORIZENET_DEBUGGING');
  }

  /**
   * Check configuration settings to determine whether hashing is configured and which hashing mode to use
   * @return string|bool
   */
  function hashingMode() {
    if (MODULE_PAYMENT_AUTHORIZENET_SECURITYKEY !== '*Get from Authorizenet Account*' && trim(MODULE_PAYMENT_AUTHORIZENET_SECURITYKEY) !== '') {
      return 'sha';
    }
    return false;
  }

  /**
   * Calculate hmac fingerprint.
   *
   * @param float $amount
   * @param string $sequence
   * @param string $currency
   * @return array
   */
  function getHmacFingerprintFields($amount, $sequence, $currency = '') {
    $timestamp = time();
    $key = hex2bin(strtoupper(MODULE_PAYMENT_AUTHORIZENET_SECURITYKEY));
    $hashString = hash_hmac('sha512', MODULE_PAYMENT_AUTHORIZENET_LOGIN . '^' . $sequence . '^' . $timestamp . '^' . $amount . '^' . $currency, $key);
    $security_array = array('x_fp_sequence' => $sequence,
                            'x_fp_timestamp' => $timestamp,
                            'x_fp_hash' => $hashString);
    return $security_array;
  }

  /**
   * Get hash for response comparison
   * @param $data
   * @return string
   */
  function getHashForResponseData($data) {
    $dataArray = array(
        $data['x_trans_id'],
        $data['x_test_request'],
        $data['x_response_code'],
        $data['x_auth_code'],
        $data['x_cvv2_resp_code'],
        $data['x_cavv_response'],
        $data['x_avs_code'],
        $data['x_method'],
        $data['x_account_number'],
        $data['x_amount'],
        $data['x_company'],
        $data['x_first_name'],
        $data['x_last_name'],
        $data['x_address'],
        $data['x_city'],
        $data['x_state'],
        $data['x_zip'],
        $data['x_country'],
        $data['x_phone'],
        $data['x_fax'],
        $data['x_email'],
        $data['x_ship_to_company'],
        $data['x_ship_to_first_name'],
        $data['x_ship_to_last_name'],
        $data['x_ship_to_address'],
        $data['x_ship_to_city'],
        $data['x_ship_to_state'],
        $data['x_ship_to_zip'],
        $data['x_ship_to_country'],
        $data['x_invoice_num'],
    );
    $string = '^' . implode('^', $dataArray) . '^';
    return strtoupper(hash_hmac('sha512', $string, hex2bin(MODULE_PAYMENT_AUTHORIZENET_SECURITYKEY)));
  }

  /**
   * Used to do any debug logging / tracking / storage as required.
   */
  function _debugActions($response, $mode, $order_time= '', $sessID = '') {
    global $db, $messageStack, $insert_id;
    if ($order_time == '') $order_time = date("F j, Y, g:i a");
    $response['url'] = $this->form_action_url;
    $this->reportable_submit_data['url'] = $this->form_action_url;

    $errorMessage = date('M-d-Y h:i:s') .
                    "\n=================================\n\n";
    if ($mode == 'Submit-Data') $errorMessage .=
                    'Sent to Authorizenet: ' . print_r($this->reportable_submit_data, true) . "\n\n";
    if ($mode == 'Response-Data') $errorMessage .=
                    'Response Code: ' . $response['x_response_code'] . ".\nResponse Text: " . $response['x_response_reason_text'] . "\n\n" .
                    ($response['x_response_code'] == 2 && $response['x_response_reason_code'] == 4 ? ' NOTICE: Card should be picked up - possibly stolen ' : '') .
                    ($response['x_response_code'] == 3 && $response['x_response_reason_code'] == 11 ? ' DUPLICATE TRANSACTION ATTEMPT ' : '') .
                    'Results Received back from Authorizenet: ' . print_r($response, true) . "\n\n";
    // store log file if log mode enabled
    if (stristr(MODULE_PAYMENT_AUTHORIZENET_DEBUGGING, 'Log') || strstr(MODULE_PAYMENT_AUTHORIZENET_DEBUGGING, 'All') || (defined('AUTHORIZENET_DEVELOPER_MODE') && in_array(AUTHORIZENET_DEVELOPER_MODE, array('on', 'certify')))) {
      $key = ($response['x_trans_id'] != '' ? $response['x_trans_id'] . '_' : '') . time() . '_' . zen_create_random_value(4);
      $file = $this->_logDir . '/' . 'SIM_Debug_' . $key . '.log';
      $fp = @fopen($file, 'a');
      @fwrite($fp, $errorMessage);
      @fclose($fp);
    }
    // send email alerts only if in alert mode or if email specifically requested as logging mode
    if ((isset($response['x_response_code']) && $response['x_response_code'] != '1' && stristr(MODULE_PAYMENT_AUTHORIZENET_DEBUGGING, 'Alerts')) || stristr(MODULE_PAYMENT_AUTHORIZENET_DEBUGGING, 'Email')) {
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Authorizenet-SIM Alert ' . $response['x_invoice_num'] . ' ' . date('M-d-Y h:i:s') . ' ' . $response['x_trans_id'], $errorMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorMessage)), 'debug');
    }

    // DATABASE SECTION
    // Insert the send and receive response data into the database.
    // This can be used for testing or for implementation in other applications
    // This can be turned on and off if the Admin Section
    if (MODULE_PAYMENT_AUTHORIZENET_STORE_DATA == 'True' && $mode == 'Response-Data'){
      $db_response_text = $response['x_response_reason_text'];
      $db_response_text .= ($response['x_response_code'] == 2 && $response['x_response_reason_code'] == 4) ? ' NOTICE: Card should be picked up - possibly stolen ' : '';
      $db_response_text .= ($response['x_response_code'] == 3 && $response['x_response_reason_code'] == 11) ? ' DUPLICATE TRANSACTION ATTEMPT ' : '';

      // Insert the data into the database
      $sql = "INSERT INTO " . TABLE_AUTHORIZENET . "  (id, customer_id, order_id, response_code, response_text, authorization_type, transaction_id, sent, received, time, session_id) VALUES (NULL, :custID, :orderID, :respCode, :respText, :authType, :transID, :sentData, :recvData, :orderTime, :sessID )";
      $sql = $db->bindVars($sql, ':custID', $_SESSION['customer_id'], 'integer');
      $sql = $db->bindVars($sql, ':orderID', preg_replace('/[^0-9]/', '', $insert_id), 'integer');
      $sql = $db->bindVars($sql, ':respCode', $response['x_response_code'], 'integer');
      $sql = $db->bindVars($sql, ':respText', $db_response_text, 'string');
      $sql = $db->bindVars($sql, ':authType', $response['x_type'], 'string');
      if (isset($response['x_trans_id']) && trim($response['x_trans_id']) != '') {
        $sql = $db->bindVars($sql, ':transID', $response['x_trans_id'], 'string');
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
    $fieldOkay1 = (method_exists($sniffer, 'field_type')) ? $sniffer->field_type(TABLE_AUTHORIZENET, 'transaction_id', 'varchar(64)', true) : -1;
    if ($fieldOkay1 !== true) {
      $db->Execute("ALTER TABLE " . TABLE_AUTHORIZENET . " CHANGE transaction_id transaction_id varchar(64) default NULL");
    }
  }
}

if(!function_exists('hash_equals')) {
    function hash_equals($str1, $str2) {
        if (strlen($str1) != strlen($str2)) {
            return false;
        }
        $res = $str1 ^ $str2;
        $ret = 0;
        for ($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
        return !$ret;
    }
}
