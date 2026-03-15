<?php
/**
 * A class that provides the actions needed to void an order placed with
 * the PayPal Restful payment module.
 *
 * @copyright Copyright 2023-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 *
 * Last updated: v1.0.2
 */
namespace PayPalRestful\Admin;

use PayPalRestful\Admin\GetPayPalOrderTransactions;
use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Common\Helpers;

class DoVoid
{
    public function __construct(int $oID, PayPalRestfulApi $ppr, string $module_name, string $module_version)
    {
        global $db, $messageStack;

        if (!isset($_POST['ppr-void-id'], $_POST['doVoidOid'], $_POST['ppr-void-note']) || $oID !== (int)$_POST['doVoidOid']) {
            $messageStack->add_session(MODULE_PAYMENT_PAYPALR_VOID_PARAM_ERROR, 'error');
            return;
        }

        // -----
        // The order needs to have at least one authorization to be voided.
        //
        $ppr_txns = new GetPayPalOrderTransactions($module_name, $module_version, $oID, $ppr);
        $ppr_db_txns = $ppr_txns->getDatabaseTxns('AUTHORIZE');
        if (count($ppr_db_txns) === 0) {
            $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALR_NO_RECORDS, 'AUTHORIZE', $oID), 'error');
            return;
        }

        // -----
        // Only the primary authorization (when the order was initially created) can be voided and
        // its transaction-id must match that entered for the void.
        //
        $main_txn = $ppr_txns->getDatabaseTxns('CREATE');
        $auth_id_txn = false;
        $auth_txn_parent_id = '';
        foreach ($ppr_db_txns as $next_txn) {
            if ($next_txn['txn_id'] === $_POST['ppr-void-id']) {
                $auth_id_txn = $next_txn;
                $auth_txn_parent_id = $next_txn['parent_txn_id'];
                break;
            }
        }
        if ($auth_id_txn === false || $auth_txn_parent_id !== $main_txn[0]['txn_id']) {
            $messageStack->add_session(MODULE_PAYMENT_PAYPALR_VOID_BAD_AUTH_ID, 'error');
            return;
        }

        $void_response = $ppr->voidPayment($_POST['ppr-void-id']);
        if ($void_response === false) {
             $messageStack->add_session(MODULE_PAYMENT_PAYPALR_VOID_ERROR . "\n" . json_encode($ppr->getErrorInfo()), 'error');
             return;
        }

        // -----
        // Note: An authorization void returns *no additional information*, with a 204 http-code.
        // Simply update this authorization's status to indicate that it's been voided and update
        // the main transaction's status as well.
        //
        $modification_date = Helpers::convertPayPalDatePay2Db($void_response['update_time']);
        $db->Execute(
            "UPDATE " . TABLE_PAYPAL . "
                SET last_modified = '$modification_date',
                    payment_status = 'VOIDED',
                    notify_version = '" . $module_version . "'
              WHERE paypal_ipn_id = " . $auth_id_txn['paypal_ipn_id'] . "
                 OR (order_id = $oID AND txn_type = 'CREATE')"
        );

        // -----
        // The order's status is unchanged if previous captures have been performed,
        // otherwise, goes to 'voided'.
        //
        $captured_txns = $ppr_txns->getDatabaseTxns('CAPTURE');
        if (count($captured_txns) !== 0) {
            $voided_status = -1;
        } else {
            $voided_status = (int)MODULE_PAYMENT_PAYPALR_VOIDED_STATUS_ID;
            $voided_status = ($voided_status > 0) ? $voided_status : 1;
        }

        $comments =
            'VOIDED. Trans ID: ' . $auth_id_txn['paypal_ipn_id'] . "\n" .
            strip_tags($_POST['ppr-void-note']);
        zen_update_orders_history($oID, $comments, null, $voided_status, 0);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALR_VOID_COMPLETE, $oID), 'warning');
    }
}
