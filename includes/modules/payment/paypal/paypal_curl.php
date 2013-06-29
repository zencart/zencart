<?php
/**
 * paypal_curl.php communications class for PayPal Express Checkout / Website Payments Pro / Payflow Pro payment methods
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Aug 28 14:21:34 2012 -0400 Modified in v1.5.1 $
 */

/**
 * PayPal NVP (v61) and Payflow Pro (v4 HTTP API) implementation via cURL.
 */
class paypal_curl extends base {

  /**
   * What level should we log at? Valid levels are:
   *   1 - Log only severe errors.
   *   2 - Date/time of operation, operation name, elapsed time, success or failure indication.
   *   3 - Full text of requests and responses and other debugging messages.
   *
   * @access protected
   *
   * @var integer $_logLevel
   */
  var $_logLevel = 3;

  /**
   * If we're logging, what directory should we create log files in?
   * Note that a log name coincides with a symlink, logging will
   * *not* be done to avoid security problems. File names are
   * <DateStamp>.PayflowPro.log.
   *
   * @access protected
   *
   * @var string $_logFile
   */
  var $_logDir = 'logs';

  /**
   * Debug or production?
   */
  var $_server = 'sandbox';

  /**
   * URL endpoints -- defaults here are for three-token NVP implementation
   */
  var $_endpoints = array('live'    => 'https://api-3t.paypal.com/nvp',
                          'sandbox' => 'https://api-3t.sandbox.paypal.com/nvp');
  /**
   * Options for cURL. Defaults to preferred (constant) options.
   */
  var $_curlOptions = array(CURLOPT_HEADER => 0,
                            CURLOPT_RETURNTRANSFER => TRUE,
                            CURLOPT_TIMEOUT => 45,
                            CURLOPT_CONNECTTIMEOUT => 10,
                            CURLOPT_FOLLOWLOCATION => FALSE,
                          //CURLOPT_SSL_VERIFYPEER => FALSE, // Leave this line commented out! This should never be set to FALSE on a live site!
                          //CURLOPT_CAINFO => '/local/path/to/cacert.pem', // for offline testing, this file can be obtained from http://curl.haxx.se/docs/caextract.html ... should never be used in production!
                            CURLOPT_SSLVERSION => 3,
                            CURLOPT_FORBID_REUSE => TRUE,
                            CURLOPT_FRESH_CONNECT => TRUE,
                            CURLOPT_POST => TRUE,
                            );

  /**
   * Parameters that are always required and that don't change
   * request to request.
   */
  var $_partner;
  var $_vendor;
  var $_user;
  var $_pwd;
  var $_version;
  var $_signature;

  /**
   * nvp or payflow?
   */
  var $_mode = 'nvp';

  /**
   * Sales or authorizations? For the U.K. this will always be 'S'
   * (Sale) because of Switch and Solo cards which don't support
   * authorizations. The other option is 'A' for Authorization.
   * NOTE: 'A' is not supported for pre-signup-EC-boarding.
   */
  var $_trxtype = 'S';

  /**
   * Store the last-generated name/value list for debugging.
   */
  var $lastParamList = null;

  /**
   * Store the last-generated headers for debugging.
   */
  var $lastHeaders = null;
  /**
   * submission values
   */
  var $values = array();
  /**
   * Constructor. Sets up communication infrastructure.
   */
  function __construct($params = array()) {
    foreach ($params as $name => $value) {
      $this->setParam($name, $value);
    }
    $this->notify('NOTIFY_PAYPAL_CURL_CONSTRUCT', $params);
    if ($this->_mode == 'TESTCOMMUNICATIONS') {
      $this->testResults = $this->_request(array(), 'testCommunications');
    }
  }

  /**
   * SetExpressCheckout
   *
   * Prepares to send customer to PayPal site so they can
   * log in and choose their funding source and shipping address.
   *
   * The token returned to this function is passed to PayPal in
   * order to link their PayPal selections to their cart actions.
   */
  function SetExpressCheckout($returnUrl, $cancelUrl, $options = array()) {
    $values = $options;
    if ($this->_mode == 'payflow') {
      $values = array_merge($values, array('ACTION'  => 'S', /* ACTION=S denotes SetExpressCheckout */
                                           'TENDER'  => 'P',
                                           'TRXTYPE' => $this->_trxtype,
                                           'RETURNURL' => $returnUrl,
                                           'CANCELURL' => $cancelUrl));
    } elseif ($this->_mode == 'nvp') {
      if (!isset($values['PAYMENTACTION']) || ($this->checkHasApiCredentials() === FALSE)) $values['PAYMENTACTION'] = ($this->_trxtype == 'S' || ($this->checkHasApiCredentials() === FALSE) ? 'Sale' : 'Authorization');
      $values['RETURNURL'] = urlencode($returnUrl);
      $values['CANCELURL'] = urlencode($cancelUrl);
    }

    // convert country code key to proper key name for paypal 2.0 (needed when sending express checkout via payflow gateway, due to PayPal field naming inconsistency)
    if ($this->_mode == 'payflow') {
      if (!isset($values['SHIPTOCOUNTRY']) && isset($values['SHIPTOCOUNTRYCODE'])) {
        $values['SHIPTOCOUNTRY'] = $values['SHIPTOCOUNTRYCODE'];
        unset($values['SHIPTOCOUNTRYCODE']);
      }
      //if (isset($values['AMT'])) unset($values['AMT']);
    }

    // allow page-styling support -- see language file for definitions
    if (defined('MODULE_PAYMENT_PAYPALWPP_PAGE_STYLE'))   $values['PAGESTYLE'] = MODULE_PAYMENT_PAYPALWPP_PAGE_STYLE;
    if (defined('MODULE_PAYMENT_PAYPAL_LOGO_IMAGE')) $values['LOGOIMG'] = urlencode(MODULE_PAYMENT_LOGO_IMAGE);
    if (defined('MODULE_PAYMENT_PAYPAL_CART_BORDER_COLOR')) $values['CARTBORDERCOLOR'] = MODULE_PAYMENT_PAYPAL_CART_BORDER_COLOR;
    if (defined('MODULE_PAYMENT_PAYPALWPP_HEADER_IMAGE')) $values['HDRIMG'] = urlencode(MODULE_PAYMENT_PAYPALWPP_HEADER_IMAGE);
    if (defined('MODULE_PAYMENT_PAYPALWPP_PAGECOLOR'))    $values['PAYFLOWCOLOR'] = MODULE_PAYMENT_PAYPALWPP_PAGECOLOR;
    if (defined('MODULE_PAYMENT_PAYPALWPP_HEADER_BORDER_COLOR')) $values['HDRBORDERCOLOR'] = MODULE_PAYMENT_PAYPALWPP_HEADER_BORDER_COLOR;
    if (defined('MODULE_PAYMENT_PAYPALWPP_HEADER_BACK_COLOR')) $values['HDRBACKCOLOR'] = MODULE_PAYMENT_PAYPALWPP_HEADER_BACK_COLOR;

    if (PAYPAL_DEV_MODE == 'true') $this->log('SetExpressCheckout - breakpoint 1 - [' . print_r($values, true) .']');
    $this->values = $values;
    $this->notify('NOTIFY_PAYPAL_SETEXPRESSCHECKOUT');
    return $this->_request($this->values, 'SetExpressCheckout');
  }

  /**
   * GetExpressCheckoutDetails
   *
   * When customer returns from PayPal site, this retrieves their payment/shipping data for use in Zen Cart
   */
  function GetExpressCheckoutDetails($token, $optional = array()) {
    $values = array_merge($optional, array('TOKEN' => $token));
    if ($this->_mode == 'payflow') {
      $values = array_merge($values, array('ACTION'  => 'G', /* ACTION=G denotes GetExpressCheckoutDetails */
                                           'TENDER'  => 'P',
                                           'TRXTYPE' => $this->_trxtype));
    }
    $this->notify('NOTIFY_PAYPAL_GETEXPRESSCHECKOUTDETAILS');
    return $this->_request($values, 'GetExpressCheckoutDetails');
  }

  /**
   * DoExpressCheckoutPayment
   *
   * Completes the sale using PayPal as payment choice
   */
  function DoExpressCheckoutPayment($token, $payerId, $options = array()) {
    $values = array_merge($options, array('TOKEN'   => $token,
                                          'PAYERID' => $payerId));
    if (PAYPAL_DEV_MODE == 'true') $this->log('DoExpressCheckout - breakpoint 1 - ['.$token  . ' ' . $payerId . ' ' . "]\n\n[" . print_r($values, true) .']', $token);

    if ($this->_mode == 'payflow') {
      $values['ACTION'] = 'D'; /* ACTION=D denotes DoExpressCheckoutPayment via Payflow */
      $values['TENDER'] = 'P';
      $values['TRXTYPE'] = $this->_trxtype;
      $values['NOTIFYURL'] = zen_href_link('ipn_main_handler.php', '', 'SSL',false,false,true);
    } elseif ($this->_mode == 'nvp') {
      if (!isset($values['PAYMENTACTION']) || $this->checkHasApiCredentials() === FALSE) $values['PAYMENTACTION'] = ($this->_trxtype == 'S' || ($this->checkHasApiCredentials() === FALSE) ? 'Sale' : 'Authorization');
      $values['NOTIFYURL'] = urlencode(zen_href_link('ipn_main_handler.php', '', 'SSL',false,false,true));
    }
    $this->values = $values;
    $this->notify('NOTIFY_PAYPAL_DOEXPRESSCHECKOUTPAYMENT');
    if (PAYPAL_DEV_MODE == 'true') $this->log('DoExpressCheckout - breakpoint 2 '.print_r($this->values, true), $token);
    return $this->_request($this->values, 'DoExpressCheckoutPayment');
  }

  /**
   * DoDirectPayment
   * Sends CC information to gateway for processing.
   *
   * Requires Website Payments Pro or Payflow Pro as merchant gateway.
   *
   * PAYMENTACTION = Authorization (auth/capt) or Sale (final)
   */
  function DoDirectPayment($cc, $cvv2 = '', $exp, $fname = null, $lname = null, $cc_type, $options = array(), $nvp = array() ) {
    $values = $options;
    $values['ACCT'] = $cc;
    if ($cvv2 != '') $values['CVV2'] = $cvv2;
    $values['FIRSTNAME'] = $fname;
    $values['LASTNAME'] = $lname;
    if (isset($values['NAME'])) unset ($values['NAME']);

    if ($this->_mode == 'payflow') {
      $values['EXPDATE'] = $exp;
      $values['TENDER'] = 'C';
      $values['TRXTYPE'] = $this->_trxtype;
      $values['VERBOSITY'] = 'MEDIUM';
      $values['NOTIFYURL'] = zen_href_link('ipn_main_handler.php', '', 'SSL',false,false,true);
    } elseif ($this->_mode == 'nvp') {
      $values = array_merge($values, $nvp);
      if (isset($values['ECI'])) {
        $values['ECI3DS'] = $values['ECI'];
        unset($values['ECI']);
      }
      $values['CREDITCARDTYPE'] = ($cc_type == 'American Express') ? 'Amex' : $cc_type;
      $values['NOTIFYURL'] = urlencode(zen_href_link('ipn_main_handler.php', '', 'SSL',false,false,true));
      if (!isset($values['PAYMENTACTION'])) $values['PAYMENTACTION'] = ($this->_trxtype == 'S' ? 'Sale' : 'Authorization');

      if (isset($values['COUNTRY'])) unset ($values['COUNTRY']);
      if (isset($values['COMMENT1'])) unset ($values['COMMENT1']);
      if (isset($values['COMMENT2'])) unset ($values['COMMENT2']);
      if (isset($values['CUSTREF'])) unset ($values['CUSTREF']);
    }
    $this->values = $values;
    $this->notify('NOTIFY_PAYPAL_DODIRECTPAYMENT');
    ksort($this->values);
    return $this->_request($this->values, 'DoDirectPayment');
  }

  /**
   * RefundTransaction
   *
   * Used to refund all or part of a given transaction
   */
  function RefundTransaction($oID, $txnID, $amount = 'Full', $note = '', $curCode = 'USD') {
    if ($this->_mode == 'payflow') {
      $values['ORIGID'] = $txnID;
      $values['TENDER'] = 'C';
      $values['TRXTYPE'] = 'C';
      $values['AMT'] = number_format((float)$amount, 2);
      if ($note != '') $values['COMMENT2'] = $note;
    } elseif ($this->_mode == 'nvp') {
      $values['TRANSACTIONID'] = $txnID;
      if ($amount != 'Full' && (float)$amount > 0) {
        $values['REFUNDTYPE'] = 'Partial';
        $values['CURRENCYCODE'] = $curCode;
        $values['AMT'] = number_format((float)$amount, 2);
      } else {
        $values['REFUNDTYPE'] = 'Full';
      }
      if ($note != '') $values['NOTE'] = $note;
    }
    return $this->_request($values, 'RefundTransaction');
  }

  /**
   * DoVoid
   *
   * Used to void a previously authorized transaction
   */
  function DoVoid($txnID, $note = '') {
    if ($this->_mode == 'payflow') {
      $values['ORIGID'] = $txnID;
      $values['TENDER'] = 'C';
      $values['TRXTYPE'] = 'V';
      if ($note != '') $values['COMMENT2'] = $note;
    } elseif ($this->_mode == 'nvp') {
      $values['AUTHORIZATIONID'] = $txnID;
      if ($note != '') $values['NOTE'] = $note;
    }
    return $this->_request($values, 'DoVoid');
  }
  /**
   * DoAuthorization
   *
   * Used to authorize part of a previously placed order which was initiated as authType of Order
   */
  function DoAuthorization($txnID, $amount = 0, $currency = 'USD', $entity = 'Order') {
    $values['TRANSACTIONID'] = $txnID;
    $values['AMT'] = number_format($amount, 2, '.', ',');
    $values['TRANSACTIONENTITY'] = $entity;
    $values['CURRENCYCODE'] = $currency;
    return $this->_request($values, 'DoAuthorization');
  }

  /**
   * DoReauthorization
   *
   * Used to reauthorize a previously-authorized order which has expired
   */
  function DoReauthorization($txnID, $amount = 0, $currency = 'USD') {
    $values['AUTHORIZATIONID'] = $txnID;
    $values['AMT'] = number_format($amount, 2, '.', ',');
    $values['CURRENCYCODE'] = $currency;
    return $this->_request($values, 'DoReauthorization');
  }

  /**
   * DoCapture
   *
   * Used to capture part or all of a previously placed order which was only authorized
   */
  function DoCapture($txnID, $amount = 0, $currency = 'USD', $captureType = 'Complete', $invNum = '', $note = '') {
    if ($this->_mode == 'payflow') {
      $values['ORIGID'] = $txnID;
      $values['TENDER'] = 'C';
      $values['TRXTYPE'] = 'D';
      $values['VERBOSITY'] = 'MEDIUM';
      if ($invNum != '') $values['INVNUM'] = $invNum;
      if ($note != '') $values['COMMENT2'] = $note;
    } elseif ($this->_mode == 'nvp') {
      $values['AUTHORIZATIONID'] = $txnID;
      $values['COMPLETETYPE'] = $captureType;
      $values['AMT'] = number_format((float)$amount, 2);
      $values['CURRENCYCODE'] = $currency;
      if ($invNum != '') $values['INVNUM'] = $invNum;
      if ($note != '') $values['NOTE'] = $note;
    }
    return $this->_request($values, 'DoCapture');
  }

  /**
   * ManagePendingTransactionStatus
   *
   * Accept/Deny pending FMF transactions
   */
  function ManagePendingTransactionStatus($txnID, $action) {
    if (!in_array($action, array('Accept', 'Deny'))) return FALSE;
    $values['TRANSACTIONID'] = $txnID;
    $values['ACTION'] = $action;
    return $this->_request($values, 'ManagePendingTransactionStatus');
  }
  /**
   * GetTransactionDetails
   *
   * Used to read data from PayPal for a given transaction
   */
  function GetTransactionDetails($txnID) {
    if ($this->_mode == 'payflow') {
      $values['ORIGID'] = $txnID;
      $values['TENDER'] = 'C';
      $values['TRXTYPE'] = 'I';
      $values['VERBOSITY'] = 'MEDIUM';
    } elseif ($this->_mode == 'nvp') {
      $values['TRANSACTIONID'] = $txnID;
    }
    return $this->_request($values, 'GetTransactionDetails');
  }
  /**
   * TransactionSearch
   *
   * Used to read data from PayPal for specified transaction criteria
   */
  function TransactionSearch($startdate, $txnID = '', $email = '', $options) {
    if ($this->_mode == 'payflow') {
      $values['CUSTREF'] = $txnID;
      $values['TENDER'] = 'C';
      $values['TRXTYPE'] = 'I';
      $values['VERBOSITY'] = 'MEDIUM';
    } elseif ($this->_mode == 'nvp') {
      $values['STARTDATE'] = $startdate;
      $values['TRANSACTIONID'] = $txnID;
      $values['EMAIL'] = $email;
      if (is_array($options)) $values = array_merge($values, $options);
    }
    return $this->_request($values, 'TransactionSearch');
  }
  /**
   * Set a parameter as passed.
   */
  function setParam($name, $value) {
    $name = '_' . $name;
    $this->$name = $value;
  }

  /**
   * Set CURL options.
   */
  function setCurlOption($name, $value) {
    $this->_curlOptions[$name] = $value;
  }

  /**
   * Send a request to endpoint.
   */
  function _request($values, $operation, $requestId = null) {
    if ($this->_mode == 'NOTCONFIGURED') {
      return array('RESULT' => 'PayPal credentials not set. Cannot proceed.');
      die('NOTCONFIGURED');
    }
    if ($this->checkHasApiCredentials() === FALSE && (!in_array($operation, array('SetExpressCheckout','GetExpressCheckoutDetails', 'DoExpressCheckoutPayment')))) {
      return array('RESULT' => 'Unauthorized: Unilateral');
    }

    if (PAYPAL_DEV_MODE == 'true') $this->log('_request - breakpoint 1 - ' . $operation . "\n" . print_r($values, true));
    $start = $this->_getMicroseconds();

    if ($this->_mode == 'nvp') {
      $values['METHOD'] = $operation;
    }
    if ($this->_mode == 'payflow') {
      $values['REQUEST_ID'] = time();
    }
    // convert currency code to proper key name for nvp
    if ($this->_mode == 'nvp') {
      if (!isset($values['CURRENCYCODE']) && isset($values['CURRENCY'])) {
        $values['CURRENCYCODE'] = $values['CURRENCY'];
        unset($values['CURRENCY']);
      }
    }

    // request-id must be unique within 30 days
    if ($requestId === null) {
      $requestId = md5(uniqid(mt_rand()));
    }

    $headers[] = 'Content-Type: text/namevalue';
    $headers[] = 'X-VPS-Timeout: 90';
    $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";
    if ($this->_mode == 'payflow') {
      $headers[] = 'X-VPS-VIT-Integration-Product: PHP::Zen Cart(R) - PayPal/Payflow Pro';
    } elseif ($this->_mode == 'nvp') {
      $headers[] = 'X-VPS-VIT-Integration-Product: PHP::Zen Cart(R) - PayPal/NVP';
    }
    $headers[] = 'X-VPS-VIT-Integration-Version: 1.6.0';
    $this->lastHeaders = $headers;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->_endpoints[$this->_server]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_buildNameValueList($values));
    foreach ($this->_curlOptions as $name => $value) {
      curl_setopt($ch, $name, $value);
    }

    $response = curl_exec($ch);
    $commError = curl_error($ch);
    $commErrNo = curl_errno($ch);

    $commInfo = @curl_getinfo($ch);
    curl_close($ch);

    $rawdata = "CURL raw data:\n" . $response . "CURL RESULTS: (" . $commErrNo . ') ' . $commError . "\n" . print_r($commInfo, true) . "\nEOF";

    $errors = ($commErrNo != 0 ? "\n(" . $commErrNo . ') ' . $commError : '');
    $response .= '&CURL_ERRORS=' . ($commErrNo != 0 ? urlencode('(' . $commErrNo . ') ' . $commError) : '') ;

    // do debug/logging
    if ((!in_array($operation, array('GetTransactionDetails','TransactionSearch'))) || (in_array($operation, array('GetTransactionDetails','TransactionSearch')) && !strstr($response, '&ACK=Success')) ) $this->_logTransaction($operation, $this->_getElapsed($start), $response, $errors . ($commErrNo != 0 ? "\n" . print_r($commInfo, true) : ''));

    if ($operation == 'testCommunications') {
      return ($commInfo['http_code'] == 200) ? TRUE : str_replace("\n", '', $errors);
    }

    if ($response) {
      return $this->_parseNameValueList($response);
    } else {
      return false;
    }
  }

  /**
   * Take an array of name-value pairs and return a properly
   * formatted list. Enforces the following rules:
   *
   *   - Names must be uppercase, all characters must match [A-Z].
   *   - Values cannot contain quotes.
   *   - If values contain & or =, the name has the length appended to
   *     it in brackets (NAME[4] for a 4-character value.
   *
   * If any of the "cannot" conditions are violated the function
   * returns false, and the caller must abort and not proceed with
   * the transaction.
   */
  function _buildNameValueList($pairs) {
    // Add the parameters that are always sent.
    $commpairs = array();
    // generic:
    if ($this->_user != '')      $commpairs['USER'] = str_replace('+', '%2B', trim($this->_user));
    if ($this->_pwd != '')       $commpairs['PWD'] = trim($this->_pwd);
    // PRO2.0 options:
    if ($this->_partner != '')   $commpairs['PARTNER'] = trim($this->_partner);
    if ($this->_vendor != '')    $commpairs['VENDOR'] = trim($this->_vendor);
    // NVP-specific options:
    if ($this->_version != '')   $commpairs['VERSION'] = trim($this->_version);
    if ($this->_signature != '') $commpairs['SIGNATURE'] = trim($this->_signature);

    // Use sandbox credentials if defined and sandbox selected
    if ($this->_server == 'sandbox'
        && defined('MODULE_PAYMENT_PAYPALWPP_SANDBOX_APIUSERNAME') && MODULE_PAYMENT_PAYPALWPP_SANDBOX_APIUSERNAME != ''
        && defined('MODULE_PAYMENT_PAYPALWPP_SANDBOX_APIPASSWORD') && MODULE_PAYMENT_PAYPALWPP_SANDBOX_APIPASSWORD != ''
        && defined('MODULE_PAYMENT_PAYPALWPP_SANDBOX_APISIGNATURE') && MODULE_PAYMENT_PAYPALWPP_SANDBOX_APISIGNATURE != '') {
      $commpairs['USER'] = str_replace('+', '%2B', trim(MODULE_PAYMENT_PAYPALWPP_SANDBOX_APIPASSWORD));
      $commpairs['PWD'] = trim(MODULE_PAYMENT_PAYPALWPP_SANDBOX_APIPASSWORD);
      $commpairs['SIGNATURE'] = trim(MODULE_PAYMENT_PAYPALWPP_SANDBOX_APISIGNATURE);
    }

    // Adjustments if Micropayments account profile details have been set
    if (defined('MODULE_PAYMENT_PAYPALWPP_MICROPAY_THRESHOLD') && MODULE_PAYMENT_PAYPALWPP_MICROPAY_THRESHOLD != ''
        && (($pairs['AMT'] > 0 && $pairs['AMT'] < strval(MODULE_PAYMENT_PAYPALWPP_MICROPAY_THRESHOLD) )
           || ($pairs['METHOD'] == 'GetExpressCheckoutDetails' && isset($_SESSION['using_micropayments']) && $_SESSION['using_micropayments'] == TRUE))
        && defined('MODULE_PAYMENT_PAYPALWPP_MICROPAY_APIUSERNAME') && MODULE_PAYMENT_PAYPALWPP_MICROPAY_APIUSERNAME != ''
        && defined('MODULE_PAYMENT_PAYPALWPP_MICROPAY_APIPASSWORD') && MODULE_PAYMENT_PAYPALWPP_MICROPAY_APIPASSWORD != ''
        && defined('MODULE_PAYMENT_PAYPALWPP_MICROPAY_APISIGNATURE') && MODULE_PAYMENT_PAYPALWPP_MICROPAY_APISIGNATURE != '') {
      $commpairs['USER'] = str_replace('+', '%2B', trim(MODULE_PAYMENT_PAYPALWPP_MICROPAY_APIUSERNAME));
      $commpairs['PWD'] = trim(MODULE_PAYMENT_PAYPALWPP_MICROPAY_APIPASSWORD);
      $commpairs['SIGNATURE'] = trim(MODULE_PAYMENT_PAYPALWPP_MICROPAY_APISIGNATURE);
      $_SESSION['using_micropayments'] = ($pairs['METHOD'] == 'DoExpressCheckoutPayment') ? FALSE : TRUE;
    }

    // Accelerated/Unilateral Boarding support:
    if ($this->checkHasApiCredentials() == FALSE) {
      $commpairs['SUBJECT'] = STORE_OWNER_EMAIL_ADDRESS;
      $commpairs['USER'] = '';
      $commpairs['PWD'] = '';
      $commpairs['SIGNATURE'] = '';
    }

    $pairs = array_merge($pairs, $commpairs);

    $string = array();
    foreach ($pairs as $name => $value) {
      if (preg_match('/[^A-Z_0-9]/', $name)) {
        if (PAYPAL_DEV_MODE == 'true') $this->log('_buildNameValueList - datacheck - ABORTING - preg_match found invalid submission key: ' . $name . ' (' . $value . ')');
        return false;
      }
      // remove quotation marks
      $value = str_replace('"', '', $value);
      // if the value contains a & or = symbol, handle it differently
      if (($this->_mode == 'payflow') && (strpos($value, '&') !== false || strpos($value, '=') !== false)) {
        $string[] = $name . '[' . strlen($value) . ']=' . $value;
        if (PAYPAL_DEV_MODE == 'true') $this->log('_buildNameValueList - datacheck - adding braces and string count to: ' . $value . ' (' . $name . ')');
      } else {
        if ($this->_mode == 'nvp' && ((strstr($name, 'SHIPTO') || strstr($name, 'L_NAME')) && (strpos($value, '&') !== false || strpos($value, '=') !== false))) $value = urlencode($value);
        $string[] = $name . '=' . $value;
      }
    }

    $this->lastParamList = implode('&', $string);
    return $this->lastParamList;
  }

  /**
   * Take a name/value response string and parse it into an
   * associative array. Doesn't handle length tags in the response
   * as they should not be present.
   */
  function _parseNameValueList($string) {
    $string = str_replace('&amp;', '|', $string);
    $pairs = explode('&', str_replace(array("\r\n","\n"), '', $string));
    //$this->log('['.$string . "]\n\n[" . print_r($pairs, true) .']');
    $values = array();
    foreach ($pairs as $pair) {
      list($name, $value) = explode('=', $pair, 2);
      $values[$name] = str_replace('|', '&amp;', $value);
    }
    return $values;
  }

  /**
   * Log the current transaction depending on the current log level.
   *
   * @access protected
   *
   * @param string $operation  The operation called.
   * @param integer $elapsed   Microseconds taken.
   * @param object $response   The response.
   */
  function _logTransaction($operation, $elapsed, $response, $errors) {
    $values = $this->_parseNameValueList($response);
    $token = isset($values['TOKEN']) ? $values['TOKEN'] : '';
    $token = preg_replace('/[^0-9.A-Z\-]/', '', urldecode($token));
    $success = false;
    if ($response) {
      if ((isset($values['RESULT']) && $values['RESULT'] == 0) || (isset($values['ACK']) && (strstr($values['ACK'],'Success') || strstr($values['ACK'],'SuccessWithWarning')) && !strstr($values['ACK'],'Failure'))) {
        $success = true;
      }
    }
    $message =   date('Y-m-d h:i:s') . "\n-------------------\n";
    $message .=  '(' . $this->_server . ' transaction) --> ' . $this->_endpoints[$this->_server] . "\n";
    $message .= 'Request Headers: ' . "\n" . $this->_sanitizeLog($this->lastHeaders) . "\n\n";
    $message .= 'Request Parameters: {' . $operation . '} ' . "\n" . urldecode($this->_sanitizeLog($this->_parseNameValueList($this->lastParamList))) . "\n\n";
    $message .= 'Response: ' . "\n" . urldecode($this->_sanitizeLog($values)) . $errors;

    if ($this->_logLevel > 0 || $success == FALSE) {
      $this->log($message, $token);
      // extra debug email: //
      if (MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log and Email') {
        zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'PayPal Debug log - ' . $operation, $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($message)), 'debug');
      }
      $this->log($operation . ', Elapsed: ' . $elapsed . 'ms -- ' . (isset($values['ACK']) ? $values['ACK'] : ($success ? 'Succeeded' : 'Failed')) . $errors, $token);

      if (!$response) {
        $this->log('No response from server' . $errors, $token);
      } else {
        if ((isset($values['RESULT']) && $values['RESULT'] != 0) || strstr($values['ACK'],'Failure')) {
          $this->log($response . $errors, $token);
        }
      }
    }
  }

  /**
   * Strip sensitive information (passwords, credit card numbers, cvv2 codes) from requests/responses.
   *
   * @access protected
   *
   * @param mixed $log  The log to sanitize.
   * @return string  The sanitized (and string-ified, if necessary) log.
   */
  function _sanitizeLog($log, $allsensitive = false) {
    if (is_array($log)) {
      foreach (array_keys($log) as $key) {
        switch (strtolower($key)) {
          case 'pwd':
          case 'cvv2':
            $log[$key] = str_repeat('*', strlen($log[$key]));
            break;

          case 'signature':
          case 'acct':
            $log[$key] = str_repeat('*', strlen(substr($log[$key], 0, -4))) . substr($log[$key], -4);
            break;
          case 'solutiontype':
            unset($log[$key]);
            break;
        }
        if ($allsensitive && in_array($key, array('BUTTONSOURCE', 'VERSION', 'SIGNATURE', 'USER', 'VENDOR', 'PARTNER', 'PWD', 'VERBOSITY'))) unset($log[$key]);
      }
      return print_r($log, true);
    } else {
      return $log;
    }
  }

  function log($message, $token = '') {
    static $tokenHash;
    if ($tokenHash == '') $tokenHash = '_' . zen_create_random_value(4);
    $this->outputDestination = 'File';
    $this->notify('PAYPAL_CURL_LOG', $token, $tokenHash);
    if ($token == '') $token = $_SESSION['paypal_ec_token'];
    if ($token == '') $token = time();
    $token .= $tokenHash;
    if ($this->outputDestination == 'File') {
      $file = $this->_logDir . '/' . 'Paypal_CURL_' . $token . '.log';
      if ($fp = @fopen($file, 'a')) {
        fwrite($fp, $message . "\n\n");
        fclose($fp);
      }
    }
  }
  /**
   * Check whether API credentials are supplied, or if is blank
   *
   * @return boolean
   */
  function checkHasApiCredentials()
  {
    return ($this->_mode == 'nvp' && ($this->_user == '' || $this->_pwd == '')) ? FALSE : TRUE;
  }
  /**
   * Return the current time including microseconds.
   *
   * @access protected
   *
   * @return integer  Current time with microseconds.
   */
  function _getMicroseconds() {
    list($ms, $s) = explode(' ', microtime());
    return floor($ms * 1000) + 1000 * $s;
  }

  /**
   * Return the difference between now and $start in microseconds.
   *
   * @access protected
   *
   * @param integer $start  Start time including microseconds.
   *
   * @return integer  Number of microseconds elapsed since $start
   */
  function _getElapsed($start) {
    return $this->_getMicroseconds() - $start;
  }
}
/**
 * Convert HTML comments to readable text
 * @param string $string
 * @return string
 */
function zen_uncomment($string) {
  return str_replace(array('<!-- ', ' -->'), array('[', ']'), $string);
}