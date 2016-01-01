<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: order.php 18695 2011-05-04 05:24:19Z drbyte  Modified in v1.5.5 $
 */

  class order extends base {
    var $info, $totals, $products, $customer, $delivery;

    function __construct($order_id) {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      $this->query($order_id);
    }

    function query($order_id) {
      global $db;
      $order = $db->Execute("select cc_cvv, customers_name, customers_company, customers_street_address,
                                    customers_suburb, customers_city, customers_postcode, customers_id,
                                    customers_state, customers_country, customers_telephone,
                                    customers_email_address, customers_address_format_id, delivery_name,
                                    delivery_company, delivery_street_address, delivery_suburb,
                                    delivery_city, delivery_postcode, delivery_state, delivery_country,
                                    delivery_address_format_id, billing_name, billing_company,
                                    billing_street_address, billing_suburb, billing_city, billing_postcode,
                                    billing_state, billing_country, billing_address_format_id,
                                    coupon_code, payment_method, payment_module_code, shipping_method, shipping_module_code,
                                    cc_type, cc_owner, cc_number, cc_expires, currency,
                                    currency_value, date_purchased, orders_status, last_modified,
                                    order_total, order_tax, ip_address
                             from " . TABLE_ORDERS . "
                             where orders_id = '" . (int)$order_id . "'");


      $totals = $db->Execute("select title, text, class, value
                              from " . TABLE_ORDERS_TOTAL . "
                              where orders_id = '" . (int)$order_id . "'
                              order by sort_order");

      while (!$totals->EOF) {
        if ($totals->fields['class'] == 'ot_coupon') {
          $coupon_link_query = "SELECT coupon_id
                                from " . TABLE_COUPONS . "
                                where coupon_code ='" . zen_db_input($order->fields['coupon_code']) . "'";
          $coupon_link = $db->Execute($coupon_link_query);
          $zc_coupon_link = '<a href="javascript:couponpopupWindow(\'' . zen_catalog_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $coupon_link->fields['coupon_id']) . '\')">';
        }
        $this->totals[] = array('title' => ($totals->fields['class'] == 'ot_coupon' ? $zc_coupon_link . $totals->fields['title'] . '</a>' : $totals->fields['title']),
                                'text' => $totals->fields['text'],
                                'value' => $totals->fields['value'],
                                'class' => $totals->fields['class']);
        $totals->MoveNext();
      }

      $this->info = array('currency' => $order->fields['currency'],
                          'currency_value' => $order->fields['currency_value'],
                          'payment_method' => $order->fields['payment_method'],
                          'payment_module_code' => $order->fields['payment_module_code'],
                          'shipping_method' => $order->fields['shipping_method'],
                          'shipping_module_code' => $order->fields['shipping_module_code'],
                          'coupon_code' => $order->fields['coupon_code'],
                          'cc_type' => $order->fields['cc_type'],
                          'cc_owner' => $order->fields['cc_owner'],
                          'cc_number' => $order->fields['cc_number'],
                          'cc_cvv' => $order->fields['cc_cvv'],
                          'cc_expires' => $order->fields['cc_expires'],
                          'date_purchased' => $order->fields['date_purchased'],
                          'orders_status' => $order->fields['orders_status'],
                          'total' => $order->fields['order_total'],
                          'tax' => $order->fields['order_tax'],
                          'last_modified' => $order->fields['last_modified'],
                          'ip_address' => $order->fields['ip_address']
                          );

      $this->customer = array('name' => $order->fields['customers_name'],
                              'id' => $order->fields['customers_id'],
                              'company' => $order->fields['customers_company'],
                              'street_address' => $order->fields['customers_street_address'],
                              'suburb' => $order->fields['customers_suburb'],
                              'city' => $order->fields['customers_city'],
                              'postcode' => $order->fields['customers_postcode'],
                              'state' => $order->fields['customers_state'],
                              'country' => $order->fields['customers_country'],
                              'format_id' => $order->fields['customers_address_format_id'],
                              'telephone' => $order->fields['customers_telephone'],
                              'email_address' => $order->fields['customers_email_address']);

      $this->delivery = array('name' => $order->fields['delivery_name'],
                              'company' => $order->fields['delivery_company'],
                              'street_address' => $order->fields['delivery_street_address'],
                              'suburb' => $order->fields['delivery_suburb'],
                              'city' => $order->fields['delivery_city'],
                              'postcode' => $order->fields['delivery_postcode'],
                              'state' => $order->fields['delivery_state'],
                              'country' => $order->fields['delivery_country'],
                              'format_id' => $order->fields['delivery_address_format_id']);

      $this->billing = array('name' => $order->fields['billing_name'],
                             'company' => $order->fields['billing_company'],
                             'street_address' => $order->fields['billing_street_address'],
                             'suburb' => $order->fields['billing_suburb'],
                             'city' => $order->fields['billing_city'],
                             'postcode' => $order->fields['billing_postcode'],
                             'state' => $order->fields['billing_state'],
                             'country' => $order->fields['billing_country'],
                             'format_id' => $order->fields['billing_address_format_id']);

      $index = 0;
      $orders_products = $db->Execute("select orders_products_id, products_id, products_name, products_model,
                                              products_price, products_tax, products_quantity,
                                              final_price, onetime_charges,
                                              product_is_free
                                       from " . TABLE_ORDERS_PRODUCTS . "
                                       where orders_id = '" . (int)$order_id . "'
                                       order by orders_products_id");

      while (!$orders_products->EOF) {
// convert quantity to proper decimals - account history
          if (QUANTITY_DECIMALS != 0) {
            $fix_qty = $orders_products->fields['products_quantity'];
            switch (true) {
            case (!strstr($fix_qty, '.')):
              $new_qty = $fix_qty;
              break;
            default:
              $new_qty = preg_replace('/[0]+$/', '', $orders_products->fields['products_quantity']);
              break;
            }
          } else {
            $new_qty = $orders_products->fields['products_quantity'];
          }

          $new_qty = round($new_qty, QUANTITY_DECIMALS);

          if ($new_qty == (int)$new_qty) {
            $new_qty = (int)$new_qty;
          }

        $this->products[$index] = array('qty' => $new_qty,
                                        'id' => $orders_products->fields['products_id'],
                                        'name' => $orders_products->fields['products_name'],
                                        'model' => $orders_products->fields['products_model'],
                                        'tax' => $orders_products->fields['products_tax'],
                                        'price' => $orders_products->fields['products_price'],
                                        'onetime_charges' => $orders_products->fields['onetime_charges'],
                                        'final_price' => $orders_products->fields['final_price'],
                                        'product_is_free' => $orders_products->fields['product_is_free']);

        $subindex = 0;
        $attributes = $db->Execute("select products_options, products_options_values, options_values_price,
                                           price_prefix, products_options_values_id,
                                           product_attribute_is_free
                                    from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                                    where orders_id = '" . (int)$order_id . "'
                                    and orders_products_id = '" . (int)$orders_products->fields['orders_products_id'] . "'");
        if ($attributes->RecordCount()>0) {
          while (!$attributes->EOF) {
            $this->products[$index]['attributes'][$subindex] = array('option' => $attributes->fields['products_options'],
                                                                     'value' => $attributes->fields['products_options_values'],
                                                                     'value_id' => $attributes->fields['products_options_values_id'],
                                                                     'prefix' => $attributes->fields['price_prefix'],
                                                                     'price' => $attributes->fields['options_values_price'],
                                                                     'product_attribute_is_free' =>$attributes->fields['product_attribute_is_free']);

            $subindex++;
            $attributes->MoveNext();
          }
        }
        $index++;
        $orders_products->MoveNext();
      }
      $this->notify('ORDER_QUERY_ADMIN_COMPLETE', array('orders_id' => $order_id));
    }
  }
