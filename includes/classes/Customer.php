<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 16 Modified in v2.1.0-alpha2 $
 */

class Customer extends base
{
    protected ?int $customer_id = null;
    protected bool $is_logged_in = false;
    protected bool $is_in_guest_checkout = false;
    protected array $data = [];

    public function __construct($customer_id = null)
    {
        $this->is_logged_in = $this->someoneIsLoggedIn();
        $this->is_in_guest_checkout = $this->isInGuestCheckout();

        if (empty($customer_id) && $this->is_logged_in) {
            $this->setCustomerIdFromSession();
        }

        if (!empty($customer_id) && empty($this->customer_id)) {
            $this->customer_id = $customer_id;
        }

        if (!empty($this->customer_id)) {
            $this->load($this->customer_id);
            // if we have no record for this customer, reset it to null
            if (empty($this->data)) {
                $this->customer_id = null;
                $this->is_logged_in = false;
            }
        }
    }

    protected static function getCustomerWholesaleInfo(): array
    {
        static $wholesaleInfo;
        if (!isset($wholesaleInfo)) {
            $wholesaleInfo = [
                'is_wholesale' => false,
                'wholesale_tier' => 0,
                'is_tax_exempt' => false,
            ];
            if (WHOLESALE_PRICING_CONFIG !== 'false' && zen_is_logged_in() && !zen_in_guest_checkout()) {
                global $db;
                $wholesale = $db->Execute(
                    "SELECT customers_whole
                       FROM " . TABLE_CUSTOMERS . "
                      WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                      LIMIT 1"
                );
                if (!$wholesale->EOF && $wholesale->fields['customers_whole'] !== '0') {
                    $wholesaleInfo = [
                        'is_wholesale' => true,
                        'wholesale_tier' => (int)$wholesale->fields['customers_whole'],
                        'is_tax_exempt' => (WHOLESALE_PRICING_CONFIG === 'Tax Exempt'),
                    ];
                }
            }
        }
        return $wholesaleInfo;
    }

    public static function isWholesaleCustomer(): bool
    {
        $wholesale_info = Customer::getCustomerWholesaleInfo();
        return $wholesale_info['is_wholesale'];
    }

    public static function isTaxExempt(): bool
    {
        $wholesale_info = Customer::getCustomerWholesaleInfo();
        $is_tax_exempt = $wholesale_info['is_tax_exempt'];

        global $zco_notifier;
        $zco_notifier->notify('NOTIFY_CUSTOMER_IS_TAX_EXEMPT', [], $is_tax_exempt);

        return (bool)$is_tax_exempt;
    }

    public static function getCustomerWholesaleTier(): int
    {
        $wholesale_info = Customer::getCustomerWholesaleInfo();
        return $wholesale_info['wholesale_tier'];
    }

    public function getData(?string $element = null)
    {
        if (empty($element)) {
            return $this->data;
        }

        if (empty($this->data) || !isset($this->data[$element])) {
            return null;
        }

        return $this->data[$element];
    }

    public function getCurrentCustomerId(): int
    {
        if (empty($this->customer_id)) {
            $this->setCustomerIdFromSession();
        }
        return (int)$this->customer_id;
    }

    public function setCustomerIdFromSession(): int
    {
        if (!empty($_SESSION['customer_id'])) {
            $this->customer_id = (int)$_SESSION['customer_id'];
        }

        return (int)$this->customer_id;
    }

    /**
     * Return whether the indicated customer is currently logged into the site.
     * If no customer is specified, we check the one already assigned to this class
     */
    public function isSameAsLoggedIn(?int $idToCheck = null): bool
    {
        if (empty($idToCheck)) {
            $idToCheck = $this->customer_id;
        }
        $is_currently_logged_in = !empty($_SESSION['customer_id']) && $idToCheck === (int)$_SESSION['customer_id'];
        $this->notify('NOTIFY_ZEN_IS_CURRENTLY_LOGGED_IN', null, $is_currently_logged_in);
        return (bool)$is_currently_logged_in;
    }

    /**
     * Return whether "any" customer is currently logged into the site.
     */
    public function someoneIsLoggedIn(): bool
    {
        $is_logged_in = (!empty($_SESSION['customer_id']));
        $this->notify('NOTIFY_ZEN_IS_LOGGED_IN', null, $is_logged_in);
        return (bool)$is_logged_in;
    }

    public function doLoginLookupByEmail(string $email): array|false
    {
        global $db;

        $sql =
            "SELECT customers_id, customers_password, customers_authorization
               FROM " . TABLE_CUSTOMERS . "
              WHERE customers_email_address = :emailAddress";
        $sql = $db->bindVars($sql, ':emailAddress', $email, 'string');
        $result = $db->Execute($sql, 1);

        if ($result->EOF) {
            return false;
        }

        return $result->fields;
    }

    public function login(int $customer_id, $restore_cart = true): bool
    {
        global $db;

        if (empty($customer_id)) {
            return false;
        }

        // @TODO
        // what if already logged in?

        if (!$this->customerExistsInDatabase($customer_id)) {
            return false;
        }

        // fire notifier to check whether login should be allowed?
//@TODO        $this->notify('NOTIFY_?LOGIN_ATTEMPT', null, $is_logged_in);

        // -----
        // Load the customer's information from the database and set the appropriate
        // session variables.
        //
        $this->load($customer_id);
        if (empty($this->data)) {
            return false;
        }

        // @TODO - delete this if we collapse the Info table
        // enforce db integrity: make sure related record exists
        if (empty($this->data['date_account_created'])) {
            $sql = "INSERT IGNORE INTO " . TABLE_CUSTOMERS_INFO . " (customers_info_id) VALUES (:customersID)";
            $sql = $db->bindVars($sql, ':customersID', $customer_id, 'integer');
            $db->Execute($sql);
        }

        // update last login
        $sql =
            "UPDATE " . TABLE_CUSTOMERS_INFO . "
                SET customers_info_date_of_last_logon = now(),
                    customers_info_number_of_logons = IF(customers_info_number_of_logons, customers_info_number_of_logons+1, 1)
              WHERE customers_info_id = " . (int)$customer_id;
        $db->Execute($sql);

        $sql =
            "UPDATE " . TABLE_CUSTOMERS . "
                SET last_login_ip = '" . zen_db_input(zen_get_ip_address()) . "'
              WHERE customers_id = " . (int)$customer_id;
        $db->Execute($sql);

        // these session variables are used in various places across the catalog
        $_SESSION['customer_id'] = (int)$customer_id;
        $_SESSION['customers_email_address'] = $this->data['customers_email_address'];
        $_SESSION['customer_first_name'] = $this->data['customers_firstname'];
        $_SESSION['customer_last_name'] = $this->data['customers_lastname'];
        $_SESSION['customer_default_address_id'] = (int)$this->data['customers_default_address_id'];
        $_SESSION['customer_country_id'] = (int)$this->data['country_id'];
        $_SESSION['customer_zone_id'] = (int)$this->data['zone_id'];
        $_SESSION['customers_authorization'] = (int)$this->data['customers_authorization'];

        // @TODO - should we add $this->data to a session var, and replace numerous other lookups?

        if ($restore_cart) {
            $_SESSION['cart']->restore_contents();
        }

        // fire any notifiers
        return true;
    }

    /**
     * Return whether the current customer session is associated with a guest-checkout process.
     */
    public function isInGuestCheckout(): bool
    {
        $in_guest_checkout = false;
        $this->notify('NOTIFY_ZEN_IN_GUEST_CHECKOUT', null, $in_guest_checkout);
        return (bool)$in_guest_checkout;
    }

    public function customerExistsInDatabase(?int $customer_id = null): bool
    {
        global $db;

        if (empty($customer_id)) {
            $customer_id = $this->customer_id;
        }
        if (empty($customer_id)) {
            return false;
        }

        $sql =
            "SELECT customers_id
               FROM " . TABLE_CUSTOMERS . "
              WHERE customers_id = " . (int)$customer_id;

        $result = $db->Execute($sql, 1);

        return $result->RecordCount() > 0;
    }

    protected function load(?int $customer_id = null): bool
    {
        global $db;

        if (empty($customer_id)) {
            $customer_id = $this->customer_id;
        }
        if (empty($customer_id)) {
            $this->data = [];
            return false;
        }

        $sql =
            "SELECT c.*,
                    CONCAT(customers_firstname,' ',LEFT(customers_lastname,1),'.') as name_with_initial,
                    cgc.amount as gv_balance,
                    customers_info_date_account_created AS date_account_created,
                    customers_info_date_account_last_modified AS date_account_last_modified,
                    customers_info_date_of_last_logon AS date_of_last_login,
                    customers_info_number_of_logons AS number_of_logins
               FROM " . TABLE_CUSTOMERS . " c
                    LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON (c.customers_id = ci.customers_info_id)
                    LEFT JOIN " . TABLE_COUPON_GV_CUSTOMER . " cgc ON (c.customers_id = cgc.customer_id)
              WHERE c.customers_id = " . (int)$customer_id;

        $result = $db->Execute($sql, 1);

        if ($result->EOF) {
            $this->data = [];
            return false;
        }

        $this->data = $result->fields;
        unset($this->data['customers_password']);

        // load address info, while also correcting for missing default address_book id
        $addresses = $this->getFormattedAddressBookList($customer_id);
        $found_default_address_id = false;
        $first_address = null;

        foreach ($addresses as $address) {
            if (empty($first_address)) {
                $first_address = $address['address_book_id'];
            }
            if ($address['address_book_id'] == $this->data['customers_default_address_id']) {
                $this->data += $address['address'];
                $found_default_address_id = true;
                break;
            }
        }
        if (!$found_default_address_id && !empty($first_address)) {
            $this->setDefaultAddressBookId($first_address);
            foreach ($addresses as $address) {
                if ($address['address_book_id'] === $first_address) {
                    $this->data += $address['address'];
                    break;
                }
            }
        }
        // keep this info so we don't have to query it again
        $this->data['addresses'] = $addresses;

        $sql =
            "SELECT COUNT(*) AS number_of_reviews
               FROM " . TABLE_REVIEWS . "
              WHERE customers_id = " . (int)$customer_id;
        $result = $db->Execute($sql);
        $this->data['number_of_reviews'] = (int)$result->fields['number_of_reviews'];

        if (IS_ADMIN_FLAG) {
            $this->data['number_of_orders'] = $this->countCustomersPreviousOrders();
            // only calculating this on the Admin side, for performance reasons
            if ($this->data['number_of_orders']) {
                $this->data['lifetime_value'] = $this->getLifetimeValue();
            }
        } else {
            $this->data['lifetime_value'] = null;
            $this->data['number_of_orders'] = $this->getNumberOfOrders();
        }

        $this->getPricingGroupAssociation();

        $this->notify('NOTIFY_CUSTOMER_DATA_LOADED', $this->data, $this->data);

        // treat these as integers even though they (may have) come from the db as strings
        $ints = [
            'customers_id',
            'customers_default_address_id',
            'customers_newsletter',
            'customers_group_pricing',
            'customers_authorization',
            'number_of_logins',
            'address_book_id',
            'zone_id',
            'country_id',
            'number_of_reviews',
            'number_of_orders',
        ];
        foreach ($ints as $key) {
            if (isset($this->data[$key]) && is_numeric($this->data[$key])) {
                $this->data[$key] = (int)$this->data[$key];
            }
        }

        return true;
    }

    /**
     * Return the count of the current customer's previous orders.
     */
    protected function countCustomersPreviousOrders(): int
    {
        global $db;
        $orders = $db->Execute(
            "SELECT COUNT(*) AS count
               FROM " . TABLE_ORDERS . "
              WHERE customers_id = " . (int)$this->customer_id
        );
        return (int)$orders->fields['count'];
    }

    /**
     * Retrieve the current customer's lifetime value,
     * the sum of all previously-placed orders.
     */
    protected function getLifetimeValue(): float|int
    {
        global $db, $currencies;
        $lifetime_value = 0;

        $sql =
            "SELECT o.orders_id, o.date_purchased, o.order_total AS order_total_raw, o.currency, o.currency_value, o.language_code
               FROM " . TABLE_ORDERS . " o
              WHERE customers_id = " . (int)$this->customer_id . "
              ORDER BY date_purchased DESC";
        $results = $db->Execute($sql);

        $last_order = null;
        foreach ($results as $result) {
            if (null === $last_order) {
                $last_order = [
                    'date_purchased' => $result['date_purchased'],
                    'order_total' => $currencies->format($result['order_total_raw'], false, $result['currency'], $result['currency_value']),
                    'order_total_raw' => $result['order_total_raw'],
                    'currency' => $result['currency'],
                    'currency_value' => $result['currency_value'],
                    'language_code' => $result['language_code'],
                ];
            }
            $lifetime_value += $result['order_total_raw'] * $result['currency_value'];
        }
        $this->data['last_order'] = $last_order;
        $this->data['lifetime_value'] = $lifetime_value;
        return $lifetime_value;
    }

    /**
     * Add group-pricing details to the $this->data array
     */
    protected function getPricingGroupAssociation(): void
    {
        global $db;
        $sql =
            "SELECT group_name, group_percentage
               FROM " . TABLE_GROUP_PRICING . "
              WHERE group_id = " . (int)$this->data['customers_group_pricing'];
        $result = $db->Execute($sql);

        if ($result->RecordCount()) {
            $this->data['pricing_group_name'] = $result->fields['group_name'];
            $this->data['pricing_group_discount_percentage'] = $result->fields['group_percentage'];
        } else {
            $this->data['pricing_group_name'] = defined('TEXT_NONE') ? TEXT_NONE : '';
            $this->data['pricing_group_discount_percentage'] = 0;
        }

        $this->notify('NOTIFY_CUSTOMER_PRICING_GROUP_LOADED', $this->data);
    }

    /**
     * Update customer record in db with default address-book id
     */
    protected function setDefaultAddressBookId(int $id): void
    {
        global $db;
        $sql =
            "UPDATE " . TABLE_CUSTOMERS . "
                SET customers_default_address_id = " . (int)$id . "
              WHERE customers_id = " . (int)$this->customer_id;
        $db->Execute($sql);
        $this->data['customers_default_address_id'] = (int)$id;
    }

    public function isBanned(?int $customer_id = null): bool
    {
        $banned_status = false;

        if (!empty($customer_id) || empty($this->data)) {
            $this->load($customer_id);
        }

        if ((int)$this->data['customers_authorization'] === 4) {  // Banned status is 4
            $banned_status = true;
        }

        $this->notify('NOTIFY_CUSTOMER_CHECK_IF_BANNED', $this->data, $banned_status);

        return $banned_status;
    }

    public function banCustomer(): void
    {
        $proceed_with_ban = true;
        $reset_shopping_session_and_basket = true;
        $this->notify('NOTIFY_BAN_CUSTOMER', $this->data, $proceed_with_ban, $reset_shopping_session_and_basket);

        if ($proceed_with_ban) {
            $this->setCustomerAuthorizationStatus(4);

            if ($reset_shopping_session_and_basket) {
                $this->resetCustomerCart();
            } else {
                $this->forceLogout();
            }

            $this->data = [];
        }
    }

    public function setCustomerAuthorizationStatus(int $status): array
    {
        global $db;
        $sql =
            "UPDATE " . TABLE_CUSTOMERS . "
                SET customers_authorization = " . (int)$status . "
              WHERE customers_id = " . (int)$this->customer_id;
        $db->Execute($sql, 1);

        $this->data['customers_authorization'] = (int)$status;

        return $this->data;
    }

    public function resetCustomerCart(): void
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id= " . $this->customer_id);
        $db->Execute("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id= " . $this->customer_id);
        $_SESSION['cart']->reset(true);
        $this->forceLogout();
    }

    public function forceLogout(): bool
    {
        global $db;

        if ($this->isSameAsLoggedIn()) {
            // clean out whos_online for this user's session
            $db->Execute("DELETE FROM " . TABLE_WHOS_ONLINE . " WHERE customer_id = " . (int)$_SESSION['customer_id']);

            // @TODO - kill actual session from sessionhandler too? (eg: really boot them out)

            unset($_SESSION['customer_id']);
            return true;
        }
        return false;
    }

    public function getAddressBookEntries(?int $customer_id = null): object
    {
        global $db;

        if (empty($customer_id)) {
            $customer_id = $this->customer_id;
        }
        if (empty($customer_id)) {
            return [];
        }

        $sql =
            "SELECT c.*, ab.*
               FROM " . TABLE_ADDRESS_BOOK . " ab
                    LEFT JOIN " . TABLE_CUSTOMERS . " c USING (customers_id)
              WHERE customers_id = " . (int)$customer_id;

        return $db->Execute($sql);
    }

    public function getNumberOfAddressBookEntries(?int $customer_id = null): int
    {
        if (empty($customer_id)) {
            $customer_id = $this->customer_id;
        }
        if (empty($customer_id)) {
            return 0;
        }

        return count($this->getAddressBookEntries());
    }

    public function getFormattedAddressBookList(?int $customer_id = null): array
    {
        global $db;

        if (empty($customer_id)) {
            $customer_id = $this->customer_id;
        }
        if (empty($customer_id)) {
            return [];
        }

        $sql =
            "SELECT ab.*,
                    entry_firstname AS firstname, entry_lastname AS lastname,
                    entry_company AS company, entry_street_address AS street_address,
                    entry_suburb AS suburb, entry_city AS city, entry_postcode AS postcode,
                    entry_state AS state,
                    entry_zone_id AS zone_id,
                    zone_name, zone_code AS zone_iso,
                    entry_country_id AS country_id,
                    countries_name AS country_name,
                    countries_iso_code_3 AS country_iso
               FROM " . TABLE_ADDRESS_BOOK . " ab
                    INNER JOIN " . TABLE_COUNTRIES . " c ON (ab.entry_country_id = c.countries_id)
                    LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id AND z.zone_country_id = c.countries_id)
              WHERE customers_id = :customersID
              ORDER BY firstname, lastname";

        $sql = $db->bindVars($sql, ':customersID', $customer_id, 'integer');
        $results = $db->Execute($sql);

        $addressArray = [];

        foreach ($results as $result) {
            $format_id = zen_get_address_format_id((int)$result['country_id']);

            if (empty($result['state']) && !empty($result['zone_name'])) {
                $result['state'] = $result['zone_name'];
            }

            $addressArray[] = [
                'firstname' => $result['firstname'],
                'lastname' => $result['lastname'],
                'company' => $result['company'],
                'address_book_id' => $result['address_book_id'],
                'country_id' => $result['country_id'],
                'country_iso' => $result['country_iso'],
                'country_name' => $result['country_name'],
                'format_id' => $format_id,
                'address' => $result,
            ];
        }
        return $addressArray;
    }

    public function getOrderHistory(int $max_number_to_return = 0, &$returned_history_split = null): array
    {
        $language = $_SESSION['languages_id'];
        global $db, $currencies;
        $sql =
            "SELECT o.orders_id, o.date_purchased, o.delivery_name,
                    o.order_total, o.currency, o.currency_value,
                    o.delivery_country, o.billing_name, o.billing_country,
                    s.orders_status_name,
                    o.language_code
               FROM " . TABLE_ORDERS . " o
                    INNER JOIN " . TABLE_ORDERS_STATUS . " s
              WHERE o.customers_id = :customersID
                AND s.orders_status_id = (
                        SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS_HISTORY . " osh
                         WHERE osh.orders_id = o.orders_id
                           AND osh.customer_notified >= 0
                         ORDER BY osh.date_added DESC LIMIT 1
                    )
                AND s.language_id = :languagesID
              ORDER BY orders_id DESC";

        $sql = $db->bindVars($sql, ':customersID', $this->customer_id, 'integer');
        $sql = $db->bindVars($sql, ':languagesID', $language, 'integer');
        if ($returned_history_split !== null) {
            $history_split = new splitPageResults($sql, $max_number_to_return);
            $returned_history_split = $history_split;
            $results = $db->Execute($history_split->sql_query);
        } else {
            $results = $db->Execute($sql, $max_number_to_return);
        }

        $ordersArray = [];
        foreach ($results as $result) {
            if (!empty($result['delivery_name'])) {
                $order_type = defined('TEXT_ORDER_SHIPPED_TO') ? TEXT_ORDER_SHIPPED_TO : 'Shipped To:';
                $order_name = $result['delivery_name'];
                $order_country = $result['delivery_country'];
            } else {
                $order_type = defined('TEXT_ORDER_BILLED_TO') ? TEXT_ORDER_BILLED_TO : 'Billed To:';
                $order_name = $result['billing_name'];
                $order_country = $result['billing_country'];
            }

            $sql =
                "SELECT COUNT(*) AS count
                   FROM " . TABLE_ORDERS_PRODUCTS . "
                  WHERE orders_id = " . (int)$result['orders_id'];
            $queryResult = $db->Execute($sql);
            $products_count = $queryResult->EOF ? 0 : $queryResult->fields['count'];

            $ordersArray[] = [
                'orders_id' => (int)$result['orders_id'],
                'date_purchased' => $result['date_purchased'],
                'order_type' => $order_type,
                'order_name' => $order_name,
                'order_country' => $order_country,
                'orders_status_name' => $result['orders_status_name'],
                'order_total' => $currencies->format($result['order_total'], true, $result['currency'], $result['currency_value']),
                'order_total_raw' => $result['order_total'],
                'currency' => $result['currency'],
                'currency_value' => $result['currency_value'],
                'language_code' => $result['language_code'],
                'product_count' => $products_count,
            ];
        }
        return $ordersArray;
    }

    /**
     * Used catalog-side in the My Account page(s)
     */
    public function getNumberOfOrders(): int
    {
        if (!$this->is_logged_in) {
            return 0;
        }
        if ($this->is_in_guest_checkout) {
            return 0;
        }
        if (empty($this->customer_id)) {
            return 0;
        }

        global $db;

        $sql =
            "SELECT COUNT(*) as total
               FROM " . TABLE_ORDERS . "
              WHERE customers_id = " . (int)$this->customer_id;

        $result = $db->Execute($sql);

        return $result->fields['total'];
    }

    public function setPassword(string $new_password): void
    {
        global $db;
        $sql =
            "UPDATE " . TABLE_CUSTOMERS . "
                SET customers_password = :password
              WHERE customers_id = :customersID";
        $sql = $db->bindVars($sql, ':customersID', $this->customer_id, 'integer');
        $sql = $db->bindVars($sql, ':password', zen_encrypt_password($new_password), 'string');
        $db->Execute($sql);
        $sql =
            "UPDATE " . TABLE_CUSTOMERS_INFO . "
                SET customers_info_date_account_last_modified = now()
              WHERE customers_info_id = :customersID";
        $sql = $db->bindVars($sql, ':customersID', $this->customer_id, 'integer');
        $db->Execute($sql);
    }

    /**
     * Delete customer and all relations
     *
     * @param bool $delete_reviews
     * @param bool $forget_only Instead of delete, simply obfuscate address/name data
     */
    public function delete(bool $delete_reviews = false, bool $forget_only = false): void
    {
        global $db;

        if ($delete_reviews) {
            $reviews = $db->Execute(
                "SELECT reviews_id
                   FROM " . TABLE_REVIEWS . "
                  WHERE customers_id = " . (int)$this->customer_id
            );
            foreach ($reviews as $review) {
                $db->Execute(
                    "DELETE FROM " . TABLE_REVIEWS_DESCRIPTION . "
                      WHERE reviews_id = " . (int)$review['reviews_id']
                );
            }

            $db->Execute(
                "DELETE FROM " . TABLE_REVIEWS . "
                  WHERE customers_id = '" . (int)$this->customer_id . "'"
            );
        } else {
            $fields = 'customers_id = null';
            if ($forget_only) {
                $text_anonymous = (defined('DB_TEXT_ANONYMOUS')) ? zen_db_input(constant('DB_TEXT_ANONYMOUS')) : 'anonymous';
                $fields = "customers_name = '" . $text_anonymous . "'";
            }
            $db->Execute(
                "UPDATE " . TABLE_REVIEWS . "
                    SET " . $fields . "
                  WHERE customers_id = " . (int)$this->customer_id
            );
        }

        $text_deleted = (defined('DB_TEXT_DELETED')) ? zen_db_input(constant('DB_TEXT_DELETED')) : 'deleted';

        if ($forget_only) {
            $db->Execute(
                "UPDATE " . TABLE_ADDRESS_BOOK . "
                    SET entry_gender = '',
                        entry_company = '',
                        entry_firstname = '',
                        entry_lastname = '" . $text_deleted . "',
                        entry_street_address = '" . $text_deleted . "',
                        entry_suburb = ''
                  WHERE customers_id = " . (int)$this->customer_id
            );

            $db->Execute(
                "UPDATE " . TABLE_CUSTOMERS . "
                    SET customers_gender = '',
                        customers_firstname = '" . $text_deleted . "',
                        customers_lastname = '" . $text_deleted . " " . date("Y-m-d") . "',
                        customers_email_address = '" . $text_deleted . "',
                        customers_dob = '0001-01-01 00:00:00',
                        customers_newsletter = null,
                        customers_nick = '',
                        customers_paypal_payerid = '',
                        customers_secret = '',
                        customers_password = '',
                        customers_telephone = '',
                        registration_ip = '',
                        last_login_ip = '',
                        customers_fax = ''
                  WHERE customers_id = " . (int)$this->customer_id
            );
        } else {
            $db->Execute(
                "DELETE FROM " . TABLE_ADDRESS_BOOK . "
                  WHERE customers_id = " . (int)$this->customer_id
            );

            $db->Execute(
                "DELETE FROM " . TABLE_CUSTOMERS . "
                  WHERE customers_id = " . (int)$this->customer_id
            );

            $db->Execute(
                "DELETE FROM " . TABLE_CUSTOMERS_INFO . "
                  WHERE customers_info_id = " . (int)$this->customer_id
            );
        }

        $db->Execute(
            "DELETE FROM " . TABLE_CUSTOMERS_BASKET . "
              WHERE customers_id = " . (int)$this->customer_id
        );

        $db->Execute(
            "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
              WHERE customers_id = " . (int)$this->customer_id
        );

        $db->Execute(
            "DELETE FROM " . TABLE_WHOS_ONLINE . "
              WHERE customer_id = " . (int)$this->customer_id
        );

        $db->Execute(
            "DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
              WHERE customers_id = " . (int)$this->customer_id
        );

        $this->notify('NOTIFY_CUSTOMER_AFTER_RECORD_DELETED', (int)$this->customer_id);

        zen_record_admin_activity('Customer with customer ID ' . (int)$this->customer_id . ' deleted.', 'warning');
    }

    public function create(array $data): array
    {
        global $db;

        $this->notify('NOTIFY_MODULE_CREATE_ACCOUNT_ADDING_CUSTOMER_RECORD', null, $data);

        $sql_data_array = [
            ['fieldName' => 'customers_firstname', 'value' => $data['firstname'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_lastname', 'value' => $data['lastname'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_email_address', 'value' => $data['email_address'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_nick', 'value' => $data['nick'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_telephone', 'value' => $data['telephone'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_fax', 'value' => $data['fax'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_newsletter', 'value' => $data['newsletter'], 'type' => 'integer'],
            ['fieldName' => 'customers_email_format', 'value' => $data['email_format'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_default_address_id', 'value' => 0, 'type' => 'integer'],
            ['fieldName' => 'customers_password', 'value' => zen_encrypt_password($data['password']), 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_authorization', 'value' => $data['customers_authorization'], 'type' => 'integer'],
            ['fieldName' => 'registration_ip', 'value' => $data['ip_address'], 'type' => 'string'],
            ['fieldName' => 'last_login_ip', 'value' => $data['ip_address'], 'type' => 'string'],
        ];

        if (CUSTOMERS_REFERRAL_STATUS == '2' && !empty($data['customers_referral'])) {
            $sql_data_array[] = ['fieldName' => 'customers_referral', 'value' => $data['customers_referral'], 'type' => 'stringIgnoreNull'];
        }
        if (ACCOUNT_GENDER === 'true') {
            $sql_data_array[] = ['fieldName' => 'customers_gender', 'value' => $data['gender'], 'type' => 'stringIgnoreNull'];
        }
        if (ACCOUNT_DOB === 'true') {
            $sql_data_array[] = ['fieldName' => 'customers_dob', 'value' => (empty($data['dob']) || $data['dob'] === '0001-01-01 00:00:00') ? '0001-01-01 00:00:00' : zen_date_raw($data['dob']), 'type' => 'date'];
        }

        $db->perform(TABLE_CUSTOMERS, $sql_data_array);

        $this->customer_id = $db->Insert_ID();
        $customer_id = $this->customer_id;

        $this->notify('NOTIFY_MODULE_CREATE_ACCOUNT_ADDED_CUSTOMER_RECORD', array_merge(['customer_id' => $customer_id], $sql_data_array));


        $sql_data_array = [
            ['fieldName' => 'customers_id', 'value' => $customer_id, 'type' => 'integer'],
            ['fieldName' => 'entry_firstname', 'value' => $data['firstname'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'entry_lastname', 'value' => $data['lastname'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'entry_street_address', 'value' => $data['street_address'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'entry_postcode', 'value' => $data['postcode'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'entry_city', 'value' => $data['city'], 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'entry_country_id', 'value' => $data['country'], 'type' => 'integer'],
        ];

        if (ACCOUNT_GENDER === 'true') {
            $sql_data_array[] = ['fieldName' => 'entry_gender', 'value' => $data['gender'], 'type' => 'stringIgnoreNull'];
        }
        if (ACCOUNT_COMPANY === 'true') {
            $sql_data_array[] = ['fieldName' => 'entry_company', 'value' => $data['company'], 'type' => 'stringIgnoreNull'];
        }
        if (ACCOUNT_SUBURB === 'true') {
            $sql_data_array[] = ['fieldName' => 'entry_suburb', 'value' => $data['suburb'], 'type' => 'stringIgnoreNull'];
        }

        if (ACCOUNT_STATE === 'true') {
            if ($data['zone_id'] > 0) {
                $sql_data_array[] = ['fieldName' => 'entry_zone_id', 'value' => $data['zone_id'], 'type' => 'integer'];
                $sql_data_array[] = ['fieldName' => 'entry_state', 'value' => '', 'type' => 'stringIgnoreNull'];
            } else {
                $sql_data_array[] = ['fieldName' => 'entry_zone_id', 'value' => 0, 'type' => 'integer'];
                $sql_data_array[] = ['fieldName' => 'entry_state', 'value' => $data['state'], 'type' => 'stringIgnoreNull'];
            }
        }

        $db->perform(TABLE_ADDRESS_BOOK, $sql_data_array);

        $address_id = $db->Insert_ID();

        $this->notify('NOTIFY_MODULE_CREATE_ACCOUNT_ADDED_ADDRESS_BOOK_RECORD', array_merge(['address_id' => $address_id], $sql_data_array));

        $sql =
            "UPDATE " . TABLE_CUSTOMERS . "
                SET customers_default_address_id = " . (int)$address_id . "
              WHERE customers_id = " . (int)$customer_id;
        $db->Execute($sql);

        $sql =
            "INSERT INTO " . TABLE_CUSTOMERS_INFO . "
                (customers_info_id, customers_info_number_of_logons,
                 customers_info_date_account_created, customers_info_date_of_last_logon)
             VALUES
                ('" . (int)$customer_id . "', '1', now(), now())";
        $db->Execute($sql);

        $this->load($customer_id);
        return $this->data;
    }
    // @TODO - add method for deleting duplicate identical address_book records?
}
