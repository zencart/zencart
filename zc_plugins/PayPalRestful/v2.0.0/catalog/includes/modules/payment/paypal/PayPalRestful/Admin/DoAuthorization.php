<?php
/**
 * A class that provides the actions needed to authorize a payment for an order placed with
 * the PayPal Restful payment module.
 *
 * @copyright Copyright 2023-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 *
 * Last updated: v1.0.0
 */
namespace PayPalRestful\Admin;

use PayPalRestful\Admin\GetPayPalOrderTransactions;
use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Common\Helpers;
use PayPalRestful\Zc2Pp\Amount;

class DoAuthorization
{
    public function __construct(int $oID, PayPalRestfulApi $ppr, string $module_name, string $module_version)
    {
        global $db, $messageStack;

        if (!isset($_POST['ppr-amount'], $_POST['doAuthOid'], $_POST['auth_txn_id']) || $oID !== (int)$_POST['doAuthOid']) {
            $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALR_REAUTH_PARAM_ERROR, 1), 'error');
            return;
        }

        $ppr_txns = new GetPayPalOrderTransactions($module_name, $module_version, $oID, $ppr);
        $ppr_db_txns = $ppr_txns->getDatabaseTxns('AUTHORIZE');
        if (count($ppr_db_txns) === 0) {
            $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALR_NO_RECORDS, 'AUTHORIZE', $oID), 'error');
            return;
        }

        $auth_id_txn = false;
        foreach ($ppr_db_txns as $next_txn) {
            if ($next_txn['txn_id'] === $_POST['auth_txn_id']) {
                $auth_id_txn = $next_txn;
                break;
            }
        }
        if ($auth_id_txn === false) {
            $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALR_REAUTH_PARAM_ERROR, 2), 'error');
            return;
        }

        $auth_currency = $auth_id_txn['mc_currency'];
        $amount = new Amount($auth_currency);
        $auth_amount = $amount->getValueFromString($_POST['ppr-amount']);

        $auth_response = $ppr->reAuthorizePayment($_POST['auth_txn_id'], $auth_currency, $auth_amount);
        if ($auth_response === false) {
            $error_info = $ppr->getErrorInfo();
            $issue = $error_info['details'][0]['issue'] ?? '';
            switch ($issue) {
                case 'REAUTHORIZATION_TOO_SOON':
                    $error_message = MODULE_PAYMENT_PAYPALR_REAUTH_TOO_SOON;
                    break;
                default:
                    $error_message = MODULE_PAYMENT_PAYPALR_REAUTH_ERROR . "\n" . json_encode($error_info);
                    break;
            }
            $messageStack->add_session($error_message, 'error');
            return;
        }

        $ppr_txns->addDbTransaction('AUTHORIZE', $auth_response);
        $ppr_txns->updateMainTransaction($auth_response);

        // -----
        // A re-authorization transaction, for whatever reason, doesn't return its 'parent'
        // transaction id (the authorization just updated) in its response.  To keep the
        // parent/child chain valid in the database, update the just-created re-authorization
        // to reflect its parent authorization.
        //
        $db->Execute(
            "UPDATE " . TABLE_PAYPAL . "
                SET parent_txn_id = '" . $_POST['auth_txn_id'] . "'
              WHERE txn_id = '" . $auth_response['id'] . "'
              LIMIT 1"
        );

        // -----
        // A re-authorization doesn't change an order's status.  Write an orders-history
        // record containing information for the admin's hidden view.
        //
        $amount = $auth_response['amount']['value'] . ' ' . $auth_response['amount']['currency_code'];
        $comments =
            'AUTHORIZATION ADDED. Trans ID: ' . $auth_response['id'] . "\n" .
            'Amount: ' . $amount;
        zen_update_orders_history($oID, $comments);

        $messageStack->add_session(sprintf(MODULE_PAYMENT_PAYPALR_REAUTH_COMPLETE, $amount), 'success');
    }
}
