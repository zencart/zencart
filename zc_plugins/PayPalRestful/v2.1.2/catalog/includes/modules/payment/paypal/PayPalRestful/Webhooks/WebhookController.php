<?php
/**
 * PayPal REST API Webhook Controller
 * This controller parses the incoming webhook and brokers the
 * necessary steps for validation and dispatching based on the
 * nature of the webhook content.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2026 Mar 17 New in v2.2.1 $
 *
 * Last updated: v2.1.0
 */

namespace PayPalRestful\Webhooks;

use PayPalRestful\Common\Logger;

class WebhookController
{
    protected Logger $ppr_logger;

    public function __invoke(): bool|null
    {
        defined('TABLE_PAYPAL_WEBHOOKS') or define('TABLE_PAYPAL_WEBHOOKS', DB_PREFIX . 'paypal_webhooks');

        // Inspect and collect webhook details
        $request_method = $_SERVER['REQUEST_METHOD'];
        $request_headers = getallheaders() ?: [];
        $request_body = file_get_contents('php://input') ?: '';
        $json_body = json_decode($request_body, true);
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $event = $json_body['event_type'] ?? '(event not determined)';
        $summary = $json_body['summary'] ?? '(summary not determined)';
        $logIdentifier = $json_body['id'] ?? $json_body['event_type'] ?? '';

        // Create logger, just for logging to /logs directory
        $this->ppr_logger = new Logger($logIdentifier);

        // Enable logging, if enabled via configuration
        if (str_starts_with(zen_config('MODULE_PAYMENT_PAYPALR_DEBUGGING', 'Off'), 'Log')) {
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

        /**
         * Idempotency guard. PayPal delivers webhook events at-least-once
         * (it re-sends on timeout or any non-2xx response) and a signed payload can
         * be replayed verbatim, so the same event-id must not be processed twice.
         * Re-processing would create duplicate order-status records, and re-send
         * customer/merchant emails and re-fire the funds-captured notifier.
         *
         * The primary gate is the UNIQUE(webhook_id) constraint on the table.
         * We keep a pre-flight alreadyProcessed() SELECT as a fast-path for the
         * common case of sequential retries, then we do an atomic INSERT IGNORE:
         * for concurrent duplicate deliveries, exactly one INSERT succeeds and
         * the other is ignored (0 affected rows) and returns early.
         */
        $this->createDatabaseTable();
        $webhook_id = substr((string)($json_body['id'] ?? ''), 0, 64);
        if ($webhook_id !== '' && $this->alreadyProcessed($webhook_id)) {
            $this->ppr_logger->write("ppr_webhook DUPLICATE event ignored (webhook_id: $webhook_id).", false, 'before');
            return true;
        }

        /**
         * Log that we received a validated webhook; treat a duplicate-key error
         * (two simultaneous webhooks that both passed the SELECT above) as an
         * already-processed event.
         */
        if ($this->saveToDatabase($user_agent, $request_method, $request_body, $request_headers) === false) {
            $this->ppr_logger->write("ppr_webhook DUPLICATE event ignored on INSERT (webhook_id: $webhook_id).", false, 'before');
            return true;
        }

        // Now that verification has passed, dispatch the webhook according to the declared event_type
        return $this->dispatch($event, $webhook);
    }

    protected function dispatch(string $event, WebhookObject $webhook): bool
    {
        // Lookup class name
        $objectName = 'PayPalRestful\Webhooks\Events\\' . $this->strToStudly($event);

        if (class_exists($objectName)) {
            //debug: $this->ppr_logger->write('class found: ' . $objectName, false, 'before');

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
        $studlyWords = array_map(static fn($word) => mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($word, 1, null, 'UTF-8'), $words);
        return implode($studlyWords);
    }

    /**
     * Save webhook records to the database for subsequent querying
     *
     *  Returns true on success, false if the INSERT is ignored due to the
     *  UNIQUE(webhook_id) constraint — which means the event was already recorded
     *  by a concurrent delivery and the caller should treat it as a duplicate.
     */
    protected function saveToDatabase(string $user_agent, string $request_method, string $request_body, array $request_headers): bool
    {
        global $db;

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

        /**
         * Use INSERT IGNORE so a duplicate webhook_id (UNIQUE constraint) silently
         * fails instead of throwing a DB exception.  We detect the duplicate by
         * checking whether any row was actually inserted (affected_rows = 0 on skip).
         */
        $columns_sql = implode(', ', array_keys($sql_data_array));
        $values_sql  = implode(', ', array_map(
            static fn($v) => "'" . $db->prepare_input((string)$v) . "'",
            $sql_data_array
        ));
        $db->Execute("INSERT IGNORE INTO " . TABLE_PAYPAL_WEBHOOKS . " ($columns_sql) VALUES ($values_sql)");
        return $db->affectedRows() > 0;
    }

    /**
     * Determine whether a webhook event-id has already been recorded.
     *
     * Used for idempotency: PayPal re-delivers events and a signed payload can be
     * replayed, so the same event-id must not be processed twice. The caller is
     * responsible for ensuring the table exists (see createDatabaseTable()).
     */
    protected function alreadyProcessed(string $webhook_id): bool
    {
        global $db;

        $webhook_id = $db->prepare_input($webhook_id);
        $existing = $db->ExecuteNoCache(
            "SELECT id
               FROM " . TABLE_PAYPAL_WEBHOOKS . "
              WHERE webhook_id = '$webhook_id'
              LIMIT 1"
        );
        return !$existing->EOF;
    }

    /**
     * Ensure database table exists
     */
    protected function createDatabaseTable(): void
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
                UNIQUE KEY idx_pprwebhook_unique (webhook_id),
                KEY idx_pprwebhook_zen (webhook_id, id, created_at)
            )"
        );
    }
}
