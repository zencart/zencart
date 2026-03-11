<?php
/**
 * PayPal REST API Webhooks
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte June 2025 $
 *
 * Last updated: v1.3.0
 */

namespace PayPalRestful\Webhooks\Events;

use PayPalRestful\Admin\GetPayPalOrderTransactions;
use PayPalRestful\Webhooks\WebhookHandlerContract;

class PaymentCaptureCompleted extends WebhookHandlerContract
{
    protected $eventsHandled = [
        'PAYMENT.CAPTURE.COMPLETED',
    ];

    public function action()
    {
        // A payment capture completes
        // https://developer.paypal.com/docs/api/payments/v2/#authorizations_capture - with response `status` of `completed`

        $this->log->write('PAYMENT.CAPTURE.COMPLETED - action() triggered');

        // A payment capture can be triggered via the Store's Admin Orders page, or via the PayPal portal.
        // And it could complete "later", out-of-band, and not in-real-time.
        // Therefore we use the webhook to listen for when PayPal completes the capture, so we can update the order accordingly, if needed.

        // Ensure order's status is updated to reflect that payment has been captured
        // - look up order
        // - ensure it was paid via paypalr
        // - CHECK WHETHER WAS ALREADY CAPTURED, so we're not duplicating status records and notifier calls
        // - update payment status, including a note with any safe-to-share info from webhook


        // Instantiate paypalr module to load its language strings for status messages
        $this->loadCorePaymentModuleAndLanguageStrings();

        $txnID = $this->data['resource']['id'] ?? null;
        $oID = GetPayPalOrderTransactions::getOrderIdFromPayPalTxnId($txnID);

        if (empty($oID)) {
            $this->log->write("\n\n---\nNOTICE: Order ID lookup returned no results.\n\n");
            return;
        }

        // Sync our database with all updates from PayPal
        $this->getApiAndCredentials();
        $ppr_txns = new GetPayPalOrderTransactions($this->paymentModule->code, $this->paymentModule->getCurrentVersion(), $oID, $this->ppr);
        $ppr_txns->syncPaypalTxns();

        // Update order-status records noting what's happened
        $summary = $this->data['summary'];

        // @TODO check status: was it already captured previously (according to our internal records)? if yes, abort to prevent duplications

        $amount = $this->data['resource']['amount']['value'];
        $comments =
            "Notice: FUNDS CAPTURED. Trans ID: $txnID \n" .
            "Amount: $amount\n$summary\n";

        if ($this->data['resource']['final_capture'] === false) {
            $admin_message = MODULE_PAYMENT_PAYPALR_PARTIAL_CAPTURE;
            $status = -1;
        } else {
            $admin_message = MODULE_PAYMENT_PAYPALR_FINAL_CAPTURE;
            $status = (int)MODULE_PAYMENT_PAYPALR_ORDER_STATUS_ID;
            $status = ($status > 0) ? $status : 2;
        }

        // Save update without notifying customer
        zen_update_orders_history($oID, $comments, 'webhook', $status, 0);

        // Notify merchant via email
        zen_update_orders_history($oID, $admin_message, 'webhook', -1, -2);
        $this->paymentModule->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN, $comments . "\n" .
            sprintf(MODULE_PAYMENT_PAYPALR_ALERT_ORDER_CREATION, $oID, $this->data['resource']['status'])
        );

        // @TODO - is this risking duplication if the order was already captured in-real-time?
        // If funds have been captured, fire a notification so that sites that
        // manage payments are aware of the incoming funds.
        //
        global $zco_notifier;
        $zco_notifier->notify('NOTIFY_PAYPALR_FUNDS_CAPTURED', ['webhook' => $this->data]);
    }
}


/*
{
    "id": "WH-7Y7254563A4550640-11V2185806837105M",
    "event_version": "1.0",
    "create_time": "2015-02-17T18:51:33Z",
    "resource_type": "capture",
    "resource_version": "2.0",
    "event_type": "PAYMENT.CAPTURE.COMPLETED",
    "summary": "Payment completed for $ 57.0 USD",
    "resource": {
        "id": "42311647XV020574X",
        "amount": {
            "currency_code": "USD",
            "value": "57.00"
        },
        "final_capture": true,
        "seller_protection": {
            "status": "ELIGIBLE",
            "dispute_categories": [
                "ITEM_NOT_RECEIVED",
                "UNAUTHORIZED_TRANSACTION"
            ]
        },
        "disbursement_mode": "DELAYED",
        "seller_receivable_breakdown": {
            "gross_amount": {
                "currency_code": "USD",
                "value": "57.00"
            },
            "paypal_fee": {
                "currency_code": "USD",
                "value": "2.48"
            },
            "platform_fees": [
                {
                    "amount": {
                        "currency_code": "USD",
                        "value": "5.13"
                    },
                    "payee": {
                        "merchant_id": "CDF4K6247RPFF"
                    }
                }
            ],
            "net_amount": {
                "currency_code": "USD",
                "value": "49.39"
            }
        },
        "invoice_id": "3942613:fav09c49-a3g6-4cbf-1358-f6d241dacea2",
        "custom_id": "d93e4fce-d3af-137c-82fe-1a8101f1ad11",
        "status": "COMPLETED",
        "supplementary_data": {
            "related_ids": {
                "order_id": "8U481631H66031715"
            }
        },
        "create_time": "2022-08-26T18:29:50Z",
        "update_time": "2022-08-26T18:29:50Z",
        "links": [
            {
                "href": "https:\/\/api.paypal.com\/v2\/payments\/captures\/0KF12345VG343800K",
                "rel": "self",
                "method": "GET"
            },
            {
                "href": "https:\/\/api.paypal.com\/v2\/payments\/captures\/0KF12345VG343880K\/refund",
                "rel": "refund",
                "method": "POST"
            },
            {
                "href": "https:\/\/api.paypal.com\/v2\/checkout\/orders\/8U431637H66031715",
                "rel": "up",
                "method": "GET"
            }
        ]
    }
}
 */
