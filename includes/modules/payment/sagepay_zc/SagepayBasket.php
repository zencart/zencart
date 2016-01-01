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
 * Class SagepayBasket
 */
class SagepayBasket
{

    /**
     * @param $order
     * @return string
     */
    public static function getCartContents($order)
    {
        $countLines = 1;
        $shipping = number_format($order->info['shipping_cost'], 2, '.', '');
        $shippingtax = number_format($order->info['shipping_tax'], 2, '.', '');
        $totalshipping = number_format($shipping + $shippingtax, 2, '.', '');
        $shippingStr = ":Shipping:---:" . $shipping . ":" . $shippingtax . ":" . $totalshipping . ":" . $totalshipping;
        $products = $_SESSION['cart']->get_products();
        $productLines = '';
        for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
            $desc = str_replace(":", "", $order->products[$i]['name']);
            $qty = $order->products[$i]['qty'];
            $price = $order->products[$i]['price'] + $_SESSION['cart']->attributes_price($products[$i]['id']);
            $tax = $price / 100 * zen_get_tax_rate($products[$i]['tax_class_id']);
            $tax = number_format($tax, 2, '.', '');
            $finalPrice = $price + $tax;
            $finalPrice = number_format($finalPrice, 2, '.', '');
            $lineTotal = $qty * $finalPrice;
            $lineTotal = number_format($lineTotal, 2, '.', '');
            $line = ":" . $desc . ":" . $qty . ":" . $price . ":" . $tax . ":" . $finalPrice . ":" . $lineTotal;
            $productLines .= $line;
            $countLines++;
        }
        return $countLines . $productLines . $shippingStr;
    }
}
