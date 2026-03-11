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

class PaymentCaptureReversed extends WebhookHandlerContract
{
    protected $eventsHandled = [
        'PAYMENT.CAPTURE.REVERSED',
    ];

    public function action()
    {
        // PayPal reverses a payment capture (not the merchant)
        // https://developer.paypal.com/docs/api/payments/v2/#captures_refund
        // Add an order-status record indicating that PayPal reversed the payment capture (refunded the payment), unbeknownst to the merchant

        $this->log->write('PAYMENT.CAPTURE.REVERSED - action() triggered');

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
        $admin_message = $this->data['summary'] . "\n" . $this->data['note_to_payer'];
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
  "id": "WH-6F207351SC284371F-0KX52201050121307",
  "create_time": "2018-08-15T21:30:35.780Z",
  "resource_type": "refund",
  "event_type": "PAYMENT.CAPTURE.REVERSED",
  "summary": "A $ 2.51 USD capture payment was reversed",
  "resource": {
    "seller_payable_breakdown": {
      "gross_amount": {
        "currency_code": "USD",
        "value": "2.51"
      },
      "paypal_fee": {
        "currency_code": "USD",
        "value": "0.00"
      },
      "net_amount": {
        "currency_code": "USD",
        "value": "2.51"
      },
      "total_refunded_amount": {
        "currency_code": "USD",
        "value": "2.51"
      }
    },
    "amount": {
      "currency_code": "USD",
      "value": "2.51"
    },
    "update_time": "2018-08-15T14:30:10-07:00",
    "create_time": "2018-08-15T14:30:10-07:00",
    "links": [
      {
        "href": "https://api.paypal.com/v2/payments/refunds/09E71677NS257044M",
        "rel": "self",
        "method": "GET"
      },
      {
        "href": "https://api.paypal.com/v2/payments/captures/4L335234718889942",
        "rel": "up",
        "method": "GET"
      }
    ],
    "id": "09E71677NS257044M",
    "note_to_payer": "Payment reversed",
    "status": "COMPLETED"
  },
  "links": [
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-6F207351SC284371F-0KX52201050121307",
      "rel": "self",
      "method": "GET",
      "encType": "application/json"
    },
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-6F207351SC284371F-0KX52201050121307/resend",
      "rel": "resend",
      "method": "POST",
      "encType": "application/json"
    }
  ],
  "event_version": "1.0",
  "resource_version": "2.0"
}
 */
