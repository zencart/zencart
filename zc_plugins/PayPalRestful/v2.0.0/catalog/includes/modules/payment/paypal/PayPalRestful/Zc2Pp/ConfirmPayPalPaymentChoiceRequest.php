<?php
/**
 * A class to create a payload to confirm the payment choice for the specified payment-type
 * for the PayPalRestful (paypalr) Payment Module
 *
 * @copyright Copyright 2023 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 *
 * Last updated: v1.3.0
 */
namespace PayPalRestful\Zc2Pp;

use PayPalRestful\Common\Logger;
use PayPalRestful\Zc2Pp\Address;

class ConfirmPayPalPaymentChoiceRequest
{
    /**
     * The request to be submitted to a v2/orders/{id}/confirm-payment-choice PayPal endpoint.
     */
    protected $request;

    // -----
    // Constructor.  Creates the payload for a PayPal payment-choice confirmation request.
    //
    public function __construct(string $listener_endpoint, \order $order)
    {
        // -----
        // Determine the shipping-preference, one of:
        //
        // - GET_FROM_FILE .......... The customer can choose one of their PayPal-registered addresses for the shipping.
        // - NO_SHIPPING ............ Indicates that the order is 'digital' (aka 'virtual') and no shipping is required.
        // - SET_PROVIDED_ADDRESS ... PayPal uses the address the customer has chosen, no modification is allowed.
        //
        $shipping_preference = ($order->content_type === 'virtual') ? 'NO_SHIPPING' : 'SET_PROVIDED_ADDRESS';

        // -----
        // The brand-name supplied to PayPal (appears on PayPal-sent invoices to the
        // customer) is either the configured value or the store's defined name.
        //
        $brand_name = (MODULE_PAYMENT_PAYPALR_BRANDNAME !== '') ? MODULE_PAYMENT_PAYPALR_BRANDNAME : STORE_NAME;

        // -----
        // Determine the post-choice action for the customer. If we're running the 'standard' 3-page
        // checkout or OPC where this payment module is in the 'confirmation-required' list, then
        // the customer can review their order prior to order-confirmation.
        //
        // Otherwise, the customer confirms their order on the PayPal payment selection.
        //
        $user_action = 'CONTINUE';
        global $current_page_base;
        if (defined('FILENAME_CHECKOUT_ONE_CONFIRMATION') && defined('CHECKOUT_ONE_CONFIRMATION_REQUIRED') && $current_page_base === FILENAME_CHECKOUT_ONE_CONFIRMATION) {
            if (!in_array('paypalr', explode(',', str_replace(' ', '', CHECKOUT_ONE_CONFIRMATION_REQUIRED)))) {
                $user_action = 'PAY_NOW';
            }
        }

        $this->request = [
            'paypal' => [
                'name' => [
                    'given_name' => $order->billing['firstname'],
                    'surname' => $order->billing['lastname'],
                ],
                'email_address' => $order->customer['email_address'],
                'address' => Address::get($order->billing),
                'experience_context' => [
                    'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',    //- No eChecks, no means to test in the sandbox environment
                    'brand_name' => $brand_name,
//                    'locale' => 'en-US',
                    'landing_page' => 'NO_PREFERENCE',  //- LOGIN, GUEST_CHECKOUT or NO_PREFERENCE
                    'shipping_preference' => $shipping_preference,    //- GET_FROM_FILE (allows shipping address change on PayPal), NO_SHIPPING, SET_PROVIDED_ADDRESS (customer can't change)
                    'user_action' => $user_action,  //- PAY_NOW or CONTINUE
                    'return_url' => $listener_endpoint . '?op=return',
                    'cancel_url' => $listener_endpoint . '?op=cancel',
                ],
            ],
        ];

        $logger = new Logger();
        $logger->write("\ConfirmPayPalPaymentChoiceRequest::__construct(...) finished, request:\n" . Logger::logJSON($this->request));
    }

    public function get(): array
    {
        return $this->request;
    }
}
