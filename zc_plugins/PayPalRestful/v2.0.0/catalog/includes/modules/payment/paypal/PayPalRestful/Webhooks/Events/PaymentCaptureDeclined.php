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

class PaymentCaptureDeclined extends WebhookHandlerContract
{
    protected $eventsHandled = [
        'PAYMENT.CAPTURE.DECLINED',
    ];

    public function action()
    {
        // A payment capture is declined.
        // https://developer.paypal.com/docs/api/payments/v2/#authorizations_capture - with response `status` of `declined`

        $this->log->write('PAYMENT.CAPTURE.DECLINED - action() triggered');

        // Instantiate paypalr module to load its language strings for status messages
        $this->loadCorePaymentModuleAndLanguageStrings();

        $txnID = $this->data['resource']['supplementary_data']['related_ids']['order_id'] ?? null;
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
        $amount = $this->data['resource']['amount']['value'];
        $comments =
            "Notice: CAPTURE DECLINED. Trans ID: $txnID \n" .
            "Amount: $amount\n$summary\n";

        $admin_message = MODULE_PAYMENT_PAYPALR_CAPTURE_ERROR;
        $status = (int)MODULE_PAYMENT_PAYPALR_VOIDED_STATUS_ID;
        $status = ($status > 0) ? $status : 1;

        // Save update without notifying customer
        zen_update_orders_history($oID, $comments, 'webhook', $status, 0);

        // Notify merchant via email
        zen_update_orders_history($oID, $admin_message, 'webhook', -1, -2);
        $this->paymentModule->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN, $comments . "\n" .
            sprintf(MODULE_PAYMENT_PAYPALR_ALERT_ORDER_CREATION, $oID, $this->data['resource']['status'])
        );
    }
}

/*
{
    "id": "WH-6HE329230C693231F-5WV60586YA659351G",
    "event_version": "1.0",
    "create_time": "2022-12-13T19:13:07.251Z",
    "resource_type": "capture",
    "resource_version": "2.0",
    "event_type": "PAYMENT.CAPTURE.DECLINED",
    "summary": "A payment capture for $ 185.1 USD was declined.",
    "resource": {
        "id": "7U133281TB3277326",
        "amount": {
            "currency_code": "USD",
            "value": "185.10"
        },
        "final_capture": false,
        "seller_protection": {
            "status": "ELIGIBLE",
            "dispute_categories": [
                "ITEM_NOT_RECEIVED",
                "UNAUTHORIZED_TRANSACTION"
            ]
        },
        "disbursement_mode": "INSTANT",
        "seller_receivable_breakdown": {
            "gross_amount": {
                "currency_code": "USD",
                "value": "185.10"
            },
            "platform_fees": [
                {
                    "amount": {
                        "currency_code": "USD",
                        "value": "0.50"
                    },
                    "payee": {
                        "merchant_id": "QG3ECYYLJ2A48"
                    }
                }
            ],
            "net_amount": {
                "currency_code": "USD",
                "value": "184.60"
            },
            "receivable_amount": {
                "currency_code": "EUR",
                "value": "115.98"
            },
            "exchange_rate": {
                "source_currency": "USD",
                "target_currency": "EUR",
                "value": "0.628281035098039"
            }
        },
        "invoice_id": "ARG0-2022-12-08T21:00:21.564Z-435",
        "custom_id": "CUSTOMID-1001",
        "status": "DECLINED",
        "supplementary_data": {
            "related_ids": {
                "order_id": "48R416400V564864N",
                "authorization_id": "24B76447NN600461P"
            }
        },
        "create_time": "2022-12-13T19:13:00Z",
        "update_time": "2022-12-13T19:13:00Z",
        "links": [
            {
                "href": "https:\/\/api.paypal.com\/v2\/payments\/captures\/7U133281TB3277326",
                "rel": "self",
                "method": "GET"
            },
            {
                "href": "https:\/\/api.paypal.com\/v2\/payments\/captures\/7U133281TB3277326\/refund",
                "rel": "refund",
                "method": "POST"
            },
            {
                "href": "https:\/\/api.paypal.com\/v2\/payments\/authorizations\/24B76447NN600461P",
                "rel": "up",
                "method": "GET"
            }
        ]
    }
}
 */
