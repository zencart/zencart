<?php
/**
 * First Data Hosted Checkout Payment Pages Module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
/**
 * First Data Hosted Checkout Payment Pages Module
 */
class firstdata_hco extends base {
  /**
   * $code determines the internal 'code' name used to designate "this" payment module
   *
   * @var string
   */
  var $code;
  /**
   * $moduleVersion is the plugin version number
   */
  var $moduleVersion = '1.04';

  /**
   * $title is the displayed name for this payment method
   *
   * @var string
   */
  var $title;
  /**
   * $description is used to display instructions in the admin
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
   * log file folder
   *
   * @var string
   */
  protected $_logDir = '';
  /**
   * vars for internal processing and debug/logging
   */
  protected $reportable_submit_data;
  protected $authorize;
  var $auth_code;
  var $transaction_id;
  /**
   * $order_status determines the status assigned to orders paid-for using this module
   */
  var $order_status;
  /**
   * @var the currency enabled in this gateway's merchant account. Transactions will be converted to this currency.
   */
  protected $gateway_currency;


  /**
   * Constructor
   */
  function __construct() {
    global $order;

    $this->code = 'firstdata_hco';

    $this->title = MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_CATALOG_TITLE; // Payment module title in Catalog
    if (IS_ADMIN_FLAG === true) {
      $this->description = MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_DESCRIPTION;
      $this->title = MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_ADMIN_TITLE; // Payment module title in Admin

      if (defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS')) {
        $new_version_details = plugin_version_check_for_updates(2051, $this->moduleVersion);
        if ($new_version_details !== false) {
          $this->title .= '<span class="alert">' . ' - NOTE: A NEW VERSION OF THIS PLUGIN IS AVAILABLE. <a href="' . $new_version_details['link'] . '" rel="noopener" target="_blank">[Details]</a>' . '</span>';
        }
      }
    }

    $this->enabled = (defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS') && MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS == 'True');
    $this->sort_order = defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_SORT_ORDER') ? MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_SORT_ORDER : null;

    if (null === $this->sort_order) return false;

    if (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS == 'True' && (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_PAGEID == 'testing' || MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TXNKEY == 'Test' || MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_RESPONSEKEY == '*Enter the Response Key here*')) {
      $this->title .=  '<span class="alert"> (Not Configured)</span>';
    } elseif (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE == 'Test') {
      $this->title .= '<span class="alert"> (in Testing mode)</span>';
    } elseif (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE == 'Sandbox') {
      $this->title .= '<span class="alert"> (in Sandbox Developer mode)</span>';
    }

    $this->form_action_url = 'https://checkout.globalgatewaye4.firstdata.com/payment';
    if (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE == 'Sandbox') $this->form_action_url = 'https://demo.globalgatewaye4.firstdata.com/payment';

    // set the currency for the gateway (others will be converted to this one before submission)
    $this->gateway_currency = MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_CURRENCY;


    if (defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ORDER_STATUS_ID;
    }

    // Reset order status to pending if capture pending:
    if (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_AUTHORIZATION_TYPE == 'Authorize') $this->order_status = 1;

    if (is_object($order)) $this->update_status();

    $this->_logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;
  }

  /**
   * Calculate zone matches and flag settings to determine whether this module should display to customers or not
   */
  function update_status() {
    global $order, $db;

    if ($this->enabled && (int)MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ZONE > 0 && isset($order->billing['country']['id'])) {
      $check_flag = false;
      $check = $db->Execute("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ZONE . "' AND zone_country_id = '" . (int)$order->billing['country']['id'] . "' ORDER BY zone_id");
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
    return '';
  }
  /**
   * Display Credit Card Information Submission Fields on the Checkout Payment Page
   *
   * @return array
   */
  function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
  }
  /**
   * Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   *
   */
  function pre_confirmation_check() {
    // no validation required since all the payment processing is hosted externally
    return true;
  }
  /**
   * Display Credit Card Information on the Checkout Confirmation Page
   *
   * @return array
   */
  function confirmation() {
    return array();
  }
  /**
   * Build the data and actions to process when the "Submit" button is pressed on the order-confirmation screen.
   * This sends the data to the payment gateway for processing.
   * (These are hidden fields on the checkout confirmation page)
   *
   * @return string
   */
  function process_button() {
    global $db, $order, $order_totals;

    // Calculate the next expected order id
    $result = $db->Execute("SELECT max(orders_id)+1 AS orders_id FROM " . TABLE_ORDERS . " ORDER BY orders_id");
    $next_order_id = $result->fields['orders_id'];
    // add randomized suffix to order id to produce uniqueness ... since it's unwise to submit the same order-number twice to the gateway, and this order is not yet committed
    $next_order_id = (string)$next_order_id . '-' . zen_create_random_value(6, 'chars');

    $submit_data_core = array(
      'x_login' => html_entity_decode(MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_PAGEID),
      'x_user3' => 'EZN001', // First Data mode
      'x_amount' => round($order->info['total'], 2),
      'x_currency_code' => $_SESSION['currency'],
      'x_type' => MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_AUTHORIZATION_TYPE == 'Authorize' ? 'AUTH_ONLY': 'AUTH_CAPTURE',
      'x_email_customer' => ((MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_EMAIL_CUSTOMER == 'True') ? 'TRUE': 'FALSE'),
      'x_cust_id' => $_SESSION['customer_id'],
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
      'x_customer_ip' => zen_get_ip_address(),
      'x_description' => 'Website Purchase from ' . str_replace('"',"'", STORE_NAME),
      'x_invoice_num' => $next_order_id,
      'x_po_num' => $next_order_id, // customer reference number; in this case we pass the proposed order ID value.
//       'x_method' => 'CC', // if not passed, then the payment types can be configured in the PaymentPage including enabling PayPal and other features.
//       'x_ga_tracking_id' => '', // Enter Google Analytics Tracking ID if you want this payment page included in your funnel
    );

    if (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ENABLE_LEVEL3 == 'Yes') {
      $submit_data_core['enable_level3_processing'] = 'TRUE';
    }

    // lookup shipping and discount amounts
    if (sizeof($order_totals)) {
      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        if ($order_totals[$i]['code'] == '') continue;
        if (in_array($order_totals[$i]['code'], array('ot_total','ot_subtotal','ot_tax','ot_shipping', 'insurance'))) {
          if ($order_totals[$i]['code'] == 'ot_shipping') $submit_data_core['x_freight'] = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_tax')      $submit_data_core['x_tax'] = round($order_totals[$i]['value'],2);
        } else {
          // handle credits
          global ${$order_totals[$i]['code']};
          if ((substr($order_totals[$i]['text'], 0, 1) == '-') || (isset(${$order_totals[$i]['code']}->credit_class) && ${$order_totals[$i]['code']}->credit_class == true)) {
            $submit_data_core['discount_amount'] += round($order_totals[$i]['value'], 2);
          }
        }
      }
    }

    // force conversion to supported currencies
    $exchange_factor = 1;
    if ($order->info['currency'] != $this->gateway_currency) {
      global $currencies;
      $exchange_factor = $currencies->get_value($this->gateway_currency);
      $submit_data_core['x_amount'] = round($order->info['total'] * $exchange_factor, 2);
      if (isset($submit_data_core['x_freight'])) $submit_data_core['x_freight'] = round($submit_data_core['x_freight'] * $exchange_factor, 2);
      if (isset($submit_data_core['x_tax'])) $submit_data_core['x_tax'] = round($submit_data_core['x_tax'] * $exchange_factor, 2);
      if (isset($submit_data_core['discount_amount'])) $submit_data_core['discount_amount'] = round($submit_data_core['discount_amount'] * $exchange_factor, 2);
      $submit_data_core['x_currency_code'] = $this->gateway_currency;
      $submit_data_core['x_description'] .= ' (Converted from: ' . round($order->info['total'] * $order->info['currency_value'], 2) . ' ' . $order->info['currency'] . ')';
    }


// to test a decline
//$submit_data_core['x_amount'] = 5234; // 5201=bad card, 5234=duplicate


    // Add line-item data to transaction
    $items = '';
    $item_log = array();
    if (sizeof($order->products) < 100) {
      $delim = '<|>';
      $product_code = $commodity_code = ''; // not submitted
      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        $p = $order->products[$i];
        // Item ID<|>Item Title<|>Item Description<|>Quantity<|>Unit Price<|>Taxable (Y or N)<|>Product Code<|>Commodity Code<|>Unit of Measure<|>Tax Rate<|>Tax Type<|>Tax Amount<|>Discount Indicator<|>Discount Amount<|>Line Item Total
        $line = $p['model'] . $delim . $p['name'] . $delim . $p['name'] . $delim . $p['qty'] . $delim . round($p['final_price'] * $exchange_factor,2) . $delim;
        $line .= (is_array($p['tax_groups']) && sizeof($p['tax_groups']) ? 'Y' : 'N') . $delim;
        $line .= $product_code . $delim . $commodity_code . $delim . '' . $delim;
        $line .= $p['tax'] . $delim . '' . round(zen_calculate_tax($p['final_price'] * $exchange_factor, $p['tax']),2) . $delim;
        $line .= '' . $delim . '' . $delim;
        $line .= round(zen_add_tax($p['final_price'] * $exchange_factor, $p['tax']) * $p['qty'],2);

        $items .= zen_draw_hidden_field('x_line_item', $line);
        $item_log[] = $line;
      }
    }

    $this->submit_extras = array();
    $this->notify('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_PRESUBMIT_HOOK');
    unset($this->submit_extras['x_login']);
    if (sizeof($this->submit_extras)) $submit_data_core = array_merge($submit_data_core, $this->submit_extras);

    $submit_data_security = $this->hmacAuthorizationToken($submit_data_core['x_amount'], $submit_data_core['x_currency_code']);

    $submit_data_offline = array(
      'x_show_form' => 'PAYMENT_FORM',
      'x_receipt_link_method' => 'AUTO-POST',
      'x_receipt_link_text' => 'Click here to complete your order.',
      'x_receipt_link_url' => zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false),

// By using AUTO-POST, relay isn't needed, and therefore requires less configuration in the merchant account settings
//       'x_relay_response' => 'TRUE',
//       'x_relay_URL' => zen_href_link(FILENAME_CHECKOUT_PROCESS, 'action=confirm', 'SSL', true, false),
       );

    $submit_data_extras = array();
    // The following can (and SHOULD) be set in the merchant account admin area instead of here
    if (defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_LOGO_URL')) {
      $submit_data_extras['x_logo_url'] = MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_LOGO_URL;
    }

    $submit_data = array_merge($submit_data_core, $submit_data_security, $submit_data_offline, $submit_data_extras);

    if (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE == 'Test') $submit_data['x_test_request'] = 'TRUE';

    $submit_data[zen_session_name()] = zen_session_id();

    $process_button_string = "\n";
    foreach($submit_data as $key => $value) {
      $process_button_string .= zen_draw_hidden_field($key, $value) . "\n";
    }
    $process_button_string .= $items . "\n";

    // prepare a copy of submitted data for error-reporting purposes
    $this->reportable_submit_data = $submit_data;
    $this->reportable_submit_data['items'] = $item_log;

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
    $this->authorize['HashValidationValue'] = $this->calc_md5_response($this->authorize['x_trans_id'], number_format($this->authorize['x_amount'], 2, '.', ''));
    $this->authorize['HashMatchStatus'] = ($this->authorize['x_MD5_Hash'] == $this->authorize['HashValidationValue']) ? 'PASS' : 'FAIL';

    $this->notify('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_POSTSUBMIT_HOOK', $this->authorize);
    $this->_debugActions($this->authorize, 'Response-Data', '', zen_session_id());

    // if in 'echo' mode, dump the returned data to the browser and stop execution
    if (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_DEBUGGING == 'echo') {
      echo 'Returned Response Codes:<br /><pre>' . print_r($_POST, true) . '</pre><br />';
      die('Press the BACK button in your browser to return to the previous page.');
    }

    if ($this->authorize['x_response_code'] == '1' && $this->authorize['x_MD5_Hash'] == $this->authorize['HashValidationValue']) {
      $order->info['cc_type'] = $this->authorize['TransactionCardType'];
      $order->info['cc_number'] = $this->authorize['Card_Number'];
      $order->info['cc_owner'] = $this->authorize['x_first_name'] . ' ' . $this->authorize['x_last_name'];
      $this->auth_code = $this->authorize['x_auth_code'];
      $this->transaction_id = $this->authorize['x_trans_id'];
//       $_SESSION['payment_method_messages'] = nl2br($this->authorize['exact_ctr']); // added to order-comments in after_process()
      return true;
    }
    if ($this->authorize['x_response_code'] == '2') {
      $messageStack->add_session('checkout_payment', $this->authorize['x_response_reason_text'] . ' ... ' . MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_DECLINED_MESSAGE . '<pre>' .  $this->authorize['exact_ctr'] . '</pre>' . $this->authorize['Bank_Message'], 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }
    // Code 3 or anything else is an error
    $messageStack->add_session('checkout_payment', MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_ERROR_MESSAGE . ' ' . $this->authorize['x_response_reason_text'] . ' ' . $this->authorize['Bank_Message'], 'error');
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
  }

  /**
   * Add receipt and transaction id to order-status-history (order comments)
   *
   * @return boolean
   */
  function after_process() {
    global $insert_id, $order, $currencies;
    $this->notify('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_POSTPROCESS_HOOK');

    zen_update_orders_history($insert_id, $this->authorize['exact_ctr'], null, $this->order_status, 0);

    $comment = 'Credit Card payment.  AUTH: ' . $this->auth_code . ' TransID: ' . $this->transaction_id;
    if ($order->info['currency'] != $this->gateway_currency) {
      $comment .= ' (' . round($order->info['total'] * $currencies->get_value($this->gateway_currency), 2) . ' ' . $this->gateway_currency . ')';
    }
    zen_update_orders_history($insert_id, $comment, null, $this->order_status, -1);

    return false;
  }
  /**
   * Check to see whether module is installed
   *
   * @return boolean
   */
  function check() {
    global $db;
    // install newer switches, if relevant
    if (defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS') && !defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ENABLE_LEVEL3')) {
            $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Level 3 Support', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ENABLE_LEVEL3', 'No', 'Should transactions be sent with Level 3 Processing enabled? (This is usually only to support Government cards) (You must enable Level 3 processing in your account Terminal and Hosted Page settings, else this will result in errors and reversals.)', '6', '0', 'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
    }
    if (!isset($this->_check)) {
      $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS'");
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
    if (defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS')) {
      $messageStack->add_session('First Data Payment Pages module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=firstdata'));
      return 'failed';
    }
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable First Data Hosted Payment Pages Module', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS', 'True', 'Do you want to accept Hosted Checkout payments via First Data Payment Pages?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_SORT_ORDER', '0', 'Sort order of displaying payment options to the customer. Lowest is displayed first.', '6', '0', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ORDER_STATUS_ID', '2', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Payment Page ID', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_PAGEID', 'testing', 'The Payment Page ID assigned in your First Data Hosted Payment Pages Console. <br><em>NOTE: If any &amp; symbols are used here, then anytime you make edits you may need to change them from &amp;amp; to just &amp;</em>', '6', '0', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Transaction Key', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TXNKEY', 'Test', 'Transaction Key (from Payment Page Settings, under 9:Security)', '6', '0', now(), 'zen_cfg_password_display')");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Response Key', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_RESPONSEKEY', '*Enter the Response Key here*', 'Response Key is used to verify responses from processed payments to ensure they are authentic. (From Payment Page Settings, under 9:Security)', '6', '0', now(), 'zen_cfg_password_display')");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Mode', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE', 'Test', 'Transaction mode used for processing orders.<br><strong>Production</strong>=Live processing with real account credentials<br><strong>Test</strong>=Simulations with real account credentials<br><strong>Sandbox</strong>=use special sandbox transaction key to do special testing of success/fail transaction responses (obtain sandbox credentials via <a href=\"https://provisioning.demo.globalgatewaye4.firstdata.com/signup/\" target=\"blank\">First Data Provisioning</a>)', '6', '0', 'zen_cfg_select_option(array(\'Test\', \'Production\', \'Sandbox\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Authorization Type', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_AUTHORIZATION_TYPE', 'Capture', 'Do you want submitted credit card transactions to be authorized only, or captured immediately?', '6', '0', 'zen_cfg_select_option(array(\'Authorize\', \'Capture\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Notifications', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_EMAIL_CUSTOMER', 'False', 'Should First Data email a payment receipt to the customer?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Mode', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_DEBUGGING', 'Alerts Only', 'Would you like to enable debug mode?  A  detailed log of failed transactions may be emailed to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Alerts Only\', \'Log File\', \'Log and Email\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Supported', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_CURRENCY', 'USD', 'Which currency is your First Data Payment Page Account configured to accept?<br>(Purchases in any other currency will be pre-converted to this currency before submission using the exchange rates in your store admin.)', '6', '0', 'zen_cfg_select_option(array(\'USD\', \'CAD\', \'GBP\', \'EUR\', \'AUD\', \'NZD\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('HMAC Calculation', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_HMAC_MODE', 'MD5', 'The HMAC Encryption Type (from Payment Page Settings, under 9:Security)', '6', '0', 'zen_cfg_select_option(array(\'MD5\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Level 3 Support', 'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ENABLE_LEVEL3', 'No', 'Should transactions be sent with Level 3 Processing enabled? (This is usually only to support Government cards) (You must enable Level 3 processing in your account Terminal and Hosted Page settings, else this will result in errors and reversals.)', '6', '0', 'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
  }
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $db;
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_SORT_ORDER',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ORDER_STATUS_ID',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ZONE',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_PAGEID',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TXNKEY',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_RESPONSEKEY',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_CURRENCY',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_AUTHORIZATION_TYPE',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_EMAIL_CUSTOMER',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_ENABLE_LEVEL3',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_HMAC_MODE',
            'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_DEBUGGING');
  }

  protected function hmacAuthorizationToken($amount, $currency)
  {
    $nonce = (string)hexdec(bin2hex(openssl_random_pseudo_bytes(4, $cstrong)));
    $timestamp = (string)time(); //time stamp as a string
    $data = html_entity_decode(MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_PAGEID) . "^" . $nonce . "^" . $timestamp . "^" . $amount . "^" . $currency;
    $hashAlgorithm = "md5"; // According to First Data they recommend MD5 here
    $hmac = hash_hmac($hashAlgorithm, $data, MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TXNKEY, false); // HMAC Hash in hex
    return array(
            'x_fp_hash' => $hmac,
            'x_fp_sequence' => $nonce,
            'x_fp_timestamp' => $timestamp,
    );
  }

  /**
   * Calculate validity of relay response
   */
  protected function calc_md5_response($trans_id = '', $amount = '') {
    if ($amount == '' || $amount == '0') $amount = '0.00';
    return md5(MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_RESPONSEKEY . html_entity_decode(MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_PAGEID) . $trans_id . $amount);
  }

  /**
   * Used to do any debug logging / tracking / storage as required.
   */
  protected function _debugActions($response, $mode, $order_time= '') {
    if ($order_time == '') $order_time = date("F j, Y, g:i a");
    $response['url'] = $this->form_action_url;
    $this->reportable_submit_data['url'] = $this->form_action_url;
    if (isset($response['x_login'])) $response['x_login'] = '*******' . substr($response['x_login'], -8);
    if (isset($this->reportable_submit_data['x_login'])) $this->reportable_submit_data['x_login'] = '*******' . substr($response['x_login'], -8);

    $errorMessage = date('M-d-Y h:i:s') .
                    "\n=================================\n\n";
    if ($mode == 'Submit-Data') $errorMessage .=
                    'Sent to First Data Hosted Payments Page: ' . print_r($this->reportable_submit_data, true) . "\n\n";
    if ($mode == 'Response-Data') $errorMessage .=
                    'Response Code: ' . $response['x_response_code'] . ".\nResponse Text: " . $response['x_response_reason_text'] . "\n\n" .
                    ($response['x_response_code'] == 2 && $response['x_response_reason_code'] == 4 ? ' NOTICE: Card should be picked up - possibly stolen ' : '') .
                    ($response['x_response_code'] == 3 && $response['x_response_reason_code'] == 11 ? ' DUPLICATE TRANSACTION ATTEMPT ' : '') .
                    'Results Received back from First Data: ' . print_r($response, true) . "\n\n";
    // store log file if log mode enabled
    if (stristr(MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_DEBUGGING, 'Log') || strstr(MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_DEBUGGING, 'All')) {
      $key = ($response['x_trans_id'] != '' ? $response['x_trans_id'] . '_' : '') . time() . '_' . zen_create_random_value(4);
      $file = $this->_logDir . '/' . 'FirstData_Debug_' . $key . '.log';
      $fp = @fopen($file, 'a');
      @fwrite($fp, $errorMessage);
      @fclose($fp);
    }
    // send email alerts only if in alert mode or if email specifically requested as logging mode
    if ((isset($response['x_response_code']) && $response['x_response_code'] != '1' && stristr(MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_DEBUGGING, 'Alerts')) || stristr(MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_DEBUGGING, 'Email')) {
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'First Data HostedPayments Alert ' . $response['x_invoice_num'] . ' ' . date('M-d-Y h:i:s') . ' ' . $response['x_trans_id'], $errorMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorMessage)), 'debug');
    }
  }
}

// for backward compatibility with older ZC versions before v152 which didn't have this function:
if (!function_exists('plugin_version_check_for_updates')) {
  function plugin_version_check_for_updates($plugin_file_id = 0, $version_string_to_compare = '', $strict_zc_version_compare = false)
  {
    if ($plugin_file_id == 0) return false;
    $new_version_available = false;
    $lookup_index = $errno = 0;
    $response = $error = '';
    $url1 = 'https://plugins.zen-cart.com/versioncheck/'.(int)$plugin_file_id;
    $url2 = 'https://www.zen-cart.com/versioncheck/'.(int)$plugin_file_id;

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 9);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Plugin Version Check [' . (int)$plugin_file_id . '] ' . HTTP_SERVER);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        if ($errno > 0) {
          trigger_error('CURL error checking plugin versions: ' . $errno . ':' . $error . "\nTrying http instead.");
          curl_setopt($ch, CURLOPT_URL, str_replace('tps:', 'tp:', $url1));
          $response = curl_exec($ch);
          $error = curl_error($ch);
          $errno = curl_errno($ch);
        }
        if ($errno > 0) {
          trigger_error('CURL error checking plugin versions: ' . $errno . ':' . $error . "\nTrying www instead.");
          curl_setopt($ch, CURLOPT_URL, str_replace('tps:', 'tp:', $url2));
          $response = curl_exec($ch);
          $error = curl_error($ch);
          $errno = curl_errno($ch);
        }
        curl_close($ch);
    } else {
        $errno = 9999;
        $error = 'curl_init not found in PHP';
    }
    if ($errno > 0 || $response == '') {
      trigger_error('CURL error checking plugin versions: ' . $errno . ':' . $error . "\nTrying file_get_contents() instead.");
      $ctx = stream_context_create(array('http' => array('timeout' => 5)));
      $response = file_get_contents($url1, null, $ctx);
      if ($response === false) {
        trigger_error('file_get_contents() error checking plugin versions.' . "\nTrying http instead.");
        $response = file_get_contents(str_replace('tps:', 'tp:', $url1), null, $ctx);
      }
      if ($response === false) {
        trigger_error('file_get_contents() error checking plugin versions.' . "\nAborting.");
        return false;
      }
    }

    $data = json_decode($response, true);
    if (!$data || !is_array($data)) return false;
    // compare versions
    if (strcmp($data[$lookup_index]['latest_plugin_version'], $version_string_to_compare) > 0) $new_version_available = true;
    // check whether present ZC version is compatible with the latest available plugin version
    $zc_version = PROJECT_VERSION_MAJOR . '.' . preg_replace('/[^0-9.]/', '', PROJECT_VERSION_MINOR);
    if ($strict_zc_version_compare) $zc_version = PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
    if (!in_array('v'. $zc_version, $data[$lookup_index]['zcversions'])) $new_version_available = false;
    return ($new_version_available) ? $data[$lookup_index] : false;
  }
}
