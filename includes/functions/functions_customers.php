<?php
/**
 * functions_customers
 *
 * @package functions
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Apr 07 Modified in v1.5.6b $
 */

/**
 * Returns the address_format_id for the given country
 */
function zen_get_address_format_id($country_id) {
    global $db;
    $address_format_query = "select address_format_id as format_id
                             from " . TABLE_COUNTRIES . "
                             where countries_id = " . (int)$country_id;

    $address_format = $db->Execute($address_format_query);

    if ($address_format->RecordCount() > 0) {
        return $address_format->fields['format_id'];
    }
    return 1;
}

/**
 * Return a formatted address, based on specified formatting pattern id
 */
function zen_address_format($address_format_id = 1, $incoming = array(), $html = false, $boln = '', $eoln = "\n") {
    global $db;
    $address = array();
    $address['hr'] = $html ? '<hr>' : '----------------------------------------';
    $address['cr'] = $html ? ($boln == '' && $eoln == "\n" ? '<br>' : $eoln . $boln) : $eoln;

    $address['company'] = !empty($incoming['company']) ? zen_output_string_protected($incoming['company']) : '';
    $address['firstname'] = !empty($incoming['firstname']) ? zen_output_string_protected($incoming['firstname']) : (!empty($incoming['name']) ? zen_output_string_protected($incoming['name']) : '');
    $address['lastname'] = !empty($incoming['lastname']) ? zen_output_string_protected($incoming['lastname']) : '';
    $address['street'] = !empty($incoming['street_address']) ? zen_output_string_protected($incoming['street_address']) : '';
    $address['suburb'] = !empty($incoming['suburb']) ? zen_output_string_protected($incoming['suburb']) : '';
    $address['city'] = !empty($incoming['city']) ? zen_output_string_protected($incoming['city']) : '';
    $address['state'] = !empty($incoming['state']) ? zen_output_string_protected($incoming['state']) : '';
    $address['postcode'] = !empty($incoming['postcode']) ? zen_output_string_protected($incoming['postcode']) : '';
    $address['zip'] = $address['postcode'];

    $address['streets'] = !empty($address['suburb']) ? $address['street'] . $address['cr'] . $address['suburb'] : $address['street'];
    $address['statecomma'] = !empty($address['state']) ? $address['state'] . ', ' : '';

    $country = '';
    if (!empty($incoming['country_id'])) {
        $country = zen_get_country_name($incoming['country_id']);
        if (!empty($incoming['zone_id'])) {
            $address['state'] = zen_get_zone_code($incoming['country_id'], $incoming['zone_id'], $address['state']);
        }
    } elseif (!empty($incoming['country'])) {
        if (is_array($incoming['country'])) {
            $country = zen_output_string_protected($incoming['country']['countries_name']);
        } else {
            $country = zen_output_string_protected($incoming['country']);
        }
    }
    $address['country'] = $country;

    // add uppercase variants for backward compatibility
    $address['HR'] = $address['hr'];
    $address['CR'] = $address['cr'];

    $sql    = "select address_format as format from " . TABLE_ADDRESS_FORMAT . " where address_format_id = " . (int)$address_format_id;
    $result = $db->Execute($sql);
    $fmt    = (!$result->EOF ? $result->fields['format'] : '');

    // sort to put longer keys at the top of the array so that longer variants are replaced before shorter ones
    array_multisort(array_map('strlen', array_keys($address)), SORT_DESC, $address);

    // store translated values into original array, just for the sake of the notifier
    $incoming = $address;
    
    // convert into $-prefixed keys
    foreach ($address as $key => $value) {
        $address['$' . $key] = $value;
        unset($address[$key]);
    }

    // do the substitutions
    $address_out = str_replace(array_keys($address), array_values($address), $fmt);

    if (ACCOUNT_COMPANY == 'true' && !empty($address['$company']) && false === strpos($fmt, '$company')) {
        $address_out = $address['$company'] . $address['$cr'] . $address_out;
    }

    // -----
    // "Package up" the various elements of an address and issue a notification that will enable
    // an observer to make modifications if needed.
    //
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_END_ZEN_ADDRESS_FORMAT',
        array(
            'format' => $fmt,
            'address' => $incoming,
            'firstname' => $address['$firstname'],
            'lastname' => $address['$lastname'],
            'street' => $address['$street'],
            'suburb' => $address['$suburb'],
            'city' => $address['$city'],
            'state' => $address['$state'],
            'country' => $address['$country'],
            'postcode' => $address['$postcode'],
            'company' => $address['$company'],
            'streets' => $address['$streets'],
            'statecomma' => $address['$statecomma'],
            'zip' => $address['$zip'],
            'cr' => $address['$cr'],
            'hr' => $address['$hr'],
        ),
        $address_out
    );

    return $address_out;
}

/**
 * Return a formatted address, based on customer's address's country format
 */
  function zen_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
    global $db;
    $address_query = "select entry_firstname as firstname, entry_lastname as lastname,
                             entry_company as company, entry_street_address as street_address,
                             entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                             entry_state as state, entry_zone_id as zone_id,
                             entry_country_id as country_id
                      from " . TABLE_ADDRESS_BOOK . "
                      where customers_id = " . (int)$customers_id . "
                      and address_book_id = " . (int)$address_id;

    $address = $db->Execute($address_query);

    $GLOBALS['zco_notifier']->notify('NOTIFY_ZEN_ADDRESS_LABEL', array(), $customers_id, $address_id, $address->fields);


    $format_id = zen_get_address_format_id($address->fields['country_id']);
    return zen_address_format($format_id, $address->fields, $html, $boln, $eoln);
  }

// look up customers default or primary address
  function zen_get_customers_address_primary($customer_id) {
    global $db;

    $lookup_customers_primary_address_query = "SELECT customers_default_address_id
                                              from " . TABLE_CUSTOMERS . "
                                              WHERE customers_id = " . (int)$customer_id;

    $lookup_customers_primary_address = $db->Execute($lookup_customers_primary_address_query);

    return $lookup_customers_primary_address->fields['customers_default_address_id'];
  }

////
// Return a customer greeting
  function zen_customer_greeting() {

    if (isset($_SESSION['customer_id']) && $_SESSION['customer_first_name']) {
      $greeting_string = sprintf(TEXT_GREETING_PERSONAL, zen_output_string_protected($_SESSION['customer_first_name']), zen_href_link(FILENAME_PRODUCTS_NEW));
    } else {
      $greeting_string = sprintf(TEXT_GREETING_GUEST, zen_href_link(FILENAME_LOGIN, '', 'SSL'), zen_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
    }

    return $greeting_string;
  }

  function zen_count_customer_orders($id = '', $check_session = true) {
    global $db;

    if (is_numeric($id) == false) {
      if ($_SESSION['customer_id']) {
        $id = $_SESSION['customer_id'];
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if ( ($_SESSION['customer_id'] == false) || ($id != $_SESSION['customer_id']) ) {
        return 0;
      }
    }

    $orders_check_query = "select count(*) as total
                           from " . TABLE_ORDERS . "
                           where customers_id = " . (int)$id;

    $orders_check = $db->Execute($orders_check_query);

    return $orders_check->fields['total'];
  }

  function zen_count_customer_address_book_entries($id = '', $check_session = true) {
    global $db;

    if (is_numeric($id) == false) {
      if ($_SESSION['customer_id']) {
        $id = $_SESSION['customer_id'];
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if ( ($_SESSION['customer_id'] == false) || ($id != $_SESSION['customer_id']) ) {
        return 0;
      }
    }

    $addresses_query = "select count(*) as total
                        from " . TABLE_ADDRESS_BOOK . "
                        where customers_id = " . (int)$id;

    $addresses = $db->Execute($addresses_query);

    return $addresses->fields['total'];
  }

////
// validate customer matches session
  function zen_get_customer_validate_session($customer_id) {
    global $db, $messageStack;
    $zc_check_customer = $db->Execute("SELECT customers_id, customers_authorization from " . TABLE_CUSTOMERS . " WHERE customers_id=" . (int)$customer_id);
    $bannedStatus = $zc_check_customer->fields['customers_authorization'] == 4; // BANNED STATUS is 4
    if ($zc_check_customer->RecordCount() <= 0 || $bannedStatus) {
      $db->Execute("DELETE from " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id= " . $customer_id);
      $db->Execute("DELETE from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id= " . $customer_id);
      $_SESSION['cart']->reset(TRUE);
      unset($_SESSION['customer_id']);
      if (!$bannedStatus) $messageStack->add_session('header', ERROR_CUSTOMERS_ID_INVALID, 'error');
      return false;
    }
    return true;
  }

/**
 * This function identifies whether (true) or not (false) the current customer session is
 * associated with a guest-checkout process.
 */
function zen_in_guest_checkout()
{
    $in_guest_checkout = false;
    $GLOBALS['zco_notifier']->notify('NOTIFY_ZEN_IN_GUEST_CHECKOUT', '', $in_guest_checkout);
    return (bool)$in_guest_checkout;
}

/**
 * This function identifies whether (true) or not (false) a customer is currently logged into the site.
 */
function zen_is_logged_in()
{
    $is_logged_in = (!empty($_SESSION['customer_id']));
    $GLOBALS['zco_notifier']->notify('NOTIFY_ZEN_IS_LOGGED_IN', '', $is_logged_in);
    return (bool)$is_logged_in;
}

/**
 * This function determines if the login-password supplied is associated with a permitted
 * admin's admin-password, returning (bool)true if so.  Normally called during the login-page's
 * header_php.php processing.
 */
function zen_validate_storefront_admin_login($password, $email_address)
{
    global $db;
    $admin_authorized = false;
    
    // -----
    // Admin passwords might be 'sanitized', e.g. this&that becomes this&amp;that, so we'll check
    // either the as-entered or 'sanitized' versions.
    //
    $pwd2 = htmlspecialchars($password, ENT_COMPAT, CHARSET);
    $check = $db->Execute(
        "SELECT admin_id, admin_pass 
           FROM " . TABLE_ADMIN . " 
          WHERE admin_id = " . (int)EMP_LOGIN_ADMIN_ID . "
          LIMIT 1"
    );
    if (!$check->EOF && (zen_validate_password($password, $check->fields['admin_pass']) || zen_validate_password($pwd2, $check->fields['admin_pass']))) {
        $admin_authorized = true;
        $_SESSION['emp_admin_login'] = true;
        $_SESSION['emp_admin_id'] = EMP_LOGIN_ADMIN_ID;
        
    } else {
        $profile_array = explode(',', str_replace(' ', '', EMP_LOGIN_ADMIN_PROFILE_ID));
        foreach ($profile_array as $index => $current_id) {
            if (!(int)$current_id == 0) {
                unset($profile_array[$index]);
            }
        }
        if (count($profile_array) != 0) {
            $profile_list = implode(', ', $profile_array);
            $admin_profiles = $db->Execute(
                "SELECT admin_id, admin_pass 
                   FROM " . TABLE_ADMIN . " 
                  WHERE admin_profile IN ($profile_list)"
            );
            while (!$admin_profiles->EOF && !$admin_authorized) {
                $admin_authorized = (zen_validate_password($p2, $admin_profiles->fields['admin_pass']) || zen_validate_password($pwd2, $admin_profiles->fields['admin_pass']));
                if ($admin_authorized) {
                    $_SESSION['emp_admin_login'] = true;
                    $_SESSION['emp_admin_id'] = $admin_profiles->fields['admin_id'];
                }
                $admin_profiles->MoveNext();
            }
        }
    }

    if ($admin_authorized) {
        $_SESSION['emp_customer_email_address'] = $email_address;
        $sql_data_array = array( 
            'access_date' => 'now()',
            'admin_id' => $_SESSION['emp_admin_id'],
            'page_accessed' => 'login.php',
            'page_parameters' => '',
            'ip_address' => substr($_SERVER['REMOTE_ADDR'],0,45),
            'gzpost' => gzdeflate(json_encode(array('action' => 'emp_admin_login', 'customer_email_address' => $email_address)), 7),
            'flagged' => 0,
            'attention' => '',
            'severity' => 'info',
            'logmessage' => 'EMP admin login',
        );
        zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
    }
    return $admin_authorized;
}
