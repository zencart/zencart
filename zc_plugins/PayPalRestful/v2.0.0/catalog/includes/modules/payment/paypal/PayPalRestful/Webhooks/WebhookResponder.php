<?php
/**
 * PayPal REST API Webhook Responder
 * This class handles verifying the validity of an incoming webhook
 * to ensure that it is legitimate. It checks POST headers for relevance
 * and checks that the payload's CRC check passes validity with PayPal
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte June 2025 $
 *
 * Last updated: v1.3.0
 */

namespace PayPalRestful\Webhooks;

use PayPalRestful\Api\PayPalRestfulApi;

class WebhookResponder
{
    protected $shouldRespond = false;

    protected $webhook_listener_subscribe_id = null;
    protected $webhook;

    public function __construct(WebhookObject $webhook)
    {
        $this->webhook = $webhook;

        $this->setWebhookSubscribeId();
    }

    /**
     * Check that headers match what PayPal Webhooks will contain,
     * and check that a few usual body content properties are present
     */
    public function shouldRespond(): bool
    {
        $headers = array_change_key_case($this->webhook->getHeaders(), CASE_UPPER);
        $data = $this->webhook->getJsonBody();
        if (array_key_exists('PAYPAL-AUTH-VERSION', $headers)
            && array_key_exists('PAYPAL-AUTH-ALGO', $headers)
            && isset($data['event_type'])
            && \str_contains($this->webhook->getUserAgent(), 'PayPal/')
        ) {
            $this->shouldRespond = true;
        }

        return $this->shouldRespond;
    }

    public function verify()
    {
        if ($this->shouldRespond !== true) {
            return null;
        }

        $valid = $this->doCrcCheck();

        // Null means we couldn't complete a CRC check (ie: internal issue, not "failed validation"),
        // So this falls through to trying a postback instead
        if ($valid === null) {
            $valid = $this->verifyByPostback();
        }

        // null means "we" (internally) couldn't complete a verification attempt (and we *will* want PayPal to see it as failed-to-complete, so they keep re-sending)
        // false means "failed validation"
        // true means "passed validation"
        if ($valid !== null) {
            // send a 200 response to acknowledge that we received the webhook
            http_response_code(200);
        }

        return $valid;
    }

    /**
     * @return bool|null  returns null if we cannot do CRC check, so fails over to PostBack approach
     */
    protected function doCrcCheck()
    {
        $headers = array_change_key_case($this->webhook->getHeaders(), CASE_UPPER);

        $transmissionId = $headers['PAYPAL-TRANSMISSION-ID'];
        $timestamp = $headers['PAYPAL-TRANSMISSION-TIME'];
        $crc = \hexdec(\hash('crc32b', $this->webhook->getRawBody()));
        $calculatedSignature = "$transmissionId|$timestamp|$this->webhook_listener_subscribe_id|$crc";
        $transmissionSignature = $headers['PAYPAL-TRANSMISSION-SIG'];
        $decodedSignature = base64_decode($transmissionSignature);

        $publicKeyUrl = $headers['PAYPAL-CERT-URL'];

        // @TODO - consider download and cache the public key, from the URL, instead of retrieving fresh in real time
        $pem_cert = $this->read_url($publicKeyUrl);

        $publicKey = openssl_get_publickey($pem_cert);

        $result = openssl_verify($calculatedSignature, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256);
        return $result === 1;
    }

    /**
     * @return bool|null  returns null if unable to use CURL or if the access token is invalid.
     */
    protected function verifyByPostback()
    {
        $headers = array_change_key_case($this->webhook->getHeaders(), CASE_UPPER);
        $params_array = [
            'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'],
            'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'],
            'cert_url' => $headers['PAYPAL-CERT-URL'],
            'auth_algo' => 'SHA256withRSA',
            'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'],
            'webhook_id' => $this->webhook_listener_subscribe_id,
            'webhook_event' => json_decode($this->webhook->getRawBody(), false), // decoded here because we re-encode for transmission later.
        ];

        // Load the PayPal RESTful API class and get the credentials, so we can make the postback using the current access token
        require DIR_WS_MODULES . 'payment/paypalr.php';
        list($client_id, $secret) = \paypalr::getEnvironmentInfo();
        $ppr = new PayPalRestfulApi(MODULE_PAYMENT_PAYPALR_SERVER, $client_id, $secret);

        // We pass true here because we can only get an access token if it is valid; else we must just say the webhook validation failed
        if ($ppr->validatePayPalCredentials(true) === false) {
            //$this->ppr_logger->write('PayPal credentials are invalid or token expired; cannot verify webhook by postback.', false, 'before');
            return null; // Unable to get a current access token.
        }

        // Now that the access token is confirmed, we submit this postback via CURL.
        $result = $ppr->webhookVerifyByPostback($params_array);

        return $result === true;
    }

    /**
     * This method is only used by the Postback verification method
     */
    protected function setWebhookSubscribeId()
    {
        if (defined('MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS')) {
            $this->webhook_listener_subscribe_id = MODULE_PAYMENT_PAYPALR_SUBSCRIBED_WEBHOOKS;
        }
    }

    /**
     * Read the cert via URL using file_get_contents or curl as fallback
     *
     * @param string $url
     * @return bool|string
     */
    protected function read_url($url)
    {
        // Try file_get_contents first
        if (ini_get('allow_url_fopen')) {
            $result = file_get_contents($url);
        }
        if (!empty($result)) {
            return $result;
        }
        // Fallback to curl
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'ZCPP WebhookResponder/1.0',
        ]);
        return curl_exec($ch);
    }

}
