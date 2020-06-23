<?php
/**
 * paypalwpp.php payment module class for PayPal Express Checkout payment method
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 23 Modified in v1.5.7 $
 */
/**
 * load the communications layer code
 */
require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/paypal_curl.php');
/**
 * the PayPal payment module with Express Checkout
 */
class paypalwpp extends base {
  /**
   * name of this module
   *
   * @var string
   */
  var $code;
  /**
   * displayed module title
   *
   * @var string
   */
  var $title;
  /**
   * displayed module description
   *
   * @var string
   */
  var $description;
  /**
   * module status - set based on various config and zone criteria
   *
   * @var string
   */
  var $enabled;
  /**
   * the zone to which this module is restricted for use
   *
   * @var string
   */
  var $zone;
  /**
   * debugging flag
   *
   * @var boolean
   */
  var $enableDebugging = false;
  /**
   * Determines whether payment page is displayed or not
   *
   * @var boolean
   */
  var $showPaymentPage = false;
  var $flagDisablePaymentAddressChange = false;
  /**
   * sort order of display
   *
   * @var int
   */
  var $sort_order = 0;
  /**
   * Button Source / BN code -- enables the module to work for Zen Cart
   *
   * @var string
   */
  var $buttonSourceEC = 'ZenCart-EC_us';
  /**
   * order status setting for pending orders
   *
   * @var int
   */
  var $order_pending_status = 1;
  /**
   * order status setting for completed orders
   *
   * @var int
   */
  var $order_status = DEFAULT_ORDERS_STATUS_ID;
  /**
   * Debug tools
   */
  var $_logDir = DIR_FS_LOGS;
  var $_logLevel = 0;
  /**
   * FMF
   */
  var $fmfResponse = '';
  var $fmfErrors = array();
  /**
   * Flag to enable the modern in-context checkout.
   * https://developer.paypal.com/docs/classic/express-checkout/in-context/integration/
   * @var boolean
   */
  var $use_incontext_checkout = true;
  /**
   * class constructor
   */
  function __construct() {
    include_once(zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/', 'paypalwpp.php', 'false'));
    global $order;
    $this->code = 'paypalwpp';
    $this->codeTitle = MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_EC;
    $this->codeVersion = '1.5.7';
    $this->enableDirectPayment = FALSE;
    $this->enabled = (defined('MODULE_PAYMENT_PAYPALWPP_STATUS') && MODULE_PAYMENT_PAYPALWPP_STATUS == 'True');
    // Set the title & description text based on the mode we're in ... EC vs US/UK vs admin
    if (IS_ADMIN_FLAG === true) {
      $this->description = sprintf(MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_DESCRIPTION, ' (rev' . $this->codeVersion . ')');
      switch (MODULE_PAYMENT_PAYPALWPP_MODULE_MODE) {
        case ('PayPal'):
          $this->title = MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_EC;
        break;
        case ('Payflow-UK'):
          $this->title = MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_PRO20;
        break;
        case ('Payflow-US'):
          if (defined('MODULE_PAYMENT_PAYPALWPP_PAYFLOW_EC') && MODULE_PAYMENT_PAYPALWPP_PAYFLOW_EC == 'Yes') {
            $this->title = MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_PF_EC;
          } else {
            $this->title = MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_PF_GATEWAY;
          }
        break;
        default:
          $this->title = MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_EC;
      }

      $this->sort_order = defined('MODULE_PAYMENT_PAYPALWPP_SORT_ORDER') ? MODULE_PAYMENT_PAYPALWPP_SORT_ORDER : null;

      if (null === $this->sort_order) return false;

      if ($this->enabled) {
        if ( (MODULE_PAYMENT_PAYPALWPP_MODULE_MODE == 'PayPal' && (MODULE_PAYMENT_PAYPALWPP_APISIGNATURE == '' || MODULE_PAYMENT_PAYPALWPP_APIUSERNAME == '' || MODULE_PAYMENT_PAYPALWPP_APIPASSWORD == ''))
          || (substr(MODULE_PAYMENT_PAYPALWPP_MODULE_MODE,0,7) == 'Payflow' && (MODULE_PAYMENT_PAYPALWPP_PFPARTNER == '' || MODULE_PAYMENT_PAYPALWPP_PFVENDOR == '' || MODULE_PAYMENT_PAYPALWPP_PFUSER == '' || MODULE_PAYMENT_PAYPALWPP_PFPASSWORD == ''))
          ) $this->title .= '<span class="alert"><strong> NOT CONFIGURED YET</strong></span>';
        if (MODULE_PAYMENT_PAYPALWPP_SERVER =='sandbox') $this->title .= '<strong><span class="alert"> (sandbox active)</span></strong>';
        if (MODULE_PAYMENT_PAYPALWPP_DEBUGGING =='Log File' || MODULE_PAYMENT_PAYPALWPP_DEBUGGING =='Log and Email') $this->title .= '<strong> (Debug)</strong>';
        if (!function_exists('curl_init')) $this->title .= '<strong><span class="alert"> CURL NOT FOUND. Cannot Use.</span></strong>';
      }
    } else {
      $this->description = MODULE_PAYMENT_PAYPALWPP_TEXT_DESCRIPTION;
      $this->title = MODULE_PAYMENT_PAYPALWPP_EC_TEXT_TITLE; //pp
    }

    if ((!defined('PAYPAL_OVERRIDE_CURL_WARNING') || (defined('PAYPAL_OVERRIDE_CURL_WARNING') && PAYPAL_OVERRIDE_CURL_WARNING != 'True')) && !function_exists('curl_init')) $this->enabled = false;

    $this->enableDebugging = (MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log File' || MODULE_PAYMENT_PAYPALWPP_DEBUGGING =='Log and Email');
    $this->emailAlerts = (MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log File' || MODULE_PAYMENT_PAYPALWPP_DEBUGGING =='Log and Email' || MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Alerts Only');
    $this->doDPonly = (MODULE_PAYMENT_PAYPALWPP_MODULE_MODE =='Payflow-US' && !(defined('MODULE_PAYMENT_PAYPALWPP_PAYFLOW_EC') && MODULE_PAYMENT_PAYPALWPP_PAYFLOW_EC == 'Yes'));
    $this->showPaymentPage = (MODULE_PAYMENT_PAYPALWPP_SKIP_PAYMENT_PAGE == 'No') ? true : false;

    $this->buttonSourceEC = 'ZenCart-EC_us';
    $this->buttonSourceDP = 'ZenCart-DP_us';
    if (MODULE_PAYMENT_PAYPALWPP_MODULE_MODE == 'Payflow-UK') {
      $this->buttonSourceEC = 'ZenCart-EC_uk';
      $this->buttonSourceDP = 'ZenCart-DP_uk';
    }
    if (MODULE_PAYMENT_PAYPALWPP_MODULE_MODE == 'Payflow-US') {
      $this->buttonSourceEC = 'ZenCart-ECGW_us';
      $this->buttonSourceDP = 'ZenCart-GW_us';
    }

    $this->order_pending_status = MODULE_PAYMENT_PAYPALWPP_ORDER_PENDING_STATUS_ID;
    if ((int)MODULE_PAYMENT_PAYPALWPP_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_PAYPALWPP_ORDER_STATUS_ID;
    }
    $this->new_acct_notify = MODULE_PAYMENT_PAYPALWPP_NEW_ACCT_NOTIFY;
    $this->zone = (int)MODULE_PAYMENT_PAYPALWPP_ZONE;
    if (is_object($order)) $this->update_status();

    if (PROJECT_VERSION_MAJOR != '1' && substr(PROJECT_VERSION_MINOR, 0, 3) != '5.6') $this->enabled = false;

    $this->cards = array();
    // if operating in markflow mode, start EC process when submitting order
    if (!$this->in_special_checkout()) {
      $this->form_action_url = zen_href_link('ipn_main_handler.php', 'type=ec&markflow=1&clearSess=1&stage=final', 'SSL', true, true, true);
    }

    if (!defined('MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE') || MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE != 'InContext') {
      $this->use_incontext_checkout = false;
    }
    if (!defined('MODULE_PAYMENT_PAYPALWPP_MERCHANTID') || MODULE_PAYMENT_PAYPALWPP_MERCHANTID == '') {
      $this->use_incontext_checkout = false;
    }


    // debug setup
    if (!@is_writable($this->_logDir)) $this->_logDir = DIR_FS_CATALOG . $this->_logDir;
    if (!@is_writable($this->_logDir)) $this->_logDir = DIR_FS_LOGS;
    if (!@is_writable($this->_logDir)) $this->_logDir = DIR_FS_SQL_CACHE;
    // Regular mode:
    if ($this->enableDebugging) $this->_logLevel = 2;
    // DEV MODE:
    if (defined('PAYPAL_DEV_MODE') && PAYPAL_DEV_MODE == 'true') $this->_logLevel = 3;

    if (IS_ADMIN_FLAG === true) $this->tableCheckup();

  }
  /**
   *  Sets payment module status based on zone restrictions etc
   */
  function update_status() {
    global $order, $db;
//    $this->zcLog('update_status', 'Checking whether module should be enabled or not.');
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
        $this->zcLog('update_status', 'Module disabled due to zone restriction. Billing address is not within the Payment Zone selected in the module settings.');
      }
    }

    // module cannot be used for purchase > 1000000 JPY
    if ($order->info['total'] > 1000000 && $order->info['currency'] == 'JPY') {
      $this->enabled = false;
      $this->zcLog('update_status', 'Module disabled because purchase price (' . $order->info['total'] . ') exceeds PayPal-imposed maximum limit of 1000000 JPY.');
    }
    // module cannot be used for purchase > $10,000 USD equiv
    $order_amount = $this->calc_order_amount($order->info['total'], 'USD', false);
    if ($order_amount > 10000) {
      $this->enabled = false;
      $this->zcLog('update_status', 'Module disabled because purchase price (' . $order_amount . ') exceeds PayPal-imposed maximum limit of 10,000 USD or equivalent.');
    }
    if ($order->info['total'] == 0) {
      $this->enabled = false;
      $this->zcLog('update_status', 'Module disabled because purchase amount is set to 0.00.' . "\n" . print_r($order, true));
    }

    // other status checks?
    if ($this->enabled) {
      // other checks here
    }
  }
  /**
   *  Validate the credit card information via javascript (Number, Owner, and CVV Lengths)
   */
  function javascript_validation() {
    return false;
  }
  /**
   * Display Credit Card Information Submission Fields on the Checkout Payment Page
   */
  function selection() {
    $this->cc_type_check = '';
    /**
     * since we are NOT processing via the gateway, we will only display MarkFlow payment option, and no CC fields
     */
    return array('id' => $this->code,
                 'module' => '<img src="' . MODULE_PAYMENT_PAYPALEC_MARK_BUTTON_IMG . '" alt="' . MODULE_PAYMENT_PAYPALWPP_TEXT_BUTTON_ALTTEXT . '" /><span style="font-size:11px; font-family: Arial, Verdana;"> ' . MODULE_PAYMENT_PAYPALWPP_MARK_BUTTON_TXT . '</span>');
  }
  function pre_confirmation_check() {
    // Since this is an EC checkout, do nothing.
    return false;
  }
  /**
   * Display Payment Information for review on the Checkout Confirmation Page
   */
  function confirmation() {
    $confirmation = array('title' => '', 'fields' => array());
    return $confirmation;
  }
  /**
   * Prepare the hidden fields comprising the parameters for the Submit button on the checkout confirmation page
   */
  function process_button() {
    // When hitting the checkout-confirm button, we are going into markflow mode
    $_SESSION['paypal_ec_markflow'] = 1;

    // if we have a token, we want to avoid incontext checkout, so we return no special markup
    if (isset($_SESSION['paypal_ec_token']) && !empty($_SESSION['paypal_ec_token'])) {
      return '';
    }

    // if incontext checkout is not enabled (ie: not configured), we return no special incontext markup
    if ($this->use_incontext_checkout == false) return '';

    // send the PayPal-provided javascript to trigger the incontext checkout experience
    return "      <script>
        window.paypalCheckoutReady = function () {
        paypal.checkout.setup('" . MODULE_PAYMENT_PAYPALWPP_MERCHANTID . "', {
          //locale: '" . $this->getLanguageCode('incontext') . "',"
          . (MODULE_PAYMENT_PAYPALWPP_SERVER == 'live' ? '' : "\n          environment: 'sandbox',") . "
          container: 'checkout_confirmation',
          button: 'btn_submit'
        });
      };
      </script>
      <script src=\"https://www.paypalobjects.com/api/checkout.js\" async></script>";
  }
  /**
   * Prepare and submit the final authorization to PayPal via the appropriate means as configured
   */
  function before_process() {
    global $order, $doPayPal, $messageStack;
    $options = array();
    $optionsShip = array();
    $optionsNVP = array();

    $options = $this->getLineItemDetails($this->selectCurrency($order->info['currency']));

    // Allow delayed payments such as eCheck? (can only use InstantPayment if Action is Sale)
    // Payment Transaction/Authorization Mode
    $options['PAYMENTREQUEST_0_PAYMENTACTION'] = (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE == 'Auth Only') ? 'Authorization' : 'Sale';
    // for future:
    if (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE == 'Order') $options['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Order';
    if (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE != 'Auth Only' && MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE != 'Sale' && $options['PAYMENTREQUEST_0_PAYMENTACTION'] == 'Sale' && defined('MODULE_PAYMENT_PAYPALEC_ALLOWEDPAYMENT') && MODULE_PAYMENT_PAYPALEC_ALLOWEDPAYMENT == 'Instant Only') {
        $options['ALLOWEDPAYMENTMETHOD'] = 'InstantPaymentOnly';
    }

    //$this->zcLog('before_process - 1', 'Have line-item details:' . "\n" . print_r($options, true));

    // Initializing DESC field: using for comments related to tax-included pricing, populated by getLineItemDetails()
    $options['PAYMENTREQUEST_0_DESC'] = '';

    $doPayPal = $this->paypal_init();
    $this->zcLog('before_process - EC-1', 'Beginning EC mode');
     /****************************************
      * Do EC checkout
      ****************************************/
      // do not allow blank address to be sent to PayPal
      if ($_SESSION['paypal_ec_payer_info']['ship_street_1'] != '' && strtoupper($_SESSION['paypal_ec_payer_info']['ship_address_status']) != 'NONE') {
        $options = array_merge($options,
                 array('PAYMENTREQUEST_0_SHIPTONAME'   => $_SESSION['paypal_ec_payer_info']['ship_name'],
                       'PAYMENTREQUEST_0_SHIPTOSTREET' => $_SESSION['paypal_ec_payer_info']['ship_street_1'],
                       'PAYMENTREQUEST_0_SHIPTOSTREET2'=> $_SESSION['paypal_ec_payer_info']['ship_street_2'],
                       'PAYMENTREQUEST_0_SHIPTOCITY'   => $_SESSION['paypal_ec_payer_info']['ship_city'],
                       'PAYMENTREQUEST_0_SHIPTOSTATE'  => $_SESSION['paypal_ec_payer_info']['ship_state'],
                       'PAYMENTREQUEST_0_SHIPTOZIP'    => $_SESSION['paypal_ec_payer_info']['ship_postal_code'],
                       'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'=> $_SESSION['paypal_ec_payer_info']['ship_country_code'],
                       ));
        $this->zcLog('before_process - EC-2', 'address overrides added:' . "\n" . print_r($options, true));
      }

      $this->zcLog('before_process - EC-3', 'address info added:' . "\n" . print_r($options, true));

      $options['BUTTONSOURCE'] = $this->buttonSourceEC;

      // If the customer has changed their shipping address,
      // override the shipping address in PayPal with the shipping
      // address that is selected in Zen Cart.
      if ($order->delivery['street_address'] != '' && $order->delivery['street_address'] != $_SESSION['paypal_ec_payer_info']['ship_street_1'] && $_SESSION['paypal_ec_payer_info']['ship_street_1'] != '') {
        $_GET['markflow'] = 2;
        if (($address_arr = $this->getOverrideAddress()) !== false) {
          // set the override var
          $options['ADDROVERRIDE'] = 1;
          // set the address info
          $options['PAYMENTREQUEST_0_SHIPTONAME']    = $address_arr['entry_firstname'] . ' ' . $address_arr['entry_lastname'];
          $options['PAYMENTREQUEST_0_SHIPTOSTREET']  = $address_arr['entry_street_address'];
          if ($address_arr['entry_suburb'] != '') $options['PAYMENTREQUEST_0_SHIPTOSTREET2'] = $address_arr['entry_suburb'];
          $options['PAYMENTREQUEST_0_SHIPTOCITY']    = $address_arr['entry_city'];
          $options['PAYMENTREQUEST_0_SHIPTOZIP']     = $address_arr['entry_postcode'];
          $options['PAYMENTREQUEST_0_SHIPTOSTATE']   = $address_arr['zone_code'];
          $options['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $address_arr['countries_iso_code_2'];
        }
      }
      // if these optional parameters are blank, remove them from transaction
      if (isset($options['PAYMENTREQUEST_0_SHIPTOSTREET2']) && trim($options['PAYMENTREQUEST_0_SHIPTOSTREET2']) == '') unset($options['PAYMENTREQUEST_0_SHIPTOSTREET2']);
      if (isset($options['PAYMENTREQUEST_0_SHIPTOPHONENUM']) && trim($options['PAYMENTREQUEST_0_SHIPTOPHONENUM']) == '') unset($options['PAYMENTREQUEST_0_SHIPTOPHONENUM']);

      // if State is not supplied, repeat the city so that it's not blank, otherwise PayPal croaks
      if ((!isset($options['PAYMENTREQUEST_0_SHIPTOSTATE']) || trim($options['PAYMENTREQUEST_0_SHIPTOSTATE']) == '') && !empty($options['PAYMENTREQUEST_0_SHIPTOCITY'])) {
          $options['PAYMENTREQUEST_0_SHIPTOSTATE'] = $options['PAYMENTREQUEST_0_SHIPTOCITY'];
      }

      // FMF support
      $options['RETURNFMFDETAILS'] = (MODULE_PAYMENT_PAYPALWPP_EC_RETURN_FMF_DETAILS == 'Yes') ? 1 : 0;

      // Add note to track that this was an EC transaction (used in properly handling update IPNs related to EC transactions):
      $options['PAYMENTREQUEST_0_CUSTOM'] = 'EC-' . (int)$_SESSION['customer_id'] . '-' . time();

      // send the store name as transaction identifier, to help distinguish payments between multiple stores:
      $options['PAYMENTREQUEST_0_INVNUM'] = (int)$_SESSION['customer_id'] . '-' . time() . '-[' . substr(preg_replace('/[^a-zA-Z0-9_]/', '', STORE_NAME), 0, 30) . ']';  // (cannot send actual invoice number because it's not assigned until after payment is completed)

      $options['PAYMENTREQUEST_0_CURRENCYCODE'] = $this->selectCurrency($order->info['currency']);
//      $order->info['total'] = zen_round($order->info['total'], 2);
      $order_amount = $this->calc_order_amount($order->info['total'], $options['PAYMENTREQUEST_0_CURRENCYCODE'], FALSE);

      if (isset($options['PAYMENTREQUEST_0_DESC']) && $options['PAYMENTREQUEST_0_DESC'] == '') unset($options['PAYMENTREQUEST_0_DESC']);
      if (!isset($options['PAYMENTREQUEST_0_AMT'])) $options['PAYMENTREQUEST_0_AMT'] = round($order_amount, 2);


if (false) { // disabled until clarification is received about coupons in PayPal Wallet
      // report details of coupons used
      $j=0;
      global $order_totals;
      if (sizeof($order_totals)) {
        for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
          if (in_array($order_totals[$i]['code'], array('ot_coupon','ot_gv'))) {
            $j++;
            $options["L_PAYMENTREQUEST_0_REDEEMEDOFFERTYPE$j"] = 'MERCHANT_COUPON';
            $options["L_PAYMENTREQUEST_0_REDEEMEDOFFERNAME$j"] = substr($order_totals[$i]['title'], 0, 127);
            $options["L_PAYMENTREQUEST_0_REDEEMEDOFFERDESCRIPTION$j"] = substr($order_totals[$i]['title'], 0, 127);
            $options["L_PAYMENTREQUEST_0_REDEEMEDOFFERAMOUNT$j"] = $order_totals[$i]['value'];
          }
        }
      }
      // end coupons
}

      // debug output
      $this->zcLog('before_process - EC-4', 'info being submitted:' . "\n" . $_SESSION['paypal_ec_token'] . ' ' . $_SESSION['paypal_ec_payer_id'] . ' ' . round($order_amount, 2) .  "\n" . print_r($options, true));

      $this->notify('NOTIFY_PAYPALWPP_BEFORE_DOEXPRESSCHECKOUT');

      // in case of funding error, set the redirect URL which is used by the _errorHandler
      $this->ec_redirect_url = $this->getPayPalLoginServer() . "?cmd=_express-checkout&token=" . $_SESSION['paypal_ec_token'] . "&useraction=continue";

      $response = $doPayPal->DoExpressCheckoutPayment($_SESSION['paypal_ec_token'],
                                                      $_SESSION['paypal_ec_payer_id'],
                                                      $options);

      $this->zcLog('before_process - EC-5', 'resultset:' . "\n" . urldecode(print_r($response, true)));

      // CHECK RESPONSE -- if error, actions are taken in the errorHandler
      $errorHandlerMessage = $this->_errorHandler($response, 'DoExpressCheckoutPayment');

      if ($this->fmfResponse != '') {
        $this->order_status = $this->order_pending_status;
      }

      // SUCCESS
      $this->payment_type = MODULE_PAYMENT_PAYPALWPP_EC_TEXT_TYPE;
      $this->responsedata = $response;
      if ($response['PAYMENTINFO_0_PAYMENTTYPE'] != '') $this->payment_type .=  ' (' . urldecode($response['PAYMENTINFO_0_PAYMENTTYPE']) . ')';

      $this->transaction_id = trim((isset($response['PNREF']) ? $response['PNREF'] : '') . ' ' . $response['PAYMENTINFO_0_TRANSACTIONID']);
      if (empty($response['PAYMENTINFO_0_PENDINGREASON']) ||
          $response['PAYMENTINFO_0_PENDINGREASON'] == 'none' ||
          $response['PAYMENTINFO_0_PENDINGREASON'] == 'completed' ||
          $response['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Completed') {
        $this->payment_status = 'Completed';
        if ($this->order_status > 0) $order->info['order_status'] = $this->order_status;
      } else {
        if ($response['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Pending')
        {
          if ($response['L_ERRORCODE0'] == 11610 && $response['PAYMENTINFO_0_PENDINGREASON'] == '') $response['PAYMENTINFO_0_PENDINGREASON'] = 'Pending FMF Review by Storeowner';
        }
        $this->payment_status = 'Pending (' . $response['PAYMENTINFO_0_PENDINGREASON'] . ')';
        $order->info['order_status'] = $this->order_pending_status;
      }
      $this->avs = 'N/A';
      $this->cvv2 = 'N/A';
      $this->correlationid = $response['CORRELATIONID'];
      $this->transactiontype = $response['PAYMENTINFO_0_TRANSACTIONTYPE'];
      $this->payment_time = urldecode($response['PAYMENTINFO_0_ORDERTIME']);
      $this->feeamt = urldecode($response['PAYMENTINFO_0_FEEAMT']);
      $this->taxamt = urldecode($response['PAYMENTINFO_0_TAXAMT']);
      $this->pendingreason = $response['PAYMENTINFO_0_PENDINGREASON'];
      $this->reasoncode = $response['PAYMENTINFO_0_REASONCODE'];
      $this->numitems = sizeof($order->products);
      $this->amt = urldecode($response['PAYMENTINFO_0_AMT'] . ' ' . $response['PAYMENTINFO_0_CURRENCYCODE']);
      $this->auth_code = (isset($response['AUTHCODE'])) ? $response['AUTHCODE'] : $response['TOKEN'];

      $this->notify('NOTIFY_PAYPALWPP_BEFORE_PROCESS_FINISHED', $response);
  }
  /**
   * When the order returns from the processor, this stores the results in order-status-history and logs data for subsequent use
   */
  function after_process() {
    global $insert_id, $order;
    // FMF
    if ($this->fmfResponse != '') {
      $detailedMessage = $insert_id . "\n" . $this->fmfResponse . "\n" . MODULES_PAYMENT_PAYPALDP_TEXT_EMAIL_FMF_INTRO . "\n" . print_r($this->fmfErrors, TRUE);
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULES_PAYMENT_PAYPALDP_TEXT_EMAIL_FMF_SUBJECT . ' (' . $insert_id . ')', $detailedMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($detailedMessage)), 'paymentalert');
    }

    // add a new OSH record for this order's PP details
    $commentString = "Transaction ID: " . $this->transaction_id .
                     (isset($this->responsedata['PPREF']) ? "\nPPRef: " . $this->responsedata['PPREF'] : '') .
                     (isset($this->responsedata['AUTHCODE'])? "\nAuthCode: " . $this->responsedata['AUTHCODE'] : '') .
                                 "\nPayment Type: " . $this->payment_type .
                     ($this->payment_time != '' ? ("\nTimestamp: " . $this->payment_time) : '') .
                                 "\nPayment Status: " . $this->payment_status .
                     (isset($this->responsedata['auth_exp']) ? "\nAuth-Exp: " . $this->responsedata['auth_exp'] : '') .
                     ($this->avs != 'N/A' ? "\nAVS Code: ".$this->avs."\nCVV2 Code: ".$this->cvv2 : '') .
                     (trim($this->amt) != '' ? ("\nAmount: " . $this->amt) : '');
    zen_update_orders_history($insert_id, $commentString, null, $order->info['order_status'], 0);

    // store the PayPal order meta data -- used for later matching and back-end processing activities
    $paypal_order = array('order_id' => $insert_id,
                          'txn_type' => $this->transactiontype,
                          'module_name' => $this->code,
                          'module_mode' => MODULE_PAYMENT_PAYPALWPP_MODULE_MODE,
                          'reason_code' => $this->reasoncode,
                          'payment_type' => $this->payment_type,
                          'payment_status' => $this->payment_status,
                          'pending_reason' => $this->pendingreason,
                          'invoice' => urldecode($_SESSION['paypal_ec_token'] . (isset($this->responsedata['PPREF']) ? $this->responsedata['PPREF'] : '')),
                          'first_name' => $_SESSION['paypal_ec_payer_info']['payer_firstname'],
                          'last_name' => $_SESSION['paypal_ec_payer_info']['payer_lastname'],
                          'payer_business_name' => $_SESSION['paypal_ec_payer_info']['payer_business'],
                          'address_name' => $_SESSION['paypal_ec_payer_info']['ship_name'],
                          'address_street' => $_SESSION['paypal_ec_payer_info']['ship_street_1'],
                          'address_city' => $_SESSION['paypal_ec_payer_info']['ship_city'],
                          'address_state' => $_SESSION['paypal_ec_payer_info']['ship_state'],
                          'address_zip' => $_SESSION['paypal_ec_payer_info']['ship_postal_code'],
                          'address_country' => $_SESSION['paypal_ec_payer_info']['ship_country_name'],
                          'address_status' => $_SESSION['paypal_ec_payer_info']['ship_address_status'],
                          'payer_email' => $_SESSION['paypal_ec_payer_info']['payer_email'],
                          'payer_id' => $_SESSION['paypal_ec_payer_id'],
                          'payer_status' => $_SESSION['paypal_ec_payer_info']['payer_status'],
                          'payment_date' => trim(preg_replace('/[^0-9-:]/', ' ', $this->payment_time)),
                          'business' => '',
                          'receiver_email' => (substr(MODULE_PAYMENT_PAYPALWPP_MODULE_MODE,0,7) == 'Payflow' ? MODULE_PAYMENT_PAYPALWPP_PFVENDOR : str_replace('_api1', '', MODULE_PAYMENT_PAYPALWPP_APIUSERNAME)),
                          'receiver_id' => '',
                          'txn_id' => $this->transaction_id,
                          'parent_txn_id' => '',
                          'num_cart_items' => (float)$this->numitems,
                          'mc_gross' => (float)$this->amt,
                          'mc_fee' => (float)urldecode($this->feeamt),
                          'mc_currency' => $this->responsedata['PAYMENTINFO_0_CURRENCYCODE'],
                          'settle_amount' => (float)(isset($this->responsedata['PAYMENTINFO_0_SETTLEAMT'])) ? $this->urldecode($this->responsedata['PAYMENTINFO_0_SETTLEAMT']) : $this->amt,
                          'settle_currency' => $this->responsedata['PAYMENTINFO_0_CURRENCYCODE'],
                          'exchange_rate' => (isset($this->responsedata['PAYMENTINFO_0_EXCHANGERATE']) && urldecode($this->responsedata['PAYMENTINFO_0_EXCHANGERATE']) > 0) ? urldecode($this->responsedata['PAYMENTINFO_0_EXCHANGERATE']) : 1.0,
                          'notify_version' => '0',
                          'verify_sign' =>'',
                          'date_added' => 'now()',
                          'memo' => (sizeof($this->fmfErrors) > 0 ? 'FMF Details ' . print_r($this->fmfErrors, TRUE) : '{Record generated by payment module}'),
                         );
    zen_db_perform(TABLE_PAYPAL, $paypal_order);

    $this->notify('NOTIFY_PAYPALWPP_AFTER_PROCESS_FINISHED', $paypal_order);

    // Unregister the paypal session variables, making it necessary to start again for another purchase
    unset($_SESSION['paypal_ec_temp']);
    unset($_SESSION['paypal_ec_token']);
    unset($_SESSION['paypal_ec_payer_id']);
    unset($_SESSION['paypal_ec_payer_info']);
    unset($_SESSION['paypal_ec_final']);
    unset($_SESSION['paypal_ec_markflow']);
  }
  /**
    * Build admin-page components
    *
    * @param int $zf_order_id
    * @return string
    */
  function admin_notification($zf_order_id) {
    if (!defined('MODULE_PAYMENT_PAYPALWPP_STATUS')) return '';
    global $db;
    $module = $this->code;
    $output = '';
    $response = $this->_GetTransactionDetails($zf_order_id);
    //$response = $this->_TransactionSearch('2006-12-01T00:00:00Z', $zf_order_id);
    $sql = "SELECT * from " . TABLE_PAYPAL . " WHERE order_id = :orderID
            AND parent_txn_id = '' AND order_id > 0
            ORDER BY paypal_ipn_id DESC LIMIT 1";
    $sql = $db->bindVars($sql, ':orderID', $zf_order_id, 'integer');
    $ipn = $db->Execute($sql);
    if ($ipn->EOF) {
      $ipn = new stdClass;
      $ipn->fields = array();
    }
    if (file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/paypalwpp_admin_notification.php')) require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/paypalwpp_admin_notification.php');
    return $output;
  }
  /**
   * Used to read details of an existing transaction.  FOR FUTURE USE.
   */
  function _GetTransactionDetails($oID, $last_txn = true) {
    if ($oID == '' || $oID < 1) return FALSE;
    global $db, $messageStack, $doPayPal;
    $doPayPal = $this->paypal_init();
    // look up history on this order from PayPal table
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID order by last_modified DESC, date_added DESC, parent_txn_id DESC, paypal_ipn_id " . ($last_txn ? "DESC " : "ASC ");
    $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
    $zc_ppHist = $db->Execute($sql);
    if ($zc_ppHist->RecordCount() == 0) return false;
    $txnID = $zc_ppHist->fields['txn_id'];
    if ($txnID == '' || $txnID === 0) return FALSE;
    /**
     * Read data from PayPal
     */
    $response = $doPayPal->GetTransactionDetails($txnID);

    // $this->zcLog("_GetTransactionDetails($oID):", print_r($response, true));

    $error = $this->_errorHandler($response, 'GetTransactionDetails', 10007);
    if ($error === false) {
      return false;
    } else {
      return $response;
    }
  }
  /**
   * Used to read details of existing transactions.  FOR FUTURE USE.
   */
  function _TransactionSearch($startDate = '', $oID = '', $criteria = '') {
    global $db, $messageStack, $doPayPal;
    $doPayPal = $this->paypal_init();
    // look up history on this order from PayPal table
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID  AND parent_txn_id = '' ";
    $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
    $zc_ppHist = $db->Execute($sql);
    if ($zc_ppHist->RecordCount() == 0) return false;
    $txnID = $zc_ppHist->fields['txn_id'];
    $startDate = $zc_ppHist->fields['payment_date'];
    $timeval = time();
    if ($startDate == '') $startDate = date('Y-m-d', $timeval) . 'T' . date('h:i:s', $timeval) . 'Z';
    /**
     * Read data from PayPal
     */
    $response = $doPayPal->TransactionSearch($startDate, $txnID, $email, $criteria);

    //$this->zcLog("_TransactionSearch($startDate, $oID, $criteria):", print_r($response, true));

    $error = $this->_errorHandler($response, 'TransactionSearch');
    if ($error === false) {
      return false;
    } else {
      return $response;
    }
  }
  /**
   * Evaluate installation status of this module. Returns true if the status key is found.
   */
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYPALWPP_STATUS'");
      $this->_check = !$check_query->EOF;
    }

    if ($this->_check) $this->keys(); // install any missing keys
    return $this->_check;
  }
  /**
   * Installs all the configuration keys for this module
   */
  function install() {
    global $db, $messageStack;
    if (defined('MODULE_PAYMENT_PAYPALWPP_STATUS')) {
      $messageStack->add_session('Express Checkout module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paypalwpp', 'NONSSL'));
      return 'failed';
    }
    if (defined('MODULE_PAYMENT_PAYPAL_STATUS')) {
      $messageStack->add_session('NOTE: You already have the PayPal Website Payments Standard module installed. You should REMOVE it if you want to install Express Checkout.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paypal', 'NONSSL'));
      return 'failed';
    }

    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable this Payment Module', 'MODULE_PAYMENT_PAYPALWPP_STATUS', 'True', 'Do you want to enable this payment module?', '6', '25', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Live or Sandbox', 'MODULE_PAYMENT_PAYPALWPP_SERVER', 'live', '<strong>Live: </strong> Used to process Live transactions<br><strong>Sandbox: </strong>For developers and testing', '6', '25', 'zen_cfg_select_option(array(\'live\', \'sandbox\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Express Checkout Shortcut Button', 'MODULE_PAYMENT_PAYPALWPP_ECS_BUTTON', 'On', 'The Express Checkout Shortcut button shows up on your shopping cart page to invite your customers to pay using PayPal without having to give all their address details on your site first before selecting shipping options.<br />It has been shown to increase sales and conversions when enabled.<br />Default: On ', '6', '25', 'zen_cfg_select_option(array(\'On\', \'Off\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Express Checkout: Require Confirmed Address', 'MODULE_PAYMENT_PAYPALWPP_CONFIRMED_ADDRESS', 'No', 'Do you want to require that your (not-logged-in) customers use a *confirmed* address when choosing their shipping address in PayPal?<br />(this is ignored for logged-in customers)', '6', '25',  'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Express Checkout: Select Cheapest Shipping Automatically', 'MODULE_PAYMENT_PAYPALWPP_AUTOSELECT_CHEAPEST_SHIPPING', 'Yes', 'When customer returns from PayPal, do we want to automatically select the Cheapest shipping method and skip the shipping page? (making it more *express*)<br />Note: enabling this means the customer does *not* have easy access to select an alternate shipping method (without going back to the Checkout-Step-1 page)', '6', '25',  'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Express Checkout: Skip Payment Page', 'MODULE_PAYMENT_PAYPALWPP_SKIP_PAYMENT_PAGE', 'Yes', 'If the customer is checking out with Express Checkout, do you want to skip the checkout payment page, making things more *express*? <br /><strong>(NOTE: The Payment Page will auto-display regardless of this setting if you have Coupons or Gift Certificates enabled in your store.)</strong>', '6', '25',  'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Express Checkout: Automatic Account Creation', 'MODULE_PAYMENT_PAYPALWPP_NEW_ACCT_NOTIFY', 'Yes', 'If a visitor is not an existing customer, a Zen Cart account is created for them.  Would you like make it a permanent account and send them an email containing their login information?<br />NOTE: Permanent accounts are auto-created if the customer purchases downloads or gift certificates, regardless of this setting.', '6', '25', 'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_PAYPALWPP_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_PAYPALWPP_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '25', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_PAYPALWPP_ORDER_STATUS_ID', '2', 'Set the status of orders paid with this payment module to this value. <br /><strong>Recommended: Processing[2]</strong>', '6', '25', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Unpaid Order Status', 'MODULE_PAYMENT_PAYPALWPP_ORDER_PENDING_STATUS_ID', '1', 'Set the status of unpaid orders made with this payment module to this value. <br /><strong>Recommended: Pending[1]</strong>', '6', '25', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refund Order Status', 'MODULE_PAYMENT_PAYPALWPP_REFUNDED_STATUS_ID', '1', 'Set the status of refunded orders to this value. <br /><strong>Recommended: Pending[1]</strong>', '6', '25', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('PayPal Page Style', 'MODULE_PAYMENT_PAYPALWPP_PAGE_STYLE', 'Primary', 'The page-layout style you want customers to see when they visit the PayPal site. You can configure your <strong>Custom Page Styles</strong> in your PayPal Profile settings. This value is case-sensitive.', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Store (Brand) Name at PayPal', 'MODULE_PAYMENT_PAYPALWPP_BRANDNAME', '', 'The name of your store as it should appear on the PayPal login page. If blank, your store name will be used.', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Payment Action', 'MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE', 'Final Sale', 'How do you want to obtain payment?<br /><strong>Default: Final Sale</strong>', '6', '25', 'zen_cfg_select_option(array(\'Auth Only\', \'Final Sale\'), ',  now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Currency', 'MODULE_PAYMENT_PAYPALWPP_CURRENCY', 'Selected Currency', 'Which currency should the order be sent to PayPal as? <br />NOTE: if an unsupported currency is sent to PayPal, it will be auto-converted to USD (or GBP if using UK account)<br /><strong>Default: Selected Currency</strong>', '6', '25', 'zen_cfg_select_option(array(\'Selected Currency\', \'Only USD\', \'Only AUD\', \'Only CAD\', \'Only EUR\', \'Only GBP\', \'Only CHF\', \'Only CZK\', \'Only DKK\', \'Only HKD\', \'Only HUF\', \'Only JPY\', \'Only NOK\', \'Only NZD\', \'Only PLN\', \'Only SEK\', \'Only SGD\', \'Only THB\', \'Only MXN\', \'Only ILS\', \'Only PHP\', \'Only TWD\', \'Only BRL\', \'Only MYR\', \'Only TRY\', \'Only INR\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow eCheck?', 'MODULE_PAYMENT_PAYPALEC_ALLOWEDPAYMENT', 'Any', 'Do you want to allow non-instant payments like eCheck/EFT/ELV?', '6', '25', 'zen_cfg_select_option(array(\'Any\', \'Instant Only\'), ', now())");

    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Fraud Mgmt Filters - FMF', 'MODULE_PAYMENT_PAYPALWPP_EC_RETURN_FMF_DETAILS', 'No', 'If you have enabled FMF support in your PayPal account and wish to utilize it in your transactions, set this to yes. Otherwise, leave it at No.', '6', '25','zen_cfg_select_option(array(\'No\', \'Yes\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('API Signature -- Username', 'MODULE_PAYMENT_PAYPALWPP_APIUSERNAME', '', 'The API Username from your PayPal API Signature settings under *API Access*. This value typically looks like an email address and is case-sensitive.', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function, use_function) VALUES ('API Signature -- Password', 'MODULE_PAYMENT_PAYPALWPP_APIPASSWORD', '', 'The API Password from your PayPal API Signature settings under *API Access*. This value is a 16-character code and is case-sensitive.', '6', '25', now(), 'zen_cfg_password_input(', 'zen_cfg_password_display')");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function, use_function) VALUES ('API Signature -- Signature Code', 'MODULE_PAYMENT_PAYPALWPP_APISIGNATURE', '', 'The API Signature from your PayPal API Signature settings under *API Access*. This value is a 56-character code, and is case-sensitive.', '6', '25', now(), '', 'zen_cfg_password_display')");

    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('PAYFLOW: User', 'MODULE_PAYMENT_PAYPALWPP_PFUSER', '', 'If you set up one or more additional users on the account, this value is the ID of the user authorized to process transactions. Otherwise it should be the same value as VENDOR. This value is case-sensitive.', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('PAYFLOW: Partner', 'MODULE_PAYMENT_PAYPALWPP_PFPARTNER', 'ZenCart', 'Your Payflow Partner name linked to your Payflow account. This value is case-sensitive.<br />Typical values: <strong>PayPal</strong> or <strong>ZenCart</strong>', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('PAYFLOW: Vendor', 'MODULE_PAYMENT_PAYPALWPP_PFVENDOR', '', 'Your merchant login ID that you created when you registered for the Payflow Pro account. This value is case-sensitive.', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function, use_function) VALUES ('PAYFLOW: Password', 'MODULE_PAYMENT_PAYPALWPP_PFPASSWORD', '', 'The 6- to 32-character password that you defined while registering for the account. This value is case-sensitive.', '6', '25', now(), 'zen_cfg_password_input(', 'zen_cfg_password_display')");

    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('PayPal Mode', 'MODULE_PAYMENT_PAYPALWPP_MODULE_MODE', 'PayPal', 'Which PayPal API system should be used for processing? <br /><u>Choices:</u><br /><font color=green>For choice #1, you need to supply <strong>API Settings</strong> above.</font><br /><strong>1. PayPal</strong> = Express Checkout with a regular PayPal account<br />or<br /><font color=green>for choices 2 &amp; 3 you need to supply <strong>PAYFLOW settings</strong>, below (and have a Payflow account)</font><br /><strong>2. Payflow-UK</strong> = Website Payments Pro UK Payflow Edition<br /><strong>3. Payflow-US</strong> = Payflow Pro Gateway only<!--<br /><strong>4. PayflowUS+EC</strong> = Payflow Pro with Express Checkout-->', '6', '25',  'zen_cfg_select_option(array(\'PayPal\', \'Payflow-UK\', \'Payflow-US\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Mode', 'MODULE_PAYMENT_PAYPALWPP_DEBUGGING', 'Off', 'Would you like to enable debug mode?  A complete detailed log of failed transactions will be emailed to the store owner.', '6', '25', 'zen_cfg_select_option(array(\'Off\', \'Alerts Only\', \'Log File\', \'Log and Email\'), ', now())");

    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('PayPal Merchant ID', 'MODULE_PAYMENT_PAYPALWPP_MERCHANTID', '', 'Enter your PayPal Merchant ID here. This is used for the more user-friendly In-Context checkout mode. You can obtain this value by going to your PayPal account, clicking on Profile and navigating to the My Business Info section; You will find your Merchant Account ID on that screen. A typical merchantID looks like FDEFDEFDEFDE11.', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use InContext Checkout?', 'MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE', 'InContext', 'PayPal now offers a newer friendlier InContext (in-page) checkout mode (Requires that you enter your MerchantID in the Merchant ID Setting above). Or you can use the older checkout style which offers Pay Without Account by default but with a full-page-redirect.', '6', '25', 'zen_cfg_select_option(array(\'InContext\', \'Old\'), ', now())");

    $this->notify('NOTIFY_PAYMENT_PAYPALWPP_INSTALLED');
  }

  function keys() {
    if (defined('MODULE_PAYMENT_PAYPALWPP_STATUS')) {
      global $db;
      if (!defined('MODULE_PAYMENT_PAYPALWPP_ECS_BUTTON')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Express Checkout Shortcut Button', 'MODULE_PAYMENT_PAYPALWPP_ECS_BUTTON', 'On', 'The Express Checkout Shortcut button shows up on your shopping cart page to invite your customers to pay using PayPal without having to give all their address details on your site first before selecting shipping options.<br />It has been shown to increase sales and conversions when enabled.<br />Default: On ', '6', '25', 'zen_cfg_select_option(array(\'On\', \'Off\'), ', now())");
      }
      if (!defined('MODULE_PAYMENT_PAYPALWPP_BRANDNAME')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Store Brand Name at PayPal', 'MODULE_PAYMENT_PAYPALWPP_BRANDNAME', '', 'The name of your store as it should appear on the PayPal login page. If blank, your store name will be used.', '6', '25', now())");
      }
      if (!defined('MODULE_PAYMENT_PAYPALEC_ALLOWEDPAYMENT')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow eCheck?', 'MODULE_PAYMENT_PAYPALEC_ALLOWEDPAYMENT', 'Any', 'Do you want to allow non-instant payments like eCheck/EFT/ELV?', '6', '25', 'zen_cfg_select_option(array(\'Any\', \'Instant Only\'), ', now())");
      }
      if (!defined('MODULE_PAYMENT_PAYPALWPP_MERCHANTID')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('PayPal Merchant ID', 'MODULE_PAYMENT_PAYPALWPP_MERCHANTID', '', 'Enter your PayPal Merchant ID here. This is used for the more user-friendly In-Context checkout mode. You can obtain this value by going to your PayPal account, clicking on Profile and navigating to the My Business Info section; You will find your Merchant Account ID on that screen. A typical merchantID looks like FDEFDEFDEFDE11.', '6', '25', now())");
      }
      if (!defined('MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE')) {
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use InContext Checkout?', 'MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE', 'InContext', 'PayPal now offers a newer friendlier InContext (in-page) checkout mode (Requires that you enter your MerchantID in the Merchant ID Setting above). Or you can use the older checkout style which offers Pay Without Account by default but with a full-page-redirect.', '6', '25', 'zen_cfg_select_option(array(\'InContext\', \'Old\'), ', now())");
      }
    }
    $keys_list = array('MODULE_PAYMENT_PAYPALWPP_STATUS', 'MODULE_PAYMENT_PAYPALWPP_SORT_ORDER', 'MODULE_PAYMENT_PAYPALWPP_ZONE', 'MODULE_PAYMENT_PAYPALWPP_ECS_BUTTON', 'MODULE_PAYMENT_PAYPALWPP_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPALWPP_ORDER_PENDING_STATUS_ID', 'MODULE_PAYMENT_PAYPALWPP_REFUNDED_STATUS_ID', 'MODULE_PAYMENT_PAYPALWPP_CONFIRMED_ADDRESS', 'MODULE_PAYMENT_PAYPALWPP_AUTOSELECT_CHEAPEST_SHIPPING', 'MODULE_PAYMENT_PAYPALWPP_SKIP_PAYMENT_PAGE', 'MODULE_PAYMENT_PAYPALWPP_NEW_ACCT_NOTIFY', 'MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE', 'MODULE_PAYMENT_PAYPALWPP_CURRENCY', 'MODULE_PAYMENT_PAYPALWPP_BRANDNAME', 'MODULE_PAYMENT_PAYPALEC_ALLOWEDPAYMENT', 'MODULE_PAYMENT_PAYPALWPP_EC_RETURN_FMF_DETAILS', 'MODULE_PAYMENT_PAYPALWPP_PAGE_STYLE', 'MODULE_PAYMENT_PAYPALWPP_APIUSERNAME', 'MODULE_PAYMENT_PAYPALWPP_APIPASSWORD', 'MODULE_PAYMENT_PAYPALWPP_APISIGNATURE', 'MODULE_PAYMENT_PAYPALWPP_MERCHANTID', 'MODULE_PAYMENT_PAYPALWPP_MODULE_MODE', 'MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE', 'MODULE_PAYMENT_PAYPALWPP_SERVER', 'MODULE_PAYMENT_PAYPALWPP_DEBUGGING');
    if (defined('PAYPAL_DEV_MODE') && IS_ADMIN_FLAG === true && (PAYPAL_DEV_MODE == 'true' || strstr(MODULE_PAYMENT_PAYPALWPP_MODULE_MODE, 'Payflow'))) {
      $keys_list = array_merge($keys_list, array('MODULE_PAYMENT_PAYPALWPP_PFPARTNER', 'MODULE_PAYMENT_PAYPALWPP_PFVENDOR', 'MODULE_PAYMENT_PAYPALWPP_PFUSER', 'MODULE_PAYMENT_PAYPALWPP_PFPASSWORD'));
    }
    return $keys_list;
  }
  /**
   * De-install this module
   */
  function remove() {
    global $messageStack;
    // cannot remove EC if DP installed:
    if (defined('MODULE_PAYMENT_PAYPALDP_STATUS')) {
      // this language text is hard-coded in english since Website Payments Pro is not yet available in any countries that speak any other language at this time.
      $messageStack->add_session('<strong>Sorry, you must remove PayPal Payments Pro (paypaldp) first.</strong> PayPal Payments Pro (Website Payments Pro) requires that you offer Express Checkout to your customers.<br /><a href="' . zen_href_link('modules.php?set=payment&module=paypaldp', '', 'NONSSL') . '">Click here to edit or remove your PayPal Payments Pro module.</a>' , 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paypalwpp', 'NONSSL'));
      return 'failed';
    }

    global $db;
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PAYPALWPP\_%' OR configuration_key LIKE 'MODULE\_PAYMENT\_PAYPALEC\_%'");
    $this->notify('NOTIFY_PAYMENT_PAYPALWPP_UNINSTALLED');
  }
  /**
   * Check settings and conditions to determine whether we are in an Express Checkout phase or not
   */
  function in_special_checkout() {
    if ((defined('MODULE_PAYMENT_PAYPALWPP_STATUS') && MODULE_PAYMENT_PAYPALWPP_STATUS == 'True') &&
             !empty($_SESSION['paypal_ec_token']) &&
             !empty($_SESSION['paypal_ec_payer_id']) &&
             !empty($_SESSION['paypal_ec_payer_info'])) {
      $this->flagDisablePaymentAddressChange = true;
      return true;
    }
  }
  /**
   * Determine whether the shipping-edit button should be displayed or not
   */
  function alterShippingEditButton() {
    return false;
    if ($this->in_special_checkout() && empty($_SESSION['paypal_ec_markflow'])) {
      return zen_href_link('ipn_main_handler.php', 'type=ec&clearSess=1', 'SSL', true,true, true);
    }
  }
  /**
   * Debug Logging support
   */
  function zcLog($stage, $message) {
    static $tokenHash;
    if ($tokenHash == '') $tokenHash = '_' . zen_create_random_value(4);
    if (MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log and Email' || MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log File') {
      $token = (isset($_SESSION['paypal_ec_token'])) ? $_SESSION['paypal_ec_token'] : ((isset($_GET['token'])) ? preg_replace('/[^0-9.A-Z\-]/', '', $_GET['token']) : '');
      $token = ($token == '') ? date('m-d-Y-H-i') : $token; // or time()
      $token .= $tokenHash;
      $file = $this->_logDir . '/' . $this->code . '_Paypal_Action_' . $token . '.log';
      if (defined('PAYPAL_DEV_MODE') && PAYPAL_DEV_MODE == 'true') $file = $this->_logDir . '/' . $this->code . '_Paypal_Debug_' . $token . '.log';
      $fp = @fopen($file, 'a');
      @fwrite($fp, date('M-d-Y H:i:s') . ' (' . time() . ')' . "\n" . $stage . "\n" . $message . "\n=================================\n\n");
      @fclose($fp);
    }
    $this->_doDebug($stage, $message, false);
  }
  /**
   * Debug Emailing support
   */
  function _doDebug($subject = 'PayPal debug data', $data, $useSession = true) {
    if (MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log and Email') {
      $data =  urldecode($data) . "\n\n";
      if ($useSession) $data .= "\nSession data: " . print_r($_SESSION, true);
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, $subject, $this->code . "\n" . $data, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($this->code . "\n" . $data)), 'debug');
    }
  }
  /**
   * Initialize the PayPal/PayflowPro object for communication to the processing gateways
   */
  function paypal_init() {
    if (!defined('MODULE_PAYMENT_PAYPALWPP_STATUS') || !defined('MODULE_PAYMENT_PAYPALWPP_SERVER')) {
      $doPayPal = new paypal_curl(array('mode' => 'NOTCONFIGURED'));
      return $doPayPal;
    }
    $ec_uses_gateway = (defined('MODULE_PAYMENT_PAYPALWPP_PRO20_EC_METHOD') && MODULE_PAYMENT_PAYPALWPP_PRO20_EC_METHOD == 'Payflow') ? true : false;
    if (substr(MODULE_PAYMENT_PAYPALWPP_MODULE_MODE,0,7) == 'Payflow') {
      $doPayPal = new paypal_curl(array('mode' => 'payflow',
                                        'user' =>   trim(MODULE_PAYMENT_PAYPALWPP_PFUSER),
                                        'vendor' => trim(MODULE_PAYMENT_PAYPALWPP_PFVENDOR),
                                        'partner'=> trim(MODULE_PAYMENT_PAYPALWPP_PFPARTNER),
                                        'pwd' =>    trim(MODULE_PAYMENT_PAYPALWPP_PFPASSWORD),
                                        'server' => MODULE_PAYMENT_PAYPALWPP_SERVER));
      $doPayPal->_endpoints = array('live'    => 'https://payflowpro.paypal.com/transaction',
                                    'sandbox' => 'https://pilot-payflowpro.paypal.com/transaction');
    } else {
      $doPayPal = new paypal_curl(array('mode' => 'nvp',
                                        'user' => trim(MODULE_PAYMENT_PAYPALWPP_APIUSERNAME),
                                        'pwd' =>  trim(MODULE_PAYMENT_PAYPALWPP_APIPASSWORD),
                                        'signature' => trim(MODULE_PAYMENT_PAYPALWPP_APISIGNATURE),
                                        'version' => '124.0',
                                        'server' => MODULE_PAYMENT_PAYPALWPP_SERVER));
      $doPayPal->_endpoints = array('live'    => 'https://api-3t.paypal.com/nvp',
                                    'sandbox' => 'https://api-3t.sandbox.paypal.com/nvp');
    }

    // set logging options
    $doPayPal->_logDir = $this->_logDir;
    $doPayPal->_logLevel = $this->_logLevel;

    // set proxy options if configured
    if (CURL_PROXY_REQUIRED == 'True' && CURL_PROXY_SERVER_DETAILS != '') {
      $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
      $doPayPal->setCurlOption(CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
      $doPayPal->setCurlOption(CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
      $doPayPal->setCurlOption(CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
    }

    // transaction processing mode
    $doPayPal->_trxtype = (in_array(MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE, array('Auth Only', 'Order'))) ? 'A' : 'S';

    return $doPayPal;
  }
  /**
   * Determine which PayPal URL to direct the customer's browser to when needed
   */
  function getPayPalLoginServer() {
    if (MODULE_PAYMENT_PAYPALWPP_SERVER == 'live') {
      // live url
      $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
    } else {
      // sandbox url
      $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    }
    return $paypal_url;
  }
  /**
   * Used to submit a refund for a given transaction.  FOR FUTURE USE.
   * @TODO: Add option to specify shipping/tax amounts for refund instead of just total. Ref: https://developer.paypal.com/docs/classic/release-notes/merchant/PayPal_Merchant_API_Release_Notes_119/
   */
  function _doRefund($oID, $amount = 'Full', $note = '') {
    global $db, $doPayPal, $messageStack;
    $new_order_status = (int)MODULE_PAYMENT_PAYPALWPP_REFUNDED_STATUS_ID;
    $orig_order_amount = 0;
    $doPayPal = $this->paypal_init();
    $proceedToRefund = false;
    $refundNote = strip_tags(zen_db_input($_POST['refnote']));
    if (isset($_POST['fullrefund']) && $_POST['fullrefund'] == MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_FULL) {
      $refundAmt = 'Full';
      if (isset($_POST['reffullconfirm']) && $_POST['reffullconfirm'] == 'on') {
        $proceedToRefund = true;
      } else {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_FULL_CONFIRM_ERROR, 'error');
      }
    }
    if (isset($_POST['partialrefund']) && $_POST['partialrefund'] == MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_PARTIAL) {
      $refundAmt = (float)$_POST['refamt'];
      $proceedToRefund = true;
      if ($refundAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_REFUND_AMOUNT, 'error');
        $proceedToRefund = false;
      }
    }

    // look up history on this order from PayPal table
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID  AND parent_txn_id = '' ";
    $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
    $zc_ppHist = $db->Execute($sql);
    if ($zc_ppHist->RecordCount() == 0) return false;
    $txnID = $zc_ppHist->fields['txn_id'];
    $curCode = $zc_ppHist->fields['mc_currency'];
    $PFamt = $zc_ppHist->fields['mc_gross'];
    if ($doPayPal->_mode == 'payflow' && $refundAmt == 'Full') $refundAmt = $PFamt;

    /**
     * Submit refund request to PayPal
     */
    if ($proceedToRefund) {
       $response = $doPayPal->RefundTransaction($oID, $txnID, $refundAmt, $refundNote, $curCode);

      //$this->zcLog("_doRefund($oID, $amount, $note):", print_r($response, true));

      $error = $this->_errorHandler($response, 'DoRefund');
      $new_order_status = ($new_order_status > 0 ? $new_order_status : 1);
      if (!$error) {
        if (!isset($response['GROSSREFUNDAMT'])) $response['GROSSREFUNDAMT'] = $refundAmt;
        // Success, so save the results
        $comments = 'REFUND INITIATED. Trans ID:' . $response['REFUNDTRANSACTIONID'] . (isset($response['PNREF']) ? $response['PNREF'] : '') . "\n" . ' Gross Refund Amt: ' . urldecode($response['GROSSREFUNDAMT']) . (isset($response['PPREF']) ? "\nPPRef: " . $response['PPREF'] : '') . "\n" . $refundNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_INITIATED, urldecode($response['GROSSREFUNDAMT']), urldecode($response['REFUNDTRANSACTIONID']). (isset($response['PNREF']) ? $response['PNREF'] : '')), 'success');
        return true;
      }
    }
  }

  /**
   * Used to authorize part of a given previously-initiated transaction.  FOR FUTURE USE.
   */
  function _doAuth($oID, $amt, $currency = 'USD') {
    global $db, $doPayPal, $messageStack;
    $doPayPal = $this->paypal_init();
    $authAmt = $amt;
    $new_order_status = (int)MODULE_PAYMENT_PAYPALWPP_ORDER_PENDING_STATUS_ID;

    if (isset($_POST['orderauth']) && $_POST['orderauth'] == MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_BUTTON_TEXT_PARTIAL) {
      $authAmt = (float)$_POST['authamt'];
      $new_order_status = MODULE_PAYMENT_PAYPALWPP_ORDER_STATUS_ID;
      if (isset($_POST['authconfirm']) && $_POST['authconfirm'] == 'on') {
        $proceedToAuth = true;
      } else {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_CONFIRM_ERROR, 'error');
        $proceedToAuth = false;
      }
      if ($authAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_AUTH_AMOUNT, 'error');
        $proceedToAuth = false;
      }
    }
    // look up history on this order from PayPal table
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID  AND parent_txn_id = '' ";
    $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
    $zc_ppHist = $db->Execute($sql);
    if ($zc_ppHist->RecordCount() == 0) return false;
    $txnID = $zc_ppHist->fields['txn_id'];
    /**
     * Submit auth request to PayPal
     */
    if ($proceedToAuth) {
      $response = $doPayPal->DoAuthorization($txnID, $authAmt, $currency);

      //$this->zcLog("_doAuth($oID, $amt, $currency):", print_r($response, true));

      $error = $this->_errorHandler($response, 'DoAuthorization');
      $new_order_status = ($new_order_status > 0 ? $new_order_status : 1);
      if (!$error) {
        // Success, so save the results
        $comments = 'AUTHORIZATION ADDED. Trans ID: ' . urldecode($response['TRANSACTIONID']) . "\n" . ' Amount:' . urldecode($response['AMT']) . ' ' . $currency;
        zen_update_orders_history($oID, $comments, null, $new_order_status, -1);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_INITIATED, urldecode($response['AMT'])), 'success');
        return true;
      }
    }
  }
  /**
   * Used to capture part or all of a given previously-authorized transaction.  FOR FUTURE USE.
   * (alt value for $captureType = 'NotComplete')
   */
  function _doCapt($oID, $captureType = 'Complete', $amt = 0, $currency = 'USD', $note = '') {
    global $db, $doPayPal, $messageStack;
    $doPayPal = $this->paypal_init();

    //@TODO: Read current order status and determine best status to set this to
    $new_order_status = (int)MODULE_PAYMENT_PAYPALWPP_ORDER_STATUS_ID;

    $orig_order_amount = 0;
    $doPayPal = $this->paypal_init();
    $proceedToCapture = false;
    $captureNote = strip_tags(zen_db_input($_POST['captnote']));
    if (isset($_POST['captfullconfirm']) && $_POST['captfullconfirm'] == 'on') {
      $proceedToCapture = true;
    } else {
      $messageStack->add_session(MODULE_PAYMENT_PAYPALWPP_TEXT_CAPTURE_FULL_CONFIRM_ERROR, 'error');
    }
    if (isset($_POST['captfinal']) && $_POST['captfinal'] == 'on') {
      $captureType = 'Complete';
    } else {
      $captureType = 'NotComplete';
    }
    if (isset($_POST['btndocapture']) && $_POST['btndocapture'] == MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_BUTTON_TEXT_FULL) {
      $captureAmt = (float)$_POST['captamt'];
      if ($captureAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_CAPTURE_AMOUNT, 'error');
        $proceedToCapture = false;
      }
    }
    // look up history on this order from PayPal table
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID  AND parent_txn_id = '' ";
    $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
    $zc_ppHist = $db->Execute($sql);
    if ($zc_ppHist->RecordCount() == 0) return false;
    $txnID = $zc_ppHist->fields['txn_id'];
    /**
     * Submit capture request to PayPal
     */
    if ($proceedToCapture) {
      $response = $doPayPal->DoCapture($txnID, $captureAmt, $currency, $captureType, '', $captureNote);

      //$this->zcLog("_doCapt($oID, $captureType, $amt, $currency, $note):", print_r($response, true));

      $error = $this->_errorHandler($response, 'DoCapture');
      $new_order_status = ($new_order_status > 0 ? $new_order_status : 1);
      if (!$error) {
        if (isset($response['PNREF'])) {
          if (!isset($response['AMT'])) $response['AMT'] = $captureAmt;
          if (!isset($response['ORDERTIME'])) $response['ORDERTIME'] = date("M-d-Y h:i:s");
        }
        // Success, so save the results
        $comments = 'FUNDS CAPTURED. Trans ID: ' . urldecode($response['TRANSACTIONID']) . (isset($response['PNREF']) ? $response['PNREF'] : ''). "\n" . ' Amount: ' . urldecode($response['AMT']) . ' ' . $currency . "\n" . 'Time: ' . urldecode($response['ORDERTIME']) . "\n" . 'Auth Code: ' . (!empty($response['AUTHCODE']) ? $response['AUTHCODE'] : $response['CORRELATIONID']) . (isset($response['PPREF']) ? "\nPPRef: " . $response['PPREF'] : '') . "\n" . $captureNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALWPP_TEXT_CAPT_INITIATED, urldecode($response['AMT']), urldecode(!empty($response['AUTHCODE']) ? $response['AUTHCODE'] : $response['CORRELATIONID']). (isset($response['PNREF']) ? $response['PNREF'] : '')), 'success');
        return true;
      }
    }
  }
  /**
   * Used to void a given previously-authorized transaction.  FOR FUTURE USE.
   */
  function _doVoid($oID, $note = '') {
    global $db, $doPayPal, $messageStack;
    $new_order_status = (int)MODULE_PAYMENT_PAYPALWPP_REFUNDED_STATUS_ID;
    $doPayPal = $this->paypal_init();
    $voidNote = strip_tags(zen_db_input($_POST['voidnote']));
    $voidAuthID = trim(strip_tags(zen_db_input($_POST['voidauthid'])));
    $proceedToVoid = false;
    if (isset($_POST['ordervoid']) && $_POST['ordervoid'] == MODULE_PAYMENT_PAYPAL_ENTRY_VOID_BUTTON_TEXT_FULL) {
      if (isset($_POST['voidconfirm']) && $_POST['voidconfirm'] == 'on') {
        $proceedToVoid = true;
      } else {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_CONFIRM_ERROR, 'error');
      }
    }
    // look up history on this order from PayPal table
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID  AND parent_txn_id = '' ";
    $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
    $sql = $db->bindVars($sql, ':transID', $voidAuthID, 'string');
    $zc_ppHist = $db->Execute($sql);
    if ($zc_ppHist->RecordCount() == 0) return false;
    $txnID = $zc_ppHist->fields['txn_id'];
    /**
     * Submit void request to PayPal
     */
    if ($proceedToVoid) {
      $response = $doPayPal->DoVoid($voidAuthID, $voidNote);

      //$this->zcLog("_doVoid($oID, $note):", print_r($response, true));

      $error = $this->_errorHandler($response, 'DoVoid');
      $new_order_status = ($new_order_status > 0 ? $new_order_status : 1);
      if (!$error) {
        // Success, so save the results
        $comments = 'VOIDED. Trans ID: ' . urldecode($response['AUTHORIZATIONID']). (isset($response['PNREF']) ? $response['PNREF'] : '') . (isset($response['PPREF']) ? "\nPPRef: " . $response['PPREF'] : '') . "\n" . $voidNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_INITIATED, urldecode($response['AUTHORIZATIONID']) . (isset($response['PNREF']) ? $response['PNREF'] : '')), 'success');
        return true;
      }
    }
  }
  /**
   * Determine the language to use when redirecting to the PayPal site
   * Order of selection: locale for current language, current-language-code, delivery-country, billing-country, store-country
   */
  function getLanguageCode($mode = 'ec') {
    global $order, $locales, $lng;
    if (!isset($lng) || (isset($lng) && !is_object($lng))) {
      $lng = new language;
    }
    $allowed_country_codes = array('US', 'AU', 'DE', 'FR', 'IT', 'GB', 'ES', 'AT', 'BE', 'CA', 'CH', 'CN', 'NL', 'PL', 'PT', 'BR', 'RU');
    $allowed_language_codes = array('da_DK', 'he_IL', 'id_ID', 'ja_JP', 'no_NO', 'pt_BR', 'ru_RU', 'sv_SE', 'th_TH', 'tr_TR', 'zh_CN', 'zh_HK', 'zh_TW');

    $additional_language_codes = array('de_DE', 'en_AU', 'en_GB', 'en_US', 'es_ES', 'fr_CA', 'fr_FR', 'it_IT', 'nl_NL', 'pl_PL', 'pt_PT');
    if ($mode == 'incontext') {
      $allowed_language_codes = array_merge($allowed_language_codes, $additional_language_codes);
      $allowed_country_codes = array();
    }

    $lang_code = '';
    $user_locale_info = array();
    if (isset($locales) && is_array($locales)) {
      $user_locale_info = $locales;
    }
    array_unshift($user_locale_info, $lng->get_browser_language());
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
   * Set the currency code -- use defaults if active currency is not a currency accepted by PayPal
   */
  function selectCurrency($val = '', $subset = 'EC') {
    $ec_currencies = array('CAD', 'EUR', 'GBP', 'JPY', 'USD', 'AUD', 'CHF', 'CZK', 'DKK', 'HKD', 'HUF', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'THB', 'MXN', 'ILS', 'PHP', 'TWD', 'BRL', 'MYR', 'TRY', 'RUB', 'INR');
    $dp_currencies = array('CAD', 'EUR', 'GBP', 'JPY', 'USD', 'AUD');
    $paypalSupportedCurrencies = ($subset == 'EC') ? $ec_currencies : $dp_currencies;

    // if using Pro 2.0 (UK), only the 6 currencies are supported.
    $paypalSupportedCurrencies = (MODULE_PAYMENT_PAYPALWPP_MODULE_MODE == 'Payflow-UK') ? $dp_currencies : $paypalSupportedCurrencies;

    $my_currency = substr(MODULE_PAYMENT_PAYPALWPP_CURRENCY, 5);
    if (MODULE_PAYMENT_PAYPALWPP_CURRENCY == 'Selected Currency') {
      $my_currency = ($val == '') ? $_SESSION['currency'] : $val;
    }

    if (!in_array($my_currency, $paypalSupportedCurrencies)) {
      $my_currency = (MODULE_PAYMENT_PAYPALWPP_MODULE_MODE == 'Payflow-UK') ? 'GBP' : 'USD';
    }
    return $my_currency;
  }
  /**
   * Calculate the amount based on acceptable currencies
   */
  function calc_order_amount($amount, $paypalCurrency, $applyFormatting = false) {
    global $currencies;
    $amount = ($amount * $currencies->get_value($paypalCurrency));
    if (in_array($paypalCurrency, array('JPY', 'HUF', 'TWD')) || (int)$currencies->get_decimal_places($paypalCurrency) == 0) {
      $amount = (int)$amount;
      $applyFormatting = FALSE;
    }
    return ($applyFormatting ? round($amount, $currencies->get_decimal_places($paypalCurrency)) : $amount);
  }
  /**
   * Set the state field depending on what PayPal requires for that country.
   * The shipping address state or province is required if the address is in one of the following countries: Argentina, Brazil, Canada, China, Indonesia, India, Japan, Mexico, Thailand, USA
   * https://developer.paypal.com/docs/classic/api/state_codes/
   */
  function setStateAndCountry(&$info) {
    global $db, $messageStack;
    switch ($info['country']['iso_code_2']) {
      case 'AU':
      case 'US':
      case 'CA':
      // Paypal only accepts two character state/province codes for some countries.
      if (strlen($info['state']) > 2) {
        $sql = "SELECT zone_code FROM " . TABLE_ZONES . " WHERE zone_name = :zoneName";
        $sql = $db->bindVars($sql, ':zoneName', $info['state'], 'string');
        $state = $db->Execute($sql);
        if (!$state->EOF) {
          $info['state'] = $state->fields['zone_code'];
        } else {
          $messageStack->add_session('header', MODULE_PAYMENT_PAYPALWPP_TEXT_STATE_ERROR, 'error');
          $this->terminateEC(MODULE_PAYMENT_PAYPALWPP_TEXT_STATE_ERROR);
        }
      }
      break;
      case 'AT':
      case 'BE':
      case 'FR':
      case 'DE':
      case 'CH':
      $info['state'] = '';
      break;
      case 'GB':
      break;
      default:
      $info['state'] = '';
    }
  }
  /**
   * Prepare subtotal and line-item detail content to send to PayPal
   */
  function getLineItemDetails($restrictedCurrency) {
    global $order, $currencies, $order_totals, $order_total_modules;

    // if not default currency, do not send subtotals or line-item details
    if (DEFAULT_CURRENCY != $order->info['currency'] || $restrictedCurrency != DEFAULT_CURRENCY) {
      $this->zcLog('getLineItemDetails 1', 'Not using default currency. Thus, no line-item details can be submitted.');
      return array();
    }
    if ($currencies->currencies[$_SESSION['currency']]['value'] != 1 || $currencies->currencies[$order->info['currency']]['value'] != 1) {
      $this->zcLog('getLineItemDetails 2', 'currency val not equal to 1.0000 - cannot proceed without coping with currency conversions. Aborting line-item details.');
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
    $optionsST['PAYMENTREQUEST_0_AMT'] = 0;
    $optionsST['PAYMENTREQUEST_0_ITEMAMT'] = 0;
    $optionsST['PAYMENTREQUEST_0_TAXAMT'] = 0;
    $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] = 0;
    $optionsST['PAYMENTREQUEST_0_SHIPDISCAMT'] = 0;
    $optionsST['PAYMENTREQUEST_0_HANDLINGAMT'] = 0;
    $optionsST['PAYMENTREQUEST_0_INSURANCEAMT'] = 0;
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
          if ($order_totals[$i]['code'] == 'ot_shipping') $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_total')    $optionsST['PAYMENTREQUEST_0_AMT']         = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_tax')      $optionsST['PAYMENTREQUEST_0_TAXAMT']     += round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_subtotal') $optionsST['PAYMENTREQUEST_0_ITEMAMT']     = round($order_totals[$i]['value'],2);
          if (strstr($order_totals[$i]['code'], 'insurance')) $optionsST['PAYMENTREQUEST_0_INSURANCEAMT'] += round($order_totals[$i]['value'],2);
          //$optionsST['SHIPDISCAMT'] = '';  // Not applicable
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

      $this->ot_merge = array();
      $this->notify('NOTIFY_PAYMENT_PAYPALEC_SUBTOTALS_TAX', $order, $order_totals);
      if (sizeof($this->ot_merge)) $optionsST = array_merge($optionsST, $this->ot_merge);

      if ($creditsApplied > 0) $optionsST['PAYMENTREQUEST_0_ITEMAMT'] -= $creditsApplied;
      if ($surcharges > 0) $optionsST['PAYMENTREQUEST_0_ITEMAMT'] += $surcharges;

      // Handle tax-included scenario
      if (DISPLAY_PRICE_WITH_TAX == 'true') $optionsST['PAYMENTREQUEST_0_TAXAMT'] = 0;

      $subtotalPRE = $optionsST;
      // Move shipping tax amount from Tax subtotal into Shipping subtotal for submission to PayPal, since PayPal applies tax to each line-item individually
      $module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
      if (zen_not_null($order->info['shipping_method']) && DISPLAY_PRICE_WITH_TAX != 'true') {
        if (isset($GLOBALS[$module]) && $GLOBALS[$module]->tax_class > 0) {
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
          $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] += $taxAdjustmentForShipping;
          $optionsST['PAYMENTREQUEST_0_TAXAMT'] -= $taxAdjustmentForShipping;
        }
      }
      $flagSubtotalsUnknownYet = (($optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] + $optionsST['PAYMENTREQUEST_0_SHIPDISCAMT'] + $optionsST['PAYMENTREQUEST_0_AMT'] + $optionsST['PAYMENTREQUEST_0_TAXAMT'] + $optionsST['PAYMENTREQUEST_0_ITEMAMT'] + $optionsST['PAYMENTREQUEST_0_INSURANCEAMT']) == 0);
    } else {
      // if we get here, we don't have any order-total information yet because the customer has clicked Express before starting normal checkout flow
      // thus, we must make a note to manually calculate subtotals, rather than relying on the more robust order-total infrastructure
      $flagSubtotalsUnknownYet = TRUE;
    }

    $decimals = $currencies->get_decimal_places($_SESSION['currency']);

    // loop thru all products to prepare details of quantity and price.
    for ($i=0, $n=sizeof($order->products), $k=-1; $i<$n; $i++) {
      // PayPal is inconsistent in how it handles zero-value line-items, so skip this entry if price is zero
      if ($order->products[$i]['final_price'] == 0) {
        continue;
      } else {
        $k++;
      }

      $optionsLI["L_PAYMENTREQUEST_0_NUMBER$k"] = $order->products[$i]['model'];
      $optionsLI["L_PAYMENTREQUEST_0_NAME$k"]   = $order->products[$i]['name'] . ' [' . (int)$order->products[$i]['id'] . ']';
      // Append *** if out-of-stock.
      $optionsLI["L_PAYMENTREQUEST_0_NAME$k"]  .= ((zen_get_products_stock($order->products[$i]['id']) - $order->products[$i]['qty']) < 0 ? STOCK_MARK_PRODUCT_OUT_OF_STOCK : '');
      // if there are attributes, loop thru them and add to description
      if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0 ) {
        for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
          $optionsLI["L_PAYMENTREQUEST_0_NAME$k"] .= "\n " . $order->products[$i]['attributes'][$j]['option'] .
                                        ': ' . $order->products[$i]['attributes'][$j]['value'];
        } // end loop
      } // endif attribute-info

      // PayPal can't handle fractional-quantity values, so convert it to qty 1 here
      if (is_float($order->products[$i]['qty']) && ($order->products[$i]['qty'] != (int)$order->products[$i]['qty'] || $flag_treat_as_partial)) {
        $optionsLI["L_PAYMENTREQUEST_0_NAME$k"] = '('.$order->products[$i]['qty'].' x ) ' . $optionsLI["L_PAYMENTREQUEST_0_NAME$k"];
        // zen_add_tax already handles whether DISPLAY_PRICES_WITH_TAX is set
        $optionsLI["L_PAYMENTREQUEST_0_AMT$k"] = zen_round(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $decimals) * $order->products[$i]['qty'], $decimals);
        $optionsLI["L_PAYMENTREQUEST_0_QTY$k"] = 1;
        // no line-item tax component
      } else {
        $optionsLI["L_PAYMENTREQUEST_0_QTY$k"] = $order->products[$i]['qty'];
        $optionsLI["L_PAYMENTREQUEST_0_AMT$k"] = zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $decimals);
      }

      $subTotalLI += ($optionsLI["L_PAYMENTREQUEST_0_QTY$k"] * $optionsLI["L_PAYMENTREQUEST_0_AMT$k"]);
//      $subTotalTax += ($optionsLI["L_PAYMENTREQUEST_0_QTY$k"] * $optionsLI["L_PAYMENTREQUEST_0_AMT$k"]);

      // add line-item for one-time charges on this product
      if ($order->products[$i]['onetime_charges'] != 0 ) {
        $k++;
        $optionsLI["L_PAYMENTREQUEST_0_NAME$k"]   = MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX . substr(htmlentities($order->products[$i]['name'], ENT_QUOTES, 'UTF-8'), 0, 120);
        $optionsLI["L_PAYMENTREQUEST_0_AMT$k"]    = zen_round(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), $decimals);
        $optionsLI["L_PAYMENTREQUEST_0_QTY$k"]    = 1;
//        $optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"] = zen_round(zen_calculate_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), $decimals);
        $subTotalLI += $optionsLI["L_PAYMENTREQUEST_0_AMT$k"];
//        $subTotalTax += $optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"];
      }
      $numberOfLineItemsProcessed = $k;

      $this->notify('NOTIFY_PAYPALWPP_GETLINEITEMDETAILS', $numberOfLineItemsProcessed, $optionsLI);

    }  // end for loopthru all products

    // add line items for any surcharges added by order-total modules
    if ($surcharges > 0) {
      $numberOfLineItemsProcessed++;
      $k = $numberOfLineItemsProcessed;
      $optionsLI["L_PAYMENTREQUEST_0_NAME$k"] = MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_SURCHARGES_LONG;
      $optionsLI["L_PAYMENTREQUEST_0_AMT$k"]  = $surcharges;
      $optionsLI["L_PAYMENTREQUEST_0_QTY$k"]  = 1;
      $subTotalLI += $surcharges;
    }

    // add line items for discounts such as gift certificates and coupons
    if ($creditsApplied > 0) {
      $numberOfLineItemsProcessed++;
      $k = $numberOfLineItemsProcessed;
      $optionsLI["L_PAYMENTREQUEST_0_NAME$k"]   = MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_DISCOUNTS_LONG;
      $optionsLI["L_PAYMENTREQUEST_0_AMT$k"]    = (-1 * $creditsApplied);
      $optionsLI["L_PAYMENTREQUEST_0_QTY$k"]    = 1;
      $subTotalLI -= $creditsApplied;
    }

    // Reformat properly
    // Replace & and = and % with * if found.
    // reformat properly according to API specs
    // Remove HTML markup from name if found
    for ($k=0, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
      $optionsLI["L_PAYMENTREQUEST_0_NAME$k"] = str_replace(array('&','=','%'), '*', $optionsLI["L_PAYMENTREQUEST_0_NAME$k"]);
      $optionsLI["L_PAYMENTREQUEST_0_NAME$k"] = zen_clean_html($optionsLI["L_PAYMENTREQUEST_0_NAME$k"], 'strong');
      $optionsLI["L_PAYMENTREQUEST_0_NAME$k"]   = substr($optionsLI["L_PAYMENTREQUEST_0_NAME$k"], 0, 127);
      $optionsLI["L_PAYMENTREQUEST_0_AMT$k"] = round($optionsLI["L_PAYMENTREQUEST_0_AMT$k"], 2);

      if (isset($optionsLI["L_PAYMENTREQUEST_0_NUMBER$k"])) {
        if ($optionsLI["L_PAYMENTREQUEST_0_NUMBER$k"] == '') {
          unset($optionsLI["L_PAYMENTREQUEST_0_NUMBER$k"]);
        } else {
          $optionsLI["L_PAYMENTREQUEST_0_NUMBER$k"] = str_replace(array('&','=','%'), '*', $optionsLI["L_PAYMENTREQUEST_0_NUMBER$k"]);
          $optionsLI["L_PAYMENTREQUEST_0_NUMBER$k"] = substr($optionsLI["L_PAYMENTREQUEST_0_NUMBER$k"], 0, 127);
        }
      }

//      if (isset($optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"]) && ($optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"] != '' || $optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"] > 0)) {
//        $optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"] = round($optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"], 2);
//      }
    }

    // Sanity Check of line-item subtotals
    for ($j=0; $j<$k; $j++) {
      $itemAMT = $optionsLI["L_PAYMENTREQUEST_0_AMT$j"];
      $itemQTY = $optionsLI["L_PAYMENTREQUEST_0_QTY$j"];
      $itemTAX = (isset($optionsLI["L_PAYMENTREQUEST_0_TAXAMT$j"]) ? $optionsLI["L_PAYMENTREQUEST_0_TAXAMT$j"] : 0);
      $sumOfLineItems += ($itemQTY * $itemAMT);
      $sumOfLineTax += ($itemQTY * $itemTAX);
    }
    $sumOfLineItems = round($sumOfLineItems, 2);
    $sumOfLineTax = round($sumOfLineTax, 2);

    if ($sumOfLineItems == 0) {
      $sumOfLineTax = 0;
      $optionsLI = array();
      $discountProblemsFlag = TRUE;
      if ($optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] == $optionsST['PAYMENTREQUEST_0_AMT']) {
        $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] = 0;
      }
    }

//    // Sanity check -- if tax-included pricing is causing problems, remove the numbers and put them in a comment instead:
//    $stDiffTaxOnly = (strval($sumOfLineItems - $sumOfLineTax - round($optionsST['AMT'], 2)) + 0);
//    $this->zcLog('tax sanity check', 'stDiffTaxOnly: ' . $stDiffTaxOnly . "\nsumOfLineItems: " . $sumOfLineItems . "\nsumOfLineTax: " . $sumOfLineTax . ' ' . $subTotalTax . ' ' . print_r(array_merge($optionsST, $optionsLI), true));
//    if (DISPLAY_PRICE_WITH_TAX == 'true' && $stDiffTaxOnly == 0 && ($optionsST['TAXAMT'] != 0 && $sumOfLineTax != 0)) {
//      $optionsNB['DESC'] = 'Tax included in prices: ' . $sumOfLineTax . ' (' . $optionsST['TAXAMT'] . ') ';
//      $optionsST['TAXAMT'] = 0;
//      for ($k=0, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
//        if (isset($optionsLI["L_TAXAMT$k"])) unset($optionsLI["L_TAXAMT$k"]);
//      }
//    }

//    // Do sanity check -- if any of the line-item subtotal math doesn't add up properly, skip line-item details,
//    // so that the order can go through even though PayPal isn't being flexible to handle Zen Cart's diversity
//    if ((strval($subTotalTax) - strval($sumOfLineTax)) > 0.02) {
//      $this->zcLog('getLineItemDetails 3', 'Tax Subtotal does not match sum of taxes for line-items. Tax details are being removed from line-item submission data.' . "\n" . $sumOfLineTax . ' ' . $subTotalTax . print_r(array_merge($optionsST, $optionsLI), true));
//      for ($k=0, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
//        if (isset($optionsLI["L_TAXAMT$k"])) unset($optionsLI["L_TAXAMT$k"]);
//      }
//      $subTotalTax = 0;
//      $sumOfLineTax = 0;
//    }

//    // If coupons exist and there's a calculation problem, then it's likely that taxes are incorrect, so reset L_TAXAMTn values
//    if ($creditsApplied > 0 && (strval($optionsST['TAXAMT']) != strval($sumOfLineTax))) {
//      $pre = $optionsLI;
//      for ($k=0, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
//        if (isset($optionsLI["L_TAXAMT$k"])) unset($optionsLI["L_TAXAMT$k"]);
//      }
//      $this->zcLog('getLineItemDetails 4', 'Coupons/Discounts have affected tax calculations, so tax details are being removed from line-item submission data.' . "\n" . $sumOfLineTax . ' ' . $optionsST['TAXAMT'] . "\n" . print_r(array_merge($optionsST, $pre, $optionsNB), true) . "\nAFTER:" . print_r(array_merge($optionsST, $optionsLI, $optionsNB), TRUE));
//      $subTotalTax = 0;
//      $sumOfLineTax = 0;
//    }

    // disable line-item tax details, leaving only TAXAMT subtotal as tax indicator
    for ($k=0, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
      if (isset($optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"])) unset($optionsLI["L_PAYMENTREQUEST_0_TAXAMT$k"]);
    }
    // if ITEMAMT >0 and subTotalLI > 0 and they're not equal ... OR subTotalLI minus sumOfLineItems isn't 0
    // check subtotals
    if ((strval($optionsST['PAYMENTREQUEST_0_ITEMAMT']) > 0 && strval($subTotalLI) > 0 && strval($subTotalLI) != strval($optionsST['PAYMENTREQUEST_0_ITEMAMT'])) || strval($subTotalLI) - strval($sumOfLineItems) != 0) {
      $this->zcLog('getLineItemDetails 5', 'Line-item subtotals do not add up properly. Line-item-details skipped.' . "\n" . strval($sumOfLineItems) . ' ' . strval($subTotalLI) . ' ' . print_r(array_merge($optionsST, $optionsLI), true));
      $optionsLI = array();
      $optionsLI["L_PAYMENTREQUEST_0_NAME0"] = MODULES_PAYMENT_PAYPALWPP_AGGREGATE_CART_CONTENTS;
      $optionsLI["L_PAYMENTREQUEST_0_AMT0"]  = $sumOfLineItems = $subTotalLI = $optionsST['PAYMENTREQUEST_0_ITEMAMT'];
    }

    // check whether discounts are causing a problem
    if (strval($optionsST['PAYMENTREQUEST_0_ITEMAMT']) <= 0) {
      $pre = array ( 'ST' => $optionsST, 'LI' => $optionsLI );
      $optionsST['PAYMENTREQUEST_0_ITEMAMT'] = $optionsST['PAYMENTREQUEST_0_AMT'];
      $optionsLI = array();
      $optionsLI["L_PAYMENTREQUEST_0_NAME0"] = MODULES_PAYMENT_PAYPALWPP_AGGREGATE_CART_CONTENTS;
      $optionsLI["L_PAYMENTREQUEST_0_AMT0"]  = $sumOfLineItems = $subTotalLI = $optionsST['PAYMENTREQUEST_0_ITEMAMT'];
      /*if ($optionsST['AMT'] < $optionsST['TAXAMT']) */ $optionsST['PAYMENTREQUEST_0_TAXAMT'] = 0;
      /*if ($optionsST['AMT'] < $optionsST['SHIPPINGAMT']) */ $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] = 0;
      $discountProblemsFlag = TRUE;
      $this->zcLog('getLineItemDetails 6', 'Discounts have caused the subtotal to calculate incorrectly. Line-item-details cannot be submitted.' . "\nBefore:" . print_r($pre, TRUE) . "\nAfter:" . print_r(array_merge($optionsST, $optionsLI), true));
    }

    // if AMT or ITEMAMT values are 0 (ie: certain OT modules disabled) or we've started express checkout without going through normal checkout flow, we have to get subtotals manually
    if ((!isset($optionsST['PAYMENTREQUEST_0_AMT']) || $optionsST['PAYMENTREQUEST_0_AMT'] == 0 || $flagSubtotalsUnknownYet == TRUE || $optionsST['PAYMENTREQUEST_0_ITEMAMT'] == 0) && $discountProblemsFlag != TRUE) {
      $optionsST['PAYMENTREQUEST_0_ITEMAMT'] = $sumOfLineItems;
      $optionsST['PAYMENTREQUEST_0_TAXAMT'] = $sumOfLineTax;
      if ($subTotalShipping > 0) $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] = $subTotalShipping;
      $optionsST['PAYMENTREQUEST_0_AMT'] = $sumOfLineItems + $optionsST['PAYMENTREQUEST_0_TAXAMT'] + $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'];
    }
    $this->zcLog('getLineItemDetails 7 - subtotal comparisons', 'BEFORE line-item calcs: ' . print_r($subtotalPRE, true) . ($flagSubtotalsUnknownYet == TRUE ? 'Subtotals Unknown Yet - ' : '') . 'AFTER doing line-item calcs: ' . print_r(array_merge($optionsST, $optionsLI, $optionsNB), true));

    // if subtotals are not adding up correctly, then skip sending any line-item or subtotal details to PayPal
    $stAll = round(strval($optionsST['PAYMENTREQUEST_0_ITEMAMT'] + $optionsST['PAYMENTREQUEST_0_TAXAMT'] + $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] + $optionsST['PAYMENTREQUEST_0_SHIPDISCAMT'] + $optionsST['PAYMENTREQUEST_0_HANDLINGAMT'] + $optionsST['PAYMENTREQUEST_0_INSURANCEAMT']), 2);
    $stDiff = strval($optionsST['PAYMENTREQUEST_0_AMT'] - $stAll);
    $stDiffRounded = (strval($stAll - round($optionsST['PAYMENTREQUEST_0_AMT'], 2)) + 0);

    // unset any subtotal values that are zero
    if (isset($optionsST['PAYMENTREQUEST_0_ITEMAMT']) && $optionsST['PAYMENTREQUEST_0_ITEMAMT'] == 0) unset($optionsST['PAYMENTREQUEST_0_ITEMAMT']);
    if (isset($optionsST['PAYMENTREQUEST_0_TAXAMT']) && $optionsST['PAYMENTREQUEST_0_TAXAMT'] == 0) unset($optionsST['PAYMENTREQUEST_0_TAXAMT']);
    if (isset($optionsST['PAYMENTREQUEST_0_SHIPPINGAMT']) && $optionsST['PAYMENTREQUEST_0_SHIPPINGAMT'] == 0) unset($optionsST['PAYMENTREQUEST_0_SHIPPINGAMT']);
    if (isset($optionsST['PAYMENTREQUEST_0_SHIPDISCAMT']) && $optionsST['PAYMENTREQUEST_0_SHIPDISCAMT'] == 0) unset($optionsST['PAYMENTREQUEST_0_SHIPDISCAMT']);
    if (isset($optionsST['PAYMENTREQUEST_0_HANDLINGAMT']) && $optionsST['PAYMENTREQUEST_0_HANDLINGAMT'] == 0) unset($optionsST['PAYMENTREQUEST_0_HANDLINGAMT']);
    if (isset($optionsST['PAYMENTREQUEST_0_INSURANCEAMT']) && $optionsST['PAYMENTREQUEST_0_INSURANCEAMT'] == 0) unset($optionsST['PAYMENTREQUEST_0_INSURANCEAMT']);

    // tidy up all values so that they comply with proper format (rounded to 2 decimals for PayPal US use )
    if (!defined('PAYPALWPP_SKIP_LINE_ITEM_DETAIL_FORMATTING') || PAYPALWPP_SKIP_LINE_ITEM_DETAIL_FORMATTING != 'true' || in_array($order->info['currency'], array('JPY', 'NOK', 'HUF', 'TWD'))) {
      if (is_array($optionsST)) foreach ($optionsST as $key=>$value) {
        $optionsST[$key] = round($value, ((int)$currencies->get_decimal_places($restrictedCurrency) == 0 ? 0 : 2));
      }
      if (is_array($optionsLI)) foreach ($optionsLI as $key=>$value) {
        if (substr($key, -6) == 'TAXAMT' && ($optionsLI[$key] == '' || $optionsLI[$key] == 0)) {
          unset($optionsLI[$key]);
        } else {
          if (strstr($key, 'AMT')) $optionsLI[$key] = round($value, ((int)$currencies->get_decimal_places($restrictedCurrency) == 0 ? 0 : 2));
        }
      }
    }

    $this->zcLog('getLineItemDetails 8', 'checking subtotals... ' . "\n" . print_r(array_merge(array('calculated total'=>round($stAll, ((int)$currencies->get_decimal_places($restrictedCurrency) == 0 ? 0 : 2))), $optionsST), true) . "\n-------------------\ndifference: " . ($stDiff + 0) . '  (abs+rounded: ' . ($stDiffRounded + 0) . ')');

    if ( $stDiffRounded != 0) {
      $this->zcLog('getLineItemDetails 9', 'Subtotals Bad. Skipping line-item/subtotal details');
      return array();
    }

    $this->zcLog('getLineItemDetails 10', 'subtotals balance - okay');

    // Send Subtotal and LineItem results back to be submitted to PayPal
    return array_merge($optionsST, $optionsLI, $optionsNB);
  }


  /**
   * This method sends the customer to PayPal's site
   * There, they will log in to their PayPal account, choose a funding source and shipping method
   * and then return to our store site with an EC token
   */
  function ec_step1() {
    global $order, $order_totals, $db, $doPayPal;

    // if cart is empty due to timeout on login or shopping cart page, go to timeout screen
    if ($_SESSION['cart']->count_contents() == 0) {
      $message = 'Logging out due to empty shopping cart.  Is session started properly? ... ' . "\nSESSION Details:\n" . print_r($_SESSION, TRUE) . 'GET:' . "\n" . print_r($_GET, TRUE);
      include_once(DIR_WS_MODULES . 'payment/paypal/paypal_functions.php');
      ipn_debug_email($message);
      zen_redirect(zen_href_link(FILENAME_TIME_OUT, '', 'SSL'));
    }

    // init new order object
    require(DIR_WS_CLASSES . 'order.php');
    $order = new order;

    // load the selected shipping module so that shipping taxes can be assessed
    require(DIR_WS_CLASSES . 'shipping.php');
    $shipping_modules = new shipping($_SESSION['shipping']);

    // load OT modules so that discounts and taxes can be assessed
    require(DIR_WS_CLASSES . 'order_total.php');
    $order_total_modules = new order_total;
    $order_totals = $order_total_modules->pre_confirmation_check();
    $order_totals = $order_total_modules->process();

    $doPayPal = $this->paypal_init();
    $options = array();

    // build line item details
    $options = $this->getLineItemDetails($this->selectCurrency());

    // Set currency and amount
    $options['PAYMENTREQUEST_0_CURRENCYCODE'] = $this->selectCurrency();
    $order_amount = $this->calc_order_amount($order->info['total'], $options['PAYMENTREQUEST_0_CURRENCYCODE']);

    // Determine the language to use when visiting the PP site
    $lc_code = $this->getLanguageCode();
    if ($lc_code != '') $options['LOCALECODE'] = $lc_code;

    //Gift Options
    $options['GIFTMESSAGEENABLE'] = 0;
    $options['GIFTRECEIPTEENABLE'] = 0;
    $options['GIFTWRAPENABLE'] = 0;
    $options['GIFTWRAPNAME'] = '';
    $options['GIFTWRAPAMOUNT'] = 0;

    $options['BUYEREMAILOPTINENABLE'] = 0;

    $options['CUSTOMERSERVICENUMBER'] = (defined('STORE_TELEPHONE_CUSTSERVICE') && STORE_TELEPHONE_CUSTSERVICE != '') ? substr(STORE_TELEPHONE_CUSTSERVICE, 0, 16) : '';

    // Store Name to appear on PayPal page
    $options['BRANDNAME'] = (defined('MODULE_PAYMENT_PAYPALWPP_BRANDNAME') && strlen(MODULE_PAYMENT_PAYPALWPP_BRANDNAME) > 3) ? substr(MODULE_PAYMENT_PAYPALWPP_BRANDNAME, 0, 127) : substr(STORE_NAME, 0, 127);

    // Payment Transaction/Authorization Mode
    $options['PAYMENTREQUEST_0_PAYMENTACTION'] = (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE == 'Auth Only') ? 'Authorization' : 'Sale';
    // for future:
    if (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE == 'Order') $options['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Order';
    // Allow delayed payments such as eCheck? (can only use InstantPayment if Action is Sale)
    if (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE != 'Auth Only' && MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE != 'Sale' && $options['PAYMENTREQUEST_0_PAYMENTACTION'] == 'Sale' && defined('MODULE_PAYMENT_PAYPALEC_ALLOWEDPAYMENT') && MODULE_PAYMENT_PAYPALEC_ALLOWEDPAYMENT == 'Instant Only') {
        $options['PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD'] = 'InstantPaymentOnly';
    }

    $options['ALLOWNOTE'] = 1;  // allow customer to enter a note on the PayPal site, which will be copied to order comments upon return to store.

    $options['SOLUTIONTYPE'] = 'Sole';  // Use 'Mark' for normal Express Checkout (where customer has a PayPal account), 'Sole' for Account-Optional. But Account-Optional must be enabled in your PayPal account under Selling Preferences.

    // PayPal has acknowledged that they have a bug which prevents Account-Optional from working in InContext mode, so we have to use 'Mark' for InContext to work as of Dec 2015:
    if ($this->use_incontext_checkout && $options['SOLUTIONTYPE'] == 'Sole') $options['SOLUTIONTYPE'] = 'Mark';

    $options['LANDINGPAGE'] = 'Billing';  // "Billing" or "Login" selects the style of landing page on PayPal site during checkout (ie: which "section" is expanded when arriving there)
    //$options['USERSELECTEDFUNDINGSOURCE'] = 'Finance';  // 'Finance' is for PayPal BillMeLater.  Requires LANDINGPAGE=Billing.

    // Set the return URL if they click "Submit" on PayPal site
    $return_url = str_replace('&amp;', '&', zen_href_link('ipn_main_handler.php', 'type=ec', 'SSL', true, true, true));
    // Select the return URL if they click "cancel" on PayPal site or click to return without making payment or login
    $cancel_url = str_replace('&amp;', '&', zen_href_link(($_SESSION['customer_first_name'] != '' && $_SESSION['customer_id'] != '' ? FILENAME_CHECKOUT_SHIPPING : FILENAME_SHOPPING_CART), 'ec_cancel=1', 'SSL'));

    // debug
    $val = $_SESSION; unset($val['navigation']);
    $this->zcLog('ec_step1 - 1', 'Checking to see if we are in markflow' . "\n" . 'cart contents: ' . $_SESSION['cart']->get_content_type() . "\n\nNOTE: " . '$this->showPaymentPage = ' . (int)$this->showPaymentPage . "\nCustomer ID: " . (int)$_SESSION['customer_id'] . "\nSession Data: " . print_r($val, true));

    /**
     * Check whether shipping is required on this order or not.
     * If not, tell PayPal to skip all shipping options
     * ie: don't ask for any shipping info if cart content is strictly virtual and customer is already logged-in
     * (if not logged in, we need address information only to build the customer record)
     */
    if ($_SESSION['cart']->get_content_type() == 'virtual' && zen_is_logged_in()) {
      $this->zcLog('ec-step1-addr_check', "cart contents is virtual and customer is logged in ... therefore options['NOSHIPPING']=1");
      $options['NOSHIPPING'] = 1;
    } else {
      $this->zcLog('ec-step1-addr_check', "cart content is not all virtual (or customer is not logged in) ... therefore will be submitting address details");
      $options['NOSHIPPING'] = 0;
      // If we are in a "mark" flow and the customer has a usable address, set the addressoverride variable to 1.
      // This will override the shipping address in PayPal with the shipping address that is selected in Zen Cart.
      // @TODO: consider using address-validation against Paypal's addresses via API to help customers understand why they may be having difficulties during checkout
      if (MODULE_PAYMENT_PAYPALWPP_CONFIRMED_ADDRESS != 'Yes' && ($address_arr = $this->getOverrideAddress()) !== false) {
        $address_error = false;
        foreach(array('entry_firstname','entry_lastname','entry_street_address','entry_city','entry_postcode','zone_code','countries_iso_code_2') as $val) {
          if ($address_arr[$val] == '') $address_error = true;
          if ($address_error == true) $this->zcLog('ec-step1-addr_check2', '$address_error = true because ' .$val . ' is blank.');
        }
        if ($address_error == false) {
          // set the override var
          $options['ADDROVERRIDE'] = 1;

          // set the address info
          $options['PAYMENTREQUEST_0_SHIPTONAME']    = substr($address_arr['entry_firstname'] . ' ' . $address_arr['entry_lastname'], 0, 128);
          $options['PAYMENTREQUEST_0_SHIPTOSTREET']  = substr($address_arr['entry_street_address'], 0, 100);
          if ($address_arr['entry_suburb'] != '') $options['PAYMENTREQUEST_0_SHIPTOSTREET2'] = substr($address_arr['entry_suburb'], 0, 100);
          $options['PAYMENTREQUEST_0_SHIPTOCITY']    = substr($address_arr['entry_city'], 0, 40);
          $options['PAYMENTREQUEST_0_SHIPTOZIP']     = substr($address_arr['entry_postcode'], 0, 20);
          $options['PAYMENTREQUEST_0_SHIPTOSTATE']   = substr($address_arr['zone_code'], 0, 40);
          $options['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = substr($address_arr['countries_iso_code_2'], 0, 2);
          if (!empty($address_arr['entry_telephone'])) {
            $options['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = substr($address_arr['entry_telephone'], 0, 20);
          }
        }
      }
      $this->zcLog('ec-step1-addr_check3', 'address details from override check:'.($address_arr == FALSE ? ' <NONE FOUND>' : print_r($address_arr, true)));

      // Do we require a "confirmed" shipping address ?
      if (MODULE_PAYMENT_PAYPALWPP_CONFIRMED_ADDRESS == 'Yes') {
        $options['REQCONFIRMSHIPPING'] = 1;
      }
    }
    // if we know customer's email address, supply it, so as to pre-fill the signup box at PayPal (useful for new PayPal accounts only)
    if (!empty($_SESSION['customer_first_name']) && !empty($_SESSION['customer_id'])) {
      $sql = "SELECT * FROM " . TABLE_CUSTOMERS . " WHERE customers_id = :custID ";
      $sql = $db->bindVars($sql, ':custID', $_SESSION['customer_id'], 'integer');
      $zc_getemail = $db->Execute($sql);
      if ($zc_getemail->RecordCount() > 0 && $zc_getemail->fields['customers_email_address'] != '') {
        $options['EMAIL'] = $zc_getemail->fields['customers_email_address'];
      }
      if ($zc_getemail->RecordCount() > 0 && $zc_getemail->fields['customers_telephone'] != '' && (!isset($options['ADDROVERRIDE']) || isset($options['ADDROVERRIDE']) && $options['ADDROVERRIDE'] != 1)) {
        $options['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = $zc_getemail->fields['customers_telephone'];
      }
    }

    if (!isset($options['PAYMENTREQUEST_0_AMT'])) $options['PAYMENTREQUEST_0_AMT'] = round($order_amount, 2);

    $this->zcLog('ec_step1 - 2 -submit', print_r(array_merge($options, array('RETURNURL' => $return_url, 'CANCELURL' => $cancel_url)), true));

    $this->notify('NOTIFY_PAYMENT_PAYPALEC_BEFORE_SETEC', array(), $options, $order, $order_totals);


    /**
     * Ask PayPal for the token with which to initiate communications
     */
    $response = $doPayPal->SetExpressCheckout($return_url, $cancel_url, $options);

    $this->notify('NOTIFY_PAYMENT_PAYPALEC_TOKEN', $response, $options);

  $submissionCheckOne = TRUE;
  $submissionCheckTwo = TRUE;
  if ($submissionCheckOne) {
    // If there's an error on line-item details, remove tax values and resubmit, since the most common cause of 10413 is tax mismatches
    if (isset($response['L_ERRORCODE0']) && $response['L_ERRORCODE0'] == '10413') {
      $this->zcLog('ec_step1 - 3 - removing tax portion', 'Tax Subtotal does not match sum of taxes for line-items. Tax details removed from line-item submission data.' . "\n" . print_r($options, true));
          //echo '1st submission REJECTED. {'.$response['L_ERRORCODE0'].'}<pre>'.print_r($options, true) . urldecode(print_r($response, true));
      $tsubtotal = 0;
      foreach ($options as $key=>$value) {
        if (substr($key, -6) == 'TAXAMT') {
          $tsubtotal += preg_replace('/[^0-9.\-]/', '', $value);
          unset($options[$key]);
        }
      }
      $options['PAYMENTREQUEST_0_TAXAMT'] = $tsubtotal;
      $amt = preg_replace('/[^0-9.%]/', '', $options['PAYMENTREQUEST_0_AMT']);
//      echo 'oldAMT:'.$amt;
//      echo ' newTAXAMT:'.$tsubtotal;
      $taxamt = preg_replace('/[^0-9.%]/', '', $options['PAYMENTREQUEST_0_TAXAMT']);
      $shipamt = preg_replace('/[^0-9.%]/', '', $options['PAYMENTREQUEST_0_SHIPPINGAMT']);
      $itemamt = preg_replace('/[^0-9.%]/', '', $options['PAYMENTREQUEST_0_ITEMAMT']);
      $calculatedAmount = $itemamt + $taxamt + $shipamt;
      if ($amt != $calculatedAmount) $amt = $calculatedAmount;
//      echo ' newAMT:'.$amt;
      $options['PAYMENTREQUEST_0_AMT'] = $amt;
      $response = $doPayPal->SetExpressCheckout($return_url, $cancel_url, $options);
//echo '<br>2nd submission. {'.$response['L_ERRORCODE0'].'}<pre>'.print_r($options, true);
    }
    if ($submissionCheckTwo) {
    if (isset($response['L_ERRORCODE0']) && $response['L_ERRORCODE0'] == '10413') {
      $this->zcLog('ec_step1 - 4 - removing line-item details', 'PayPal designed their own mathematics rules. Dumbing it down for them.' . "\n" . print_r($options, true));
//echo '2nd submission REJECTED. {'.$response['L_ERRORCODE0'].'}<pre>'.print_r($options, true) . urldecode(print_r($response, true));
      foreach ($options as $key=>$value) {
        if (substr($key, 0, 2) == 'L_') {
          unset($options[$key]);
        }
      }
      $amt = preg_replace('/[^0-9.%]/', '', $options['PAYMENTREQUEST_0_AMT']);
      $taxamt = preg_replace('/[^0-9.%]/', '', $options['PAYMENTREQUEST_0_TAXAMT']);
      $shipamt = preg_replace('/[^0-9.%]/', '', $options['PAYMENTREQUEST_0_SHIPPINGAMT']);
      $itemamt = preg_replace('/[^0-9.%]/', '', $options['PAYMENTREQUEST_0_ITEMAMT']);
      $calculatedAmount = $itemamt + $taxamt + $shipamt;
      if ($amt != $calculatedAmount) $amt = $calculatedAmount;
      $options['PAYMENTREQUEST_0_AMT'] = $amt;
      $response = $doPayPal->SetExpressCheckout($return_url, $cancel_url, $options);
//echo '<br>3rd submission. {'.$response['L_ERRORCODE0'].'}<pre>'.print_r($options, true);
    }
   }
  }

    /**
     * Determine result of request for token -- if error occurred, the errorHandler will redirect accordingly
     */
    $error = $this->_errorHandler($response, 'SetExpressCheckout');

    // Success, so read the EC token
    $_SESSION['paypal_ec_token'] = preg_replace('/[^0-9.A-Z\-]/', '', urldecode($response['TOKEN']));

    // prepare to redirect to PayPal so the customer can log in and make their selections
    $paypal_url = $this->getPayPalLoginServer();

    // incontext checkout URL override:
    if ($this->use_incontext_checkout) {
      $paypal_url = str_replace('cgi-bin/webscr', 'checkoutnow/', $paypal_url);
    }

    // Set the name of the displayed "continue" button on the PayPal site.
    // 'commit' = "Pay Now"  ||  'continue' = "Review Payment"
    $orderReview = true;
    if ($_SESSION['paypal_ec_markflow'] == 1) $orderReview = false;
    $userActionKey = "&useraction=" . ((int)$orderReview == false ? 'commit' : 'continue');

    $this->ec_redirect_url = $paypal_url . "?cmd=_express-checkout&token=" . $_SESSION['paypal_ec_token'] . $userActionKey;
    // This is where we actually redirect the customer's browser to PayPal. Upon return from PayPal, they go to ec_step2
    header("HTTP/1.1 302 Object Moved");
    zen_redirect($this->ec_redirect_url);

    // this should never be reached:
    return $error;
  }
  /**
     * This method is for step 2 of the express checkout option.  This
     * retrieves from PayPal the data set by step one and sets the Zen Cart
     * data accordingly depending on admin settings.
     */
  function ec_step2() {
    // Visitor just came back from PayPal and so we collect all
    // the info returned, create an account if necessary, then log
    // them in, and then send them to the appropriate page.
    if (empty($_SESSION['paypal_ec_token'])) {
      // see if the token is set -- if not, we cannot continue -- ideally the token should match the session token
      if (isset($_GET['token'])) {
        // we have a token, so we will proceed
        $_SESSION['paypal_ec_token'] = $_GET['token'];
        // sanitize this
        $_SESSION['paypal_ec_token'] = preg_replace('/[^0-9.A-Z\-]/', '', $_GET['token']);

      } else {
        // no token -- not ready for this step -- send them back to checkout page with error
        $this->terminateEC(MODULE_PAYMENT_PAYPALWPP_INVALID_RESPONSE, true);
      }
    }

    // debug
    //$this->zcLog('PayPal test Log - ec_step2 $_REQUEST data', "In function: ec_step2()\r\nData in \$_REQUEST = \r\n" . print_r($_REQUEST, true));

    // Initialize the paypal caller object.
    global $doPayPal;
    $doPayPal = $this->paypal_init();

    // with the token we retrieve the data about this user
    $response = $doPayPal->GetExpressCheckoutDetails($_SESSION['paypal_ec_token']);

    $this->notify('NOTIFY_PAYPALEC_PARSE_GETEC_RESULT', array(), $response);

    //$this->zcLog('ec_step2 - GetExpressCheckout response', print_r($response, true));

    /**
     * Determine result of request for data -- if error occurred, the errorHandler will redirect accordingly
     */
    $error = $this->_errorHandler($response, 'GetExpressCheckoutDetails');

    // Fill in possibly blank return values, prevents PHP notices in follow-on checking clause.
    $response_vars = array(
        'PAYMENTREQUEST_0_SHIPTONAME',
        'PAYMENTREQUEST_0_SHIPTOSTREET',
        'PAYMENTREQUEST_0_SHIPTOSTREET2',
        'PAYMENTREQUEST_0_SHIPTOCITY',
        'PAYMENTREQUEST_0_SHIPTOSTATE',
        'PAYMENTREQUEST_0_SHIPTOZIP',
        'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE',
    );
    $address_received = '';
    foreach ($response_vars as $response_var) {
        if (!isset($response[$response_var])) {
            $response[$response_var] = '';
        } else {
            $address_received .= $response[$response_var];
        }
    }
    // Check for blank address -- if address received from PayPal is blank, ask the customer to register in the store first and then resume checkout
    if ($_SESSION['cart']->get_content_type() != 'virtual' && $address_received == '') {
      $this->terminateEC(MODULES_PAYMENT_PAYPALWPP_TEXT_BLANK_ADDRESS, true, FILENAME_CREATE_ACCOUNT);
    }

    // will we be creating an account for this customer?  We must if the cart contents are virtual, so can login to download etc.
    if ($_SESSION['cart']->get_content_type('true') > 0 || in_array($_SESSION['cart']->content_type, array('mixed', 'virtual'))) $this->new_acct_notify = 'Yes';

    // get the payer_id from the customer's info as returned from PayPal
    $_SESSION['paypal_ec_payer_id'] = $response['PAYERID'];
    $this->notify('NOTIFY_PAYPAL_EXPRESS_CHECKOUT_PAYERID_DETERMINED', $response['PAYERID']);

    // More optional response elements; initialize them to prevent follow-on PHP notices.
    $response_optional = array(
        'PAYMENTREQUEST_0_SHIPTOPHONENUM',
        'PHONENUM',
        'BUSINESS',
    );
    foreach ($response_optional as $optional) {
        if (!isset($response[$optional])) {
            $response[$optional] = '';
        }
    }

    // prepare the information to pass to the ec_step2_finish() function, which does the account creation, address build, etc
    $step2_payerinfo = array('payer_id'        => $response['PAYERID'],
                             'payer_email'     => urldecode($response['EMAIL']),
                             'payer_salutation'=> '',
                             'payer_gender'    => '',
                             'payer_firstname' => urldecode($response['FIRSTNAME']),
                             'payer_lastname'  => urldecode($response['LASTNAME']),
                             'payer_business'  => urldecode($response['BUSINESS']),
                             'payer_status'    => $response['PAYERSTATUS'],
                             'ship_country_code'   => urldecode($response['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']),
                             'ship_address_status' => urldecode($response['PAYMENTREQUEST_0_ADDRESSSTATUS']),
                             'ship_phone'      => urldecode($response['PAYMENTREQUEST_0_SHIPTOPHONENUM'] != '' ? $response['PAYMENTREQUEST_0_SHIPTOPHONENUM'] : $response['PHONENUM']),
                             'order_comment'   => (isset($response['NOTE']) || isset($response['PAYMENTREQUEST_0_NOTETEXT']) ? urldecode($response['NOTE']) . ' ' . urldecode($response['PAYMENTREQUEST_0_NOTETEXT']) : ''),
                             );

//    if (strtoupper($response['ADDRESSSTATUS']) == 'NONE' || !isset($response['SHIPTOSTREET']) || $response['SHIPTOSTREET'] == '') {
//      $step2_shipto = array();
//    } else {
      // accomodate PayPal bug which repeats 1st line of address for 2nd line if 2nd line is empty.
      if ($response['PAYMENTREQUEST_0_SHIPTOSTREET2'] == $response['PAYMENTREQUEST_0_SHIPTOSTREET']) $response['PAYMENTREQUEST_0_SHIPTOSTREET2'] = '';

      // accomodate PayPal bug which incorrectly treats 'Yukon Territory' as YK instead of ISO standard of YT.
      if ($response['PAYMENTREQUEST_0_SHIPTOSTATE'] == 'YK') $response['PAYMENTREQUEST_0_SHIPTOSTATE'] = 'YT';
      // same with Newfoundland
      if ($response['PAYMENTREQUEST_0_SHIPTOSTATE'] == 'NF') $response['PAYMENTREQUEST_0_SHIPTOSTATE'] = 'NL';

      // process address details supplied
      $step2_shipto = array('ship_name'     => urldecode($response['PAYMENTREQUEST_0_SHIPTONAME']),
                            'ship_street_1' => urldecode($response['PAYMENTREQUEST_0_SHIPTOSTREET']),
                            'ship_street_2' => urldecode($response['PAYMENTREQUEST_0_SHIPTOSTREET2']),
                            'ship_city'     => urldecode($response['PAYMENTREQUEST_0_SHIPTOCITY']),
                            'ship_state'    => (isset($response['PAYMENTREQUEST_0_SHIPTOSTATE']) && $response['PAYMENTREQUEST_0_SHIPTOSTATE'] !='' ? urldecode($response['PAYMENTREQUEST_0_SHIPTOSTATE']) : urldecode($response['PAYMENTREQUEST_0_SHIPTOCITY'])),
                            'ship_postal_code' => urldecode($response['PAYMENTREQUEST_0_SHIPTOZIP']),
                            'ship_country_code'  => urldecode($response['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']),
                            'ship_country_name'  => (isset($response['SHIPTOCOUNTRY']) ? urldecode($response['SHIPTOCOUNTRY']) : (isset($response['SHIPTOCOUNTRYNAME']) ? urldecode($response['SHIPTOCOUNTRYNAME']) : '')));
//    }

    // reset all previously-selected shipping choices, because cart contents may have been changed
    if ((isset($response['SHIPPINGCALCULATIONMODE']) && $response['SHIPPINGCALCULATIONMODE'] != 'Callback') && (!(isset($_SESSION['paypal_ec_markflow']) && $_SESSION['paypal_ec_markflow'] == 1))) unset($_SESSION['shipping']);

    // set total temporarily based on amount returned from PayPal, so validations continue to work properly
    global $order, $order_totals;
    if (!isset($order) || !isset($order->info) || !is_array($order->info) || !zen_not_null($order)) {
      $this->zcLog('ec_step2 ', 'Re-instantiating $order object.');
      // init new order object
      if (!class_exists('order')) require(DIR_WS_CLASSES . 'order.php');
      $order = new order;

      // load the selected shipping module so that shipping taxes can be assessed
      if (!class_exists('shipping')) require(DIR_WS_CLASSES . 'shipping.php');
      $shipping_modules = new shipping($_SESSION['shipping']);

      // load OT modules so that discounts and taxes can be assessed
      if (!class_exists('order_total')) require(DIR_WS_CLASSES . 'order_total.php');
      $order_total_modules = new order_total;
      $order_totals = $order_total_modules->pre_confirmation_check();
      $order_totals = $order_total_modules->process();
      $this->zcLog('ec_step2 ', 'Instantiated $order object contents: ' . print_r($order, true));
    }
    if ($order->info['total'] < 0.01 && urldecode($response['PAYMENTREQUEST_0_AMT']) > 0) $order->info['total'] = urldecode($response['PAYMENTREQUEST_0_AMT']);
    //$this->zcLog('ec_step2 - processed info', print_r(array_merge($step2_payerinfo, $step2_shipto), true));

    // send data off to build account, log in, set addresses, place order
    $this->ec_step2_finish(array_merge($step2_payerinfo, $step2_shipto), $this->new_acct_notify);
  }

  /**
   * Complete the step2 phase by creating accounts if needed, linking data, placing order, etc.
   */
  function ec_step2_finish($paypal_ec_payer_info, $new_acct_notify) {
    global $db, $order;

    // register the payer_info in the session
    $_SESSION['paypal_ec_payer_info'] = $paypal_ec_payer_info;

    // debug
    $this->zcLog('ec_step2_finish - 1', 'START: paypal_ec_payer_info= ' . print_r($_SESSION['paypal_ec_payer_info'], true));

    /**
     * Building customer zone/address from returned data
     */
    // set some defaults, which will be updated later:
    $country_id = '223';
    $address_format_id = 2;
    $state_id = 0;
    $acct_exists = false;
    // store default address id for later use/reference
    $original_default_address_id = $_SESSION['customer_default_address_id'];

    // Get the customer's country ID based on name or ISO code
    $sql = "SELECT countries_id, address_format_id, countries_iso_code_2, countries_iso_code_3
                FROM " . TABLE_COUNTRIES . "
                WHERE countries_iso_code_2 = :countryId
                   OR countries_name = :countryId
                LIMIT 1";
    $sql1 = $db->bindVars($sql, ':countryId', $paypal_ec_payer_info['ship_country_name'], 'string');
    $country1 = $db->Execute($sql1);
    $sql2 = $db->bindVars($sql, ':countryId', $paypal_ec_payer_info['ship_country_code'], 'string');
    $country2 = $db->Execute($sql2);

    // see if we found a record, if yes, then use it instead of default American format
    if ($country1->RecordCount() > 0) {
      $country_id = $country1->fields['countries_id'];
      if (!isset($paypal_ec_payer_info['ship_country_code']) || $paypal_ec_payer_info['ship_country_code'] == '') $paypal_ec_payer_info['ship_country_code'] = $country1->fields['countries_iso_code_2'];
      $country_code3 = $country1->fields['countries_iso_code_3'];
      $address_format_id = (int)$country1->fields['address_format_id'];
    } elseif ($country2->RecordCount() > 0) {
      // if didn't find it based on name, check using ISO code (ie: in case of no-shipping-address required/supplied)
      $country_id = $country2->fields['countries_id'];
      $country_code3 = $country2->fields['countries_iso_code_3'];
      $address_format_id = (int)$country2->fields['address_format_id'];
    } else {
      // if defaulting to US, make sure US is valid
      $sql = "SELECT countries_id FROM " . TABLE_COUNTRIES . " WHERE countries_id = :countryId: LIMIT 1";
      $sql = $db->bindVars($sql, ':countryId:', $country_id, 'integer');
      $result = $db->Execute($sql);
      if ($result->EOF) {
        $this->notify('NOTIFY_PAYPAL_CUSTOMER_ATTEMPT_TO_USE_INVALID_COUNTRY_CODE');
        $this->zcLog('ec-step2-finish - 1b', 'Cannot use address due to country lookup/match failure.');
        $this->terminateEC(MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_ZONE_ERROR, true, FILENAME_SHOPPING_CART);
      }
    }
    // Need to determine zone, based on zone name first, and then zone code if name fails check. Otherwise uses 0.
    $sql = "SELECT zone_id
                  FROM " . TABLE_ZONES . "
                  WHERE zone_country_id = :zCountry
                  AND zone_code = :zoneCode
                   OR zone_name = :zoneCode
                  LIMIT 1";
    $sql = $db->bindVars($sql, ':zCountry', $country_id, 'integer');
    $sql = $db->bindVars($sql, ':zoneCode', $paypal_ec_payer_info['ship_state'], 'string');
    $states = $db->Execute($sql);
    if ($states->RecordCount() > 0) {
      $state_id = $states->fields['zone_id'];
    }
    /**
     * Using the supplied data from PayPal, set the data into the order record
     */
    // customer
    $order->customer['name']            = $paypal_ec_payer_info['payer_firstname'] . ' ' . $paypal_ec_payer_info['payer_lastname'];
    $order->customer['company']         = $paypal_ec_payer_info['payer_business'];
    $order->customer['street_address']  = $paypal_ec_payer_info['ship_street_1'];
    $order->customer['suburb']          = $paypal_ec_payer_info['ship_street_2'];
    $order->customer['city']            = $paypal_ec_payer_info['ship_city'];
    $order->customer['postcode']        = $paypal_ec_payer_info['ship_postal_code'];
    $order->customer['state']           = $paypal_ec_payer_info['ship_state'];
    $order->customer['country']         = array('id' => $country_id, 'title' => $paypal_ec_payer_info['ship_country_name'], 'iso_code_2' => $paypal_ec_payer_info['ship_country_code'], 'iso_code_3' => $country_code3);
    $order->customer['format_id']       = $address_format_id;
    $order->customer['email_address']   = $paypal_ec_payer_info['payer_email'];
    $order->customer['telephone']       = $paypal_ec_payer_info['ship_phone'];
    $order->customer['zone_id']         = $state_id;

    // billing
    $order->billing['name']             = $paypal_ec_payer_info['payer_firstname'] . ' ' . $paypal_ec_payer_info['payer_lastname'];
    $order->billing['company']          = $paypal_ec_payer_info['payer_business'];
    $order->billing['street_address']   = $paypal_ec_payer_info['ship_street_1'];
    $order->billing['suburb']           = $paypal_ec_payer_info['ship_street_2'];
    $order->billing['city']             = $paypal_ec_payer_info['ship_city'];
    $order->billing['postcode']         = $paypal_ec_payer_info['ship_postal_code'];
    $order->billing['state']            = $paypal_ec_payer_info['ship_state'];
    $order->billing['country']          = array('id' => $country_id, 'title' => $paypal_ec_payer_info['ship_country_name'], 'iso_code_2' => $paypal_ec_payer_info['ship_country_code'], 'iso_code_3' => $country_code3);
    $order->billing['format_id']        = $address_format_id;
    $order->billing['zone_id']          = $state_id;

    // delivery
    if (strtoupper($_SESSION['paypal_ec_payer_info']['ship_address_status']) != 'NONE') {
      $order->delivery['name']          = $paypal_ec_payer_info['ship_name'];
      $order->delivery['company']       = trim($paypal_ec_payer_info['ship_name'] . ' ' . $paypal_ec_payer_info['payer_business']);
      $order->delivery['street_address']= $paypal_ec_payer_info['ship_street_1'];
      $order->delivery['suburb']        = $paypal_ec_payer_info['ship_street_2'];
      $order->delivery['city']          = $paypal_ec_payer_info['ship_city'];
      $order->delivery['postcode']      = $paypal_ec_payer_info['ship_postal_code'];
      $order->delivery['state']         = $paypal_ec_payer_info['ship_state'];
      $order->delivery['country']       = array('id' => $country_id, 'title' => $paypal_ec_payer_info['ship_country_name'], 'iso_code_2' => $paypal_ec_payer_info['ship_country_code'], 'iso_code_3' => $country_code3);
      $order->delivery['country_id']    = $country_id;
      $order->delivery['format_id']     = $address_format_id;
      $order->delivery['zone_id']       = $state_id;
    }

    // process submitted customer notes
    if (isset($paypal_ec_payer_info['order_comment']) && $paypal_ec_payer_info['order_comment'] != '') {
      $_SESSION['comments'] = (isset($_SESSION['comments']) && $_SESSION['comments'] != '' ? $_SESSION['comments'] . "\n" : '') . $paypal_ec_payer_info['order_comment'];
      $order->info['comments'] = $_SESSION['comments'];
    }
    // debug
    $this->zcLog('ec_step2_finish - 2', 'country_id = ' . $country_id . ' ' . $paypal_ec_payer_info['ship_country_name'] . ' ' . $paypal_ec_payer_info['ship_country_code'] ."\naddress_format_id = " . $address_format_id . "\nstate_id = " . $state_id . ' (original state tested: ' . $paypal_ec_payer_info['ship_state'] . ')' . "\ncountry1->fields['countries_id'] = " . $country1->fields['countries_id'] . "\ncountry2->fields['countries_id'] = " . $country2->fields['countries_id'] . "\n" . '$order->customer = ' . print_r($order->customer, true));

    // check to see whether PayPal should still be offered to this customer, based on the zone of their address:
    $this->update_status();
    if (!$this->enabled) {
      $this->terminateEC(MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_ZONE_ERROR, true, FILENAME_SHOPPING_CART);
    }

    // see if the user is logged in
    if (!empty($_SESSION['customer_first_name']) && zen_is_logged_in()) {
      // They're logged in, so forward them straight to checkout stages, depending on address needs etc
      $order->customer['id'] = $_SESSION['customer_id'];

      // set the session value for express checkout temp
      $_SESSION['paypal_ec_temp'] = false;

      // -----
      // Allow an observer to override the default address-creation processing.
      //
      $bypass_address_creation = false;
      $this->notify('NOTIFY_PAYPALEXPRESS_BYPASS_ADDRESS_CREATION', $paypal_ec_payer_info, $bypass_address_creation);
      if ($bypass_address_creation) {
          $this->zcLog('ec_step2_finish - 2a', 'address-creation bypassed based on observer setting.');
      }

      // if no address required for shipping (or overridden by above), leave shipping portion alone
      if (!$bypass_address_creation && strtoupper($_SESSION['paypal_ec_payer_info']['ship_address_status']) != 'NONE' && $_SESSION['paypal_ec_payer_info']['ship_street_1'] != '') {
        // set the session info for the sendto
        $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];

        // This is the address matching section
        // try to match it first
        // note: this is by no means 100%
        $address_book_id = $this->findMatchingAddressBookEntry($_SESSION['customer_id'], (isset($order->delivery) ? $order->delivery : $order->billing));

        // no match, so add the record
        if (!$address_book_id) {
          $address_book_id = $this->addAddressBookEntry($_SESSION['customer_id'], (isset($order->delivery) ? $order->delivery : $order->billing), false);
        }
        // if couldn't add the record, perhaps due to removed country, or some other error, abort with message
        if (!$address_book_id) {
          $this->terminateEC(MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_ZONE_ERROR, true, FILENAME_SHOPPING_CART);
        }

        // set the address for use
        $_SESSION['sendto'] = $address_book_id;
      }
      // set the users billto information (default address)
      if (!isset($_SESSION['billto'])) {
        $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
      }

      // debug
      $this->zcLog('ec_step2_finish - 3', 'Exiting ec_step2_finish logged-in mode.' . "\n" . 'Selected address: ' . $address_book_id . "\nOriginal was: " . $original_default_address_id);


      // select a shipping method, based on cheapest available option
      if (MODULE_PAYMENT_PAYPALWPP_AUTOSELECT_CHEAPEST_SHIPPING == 'Yes') $this->setShippingMethod();

      // send the user on
      if ($_SESSION['paypal_ec_markflow'] == 1) {
        $this->terminateEC('', false, FILENAME_CHECKOUT_PROCESS);
      } else {
        $this->terminateEC('', false, FILENAME_CHECKOUT_CONFIRMATION);
      }
    } else {
      // They're not logged in.  Create an account if necessary, and then log them in.
      // First, see if they're an existing customer, and log them in automatically

      // If Paypal didn't supply us an email address, something went wrong
      if (trim($paypal_ec_payer_info['payer_email']) == '') $this->terminateEC(MODULE_PAYMENT_PAYPALWPP_INVALID_RESPONSE, true);

      // attempt to obtain the user information using the payer_email from the info returned from PayPal, via email address
      $sql = "SELECT customers_id, customers_firstname, customers_lastname, customers_paypal_payerid, customers_paypal_ec
              FROM " . TABLE_CUSTOMERS . "
              WHERE customers_email_address = :emailAddress ";
      $sql = $db->bindVars($sql, ':emailAddress', $paypal_ec_payer_info['payer_email'], 'string');
      $check_customer = $db->Execute($sql);

      // debug
      $this->zcLog('ec_step2_finish - 4', 'Not logged in. Looking for account.' . "\n" . (int)$check_customer->RecordCount() . ' matching customer records found.');

      if (!$check_customer->EOF) {
        $acct_exists = true;

        // see if this was only a temp account -- if so, remove it
        if ($check_customer->fields['customers_paypal_ec'] == '1') {
          // Delete the existing temporary account
          $this->ec_delete_user($check_customer->fields['customers_id']);
          $acct_exists = false;

          // debug
          $this->zcLog('ec_step2_finish - 5', 'Found temporary account - deleting it.');

        }
      }

      // Create an account, if the account does not exist
      if (!$acct_exists) {

        // debug
        $this->zcLog('ec_step2_finish - 6', 'No ZC account found for this customer. Creating new account.' . "\n" . '$this->new_acct_notify =' . $this->new_acct_notify);

        // Generate a random 8-char password
        $password = zen_create_random_value(8);

        $sql_data_array = array();

        // set the customer information in the array for the table insertion
        $sql_data_array = array(
            'customers_firstname'           => $paypal_ec_payer_info['payer_firstname'],
            'customers_lastname'            => $paypal_ec_payer_info['payer_lastname'],
            'customers_email_address'       => $paypal_ec_payer_info['payer_email'],
            'customers_email_format'        => (ACCOUNT_EMAIL_PREFERENCE == '1' ? 'HTML' : 'TEXT'),
            'customers_telephone'           => $paypal_ec_payer_info['ship_phone'],
            'customers_fax'                 => '',
            'customers_gender'              => $paypal_ec_payer_info['payer_gender'],
            'customers_newsletter'          => '0',
            'customers_password'            => zen_encrypt_password($password),
            'customers_paypal_payerid'      => $_SESSION['paypal_ec_payer_id']);

        // insert the data
        $result = zen_db_perform(TABLE_CUSTOMERS, $sql_data_array);

        // grab the customer_id (last insert id)
        $customer_id = $db->Insert_ID();

        // set the Guest customer ID -- for PWA purposes
        $_SESSION['customer_guest_id'] = $customer_id;

        $this->notify('NOTIFY_PAYPALEXPRESS_CREATE_ACCOUNT_ADDED_CUSTOMER_RECORD', $customer_id, $sql_data_array);

        // set the customer address information in the array for the table insertion
        $sql_data_array = array(
            'customers_id'              => $customer_id,
            'entry_gender'              => $paypal_ec_payer_info['payer_gender'],
            'entry_firstname'           => $paypal_ec_payer_info['payer_firstname'],
            'entry_lastname'            => $paypal_ec_payer_info['payer_lastname'],
            'entry_street_address'      => $paypal_ec_payer_info['ship_street_1'],
            'entry_suburb'              => $paypal_ec_payer_info['ship_street_2'],
            'entry_city'                => $paypal_ec_payer_info['ship_city'],
            'entry_zone_id'             => $state_id,
            'entry_postcode'            => $paypal_ec_payer_info['ship_postal_code'],
            'entry_country_id'          => $country_id);
        if (isset($paypal_ec_payer_info['ship_name']) && $paypal_ec_payer_info['ship_name'] != ''  && $paypal_ec_payer_info['ship_name'] != $paypal_ec_payer_info['payer_firstname'] . ' ' . $paypal_ec_payer_info['payer_lastname']) {
          $sql_data_array['entry_company'] = $paypal_ec_payer_info['ship_name'];
        }
        if ($state_id > 0) {
          $sql_data_array['entry_zone_id'] = $state_id;
          $sql_data_array['entry_state'] = '';
        } else {
          $sql_data_array['entry_zone_id'] = 0;
          $sql_data_array['entry_state'] = $paypal_ec_payer_info['ship_state'];
        }

        // insert the data
        zen_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

        // grab the address_id (last insert id)
        $address_id = $db->Insert_ID();

        $this->notify('NOTIFY_PAYPALEXPRESS_CREATE_ACCOUNT_ADDED_ADDRESS_BOOK_RECORD', array(), $address_id, $sql_data_array);

        // set the address id lookup for the customer
        $sql = "UPDATE " . TABLE_CUSTOMERS . "
                SET customers_default_address_id = :addrID
                WHERE customers_id = :custID";
        $sql = $db->bindVars($sql, ':addrID', $address_id, 'integer');
        $sql = $db->bindVars($sql, ':custID', $customer_id, 'integer');
        $db->Execute($sql);

        // insert the new customer_id into the customers info table for consistency
        $sql = "INSERT INTO " . TABLE_CUSTOMERS_INFO . "
                       (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created, customers_info_date_of_last_logon)
                VALUES (:custID, 1, now(), now())";
        $sql = $db->bindVars($sql, ':custID', $customer_id, 'integer');
        $db->Execute($sql);

        // send Welcome Email if appropriate
        if ($this->new_acct_notify == 'Yes') {
          // require the language file
          require(zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . "/", 'create_account.php', 'false'));
          // set the mail text
          $email_text = sprintf(EMAIL_GREET_NONE, $paypal_ec_payer_info['payer_firstname']) . EMAIL_WELCOME . "\n\n" . EMAIL_TEXT;
          $email_text .= "\n\n" . EMAIL_EC_ACCOUNT_INFORMATION . "\nUsername: " . $paypal_ec_payer_info['payer_email'] . "\nPassword: " . $password . "\n\n";
          $email_text .= EMAIL_CONTACT;
          // include create-account-specific disclaimer
          $email_text .= "\n\n" . sprintf(EMAIL_DISCLAIMER_NEW_CUSTOMER, STORE_OWNER_EMAIL_ADDRESS). "\n\n";
          $email_html = array();
          $email_html['EMAIL_GREETING']      = sprintf(EMAIL_GREET_NONE, $paypal_ec_payer_info['payer_firstname']) ;
          $email_html['EMAIL_WELCOME']       = EMAIL_WELCOME;
          $email_html['EMAIL_MESSAGE_HTML']  = nl2br(EMAIL_TEXT . "\n\n" . EMAIL_EC_ACCOUNT_INFORMATION . "\nUsername: " . $paypal_ec_payer_info['payer_email'] . "\nPassword: " . $password . "\n\n");
          $email_html['EMAIL_CONTACT_OWNER'] = EMAIL_CONTACT;
          $email_html['EMAIL_CLOSURE']       = nl2br(EMAIL_GV_CLOSURE);
          $email_html['EMAIL_DISCLAIMER']    = sprintf(EMAIL_DISCLAIMER_NEW_CUSTOMER, '<a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">'. STORE_OWNER_EMAIL_ADDRESS .' </a>');

          // send the mail
          if (trim(EMAIL_SUBJECT) != 'n/a') zen_mail($paypal_ec_payer_info['payer_firstname'] . " " . $paypal_ec_payer_info['payer_lastname'], $paypal_ec_payer_info['payer_email'], EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_html, 'welcome');

          // set the express checkout temp -- false means the account is no longer "only" for EC ... it'll be permanent
          $_SESSION['paypal_ec_temp'] = false;
        } else {
          // Make it a temporary account that'll be deleted once they've checked out
          $sql = "UPDATE " . TABLE_CUSTOMERS . "
                  SET customers_paypal_ec = 1
                  WHERE customers_id = :custID ";
          $sql = $db->bindVars($sql, ':custID', $customer_id, 'integer');
          $db->Execute($sql);

          // set the boolean ec temp value since we created account strictly for EC purposes
          $_SESSION['paypal_ec_temp'] = true;
        }

        // hook notifier class vis a vis account-creation
        $this->notify('NOTIFY_LOGIN_SUCCESS_VIA_CREATE_ACCOUNT', 'paypal express checkout');

      } else {
        // set the boolean ec temp value for the account to false, since we didn't have to create one
        $_SESSION['paypal_ec_temp'] = false;
      }

      // log the user in with the email sent back from paypal response
      $this->user_login($_SESSION['paypal_ec_payer_info']['payer_email'], false);

      // debug
      $this->zcLog('ec_step2_finish - 7', 'Auto-Logged customer in. (' . $_SESSION['paypal_ec_payer_info']['payer_email'] . ') (' . $_SESSION['customer_id'] . ')' . "\n" . '$_SESSION[paypal_ec_temp]=' . $_SESSION['paypal_ec_temp']);


      // This is the address matching section
      // try to match it first
      // note: this is by no means 100%
      $address_book_id = $this->findMatchingAddressBookEntry($_SESSION['customer_id'], (isset($order->delivery) ? $order->delivery : $order->billing));
      // no match add the record
      if (!$address_book_id) {
        $address_book_id = $this->addAddressBookEntry($_SESSION['customer_id'], (isset($order->delivery) ? $order->delivery : $order->billing), false);
        if (!$address_book_id) {
          $address_book_id = $_SESSION['customer_default_address_id'];
        }
      }
      // if couldn't add the record, perhaps due to removed country, or some other error, abort with message
      if ($address_book_id == FALSE) {
        $this->terminateEC(MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_ZONE_ERROR, true, FILENAME_SHOPPING_CART);
      }

      // set the sendto to the address
      $_SESSION['sendto'] = $address_book_id;
      // set billto in the session
      $_SESSION['billto'] = $_SESSION['customer_default_address_id'];

      // select a shipping method, based on cheapest available option
      if (MODULE_PAYMENT_PAYPALWPP_AUTOSELECT_CHEAPEST_SHIPPING == 'Yes') $this->setShippingMethod();

      // debug
      $this->zcLog('ec_step2_finish - 8', 'Exiting via terminateEC (from originally-not-logged-in mode).' . "\n" . 'Selected address: ' . $address_book_id . "\nOriginal was: " . (int)$original_default_address_id . "\nprepared data: " . print_r($order->customer, true));

      $this->notify('NOTIFY_PAYPALEC_END_ECSTEP2', $order);

      // send the user on
      if ($_SESSION['paypal_ec_markflow'] == 1) {
        $this->terminateEC('', false, FILENAME_CHECKOUT_PROCESS);
      } else {
        $this->terminateEC('', false, FILENAME_CHECKOUT_CONFIRMATION);
      }
    }
  }
  /**
   * Determine the appropriate shipping method if applicable
   * By default, selects the lowest-cost quote
   */
  function setShippingMethod() {
    global $total_count, $total_weight;
    // ensure that cart contents is calculated properly for weight and value
    if (!isset($total_weight)) $total_weight = $_SESSION['cart']->show_weight();
    if (!isset($total_count)) $total_count = $_SESSION['cart']->count_contents();
    // set the shipping method if one is not already set
    // defaults to the cheapest shipping method
    if ((!isset($_SESSION['shipping']) || (!isset($_SESSION['shipping']['id']) || $_SESSION['shipping']['id'] == '') && zen_count_shipping_modules() >= 1)) {
      require_once(DIR_WS_CLASSES . 'http_client.php');
      require_once(DIR_WS_CLASSES . 'shipping.php');
      $shipping_Obj = new shipping;

      // generate the quotes
      $shipping_Obj->quote();

      // set the cheapest one
      $_SESSION['shipping'] = $shipping_Obj->cheapest();
    }
  }
  /**
   * Get Override Address (uses sendto if set, otherwise uses customer's primary address)
   */
  function getOverrideAddress() {
    global $db;

    // Only proceed IF *in* markflow mode AND logged-in (have to be logged in to get to markflow mode anyway)
    if (!empty($_GET['markflow']) && zen_is_logged_in()) {
      // From now on for this user we will edit addresses in Zen Cart, not by going to PayPal.
      $_SESSION['paypal_ec_markflow'] = 1;


      // debug
      $this->zcLog('getOverrideAddress - 1', 'Now in markflow mode.' . "\n" . 'SESSION[sendto] = ' . (int)$_SESSION['sendto']);


      // find the users default address id
      if (!empty($_SESSION['sendto'])) {
        $address_id = $_SESSION['sendto'];
      } else {
        $sql = "SELECT customers_default_address_id
                FROM " . TABLE_CUSTOMERS . "
                WHERE customers_id = :customerId";
        $sql = $db->bindVars($sql, ':customerId', $_SESSION['customer_id'], 'integer');
        $default_address_id_arr = $db->Execute($sql);
        if (!$default_address_id_arr->EOF) {
          $address_id = $default_address_id_arr->fields['customers_default_address_id'];
        } else {
          // couldn't find an address.
          return false;
        }
      }
      // now grab the address from the database and set it as the overridden address
      $sql = "SELECT entry_firstname, entry_lastname, entry_company,
                     entry_street_address, entry_suburb, entry_city, entry_postcode,
                     entry_country_id, entry_zone_id, entry_state
              FROM " . TABLE_ADDRESS_BOOK . "
              WHERE address_book_id = :addressId
              AND customers_id = :customerId
              LIMIT 1";
      $sql = $db->bindVars($sql, ':addressId', $address_id, 'integer');
      $sql = $db->bindVars($sql, ':customerId', $_SESSION['customer_id'], 'integer');
      $address_arr = $db->Execute($sql);

      // see if we found a record, if not then we have nothing to override with
      if (!$address_arr->EOF) {
        // get the state/prov code
        $sql = "SELECT zone_code
                FROM " . TABLE_ZONES . "
                WHERE zone_id = :zoneId";
        $sql = $db->bindVars($sql, ':zoneId', $address_arr->fields['entry_zone_id'], 'integer');
        $state_code_arr = $db->Execute($sql);
        if ($state_code_arr->EOF) {
          $state_code_arr->fields['zone_code'] = '';
        }
        if ($state_code_arr->fields['zone_code'] == '' && $address_arr->fields['entry_state'] != '') {
          $state_code_arr->fields['zone_code'] = $address_arr->fields['entry_state'];
        }
        $address_arr->fields['zone_code'] = $state_code_arr->fields['zone_code'];

        // get the country code
        // ISO 3166 standard country code
        $sql = "SELECT countries_iso_code_2
                FROM " . TABLE_COUNTRIES . "
                WHERE countries_id = :countryId";
        $sql = $db->bindVars($sql, ':countryId', $address_arr->fields['entry_country_id'], 'integer');
        $country_code_arr = $db->Execute($sql);
        if ($country_code_arr->EOF) {
          // default to US if not found
          $country_code_arr->fields['countries_iso_code_2'] = 'US';
        }
        $address_arr->fields['countries_iso_code_2'] = $country_code_arr->fields['countries_iso_code_2'];

        // debug
        $this->zcLog('getOverrideAddress - 2', '$address_arr->fields = ' . print_r($address_arr->fields, true));

        // return address data.
        return $address_arr->fields;
      }
      // debug
      $this->zcLog('getOverrideAddress - 3', 'no override record found');
    }
    // debug
    $this->zcLog('getOverrideAddress - 4', 'not logged in and not in markflow mode - nothing to override');

    return false;
  }
  /**
     * This method attempts to match items in an address book, to avoid
     * duplicate entries to the address book.  On a successful match it
     * returns the address_book_id(int) -  on failure it returns false.
     *
     * @param int $customer_id
     * @param array $address_question_arr
     * @return int|boolean
     */
  function findMatchingAddressBookEntry($customer_id, $address_question_arr) {
    global $db;

    // if address is blank, don't do any matching
    if ($address_question_arr['street_address'] == '') return false;

    // default
    $country_id = '223';
    $address_format_id = 2; //2 is the American format

    // first get the zone id's from the 2 digit iso codes
    // country first
    $sql = "SELECT countries_id, address_format_id
                FROM " . TABLE_COUNTRIES . "
                WHERE countries_iso_code_2 = :countryCode:
                OR countries_name = :countryName:
                OR countries_iso_code_2 = :countryName:
                OR countries_name = :countryCode:
                LIMIT 1";
    $sql = $db->bindVars($sql, ':countryCode:', $address_question_arr['country']['iso_code_2'], 'string');
    $sql = $db->bindVars($sql, ':countryName:', $address_question_arr['country']['title'], 'string');
    $country = $db->Execute($sql);

    // see if we found a record, if not default to American format
    if (!$country->EOF) {
      $country_id = $country->fields['countries_id'];
      $address_format_id = $country->fields['address_format_id'];
    }

    // see if the country code has a state
    $sql = "SELECT zone_id
            FROM " . TABLE_ZONES . "
            WHERE zone_country_id = :zoneId
            LIMIT 1";
    $sql = $db->bindVars($sql, ':zoneId', $country_id, 'integer');
    $country_zone_check = $db->Execute($sql);
    $check_zone = $country_zone_check->RecordCount();

    $zone_id = 0;
    $logMsg = array('zone_id' => '-not found-');

    // now try and find the zone_id (state/province code)
    // use the country id above
    if ($check_zone) {
      $sql = "SELECT zone_id
              FROM " . TABLE_ZONES . "
              WHERE zone_country_id = :zoneId
                AND (zone_code = :zoneCode
                 OR zone_name = :zoneCode )
              LIMIT 1";
      $sql = $db->bindVars($sql, ':zoneId', $country_id, 'integer');
      $sql = $db->bindVars($sql, ':zoneCode', $address_question_arr['state'], 'string');
      $zone = $db->Execute($sql);
      if (!$zone->EOF) {
        // grab the id
        $zone_id = $zone->fields['zone_id'];
        $logMsg = $zone->fields;
      } else {
        $check_zone = false;
      }
    }
    // debug
    $this->zcLog('findMatchingAddressBookEntry - 1-stats', 'lookups:' . "\n" . print_r(array_merge($country->fields, array('zone_country_id' => $country_zone_check->fields), $logMsg), true) . "\n" . 'check_zone: ' . $check_zone . "\n" . 'zone:' . $zone_id . "\nSubmittedAddress:".print_r($address_question_arr, TRUE));

    // do a match on address, street, street2, city
    $sql = "SELECT address_book_id, entry_street_address, entry_suburb, entry_city, entry_company, entry_firstname, entry_lastname
                FROM " . TABLE_ADDRESS_BOOK . "
                WHERE customers_id = :customerId
                AND entry_country_id = :countryId";
    if ($check_zone) {
      $sql .= "  AND entry_zone_id = :zoneId";
    }
    $sql = $db->bindVars($sql, ':zoneId', $zone_id, 'integer');
    $sql = $db->bindVars($sql, ':countryId', $country_id, 'integer');
    $sql = $db->bindVars($sql, ':customerId', $customer_id, 'integer');
    $answers_arr = $db->Execute($sql);
    // debug
    $this->zcLog('findMatchingAddressBookEntry - 2-read for match', "\nLookup RecordCount = " . $answers_arr->RecordCount());

    if (!$answers_arr->EOF) {
      // build a base string to compare street+suburb+city content
      //$matchQuestion = str_replace("\n", '', $address_question_arr['company']);
      //$matchQuestion = str_replace("\n", '', $address_question_arr['name']);
      $matchQuestion = str_replace("\n", '', $address_question_arr['street_address']);
      $matchQuestion = trim($matchQuestion);
      $matchQuestion = $matchQuestion . str_replace("\n", '', $address_question_arr['suburb']);
      $matchQuestion = $matchQuestion . str_replace("\n", '', $address_question_arr['city']);
      $matchQuestion = str_replace("\t", '', $matchQuestion);
      $matchQuestion = trim($matchQuestion);
      $matchQuestion = strtolower($matchQuestion);
      $matchQuestion = str_replace(' ', '', $matchQuestion);

      // go through the data
      while (!$answers_arr->EOF) {
        // now the matching logic

        // first from the db
        $fromDb = '';
//        $fromDb = str_replace("\n", '', $answers_arr->fields['entry_company']);
///        $fromDb = str_replace("\n", '', $answers_arr->fields['entry_firstname'].$answers_arr->fields['entry_lastname']);
        $fromDb = str_replace("\n", '', $answers_arr->fields['entry_street_address']);
        $fromDb = trim($fromDb);
        $fromDb = $fromDb . str_replace("\n", '', $answers_arr->fields['entry_suburb']);
        $fromDb = $fromDb . str_replace("\n", '', $answers_arr->fields['entry_city']);
        $fromDb = str_replace("\t", '', $fromDb);
        $fromDb = trim($fromDb);
        $fromDb = strtolower($fromDb);
        $fromDb = str_replace(' ', '', $fromDb);

        // debug
        $this->zcLog('findMatchingAddressBookEntry - 3a', "From PayPal:\r\n" . $matchQuestion . "\r\n\r\nFrom DB:\r\n" . $fromDb . "\r\n". print_r($answers_arr->fields, true));

        // check the strings
        if (strlen($fromDb) == strlen($matchQuestion)) {
          if ($fromDb == $matchQuestion) {
            // exact match return the id
            // debug
            $this->zcLog('findMatchingAddressBookEntry - 3b', "Exact match:\n" . print_r($answers_arr->fields, true));
            return $answers_arr->fields['address_book_id'];
          }
        } elseif (strlen($fromDb) > strlen($matchQuestion)) {
          if (substr($fromDb, 0, strlen($matchQuestion)) == $matchQuestion) {
            // we have a match return it (PP)
            // debug
            $this->zcLog('findMatchingAddressBookEntry - 3b', "partial match (PP):\n" . print_r($answers_arr->fields, true));
            return $answers_arr->fields['address_book_id'];
          }
        } else {
          if ($fromDb == substr($matchQuestion, 0, strlen($fromDb))) {
            // we have a match return it (DB)
            // debug
            $this->zcLog('findMatchingAddressBookEntry - 3b', "partial match (DB):\n" . print_r($answers_arr->fields, true));
            return $answers_arr->fields['address_book_id'];
          }
        }

        $answers_arr->MoveNext();
      }
    }
    // debug
    $this->zcLog('findMatchingAddressBookEntry - 4', "no match");

    // no matches found
    return false;
  }
  /**
     * This method adds an address book entry to the database, this allows us to add addresses
     * that we get back from PayPal that are not in Zen Cart
     *
     * @param int $customer_id
     * @param array $address_question_arr
     * @return int
     */
  function addAddressBookEntry($customer_id, $address_question_arr, $make_default = false) {
    global $db;

    // debug
    $this->zcLog('addAddressBookEntry - 1', 'address to add: ' . "\n" . print_r($address_question_arr, true));

    // if address is blank, don't do any matching
    if ($address_question_arr['street_address'] == '') return false;

    // set some defaults
    $country_id = '223';
    $address_format_id = 2; //2 is the American format

    // first get the zone id's from the 2 digit iso codes
    // country first
    $sql = "SELECT countries_id, address_format_id
                FROM " . TABLE_COUNTRIES . "
                WHERE countries_iso_code_2 = :countryCode:
                OR countries_name = :countryName:
                OR countries_iso_code_2 = :countryName:
                OR countries_name = :countryCode:
                LIMIT 1";
    $sql = $db->bindVars($sql, ':countryCode:', $address_question_arr['country']['iso_code_2'], 'string');
    $sql = $db->bindVars($sql, ':countryName:', $address_question_arr['country']['title'], 'string');
    $country = $db->Execute($sql);

    // see if we found a record, if not default to American format
    if (!$country->EOF) {
      $country_id = $country->fields['countries_id'];
      $address_format_id = (int)$country->fields['address_format_id'];
    } else {
      // if defaulting to US, make sure US is valid
      $sql = "SELECT countries_id FROM " . TABLE_COUNTRIES . " WHERE countries_id = :countryId LIMIT 1";
      $sql = $db->bindVars($sql, ':countryId', $country_id, 'integer');
      $result = $db->Execute($sql);
      if ($result->EOF) {
        $this->notify('NOTIFY_HEADER_ADDRESS_BOOK_ADD_ENTRY_INVALID_ATTEMPT', $customer_id, $country_id, $address_format_id, $address_question_arr);
        $this->zcLog('addAddressBookEntry - 3', 'Failed to add address due to country restrictions');
        return FALSE;
      }
    }

    // see if the country code has a state
    $sql = "SELECT zone_id
                FROM " . TABLE_ZONES . "
                WHERE zone_country_id = :zoneId
                LIMIT 1";
    $sql = $db->bindVars($sql, ':zoneId', $country_id, 'integer');
    $country_zone_check = $db->Execute($sql);
    $check_zone = $country_zone_check->RecordCount();

    // now try and find the zone_id (state/province code)
    // use the country id above
    if ($check_zone) {
      $sql = "SELECT zone_id
                    FROM " . TABLE_ZONES . "
                    WHERE zone_country_id = :zoneId
                    AND (zone_code = :zoneCode
                    OR zone_name = :zoneCode )
                    LIMIT 1";
      $sql = $db->bindVars($sql, ':zoneId', $country_id, 'integer');
      $sql = $db->bindVars($sql, ':zoneCode', $address_question_arr['state'], 'string');
      $zone = $db->Execute($sql);
      if (!$zone->EOF) {
        // grab the id
        $zone_id = $zone->fields['zone_id'];
      } else {
        $zone_id = 0;
      }
    }

    // now run the insert

    // this isn't the best way to get fname/lname but it will get the majority of cases
    list($fname, $lname) = explode(' ', $address_question_arr['name']);

    $sql_data_array= array(array('fieldName'=>'entry_firstname', 'value'=>$fname, 'type'=>'string'),
                           array('fieldName'=>'entry_lastname', 'value'=>$lname, 'type'=>'string'),
                           array('fieldName'=>'entry_street_address', 'value'=>$address_question_arr['street_address'], 'type'=>'string'),
                           array('fieldName'=>'entry_postcode', 'value'=>$address_question_arr['postcode'], 'type'=>'string'),
                           array('fieldName'=>'entry_city', 'value'=>$address_question_arr['city'], 'type'=>'string'),
                           array('fieldName'=>'entry_country_id', 'value'=>$country_id, 'type'=>'integer'));
    if ($address_question_arr['company'] != '' && $address_question_arr['company'] != $address_question_arr['name']) array('fieldName'=>'entry_company', 'value'=>$address_question_arr['company'], 'type'=>'string');
    if (!empty($address_question_arr['payer_gender'])) {
    $sql_data_array[] = array('fieldName'=>'entry_gender', 'value'=>$address_question_arr['payer_gender'], 'type'=>'enum:m|f');
    }
    $sql_data_array[] = array('fieldName'=>'entry_suburb', 'value'=>$address_question_arr['suburb'], 'type'=>'string');
    if ($zone_id > 0) {
      $sql_data_array[] = array('fieldName'=>'entry_zone_id', 'value'=>$zone_id, 'type'=>'integer');
      $sql_data_array[] = array('fieldName'=>'entry_state', 'value'=>'', 'type'=>'string');
    } else {
      $sql_data_array[] = array('fieldName'=>'entry_zone_id', 'value'=>'0', 'type'=>'integer');
      $sql_data_array[] = array('fieldName'=>'entry_state', 'value'=>$address_question_arr['state'], 'type'=>'string');
    }
    $sql_data_array[] = array('fieldName'=>'customers_id', 'value'=>$customer_id, 'type'=>'integer');
    $db->perform(TABLE_ADDRESS_BOOK, $sql_data_array);

    $new_address_book_id = $db->Insert_ID();

    $this->notify('NOTIFY_HEADER_ADDRESS_BOOK_ADD_ENTRY_DONE', 'paypal express checkout', $new_address_book_id, $sql_data_array, $make_default);

    // make default if set, update
    if ($make_default) {
      $sql_data_array = array();
      $sql_data_array[] = array('fieldName'=>'customers_default_address_id', 'value'=>$new_address_book_id, 'type'=>'integer');
      $where_clause = "customers_id = :customersID";
      $where_clause = $db->bindVars($where_clause, ':customersID', $customer_id, 'integer');
      $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', $where_clause);
      $_SESSION['customer_default_address_id'] = $new_address_book_id;
    }

    // set the sendto
    $_SESSION['sendto'] = $new_address_book_id;

    // debug
    $this->zcLog('addAddressBookEntry - 2', 'added address #' . $new_address_book_id. "\n" . 'SESSION[sendto] is now set to ' . $_SESSION['sendto']);

    // return the address_id
    return $new_address_book_id;
  }


  /**
   * If we created an account for the customer, this logs them in and notes that the record was created for PayPal EC purposes
   */
  function user_login($email_address, $redirect = true) {
    global $db, $order, $messageStack;
    global $session_started;
    if ($session_started == false) {
      zen_redirect(zen_href_link(FILENAME_COOKIE_USAGE));
    }
    $sql = "SELECT * FROM " . TABLE_CUSTOMERS . "
            WHERE customers_email_address = :custEmail ";
    $sql = $db->bindVars($sql, ':custEmail', $email_address, 'string');
    $check_customer = $db->Execute($sql);

    if ($check_customer->EOF) {
      $this->terminateEC(MODULE_PAYMENT_PAYPALWPP_TEXT_BAD_LOGIN, true);
    }
    if (SESSION_RECREATE == 'True') {
      zen_session_recreate();
    }
    $sql = "SELECT entry_country_id, entry_zone_id
            FROM " . TABLE_ADDRESS_BOOK . "
            WHERE customers_id = :custID
            AND address_book_id = :addrID ";
    $sql = $db->bindVars($sql, ':custID', $check_customer->fields['customers_id'], 'integer');
    $sql = $db->bindVars($sql, ':addrID', $check_customer->fields['customers_default_address_id'], 'integer');
    $check_country = $db->Execute($sql);
    $_SESSION['customer_id'] = (int)$check_customer->fields['customers_id'];
    $_SESSION['customer_default_address_id'] = $check_customer->fields['customers_default_address_id'];
    $_SESSION['customer_first_name'] = $check_customer->fields['customers_firstname'];
    $_SESSION['customer_country_id'] = $check_country->fields['entry_country_id'];
    $_SESSION['customer_zone_id'] = $check_country->fields['entry_zone_id'];
    $order->customer['id'] = $_SESSION['customer_id'];
    $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
            SET customers_info_date_of_last_logon = now(),
                customers_info_number_of_logons = customers_info_number_of_logons+1
            WHERE customers_info_id = :custID ";
    $sql = $db->bindVars($sql, ':custID', $_SESSION['customer_id'], 'integer');
    $db->Execute($sql);

    // bof: contents merge notice
    // save current cart contents count if required
        if (SHOW_SHOPPING_CART_COMBINED > 0) {
          $zc_check_basket_before = $_SESSION['cart']->count_contents();
        }

        // bof: not require part of contents merge notice
        // restore cart contents
        $_SESSION['cart']->restore_contents();
        // eof: not require part of contents merge notice

        // check current cart contents count if required
        if (SHOW_SHOPPING_CART_COMBINED > 0 && $zc_check_basket_before > 0) {
          $zc_check_basket_after = $_SESSION['cart']->count_contents();
          if (($zc_check_basket_before != $zc_check_basket_after) && $_SESSION['cart']->count_contents() > 0 && SHOW_SHOPPING_CART_COMBINED > 0) {
            if (SHOW_SHOPPING_CART_COMBINED == 2) {
              // warning only do not send to cart
              $messageStack->add_session('header', WARNING_SHOPPING_CART_COMBINED, 'caution');
            }
            if (SHOW_SHOPPING_CART_COMBINED == 1) {
              // show warning and send to shopping cart for review
              $messageStack->add_session('shopping_cart', WARNING_SHOPPING_CART_COMBINED, 'caution');
              zen_redirect(zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
            }
          }
        }
    // eof: contents merge notice
    if ($redirect) {
      $this->terminateEC();
    }
    return true;
  }
  /**
   * If the account was created only for temporary purposes to place the PayPal order, delete it.
   */
  function ec_delete_user($cid) {
    global $db;
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_default_address_id']);
    unset($_SESSION['customer_first_name']);
    unset($_SESSION['customer_country_id']);
    unset($_SESSION['customer_zone_id']);
    unset($_SESSION['comments']);
    unset($_SESSION['customer_guest_id']);
    $cid = (int)$cid;
    $sql = "DELETE FROM " . TABLE_ADDRESS_BOOK . " WHERE customers_id = " . $cid;
    $db->Execute($sql);
    $sql = "DELETE FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . $cid;
    $db->Execute($sql);
    $sql = "DELETE FROM " . TABLE_CUSTOMERS_INFO . " WHERE customers_info_id = " . $cid;
    $db->Execute($sql);
    $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = " . $cid;
    $db->Execute($sql);
    $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id = " . $cid;
    $db->Execute($sql);
    $sql = "DELETE FROM " . TABLE_WHOS_ONLINE . " WHERE customer_id = " . $cid;
    $db->Execute($sql);
  }
  /**
   * If the EC flow has to be interrupted for any reason, this does the appropriate cleanup and displays status/error messages.
   */
  function terminateEC($error_msg = '', $kill_sess_vars = false, $goto_page = '') {
    global $messageStack, $order, $order_total_modules;
    $error_msg = trim($error_msg);
    if (substr($error_msg, -1) == '-') $error_msg = trim(substr($error_msg, 0, strlen($error_msg) - 1));
    $stackAlert = 'header';

    // debug
    $this->_doDebug('PayPal test Log - terminateEC-A', "goto page: " . $goto_page . "\nerror_msg: " . $error_msg . "\n\nSession data: " . print_r($_SESSION, true));

    if ($kill_sess_vars) {
      if (!empty($_SESSION['paypal_ec_temp'])) {
        $this->ec_delete_user($_SESSION['customer_id']);
      }
      // Unregister the paypal session variables, making the user start over.
      unset($_SESSION['paypal_ec_temp']);
      unset($_SESSION['paypal_ec_token']);
      unset($_SESSION['paypal_ec_payer_id']);
      unset($_SESSION['paypal_ec_payer_info']);
      unset($_SESSION['paypal_ec_final']);
      unset($_SESSION['paypal_ec_markflow']);
      // debug
      $this->zcLog('termEC-1', 'Killed the session vars as requested');
    }

    $this->zcLog('termEC-2', 'BEFORE: $this->showPaymentPage = ' . (int)$this->showPaymentPage . "\nToken Data:" . $_SESSION['paypal_ec_token']);
    // force display of payment page if GV/DC active for this customer
    if (MODULE_ORDER_TOTAL_INSTALLED && $this->showPaymentPage !== true && isset($_SESSION['paypal_ec_token']) ) {
      require_once(DIR_WS_CLASSES . 'order.php');
      $order = new order;
      require_once(DIR_WS_CLASSES . 'order_total.php');
      $order_total_modules = new order_total;
      $order_totals = $order_total_modules->process();
      $selection =  $order_total_modules->credit_selection();
      if (sizeof($selection)>0) $this->showPaymentPage = true;
    }
    // if came from Payment page, don't go back to it
    if ($_SESSION['paypal_ec_markflow'] == 1) $this->showPaymentPage = false;
    // if in DP mode, don't go to payment page ... we've already been there to get here
    if ($goto_page == FILENAME_CHECKOUT_PROCESS) $this->showPaymentPage = false;

    // debug
    $this->zcLog('termEC-3', 'AFTER: $this->showPaymentPage = ' . (int)$this->showPaymentPage);

    if (!empty($_SESSION['customer_first_name']) && !empty($_SESSION['customer_id'])) {
      if ($this->showPaymentPage === true || $goto_page == FILENAME_CHECKOUT_PAYMENT) {
        // debug
        $this->zcLog('termEC-4', 'We ARE logged in, and $this->showPaymentPage === true');
        // if no shipping selected or if shipping cost is < 0 goto shipping page
        if (!isset($_SESSION['shipping']) || !isset($_SESSION['shipping']['cost']) || $_SESSION['shipping']['cost'] < 0) {
          // debug
          $this->zcLog('termEC-5', 'Have no shipping method selected, or shipping < 0 so set FILENAME_CHECKOUT_SHIPPING');
          $redirect_path = FILENAME_CHECKOUT_SHIPPING;
          $stackAlert = 'checkout_shipping';
        } else {
          // debug
          $this->zcLog('termEC-6', 'We DO have a shipping method selected, so goto PAYMENT');
          $redirect_path = FILENAME_CHECKOUT_PAYMENT;
          $stackAlert = 'checkout_payment';
        }
      } elseif ($goto_page) {
        // debug
        $this->zcLog('termEC-7', '$this->showPaymentPage NOT true, and have custom page parameter: ' . $goto_page);
        $redirect_path = $goto_page;
        $stackAlert = 'header';
        if ($goto_page == FILENAME_SHOPPING_CART) $stackAlert = 'shopping_cart';
      } else {
        // debug
        $this->zcLog('termEC-8', '$this->showPaymentPage NOT true, and NO custom page selected ... using SHIPPING as default');
        $redirect_path = FILENAME_CHECKOUT_SHIPPING;
        $stackAlert = 'checkout_shipping';
      }
    } else {
      // debug
      $this->zcLog('termEC-9', 'We are NOT logged in, so set snapshot to Shipping page, and redirect to login');
      $_SESSION['navigation']->set_snapshot(FILENAME_CHECKOUT_SHIPPING);
      $redirect_path = FILENAME_LOGIN;
      $stackAlert = 'login';
    }
    if ($error_msg) {
      $messageStack->add_session($stackAlert, $error_msg, 'error');
    }
    // debug
    $this->zcLog('termEC-10', 'Redirecting to ' . $redirect_path . ' - Stack: ' . $stackAlert . "\n" . 'Message: ' . $error_msg . "\nSession Data: " . print_r($_SESSION, true));
    zen_redirect(zen_href_link($redirect_path, '', 'SSL', true, false));
  }
  /**
   * Error / exception handling
   */
  function _errorHandler($response, $operation = '', $ignore_codes = '') {
    global $messageStack, $doPayPal;
    $gateway_mode = (!empty($response['PNREF']));
    $basicError = (!$response || (isset($response['RESULT']) && $response['RESULT'] != 0) || (isset($response['ACK']) && !strstr($response['ACK'], 'Success')) || (!isset($response['RESULT']) && !isset($response['ACK'])));
    $ignoreList = explode(',', str_replace(' ', '', $ignore_codes));
    if (!empty($response['L_ERRORCODE0'])) {
      foreach($ignoreList as $key=>$value) {
            if ($value != '' && $response['L_ERRORCODE0'] == $value) {
                $basicError = false;
            }
      }
    }
    /** Handle unilateral **/
    if (!empty($response['RESULT']) && $response['RESULT'] == 'Unauthorized: Unilateral') {
      $errorText = $response['RESULT'] . MODULE_PAYMENT_PAYPALWPP_TEXT_UNILATERAL;
      $messageStack->add_session($errorText, 'error');
    }
    /** Handle FMF Scenarios **/
    if (in_array($operation, array('DoExpressCheckoutPayment', 'DoDirectPayment'))
        && ((isset($response['PAYMENTINFO_0_PAYMENTSTATUS']) && $response['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Pending')
            || (isset($response['PAYMENTSTATUS']) && $response['PAYMENTSTATUS'] == 'Pending')
           )
        && $response['L_ERRORCODE0'] == 11610) {
      $this->fmfResponse = urldecode($response['L_SHORTMESSAGE0']);
      $this->fmfErrors = array();
      if ($response['ACK'] == 'SuccessWithWarning' && isset($response['L_PAYMENTINFO_0_FMFPENDINGID0'])) {
        for ($i=0; $i<20; $i++) {
          if (!isset($response["L_PAYMENTINFO_0_FMFPENDINGID$i"])) {
            break;
          }
          $pendingid = $response["L_PAYMENTINFO_0_FMFPENDINGID$i"];
          $this->fmfErrors[] = array('key' => $pendingid, 'status' => $pendingid, 'desc' => $response["L_PAYMENTINFO_0_FMFPENDINGDESCRIPTION$i"]);
        }
      }
      return (sizeof($this->fmfErrors)>0) ? $this->fmfErrors : FALSE;
    }
    if (!isset($response['L_SHORTMESSAGE0']) && isset($response['RESPMSG']) && $response['RESPMSG'] != '') $response['L_SHORTMESSAGE0'] = $response['RESPMSG'];
    //echo '<br />basicError='.$basicError.'<br />' . urldecode(print_r($response,true)); die('halted');
    $errorInfo = '';
    if (IS_ADMIN_FLAG === false) {
        $errorInfo = 'Problem occurred while customer ' . zen_output_string_protected($_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name']) . ' was attempting checkout with PayPal Express Checkout.' . "\n\n";
    }

    $this->notify('NOTIFY_PAYPALWPP_ERROR_HANDLER', $response, $operation, $basicError, $ignoreList, $errorInfo);

    switch($operation) {
      case 'SetExpressCheckout':
        if ($basicError) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ec_step1()', "In function: ec_step1()\r\n\r\nValue List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_GEN_ERROR;
          $errorNum = urldecode($response['L_ERRORCODE0'] . $response['RESULT']);
          if ($response['RESULT'] == 25) $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_NOT_WPP_ACCOUNT_ERROR;
          if ($response['L_ERRORCODE0'] == 10002) $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_SANDBOX_VS_LIVE_ERROR;
          if ($response['L_ERRORCODE0'] == 10565) {
            $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_WPP_BAD_COUNTRY_ERROR;
            $_SESSION['payment'] = '';
          }
          if ($response['L_ERRORCODE0'] == 10736) $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_ADDR_ERROR;
          if ($response['L_ERRORCODE0'] == 10752) $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_DECLINED;

          $detailedMessage = ($errorText == MODULE_PAYMENT_PAYPALWPP_TEXT_GEN_ERROR || (int)trim($errorNum) > 0 || $this->enableDebugging || $response['CURL_ERRORS'] != '' || $this->emailAlerts) ? $errorNum . ' ' . urldecode(' ' . $response['L_SHORTMESSAGE0'] . ' - ' . $response['L_LONGMESSAGE0'] . (isset($response['RESPMSG']) ? ' ' . $response['RESPMSG'] : '') . ' ' . $response['CURL_ERRORS']) : '';
          $detailedEmailMessage = ($detailedMessage == '') ? '' : $errorInfo . MODULE_PAYMENT_PAYPALWPP_TEXT_EMAIL_ERROR_MESSAGE . urldecode($response['L_ERRORCODE0'] . "\n" . $response['L_SHORTMESSAGE0'] . "\n" . $response['L_LONGMESSAGE0'] . $response['L_ERRORCODE1'] . "\n" . $response['L_SHORTMESSAGE1'] . "\n" . $response['L_LONGMESSAGE1'] . $response['L_ERRORCODE2'] . "\n" . $response['L_SHORTMESSAGE2'] . "\n" . $response['L_LONGMESSAGE2'] . ($response['CURL_ERRORS'] != '' ? "\n" . $response['CURL_ERRORS'] : '') . "\n\n" . 'Zen Cart message: ' . $errorText);
          if (!isset($response['L_ERRORCODE0']) && isset($response['RESULT'])) $detailedEmailMessage .= "\n\n" . print_r($response, TRUE);
          if ($detailedEmailMessage != '') zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_PAYPALWPP_TEXT_EMAIL_ERROR_SUBJECT . ' (' . $this->uncomment($errorNum) . ')', $this->uncomment($detailedEmailMessage), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>$this->uncomment($detailedMessage)), 'paymentalert');
          $this->terminateEC($errorText . ' (' . $errorNum . ') ' . $detailedMessage, true);
          return true;
        }
        break;

      case 'GetExpressCheckoutDetails':
        if ($basicError || $_SESSION['paypal_ec_token'] != urldecode($response['TOKEN'])) {
          // if response indicates an error, send the customer back to checkout and display the error. Debug to store owner if active.
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ec_step2()', "In function: ec_step2()\r\n\r\nValue List:\r\n" . str_replace('&',"\r\n", urldecode($doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList)))) . "\r\n\r\nResponse:\r\n" . urldecode(print_r($response, true)));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_GEN_ERROR;
          if ($response['L_ERRORCODE0'] == 13113) {
            $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_PAYPAL_DECLINED;
            $_SESSION['payment'] = '';
          }
          $this->terminateEC($errorText . ' (' . $response['L_ERRORCODE0'] . ' ' . urldecode($response['L_SHORTMESSAGE0'] . $response['RESULT']) . ')', true);
          return true;
        }
        break;

      case 'DoExpressCheckoutPayment':
        if ($basicError || $_SESSION['paypal_ec_token'] != urldecode($response['TOKEN'])) {
          // there's an error, so alert customer, and if debug is on, notify storeowner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - before_process() - EC', "In function: before_process() - Express Checkout\r\n\r\nValue List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }

          // if funding source problem occurred, must send back to re-select alternate funding source
          if ($response['L_ERRORCODE0'] == 10422 || $response['L_ERRORCODE0'] == 10486) {
            header("HTTP/1.1 302 Object Moved");
            zen_redirect($this->ec_redirect_url);
            die(MODULE_PAYMENT_PAYPALWPP_FUNDING_ERROR . " (Error " . zen_output_string_protected($response['L_ERRORCODE0']) . ")");
          }

          // some other error condition
          $errorText = MODULE_PAYMENT_PAYPALWPP_INVALID_RESPONSE;
          $errorNum = urldecode($response['L_ERRORCODE0'] . $response['RESULT']);
          if ($response['L_ERRORCODE0'] == 10415) $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_ORDER_ALREADY_PLACED_ERROR;
          if ($response['L_ERRORCODE0'] == 10417) $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_INSUFFICIENT_FUNDS_ERROR;
          if ($response['L_ERRORCODE0'] == 10474) $errorText .= urldecode($response['L_LONGMESSAGE0']);

          $detailedMessage = ($errorText == MODULE_PAYMENT_PAYPALWPP_INVALID_RESPONSE || (int)trim($errorNum) > 0 || $this->enableDebugging || $response['CURL_ERRORS'] != '' || $this->emailAlerts) ? $errorNum . ' ' . urldecode(' ' . $response['L_SHORTMESSAGE0'] . ' - ' . $response['L_LONGMESSAGE0'] . $response['RESULT'] . ' ' . $response['CURL_ERRORS']) : '';
          $detailedEmailMessage = ($detailedMessage == '') ? '' : $errorInfo . MODULE_PAYMENT_PAYPALWPP_TEXT_EMAIL_ERROR_MESSAGE . urldecode($response['L_ERRORCODE0'] . "\n" . $response['L_SHORTMESSAGE0'] . "\n" . $response['L_LONGMESSAGE0'] . $response['L_ERRORCODE1'] . "\n" . $response['L_SHORTMESSAGE1'] . "\n" . $response['L_LONGMESSAGE1'] . $response['L_ERRORCODE2'] . "\n" . $response['L_SHORTMESSAGE2'] . "\n" . $response['L_LONGMESSAGE2'] . ($response['CURL_ERRORS'] != '' ? "\n" . $response['CURL_ERRORS'] : '') . "\n\n" . 'Zen Cart message: ' . $errorText);
          if (!isset($response['L_ERRORCODE0']) && isset($response['RESULT'])) $detailedEmailMessage .= "\n\n" . print_r($response, TRUE);
          if ($detailedEmailMessage != '') zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_PAYPALWPP_TEXT_EMAIL_ERROR_SUBJECT . ' (' . $this->uncomment($errorNum) . ')', $this->uncomment($detailedEmailMessage), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>$this->uncomment($detailedMessage)), 'paymentalert');
          $this->terminateEC(($detailedEmailMessage == '' ? $errorText . ' (' . urldecode($response['L_SHORTMESSAGE0'] . $response['RESULT']) . ') ' : $detailedMessage), true);
          return true;
        }
        break;
      case 'DoRefund':
        if ($basicError || (!isset($response['RESPMSG']) && !isset($response['REFUNDTRANSACTIONID']))) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_ERROR;
          if ($response['L_ERRORCODE0'] == 10009) $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_REFUNDFULL_ERROR;
          if ($response['RESULT'] == 105 || isset($response['RESPMSG'])) $response['L_SHORTMESSAGE0'] = $response['RESULT'] . ' ' . $response['RESPMSG'];
          if (urldecode($response['L_LONGMESSAGE0']) == 'This transaction has already been fully refunded') $response['L_SHORTMESSAGE0'] = urldecode($response['L_LONGMESSAGE0']);
          if (urldecode($response['L_LONGMESSAGE0']) == 'Can not do a full refund after a partial refund') $response['L_SHORTMESSAGE0'] = urldecode($response['L_LONGMESSAGE0']);
          if (urldecode($response['L_LONGMESSAGE0']) == 'The partial refund amount must be less than or equal to the remaining amount') $response['L_SHORTMESSAGE0'] = urldecode($response['L_LONGMESSAGE0']);
          if (urldecode($response['L_LONGMESSAGE0']) == 'You can not refund this type of transaction') $response['L_SHORTMESSAGE0'] = urldecode($response['L_LONGMESSAGE0']);
          $errorText .= ' (' . urldecode($response['L_SHORTMESSAGE0']) . ') ' . $response['L_ERRORCODE0'];
          $messageStack->add_session($errorText, 'error');
          return true;
        }
        break;
      case 'DoAuthorization':
      case 'DoReauthorization':
        if ($basicError) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_ERROR;
          $errorText .= ' (' . urldecode($response['L_SHORTMESSAGE0']) . ') ' . $response['L_ERRORCODE0'];
          $messageStack->add_session($errorText, 'error');
          return true;
        }
        break;
      case 'DoCapture':
        if ($basicError) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_CAPT_ERROR;
          if ($response['RESULT'] == 111) $response['L_SHORTMESSAGE0'] = $response['RESULT'] . ' ' . $response['RESPMSG'];
          $errorText .= ' (' . urldecode($response['L_SHORTMESSAGE0']) . ') ' . $response['L_ERRORCODE0'];
          $messageStack->add_session($errorText, 'error');
          return true;
        }
        break;
      case 'DoVoid':
        if ($basicError) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_ERROR;
          if ($response['RESULT'] == 12) $response['L_SHORTMESSAGE0'] = $response['RESULT'] . ' ' . $response['RESPMSG'];
          if ($response['RESULT'] == 108) $response['L_SHORTMESSAGE0'] = $response['RESULT'] . ' ' . $response['RESPMSG'];
          $errorText .= ' (' . urldecode($response['L_SHORTMESSAGE0']) . ') ' . $response['L_ERRORCODE0'];
          $messageStack->add_session($errorText, 'error');
          return true;
        }
        break;
      case 'GetTransactionDetails':
        if ($basicError) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_GETDETAILS_ERROR;
          $errorText .= ' (' . urldecode($response['L_SHORTMESSAGE0']) . ') ' . $response['L_ERRORCODE0'];
          $messageStack->add_session($errorText, 'error');
          return true;
        }
        break;
      case 'TransactionSearch':
        if ($basicError) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_TRANSSEARCH_ERROR;
          $errorText .= ' (' . urldecode($response['L_SHORTMESSAGE0']) . ') ' . $response['L_ERRORCODE0'];
          $messageStack->add_session($errorText, 'error');
          return true;
        }
        break;

      default:
        if ($basicError) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALWPP_TEXT_GEN_API_ERROR;
          $errorNum .= ' (' . urldecode($response['L_SHORTMESSAGE0'] . ' <!-- ' . $response['RESPMSG']) . ' -->) ' . $response['L_ERRORCODE0'];
          $detailedMessage = ($errorText == MODULE_PAYMENT_PAYPALWPP_TEXT_GEN_API_ERROR || $errorText == MODULE_PAYMENT_PAYPALWPP_TEXT_DECLINED || (int)trim($errorNum) > 0 || $this->enableDebugging || $response['CURL_ERRORS'] != '' || $this->emailAlerts) ? urldecode(' ' . $response['L_SHORTMESSAGE0'] . ' - ' . $response['L_LONGMESSAGE0'] . ' ' . $response['CURL_ERRORS']) : '';
          $detailedEmailMessage = ($detailedMessage == '') ? '' : MODULE_PAYMENT_PAYPALWPP_TEXT_EMAIL_ERROR_MESSAGE .  ' ' . $response['RESPMSG'] . urldecode($response['L_ERRORCODE0'] . "\n" . $response['L_SHORTMESSAGE0'] . "\n" . $response['L_LONGMESSAGE0'] . $response['L_ERRORCODE1'] . "\n" . $response['L_SHORTMESSAGE1'] . "\n" . $response['L_LONGMESSAGE1'] . $response['L_ERRORCODE2'] . "\n" . $response['L_SHORTMESSAGE2'] . "\n" . $response['L_LONGMESSAGE2'] . ($response['CURL_ERRORS'] != '' ? "\n" . $response['CURL_ERRORS'] : '') . "\n\n" . 'Zen Cart message: ' . $detailedMessage . "\n\n" . 'Transaction Response Details: ' . print_r($response, true) . "\n\n" . 'Transaction Submission: ' . urldecode($doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList), true)));
          if ($detailedEmailMessage != '') zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_PAYPALWPP_TEXT_EMAIL_ERROR_SUBJECT . ' (' . $this->uncomment($errorNum) . ')', $this->uncomment($detailedMessage), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($this->uncomment($detailedEmailMessage))), 'paymentalert');
          $messageStack->add_session($errorText . $errorNum . $detailedMessage, 'error');
          return true;
        }
        break;
    }
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
  /**
   * Convert HTML comments to readable text
   * @param string $string
   * @return string
   */
  function uncomment($string) {
    return str_replace(array('<!-- ', ' -->'), array('[', ']'), $string);
  }

}
