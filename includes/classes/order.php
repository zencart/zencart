<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Oct 22 Modified in v2.1.0 $
 */
/**
 * order class
 *
 * Prepares order from cart contents, and populates an order from database history
 * Stores new orders and sends order confirmation emails
 *
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class order extends base
{

    /**
     * $attachArray is an array of file names to be attached to the email
     * @var array
     */
    public $attachArray = [];
    /**
     * $bestSellersUpdate is a flag used in Notifier to prevent updating of the best sellers details.
     * @var boolean
     */
    public $bestSellersUpdate;
    /**
     * $billing is an array containing the billing details for the order
     * @var array
     */
    public $billing = [];
    /**
     * $content_type is the overall content type of order
     * @var string "mixed","physical","virtual"
     */
    public $content_type;
    /**
     * $customer is an array containing information about the customer for the order
     * @var array
     */
    public $customer = [];
    /**
     * $delivery is an array containing delivery details for the order
     * @var array
     */
    public $delivery = [];
    /**
     * $doStockDecrement is a flag used by a notifier to prevent the default stock decrement processing
     * @var boolean
     */
    public $doStockDecrement;
    /**
     * $extra_header_text is a string containing header text to be added to email
     * @var string
     */
    public $extra_header_text;
    /**
     * $email_low_stock is the contents of the email to be sent if stock is low
     * @var string
     */
    public $email_low_stock;
    /**
     * $email_order_message is a string containing the store order message
     * @var string
     */
    public $email_order_message;
    /**
     * $info is an array containing general information about the order
     * @var array
     */
    public $info = [];
    /**
     * $orderId is the order identifier.
     * @var int
     */
    protected $orderId = null;
    /**
     * $products is an array containing details of the products for the order
     * @var array
     */
    public $products = [];
    /**
     * $products_ordered a plain text string containing the details of products order for email
     * @var string
     */
    public $products_ordered;
    /**
     * $products_ordered_attributes is a string containing the products attributes
     * @var string
     */
    public $products_ordered_attributes;
    /**
     * $products_ordered_html is an HTML formatted string containing details of the products ordered for email
     * @var string
     */
    public $products_ordered_html;
    /**
     * $queryReturnFlag is a flag used in a notifier to prevent default processing of order query.
     * @var boolean
     */
    public $queryReturnFlag;
    /**
     * $send_low_stock_emails is a flag to indicate if a low stock email should be send. It may be modified by a notifier
     * @var boolean
     */
    public $send_low_stock_emails;
    /**
     * $statuses is an array containing the status history information for the order
     * @var array
     */
    public $statuses = [];
    /**
     * $total_cost is the total cost of the order
     * @var float
     */
    public $total_cost;
    /**
     * $total_tax is the total amount of tax for the order
     * @var float
     */
    public $total_tax;
    /**
     * $total_weight is the total weight of the order
     * @var float
     */
    public $total_weight;
    /**
     * $totals is an array of order total information
     * @var array
     */
    public $totals = [];
    /**
     * $use_external_tax_handler_only is a flag used by notifier to prevent default tax calculation.
     * @var boolean
     */
    public $use_external_tax_handler_only;

    function __construct($order_id = null)
    {
        $this->info = [];
        $this->totals = [];
        $this->products = [];
        $this->customer = [];
        $this->delivery = [];

        $this->notify('NOTIFY_ORDER_INSTANTIATE', [], $order_id);
        if (!empty($order_id)) {
            $this->query($order_id);
        } else {
            $this->cart();
        }
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    function query($order_id)
    {
        global $db;

        $this->queryReturnFlag = null;
        $this->notify('NOTIFY_ORDER_BEFORE_QUERY', [], $order_id);
        if ($this->queryReturnFlag === true) return false;

        $order_query = "SELECT * FROM " . TABLE_ORDERS . " where orders_id = " . (int)$order_id;
        $order = $db->Execute($order_query);

        if ($order->EOF) return false;

        $this->orderId = $order_id = (int)$order_id;

        $totals_query = "SELECT title, text, class, value
                         FROM " . TABLE_ORDERS_TOTAL . "
                         WHERE orders_id = " . (int)$this->orderId . "
                         ORDER BY sort_order";

        $totals = $db->Execute($totals_query);

        $precision = QUANTITY_DECIMALS > 0 ? (int)QUANTITY_DECIMALS : 0;

        while (!$totals->EOF) {
            if ($totals->fields['class'] == 'ot_coupon') {
                $coupon_link_query = "SELECT coupon_id
                                      FROM " . TABLE_COUPONS . "
                                      WHERE coupon_code ='" . zen_db_input($order->fields['coupon_code']) . "'";
                $coupon_link = $db->Execute($coupon_link_query);

                $zc_coupon_link = '';

                if (!$coupon_link->EOF) {
                    if (IS_ADMIN_FLAG === true) {
                        $zc_coupon_link = '<a href="javascript:couponpopupWindow(\'' . zen_catalog_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $coupon_link->fields['coupon_id']) . '\')">';
                    } else {
                        $zc_coupon_link = '<a href="javascript:couponpopupWindow(\'' . zen_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $coupon_link->fields['coupon_id']) . '\')">';
                    }
                    $this->notify('NOTIFY_ORDER_COUPON_LINK', $coupon_link->fields, $zc_coupon_link);
                }
            }
            $this->totals[] = [
                'title' => ($totals->fields['class'] == 'ot_coupon' ? $zc_coupon_link . $totals->fields['title'] . '</a>' : $totals->fields['title']),
                'text' => $totals->fields['text'],
                'class' => $totals->fields['class'],
                'value' => $totals->fields['value'],
            ];
            $totals->MoveNext();
        }

        $this->info = [
            'order_id' => $this->orderId,
            'customer_id' => $order->fields['customers_id'],
            'currency' => $order->fields['currency'],
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
            'shipping_tax_rate' => $order->fields['shipping_tax_rate'],
            'last_modified' => $order->fields['last_modified'],
            'ip_address' => $order->fields['ip_address'],
            'language_code' => $order->fields['language_code'],
            'order_weight' => $order->fields['order_weight'],
            'is_wholesale' => $order->fields['is_wholesale'],  //- Note: Either 0/1 or null if not recorded
        ];

        $this->customer = [
            'id' => $order->fields['customers_id'],
            'name' => $order->fields['customers_name'],
            'company' => $order->fields['customers_company'],
            'street_address' => $order->fields['customers_street_address'],
            'suburb' => $order->fields['customers_suburb'],
            'city' => $order->fields['customers_city'],
            'postcode' => $order->fields['customers_postcode'],
            'state' => $order->fields['customers_state'],
            'country' => $this->getCountryInfo($order->fields['customers_country']),
            'format_id' => $order->fields['customers_address_format_id'],
            'telephone' => $order->fields['customers_telephone'],
            'email_address' => $order->fields['customers_email_address'],
        ];
        $this->customer['zone_id'] = $this->getCountryZoneId((int)$this->customer['country'], $this->customer['state']);

        $this->delivery = [
            'name' => $order->fields['delivery_name'],
            'company' => $order->fields['delivery_company'],
            'street_address' => $order->fields['delivery_street_address'],
            'suburb' => $order->fields['delivery_suburb'],
            'city' => $order->fields['delivery_city'],
            'postcode' => $order->fields['delivery_postcode'],
            'state' => $order->fields['delivery_state'],
            'country' => $this->getCountryInfo($order->fields['delivery_country']),
            'format_id' => $order->fields['delivery_address_format_id'],
        ];
        $this->delivery['zone_id'] = $this->getCountryZoneId((int)$this->delivery['country']['id'], $this->delivery['state']);

        if (($order->fields['shipping_module_code'] == 'storepickup') ||
            (empty($this->delivery['name']) && empty($this->delivery['street_address']))) {
            $this->delivery = false;
        }

        $this->billing = [
            'name' => $order->fields['billing_name'],
            'company' => $order->fields['billing_company'],
            'street_address' => $order->fields['billing_street_address'],
            'suburb' => $order->fields['billing_suburb'],
            'city' => $order->fields['billing_city'],
            'postcode' => $order->fields['billing_postcode'],
            'state' => $order->fields['billing_state'],
            'country' => $this->getCountryInfo($order->fields['billing_country']),
            'format_id' => $order->fields['billing_address_format_id'],
        ];
        $this->billing['zone_id'] = $this->getCountryZoneId((int)$this->billing['country'], $this->billing['state']);

        $index = 0;
        $orders_products_query = "SELECT *
                                  FROM " . TABLE_ORDERS_PRODUCTS . "
                                  WHERE orders_id = " . (int)$this->orderId . "
                                  ORDER BY orders_products_id";

        $orders_products = $db->Execute($orders_products_query);

        while (!$orders_products->EOF) {
            // convert quantity to proper decimals - account history
            $new_qty = $orders_products->fields['products_quantity'];
            if ($precision !== 0 && str_contains($new_qty, '.')) {
                $new_qty = rtrim($new_qty, '0');
            }

            $new_qty = round($new_qty, $precision);

            if ($new_qty == (int)$new_qty) {
                $new_qty = (int)$new_qty;
            }

            $this->products[$index] = [
                'qty' => $new_qty,
                'id' => $orders_products->fields['products_id'],
                'orders_products_id' => $orders_products->fields['orders_products_id'],
                'name' => $orders_products->fields['products_name'],
                'model' => $orders_products->fields['products_model'],
                'tax' => $orders_products->fields['products_tax'],
                'price' => $orders_products->fields['products_price'],
                'final_price' => $orders_products->fields['final_price'],
                'onetime_charges' => $orders_products->fields['onetime_charges'],
                'products_priced_by_attribute' => $orders_products->fields['products_priced_by_attribute'],
                'product_is_free' => $orders_products->fields['product_is_free'],
                'products_discount_type' => $orders_products->fields['products_discount_type'],
                'products_discount_type_from' => $orders_products->fields['products_discount_type_from'],
                'products_weight' => $orders_products->fields['products_weight'],
                'products_virtual' => (int)$orders_products->fields['products_virtual'],
                'product_is_always_free_shipping' => (int)$orders_products->fields['product_is_always_free_shipping'],
                'products_quantity_order_min' => $orders_products->fields['products_quantity_order_min'],
                'products_quantity_order_units' => $orders_products->fields['products_quantity_order_units'],
                'products_quantity_order_max' => $orders_products->fields['products_quantity_order_max'],
                'products_quantity_mixed' => (int)$orders_products->fields['products_quantity_mixed'],
                'products_mixed_discount_quantity' => (int)$orders_products->fields['products_mixed_discount_quantity'],
            ];

            $subindex = 0;
            $attributes_query = "SELECT products_options_id, products_options_values_id, products_options, products_options_values,
                                 options_values_price, price_prefix, product_attribute_is_free,
                                 products_attributes_weight, products_attributes_weight_prefix
                                 FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                                 WHERE orders_id = " . (int)$this->orderId . "
                                 AND orders_products_id = " . (int)$orders_products->fields['orders_products_id'] . "
                                 ORDER BY orders_products_attributes_id ASC";

            $attributes = $db->Execute($attributes_query);
            if ($attributes->RecordCount()) {
                $this->products[$index]['attributes'] = [];
                while (!$attributes->EOF) {
                    $this->products[$index]['attributes'][$subindex] = [
                        'option' => $attributes->fields['products_options'],
                        'value' => $attributes->fields['products_options_values'],
                        'option_id' => $attributes->fields['products_options_id'],
                        'value_id' => $attributes->fields['products_options_values_id'],
                        'prefix' => $attributes->fields['price_prefix'],
                        'price' => $attributes->fields['options_values_price'],
                        'product_attribute_is_free' => (int)$attributes->fields['product_attribute_is_free'],
                        'weight' => $attributes->fields['products_attributes_weight'],
                        'weight_prefix' => $attributes->fields['products_attributes_weight_prefix'],
                    ];

                    $subindex++;
                    $attributes->MoveNext();
                }
            }

            $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';

            $this->notify('NOTIFY_ORDER_QUERY_ADD_PRODUCT', $this->products[$index], $index);

            $index++;
            $orders_products->MoveNext();
        }

        $this->statuses = $this->getStatusHistory($this->orderId);

        $this->notify('NOTIFY_ORDER_AFTER_QUERY', IS_ADMIN_FLAG, $this->orderId);

        /**
         * @deprecated since v1.5.6; use NOTIFY_ORDER_AFTER_QUERY instead
         */
        if (IS_ADMIN_FLAG === true) {
            $this->notify('ORDER_QUERY_ADMIN_COMPLETE', ['orders_id' => $this->orderId]);
        }
    }

    function getStatusHistory($order_id, $language_id = null)
    {
        global $db;

        if (empty($language_id)) {
// @TODO - provide lookup in language class
//          if (!empty($this->info['language_code'])) {
//              global $lng;
//              $language_id = $lng->getLanguageIdFromCode($this->info['language_code']);
//          }
            if (empty($language_id)) {
                $language_id = $_SESSION['languages_id'];
            }
        }

        $customer_notified_clause = (IS_ADMIN_FLAG === true) ? '' : ' AND osh.customer_notified >= 0';
        $sql = "SELECT os.orders_status_name, osh.*
                FROM   " . TABLE_ORDERS_STATUS . " os
                LEFT JOIN " . TABLE_ORDERS_STATUS_HISTORY . " osh USING (orders_status_id)
                WHERE osh.orders_id = :ordersID
                AND os.language_id = :languageID
                $customer_notified_clause
                ORDER BY osh.date_added";

        $sql = $db->bindVars($sql, ':ordersID', $order_id, 'integer');
        $sql = $db->bindVars($sql, ':languageID', $language_id, 'integer');
        $results = $db->Execute($sql);

        $statusArray = [];
        foreach ($results as $result) {
            $statusArray[] = $result;
        }
        return $statusArray;
    }

    protected function getCountryInfo(string $country)
    {
        global $db;
        $sql = "SELECT countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, status
            FROM " . TABLE_COUNTRIES . "
            WHERE countries_name = :country";
        $sql = $db->bindVars($sql, ':country', $country, 'string');
        $results = $db->Execute($sql);
        if (!$results->EOF) {
            $result = $results->fields;
            $return = [
                'id' => $result['countries_id'],
                'title' => $country,
                'iso_code_2' => $result['countries_iso_code_2'],
                'iso_code_3' => $result['countries_iso_code_3'],
            ];
        } else {
            $return = [
                'id' => 0,
                'title' => $country,
                'iso_code_2' => '',
                'iso_code_3' => '',
            ];

        }
        return $return;
    }

    protected function getCountryZoneId(int $countries_id, string $state)
    {
        global $db;

        $sql =
            "SELECT zone_id
               FROM " . TABLE_ZONES . "
              WHERE zone_country_id = $countries_id
                AND (zone_code = :state: OR zone_name = :state:)
              LIMIT 1";
        $sql = $db->bindVars($sql, ':state:', $state, 'string');
        $results = $db->Execute($sql);

        return ($results->EOF) ? '0' : $results->fields['zone_id'];
    }

    function cart()
    {
        global $db, $currencies;

        $this->notify('NOTIFY_ORDER_CART_BEGINS');

        $billto = (!empty($_SESSION['billto']) ? (int)$_SESSION['billto'] : 0);
        $sendto = (!empty($_SESSION['sendto']) ? (int)$_SESSION['sendto'] : 0);

        $decimals = $currencies->get_decimal_places($_SESSION['currency']);

        $this->content_type = $_SESSION['cart']->get_content_type();

        $customer_address_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_telephone,
                                    c.customers_email_address, ab.entry_company, ab.entry_street_address,
                                    ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id,
                                    z.zone_code, z.zone_name, co.countries_id, co.countries_name,
                                    co.countries_iso_code_2, co.countries_iso_code_3,
                                    co.address_format_id, ab.entry_state
                                   FROM " . TABLE_CUSTOMERS . " c
                                   INNER JOIN " . TABLE_ADDRESS_BOOK . " ab ON (c.customers_id = ab.customers_id)
                                   LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                   LEFT JOIN " . TABLE_COUNTRIES . " co ON (ab.entry_country_id = co.countries_id)
                                   WHERE c.customers_id = " . (!empty($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0) . "
                                   AND c.customers_default_address_id = ab.address_book_id";

        $customer_address = $db->Execute($customer_address_query);

        $shipping_address_query = "SELECT ab.entry_firstname, ab.entry_lastname, ab.entry_company,
                                    ab.entry_street_address, ab.entry_suburb, ab.entry_postcode,
                                    ab.entry_city, ab.entry_zone_id, z.zone_code, z.zone_name, ab.entry_country_id,
                                    c.countries_id, c.countries_name, c.countries_iso_code_2,
                                    c.countries_iso_code_3, c.address_format_id, ab.entry_state
                                   FROM " . TABLE_ADDRESS_BOOK . " ab
                                   LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                   LEFT JOIN " . TABLE_COUNTRIES . " c ON (ab.entry_country_id = c.countries_id)
                                   WHERE ab.customers_id = " . (!empty($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0) . "
                                   AND ab.address_book_id = " . $sendto;

        $shipping_address = $db->Execute($shipping_address_query);

        $billing_address_query = "SELECT ab.entry_firstname, ab.entry_lastname, ab.entry_company,
                                   ab.entry_street_address, ab.entry_suburb, ab.entry_postcode,
                                   ab.entry_city, ab.entry_zone_id, z.zone_code, z.zone_name, ab.entry_country_id,
                                   c.countries_id, c.countries_name, c.countries_iso_code_2,
                                   c.countries_iso_code_3, c.address_format_id, ab.entry_state
                                  FROM " . TABLE_ADDRESS_BOOK . " ab
                                  LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                  LEFT JOIN " . TABLE_COUNTRIES . " c ON (ab.entry_country_id = c.countries_id)
                                  WHERE ab.customers_id = " . (!empty($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0) . "
                                  AND ab.address_book_id = " . $billto;

        $billing_address = $db->Execute($billing_address_query);

        $paymentModule = !empty($_SESSION['payment']) ? $_SESSION['payment'] : 'NOT SET YET';

        if (isset($_SESSION['cc_id'])) {
            $coupon_code_query = "SELECT coupon_code
                                  FROM " . TABLE_COUPONS . "
                                  WHERE coupon_id = " . (int)$_SESSION['cc_id'];
            $coupon_code = $db->Execute($coupon_code_query);
        }

        $shipping_module_code = '';
        // A shipping-module's 'code', if present in the session, **must** contain a '_' character, separating
        // the shipping module's name from the selected method, e.g. 'module_method'.  That '_' cannot be the first
        // character of the 'code' value.
        //
        // If that's not the case, issue a PHP Notice and reset the shipping to its unselected state.
        //
        if (!empty($_SESSION['shipping'])) {
            if (!empty($_SESSION['shipping']['id']) && strpos((string)$_SESSION['shipping']['id'], '_')) {
                $shipping_module_code = $_SESSION['shipping']['id'];
            } else {
                trigger_error('Malformed value for session-based shipping module; customer will need to re-select: ' . json_encode($_SESSION['shipping']), E_USER_NOTICE);
                unset($_SESSION['shipping']);
            }
        }

        $this->info = [
            'order_status' => DEFAULT_ORDERS_STATUS_ID,
            'currency' => $_SESSION['currency'],
            'currency_value' => $currencies->currencies[$_SESSION['currency']]['value'],
            'payment_method' => (isset($GLOBALS[$paymentModule]) && is_object($GLOBALS[$paymentModule])) ? $GLOBALS[$paymentModule]->title : '',
            'payment_module_code' => (isset($GLOBALS[$paymentModule]) && is_object($GLOBALS[$paymentModule])) ? $GLOBALS[$paymentModule]->code : '',
            'coupon_code' => $coupon_code->fields['coupon_code'] ?? '',
//            'cc_type' => (isset($GLOBALS['cc_type']) ? $GLOBALS['cc_type'] : ''),
//            'cc_owner' => (isset($GLOBALS['cc_owner']) ? $GLOBALS['cc_owner'] : ''),
//            'cc_number' => (isset($GLOBALS['cc_number']) ? $GLOBALS['cc_number'] : ''),
//            'cc_expires' => (isset($GLOBALS['cc_expires']) ? $GLOBALS['cc_expires'] : ''),
//            'cc_cvv' => (isset($GLOBALS['cc_cvv']) ? $GLOBALS['cc_cvv'] : ''),
            'shipping_method' => (isset($_SESSION['shipping']['title'])) ? $_SESSION['shipping']['title'] : '',
            'shipping_module_code' => $shipping_module_code,
            'shipping_cost' => !empty($_SESSION['shipping']['cost']) ? $_SESSION['shipping']['cost'] : 0,
            'tax_subtotals' => [],
            'subtotal' => 0,
            'shipping_tax' => 0,
            'shipping_tax_rate' => null,
            'tax' => 0,
            'total' => 0,
            'tax_groups' => [],
            'comments' => (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''),
            'ip_address' => $_SESSION['customers_ip_address'] . ' - ' . $_SERVER['REMOTE_ADDR'],
            'is_wholesale' => (int)Customer::isWholesaleCustomer(),
        ];

        // -----
        // Provide a watching observer (think EO!) a means to override the order's information as
        // well as the customer/delivery/billing addresses.
        //
        // Note: Any address-override array returned must provide ALL address-array keys as used by this class!
        // For example, the $customer_address_override array must include a 'firstname' element, not 'customers_firstname'.
        //
        $customer_address_override = [];
        $delivery_address_override = [];
        $billing_address_override = [];
        $this->notify('NOTIFY_ORDER_CART_ADDRESS_OVERRIDES', [], $customer_address_override, $delivery_address_override, $billing_address_override);

        if (count($customer_address_override) !== 0) {
            $this->customer = $customer_address_override;

        } elseif (!$customer_address->EOF) {
            $this->customer = [
                'firstname' => $customer_address->fields['customers_firstname'],
                'lastname' => $customer_address->fields['customers_lastname'],
                'company' => $customer_address->fields['entry_company'],
                'street_address' => $customer_address->fields['entry_street_address'],
                'suburb' => $customer_address->fields['entry_suburb'],
                'city' => $customer_address->fields['entry_city'],
                'postcode' => $customer_address->fields['entry_postcode'],
                'state' => ((zen_not_null($customer_address->fields['entry_state'])) ? $customer_address->fields['entry_state'] : $customer_address->fields['zone_name']),
                'state_code' => ((zen_not_null($customer_address->fields['zone_code'])) ? $customer_address->fields['zone_code'] : $customer_address->fields['zone_name']),
                'zone_id' => $customer_address->fields['entry_zone_id'],
                'country' => [
                    'id' => $customer_address->fields['countries_id'],
                    'title' => $customer_address->fields['countries_name'],
                    'iso_code_2' => $customer_address->fields['countries_iso_code_2'],
                    'iso_code_3' => $customer_address->fields['countries_iso_code_3'],
                ],
                'format_id' => (int)$customer_address->fields['address_format_id'],
                'telephone' => $customer_address->fields['customers_telephone'],
                'email_address' => $customer_address->fields['customers_email_address'],
            ];
        }

        if ($this->content_type == 'virtual') {
            $this->delivery = [
                'firstname' => '',
                'lastname' => '',
                'company' => '',
                'street_address' => '',
                'suburb' => '',
                'city' => '',
                'postcode' => '',
                'state' => '',
                'zone_id' => 0,
                'country' => [
                    'id' => 0,
                    'title' => '',
                    'iso_code_2' => '',
                    'iso_code_3' => ''
                ],
                'country_id' => 0,
                'format_id' => 0,
            ];
        } elseif (count($delivery_address_override) !== 0) {
            $this->delivery = $delivery_address_override;

        } elseif (!$shipping_address->EOF) {
            $this->delivery = [
                'firstname' => $shipping_address->fields['entry_firstname'],
                'lastname' => $shipping_address->fields['entry_lastname'],
                'company' => $shipping_address->fields['entry_company'],
                'street_address' => $shipping_address->fields['entry_street_address'],
                'suburb' => $shipping_address->fields['entry_suburb'],
                'city' => $shipping_address->fields['entry_city'],
                'postcode' => $shipping_address->fields['entry_postcode'],
                'state' => ((zen_not_null($shipping_address->fields['entry_state'])) ? $shipping_address->fields['entry_state'] : $shipping_address->fields['zone_name']),
                'state_code' => ((zen_not_null($shipping_address->fields['zone_code'])) ? $shipping_address->fields['zone_code'] : $shipping_address->fields['zone_name']),
                'zone_id' => $shipping_address->fields['entry_zone_id'],
                'country' => [
                    'id' => $shipping_address->fields['countries_id'],
                    'title' => $shipping_address->fields['countries_name'],
                    'iso_code_2' => $shipping_address->fields['countries_iso_code_2'],
                    'iso_code_3' => $shipping_address->fields['countries_iso_code_3'],
                ],
                'country_id' => $shipping_address->fields['entry_country_id'],
                'format_id' => (int)$shipping_address->fields['address_format_id'],
            ];
        }

        if (count($billing_address_override) !== 0) {
            $this->billing = $billing_address_override;

        } elseif (!$billing_address->EOF) {
            $this->billing = [
                'firstname' => $billing_address->fields['entry_firstname'],
                'lastname' => $billing_address->fields['entry_lastname'],
                'company' => $billing_address->fields['entry_company'],
                'street_address' => $billing_address->fields['entry_street_address'],
                'suburb' => $billing_address->fields['entry_suburb'],
                'city' => $billing_address->fields['entry_city'],
                'postcode' => $billing_address->fields['entry_postcode'],
                'state' => ((zen_not_null($billing_address->fields['entry_state'])) ? $billing_address->fields['entry_state'] : $billing_address->fields['zone_name']),
                'state_code' => ((zen_not_null($billing_address->fields['zone_code'])) ? $billing_address->fields['zone_code'] : $billing_address->fields['zone_name']),
                'zone_id' => $billing_address->fields['entry_zone_id'],
                'country' => [
                    'id' => $billing_address->fields['countries_id'],
                    'title' => $billing_address->fields['countries_name'],
                    'iso_code_2' => $billing_address->fields['countries_iso_code_2'],
                    'iso_code_3' => $billing_address->fields['countries_iso_code_3'],
                ],
                'country_id' => $billing_address->fields['entry_country_id'],
                'format_id' => (int)$billing_address->fields['address_format_id'],
            ];
        }

        [$taxCountryId, $taxZoneId] = $this->determineTaxAddressZones($billto, $sendto);

        // -----
        // Allow an observer to potentially make changes to any of the order-related addresses
        // and/or the country/zone information used to determine the order's products' tax rate.
        $this->notify('NOTIFY_ORDER_CART_AFTER_ADDRESSES_SET', '', $taxCountryId, $taxZoneId);

        $index = 0;
        $products = $_SESSION['cart']->get_products();
        for ($i = 0, $n = count($products); $i < $n; $i++) {
            $rowClass = ($i / 2) == floor($i / 2) ? 'rowEven' : 'rowOdd';
            $products_final_price_without_tax = $products[$i]['price'] + $_SESSION['cart']->attributes_price($products[$i]['id']);
            $this->products[$index] = [
                'qty' => $products[$i]['quantity'],
                'name' => $products[$i]['name'],
                'model' => $products[$i]['model'],
                'price' => $products[$i]['price'],
                'tax' => null, // calculated later
                'tax_groups' => null, // calculated later
                'final_price' => $products_final_price_without_tax,
                'onetime_charges' => $_SESSION['cart']->attributes_price_onetime_charges($products[$i]['id'], $products[$i]['quantity']),
                'weight' => $products[$i]['weight'],
                'length' => $products[$i]['length'] ?? null,
                'width' => $products[$i]['width'] ?? null,
                'height' => $products[$i]['height'] ?? null,
                'ships_in_own_box' => $products[$i]['ships_in_own_box'] ?? null,
                'products_priced_by_attribute' => $products[$i]['products_priced_by_attribute'],
                'product_is_free' => $products[$i]['product_is_free'],
                'products_discount_type' => $products[$i]['products_discount_type'],
                'products_discount_type_from' => $products[$i]['products_discount_type_from'],
                'id' => $products[$i]['id'],
                'rowClass' => $rowClass,
                'products_weight' => (float)$products[$i]['weight'],
                'products_virtual' => (int)$products[$i]['products_virtual'],
                'product_is_always_free_shipping' => (int)$products[$i]['product_is_always_free_shipping'],
                'products_quantity_order_min' => (float)$products[$i]['products_quantity_order_min'],
                'products_quantity_order_units' => (float)$products[$i]['products_quantity_order_units'],
                'products_quantity_order_max' => (float)$products[$i]['products_quantity_order_max'],
                'products_quantity_mixed' => (int)$products[$i]['products_quantity_mixed'],
                'products_mixed_discount_quantity' => (int)$products[$i]['products_mixed_discount_quantity'],
            ];

            $attributes_handled = false;
            $this->notify('NOTIFY_ORDER_CART_ADD_PRODUCT_LIST', ['index' => $index, 'products' => $products[$i]], $attributes_handled);

            if ($attributes_handled === false && !empty($products[$i]['attributes'])) {
                $subindex = 0;
                foreach ($products[$i]['attributes'] as $option => $value) {

                    $sql = "SELECT popt.products_options_name, poval.products_options_values_name,
                                   pa.options_values_price, pa.price_prefix, pa.attributes_discounted
                            FROM " . TABLE_PRODUCTS_OPTIONS . " popt,
                                 " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                 " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                            WHERE pa.products_id = '" . (int)$products[$i]['id'] . "'
                            AND pa.options_id = '" . (int)$option . "'
                            AND pa.options_id = popt.products_options_id
                            AND pa.options_values_id = '" . (int)$value . "'
                            AND pa.options_values_id = poval.products_options_values_id
                            AND popt.language_id = '" . (int)$_SESSION['languages_id'] . "'
                            AND poval.language_id = '" . (int)$_SESSION['languages_id'] . "'";

                    $attributes = $db->Execute($sql);

                    //clr 030714 Account for text attributes
                    if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
                        $attr_value = $products[$i]['attributes_values'][$option];
                    } else {
                        $attr_value = htmlspecialchars_decode($attributes->fields['products_options_values_name'], ENT_COMPAT);
                    }

                    $this->products[$index]['attributes'][$subindex] = [
                        'option' => $attributes->fields['products_options_name'],
                        'value' => $attr_value,
                        'option_id' => $option,
                        'value_id' => $value,
                        'prefix' => $attributes->fields['price_prefix'],
                        'price' => $attributes->fields['options_values_price'],
                        'discountable' => $attributes->fields['attributes_discounted'],
                    ];

                    $this->notify('NOTIFY_ORDER_CART_ADD_ATTRIBUTE_LIST', ['index' => $index, 'subindex' => $subindex, 'products' => $products[$i], 'attributes' => $attributes]);
                    $subindex++;
                }
            }

            // add onetime charges here
            //$_SESSION['cart']->attributes_price_onetime_charges($products[$i]['id'], $products[$i]['quantity'])

            // Set Product tax RATES based on Tax Basis configuration
            $taxRates = $this->setTaxRatesForProduct($products, $i, $index, $taxCountryId, $taxZoneId);
            $this->products[$index]['tax_groups'] = $taxRates;

            // -----
            // Update the order's subtotal and gather the tax-group-specific
            // product totals for the order's overall tax calculation.
            //
            $this->calculateTaxForProduct($index, $taxRates);

            $index++;
        }

        // -----
        // Using the information gathered on a per-product basis by calculateTaxForProduct,
        // determine the order's overall product-related tax.
        //
        $this->calculateProductsTaxForOrder();

        // Update the final total to include tax if not already tax-inc
        if (DISPLAY_PRICE_WITH_TAX == 'true') {
            $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
        } else {
            $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
        }

        // set order's status according to payment module configuration
        if (isset($GLOBALS[$paymentModule]) && is_object($GLOBALS[$paymentModule])) {
            if (isset($GLOBALS[$paymentModule]->order_status) && is_numeric($GLOBALS[$paymentModule]->order_status) && $GLOBALS[$paymentModule]->order_status > 0) {
                $this->info['order_status'] = $GLOBALS[$paymentModule]->order_status;
            }
        }
        $this->notify('NOTIFY_ORDER_CART_FINISHED');
    }

    function determineTaxAddressZones($billToAddressId, $shipToAddressId)
    {
        global $db;

        // set default tax calculation for not-logged-in visitors
        $taxCountryId = $taxZoneId = 0;

        // get tax zone info for logged-in visitors (including guests).  Note that a guest-checkout observer
        // can use 'NOTIFY_ORDER_CART_AFTER_ADDRESSES_SET' to modify the $taxCountryId and/or $taxZoneId.
        if (zen_is_logged_in()) {
            $taxCountryId = $taxZoneId = -1;
            $tax_address_query = '';
            switch (STORE_PRODUCT_TAX_BASIS) {
                case 'Shipping':
                    $address_book_id = ($this->content_type === 'virtual' ? $billToAddressId : $shipToAddressId);
                    break;
                case 'Billing':
                    $address_book_id = $billToAddressId;
                    break;
                case 'Store':
                    if (isset($this->billing['zone_id']) && $this->billing['zone_id'] == STORE_ZONE) {
                        $address_book_id = $billToAddressId;
                    } else {
                        $address_book_id = ($this->content_type === 'virtual' ? $billToAddressId : $shipToAddressId);
                    }
            }
            $tax_address_query = "SELECT ab.entry_country_id, ab.entry_zone_id
                                  FROM " . TABLE_ADDRESS_BOOK . " ab
                                  LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                  WHERE ab.customers_id = " . (int)$_SESSION['customer_id'] . "
                                  AND ab.address_book_id = " . $address_book_id;

            if ($tax_address_query != '') {
                $tax_address = $db->Execute($tax_address_query);
                if ($tax_address->RecordCount() > 0) {
                    $taxCountryId = $tax_address->fields['entry_country_id'];
                    $taxZoneId = $tax_address->fields['entry_zone_id'];
                }
            }
        }

        return [$taxCountryId, $taxZoneId];
    }

    /**
     * Determine tax RATES for product
     */
    function setTaxRatesForProduct($products, $loop, $index, $taxCountryId, $taxZoneId)
    {
        $taxRates = null;
        $this->notify('NOTIFY_ORDER_CART_EXTERNAL_TAX_RATE_LOOKUP', STORE_PRODUCT_TAX_BASIS, $products, $loop, $index, $taxCountryId, $taxZoneId, $taxRates);
        if ($taxRates !== null) {
            return $taxRates;
        }

        // Handle store-pickup scenario
        if (STORE_PRODUCT_TAX_BASIS == 'Shipping' && isset($_SESSION['shipping']['id']) && stristr($_SESSION['shipping']['id'], 'storepickup') == TRUE) {
            $taxRates = zen_get_multiple_tax_rates($products[$loop]['tax_class_id'], STORE_COUNTRY, STORE_ZONE);
            $this->products[$index]['tax'] = zen_get_tax_rate($products[$loop]['tax_class_id'], STORE_COUNTRY, STORE_ZONE);
            $this->products[$index]['tax_description'] = zen_get_tax_description($products[$loop]['tax_class_id'], STORE_COUNTRY, STORE_ZONE);
            $this->products[$index]['tax_groups'] = $taxRates;
            return $taxRates;
        }

        $taxRates = zen_get_multiple_tax_rates($products[$loop]['tax_class_id'], $taxCountryId, $taxZoneId);
        $this->products[$index]['tax'] = zen_get_tax_rate($products[$loop]['tax_class_id'], $taxCountryId, $taxZoneId);
        $this->products[$index]['tax_description'] = zen_get_tax_description($products[$loop]['tax_class_id'], $taxCountryId, $taxZoneId);
        $this->products[$index]['tax_groups'] = $taxRates;

        return $taxRates;
    }

    /**
     * Calculate taxes for a specific product and add them to the order's sub-total
     * and running-sub-total array for the final tax calculations.
     */
    protected function calculateTaxForProduct($index, $taxRates)
    {
        global $currencies;

        $product_tax_rate = $this->products[$index]['tax'];
        $product_final_price = $this->products[$index]['final_price'];
        $product_qty = $this->products[$index]['qty'];
        $product_onetime_charges = $this->products[$index]['onetime_charges'];

        // ----
        // Pricing calculations are different when a store displays prices with tax.
        //
        if (DISPLAY_PRICE_WITH_TAX === 'true') {
            $shown_price =
                zen_add_tax($product_final_price, $product_tax_rate) * $product_qty
                    + zen_add_tax($product_onetime_charges, $product_tax_rate);
        } else {
            $shown_price =
                $product_final_price * $product_qty
                    + $product_onetime_charges;
        }

        $this->info['subtotal'] += $shown_price;
        $this->notify('NOTIFY_ORDER_CART_SUBTOTAL_CALCULATE', ['shown_price' => $shown_price]);

        foreach ($taxRates as $tax_description => $tax_rate) {
            if (!isset($this->info['tax_subtotals'][$tax_description])) {
                $this->info['tax_subtotals'][$tax_description] = [
                    'tax_rate' => $tax_rate,
                    'subtotal' => 0,
                ];
            }
            $this->info['tax_subtotals'][$tax_description]['subtotal'] += $product_final_price * $product_qty + $product_onetime_charges;
        }
    }

    /**
     * Using the per-tax-group product totals calculated by calculateTaxForProduct, above,
     * calculate the *overall* product-related tax to be applied to the order
     */
    protected function calculateProductsTaxForOrder()
    {
        global $currencies;

        foreach ($this->info['tax_subtotals'] as $tax_description => $tax_info) {
            $tax_to_add = zen_calculate_tax($tax_info['subtotal'], $tax_info['tax_rate']);
            $this->info['tax'] += $tax_to_add;
            $this->info['tax_groups'][$tax_description] = $tax_to_add;
        }
    }

    /**
     * @param array $zf_ot_modules OrderTotalModules array from during checkout_process. Used to lookup OT prices to store into order
     * @return int|string
     */
    function create($zf_ot_modules)
    {
        global $db;

        $this->notify('NOTIFY_ORDER_CART_EXTERNAL_TAX_DURING_ORDER_CREATE', [], $zf_ot_modules);

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

        // Sanitize cc-num if present, using maximum 10 chars, with middle chars stripped out with XX
        if (isset($this->info['cc_number']) && strlen($this->info['cc_number']) > 10) {
            $cEnd = substr($this->info['cc_number'], -4);
            $cOffset = strlen($this->info['cc_number']) - 4;
            $cStart = substr($this->info['cc_number'], 0, ($cOffset > 4 ? 4 : (int)$cOffset));
            $this->info['cc_number'] = str_pad($cStart, 6, 'X') . $cEnd;
        }

        $sql_data_array = [
            'customers_id' => $_SESSION['customer_id'],
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
            'delivery_address_format_id' => (int)$this->delivery['format_id'],
            'billing_name' => $this->billing['firstname'] . ' ' . $this->billing['lastname'],
            'billing_company' => $this->billing['company'],
            'billing_street_address' => $this->billing['street_address'],
            'billing_suburb' => $this->billing['suburb'],
            'billing_city' => $this->billing['city'],
            'billing_postcode' => $this->billing['postcode'],
            'billing_state' => $this->billing['state'],
            'billing_country' => $this->billing['country']['title'],
            'billing_address_format_id' => $this->billing['format_id'],
            'payment_method' => (($this->info['payment_module_code'] == '' && $this->info['payment_method'] == '') ? PAYMENT_METHOD_GV : $this->info['payment_method']),
            'payment_module_code' => (($this->info['payment_module_code'] == '' && $this->info['payment_method'] == '') ? PAYMENT_MODULE_GV : $this->info['payment_module_code']),
            'shipping_method' => $this->info['shipping_method'],
            'shipping_module_code' => (strpos($this->info['shipping_module_code'], '_') > 0 ? substr($this->info['shipping_module_code'], 0, strpos($this->info['shipping_module_code'], '_')) : $this->info['shipping_module_code']),
            'coupon_code' => $this->info['coupon_code'],
            'cc_type' => isset($this->info['cc_type']) ? $this->info['cc_type'] : '',
            'cc_owner' => isset($this->info['cc_owner']) ? $this->info['cc_owner'] : '',
            'cc_number' => isset($this->info['cc_number']) ? $this->info['cc_number'] : '',
            'cc_expires' => isset($this->info['cc_expires']) ? $this->info['cc_expires'] : '',
            'date_purchased' => 'now()',
            'orders_status' => $this->info['order_status'],
            'order_total' => $this->info['total'],
            'order_tax' => $this->info['tax'],
            'shipping_tax_rate' => $this->info['shipping_tax_rate'] ?? 'null',
            'currency' => $this->info['currency'],
            'currency_value' => $this->info['currency_value'],
            'ip_address' => $_SESSION['customers_ip_address'] . ' - ' . $_SERVER['REMOTE_ADDR'],
            'language_code' => $_SESSION['languages_code'],
            'order_weight' => $_SESSION['cart']->weight,
            'is_wholesale' => $this->info['is_wholesale'],
        ];

        zen_db_perform(TABLE_ORDERS, $sql_data_array);
        $this->orderId = $this->info['order_id'] = $insert_id = $db->insert_ID();
        $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ORDER_HEADER', array_merge(['orders_id' => $this->orderId, 'shipping_weight' => $_SESSION['cart']->weight], $sql_data_array), $this->orderId);

        for ($i = 0, $n = count($zf_ot_modules); $i < $n; $i++) {
            $sql_data_array = [
                'orders_id' => $this->orderId,
                'title' => $zf_ot_modules[$i]['title'],
                'text' => $zf_ot_modules[$i]['text'],
                'value' => (is_numeric($zf_ot_modules[$i]['value'])) ? $zf_ot_modules[$i]['value'] : '0',
                'class' => $zf_ot_modules[$i]['code'],
                'sort_order' => $zf_ot_modules[$i]['sort_order'],
            ];

            zen_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
            $ot_insert_id = $db->insert_ID();
            $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ORDERTOTAL_LINE_ITEM', $sql_data_array, $ot_insert_id);
        }

        $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
        $sql_data_array = [
            'orders_id' => $this->orderId,
            'orders_status_id' => $this->info['order_status'],
            'date_added' => 'now()',
            'customer_notified' => $customer_notification,
            'comments' => $this->info['comments'],
        ];

        // -----
        // emp_admin mod support:
        // If an admin has just placed an order on a customer's behalf,
        // note that admin's name/id in the order's 'updated_by' field.
        //
        if (isset($_SESSION['emp_admin_id'])) {
            $admin_id_sql = "SELECT admin_name FROM " . TABLE_ADMIN . " WHERE admin_id = :adminid: LIMIT 1";
            $admin_id_sql = $db->bindVars($admin_id_sql, ':adminid:', $_SESSION['emp_admin_id'], 'integer');
            $admin_info = $db->Execute($admin_id_sql);

            $admin_name = (($admin_info->EOF) ? '???' : $admin_info->fields['admin_name']) . ' [' . $_SESSION['emp_admin_id'] . ']';
            $sql_data_array['updated_by'] = $admin_name;
        }

        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $osh_insert_id = $db->insert_ID();
        $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ORDER_COMMENT', $sql_data_array, $osh_insert_id);

        return $this->orderId;

    }

    /**
     * @param null $zf_insert_id - OrderNumber - deprecated since 1.5.7
     * @param bool $zf_mode Deprecated/unused since 1.5.0
     */
    function create_add_products($zf_insert_id = null, $zf_mode = false)
    {
        global $db, $currencies, $order_total_modules, $order_totals;

        if ($zf_insert_id === null) $zf_insert_id = $this->orderId;

        // initialized for the email confirmation
        $this->products_ordered = '';
        $this->products_ordered_html = '';
        $this->total_tax = 0;

        // lowstock email report
        $this->email_low_stock = '';

        for ($i = 0, $n = sizeof($this->products); $i < $n; $i++) {
            $custom_insertable_text = '';

            $this->doStockDecrement = (STOCK_LIMITED == 'true');
            $this->notify('NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_INIT', ['i' => $i], $this->products[$i], $i);
            // Stock Update - Joao Correia
            if ($this->doStockDecrement) {
                if (DOWNLOAD_ENABLED == 'true') {
                    $stock_query_raw = "SELECT p.*, pad.products_attributes_filename
                              FROM " . TABLE_PRODUCTS . " p
                              LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                               ON p.products_id=pa.products_id
                              LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                               ON pa.products_attributes_id=pad.products_attributes_id
                              WHERE p.products_id = " . zen_get_prid($this->products[$i]['id']);

                    // Will work with only one option for downloadable products
                    // otherwise, we have to build the query dynamically with a loop
                    // NOTE: Need the (int) cast on the option_id, since checkbox-type attributes' are formatted like '46_chk887'.
                    if (!empty($this->products[$i]['attributes'])) {
                        $products_attributes = $this->products[$i]['attributes'];
                        $stock_query_raw .= " AND pa.options_id = " . (int)$products_attributes[0]['option_id'] . " AND pa.options_values_id = " . $products_attributes[0]['value_id'];
                    }
                    $stock_values = $db->ExecuteNoCache($stock_query_raw . ' LIMIT 1');
                } else {
                    $stock_values = $db->ExecuteNoCache("SELECT * FROM " . TABLE_PRODUCTS . " WHERE products_id = " . zen_get_prid($this->products[$i]['id']) . " LIMIT 1");
                }

                $this->notify('NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_BEGIN', $i, $stock_values);

                if ($stock_values->RecordCount() > 0) {
                    // do not decrement quantities if products_attributes_filename exists
                    if ((DOWNLOAD_ENABLED != 'true') || $stock_values->fields['product_is_always_free_shipping'] == 2 || (!$stock_values->fields['products_attributes_filename'])) {
                        $stock_left = $stock_values->fields['products_quantity'] - $this->products[$i]['qty'];
                    } else {
                        $stock_left = $stock_values->fields['products_quantity'];
                    }

                    $products_status_update = ($stock_left <= 0 && SHOW_PRODUCTS_SOLD_OUT == '0') ? ', products_status = 0' : '';

                    $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                        SET products_quantity = " . $stock_left .
                        $products_status_update .
                        " WHERE products_id = " . zen_get_prid($this->products[$i]['id']) . " LIMIT 1");

                    // for low stock email
                    if ($stock_left <= STOCK_REORDER_LEVEL) {
                        // add product to low stock email content
                        $this->email_low_stock .= ($this->products[$i]['model'] === '' ? ''  : $this->products[$i]['model'] . "\t\t") . ' "' . $this->products[$i]['name'] . '" (#' . zen_get_prid($this->products[$i]['id']) . ')'. "\t\t" . ' ' . TEXT_PRODUCTS_QUANTITY . ' ' . $stock_left . "\n";
                    }
                }
            }

            // Update products_ordered (for bestsellers list)
            $this->bestSellersUpdate = true;
            $this->notify('NOTIFY_ORDER_PROCESSING_BESTSELLERS_UPDATE', [], $this->products[$i], $i);
            if ($this->bestSellersUpdate) {
                $db->Execute("UPDATE " . TABLE_PRODUCTS . " SET products_ordered = products_ordered + " . sprintf('%f', $this->products[$i]['qty']) . " WHERE products_id = '" . zen_get_prid($this->products[$i]['id']) . "'");
            }

            $this->notify('NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_END', $i);

            $sql_data_array = [
                'orders_id' => $zf_insert_id,
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
                'products_weight' => (float)$this->products[$i]['weight'],
                'products_virtual' => (int)$this->products[$i]['products_virtual'],
                'product_is_always_free_shipping' => (int)$this->products[$i]['product_is_always_free_shipping'],
                'products_quantity_order_min' => (float)$this->products[$i]['products_quantity_order_min'],
                'products_quantity_order_units' => (float)$this->products[$i]['products_quantity_order_units'],
                'products_quantity_order_max' => (float)$this->products[$i]['products_quantity_order_max'],
                'products_quantity_mixed' => (int)$this->products[$i]['products_quantity_mixed'],
                'products_mixed_discount_quantity' => (int)$this->products[$i]['products_mixed_discount_quantity'],
            ];
            zen_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

            $order_products_id = $db->insert_ID();

            $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_PRODUCT_LINE_ITEM', array_merge(['orders_products_id' => $order_products_id, 'i' => $i], $sql_data_array), $order_products_id);

            $this->notify('NOTIFY_ORDER_PROCESSING_CREDIT_ACCOUNT_UPDATE_BEGIN');
            $order_total_modules->update_credit_account($i);//ICW ADDED FOR CREDIT CLASS SYSTEM

            $this->notify('NOTIFY_ORDER_PROCESSING_ATTRIBUTES_BEGIN');

            //------ bof: insert customer-chosen options to order--------
            $attributes_exist = '0';
            $this->products_ordered_attributes = '';
            if (isset($this->products[$i]['attributes'])) {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($this->products[$i]['attributes']); $j < $n2; $j++) {
                    if (DOWNLOAD_ENABLED == 'true') {
                        $attributes_query = "SELECT popt.products_options_name, poval.products_options_values_name,
                                 pa.options_values_price, pa.price_prefix,
                                 pa.product_attribute_is_free, pa.products_attributes_weight, pa.products_attributes_weight_prefix,
                                 pa.attributes_discounted, pa.attributes_price_base_included, pa.attributes_price_onetime,
                                 pa.attributes_price_factor, pa.attributes_price_factor_offset,
                                 pa.attributes_price_factor_onetime, pa.attributes_price_factor_onetime_offset,
                                 pa.attributes_qty_prices, pa.attributes_qty_prices_onetime,
                                 pa.attributes_price_words, pa.attributes_price_words_free,
                                 pa.attributes_price_letters, pa.attributes_price_letters_free,
                                 pad.products_attributes_maxdays, pad.products_attributes_maxcount, pad.products_attributes_filename
                                 FROM " . TABLE_PRODUCTS_OPTIONS . " popt,
                                 " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                 " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                 LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON pa.products_attributes_id=pad.products_attributes_id
                                 WHERE pa.products_id = '" . zen_db_input($this->products[$i]['id']) . "'
                                 AND pa.options_id = '" . $this->products[$i]['attributes'][$j]['option_id'] . "'
                                 AND pa.options_id = popt.products_options_id
                                 AND pa.options_values_id = '" . $this->products[$i]['attributes'][$j]['value_id'] . "'
                                 AND pa.options_values_id = poval.products_options_values_id
                                 AND popt.language_id = '" . $_SESSION['languages_id'] . "'
                                 AND poval.language_id = '" . $_SESSION['languages_id'] . "'";

                        $attributes_values = $db->Execute($attributes_query);
                    } else {
                        $attributes_values = $db->Execute("SELECT popt.products_options_name, poval.products_options_values_name,
                                 pa.options_values_price, pa.price_prefix,
                                 pa.product_attribute_is_free, pa.products_attributes_weight, pa.products_attributes_weight_prefix,
                                 pa.attributes_discounted, pa.attributes_price_base_included, pa.attributes_price_onetime,
                                 pa.attributes_price_factor, pa.attributes_price_factor_offset,
                                 pa.attributes_price_factor_onetime, pa.attributes_price_factor_onetime_offset,
                                 pa.attributes_qty_prices, pa.attributes_qty_prices_onetime,
                                 pa.attributes_price_words, pa.attributes_price_words_free,
                                 pa.attributes_price_letters, pa.attributes_price_letters_free
                                 FROM " . TABLE_PRODUCTS_OPTIONS . " popt,
                                 " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                 " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                 WHERE pa.products_id = '" . $this->products[$i]['id'] . "'
                                 AND pa.options_id = '" . (int)$this->products[$i]['attributes'][$j]['option_id'] . "'
                                 AND pa.options_id = popt.products_options_id
                                 AND pa.options_values_id = '" . (int)$this->products[$i]['attributes'][$j]['value_id'] . "'
                                 AND pa.options_values_id = poval.products_options_values_id
                                 AND popt.language_id = '" . $_SESSION['languages_id'] . "'
                                 AND poval.language_id = '" . $_SESSION['languages_id'] . "'");
                    }

                    //clr 030714 update insert query.  changing to use values form $order->products for products_options_values.
                    // -----
                    // A couple of the 'products_attributes' fields' values might be `NULL` and zen_db_perform's processing
                    // doesn't accept those values when run under PHP versions 8.1 and later.  A temporary copy of those
                    // values is created, with `NULL` values converted to an empty string ('').
                    //
                    $string_attributes_qty_prices = $attributes_values->fields['attributes_qty_prices'] ?? '';
                    $string_attributes_qty_prices_onetime = $attributes_values->fields['attributes_qty_prices_onetime'] ?? '';
                    $sql_data_array = [
                        'orders_id' => $zf_insert_id,
                        'orders_products_id' => $order_products_id,
                        'products_options' => $attributes_values->fields['products_options_name'],

                        // 'products_options_values' => $attributes_values->fields['products_options_values_name'],
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
                        'attributes_qty_prices' => $string_attributes_qty_prices,
                        'attributes_qty_prices_onetime' => $string_attributes_qty_prices_onetime,
                        'attributes_price_words' => $attributes_values->fields['attributes_price_words'],
                        'attributes_price_words_free' => $attributes_values->fields['attributes_price_words_free'],
                        'attributes_price_letters' => $attributes_values->fields['attributes_price_letters'],
                        'attributes_price_letters_free' => $attributes_values->fields['attributes_price_letters_free'],
                        'products_options_id' => (int)$this->products[$i]['attributes'][$j]['option_id'],
                        'products_options_values_id' => (int)$this->products[$i]['attributes'][$j]['value_id'],
                        'products_prid' => $this->products[$i]['id'],
                    ];

                    zen_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
                    $opa_insert_id = $db->insert_ID();
                    $this->notify('NOTIFY_ORDER_DURING_CREATE_ADDED_ATTRIBUTE_LINE_ITEM', array_merge(['orders_products_attributes_id' => $opa_insert_id], $sql_data_array), $opa_insert_id);

                    if ((DOWNLOAD_ENABLED == 'true') && !empty($attributes_values->fields['products_attributes_filename'])) {
                        $sql_data_array = [
                            'orders_id' => $zf_insert_id,
                            'orders_products_id' => $order_products_id,
                            'orders_products_filename' => $attributes_values->fields['products_attributes_filename'],
                            'download_maxdays' => $attributes_values->fields['products_attributes_maxdays'],
                            'download_count' => $attributes_values->fields['products_attributes_maxcount'],
                            'products_prid' => $this->products[$i]['id'],
                            'products_attributes_id' => $opa_insert_id,
                        ];

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

            /* START: ADD MY CUSTOM DETAILS
             * 1. calculate/prepare custom information to be added to this product entry in order-confirmation, perhaps as a function call to custom code to build a serial number etc:
             *   Possible parameters to pass to custom functions at this point:
             *     Product ID ordered (for this line item): $this->products[$i]['id']
             *     Quantity ordered (of this line-item): $this->products[$i]['qty']
             *     Order number: $zf_insert_id
             *     Attribute Option Name ID: (int)$this->products[$i]['attributes'][$j]['option_id']
             *     Attribute Option Value ID: (int)$this->products[$i]['attributes'][$j]['value_id']
             *     Attribute Filename: $attributes_values->fields['products_attributes_filename']
             *
             * 2. Add that data to the $this->products_ordered_attributes variable, using this sort of format:
             *      $this->products_ordered_attributes .=  {INSERT CUSTOM INFORMATION HERE};
             */

            $this->products_ordered_attributes .= $custom_insertable_text;

            /* END: ADD MY CUSTOM DETAILS */

            // update totals counters
            if (!isset($this->total_weight)) $this->total_weight = 0.0;
            if (!isset($this->total_tax)) $this->total_tax = 0.0;
            if (!isset($this->total_cost)) $this->total_cost = 0.0;
            $this->total_weight += ($this->products[$i]['qty'] * $this->products[$i]['weight']);
            $this->total_tax += zen_calculate_tax($this->products[$i]['final_price'] * $this->products[$i]['qty'], $this->products[$i]['tax']);
            $this->total_cost += $this->products[$i]['final_price'] + $this->products[$i]['onetime_charges'];

            $this->notify('NOTIFY_ORDER_PROCESSING_ONE_TIME_CHARGES_BEGIN', $i);

            // build output for email notification
            $this->products_ordered .= $this->products[$i]['qty'] . ' x ' . $this->products[$i]['name'] . ($this->products[$i]['model'] != '' ? ' (' . $this->products[$i]['model'] . ') ' : '') . ' = ' .
                $currencies->display_price($this->products[$i]['final_price'], $this->products[$i]['tax'], $this->products[$i]['qty']) .
                ($this->products[$i]['onetime_charges'] != 0 ? "\n" . TEXT_ONETIME_CHARGES_EMAIL . $currencies->display_price($this->products[$i]['onetime_charges'], $this->products[$i]['tax'], 1) : '') .
                $this->products_ordered_attributes . "\n";
            $this->products_ordered_html .=
                '<tr>' . "\n" .
                '<td class="product-details" align="right" valign="top" width="30">' . $this->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
                '<td class="product-details" valign="top">' . nl2br($this->products[$i]['name']) . ($this->products[$i]['model'] != '' ? ' (' . nl2br($this->products[$i]['model']) . ') ' : '') .
                (!empty($this->products_ordered_attributes) ? "\n" . '<nobr>' . '<small><em>' . nl2br($this->products_ordered_attributes) . '</em></small>' . '</nobr>' : '') .
                '</td>' . "\n" .
                '<td class="product-details-num" valign="top" align="right">' .
                $currencies->display_price($this->products[$i]['final_price'], $this->products[$i]['tax'], $this->products[$i]['qty']) . '</td>' . "\n" . '</tr>' . "\n" .
                ($this->products[$i]['onetime_charges'] != 0 ?
                    '<tr>'. "\n" . '<td class="product-details" colspan="2">' . nl2br(TEXT_ONETIME_CHARGES_EMAIL) . '</td>' . "\n" .
                    '<td valign="top" align="right">' . $currencies->display_price($this->products[$i]['onetime_charges'], $this->products[$i]['tax'], 1) . '</td>' . "\n" . '</tr>' . "\n": '');
        }

        $order_total_modules->apply_credit();//ICW ADDED FOR CREDIT CLASS SYSTEM
        $this->notify('NOTIFY_ORDER_AFTER_ORDER_CREATE_ADD_PRODUCTS');
    }


    /**
     * @param int|null $zf_insert_id OrderNumber for display - unused/deprecated since 1.5.7.
     */
    function send_order_email($zf_insert_id = null)
    {
        global $currencies, $order_totals, $zcDate;

        if ($zf_insert_id === null) $zf_insert_id = $this->orderId;

        $this->notify('NOTIFY_ORDER_SEND_EMAIL_INITIALIZE', [], $zf_insert_id, $order_totals, $zf_mode);

        $this->send_low_stock_emails = true;
        $this->notify('NOTIFY_ORDER_SEND_LOW_STOCK_EMAILS');
        if ($this->send_low_stock_emails && $this->email_low_stock != '' && SEND_LOWSTOCK_EMAIL == '1') {
            $email_low_stock = SEND_EXTRA_LOW_STOCK_EMAIL_TITLE . "\n\n" . $this->email_low_stock;
            zen_mail('', SEND_EXTRA_LOW_STOCK_EMAILS_TO, EMAIL_TEXT_SUBJECT_LOWSTOCK, $email_low_stock, STORE_OWNER, EMAIL_FROM, ['EMAIL_MESSAGE_HTML' => nl2br($email_low_stock)], 'low_stock');
        }

        // lets start with the email confirmation
        // make an array to store the html version
        $html_msg = [];

        //intro area
        $email_order = EMAIL_TEXT_HEADER . EMAIL_TEXT_FROM . STORE_NAME . "\n\n" .
            $this->customer['firstname'] . ' ' . $this->customer['lastname'] . "\n\n" .
            EMAIL_THANKS_FOR_SHOPPING . "\n" . EMAIL_DETAILS_FOLLOW . "\n" .
            EMAIL_SEPARATOR . "\n" .
            EMAIL_TEXT_ORDER_NUMBER . ' ' . $zf_insert_id . "\n" .
            EMAIL_TEXT_DATE_ORDERED . ' ' . $zcDate->output(DATE_FORMAT_LONG) . "\n" .
            EMAIL_TEXT_INVOICE_URL . ' ' . zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $zf_insert_id, 'SSL', false) . "\n\n";

        $html_msg['EMAIL_TEXT_HEADER'] = EMAIL_TEXT_HEADER;
        $html_msg['EMAIL_TEXT_FROM'] = EMAIL_TEXT_FROM;
        $html_msg['INTRO_STORE_NAME'] = STORE_NAME;
        $html_msg['EMAIL_THANKS_FOR_SHOPPING'] = EMAIL_THANKS_FOR_SHOPPING;
        $html_msg['EMAIL_DETAILS_FOLLOW'] = EMAIL_DETAILS_FOLLOW;
        $html_msg['INTRO_ORDER_NUM_TITLE'] = EMAIL_TEXT_ORDER_NUMBER;
        $html_msg['INTRO_ORDER_NUMBER'] = $zf_insert_id;
        $html_msg['INTRO_DATE_TITLE'] = EMAIL_TEXT_DATE_ORDERED;
        $html_msg['INTRO_DATE_ORDERED'] = $zcDate->output(DATE_FORMAT_LONG);
        $html_msg['INTRO_URL_TEXT'] = EMAIL_TEXT_INVOICE_URL_CLICK;
        $html_msg['INTRO_URL_VALUE'] = zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $zf_insert_id, 'SSL', false);
        $html_msg['EMAIL_CUSTOMER_PHONE'] = $this->customer['telephone'];
        $html_msg['EMAIL_TEXT_TELEPHONE'] = EMAIL_TEXT_TELEPHONE;

        //comments area
        $html_msg['ORDER_COMMENTS'] = '';
        if ($this->info['comments']) {
            $email_order .= zen_output_string_protected($this->info['comments']) . "\n\n";
            $html_msg['ORDER_COMMENTS'] = nl2br(zen_output_string_protected($this->info['comments']));
        }

        $this->notify('NOTIFY_ORDER_EMAIL_BEFORE_PRODUCTS', [], $email_order, $html_msg);

        //products area
        $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
            EMAIL_SEPARATOR . "\n" .
            $this->products_ordered .
            EMAIL_SEPARATOR . "\n";
        $html_msg['PRODUCTS_TITLE'] = EMAIL_TEXT_PRODUCTS;
        $html_msg['PRODUCTS_DETAIL'] = '<table class="product-details" border="0" width="100%" cellspacing="0" cellpadding="2">' . $this->products_ordered_html . '</table>';

        //order totals area
        $html_ot = '<tr><td class="order-totals-text" align="right" width="100%">' . '&nbsp;' . '</td> ' . "\n" . '<td class="order-totals-num" align="right" nowrap="nowrap">' . '---------' . '</td> </tr>' . "\n";
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
            $html_ot .= '<tr><td class="order-totals-text" align="right" width="100%">' . $order_totals[$i]['title'] . '</td> ' . "\n" . '<td class="order-totals-num" align="right" nowrap="nowrap">' . ($order_totals[$i]['text']) . '</td> </tr>' . "\n";
        }
        $html_msg['ORDER_TOTALS'] = '<table border="0" width="100%" cellspacing="0" cellpadding="2"> ' . $html_ot . ' </table>';

        //addresses area: Delivery
        $html_msg['HEADING_ADDRESS_INFORMATION'] = HEADING_ADDRESS_INFORMATION;
        $html_msg['ADDRESS_DELIVERY_TITLE'] = EMAIL_TEXT_DELIVERY_ADDRESS;

        $storepickup = (strpos($this->info['shipping_module_code'], "storepickup") !== false);
        if ($this->content_type != 'virtual' && !$storepickup) {
            $html_msg['ADDRESS_DELIVERY_DETAIL'] = zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, '', "<br>");
        } else {
            $html_msg['ADDRESS_DELIVERY_DETAIL'] = 'n/a';
        }
        $html_msg['SHIPPING_METHOD_TITLE'] = HEADING_SHIPPING_METHOD;
        $html_msg['SHIPPING_METHOD_DETAIL'] = (!empty($this->info['shipping_method'])) ? $this->info['shipping_method'] : 'n/a';

        if ($this->content_type != 'virtual' && !$storepickup) {
            $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                EMAIL_SEPARATOR . "\n" .
                zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], false, '', "\n") . "\n";
        }
        $email_order .= EMAIL_TEXT_TELEPHONE . $this->customer['telephone'] . "\n\n";

        //addresses area: Billing
        $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
            EMAIL_SEPARATOR . "\n" .
            zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], false, '', "\n") . "\n\n";
        $html_msg['ADDRESS_BILLING_TITLE'] = EMAIL_TEXT_BILLING_ADDRESS;
        $html_msg['ADDRESS_BILLING_DETAIL'] = zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, '', "<br>");

        if (!empty($_SESSION['payment']) && is_object($GLOBALS[$_SESSION['payment']])) {
            $cc_num_display = (isset($this->info['cc_number']) && $this->info['cc_number'] != '') ? /*substr($this->info['cc_number'], 0, 4) . */
                str_repeat('X', (strlen($this->info['cc_number']) - 8)) . substr($this->info['cc_number'], -4) . "\n\n" : '';
            $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                EMAIL_SEPARATOR . "\n";
            $payment_class = $_SESSION['payment'];
            $email_order .= $GLOBALS[$payment_class]->title . "\n\n";
            $email_order .= (isset($this->info['cc_type']) && $this->info['cc_type'] != '') ? $this->info['cc_type'] . ' ' . $cc_num_display . "\n\n" : '';
            $email_order .= (isset($GLOBALS[$payment_class]->email_footer) && $GLOBALS[$payment_class]->email_footer) ? $GLOBALS[$payment_class]->email_footer . "\n\n" : '';
        } else {
            $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                EMAIL_SEPARATOR . "\n";
            $email_order .= PAYMENT_METHOD_GV . "\n\n";
        }
        $html_msg['PAYMENT_METHOD_TITLE'] = EMAIL_TEXT_PAYMENT_METHOD;
        $html_msg['PAYMENT_METHOD_DETAIL'] = (isset($GLOBALS[$_SESSION['payment']]) && is_object($GLOBALS[$_SESSION['payment']]) ? $GLOBALS[$payment_class]->title : PAYMENT_METHOD_GV);
        $html_msg['PAYMENT_METHOD_FOOTER'] = (!empty($payment_class) && isset($GLOBALS[$payment_class]->email_footer) && is_object($GLOBALS[$_SESSION['payment']]) &&
    $GLOBALS[$payment_class]->email_footer != '') ? nl2br($GLOBALS[$payment_class]->email_footer) : (isset($this->info['cc_type']) && $this->info['cc_type'] != '' ? $this->info['cc_type'] . ' ' . $cc_num_display : '');

        // Add in store specific order message
        $this->email_order_message = defined('EMAIL_ORDER_MESSAGE') ? constant('EMAIL_ORDER_MESSAGE') : '';
        $this->notify('NOTIFY_ORDER_SET_ORDER_MESSAGE');
        if (!empty($this->email_order_message)) {
            $email_order .= "\n\n" . $this->email_order_message . "\n\n";
        }
        $html_msg['EMAIL_ORDER_MESSAGE'] = $this->email_order_message;

        // include disclaimer
        if (defined('EMAIL_DISCLAIMER') && EMAIL_DISCLAIMER != '') $email_order .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";
        // include copyright
        if (defined('EMAIL_FOOTER_COPYRIGHT')) $email_order .= "\n-----\n" . EMAIL_FOOTER_COPYRIGHT . "\n\n";

        $email_order = str_replace('&nbsp;', ' ', $email_order);

        $html_msg['EMAIL_FIRST_NAME'] = $this->customer['firstname'];
        $html_msg['EMAIL_LAST_NAME'] = $this->customer['lastname'];
        //  $html_msg['EMAIL_TEXT_HEADER'] = EMAIL_TEXT_HEADER;

        $html_msg['EXTRA_INFO'] = '';

        // -----
        // Send customer confirmation email unless observer overrides it.
        $send_customer_email = true;
        $this->notify('NOTIFY_ORDER_INVOICE_CONTENT_READY_TO_SEND', ['zf_insert_id' => $zf_insert_id, 'text_email' => $email_order, 'html_email' => $html_msg], $email_order, $html_msg, $send_customer_email);
        if ($send_customer_email === true) {
            zen_mail($this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $zf_insert_id, $email_order, STORE_NAME, EMAIL_FROM, $html_msg, 'checkout', $this->attachArray);
        }

        // send additional emails
        if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
            $extra_info = email_collect_extra_info('', '', $this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], $this->customer['telephone']);
            $html_msg['EXTRA_INFO'] = $extra_info['HTML'];

            // include authcode and transaction id in admin-copy of email
            $payment_auth_code = !empty($GLOBALS[$_SESSION['payment']]->auth_code) ? $GLOBALS[$_SESSION['payment']]->auth_code : '';
            $payment_transaction_id = !empty($GLOBALS[$_SESSION['payment']]->transaction_id) ? $GLOBALS[$_SESSION['payment']]->transaction_id : '';
            if ($payment_auth_code !== '' || $payment_transaction_id !== '') {
                $pmt_details = ($payment_auth_code != '' ? 'AuthCode: ' . $payment_auth_code . '  ' : '') . ($payment_transaction_id != '' ? 'TransID: ' . $payment_transaction_id : '') . "\n\n";
                $email_order = $pmt_details . $email_order;
                $html_msg['EMAIL_TEXT_HEADER'] = nl2br($pmt_details) . $html_msg['EMAIL_TEXT_HEADER'];
            }

            // Add extra heading stuff via observer class
            $this->extra_header_text = '';
            $sendExtraOrderEmail = true;
            $this->notify('NOTIFY_ORDER_INVOICE_CONTENT_FOR_ADDITIONAL_EMAILS', $zf_insert_id, $email_order, $html_msg, $sendExtraOrderEmail);
            $email_order = $this->extra_header_text . $email_order;
            $html_msg['EMAIL_TEXT_HEADER'] = nl2br($this->extra_header_text) . $html_msg['EMAIL_TEXT_HEADER'];

            if ($sendExtraOrderEmail) {
                zen_mail('', SEND_EXTRA_ORDER_EMAILS_TO,
                    SEND_EXTRA_NEW_ORDERS_EMAILS_TO_SUBJECT . ' ' . EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $zf_insert_id,
                    $email_order . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg, 'checkout_extra',
                    $this->attachArray, $this->customer['firstname'] . ' ' . $this->customer['lastname'],
                    $this->customer['email_address']);
            }
        }
        $this->notify('NOTIFY_ORDER_AFTER_SEND_ORDER_EMAIL', $zf_insert_id, $email_order, $extra_info, $html_msg);
    }

}
