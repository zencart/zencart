<?php
/**
 * PayPalRestfulApi.php communications class for PayPal Rest payment module
 *
 * Applicable PayPal documentation:
 *
 * - https://developer.paypal.com/docs/checkout/advanced/processing/
 * - https://stackoverflow.com/questions/14451401/how-do-i-make-a-patch-request-in-php-using-curl
 * - https://developer.paypal.com/docs/checkout/standard/customize/
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 *
 * Last updated: v1.3.0
 */
namespace PayPalRestful\Api;

use PayPalRestful\Common\ErrorInfo;
use PayPalRestful\Common\Logger;
use PayPalRestful\Common\PayPalShippingCarriers;
use PayPalRestful\Token\TokenCache;

/**
 * PayPal REST API (see https://developer.paypal.com/api/rest/)
 */
class PayPalRestfulApi extends ErrorInfo
{
    // -----
    // Constants used to set the class variable errorInfo['errNum'].
    //
    const ERR_NO_ERROR      = 0;    //-No error occurred, initial value

    const ERR_NO_CHANNEL    = -1;   //-Set if the curl_init fails; no other requests are honored
    const ERR_CURL_ERROR    = -2;   //-Set if the curl_exec fails.  The curlErrno variable contains the curl_errno and errMsg contains curl_error

    // -----
    // Constants that define the test and production endpoints for the API requests.
    //
    const ENDPOINT_SANDBOX = 'https://api-m.sandbox.paypal.com/';
    const ENDPOINT_PRODUCTION = 'https://api-m.paypal.com/';

    // -----
    // PayPal constants associated with an order/payment's current 'status'. Also
    // used for the paypal::payment_status field.
    //
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_CAPTURED = 'CAPTURED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CREATED = 'CREATED';
    const STATUS_DENIED = 'DENIED';
    const STATUS_FAILED = 'FAILED';
    const STATUS_PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';

    //- The order requires an action from the payer (e.g. 3DS authentication or PayPal confirmation).
    //    Redirect the payer to the "rel":"payer-action" HATEOAS link returned as part of the response
    //    prior to authorizing or capturing the order.
    const STATUS_PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';

    const STATUS_PENDING = 'PENDING';
    const STATUS_REFUNDED = 'REFUNDED';
    const STATUS_SAVED = 'SAVED';
    const STATUS_VOIDED = 'VOIDED';

    /**
     * Webhook actions we intend to listen for notifications regarding.
     */
    protected $webhooksToRegister = [
        'CHECKOUT.PAYMENT-APPROVAL.REVERSED',
        'PAYMENT.AUTHORIZATION.VOIDED',
        'PAYMENT.CAPTURE.COMPLETED',
        'PAYMENT.CAPTURE.DECLINED',
        'PAYMENT.CAPTURE.PENDING',
        'PAYMENT.CAPTURE.REFUNDED',
        'PAYMENT.CAPTURE.REVERSED',
    ];

    /**
     * Variables associated with interface logging;
     *
     * @log Logger object, logs debug tracing information.
     */
    protected $log;

    /**
     * Variables associated with interface logging;
     *
     * @token TokenCache object, caches any access-token retrieved from PayPal.
     */
    protected $tokenCache;

    /**
     * Sandbox or production? Set during class construction.
     */
    protected $endpoint;

    /**
     * OAuth client id and secret, set during class construction.
     */
    private $clientId;
    private $clientSecret;

    /**
     * The CURL channel, initialized during construction.
     */
    protected $ch = false;

    /**
     * Options for cURL. Defaults to preferred (constant) options.  Used by
     * the curlGet and curlPost methods.
     */
    protected $curlOptions = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_FORBID_REUSE => true,
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 45,
    ];

    /**
     * Contains the (optional) HTTP Header's PayPal-Request-Id value;
     * required for payments with a payment_source *other than* paypal
     * (the default).  See https://developer.paypal.com/api/rest/requests/#http-request-headers
     * for additional information.
     */
    protected $paypalRequestId = '';

    /**
     * Contains an (optional) "Mock Response" to be included in the HTTP
     * header's PayPal-Mock-Response value, enabling testing to be performed
     * for error responses; see the above link for additional information.
     */
    protected $paypalMockResponse = '';

    /**
     * A binary flag that indicates whether/not the caller wants to keep the 'links' returned
     * by the various PayPal responses.
     */
    protected $keepTxnLinks = false;

    // -----
    // Class constructor, saves endpoint (live vs. sandbox), clientId and clientSecret
    //
    public function __construct(string $endpoint_type, string $client_id, string $client_secret)
    {
        parent::__construct();

        $this->endpoint = ($endpoint_type === 'live') ? self::ENDPOINT_PRODUCTION : self::ENDPOINT_SANDBOX;
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;

        $this->ch = curl_init();
        if ($this->ch === false) {
            $this->setErrorInfo(self::ERR_NO_CHANNEL, 'Unable to initialize the CURL channel.');
            trigger_error($this->errMsg, E_USER_WARNING);
        }

        $this->log = new Logger();
        $this->tokenCache = new TokenCache($client_secret);
    }

    // ----
    // Class destructor, close the CURL channel if the channel's open (i.e. not false).  Also an 'alias' for the
    // public 'close' method.
    //
    public function __destruct()
    {
        $this->close();
    }
    public function close()
    {
        if ($this->ch !== false) {
            if (PHP_VERSION_ID < 80000) {
                curl_close($this->ch);
            }
            $this->ch = false;
        }
    }

    public function setPayPalRequestId(string $request_id)
    {
        $this->paypalRequestId = $request_id;
    }

    public function setPayPalMockResponse(string $mock_response)
    {
        $this->paypalMockResponse = $mock_response;
    }

    public function setKeepTxnLinks(bool $keep_links)
    {
        $this->keepTxnLinks = $keep_links;
    }

    // ===== Start Token-required Methods =====

    public function createOrder(array $order_request)
    {
        $this->log->write('==> Start createOrder', true);
        $response = $this->curlPost('v2/checkout/orders', $order_request);
        $this->log->write("==> End createOrder", true);
        return $response;
    }

    public function getOrderStatus(string $paypal_id)
    {
        $this->log->write('==> Start getOrderStatus', true);
        $response = $this->curlGet("v2/checkout/orders/$paypal_id");
        $this->log->write("==> End getOrderStatus", true);
        return $response;
    }

    public function confirmPaymentSource(string $paypal_id, array $payment_source)
    {
        $this->log->write('==> Start confirmPaymentSource', true);
        $paypal_options = [
            'payment_source' => $payment_source,
        ];
        $response = $this->curlPost("v2/checkout/orders/$paypal_id/confirm-payment-source", $paypal_options);
        $this->log->write("==> End confirmPaymentSource", true);
        return $response;
    }

    public function captureOrder(string $paypal_id)
    {
        $this->log->write('==> Start captureOrder', true);
        $response = $this->curlPost("v2/checkout/orders/$paypal_id/capture");
        $this->log->write("==> End captureOrder", true);
        return $response;
    }

    public function authorizeOrder(string $paypal_id)
    {
        $this->log->write('==> Start authorizeOrder', true);
        $response = $this->curlPost("v2/checkout/orders/$paypal_id/authorize");
        $this->log->write("==> End authorizeOrder", true);
        return $response;
    }

    public function getAuthorizationStatus(string $paypal_auth_id)
    {
        $this->log->write('==> Start getAuthorizationStatus', true);
        $response = $this->curlGet("v2/payments/authorizations/$paypal_auth_id");
        $this->log->write("==> End getAuthorizationStatus\n", true);
        return $response;
    }

    public function capturePaymentRemaining(string $paypal_auth_id, string $invoice_id, string $payer_note, bool $final_capture)
    {
        $this->log->write("==> Start capturePaymentRemaining($paypal_auth_id, $invoice_id, $payer_note, $final_capture)", true);
        $parameters = [
            'invoice_id' => $invoice_id,
            'note_to_payer' => $payer_note,
            'final_capture' => $final_capture,
        ];
        $response = $this->curlPost("v2/payments/authorizations/$paypal_auth_id/capture", $parameters);
        $this->log->write("==> End capturePaymentRemaining\n", true);
        return $response;
    }

    public function capturePaymentAmount(string $paypal_auth_id, string $currency_code, string $value, string $invoice_id, string $payer_note, bool $final_capture)
    {
        $this->log->write("==> Start capturePaymentAmount($paypal_auth_id, $currency_code, $value, $invoice_id, $payer_note, $final_capture)", true);
        $parameters = [
            'amount' => [
                'currency_code' => $currency_code,
                'value' => $value,
            ],
            'invoice_id' => $invoice_id,
            'note_to_payer' => $payer_note,
            'final_capture' => $final_capture,
        ];
        $response = $this->curlPost("v2/payments/authorizations/$paypal_auth_id/capture", $parameters);
        $this->log->write("==> End capturePaymentAmount\n", true);
        return $response;
    }

    public function getCaptureStatus(string $paypal_capture_id)
    {
        $this->log->write('==> Start getCaptureStatus', true);
        $response = $this->curlGet("v2/payments/captures/$paypal_capture_id");
        $this->log->write("==> End getCaptureStatus\n", true);
        return $response;
    }

    public function reAuthorizePayment(string $paypal_auth_id, string $currency_code, string $value)
    {
        $this->log->write("==> Start reAuthorizePayment($paypal_auth_id, $currency_code, $value)", true);
        $amount = [
            'amount' => [
                'currency_code' => $currency_code,
                'value' => $value,
            ],
        ];
        $response = $this->curlPost("v2/payments/authorizations/$paypal_auth_id/reauthorize", $amount);
        $this->log->write('==> End reAuthorizePayment', true);
        return $response;
    }

    public function voidPayment(string $paypal_auth_id)
    {
        $this->log->write("==> Start voidPayment($paypal_auth_id)", true);
        $response = $this->curlPost("v2/payments/authorizations/$paypal_auth_id/void");
        $this->log->write('==> End voidPayment', true);
        return $response;
    }

    public function getTransactionStatus(string $paypal_id)
    {
        $this->log->write("==> Start getTransactionStatus ($paypal_id)", true);
        $parameters = [
            'transaction_id' => $paypal_id,
            'fields' => 'all',
        ];
        $response = $this->curlGet("v1/reporting/transactions", $parameters);
        $this->log->write("==> End getTransactionStatus", true);
        return $response;
    }

    public function refundCaptureFull(string $paypal_capture_id, string $invoice_id, string $payer_note)
    {
        return $this->refundCapture($paypal_capture_id, $invoice_id, $payer_note);
    }
    public function refundCapturePartial(string $paypal_capture_id, string $currency_code, string $value, string $invoice_id, string $payer_note)
    {
        return $this->refundCapture($paypal_capture_id, $invoice_id, $payer_note, compact('currency_code', 'value'));
    }
    protected function refundCapture(string $paypal_capture_id, string $invoice_id, string $payer_note, array $amount = [])
    {
        $this->log->write("==> Start refundCapture($paypal_capture_id, $invoice_id, $payer_note, ...)\n" . Logger::logJSON($amount), true);
        $parameters = [
            'invoice_id' => $invoice_id,
            'note_to_payer' => $payer_note,
        ];
        if (!empty($amount)) {
            $parameters['amount'] = $amount;
        }
        $response = $this->curlPost("v2/payments/captures/$paypal_capture_id/refund", $parameters);
        $this->log->write("==> End refundCapture", true);
        return $response;
    }

    public function getRefundStatus($paypal_refund_id)
    {
        $this->log->write('==> Start getRefundStatus', true);
        $response = $this->curlGet("v2/payments/refunds/$paypal_refund_id");
        $this->log->write("==> End getRefundStatus\n", true);
        return $response;
    }

    /**
     * Send package tracking details to PayPal for a given PayPal Transaction ID
     *
     * @param string $paypal_txnid
     * @param string $tracking_number
     * @param string $carrier_code Must match enum name of valid carriers per https://developer.paypal.com/docs/tracking/reference/carriers/
     * @param string $action ADD or CANCEL
     * @param bool $email_buyer Whether PayPal should email tracking info to the buyer
     * @return false|array
     */
    public function updatePackageTracking(
        string $paypal_txnid,
        string $tracking_number,
        string $carrier_code,
        string $action = 'ADD',
        bool $email_buyer = false
    ) {
        $this->log->write("==> Start updatePackageTracking($paypal_txnid, " . Logger::logJSON($tracking_number) . ", $carrier_code, $action ...)\n", true);

        if (empty($tracking_number)) {
            return false;
        }

        $orderDetails = $this->getOrderStatus($paypal_txnid);
        if (empty($orderDetails)) {
            $this->log->write('Cannot find order to update/cancel tracking. Txn ID: ' . $paypal_txnid);
            return false;
        }
        if (($orderStatus = $orderDetails['status'] ?? '(null)') !== 'COMPLETED' || empty($orderDetails['purchase_units'][0]['payments']['captures'])) {
            $this->log->write("Only orders with COMPLETED captures may add tracking. Txn ID: $paypal_txnid, Status: $orderStatus");
            return false;
        }

        if (!in_array($action, ['ADD', 'CANCEL'])) {
            $action = 'ADD';
        }

        if ($action === 'ADD') {
            // carrier code is required if tracking number provided
            if (empty($carrier_code) && !empty($tracking_number)) {
                $this->log->write('ERROR: Package Tracking requires a carrier_code value when tracking_number is provided. Carrier code is empty.');
                return false;
            }
            // find country code
            $country_iso_2 = $orderDetails['purchase_units'][0]['shipping']['address']['country_code'] ?? '';
            global $db;
            $sql = "SELECT countries_iso_code_3 FROM " . TABLE_COUNTRIES . " WHERE countries_iso_code_2 = '" . zen_db_input($country_iso_2) . "'";
            $result = $db->Execute($sql, 1);
            $country_iso_3 = $result->fields['countries_iso_code_3'] ?? '';
            $checkedCode = PayPalShippingCarriers::findBestMatch($carrier_code, $country_iso_3);

            if ($checkedCode !== null) {
                $carrier_code = $checkedCode;
            }
            // If carrier name is not officially supported, set to OTHER and use provided name as explanation
            if (!empty($checkedCode) || PayPalShippingCarriers::isValid($carrier_code)) {
                $carrier_name_other = null;
            } else {
                $carrier_name_other = $carrier_code;
                $carrier_code = 'OTHER';
            }

            $paypal_capture_id = $orderDetails['purchase_units'][0]['payments']['captures'][0]['id'];

            $parameters = [
                'capture_id' => $paypal_capture_id,
                'tracking_number' => substr($tracking_number, 0, 64),
                'carrier' => $carrier_code,
                'carrier_name_other' => $carrier_name_other,
                'notify_buyer' => $email_buyer,
            ];
            // $this->log->write("==> Sending tracking update: $paypal_txnid, " . Logger::logJSON($parameters) . ")\n", true);
            $response = $this->curlPost("v2/checkout/orders/$paypal_txnid/track", $parameters);

        } else { // $action == 'CANCEL' (to delete a package tracking number)
            $trackers = $orderDetails['purchase_units'][0]['shipping']['trackers'] ?? null;
            if (empty($trackers)) {
                $this->log->write('No registered trackers found; nothing to update/cancel. Txn ID: ' . $paypal_txnid);
                return false;
            }
            foreach ($trackers as $tracker) {
                if (\str_ends_with($tracker['id'], $tracking_number)) {
                    if ($tracker['status'] === 'CANCELLED') {
                        $this->log->write("Tracker ALREADY CANCELLED for tracking_number $tracking_number; nothing to update/cancel. Txn ID: $paypal_txnid");
                        return false;
                    }
                    // use the located id
                    $tracker_id = $tracker['id'];
                    break;
                }
            }
            if (empty($tracker_id)) {
                $this->log->write("No registered trackers found for tracking_number $tracking_number; nothing to update/cancel. Txn ID: $paypal_txnid");
                return false;
            }
            $parameters = ['op' => 'replace', 'path' => '/status', 'value' => 'CANCELLED'];
            // $this->log->write("==> Sending tracking update: $paypal_txnid, " . Logger::logJSON($parameters) . ")\n", true);
            $response = $this->curlPatch("v2/checkout/orders/$paypal_txnid/trackers/$tracker_id", [$parameters]);
            if ($response === null) {
                $response = ['success'];
            }
        }
        $this->log->write("==> End updatePackageTracking", true);
        return $response;
    }

    /**
     * Submit API call to register the webhooks we are able to listen for
     */
    public function subscribeWebhook()
    {
        if (empty($this->webhooksToRegister)) {
            return;
        }
        // skip unreachable localhost/testing domains
        $domain = str_replace(['http'.'://', 'https'.'://'], '', rtrim(HTTP_SERVER, '/'));
        foreach (['.local', '.test'] as $val) {
            if (str_ends_with($domain, $val)) {
                return;
            }
        }
        foreach (['localhost', '127.0.0.1'] as $val) {
            if (str_starts_with($domain, $val)) {
                return;
            }
        }

        $url = HTTP_SERVER . DIR_WS_CATALOG . 'ppr_webhook.php';

        $events = [];
        foreach ($this->webhooksToRegister as $event) {
            $events[] = ['name' => $event];
        }
        $parameters = ['url' => $url, 'event_types' => $events];

        $response = $this->curlPost("v1/notifications/webhooks", $parameters);
        if ($response === false) {
            $err = $this->getErrorInfo();
            if ($err['errNum'] === 400 && $err['name'] === 'WEBHOOK_URL_ALREADY_EXISTS') {
                // Failed to set, so inquire and store registered ID
                $response = $this->curlGet("v1/notifications/webhooks/");
                if ($response === false) {
                    $this->log->write("ALERT: Webhooks could not be registered. Unable to listen for notifications.", true);
                    return;
                }
                foreach ($response['webhooks'] as $webhook) {
                    if ($webhook['url'] === $url) {
                        $webhook_id = $webhook['id'];
                        break;
                    }
                }
            }
        } else {
            $webhook_id = $response['id'];
        }

        // store the resulting webhook registration ID for later reference
        global $db;
        $result = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS' LIMIT 1");
        if ($result->EOF) {
            zen_db_perform(TABLE_CONFIGURATION, [
                'configuration_key' => 'MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS',
                'configuration_value' => $webhook_id,
                'configuration_title' => 'PayPal webhooks subscribe ID',
                'configuration_description' => 'This module registers certain actions to trigger webhook notifications to this store; here we store the ID of that registration so we can update or delete it later if needed.',
                'configuration_group_id' => 6,
                'sort_order' => 0,
                'date_added' => 'now()',
                'last_modified' => 'now()',
            ]);
        } else {
            zen_db_perform(TABLE_CONFIGURATION, [
                'configuration_value' => $webhook_id,
                'last_modified' => 'now()',
            ], 'UPDATE', "configuration_key='MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS'");
        }
    }

    /**
     * Ensure the webhooks we want to listen for are all registered
     */
    public function registerAndUpdateSubscribedWebhooks()
    {
        $webhook_id = defined('MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS') ? MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS : '';

        if (empty($webhook_id)) {
            $this->subscribeWebhook();
            return;
        }

        // skip unreachable localhost/testing domains
        $domain = str_replace(['http'.'://', 'https'.'://'], '', rtrim(HTTP_SERVER, '/'));
        foreach (['.local', '.test'] as $val) {
            if (str_ends_with($domain, $val)) {
                return;
            }
        }
        foreach (['localhost', '127.0.0.1'] as $val) {
            if (str_starts_with($domain, $val)) {
                return;
            }
        }

        // Check whether all the desired webhook actions are registered,
        // if they're not, send an updated array of event_types names

        $response = $this->curlGet("v1/notifications/webhooks/$webhook_id");
        if ($response === false) {
            $this->subscribeWebhook();
            return;
        }

        $patchRequired = false;
        $registeredEvents = [];
        foreach ($response['event_types'] as $event) {
            $registeredEvents[] = $event['name'];
        }
        if ($registeredEvents[0] !== '*') {
            foreach ($this->webhooksToRegister as $hook) {
                if (!\in_array($hook, $registeredEvents, true)) {
                    $patchRequired = true;
                }
            }
        }

        if ($patchRequired === false) {
            return;
        }

        $events = [];
        foreach ($this->webhooksToRegister as $event) {
            $events[] = ['name' => $event];
        }
        $parameters = ['op' => 'replace', 'path' => '/event_types', 'value' => $events];
        $response = $this->curlPatch("v1/notifications/webhooks/$webhook_id", [$parameters]);
    }

    public function webhookVerifyByPostback($parameters)
    {
        $this->log->write("==> Start webhookVerifyByPostback", true);
        $response = $this->curlPost('v1/notifications/verify-webhook-signature', $parameters);
        if ($response === false) {
            $this->log->write("==> End webhookVerifyByPostback (failed)", true);
            return null;
        }
        $this->log->write("==> End webhookVerifyByPostback (success)", true);
        return ($response['verification_status'] === 'SUCCESS');
    }

    /**
     * When uninstalling this module, we should cleanup the webhook subscription record, so PayPal stops sending notifications.
     */
    public function unsubscribeWebhooks()
    {
        $this->log->write("==> Start deleteWebhook Registration", true);
        $url = HTTP_SERVER . DIR_WS_CATALOG . 'ppr_webhook.php';

        $webhook_id = defined('MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS') ? MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS : '';
        if (empty($webhook_id)) {
            // None remembered internally, but let's also check if any are registered at PayPal for our URL, and remove them.
            $response = $this->curlGet("v1/notifications/webhooks/");
            if ($response !== false) {
                foreach ($response['webhooks'] as $webhook) {
                    if ($webhook['url'] === $url) {
                        $this->curlDelete("v1/notifications/webhooks/" . $webhook['id']);
                    }
                }
            } else {
                $this->log->write("No webhook registration ID found in store configuration database. Nothing to do.", true);
                return;
            }
        }

        $response = $this->curlDelete("v1/notifications/webhooks/$webhook_id");
        if ($response !== false) {
            global $db;
            // deregistration successful, so we delete our record.
            $db->Execute('DELETE FROM ' . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS'");
        }
        $this->log->write("==> End deleteWebhook Registration", true);
    }

    // ===== End Token-required Methods =====

    // ===== Start Token Handling Methods =====

    // -----
    // Validates the supplied client-id/secret; used during admin/store initialization to
    // auto-disable the associated payment method if the credentials aren't valid.
    //
    // Normally, this method is called requesting that any saved token be used, to cut
    // down on API requests.  The one exception is during the payment-module's configuration
    // in the admin, where the currently-configured credentials need to be specifically validated!
    //
    public function validatePayPalCredentials(bool $use_saved_token = true): bool
    {
        return ($this->getOAuth2Token($this->clientId, $this->clientSecret, $use_saved_token) !== '');
    }

    // -----
    // Retrieves an OAuth token from PayPal to use in follow-on requests, returning the token
    // to the caller.
    //
    // Normally, the method's called without the 3rd parameter, so it will check to see if a
    // previously-saved token is available to cut down on API calls.  The validatePayPalCredentials
    // method is an exclusion, as it's used during the admin configuration of the payment module to
    // ensure that the client id/secret are validated.
    //
    protected function getOAuth2Token(string $client_id, string $client_secret, bool $use_saved_token = true): string
    {
        if ($this->ch === false) {
            $this->ch = curl_init();
            if ($this->ch === false) {
                $this->setErrorInfo(self::ERR_NO_CHANNEL, 'Unable to initialize the CURL channel.');
                return '';
            }
        }

        if ($use_saved_token === true) {
            $token = $this->tokenCache->get();
            if ($token !== '') {
                return $token;
            }
        }

        $additional_curl_options = [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
            ],
        ];
        $response = $this->curlPost('v1/oauth2/token', ['grant_type' => 'client_credentials'], $additional_curl_options, false);

        $token = '';
        if ($response !== false) {
            $token = $response['access_token'];
            if ($use_saved_token === true) {
                $this->tokenCache->save($token, $response['expires_in']);
            }
         }

        return $token;
    }

    // -----
    // Sets the common authorization header into the CURL options for a PayPal Restful request.
    //
    // If the request to retrieve the token fails, an empty array is returned; otherwise,
    // the authorization-header containing the successfully-retrieved token is merged into
    // supplied array of CURL options and returned.
    //
    protected function setAuthorizationHeader(array $curl_options): array
    {
        $oauth2_token = $this->getOAuth2Token($this->clientId, $this->clientSecret);
        if ($oauth2_token === '') {
            return [];
        }

        $curl_options[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/json',
            "Authorization: Bearer $oauth2_token",
            'Prefer: return=representation',
            'PayPal-Partner-Attribution-Id: ZenCart_SP_PPCP',
        ];

        // -----
        // If a PayPal-Request-Id value is set, include that value
        // in the HTTP header.
        //
        if ($this->paypalRequestId !== '') {
            $curl_options[CURLOPT_HTTPHEADER][] = 'PayPal-Request-Id: ' . $this->paypalRequestId;
        }

        // -----
        // If a PayPal-Mock-Response value is set, include that value
        // in the HTTP header.
        //
        if ($this->paypalMockResponse !== '') {
            $curl_options[CURLOPT_HTTPHEADER][] = 'PayPal-Mock-Response: ' . json_encode(['mock_application_codes' => $this->paypalMockResponse]);
        }

        return $curl_options;
    }

    // ===== End Token Handling Methods =====

    // ===== Start CURL Interface Methods =====

    // -----
    // A common method for all POST requests to PayPal.
    //
    // Parameters:
    // - option
    //     The option to be performed, e.g. v2/checkout/orders
    // - options_array
    //     An (optional) array of options to be supplied, dependent on the 'option' to be sent.
    // - additional_curl_options
    //     An array of additional CURL options to be applied.
    // - token_required
    //     An indication as to whether/not an authorization header is to be included.
    //
    // Return Values:
    // - On success, an associative array containing the PayPal response.
    // - On failure, returns false.  The details of the failure can be interrogated via the getErrorInfo method.
    //
    protected function curlPost(string $option, array $options_array = [], array $additional_curl_options = [], bool $token_required = true)
    {
        if ($this->ch === false) {
            $this->ch = curl_init();
            if ($this->ch === false) {
                $this->setErrorInfo(self::ERR_NO_CHANNEL, 'Unable to initialize the CURL channel.');
                return false;
            }
        }

        $url = $this->endpoint . $option;
        $curl_options = array_replace($this->curlOptions, [CURLOPT_POST => true, CURLOPT_URL => $url], $additional_curl_options);

        // -----
        // If a token is required, i.e. it's not a request to gather an access-token, use
        // the existing token to set the request's authorization.  Note that the method
        // being called will check to see if the current token has expired and will request
        // an update, if needed.
        //
        // Set the CURL options to use for this current request and then, if the token is NOT
        // required (i.e. the request is to retrieve an access-token), remove the site's
        // PayPal credentials from the posted options so that they're not exposed in subsequent
        // API logs.
        //
        if ($token_required === false) {
            if (count($options_array) !== 0) {
                $curl_options[CURLOPT_POSTFIELDS] = http_build_query($options_array);
            }
        } else {
            $curl_options = $this->setAuthorizationHeader($curl_options);
            if (count($curl_options) === 0) {
                return false;
            }
            if (count($options_array) !== 0) {
                $curl_options[CURLOPT_POSTFIELDS] = json_encode($options_array);
            }
        }

        curl_reset($this->ch);
        curl_setopt_array($this->ch, $curl_options);
        if ($token_required === false) {
            unset($curl_options[CURLOPT_POSTFIELDS]);
        }
        return $this->issueRequest('curlPost', $option, $curl_options);
    }

    // -----
    // A common method for all GET requests to PayPal.
    //
    // Parameters:
    // - option
    //      The option to be performed, e.g. v2/checkout/orders/{id}
    // - options_array
    //      An (optional) array of options to be supplied, dependent on the 'option' to be sent.
    //
    // Return Values:
    // - On success, an associative array containing the PayPal response.
    // - On failure, returns false.  The details of the failure can be interrogated via the getErrorInfo method.
    //
    protected function curlGet($option, $options_array = [])
    {
        if ($this->ch === false) {
            $this->ch = curl_init();
            if ($this->ch === false) {
                $this->setErrorInfo(self::ERR_NO_CHANNEL, 'Unable to initialize the CURL channel.');
                return false;
            }
        }

        $url = $this->endpoint . $option;
        if (count($options_array) !== 0) {
            $url .= '?' . http_build_query($options_array);
        }
        curl_reset($this->ch);
        $curl_options = array_replace($this->curlOptions, [CURLOPT_HTTPGET => true, CURLOPT_URL => $url]);  //-HTTPGET Needed since we might be toggling between GET and POST requests
        $curl_options = $this->setAuthorizationHeader($curl_options);
        if (count($curl_options) === 0) {
            return false;
        }

        curl_setopt_array($this->ch, $curl_options);
        return $this->issueRequest('curlGet', $option, $curl_options);
    }

    // -----
    // A common method for all PATCH requests to PayPal.
    //
    // Parameters:
    // - option
    //     The option to be performed, e.g. v2/checkout/orders/{id}
    // - options_array
    //     An (optional) array of options to be supplied, dependent on the 'option' to be sent.
    //
    // Return Values:
    // - On success, an associative array containing the PayPal response.
    // - On failure, returns false.  The details of the failure can be interrogated via the getErrorInfo method.
    //
    //
    protected function curlPatch($option, $options_array = [])
    {
        if ($this->ch === false) {
            $this->ch = curl_init();
            if ($this->ch === false) {
                $this->setErrorInfo(self::ERR_NO_CHANNEL, 'Unable to initialize the CURL channel.');
                return false;
            }
        }

        $url = $this->endpoint . $option;
        $curl_options = array_replace($this->curlOptions, [CURLOPT_POST => true, CURLOPT_CUSTOMREQUEST => 'PATCH', CURLOPT_URL => $url]);
        $curl_options = $this->setAuthorizationHeader($curl_options);
        if (count($curl_options) === 0) {
            return false;
        }

        if (count($options_array) !== 0) {
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($options_array);
        }
        curl_reset($this->ch);
        curl_setopt_array($this->ch, $curl_options);
        return $this->issueRequest('curlPatch', $option, $curl_options);
    }

    // -----
    // A common method for all DELETE requests to PayPal.
    //
    // Parameters:
    // - option
    //     The option to be performed, e.g. v1/notifications/webhooks/{id}
    // - options_array
    //     An (optional) array of options to be supplied, dependent on the 'option' to be sent.
    //
    // Return Values:
    // - On success, an associative array containing the PayPal response.
    // - On failure, returns false.  The details of the failure can be interrogated via the getErrorInfo method.
    //
    //
    protected function curlDelete($option, $options_array = [])
    {
        if ($this->ch === false) {
            $this->ch = curl_init();
            if ($this->ch === false) {
                $this->setErrorInfo(self::ERR_NO_CHANNEL, 'Unable to initialize the CURL channel.');
                return false;
            }
        }

        $url = $this->endpoint . $option;
        $curl_options = array_replace($this->curlOptions, [CURLOPT_POST => true, CURLOPT_CUSTOMREQUEST => 'DELETE', CURLOPT_URL => $url]);
        $curl_options = $this->setAuthorizationHeader($curl_options);
        if (count($curl_options) === 0) {
            return false;
        }

        if (count($options_array) !== 0) {
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($options_array);
        }
        curl_reset($this->ch);
        curl_setopt_array($this->ch, $curl_options);
        return $this->issueRequest('curlDelete', $option, $curl_options);
    }

    protected function issueRequest(string $request_type, string $option, array $curl_options)
    {
        // -----
        // Issue the CURL request.
        //
        $curl_response = curl_exec($this->ch);

        // -----
        // If a CURL error is indicated, call the common error-handling method to record that error.
        //
        if ($curl_response === false) {
            $response = false;
            $this->handleCurlError($request_type, $option, $curl_options);
        // -----
        // Otherwise, a response was returned.
        // Call the common response-handler to determine whether or not an error occurred.
        //
        } else {
            $response = $this->handleResponse($request_type, $option, $curl_options, $curl_response);
        }
        return $response;
    }

    // -----
    // Protected method, called by curlGet and curlPost when the curl_exec itself
    // returns an error.  Set the internal variables to capture the error information
    // and log (if enabled) to the PayPal logfile.
    //
    protected function handleCurlError(string $method, string $option, array $curl_options)
    {
        $this->setErrorInfo(self::ERR_CURL_ERROR, curl_error($this->ch), curl_errno($this->ch));
        curl_reset($this->ch);
        $this->log->write("handleCurlError for $method ($option) : CURL error (" . Logger::logJSON($this->errorInfo) . "\nCURL Options:\n" . Logger::logJSON($curl_options));
    }

    // -----
    // Protected method, called by curlGet and curlPost when no CURL error is reported.
    //
    // We'll check the HTTP response code returned by PayPal and take possibly option-specific
    // actions.
    //
    // Returns false if an error is detected, otherwise an associative array containing
    // the PayPal response.
    //
    protected function handleResponse(string $method, string $option, array $curl_options, $response)
    {
        // -----
        // Decode the PayPal response into an associative array, retrieve the httpCode associated
        // with the response and 'reset' the errorInfo property.
        //
        $response = json_decode($response, true);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->setErrorInfo($httpCode, '', 0, []);

        // -----
        // If no error, simply return the associated response.
        //
        // 200: Request succeeded
        // 201: A POST method successfully created a resource.
        // 204: No content returned; implies successful completion of an updateOrder request.
        //
        if ($httpCode === 200 || $httpCode === 201 || $httpCode === 204) {
            $this->log->write("The $method ($option) request was successful ($httpCode).\n" . Logger::logJSON($response, $this->keepTxnLinks));
            return $response;
        }

        $errMsg = '';
        switch ($httpCode) {
            // -----
            // 401: The access token has expired, noting that this "shouldn't" happen.
            //
            case 401:
                $this->tokenCache->clear();
                $errMsg = 'An expired-token error was received.';
                trigger_error($errMsg, E_USER_WARNING);
                break;

            // -----
            // 400: A general, usually interface-related, error occurred.
            // 403: Permissions error, the client doesn't have access to the requested endpoint.
            // 404: Something was not found.
            // 409: Resource conflict: duplicate/request already in progress.
            // 422: Unprocessable entity, kind of like 400.
            // 429: Rate Limited (you're making too many requests too quickly; you should reduce your rate of requests to stay within our Acceptable Useage Policy)
            // 500: Server Error
            // 503: Service Unavailable (our machine is currently down for maintenance; try your request again later)
            //
            case 400:
            case 403:
            case 404:
            case 409:
            case 422:
            case 429:
            case 500:
            case 503:
                $errMsg = "An interface error ($httpCode) was returned from PayPal.";
                break;

            // -----
            // Anything else wasn't expected.  Create a warning-level log indicating the
            // issue and that the response wasn't 'valid' and indicate that the
            // slamming timeout has started for some.
            //
            default:
                $errMsg = "An unexpected response ($httpCode) was returned from PayPal.";
                trigger_error($errMsg, E_USER_WARNING);
                break;
        }

        // -----
        // Note the error information in the errorInfo array, log a message to the PayPal log and
        // let the caller know that the request was unsuccessful.
        //
        $this->setErrorInfo($httpCode, $errMsg, 0, $response);
        $this->log->write("The $method ($option) request was unsuccessful.\n" . Logger::logJSON($this->errorInfo) . "\nCURL Options: " . Logger::logJSON($curl_options));

        return false;
    }
    // ===== End CURL Interface Methods =====
}
