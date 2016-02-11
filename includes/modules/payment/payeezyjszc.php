<?php
/**
 * PayEezy payment module
 *
 * Payeezy does token-based transactions, to avoid the risks of onsite handling of card data, thereby not interfering with PCI Compliance.
 * The customer stays on-site but card-processing is done remotely over secure channels, preventing any unnecessary processing of sensitive data.
 * 
 * NOTE: You will need TransArmor enabled on your merchant account to do token based transactions. 
 * Contact your merchant account representative for more details on how to enable this or call 1-855-799-0790.
 * For merchants domiciled outside the U.S. please contact your local technical support team for assistance with preparing your account to work with PayEezyJS and Token-Based transactions.
 *
 * @package payeezy
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson   New in v1.5.5 $
 */
/**
 * PayEezy Payment module class
 */
class payeezyjszc extends base {
  /**
   * $code determines the internal 'code' name used to designate "this" payment module
   *
   * @var string
   */
  var $code;
  /**
   * $moduleVersion is the plugin version number
   */
  var $moduleVersion = '1.00';
  /**
   * $title is the displayed name for this payment method
   *
   * @var string
   */
  var $title;
  /**
   * $description is admin-display details for this payment method
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
   * $sort_order determines the display-order of this module to customers
   */
  var $sort_order;
  /**
   * $commError and $commErrNo are CURL communication error details for debug purposes
   */
  var $commError, $commErrNo;
  /**
   * transaction vars hold the IDs of the completed payment
   */
  var $transaction_id, $transaction_messages, $auth_code;
  /**
   * internal vars
   */
  private $avs_codes, $cvv_codes;


  /**
   * Constructor
   */
  function __construct() {
    global $order;

    $this->code = 'payeezyjszc';
    $this->title = MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CATALOG_TITLE; // Payment module title in Catalog
    if (IS_ADMIN_FLAG === true) {
      $this->title = MODULE_PAYMENT_PAYEEZYJSZC_TEXT_ADMIN_TITLE;
      if (MODULE_PAYMENT_PAYEEZYJSZC_API_SECRET == '')          $this->title .= '<span class="alert"> (not configured; API details needed)</span>';
      if (MODULE_PAYMENT_PAYEEZYJSZC_TESTING_MODE == 'Sandbox') $this->title .= '<span class="alert"> (Sandbox mode)</span>';
      $new_version_details = plugin_version_check_for_updates(2050, $this->moduleVersion);
      if ($new_version_details !== false) {
          $this->title .= '<span class="alert">' . ' - NOTE: A NEW VERSION OF THIS PLUGIN IS AVAILABLE. <a href="' . $new_version_details['link'] . '" target="_blank">[Details]</a>' . '</span>';
      }
    }

    $this->description = 'PayeezyJS ' . $this->moduleVersion . '<br>' . MODULE_PAYMENT_PAYEEZYJSZC_TEXT_DESCRIPTION;
    $this->enabled = ((MODULE_PAYMENT_PAYEEZYJSZC_STATUS == 'True') ? true : false);
    $this->sort_order = MODULE_PAYMENT_PAYEEZYJSZC_SORT_ORDER;

    // determine order-status for transactions
    if ((int)MODULE_PAYMENT_PAYEEZYJSZC_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_PAYEEZYJSZC_ORDER_STATUS_ID;
    }
    // Reset order status to pending if capture pending:
    if (MODULE_PAYMENT_PAYEEZYJSZC_TRANSACTION_TYPE == 'authorize') {
      $this->order_status = 1;
    }

    $this->_logDir = DIR_FS_LOGS;

    // check for zone compliance and any other conditionals
    if (is_object($order)) $this->update_status();

    $this->setAvsCvvMeanings();
  }


  function update_status() {
    global $order, $db;
    if ($this->enabled == false || (int)MODULE_PAYMENT_PAYEEZYJSZC_ZONE == 0) {
      return;
    }
    $check_flag = false;
    $sql = "SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . (int)MODULE_PAYMENT_PAYEEZYJSZC_ZONE . "' AND zone_country_id = '" . (int)$order->billing['country']['id'] . "' ORDER BY zone_id";
    $checks = $db->Execute($sql);
    foreach ($checks as $check) {
      if ($check['zone_id'] < 1) {
        $check_flag = true;
        break;
      } elseif ($check['zone_id'] == $order->billing['zone_id']) {
        $check_flag = true;
        break;
      }
    }
    if ($check_flag == false) {
      $this->enabled = false;
    }
  }
  function javascript_validation() {
    return '';
  }
  function selection() {
    global $order;

    // PayEezy currently only accepts  "American Express", "Visa", "Mastercard", "Discover", "JCB", "Diners Club"
    $cc_types = array();
    if (CC_ENABLED_VISA == 1)     $cc_types[] = array('id' => 'Visa', 'text'=> 'Visa');
    if (CC_ENABLED_MC == 1)       $cc_types[] = array('id' => 'Mastercard', 'text'=> 'Mastercard');
    if (CC_ENABLED_DISCOVER == 1) $cc_types[] = array('id' => 'Discover', 'text'=> 'Discover');
    if (CC_ENABLED_AMEX == 1)     $cc_types[] = array('id' => 'American Express', 'text'=> 'American Express');
    if (CC_ENABLED_JCB == 1)      $cc_types[] = array('id' => 'JCB', 'text'=> 'JCB');
    if (CC_ENABLED_DINERS_CLUB == 1) $cc_types[] = array('id' => 'Diners Club', 'text'=> 'Diners Club');

    // Prepare selection of expiry dates
    for ($i=1; $i<13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
    }
    $today = getdate();
    for ($i=$today['year']; $i < $today['year']+15; $i++) {
      $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }

    // helper for auto-selecting the radio-button next to this module so the user doesn't have to make that choice
    $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

    $selection = array(
        'id' => $this->code,
        'module' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CATALOG_TITLE,
        'fields' => array(
            array(
                'title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_TYPE,
                'field' => zen_draw_pull_down_menu($this->code . '_cc_type', $cc_types, '',
                    'payeezy-data="card_type" id="' . $this->code . '_cc-type"' . $onFocus . ' autocomplete="off"'),
                'tag' => $this->code . '_cc-type'
            ),
            array(
                'title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_OWNER,
                'field' => zen_draw_input_field($this->code . '_cc_owner',
                    $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                    'payeezy-data="cardholder_name" id="' . $this->code . '_cc-owner"' . $onFocus . ' autocomplete="off"'),
                'tag' => $this->code . '_cc-owner'
            ),
            array(
                'title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_NUMBER,
                'field' => zen_draw_input_field($this->code . '_cc_number', '',
                    'payeezy-data="cc_number" id="' . $this->code . '_cc-number"' . $onFocus . ' autocomplete="off"'),
                'tag' => $this->code . '_cc-number'
            ),
            array(
                'title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_EXPIRES,
                'field' => zen_draw_pull_down_menu($this->code . '_cc_expires_month', $expires_month, strftime('%m'), 'payeezy-data="exp_month" id="' . $this->code . '_cc-expires-month"' . $onFocus) . '&nbsp;' . 
                         zen_draw_pull_down_menu($this->code . '_cc_expires_year', $expires_year, '', 'payeezy-data="exp_year" id="' . $this->code . '_cc-expires-year"' . $onFocus),
                'tag' => $this->code . '_cc-expires-month'
            ),
            array(
                'title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CVV,
                'field' => zen_draw_input_field($this->code. '_cc_cvv', '', 'size="4" maxlength="4"' . 'payeezy-data="cvv_code" id="'.$this->code.'_cc-cvv"' . $onFocus . ' autocomplete="off"'),
                'tag' => $this->code.'_cc-cvv'
            ),
            array(
                'title' => '',
                'field' => zen_draw_hidden_field($this->code . '_fdtoken', '', 'id="' . $this->code . '_fdtoken"') . '<div id="payeezy-payment-errors"></div>' .
                           zen_draw_hidden_field($this->code . '_billing_street', $order->billing['street_address'], 'payeezy-data="billing.street"') .
                           zen_draw_hidden_field($this->code . '_billing_city', $order->billing['city'], 'payeezy-data="billing.city"') .
                           zen_draw_hidden_field($this->code . '_billing_state', zen_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']), 'payeezy-data="billing.state"') .
                           zen_draw_hidden_field($this->code . '_billing_country', $order->billing['country']['iso_code_2'], 'payeezy-data="billing.country"') .
                           zen_draw_hidden_field($this->code . '_billing_zip', $order->billing['postcode'], 'payeezy-data="billing.zip"') .
                           zen_draw_hidden_field($this->code . '_billing_email', $order->customer['email_address'], 'payeezy-data="billing.email"') .
                           zen_draw_hidden_field($this->code . '_billing_phone', $order->customer['telephone'], 'payeezy-data="billing.phone"') ,
                'tag' => ''
            ),
        )
    );
    return $selection;
  }

  function pre_confirmation_check() {
    global $messageStack;
    if (!isset($_POST[$this->code . '_fdtoken']) || trim($_POST[$this->code . '_fdtoken']) == '') {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_ERROR_MISSING_FDTOKEN, 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }
  }

  function confirmation() {
    $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_TYPE,
                                                  'field' => zen_output_string_protected($_POST[$this->code . '_cc_type'])),
                                            array('title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_OWNER,
                                                  'field' => zen_output_string_protected($_POST[$this->code . '_cc_owner'])),
                                            array('title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_NUMBER,
                                                  'field' => zen_output_string_protected($_POST[$this->code . '_cc_number'])),
                                            array('title' => MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_EXPIRES,
                                                  'field' => strftime('%B, %Y', mktime(0,0,0,$_POST[$this->code . '_cc_expires_month'], 1, '20' . $_POST[$this->code . '_cc_expires_year']))),
                                            ));
    return $confirmation;
  }

  function process_button() {
    $process_button_string = zen_draw_hidden_field($this->code . '_fdtoken', $_POST[$this->code . '_fdtoken']);
    $process_button_string .= zen_draw_hidden_field('cc_owner', zen_output_string_protected($_POST[$this->code . '_cc_owner']));
    $process_button_string .= zen_draw_hidden_field('cc_type', zen_output_string_protected($_POST[$this->code . '_cc_type']));
    $process_button_string .= zen_draw_hidden_field('cc_number', zen_output_string_protected($_POST[$this->code . '_cc_number']));
    $process_button_string .= zen_draw_hidden_field('cc_expires', (int)$_POST[$this->code . '_cc_expires_month'] . (int)$_POST[$this->code . '_cc_expires_year']);
    return $process_button_string;
  }

  function before_process() {
    global $messageStack, $order, $currencies;

    if (!isset($_POST[$this->code . '_fdtoken']) || trim($_POST[$this->code . '_fdtoken']) == '') {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_ERROR_MISSING_FDTOKEN, 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }
    
    $order->info['cc_owner']   = $_POST['cc_owner'];
    $order->info['cc_type'] = $_POST['cc_type'];
    $order->info['cc_number']  = $_POST['cc_number'];
    if (!strpos($order->info['cc_number'], 'XX')) {
      $order->info['cc_number']  = str_pad(substr($_POST['cc_number'], -4), strlen($_POST['cc_number']), "X", STR_PAD_LEFT);
    }
    $order->info['cc_expires'] = '';
    $order->info['cc_cvv']     = '***';


    // @TODO - consider converting currencies if the gateway requires


    // format purchase amount 
    $payment_amount = $order->info['total'];
    $decimal_places = $currencies->get_decimal_places($order->info['currency']);
    if ($decimal_places > 0) {
      $payment_amount = $payment_amount * pow(10, $decimal_places); // Future: Exponentiation Operator ** requires PHP 5.6
    }

// sandbox testing
// $payment_amount = 520200;

    // prepare data for submission
    $payload = array();
    $payload['merchant_ref'] = substr(htmlentities(STORE_NAME), 0, 20);
    $payload['transaction_type'] = MODULE_PAYMENT_PAYEEZYJSZC_TRANSACTION_TYPE;
    $payload['method'] = 'token';
    $payload['amount'] = (int)$payment_amount;
    $payload['currency_code'] = strtoupper($order->info['currency']);
    $payload['token'] = array('token_type' => 'FDToken');
    $payload['token']['token_data']['value'] = preg_replace('/[^0-9a-z]/i', '', $_POST[$this->code . '_fdtoken']);
    $payload['token']['token_data']['cardholder_name'] = htmlentities($order->info['cc_owner']);
    $payload['token']['token_data']['exp_date'] = str_pad(preg_replace('/[^0-9]/', '', $_POST['cc_expires']), 4, '0', STR_PAD_LEFT); // ensure month is 2 digits
    $payload['token']['token_data']['type'] = preg_replace('/[^a-z ]/i', '', $_POST['cc_type']);


    $payload = json_encode($payload, JSON_FORCE_OBJECT);
    // submit transaction
    $response = $this->postTransaction($payload, $this->hmacAuthorizationToken($payload));

    // log the response data
    $this->logTransactionData($response, $payload);

    // analyze the response

    // http_codes:
    // 200, 201, 202 - OK
    // 400 = bad request, therefore did not complete
    // 401 = unauthorized = invalid API key and token
    // 403 = unauthorized = bad hmac verification
    // 404 = requested resource did not exist
    // 500, 502, 503, 504 = server error on Payeezy end

    // transaction_status: 
    // Approved = Card Approved 
    // Declined = Gateway declined 
    // Not Processed = For any internal errors this status is returned. 

    // validation_status: values - “success” / ”failure” based on input validation

    // transaction_id and transaction_tag (auth code) -- are used for follow-on processing such as recurring billing, void/capture/refund, etc


    // successful submission; now need to ensure it was not declined
    if (in_array($response['http_code'], array(200, 201, 202))) {
    // success example:
    // {"correlation_id":"228.1100035528625",
    // "transaction_status":"approved",
    // "validation_status":"success",
    // "transaction_type":"purchase",
    // "transaction_id":"ET159009",
    // "transaction_tag":"74080064",
    // "method":"token",
    // "amount":"200",
    // "currency":"USD",
    // "cvv2":"I",
    // "token":{"token_type":"FDToken",
    //   "token_data":{"type":"Mastercard",
    //   "cardholder_name":"xyz",
    //   "exp_date":"0430","value":"2833693200041732"}
    // },
    // "bank_resp_code":"100",
    // "bank_message":"Approved",
    // "gateway_resp_code":"00",
    // "gateway_message":"Transaction Normal"}

      if ($response['transaction_status'] == 'approved') {
        $this->auth_code = $response['transaction_tag'];
        $this->transaction_id = $response['transaction_id'] . ' ' . $response['transaction_tag'];
        $this->transaction_messages = $response['bank_resp_code'] . ' ' . $response['bank_message'] . ' ' . $response['gateway_resp_code'] . ' ' . $response['gateway_message'];
        if (isset($response['avs']) && isset($this->avs_codes[$response['avs']])) $this->transaction_messages .= "\n" . 'AVS: ' . $this->avs_codes[$response['avs']];
        if (isset($response['cvv2']) && isset($this->cvv_codes[$response['cvv2']])) $this->transaction_messages .= "\n" . 'CVV: ' . $this->cvv_codes[$response['cvv2']];
        return true;        
      }

      if ($response['transaction_status'] == 'declined') {

        // check if card is flagged for fraud
        if (in_array($response['bank_resp_code'], array(500,501,502,503,596,534,524,519))) {
          global $zco_notifier;
          $_SESSION['payment_attempt'] = 500;
          $zco_notifier->notify('NOTIFY_CHECKOUT_SLAMMING_LOCKOUT', $response);
          $_SESSION['cart']->reset(TRUE);
          zen_session_destroy();
          $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_ERROR_DECLINED, 'error');
          zen_redirect(zen_href_link(FILENAME_TIME_OUT));
        }

        // generic "declined" message response
        $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_ERROR_DECLINED, 'error');
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
      }

      // Should never get here if we have a 200-204 response; if we get here, the transaction could not be processed for some other reason
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_TEXT_ERROR . '[' . zen_output_string_protected($response['bank_resp_code'] . '/' . $response['gateway_resp_code']) . ']', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }


    // failed
    if ($response['http_code'] == 400) {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_TEXT_ERROR . '[' . zen_output_string_protected($response['Error']['messages'][0]['description']) . ']', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));

    // error example:
    // {"correlation_id":"228.1454542837160",
    // "Error":
    //   {"messages":[
    //     {"code":"invalid_card_type",
    //      "description":"The card type is invalid"}
    //      ]
    //   },
    // "transaction_status":"Not Processed",
    // "validation_status":"failed",

    }

    // invalid API key and token
    if ($response['http_code'] == 401) {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_TEXT_MISCONFIGURATION . 'PAYEEZY-401-BAD-API-TOKEN', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    if ($response['http_code'] == 403) {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_TEXT_MISCONFIGURATION . 'PAYEEZY-403-BAD-HMAC', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

      // bad transaction call
    if ($response['http_code'] == 404) {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_TEXT_MISCONFIGURATION . 'PAYEEZY-404-FAILED-SEE-LOGS', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    // error at PayEezy. Call tech support
    if (in_array($response['http_code'], array(500,502,503,504))) {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_TEXT_MISCONFIGURATION . 'PAYEEZY-500-CALL_TECH_SUPPORT', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    // communications/CURL error
    if ($this->commError != '') {
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_TEXT_COMM_ERROR . ' (' . $this->commErrNo . ')', 'caution');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    // should never get here
    $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYEEZYJSZC_TEXT_ERROR . '[PAYEEZY-GENERAL-FAILURE]', 'error');
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
  }
  /**
   * Post-process activities. Updates the order-status history data with the auth code from the transaction.
   *
   * @return boolean
   */
  function after_process() {
    global $insert_id, $db, $order;
    $sql = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, customer_notified, date_added) values (:orderComments, :orderID, :orderStatus, -1, now() )";
    $sql = $db->bindVars($sql, ':orderComments', 'Credit Card payment.  TransID: ' . $this->transaction_id . ' - ' . $this->transaction_messages, 'string');
    $sql = $db->bindVars($sql, ':orderID', $insert_id, 'integer');
    $sql = $db->bindVars($sql, ':orderStatus', $this->order_status, 'integer');
    $db->Execute($sql);
    return true;
  }


  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYEEZYJSZC_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }
  function install() {
    global $db;

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Payeezy JS Module', 'MODULE_PAYMENT_PAYEEZYJSZC_STATUS', 'True', 'Do you want to accept PayEezy (First Data) payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYEEZYJSZC_SORT_ORDER', '0', 'Sort order of displaying payment options to the customer. Lowest is displayed first.', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYEEZYJSZC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYEEZYJSZC_ORDER_STATUS_ID', '2', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Type', 'MODULE_PAYMENT_PAYEEZYJSZC_TRANSACTION_TYPE', 'purchase', 'Should payments be [authorized] only, or be completed [purchases]?', '6', '0', 'zen_cfg_select_option(array(\'authorize\', \'purchase\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('API Key', 'MODULE_PAYMENT_PAYEEZYJSZC_API_KEY', '', 'Enter the API Key assigned to your account', '6', '0',  now(), 'zen_cfg_password_display')");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('API Secret', 'MODULE_PAYMENT_PAYEEZYJSZC_API_SECRET', '', 'Enter the API Secret assigned to your account', '6', '0',  now(), 'zen_cfg_password_display')");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('Merchant Token', 'MODULE_PAYMENT_PAYEEZYJSZC_MERCHANT_TOKEN', '', 'Enter the Merchant Token from your account settings', '6', '0',  now(), 'zen_cfg_password_display')");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('JS Security Key', 'MODULE_PAYMENT_PAYEEZYJSZC_JSSECURITY_KEY', '', 'Enter the JS Security key from your account settings', '6', '0',  now(), 'zen_cfg_password_display')");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('Trans Armour Token', 'MODULE_PAYMENT_PAYEEZYJSZC_TATOKEN', '', 'Enter the TA Token from your account settings', '6', '0',  now(), 'zen_cfg_password_display')");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Sandbox/Live Mode', 'MODULE_PAYMENT_PAYEEZYJSZC_TESTING_MODE', 'Sandbox', 'Use [Live] for real transactions<br>Use [Sandbox] for developer testing', '6', '0', 'zen_cfg_select_option(array(\'Live\', \'Sandbox\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Log Mode', 'MODULE_PAYMENT_PAYEEZYJSZC_LOGGING', 'Off', 'Would you like to enable debug mode?  A complete detailed log of failed transactions may be emailed to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Log Always\', \'Log on Failures\', \'Log Always and Email on Failures\', \'Log on Failures and Email on Failures\', \'Email Always\', \'Email on Failures\'), ', now())");

  }
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }
  function keys() {
    return array(
       'MODULE_PAYMENT_PAYEEZYJSZC_STATUS',
       'MODULE_PAYMENT_PAYEEZYJSZC_SORT_ORDER',
       'MODULE_PAYMENT_PAYEEZYJSZC_ZONE',
       'MODULE_PAYMENT_PAYEEZYJSZC_TRANSACTION_TYPE',
       'MODULE_PAYMENT_PAYEEZYJSZC_ORDER_STATUS_ID',
       'MODULE_PAYMENT_PAYEEZYJSZC_API_KEY',
       'MODULE_PAYMENT_PAYEEZYJSZC_API_SECRET',
       'MODULE_PAYMENT_PAYEEZYJSZC_MERCHANT_TOKEN', 
       'MODULE_PAYMENT_PAYEEZYJSZC_JSSECURITY_KEY',
       'MODULE_PAYMENT_PAYEEZYJSZC_TATOKEN',
       'MODULE_PAYMENT_PAYEEZYJSZC_TESTING_MODE',
       'MODULE_PAYMENT_PAYEEZYJSZC_LOGGING',
     );
  }


  private function hmacAuthorizationToken($payload)
  {
    $nonce = strval(hexdec(bin2hex(openssl_random_pseudo_bytes(4, $cstrong))));
    $timestamp = strval(time()*1000); //time stamp in milli seconds
    $data = MODULE_PAYMENT_PAYEEZYJSZC_API_KEY . $nonce . $timestamp . MODULE_PAYMENT_PAYEEZYJSZC_MERCHANT_TOKEN . $payload;
    $hashAlgorithm = "sha256";
    $hmac = hash_hmac($hashAlgorithm, $data, MODULE_PAYMENT_PAYEEZYJSZC_API_SECRET, false);    // HMAC Hash in hex
    $authorization = base64_encode($hmac);
    return array(
        'authorization' => $authorization,
        'nonce' => $nonce,
        'timestamp' => $timestamp,
    );
  }

  private function postTransaction($payload, $headers)
  {
    $curlHeaders = array(
        'Content-Type: application/json',
        'apikey:'.strval(MODULE_PAYMENT_PAYEEZYJSZC_API_KEY),
        'token:'.strval(MODULE_PAYMENT_PAYEEZYJSZC_MERCHANT_TOKEN),
        'Authorization:'.$headers['authorization'],
        'nonce:'.$headers['nonce'],
        'timestamp:'.$headers['timestamp'],
    );
    $request = curl_init();
    curl_setopt($request, CURLOPT_URL, "https://api-cert.payeezy.com/v1/transactions");
    curl_setopt($request, CURLOPT_POST, true);
    curl_setopt($request, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_HEADER, false);
    curl_setopt($request, CURLOPT_HTTPHEADER, $curlHeaders);
    $response = curl_exec($request);
    if (FALSE === $response) {
      $this->commError = curl_error($request);
      $this->commErrNo = curl_errno($request);
    }
    $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
    $this->commInfo = curl_getinfo($request);
    curl_close($request);

    if (!in_array($httpcode, array(200,201,202))) {
      error_log($response);
    }

    $response = json_decode($response, true);
    $response['http_code'] = $httpcode;
    $response['curlHeaders'] = $curlHeaders;
    return $response;
  }


  /**
   * Log transaction errors if enabled
   */
  private function logTransactionData($response, $payload) {
    global $db;

    // Don't log headers if we get a success response
    if (substr($response['http_code'], 0, 2) == '20') unset($response['curlHeaders']);

    $logMessage = date('M-d-Y h:i:s') .
                    "\n=================================\n\n" .
                    ($this->commError !='' ? 'Comm results: ' . $this->commErrNo . ' ' . $this->commError . "\n\n" : '') .
                    'Transaction Status: ' . $response['transaction_status'] . "\n" .
                    'Bank Message: ' . $response['bank_message'] . "\n" .
                    'HTTP Response Code: ' . $response['http_code'] . "\n\n" .
                    'Sent to Payeezy: ' . print_r($payload, true) . "\n\n" .
                    'Results Received back from Payeezy: ' . print_r($response, true) . "\n\n" .
                    'CURL communication info: ' . print_r($this->commInfo, true) . "\n";

    if (strstr(MODULE_PAYMENT_PAYEEZYJSZC_LOGGING, 'Log Always') || ($response['transaction_status'] != 'approved' && strstr(MODULE_PAYMENT_PAYEEZYJSZC_LOGGING, 'Log on Failures'))) {
      $key = $response['transaction_id'] . '_' . preg_replace('/[^a-z]/i', '', $response['transaction_status']) . '_' . time() . '_' . zen_create_random_value(4);
      $file = $this->_logDir . '/' . 'PayEezy_' . $key . '.log';
      if ($fp = @fopen($file, 'a')) {
        fwrite($fp, $logMessage);
        fclose($fp);
      }
    }
    if (($response['transaction_status'] != 'approved' && stristr(MODULE_PAYMENT_PAYEEZYJSZC_LOGGING, 'Email on Failures')) || strstr(MODULE_PAYMENT_PAYEEZYJSZC_LOGGING, 'Email Always')) {
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'PayEezy Alert ' . $response['transaction_status'] . ' ' . date('M-d-Y h:i:s'), $logMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($logMessage)), 'debug');
    }
  }

  private function setAvsCvvMeanings() {
    $this->cvv_codes['M'] = 'CVV2/CVC2 Match - Indicates that the card is authentic. Complete the transaction if the authorization request was approved.';
    $this->cvv_codes['N'] = 'CVV2 / CVC2 No Match – May indicate a problem with the card. Contact the cardholder to verify the CVV2 code before completing the transaction, even if the authorization request was approved.';
    $this->cvv_codes['P'] = 'Not Processed - Indicates that the expiration date was not provided with the request, or that the card does not have a valid CVV2 code. If the expiration date was not included with the request, resubmit the request with the expiration date.';
    $this->cvv_codes['S'] = 'Merchant Has Indicated that CVV2 / CVC2 is not present on card - May indicate a problem with the card. Contact the cardholder to verify the CVV2 code before completing the transaction.';
    $this->cvv_codes['U'] = 'Issuer is not certified and/or has not provided visa encryption keys';
    $this->cvv_codes['I'] = 'CVV2 code is invalid or empty';

    $this->avs_codes['X'] = 'Exact match, 9 digit zip - Street Address, and 9 digit ZIP Code match';
    $this->avs_codes['Y'] = 'Exact match, 5 digit zip - Street Address, and 5 digit ZIP Code match';
    $this->avs_codes['A'] = 'Partial match - Street Address matches, ZIP Code does not';
    $this->avs_codes['W'] = 'Partial match - ZIP Code matches, Street Address does not';
    $this->avs_codes['Z'] = 'Partial match - 5 digit ZIP Code match only';
    $this->avs_codes['N'] = 'No match - No Address or ZIP Code match';
    $this->avs_codes['U'] = 'Unavailable - Address information is unavailable for that account number, or the card issuer does not support';
    $this->avs_codes['G'] = 'Service Not supported, non-US Issuer does not participate';
    $this->avs_codes['R'] = 'Retry - Issuer system unavailable, retry later';
    $this->avs_codes['E'] = 'Not a mail or phone order';
    $this->avs_codes['S'] = 'Service not supported';
    $this->avs_codes['Q'] = 'Bill to address did not pass edit checks/Card Association cannot verify the authentication of an address';
    $this->avs_codes['D'] = 'International street address and postal code match';
    $this->avs_codes['B'] = 'International street address match, postal code not verified due to incompatible formats';
    $this->avs_codes['C'] = 'International street address and postal code not verified due to incompatible formats';
    $this->avs_codes['P'] = 'International postal code match, street address not verified due to incompatible format';
    $this->avs_codes['1'] = 'Cardholder name matches';
    $this->avs_codes['2'] = 'Cardholder name, billing address, and postal code match';
    $this->avs_codes['3'] = 'Cardholder name and billing postal code match';
    $this->avs_codes['4'] = 'Cardholder name and billing address match';
    $this->avs_codes['5'] = 'Cardholder name incorrect, billing address and postal code match';
    $this->avs_codes['6'] = 'Cardholder name incorrect, billing postal code matches';
    $this->avs_codes['7'] = 'Cardholder name incorrect, billing address matches';
    $this->avs_codes['8'] = 'Cardholder name, billing address, and postal code are all incorrect';
    $this->avs_codes['F'] = 'Address and Postal Code match (UK only)';
    $this->avs_codes['I'] = 'Address information not verified for international transaction';
    $this->avs_codes['M'] = 'Address and Postal Code match';
  }

}
