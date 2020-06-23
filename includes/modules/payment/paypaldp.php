<?php
/**
 * paypaldp.php payment module class for Paypal Payments Pro (aka Website Payments Pro)
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2005 CardinalCommerce
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 23 Modified in v1.5.7 $
 */
/**
 * The transaction URL for the Cardinal Centinel 3D-Secure service.
 */
define('MODULE_PAYMENT_PAYPALDP_CARDINAL_TXN_URL', 'https://paypal.cardinalcommerce.com/maps/processormodule.asp');
//define('MODULE_PAYMENT_PAYPALDP_CARDINAL_TXN_URL', 'https://centineltest.cardinalcommerce.com/maps/processormodule.asp');
/**
 * debug flag for developer use only:
 */
if (!defined('MODULE_PAYMENT_CARDINAL_CENTINEL_DEBUGGING')) define('MODULE_PAYMENT_CARDINAL_CENTINEL_DEBUGGING', FALSE);
/**
 * load the communications layer code
 */
require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/paypal_curl.php');
/**
 * the PayPal payment module for PayPal Payments Pro (Direct Payment API)
 */
class paypaldp extends base {
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
   * array holding accepted DP/gateway card types
   *
   * @var array
   */
  var $cards = array();
  /**
   * JS code used for gateway/DP mode
   *
   * @var string
   */
  var $cc_type_javascript = '';
  /**
   * JS code used for gateway/DP mode
   *
   * @var string
   */
  var $cc_type_check = '';
  /**
   * debugging flag
   *
   * @var boolean
   */
  var $enableDebugging = false;
  /**
   * is DP enabled ?
   *
   * @var boolean
   */
  var $enableDirectPayment = true;
  /**
   * sort order of display
   *
   * @var int
   */
  var $sort_order = 0;
  /**
   * Button Source / BN code -- enables the module to work for Zen Cart sites
   *
   * @var string
   */
  var $buttonSource = 'ZenCart-DP_us';
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
   * this module collects card-info onsite
   */
  var $collectsCardDataOnsite = TRUE;
  /**
   * class constructor
   */
  function __construct() {
    include_once(zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/', 'paypaldp.php', 'false'));
    global $order;
    $this->code = 'paypaldp';
    $this->codeTitle = MODULE_PAYMENT_PAYPALDP_TEXT_ADMIN_TITLE_WPP;
    $this->codeVersion = '1.5.7';
    $this->enableDirectPayment = true;
    $this->enabled = (defined('MODULE_PAYMENT_PAYPALDP_STATUS') && MODULE_PAYMENT_PAYPALDP_STATUS == 'True');
    // Set the title & description text based on the mode we're in
    if (IS_ADMIN_FLAG === true) {
      $this->description = sprintf(MODULE_PAYMENT_PAYPALDP_TEXT_ADMIN_DESCRIPTION, ' (rev' . $this->codeVersion . ')');
      $country = (defined('MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY')) ? MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY : STORE_COUNTRY;
      $this->title = $country == '223' || $country == 'USA' ? MODULE_PAYMENT_PAYPALDP_TEXT_ADMIN_TITLE_WPP : MODULE_PAYMENT_PAYPALDP_TEXT_ADMIN_TITLE_NONUSA;
      $this->title .= (defined('MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY') ? ' (' . MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY . ')' : '');
      if ($this->enabled) {
        if ( ((MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'US' || MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'Canada') && (MODULE_PAYMENT_PAYPALWPP_APISIGNATURE == '' || MODULE_PAYMENT_PAYPALWPP_APIUSERNAME == '' || MODULE_PAYMENT_PAYPALWPP_APIPASSWORD == ''))
              || (!defined('MODULE_PAYMENT_PAYPALWPP_STATUS') || MODULE_PAYMENT_PAYPALWPP_STATUS != 'True')
          ) $this->title .= '<span class="alert"><strong> NOT CONFIGURED YET</strong></span>';
        if (MODULE_PAYMENT_PAYPALDP_SERVER =='sandbox') $this->title .= '<strong><span class="alert"> (sandbox active)</span></strong>';
        if (MODULE_PAYMENT_PAYPALDP_DEBUGGING =='Log File' || MODULE_PAYMENT_PAYPALDP_DEBUGGING =='Log and Email') $this->title .= '<strong> (Debug)</strong>';
        if (!function_exists('curl_init')) $this->title .= '<strong><span class="alert"> CURL NOT FOUND. Cannot Use.</span></strong>';
      }
    } else {
      $this->description = MODULE_PAYMENT_PAYPALDP_TEXT_DESCRIPTION;
      $this->title = MODULE_PAYMENT_PAYPALDP_TEXT_TITLE; //cc
    }

    $this->sort_order = defined('MODULE_PAYMENT_PAYPALDP_SORT_ORDER') ? MODULE_PAYMENT_PAYPALDP_SORT_ORDER : null;

    if (null === $this->sort_order) return false;

    if ((!defined('PAYPAL_OVERRIDE_CURL_WARNING') || (defined('PAYPAL_OVERRIDE_CURL_WARNING') && PAYPAL_OVERRIDE_CURL_WARNING != 'True')) && !function_exists('curl_init')) $this->enabled = false;

    $this->enableDebugging = (MODULE_PAYMENT_PAYPALDP_DEBUGGING == 'Log File' || MODULE_PAYMENT_PAYPALDP_DEBUGGING =='Log and Email');
    $this->emailAlerts = (MODULE_PAYMENT_PAYPALDP_DEBUGGING == 'Log File' || MODULE_PAYMENT_PAYPALDP_DEBUGGING =='Log and Email' || MODULE_PAYMENT_PAYPALDP_DEBUGGING == 'Alerts Only');

    $this->buttonSource = (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK') ? 'ZenCart-DP_uk' : 'ZenCart-DP_us';

    $this->order_pending_status = MODULE_PAYMENT_PAYPALDP_ORDER_PENDING_STATUS_ID;
    if ((int)MODULE_PAYMENT_PAYPALDP_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_PAYPALDP_ORDER_STATUS_ID;
    }
//    $this->new_acct_notify = MODULE_PAYMENT_PAYPALDP_NEW_ACCT_NOTIFY;
    $this->zone = (int)MODULE_PAYMENT_PAYPALDP_ZONE;
    if (is_object($order)) $this->update_status();

    if (PROJECT_VERSION_MAJOR != '1' && substr(PROJECT_VERSION_MINOR, 0, 3) != '5.6') $this->enabled = false;

    // offer credit card choices for pull-down menu -- only needed for UK version
    $this->cards = array();
    if (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK') {
      if (CC_ENABLED_VISA=='1')    $this->cards[] = array('id' => 'Visa', 'text' => 'Visa');
      if (CC_ENABLED_MC=='1')      $this->cards[] = array('id' => 'MasterCard', 'text' => 'MasterCard');
      if (CC_ENABLED_MAESTRO=='1') $this->cards[] = array('id' => 'Maestro', 'text' => 'Maestro');
      if (CC_ENABLED_SOLO=='1')    $this->cards[] = array('id' => 'Solo', 'text' => 'Solo');
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
    if (IS_ADMIN_FLAG === false && $this->enabled) {
      // if store is not running in SSL, cannot offer credit card module, for PCI reasons
      if (!defined('ENABLE_SSL') || (ENABLE_SSL != 'true' && substr(HTTP_SERVER, 0, 5) != 'https')) {
        $this->enabled = FALSE;
        $this->zcLog('update_status', 'Module disabled because SSL is not enabled on this site.');
      }
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
        $this->zcLog('update_status', 'Module disabled due to zone restriction. Billing address is not within the Payment Zone selected in the module settings.');
      }
    }

    // Purchase amount
    if ($this->enabled && isset($order) && isset($order->info)) {
      // module cannot be used for purchase > $10,000 USD equiv
      $order_amount = $this->calc_order_amount($order->info['total'], 'USD', false);
      if ($order_amount > 10000) {
        $this->enabled = false;
        $this->zcLog('update_status', 'Module disabled because purchase price (' . $order_amount . ') exceeds PayPal-imposed maximum limit of 10,000 USD.');
      }
      if ($order->info['total'] == 0) {
        $this->enabled = false;
        $this->zcLog('update_status', 'Module disabled because purchase amount is set to 0.00.' . "\n" . print_r($order, true));
      }
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
    return '  if (payment_value == "' . $this->code . '") {' . "\n" .
           '    var cc_firstname = document.checkout_payment.paypalwpp_cc_firstname.value;' . "\n" .
           '    var cc_lastname = document.checkout_payment.paypalwpp_cc_lastname.value;' . "\n" .
           '    var cc_number = document.checkout_payment.paypalwpp_cc_number.value;' . "\n" .
           '    var cc_checkcode = document.checkout_payment.paypalwpp_cc_checkcode.value;' . "\n" .
           '    if (cc_firstname == "" || cc_lastname == "" || eval(cc_firstname.length) + eval(cc_lastname.length) < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
           '      error_message = error_message + "' . MODULE_PAYMENT_PAYPALDP_TEXT_JS_CC_OWNER . '";' . "\n" .
           '      error = 1;' . "\n" .
           '    }' . "\n" .
           '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
           '      error_message = error_message + "' . MODULE_PAYMENT_PAYPALDP_TEXT_JS_CC_NUMBER . '";' . "\n" .
           '      error = 1;' . "\n" .
           '    }' . "\n" .
           '    if (document.checkout_payment.paypalwpp_cc_checkcode.disabled == false && (cc_checkcode == "" || cc_checkcode.length < 3 || cc_checkcode.length > 4)) {' . "\n".
           '      error_message = error_message + "' . MODULE_PAYMENT_PAYPALDP_TEXT_JS_CC_CVV . '";' . "\n" .
           '      error = 1;' . "\n" .
           '    }' . "\n" .
           '  }' . "\n";
  }
  /**
   * Display Credit Card Information Submission Fields on the Checkout Payment Page
   */
  function selection() {
    global $order;
    $this->cc_type_check =
            'var value = document.checkout_payment.paypalwpp_cc_type.value;' .
            'if (value == "Solo" || value == "Maestro") {' .
            '    document.checkout_payment.paypalwpp_cc_issue_month.disabled = false;' .
            '    document.checkout_payment.paypalwpp_cc_issue_year.disabled = false;' .
            '    document.checkout_payment.paypalwpp_cc_checkcode.disabled = false;' .
            '    if (document.checkout_payment.paypalwpp_cc_issuenumber) document.checkout_payment.paypalwpp_cc_issuenumber.disabled = false;' .
            '} else {' .
            '    if (document.checkout_payment.paypalwpp_cc_issuenumber) document.checkout_payment.paypalwpp_cc_issuenumber.disabled = true;' .
            '    if (document.checkout_payment.paypalwpp_cc_issue_month) document.checkout_payment.paypalwpp_cc_issue_month.disabled = true;' .
            '    if (document.checkout_payment.paypalwpp_cc_issue_year) document.checkout_payment.paypalwpp_cc_issue_year.disabled = true;' .
            '    document.checkout_payment.paypalwpp_cc_checkcode.disabled = false;' .
            '}';
    if (sizeof($this->cards) == 0) $this->cc_type_check = '';

    /**
     * since we are processing via the gateway, prepare and display the CC fields
     */
    $expires_month = array();
    $expires_year = array();
    $issue_year = array();
    for ($i = 1; $i < 13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
    }

    $today = getdate();
    for ($i = $today['year']; $i < $today['year'] + 15; $i++) {
      $expires_year[] = array('id' => strftime('%y', mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }

    $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

    $fieldsArray = array();
    $fieldsArray[] = array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_FIRSTNAME,
                           'field' => zen_draw_input_field('paypalwpp_cc_firstname', $order->billing['firstname'], 'id="'.$this->code.'-cc-ownerf"'. $onFocus . ' autocomplete="off"') .
                           '<script type="text/javascript">function paypalwpp_cc_type_check() { ' . $this->cc_type_check . ' } </script>',
                           'tag' => $this->code.'-cc-ownerf');
    $fieldsArray[] = array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_LASTNAME,
                           'field' => zen_draw_input_field('paypalwpp_cc_lastname', $order->billing['lastname'], 'id="'.$this->code.'-cc-ownerl"'. $onFocus . ' autocomplete="off"'),
                           'tag' => $this->code.'-cc-ownerl');
    if (sizeof($this->cards)>0) $fieldsArray[] = array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_TYPE,
                            'field' => zen_draw_pull_down_menu('paypalwpp_cc_type', $this->cards, '', 'onchange="paypalwpp_cc_type_check();" onblur="paypalwpp_cc_type_check();"' . 'id="'.$this->code.'-cc-type"'. $onFocus),
                           'tag' => $this->code.'-cc-type');
    $fieldsArray[] = array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_NUMBER,
                           'field' => zen_draw_input_field('paypalwpp_cc_number', $ccnum, 'id="'.$this->code.'-cc-number"' . $onFocus . ' autocomplete="off"'),
                           'tag' => $this->code.'-cc-number');
    $fieldsArray[] = array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_EXPIRES,
                           'field' => zen_draw_pull_down_menu('paypalwpp_cc_expires_month', $expires_month, strftime('%m'), 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('paypalwpp_cc_expires_year', $expires_year, '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
                           'tag' => $this->code.'-cc-expires-month');
    $fieldsArray[] = array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_CHECKNUMBER,
                           'field' => zen_draw_input_field('paypalwpp_cc_checkcode', '', 'size="4" maxlength="4"' . ' id="'.$this->code.'-cc-cvv"' . $onFocus . ' autocomplete="off"') . '&nbsp;<small>' . MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_CHECKNUMBER_LOCATION . '</small><script type="text/javascript">paypalwpp_cc_type_check();</script>',
                           'tag' => $this->code.'-cc-cvv');

    $selection = array('id' => $this->code,
                       'module' => MODULE_PAYMENT_PAYPALDP_TEXT_TITLE,
                       'fields' => $fieldsArray);

    if (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK' && (CC_ENABLED_MAESTRO=='1' || CC_ENABLED_SOLO=='1')) {
      // add extra fields for UK cards
      for ($i = $today['year'] - 10; $i <= $today['year']; $i++) {
        $issue_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }
      array_splice($selection['fields'], 4, 0,
                   array(array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_ISSUE,
                               'field' => zen_draw_pull_down_menu('paypalwpp_cc_issue_month', $expires_month, '', 'id="'.$this->code.'-cc-issue-month"' . $onFocus ) . '&nbsp;' . zen_draw_pull_down_menu('paypalwpp_cc_issue_year', $issue_year, '', 'id="'.$this->code.'-cc-issue-year"' . $onFocus),
                               'tag' => $this->code.'-cc-issue-month')));
      // add extra field for Maestro cards
      array_splice($selection['fields'], 4, 0,
                   array(array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_MAESTRO_ISSUENUMBER,
                               'field' => zen_draw_input_field('paypalwpp_cc_issuenumber', $maestronum, 'size="4" maxlength="4"' . ' id="'.$this->code.'-cc-issuenumber"' . $onFocus . ' autocomplete="off"'),
                               'tag' => $this->code.'-cc-issuenumber')));
      // 3D-Secure
      $selection['fields'][] = array('title' => '',
                               'field' => '<div id="' . $this->code.'-cc-securetext"><p>' .
                                     '<a href="javascript:void window.open(\'vbv_learn_more.html\',\'vbv_service\',\'width=550,height=450\')">' .
                                     zen_image(DIR_WS_IMAGES.'3ds/vbv_learn_more.gif') . '</a>' .
                                     '<a href="javascript:void window.open(\'mcs_learn_more.html\',\'mcsc_service\',\'width=550,height=450\')">' .
                                     zen_image(DIR_WS_IMAGES.'3ds/mcsc_learn_more.gif') . '</a>' .
                                     '</p>' .
                                     '<p>' . TEXT_3DS_CARD_MAY_BE_ENROLLED . '</p></div>',
                               'tag' => $this->code.'-cc-securetext');
    }
    return $selection;
  }
  /**
   * This is the credit card check done between checkout_payment and
   * checkout_confirmation (called from checkout_confirmation).
   * Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   */
  function pre_confirmation_check() {
    global $messageStack, $order;
    include(DIR_WS_CLASSES . 'cc_validation.php');
    $cc_validation = new cc_validation();
    $result = $cc_validation->validate($_POST['paypalwpp_cc_number'],
                                       $_POST['paypalwpp_cc_expires_month'], $_POST['paypalwpp_cc_expires_year'],
                                       (isset($_POST['paypalwpp_cc_issue_month']) ? $_POST['paypalwpp_cc_issue_month'] : ''), (isset($_POST['paypalwpp_cc_issue_year']) ? $_POST['paypalwpp_cc_issue_year'] : ''));
    $error = '';
    switch ($result) {
      case 1:
        break;
      case -1:
      $error = MODULE_PAYMENT_PAYPALDP_TEXT_BAD_CARD;//sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
      if ($_POST['paypalwpp_cc_number'] == '') $error = str_replace('\n', '', MODULE_PAYMENT_PAYPALDP_TEXT_JS_CC_NUMBER); // yes, those are supposed to be single-quotes.
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

    $_POST['paypalwpp_cc_checkcode'] = preg_replace('/[^0-9]/i', '', $_POST['paypalwpp_cc_checkcode']);
    if (isset($_POST['paypalwpp_cc_issuenumber'])) $_POST['paypalwpp_cc_issuenumber'] = preg_replace('/[^0-9]/i', '', $_POST['paypalwpp_cc_issuenumber']);

    if (($result === false) || ($result < 1) ) {
      $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->' . '<!-- result: ' . $result . ' -->', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    $this->cc_card_type = $cc_validation->cc_type;
    $this->cc_card_number = $cc_validation->cc_number;
    $this->cc_expiry_month = $cc_validation->cc_expiry_month;
    $this->cc_expiry_year = $cc_validation->cc_expiry_year;
    $this->cc_checkcode = $_POST['paypalwpp_cc_checkcode'];


    // In the case of UK cards, hook 3D-Secure if appropriate
    // 3D-Secure
    /**
     * Checks if the card is enrolled in an authentication program and
     * launches the start-authentication process if it is enrolled.
     * The order may continue to authorization if a card is not enrolled
     * or an error occurs.
     *
     * Under the Verified by Visa, MasterCard SecureCode and JCB J/Secure
     * program guidelines, not all credit cards are eligible for
     * participation in the payer authentication programs. Certain types of
     * credit and debit cards, such as commercial and prepaid cards,
     * are simply not able to participate in the programs. For this reason,
     * this configuration is available to provide the option to allow
     * transactions using credit and debit cards that are unable to be
     * authenticated to complete and proceed with authorization.
     */
    if (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK' && (!isset($_POST['MD']))) {
      if (isset($_SESSION['3Dsecure_auth_status']) && isset($_SESSION['3Dsecure_auth_xid']) && isset($_SESSION['3Dsecure_auth_cavv']) && isset($_SESSION['3Dsecure_auth_eci'])) {
        // at this point we have 3d-secure auth data
      } else {
        // at this stage we need to prepare for checking whether 3d-secure is needed
        $this->clear_3DSecure_session_vars(TRUE);
        $_SESSION['3Dsecure_requires_lookup'] = $this->requiresLookup($_POST['paypalwpp_cc_number']);
        $_SESSION['3Dsecure_card_type'] = $this->determineCardType($_POST['paypalwpp_cc_number']);
      }
      if (isset($_SESSION['3Dsecure_requires_lookup']) && $_SESSION['3Dsecure_requires_lookup'] == TRUE) {
        $_SESSION['3Dsecure_merchantData'] = serialize(array('im'=>$_POST['paypalwpp_cc_issue_month'], 'iy'=>$_POST['paypalwpp_cc_issue_year'], 'in'=>$_POST['paypalwpp_cc_issuenumber'], 'fn'=>$_POST['paypalwpp_cc_firstname'], 'ln'=>$_POST['paypalwpp_cc_lastname']));
        global $order_total_modules;
        $calculatedOrderTotal = $order_total_modules->pre_confirmation_check(TRUE);
        $lookup_data_array = array('currency' => $order->info['currency'],
                                   'txn_amount' => $calculatedOrderTotal,
                                   'order_desc' => 'Zen Cart(R) ' . MODULE_PAYMENT_PAYPALDP_TEXT_TRANSACTION_FOR . ' ' . $_POST['paypalwpp_cc_firstname'] . ' ' . $_POST['paypalwpp_cc_lastname'],
                                   'cc3d_card_number' => $_POST['paypalwpp_cc_number'],
                                   'cc3d_checkcode' => $_POST['paypalwpp_cc_checkcode'],
                                   'cc3d_exp_month' => $_POST['paypalwpp_cc_expires_month'],
                                   'cc3d_exp_year' => $_POST['paypalwpp_cc_expires_year']  );
        ////////////////////////////////////////////////////////////////////////////
        // Process the enrollment lookup
        ////////////////////////////////////////////////////////////////////////////
        $lookup_response = $this->get3DSecureLookupResponse($lookup_data_array);

        $shouldContinue = $lookup_response['continue_flag'];
        $errorNo = $lookup_response['error_no'];
        $errorDesc = $lookup_response['error_desc'];
        $_SESSION['3Dsecure_enrolled'] = $lookup_response['enrolled'];
        $_SESSION['3Dsecure_transactionId'] = $lookup_response['transaction_id'];
        $requestXML = $lookup_response['requestXML'];
        $rawXML = $lookup_response['rawXML'];
        if (isset($lookup_response['EciFlag'])) $_SESSION['3Dsecure_auth_eci'] = $lookup_response['EciFlag'];

        ////////////////////////////////////////////////////////////////////////////
        // Assert that there was no error code returned and the Cardholder is
        // enrolled in the authentication program prior to starting the
        // Authentication process.
        //
        // If the card is not enrolled or an error was returned, check the business
        // rules to determine if the order should continue.
        ////////////////////////////////////////////////////////////////////////////

        if (strcasecmp('0', $errorNo) == 0 && strcasecmp('Y', $_SESSION['3Dsecure_enrolled']) == 0) {

          ////////////////////////////////////////////////////////////////////////
          // Card is enrolled, continue to payer authentication
          ////////////////////////////////////////////////////////////////////////
          $_SESSION['3Dsecure_acsURL'] = $lookup_response['acs_url'];
          $_SESSION['3Dsecure_payload'] = $lookup_response['payload'];
          $this->form_action_url = zen_href_link(FILENAME_PAYER_AUTH_FRAME, '', 'SSL', true, false);

        } else {

          if ($shouldContinue != 'Y') {
            ////////////////////////////////////////////////////////////////////
            // Business rules are set to prompt for another form of payment
            ////////////////////////////////////////////////////////////////////
            $error= $this->get_authentication_error();
            if (in_array($errorNo, array('8000', '8010', '8020', '8030'))) $error = CENTINEL_PROCESSING_ERROR;
            $reason = $errorNo . ' - ' . $errorDesc;
            $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->' . '<!-- result: ' . $reason . ' -->', 'error');
            $errorText = $error . "\n\n" . $reason . "\n(" . $this->code . ")\n\nProblem occurred while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication.';
            zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_SUBJECT . ' ' . $reason, $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));

          } else {
            ////////////////////////////////////////////////////////////////////
            // Business rules are set to continue to authorization
            ////////////////////////////////////////////////////////////////////
            if (!isset($_SESSION['3Dsecure_auth_eci']) || $_SESSION['3Dsecure_auth_eci'] == '') {
              // Not enrolled or error, determine the ECI value for the card number, and make it available to the payment module.
              if ($_SESSION['3Dsecure_enrolled'] == 'N') {
                switch($_SESSION['3Dsecure_card_type']) {
                  case 'VISA':
                    $_SESSION['3Dsecure_auth_eci'] = "06";
                    break;
                  case 'MASTERCARD':
                    $_SESSION['3Dsecure_auth_eci'] = "01";
                    break;
                  case 'JCB':
                    $_SESSION['3Dsecure_auth_eci'] = "06";
                    break;
                }
              } else if ('U' == $_SESSION['3Dsecure_enrolled'] || '0' != $errorNo) {
                switch($_SESSION['3Dsecure_card_type']) {
                  case 'VISA':
                    $_SESSION['3Dsecure_auth_eci'] = "07";
                    break;
                  case 'MASTERCARD':
                    $_SESSION['3Dsecure_auth_eci'] = "01";
                    break;
                  case 'JCB':
                    $_SESSION['3Dsecure_auth_eci'] = "07";
                    break;
                }
              }
            }
          }
        }
      }
    }
    // end uk/3d-secure check
  }
  /**
   * Display Credit Card Information for review on the Checkout Confirmation Page
   */
  function confirmation() {
    $confirmation = array('title' => '',
                          'fields' => array(array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_FIRSTNAME,
                                                  'field' => $_POST['paypalwpp_cc_firstname']),
                                            array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_LASTNAME,
                                                  'field' => $_POST['paypalwpp_cc_lastname']),
                                            array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_TYPE,
                                                  'field' => $this->cc_card_type),
                                            array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_NUMBER,
                                                  'field' => substr($_POST['paypalwpp_cc_number'], 0, 4) . str_repeat('X', (strlen($_POST['paypalwpp_cc_number']) - 8)) . substr($_POST['paypalwpp_cc_number'], -4)),
                                            array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_EXPIRES,
                                                  'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['paypalwpp_cc_expires_month'], 1, '20' . $_POST['paypalwpp_cc_expires_year'])),
                                            (isset($_POST['paypalwpp_cc_issuenumber']) ? array('title' => MODULE_PAYMENT_PAYPALDP_TEXT_ISSUE_NUMBER,
                                                  'field' => $_POST['paypalwpp_cc_issuenumber']) : '')
                                            )));
    // 3D-Secure
    if (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK' && $this->requiresLookup($_POST['paypalwpp_cc_number']) == true) {
          $confirmation['fields'][count($confirmation['fields'])] = array(
              'title' => '',
              'field' => '<div id="' . $this->code.'-cc-securetext"><p>' .
                         '<a href="javascript:void window.open(\'vbv_learn_more.html\',\'vbv_service\',\'width=550,height=450\')">' .
                         zen_image(DIR_WS_IMAGES.'3ds/vbv_learn_more.gif') . '</a>' .
                         '<a href="javascript:void window.open(\'mcs_learn_more.html\',\'mcsc_service\',\'width=550,height=450\')">' .
                         zen_image(DIR_WS_IMAGES.'3ds/mcsc_learn_more.gif') . '</a></p>' .
                         '<p>' . TEXT_3DS_CARD_MAY_BE_ENROLLED . '</p></div>');
    }
    return $confirmation;
  }
  /**
   * Prepare the hidden fields comprising the parameters for the Submit button on the checkout confirmation page
   */
  function process_button() {
    global $order;
    $_SESSION['paypal_ec_markflow'] = 1;
    $process_button_string = '';
    $process_button_string .= "\n" . zen_draw_hidden_field('wpp_cc_type', $_POST['paypalwpp_cc_type']) . "\n" .
        zen_draw_hidden_field('wpp_cc_expdate_month', $_POST['paypalwpp_cc_expires_month']) . "\n" .
        zen_draw_hidden_field('wpp_cc_expdate_year', $_POST['paypalwpp_cc_expires_year']) . "\n" .
        zen_draw_hidden_field('wpp_cc_issuedate_month', $_POST['paypalwpp_cc_issue_month']) . "\n" .
        zen_draw_hidden_field('wpp_cc_issuedate_year', $_POST['paypalwpp_cc_issue_year']) . "\n" .
        zen_draw_hidden_field('wpp_cc_issuenumber', $_POST['paypalwpp_cc_issuenumber']) . "\n" .
        zen_draw_hidden_field('wpp_cc_number', $_POST['paypalwpp_cc_number']) . "\n" .
        zen_draw_hidden_field('wpp_cc_checkcode', $_POST['paypalwpp_cc_checkcode']) . "\n" .
        zen_draw_hidden_field('wpp_payer_firstname', $_POST['paypalwpp_cc_firstname']) . "\n" .
        zen_draw_hidden_field('wpp_payer_lastname', $_POST['paypalwpp_cc_lastname']) . "\n";
    $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());
    return $process_button_string;
  }
  function process_button_ajax() {
    $processButton = array('ccFields'=>array('wpp_cc_type'=>'paypalwpp_cc_type',
        'wpp_cc_expdate_month'=>'paypalwpp_cc_expires_month',
        'wpp_cc_expdate_year'=>'paypalwpp_cc_expires_year',
        'wpp_cc_issuedate_month'=>'paypalwpp_cc_issue_year',
        'wpp_cc_issuedate_year'=>'paypalwpp_cc_issue_year',
        'wpp_cc_issuenumber'=>'paypalwpp_cc_issuenumber',
        'wpp_cc_number'=>'paypalwpp_cc_number',
        'wpp_cc_checkcode'=>'paypalwpp_cc_checkcode',
        'wpp_payer_firstname'=>'paypalwpp_cc_firstname',
        'wpp_payer_lastname'=>'paypalwpp_cc_lastname',
    ), 'extraFields'=>array(zen_session_name()=>zen_session_id()));
    return $processButton;
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

    //$this->zcLog('before_process - 1', 'Have line-item details:' . "\n" . print_r($options, true));

    // Initializing DESC field: using for comments related to tax-included pricing, populated by getLineItemDetails()
    $options['DESC'] = '';

    $doPayPal = $this->paypal_init();
      /****************************************
       * Do DP checkout
       ****************************************/
      $this->zcLog('before_process - DP-1', 'Beginning DP mode' /* . print_r($_POST, TRUE)*/);
      // Set state fields depending on what PayPal wants to see for that country
      $this->setStateAndCountry($order->billing);
      if (zen_not_null($order->delivery['street_address'])) {
        $this->setStateAndCountry($order->delivery);
      }

      // Validate credit card data
      include(DIR_WS_CLASSES . 'cc_validation.php');
      $cc_validation = new cc_validation();
      $response = $cc_validation->validate($_POST['wpp_cc_number'], $_POST['wpp_cc_expdate_month'], $_POST['wpp_cc_expdate_year'],
                                           $_POST['wpp_cc_issuedate_month'], $_POST['wpp_cc_issuedate_year']);
      $error = '';
      switch ($response) {
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

      if (($response === false) || ($response < 1) ) {
        $this->zcLog('before_process - DP-2', 'CC validation results: ' . $error . '(' . $response . ')');
        $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->' . '<!-- result: ' . $response . ' -->', 'error');
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
      }
      if (!in_array($cc_validation->cc_type, array('Visa', 'MasterCard', 'Solo', 'Discover', 'American Express', 'Maestro'))) {
//        $this->zcLog('before_process - DP-3', 'CC info: ' . $cc_validation->cc_type . ' ' . substr($cc_validation->cc_number, 0, 4) . str_repeat('X', (strlen($cc_validation->cc_number) - 8)) . substr($cc_validation->cc_number, -4) . ' ' . $error);
        $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYPALDP_TEXT_BAD_CARD . '<!-- [' . $this->code . ' ' . $cc_validation->cc_type . '] -->', 'error');
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
      }

      // if CC validation passed, continue using the validated data
      $cc_type = $cc_validation->cc_type;
      $cc_number = $cc_validation->cc_number;
      $cc_first_name = ($_POST['wpp_payer_firstname'] != '' ? $_POST['wpp_payer_firstname'] : $_SESSION['customer_first_name']);
      $cc_last_name = ($_POST['wpp_payer_lastname'] != '' ? $_POST['wpp_payer_lastname'] : $_SESSION['customer_last_name']);
      $cc_checkcode = $_POST['wpp_cc_checkcode'];
      $cc_expdate_month = $cc_validation->cc_expiry_month;
      $cc_expdate_year = $cc_validation->cc_expiry_year;
      $cc_issuedate_month = $_POST['wpp_cc_issuedate_month'];
      $cc_issuedate_year = $_POST['wpp_cc_issuedate_year'];
      $cc_issuenumber = $_POST['wpp_cc_issuenumber'];
      $cc_owner_ip = current(explode(':', str_replace(',', ':', zen_get_ip_address())));

      // If they're still here, set some of the order object's variables.
      $order->info['cc_type'] = $cc_type;
      $order->info['cc_number'] = substr($cc_number, 0, 4) . str_repeat('X', (strlen($cc_number) - 8)) . substr($cc_number, -4);
      $order->info['cc_owner'] = $cc_first_name . ' ' . $cc_last_name;
      $order->info['cc_expires'] = ''; //$cc_expdate_month . substr($cc_expdate_year, -2);
      $order->info['ip_address'] = $cc_owner_ip;

      // Set currency
      $my_currency = $this->selectCurrency($order->info['currency']);

      // if CC is maestro or solo, must be GBP
      if (in_array($cc_type, array('Solo', 'Maestro'))) {
        $my_currency = 'GBP';
      }

//      $order->info['total'] = zen_round($order->info['total'], 2);
      $order_amount = $this->calc_order_amount($order->info['total'], $my_currency);
      $display_order_amount = $this->calc_order_amount($order->info['total'], $my_currency, TRUE);


      // 3D-Secure
      if (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK') {
        // determine the card type and validate that authentication was attempted and completed if applicable
        if (($_SESSION['3Dsecure_requires_lookup'] || $this->requiresLookup($_POST['wpp_cc_number']) == true)) {  // authentication attempt required?
          // validate an acceptable lookup result
          if (isset($_SESSION['3Dsecure_enroll_lookup_attempted']) == false || strcasecmp($_SESSION['3Dsecure_enroll_lookup_attempted'], 'Y') != 0) {
            // lookup never attempted for required card, so need to redirect to payment-selection page
            $reason = 'Customer arrived on the order process page without attempting authentication lookup.';
            $error = MODULE_PAYMENT_PAYPALDP_CANNOT_BE_COMPLETED;
            $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->' . '<!-- result: ' . $reason . ' -->', 'error');
            $errorText = $reason ."\n\nProblem occurred while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication.';
            $errorText .= $this->code;
            zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_SUBJECT, $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
          }
          // if enrolled, validate an acceptable authentication result
          if (strcasecmp('Y', $_SESSION['3Dsecure_enrolled']) == 0) {
            if (isset($_SESSION['3Dsecure_authentication_attempted']) == false || strcasecmp($_SESSION['3Dsecure_authentication_attempted'], 'Y') != 0) {
              $reason = 'Customer arrived on the order process page without completing required authentication.';
              $error = MODULE_PAYMENT_PAYPALDP_CANNOT_BE_COMPLETED;
              $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->' . '<!-- result: ' . $reason . ' -->', 'error');
              $errorText = $reason ."\n\nProblem occurred while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication.';
              $errorText .= $this->code;
              zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_SUBJECT, $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');

              // remove the lookup/auth attempted status
              unset($_SESSION['3Dsecure_enroll_lookup_attempted']);
              unset($_SESSION['3Dsecure_authentication_attempted']);

              // authentication result was not acceptable, redirect
              zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            }
          }
        }
        if ($cc_type != 'Solo') {  // PayPal doesn't support 3d-secure on Solo cards
          if (isset($_SESSION['3Dsecure_enrolled'])) {
            $options['MPIVENDOR3DS'] = $_SESSION['3Dsecure_enrolled'];
          }
          if ($_SESSION['3Dsecure_auth_eci'] != '') {
            $options['ECI'] = $_SESSION['3Dsecure_auth_eci'];
          }
          if (isset($_SESSION['3Dsecure_auth_xid']) and strlen($_SESSION['3Dsecure_auth_xid']) > 0) {
            $options['XID'] = $_SESSION['3Dsecure_auth_xid'];
            $options['CAVV'] = $_SESSION['3Dsecure_auth_cavv'];
            $options['AUTHSTATUS3DS'] = $_SESSION['3Dsecure_auth_status'];
          }
        }
      }
///////////////////////////


      // Initialize the paypal caller object.
      $doPayPal = $this->paypal_init();
      $optionsAll = array_merge($options,
                    array('STREET'      => $order->billing['street_address'],
                          'ZIP'         => $order->billing['postcode'],
                          'CITY'        => $order->billing['city'],
                          'STATE'       => $order->billing['state'],
                          'STREET2'     => $order->billing['suburb'],
                          'COUNTRYCODE' => $order->billing['country']['iso_code_2'],
                          'EXPDATE'     => $cc_expdate_month . $cc_expdate_year,
                          'EMAIL'       => $order->customer['email_address'],
                          'PHONENUM'    => $order->customer['telephone']));

      $optionsShip = array();
      if (isset($order->delivery) && $order->delivery['street_address'] != '') {
        $optionsShip= array('SHIPTONAME'   => ($order->delivery['name'] == '' ? $order->delivery['firstname'] . ' ' . $order->delivery['lastname'] : $order->delivery['name']),
                            'SHIPTOSTREET' => $order->delivery['street_address'],
                            'SHIPTOSTREET2' => $order->delivery['suburb'],
                            'SHIPTOCITY'   => $order->delivery['city'],
                            'SHIPTOZIP'    => $order->delivery['postcode'],
                            'SHIPTOSTATE'  => $order->delivery['state'],
                            'SHIPTOCOUNTRYCODE'=> $order->delivery['country']['iso_code_2']);
      }
      // if these optional parameters are blank, remove them from transaction
      if (isset($optionsShip['SHIPTOSTREET2']) && trim($optionsShip['SHIPTOSTREET2']) == '') unset($optionsShip['SHIPTOSTREET2']);
      if ($optionsAll['STREET2'] == '') unset($optionsAll['STREET2']);
      if (isset($optionsShip['SHIPTOPHONE']) && trim($optionsShip['SHIPTOPHONE']) == '') unset($optionsShip['SHIPTOPHONE']);

      // if State is not supplied, repeat the city so that it's not blank, otherwise PayPal croaks
      if ((!isset($optionsShip['SHIPTOSTATE']) || trim($optionsShip['SHIPTOSTATE']) == '') && isset($optionsShip['SHIPTOCITY'])) $optionsShip['SHIPTOSTATE'] = $optionsShip['SHIPTOCITY'];

      // Payment Transaction/Authorization Mode
      $optionsNVP['PAYMENTACTION'] = (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only') ? 'Authorization' : 'Sale';
      if (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only') $this->order_status = $this->order_pending_status;

      $optionsAll['BUTTONSOURCE'] = $this->buttonSource;
      $optionsAll['CURRENCY']     = $my_currency;
      if (strlen($cc_owner_ip) > 7) {
        $optionsAll['IPADDRESS']    = $cc_owner_ip;
      }
      if ($cc_issuedate_month && $cc_issuedate_year) {
        $optionsAll['CARDSTART'] = $cc_issuedate_month . substr($cc_issuedate_year, -2);
      }
      if (isset($_POST['wpp_cc_issuenumber'])) $optionsAll['CARDISSUE'] = $_POST['wpp_cc_issuenumber'];

      // Add note to track that this was an API WPP transaction:
      $optionsAll['CUSTOM'] = 'DP-' . (int)$_SESSION['customer_id'] . '-' . time();

      // send the store name as transaction identifier, to help distinguish payments between multiple stores:
      $optionsAll['INVNUM'] = (int)$_SESSION['customer_id'] . '-' . time() . '-[' . substr(preg_replace('/[^a-zA-Z0-9_]/', '', STORE_NAME), 0, 30) . ']';  // (cannot send actual invoice number because it's not assigned until after payment is completed)

//       This feature must be enabled in your PayPal account, by contacting PayPal Support:
//       $optionsAll['SOFTDESCRIPTOR'] = substr(preg_replace('/[^a-zA-Z0-9. ]/', '', STORE_NAME), 0, 23);
//       $optionsAll['SOFTDESCRIPTORCITY'] = substr(preg_replace('/[^a-zA-Z0-9. !,' . preg_quote('"$%&\'()+-*/:;<=>?@') . ']/', '', STORE_TELEPHONE_CUSTSERVICE), 0, 23);

      if (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK' || (MODULE_PAYMENT_PAYPALWPP_PFVENDOR != '' && MODULE_PAYMENT_PAYPALWPP_PFPASSWORD != '')) { // Payflow params required
        if (isset($optionsAll['COUNTRYCODE'])) {
          $optionsAll['COUNTRY'] = $optionsAll['COUNTRYCODE'];
          unset($optionsAll['COUNTRYCODE']);
        }
        if (isset($optionsShip['SHIPTOCOUNTRYCODE'])) {
          $optionsShip['SHIPTOCOUNTRY'] = $optionsShip['SHIPTOCOUNTRYCODE'];
          unset($optionsShip['SHIPTOCOUNTRYCODE']);
        }
        if (isset($optionsShip['SHIPTOSTREET2'])) unset($optionsShip['SHIPTOSTREET2']);
        if (isset($optionsAll['STREET2'])) unset($optionsAll['STREET2']);
      }
      if (isset($optionsAll['DESC']) && $optionsAll['DESC'] == '') unset($optionsAll['DESC']);
      $this->zcLog('before_process - DP-4', 'options: ' . print_r(array_merge($optionsAll, $optionsNVP, $optionsShip), true) . "\n" . 'Rest of data: ' . "\n" . round($order_amount, 2) . ' ' . $cc_expdate_month . ' ' . substr($cc_expdate_year, -2) . ' ' . $cc_first_name . ' ' . $cc_last_name . ' ' . $cc_type);

      if (!isset($optionsAll['AMT'])) $optionsAll['AMT'] = round($order_amount, 2);
      $response = $doPayPal->DoDirectPayment($cc_number,
                                           $cc_checkcode,
                                           $cc_expdate_month . substr($cc_expdate_year, -2),
                                           $cc_first_name, $cc_last_name,
                                           $cc_type,
                                           $optionsAll, array_merge($optionsNVP, $optionsShip));

      $this->zcLog('before_process - DP-5', 'resultset:' . "\n" . urldecode(print_r($response, true)));

      // CHECK RESPONSE
      $error = $this->_errorHandler($response, 'DoDirectPayment');

      if ($this->fmfResponse != '') {
        $this->order_status = $this->order_pending_status;
      }

      $this->feeamt = '';
      $this->taxamt = '';
      $this->pendingreason = '';
      $this->reasoncode = '';
      $this->numitems = sizeof($order->products);
      $this->responsedata = $response;

      if ($response['PNREF']) {
      // PNREF only comes from payflow mode
        $this->payment_type = MODULE_PAYMENT_PAYPALDP_PF_TEXT_TYPE;
        $this->transaction_id = $response['PNREF'];
        $this->payment_status = (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only') ? 'Authorization' : 'Completed';
        $this->avs = 'AVSADDR: ' . $response['AVSADDR'] . ', AVSZIP: ' . $response['AVSZIP'] . ', IAVS: ' . $response['IAVS'];
        $this->cvv2 = $response['CVV2MATCH'];
        $this->amt = $display_order_amount . ' ' . $my_currency;
        $this->payment_time = date('Y-m-d h:i:s');
        $this->responsedata['CURRENCYCODE'] = $my_currency;
        $this->responsedata['EXCHANGERATE'] = $order->info['currency_value'];
        $this->auth_code = $this->response['AUTHCODE'];
      } else {
        // here we're in NVP mode
        $this->transaction_id = $response['TRANSACTIONID'];
        $this->payment_type = MODULE_PAYMENT_PAYPALDP_DP_TEXT_TYPE;
        $this->payment_status = (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only') ? 'Authorization' : 'Completed';
        $this->pendingreason = (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only') ? 'authorization' : '';
        $this->avs = $response['AVSCODE'];
        $this->cvv2 = $response['CVV2MATCH'];
        $this->correlationid = $response['CORRELATIONID'];
        $this->payment_time = urldecode($response['TIMESTAMP']);
        $this->amt = urldecode($response['AMT'] . ' ' . $response['CURRENCYCODE']);
        $this->auth_code = (isset($this->response['AUTHCODE'])) ? $this->response['AUTHCODE'] : $this->response['TOKEN'];
        $this->transactiontype = 'cart';
      }
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
                     ($this->payment_time != '' ? ("\nTimestamp: " . $this->payment_time . ' ') : '') .
                                 "\nPayment Status: " . $this->payment_status .
                     (isset($this->responsedata['auth_exp']) ? "\nAuth-Exp: " . $this->responsedata['auth_exp'] : '') .
                     ($this->avs != 'N/A' ? "\nAVS Code: ".$this->avs."\nCVV2 Code: ".$this->cvv2 : '') .
                     (trim($this->amt) != '' ? ("\nAmount: " . $this->amt) : '');
    zen_update_orders_history($insert_id, $commentString, null, $order->info['order_status'], 0);

    // 3D-Secure
    if ($this->requiresLookup($order->info['cc_type']) == true) {
      // CardinalCommerce Liability Protection Status
      // Inserts 'PROTECTED' or 'NOT PROTECTED' status, ECI, CAVV values in the order status history comments
      $auth_proc_status = $this->determine3DSecureProtection($order->info['cc_type'], $_SESSION['3Dsecure_auth_eci']);
      $commentString = "3D-Secure: " . $auth_proc_status . "\n" . 'ECI Value = ' . $_SESSION['3Dsecure_auth_eci'] . "\n" . 'CAVV Value = ' . $_SESSION['3Dsecure_auth_cavv'];
      zen_update_orders_history($insert_id, $commentString, null, $order->info['order_status'], -1);
    }

    // store the PayPal order meta data -- used for later matching and back-end processing activities
    $paypal_order = array('order_id' => $insert_id,
                          'txn_type' => $this->transactiontype,
                          'module_name' => $this->code,
                          'module_mode' => MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY,
                          'reason_code' => $this->reasoncode,
                          'payment_type' => $this->payment_type,
                          'payment_status' => $this->payment_status,
                          'pending_reason' => $this->pendingreason,
                          'invoice' => urldecode($_SESSION['paypal_ec_token'] . $this->responsedata['PPREF']),
                          'first_name' => $_SESSION['paypal_ec_payer_info']['payer_firstname'],
                          'last_name' => $_SESSION['paypal_ec_payer_info']['payer_lastname'],
                          'payer_business_name' => $_SESSION['paypal_ec_payer_info']['payer_business'],
                          'address_name' => $_SESSION['paypal_ec_payer_info']['ship_name'],
                          'address_street' => $_SESSION['paypal_ec_payer_info']['ship_street_1'],
                          'address_city' => $_SESSION['paypal_ec_payer_info']['ship_city'],
                          'address_state' => $_SESSION['paypal_ec_payer_info']['ship_state'],
                          'address_zip' => $_SESSION['paypal_ec_payer_info']['ship_postal_code'],
                          'address_country' => $_SESSION['paypal_ec_payer_info']['ship_country'],
                          'address_status' => $_SESSION['paypal_ec_payer_info']['ship_address_status'],
                          'payer_email' => $_SESSION['paypal_ec_payer_info']['payer_email'],
                          'payer_id' => $_SESSION['paypal_ec_payer_id'],
                          'payer_status' => $_SESSION['paypal_ec_payer_info']['payer_status'],
                          'payment_date' => trim(preg_replace('/[^0-9-:]/', ' ', $this->payment_time)),
                          'business' => '',
                          'receiver_email' => (MODULE_PAYMENT_PAYPALWPP_PFVENDOR != '' ? MODULE_PAYMENT_PAYPALWPP_PFVENDOR : str_replace('_api1', '', MODULE_PAYMENT_PAYPALWPP_APIUSERNAME)),
                          'receiver_id' => '',
                          'txn_id' => $this->transaction_id,
                          'parent_txn_id' => '',
                          'num_cart_items' => (float)$this->numitems,
                          'mc_gross' => (float)$this->amt,
                          'mc_fee' => (float)urldecode($this->feeamt),
                          'mc_currency' => $this->responsedata['CURRENCYCODE'],
                          'settle_amount' => (float)urldecode($this->responsedata['SETTLEAMT']),
                          'settle_currency' => $this->responsedata['CURRENCYCODE'],
                          'exchange_rate' => (urldecode($this->responsedata['EXCHANGERATE']) > 0 ? urldecode($this->responsedata['EXCHANGERATE']) : 1.0),
                          'notify_version' => '0',
                          'verify_sign' =>'',
                          'date_added' => 'now()',
                          'memo' => (sizeof($this->fmfErrors) > 0 ? 'FMF Details ' . print_r($this->fmfErrors, TRUE) : '{Record generated by payment module}'),
                         );
    zen_db_perform(TABLE_PAYPAL, $paypal_order);

    // Unregister the paypal session variables, making it necessary to start again for another purchase
    unset($_SESSION['paypal_ec_temp']);
    unset($_SESSION['paypal_ec_token']);
    unset($_SESSION['paypal_ec_payer_id']);
    unset($_SESSION['paypal_ec_payer_info']);
    unset($_SESSION['paypal_ec_final']);
    unset($_SESSION['paypal_ec_markflow']);
    $this->clear_3DSecure_session_vars(TRUE);
  }
  /**
    * Build admin-page components
    *
    * @param int $zf_order_id
    * @return string
    */
  function admin_notification($zf_order_id) {
    if (!defined('MODULE_PAYMENT_PAYPALDP_STATUS')) return '';
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
  function _GetTransactionDetails($oID) {
    if ($oID == '' || $oID < 1) return FALSE;
    global $db, $messageStack, $doPayPal;
    $doPayPal = $this->paypal_init();
    // look up history on this order from PayPal table
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID ORDER BY last_modified DESC, date_added DESC, parent_txn_id DESC, paypal_ipn_id DESC LIMIT 2";
    $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
    $zc_ppHist = $db->Execute($sql);
    if ($zc_ppHist->RecordCount() == 0) return false;
    $txnID = $zc_ppHist->fields['txn_id'];
    if ($txnID == '' || $txnID === 0) return FALSE;
    /**
     * Read data from PayPal
     */
    $response = $doPayPal->GetTransactionDetails($txnID);
    if (isset($response['RESULT']) && $response['RESULT'] == '7' && $zc_ppHist->RecordCount() > 1) {
      $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID AND txn_id != :condition: ORDER BY last_modified ASC, date_added ASC, paypal_ipn_id ASC LIMIT 1";
      $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
      $sql = $db->bindVars($sql, ':condition:', $zc_ppHist->fields['txn_id'], 'integer');
      $zc_ppHist = $db->Execute($sql);
      if ($zc_ppHist->RecordCount() == 0) return false;
      $txnID = $zc_ppHist->fields['txn_id'];
      if ($txnID == '' || $txnID === 0) return FALSE;
      $response = $doPayPal->GetTransactionDetails($txnID);
    }

    $error = $this->_errorHandler($response, 'GetTransactionDetails', 10007);
    if ($error === true) {
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
    $sql = "SELECT * FROM " . TABLE_PAYPAL . " WHERE order_id = :orderID AND parent_txn_id = '' ";
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
      $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYPALDP_STATUS'");
      $this->_check = !$check_query->EOF;
    }
    return $this->_check;
  }
  /**
   * Installs all the configuration keys for this module
   */
  function install() {
    global $db, $messageStack;
    if (defined('MODULE_PAYMENT_PAYPALDP_STATUS')) {
      $messageStack->add_session('Website Payments Pro module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paypaldp', 'NONSSL'));
      return 'failed';
    }
    // cannot install DP if EC not already enabled:
    if (!defined('MODULE_PAYMENT_PAYPALWPP_STATUS') || MODULE_PAYMENT_PAYPALWPP_STATUS != 'True') {
      $messageStack->add_session('<strong>Sorry, you must install and configure PayPal Express Checkout first.</strong> PayPal Website Payments Pro requires that you offer Express Checkout to your customers.<br /><a href="' . zen_href_link('modules.php?set=payment&module=paypalwpp', '', 'NONSSL') . '">Click here to set up Express Checkout.</a>' , 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paypaldp', 'NONSSL'));
      return 'failed';
    }
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable this Payment Module', 'MODULE_PAYMENT_PAYPALDP_STATUS', 'True', 'Do you want to enable this payment module?', '6', '25', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Live or Sandbox', 'MODULE_PAYMENT_PAYPALDP_SERVER', 'live', '<strong>Live: </strong> Used to process Live transactions<br><strong>Sandbox: </strong>For developers and testing', '6', '25', 'zen_cfg_select_option(array(\'live\', \'sandbox\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_PAYPALDP_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_PAYPALDP_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '25', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_PAYPALDP_ORDER_STATUS_ID', '2', 'Set the status of orders paid with this payment module to this value. <br /><strong>Recommended: Processing[2]</strong>', '6', '25', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Unpaid Order Status', 'MODULE_PAYMENT_PAYPALDP_ORDER_PENDING_STATUS_ID', '1', 'Set the status of unpaid orders made with this payment module to this value. <br /><strong>Recommended: Pending[1]</strong>', '6', '25', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refund Order Status', 'MODULE_PAYMENT_PAYPALDP_REFUNDED_STATUS_ID', '1', 'Set the status of refunded orders to this value. <br /><strong>Recommended: Pending[1]</strong>', '6', '25', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Payment Action', 'MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE', 'Final Sale', 'How do you want to obtain payment?<br /><strong>Default: Final Sale</strong>', '6', '25', 'zen_cfg_select_option(array(\'Auth Only\', \'Final Sale\'), ',  now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Currency', 'MODULE_PAYMENT_PAYPALDP_CURRENCY',  'Selected Currency', 'Which currency should the order be sent to PayPal as? <br />NOTE: if an unsupported currency is sent to PayPal, it will be auto-converted to USD (or GBP if using UK account)<br /><strong>Default: Selected Currency</strong>', '6', '25', 'zen_cfg_select_option(array(\'Selected Currency\', \'Only USD\', \'Only AUD\', \'Only CAD\', \'Only EUR\', \'Only GBP\', \'Only CHF\', \'Only CZK\', \'Only DKK\', \'Only HKD\', \'Only HUF\', \'Only JPY\', \'Only NOK\', \'Only NZD\', \'Only PLN\', \'Only SEK\', \'Only SGD\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Fraud Mgmt Filters - FMF', 'MODULE_PAYMENT_PAYPALDP_EC_RETURN_FMF_DETAILS', 'No', 'If you have enabled FMF support in your PayPal account and wish to utilize it in your transactions, set this to yes. Otherwise, leave it at No.', '6', '25','zen_cfg_select_option(array(\'No\', \'Yes\'), ', now())");

    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Merchant Country', 'MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY', 'USA', 'Which country is your PayPal Account registered to? <br /><u>Choices:</u><br /><font color=green>You will need to supply <strong>API Settings</strong> in the Express Checkout module.</font><br /><strong>USA and Canada merchants</strong> need PayPal API credentials and a PayPal Payments Pro account.<br /><strong>UK merchants</strong> need to supply <strong>PAYFLOW settings</strong> (and have a Payflow account)<br><strong>Australia merchants</strong> choose Canada<br><em>(This setting is really about the internal PayPal API specification, and not so much about country: US=1.5, UK=2.0, Canada/Australia=3.0)</em>', '6', '25',  'zen_cfg_select_option(array(\'USA\', \'UK\', \'Canada\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Mode', 'MODULE_PAYMENT_PAYPALDP_DEBUGGING', 'Off', 'Would you like to enable debug mode?  A complete detailed log of failed transactions will be emailed to the store owner.', '6', '25', 'zen_cfg_select_option(array(\'Off\', \'Alerts Only\', \'Log File\', \'Log and Email\'), ', now())");

    // 3D-Secure
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Cardinal Processor ID', 'MODULE_PAYMENT_PAYPALDP_CARDINAL_PROCESSOR', '134-01', 'The processor ID for the Cardinal Centinel service. ', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Cardinal Merchant ID', 'MODULE_PAYMENT_PAYPALDP_CARDINAL_MERCHANT', 'enter value', 'The merchant ID for the Cardinal Centinel service. ', '6', '25', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function, use_function) VALUES ('Cardinal Transaction Password', 'MODULE_PAYMENT_PAYPALDP_CARDINAL_PASSWORD', '', 'Enter your Cardinal Transaction Password from your Cardinal Merchant Admin console. This is used to secure and verify that the transaction originated from your store legitimately.', '6', '25', now(), 'zen_cfg_password_input(', 'zen_cfg_password_display')");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Only Accept Chargeback-Protected Orders via Cardinal?', 'MODULE_PAYMENT_PAYPALDP_CARDINAL_AUTHENTICATE_REQ', 'No', 'Only proceed with authorization when the Cardinal authentication result provides chargeback protection? ', '6', '25', 'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");

    $this->notify('NOTIFY_PAYMENT_PAYPALDP_INSTALLED');
  }

  function keys() {
    $keys_list = array('MODULE_PAYMENT_PAYPALDP_STATUS', 'MODULE_PAYMENT_PAYPALDP_SORT_ORDER', 'MODULE_PAYMENT_PAYPALDP_ZONE', 'MODULE_PAYMENT_PAYPALDP_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPALDP_ORDER_PENDING_STATUS_ID', 'MODULE_PAYMENT_PAYPALDP_REFUNDED_STATUS_ID', 'MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE', 'MODULE_PAYMENT_PAYPALDP_CURRENCY', 'MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY', 'MODULE_PAYMENT_PAYPALDP_EC_RETURN_FMF_DETAILS', 'MODULE_PAYMENT_PAYPALDP_SERVER', 'MODULE_PAYMENT_PAYPALDP_DEBUGGING');
    if (defined('MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY') && MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK') {
      $keys_list = array_merge($keys_list, array('MODULE_PAYMENT_PAYPALDP_CARDINAL_PROCESSOR','MODULE_PAYMENT_PAYPALDP_CARDINAL_MERCHANT','MODULE_PAYMENT_PAYPALDP_CARDINAL_PASSWORD','MODULE_PAYMENT_PAYPALDP_CARDINAL_AUTHENTICATE_REQ'));
    }
    return $keys_list;
  }
  /**
   * De-install this module
   */
  function remove() {
    global $db;
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PAYPALDP\_%'");
    $this->notify('NOTIFY_PAYMENT_PAYPALDP_UNINSTALLED');
  }
  /**
   * Check settings and conditions to determine whether we are in an Express Checkout phase or not
   */
  function in_special_checkout() {
    if ((defined('MODULE_PAYMENT_PAYPALDP_STATUS') && MODULE_PAYMENT_PAYPALDP_STATUS == 'True') &&
             !empty($_SESSION['paypal_ec_token']) &&
             !empty($_SESSION['paypal_ec_payer_id']) &&
             !empty($_SESSION['paypal_ec_payer_info'])) {
      return true;
    }
  }
  /**
   * Debug Logging support
   */
  function zcLog($stage, $message) {
    static $tokenHash;
    if ($tokenHash == '') $tokenHash = '_' . zen_create_random_value(4);
    if (MODULE_PAYMENT_PAYPALDP_DEBUGGING == 'Log and Email' || MODULE_PAYMENT_PAYPALDP_DEBUGGING == 'Log File') {
      $token = (isset($_SESSION['paypal_ec_token'])) ? $_SESSION['paypal_ec_token'] : preg_replace('/[^0-9.A-Z\-]/', '', $_GET['token']);
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
    if (MODULE_PAYMENT_PAYPALDP_DEBUGGING == 'Log and Email') {
      $data =  urldecode($data) . "\n\n";
      if ($useSession) $data .= "\nSession data: " . print_r($_SESSION, true);
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, $subject, $this->code . "\n" . $data, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($this->code . "\n" . $data)), 'debug');
    }
  }
  /**
   * Initialize the PayPal/PayflowPro object for communication to the processing gateways
   */
  function paypal_init() {
    $nvp = (MODULE_PAYMENT_PAYPALWPP_APIPASSWORD != '' && MODULE_PAYMENT_PAYPALWPP_APISIGNATURE != '') ? true : false;
    $ec = ($nvp && isset($_GET['type']) && $_GET['type'] == 'ec') ? true : false;
    if (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK' && !$ec) {
      $doPayPal = new paypal_curl(array('mode' => 'payflow',
                                        'user' =>   trim(MODULE_PAYMENT_PAYPALWPP_PFUSER),
                                        'vendor' => trim(MODULE_PAYMENT_PAYPALWPP_PFVENDOR),
                                        'partner'=> trim(MODULE_PAYMENT_PAYPALWPP_PFPARTNER),
                                        'pwd' =>    trim(MODULE_PAYMENT_PAYPALWPP_PFPASSWORD),
                                        'server' => MODULE_PAYMENT_PAYPALDP_SERVER));
      $doPayPal->_endpoints = array('live'    => 'https://payflowpro.paypal.com/transaction',
                                    'sandbox' => 'https://pilot-payflowpro.paypal.com/transaction');
    } else {
      $doPayPal = new paypal_curl(array('mode' => 'nvp',
                                        'user' => trim(MODULE_PAYMENT_PAYPALWPP_APIUSERNAME),
                                        'pwd' =>  trim(MODULE_PAYMENT_PAYPALWPP_APIPASSWORD),
                                        'signature' => trim(MODULE_PAYMENT_PAYPALWPP_APISIGNATURE),
                                        'version' => '124.0',
                                        'server' => MODULE_PAYMENT_PAYPALDP_SERVER));
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
    $doPayPal->_trxtype = (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only') ? 'A' : 'S';

    return $doPayPal;
  }
  /**
   * Determine which PayPal URL to direct the customer's browser to when needed
   */
  function getPayPalLoginServer() {
    if (MODULE_PAYMENT_PAYPALDP_SERVER == 'live') {
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
    $new_order_status = (int)MODULE_PAYMENT_PAYPALDP_REFUNDED_STATUS_ID;
    $orig_order_amount = 0;
    $doPayPal = $this->paypal_init();
    $proceedToRefund = false;
    $refundNote = strip_tags(zen_db_input($_POST['refnote']));
    if (isset($_POST['fullrefund']) && $_POST['fullrefund'] == MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_FULL) {
      $refundAmt = 'Full';
      if (isset($_POST['reffullconfirm']) && $_POST['reffullconfirm'] == 'on') {
        $proceedToRefund = true;
      } else {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALDP_TEXT_REFUND_FULL_CONFIRM_ERROR, 'error');
      }
    }
    if (isset($_POST['partialrefund']) && $_POST['partialrefund'] == MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_PARTIAL) {
      $refundAmt = (float)$_POST['refamt'];
      $proceedToRefund = true;
      if ($refundAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALDP_TEXT_INVALID_REFUND_AMOUNT, 'error');
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
      $error = $this->_errorHandler($response, 'DoRefund');
      $new_order_status = ($new_order_status > 0 ? $new_order_status : 1);
      if (!$error) {
        if (!isset($response['GROSSREFUNDAMT'])) $response['GROSSREFUNDAMT'] = $refundAmt;
        // Success, so save the results
        $comments = 'REFUND INITIATED. Trans ID:' . $response['REFUNDTRANSACTIONID'] . $response['PNREF']. "\nGross Refund Amt: " . urldecode($response['GROSSREFUNDAMT']) . (isset($response['PPREF']) ? "\nPPRef: " . $response['PPREF'] : '') . "\n" . $refundNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALDP_TEXT_REFUND_INITIATED, urldecode($response['GROSSREFUNDAMT']), urldecode($response['REFUNDTRANSACTIONID']). $response['PNREF']), 'success');
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
    $new_order_status = (int)MODULE_PAYMENT_PAYPALDP_ORDER_STATUS_ID;

    $orig_order_amount = 0;
    $doPayPal = $this->paypal_init();
    $proceedToCapture = false;
    $captureNote = strip_tags(zen_db_input($_POST['captnote']));
    if (isset($_POST['captfullconfirm']) && $_POST['captfullconfirm'] == 'on') {
      $proceedToCapture = true;
    } else {
      $messageStack->add_session(MODULE_PAYMENT_PAYPALDP_TEXT_CAPTURE_FULL_CONFIRM_ERROR, 'error');
    }
    if (isset($_POST['captfinal']) && $_POST['captfinal'] == 'on') {
      $captureType = 'Complete';
    } else {
      $captureType = 'NotComplete';
    }
    if (isset($_POST['btndocapture']) && $_POST['btndocapture'] == MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_BUTTON_TEXT_FULL) {
      $captureAmt = (float)$_POST['captamt'];
      if ($captureAmt == 0) {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALDP_TEXT_INVALID_CAPTURE_AMOUNT, 'error');
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
      $error = $this->_errorHandler($response, 'DoCapture');
      $new_order_status = ($new_order_status > 0 ? $new_order_status : 1);
      if (!$error) {
        if (isset($response['PNREF'])) {
          if (!isset($response['AMT'])) $response['AMT'] = $captureAmt;
          if (!isset($response['ORDERTIME'])) $response['ORDERTIME'] = date("M-d-Y h:i:s");
        }
        // Success, so save the results
        $comments = 'FUNDS CAPTURED. Trans ID: ' . urldecode($response['TRANSACTIONID']) . $response['PNREF']. "\n" . ' Amount: ' . urldecode($response['AMT']) . ' ' . $currency . "\n" . 'Time: ' . urldecode($response['ORDERTIME']) . "\n" . 'Auth Code: ' . $response['AUTHCODE'] . (isset($response['PPREF']) ? "\nPPRef: " . $response['PPREF'] : '') . "\n" . $captureNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALDP_TEXT_CAPT_INITIATED, urldecode($response['AMT']), urldecode($response['AUTHCODE']). $response['PNREF']), 'success');
        return true;
      }
    }
  }
  /**
   * Used to void a given previously-authorized transaction.  FOR FUTURE USE.
   */
  function _doVoid($oID, $note = '') {
    global $db, $doPayPal, $messageStack;
    $new_order_status = (int)MODULE_PAYMENT_PAYPALDP_REFUNDED_STATUS_ID;
    $doPayPal = $this->paypal_init();
    $voidNote = strip_tags(zen_db_input($_POST['voidnote']));
    $voidAuthID = trim(strip_tags(zen_db_input($_POST['voidauthid'])));
    if (isset($_POST['ordervoid']) && $_POST['ordervoid'] == MODULE_PAYMENT_PAYPAL_ENTRY_VOID_BUTTON_TEXT_FULL) {
      if (isset($_POST['voidconfirm']) && $_POST['voidconfirm'] == 'on') {
        $proceedToVoid = true;
      } else {
        $messageStack->add_session(MODULE_PAYMENT_PAYPALDP_TEXT_VOID_CONFIRM_ERROR, 'error');
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
      $error = $this->_errorHandler($response, 'DoVoid');
      $new_order_status = ($new_order_status > 0 ? $new_order_status : 1);
      if (!$error) {
        // Success, so save the results
        $comments = 'VOIDED. Trans ID: ' . urldecode($response['AUTHORIZATIONID']). $response['PNREF'] . (isset($response['PPREF']) ? "\nPPRef: " . $response['PPREF'] : '') . "\n" . $voidNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALDP_TEXT_VOID_INITIATED, urldecode($response['AUTHORIZATIONID']) . $response['PNREF']), 'success');
        return true;
      }
    }
  }

  /**
   * Set the currency code -- use defaults if active currency is not a currency accepted by PayPal
   */
  function selectCurrency($val = '') {
    $ec_currencies = array('CAD', 'EUR', 'GBP', 'JPY', 'USD', 'AUD', 'CHF', 'CZK', 'DKK', 'HKD', 'HUF', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'THB', 'MXN', 'ILS', 'PHP', 'TWD', 'BRL', 'MYR', 'TRY', 'RUB');
    $dp_currencies = array('CAD', 'EUR', 'GBP', 'JPY', 'USD', 'AUD', 'CHF', 'CZK', 'DKK', 'HKD', 'HUF', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD');
    $dpus_currencies = array('CAD', 'EUR', 'GBP', 'JPY', 'USD', 'AUD');

    // in USA, only 6 currencies are supported. But UK and Canada support 16 currencies (as of Jan 2011):
    $paypalSupportedCurrencies = (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK' || MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'Canada') ? $dp_currencies : $dpus_currencies;

    $my_currency = substr(MODULE_PAYMENT_PAYPALDP_CURRENCY, 5);
    if (MODULE_PAYMENT_PAYPALDP_CURRENCY == 'Selected Currency') {
      $my_currency = ($val == '') ? $_SESSION['currency'] : $val;
    }

    if (!in_array($my_currency, $paypalSupportedCurrencies)) {
      $my_currency = (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'UK') ? 'GBP' : (MODULE_PAYMENT_PAYPALDP_MERCHANT_COUNTRY == 'Canada' ? 'CAD' : 'USD');
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
          $messageStack->add_session('header', MODULE_PAYMENT_PAYPALDP_TEXT_STATE_ERROR, 'error');
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
      case 'MX':
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
    $optionsST['AMT'] = 0;
    $optionsST['ITEMAMT'] = 0;
    $optionsST['TAXAMT'] = 0;
    $optionsST['SHIPPINGAMT'] = 0;
    $optionsST['SHIPDISCAMT'] = 0;
    $optionsST['HANDLINGAMT'] = 0;
    $optionsST['INSURANCEAMT'] = 0;
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
          if ($order_totals[$i]['code'] == 'ot_shipping') $optionsST['SHIPPINGAMT'] = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_total')    $optionsST['AMT']         = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_tax')      $optionsST['TAXAMT']     += strval(round($order_totals[$i]['value'],2));
          if ($order_totals[$i]['code'] == 'ot_subtotal') $optionsST['ITEMAMT']     = round($order_totals[$i]['value'],2);
          if (strstr($order_totals[$i]['code'], 'insurance')) $optionsST['INSURANCEAMT'] += round($order_totals[$i]['value'],2);
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
      $this->notify('NOTIFY_PAYMENT_PAYPALDP_SUBTOTALS_REVIEW', $order, $order_totals);
      if (sizeof($this->ot_merge)) $optionsST = array_merge($optionsST, $this->ot_merge);

      if ($creditsApplied > 0) $optionsST['ITEMAMT'] -= $creditsApplied;
      if ($surcharges > 0) $optionsST['ITEMAMT'] += $surcharges;

      // Handle tax-included scenario
      if (DISPLAY_PRICE_WITH_TAX == 'true') $optionsST['TAXAMT'] = 0;

      $subtotalPRE = $optionsST;
      // Move shipping tax amount from Tax subtotal into Shipping subtotal for submission to PayPal, since PayPal applies tax to each line-item individually
      $module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
      if (zen_not_null($order->info['shipping_method']) && DISPLAY_PRICE_WITH_TAX != 'true') {
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
          $optionsST['SHIPPINGAMT'] += $taxAdjustmentForShipping;
          $optionsST['TAXAMT'] -= $taxAdjustmentForShipping;
        }
      }
      $flagSubtotalsUnknownYet = (($optionsST['SHIPPINGAMT'] + $optionsST['SHIPDISCAMT'] + $optionsST['AMT'] + $optionsST['TAXAMT'] + $optionsST['ITEMAMT'] + $optionsST['INSURANCEAMT']) == 0);
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

      $optionsLI["L_NUMBER$k"] = $order->products[$i]['model'];
      $optionsLI["L_NAME$k"]   = $order->products[$i]['name'] . ' [' . (int)$order->products[$i]['id'] . ']';
      // Append *** if out-of-stock.
      $optionsLI["L_NAME$k"]  .= ((zen_get_products_stock($order->products[$i]['id']) - $order->products[$i]['qty']) < 0 ? STOCK_MARK_PRODUCT_OUT_OF_STOCK : '');
      // if there are attributes, loop thru them and add to description
      if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0 ) {
        for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
          $optionsLI["L_NAME$k"] .= "\n " . $order->products[$i]['attributes'][$j]['option'] .
                                        ': ' . $order->products[$i]['attributes'][$j]['value'];
        } // end loop
      } // endif attribute-info

      // PayPal can't handle fractional-quantity values, so convert it to qty 1 here
      if (is_float($order->products[$i]['qty']) && ($order->products[$i]['qty'] != (int)$order->products[$i]['qty'] || $flag_treat_as_partial)) {
        $optionsLI["L_NAME$k"] = '('.$order->products[$i]['qty'].' x ) ' . $optionsLI["L_NAME$k"];
        // zen_add_tax already handles whether DISPLAY_PRICES_WITH_TAX is set
        $optionsLI["L_AMT$k"] = zen_round(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $decimals) * $order->products[$i]['qty'], $decimals);
        $optionsLI["L_QTY$k"] = 1;
        // no line-item tax component
      } else {
        $optionsLI["L_QTY$k"] = $order->products[$i]['qty'];
        $optionsLI["L_AMT$k"] = zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $decimals);
      }

      $subTotalLI += ($optionsLI["L_QTY$k"] * $optionsLI["L_AMT$k"]);
//      $subTotalTax += ($optionsLI["L_QTY$k"] * $optionsLI["L_TAXAMT$k"]);

      // add line-item for one-time charges on this product
      if ($order->products[$i]['onetime_charges'] != 0 ) {
        $k++;
        $optionsLI["L_NAME$k"]   = MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX . substr(htmlentities($order->products[$i]['name'], ENT_QUOTES, 'UTF-8'), 0, 120);
        $optionsLI["L_AMT$k"]    = zen_round(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), $decimals);
        $optionsLI["L_QTY$k"]    = 1;
//        $optionsLI["L_TAXAMT$k"] = zen_round(zen_calculate_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), $decimals);
        $subTotalLI += $optionsLI["L_AMT$k"];
//        $subTotalTax += $optionsLI["L_TAXAMT$k"];
      }
      $numberOfLineItemsProcessed = $k;
    }  // end for loopthru all products

    // add line items for any surcharges added by order-total modules
    if ($surcharges > 0) {
      $numberOfLineItemsProcessed++;
      $k = $numberOfLineItemsProcessed;
      $optionsLI["L_NAME$k"] = MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_SURCHARGES_LONG;
      $optionsLI["L_AMT$k"]  = $surcharges;
      $optionsLI["L_QTY$k"]  = 1;
      $subTotalLI += $surcharges;
    }

    // add line items for discounts such as gift certificates and coupons
    if ($creditsApplied > 0) {
      $numberOfLineItemsProcessed++;
      $k = $numberOfLineItemsProcessed;
      $optionsLI["L_NAME$k"]   = MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_DISCOUNTS_LONG;
      $optionsLI["L_AMT$k"]    = (-1 * $creditsApplied);
      $optionsLI["L_QTY$k"]    = 1;
      $subTotalLI -= $creditsApplied;
    }

    // Reformat properly
    // Replace & and = and % with * if found.
    // reformat properly according to API specs
    // Remove HTML markup from name if found
    for ($k=0, $n=$numberOfLineItemsProcessed+1; $k<$n; $k++) {
      $optionsLI["L_NAME$k"] = str_replace(array('&','=','%'), '*', $optionsLI["L_NAME$k"]);
      $optionsLI["L_NAME$k"] = zen_clean_html($optionsLI["L_NAME$k"], 'strong');
      $optionsLI["L_NAME$k"]   = substr($optionsLI["L_NAME$k"], 0, 127);
      $optionsLI["L_AMT$k"] = round($optionsLI["L_AMT$k"], 2);

      if (isset($optionsLI["L_NUMBER$k"])) {
        if ($optionsLI["L_NUMBER$k"] == '') {
          unset($optionsLI["L_NUMBER$k"]);
        } else {
          $optionsLI["L_NUMBER$k"] = str_replace(array('&','=','%'), '*', $optionsLI["L_NUMBER$k"]);
          $optionsLI["L_NUMBER$k"] = substr($optionsLI["L_NUMBER$k"], 0, 127);
        }
      }

//      if (isset($optionsLI["L_TAXAMT$k"]) && ($optionsLI["L_TAXAMT$k"] != '' || $optionsLI["L_TAXAMT$k"] > 0)) {
//        $optionsLI["L_TAXAMT$k"] = round($optionsLI["L_TAXAMT$k"], 2);
//      }
    }

    // Sanity Check of line-item subtotals
    for ($j=0; $j<$k; $j++) {
      $itemAMT = $optionsLI["L_AMT$j"];
      $itemQTY = $optionsLI["L_QTY$j"];
      $itemTAX = (isset($optionsLI["L_TAXAMT$j"]) ? $optionsLI["L_TAXAMT$j"] : 0);
      $sumOfLineItems += ($itemQTY * $itemAMT);
      $sumOfLineTax += ($itemQTY * $itemTAX);
    }
    $sumOfLineItems = round($sumOfLineItems, 2);
    $sumOfLineTax = round($sumOfLineTax, 2);

    if ($sumOfLineItems == 0) {
      $sumOfLineTax = 0;
      $optionsLI = array();
      $discountProblemsFlag = TRUE;
      if ($optionsST['SHIPPINGAMT'] == $optionsST['AMT']) {
        $optionsST['SHIPPINGAMT'] = 0;
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
      if (isset($optionsLI["L_TAXAMT$k"])) unset($optionsLI["L_TAXAMT$k"]);
    }
    // if ITEMAMT >0 and subTotalLI > 0 and they're not equal ... OR subTotalLI minus sumOfLineItems isn't 0
    // check subtotals
    if ((strval($optionsST['ITEMAMT']) > 0 && strval($subTotalLI) > 0 && strval($subTotalLI) != strval($optionsST['ITEMAMT'])) || strval($subTotalLI) - strval($sumOfLineItems) != 0) {
      $this->zcLog('getLineItemDetails 5', 'Line-item subtotals do not add up properly. Line-item-details skipped.' . "\n" . strval($sumOfLineItems) . ' ' . strval($subTotalLI) . ' ' . print_r(array_merge($optionsST, $optionsLI), true));
      $optionsLI = array();
      $optionsLI["L_NAME0"] = MODULES_PAYMENT_PAYPALWPP_AGGREGATE_CART_CONTENTS;
      $optionsLI["L_AMT0"]  = $sumOfLineItems = $subTotalLI = $optionsST['ITEMAMT'];
    }

    // check whether discounts are causing a problem
    if (strval($optionsST['ITEMAMT']) < 0) {
      $pre = (array_merge($optionsST, $optionsLI));
      $optionsST['ITEMAMT'] = $optionsST['AMT'];
      $optionsLI = array();
      $optionsLI["L_NAME0"] = MODULES_PAYMENT_PAYPALWPP_AGGREGATE_CART_CONTENTS;
      $optionsLI["L_AMT0"]  = $sumOfLineItems = $subTotalLI = $optionsST['ITEMAMT'];
      if ($optionsST['AMT'] < $optionsST['TAXAMT']) $optionsST['TAXAMT'] = 0;
      if ($optionsST['AMT'] < $optionsST['SHIPPINGAMT']) $optionsST['SHIPPINGAMT'] = 0;
      $discountProblemsFlag = TRUE;
      $this->zcLog('getLineItemDetails 6', 'Discounts have caused the subtotal to calculate incorrectly. Line-item-details cannot be submitted.' . "\nBefore:" . print_r($pre, TRUE) . "\nAfter:" . print_r(array_merge($optionsST, $optionsLI), true));
    }

    // if AMT or ITEMAMT values are 0 (ie: certain OT modules disabled) or we've started express checkout without going through normal checkout flow, we have to get subtotals manually
    if ((!isset($optionsST['AMT']) || $optionsST['AMT'] == 0 || $flagSubtotalsUnknownYet == TRUE || $optionsST['ITEMAMT'] == 0) && $discountProblemsFlag != TRUE) {
      $optionsST['ITEMAMT'] = $sumOfLineItems;
      $optionsST['TAXAMT'] = $sumOfLineTax;
      if ($subTotalShipping > 0) $optionsST['SHIPPINGAMT'] = $subTotalShipping;
      $optionsST['AMT'] = $sumOfLineItems + $optionsST['TAXAMT'] + $optionsST['SHIPPINGAMT'];
    }
    $this->zcLog('getLineItemDetails 7 - subtotal comparisons', 'BEFORE line-item calcs: ' . print_r($subtotalPRE, true) . ($flagSubtotalsUnknownYet == TRUE ? 'Subtotals Unknown Yet - ' : '') . 'AFTER doing line-item calcs: ' . print_r(array_merge($optionsST, $optionsLI, $optionsNB), true));

    // if subtotals are not adding up correctly, then skip sending any line-item or subtotal details to PayPal
    $stAll = round(strval($optionsST['ITEMAMT']) + strval($optionsST['TAXAMT']) + strval($optionsST['SHIPPINGAMT']) + strval($optionsST['SHIPDISCAMT']) + strval($optionsST['HANDLINGAMT']) + strval($optionsST['INSURANCEAMT']), 2);
    $stDiff = strval($optionsST['AMT'] - $stAll);
    $stDiffRounded = (strval($stAll - round($optionsST['AMT'], 2)) + 0);

    // unset any subtotal values that are zero
    if (isset($optionsST['ITEMAMT']) && $optionsST['ITEMAMT'] == 0) unset($optionsST['ITEMAMT']);
    if (isset($optionsST['TAXAMT']) && $optionsST['TAXAMT'] == 0) unset($optionsST['TAXAMT']);
    if (isset($optionsST['SHIPPINGAMT']) && $optionsST['SHIPPINGAMT'] == 0) unset($optionsST['SHIPPINGAMT']);
    if (isset($optionsST['SHIPDISCAMT']) && $optionsST['SHIPDISCAMT'] == 0) unset($optionsST['SHIPDISCAMT']);
    if (isset($optionsST['HANDLINGAMT']) && $optionsST['HANDLINGAMT'] == 0) unset($optionsST['HANDLINGAMT']);
    if (isset($optionsST['INSURANCEAMT']) && $optionsST['INSURANCEAMT'] == 0) unset($optionsST['INSURANCEAMT']);

    // tidy up all values so that they comply with proper format (rounded to 2 decimals for PayPal US use )
    if (!defined('PAYPALWPP_SKIP_LINE_ITEM_DETAIL_FORMATTING') || PAYPALWPP_SKIP_LINE_ITEM_DETAIL_FORMATTING != 'true' || in_array($order->info['currency'], array('JPY', 'NOK', 'HUF', 'TWD'))) {
      if (is_array($optionsST)) foreach ($optionsST as $key=>$value) {
        $optionsST[$key] = round($value, ((int)$currencies->get_decimal_places($restrictedCurrency) == 0 ? 0 : 2));
      }
      if (is_array($optionsLI)) foreach ($optionsLI as $key=>$value) {
        if (substr($key, 0, 8) == 'L_TAXAMT' && ($optionsLI[$key] == '' || $optionsLI[$key] == 0)) {
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
  }
  /**
   * If the EC flow has to be interrupted for any reason, this does the appropriate cleanup and displays status/error messages.
   */
  function terminateEC($error_msg = '', $kill_sess_vars = false, $goto_page = '') {
    global $messageStack, $order, $order_total_modules;
    $error_msg = trim($error_msg);
    if (substr($error_msg, -1) == '-') $error_msg = trim(substr($error_msg, 0, strlen($error_msg) - 1));
    $stackAlert = 'checkout_payment';

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

    $this->zcLog('termEC-2', 'BEFORE: Token Data:' . $_SESSION['paypal_ec_token']);

    if ($error_msg) {
      $messageStack->add_session($stackAlert, $error_msg, 'error');
    }
    // debug
    $this->zcLog('termEC-10', 'Redirecting to ' . $goto_page . ' - Stack: ' . $stackAlert . "\n" . 'Message: ' . $error_msg . "\nSession Data: " . print_r($_SESSION, true));
    zen_redirect(zen_href_link($goto_page, '', 'SSL', true, false));
  }
  /**
   * Error / exception handling
   */
  function _errorHandler($response, $operation = '', $ignore_codes = '') {
    global $messageStack, $doPayPal;
    $gateway_mode = (isset($response['PNREF']) && $response['PNREF'] != '');
    $basicError = (!$response || (isset($response['RESULT']) && $response['RESULT'] != 0) || (isset($response['ACK']) && !strstr($response['ACK'], 'Success')) || (!isset($response['RESULT']) && !isset($response['ACK'])));
    if (isset($response['L_ERRORCODE0'])) {
    $ignoreList = explode(',', str_replace(' ', '', $ignore_codes));
    foreach($ignoreList as $key=>$value) {
            if ($value != '' && $response['L_ERRORCODE0'] == $value) {
                $basicError = false;
            }
        }
    }
    /** Handle FMF Scenarios **/
    if (in_array($operation, array('DoExpressCheckoutPayment', 'DoDirectPayment')) && $response['PAYMENTSTATUS'] == 'Pending' && isset($response['L_ERRORCODE0']) && $response['L_ERRORCODE0'] == 11610) {
      $this->fmfResponse = urldecode($response['L_SHORTMESSAGE0']);
      $this->fmfErrors = array();
      if ($response['ACK'] == 'SuccessWithWarning' && isset($response['L_FMFPENDINGID0'])) {
        for ($i=0; $i<20; $i++) {
          $this->fmfErrors[] = array('key' => $response['L_FMFPENDINGID' . $i], 'status' => $response['L_FMFPENDINGID' . $i], 'desc' => $response['L_FMFPENDINGDESCRIPTION' . $i]);
        }
      }
      return (sizeof($this->fmfErrors)>0) ? $this->fmfErrors : FALSE;
    }
    //echo '<br />basicError='.$basicError.'<br />' . urldecode(print_r($response,true)); die('halted');
    if (!isset($response['L_SHORTMESSAGE0']) && isset($response['RESPMSG']) && $response['RESPMSG'] != '') $response['L_SHORTMESSAGE0'] = $response['RESPMSG'];
    if (IS_ADMIN_FLAG === false) {
    $errorInfo = 'Problem occurred while customer ' . zen_output_string_protected($_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name']) . ' was attempting checkout with PayPal Website Payments Pro.';
    } else {
        $errorInfo = 'Problem occurred during admin updates using PayPal Website Payments Pro.';
    }

    switch($operation) {
      case 'DoDirectPayment':
        if ($basicError ||
           ((isset($_SESSION['paypal_ec_token']) && isset($response['TOKEN'])) && $_SESSION['paypal_ec_token'] != urldecode($response['TOKEN'])) ) {
            // Error, so send the store owner a complete dump of the transaction.
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - before_process() - DP', "In function: before_process() - Direct Payment \r\nDid first contact attempt return error? " . ($error_occurred ? "Yes" : "No") . " \r\n\r\nValue List:\r\n" . str_replace('&',"\r\n", urldecode($doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList)))) . "\r\n\r\nResponse:\r\n" . urldecode(print_r($response, true)));
          }
          $errorText = MODULE_PAYMENT_PAYPALDP_INVALID_RESPONSE;
          $errorNum = urldecode($response['L_ERRORCODE0'] . ' ' . $response['RESULT'] . ' <!-- ' . $response['RESPMSG'] . ' -->');
          if ($response['RESULT'] == 25) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_NOT_WPP_ACCOUNT_ERROR;
          if ($response['L_ERRORCODE0'] == 10500 || $response['L_ERRORCODE0'] == 10501) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_NOT_US_WPP_ACCOUNT_ERROR;
          if ($response['HOSTCODE'] == 10500 || $response['HOSTCODE'] == 10501) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_NOT_UKWPP_ACCOUNT_ERROR;
          if ($response['HOSTCODE'] == 10558) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_CANNOT_USE_THIS_CURRENCY_ERROR;
          if ($response['L_ERRORCODE0'] == 10002) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_SANDBOX_VS_LIVE_ERROR;
          if ($response['L_ERRORCODE0'] == 10565) {
            $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_WPP_BAD_COUNTRY_ERROR;
            $_SESSION['payment'] = '';
          }
          if ($response['L_ERRORCODE0'] == 10566) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_CARD_TYPE_NOT_SUPPORTED;
          if ($response['L_ERRORCODE0'] == 10417) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_TRY_OTHER_PAYMENT_METHOD;
          if ($response['L_ERRORCODE0'] == 10736) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_ADDR_ERROR;
          if ($response['L_ERRORCODE0'] == 10752) {
            $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_DECLINED;
            $errorNum = '10752';
          }
          if ($response['L_ERRORCODE0'] == 15012) { // Mastercard CE agreement not signed between merchant and PayPal. Thus cannot accept mastercard.
            $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_CARD_TYPE_NOT_SUPPORTED;
            $errorNum = '15012';
          }
          if ($response['L_ERRORCODE0'] == 15005) {
            $errorText = 'Card rejected by the bank. Your IP address has been recorded.';
            $errorNum = '15005';
          }
          if ($response['RESPMSG'] != '') $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_DECLINED . ' ' . $errorText;

          $detailedMessage = ($errorText == MODULE_PAYMENT_PAYPALDP_INVALID_RESPONSE || $errorText == MODULE_PAYMENT_PAYPALDP_TEXT_DECLINED || (int)trim($errorNum) > 0 || $this->enableDebugging || $response['CURL_ERRORS'] != '' || $this->emailAlerts) ? (isset($response['RESULT']) && $response['RESULT'] != 0 ? MODULE_PAYMENT_PAYPALDP_CANNOT_BE_COMPLETED . ' (' . $errorNum . ')' : $errorNum) . ' ' . urldecode(' ' . $response['L_SHORTMESSAGE0'] . ' - ' . $response['L_LONGMESSAGE0'] . ' ' . $response['CURL_ERRORS']) : '';
          $explain = "\n\nProblem occurred while customer #" . $_SESSION['customer_id'] . ' -- ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' -- was attempting checkout.' . "\n";
          $detailedEmailMessage = MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_MESSAGE . urldecode($response['L_ERRORCODE0']  . ' ' . $response['RESPMSG']. "\n" . $response['L_SHORTMESSAGE0'] . "\n" . $response['L_LONGMESSAGE0'] . $response['L_ERRORCODE1'] . "\n" . $response['L_SHORTMESSAGE1'] . "\n" . $response['L_LONGMESSAGE1'] . $response['L_ERRORCODE2'] . "\n" . $response['L_SHORTMESSAGE2'] . "\n" . $response['L_LONGMESSAGE2'] . ($response['CURL_ERRORS'] != '' ? "\n" . $response['CURL_ERRORS'] : '') . "\n\n" . 'Zen Cart message: ' . $detailedMessage . "\n\n" . $errorInfo . "\n\n" . 'Transaction Response Details: ' . print_r($response, true) . "\n\n" . 'Transaction Submission: ' . urldecode($doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList), true)));
          $detailedEmailMessage .= $explain;
          if (!isset($response['L_ERRORCODE0']) && isset($response['RESULT'])) $detailedEmailMessage .= "\n\n" . print_r($response, TRUE);
          zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_SUBJECT . ' (' . zen_uncomment($errorNum) . ')', zen_uncomment($detailedEmailMessage), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br(zen_uncomment($detailedEmailMessage))), 'paymentalert');
          if ($response['L_ERRORCODE0'] == 15012) $detailedEmailMessage = '';
          $this->terminateEC(($detailedEmailMessage == '' ? $errorText . ' (' . $errorNum . ') ' : $detailedMessage), ($gateway_mode ? true : false), FILENAME_CHECKOUT_PAYMENT);
          return true;
        }
        break;
      case 'DoRefund':
        if ($basicError || (!isset($response['RESPMSG']) && !isset($response['REFUNDTRANSACTIONID']))) {
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_REFUND_ERROR;
          if ($response['L_ERRORCODE0'] == 10009) $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_REFUNDFULL_ERROR;
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
          $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_AUTH_ERROR;
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
          $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_CAPT_ERROR;
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
          $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_VOID_ERROR;
          if ($response['RESULT'] == 12) $response['L_SHORTMESSAGE0'] = $response['RESULT'] . ' ' . $response['RESPMSG'];
          if ($response['RESULT'] == 108) $response['L_SHORTMESSAGE0'] = $response['RESULT'] . ' ' . $response['RESPMSG'];
          $errorText .= ' (' . urldecode($response['L_SHORTMESSAGE0']) . ') ' . $response['L_ERRORCODE0'];
          $messageStack->add_session($errorText, 'error');
          return true;
        }
        break;
      case 'GetTransactionDetails':
        if ($basicError) {
          if (isset($response['RESPMSG']) && $response['RESPMSG'] == 'Field format error: ORIGID missing') {
            return FALSE;
          }
          // if error, display error message. If debug options enabled, email dump to store owner
          if ($this->enableDebugging) {
            $this->_doDebug('PayPal Error Log - ' . $operation, "Value List:\r\n" . str_replace('&',"\r\n", $doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList))) . "\r\n\r\nResponse:\r\n" . print_r($response, true));
          }
          $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_GETDETAILS_ERROR;
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
          $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_TRANSSEARCH_ERROR;
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
          $errorText = MODULE_PAYMENT_PAYPALDP_TEXT_GEN_API_ERROR;
          $errorNum .= ' (' . urldecode($response['L_SHORTMESSAGE0'] . ' <!-- ' . $response['RESPMSG']) . ' -->) ' . $response['L_ERRORCODE0'];
          $detailedMessage = ($errorText == MODULE_PAYMENT_PAYPALDP_TEXT_GEN_API_ERROR || $errorText == MODULE_PAYMENT_PAYPALDP_TEXT_DECLINED || $this->enableDebugging || $response['CURL_ERRORS'] != '' || $this->emailAlerts) ? urldecode(' ' . $response['L_SHORTMESSAGE0'] . ' - ' . $response['L_LONGMESSAGE0'] . ' ' . $response['CURL_ERRORS']) : '';
          $explain = "\n\nProblem occurred while customer #" . $_SESSION['customer_id'] . ' -- ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' -- was attempting checkout.' . "\n";
          $detailedEmailMessage = ($detailedMessage == '') ? '' : MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_MESSAGE . ' ' . $response['RESPMSG'] . urldecode($response['L_ERRORCODE0'] . "\n" . $response['L_SHORTMESSAGE0'] . "\n" . $response['L_LONGMESSAGE0'] . $response['L_ERRORCODE1'] . "\n" . $response['L_SHORTMESSAGE1'] . "\n" . $response['L_LONGMESSAGE1'] . $response['L_ERRORCODE2'] . "\n" . $response['L_SHORTMESSAGE2'] . "\n" . $response['L_LONGMESSAGE2'] . ($response['CURL_ERRORS'] != '' ? "\n" . $response['CURL_ERRORS'] : '') . "\n\n" . 'Zen Cart message: ' . $detailedMessage . "\n\n" . $errorInfo . "\n\n" . 'Transaction Response Details: ' . print_r($response, true) . "\n\n" . 'Transaction Submission: ' . urldecode($doPayPal->_sanitizeLog($doPayPal->_parseNameValueList($doPayPal->lastParamList), true)));
          if ($detailedEmailMessage != '') $detailedEmailMessage .= $explain;
          if ($detailedEmailMessage != '') zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_SUBJECT . ' (' . zen_uncomment($errorNum) . ')', zen_uncomment($detailedMessage . $explain), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br(zen_uncomment($detailedEmailMessage))), 'paymentalert');
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

  /****************************************************************************************************************************
   * ADDED CODE FOR 3D-SECURE SUPPORT PROVIDED BY CARDINALCOMMERCE FOR PAYPAL-UK
   */

  /**
   * reset session vars related to 3D-Secure processing
   */
  function clear_3DSecure_session_vars($thorough = FALSE) {
    if ($thorough) {
      if (isset($_SESSION['3Dsecure_requires_lookup'])) unset($_SESSION['3Dsecure_requires_lookup']);
      if (isset($_SESSION['3Dsecure_card_type'])) unset($_SESSION['3Dsecure_card_type']);
    }
    if (isset($_SESSION['3Dsecure_merchantData'])) unset($_SESSION['3Dsecure_merchantData']);
    if (isset($_SESSION['3Dsecure_enroll_lookup_attempted'])) unset($_SESSION['3Dsecure_enroll_lookup_attempted']);
    if (isset($_SESSION['3Dsecure_authentication_attempted'])) unset($_SESSION['3Dsecure_authentication_attempted']);
    if (isset($_SESSION['3Dsecure_transactionId'])) unset($_SESSION['3Dsecure_transactionId']);
    if (isset($_SESSION['3Dsecure_enrolled'])) unset($_SESSION['3Dsecure_enrolled']);
    if (isset($_SESSION['3Dsecure_acsURL'])) unset($_SESSION['3Dsecure_acsURL']);
    if (isset($_SESSION['3Dsecure_payload'])) unset($_SESSION['3Dsecure_payload']);
    if (isset($_SESSION['3Dsecure_auth_status'])) unset($_SESSION['3Dsecure_auth_status']);
    if (isset($_SESSION['3Dsecure_sig_status'])) unset($_SESSION['3Dsecure_sig_status']);
    if (isset($_SESSION['3Dsecure_auth_xid'])) unset($_SESSION['3Dsecure_auth_xid']);
    if (isset($_SESSION['3Dsecure_auth_cavv'])) unset($_SESSION['3Dsecure_auth_cavv']);
    if (isset($_SESSION['3Dsecure_auth_eci'])) unset($_SESSION['3Dsecure_auth_eci']);
    if (isset($_SESSION['3Dsecure_term_url'])) unset($_SESSION['3Dsecure_term_url']);
    if (isset($_SESSION['3Dsecure_auth_url'])) unset($_SESSION['3Dsecure_auth_url']);
  }


  function determine3DSecureProtection($cardType, $ECI) {
    $resultStatus = "NOT PROTECTED";
    if (strcasecmp($cardType, "VISA") == 0){
      if ((strcasecmp($ECI, "05") == 0) || (strcasecmp($ECI, "06") == 0)) {
        $resultStatus = "PROTECTED";
      } else {
        $resultStatus = "NOT PROTECTED";
      }
    } else if (strcasecmp($cardType, "MASTERCARD") == 0){
      if (strcasecmp($ECI, "02") == 0) {
        $resultStatus = "PROTECTED";
      } else {
        $resultStatus = "NOT PROTECTED";
      }
    } else if (strcasecmp($cardType, "JCB") == 0){
      if ((strcasecmp($ECI, "05") == 0) || (strcasecmp($ECI, "06") == 0)) {
        $resultStatus = "PROTECTED";
      } else {
        $resultStatus = "NOT PROTECTED";
      }
    }
    return $resultStatus;
  }

  /**
   * 3D-Secure lookup
   *
   * @param array $lookup_data_array
   * @return array
   */
  function get3DSecureLookupResponse($lookup_data_array) {
    // Set some defaults
    if (!isset($lookup_data_array['order_desc']) || $lookup_data_array['order_desc'] == '') $lookup_data_array['order_desc'] = 'Zen Cart(R) Transaction';
    if (!isset($lookup_data_array['order_number']) || $lookup_data_array['order_number'] == '') $lookup_data_array['order_number'] = zen_session_id();
    // format the card expiration
    $lookup_data_array['cc3d_exp_year'] = (strlen($lookup_data_array['cc3d_exp_year']) == 2 ? '20' : '') . $lookup_data_array['cc3d_exp_year'];
    // get the ISO 4217 currency
    $iso_currency = $this->getISOCurrency($lookup_data_array['currency']);
    // format the transaction amounts
    $raw_amount = $this->formatRawAmount($lookup_data_array['txn_amount'], $iso_currency);
    // determine the appropriate product code for submission
    $prodCode = FALSE;
    if (isset($_SESSION['cart'])) {
      if ($_SESSION['cart']->get_content_type == 'virtual') {
        $prodCode = 'DIG';
      } else {
        $prodCode = 'PHY';
      }
    }

    // DEBUG ONLY: $this->zcLog(__FILE__ . '->' . __LINE__, 'session details: ' . print_r(array_merge($_POST, $_SESSION), true));

    // Build the XML cmpi_lookup message
    $data = '<CardinalMPI>';
    $data .= '<MsgType>cmpi_lookup</MsgType>';
    $data .= '<Version>1.7</Version>';
    $data .= '<ProcessorId>' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_PROCESSOR) . '</ProcessorId>';
    $data .= '<MerchantId><![CDATA[' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_MERCHANT) . ']]></MerchantId>';
    $data .= '<TransactionPwd><![CDATA[' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_PASSWORD) . ']]></TransactionPwd>';
    $data .= '<TransactionType>CC</TransactionType>';
    $data .= '<TransactionMode>S</TransactionMode>';
    $data .= '<OrderNumber>' . $this->escapeXML($lookup_data_array['order_number']) . '</OrderNumber>';
    $data .= '<OrderDescription>' . $this->escapeXML($lookup_data_array['order_desc']) . '</OrderDescription>';
    $data .= '<Amount>' . $this->escapeXML($raw_amount) . '</Amount>';
    $data .= '<CurrencyCode>' . $this->escapeXML($iso_currency) . '</CurrencyCode>';
    $data .= '<CardNumber>' . $this->escapeXML($lookup_data_array['cc3d_card_number']) . '</CardNumber>';
    $data .= '<Cvv>' . $this->escapeXML($lookup_data_array['cc3d_checkcode']) . '</Cvv>';
    $data .= '<CardCode>' . $this->escapeXML($lookup_data_array['cc3d_checkcode']) . '</CardCode>';
    $data .= '<CardExpMonth>' . $this->escapeXML($lookup_data_array['cc3d_exp_month']) . '</CardExpMonth>';
    $data .= '<CardExpYear>' . $this->escapeXML($lookup_data_array['cc3d_exp_year']) . '</CardExpYear>';
    $data .= '<UserAgent>' . $this->escapeXML($_SERVER["HTTP_USER_AGENT"]) . '</UserAgent>';
    $ipAddress = current(explode(':', str_replace(',', ':', zen_get_ip_address())));
    $data .= '<IPAddress>' . $this->escapeXML($ipAddress) . '</IPAddress>';
    $data .= '<BrowserHeader>' . $this->escapeXML($_SERVER["HTTP_ACCEPT"]) . '</BrowserHeader>';
    $data .= '<OrderChannel>' . $this->escapeXML('MARK') . '</OrderChannel>';
    if (isset($lookup_data_array['merchantData'])) $data .= '<MerchantData>' . $this->escapeXML($lookup_data_array['merchantData']) . '</MerchantData>';
    if ($prodCode !== FALSE && $prodCode != '') $data .= '<ProductCode>' . $this->escapeXML($prodCode) . '</ProductCode>';
    $data .= '</CardinalMPI>';
    $debugData = str_replace(array('[CDATA[' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_MERCHANT) . ']]', '[CDATA[' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_PASSWORD) . ']]', $this->escapeXML($lookup_data_array['cc3d_card_number']), $this->escapeXML($lookup_data_array['cc3d_checkcode'])), '********', $data);

    if (MODULE_PAYMENT_CARDINAL_CENTINEL_DEBUGGING !== FALSE) {
      $this->zcLog('Cardinal Lookup 1', '[' . zen_session_id() . '] Cardinal Centinel - cmpi_lookup request (' . MODULE_PAYMENT_PAYPALDP_CARDINAL_TXN_URL . ') - ' . $debugData);
    }

    $responseString = $this->send3DSecureHttp(MODULE_PAYMENT_PAYPALDP_CARDINAL_TXN_URL, $data, $debugData);

    if (MODULE_PAYMENT_CARDINAL_CENTINEL_DEBUGGING !== FALSE) {
      $this->zcLog('Cardinal Lookup 2', '[' . zen_session_id() . '] Cardinal Centinel - cmpi_lookup response - ' . $responseString);
    }

    // parse the XML
    $parser = new CardinalXMLParser;
    $parser->deserializeXml($responseString);

    $errorNo = $parser->deserializedResponse['ErrorNo'];
    $errorDesc = $parser->deserializedResponse['ErrorDesc'];
    $enrolled = $parser->deserializedResponse['Enrolled'];

    if ($errorNo != 0) {
      $this->zcLog('Cardinal Lookup 3', '[' . zen_session_id() . '] Cardinal Centinel - cmpi_lookup error - ' . $errorNo . ' - ' . $errorDesc);
      $errorText = 'Cardinal Lookup 3' . '[' . zen_session_id() . '] Cardinal Centinel - cmpi_lookup error - ' . $errorNo . ' - ' . $errorDesc;
      $errorText .= "\n\n" . 'There are 3 steps to configuring your Cardinal 3D-Secure service properly: ' . "\n1-Login to the Cardinal Merchant Admin URL supplied in your welcome package (NOT the test URL), and accept the license agreement.\n2-Set a transaction password.\n3-Copy your Cardinal Merchant ID and Cardinal Transaction Password into your ZC PayPal module.\n\nFor specific help, please contact implement@cardinalcommerce.com to sort out your account configuration issues.";
      $errorText .= "\n\nProblem observed while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication. THEIR PURCHASE WAS NOT SUCCESSFUL. Please resolve this matter to enable future checkouts.';
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, substr($errorDesc, 0, 75) . ' (' . $errorNo . ')', $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');
    }

    // default the continue flag to 'N'
    $continue_flag = 'N';

    // determine whether the transaction should continue or fail based upon
    // the enrollment lookup results
    if (strcasecmp(MODULE_PAYMENT_PAYPALDP_CARDINAL_AUTHENTICATE_REQ, 'No') == 0) {
      $continue_flag = 'Y';
    } else if (strcmp($errorNo, '0') == 0) {
      if (strcasecmp($enrolled, 'Y') == 0) {
        $continue_flag = 'Y';
      } else if (strcasecmp($enrolled, 'N') == 0) {
        $cardType = $this->determineCardType($this->cc_card_number);
        if (strcasecmp($cardType, 'VISA') == 0 || strcasecmp($cardType, 'JCB') == 0) {
          $continue_flag = 'Y';
        }
      }
    } else if ($errorNo == 1001) { // merchant has an account configuration problem to fix
      $errorText = CENTINEL_ERROR_CODE_1001 . ' - ' . CENTINEL_ERROR_CODE_1001_DESC;
      $errorText .= "\n\nProblem occurred while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication.';
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, CENTINEL_ERROR_CODE_1001_DESC . ' (' . CENTINEL_ERROR_CODE_1001 . ')', $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');
      $continue_flag = 'Y';
    }

    if (strcasecmp('Y', $continue_flag) == 0) {
      // For validation/security purposes, mark the session that the lookup result was acceptable.
      $_SESSION['3Dsecure_enroll_lookup_attempted'] = 'Y';
    } else {
      // For validation/security purposes, mark the session that the lookup result was not acceptable.
      unset($_SESSION['3Dsecure_enroll_lookup_attempted']);
    }

    $result = array('continue_flag' => $continue_flag,
                    'enrolled' => $enrolled,
                    'transaction_id' => $parser->deserializedResponse['TransactionId'],
                    'error_no' => $errorNo,
                    'error_desc' => $errorDesc,
                    'acs_url' => $parser->deserializedResponse['ACSUrl'],
                    'spa_hidden_fields' => $parser->deserializedResponse['SPAHiddenFields'],
                    'payload' => $parser->deserializedResponse['Payload'],
                    'cc3d_card_number' => $parser->deserializedResponse['CardNumber'],
                    'cc3d_checkcode' => $parser->deserializedResponse['CardCode'],
                    'cc3d_exp_month' => $parser->deserializedResponse['CardExpMonth'],
                    'cc3d_exp_year' => $parser->deserializedResponse['CardExpYear'],
                    'EciFlag' => $parser->deserializedResponse['EciFlag'],
                    'cc3d_merchantdata' => $parser->deserializedResponse['MerchantData']);
    return $result;
  }
  /**
   * 3D-Secure Authenticate
   * @param array $authenticate_data_array
   * @return array
   */
  function get3DSecureAuthenticateResponse($authenticate_data_array) {
    // Build the XML cmpi_authenticate message
    $data = '<CardinalMPI>';
    $data .= '<MsgType>cmpi_authenticate</MsgType>';
    $data .= '<Version>1.7</Version>';
    $data .= '<ProcessorId>' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_PROCESSOR) . '</ProcessorId>';
    $data .= '<MerchantId><![CDATA[' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_MERCHANT) . ']]></MerchantId>';
    $data .= '<TransactionType>CC</TransactionType>';
    $data .= '<TransactionPwd><![CDATA[' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_PASSWORD) . ']]></TransactionPwd>';
    $data .= '<TransactionId>' . $this->escapeXML($authenticate_data_array['transaction_id']) . '</TransactionId>';
    $data .= '<PAResPayload>' . $this->escapeXML($authenticate_data_array['payload']) . '</PAResPayload>';
    if (isset($authenticate_data_array['merchantData'])) $data .= '<MerchantData>' . $this->escapeXML($authenticate_data_array['merchantData']) . '</MerchantData>';
    $data .= '</CardinalMPI>';
    $debugData = str_replace(array('[CDATA[' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_MERCHANT) . ']]', '[CDATA[' . $this->escapeXML(MODULE_PAYMENT_PAYPALDP_CARDINAL_PASSWORD) . ']]'), '********', $data);

    if (MODULE_PAYMENT_CARDINAL_CENTINEL_DEBUGGING !== FALSE) {
      $this->zcLog('Cardinal Auth 1', '[' . zen_session_id() . '] Cardinal Centinel - cmpi_authenticate request (' . MODULE_PAYMENT_PAYPALDP_CARDINAL_TXN_URL . ') - ' . $debugData);
    }

    $responseString = $this->send3DSecureHttp(MODULE_PAYMENT_PAYPALDP_CARDINAL_TXN_URL, $data, $debugData);

    if (MODULE_PAYMENT_CARDINAL_CENTINEL_DEBUGGING !== FALSE) {
      $this->zcLog('Cardinal Auth 2', '[' . zen_session_id() . '] Cardinal Centinel - cmpi_authenticate response - ' . $responseString);
    }

    // parse the XML
    $parser = new CardinalXMLParser;
    $parser->deserializeXml($responseString);

    $errorNo = $parser->deserializedResponse['ErrorNo'];
    $errorDesc = $parser->deserializedResponse['ErrorDesc'];
    $authStatus = $parser->deserializedResponse['PAResStatus'];
    $sigStatus = $parser->deserializedResponse['SignatureVerification'];
    $xid = $parser->deserializedResponse['Xid'];
    $cavv = $parser->deserializedResponse['Cavv'];
    $eci = $parser->deserializedResponse['EciFlag'];

    // default the continue flag to 'N'
    $continue_flag = 'N';

    if ($errorNo == 0) {
      if (strcasecmp($authStatus, 'Y') == 0 || strcasecmp($authStatus, 'A') == 0) {
        $continue_flag = 'Y';
      } else if (strcasecmp($authStatus, 'N') == 0) {
        $continue_flag = 'N';
      } else if (strcasecmp($authStatus, 'U') == 0) {
        if (strcasecmp(MODULE_PAYMENT_PAYPALDP_CARDINAL_AUTHENTICATE_REQ, 'No') == 0) {
          $this->zcLog('Cardinal Auth 3', 'Business rule in effect (not requiring chargeback protection), so setting to continue to Y');
          $continue_flag = 'Y';
        }
      }
    } else {
      $this->zcLog('Cardinal Auth 4', '[' . zen_session_id() . '] Cardinal Centinel - cmpi_authenticate returned an error - ' . $errorNo . ' - ' . $errorDesc);
      $continue_flag = 'N';
    }

    if ($continue_flag =='Y' && strcasecmp($sigStatus, 'N') == 0) {
      // Signature status is 'N', do not continue
      $continue_flag = 'N';
    }

    if ($continue_flag == 'Y') {
      // For validation/security purposes, mark the session that the
      // authentication result was acceptable.
      $_SESSION['3Dsecure_authentication_attempted'] = 'Y';
    } else {
      // For validation/security purposes, mark the session that the
      // authentication result was not acceptable.
      unset($_SESSION['3Dsecure_authentication_attempted']);
    }

    $result = array('continue_flag' => $continue_flag,
                    'auth_status' => $authStatus,
                    'sig_status' => $sigStatus,
                    'error_no' => $errorNo,
                    'error_desc' => $errorDesc,
                    'auth_xid' => $xid,
                    'auth_cavv' => $cavv,
                    'auth_eci' => $eci,
                    'cc3d_card_number' => $parser->deserializedResponse['CardNumber'],
                    'cc3d_checkcode' => $parser->deserializedResponse['CardCode'],
                    'cc3d_exp_month' => $parser->deserializedResponse['CardExpMonth'],
                    'cc3d_exp_year' => $parser->deserializedResponse['CardExpYear'],
                    'cc3d_merchantdata' => $parser->deserializedResponse['MerchantData']);
    return $result;
  }

  /////////////////////////////////////////////////////////////////////////////////////////////
  // Function sendHttp(url, data)
  //
  // HTTP POST the form payload to the url using cURL.
  // form payload according to the Centinel XML Message APIs. The form payload is returned from
  // the function.
  /////////////////////////////////////////////////////////////////////////////////////////////

  function send3DSecureHttp($url, $data, $debugData) {
      // verify that the URL uses a supported protocol.
    if ((strpos($url, "http://")=== 0) || (strpos($url, "https://")=== 0)) {

      // create a new cURL resource and set params
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_POST,1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, "cmpi_msg=".urlencode($data));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
//   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // NOTE: Leave commented-out! or set to TRUE!  This should NEVER be set to FALSE in production!!!!
//   curl_setopt($ch, CURLOPT_CAINFO, '/local/path/to/cacert.pem'); // for offline testing, this file can be obtained from http://curl.haxx.se/docs/caextract.html ... should never be used in production!
      curl_setopt($ch, CURLOPT_TIMEOUT, 8);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);

      // Execute the request.
      $result = curl_exec($ch);
      $succeeded  = curl_errno($ch) == 0 ? true : false;
      $error = curl_errno($ch) . '-' . curl_error($ch);

      // close cURL resource, and free up system resources
      curl_close($ch);

      // If Communication was not successful set error result
      if (!$succeeded) {
        $this->zcLog('Cardinal Send 1', '[' . zen_session_id() . '] Cardinal Centinel - ' . CENTINEL_ERROR_CODE_8030_DESC);
        $this->zcLog('Cardinal Send 2', '[' . zen_session_id() . '] Centinel Request:  ' . $debugData);
        $this->zcLog('Cardinal Send 3', '[' . zen_session_id() . '] Centinel Response: ' . $result);
        $result = $this->setErrorResponse(CENTINEL_ERROR_CODE_8030, CENTINEL_ERROR_CODE_8030_DESC);

        $errorText = CENTINEL_ERROR_CODE_8030 . ' - ' . CENTINEL_ERROR_CODE_8030_DESC;
        $errorText .= "\n\nProblem occurred while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication.';
        if ($error != '-') $errorText .= "\n\nCURL error: " . $error;
        zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, CENTINEL_ERROR_CODE_8030_DESC . ' (' . CENTINEL_ERROR_CODE_8030 . ')', $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');
      } else if (strpos($result, "<CardinalMPI>") === false) {
        // Assert that we received an expected Centinel Message in response.
        $this->zcLog('Cardinal Send 4', '[' . zen_session_id() . '] Cardinal Centinel - ' . CENTINEL_ERROR_CODE_8010_DESC);
        $this->zcLog('Cardinal Send 5', '[' . zen_session_id() . '] Centinel Request:  ' . $debugData);
        $this->zcLog('Cardinal Send 6', '[' . zen_session_id() . '] Centinel Response: ' . $result);
        $result = $this->setErrorResponse(CENTINEL_ERROR_CODE_8010, CENTINEL_ERROR_CODE_8010_DESC);
        $errorText = CENTINEL_ERROR_CODE_8010 . ' - ' . CENTINEL_ERROR_CODE_8010_DESC;
        $errorText .= "\n\nProblem occurred while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication.';
        zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, CENTINEL_ERROR_CODE_8010_DESC . ' (' . CENTINEL_ERROR_CODE_8010 . ')', $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');
      } else {
        // Check whether the merchant has a properly configured 3D-Secure account
        if (strpos($result, "<ErrorNo>4243") > 0) {
          $this->zcLog('Cardinal Send 4', '[' . zen_session_id() . '] Cardinal Centinel - ' . CENTINEL_ERROR_CODE_4243_DESC);
          $this->zcLog('Cardinal Send 5', '[' . zen_session_id() . '] Centinel Request:  ' . $debugData);
          $this->zcLog('Cardinal Send 6', '[' . zen_session_id() . '] Centinel Response: ' . $result);
          $result = $this->setErrorResponse(CENTINEL_ERROR_CODE_4243, CENTINEL_ERROR_CODE_4243_DESC);
          $errorText = CENTINEL_ERROR_CODE_4243 . ' - ' . CENTINEL_ERROR_CODE_4243_DESC;
          $errorText .= "\n\nProblem occurred while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication.';
          zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, CENTINEL_ERROR_CODE_4243_DESC . ' (' . CENTINEL_ERROR_CODE_4243 . ')', $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');
        }
      }
    } else {
      $this->zcLog('Cardinal Send 7', '[' . zen_session_id() . '] Cardinal Centinel - ' . CENTINEL_ERROR_CODE_8000_DESC . ' - ' . $url);
      $result = $this->setErrorResponse(CENTINEL_ERROR_CODE_8000, CENTINEL_ERROR_CODE_8000_DESC);
      $errorText = CENTINEL_ERROR_CODE_8000 . ' - ' . CENTINEL_ERROR_CODE_8000_DESC;
      $errorText .= "\n\nProblem occurred while customer " . $_SESSION['customer_id'] . ' ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] . ' was attempting checkout with 3D-Secure authentication.';
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, CENTINEL_ERROR_CODE_8000_DESC . ' (' . CENTINEL_ERROR_CODE_8000 . ')', $errorText, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($errorText)), 'paymentalert');
    }

    return $result;
  }

  /////////////////////////////////////////////////////////////////////////////////////////////
  // Function escapeXML(value)
  //
  // Escaped string converting all '&' to '&amp;' and all '<' to '&lt'. Return the escaped value.
  /////////////////////////////////////////////////////////////////////////////////////////////
  function escapeXML($elementValue){
    $escapedValue = str_replace("&", "&amp;", trim($elementValue));
    $escapedValue = str_replace("<", "&lt;", $escapedValue);
    return $escapedValue;
  }

  /////////////////////////////////////////////////////////////////////////////////////////////
  // Function setErrorResponse(errorNo, errorDesc)
  //
  // Initialize an Error response to ensure that parsing will be handled properly.
  /////////////////////////////////////////////////////////////////////////////////////////////
  function setErrorResponse($errorNo, $errorDesc) {
    $resultText  = "<CardinalMPI>";
    $resultText = $resultText."<ErrorNo>".($errorNo)."</ErrorNo>" ;
    $resultText = $resultText."<ErrorDesc>".($errorDesc)."</ErrorDesc>" ;
    $resultText  = $resultText."</CardinalMPI>";
    return $resultText;
  }

  function get_authentication_error() {
    $this->clear_3DSecure_session_vars();
    return CENTINEL_AUTHENTICATION_ERROR;
  }

  // Convert Currency to ISO4217 3 digit code
  //   If curr is char code will convert to digit code
  //   If curr is digits less than 3, will pad with leading zeros
  //   If we are unable to format curr, curr is returned unformatted.
  //   MAPs will return the appropriate error code.
  function getISOCurrency($curr) {
    $out = "";
    if(ctype_digit($curr) || is_int($curr)) {
      $numCurr = $curr + 0;
      if($numCurr < 10) {
        $out = "00" . $numCurr;
      } else if ($numCurr < 100) {
        $out = "0" . $numCurr;
      } else {
        //Assume 3 digits (if greater let MAPs handle error)
        $out = "" . $numCurr;
      }
    } else {
      // Convert char to digit
      $curCode = Array();
      $curCode["AUD"]="036";
      $curCode["CAD"]="124";
      $curCode["CHF"]="756";
      $curCode["CZK"]="203";
      $curCode["DKK"]="208";
      $curCode["EUR"]="978";
      $curCode["GBP"]="826";
      $curCode["HUF"]="348";
      $curCode["JPY"]="392";
      $curCode["NOK"]="578";
      $curCode["NZD"]="554";
      $curCode["PLN"]="985";
      $curCode["SEK"]="752";
      $curCode["SGD"]="702";
      $curCode["USD"]="840";
      $out = $curCode[$curr];
    }

    return $out;
  }

  // Format Amount to rawamount
  //   Rawamount does not contain a decimal and is rounded and padded
  //   based on the currency exponenet value
  //   amount - Double floating point
  //   curr - ISO4217 Currency code, 3char or 3digit
  function formatRawAmount($amount, $curr) {
    $dblAmount = $amount + 0.0;

    // Build Currency format table
    $curFormat = Array();
    $curFormat["036"]=2;
    $curFormat["124"]=2;
    $curFormat["203"]=2;
    $curFormat["208"]=2;
    $curFormat["348"]=2;
    $curFormat["392"]=0;
    $curFormat["554"]=2;
    $curFormat["578"]=2;
    $curFormat["702"]=2;
    $curFormat["752"]=2;
    $curFormat["756"]=2;
    $curFormat["826"]=2;
    $curFormat["840"]=2;
    $curFormat["978"]=2;
    $curFormat["985"]=2;

    $digCurr = $this->getISOCurrency("" . $curr);
    $exponent = $curFormat[$digCurr];
    $strAmount = "" . Round($dblAmount, $exponent);
    $strRetVal = "" . $strAmount;

    // decimal position
    $curpos = strpos($strRetVal, ".");

    // Pad with zeros
    if($curpos == true) {
      $padCount = $exponent - (strlen($strRetVal) - $curpos - 1);
      for($i=0;$i<$padCount;$i++) {
        $strRetVal .= "0";
      }
    } else {
      $padCount = $exponent;
      for($i=0;$i<$padCount;$i++) {
        $strRetVal .= "0";
      }
    }

    if($curpos !== false) {
      $strRetVal = substr($strRetVal, 0, $curpos) . substr($strRetVal, $curpos+1);
    }
    return $strRetVal;
  }

  function requiresLookup($info) {
    if (is_numeric($info)) {
      $cardType = $this->determineCardType($info);
    } else {
      $cardType = $info;
    }
    if (in_array(strtoupper($cardType), array('VISA', 'MASTERCARD', 'JCB', 'MAESTRO'))) {
      return true;
    } else {
      return false;
    }
  }

  function determineCardType($cardNumber) {
    $cardNumber = preg_replace('/[^0-9]/', '', $cardNumber);
    // NOTE: We check Solo before Maestro, and Maestro *before* we check Visa/Mastercard, so we don't have to rule-out numerous types from V/MC matching rules.
    if (preg_match('/^(6334[5-9][0-9]|6767[0-9]{2})[0-9]{10}([0-9]{2,3}?)?$/', $cardNumber)) {
      $cardType = "SOLO";
    } else if (preg_match('/^(49369[8-9]|490303|6333[0-4][0-9]|6759[0-9]{2}|5[0678][0-9]{4}|6[0-9][02-9][02-9][0-9]{2})[0-9]{6,13}?$/', $cardNumber)) {
      $cardType = "MAESTRO";
    } else if (preg_match('/^(49030[2-9]|49033[5-9]|4905[0-9]{2}|49110[1-2]|49117[4-9]|49918[0-2]|4936[0-9]{2}|564182|6333[0-4][0-9])[0-9]{10}([0-9]{2,3}?)?$/', $cardNumber)) {
      $cardType = "MAESTRO"; // SWITCH is now Maestro
    } elseif (preg_match('/^4[0-9]{12}([0-9]{3})?$/', $cardNumber)) {
      $cardType = 'VISA';
    } elseif (preg_match('/^5[1-5][0-9]{14}$/', $cardNumber)) {
      $cardType = 'MASTERCARD';
    } elseif (preg_match('/^3[47][0-9]{13}$/', $cardNumber)) {
      $cardType = 'AMEX';
    } elseif (preg_match('/^3(0[0-5]|[68][0-9])[0-9]{11}$/', $cardNumber)) {
      $cardType = 'DINERS CLUB';
    } elseif (preg_match('/^(6011[0-9]{12}|622[1-9][0-9]{12}|64[4-9][0-9]{13}|65[0-9]{14})$/', $cardNumber)) {
      $cardType = 'DISCOVER';
    } elseif (preg_match('/^(35(28|29|[3-8][0-9])[0-9]{12}|2131[0-9]{11}|1800[0-9]{11})$/', $cardNumber)) {
      $cardType = "JCB";
    } else {
      $cardType = "UNKNOWN";
    }
    return $cardType;
  }

}



class CardinalXMLParser{

  var $xml_parser;
  var $deseralizedResponse;
  var $elementName;
  var $elementValue;

  /////////////////////////////////////////////////////////////////////////////////////////////
  // Function CardinalXMLParser()
  //
  // Initialize the XML parser.
  /////////////////////////////////////////////////////////////////////////////////////////////

  function __construct() {
    $this->xml_parser = xml_parser_create();
  }

  /////////////////////////////////////////////////////////////////////////////////////////////
  // Function startElement(parser, name, attribute)
  //
  // Start Tag Element Handler
  /////////////////////////////////////////////////////////////////////////////////////////////

  function startElement($parser, $name, $attrs='') {
    $this->elementName = $name;
  }

  /////////////////////////////////////////////////////////////////////////////////////////////
  // Function elementData(parser, data)
  //
  // Element Data Handler
  /////////////////////////////////////////////////////////////////////////////////////////////

  function elementData($parser, $data) {
    $this->elementValue .= $data;
  }

  /////////////////////////////////////////////////////////////////////////////////////////////
  // Function endElement(name, value)
  //
  // End Tag Element Handler
  /////////////////////////////////////////////////////////////////////////////////////////////

  function endElement($parser, $name) {
    if (substr($this->elementValue, 0, 1) == "\n") $this->elementValue = substr($this->elementValue, 1);
    $this->deserializedResponse[$this->elementName]= $this->elementValue;
    $this->elementName = "";
    $this->elementValue = "";
  }

  /////////////////////////////////////////////////////////////////////////////////////////////
  // Function deserialize(xmlString)
  //
  // Deserialize the XML reponse message and add each element to the deserializedResponse collection.
  // Once complete, then each element reference will be available using the getValue function.
  /////////////////////////////////////////////////////////////////////////////////////////////

  function deserializeXml($responseString) {
    xml_set_object($this->xml_parser, $this);
    xml_parser_set_option($this->xml_parser,XML_OPTION_CASE_FOLDING,FALSE);
    xml_set_element_handler($this->xml_parser, "startElement", "endElement");
    xml_set_character_data_handler($this->xml_parser, "elementData");

    if (!xml_parse($this->xml_parser, $responseString)) {
      $this->deserializedResponse["ErrorNo"]= CENTINEL_ERROR_CODE_8020;
      $this->deserializedResponse["ErrorDesc"]= CENTINEL_ERROR_CODE_8020_DESC;
    }

    xml_parser_free($this->xml_parser);
  }
}
