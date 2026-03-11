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

class PaymentCapturePending extends WebhookHandlerContract
{
    protected $eventsHandled = [
        'PAYMENT.CAPTURE.PENDING',
    ];

    public function action()
    {
        // The state of a payment capture changes to pending
        // https://developer.paypal.com/docs/api/payments/v2/#authorizations_get - Show details for authorized payment with response `status` of `pending`.
        // Add an order-status record saying that PayPal has marked the capture as "pending" (which means they're waiting for the payment to be approved)

        $this->log->write('PAYMENT.CAPTURE.PENDING - action() triggered');

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
            "Notice: CAPTURE PENDING. Trans ID: $txnID \n" .
            "Amount: $amount\n$summary\n";

        // Save update without notifying customer, and without updating actual status
        zen_update_orders_history($oID, $comments, 'webhook', -1, 0);

        // Notify merchant via email
        $this->paymentModule->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN, $comments . "\n" .
            sprintf(MODULE_PAYMENT_PAYPALR_ALERT_ORDER_CREATION, $oID, $this->data['resource']['status'])
        );
    }
}

/*
{
  "id": "WH-9Y180613C5171350R-3A568107UP261041K",
  "create_time": "2018-08-15T20:03:06.086Z",
  "resource_type": "capture",
  "event_type": "PAYMENT.CAPTURE.PENDING",
  "summary": "Payment pending for $ 2.51 USD",
  "resource": {
    "amount": {
      "currency_code": "USD",
      "value": "2.51"
    },
    "seller_protection": {
      "status": "NOT_ELIGIBLE"
    },
    "update_time": "2018-08-15T20:02:40Z",
    "create_time": "2018-08-15T20:02:40Z",
    "final_capture": true,
    "links": [
      {
        "href": "https://api.paypal.com/v2/payments/captures/02T21492PP3782704",
        "rel": "self",
        "method": "GET"
      },
      {
        "href": "https://api.paypal.com/v2/payments/captures/02T21492PP3782704/refund",
        "rel": "refund",
        "method": "POST"
      },
      {
        "href": "https://api.paypal.com/v2/checkout/orders/8PR65097T8571330M",
        "rel": "up",
        "method": "GET"
      }
    ],
    "id": "02T21492PP3782704",
    "status_details": {
      "reason": "UNILATERAL"
    },
    "status": "PENDING"
  },
  "links": [
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-9Y180613C5171350R-3A568107UP261041K",
      "rel": "self",
      "method": "GET",
      "encType": "application/json"
    },
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-9Y180613C5171350R-3A568107UP261041K/resend",
      "rel": "resend",
      "method": "POST",
      "encType": "application/json"
    }
  ],
  "event_version": "1.0",
  "resource_version": "2.0"
}
 */
