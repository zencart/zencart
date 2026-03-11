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
use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Webhooks\WebhookHandlerContract;

class CheckoutPaymentApprovalReversed extends WebhookHandlerContract
{
    protected $eventsHandled = [
        'CHECKOUT.PAYMENT-APPROVAL.REVERSED',
    ];

    public function action()
    {
        // A problem occurred after the buyer approved the order but before you captured the payment.
        // https://developer.paypal.com/docs/api/orders/v2/

        $this->log->write('CHECKOUT.PAYMENT-APPROVAL.REVERSED - action() triggered');


        // Refer to Handle uncaptured payments for what to do when this event occurs
        // https://developer.paypal.com/docs/checkout/apm/reference/handle-uncaptured-payments/

        // When a transaction is not captured within a specified amount of time
        // after the buyer approves it through the payment method, PayPal sends
        // CHECKOUT.PAYMENT-APPROVAL.REVERSED webhook event, initiates a cancellation of the order,
        // and refunds the buyer's account. The time window for capturing the payment is
        // controlled by the merchant, but the default is 3 hours.
        // Send a notification to your buyer that provides them with possible next steps, like contacting customer support. And cancel/downgrade the order to unpaid status.


        // Instantiate paypalr module to load its language strings for status messages
        $this->loadCorePaymentModuleAndLanguageStrings();

        $oID = GetPayPalOrderTransactions::getOrderIdFromPayPalTxnId($this->data['resource']['order_id'] ?? null);

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
        $comments = "Notice: PAYMENT REVERSAL. Order ID: $oID \n$summary\n";
        $admin_message = MODULE_PAYMENT_PAYPALR_CAPTURE_ERROR;
        // downgrade order status to payment-pending
        $status = (int)MODULE_PAYMENT_PAYPALR_VOIDED_STATUS_ID;
        $status = ($status > 0) ? $status : 1;
        // Save update and notify customer
        zen_update_orders_history($oID, $comments, 'webhook', $status, 1);

        // Notify merchant via email
        zen_update_orders_history($oID, $admin_message . "\n" . $comments, 'webhook', -1, -2);
        $this->paymentModule->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN, sprintf(MODULE_PAYMENT_PAYPALR_ALERT_EXTERNAL_TXNS, $oID));

        // Alert any listeners
        global $zco_notifier;
        $zco_notifier->notify('NOTIFY_PAYPALR_ADMIN_FUNDS_IN_OUT', ['webhook' => $this->data]);
    }
}

/*
{
  "id": "WH-COC11055RA711503B-4YM959094A144403T",
  "create_time": "2020-01-25T21:21:49.000Z",
  "event_type": "CHECKOUT.PAYMENT-APPROVAL.REVERSED",
  "summary": "A payment has been reversed after approval.",
  "resource": {
    "order_id": "5O190127TN364715T",
    "purchase_units": [
      {
        "reference_id": "d9f83340-38f0-11e8-b467-0ed5f89f718b",
        "custom_id": "MERCHANT_CUSTOM_ID",
        "invoice_id": "MERCHANT_INVOICE_ID"
      }
    ],
    "payment_source": {
      "ideal": {
        "name": "John Doe",
        "country_code": "NL"
      }
    }
  }
  "event_version": "1.0"
}
 */
