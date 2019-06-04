<?php
/**
 * Square payments module
 * www.squareup.com
 *
 * Integrated using SquareConnect PHP SDK v2.5.1 thru 2.20181205.0
 *
 * REQUIRES PHP 5.4 or newer
 *
 * @package square
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Mar 15 Modified in v1.5.6b $
 */

if (!defined('TABLE_SQUARE_PAYMENTS')) define('TABLE_SQUARE_PAYMENTS', DB_PREFIX . 'square_payments');

// required to prevent PHP 5.3 from throwing errors:
if (!defined('JSON_PRETTY_PRINT')) define('JSON_PRETTY_PRINT', 128);

/**
 * Square Payments module class
 */
class square extends base
{
    /**
     * $code determines the internal 'code' name used to designate "this" payment module
     *
     * @var string
     */
    public $code;
    /**
     * $moduleVersion is the plugin version number
     */
    public $moduleVersion = '0.97';
    protected $SquareApiVersion = '2018-12-05';
    /**
     * $title is the displayed name for this payment method
     *
     * @var string
     */
    public $title;
    /**
     * $description is admin-display details for this payment method
     *
     * @var string
     */
    public $description;
    /**
     * $enabled determines whether this module shows or not... in catalog.
     *
     * @var boolean
     */
    public $enabled;
    /**
     * $sort_order determines the display-order of this module to customers
     */
    public $sort_order;
    /**
     * $commError and $commErrNo are CURL communication error details for debug purposes
     */
    protected $commError, $commErrNo;
    /**
     * transaction vars hold the IDs of the completed payment
     */
    public $transaction_id, $transaction_messages, $auth_code;
    protected $currency_comment;


    /**
     * Constructor
     */
    public function __construct()
    {
        require DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/square/connect/autoload.php';
        require_once DIR_FS_CATALOG . 'includes/modules/payment/square_support/ZenCartChargeRequest.php';

        global $order;
        $this->code        = 'square';
        $this->enabled     = (defined('MODULE_PAYMENT_SQUARE_STATUS') && MODULE_PAYMENT_SQUARE_STATUS == 'True');
        $this->sort_order  = defined('MODULE_PAYMENT_SQUARE_SORT_ORDER') ? MODULE_PAYMENT_SQUARE_SORT_ORDER : null;
        $this->title       = MODULE_PAYMENT_SQUARE_TEXT_CATALOG_TITLE; // Payment module title in Catalog
        $this->description = '<strong>Square Payments Module ' . $this->moduleVersion . '</strong><br><br>' . MODULE_PAYMENT_SQUARE_TEXT_DESCRIPTION;
        if (IS_ADMIN_FLAG === true) {
            $this->title = MODULE_PAYMENT_SQUARE_TEXT_ADMIN_TITLE;
            if (defined('MODULE_PAYMENT_SQUARE_STATUS')) {
                if (MODULE_PAYMENT_SQUARE_APPLICATION_ID == '') $this->title .= '<span class="alert"> (not configured; API details needed)</span>';
                if (MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '') {
                    $this->title       .= '<span class="alert"> (Access Token needed)</span>';
                    $this->description .= "\n" . '<br><br>' . sprintf(MODULE_PAYMENT_SQUARE_TEXT_NEED_ACCESS_TOKEN, $this->getAuthorizeURL());
                    $this->description .= '<script>
                    function tokenCheckSqH(){
                        $.ajax({
                            url: "' . str_replace(array('index.php?main_page=index', 'http://'), array('square_handler.php?nocache=1', 'https://'), zen_catalog_href_link(FILENAME_DEFAULT, '', 'SSL')) . '",
                            cache: false,
                            success: function() {
                              window.location.reload();
                            }
                          });
                          return true;
                    }
                    $(".onClickStartCheck").click(function(){setInterval(function() {tokenCheckSqH()}, 4000)});
                    </script>';
                }
                if (MODULE_PAYMENT_SQUARE_TESTING_MODE == 'Sandbox') $this->title .= '<span class="alert"> (Sandbox mode)</span>';
                $new_version_details = plugin_version_check_for_updates(156, $this->moduleVersion);
                if ($new_version_details !== false) {
                    $this->title .= '<span class="alert">' . ' - NOTE: A NEW VERSION OF THIS PLUGIN IS AVAILABLE. <a href="' . $new_version_details['link'] . '" target="_blank">[Details]</a>' . '</span>';
                }
            }
            $this->tableCheckup();
        }

        // determine order-status for transactions
        if (defined('MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID;
        }
        // Reset order status to pending if capture pending:
        if (defined('MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE') && MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE == 'authorize') {
            $this->order_status = 1;
        }

        $this->_logDir = DIR_FS_LOGS;

        // module can't work without a token; must be configured with OAUTH refreshable token
        if (!defined('MODULE_PAYMENT_SQUARE_ACCESS_TOKEN') || ((MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '' || MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT == '') && MODULE_PAYMENT_SQUARE_TESTING_MODE == 'Live')) {
            $this->enabled = false;
        }

        // check for zone compliance and any other conditionals
        if ($this->enabled && is_object($order)) $this->update_status();
    }


    public function update_status()
    {
        global $order, $db;
        if ($this->enabled == false || (int)MODULE_PAYMENT_SQUARE_ZONE == 0) {
            return;
        }
        if (!isset($order->billing['country']['id'])) return;

        $check_flag = false;
        $sql        = "SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . (int)MODULE_PAYMENT_SQUARE_ZONE . "' AND zone_country_id = '" . (int)$order->billing['country']['id'] . "' ORDER BY zone_id";
        $checks     = $db->Execute($sql);
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

    public function javascript_validation()
    {
        return '';
    }

    public function selection()
    {
        // helper for auto-selecting the radio-button next to this module so the user doesn't have to make that choice
        $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

        $selection = array(
            'id'     => $this->code,
            'module' => $this->title,
            'fields' => array(
                array(
                    'title' => MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_NUMBER,
                    'field' => '<div id="' . $this->code . '_cc-number"></div><div id="sq-card-brand"></div>',
                ),
                array(
                    'title' => MODULE_PAYMENT_SQUARE_TEXT_CVV,
                    'field' => '<div id="' . $this->code . '_cc-cvv"></div>',
                ),
                array(
                    'title' => MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_EXPIRES,
                    'field' => '<div id="' . $this->code . '_cc-expires"></div>',
                ),
                array(
                    'title' => MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_POSTCODE,
                    'field' => '<div id="' . $this->code . '_cc-postcode"></div>',
                ),
                array(
                    'field' => '<div id="card-errors" class="alert error"></div>',
                ),
                array(
                    'title' => '',
                    'field' => '<input type="hidden" id="card-nonce" name="nonce">' .
                        '<input type="hidden" id="card-type" name="' . $this->code . '_cc_type">' .
                        '<input type="hidden" id="card-four" name="' . $this->code . '_cc_four">' .
                        '<input type="hidden" id="card-exp" name="' . $this->code . '_cc_exp">',
                ),
            ),
        );

        return $selection;
    }

    public function pre_confirmation_check()
    {
        global $messageStack;
        if (!isset($_POST['nonce']) || trim($_POST['nonce']) == '') {
            $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_ERROR_INVALID_CARD_DATA, 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }
    }

    public function confirmation()
    {
        $confirmation = array(
            'fields' => array(
                array(
                    'title' => MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_TYPE,
                    'field' => zen_output_string_protected($_POST[$this->code . '_cc_type']),
                ),
                array(
                    'title' => MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_NUMBER,
                    'field' => zen_output_string_protected($_POST[$this->code . '_cc_four']),
                ),
                array(
                    'title' => MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_EXPIRES,
                    'field' => zen_output_string_protected($_POST[$this->code . '_cc_exp']),
                ),
            ),
        );

        return $confirmation;
    }

    public function process_button()
    {
        $process_button_string = zen_draw_hidden_field($this->code . '_nonce', $_POST['nonce']);
        $process_button_string .= zen_draw_hidden_field('cc_type', zen_output_string_protected($_POST[$this->code . '_cc_type']));
        $process_button_string .= zen_draw_hidden_field('cc_four', zen_output_string_protected($_POST[$this->code . '_cc_four']));
        $process_button_string .= zen_draw_hidden_field('cc_expires', zen_output_string_protected($_POST[$this->code . '_cc_exp']));

        return $process_button_string;
    }

    public function before_process()
    {
        global $messageStack, $order, $currencies;

        if (!isset($_POST[$this->code . '_nonce']) || trim($_POST[$this->code . '_nonce']) == '') {
            $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_ERROR_INVALID_CARD_DATA, 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $order->info['cc_type']   = zen_output_string_protected($_POST['cc_type']);
        $order->info['cc_number'] = zen_output_string_protected($_POST['cc_four']);
        if (!strpos($order->info['cc_number'], 'XX')) {
            $order->info['cc_number'] = 'XXXX' . zen_output_string_protected(substr($_POST['cc_four'], -4));
        }
        $order->info['cc_expires'] = zen_output_string_protected($_POST['cc_expires']);
        $order->info['cc_cvv']     = '***';

        // get Square Location (since we need the ID and the currency for preparing transaction)
        $this->getAccessToken();
        $location = $this->getLocationDetails();

        $payment_amount = $order->info['total'];
        $currency_code  = strtoupper($order->info['currency']);

        $this->currency_comment = '';

        // force conversion to Square Account's currency:
        if ($order->info['currency'] != $location->currency || $order->info['currency'] != DEFAULT_CURRENCY) {
            $payment_amount = $currencies->rateAdjusted($order->info['total'], true, $location->currency);
            $currency_code  = $location->currency;
            if ($order->info['currency'] != $location->currency) {
                $this->currency_comment = '(Converted from: ' . round($order->info['total'] * $order->info['currency_value'], 2) . ' ' . $order->info['currency'] . ')';
            }
        }
        // @TODO - if Square adds support for transmission of tax and shipping amounts, these may need recalculation here too

        $billing_address = array(
            'address_line'                    => (string)$order->billing['street_address'],
            'address_line_2'                  => (string)$order->billing['suburb'],
            'locality'                        => (string)$order->billing['city'],
            'administrative_district_level_1' => (string)zen_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
            'postal_code'                     => (string)$order->billing['postcode'],
            'country'                         => (string)$order->billing['country']['iso_code_2'],
            'last_name'                       => (string)$order->billing['lastname'],
            'organization'                    => (string)$order->billing['company'],
        );
        if ($order->delivery !== false && isset($order->delivery['street_address'])) {
            $shipping_address = array(
                'address_line'                    => (string)$order->delivery['street_address'],
                'address_line_2'                  => (string)$order->delivery['suburb'],
                'locality'                        => (string)$order->delivery['city'],
                'administrative_district_level_1' => (string)zen_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']),
                'postal_code'                     => (string)$order->delivery['postcode'],
                'country'                         => (string)$order->delivery['country']['iso_code_2'],
                'last_name'                       => (string)$order->delivery['lastname'],
                'organization'                    => (string)$order->delivery['company'],
            );
        }

        $request_body = array(
            'idempotency_key'     => uniqid(),
            'card_nonce'          => (string)$_POST[$this->code . '_nonce'],
            'amount_money'        => array(
                'amount'   => $this->convert_to_cents($payment_amount, $currency_code),
                'currency' => (string)$currency_code,
            ),
            'delay_capture'       => (bool)(MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE === 'authorize'),
            'reference_id'        => (string)(substr(zen_session_id(), 0, 40)), // 40 char max
            'note'                => (string)substr(htmlentities(trim($this->currency_comment . ' ' . STORE_NAME)), 0, 60), // 60 char max
            'customer_id'         => (string)$_SESSION['customer_id'],
            'buyer_email_address' => $order->customer['email_address'],
            'billing_address'     => $billing_address,
        );
        if (!empty($shipping_address)) {
            $request_body['shipping_address'] = $shipping_address;
        }

        $api_instance = new \SquareConnect\Api\TransactionsApi();
        $body         = new \SquareConnect\Model\ZenCartChargeRequest($request_body);

        try {
            $result        = $api_instance->charge($location->id, $body);
            $errors_object = $result->getErrors();
            $transaction   = $result->getTransaction();
            $this->logTransactionData($transaction, $request_body, (string)$errors_object);
        } catch (\SquareConnect\ApiException $e) {
            $errors_object = $e->getResponseBody()->errors;
            $error         = $this->parse_error_response($errors_object);
            $this->logTransactionData(array($e->getCode() => $e->getMessage()), $request_body, print_r($e->getResponseBody(), true));

            // location configuration error
            if ($error['category'] === 'INVALID_REQUEST_ERROR') {
                trigger_error("Square Connect [configuration] error. \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
                $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_TEXT_MISCONFIGURATION, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            }
            else

            // only display payment-related errors to customers
            if ($error['category'] != 'PAYMENT_METHOD_ERROR') {
                trigger_error("Square Connect error. \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
                $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            }
        }

        // analyze for errors
        if (count($errors_object)) {
            $error = $this->parse_error_response($errors_object);
            $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_TEXT_ERROR . ' [' . $error['detail'] . ']', 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        // success
        if ($transaction->getId()) {
            $tenders                = $transaction->getTenders();
            $this->auth_code        = $tenders[0]['id']; // since Square doesn't supply an auth code, we use the tender-id instead, since it is required for submitting refunds
            $this->transaction_id   = $transaction->getId();
            $this->transaction_date = $transaction->getCreatedAt();

            return true;
        }

        // if we get here, send a generic 'declined' message response
        $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_ERROR_DECLINED, 'error');
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    /**
     * Update the order-status history data with the transaction id and tender id from the transaction.
     *
     * @return boolean
     */
    public function after_process()
    {
        global $insert_id, $db, $order, $currencies;
        $sql = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, customer_notified, date_added) values (:orderComments, :orderID, :orderStatus, -1, now() )";
        $sql = $db->bindVars($sql, ':orderComments', 'Credit Card payment.  TransID: ' . $this->transaction_id . "\nTender ID: " . $this->auth_code . "\n" . $this->transaction_date . $this->currency_comment, 'string');
        $sql = $db->bindVars($sql, ':orderID', $insert_id, 'integer');
        $sql = $db->bindVars($sql, ':orderStatus', $this->order_status, 'integer');
        $db->Execute($sql);

        $sql_data_array = array(
            'order_id'       => $insert_id,
            'location_id'    => $this->getLocationDetails()->id,
            'transaction_id' => $this->transaction_id,
            'tender_id'      => $this->auth_code,
            'created_at'     => 'now()',
        );
        zen_db_perform(TABLE_SQUARE_PAYMENTS, $sql_data_array);

        return true;
    }

    /**
     * fetch original transaction details, live
     *
     * @param $order_id
     * @return \SquareConnect\Model\Transaction
     */
    protected function lookupTransactionForOrder($order_id)
    {
        global $db;
        $sql    = "SELECT order_id, location_id, transaction_id, tender_id from " . TABLE_SQUARE_PAYMENTS . " WHERE order_id = " . (int)$order_id . " order by id LIMIT 1";
        $result = $db->Execute($sql);
        if ($result->EOF) {
            $transaction = new \SquareConnect\Model\Transaction;
        } else {
            $this->getAccessToken();
            $location_id = $result->fields['location_id'];
            if (empty($location_id)) $location_id = $this->getLocationDetails()->id;
            $api_instance = new \SquareConnect\Api\TransactionsApi();
            try {
                $result        = $api_instance->retrieveTransaction($location_id, $result->fields['transaction_id']);
                $errors_object = $result->getErrors();
                $transaction   = $result->getTransaction();
            } catch (\SquareConnect\ApiException $e) {
                $errors_object = $e->getResponseBody()->errors;
                $transaction   = new \SquareConnect\Model\Transaction;
            }
        }

        return $transaction;
    }

    public function transactionDetails($order_id)
    {
        global $currencies;
        $transaction              = $this->lookupTransactionForOrder($order_id);
        $payments                 = $transaction->getTenders();
        $payment_created_at       = null;
        $this->transaction_status = '';
        foreach ($payments as $payment) {
            $this->transaction_status = $payment->getCardDetails()->getStatus();
            if (!$payment_created_at) $payment_created_at = $payment->getCreatedAt();
            $currency_code = $payment->getAmountMoney()->getCurrency();
            $amount        = $currencies->format($this->convert_from_cents($payment->getAmountMoney()->getAmount(), $currency_code), false, $currency_code);
            $date          = $payment->getCreatedAt();
            $id            = $payment->getId();
        }
        $refunds = $transaction->getRefunds();
        if (count($refunds)) {
            foreach ($refunds as $refund) {
                $currency_code = $refund->getAmountMoney()->getCurrency();
                $amount        = $currencies->format($this->convert_from_cents($refund->getAmountMoney()->getAmount(), $currency_code), false, $currency_code);
                $date          = $refund->getCreatedAt();
                $id            = $refund->getId();
                $status        = $refund->getStatus();
            }
        }
    }

    /**
     * Prepare admin-page components
     *
     * @param int $order_id
     * @return string
     */
    public function admin_notification($order_id)
    {
        global $currencies;
        $transaction = $this->lookupTransactionForOrder($order_id);
        if (empty($transaction) || !$transaction->getId()) return '';
        $output = '';
        require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/square_support/square_admin_notification.php');

        return $output;
    }


// SIMPLIFIED OAUTH TOKENIZATION
    protected function getAccessToken()
    {
        $this->token_refresh_check();
        $access_token = (string)(MODULE_PAYMENT_SQUARE_TESTING_MODE == 'Live' ? MODULE_PAYMENT_SQUARE_ACCESS_TOKEN : MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN);

        // set token into Square Config for subsequent API calls
        SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($access_token); //->setDebug(true)->setDebugFile(DIR_FS_LOGS . '/squareDebug.txt');

        return $access_token;
    }

    protected function isTokenExpired($difference = '')
    {
        if (MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT == '') return true;
        $expiry = new DateTime(MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT);  // formatted as '2016-08-10T19:42:08Z'

        // to be useful, we have to allow time for a customer to checkout. Opting generously for 1 hour here.
        if ($difference == '') $difference = '+1 hour';
        $now = new DateTime($difference);

        return $expiry < $now;
    }

    // called by module and by cron job
    public function token_refresh_check()
    {
        if (MODULE_PAYMENT_SQUARE_APPLICATION_ID == '') return 'not configured';

        $token = MODULE_PAYMENT_SQUARE_ACCESS_TOKEN;

        // if we have no token, alert that we need to get one
        if (trim($token) == '') {
            if (IS_ADMIN_FLAG === true) {
                global $messageStack;
                $messageStack->add_session(sprintf(MODULE_PAYMENT_SQUARE_TEXT_NEED_ACCESS_TOKEN, $this->getAuthorizeURL()), 'error');
            }
            $this->disableDueToInvalidAccessToken();
            return 'failure';
        }

        // refreshes can't be done if the token has expired longer than 15 days.
        if ($this->isTokenExpired('-15 days')) {
            $this->disableDueToInvalidAccessToken();
            return 'failure';
        }

        // ideal refresh threshold is 3 weeks out
        $refresh_threshold = new DateTime('+3 weeks');

        // if expiry is less than (threshold) away, refresh  (ie: refresh weekly)
        $expiry = new DateTime(MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT);
        if ($expiry < $refresh_threshold) {
            $result = $this->getRefreshToken();
            if ($result) {
                return 'refreshed';
            }
            return 'not refreshed';
        }

        return 'not expired';
    }

    protected function disableDueToInvalidAccessToken()
    {
        if (MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT == '' || MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '') return;
        global $db;
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'False' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_STATUS'");
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_ACCESS_TOKEN'");
        $msg = "This is an alert from your Zen Cart store.\n\nYour Square Payment Module access-token has expired, or cannot be refreshed automatically. Please login to your store Admin, go to the Payment Module settings, click on the Square module, and click the button to Re/Authorize your account.\n\nSquare Payments are disabled until a new valid token can be established.";
        $msg .= "\n\n" . ' The token expired on ' . MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT;
        zen_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER_EMAIL_ADDRESS, 'Square Payment Module Problem: Critical', $msg, STORE_NAME, EMAIL_FROM, array('EMAIL_MESSAGE_HTML' => $msg), 'payment_module_error');
        if (IS_ADMIN_FLAG !== true) trigger_error('Square Payment Module token expired' . (MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT != '' ? ' on ' . MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT : '') . '. Payment module has been disabled. Please login to Admin and re-authorize the module.',
            E_USER_ERROR);
    }

    protected function getRefreshToken()
    {
        $url  = 'https://connect.squareup.com/oauth2/clients/' . MODULE_PAYMENT_SQUARE_APPLICATION_ID . '/access-token/renew';
        $body = '{"access_token": "' . MODULE_PAYMENT_SQUARE_ACCESS_TOKEN . '"}';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_TIMEOUT, 9);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Client ' . MODULE_PAYMENT_SQUARE_APPLICATION_SECRET,
            'Square-Version: ' . $this->SquareApiVersion,
        ));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart token refresh [' . preg_replace('#https?://#', '', HTTP_SERVER) . '] ');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);

        if ($error == 0) {
            return $this->setAccessToken($response);
        }

        error_log('Could not refresh Square token. Response: ' . "\n" . print_r($response, true) . "\n" . $errno . ' ' . $error . ' HTTP: ' . $httpcode);
        return false;
    }

    protected function setAccessToken($json_payload)
    {
        global $db;
        $payload = json_decode($json_payload, true);
        if (!isset($payload['access_token']) || $payload['access_token'] == '') return false;
        $token   = preg_replace('[^0-9A-Za-z\-]', '', $payload['access_token']);
        $expires = preg_replace('[^0-9A-Za-z\-:]', '', $payload['expires_at']);
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $token . "' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_ACCESS_TOKEN'");
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $expires . "' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT'");
        return true;
    }


    public function getAuthorizeURL()
    {
        $url    = 'https://connect.squareup.com/oauth2/authorize?';
        $params = http_build_query(
            array(
                'client_id' => MODULE_PAYMENT_SQUARE_APPLICATION_ID,
                'scope'     => 'MERCHANT_PROFILE_READ PAYMENTS_WRITE PAYMENTS_READ ORDERS_WRITE ORDERS_READ CUSTOMERS_WRITE CUSTOMERS_READ ITEMS_WRITE ITEMS_READ',
                'state'     => uniqid(),
                'session'   => 'false',
            )
        );

        return $url . $params;
        // example: code=sq0abc-D1efG2HIJK345lmno6PqR78S9Tuv0WxY&response_type=code
    }

    public function exchangeForToken($token_redeem_code)
    {
        $url  = 'https://connect.squareup.com/oauth2/token';
        $body = json_encode(
            array(
                'client_id'     => MODULE_PAYMENT_SQUARE_APPLICATION_ID,
                'client_secret' => MODULE_PAYMENT_SQUARE_APPLICATION_SECRET,
                'code'          => $token_redeem_code,
            )
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_TIMEOUT, 9);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Square-Version: ' . $this->SquareApiVersion,
        ));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart token request [' . preg_replace('#https?://#', '', HTTP_SERVER) . '] ');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);
//error_log('SQUARE TOKEN EXCHANGE response: ' . "\n" . print_r($response, true) . "\n" . $errno . ' ' . $error . ' HTTP: ' . $httpcode);

        if ($error == 0) {
            $this->setAccessToken($response);
            echo 'Token set. You may now continue configuring the module. <script type="text/javascript">window.close()</script>';

            return true;
        }
        trigger_error('Could not exchange Square code for a token. HTTP ' . $httpcode . '. Error ' . $errno . ': ' . $error, E_USER_ERROR);
    }

    protected function getLocationDetails()
    {
        $location = new stdClass;

        $data = trim((string)MODULE_PAYMENT_SQUARE_LOCATION);

        // this splits it out from stored format of: LocationName:[LocationID]:CurrencyCode
        preg_match('/(.+(?<!:\[)):\[(.+(?<!]:))]:([A-Z]{3})?/', $data, $matches);

        $location->name     = $matches[1];
        $location->id       = $matches[2];
        $location->currency = $matches[3];

        if (empty($data)) {
            $locations = $this->getLocationsList();
            if ($locations == null) return '';
            $first_location     = $locations[0];
            $location->id       = $first_location->getId();
            $location->name     = $first_location->getName();
            if (method_exists($first_location, 'getCurrencyCode')) {
                $location->currency = $first_location->getCurrencyCode();
            } else {
                $location->currency = DEFAULT_CURRENCY;
            }
        }

        return $location;
    }

    protected function getLocationsList()
    {
        if (MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '') return null;
        $this->getAccessToken();
        $api_instance = new SquareConnect\Api\LocationsApi();
        try {
            $result    = $api_instance->listLocations();
            $locations = $result->getLocations();


            // Square hasn't yet put currency_code into their v2 API, so we have to look it up using the old v1 API and match things up
            $first_location = $locations[0];
            if (!method_exists($first_location, 'getCurrencyCode') && MODULE_PAYMENT_SQUARE_TESTING_MODE == 'Live') {
                $api_instance = new SquareConnect\Api\V1LocationsApi;
                $locations    = $api_instance->listLocations();
            }

            return $locations;

        } catch (Exception $e) {
            trigger_error('Exception when calling LocationsApi->listLocations: ' . $e->getMessage(), E_USER_NOTICE);

            return array();
        }
    }

    public function getLocationsPulldownArray()
    {
        $locations = $this->getLocationsList();
        if (empty($locations)) return array();
        $locations_pulldown = array();
        foreach ($locations as $key => $value) {
            // This causes us to store this as: LocationName:[LocationID]:CurrencyCode
            $locations_pulldown[] = array('id' => $value->getName() . ':[' . $value->getId() . ']:' . (method_exists($value, 'getCurrencyCode') ? $value->getCurrencyCode() : 'USD'), 'text' => $value->getName());
        }

        return $locations_pulldown;
    }

    /**
     * format purchase amount
     * Monetary amounts are specified in the smallest unit of the applicable currency. ie: for USD the amount is in cents.
     */
    protected function convert_to_cents($amount, $currency_code = null)
    {
        global $currencies, $order;
        if (empty($currency_code)) $currency_code = (isset($order) && isset($order->info['currency'])) ? $order->info['currency'] : $this->gateway_currency;
        $decimal_places = $currencies->get_decimal_places($currency_code);

        // if this currency is "already" in cents, just use the amount directly
        if ((int)$decimal_places === 0) return (int)$amount;

// For compatibility with older than PHP 5.6, we must comment out the following several lines, and use only the pow() call instead of the ** exponentiation operator
        // if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        // old PHP way
        return (int)(string)(round($amount, $decimal_places) * pow(10, $decimal_places));
        // }
        // modern way
        // return (int)(string)(round($amount, $decimal_places) * 10 ** $decimal_places);
    }

    protected function convert_from_cents($amount, $currency_code = null)
    {
        global $currencies, $order;
        if (empty($currency_code)) $currency_code = (isset($order) && isset($order->info['currency'])) ? $order->info['currency'] : $this->gateway_currency;
        $decimal_places = $currencies->get_decimal_places($currency_code);

        // if this currency is "already" in cents, just use the amount directly
        if ((int)$decimal_places === 0) return (int)$amount;

// For compatibility with older than PHP 5.6, we must comment out the following several lines, and use only the pow() call instead of the ** exponentiation operator
        // if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        // old PHP way
        return ((int)$amount / pow(10, $decimal_places));
        // }

        // modern way
        // return ((int)$amount / 10 ** $decimal_places);
    }

    public function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query  = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SQUARE_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        if ($this->_check > 0) $this->install(); // install any missing keys

        return $this->_check;
    }

    /** Install required configuration keys */
    public function install()
    {
        global $db;

        if (!defined('MODULE_PAYMENT_SQUARE_STATUS')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Square Module', 'MODULE_PAYMENT_SQUARE_STATUS', 'True', 'Do you want to accept Square payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_APPLICATION_ID')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('Application ID', 'MODULE_PAYMENT_SQUARE_APPLICATION_ID', 'sq0idp-', 'Enter the Application ID from your App settings', '6', '0',  now(), 'zen_cfg_password_display')");
        if (!defined('MODULE_PAYMENT_SQUARE_APPLICATION_SECRET')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('Application Secret (OAuth)', 'MODULE_PAYMENT_SQUARE_APPLICATION_SECRET', 'sq0csp-', 'Enter the Application Secret from your App OAuth settings', '6', '0',  now(), 'zen_cfg_password_display')");
        if (!defined('MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Type', 'MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE', 'purchase', 'Should payments be [authorized] only, or be completed [purchases]?<br>NOTE: If you use [authorize] then you must manually capture each payment within 6 days or it will be voided automatically.', '6', '0', 'zen_cfg_select_option(array(\'authorize\', \'purchase\'), ', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_LOCATION')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) values ('<hr>Location ID', 'MODULE_PAYMENT_SQUARE_LOCATION', '', 'Enter the (Store) Location ID from your account settings. You can have multiple locations configured in your account; this setting lets you specify which location your sales should be attributed to. If you want to enable Apple Pay support, this location must already be verified for Apple Pay in your Square account.', '6', '0',  now(), 'zen_cfg_pull_down_square_locations(')");
        if (!defined('MODULE_PAYMENT_SQUARE_SORT_ORDER')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr>Sort order of display.', 'MODULE_PAYMENT_SQUARE_SORT_ORDER', '0', 'Sort order of displaying payment options to the customer. Lowest is displayed first.', '6', '0', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_ZONE')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SQUARE_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID', '2', 'Set the status of Paid orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_REFUNDED_ORDER_STATUS_ID')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Refunded Order Status', 'MODULE_PAYMENT_SQUARE_REFUNDED_ORDER_STATUS_ID', '1', 'Set the status of refunded orders to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_LOGGING')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Log Mode', 'MODULE_PAYMENT_SQUARE_LOGGING', 'Log on Failures and Email on Failures', 'Would you like to enable debug mode?  A complete detailed log of failed transactions may be emailed to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Log Always\', \'Log on Failures\', \'Log Always and Email on Failures\', \'Log on Failures and Email on Failures\', \'Email Always\', \'Email on Failures\'), ', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_ACCESS_TOKEN')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('Live Merchant Token', 'MODULE_PAYMENT_SQUARE_ACCESS_TOKEN', '', 'Enter the Access Token for Live transactions from your account settings', '6', '0',  now(), 'zen_cfg_password_display')");
        if (!defined('MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('Square Refresh Token (read only)', 'MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT', '', 'DO NOT EDIT', '6', '0',  now(), '')");
        // DEVELOPER USE ONLY
        if (!defined('MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) values ('Sandbox Merchant Token', 'MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN', 'sq0atb-nn_yQbQgZaA3VhFEykuYlQ', 'Enter the Sandbox Access Token from your account settings', '6', '0',  now(), 'zen_cfg_password_display')");
        if (!defined('MODULE_PAYMENT_SQUARE_TESTING_MODE')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Sandbox/Live Mode', 'MODULE_PAYMENT_SQUARE_TESTING_MODE', 'Live', 'Use [Live] for real transactions<br>Use [Sandbox] for developer testing', '6', '0', 'zen_cfg_select_option(array(\'Live\', \'Sandbox\'), ', now())");

        $this->tableCheckup();
    }

    public function remove()
    {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_PAYMENT\_SQUARE\_%'");
    }

    public function keys()
    {
        $keys = array(
            'MODULE_PAYMENT_SQUARE_STATUS',
            'MODULE_PAYMENT_SQUARE_APPLICATION_ID',
            'MODULE_PAYMENT_SQUARE_APPLICATION_SECRET',
            'MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE',
            'MODULE_PAYMENT_SQUARE_LOCATION',
            'MODULE_PAYMENT_SQUARE_SORT_ORDER',
            'MODULE_PAYMENT_SQUARE_ZONE',
            'MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID',
            'MODULE_PAYMENT_SQUARE_REFUNDED_ORDER_STATUS_ID',
            'MODULE_PAYMENT_SQUARE_LOGGING',
        );

        if (isset($_GET['sandbox'])) {
            // Developer use only
            $keys = array_merge($keys, array(
                'MODULE_PAYMENT_SQUARE_ACCESS_TOKEN',
                'MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT',
                'MODULE_PAYMENT_SQUARE_TESTING_MODE',
                'MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN',
                )
            );
        }

        return $keys;
    }

    /**
     * Check and fix table structure if appropriate
     */
    protected function tableCheckup()
    {
        global $db, $sniffer;
        if (!$sniffer->table_exists(TABLE_SQUARE_PAYMENTS)) {
            $sql = "
            CREATE TABLE `" . TABLE_SQUARE_PAYMENTS . "` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `order_id` int(11) UNSIGNED NOT NULL,
              `location_id` varchar(40) NOT NULL,
              `transaction_id` varchar(255) NOT NULL,
              `tender_id` varchar(64),
              `action` varchar(40),
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            )";
            $db->Execute($sql);
        }
        $fieldOkay1 = (method_exists($sniffer, 'field_type')) ? $sniffer->field_type(TABLE_SQUARE_PAYMENTS, 'transaction_id', 'varchar(255)', false) : false;
        if ($fieldOkay1 !== true) {
            $db->Execute("ALTER TABLE " . TABLE_SQUARE_PAYMENTS . " MODIFY transaction_id varchar(255) NOT NULL");
            $db->Execute("ALTER TABLE " . TABLE_SQUARE_PAYMENTS . " MODIFY tender_id varchar(64)");
        }
    }

    /**
     * Log transaction errors if enabled
     *
     * @param array $response
     * @param array $payload
     * @param string $errors
     */
    private function logTransactionData($response, $payload, $errors = '')
    {
        global $db;
        $logMessage = date('M-d-Y h:i:s') .
            "\n=================================\n\n" .
            ($errors != '' ? 'Error Dump: ' . $errors . "\n\n" : '') .
            'Transaction ID assigned: ' . $response['id'] . "\n" .
            'Sent to Square: ' . print_r($payload, true) . "\n\n" .
            'Results Received back from Square: ' . print_r($response, true) . "\n\n";

        if (strstr(MODULE_PAYMENT_SQUARE_LOGGING, 'Log Always') || ($errors != '' && strstr(MODULE_PAYMENT_SQUARE_LOGGING, 'Log on Failures'))) {
            $key  = $response['id'] . '_' . time() . '_' . zen_create_random_value(4);
            $file = $this->_logDir . '/' . 'Square_' . $key . '.log';
            if ($fp = @fopen($file, 'a')) {
                fwrite($fp, $logMessage);
                fclose($fp);
            }
        }
        if (($errors != '' && stristr(MODULE_PAYMENT_SQUARE_LOGGING, 'Email on Failures')) || strstr(MODULE_PAYMENT_SQUARE_LOGGING, 'Email Always')) {
            zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Square Alert (customer transaction error) ' . date('M-d-Y h:i:s'), $logMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS,
                array('EMAIL_MESSAGE_HTML' => nl2br($logMessage)), 'debug');
        }
    }

    /**
     * Refund for a given transaction+tender
     */
    public function _doRefund($oID, $amount = null, $currency_code = null)
    {
        global $db, $messageStack, $currencies;

        $new_order_status = $this->getNewOrderStatus($oID, 'refund', (int)MODULE_PAYMENT_SQUARE_REFUNDED_ORDER_STATUS_ID);
        if ($new_order_status == 0) $new_order_status = 1;

        $proceedToRefund = true;

        if (!isset($_POST['refconfirm']) || $_POST['refconfirm'] != 'on') {
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_REFUND_CONFIRM_ERROR, 'error');
            $proceedToRefund = false;
        }
        if (isset($_POST['buttonrefund']) && $_POST['buttonrefund'] == MODULE_PAYMENT_SQUARE_ENTRY_REFUND_BUTTON_TEXT) {
            $amount = preg_replace('/[^0-9.,]/', '', $_POST['refamt']);
            if (empty($amount)) {
                $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_INVALID_REFUND_AMOUNT, 'error');
                $proceedToRefund = false;
            }
        }
        if (!$proceedToRefund) return false;

        $refundNote = strip_tags(zen_db_input($_POST['refnote']));

        $transaction    = $this->lookupTransactionForOrder($oID);
        $transaction_id = $transaction->getId();
        $payments       = $transaction->getTenders();
        $payment        = $payments[0];
        $tender_id      = $payment->getId();
        $currency_code  = $payment->getAmountMoney()->getCurrency();

        $refund_details = array(
            'amount_money'    => array(
                'amount'   => $this->convert_to_cents($amount, $currency_code),
                'currency' => $currency_code,
            ),
            'tender_id'       => $tender_id,
            'reason'          => substr(htmlentities(trim($refundNote)), 0, 60),
            'idempotency_key' => uniqid(),
        );
        $request_body   = new \SquareConnect\Model\CreateRefundRequest($refund_details);
        $this->logTransactionData(array('comment' => 'Creating refund request'), $refund_details);

        $this->getAccessToken();
        $location_id  = $this->getLocationDetails()->id;
        $api_instance = new SquareConnect\Api\TransactionsApi();
        try {
            $result        = $api_instance->createRefund($location_id, $transaction_id, $request_body);
            $errors_object = $result->getErrors();
            $transaction   = $result->getRefund();
            $this->logTransactionData($transaction, $refund_details, (string)$errors_object);
        } catch (\SquareConnect\ApiException $e) {
            $errors_object = $e->getResponseBody()->errors;
            $this->logTransactionData(array($e->getCode() => $e->getMessage()), $refund_details, print_r($e->getResponseBody(), true));
            trigger_error("Square Connect error (REFUNDING). \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR, 'error');
        }

        if (count($errors_object)) {
            $error = $this->parse_error_response($errors_object);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_UPDATE_FAILED . ' [' . $error['detail'] . ']', 'error');

            return false;
        }

        $currency_code = $transaction->getAmountMoney()->getCurrency();
        $amount        = $currencies->format($transaction->getAmountMoney()->getAmount() / (pow(10, $currencies->get_decimal_places($currency_code))), false, $currency_code);

        // Success, so save the results
        $sql_data_array = array(
            'orders_id'         => $oID,
            'orders_status_id'  => (int)$new_order_status,
            'date_added'        => 'now()',
            'comments'          => 'REFUNDED: ' . $amount . "\n" . $refundNote,
            'customer_notified' => 0,
        );
        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $db->Execute("update " . TABLE_ORDERS . "
                      set orders_status = " . (int)$new_order_status . "
                      where orders_id = " . (int)$oID);
        $messageStack->add_session(sprintf(MODULE_PAYMENT_SQUARE_TEXT_REFUND_INITIATED . $amount), 'success');

        return true;
    }

    /**
     * Capture a previously-authorized transaction.
     */
    public function _doCapt($oID, $type = 'Complete', $amount = null, $currency = null)
    {
        global $db, $messageStack;

        $new_order_status = $this->getNewOrderStatus($oID, 'capture', (int)MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID);
        if ($new_order_status == 0) $new_order_status = 1;

        $captureNote = strip_tags(zen_db_input($_POST['captnote']));

        $proceedToCapture = true;
        if (!isset($_POST['captconfirm']) || $_POST['captconfirm'] != 'on') {
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_CAPTURE_CONFIRM_ERROR, 'error');
            $proceedToCapture = false;
        }

        if (!$proceedToCapture) return false;

        $transaction    = $this->lookupTransactionForOrder($oID);
        $transaction_id = $transaction->getId();

        $this->getAccessToken();
        $location_id  = $this->getLocationDetails()->id;
        $api_instance = new SquareConnect\Api\TransactionsApi();
        try {
            $result        = $api_instance->captureTransaction($location_id, $transaction_id);
            $errors_object = $result->getErrors();
            $this->logTransactionData(array('capture request' => 'transaction ' . $transaction_id), array(), (string)$errors_object);
        } catch (\SquareConnect\ApiException $e) {
            $errors_object = $e->getResponseBody()->errors;
            $this->logTransactionData(array($e->getCode() => $e->getMessage()), array(), print_r($e->getResponseBody(), true));
            trigger_error("Square Connect error (CAPTURE attempt). \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR, 'error');
        }

        if (count($errors_object)) {
            $error = $this->parse_error_response($errors_object);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_UPDATE_FAILED . ' [' . $error['detail'] . ']', 'error');

            return false;
        }

        // Success, so save the results
        $sql_data_array = array(
            'orders_id'         => (int)$oID,
            'orders_status_id'  => (int)$new_order_status,
            'date_added'        => 'now()',
            'comments'          => 'FUNDS COLLECTED. Trans ID: ' . $transaction_id . "\n" . 'Time: ' . date('Y-m-D h:i:s') . "\n" . $captureNote,
            'customer_notified' => 0,
        );
        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $db->Execute("update " . TABLE_ORDERS . "
                      set orders_status = " . (int)$new_order_status . "
                      where orders_id = " . (int)$oID);
        $messageStack->add_session(sprintf(MODULE_PAYMENT_SQUARE_TEXT_CAPT_INITIATED, $transaction_id), 'success');

        return true;
    }

    /**
     * Void an not-yet-captured authorized transaction.
     */
    public function _doVoid($oID, $note = '')
    {
        global $db, $messageStack;

        $new_order_status = $this->getNewOrderStatus($oID, 'void', (int)MODULE_PAYMENT_SQUARE_REFUNDED_ORDER_STATUS_ID);
        if ($new_order_status == 0) $new_order_status = 1;

        $voidNote = strip_tags(zen_db_input($_POST['voidnote'] . $note));

        $proceedToVoid = true;
        if (isset($_POST['ordervoid']) && $_POST['ordervoid'] == MODULE_PAYMENT_SQUARE_ENTRY_VOID_BUTTON_TEXT) {
            if (!isset($_POST['voidconfirm']) || $_POST['voidconfirm'] != 'on') {
                $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_VOID_CONFIRM_ERROR, 'error');
                $proceedToVoid = false;
            }
        }
        if (!$proceedToVoid) return false;

        $transaction    = $this->lookupTransactionForOrder($oID);
        $transaction_id = $transaction->getId();

        $this->getAccessToken();
        $location_id  = $this->getLocationDetails()->id;
        $api_instance = new \SquareConnect\Api\TransactionsApi();
        try {
            $result        = $api_instance->voidTransaction($location_id, $transaction_id);
            $errors_object = $result->getErrors();
            $this->logTransactionData(array('void request' => 'transaction ' . $transaction_id), array(), (string)$errors_object);
        } catch (\SquareConnect\ApiException $e) {
            $errors_object = $e->getResponseBody()->errors;
            $this->logTransactionData(array($e->getCode() => $e->getMessage()), array(), print_r($e->getResponseBody(), true));
            trigger_error("Square Connect error (VOID attempt). \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR, 'error');
        }

        if (count($errors_object)) {
            $msg = $this->parse_error_response($errors_object);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_UPDATE_FAILED . ' [' . $msg['detail'] . ']', 'error');

            return false;
        }
        // Success, so save the results
        $sql_data_array = array(
            'orders_id'         => (int)$oID,
            'orders_status_id'  => (int)$new_order_status,
            'date_added'        => 'now()',
            'comments'          => 'VOIDED. Trans ID: ' . $transaction_id . "\n" . $voidNote,
            'customer_notified' => 0,
        );
        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $db->Execute("update " . TABLE_ORDERS . "
                      set orders_status = '" . (int)$new_order_status . "'
                      where orders_id = '" . (int)$oID . "'");
        $messageStack->add_session(sprintf(MODULE_PAYMENT_SQUARE_TEXT_VOID_INITIATED, $transaction_id), 'success');

        return true;
    }

    protected function getNewOrderStatus($order_id, $action, $default)
    {
        //global $order;
        //@TODO: fetch current order status and determine best status to set this to, based on $action

        return $default;
    }

    /**
     * @param $error_object
     * @return array
     */
    protected function parse_error_response($error_object)
    {
        $msg            = '';
        $first_category = null;
        $first_code     = null;
        foreach ($error_object as $err) {
            $category = method_exists($err, 'getCategory') ? $err->getCategory() : $err->category;
            $code     = method_exists($err, 'getCode') ? $err->getCode() : $err->code;
            $detail   = method_exists($err, 'getDetail') ? $err->getDetail() : $err->detail;
            $msg      .= "$code: $detail\n";
            if (is_null($first_category)) $first_category = $category;
            if (is_null($first_code)) $first_code = $code;
        }
        $msg = trim($msg, "\n");
        $msg = str_replace("\n", "\n<br>", $msg);

        $this->transaction_messages = $msg;

        return array('detail' => $msg, 'category' => $first_category, 'code' => $first_code);
    }

}

// helper for Square admin configuration: locations selector
function zen_cfg_pull_down_square_locations($location, $key = '')
{
    $name     = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $class    = new square;
    $pulldown = $class->getLocationsPulldownArray();

    return zen_draw_pull_down_menu($name, $pulldown, $location);
}

/////////////////////////////


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
