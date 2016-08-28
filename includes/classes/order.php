<?php
/**
 * File contains the order-processing class ("order")
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Modified in v1.6.0 $
 */
/**
 * order class
 * Handles order creation/querying, and prepares/sends confirmation/update emails
 *
 * @package classes
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

class order extends base {
  public $id = 0;
  public $info, $totals, $customer, $billing, $delivery, $attachArray = array();
  public $products = array();
  public $currency = array();
  public $language = array();
  public $content_type = '';
  public $email_low_stock, $products_ordered_email, $products_ordered, $products_ordered_html, $products_ordered_attributes = '';
  public $order_statuses_array = array();
  public $status_history = array();
  public $downloads = array();
  private $use_external_tax_handler_only = false;
  public $queryReturnFlag = null;

  /**
   * Constructor
   * Builds order object contents from customer's session basket or from db
   *
   * @param int $order_id
   * @param bool $override_currency
   */
  public function __construct($order_id = 0, $override_currency = false) {
    global $lng;

    $this->currency = ($override_currency === false) ? $_SESSION['currency'] : $override_currency;

    $this->notify('NOTIFY_ORDER_INSTANTIATE', array(), $order_id);
    if ((int)$order_id > 0) {
      $this->query((int)$order_id);
    } else {
      $this->language = $lng->get_language_data_by_id($_SESSION['languages_id']);
      $this->cart($_SESSION['cart']);
    }

    if (!defined('ORDER_EMAIL_DATE_FORMAT')) define('ORDER_EMAIL_DATE_FORMAT', 'M-d-Y h:iA');
  }

  /**
   * Retrieve specified order's data from database and populate object arrays
   *
   * @param int $order_id
   * @return bool returns false if we were unable to retrieve the specified order
   */
  protected function query($order_id = 0) {
    global $db, $lng;

    $order_id = (int)$order_id;
    $this->queryReturnFlag = null;
    $this->notify('NOTIFY_ORDER_BEFORE_QUERY', array(), $order_id);
    if ($this->queryReturnFlag === true) return false;

    // cast again in case the notifier changed it
    $this->id = (int)$order_id;

    // retrieve data
    $order_query = "select * FROM " . TABLE_ORDERS . " WHERE orders_id = " . (int)$this->id;
    $order = $db->Execute($order_query);
    if ($order->EOF) return false;

    // retrieve language details
    $this->language = $lng->get_language_data_by_code($order->fields['language_code']);
    $result = $db->Execute("SELECT orders_status_name
                            FROM " . TABLE_ORDERS_STATUS . " o 
                            WHERE language_id = " . $this->language['id'] . "
                            AND orders_status_id = " . $order->fields['orders_status']);
    $ordersStatusName = $result->fields['orders_status_name'];

    // retrieve order-totals
    $totals_query = "select title, text, class, value
                     from " . TABLE_ORDERS_TOTAL . "
                     where orders_id = " . (int)$this->id . "
                     order by sort_order";
    $totals = $db->Execute($totals_query);
    foreach($totals as $total) {
      $zc_coupon_link = '';
      if ($total['class'] == 'ot_coupon') {
        $sql = "SELECT coupon_id
                from " . TABLE_COUPONS . "
                where coupon_code = :couponCode";
        $sql = $db->bindVars($sql, ':couponCode', $order->fields['coupon_code'], 'string');
        $coupon_link = $db->Execute($sql);
        $zc_coupon_link = '<a href="javascript:couponpopupWindow(\'' . zen_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $coupon_link->fields['coupon_id']) . '\')">';
      }
      $this->totals[] = array('title' => ($total['class'] == 'ot_coupon' ? $zc_coupon_link . $total['title'] . '</a>' : $total['title']),
                              'text' => $total['text'],
                              'value' => $total['value'],
                              'class' => $total['class']);
    }

    $gv_count_in_queue = $this->get_number_of_unreleased_gvs($this->id);

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
                        'orders_status_name' => $ordersStatusName,
                        'total' => $order->fields['order_total'],
                        'tax' => $order->fields['order_tax'],
                        'last_modified' => $order->fields['last_modified'],
                        'ip_address' => $order->fields['ip_address'],
                        'order_weight' => $order->fields['order_weight'],
                        'is_guest_order' => $order->fields['is_guest_order'],
                        'language_code' => $order->fields['language_code'],
                        'gv_count_in_queue' => $gv_count_in_queue,
                        );

    $this->customer = array('id' => $order->fields['customers_id'],
                            'name' => $order->fields['customers_name'],
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

    if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
      $this->delivery = false;
    }

    $this->billing = array('name' => $order->fields['billing_name'],
                           'company' => $order->fields['billing_company'],
                           'street_address' => $order->fields['billing_street_address'],
                           'suburb' => $order->fields['billing_suburb'],
                           'city' => $order->fields['billing_city'],
                           'postcode' => $order->fields['billing_postcode'],
                           'state' => $order->fields['billing_state'],
                           'country' => $order->fields['billing_country'],
                           'format_id' => $order->fields['billing_address_format_id']);

    $orders_products_query = "select *
                              from " . TABLE_ORDERS_PRODUCTS . "
                              where orders_id = " . (int)$this->id . "
                              order by orders_products_id";
    $orders_products = $db->Execute($orders_products_query);

    $index = 0;
    foreach($orders_products as $product) {
      // convert quantity to proper decimals (particularly for account history)
      if (QUANTITY_DECIMALS != 0) {
        $fix_qty = $product['products_quantity'];
        switch (true) {
          case (!strstr($fix_qty, '.')):
          $new_qty = $fix_qty;
          break;
          default:
          // remove trailing 0's
          $new_qty = preg_replace('/[0]+$/', '', $product['products_quantity']);
          break;
        }
      } else {
        $new_qty = $product['products_quantity'];
      }

      $new_qty = round($new_qty, QUANTITY_DECIMALS);
      // cast to integer if value doesn't need to be float
      if ($new_qty == (int)$new_qty) {
        $new_qty = (int)$new_qty;
      }

      $this->products[$index] = array('qty' => $new_qty,
                                      'id' => $product['products_id'],
                                      'name' => $product['products_name'],
                                      'model' => $product['products_model'],
                                      'tax' => $product['products_tax'],
                                      'price' => $product['products_price'],
                                      'final_price' => $product['final_price'],
                                      'onetime_charges' => $product['onetime_charges'],
                                      'products_priced_by_attribute' => $product['products_priced_by_attribute'],
                                      'product_is_free' => $product['product_is_free'],
                                      'products_discount_type' => $product['products_discount_type'],
                                      'products_discount_type_from' => $product['products_discount_type_from'],
                                      'products_weight' => $product['products_weight'],
                                      'products_virtual' => $product['products_virtual'],
                                      'product_is_always_free_shipping' => $product['product_is_always_free_shipping'],
                                      'products_quantity_order_min' => $product['products_quantity_order_min'],
                                      'products_quantity_order_units' => $product['products_quantity_order_units'],
                                      'products_quantity_order_max' => $product['products_quantity_order_max'],
                                      'products_quantity_mixed' => $product['products_quantity_mixed'],
                                      'products_mixed_discount_quantity' => $product['products_mixed_discount_quantity']
                                      );

      $attributes_query = "select * from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                           where orders_id = " . (int)$this->id . "
                           and orders_products_id = " . (int)$product['orders_products_id'];
      $attributes = $db->Execute($attributes_query);
      if (sizeof($attributes)) {
        foreach($attributes as $attribute) {
          $this->products[$index]['attributes'][]  = array('option' => $attribute['products_options'],
                                                           'value' => $attribute['products_options_values'],
                                                           'option_id' => $attribute['products_options_id'],
                                                           'value_id' => $attribute['products_options_values_id'],
                                                           'prefix' => $attribute['price_prefix'],
                                                           'price' => $attribute['options_values_price'],
                                                           'product_attribute_is_free' =>$attribute['product_attribute_is_free'],
                                                           );
        }
      }// endif sizeof attributes

      $this->info['tax_groups']["{$this->products[$index]['tax']}"] = 1;

      $index++;
    } // end loop $orders_products as $product

    // retrieve downloads for current order
    $orders_download_query = "select * from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id=" . (int)$this->id;
    $orders_download = $db->Execute($orders_download_query);
    foreach($orders_download as $key=>$row) 
    {
      $this->downloads[] = $row;
    }

    // get order-status-history including comments
    $orders_history = $db->Execute("select orders_status_id, date_added, customer_notified, comments
                                    from " . TABLE_ORDERS_STATUS_HISTORY . "
                                    where orders_id = " . (int)$this->id . "
                                    order by date_added asc");
    foreach($orders_history as $key=>$row) 
    {
      $this->status_history[] = $row;
    }

    $this->notify('NOTIFY_ORDER_AFTER_QUERY', array(), $this->id);
  }

    /**
     * count unreleased GVs associated with this order
     *
     * @param int $order_id
     * @return int
     */
    public function get_number_of_unreleased_gvs($order_id)
  {
      global $db;
      $result = $db->Execute("select order_id, unique_id
                              from " . TABLE_COUPON_GV_QUEUE ."
                              where order_id = " . (int)$order_id . " and release_flag='N'");
      return count($result);

  }
    /**
     * Get list of all order status names and ids for the specified language
     *
     * @param int $language_id
     * @return array
     */
  public static function get_order_statuses($language_id = null)
  {
    global $db;
    if (!$language_id) $language_id = $_SESSION['languages_id'];
    $order_statuses_array = array();
    $result = $db->Execute("select orders_status_id, orders_status_name
                            from " . TABLE_ORDERS_STATUS . "
                            where language_id = " . (int)$language_id . "
                            order by orders_status_id");
    foreach($result as $row) {
      $order_statuses_array[$row['orders_status_id']] = $row['orders_status_name'];
    }
    return $order_statuses_array;
  }

    /**
     * Prepare order object data based on contents of customer's shopping basket from customer's active session.
     *
     * @param shoppingCart $basket
     * @param int $customer_id to apply to this order, including for use in lookups of address data
     * @param int $sendto_address_id shipping address_book_id
     * @param int $billto_address_id billing address_book_id
     */
  protected function cart(shoppingCart $basket, $customer_id = null, $sendto_address_id = null, $billto_address_id = null)
  {
    global $db, $currencies, $shipping_weight, $shipping_num_boxes;

    if ($customer_id == null && isset($_SESSION['customer_id'])) $customer_id = (int)$_SESSION['customer_id'];
    if ($sendto_address_id == null && isset($_SESSION['sendto'])) $sendto_address_id = (int)$_SESSION['sendto'];
    if ($billto_address_id == null && isset($_SESSION['billto'])) $billto_address_id = (int)$_SESSION['billto'];

    $decimals = $currencies->get_decimal_places($this->currency);

    $this->content_type = $basket->get_content_type();

    $customer_address_query = "select c.customers_firstname, c.customers_lastname, c.customers_telephone,
                                c.customers_email_address, ab.entry_company, ab.entry_street_address,
                                ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id,
                                z.zone_name, co.countries_id, cn.countries_name,
                                co.countries_iso_code_2, co.countries_iso_code_3,
                                co.address_format_id, ab.entry_state
                               from (" . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab )
                               left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                               left join " . TABLE_COUNTRIES . " co on (ab.entry_country_id = co.countries_id)
                               left join " . TABLE_COUNTRIES_NAME . " cn on (ab.entry_country_id = cn.countries_id)
                               where c.customers_id = " . (int)$customer_id . "
                               and ab.customers_id = " . (int)$customer_id . "
                               and c.customers_default_address_id = ab.address_book_id
                               and cn.language_id = " . (int)$this->language['id'];

    $customer_address = $db->Execute($customer_address_query);

    $shipping_address_query = "select ab.entry_firstname, ab.entry_lastname, ab.entry_company,
                                ab.entry_street_address, ab.entry_suburb, ab.entry_postcode,
                                ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id,
                                c.countries_id, cn.countries_name, c.countries_iso_code_2,
                                c.countries_iso_code_3, c.address_format_id, ab.entry_state
                               from " . TABLE_ADDRESS_BOOK . " ab
                               left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                               left join " . TABLE_COUNTRIES . " c on (ab.entry_country_id = c.countries_id)
                               left join " . TABLE_COUNTRIES_NAME . " cn on (ab.entry_country_id = cn.countries_id)
                               where ab.customers_id = " . (int)$customer_id . "
                               and ab.address_book_id = " . (int)$sendto_address_id . "
                               and cn.language_id = " . (int)$this->language['id'];

    $shipping_address = $db->Execute($shipping_address_query);

    $billing_address_query = "select ab.entry_firstname, ab.entry_lastname, ab.entry_company,
                               ab.entry_street_address, ab.entry_suburb, ab.entry_postcode,
                               ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id,
                               c.countries_id, cn.countries_name, c.countries_iso_code_2,
                               c.countries_iso_code_3, c.address_format_id, ab.entry_state
                              from " . TABLE_ADDRESS_BOOK . " ab
                              left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                              left join " . TABLE_COUNTRIES . " c on (ab.entry_country_id = c.countries_id)
                              left join " . TABLE_COUNTRIES_NAME . " cn on (ab.entry_country_id = cn.countries_id)
                              where ab.customers_id = " . (int)$customer_id . "
                              and ab.address_book_id = " . (int)$billto_address_id . "
                              and cn.language_id = " . (int)$this->language['id'];

    $billing_address = $db->Execute($billing_address_query);

    // set default tax calculation for not-logged-in visitors
    $taxCountryId = $taxZoneId = 0;

    // get tax zone info for logged-in visitors
    if ((int)$customer_id > 0) {
      $taxCountryId = $taxZoneId = -1;
      $tax_address_query = '';
      switch (STORE_PRODUCT_TAX_BASIS) {
        case 'Shipping':
        $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                              from " . TABLE_ADDRESS_BOOK . " ab
                              left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                              where ab.customers_id = " . (int)$customer_id . "
                              and ab.address_book_id = " . (int)($this->content_type == 'virtual' ? $billto_address_id : $sendto_address_id);
        break;
        case 'Billing':
        $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                              from " . TABLE_ADDRESS_BOOK . " ab
                              left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                              where ab.customers_id = " . (int)$customer_id . "
                              and ab.address_book_id = " . (int)$billto_address_id;
        break;
        case 'Store':
        if ($billing_address->fields['entry_zone_id'] == STORE_ZONE) {

          $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.customers_id = " . (int)$customer_id . "
                                and ab.address_book_id = " . (int)$billto_address_id;
        } else {
          $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.customers_id = " . (int)$customer_id . "
                                and ab.address_book_id = " . (int)($this->content_type == 'virtual' ? $billto_address_id : $sendto_address_id);
        }
      }
      if ($tax_address_query != '') {
        $tax_address = $db->Execute($tax_address_query);
        if ($tax_address->RecordCount() > 0) {
          $taxCountryId = $tax_address->fields['entry_country_id'];
          $taxZoneId = $tax_address->fields['entry_zone_id'];
        }
      }
    }

    $this->notify('NOTIFY_ORDER_CART_ADD_PRODUCT_TAX_ZONES', [], $taxCountryId, $taxZoneId, $decimals);

    $class =& $_SESSION['payment'];

    $coupon_code = $this->get_coupon_code_from_session();

    $this->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
                        'currency' => $this->currency,
                        'currency_value' => $currencies->currencies[$this->currency]['value'],
                        'payment_method' => $GLOBALS[$class]->title,
                        'payment_module_code' => $GLOBALS[$class]->code,
                        'coupon_code' => $coupon_code,
                        'shipping_method' => (isset($_SESSION['shipping']['title'])) ? $_SESSION['shipping']['title'] : '',
                        'shipping_module_code' => (isset($_SESSION['shipping']['id']) && strpos($_SESSION['shipping']['id'], '_') > 0 ? $_SESSION['shipping']['id'] : $_SESSION['shipping']),
                        'shipping_cost' => $currencies->value(isset($_SESSION['shipping']['cost']) ? $_SESSION['shipping']['cost'] : 0, false, $this->currency),
                        'subtotal' => 0,
                        'shipping_tax' => 0,
                        'tax' => 0,
                        'total' => 0,
                        'tax_groups' => array(),
                        'comments' => (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''),
                        'ip_address' => $_SESSION['customers_ip_address'] . ' - ' . zen_get_ip_address(),
                        'order_weight' => ($shipping_weight * $shipping_num_boxes),
                        'language_code' => $this->language['code'],
                        );

    $this->customer = array('id' => (int)$customer_id,
                            'firstname' => $customer_address->fields['customers_firstname'],
                            'lastname' => $customer_address->fields['customers_lastname'],
                            'company' => $customer_address->fields['entry_company'],
                            'street_address' => $customer_address->fields['entry_street_address'],
                            'suburb' => $customer_address->fields['entry_suburb'],
                            'city' => $customer_address->fields['entry_city'],
                            'postcode' => $customer_address->fields['entry_postcode'],
                            'state' => ((zen_not_null($customer_address->fields['entry_state'])) ? $customer_address->fields['entry_state'] : $customer_address->fields['zone_name']),
                            'zone_id' => $customer_address->fields['entry_zone_id'],
                            'country' => array('id' => $customer_address->fields['countries_id'], 'title' => $customer_address->fields['countries_name'], 'iso_code_2' => $customer_address->fields['countries_iso_code_2'], 'iso_code_3' => $customer_address->fields['countries_iso_code_3']),
                            'format_id' => (int)$customer_address->fields['address_format_id'],
                            'telephone' => $customer_address->fields['customers_telephone'],
                            'email_address' => $customer_address->fields['customers_email_address']);

    $this->delivery = array('firstname' => $shipping_address->fields['entry_firstname'],
                            'lastname' => $shipping_address->fields['entry_lastname'],
                            'company' => $shipping_address->fields['entry_company'],
                            'street_address' => $shipping_address->fields['entry_street_address'],
                            'suburb' => $shipping_address->fields['entry_suburb'],
                            'city' => $shipping_address->fields['entry_city'],
                            'postcode' => $shipping_address->fields['entry_postcode'],
                            'state' => ((zen_not_null($shipping_address->fields['entry_state'])) ? $shipping_address->fields['entry_state'] : $shipping_address->fields['zone_name']),
                            'zone_id' => $shipping_address->fields['entry_zone_id'],
                            'country' => array('id' => $shipping_address->fields['countries_id'], 'title' => $shipping_address->fields['countries_name'], 'iso_code_2' => $shipping_address->fields['countries_iso_code_2'], 'iso_code_3' => $shipping_address->fields['countries_iso_code_3']),
                            'country_id' => $shipping_address->fields['entry_country_id'],
                            'format_id' => (int)$shipping_address->fields['address_format_id'],
                            'address_book_id' => $sendto_address_id,
                            );

    $this->billing = array('firstname' => $billing_address->fields['entry_firstname'],
                           'lastname' => $billing_address->fields['entry_lastname'],
                           'company' => $billing_address->fields['entry_company'],
                           'street_address' => $billing_address->fields['entry_street_address'],
                           'suburb' => $billing_address->fields['entry_suburb'],
                           'city' => $billing_address->fields['entry_city'],
                           'postcode' => $billing_address->fields['entry_postcode'],
                           'state' => ((zen_not_null($billing_address->fields['entry_state'])) ? $billing_address->fields['entry_state'] : $billing_address->fields['zone_name']),
                           'zone_id' => $billing_address->fields['entry_zone_id'],
                           'country' => array('id' => $billing_address->fields['countries_id'], 'title' => $billing_address->fields['countries_name'], 'iso_code_2' => $billing_address->fields['countries_iso_code_2'], 'iso_code_3' => $billing_address->fields['countries_iso_code_3']),
                           'country_id' => $billing_address->fields['entry_country_id'],
                           'format_id' => (int)$billing_address->fields['address_format_id'],
                           'address_book_id' => $billto_address_id,
                           );

    $index = 0;
    $products = $basket->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {

      $taxRates = zen_get_multiple_tax_rates($products[$i]['tax_class_id'], $taxCountryId, $taxZoneId);
      $this->products[$index] = array('qty' => $products[$i]['quantity'],
                                      'name' => $products[$i]['name'],
                                      'model' => $products[$i]['model'],
                                      'tax_groups'=>$taxRates,
                                      'tax_description' => zen_get_tax_description($products[$i]['tax_class_id'], $taxCountryId, $taxZoneId),
                                      'price' => $products[$i]['price'],
                                      'final_price' => $products[$i]['price'] + $basket->attributes_price($products[$i]['id']),
                                      'onetime_charges' => $basket->attributes_price_onetime_charges($products[$i]['id'], $products[$i]['quantity']),
                                      'weight' => $products[$i]['weight'],
                                      'products_priced_by_attribute' => $products[$i]['products_priced_by_attribute'],
                                      'product_is_free' => $products[$i]['product_is_free'],
                                      'products_discount_type' => $products[$i]['products_discount_type'],
                                      'products_discount_type_from' => $products[$i]['products_discount_type_from'],
                                      'id' => $products[$i]['id'],
                                      'rowClass' => $this->calculate_odd_even_rowclass($i),
                                      'products_weight' => $products[$i]['weight'],
                                      'products_virtual' => $products[$i]['products_virtual'],
                                      'product_is_always_free_shipping' => $products[$i]['product_is_always_free_shipping'],
                                      'products_quantity_order_min' => $products[$i]['products_quantity_order_min'],
                                      'products_quantity_order_units' => $products[$i]['products_quantity_order_units'],
                                      'products_quantity_order_max' => $products[$i]['products_quantity_order_max'],
                                      'products_quantity_mixed' => $products[$i]['products_quantity_mixed'],
                                      'products_mixed_discount_quantity' => $products[$i]['products_mixed_discount_quantity'],
                                      'tax' => zen_get_tax_rate($products[$i]['tax_class_id'], $taxCountryId, $taxZoneId),
                                      );
      $taxRates = zen_get_multiple_tax_rates($products[$i]['tax_class_id'], $taxCountryId, $taxZoneId);
      $newRates = $this->overrideTaxRatesBasedOnShippingModule($_SESSION['shipping']['id'], $index, $products, $i);
      if ($newRates !== false) {
        $taxRates = $newRates;
      }

      $this->notify('NOTIFY_ORDER_CART_ADD_PRODUCT_LIST', array('index'=>$index, 'products'=>$products[$i]));
      if ($products[$i]['attributes']) {
        $subindex = 0;
        reset($products[$i]['attributes']);
        while (list($option, $value) = each($products[$i]['attributes'])) {

          $attributes_query = "select popt.products_options_name, poval.products_options_values_name,
                                      pa.options_values_price, pa.price_prefix
                               from " . TABLE_PRODUCTS_OPTIONS . " popt,
                                    " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                    " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                               where pa.products_id = " . (int)$products[$i]['id'] . "
                               and pa.options_id = " . (int)$option . "
                               and pa.options_id = popt.products_options_id
                               and pa.options_values_id = " . (int)$value . "
                               and pa.options_values_id = poval.products_options_values_id
                               and popt.language_id = " . (int)$this->language['id'] . "
                               and poval.language_id = popt.language_id";

          $attributes = $db->Execute($attributes_query);

          //clr 030714 Determine if attribute is a text attribute and change products array if it is.
          if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID){
            $attr_value = $products[$i]['attributes_values'][$option];
          } else {
            $attr_value = $attributes->fields['products_options_values_name'];
          }

          $this->notify('NOTIFY_ORDER_CART_ADD_ATTRIBUTE_LIST', array('index'=>$index, 'subindex'=>$subindex, 'products'=>$products[$i], 'attributes'=>$attributes), $attr_value, $option, $value, $attributes);
          $this->products[$index]['attributes'][$subindex] = array('option' => $attributes->fields['products_options_name'],
                                                                   'value' => $attr_value,
                                                                   'option_id' => $option,
                                                                   'value_id' => $value,
                                                                   'prefix' => $attributes->fields['price_prefix'],
                                                                   'price' => $attributes->fields['options_values_price']);

          $subindex++;
        }
      }

      // add onetime charges here
      //$basket->attributes_price_onetime_charges($products[$i]['id'], $products[$i]['quantity'])


      /**************************************
       * Trigger any external tax handling code
       **************************************/
      $this->use_external_tax_handler_only = false;
      $this->notify('NOTIFY_ORDER_CART_EXTERNAL_TAX_HANDLING', array(), $index, $taxCountryId, $taxZoneId);
      // if no external calculation is overriding, do internal default tax calculations
      if ($this->use_external_tax_handler_only == false) {
          $this->do_calculate_taxes($taxRates, $index, $taxCountryId, $taxZoneId);
      }

      $index++;
    }

    // Update the final total to include tax if not already tax-inc
    if (DISPLAY_PRICE_WITH_TAX == 'true') {
      $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
    } else {
      $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
    }

    // set order-status of this order based on the selected payment module's rule for order-status
    if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {
      if ( isset($GLOBALS[$class]->order_status) && is_numeric($GLOBALS[$class]->order_status) && ($GLOBALS[$class]->order_status > 0) ) {
        $this->info['order_status'] = $GLOBALS[$class]->order_status;
      }
    }
    $this->notify('NOTIFY_ORDER_CART_FINISHED');
  }

    /**
     * Lookup 'cc_id' from session
     * @return string
     */
    public function get_coupon_code_from_session()
  {
    global $db;
    if (!isset($_SESSION['cc_id'])) return '';
    $coupon_code_query = "select coupon_code
                          from " . TABLE_COUPONS . "
                          where coupon_id = " . (int)$_SESSION['cc_id'];
    $result = $db->Execute($coupon_code_query);
    if (!$result->RecordCount()) return '';
    return $result->fields['coupon_code'];
  }
    /**
     * calculate display-class for alternating rows, if needed
     * @param int $i
     * @return string
     */
    protected function calculate_odd_even_rowclass($i)
  {
      if (($i/2) == floor($i/2)) {
          $rowClass="rowEven";
      } else {
          $rowClass="rowOdd";
      }
      return $rowClass;
  }
    /**
     * Override Tax rate for "storepickup" shipping module, if it's in use
     *
     * @param string $method Shipping Method Name, usually from $_SESSION[shipping][id]
     * @param int $index iterator index for order's product to override
     * @param array $products product array being processed
     * @param int $i iterator index for product array
     * @return bool|mixed
     */
    public function overrideTaxRatesBasedOnShippingModule($method, $index, $products, $i)
  {
      if (STORE_PRODUCT_TAX_BASIS == 'Shipping' && stristr($method, 'storepickup') === true)
      {
          $taxRates = zen_get_multiple_tax_rates($products[$i]['tax_class_id'], STORE_COUNTRY, STORE_ZONE);
          $this->products[$index]['tax'] = zen_get_tax_rate($products[$i]['tax_class_id'], STORE_COUNTRY, STORE_ZONE);
          return $taxRates;
      }
      return false;
  }

    /**
     * Calculate and apply tax rates and amounts to this order
     *
     * @param array $taxRates array of calculated tax rates for the specified product
     * @param int $index iterator position
     * @param int $taxCountryId tax zone country id
     * @param int $taxZoneId tax zone id
     */
    public function do_calculate_taxes($taxRates, $index, $taxCountryId, $taxZoneId)
  {
      global $currencies;
      $shown_price = $currencies->value(zen_add_tax($this->products[$index]['final_price'] * $this->products[$index]['qty'], $this->products[$index]['tax']), false, $this->currency)
                   + $currencies->value(zen_add_tax($this->products[$index]['onetime_charges'], $this->products[$index]['tax']), false, $this->currency);

      $this->notify('NOTIFY_ORDER_CART_SUBTOTAL_CALCULATE', array('shown_price'=>$shown_price), $shown_price);

      $this->info['subtotal'] += $shown_price;

      // find product's tax rate and description
      $product_tax_rate = $this->products[$index]['tax'];

      if (DISPLAY_PRICE_WITH_TAX == 'true') {
          // calculate the amount of tax "inc"luded in price (used if tax-in pricing is enabled)
          $tax_add = $shown_price - ($shown_price / (($product_tax_rate < 10) ? "1.0" . str_replace('.', '', $product_tax_rate) : "1." . str_replace('.', '', $product_tax_rate)));
      } else {
          // calculate the amount of tax for this product (assuming tax is NOT included in the price)
          $tax_add = ($product_tax_rate/100) * $shown_price;
      }

      $tax_add = $currencies->value($tax_add, false, $this->currency);
      $this->info['tax'] += $tax_add;

      foreach ($taxRates as $taxDescription=>$taxRate)
      {
          $taxAdd = $currencies->value(zen_calculate_tax($this->products[$index]['final_price']*$this->products[$index]['qty'], $taxRate), false, $this->currency)
                  + $currencies->value(zen_calculate_tax($this->products[$index]['onetime_charges'], $taxRate), false, $this->currency);
          if (isset($this->info['tax_groups'][$taxDescription]))
          {
              $this->info['tax_groups'][$taxDescription] += $taxAdd;
          } else
          {
              $this->info['tax_groups'][$taxDescription] = $taxAdd;
          }
      }
  }
  /**
   * Creates a new order from the object's arrays built from the cart() method triggered by the constructor
   * $order_totals is an array of order-totals calculated by checkout_process during checkout
   *
   * @param array $order_totals
   * @return int order number
   */
  public function create($order_totals) {
    global $db;

    $this->notify('NOTIFY_ORDER_CART_EXTERNAL_TAX_DURING_ORDER_CREATE', array(), $order_totals);

    if ($this->info['total'] == 0) {
      if (DEFAULT_ZERO_BALANCE_ORDERS_STATUS_ID == 0) {
        $this->info['order_status'] = (int)DEFAULT_ORDERS_STATUS_ID;
      } else {
        if ($_SESSION['payment'] != 'freecharger') {
          $this->info['order_status'] = (int)DEFAULT_ZERO_BALANCE_ORDERS_STATUS_ID;
        }
      }
    }
    $this->notify('NOTIFY_ORDER_CART_ORDERSTATUS');

    if (isset($_SESSION['shipping']['id']) && $_SESSION['shipping']['id'] == 'free_free') {
      $this->info['shipping_module_code'] = $_SESSION['shipping']['id'];
    }

    $this->info['order_weight'] = $_SESSION['shipping_weight'];

    // Sanitize cc-num if present, using maximum 10 chars, with middle chars stripped out with XX
    if (strlen($this->info['cc_number']) > 10) {
      $cEnd = substr($this->info['cc_number'], -4);
      $cOffset = strlen($this->info['cc_number']) -4;
      $cStart = substr($this->info['cc_number'], 0, ($cOffset > 4 ? 4 : (int)$cOffset));
      $this->info['cc_number'] = str_pad($cStart, 6, 'X') . $cEnd;
    };

    $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                            'customers_name' => $this->customer['firstname'] . ' ' . $this->customer['lastname'],
                            'customers_company' => $this->customer['company'],
                            'customers_street_address' => $this->customer['street_address'],
                            'customers_suburb' => $this->customer['suburb'],
                            'customers_city' => $this->customer['city'],
                            'customers_postcode' => $this->customer['postcode'],
                            'customers_state' => $this->customer['state'],
                            'customers_country' => $this->customer['country']['title'],
                            'customers_telephone' => $this->customer['telephone'],
                            'customers_email_address' => $this->customer['email_address'],
                            'customers_address_format_id' => $this->customer['format_id'],
                            'delivery_name' => $this->delivery['firstname'] . ' ' . $this->delivery['lastname'],
                            'delivery_company' => $this->delivery['company'],
                            'delivery_street_address' => $this->delivery['street_address'],
                            'delivery_suburb' => $this->delivery['suburb'],
                            'delivery_city' => $this->delivery['city'],
                            'delivery_postcode' => $this->delivery['postcode'],
                            'delivery_state' => $this->delivery['state'],
                            'delivery_country' => $this->delivery['country']['title'],
                            'delivery_address_format_id' => $this->delivery['format_id'],
                            'billing_name' => $this->billing['firstname'] . ' ' . $this->billing['lastname'],
                            'billing_company' => $this->billing['company'],
                            'billing_street_address' => $this->billing['street_address'],
                            'billing_suburb' => $this->billing['suburb'],
                            'billing_city' => $this->billing['city'],
                            'billing_postcode' => $this->billing['postcode'],
                            'billing_state' => $this->billing['state'],
                            'billing_country' => $this->billing['country']['title'],
                            'billing_address_format_id' => $this->billing['format_id'],
                            'payment_method' => (($this->info['payment_module_code'] == '' and $this->info['payment_method'] == '') ? PAYMENT_METHOD_GV : $this->info['payment_method']),
                            'payment_module_code' => (($this->info['payment_module_code'] == '' and $this->info['payment_method'] == '') ? PAYMENT_MODULE_GV : $this->info['payment_module_code']),
                            'shipping_method' => $this->info['shipping_method'],
                            'shipping_module_code' => (strpos($this->info['shipping_module_code'], '_') > 0 ? substr($this->info['shipping_module_code'], 0, strpos($this->info['shipping_module_code'], '_')) : $this->info['shipping_module_code']),
                            'coupon_code' => $this->info['coupon_code'],
                            'cc_type' => $this->info['cc_type'],
                            'cc_owner' => $this->info['cc_owner'],
                            'cc_number' => $this->info['cc_number'],
                            'cc_expires' => $this->info['cc_expires'],
                            'date_purchased' => 'now()',
                            'orders_status' => $this->info['order_status'],
                            'order_total' => $this->info['total'],
                            'order_tax' => $this->info['tax'],
                            'currency' => $this->info['currency'],
                            'currency_value' => $this->info['currency_value'],
                            'ip_address' => $_SESSION['customers_ip_address'] . ' - ' . zen_get_ip_address(),
                            'order_weight' => $this->info['order_weight'],
                            'language_code' => $this->info['language_code'],
                            );

    $this->notify('NOTIFY_ORDER_CREATE_SET_SQL_DATA_ARRAY', array(), $sql_data_array);
    zen_db_perform(TABLE_ORDERS, $sql_data_array);

    $this->id = $this->info['id'] = $db->Insert_ID();
    $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ORDER_HEADER', array_merge(array('orders_id' => $this->id, 'shipping_weight' => $this->info['order_weight']), $sql_data_array));

    for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
      $sql_data_array = array('orders_id' => $this->id,
                              'title' => $order_totals[$i]['title'],
                              'text' => $order_totals[$i]['text'],
                              'value' => (is_numeric($order_totals[$i]['value'])) ? $order_totals[$i]['value'] : '0',
                              'class' => $order_totals[$i]['code'],
                              'sort_order' => $order_totals[$i]['sort_order']);

      zen_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
      $ot_insert_id = $db->Insert_ID();
      $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ORDERTOTAL_LINE_ITEM', $sql_data_array, $ot_insert_id);
    }

    $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
    $sql_data_array = array('orders_id' => $this->id,
                            'orders_status_id' => $this->info['order_status'],
                            'date_added' => 'now()',
                            'customer_notified' => $customer_notification,
                            'comments' => $this->info['comments']);

    zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    $osh_insert_id = $db->Insert_ID();
    $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ORDER_COMMENT', $sql_data_array, $osh_insert_id);

    return $this->id;
  }

  /**
   * Adds the object's products to the orders_products table for the specified order number
   *
   * @param int $order_id
   */
  public function create_add_products($order_id) {
    global $db, $currencies, $order_total_modules;

    $this->total_weight = 0;
    $this->total_tax = 0;

    for ($i=0, $n=sizeof($this->products); $i<$n; $i++) {
      $custom_insertable_text = '';

      $this->decrement_stock($this->products[$i]);
      $this->notify('NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_END', $i, $this->products[$i]);

      $this->update_bestsellers($this->products[$i]);


      $sql_data_array = array('orders_id' => $order_id,
                              'products_id' => zen_get_prid($this->products[$i]['id']),
                              'products_model' => $this->products[$i]['model'],
                              'products_name' => $this->products[$i]['name'],
                              'products_price' => $this->products[$i]['price'],
                              'final_price' => $this->products[$i]['final_price'],
                              'onetime_charges' => $this->products[$i]['onetime_charges'],
                              'products_tax' => $this->products[$i]['tax'],
                              'products_quantity' => $this->products[$i]['qty'],
                              'products_priced_by_attribute' => $this->products[$i]['products_priced_by_attribute'],
                              'product_is_free' => $this->products[$i]['product_is_free'],
                              'products_discount_type' => $this->products[$i]['products_discount_type'],
                              'products_discount_type_from' => $this->products[$i]['products_discount_type_from'],
                              'products_prid' => $this->products[$i]['id'],
                              'products_weight' => $this->products[$i]['weight'],
                              'products_virtual' => $this->products[$i]['products_virtual'],
                              'product_is_always_free_shipping' => $this->products[$i]['product_is_always_free_shipping'],
                              'products_quantity_order_min' => $this->products[$i]['products_quantity_order_min'],
                              'products_quantity_order_units' => $this->products[$i]['products_quantity_order_units'],
                              'products_quantity_order_max' => $this->products[$i]['products_quantity_order_max'],
                              'products_quantity_mixed' => $this->products[$i]['products_quantity_mixed'],
                              'products_mixed_discount_quantity' => $this->products[$i]['products_mixed_discount_quantity'],
                              );
      zen_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

      $order_products_id = $db->Insert_ID();

      $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_PRODUCT_LINE_ITEM', array_merge(array('orders_products_id' => $order_products_id, 'i' => $i), $sql_data_array), $order_products_id);

      $this->notify('NOTIFY_ORDER_PROCESSING_CREDIT_ACCOUNT_UPDATE_BEGIN');
      $order_total_modules->update_credit_account($i);//ICW ADDED FOR CREDIT CLASS SYSTEM

      $this->notify('NOTIFY_ORDER_PROCESSING_ATTRIBUTES_BEGIN');

      //------ bof: insert customer-chosen options to order--------
      $attributes_exist = '0';
      $this->products_ordered_attributes = '';
      if (isset($this->products[$i]['attributes'])) {
        $attributes_exist = '1';
        for ($j=0, $n2=sizeof($this->products[$i]['attributes']); $j<$n2; $j++) {
          if (DOWNLOAD_ENABLED == 'true') {
            $attributes_query = "select popt.products_options_name, poval.products_options_values_name,
                                 pa.options_values_price, pa.price_prefix,
                                 pa.product_attribute_is_free, pa.products_attributes_weight, pa.products_attributes_weight_prefix,
                                 pa.attributes_discounted, pa.attributes_price_base_included, pa.attributes_price_onetime,
                                 pa.attributes_price_factor, pa.attributes_price_factor_offset,
                                 pa.attributes_price_factor_onetime, pa.attributes_price_factor_onetime_offset,
                                 pa.attributes_qty_prices, pa.attributes_qty_prices_onetime,
                                 pa.attributes_price_words, pa.attributes_price_words_free,
                                 pa.attributes_price_letters, pa.attributes_price_letters_free,
                                 pad.products_attributes_maxdays, pad.products_attributes_maxcount, pad.products_attributes_filename
                                 from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " .
                                  TABLE_PRODUCTS_ATTRIBUTES . " pa LEFT JOIN  " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad on pa.products_attributes_id=pad.products_attributes_id
                                 where pa.products_id = " . (int)$this->products[$i]['id'] . "
                                  and pa.options_id = " . (int)$this->products[$i]['attributes'][$j]['option_id'] . "
                                  and pa.options_id = popt.products_options_id
                                  and pa.options_values_id = " . (int)$this->products[$i]['attributes'][$j]['value_id'] . "
                                  and pa.options_values_id = poval.products_options_values_id
                                  and popt.language_id = " . (int)$this->language['id'] . "
                                  and poval.language_id = popt.language_id";

            $attributes_values = $db->Execute($attributes_query);
          } else {
            $attributes_values = $db->Execute("select popt.products_options_name, poval.products_options_values_name,
                                 pa.options_values_price, pa.price_prefix,
                                 pa.product_attribute_is_free, pa.products_attributes_weight, pa.products_attributes_weight_prefix,
                                 pa.attributes_discounted, pa.attributes_price_base_included, pa.attributes_price_onetime,
                                 pa.attributes_price_factor, pa.attributes_price_factor_offset,
                                 pa.attributes_price_factor_onetime, pa.attributes_price_factor_onetime_offset,
                                 pa.attributes_qty_prices, pa.attributes_qty_prices_onetime,
                                 pa.attributes_price_words, pa.attributes_price_words_free,
                                 pa.attributes_price_letters, pa.attributes_price_letters_free
                                 from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                 where pa.products_id = " . (int)$this->products[$i]['id'] . "
                                 and pa.options_id = " . (int)$this->products[$i]['attributes'][$j]['option_id'] . "
                                 and pa.options_id = popt.products_options_id 
                                 and pa.options_values_id = " . (int)$this->products[$i]['attributes'][$j]['value_id'] . "
                                 and pa.options_values_id = poval.products_options_values_id 
                                 and popt.language_id = " . (int)$this->language['id'] . "
                                 and poval.language_id = popt.language_id");
          }

          $sql_data_array = array('orders_id' => $order_id,
                                  'orders_products_id' => $order_products_id,
                                  'products_options' => $attributes_values->fields['products_options_name'],
                                  'products_options_values' => $this->products[$i]['attributes'][$j]['value'],
                                  'options_values_price' => $attributes_values->fields['options_values_price'],
                                  'price_prefix' => $attributes_values->fields['price_prefix'],
                                  'product_attribute_is_free' => $attributes_values->fields['product_attribute_is_free'],
                                  'products_attributes_weight' => $attributes_values->fields['products_attributes_weight'],
                                  'products_attributes_weight_prefix' => $attributes_values->fields['products_attributes_weight_prefix'],
                                  'attributes_discounted' => $attributes_values->fields['attributes_discounted'],
                                  'attributes_price_base_included' => $attributes_values->fields['attributes_price_base_included'],
                                  'attributes_price_onetime' => $attributes_values->fields['attributes_price_onetime'],
                                  'attributes_price_factor' => $attributes_values->fields['attributes_price_factor'],
                                  'attributes_price_factor_offset' => $attributes_values->fields['attributes_price_factor_offset'],
                                  'attributes_price_factor_onetime' => $attributes_values->fields['attributes_price_factor_onetime'],
                                  'attributes_price_factor_onetime_offset' => $attributes_values->fields['attributes_price_factor_onetime_offset'],
                                  'attributes_qty_prices' => $attributes_values->fields['attributes_qty_prices'],
                                  'attributes_qty_prices_onetime' => $attributes_values->fields['attributes_qty_prices_onetime'],
                                  'attributes_price_words' => $attributes_values->fields['attributes_price_words'],
                                  'attributes_price_words_free' => $attributes_values->fields['attributes_price_words_free'],
                                  'attributes_price_letters' => $attributes_values->fields['attributes_price_letters'],
                                  'attributes_price_letters_free' => $attributes_values->fields['attributes_price_letters_free'],
                                  'products_options_id' => (int)$this->products[$i]['attributes'][$j]['option_id'],
                                  'products_options_values_id' => (int)$this->products[$i]['attributes'][$j]['value_id'],
                                  'products_prid' => $this->products[$i]['id'],
                                  );

          zen_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

          $products_attributes_id = $db->Insert_ID();

          $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ATTRIBUTE_LINE_ITEM', array_merge(array('orders_products_attributes_id' => $products_attributes_id), $sql_data_array), $products_attributes_id);

          if (DOWNLOAD_ENABLED == 'true' && isset($attributes_values->fields['products_attributes_filename']) && zen_not_null($attributes_values->fields['products_attributes_filename'])) {
            $sql_data_array = array('orders_id' => $order_id,
                                    'orders_products_id' => $order_products_id,
                                    'orders_products_filename' => $attributes_values->fields['products_attributes_filename'],
                                    'download_maxdays' => $attributes_values->fields['products_attributes_maxdays'],
                                    'download_count' => $attributes_values->fields['products_attributes_maxcount'],
                                    'products_prid' => $this->products[$i]['id'],
                                    'products_attributes_id' => $products_attributes_id,
                                    );

            zen_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
            $opd_insert_id = $db->insert_ID();
            $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ATTRIBUTE_DOWNLOAD_LINE_ITEM', $sql_data_array, $opd_insert_id);
          }
          $this->products_ordered_attributes .= "\n\t" . $attributes_values->fields['products_options_name'] . ' ' . zen_decode_specialchars($this->products[$i]['attributes'][$j]['value']);
        }
      }
      //------eof: insert customer-chosen options ----
    $this->notify('NOTIFY_ORDER_PROCESSING_ATTRIBUTES_EXIST', $attributes_exist);

    $this->notify('NOTIFY_ORDER_DURING_CREATE_ADD_PRODUCTS', $i, $custom_insertable_text);
    $this->products_ordered_attributes .= $this->get_custom_product_attribute_text($this->products[$i], $this->products[$i]['attributes'], $custom_insertable_text);


      // update totals counters
      $this->total_weight += ($this->products[$i]['qty'] * $this->products[$i]['weight']);
      $this->total_tax += zen_calculate_tax($this->products[$i]['final_price'] * $this->products[$i]['qty'], $this->products[$i]['tax']);
      $this->total_cost += $this->products[$i]['final_price'] + $this->products[$i]['onetime_charges'];

      $this->notify('NOTIFY_ORDER_PROCESSING_ONE_TIME_CHARGES_BEGIN', $i);

      $this->build_product_text_for_confirmation_email($this->products[$i]);
    }

    $order_total_modules->apply_credit();//ICW ADDED FOR CREDIT CLASS SYSTEM
    $this->notify('NOTIFY_ORDER_AFTER_ORDER_CREATE_ADD_PRODUCTS');
  }

    /**
     * Prepare custom text to get added to product details, such as a software serial number or voucher code
     * EXPECTS YOU TO WRITE/ADD YOUR CUSTOM LOOKUP CODE HERE
     * Alternatively you could hook the NOTIFY_ORDER_DURING_CREATE_ADD_PRODUCTS and NOTIFY_ORDER_DURING_CREATE_ADDED_ATTRIBUTE_LINE_ITEM notifier hooks instead (which might be more flexible)
     *
     * @param array $product
     * @param array $attributes
     * @param string $custom_insertable_text
     * @return string
     */
    protected function get_custom_product_attribute_text($product, $attributes, $custom_insertable_text = '')
  {
      /* START: ADD MY CUSTOM DETAILS
       * 1. Calculate/prepare custom information to be added to this product entry in order-confirmation, perhaps as a function call to custom code to build a serial number etc:
       *    Some possible parameters to pass to custom functions at this point:
       *     Product ID ordered (for this line item): $product['id']
       *     Quantity ordered (of this line-item): $product['qty']
       *     Order number: $this->id
       *     Attribute Option Name ID: (int)$product['attributes'][$x]['option_id']
       *     Attribute Option Value ID: (int)$product['attributes'][$x]['value_id']
       *
       * 2. Add that data to the $custom_insertable_text, so that it is returned back into the description
       */

      // eg: I do something here, such as lookup a unique code for this product for this customer
      // and then I add it to $custom_insertable_text, before the following "return" statement.

      //$custom_insertable_text = 'whatever special value I need to give the customer';

      return $custom_insertable_text;
      /* END: ADD MY CUSTOM DETAILS */
  }
  /**
   * Update bestsellers count for specified product
   * @param array $product containing 'id' and 'qty' to increment by
   */
  public function update_bestsellers($product)
  {
      global $db;
      // Update products_ordered (for bestsellers list)
      $this->bestSellersUpdate = true;
      $this->notify('NOTIFY_ORDER_PROCESSING_BESTSELLERS_UPDATE', array(), $product);
      if ($this->bestSellersUpdate) {
          $db->Execute("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%f', $product['qty']) . " where products_id = " . zen_get_prid($product['id']));
      }

  }
  /**
   * Decrease stock-on-hand for the specified product based on the qty attributes in the passed array
   *
   * @param array $product Product whose inventory is being adjusted
   */
  public function decrement_stock($product)
  {
      global $db;
      $this->doStockDecrement = (STOCK_LIMITED == 'true');
      $this->notify('NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_INIT', array(), $products);
      if ($this->doStockDecrement === false) return;

      // first handle downloadable items
      if (DOWNLOAD_ENABLED == 'true') {
          $stock_query_raw = "SELECT p.products_quantity, pad.products_attributes_filename, p.product_is_always_free_shipping
                          FROM " . TABLE_PRODUCTS . " p
                          LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON p.products_id=pa.products_id
                          LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON pa.products_attributes_id=pad.products_attributes_id
                          WHERE p.products_id = " . zen_get_prid($product['id']);

          // Will work with only one option for downloadable products
          // otherwise, we have to build the query dynamically with a loop
          $products_attributes = $product['attributes'];
          if (is_array($products_attributes)) {
              $stock_query_raw .= " AND pa.options_id = " . $products_attributes[0]['option_id'] . " AND pa.options_values_id = " . $products_attributes[0]['value_id'];
          }
          $stock_values = $db->Execute($stock_query_raw, false, false, 0, true);
      } else {
          $stock_values = $db->Execute("select products_quantity, '' as products_attributes_filename, product_is_always_free_shipping from " . TABLE_PRODUCTS . " where products_id = " . zen_get_prid($product['id']), false, false, 0, true);
      }

      $this->notify('NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_BEGIN', $product, $stock_values);

      if ($stock_values->RecordCount() > 0) {
          // do not decrement quantities if products_attributes_filename exists
          if (DOWNLOAD_ENABLED != 'true' || $stock_values->fields['product_is_always_free_shipping'] == 2 || $stock_values->fields['products_attributes_filename'] != '') {
              $stock_left = $stock_values->fields['products_quantity'] - $product['qty'];
              $product['stock_reduce'] = $product['qty'];
          } else {
              $stock_left = $stock_values->fields['products_quantity'];
          }

          $db->Execute("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = " . zen_get_prid($product['id']));
          if ($stock_left <= 0) {
              // only set status to off when not displaying sold out
              if (SHOW_PRODUCTS_SOLD_OUT == '0') {
                  $db->Execute("update " . TABLE_PRODUCTS . " set products_status = 0 where products_id = " . zen_get_prid($product['id']));
              }
          }

          // add to message for low stock email
          if ( $stock_left <= STOCK_REORDER_LEVEL ) {
              $this->email_low_stock .=  'ID# ' . zen_get_prid($product['id']) . "\t\t" . $product['model'] . "\t\t" . $product['name'] . "\t\t" . ' Qty Left: ' . $stock_left . "\n";
          }
      }
  }
  /**
   * Sends an email to the storeowner summarizing the items that are now in low-stock status
   * as calculated by inventory-update actions when the create_add_products() method was called
   */
  protected function sendLowStockEmails()
  {
    $this->send_low_stock_emails = true;
    $this->notify('NOTIFY_ORDER_SEND_LOW_STOCK_EMAILS');
    if ($this->send_low_stock_emails && $this->email_low_stock != ''  && SEND_LOWSTOCK_EMAIL=='1') {
        $email_low_stock = SEND_EXTRA_LOW_STOCK_EMAIL_TITLE . "\n\n" . $this->email_low_stock;
        zen_mail('', SEND_EXTRA_LOW_STOCK_EMAILS_TO, EMAIL_TEXT_SUBJECT_LOWSTOCK, $email_low_stock, STORE_OWNER, EMAIL_FROM, array('EMAIL_MESSAGE_HTML' => nl2br($email_low_stock)),'low_stock');
    }
  }

    /**
     * Alias to send_order_confirmation_email()
     *
     * @deprecated since v1.6.0
     * @param $order_id
     */
  public function send_order_email($order_id) {
    return $this->send_order_confirmation_email($order_id);
  }

  /**
   * Build product-detail text strings that will be used in final order-confirmation email
   * @param array $product Data about current product, from which data will be assembled
   */
  public function build_product_text_for_confirmation_email($product)
  {
      global $currencies;
      $this->products_ordered .=  $product['qty'] . ' x ' . $product['name'] . ($product['model'] != '' ? ' (' . $product['model'] . ') ' : '') . ' = ' .
          $currencies->display_price($product['final_price'], $product['tax'], $product['qty']) .
          ($product['onetime_charges'] !=0 ? "\n" . TEXT_ONETIME_CHARGES_EMAIL . $currencies->display_price($product['onetime_charges'], $product['tax'], 1) : '') .
          $this->products_ordered_attributes . "\n";
      $this->products_ordered_html .=
          '<tr>' . "\n" .
          '<td class="product-details" align="right" valign="top" width="30">' . $product['qty'] . '&nbsp;x</td>' . "\n" .
          '<td class="product-details" valign="top">' . nl2br($product['name']) . ($product['model'] != '' ? ' (' . nl2br($product['model']) . ') ' : '') . "\n" .
          '<nobr>' .
          '<small><em> '. nl2br($this->products_ordered_attributes) .'</em></small>' .
          '</nobr>' .
          '</td>' . "\n" .
          '<td class="product-details-num" valign="top" align="right">' .
          $currencies->display_price($product['final_price'], $product['tax'], $product['qty']) .
          ($product['onetime_charges'] !=0 ?
              '</td></tr>' . "\n" . '<tr><td class="product-details">' . nl2br(TEXT_ONETIME_CHARGES_EMAIL) . '</td>' . "\n" .
              '<td>' . $currencies->display_price($product['onetime_charges'], $product['tax'], 1) : '') .
          '</td></tr>' . "\n";
  }
  /**
   * Sends order-confirmation email to customer and storeowner
   * Depends on the product-related content being prepared in advance by the create_add_products() method
   *
   * @param int $order_id order number to identify the order for which this email is being sent
   */
  public function send_order_confirmation_email($order_id) {
    global $currencies, $order_totals;

    $this->notify('NOTIFY_ORDER_SEND_EMAIL_INITIALIZE', array(), $order_id, $order_totals);

    if (!$order_id) $order_id = $this->id;

    $this->sendLowStockEmails();

    // prepare the email confirmation message details
    // make an array to store the html version of the email
    $html_msg = array();
    $email_order = '';

    $emailTextInvoiceText = EMAIL_TEXT_INVOICE_URL_CLICK;
    $emailTextInvoiceUrl =zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $order_id, 'SSL', false);

    $this->notify('NOTIFY_ORDER_SEND_EMAIL_SET_ORDER_LINK', array(), $emailTextInvoiceText, $emailTextInvoiceUrl, $order_id);

    $html_msg['EMAIL_TEXT_HEADER']     = EMAIL_TEXT_HEADER;
    $html_msg['EMAIL_TEXT_FROM']       = EMAIL_TEXT_FROM;
    $html_msg['INTRO_STORE_NAME']      = STORE_NAME;
    $html_msg['EMAIL_THANKS_FOR_SHOPPING'] = EMAIL_THANKS_FOR_SHOPPING;
    $html_msg['EMAIL_DETAILS_FOLLOW']  = EMAIL_DETAILS_FOLLOW;
    $html_msg['INTRO_ORDER_NUM_TITLE'] = EMAIL_TEXT_ORDER_NUMBER;
    $html_msg['INTRO_ORDER_NUMBER']    = $order_id;
    $html_msg['INTRO_DATE_TITLE']      = EMAIL_TEXT_DATE_ORDERED;
    $html_msg['INTRO_DATE_ORDERED']    = strftime(DATE_FORMAT_LONG);
    $html_msg['INTRO_URL_TEXT']        = $emailTextInvoiceText;
    $html_msg['INTRO_URL_VALUE']       = $emailTextInvoiceUrl;
    $html_msg['EMAIL_CUSTOMER_PHONE']  = $this->customer['telephone'];
    $html_msg['EMAIL_ORDER_DATE']      = date(ORDER_EMAIL_DATE_FORMAT);

    $email_order = EMAIL_TEXT_HEADER . EMAIL_TEXT_FROM . STORE_NAME . "\n\n" .
    $this->customer['firstname'] . ' ' . $this->customer['lastname'] . "\n\n" .
    EMAIL_THANKS_FOR_SHOPPING . "\n" . EMAIL_DETAILS_FOLLOW . "\n" .
    EMAIL_SEPARATOR . "\n" .
    EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_id . "\n" .
    EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n" .
        $emailTextInvoiceText . ' ' . $emailTextInvoiceUrl . "\n\n";

  //comments area
    $html_msg['ORDER_COMMENTS'] = '';
    if ($this->info['comments']) {
      $email_order .= zen_output_string_protected($this->info['comments']) . "\n\n";
      $html_msg['ORDER_COMMENTS'] = nl2br(zen_output_string_protected($this->info['comments']));
    }

    $this->notify('NOTIFY_ORDER_EMAIL_BEFORE_PRODUCTS', array(), $email_order, $html_msg);

    //products area
    $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
    EMAIL_SEPARATOR . "\n" .
    $this->products_ordered .
    EMAIL_SEPARATOR . "\n";
    $html_msg['PRODUCTS_TITLE'] = EMAIL_TEXT_PRODUCTS;
    $html_msg['PRODUCTS_DETAIL']='<table class="product-details" border="0" width="100%" cellspacing="0" cellpadding="2">' . $this->products_ordered_html . '</table>';

    //order totals area
    $html_ot = '<tr><td class="order-totals-text" align="right" width="100%">' . '&nbsp;' . '</td> ' . "\n" . '<td class="order-totals-num" align="right" nowrap="nowrap">' . '---------' .'</td> </tr>' . "\n";
    for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
      $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      $html_ot .= '<tr><td class="order-totals-text" align="right" width="100%">' . $order_totals[$i]['title'] . '</td> ' . "\n" . '<td class="order-totals-num" align="right" nowrap="nowrap">' .($order_totals[$i]['text']) .'</td> </tr>' . "\n";
    }
    $html_msg['ORDER_TOTALS'] = '<table border="0" width="100%" cellspacing="0" cellpadding="2"> ' . $html_ot . ' </table>';

    //addresses area: Delivery
    $html_msg['HEADING_ADDRESS_INFORMATION']= HEADING_ADDRESS_INFORMATION;
    $html_msg['ADDRESS_DELIVERY_TITLE']     = EMAIL_TEXT_DELIVERY_ADDRESS;
    $html_msg['ADDRESS_DELIVERY_DETAIL']    = ($this->content_type != 'virtual') ? zen_address_label($this->customer['id'], $this->delivery['address_book_id'], true, '', "<br />") : 'n/a';
    $html_msg['SHIPPING_METHOD_TITLE']      = HEADING_SHIPPING_METHOD;
    $html_msg['SHIPPING_METHOD_DETAIL']     = (zen_not_null($this->info['shipping_method'])) ? $this->info['shipping_method'] : 'n/a';

    if ($this->content_type != 'virtual') {
      $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
      EMAIL_SEPARATOR . "\n" .
      zen_address_label($this->customer['id'], $this->delivery['address_book_id'], false, '', "\n") . "\n";
    }

    if ($this->info['total'] > 0) {
      $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
      EMAIL_SEPARATOR . "\n" .
      zen_address_label($this->customer['id'], $this->billing['address_book_id'], false, '', "\n") . "\n\n";
      $html_msg['ADDRESS_BILLING_TITLE']   = EMAIL_TEXT_BILLING_ADDRESS;
      $html_msg['ADDRESS_BILLING_DETAIL']  = zen_address_label($this->customer['id'], $this->billing['address_book_id'], true, '', "<br />");
//     $html_msg['ADDRESS_BILLING_DETAIL'] .= $this->customer['telephone'] . '<br />';
    } else {
      $html_msg['ADDRESS_BILLING_TITLE']   = '';
      $html_msg['ADDRESS_BILLING_DETAIL']  = ' <br />';
    }
    $payment_class = $this->info['payment_module_code'];
    if (is_object($GLOBALS[$payment_class])) {
      $cc_num_display = (isset($this->info['cc_number']) && $this->info['cc_number'] != '') ? /*substr($this->info['cc_number'], 0, 4) . */ str_repeat('X', (strlen($this->info['cc_number']) - 8)) . substr($this->info['cc_number'], -4) . "\n\n" : '';
      $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
      EMAIL_SEPARATOR . "\n";
      $email_order .= $this->info['payment_module']. "\n\n";
      $email_order .= (isset($this->info['cc_type']) && $this->info['cc_type'] != '') ? $this->info['cc_type'] . ' ' . $cc_num_display . "\n\n" : '';
      $email_order .= ($GLOBALS[$payment_class]->email_footer) ? $GLOBALS[$payment_class]->email_footer . "\n\n" : '';
      $html_msg['PAYMENT_METHOD_DETAIL'] = $GLOBALS[$payment_class]->title;
      $html_msg['PAYMENT_METHOD_FOOTER'] = ($GLOBALS[$payment_class]->email_footer != '') ? nl2br($GLOBALS[$payment_class]->email_footer) : (isset($this->info['cc_type']) && $this->info['cc_type'] != '' ? $this->info['cc_type'] . ' ' . $cc_num_display . "\n\n" : '');
    } else {
      $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
      EMAIL_SEPARATOR . "\n";
      $email_order .= PAYMENT_METHOD_GV . "\n\n";
      $html_msg['PAYMENT_METHOD_DETAIL'] = PAYMENT_METHOD_GV;
      $html_msg['PAYMENT_METHOD_FOOTER'] = '';
    }
    $html_msg['PAYMENT_METHOD_TITLE']  = EMAIL_TEXT_PAYMENT_METHOD;

    // include disclaimer
    if (defined('EMAIL_DISCLAIMER') && EMAIL_DISCLAIMER != '') $email_order .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";
    // include copyright
    if (defined('EMAIL_FOOTER_COPYRIGHT')) $email_order .= "\n-----\n" . EMAIL_FOOTER_COPYRIGHT . "\n\n";

    $email_order = str_replace('&nbsp;', ' ', $email_order);

    $html_msg['EMAIL_FIRST_NAME'] = $this->customer['firstname'];
    $html_msg['EMAIL_LAST_NAME'] = $this->customer['lastname'];
    //  $html_msg['EMAIL_TEXT_HEADER'] = EMAIL_TEXT_HEADER;

    $html_msg['EXTRA_INFO'] = '';
    $this->notify('NOTIFY_ORDER_INVOICE_CONTENT_READY_TO_SEND', array('order_id' => $order_id, 'text_email' => $email_order, 'html_email' => $html_msg), $email_order, $html_msg);
    zen_mail($this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $order_id, $email_order, STORE_NAME, EMAIL_FROM, $html_msg, 'checkout', $this->attachArray);

    // send additional emails
    if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
      $extra_info = email_collect_extra_info('', '', $this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], $this->customer['telephone']);
      $html_msg['EXTRA_INFO'] = $extra_info['HTML'];

      // include authcode and transaction id in admin-copy of email
      if ($GLOBALS[$payment_class]->auth_code || $GLOBALS[$payment_class]->transaction_id) {
        $pmt_details = ($GLOBALS[$payment_class]->auth_code != '' ? 'AuthCode: ' . $GLOBALS[$payment_class]->auth_code . '  ' : '') . ($GLOBALS[$payment_class]->transaction_id != '' ?  'TransID: ' . $GLOBALS[$payment_class]->transaction_id : '') . "\n\n";
        $email_order = $pmt_details . $email_order;
        $html_msg['EMAIL_TEXT_HEADER'] = nl2br($pmt_details) . $html_msg['EMAIL_TEXT_HEADER'];
      }

      // Add extra heading stuff via observer class
      $this->extra_header_text = '';
      $this->notify('NOTIFY_ORDER_INVOICE_CONTENT_FOR_ADDITIONAL_EMAILS', $order_id, $email_order, $html_msg);
      $email_order = $this->extra_header_text . $email_order;
      $html_msg['EMAIL_TEXT_HEADER'] = nl2br($this->extra_header_text) . $html_msg['EMAIL_TEXT_HEADER'];

      zen_mail('', SEND_EXTRA_ORDER_EMAILS_TO, SEND_EXTRA_NEW_ORDERS_EMAILS_TO_SUBJECT . ' ' . EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $order_id,
      $email_order . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg, 'checkout_extra', $this->attachArray, $this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address']);
    }
    $this->notify('NOTIFY_ORDER_AFTER_SEND_ORDER_EMAIL', $order_id, $email_order, $extra_info, $html_msg);
  }

    /**
     * Reset download count and max_days for the specified orders_products_download_id on the current order
     * Used by toggle on admin order screen, and on update_status_and_comments()
     *
     * @param int $orders_products_download_id
     * @param int $order_id
     * @return bool
     */
  public function resetSingleDownloadToOn($orders_products_download_id = 0, $order_id = null)
  {
    global $db;
    if ((int)$orders_products_download_id == 0) return false;
    if (!$order_id) $order_id = $this->id;

    // get existing download days and max, if present
    $sql = "SELECT pad.products_attributes_maxdays, pad.products_attributes_maxcount
            from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
            WHERE pa.products_attributes_id = pad.products_attributes_id
            and pad.products_attributes_filename = opd.orders_products_filename
            and pa.products_id = opd.products_prid
            and opd.orders_products_download_id=" . (int)$orders_products_download_id;
    $result = $db->Execute($sql);

    // defaults
    $days = DOWNLOAD_MAX_DAYS;
    $count = DOWNLOAD_MAX_COUNT;

    // override defaults with db values, if exist
    if (count($result)) {
      $days = $result->fields['products_attributes_maxdays'];
      $count = $result->fields['products_attributes_maxcount'];
    }
    // prepare and execute SQL
    $zc_max_days = ($days == 0 ? 0 : zen_date_diff($this->info['date_purchased'], date('Y-m-d H:i:s', time())) + $days);
    $sql = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays=" . $zc_max_days . ", download_count=" . $count . " where orders_id=" . $order_id . " and orders_products_download_id=" . (int)$orders_products_download_id;
    $db->Execute($sql);
  }

    /**
     * disable specified download item by resetting count to 0
     *
     * @param int $orders_products_download_id
     * @return bool
     */
  public function resetSingleDownloadToOff($orders_products_download_id = 0)
  {
    global $db;
    if ((int)$orders_products_download_id == 0) return false;
    $sql = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_count=0 where orders_id=" . (int)$this->id . " and orders_products_download_id=" . (int)$orders_products_download_id;
    $db->Execute($sql);
  }

    /**
     * Re-enable all downloads for the specified or current order
     *
     * @param int $order_id
     * @return bool|int
     */
  public function reEnableAllDownloads($order_id = null)
  {
    global $db;
    if (!$order_id) $order_id = $this->id;

    // retrieve all downloadable attribute IDs for this order
    $sql = "SELECT opd.orders_products_download_id from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd, " . TABLE_ORDERS_PRODUCTS . " op
            WHERE op.orders_id=" . (int)$order_id . "
            AND opd.orders_products_id = op.orders_products_id";
    $result = $db->Execute($sql);
    if ($result->EOF) return false;

    foreach($result as $row) {
      $this->resetSingleDownloadToOn($row['orders_products_download_id'], $order_id);
    }
    return count($result);
  }


    /**
     * Update order status and comments
     * @TODO - deduplicate --- where did all this duplication originate from?
     *
     * @param int $status
     * @param string $comments
     * @param bool $send_notify_email
     * @param string $notify_include_comments (only 'on' is tested)
     * @param null $fields (optional, used only by notifier/observer)
     * @return bool
     */
    public function update_status_and_comments($status = 0, $comments = '', $send_notify_email = true, $notify_include_comments = 'on', $fields = null)
  {
    global $db;
    $order_updated = $customer_notified = false;
    $status = (int)$status;
    $comments = $db->prepare_input($comments);
    $notify_comments = '';
    $order_number_string = $this->id; // for email text
    $html_msg = array();
    $message = '';

    $this->notify('NOTIFY_ORDER_SEND_STATUS_EMAIL_INITIALIZE', $this->id, $status, $comments, $send_notify_email, $notify_include_comments, $fields);

    // note: this refers to the *admin* language file named orders_email.php
    zen_load_language_file('orders_email.php', $this->language['directory']);

    // get PayPal Trans ID, if any
    $paypal_txn = $html_msg['EMAIL_PAYPAL_TRANSID'] = '';
    $sql = "select txn_id, parent_txn_id from " . TABLE_PAYPAL . " where order_id = :orderID order by last_modified DESC, date_added DESC, parent_txn_id DESC, paypal_ipn_id DESC ";
    $sql = $db->bindVars($sql, ':orderID', $this->id, 'integer');
    $result = $db->Execute($sql);
    if ($result->RecordCount() > 0) {
      $paypal_txn = $result->fields['txn_id'];
    }

    if ($this->info['orders_status'] != $status || zen_not_null($comments)) {
      $db->Execute("update " . TABLE_ORDERS . "
                    set orders_status = " . (int)$status . ", last_modified = now()
                    where orders_id = " . $this->id);

      // obtain localized status name
      $ordersStatusLocalizedQuery = $db->Execute("SELECT orders_status_name
                                                  FROM " . TABLE_ORDERS_STATUS . "
                                                  WHERE language_id = " . (int)$this->language['id'] . "
                                                  AND orders_status_id = " . $status);
      $ordersStatusLocalized = $ordersStatusLocalizedQuery->fields['orders_status_name'];

      $customer_notified = 0;
      if ($send_notify_email == 1) {
        if ($notify_include_comments == 'on' && zen_not_null($comments)) {
          $notify_comments = EMAIL_TEXT_COMMENTS_UPDATE . $comments . "\n\n";
        }

        //send emails
        if (GUEST_ORDER_STATUS == 'true') {
          if ($this->info['is_guest_order'] == 1)  {

            $message =
                EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_number_string . "\n\n" .
                EMAIL_TEXT_GUEST_ORDER_STATUS_URL . ' ' . zen_catalog_href_link(FILENAME_ORDER_STATUS, 'order_id=' . $this->id, 'SSL') . "\n\n" .
                EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($this->info['date_purchased']) . "\n\n" .
                $notify_comments .
                EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $ordersStatusLocalized ) .
                EMAIL_TEXT_STATUS_PLEASE_REPLY;

            $html_msg['EMAIL_CUSTOMERS_NAME']    = $this->customer['name'];
            $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_number_string;
            $html_msg['EMAIL_TEXT_INVOICE_URL']  = '<a href="' . zen_catalog_href_link(FILENAME_ORDER_STATUS, 'order_id=' . $this->id, 'SSL') .'">'.str_replace(':','',EMAIL_TEXT_GUEST_ORDER_STATUS_URL).'</a>';
            $html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($this->info['date_purchased']);
            $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
            $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n','', EMAIL_TEXT_STATUS_UPDATED);
            $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n','', sprintf(EMAIL_TEXT_STATUS_LABEL, $ordersStatusLocalized));
            $html_msg['EMAIL_TEXT_NEW_STATUS'] = $ordersStatusLocalized;
            $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n','', EMAIL_TEXT_STATUS_PLEASE_REPLY);

            if ($paypal_txn != '') {
              $message .= "\n\n" . ' PayPal Trans ID: ' . $result->fields['txn_id'];
              $html_msg['EMAIL_PAYPAL_TRANSID'] = $result->fields['txn_id'];
            }

            zen_mail($this->customer['name'], $this->customer['email_address'], EMAIL_TEXT_SUBJECT . ' #' . $order_number_string, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');
            $customer_notified = 1;
          }
        }
        if (GUEST_ORDER_STATUS == 'false') {
          if ($this->info['is_guest_order'] == 1)  {
            $message =
              EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_number_string . "\n\n" .
              EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($this->info['date_purchased']) . "\n\n" .
              $notify_comments .
              EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $ordersStatusLocalized) .
              EMAIL_TEXT_STATUS_PLEASE_REPLY;
            $html_msg['EMAIL_CUSTOMERS_NAME']    = $this->customer['name'];
            $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_number_string;
            $html_msg['INTRO_URL_TEXT']        = '';
            $html_msg['INTRO_URL_VALUE']       = '';
            $html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($this->info['date_purchased']);
            $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
            $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n','', EMAIL_TEXT_STATUS_UPDATED);
            $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n','', sprintf(EMAIL_TEXT_STATUS_LABEL, $ordersStatusLocalized));
            $html_msg['EMAIL_TEXT_NEW_STATUS'] = $ordersStatusLocalized;
            $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n','', EMAIL_TEXT_STATUS_PLEASE_REPLY);

            if ($paypal_txn != '') {
              $message .= "\n\n" . ' PayPal Trans ID: ' . $result->fields['txn_id'];
              $html_msg['EMAIL_PAYPAL_TRANSID'] = $result->fields['txn_id'];
            }

            zen_mail($this->customer['name'], $this->customer['email_address'], EMAIL_TEXT_SUBJECT . ' #' . $order_number_string, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');
            $customer_notified = 1;
          }
        }
        if ($this->info['is_guest_order'] != 1)  {
          $message =
            EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_number_string . "\n\n" .
            EMAIL_TEXT_INVOICE_URL . ' ' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $order_number_string, 'SSL') . "\n\n" .
            EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($this->info['date_purchased']) . "\n\n" .
            $notify_comments .
            EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $ordersStatusLocalized) .
            EMAIL_TEXT_STATUS_PLEASE_REPLY;

          $html_msg['EMAIL_CUSTOMERS_NAME']    = $this->customer['name'];
          $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_number_string;
          $html_msg['EMAIL_TEXT_INVOICE_URL']  = '<a href="' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->id, 'SSL') .'">'.str_replace(':','',EMAIL_TEXT_INVOICE_URL).'</a>';
          $html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($this->info['date_purchased']);
          $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
          $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n','', EMAIL_TEXT_STATUS_UPDATED);
          $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n','', sprintf(EMAIL_TEXT_STATUS_LABEL, $ordersStatusLocalized));
          $html_msg['EMAIL_TEXT_NEW_STATUS'] = $ordersStatusLocalized;
          $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n','', EMAIL_TEXT_STATUS_PLEASE_REPLY);

          if ($paypal_txn != '') {
            $message .= "\n\n" . ' PayPal Trans ID: ' . $result->fields['txn_id'];
            $html_msg['EMAIL_PAYPAL_TRANSID'] = $result->fields['txn_id'];
          }

          zen_mail($this->customer['name'], $this->customer['email_address'], EMAIL_TEXT_SUBJECT . ' #' . $order_number_string, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');
          $customer_notified = 1;
        }


        //send extra emails
        if (SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS == '1' and SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO != '') {
          zen_mail('', SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . EMAIL_TEXT_SUBJECT . ' #' . $order_number_string, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status_extra');
        }
      } elseif ($send_notify_email == -1) {
        // hide comment
        $customer_notified = -1;
      }

      $db->Execute("insert into " . TABLE_ORDERS_STATUS_HISTORY . "
                  (orders_id, orders_status_id, date_added, customer_notified, comments)
                  values ('" . (int)$this->id . "',
                  '" . (int)$status . "',
                  now(),
                  '" . (int)$customer_notified . "',
                  '" . zen_db_input($comments)  . "')");
      $order_updated = true;
    }

    // re-enable any downloads, if status matches magic reset status
    if ($order_updated && $status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) {
      $this->reEnableAllDownloads();
    }

    // trigger any appropriate updates which should be sent back to the payment gateway:
    if ($this->info['payment_module_code']) {
      if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php')) {
        require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php');
        require_once(DIR_FS_CATALOG_LANGUAGES . $this->language['directory'] . '/modules/payment/' . $this->info['payment_module_code'] . '.php');
        $module = new $this->info['payment_module_code'];
        if (method_exists($module, '_doStatusUpdate')) {
          $response = $module->_doStatusUpdate($this->id, $status, $comments, $customer_notified, $this->info['orders_status']);
        }
      }
    }

    zen_record_admin_activity('Order ' . $this->id . ' updated.', 'info');
    $this->notify('ADMIN_ORDER_UPDATED', $this->id, $fields);
    return $order_updated;
  }

  /**
   * Delete the specified order from the database, optionally returning product counts back into inventory
   */
  public function delete_order($order_id, $restock = false) {
    global $db;
    $this->notify('NOTIFIER_ADMIN_ZEN_REMOVE_ORDER', array(), $order_id, $restock);
    if ($restock === true) {
      $result = $db->Execute("select products_id, products_quantity
                              from " . TABLE_ORDERS_PRODUCTS . "
                              where orders_id = " . (int)$order_id);
      foreach($result as $row)
      {
        $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                      SET products_quantity = products_quantity + " . $row['products_quantity'] . ", products_ordered = products_ordered - " . $row['products_quantity'] . " 
                      WHERE products_id = " . (int)$row['products_id']);
      }
    }

    $db->Execute("delete from " . TABLE_ORDERS . " where orders_id = " . (int)$order_id);
    $db->Execute("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = " . (int)$order_id);
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = " . (int)$order_id);
    $db->Execute("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = " . (int)$order_id);
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = " . (int)$order_id);
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = " . (int)$order_id);
    $db->Execute("delete from " . TABLE_COUPON_GV_QUEUE . " where order_id = " . (int)$order_id . " and release_flag = 'N'");

    zen_record_admin_activity('Deleted order ' . (int)$order_id . ' from database via admin console.', 'warning');
  }

  /**
   * Purge inappropriately stored cvv data if found
   * This shouldn't be needed, but is provided in case certain 3rd-party payment modules had done this
   */
  public function delete_cvv($order_id) 
  {
    global $db;
    $db->Execute("update " . TABLE_ORDERS . " set cc_cvv = '" . TEXT_DELETE_CVV_REPLACEMENT . "' where orders_id = " . (int)$order_id);
  }

  /**
   * Mask inappropriately stored cc data if found
   * This shouldn't be needed, but is provided in case certain 3rd-party payment modules had done this
   */
  public function mask_cc($order_id)
  {
    global $db;
    $result  = $db->Execute("select cc_number from " . TABLE_ORDERS . " where orders_id = " . (int)$order_id);
    if ($result->EOF) return false;
    $old_num = $result->fields['cc_number'];
    $new_num = substr($old_num, 0, 4) . str_repeat('*', (strlen($old_num) - 8)) . substr($old_num, -4);
    $db->Execute("update " . TABLE_ORDERS . " set cc_number = '" . $new_num . "' where orders_id = " . (int)$order_id);
  }

    /**
     * Trigger the doRefund() method of the order's payment module
     *
     * @TODO - handle partial refunds?
     *
     * @param int $order_id
     */
    public function doRefund($order_id = null)
  {
    if (!$order_id) $order_id = $this->id;
    if (!$this->info['payment_module_code']) return;
    if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php')) {
      require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php');
      require_once(DIR_FS_CATALOG_LANGUAGES . $this->language['directory'] . '/modules/payment/' . $this->info['payment_module_code'] . '.php');
      $module = new $this->info['payment_module_code'];
      if (method_exists($module, '_doRefund')) {
        $module->_doRefund($order_id);
        $this->notify('ADMIN_ORDER_REFUNDED', $order_id);
        zen_record_admin_activity('Order ' . $order_id . ' refund processed. See order comments for details.', 'info');
      }
    }
  }

    /**
     * Trigger the doAuth() method of the order's payment module
     *
     * @param int $order_id
     */
    public function doAuth($order_id = null)
  {
    if (!$order_id) $order_id = $this->id;
    if (!$this->info['payment_module_code']) return;
    if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php')) {
      require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php');
      require_once(DIR_FS_CATALOG_LANGUAGES . $this->language['directory'] . '/modules/payment/' . $this->info['payment_module_code'] . '.php');
      $module = new $this->info['payment_module_code'];
      if (method_exists($module, '_doAuth')) {
        $module->_doAuth($order_id, $this->info['total'], $this->info['currency']);
        $this->notify('ADMIN_ORDER_PAYMENT_AUTH', $order_id, $this->info['total'], $this->info['currency']);
        zen_record_admin_activity('Order ' . $order_id . ' auth processed. See order comments for details.', 'info');
      }
    }
  }

    /**
     * Trigger the doCapture() method of the order's payment module
     *
     * @param int $order_id
     */
    public function doCapture($order_id = null)
  {
    if (!$order_id) $order_id = $this->id;
    if (!$this->info['payment_module_code']) return;
    if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php')) {
      require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php');
      require_once(DIR_FS_CATALOG_LANGUAGES . $this->language['directory'] . '/modules/payment/' . $this->info['payment_module_code'] . '.php');
      $module = new $this->info['payment_module_code'];
      if (method_exists($module, '_doCapt')) {
        $module->_doCapt($order_id, 'Complete', $this->info['total'], $this->info['currency']);
        $this->notify('ADMIN_ORDER_PAYMENT_CAPTURE', $order_id, $this->info['total'], $this->info['currency']);
        zen_record_admin_activity('Order ' . $order_id . ' capture processed. See order comments for details.', 'info');
      }
    }
  }

    /**
     * Trigger the doVoid() method of the order's payment module
     *
     * @param int $order_id
     */
    public function doVoid($order_id = null)
  {
    if (!$order_id) $order_id = $this->id;
    if (!$this->info['payment_module_code']) return;
    if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php')) {
      require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $this->info['payment_module_code'] . '.php');
      require_once(DIR_FS_CATALOG_LANGUAGES . $this->language['directory'] . '/modules/payment/' . $this->info['payment_module_code'] . '.php');
      $module = new $this->info['payment_module_code'];
      if (method_exists($module, '_doVoid')) {
        $module->_doVoid($order_id);
        $this->notify('ADMIN_ORDER_PAYMENT_VOID', $order_id);
        zen_record_admin_activity('Order ' . $order_id . ' void processed. See order comments for details.', 'info');
      }
    }
  }

  public function doGetPaymentDetailsFromGateway($order_id = null)
  {
    if (!$order_id) $order_id = $this->id;
    if (!$this->info['payment_module_code']) return;
    // @TODO future
  }

    /**
     * Build order search query, mainly used in Admin order-search screen
     *
     * @param string $keywords
     * @param string $products_search_string string beginning with "ID:" followed by product ID number
     * @param int $customer_id filter by customer id
     * @param int $status filter by status
     * @param string $sort_order
     * @return string
     */
  public function build_search_query($keywords = null, $products_search_string = null, $customer_id = null, $status = null, $sort_order = 'desc')
  {
    global $db, $zco_notifier;
    $search = '';
    $new_table = '';
    $new_fields = '';
    $search_distinct = '';

    // Only one or the other search
    
    // create search_orders_products filter
    if (isset($products_search_string) && zen_not_null($products_search_string)) {
      $keywords = $db->prepare_input($products_search_string);
      $search_distinct = ' distinct ';
      $new_table = " left join " . TABLE_ORDERS_PRODUCTS . " op on (op.orders_id = o.orders_id) ";
      $search = " and (op.products_model like '%" . $keywords . "%' or op.products_name like '" . $keywords . "%')";
      if (substr(strtoupper($products_search_string), 0, 3) == 'ID:') {
        $keywords = trim(substr($products_search_string, 3));
        $search = " and op.products_id = " . (int)$keywords; // the (int) is intentional in this case because its looking up a product id
      }
    } else {
      // create search filter
      if (isset($keywords) && zen_not_null($keywords)) {
        $keywords = $db->prepare_input($keywords);
        $search_distinct = ' ';
        $search = " and (o.customers_city like '%" . $keywords . "%' 
                    or o.customers_postcode like '%" . $keywords . "%' 
                    or o.date_purchased like '%" . $keywords . "%' 
                    or o.billing_name like '%" . $keywords . "%' 
                    or o.billing_company like '%" . $keywords . "%' 
                    or o.billing_street_address like '%" . $keywords . "%' 
                    or o.delivery_city like '%" . $keywords . "%' 
                    or o.delivery_postcode like '%" . $keywords . "%' 
                    or o.delivery_name like '%" . $keywords . "%' 
                    or o.delivery_company like '%" . $keywords . "%' 
                    or o.delivery_street_address like '%" . $keywords . "%' 
                    or o.billing_city like '%" . $keywords . "%' 
                    or o.billing_postcode like '%" . $keywords . "%' 
                    or o.customers_email_address like '%" . $keywords . "%' 
                    or o.customers_name like '%" . $keywords . "%' 
                    or o.customers_company like '%" . $keywords . "%' 
                    or o.customers_street_address  like '%" . $keywords . "%' 
                    or o.customers_telephone like '%" . $keywords . "%' 
                    or o.ip_address like '%" . $keywords . "%')";
        $new_table = '';
      }
    } // eof: search orders or orders_products

    $new_fields = ", o.customers_company, o.customers_email_address, o.customers_street_address, o.delivery_company, o.delivery_name, o.delivery_street_address, o.billing_company, o.billing_name, o.billing_street_address, o.payment_module_code, o.shipping_module_code, o.ip_address, o.language_code ";


    $orders_query_raw = "select " . $search_distinct . " o.orders_id, o.customers_id, o.customers_name, o.payment_method, o.shipping_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total" .
                          $new_fields . "
                          from (" . TABLE_ORDERS . " o " .
                          $new_table . ")
                          left join " . TABLE_ORDERS_STATUS . " s on (o.orders_status = s.orders_status_id and s.language_id = " . (int)$_SESSION['languages_id'] . ")
                          left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and ot.class = 'ot_total') ";

    if (isset($customer_id)) {
      $orders_query_raw .= " WHERE o.customers_id = " . (int)$customer_id;

    } elseif ($status) {
      $orders_query_raw .= " WHERE s.orders_status_id = " . (int)$status . $search;

    } else {
      $orders_query_raw .= (trim($search) != '') ? preg_replace('/ *AND /i', ' WHERE ', $search) : '';
    }

    if ($sort_order == 'desc') {
      $orders_query_raw .= " order by o.orders_id DESC";
    }

    $zco_notifier->notify('NOTIFY_BUILD_ORDER_SEARCH_QUERY_RAW', [], $orders_query_raw);
    return $orders_query_raw;
  }


}
