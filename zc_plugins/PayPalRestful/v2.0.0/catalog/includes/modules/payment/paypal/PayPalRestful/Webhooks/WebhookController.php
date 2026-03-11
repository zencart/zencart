<?php
/**
 * PayPal REST API Webhook Controller
 * This controller parses the incoming webhook and brokers the
 * necessary steps for validation and dispatching based on the
 * nature of the webhook content.
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte June 2025 $
 *
 * Last updated: v1.2.2/v1.3.0
 */

namespace PayPalRestful\Webhooks;

use PayPalRestful\Common\Logger;

class WebhookController
{
    protected $ppr_logger;

    public function __invoke()
    {
        defined('TABLE_PAYPAL_WEBHOOKS') or define('TABLE_PAYPAL_WEBHOOKS', DB_PREFIX . 'paypal_webhooks');

        // Inspect and collect webhook details
        $request_method = $_SERVER['REQUEST_METHOD'];
        $request_headers = getallheaders();
        $request_body = file_get_contents('php://input');
        $json_body = json_decode($request_body, true);
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $event = $json_body['event_type'] ?? '(event not determined)';
        $summary = $json_body['summary'] ?? '(summary not determined)';
        $logIdentifier = $json_body['id'] ?? $json_body['event_type'] ?? '';

        // Create logger, just for logging to /logs directory
        $this->ppr_logger = new Logger($logIdentifier);

        // Enable logging, if enabled via configuration
        if (strpos(MODULE_PAYMENT_PAYPALR_DEBUGGING, 'Log') === 0) {
            $this->ppr_logger->enableDebug();
        }

        // log that we got an incoming webhook, and its details
        $this->ppr_logger->write("ppr_webhook ($event, $user_agent, $request_method) starts.\n" . Logger::logJSON($json_body), true);

        // set object, which will be used for validation and for dispatching
        $webhook = new WebhookObject($request_method, $request_headers, $request_body, $user_agent);

        // prepare for verification
        $verifier = new WebhookResponder($webhook);

        // Ensure that the incoming request contains headers etc relevant to PayPal
        if (!$verifier->shouldRespond()) {
            $this->ppr_logger->write('ppr_webhook IGNORED DUE TO HEADERS MISMATCH' . "\n" . print_r($request_headers, true), false, 'before');
            return false;
        }

        // Verify that the webhook's signature is valid, to avoid spoofing and fraud, and wasted processing cycles
        $status = $verifier->verify();

        if ($status === null) {
            // For future dev: null means this webhook handler should be ignored, and go to next one
            // Probably this logic would be in a loop of classes being iterated, and would respond null to loop to the next one.
            return null;
        }

        // This should never happen, but we must abort if verification fails.
        if ($status === false) {
            $this->ppr_logger->write('ppr_webhook FAILED VERIFICATION', false, 'before');
            // The verifier already sent an HTTP response, so we just exit here by returning false to the ppr_webhook handler script.
            return false;
        }

        $this->ppr_logger->write("\n\n" . 'webhook verification passed', false, 'before');

        // Log that we received a validated webhook
        $this->saveToDatabase($user_agent, $request_method, $request_body, $request_headers);

        // Now that verification has passed, dispatch the webhook according to the declared event_type
        return $this->dispatch($event, $webhook);
    }

    protected function dispatch(string $event, WebhookObject $webhook): bool
    {
        // Lookup class name
        $objectName = 'PayPalRestful\Webhooks\Events\\' . $this->strToStudly($event);

        if (class_exists($objectName)) {
//debug:    $this->ppr_logger->write('class found: ' . $objectName, false, 'before');

            $call = new $objectName($webhook);
            if ($call->eventTypeIsSupported()) {
                $this->ppr_logger->write("\n\n" . 'webhook event supported by ' . $objectName . "\n", false, 'before');

                // dispatch to take the necessary action for the webhook
                $call->action();

                return true;
            }
        }
        $this->ppr_logger->write('class NOT found: ' . $objectName, false, 'before');
        return false;
    }

    /**
     * Convert string to Studly/CamelCase, using space, dot, hyphen, underscore as word break indicators
     */
    protected function strToStudly(string $value, array $dividers = ['.', '-', '_']): string
    {
        $words = explode(' ', str_replace($dividers, ' ', strtolower($value)));
        $studlyWords = array_map(static function ($word) { return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($word, 1, null, 'UTF-8'); }, $words);
        return implode($studlyWords);
    }

    /**
     * Save webhook records to database for subsequent querying
     */
    protected function saveToDatabase(string $user_agent, string $request_method, string $request_body, $request_headers)
    {
        $json_body = json_decode($request_body, true);

        $sql_data_array = [
            'webhook_id' => substr($json_body['id'] ?? '(webhook id not determined)', 0, 64),
            'event_type' => substr($json_body['event_type'] ?? '(event not determined)', 0, 64),
            'user_agent' => substr($user_agent, 0, 192),
            'request_method' => substr($request_method, 0, 32),
            'request_headers' => \json_encode($request_headers ?? []),
            'body' => $request_body,
        ];

        // ensure table exists
        $this->createDatabaseTable();

        // store
        zen_db_perform(TABLE_PAYPAL_WEBHOOKS, $sql_data_array);
    }

    /**
     * Ensure database table exists
     */
    protected function createDatabaseTable()
    {
        global $db;
        $db->Execute(
            "CREATE TABLE IF NOT EXISTS " . TABLE_PAYPAL_WEBHOOKS . " (
                id BIGINT NOT NULL AUTO_INCREMENT,
                webhook_id VARCHAR(64) NOT NULL,
                event_type VARCHAR(64) DEFAULT NULL,
                body LONGTEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                user_agent VARCHAR(192) DEFAULT NULL,
                request_method VARCHAR(32) DEFAULT NULL,
                request_headers TEXT DEFAULT NULL,
                PRIMARY KEY (id),
                KEY idx_pprwebhook_zen (webhook_id, id, created_at)
            )"
        );
    }
}
