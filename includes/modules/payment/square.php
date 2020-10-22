<?php
/**
 * Square payments module
 * www.squareup.com
 *
 * Integrated using SquareConnect PHP SDK 3.20200528.1
 *
 * REQUIRES PHP 5.4 or newer
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 22 Modified in v1.5.7 $
 */

if (!defined('TABLE_SQUARE_PAYMENTS')) define('TABLE_SQUARE_PAYMENTS', DB_PREFIX . 'square_payments');

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
    public $moduleVersion = '1.5';
    /**
     * API version this module was last updated to use
     */
    protected $apiVersion = '3.20200528.1';
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
     * transaction vars hold the IDs of the completed payment
     */
    public $transaction_id, $transaction_messages, $auth_code;
    protected $currency_comment, $transaction_date;
    /**
     * Square configuration/connection
     * @var SquareConnect\Configuration
     */
    protected $_sqConfig;
    /**
     * Square API Client
     * @var \SquareConnect\ApiClient
     */
    protected $_apiConnection;


    /**
     * Constructor
     */
    public function __construct()
    {
        require DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/square/connect/autoload.php';
        require_once DIR_FS_CATALOG . 'includes/modules/payment/square_support/ZenCartConnectCreatePaymentRequest.php';

        global $order;
        $this->code = 'square';
        $this->enabled = (defined('MODULE_PAYMENT_SQUARE_STATUS') && MODULE_PAYMENT_SQUARE_STATUS == 'True');
        $this->sort_order = defined('MODULE_PAYMENT_SQUARE_SORT_ORDER') ? MODULE_PAYMENT_SQUARE_SORT_ORDER : null;
        $this->title = MODULE_PAYMENT_SQUARE_TEXT_CATALOG_TITLE; // Payment module title in Catalog
        $this->description = '<strong>Square Payments Module ' . $this->moduleVersion . '</strong>';
        $this->description .= '<br>[designed for API: ' . $this->apiVersion . ']';

        if (IS_ADMIN_FLAG === true) {
            $this->sdkApiVersion = (new \SquareConnect\Configuration())->getUserAgent();
            //$this->sdkApiVersion = (new \Square\SquareClient())->getSquareVersion();
            $this->description .= '<br>[using SDK: ' . $this->sdkApiVersion . ']';
        }

        $this->description .= '<br><br>' . MODULE_PAYMENT_SQUARE_TEXT_DESCRIPTION;

        if (IS_ADMIN_FLAG === true) {
            $this->title = MODULE_PAYMENT_SQUARE_TEXT_ADMIN_TITLE;
            if (defined('MODULE_PAYMENT_SQUARE_STATUS')) {
                if (MODULE_PAYMENT_SQUARE_APPLICATION_ID == '') $this->title .= '<span class="alert"> (not configured; API details needed)</span>';
                if (MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '') {
                    $this->title .= '<span class="alert"> (Access Token needed)</span>';
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
                if (MODULE_PAYMENT_SQUARE_TESTING_MODE === 'Sandbox') $this->title .= '<span class="alert"> (Sandbox mode)</span>';
                $new_version_details = plugin_version_check_for_updates(156, $this->moduleVersion);
                if ($new_version_details !== false) {
                    $this->title .= '<span class="alert">' . ' - NOTE: A NEW VERSION OF THIS PLUGIN IS AVAILABLE. <a href="' . $new_version_details['link'] . '" rel="noopener" target="_blank">[Details]</a>' . '</span>';
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

        // module can't work without a token; must be configured via OAUTH handshake
        if (!defined('MODULE_PAYMENT_SQUARE_ACCESS_TOKEN') || ((MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '' || MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT == '') && MODULE_PAYMENT_SQUARE_TESTING_MODE == 'Live')) {
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
        $sql = "SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . (int)MODULE_PAYMENT_SQUARE_ZONE . "' AND zone_country_id = '" . (int)$order->billing['country']['id'] . "' ORDER BY zone_id";
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

    public function javascript_validation()
    {
        return '';
    }

    public function selection()
    {
        // helper for auto-selecting the radio-button next to this module so the user doesn't have to make that choice
        $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

        $selection = array(
            'id' => $this->code,
            'module' => $this->title,
            'fields' => array(
                array(
                    'field' => '<div>' . MODULE_PAYMENT_SQUARE_TEXT_NOTICES_TO_CUSTOMER . '</div>',
                ),
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
                    'title' => '',
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

        $order->info['cc_type'] = zen_output_string_protected($_POST['cc_type']);
        $order->info['cc_number'] = zen_output_string_protected($_POST['cc_four']);
        if (!strpos($order->info['cc_number'], 'XX')) {
            $order->info['cc_number'] = 'XXXX' . zen_output_string_protected(substr($_POST['cc_four'], -4));
        }
        $order->info['cc_expires'] = zen_output_string_protected($_POST['cc_expires']);
        $order->info['cc_cvv'] = '***';

        // get Square Location (since we need the ID and the currency for preparing the transaction)
        $location = $this->getLocationDetails();

        $payment_amount = $order->info['total'];
        $currency_code = strtoupper($order->info['currency']);

        $this->currency_comment = '';

        // force conversion to Square Location's currency:
        if ($order->info['currency'] != $location->getCurrency() || $order->info['currency'] != DEFAULT_CURRENCY) {
            $payment_amount = $currencies->rateAdjusted($order->info['total'], true, $location->getCurrency());
            $currency_code = $location->getCurrency();
            if ($order->info['currency'] != $location->getCurrency()) {
                $this->currency_comment = '(Converted from: ' . round($order->info['total'] * $order->info['currency_value'], 2) . ' ' . $order->info['currency'] . ')';
            }
            // Note: Add tax/shipping conversion as well if rewriting for Orders API integration
        }

        $billing_address = array(
            'first_name' => (string)$order->billing['firstname'],
            'last_name' => (string)$order->billing['lastname'],
            'organization' => (string)$order->billing['company'],
            'address_line_1' => (string)$order->billing['street_address'],
            'address_line_2' => (string)$order->billing['suburb'],
            'locality' => (string)$order->billing['city'],
            'administrative_district_level_1' => (string)zen_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
            'postal_code' => (string)$order->billing['postcode'],
            'country' => (string)$order->billing['country']['iso_code_2'],
        );
        if ($order->delivery !== false && !empty($order->delivery['street_address']) && !empty($order->delivery['country']['iso_code_2'])) {
            $shipping_address = array(
                'first_name' => (string)$order->delivery['firstname'],
                'last_name' => (string)$order->delivery['lastname'],
                'organization' => (string)$order->delivery['company'],
                'address_line_1' => (string)$order->delivery['street_address'],
                'address_line_2' => (string)$order->delivery['suburb'],
                'locality' => (string)$order->delivery['city'],
                'administrative_district_level_1' => (string)zen_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']),
                'postal_code' => (string)$order->delivery['postcode'],
                'country' => (string)$order->delivery['country']['iso_code_2'],
            );
        }

        $payment_request = new \SquareConnect\Model\ZenCartConnectCreatePaymentRequest();
        $money = new \SquareConnect\Model\Money();
        $money->setAmount($this->convert_to_cents($payment_amount, $currency_code))->setCurrency((string)$currency_code);
        $payment_request->setAmountMoney($money);
        $payment_request->setIdempotencyKey(uniqid());
        $payment_request->setSourceId((string)$_POST[$this->code . '_nonce']);
        $payment_request->setReferenceId((string)(substr(zen_session_id(), 0, 40)));
        $payment_request->setLocationId($location->getId());

        // brief additional information transmitted as a "note", to max of 500 characters:
        $extraNotes = defined('MODULES_PAYMENT_SQUARE_TEXT_ITEMS_ORDERED') ? MODULES_PAYMENT_SQUARE_TEXT_ITEMS_ORDERED : 'Ordered:';
        if (count($order->products) < 100) {
            for ($i = 0, $j = count($order->products); $i < $j; $i++) {
                if ($i > 0 && $i < $j) $extraNotes .= ', ';
                $extraNotes .= '(' . $order->products[$i]['qty'] . ') ' . $order->products[$i]['name'];
            }
        }
        if ($order->delivery !== false && !empty($order->delivery['street_address']) && !empty($order->delivery['country']['iso_code_2'])) {
            $extraNotes .= '; ';
            $extraNotes .= defined('MODULES_PAYMENT_SQUARE_TEXT_DELIVERY_ADDRESS') ? MODULES_PAYMENT_SQUARE_TEXT_DELIVERY_ADDRESS : 'Deliver To: ';
            $extraNotes .= $order->delivery['street_address'] . ', ' . $order->delivery['city'] . ', ' . $order->delivery['state'] . '  ' . $order->delivery['postcode'] . '  tel:' . $order->customer['telephone'];
        }
        // Use Notes to identify customer and store name
        $note = htmlentities(trim($order->billing['firstname'] . ' ' . $order->billing['lastname'] . '; ' . $extraNotes . ' ' . $this->currency_comment . ' ' . STORE_NAME));
        $payment_request->setNote((string)substr($note, 0, 500));

        $payment_request->setBuyerEmailAddress(substr($order->customer['email_address'], 0, 255));
        $payment_request->setBillingAddress(new \SquareConnect\Model\Address($billing_address));

        if (!empty($shipping_address)) {
            $payment_request->setShippingAddress(new \SquareConnect\Model\Address($shipping_address));
        }

        if (MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE === 'authorize') {
            $payment_request->setAutocomplete(false);
        }

        // Skipping these since they would have to be generated after the Order is actually saved by Zen Cart, which is not part of the current workflow.
        // $payment_request->setReceiptNumber();
        // $payment_request->setReceiptUrl();

        // beta
        // $payment_request->setStatementDescriptionIdentifier(substr(STORE_NAME, 0, 20));

        try {
            $this->getAccessToken();
            $api_instance = new \SquareConnect\Api\PaymentsApi($this->_apiConnection);
            $result = $api_instance->createPayment($payment_request);
            $errors_object = $result->getErrors();
            $payment = $result->getPayment();
            $this->logTransactionData($payment, $payment_request, (string)$errors_object);
        } catch (\SquareConnect\ApiException $e) {
            $errors_object = $e->getResponseBody()->errors;
            $error = $this->parse_error_response($errors_object);
            $this->logTransactionData(array($e->getCode() => $e->getMessage()), $payment_request, print_r($e->getResponseBody(), true));

            // location configuration error
            if ($error['category'] === 'INVALID_REQUEST_ERROR' && strpos($error['detail'], 'retrieve nonce')) {
                trigger_error("Square Connect [nonce] error. \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
                $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_TEXT_ERROR . ' Payment token expired or has already been submitted. Please check transaction history.', 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            } else

              if ($error['category'] === 'INVALID_REQUEST_ERROR') {
                trigger_error("Square Connect [configuration] error. \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
                $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_TEXT_MISCONFIGURATION, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            } else

                // only display payment-related errors to customers
                if ($error['category'] !== 'PAYMENT_METHOD_ERROR') {
                    trigger_error("Square Connect error. \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
                    $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR, 'error');
                    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
                }
        }

        // analyze for errors
        if (!empty($errors_object)) {
            $error = $this->parse_error_response($errors_object);
            $messageStack->add_session('checkout_payment', MODULE_PAYMENT_SQUARE_TEXT_ERROR . ' [' . $error['detail'] . ']', 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        // success
        if ($payment->getId() && in_array($payment->getStatus(), array('COMPLETED', 'APPROVED'))) {
            $this->transaction_date = $payment->getCreatedAt();
            $this->auth_code = $payment->getOrderId(); // the order_id assigned by Square, used for lookups later
            $this->transaction_id = $payment->getId(); // The payment_id is used for refund requests

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
        global $insert_id, $order, $currencies;

        $comments = 'Credit Card payment.  TransID: ' . $this->transaction_id . "\n" . $this->transaction_date . $this->currency_comment . "\nOID: " . $this->auth_code;
        zen_update_orders_history($insert_id, $comments, null, $this->order_status, -1);

        $sql_data_array = array(
            'order_id' => $insert_id,
            'location_id' => $this->getLocationDetails()->getId(),
            'payment_id' => $this->transaction_id,
            'sq_order' => $this->auth_code,
            'created_at' => 'now()',
        );
        zen_db_perform(TABLE_SQUARE_PAYMENTS, $sql_data_array);

        return true;
    }

    /**
     * Prepare admin-page components
     *
     * @param int $order_id
     * @return string
     */
    public function admin_notification($order_id)
    {
        $records = $this->lookupOrderDetails($order_id);
        if (empty($records)) return '';
        $transaction = $records[0];
        if (empty($transaction) || !$transaction->getId()) return '';
        $output = '';
        require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/square_support/square_admin_notification.php');

        return $output;
    }

////////////////////////////////////

    /**
     * Create Square Configuration for making all connections
     */
    protected function setSquareConfig()
    {
        if (empty($this->_sqConfig)) {
            $this->_sqConfig = \SquareConnect\Configuration::getDefaultConfiguration();

            //$this->_sqConfig->setDebug(true)->setDebugFile(DIR_FS_LOGS . '/squareDebug.txt');

            if (MODULE_PAYMENT_SQUARE_TESTING_MODE === 'Sandbox') {
                $this->_sqConfig->setHost('https://connect.squareupsandbox.com');
            }

            \SquareConnect\Configuration::setDefaultConfiguration($this->_sqConfig);
        }
    }

    /**
     * Create Square API Client connection for all communications
     */
    protected function setApiClient()
    {
        if (empty($this->_sqConfig)) {
            $this->setSquareConfig();
        }
        if (empty($this->_apiConnection)) {
            $this->_apiConnection = new \SquareConnect\ApiClient($this->_sqConfig);
        }
    }

    /**
     * If access token is valid, set it for connections, else start renewal process
     * @return string
     */
    protected function getAccessToken()
    {
        $this->token_refresh_check();
        $access_token = (string)(MODULE_PAYMENT_SQUARE_TESTING_MODE === 'Live' ? MODULE_PAYMENT_SQUARE_ACCESS_TOKEN : MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN);

        $this->setApiClient();

        // set token into Square Config for subsequent API calls
        $this->_sqConfig->setAccessToken($access_token);

        return $access_token;
    }

    /**
     * Test for token expiration
     *
     * @param string $difference
     * @return bool
     * @throws Exception
     */
    protected function isTokenExpired($difference = '')
    {
        if (MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT == '') return true;
        $expiry = new DateTime(MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT);  // formatted as '2016-08-10T19:42:08Z'

        // to be useful, we have to allow time for a customer to checkout. Opting generously for 1 hour here.
        if ($difference == '') $difference = '+1 hour';
        $now = new DateTime($difference);

        return $expiry < $now;
    }

    /**
     * Check if token needs refresh (ie: recently expired, or nearly expired)
     * Called by payment module and by cron job
     *
     * @return string
     * @throws Exception
     */
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
        $expiry = new DateTime(MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT);
        if ($expiry < $refresh_threshold) {
            $result = $this->renewOAuthToken();
            if ($result) {
                return 'refreshed';
            }
            return 'not refreshed';
        }

        return 'not expired';
    }

    /**
     * Disable this payment module if access token is invalid or expired
     */
    protected function disableDueToInvalidAccessToken()
    {
        if (MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT == '' || MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '') return;
        global $db;
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'False' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_STATUS'");
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_ACCESS_TOKEN'");
        $msg = "This is an alert from your Zen Cart store.\n\nYour Square Payment Module access-token has expired, or cannot be refreshed automatically. Please login to your store Admin, go to the Payment Module settings, click on the Square module, and click the button to Re/Authorize your account.\n\nSquare Payments are disabled until a new valid token can be established.";
        $msg .= "\n\n" . ' The token expired on ' . MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT;
        zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Square Payment Module Problem: Critical', $msg, STORE_NAME, EMAIL_FROM, array('EMAIL_MESSAGE_HTML' => $msg), 'payment_module_error');
        if (IS_ADMIN_FLAG !== true) trigger_error('Square Payment Module token expired' . (MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT != ''
                ? ' on ' . MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT
                : '') . '. Payment module has been disabled. Please login to Admin and re-authorize the module.',
            E_USER_ERROR);
    }

    /**
     * Disconnect all auth to Square account (useful for troubleshooting, and linking to a different account)
     */
    protected function resetTokensAndDisconnectFromSquare($include_sandbox = false)
    {
        global $db;
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'False' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_STATUS'");
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '' WHERE configuration_key in ('MODULE_PAYMENT_SQUARE_ACCESS_TOKEN', 'MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT', 'MODULE_PAYMENT_SQUARE_REFRESH_TOKEN'");
        if ($include_sandbox) {
            $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '' WHERE configuration_key in ('MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN'");
            $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'Live' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_TESTING_MODE'");
        }
    }

    /**
     * Store access token to db once a valid replacement token has been received
     * @param \SquareConnect\Model\ObtainTokenResponse $response
     * @return bool
     */
    private function saveAccessToken(\SquareConnect\Model\ObtainTokenResponse $response)
    {
        global $db;
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $response->getAccessToken() . "' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_ACCESS_TOKEN'");
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $response->getExpiresAt() . "' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT'");
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $response->getRefreshToken() . "' WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_REFRESH_TOKEN'");
    }

    /**
     * Generate the oauth URL for making an authorize request for the account
     *
     * @return string
     */
    public function getAuthorizeURL()
    {
        $url = 'https://connect.squareup.com/oauth2/authorize?';

        if (MODULE_PAYMENT_SQUARE_TESTING_MODE === 'Sandbox') {
            $url = 'https://connect.squareupsandbox.com/oauth2/authorize?';
        }

        $params = http_build_query(
            array(
                'client_id' => MODULE_PAYMENT_SQUARE_APPLICATION_ID,
                'scope' => 'MERCHANT_PROFILE_READ PAYMENTS_WRITE PAYMENTS_READ ORDERS_WRITE ORDERS_READ CUSTOMERS_WRITE CUSTOMERS_READ ITEMS_WRITE ITEMS_READ',
                'state' => uniqid(),
            )
        );

        return $url . $params;
    }

    /**
     * Part of the Oauth handshake: exchanges auth code for auth token
     *
     * @param $token_redeem_code
     * @throws Exception
     */
    public function exchangeForToken($token_redeem_code)
    {
        $this->setApiClient();
        $oauthApi = new SquareConnect\Api\OAuthApi($this->_apiConnection);

        $body = new \SquareConnect\Model\ObtainTokenRequest(
            array(
                'client_id' => MODULE_PAYMENT_SQUARE_APPLICATION_ID,
                'client_secret' => MODULE_PAYMENT_SQUARE_APPLICATION_SECRET,
                'code' => $token_redeem_code,
            )
        );
        $body->setGrantType('authorization_code');

        try {
            $response = $oauthApi->obtainToken($body);
        } catch (Exception $e) {
            trigger_error('Exception when calling OAuthApi->obtainToken: ' . $e->getMessage());
            throw new Exception('Error Processing Request: Could not exchange auth token!', 1);
        }

        $this->saveAccessToken($response);
        echo 'Token set. You may now continue configuring the module. <script type="text/javascript">window.close()</script>';
    }

    /**
     * Renew OAuth access token using a refresh token.
     */
    protected function renewOAuthToken()
    {
        $refreshToken = MODULE_PAYMENT_SQUARE_REFRESH_TOKEN;

        if (empty($refreshToken)) {
            $this->resetTokensAndDisconnectFromSquare($sandbox = true);
            $GLOBALS['messageStack']->add('FATAL ERROR: No refresh token found. Please re-authorize your Square account via the Admin console.');
        }

        $this->setApiClient();
        $oauthApi = new SquareConnect\Api\OAuthApi($this->_apiConnection);

        $body = new \SquareConnect\Model\ObtainTokenRequest(
            array(
                'client_id' => MODULE_PAYMENT_SQUARE_APPLICATION_ID,
                'client_secret' => MODULE_PAYMENT_SQUARE_APPLICATION_SECRET,
            )
        );
        $body->setGrantType("refresh_token");
        $body->setRefreshToken($refreshToken);

        try {
            $response = $oauthApi->obtainToken($body);
        } catch (Exception $e) {
            trigger_error('Exception when calling OAuthApi->obtainToken: ' . $e->getMessage());
            throw new Exception('Error Processing Request: Token renewal failed!', 1);
        }

        $this->saveAccessToken($response);

        return $response->getAccessToken();
    }

    /**
     * Lookup and return location information, whether from configured setting, or by lookup from Square directly
     *
     * @return string | \SquareConnect\Model\Location
     */
    protected function getLocationDetails()
    {
        $location = new \SquareConnect\Model\Location;

        $data = trim((string)MODULE_PAYMENT_SQUARE_LOCATION);

        // this splits it out from stored format of: LocationName:[LocationID]:CurrencyCode
        preg_match('/(.+(?<!:\[)):\[(.+(?<!]:))]:([A-Z]{3})?/', $data, $matches);

        $location->setName($matches[1]);
        $location->setId($matches[2]);
        $location->setCurrency($matches[3]);

        if (empty($data)) {
            $locations = $this->getLocationsList();
            if (empty($locations)) return '';
            $location = $locations[0];
        }

        return $location;
    }

    protected function getLocationsList()
    {
        if (MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '') return null;

        $this->getAccessToken();
        $api_instance = new SquareConnect\Api\LocationsApi($this->_apiConnection);
        try {
            $result = $api_instance->listLocations();
            return $result->getLocations();

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
            // This causes this to be saved as: LocationName:[LocationID]:CurrencyCode
            $locations_pulldown[] = array(
                'id' => $value->getName() . ':[' . $value->getId() . ']:' . $value->getCurrency(),
                'text' => $value->getName(),
            );
        }

        return $locations_pulldown;
    }

    /**
     * Retrieve all payments for this year
     *
     * @param $order_id
     * @return \SquareConnect\Model\Payment | array | \SquareConnect\Model\Payment[]
     */
    protected function getAllPayments()
    {
        $this->getAccessToken();
        $location = $this->getLocationDetails();

        $request = new \SquareConnect\Model\ListPaymentsRequest(array('location_id' => $location->getId()));

        try {
            $api_instance = new \SquareConnect\Api\PaymentsApi($this->_apiConnection);
            $result = $api_instance->listPayments($request);
            return $result->getPayments();

        } catch (\SquareConnect\ApiException $e) {
            return new \SquareConnect\Model\Payment;
        }
    }

    /**
     * fetch live order details
     *
     * @param $order_id
     * @return \SquareConnect\Model\Order | array | \SquareConnect\Model\Order[]
     */
    protected function lookupOrderDetails($order_id)
    {
        global $db;
        $sql = "SELECT * from " . TABLE_SQUARE_PAYMENTS . " WHERE order_id = " . (int)$order_id . " ORDER BY id LIMIT 1";
        $order = $db->Execute($sql);

        if ($order->EOF) {
            return new \SquareConnect\Model\Order;
        }

        $this->getAccessToken();
        $location = $this->getLocationDetails();

        $ids = array();
        foreach(array('tender_id', 'transaction_id', 'payment_id', 'sq_order') as $key) {
            if (!empty($order->fields[$key])) {
                $ids[] = $order->fields[$key];
            }
        }
        $request = new \SquareConnect\Model\BatchRetrieveOrdersRequest(array('order_ids' => $ids));

        try {
            $api_instance = new \SquareConnect\Api\OrdersApi($this->_apiConnection);
            $result = $api_instance->batchRetrieveOrders($location->getId(), $request);
            return $result->getOrders();

        } catch (\SquareConnect\ApiException $e) {
            return new \SquareConnect\Model\Order;
        }
    }

    /**
     * fetch original payment details for an order
     *
     * @param $order_id
     * @return \SquareConnect\Model\Order[]
     */
    protected function lookupPaymentForOrder($order_id)
    {
        $records = $this->lookupOrderDetails($order_id);

        if (empty($records)) {
            return (new \SquareConnect\Model\BatchRetrieveOrdersResponse(['orders' => new \SquareConnect\Model\Order()]))->getOrders();
        }

        return $records[0];
    }

    /**
     * @TODO - this is unused, but is similar to the admin_notification lookup/display loop
     */
    public function transactionDetails($order_id)
    {
        global $currencies;

        $transaction = $this->lookupOrderDetails($order_id);
        $payments = $transaction->getTenders();
        $payment_created_at = null;
        $this->transaction_status = '';
        foreach ($payments as $payment) {
            $this->transaction_status = $payment->getCardDetails()->getStatus();
            if (!$payment_created_at) $payment_created_at = $payment->getCreatedAt();
            $currency_code = $payment->getAmountMoney()->getCurrency();
            $amount = $currencies->format($this->convert_from_cents($payment->getAmountMoney()->getAmount(), $currency_code), false, $currency_code);
            $date = $payment->getCreatedAt();
            $id = $payment->getId();
        }
        $refunds = $transaction->getRefunds();
        if (count($refunds)) {
            foreach ($refunds as $refund) {
                $currency_code = $refund->getAmountMoney()->getCurrency();
                $amount = $currencies->format($this->convert_from_cents($refund->getAmountMoney()->getAmount(), $currency_code), false, $currency_code);
                $date = $refund->getCreatedAt();
                $id = $refund->getId();
                $status = $refund->getStatus();
            }
        }
    }

    /**
     * format purchase amount
     * Monetary amounts are specified in the smallest unit of the applicable currency. ie: for USD the amount is in cents.
     */
    protected function convert_to_cents($amount, $currency_code = null)
    {
        global $currencies, $order;
        if (empty($currency_code)) $currency_code = (isset($order) && isset($order->info['currency']))
            ? $order->info['currency'] : $this->gateway_currency;
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
        if (empty($currency_code)) $currency_code = (isset($order) && isset($order->info['currency']))
            ? $order->info['currency'] : $this->gateway_currency;
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
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_SQUARE_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        if ($this->_check > 0) $this->install(); // install any missing keys

        return $this->_check;
    }

    /** Install required configuration keys */
    public function install()
    {
        global $db;

        if (!defined('MODULE_PAYMENT_SQUARE_STATUS')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Square Module', 'MODULE_PAYMENT_SQUARE_STATUS', 'True', 'Do you want to accept Square payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_APPLICATION_ID')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Application ID', 'MODULE_PAYMENT_SQUARE_APPLICATION_ID', 'sq0idp-', 'Enter the Application ID from your App settings', '6', '0',  now(), 'zen_cfg_password_display')");
        if (!defined('MODULE_PAYMENT_SQUARE_APPLICATION_SECRET')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Application Secret (OAuth)', 'MODULE_PAYMENT_SQUARE_APPLICATION_SECRET', 'sq0csp-', 'Enter the Application Secret from your App OAuth settings', '6', '0',  now(), 'zen_cfg_password_display')");
        if (!defined('MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Type', 'MODULE_PAYMENT_SQUARE_TRANSACTION_TYPE', 'purchase', 'Should payments be [authorized] only, or be completed [purchases]?<br>NOTE: If you use [authorize] then you must manually capture each payment within 6 days or it will be voided automatically.', '6', '0', 'zen_cfg_select_option(array(\'authorize\', \'purchase\'), ', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_LOCATION')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('<hr>Location ID', 'MODULE_PAYMENT_SQUARE_LOCATION', '', 'Enter the (Store) Location ID from your account settings. You can have multiple locations configured in your account; this setting lets you specify which location your sales should be attributed to. If you want to enable Apple Pay support, this location must already be verified for Apple Pay in your Square account.', '6', '0',  now(), 'zen_cfg_pull_down_square_locations(')");
        if (!defined('MODULE_PAYMENT_SQUARE_SORT_ORDER')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('<hr>Sort order of display.', 'MODULE_PAYMENT_SQUARE_SORT_ORDER', '0', 'Sort order of displaying payment options to the customer. Lowest is displayed first.', '6', '0', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_ZONE')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_SQUARE_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID', '2', 'Set the status of Paid orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_REFUNDED_ORDER_STATUS_ID')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refunded Order Status', 'MODULE_PAYMENT_SQUARE_REFUNDED_ORDER_STATUS_ID', '1', 'Set the status of refunded orders to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_LOGGING')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Log Mode', 'MODULE_PAYMENT_SQUARE_LOGGING', 'Log on Failures and Email on Failures', 'Would you like to enable debug mode?  A complete detailed log of failed transactions may be emailed to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Log Always\', \'Log on Failures\', \'Log Always and Email on Failures\', \'Log on Failures and Email on Failures\', \'Email Always\', \'Email on Failures\'), ', now())");
        if (!defined('MODULE_PAYMENT_SQUARE_ACCESS_TOKEN')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Live Merchant Token', 'MODULE_PAYMENT_SQUARE_ACCESS_TOKEN', '', 'Enter the Access Token for Live transactions from your account settings', '6', '0',  now(), 'zen_cfg_password_display')");
        if (!defined('MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Square Token TTL (read only)', 'MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT', '', 'DO NOT EDIT', '6', '0',  now(), '')");
        if (!defined('MODULE_PAYMENT_SQUARE_REFRESH_TOKEN')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Square Refresh Token (read only)', 'MODULE_PAYMENT_SQUARE_REFRESH_TOKEN', '', 'DO NOT EDIT', '6', '0',  now(), '')");
        // DEVELOPER USE ONLY
        if (!defined('MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Sandbox Merchant Token', 'MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN', 'sandbox-sq0atb-abcdefghijklmnop', 'Enter the Sandbox Access Token from your account settings', '6', '0',  now(), 'zen_cfg_password_display')");
        if (!defined('MODULE_PAYMENT_SQUARE_TESTING_MODE')) $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Sandbox/Live Mode', 'MODULE_PAYMENT_SQUARE_TESTING_MODE', 'Live', 'Use [Live] for real transactions<br>Use [Sandbox] for developer testing', '6', '0', 'zen_cfg_select_option(array(\'Live\', \'Sandbox\'), ', now())");

        $db->Execute('DELETE FROM ' . TABLE_CONFIGURATION . " WHERE configuration_key='MODULE_PAYMENT_SQUARE_REFRESH_EXPIRES_AT'");

        $this->tableCheckup();
    }

    public function remove()
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_SQUARE\_%'");
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
                    'MODULE_PAYMENT_SQUARE_TOKEN_EXPIRES_AT',
                    'MODULE_PAYMENT_SQUARE_REFRESH_TOKEN',
                    'MODULE_PAYMENT_SQUARE_TESTING_MODE',
                    'MODULE_PAYMENT_SQUARE_SANDBOX_TOKEN',
                )
            );
        }

        return $keys;
    }

    /**
     * Check and fix table structure if appropriate
     *
     * Note: The tender_id and transaction_id fields are no longer populated; but are left behind in older installs for lookup of history
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
              `payment_id` varchar(255) DEFAULT NULL,
              `sq_order` varchar(255) DEFAULT NULL,
              `action` varchar(40),
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            )";
            $db->Execute($sql);
        }
        $fieldOkay1 = (method_exists($sniffer, 'field_exists')) ? $sniffer->field_exists(TABLE_SQUARE_PAYMENTS, 'payment_id') : false;
        if ($fieldOkay1 !== true) {
            $db->Execute("ALTER TABLE " . TABLE_SQUARE_PAYMENTS . " ADD payment_id varchar(255) DEFAULT NULL AFTER location_id");
        }
        $fieldOkay2 = (method_exists($sniffer, 'field_exists')) ? $sniffer->field_exists(TABLE_SQUARE_PAYMENTS, 'sq_order') : false;
        if ($fieldOkay2 !== true) {
            $db->Execute("ALTER TABLE " . TABLE_SQUARE_PAYMENTS . " ADD sq_order varchar(255) DEFAULT NULL AFTER payment_id");
        }
        if (method_exists($sniffer, 'field_exists') && $sniffer->field_exists(TABLE_SQUARE_PAYMENTS, 'transaction_id')) {
            $db->Execute("ALTER TABLE " . TABLE_SQUARE_PAYMENTS . " MODIFY transaction_id varchar(255) DEFAULT NULL");
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
            'Payment ID assigned: ' . $response['id'] . "\n" .
            'Sent to Square: ' . print_r($payload, true) . "\n\n" .
            'Results Received back from Square: ' . print_r($response, true) . "\n\n";

        if (strstr(MODULE_PAYMENT_SQUARE_LOGGING, 'Log Always') || ($errors != '' && strstr(MODULE_PAYMENT_SQUARE_LOGGING, 'Log on Failures'))) {
            $key = $response['id'] . '_' . time() . '_' . zen_create_random_value(4);
            $file = $this->_logDir . '/' . 'Square_' . $key . '.log';
            if ($fp = @fopen($file, 'a')) {
                fwrite($fp, $logMessage);
                fclose($fp);
            }
        }
        if (($errors != '' && stristr(MODULE_PAYMENT_SQUARE_LOGGING, 'Email on Failures')) || strstr(MODULE_PAYMENT_SQUARE_LOGGING, 'Email Always')) {
            zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Square Alert (' . (IS_ADMIN_FLAG === true ? 'admin' : 'customer') . ' transaction error) ' . date('M-d-Y h:i:s'), $logMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS,
                array('EMAIL_MESSAGE_HTML' => nl2br($logMessage)), 'debug');
        }
    }

    /**
     * Refund all or part of an order
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

        $record = $this->lookupPaymentForOrder($oID);
        if (!method_exists($record, 'getTenders')) {
            $messageStack->add_session('ERROR: Could not look up details. Probable bad record number, or incorrect Square account credentials.', 'error');
            return false;
        }
        $transactions = $record->getTenders();

        $payment = $transactions[0];
        $currency_code = $payment->getAmountMoney()->getCurrency();

        $refund_details = array(
            'amount_money' => array(
                'amount' => $this->convert_to_cents($amount, $currency_code),
                'currency' => $currency_code,
            ),
            'payment_id' => $payment->getId(),
            'reason' => substr(htmlentities(trim($refundNote)), 0, 60),
            'idempotency_key' => uniqid(),
        );
        $request_body = new \SquareConnect\Model\RefundPaymentRequest($refund_details);
        $this->logTransactionData(array('comment' => 'Creating refund request'), $refund_details);

        $this->getAccessToken();
        $api_instance = new SquareConnect\Api\RefundsApi($this->_apiConnection);
        try {
            $result = $api_instance->refundPayment($request_body);
            $errors_object = $result->getErrors();
            $transaction = $result->getRefund();
            $this->logTransactionData(['refund request' => 'payment ' . $payment->getId(), 'id' => '[refund]'], $refund_details, (string)$errors_object);

            $currency_code = $transaction->getAmountMoney()->getCurrency();
            $amount = $currencies->format($transaction->getAmountMoney()->getAmount() / (pow(10, $currencies->get_decimal_places($currency_code))), false, $currency_code);

            $comments = 'REFUNDED: ' . $amount . "\n" . $refundNote;
            zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

            $messageStack->add_session(sprintf(MODULE_PAYMENT_SQUARE_TEXT_REFUND_INITIATED . $amount), 'success');

            return true;

        } catch (\SquareConnect\ApiException $e) {
            $errors_object = $e->getResponseBody()->errors;
            $this->logTransactionData(array($e->getCode() => $e->getMessage()), $refund_details, print_r($e->getResponseBody(), true));
            trigger_error("Square Connect error (REFUNDING). \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
//            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR, 'error');
        }

        if (is_array($errors_object) && count($errors_object)) {
            $error = $this->parse_error_response($errors_object);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_UPDATE_FAILED . ' [' . $error['detail'] . ']', 'error');
        }
        return false;
    }

    /**
     * Capture a previously-authorized transaction.
     */
    public function _doCapt($oID, $type = 'Complete', $amount = null, $currency = null)
    {
        global $messageStack;

        $new_order_status = $this->getNewOrderStatus($oID, 'capture', (int)MODULE_PAYMENT_SQUARE_ORDER_STATUS_ID);
        if ($new_order_status == 0) $new_order_status = 1;

        $captureNote = strip_tags(zen_db_input($_POST['captnote']));

        $proceedToCapture = true;
        if (!isset($_POST['captconfirm']) || $_POST['captconfirm'] != 'on') {
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_CAPTURE_CONFIRM_ERROR, 'error');
            $proceedToCapture = false;
        }

        if (!$proceedToCapture) return false;

        $record = $this->lookupPaymentForOrder($oID);
        if (!method_exists($record, 'getTenders')) {
            $messageStack->add_session('ERROR: Could not look up details. Probable bad record number, or incorrect Square account credentials.', 'error');
            return false;
        }
        $transactions = $record->getTenders();
        $transaction = $transactions[0];
        $payment_id = $transaction->getPaymentId();

        $this->getAccessToken();

        $api_instance = new \SquareConnect\Api\PaymentsApi($this->_apiConnection);

        try {
            $result = $api_instance->completePayment($payment_id, new \SquareConnect\Model\CompletePaymentRequest([]));
            $errors_object = $result->getErrors();
            $this->logTransactionData(array('capture request' => 'payment ' . $payment_id, 'id' => '[capture]'), array(), (string)$errors_object);

            $comments = 'FUNDS COLLECTED. Trans ID: ' . $payment_id . "\n" . 'Time: ' . date('Y-m-D h:i:s') . "\n" . $captureNote;
            zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

            $messageStack->add_session(sprintf(MODULE_PAYMENT_SQUARE_TEXT_CAPT_INITIATED, $payment_id), 'success');

            return true;

        } catch (\SquareConnect\ApiException $e) {
            $errors_object = $e->getResponseBody()->errors;
            $this->logTransactionData(array($e->getCode() => $e->getMessage()), array(), print_r($e->getResponseBody(), true));
            trigger_error("Square Connect error (CAPTURE attempt). \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
//            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR, 'error');
        }

        if (is_array($errors_object) && count($errors_object)) {
            $error = $this->parse_error_response($errors_object);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_UPDATE_FAILED . ' [' . $error['detail'] . ']', 'error');
        }
        return false;
    }

    /**
     * Void/Cancel a not-yet-captured/completed authorized transaction.
     */
    public function _doVoid($oID, $note = '')
    {
        global $messageStack;

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

        $record = $this->lookupPaymentForOrder($oID);
        if (!method_exists($record, 'getTenders')) {
            $messageStack->add_session('ERROR: Could not look up details. Probable bad record number, or incorrect Square account credentials.', 'error');
            return false;
        }
        $transactions = $record->getTenders();
        $transaction = $transactions[0];
        $payment_id = $transaction->getPaymentId();

        $this->getAccessToken();
        $api_instance = new \SquareConnect\Api\PaymentsApi($this->_apiConnection);
        try {
            $result = $api_instance->cancelPayment($payment_id);
            $errors_object = $result->getErrors();
            $this->logTransactionData(array('void request' => 'payment ' . $payment_id, 'id' => '[void]'), array(), (string)$errors_object);

            $comments = 'VOIDED. Trans ID: ' . $payment_id . "\n" . $voidNote;
            zen_update_orders_history($oID, $comments, null, $new_order_status, 0);

            $messageStack->add_session(sprintf(MODULE_PAYMENT_SQUARE_TEXT_VOID_INITIATED, $payment_id), 'success');

            return true;

        } catch (\SquareConnect\ApiException $e) {
            $errors_object = $e->getResponseBody()->errors;
            $this->logTransactionData(array($e->getCode() => $e->getMessage()), array(), print_r($e->getResponseBody(), true));
            trigger_error("Square Connect error (VOID attempt). \nResponse Body:\n" . print_r($e->getResponseBody(), true) . "\nResponse Headers:\n" . print_r($e->getResponseHeaders(), true), E_USER_NOTICE);
//            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR, 'error');
        }

        if (is_array($errors_object) && count($errors_object)) {
            $msg = $this->parse_error_response($errors_object);
            $messageStack->add_session(MODULE_PAYMENT_SQUARE_TEXT_UPDATE_FAILED . ' [' . $msg['detail'] . ']', 'error');
        }
        return false;
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
        $msg = '';
        $first_category = null;
        $first_code = null;
        if (!empty($error_object)) {
            foreach ($error_object as $err) {
                $category = method_exists($err, 'getCategory') ? $err->getCategory() : $err->category;
                $code = method_exists($err, 'getCode') ? $err->getCode() : $err->code;
                $detail = method_exists($err, 'getDetail') ? $err->getDetail() : $err->detail;
                $msg .= "$code: $detail\n";
                if (is_null($first_category)) $first_category = $category;
                if (is_null($first_code)) $first_code = $code;
            }
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
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $class = new square;
    $pulldown = $class->getLocationsPulldownArray();

    return zen_draw_pull_down_menu($name, $pulldown, $location);
}

/////////////////////////////

// for backward compatibility prior to v1.5.7;
if (!function_exists('zen_update_orders_history'))
{
    function zen_update_orders_history($orders_id, $message = '', $updated_by = null, $orders_new_status = -1, $notify_customer = -1)
    {
        $data = array(
            'orders_id' => (int)$orders_id,
            'orders_status_id' => (int)$orders_new_status,
            'customer_notified' => (int)$notify_customer,
            'comments' => zen_db_input($message),
        );
        zen_db_perform (TABLE_ORDERS_STATUS_HISTORY, $data);
    }
}
