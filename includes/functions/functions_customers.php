<?php
/**
 * functions_customers
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 09 Modified in v2.0.0-alpha1 $
 */

/**
 * Return a customer greeting string based on login/guest condition
 */
function zen_customer_greeting(): string
{

    $greeting_string = sprintf(TEXT_GREETING_GUEST, zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    if (zen_is_logged_in() && !zen_in_guest_checkout() && !empty($_SESSION['customer_first_name'])) {
        $greeting_string = sprintf(TEXT_GREETING_PERSONAL, zen_output_string_protected($_SESSION['customer_first_name']), zen_href_link(FILENAME_PRODUCTS_NEW));
    } elseif (STORE_STATUS != '0') {
        $greeting_string = TEXT_GREETING_GUEST_SHOWCASE;
    }

    return $greeting_string;
}

/**
 * @deprecated use $customer->getNumberOfOrders() directly
 *
 * @param int|null $customer_id
 * @param bool $check_session unused legacy param
 * @return int
 */
function zen_count_customer_orders(?int $customer_id = null, $check_session = true): int
{
    $customer = new Customer($customer_id);

    return $customer->getNumberOfOrders();
}

/**
 * look up customer's default/primary address
 * @param int $customer_id
 * @return int|null
 */
function zen_get_customers_address_primary(int $customer_id): int
{
    $customer = new Customer($customer_id);

    return $customer->getData('customers_default_address_id');
}

/**
 * @param int|null $customer_id
 * @return array
 */
function zen_get_customer_address_book_entries(?int $customer_id = null): array
{
    $customer = new Customer($customer_id);

    return $customer->getFormattedAddressBookList($customer_id);
}

/**
 * @deprecated use zen_get_customer_address_book_entries()
 */
function zen_get_customers_address_book($customer_id) {
    return zen_get_customer_address_book_entries($customer_id);
}

/**
 * @param int|null $customer_id
 * @param bool $check_session unused legacy param
 * @return int
 * @deprecated use Customer::getFormattedAddressBookList or zen_get_customer_address_book_entries()
 */
function zen_count_customer_address_book_entries(?int $customer_id = null, $check_session = true): int
{
    return count(zen_get_customer_address_book_entries($customer_id));
}

/**
 * Concatenate customer first+last names into one string
 * @param $customer_id
 * @return string
 */
function zen_customers_name($customer_id): string
{
    $customer = new Customer($customer_id);
    $data = $customer->getData();

    if (empty($data)) return '';

    $name = $data['customers_firstname'] . ' ' . $data['customers_lastname'];

    return trim($name);
}

/**
 * @param string $email
 * @param int $customer_id_to_exclude pass this id to allow for changing the email address
 * @return bool
 */
function zen_check_email_address_not_already_used(string $email, int $customer_id_to_exclude = 0): bool
{
    global $db;

    $sql = "SELECT customers_id
            FROM " . TABLE_CUSTOMERS . "
            WHERE customers_email_address = '" . zen_db_input($email) . "'
            AND customers_id != " . (int)$customer_id_to_exclude;
    $result = $db->Execute($sql);

    if ($result->EOF) {
        return true;
    }

    return false;
}

/**
 * validate customer matches session
 * @param int $customer_id
 * @return bool
 */
function zen_get_customer_validate_session(int $customer_id): bool
{
    global $messageStack, $customer;

    if (!zen_is_logged_in()) {
        return false;
    }

    if (!isset($customer) || !is_a($customer, Customer::class)) {
        $customer = new Customer($customer_id);
    }

    $banned = $customer->isBanned($customer_id);

    if ($customer->isSameAsLoggedIn($customer_id) && !$banned) {
        return true;
    }

    if ($banned) {
        $customer->resetCustomerCart();
    }

    $messageStack->add_session('header', ERROR_CUSTOMERS_ID_INVALID, 'error');
    return false;
}

/**
 * This function identifies whether (true) or not (false) the current customer session is
 * associated with a guest-checkout process.
 * @alias Customer::isInGuestCheckout()
 */
function zen_in_guest_checkout(): bool
{
    global $zco_notifier;
    $in_guest_checkout = false;
    $zco_notifier->notify('NOTIFY_ZEN_IN_GUEST_CHECKOUT', null, $in_guest_checkout);
    return (bool)$in_guest_checkout;
}

/**
 * This function identifies whether (true) or not (false) a customer is currently logged into the site.
 * @alias Customer::someoneIsLoggedIn()
 */
function zen_is_logged_in(): bool
{
    global $zco_notifier;
    $is_logged_in = (!empty($_SESSION['customer_id']));
    $zco_notifier->notify('NOTIFY_ZEN_IS_LOGGED_IN', null, $is_logged_in);
    return (bool)$is_logged_in;
}

/**
 * This function determines if the proviced login-password is associated with a permitted
 * admin's admin-password, returning (bool)true if so.
 * Normally called during the login-page's header_php.php processing.
 * @param string $password
 * @param string $email_address
 * @return bool
 */
function zen_validate_storefront_admin_login($password, $email_address): bool
{
    global $db;
    $admin_authorized = false;

    // Before v1.5.7 Admin passwords might be 'sanitized', e.g. this&that becomes this&amp;that, so we'll check both versions.
    $pwd2 = htmlspecialchars($password, ENT_COMPAT, CHARSET);

    if (!empty(EMP_LOGIN_ADMIN_ID)) {
        $check = $db->Execute(
            "SELECT admin_id, admin_pass
             FROM " . TABLE_ADMIN . "
             WHERE admin_id = " . (int)EMP_LOGIN_ADMIN_ID . "
             LIMIT 1"
        );
        if (!$check->EOF && (zen_validate_password($password, $check->fields['admin_pass']) || zen_validate_password($pwd2, $check->fields['admin_pass']))) {
            $admin_authorized = true;
            $_SESSION['emp_admin_login'] = true;
            $_SESSION['emp_admin_id'] = (int)EMP_LOGIN_ADMIN_ID;
        }
    }

    if (!$admin_authorized && empty(EMP_LOGIN_ADMIN_PROFILE_ID)) {
        return false;
    }

    $profile_array = explode(',', str_replace(' ', '', EMP_LOGIN_ADMIN_PROFILE_ID));
    foreach ($profile_array as $index => $current_id) {
        if (empty($current_id)) {
            unset($profile_array[$index]);
        }
    }
    if (count($profile_array)) {
        $profile_list = implode(',', $profile_array);
        $admin_profiles = $db->Execute(
            "SELECT admin_id, admin_pass
               FROM " . TABLE_ADMIN . "
              WHERE admin_profile IN (" . $profile_list . ")"
        );
        foreach ($admin_profiles as $profile) {
            $admin_authorized = (zen_validate_password($pwd2, $profile['admin_pass']) || zen_validate_password($pwd2, $profile['admin_pass']));
            if ($admin_authorized) {
                $_SESSION['emp_admin_login'] = true;
                $_SESSION['emp_admin_id'] = (int)$profile['admin_id'];
                break;
            }
        }
    }

    if ($admin_authorized) {
        $_SESSION['emp_customer_email_address'] = $email_address;
        $params['action'] = 'emp_admin_login';
        $params['emailAddress'] = $email_address;
        $params['message'] = 'EMP admin login';
        zen_log_hmac_login($params);
    }
    return $admin_authorized;
}

function zen_update_customers_secret($customerId)
{
    global $db;

    $hashable = openssl_random_pseudo_bytes(64);
    $secret = hash('sha256', $hashable);
    $sql = "UPDATE " . TABLE_CUSTOMERS . " SET customers_secret = :secret: WHERE customers_id = :id:";
    $sql = $db->bindVars($sql, ':secret:', $secret, 'string');
    $sql = $db->bindVars($sql, ':id:', $customerId, 'integer');
    $db->Execute($sql);
    return $secret;
}

function zen_create_hmac_uri($data, $secret)
{
    $secret = hash('sha256', $secret . GLOBAL_AUTH_KEY);
    foreach ($data as $k => $val) {
        $k = str_replace('%', '%25', $k);
        $k = str_replace('&', '%26', $k);
        $k = str_replace('=', '%3D', $k);
        $val = str_replace('%', '%25', $val);
        $val = str_replace('&', '%26', $val);
        $params[$k] = $val;
    }
    ksort($params);
    $hmacData = implode('&', $params);
    foreach ($data as $k => $val) {
        unset($params[$k]);
    }

    $params['hmac'] = hash_hmac('sha256', $hmacData, $secret);
    return http_build_query($params);
}

function zen_is_hmac_login()
{
    if (!isset($_GET['main_page'], $_GET['hmac'], $_POST['timestamp']) || $_GET['main_page'] !== FILENAME_LOGIN) {
        return false;
    }
    return true;
}

function zen_validate_hmac_login()
{
    global $db, $zenSessionId;
    
    if (!isset($_POST['aid'], $_POST['cid'], $_POST['email_address'], $_POST['timestamp'])) {
        return false;
    }

    $data = $_REQUEST;
    $unsetArray = ['action', 'main_page', 'securityToken', 'zenid', 'zenInstallerId', $zenSessionId];
    foreach ($unsetArray as $entry) {
        unset($data[$entry]);
    }
    foreach ($data as $k => $val) {
        $k = str_replace('%', '%25', $k);
        $k = str_replace('&', '%26', $k);
        $k = str_replace('=', '%3D', $k);
        $val = str_replace('%', '%25', $val);
        $val = str_replace('&', '%26', $val);
        $params[$k] = $val;
    }

    $sql = "SELECT customers_secret FROM " . TABLE_CUSTOMERS . " WHERE customers_id = :id: LIMIT 1";
    $sql = $db->bindVars($sql, ':id:', $params['cid'], 'integer');
    $result = $db->Execute($sql);
    $secret = $result->fields['customers_secret'];
    $secret = hash('sha256', $secret . GLOBAL_AUTH_KEY);
    $hmacOriginal = $data['hmac'];
    unset($params['hmac']);
    ksort($params);
    $hmacData = implode('&', $params);

    return hash_equals(hash_hmac('sha256', $hmacData, $secret), $hmacOriginal);
}

function zen_validate_hmac_timestamp()
{
    $currentTime = time();
    $hmacTime = (isset($_POST['timestamp'])) ? (int)$_POST['timestamp'] : 0;
    return (($currentTime - $hmacTime) <= 20);
}


function zen_validate_hmac_admin_id($adminId)
{
    global $db;

    if (!empty(EMP_LOGIN_ADMIN_ID)) {
        $check = $db->Execute(
            "SELECT admin_id
           FROM " . TABLE_ADMIN . "
          WHERE admin_id = " . (int)EMP_LOGIN_ADMIN_ID . "
          LIMIT 1"
        );
        if ($check->RecordCount() > 0 && (int)EMP_LOGIN_ADMIN_ID == (int)$adminId) {
            return (int)$adminId;
        }
    }

    $profile_array = explode(',', str_replace(' ', '', EMP_LOGIN_ADMIN_PROFILE_ID));
    foreach ($profile_array as $index => $current_id) {
        if (empty($current_id)) {
            unset($profile_array[$index]);
        }
    }
    if (empty($profile_array)) return false;
    $profile_list = implode(',', $profile_array);
    $admin_profiles = $db->Execute(
        "SELECT admin_id FROM " . TABLE_ADMIN . "
         WHERE admin_id = " . (int)$adminId . " AND admin_profile IN (" . $profile_list . ")"
    );
    if ($admin_profiles->RecordCount() > 0) {
        return (int)$adminId;
    }
    return false;
}

function zen_log_hmac_login($params)
{
    $sql_data_array = array(
        'access_date' => 'now()',
        'admin_id' => $_SESSION['emp_admin_id'],
        'page_accessed' => 'login.php',
        'page_parameters' => '',
        'ip_address' => substr($_SERVER['REMOTE_ADDR'],0,45),
        'gzpost' => gzdeflate(json_encode(
            array(
                'action' => $params['action'],
                'customer_email_address' => $params['emailAddress'],
                )), 7),
        'flagged' => 0,
        'attention' => '',
        'severity' => 'info',
        'logmessage' => $params['message'],
    );
    zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
}



/** @deprecated  */
function zen_user_has_gv_balance($c_id) {
    trigger_error('Call to deprecated function zen_user_has_gv_balance. Use Customer object instead', E_USER_DEPRECATED);

    global $db;
    $gv_result = $db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = " . (int)$c_id);
    if ($gv_result->RecordCount() > 0) {
        if ($gv_result->fields['amount'] > 0) {
            return $gv_result->fields['amount'];
        }
    }
    return 0;
}
