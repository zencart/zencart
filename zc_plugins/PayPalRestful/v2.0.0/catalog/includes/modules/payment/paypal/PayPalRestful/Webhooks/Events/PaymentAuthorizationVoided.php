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

class PaymentAuthorizationVoided extends WebhookHandlerContract
{
    protected $eventsHandled = [
        'PAYMENT.AUTHORIZATION.VOIDED',
    ];

    public function action()
    {
        // A payment authorization is voided either due to authorization
        // reaching its 30-day validity period or authorization was
        // manually voided using the Void Authorized Payment API.
        // https://developer.paypal.com/docs/api/payments/v2/#authorizations_get

        $this->log->write('PAYMENT.AUTHORIZATION.VOIDED - action() triggered');

        // ACTION: Add an order-status record indicating that the prior auth has expired or been voided.


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
            "Notice: VOIDED. Trans ID: $txnID \n" .
            "Amount: $amount\n$summary\n";

        $status = (int)MODULE_PAYMENT_PAYPALR_VOIDED_STATUS_ID;
        $status = ($status > 0) ? $status : 1;

        // Save update without notifying customer
        zen_update_orders_history($oID, $comments, 'webhook', $status, 0);

        // Notify merchant via email
        $this->paymentModule->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN, sprintf(MODULE_PAYMENT_PAYPALR_ALERT_EXTERNAL_TXNS, $oID));
    }
}

/*
{
  "id": "WH-5J139929H24960831-61917983AY1263720",
  "create_time": "2018-08-15T19:21:36.256Z",
  "resource_type": "authorization",
  "event_type": "PAYMENT.AUTHORIZATION.VOIDED",
  "summary": "A payment authorization was voided for $ 2.51 USD",
  "resource": {
    "amount": {
      "currency_code": "USD",
      "value": "2.51"
    },
    "seller_protection": {
      "status": "ELIGIBLE",
      "dispute_categories": [
        "ITEM_NOT_RECEIVED",
        "UNAUTHORIZED_TRANSACTION"
      ]
    },
    "update_time": "2018-08-15T19:21:18Z",
    "create_time": "2018-08-15T19:21:11Z",
    "expiration_time": "2018-09-13T19:21:11Z",
    "links": [
      {
        "href": "https://api.paypal.com/v2/payments/authorizations/8PP38301D7923932J",
        "rel": "self",
        "method": "GET"
      },
      {
        "href": "https://api.paypal.com/v2/payments/authorizations/8PP38301D7923932J/capture",
        "rel": "capture",
        "method": "POST"
      },
      {
        "href": "https://api.paypal.com/v2/payments/authorizations/8PP38301D7923932J/void",
        "rel": "void",
        "method": "POST"
      },
      {
        "href": "https://api.paypal.com/v2/payments/authorizations/8PP38301D7923932J/reauthorize",
        "rel": "reauthorize",
        "method": "POST"
      },
      {
        "href": "https://api.paypal.com/v2/checkout/orders/9RC99380MW614700D",
        "rel": "up",
        "method": "GET"
      }
    ],
    "id": "8PP38301D7923932J",
    "status": "VOIDED"
  },
  "links": [
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-5J139929H24960831-61917983AY1263720",
      "rel": "self",
      "method": "GET",
      "encType": "application/json"
    },
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-5J139929H24960831-61917983AY1263720/resend",
      "rel": "resend",
      "method": "POST",
      "encType": "application/json"
    }
  ],
  "event_version": "1.0",
  "resource_version": "2.0"
}
 */
