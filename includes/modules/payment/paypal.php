<?php
/**
 * paypal.php payment module class for PayPal Payments Standard (IPN) method
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 23 Modified in v1.5.7 $
 */

define('MODULE_PAYMENT_PAYPAL_TAX_OVERRIDE', 'true');

/**
 *  ensure dependencies are loaded
 */
  include_once((IS_ADMIN_FLAG === true ? DIR_FS_CATALOG_MODULES : DIR_WS_MODULES) . 'payment/paypal/paypal_functions.php');

/**
 * paypal.php payment module class for PayPal Payments Standard (IPN) method
 *
 */
class paypal extends base {
  /**
   * string representing the payment method
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
    * constructor
    *
    * @param int $paypal_ipn_id
    * @return paypal
    */
  function __construct($paypal_ipn_id = '') {
    global $order, $messageStack;
    $this->code = 'paypal';
    $this->codeVersion = '1.5.7';
    $this->sort_order = defined('MODULE_PAYMENT_PAYPAL_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_SORT_ORDER : null;
    $this->enabled = (defined('MODULE_PAYMENT_PAYPAL_STATUS') && MODULE_PAYMENT_PAYPAL_STATUS == 'True');
    if (IS_ADMIN_FLAG === true) {
      // Payment Module title in Admin
      $this->title = STORE_COUNTRY != '223' ? MODULE_PAYMENT_PAYPAL_TEXT_ADMIN_TITLE_NONUSA : MODULE_PAYMENT_PAYPAL_TEXT_ADMIN_TITLE;
      if (IS_ADMIN_FLAG === true && defined('MODULE_PAYMENT_PAYPAL_IPN_DEBUG') && MODULE_PAYMENT_PAYPAL_IPN_DEBUG != 'Off') $this->title .= '<span class="alert"> (debug mode active)</span>';
      if (IS_ADMIN_FLAG === true && defined('MODULE_PAYMENT_PAYPAL_TESTING') && MODULE_PAYMENT_PAYPAL_TESTING == 'Test') $this->title .= '<span class="alert"> (dev/test mode active)</span>';
    } else {
      $this->title = MODULE_PAYMENT_PAYPAL_TEXT_CATALOG_TITLE; // Payment Module title in Catalog
    }

    if (null === $this->sort_order) return false;

    $this->description = MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION;
    if (defined('MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID;
    }

    if (is_object($order)) $this->update_status();

    /**
     * Determine which PayPal URL to direct the customer's browser to when needed
     */
    if (MODULE_PAYMENT_PAYPAL_HANDLER == 'live' || !strstr(MODULE_PAYMENT_PAYPAL_HANDLER, 'sandbox')) {
      $this->form_action_url = 'https://www.paypal.com/cgi-bin/webscr';
    } else {
      $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    }

    if (PROJECT_VERSION_MAJOR != '1' && substr(PROJECT_VERSION_MINOR, 0, 3) != '5.6') $this->enabled = false;

    // verify table structure
    if (IS_ADMIN_FLAG === true) $this->tableCheckup();
  }
  /**
   * calculate zone matches and flag settings to determine whether this module should display to customers or not
    *
    */
  function update_status() {
    global $order, $db;

    if ($this->enabled && (int)MODULE_PAYMENT_PAYPAL_ZONE > 0 && isset($order->billing['country']['id'])) {
      $check_flag = false;
      $check_query = $db->Execute("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_ZONE . "' AND zone_country_id = '" . (int)$order->billing['country']['id'] . "' ORDER BY zone_id");
      while (!$check_query->EOF) {
        if ($check_query->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check_query->fields['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
        $check_query->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
  }
  /**
   * JS validation which does error-checking of data-entry if this module is selected for use
   * (Number, Owner, and CVV Lengths)
   *
   * @return string
    */
  function javascript_validation() {
    return false;
  }
  /**
   * Displays payment method name along with Credit Card Information Submission Fields (if any) on the Checkout Payment Page
   *
   * @return array
    */
  function selection() {
    return array('id' => $this->code,
                 'module' => MODULE_PAYMENT_PAYPAL_TEXT_CATALOG_LOGO,
                 'icon' => MODULE_PAYMENT_PAYPAL_TEXT_CATALOG_LOGO
                 );
  }
  /**
   * Normally evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   * Since paypal module is not collecting info, it simply skips this step.
   *
   * @return boolean
   */
  function pre_confirmation_check() {
    return false;
  }
  /**
   * Display Credit Card Information on the Checkout Confirmation Page
   * Since none is collected for paypal before forwarding to paypal site, this is skipped
   *
   * @return boolean
    */
  function confirmation() {
    return false;
  }
  /**
   * Build the data and actions to process when the "Submit" button is pressed on the order-confirmation screen.
   * This sends the data to the payment gateway for processing.
   * (These are hidden fields on the checkout confirmation page)
   *
   * @return string
    */
  function process_button() {
    global $db, $order, $currencies, $currency;
    $options = array();
    $optionsCore = array();
    $optionsPhone = array();
    $optionsShip = array();
    $optionsLineItems = array();
    $optionsAggregate = array();
    $optionsTrans = array();
    $buttonArray = array();


    // save the session stuff permanently in case paypal loses the session
    $_SESSION['ppipn_key_to_remove'] = session_id();
    $db->Execute("DELETE FROM " . TABLE_PAYPAL_SESSION . " WHERE session_id = '" . zen_db_input($_SESSION['ppipn_key_to_remove']) . "'");

    $sql = "insert into " . TABLE_PAYPAL_SESSION . " (session_id, saved_session, expiry) values (
            '" . zen_db_input($_SESSION['ppipn_key_to_remove']) . "',
            '" . base64_encode(serialize($_SESSION)) . "',
            '" . (time() + (1*60*60*24*2)) . "')";

    $db->Execute($sql);

    $my_currency = select_pp_currency();
    $this->transaction_currency = $my_currency;

    $this->totalsum = $order->info['total'] = zen_round($order->info['total'], 2);
    $this->transaction_amount = zen_round($this->totalsum * $currencies->get_value($my_currency), $currencies->get_decimal_places($my_currency));

    $telephone = preg_replace('/\D/', '', $order->customer['telephone']);
    if ($telephone != '') {
      $optionsPhone['H_PhoneNumber'] = $telephone;
      if (in_array($order->customer['country']['iso_code_2'], array('US','CA'))) {
        $optionsPhone['night_phone_a'] = substr($telephone,0,3);
        $optionsPhone['night_phone_b'] = substr($telephone,3,3);
        $optionsPhone['night_phone_c'] = substr($telephone,6,4);
        $optionsPhone['day_phone_a'] = substr($telephone,0,3);
        $optionsPhone['day_phone_b'] = substr($telephone,3,3);
        $optionsPhone['day_phone_c'] = substr($telephone,6,4);
    } else {
        $optionsPhone['night_phone_b'] = $telephone;
        $optionsPhone['day_phone_b'] = $telephone;
      }
    }

    $optionsCore = array(
                   'lc' => $this->getLanguageCode(),
//                   'lc' => $order->customer['country']['iso_code_2'],
                   'charset' => CHARSET,
                   'page_style' => MODULE_PAYMENT_PAYPAL_PAGE_STYLE,
                   'custom' => zen_session_name() . '=' . zen_session_id(),
                   'business' => MODULE_PAYMENT_PAYPAL_BUSINESS_ID,
                   'return' => zen_href_link(FILENAME_CHECKOUT_PROCESS, 'referer=paypal', 'SSL'),
                   'cancel_return' => zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
                   'shopping_url' => zen_href_link(FILENAME_SHOPPING_CART, '', 'SSL'),
                   'notify_url' => zen_href_link('ipn_main_handler.php', '', 'SSL',false,false,true),
                   'redirect_cmd' => '_xclick','rm' => 2,'bn' => 'zencart','mrb' => 'R-6C7952342H795591R','pal' => '9E82WJBKKGPLQ',
                   );
    $optionsCust = array(
                   'first_name' => replace_accents($order->customer['firstname']),
                   'last_name' => replace_accents($order->customer['lastname']),
                   'address1' => replace_accents($order->customer['street_address']),
                   'city' => replace_accents($order->customer['city']),
                   'state' => zen_get_zone_code($order->customer['country']['id'], $order->customer['zone_id'], $order->customer['state']),
                   'zip' => $order->customer['postcode'],
                   'country' => $order->customer['country']['iso_code_2'],
                   'email' => $order->customer['email_address'],
                   );
    // address line 2 is optional
    if ($order->customer['suburb'] != '') $optionsCust['address2'] = $order->customer['suburb'];
    // different format for Japanese address layout:
    if ($order->customer['country']['iso_code_2'] == 'JP') $optionsCust['zip'] = substr($order->customer['postcode'], 0, 3) . '-' . substr($order->customer['postcode'], 3);
    if (MODULE_PAYMENT_PAYPAL_ADDRESS_REQUIRED == 2) {
      $optionsCust = array(
                   'first_name' => replace_accents($order->delivery['firstname'] != '' ? $order->delivery['firstname'] : $order->billing['firstname']),
                   'last_name' => replace_accents($order->delivery['lastname'] != '' ? $order->delivery['lastname'] : $order->billing['lastname']),
                   'address1' => replace_accents($order->delivery['street_address'] != '' ? $order->delivery['street_address'] : $order->billing['street_address']),
                   'city' => replace_accents($order->delivery['city'] != '' ? $order->delivery['city'] : $order->billing['city']),
                   'state' => ($order->delivery['country']['id'] != '' ? zen_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']) : zen_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state'])),
                   'zip' => ($order->delivery['postcode'] != '' ? $order->delivery['postcode'] : $order->billing['postcode']),
                   'country' => ($order->delivery['country']['title'] != '' ? $order->delivery['country']['title'] : $order->billing['country']['title']),
                   'country_code' => ($order->delivery['country']['iso_code_2'] != '' ? $order->delivery['country']['iso_code_2'] : $order->billing['country']['iso_code_2']),
                   'email' => $order->customer['email_address'],
                   );
      if ($order->delivery['suburb'] != '') $optionsCust['address2'] = $order->delivery['suburb'];
      if ($order->delivery['country']['iso_code_2'] == 'JP') $optionsCust['zip'] = substr($order->delivery['postcode'], 0, 3) . '-' . substr($order->delivery['postcode'], 3);
    }
    $optionsShip['no_shipping'] = MODULE_PAYMENT_PAYPAL_ADDRESS_REQUIRED;
    if (MODULE_PAYMENT_PAYPAL_ADDRESS_OVERRIDE == '1') $optionsShip['address_override'] = MODULE_PAYMENT_PAYPAL_ADDRESS_OVERRIDE;
    // prepare cart contents details where possible
    if (MODULE_PAYMENT_PAYPAL_DETAILED_CART == 'Yes') $optionsLineItems = ipn_getLineItemDetails($my_currency);
    if (sizeof($optionsLineItems) > 0) {
      $optionsLineItems['cmd'] = '_cart';
//      $optionsLineItems['num_cart_items'] = sizeof($order->products);
      if (isset($optionsLineItems['shipping'])) {
        $optionsLineItems['shipping_1'] = $optionsLineItems['shipping'];
        unset($optionsLineItems['shipping']);
      }
      unset($optionsLineItems['subtotal']);
      // if line-item details couldn't be kept due to calculation mismatches or discounts etc, default to aggregate mode
      if (!isset($optionsLineItems['item_name_1']) || $optionsLineItems['creditsExist'] == TRUE) $optionsLineItems = array();
      //if ($optionsLineItems['amount'] != $this->transaction_amount) $optionsLineItems = array();
      // debug:
      //ipn_debug_email('Line Item Details (if blank, this means there was a data mismatch or credits applied, and thus bypassed): ' . "\n" . print_r($optionsLineItems, true));
      unset($optionsLineItems['creditsExist']);
    }
    $optionsAggregate = array(
                   'cmd' => '_ext-enter',
                   'item_name' => MODULE_PAYMENT_PAYPAL_PURCHASE_DESCRIPTION_TITLE,
                   'item_number' => MODULE_PAYMENT_PAYPAL_PURCHASE_DESCRIPTION_ITEMNUM,
                   //'num_cart_items' => sizeof($order->products),
                   'amount' => round($this->transaction_amount, $currencies->get_decimal_places($my_currency)),
                   'shipping' => '0.00',
                    );
    if (MODULE_PAYMENT_PAYPAL_TAX_OVERRIDE == 'true') $optionsAggregate['tax'] = '0.00';
    if (MODULE_PAYMENT_PAYPAL_TAX_OVERRIDE == 'true') $optionsAggregate['tax_cart'] = '0.00';
    $optionsTrans = array(
                   'upload' => (int)(sizeof($order->products) > 0),
                   'currency_code' => $my_currency,
//                   'paypal_order_id' => $paypal_order_id,
                   //'no_note' => '1',
                   //'invoice' => '',
                    );

    // if line-item info is invalid, use aggregate:
    if (sizeof($optionsLineItems) > 0) $optionsAggregate = $optionsLineItems;

    if (defined('MODULE_PAYMENT_PAYPAL_LOGO_IMAGE')) $optionsCore['cpp_logo_image'] = urlencode(MODULE_PAYMENT_LOGO_IMAGE);
    if (defined('MODULE_PAYMENT_PAYPAL_CART_BORDER_COLOR')) $optionsCore['cpp_cart_border_color'] = MODULE_PAYMENT_PAYPAL_CART_BORDER_COLOR;

    // prepare submission
    $options = array_merge($optionsCore, $optionsCust, $optionsPhone, $optionsShip, $optionsTrans, $optionsAggregate);
    //ipn_debug_email('Keys for submission: ' . print_r($options, true));

    // build the button fields
    foreach ($options as $name => $value) {
      // remove quotation marks
      $value = str_replace('"', '', $value);
      // check for invalid chars
      if (preg_match('/[^a-zA-Z_0-9]/', $name)) {
        ipn_debug_email('datacheck - ABORTING - preg_match found invalid submission key: ' . $name . ' (' . $value . ')');
        break;
      }
      // do we need special handling for & and = symbols?
      //if (strpos($value, '&') !== false || strpos($value, '=') !== false) $value = urlencode($value);

      $buttonArray[] = zen_draw_hidden_field($name, $value);
    }
    $process_button_string = "\n" . implode("\n", $buttonArray) . "\n";

    $_SESSION['paypal_transaction_info'] = array($this->transaction_amount, $this->transaction_currency);
    return $process_button_string;
  }
  /**
   * Determine the language to use when redirecting to the PayPal site
   * Order of selection: locale for current language, current-language-code, delivery-country, billing-country, store-country
   */
  function getLanguageCode() {
    global $order, $locales;
    $allowed_country_codes = array('US', 'AU', 'DE', 'FR', 'IT', 'GB', 'ES', 'AT', 'BE', 'CA', 'CH', 'CN', 'NL', 'PL', 'PT', 'BR', 'RU');
    $allowed_language_codes = array('da_DK', 'he_IL', 'id_ID', 'ja_JP', 'no_NO', 'pt_BR', 'ru_RU', 'sv_SE', 'th_TH', 'tr_TR', 'zh_CN', 'zh_HK', 'zh_TW');

    $lang_code = '';
    $user_locale_info = array();
    if (isset($locales) && is_array($locales)) {
      $user_locale_info = $locales;
    }
    $user_locale_info[] = strtoupper($_SESSION['languages_code']);
    $shippingISO = zen_get_countries($order->delivery['country']['id'], true);
    $user_locale_info[] = strtoupper($shippingISO['countries_iso_code_2']);
    $billingISO = zen_get_countries($order->billing['country']['id'], true);
    $user_locale_info[] = strtoupper($billingISO['countries_iso_code_2']);
    $custISO = zen_get_countries($order->customer['country']['id'], true);
    $user_locale_info[] = strtoupper($custISO['countries_iso_code_2']);
    $storeISO = zen_get_countries(STORE_COUNTRY, true);
    $user_locale_info[] = strtoupper($storeISO['countries_iso_code_2']);

    $to_match = array_map('strtoupper', array_merge($allowed_country_codes, $allowed_language_codes));
    foreach($user_locale_info as $val) {
      if (in_array(strtoupper($val), $to_match)) {
        if (strtoupper($val) == 'EN' && isset($locales) && $locales[0] == 'en_GB') $val = 'GB';
        if (strtoupper($val) == 'EN') $val = 'US';
        return $val;
      }
    }
  }
  /**
   * Store transaction info to the order and process any results that come back from the payment gateway
   */
  function before_process() {
    global $order_total_modules;
    list($this->transaction_amount, $this->transaction_currency) = $_SESSION['paypal_transaction_info'];
    unset($_SESSION['paypal_transaction_info']);
    if (isset($_GET['referer']) && $_GET['referer'] == 'paypal') {
      $this->notify('NOTIFY_PAYMENT_PAYPAL_RETURN_TO_STORE', $_GET);
      if (defined('MODULE_PAYMENT_PAYPAL_PDTTOKEN') && MODULE_PAYMENT_PAYPAL_PDTTOKEN != '' && isset($_GET['tx']) && $_GET['tx'] != '') {
        $pdtStatus = $this->_getPDTresults($this->transaction_amount, $this->transaction_currency, $_GET['tx']);
      } else {
        $pdtStatus = false;
      }
      if ($pdtStatus == false) {
        $_SESSION['cart']->reset(true);
        unset($_SESSION['sendto']);
        unset($_SESSION['billto']);
        unset($_SESSION['shipping']);
        unset($_SESSION['payment']);
        unset($_SESSION['comments']);
        unset($_SESSION['cot_gv']);
        $order_total_modules->clear_posts();//ICW ADDED FOR CREDIT CLASS SYSTEM
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
      } else {
        // PDT was good, so delete IPN session from PayPal table -- housekeeping.
        global $db;
        $db->Execute("DELETE FROM " . TABLE_PAYPAL_SESSION . " WHERE session_id = '" . zen_db_input($_SESSION['ppipn_key_to_remove']) . "'");
        unset($_SESSION['ppipn_key_to_remove']);
        $_SESSION['paypal_transaction_PDT_passed'] = true;
        return true;
      }
    } else {
      $this->notify('NOTIFY_PAYMENT_PAYPAL_CANCELLED_DURING_CHECKOUT', $_GET);
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
  }
  /**
    * Checks referrer
    *
    * @param string $zf_domain
    * @return boolean
    */
  function check_referrer($zf_domain) {
    return true;
  }
  /**
    * Build admin-page components
    *
    * @param int $zf_order_id
    * @return string
    */
  function admin_notification($zf_order_id) {
    global $db;
    $output = '';
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = '" . (int)$zf_order_id . "' ORDER BY paypal_ipn_id DESC LIMIT 1";
    $ipn = $db->Execute($sql);
    if ($ipn->RecordCount() > 0 && file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/paypal_admin_notification.php')) require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/paypal_admin_notification.php');
    return $output;
  }
  /**
   * Post-processing activities
   * When the order returns from the processor, if PDT was successful, this stores the results in order-status-history and logs data for subsequent reference
   *
   * @return boolean
    */
  function after_process() {
    global $insert_id, $order;
    if ($_SESSION['paypal_transaction_PDT_passed'] != true) {
      $_SESSION['order_created'] = '';
      unset($_SESSION['paypal_transaction_PDT_passed']);
      return false;
    } else {
    // PDT found order to be approved, so add a new OSH record for this order's PP details
      unset($_SESSION['paypal_transaction_PDT_passed']);

      $comments = 'PayPal status: ' . $this->pdtData['payment_status'] . ' ' . ' @ ' . $this->pdtData['payment_date'] . "\n" . ' Trans ID:' . $this->pdtData['txn_id'] . "\n" . ' Amount: ' . $this->pdtData['mc_gross'] . ' ' . $this->pdtData['mc_currency'] . '.';
      zen_update_orders_history($insert_id, $comments, null, $this->order_status, 0);

      ipn_debug_email('PDT NOTICE :: Order added: ' . $insert_id . "\n" . $comments);

      // store the PayPal order meta data -- used for later matching and back-end processing activities
      $sql_data_array = array('order_id' => $insert_id,
                          'txn_type' => $this->pdtData['txn_type'],
                          'module_name' => $this->code . ' ' . $this->codeVersion,
                          'module_mode' => 'PDT',
                          'reason_code' => $this->pdtData['reasoncode'],
                          'payment_type' => $this->pdtData['payment_type'],
                          'payment_status' => $this->pdtData['payment_status'],
                          'pending_reason' => $this->pdtData['pendingreason'],
                          'invoice' => $this->pdtData['invoice'],
                          'first_name' => $this->pdtData['first_name'],
                          'last_name' => $this->pdtData['last_name'],
                          'payer_business_name' => $order->billing['company'],
                          'address_name' => $order->billing['name'],
                          'address_street' => $order->billing['street_address'],
                          'address_city' => $order->billing['city'],
                          'address_state' => $order->billing['state'],
                          'address_zip' => $order->billing['postcode'],
                          'address_country' => $this->pdtData['residence_country'], // $order->billing['country']
                          'address_status' => $this->pdtData['address_status'],
                          'payer_email' => $this->pdtData['payer_email'],
                          'payer_id' => $this->pdtData['payer_id'],
                          'payer_status' => $this->pdtData['payer_status'],
                          'payment_date' => datetime_to_sql_format($this->pdtData['payment_date']),
                          'business' => $this->pdtData['business'],
                          'receiver_email' => $this->pdtData['receiver_email'],
                          'receiver_id' => $this->pdtData['receiver_id'],
                          'txn_id' => $this->pdtData['txn_id'],
                          'parent_txn_id' => $this->pdtData['parent_txn_id'],
                          'num_cart_items' => (float)$this->pdtData['num_cart_items'],
                          'mc_gross' => (float)$this->pdtData['mc_gross'],
                          'mc_fee' => (float)$this->pdtData['mc_fee'],
                          'mc_currency' => $this->pdtData['mc_currency'],
                          'settle_amount' => (float)$this->pdtData['settle_amount'],
                          'settle_currency' => $this->pdtData['settle_currency'],
                          'exchange_rate' => ($this->pdtData['exchange_rate'] > 0 ? $this->pdtData['exchange_rate'] : 1.0),
                          'notify_version' => (float)$this->pdtData['notify_version'],
                          'verify_sign' => $this->pdtData['verify_sign'],
                          'date_added' => 'now()',
                          'memo' => '{Successful PDT Confirmation - Record auto-generated by payment module}'
                         );
//TODO: $db->perform vs zen_db_perform
      zen_db_perform(TABLE_PAYPAL, $sql_data_array);
      ipn_debug_email('PDT NOTICE :: paypal table updated: ' . print_r($sql_data_array, true));
    }
  }

  /**
   * Check to see whether module is installed
   *
   * @return boolean
    */
  function check() {
    global $db;
    if (IS_ADMIN_FLAG === true) {
      global $sniffer;
      if ($sniffer->field_exists(TABLE_PAYPAL, 'zen_order_id'))  $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE COLUMN zen_order_id order_id int(11) NOT NULL default '0'");
    }
    if (!isset($this->_check)) {
      $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    if (defined('MODULE_PAYMENT_PAYPAL_HANDLER') && !in_array(MODULE_PAYMENT_PAYPAL_HANDLER, array('live', 'sandbox'))) {
      $val = (stristr(MODULE_PAYMENT_PAYPAL_HANDLER, 'sand')) ? 'sandbox' : 'live';
      $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_title = 'Live or Sandbox', configuration_value = '" . $val . "', configuration_description= '<strong>Live: </strong> Used to process Live transactions<br><strong>Sandbox: </strong>For developers and testing', set_function='zen_cfg_select_option(array(\'live\', \'sandbox\'), ' WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_HANDLER'";
      $db->Execute($sql);
    }
    return $this->_check;
  }
  /**
   * Install the payment module and its configuration settings
    *
    */
  function install() {
    global $db, $messageStack;
    if (defined('MODULE_PAYMENT_PAYPAL_STATUS')) {
      $messageStack->add_session('PayPal Payments Standard module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paypal', 'NONSSL'));
      return 'failed';
    }
    if (defined('MODULE_PAYMENT_PAYPALWPP_STATUS')) {
      $messageStack->add_session('NOTE: PayPal Express Checkout module already installed. You don\'t need Standard if you have Express installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paypalwpp', 'NONSSL'));
      return 'failed';
    }
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable PayPal Module', 'MODULE_PAYMENT_PAYPAL_STATUS', 'True', 'Do you want to accept PayPal payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Business ID', 'MODULE_PAYMENT_PAYPAL_BUSINESS_ID','".STORE_OWNER_EMAIL_ADDRESS."', 'Primary email address for your PayPal account.<br />NOTE: This must match <strong>EXACTLY </strong>the primary email address on your PayPal account settings.  It <strong>IS case-sensitive</strong>, so please check your PayPal profile preferences at paypal.com and be sure to enter the EXACT same primary email address here.', '6', '2', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Currency', 'MODULE_PAYMENT_PAYPAL_CURRENCY', 'Selected Currency', 'Which currency should the order be sent to PayPal as? <br />NOTE: if an unsupported currency is sent to PayPal, it will be auto-converted to USD.', '6', '3', 'zen_cfg_select_option(array(\'Selected Currency\', \'Only USD\', \'Only AUD\', \'Only CAD\', \'Only EUR\', \'Only GBP\', \'Only CHF\', \'Only CZK\', \'Only DKK\', \'Only HKD\', \'Only HUF\', \'Only JPY\', \'Only NOK\', \'Only NZD\', \'Only PLN\', \'Only SEK\', \'Only SGD\', \'Only THB\', \'Only MXN\', \'Only ILS\', \'Only PHP\', \'Only TWD\', \'Only BRL\', \'Only MYR\', \'Only TRY\', \'Only INR\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_PAYPAL_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '4', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Pending Notification Status', 'MODULE_PAYMENT_PAYPAL_PROCESSING_STATUS_ID', '" . DEFAULT_ORDERS_STATUS_ID .  "', 'Set the status of orders made with this payment module that are not yet completed to this value<br />(\'Pending\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID', '2', 'Set the status of orders made with this payment module that have completed payment to this value<br />(\'Processing\' recommended)', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refund Order Status', 'MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID', '1', 'Set the status of orders that have been refunded made with this payment module to this value<br />(\'Pending\' recommended)', '6', '7', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_PAYPAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '8', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Address Override', 'MODULE_PAYMENT_PAYPAL_ADDRESS_OVERRIDE', '1', 'If set to 1, the customer shipping address selected in Zen Cart will override the customer PayPal-stored address book. The customer will see their address from Zen Cart, but will NOT be able to edit it at PayPal.<br />(An invalid address will be treated by PayPal as not-supplied, or override=0)<br />0=No Override<br />1=ZC address overrides PayPal address choices', '6', '18', 'zen_cfg_select_option(array(\'0\',\'1\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Shipping Address Requirements?', 'MODULE_PAYMENT_PAYPAL_ADDRESS_REQUIRED', '2', 'The buyers shipping address. If set to 0 your customer will be prompted to include a shipping address. If set to 1 your customer will not be asked for a shipping address. If set to 2 your customer will be required to provide a shipping address.<br />0=Prompt<br />1=Not Asked<br />2=Required<br /><br /><strong>NOTE: If you allow your customers to enter their own shipping address, then MAKE SURE you PERSONALLY manually verify the PayPal confirmation details to verify the proper address when filling orders. When using PayPal Payments Standard (IPN), Zen Cart does not know if they choose an alternate shipping address at PayPal vs the one entered when placing an order.</strong>', '6', '20', 'zen_cfg_select_option(array(\'0\',\'1\',\'2\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Detailed Line Items in Cart', 'MODULE_PAYMENT_PAYPAL_DETAILED_CART', 'No', 'Do you want to give line-item details to PayPal?  If set to True, line-item details will be shared with PayPal if no discounts apply and if tax and shipping are simple. Otherwise an Aggregate cart summary will be sent.', '6', '22', 'zen_cfg_select_option(array(\'No\',\'Yes\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Page Style', 'MODULE_PAYMENT_PAYPAL_PAGE_STYLE', 'Primary', 'Sets the Custom Payment Page Style for payment pages. The value of page_style is the same as the Page Style Name you chose when adding or editing the page style. You can add and edit Custom Payment Page Styles from the Profile subtab of the My Account tab on the PayPal site. If you would like to always reference your Primary style, set this to \"primary.\" If you would like to reference the default PayPal page style, set this to \"paypal\".', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Live or Sandbox', 'MODULE_PAYMENT_PAYPAL_HANDLER', 'live', '<strong>Live: </strong> Used to process Live transactions<br><strong>Sandbox: </strong>For developers and testing', '6', '73', 'zen_cfg_select_option(array(\'live\', \'sandbox\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('PDT Token (Payment Data Transfer)', 'MODULE_PAYMENT_PAYPAL_PDTTOKEN', '', 'Enter your PDT Token value here in order to activate transactions immediately after processing (if they pass validation).', '6', '25', now(), 'zen_cfg_password_display')");
    // Paypal testing options here
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Mode', 'MODULE_PAYMENT_PAYPAL_IPN_DEBUG', 'Off', 'Enable debug logging? <br />NOTE: This can REALLY clutter your email inbox and use up disk space!<br />Logging goes to the /logs folder<br />Email goes to the store-owner address.<br />Email option NOT recommended.<br /><strong>Leave OFF for normal operation.</strong>', '6', '71', 'zen_cfg_select_option(array(\'Off\',\'Log File\',\'Log and Email\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Debug Email Address', 'MODULE_PAYMENT_PAYPAL_DEBUG_EMAIL_ADDRESS','".STORE_OWNER_EMAIL_ADDRESS."', 'The email address to use for PayPal debugging', '6', '72', now())");

    $this->notify('NOTIFY_PAYMENT_PAYPAL_INSTALLED');
  }
  /**
   * Remove the module and all its settings
    *
    */
  function remove() {
    global $db;
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PAYPAL\_%'");
    $this->notify('NOTIFY_PAYMENT_PAYPAL_UNINSTALLED');
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
    */
  function keys() {
    $keys_list = array(
                       'MODULE_PAYMENT_PAYPAL_STATUS',
                       'MODULE_PAYMENT_PAYPAL_BUSINESS_ID',
                       'MODULE_PAYMENT_PAYPAL_PDTTOKEN',
                       'MODULE_PAYMENT_PAYPAL_CURRENCY',
                       'MODULE_PAYMENT_PAYPAL_ZONE',
                       'MODULE_PAYMENT_PAYPAL_PROCESSING_STATUS_ID',
                       'MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID',
                       'MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID',
                       'MODULE_PAYMENT_PAYPAL_SORT_ORDER',
                       'MODULE_PAYMENT_PAYPAL_DETAILED_CART',
                       'MODULE_PAYMENT_PAYPAL_ADDRESS_OVERRIDE' ,
                       'MODULE_PAYMENT_PAYPAL_ADDRESS_REQUIRED' ,
                       'MODULE_PAYMENT_PAYPAL_PAGE_STYLE' ,
                       'MODULE_PAYMENT_PAYPAL_HANDLER',
                       'MODULE_PAYMENT_PAYPAL_IPN_DEBUG',
                        );

    // Paypal testing/debug options go here:
    if (IS_ADMIN_FLAG === true) {
      if (isset($_GET['debug']) && $_GET['debug']=='on') {
        $keys_list[]='MODULE_PAYMENT_PAYPAL_DEBUG_EMAIL_ADDRESS';  /* this defaults to store-owner-email-address */
      }
    }
    return $keys_list;
  }

  function _getPDTresults($orderAmount, $my_currency, $pdtTX) {
    global $db;
    $ipnData  = ipn_postback('PDT', $pdtTX);
    $respdata = $ipnData['info'];

    // parse the data
    $lines = explode("\n", $respdata);
    $this->pdtData = array();
    for ($i=1; $i<count($lines);$i++){
      if (!strstr($lines[$i], "=")) continue;
      list($key,$val) = explode("=", $lines[$i]);
      $this->pdtData[urldecode($key)] = urldecode($val);
    }

    if ($this->pdtData['txn_id'] == '' || $this->pdtData['payment_status'] == '') {
      ipn_debug_email('PDT Returned INVALID Data. Must wait for IPN to process instead. ' . "\n" . print_r($this->pdtData, true));
      return FALSE;
    } else {
      ipn_debug_email('PDT Returned Data ' . print_r($this->pdtData, true));
    }

    $_POST['mc_gross'] = $this->pdtData['mc_gross'];
    $_POST['mc_currency'] = $this->pdtData['mc_currency'];
    $_POST['business'] = $this->pdtData['business'];
    $_POST['receiver_email'] = $this->pdtData['receiver_email'];

    $PDTstatus = (ipn_validate_transaction($respdata, $this->pdtData, 'PDT') && valid_payment($orderAmount, $my_currency, 'PDT') && $this->pdtData['payment_status'] == 'Completed');
    if ($this->pdtData['payment_status'] != '' && $this->pdtData['payment_status'] != 'Completed') {
      ipn_debug_email('PDT WARNING :: Order not marked as "Completed".  Check for Pending reasons or wait for IPN to complete.' . "\n" . '[payment_status] => ' . $this->pdtData['payment_status'] . "\n" . '[pending_reason] => ' . $this->pdtData['pending_reason']);
    }

    $sql = "SELECT order_id, paypal_ipn_id, payment_status, txn_type, pending_reason
                FROM " . TABLE_PAYPAL . "
                WHERE txn_id = :transactionID OR parent_txn_id = :transactionID
                ORDER BY order_id DESC  ";
    $sql = $db->bindVars($sql, ':transactionID', $this->pdtData['txn_id'], 'string');
    $ipn_id = $db->Execute($sql);
    if ($ipn_id->RecordCount() != 0) {
      ipn_debug_email('PDT WARNING :: Transaction already exists. Perhaps IPN already added it.  PDT processing ended.');
      $pdtTXN_is_unique = false;
    } else {
      $pdtTXN_is_unique = true;
    }

    $PDTstatus = ($pdtTXN_is_unique && $PDTstatus);
    if ($PDTstatus == TRUE) $this->transaction_id = $this->pdtData['txn_id'];
    return $PDTstatus;
  }


  function tableCheckup() {
    global $db, $sniffer;
    $fieldOkay1 = (method_exists($sniffer, 'field_type')) ? $sniffer->field_type(TABLE_PAYPAL, 'txn_id', 'varchar(20)', true) : -1;
    $fieldOkay2 = ($sniffer->field_exists(TABLE_PAYPAL, 'module_name')) ? true : -1;
    $fieldOkay3 = ($sniffer->field_exists(TABLE_PAYPAL, 'order_id')) ? true : -1;

    if ($fieldOkay1 == -1) {
      $sql = "SHOW fields FROM " . TABLE_PAYPAL;
      $result = $db->Execute($sql);
      while (!$result->EOF) {
        if  ($result->fields['Field'] == 'txn_id') {
          if  ($result->fields['Type'] == 'varchar(20)') {
            $fieldOkay1 = true; // exists and matches required type, so skip to other checkup
          } else {
            $fieldOkay1 = $result->fields['Type']; // doesn't match, so return what it "is"
            break;
          }
        }
        $result->MoveNext();
      }
    }

    if ($fieldOkay1 !== true) {
      // temporary fix to table structure for v1.3.7.x -- may remove in later release
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE payment_type payment_type varchar(40) NOT NULL default ''");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE txn_type txn_type varchar(40) NOT NULL default ''");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE payment_status payment_status varchar(32) NOT NULL default ''");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE reason_code reason_code varchar(40) default NULL");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE pending_reason pending_reason varchar(32) default NULL");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE invoice invoice varchar(128) default NULL");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE payer_business_name payer_business_name varchar(128) default NULL");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE address_name address_name varchar(64) default NULL");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE address_street address_street varchar(254) default NULL");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE address_city address_city varchar(120) default NULL");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE address_state address_state varchar(120) default NULL");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE payer_email payer_email varchar(128) NOT NULL default ''");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE business business varchar(128) NOT NULL default ''");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE receiver_email receiver_email varchar(128) NOT NULL default ''");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE txn_id txn_id varchar(20) NOT NULL default ''");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE parent_txn_id parent_txn_id varchar(20) default NULL");
    }
    if ($fieldOkay2 !== true) {
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " ADD COLUMN module_name varchar(40) NOT NULL default '' after txn_type");
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " ADD COLUMN module_mode varchar(40) NOT NULL default '' after module_name");
    }
    if ($fieldOkay3 !== true) {
      $db->Execute("ALTER TABLE " . TABLE_PAYPAL . " CHANGE zen_order_id order_id int(11) NOT NULL default '0'");
    }
  }

}
