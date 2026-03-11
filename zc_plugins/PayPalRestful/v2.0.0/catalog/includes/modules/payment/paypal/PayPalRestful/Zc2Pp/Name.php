<?php
/**
 * A ZenCart-to-PayPal conversion class for Name types used by the PayPalRestful (paypalr) Payment Module
 *
 * @copyright Copyright 2023 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 */
namespace PayPalRestful\Zc2Pp;

class Name
{
    public static function get(array $order_address): array
    {
/*
        if (isset($order_address['name'])) {
            $name_pieces = explode(' ', $order_address['name'], 2);
            $given_name = $name_pieces[0];
            $surname = $name_pieces[1] ?? '';
        } else {
            $given_name = $order_address['firstname'];
            $surname = $order_address['lastname'];
        }
*/
        $full_name = $order_address['name'] ?? ($order_address['firstname'] . ' ' . $order_address['lastname']);
        return [
            'full_name' => $full_name,
        ];
    }
}
