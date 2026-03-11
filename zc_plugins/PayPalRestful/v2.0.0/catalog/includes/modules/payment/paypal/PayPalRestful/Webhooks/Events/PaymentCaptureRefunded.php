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

class PaymentCaptureRefunded extends WebhookHandlerContract
{
    protected $eventsHandled = [
        'PAYMENT.CAPTURE.REFUNDED',
    ];

    public function action()
    {
        // A merchant refunds a payment capture.
        // https://developer.paypal.com/docs/api/payments/v2/#authorizations_capture - Show details for authorized payment with response `status` of `refunded`.

        $this->log->write('PAYMENT.CAPTURE.REFUNDED - action() triggered');


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
        $amount = $this->data['resource']['amount']['value'];
        $comments =
            "Notice: REFUNDED/REVERSED. Trans ID: $txnID \n" .
            "Amount: $amount\n$summary\n";
        $admin_message = sprintf(MODULE_PAYMENT_PAYPALR_REFUND_COMPLETE, $amount);
        $status = (int)MODULE_PAYMENT_PAYPALR_REFUNDED_STATUS_ID;
        $status = ($status > 0) ? $status : 1;

        // Save update and notify customer
        zen_update_orders_history($oID, $comments, 'webhook', $status, 1);

        // Notify merchant via email
        zen_update_orders_history($oID, $admin_message, 'webhook', -1, -2);
        $this->paymentModule->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN, $comments . "\n" .
            sprintf(MODULE_PAYMENT_PAYPALR_ALERT_ORDER_CREATION, $oID, $this->data['resource']['status'])
        );
    }
}

/*
{
  "id": "WH-1GE84257G0350133W-6RW800890C634293G",
  "create_time": "2018-08-15T19:14:04.543Z",
  "resource_type": "refund",
  "event_type": "PAYMENT.CAPTURE.REFUNDED",
  "summary": "A $ 0.99 USD capture payment was refunded",
  "resource": {
    "seller_payable_breakdown": {
      "gross_amount": {
        "currency_code": "USD",
        "value": "0.99"
      },
      "paypal_fee": {
        "currency_code": "USD",
        "value": "0.02"
      },
      "net_amount": {
        "currency_code": "USD",
        "value": "0.97"
      },
      "total_refunded_amount": {
        "currency_code": "USD",
        "value": "1.98"
      }
    },
    "amount": {
      "currency_code": "USD",
      "value": "0.99"
    },
    "update_time": "2018-08-15T12:13:29-07:00",
    "create_time": "2018-08-15T12:13:29-07:00",
    "links": [
      {
        "href": "https://api.paypal.com/v2/payments/refunds/1Y107995YT783435V",
        "rel": "self",
        "method": "GET"
      },
      {
        "href": "https://api.paypal.com/v2/payments/captures/0JF852973C016714D",
        "rel": "up",
        "method": "GET"
      }
    ],
    "id": "1Y107995YT783435V",
    "status": "COMPLETED"
  },
  "links": [
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-1GE84257G0350133W-6RW800890C634293G",
      "rel": "self",
      "method": "GET",
      "encType": "application/json"
    },
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-1GE84257G0350133W-6RW800890C634293G/resend",
      "rel": "resend",
      "method": "POST",
      "encType": "application/json"
    }
  ],
  "event_version": "1.0",
  "resource_version": "2.0"
}
 */
