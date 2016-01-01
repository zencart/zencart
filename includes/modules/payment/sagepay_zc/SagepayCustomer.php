<?php
/**
 * sagepay form
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: Wilt New in v1.5.5 $
 */

/**
 * Class SagepayCustomer
 */
class SagepayCustomer
{
    /**
     * @param $order
     * @param $us_state_codes
     * @return mixed
     */
    public static function setBillingEntries($order, $us_state_codes)
    {
        $billingEntries['BillingSurname'] = $order->billing['lastname'];
        $billingEntries['BillingFirstnames'] = $order->billing['firstname'];
        $billingEntries['BillingAddress1'] = $order->billing['street_address'];
        $billingEntries['BillingAddress2'] = '';
        if (ACCOUNT_SUBURB == 'true') {
            $billingEntries['BillingAddress2'] = $order->billing['suburb'];
        }
        $billingEntries['BillingCity'] = $order->billing['city'];
        $billingEntries['BillingPostCode'] = $order->billing['postcode'];
        $billingEntries['BillingCountry'] = $order->billing['country']['iso_code_2'];
        $orderbillstate = '';
        if (($order->billing['country']['iso_code_2']) == 'US') {
            $orderbillstate = $us_state_codes[$order->billing['state']];
        }
        $billingEntries['BillingState'] = $orderbillstate;
        $billingEntries['BillingPhone'] = $order->customer['telephone'];
        return $billingEntries;
    }

    /**
     * @param $order
     * @param $us_state_codes
     * @return mixed
     */
    public static function setDeliveryEntries($order, $us_state_codes)
    {
        $delvery = Self::handleMissingDeliveryEntries($order, $us_state_codes);
        $deliveryEntries['DeliverySurname'] = $delvery['lastname'];
        $deliveryEntries['DeliveryFirstnames'] = $delvery['firstname'];
        $deliveryEntries['DeliveryAddress1'] = $delvery['street_address'];
        $deliveryEntries['DeliveryAddress2'] = '';
        if (ACCOUNT_SUBURB == 'true') {
            $deliveryEntries['DeliveryAddress2'] = $delvery['suburb'];
        }
        $deliveryEntries['DeliveryCity'] = $delvery['city'];
        $deliveryEntries['DeliveryPostCode'] = $delvery['postcode'];
        $deliveryEntries['DeliveryCountry'] = $delvery['country']['iso_code_2'];
        $orderstate = '';
        if (($delvery['country']['iso_code_2']) == 'US') {
            $orderstate = $us_state_codes[$delvery['state']];
        }
        $deliveryEntries['DeliveryState'] = $orderstate;
        $deliveryEntries['DeliveryPhone'] = $order->customer['telephone'];
        return $deliveryEntries;

    }

    /**
     * @param $order
     * @param $us_state_codes
     * @return mixed
     */
    public static function handleMissingDeliveryEntries($order, $us_state_codes)
    {
        if (isset($order->delivery) && $order->delivery != false) {
            return $order->delivery;
        }
        return $order->billing;
    }
}
