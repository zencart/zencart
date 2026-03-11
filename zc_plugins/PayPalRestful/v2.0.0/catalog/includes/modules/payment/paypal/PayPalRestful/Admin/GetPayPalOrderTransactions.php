<?php
/**
 * A class that returns an array of 'current' transactions for a specified order for
 * Cart processing for the PayPal Restful payment module's admin_notifications processing.
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Last updated: v1.3.0
 */
namespace PayPalRestful\Admin;

use PayPalRestful\Admin\Formatters\Messages;
use PayPalRestful\Admin\Formatters\NullMessages;
use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Common\Helpers;
use PayPalRestful\Common\Logger;

class GetPayPalOrderTransactions
{
    protected $moduleName;

    protected $moduleVersion;

    protected $oID;

    protected $ppr;

    protected $log;

    protected $databaseTxns = [];

    protected $messages;

    protected $paymentType;

    protected $paypalTransactions = [];

    protected $externalTxnAdded = false;

    public function __construct(string $module_name, string $module_version, int $oID, PayPalRestfulApi $ppr)
    {
        $this->moduleName = $module_name;
        $this->moduleVersion = $module_version;
        $this->oID = $oID;
        $this->ppr = $ppr;

        $this->log = new Logger();

        if (\IS_ADMIN_FLAG === true) {
            $this->messages = new Messages();
        } else {
            // Use a null-object class to avoid errors where calling messageStack methods
            // may trigger problems where no messages are actually needed such as webhooks
            $this->messages = new NullMessages();
        }

        $this->getPayPalDatabaseTransactionsForOrder();
    }

    public function externalTxnAdded(): bool
    {
        return $this->externalTxnAdded;
    }

    public function getDatabaseTxns(string $txn_type = ''): array
    {
        if ($txn_type === '') {
            return $this->databaseTxns;
        }

        $database_txns = [];
        foreach ($this->databaseTxns as $next_db_txn) {
            if ($next_db_txn['txn_type'] === $txn_type) {
                $database_txns[] = $next_db_txn;
            }
        }
        return $database_txns;
    }

    public function getInvoiceId(): string
    {
        $main_txn = $this->getDatabaseTxns('CREATE');
        return (string)$main_txn[0]['invoice'];
    }

    /**
     * Update paypal db table with latest updates for this order
     * by retrieving entire order history from PayPal
     */
    public function syncPaypalTxns()
    {
        $this->getPayPalUpdates();

        if ($this->externalTxnAdded === true) {
            $this->messages->add(MODULE_PAYMENT_PAYPALR_EXTERNAL_ADDITION, 'warning');
        }

        if (!\property_exists($this->messages, 'size') || $this->messages->size !== 0) {
            $this->getPayPalDatabaseTransactionsForOrder();
        }
    }

    public function getMessages(): string
    {
        return $this->messages->output();
    }

    protected function getPayPalDatabaseTransactionsForOrder()
    {
        global $db;

        // -----
        // Grab the transactions for the current order from the database.  The
        // original order (with a txn_type of "CREATE") is always displayed first;
        // the remaining ones are included in txn_type/parent_txn_id order.
        //
        $txns = $db->ExecuteNoCache(
            "SELECT *
               FROM " . TABLE_PAYPAL . "
              WHERE order_id = {$this->oID}
                AND module_name = '{$this->moduleName}'
              ORDER BY
                CASE txn_type
                    WHEN 'CREATE' THEN -1
                    WHEN 'AUTHORIZE' THEN 0
                    WHEN 'CAPTURE' THEN 1
                    ELSE 2
                END ASC, date_added ASC"
        );

        // -----
        // Now, sort those transactions so that the REFUNDs and VOIDs are
        // located *after* their parent CAPTURE/AUTHORIZE.
        //
        $db_txns = [];
        foreach ($txns as $txn) {
            // -----
            // 
            //
            if ($txn['txn_type'] === 'CREATE') {
                $db_txns[] = $txn;
                continue;
            }

            // -----
            // Credit: https://stackoverflow.com/questions/3797239/insert-new-item-in-array-on-any-position-in-php
            //
            $parent_txn_id = $txn['parent_txn_id'];
            for ($position = 0, $txn_count = count($db_txns); $position < $txn_count; $position++) {
                if ($db_txns[$position]['txn_id'] === $parent_txn_id) {
                    $position_after = $position + 1;
                    $db_txns = array_merge(
                        array_slice($db_txns, 0, $position_after),
                        [$position_after => $txn],
                        array_slice($db_txns, $position_after)
                    );
                    break;
                }
            }
            if ($position === $txn_count) {
                $db_txns[] = $txn;
            }
        }
        $this->databaseTxns = $db_txns;
    }

    protected function getPayPalUpdates()
    {
        // -----
        // If no database transactions were found for the current order, nothing
        // further to be done here.
        //
        if (empty($this->databaseTxns[0]['txn_id'])) {
            return;
        }

        // -----
        // Retrieve the current status information for the primary/order transaction
        // from PayPal.
        //
        $primary_txn_id = $this->databaseTxns[0]['txn_id'];
        $txns = $this->ppr->getOrderStatus($primary_txn_id);
        if ($txns === false) {
            $error_info = $this->ppr->getErrorInfo();
            if ($error_info['name'] !== 'RESOURCE_NOT_FOUND') {
                $this->messages->add(MODULE_PAYMENT_PAYPALR_TEXT_GETDETAILS_ERROR . "\n" . Logger::logJSON($error_info), 'error');
            }
            return;
        }
        $this->paypalTransactions = $txns;

        // -----
        // Determine the type of payment, e.g. 'paypal' vs. 'card', associated with this order.
        //
        $this->paymentType = array_key_first($txns['payment_source']);

        // -----
        // The primary (initially-created) transaction has already been recorded in the
        // database.  Loop through the 'payments' applied to this transaction, updating
        // the database with any that are missing as an order might have been updated in
        // the store's PayPal Management Console.
        //
        $authorizations = [];
        $captures = [];
        foreach ($txns['purchase_units'][0]['payments'] as $record_type => $child_txns) {
            switch ($record_type) {
                case 'authorizations':
                    $authorizations = $child_txns;
                    $this->updateAuthorizations($authorizations);
                    break;
                case 'captures':
                    $captures = $child_txns;
                    $this->updateCaptures($captures, $authorizations);
                    break;
                case 'refunds':
                    $this->updateRefunds($child_txns, $captures);
                    break;
                default:
                    $this->messages->add("Unknown payment record ($record_type) provided by PayPal.\n" . Logger::logJSON($child_txns, true), 'error');
                    break;
            }
        }
    }

    protected function updateAuthorizations(array $authorizations)
    {
        foreach ($authorizations as $next_authorization) {
            $authorization_txn_id = $next_authorization['id'];
            $first_auth_txn_id = $first_auth_txn_id ?? $authorization_txn_id;
            if ($this->transactionExists($authorization_txn_id) === true) {
                continue;
            }

            $this->externalTxnAdded = true;
            $this->addDbTransaction('AUTHORIZE', $next_authorization, 'Externally added.', true, $first_auth_txn_id);
            $this->updateMainTransaction($next_authorization);
        }
    }

    protected function updateCaptures(array $captures, array $authorizations)
    {
        foreach ($captures as $next_capture) {
            $capture_txn_id = $next_capture['id'];
            if ($this->transactionExists($capture_txn_id) === true) {
                continue;
            }

            $this->externalTxnAdded = true;
            $parent_txn_id = $authorizations[0]['id'];
            $this->addDbTransaction('CAPTURE', $next_capture, 'Externally added.', true, $parent_txn_id);
            $parent_txn_response = $this->getParentTxnStatus($parent_txn_id, $authorizations);
            if (!empty($parent_txn_response)) {
                $this->updateParentTxnDateAndStatus($parent_txn_response);
            }
            $this->updateMainTransaction($next_capture);
        }
    }

    protected function updateRefunds(array $refunds, array $captures)
    {
        foreach ($refunds as $next_refund) {
            $refund_txn_id = $next_refund['id'];
            if ($this->transactionExists($refund_txn_id) === true) {
                continue;
            }

            $this->externalTxnAdded = true;

            // -----
            // Need to get the refund's status to see what its parent capture's id is (from its links).
            //
            $refund_status = $this->ppr->getRefundStatus($refund_txn_id);
            $parent_txn_id = $this->addDbTransaction('REFUND', $refund_status, 'Externally added', true);
            $parent_txn_response = $this->getParentTxnStatus($parent_txn_id, $captures);
            if (!empty($parent_txn_response)) {
                $this->updateParentTxnDateAndStatus($parent_txn_response);
            }
            $this->updateMainTransaction($next_refund);
        }
    }

    protected function transactionExists(string $txn_id): bool
    {
        $txn_exists = false;
        foreach ($this->databaseTxns as $next_txn) {
            if ($next_txn['txn_id'] === $txn_id) {
                $txn_exists = true;
                break;
            }
        }
        return $txn_exists;
    }

    protected function getParentTxnStatus(string $txn_id, array $paypal_response): array
    {
        foreach ($paypal_response as $next_response) {
            if ($next_response['id'] === $txn_id) {
                return $next_response;
            }
        }
        return [];
    }

    public function addDbTransaction(string $txn_type, array $paypal_response, string $memo_comment = '', bool $keep_links_in_log = false, string $parent_txn_id = ''): string
    {
        $this->log->write("addDbTransaction($txn_type, ..., $memo_comment):\n" . Logger::logJSON($paypal_response, $keep_links_in_log));

        $date_added = Helpers::convertPayPalDatePay2Db($paypal_response['create_time']);

        $payment_info = $this->getPaymentInfo($paypal_response);
        if ($txn_type === 'CAPTURE' && count($payment_info) !== 0) {
            $payment_info['payment_date'] = $date_added;
        }

        $memo = [];
        if ($memo_comment !== '') {
            $memo['comment'] = $memo_comment;
        }

        $expiration_time = $paypal_response['expiration_time'] ?? 'null';
        if ($expiration_time !== 'null') {
            $expiration_time = Helpers::convertPayPalDatePay2Db($expiration_time);
        }

        $parent_txn_id = ($parent_txn_id === '') ? $this->getParentTxnFromResponse($paypal_response['links']) : $parent_txn_id;
        $sql_data_array = [
            'order_id' => $this->oID,
            'txn_type' => $txn_type,
            'final_capture' => (int)($paypal_response['final_capture'] ?? 0),
            'module_name' => $this->moduleName,
            'module_mode' => '',
            'payment_type' => $this->paymentType ?? $this->databaseTxns[0]['payment_type'],
            'payment_status' => $paypal_response['status'],
            'pending_reason' => $paypal_response['status_details']['reason'] ?? 'null',
            'mc_currency' => $paypal_response['amount']['currency_code'],
            'txn_id' => $paypal_response['id'],
            'parent_txn_id' => $parent_txn_id,
            'mc_gross' => $paypal_response['amount']['value'],
            'notify_version' => $this->moduleVersion,
            'date_added' => $date_added,
            'last_modified' => Helpers::convertPayPalDatePay2Db($paypal_response['update_time']),
            'expiration_time' => $expiration_time,
            'memo' => json_encode($memo),
        ];
        $sql_data_array = array_merge($sql_data_array, $payment_info);
        zen_db_perform(TABLE_PAYPAL, $sql_data_array);

        // -----
        // If funds have been captured or refunded, fire a notification so that sites that
        // manage payments are aware of the incoming/outgoing funds.
        //
        if ($txn_type === 'CAPTURE' || $txn_type === 'REFUND') {
            global $zco_notifier;
            $zco_notifier->notify('NOTIFY_PAYPALR_ADMIN_FUNDS_IN_OUT', $sql_data_array);
        }

        return $parent_txn_id;
    }

    protected function getParentTxnFromResponse(array $links): string
    {
        $parent_txn_id = '';
        foreach ($links as $next_link) {
            if ($next_link['rel'] === 'up') {
                $pieces = explode('/', $next_link['href']);
                $parent_txn_id = end($pieces);
                break;
            }
        }
        return $parent_txn_id;
    }

    public function updateParentTxnDateAndStatus(array $paypal_response)
    {
        $parent_txn_id = $paypal_response['id'];
        $sql_data_array = [
            'payment_status' => $paypal_response['status'],
            'pending_reason' => $paypal_response['status_details']['reason'] ?? 'null',
            'notify_version' => $this->moduleVersion,
            'last_modified' => Helpers::convertPayPalDatePay2Db($paypal_response['update_time']),
        ];
        zen_db_perform(TABLE_PAYPAL, $sql_data_array, 'update', "order_id={$this->oID} AND txn_id = '$parent_txn_id' LIMIT 1");
    }

    protected function getPaymentInfo(array $paypal_response): array
    {
        $payment_info = $paypal_response['seller_receivable_breakdown'] ?? $paypal_response['seller_payable_breakdown'] ?? [];
        if (count($payment_info) === 0) {
            return [];
        }

        return [
            'payment_gross' => $payment_info['gross_amount']['value'],
            'payment_fee' => $payment_info['paypal_fee']['value'],
            'settle_amount' => $payment_info['receivable_amount']['value'] ?? $payment_info['net_amount']['value'],
            'settle_currency' => $payment_info['receivable_amount']['currency_code'] ?? $payment_info['net_amount']['currency_code'],
            'exchange_rate' => $payment_info['exchange_rate']['value'] ?? 'null',
        ];
    }

    public function updateMainTransaction(array $paypal_response)
    {
        global $db;

        $modification_date = Helpers::convertPayPalDatePay2Db($paypal_response['update_time']);
        $db->Execute(
            "UPDATE " . TABLE_PAYPAL . "
                SET last_modified = '$modification_date',
                    notify_version = '" . $this->moduleVersion . "'
              WHERE order_id = {$this->oID}
                AND txn_type = 'CREATE'
              LIMIT 1"
        );
    }

    /**
     * Lookup Order ID using TxnID -- mostly used by Webhooks
     * @return int (0 if no match found)
     */
    public static function getOrderIdFromPayPalTxnId(string $txn_id): int
    {
        global $db;

        // if blank, skip db call
        if (empty($txn_id)) {
            return 0;
        }
        // sanitize
        $txn_id = $db->prepare_input($txn_id);

        $results = $db->ExecuteNoCache(
            "SELECT *
               FROM " . TABLE_PAYPAL . "
              WHERE txn_id = '$txn_id'
              ORDER BY
                CASE txn_type
                    WHEN 'CREATE' THEN -1
                    WHEN 'AUTHORIZE' THEN 0
                    WHEN 'CAPTURE' THEN 1
                    ELSE 2
                END ASC, date_added ASC"
        );

        if ($results->EOF) {
            return 0;
        }
        return (int)$results->fields['order_id'];
    }
}
