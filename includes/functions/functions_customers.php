<?php
/**
 * functions_customers
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: Modified in v1.6.0 $
 */

/**
 * Returns the address_format_id for the given country
 * @param int $country_id
 * @return int
 */
  function zen_get_address_format_id($country_id) {
    global $db;
    $address_format_query = "select address_format_id as format_id
                             from " . TABLE_COUNTRIES . "
                             where countries_id = " . (int)$country_id;

    $address_format = $db->Execute($address_format_query);

    if ($address_format->RecordCount() > 0) {
      return $address_format->fields['format_id'];
    } else {
      return 1;
    }
  }

/**
 * Return a formatted address
 * @param int $address_format_id
 * @param array $address
 * @param bool $html
 * @param string $boln
 * @param string $eoln
 * @return string
 */
  function zen_address_format($address_format_id, $address, $html, $boln, $eoln) {
    global $db;
    $address_out = '';
    $address_format_query = "select address_format as format
                             from " . TABLE_ADDRESS_FORMAT . "
                             where address_format_id = " . (int)$address_format_id;

    $address_format = $db->Execute($address_format_query);
    $company = zen_output_string_protected($address['company']);
    if (isset($address['firstname']) && zen_not_null($address['firstname'])) {
      $firstname = zen_output_string_protected($address['firstname']);
      $lastname = zen_output_string_protected($address['lastname']);
    } elseif (isset($address['name']) && zen_not_null($address['name'])) {
      $firstname = zen_output_string_protected($address['name']);
      $lastname = '';
    } else {
      $firstname = '';
      $lastname = '';
    }
    $street = zen_output_string_protected($address['street_address']);
    $suburb = zen_output_string_protected($address['suburb']);
    $city = zen_output_string_protected($address['city']);
    $state = zen_output_string_protected($address['state']);
    if (isset($address['country_id']) && zen_not_null($address['country_id'])) {
      $country = zen_get_country_name($address['country_id']);

      if (isset($address['zone_id']) && zen_not_null($address['zone_id'])) {
        $state = zen_get_zone_code($address['country_id'], $address['zone_id'], $state);
      }
    } elseif (isset($address['country']) && zen_not_null($address['country'])) {
      if (is_array($address['country'])) {
        $country = zen_output_string_protected($address['country']['countries_name']);
      } else {
      $country = zen_output_string_protected($address['country']);
      }
    } else {
      $country = '';
    }
    $postcode = zen_output_string_protected($address['postcode']);
    $zip = $postcode;

    if ($html) {
// HTML Mode
      $HR = '<hr />';
      $hr = '<hr />';
      if ( ($boln == '') && ($eoln == "\n") ) { // Values not specified, use rational defaults
        $CR = '<br />';
        $cr = '<br />';
        $eoln = $cr;
      } else { // Use values supplied
        $CR = $eoln . $boln;
        $cr = $CR;
      }
    } else {
// Text Mode
      $CR = $eoln;
      $cr = $CR;
      $HR = '----------------------------------------';
      $hr = '----------------------------------------';
    }

    $statecomma = '';
    $streets = $street;
    if ($suburb != '') $streets = $street . $cr . $suburb;
    if ($country == '') {
      if (is_array($address['country'])) {
        $country = zen_output_string_protected($address['country']['countries_name']);
      } else {
      $country = zen_output_string_protected($address['country']);
      }
    }
    if ($state != '') $statecomma = $state . ', ';

    $fmt = $address_format->fields['format'];
    eval("\$address_out = \"$fmt\";");

    if ( (ACCOUNT_COMPANY == 'true') && (zen_not_null($company)) ) {
      $address_out = $company . $cr . $address_out;
    }

    return $address_out;
  }

/**
 * Return a formatted address
 * @param int $customers_id
 * @param int $address_id
 * @param bool $html
 * @param string $boln
 * @param string $eoln
 * @return string
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

    $format_id = zen_get_address_format_id($address->fields['country_id']);
    return zen_address_format($format_id, $address->fields, $html, $boln, $eoln);
  }

/**
 * Return a customer greeting
 * @return string
 */
  function zen_customer_greeting() {

    if (isset($_SESSION['customer_id']) && $_SESSION['customer_first_name']) {
      $greeting_string = sprintf(TEXT_GREETING_PERSONAL, zen_output_string_protected($_SESSION['customer_first_name']), zen_href_link(FILENAME_PRODUCTS_NEW));
    } else {
      $greeting_string = sprintf(TEXT_GREETING_GUEST, zen_href_link(FILENAME_LOGIN, '', 'SSL'), zen_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
    }

    return $greeting_string;
  }

/**
 * @param string $id
 * @param bool $check_session
 * @return int
 */
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

/**
 * @param string $id
 * @param bool $check_session
 * @return int
 */
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

/**
 * look up customer's default or primary address
 * @param int $customer_id
 * @return int
 */
  function zen_get_customers_address_primary($customer_id) {
    global $db;

    $sql = "SELECT customers_default_address_id
            from " . TABLE_CUSTOMERS . "
            WHERE customers_id = " . (int)$customer_id;

    $result = $db->Execute($sql);

    return $result->fields['customers_default_address_id'];
  }


/**
 * customer lookup of address book records
 *
 * @param int $customer_id
 * @return queryFactoryResult
 */
  function zen_get_customers_address_book($customer_id) {
    global $db;

    $sql = "SELECT c.*, ab.* from " .
            TABLE_CUSTOMERS . " c
            left join " . TABLE_ADDRESS_BOOK . " ab on c.customers_id = ab.customers_id
            WHERE c.customers_id = " . (int)$customer_id;

    $result = $db->Execute($sql);
    return $result;
  }

/**
 * validate customer matches session
 * If banned, purge their shopping basket
 *
 * @param $customer_id
 * @return bool
 */
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
 * Get concatenated first+last name of customer
 * @param int $customers_id
 * @return string
 */
  function zen_customers_name($customers_id) {
    global $db;
    $result = $db->Execute("select customers_firstname, customers_lastname
                            from " . TABLE_CUSTOMERS . "
                            where customers_id = " . (int)$customers_id);
    if ($result->EOF) return '';
    return $result->fields['customers_firstname'] . ' ' . $result->fields['customers_lastname'];
  }

/**
 * Build pulldown list of customer addresses
 *
 * @param int $customers_id
 * @return array
 */
  function zen_prepare_customer_address_pulldown($customers_id) {
    global $db;
    $sql = "SELECT address_book_id, entry_firstname as firstname, entry_lastname as lastname,
            entry_company as company, entry_street_address as street_address,
            entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
            entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id
            FROM   " . TABLE_ADDRESS_BOOK . "
            WHERE  customers_id = :customersID
            ORDER BY firstname, lastname";

    $sql = $db->bindVars($sql, ':customersID', $customers_id, 'integer');
    $addresses = $db->Execute($sql);
    $addressArray = array();
    foreach ($addresses as $address) {
      $format_id = zen_get_address_format_id($address['country_id']);

      $addressArray[] = array('firstname'=>$address['firstname'],
                              'lastname'=>$address['lastname'],
                              'address_book_id'=>$address['address_book_id'],
                              'format_id'=>$format_id,
                              'address'=>$address);
    }
    return $addressArray;
  }

/**
 * @param int $customer_id
 * @param int $current_status
 * @param bool $send_email_notification
 */
  function zen_toggle_customer_auth_status($customer_id, $current_status, $send_email_notification = true)
  {
    global $db, $zco_notifier;
    $customer_id = (int)$customer_id;
    $current_status = (int)$current_status;

    if ($current_status == CUSTOMERS_APPROVAL_AUTHORIZATION) {
      $sql = "update " . TABLE_CUSTOMERS . " set customers_authorization=0 where customers_id=" . $customer_id;
      $custinfo = $db->Execute("select customers_email_address, customers_firstname, customers_lastname
                                from " . TABLE_CUSTOMERS . "
                                where customers_id = " . $customer_id);
      if ($send_email_notification === true && (int)CUSTOMERS_APPROVAL_AUTHORIZATION > 0 && (int)$current_status > 0 && $custinfo->RecordCount() > 0) {
        $message = EMAIL_CUSTOMER_STATUS_CHANGE_MESSAGE;
        $html_msg['EMAIL_MESSAGE_HTML'] = EMAIL_CUSTOMER_STATUS_CHANGE_MESSAGE ;
        zen_mail($custinfo->fields['customers_firstname'] . ' ' . $custinfo->fields['customers_lastname'], $custinfo->fields['customers_email_address'], EMAIL_CUSTOMER_STATUS_CHANGE_SUBJECT , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'default');
      }
      zen_record_admin_activity('Customer-approval-authorization set customer auth status to 0 for customer ID ' . $customer_id, 'info');
      $zco_notifier->notify('ADMIN_CUSTOMER_AUTHORIZATION_CHANGE', 0, $customer_id);
    } else {
      $sql = "update " . TABLE_CUSTOMERS . " set customers_authorization='" . CUSTOMERS_APPROVAL_AUTHORIZATION . "' where customers_id=" . $customer_id;
      zen_record_admin_activity('Customer-approval-authorization set customer auth status to ' . CUSTOMERS_APPROVAL_AUTHORIZATION . ' for customer ID ' . $customer_id, 'info');
      $zco_notifier->notify('ADMIN_CUSTOMER_AUTHORIZATION_CHANGE', CUSTOMERS_APPROVAL_AUTHORIZATION, $customer_id);
    }
    $db->Execute($sql);

  }

/**
 * @param int $customer_id
 * @param bool $delete_reviews
 */
  function zen_delete_customer($customer_id, $delete_reviews = true)
  {
    global $db, $zco_notifier;
    $customer_id = (int)$customer_id;
    if ($delete_reviews === true) {
      $reviews = $db->Execute("select reviews_id from " . TABLE_REVIEWS . " where customers_id = " . $customer_id);
      foreach ($reviews as $review) {
        $db->Execute("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = " . (int)$review['reviews_id']);
      }
      $db->Execute("delete from " . TABLE_REVIEWS . " where customers_id = " . $customer_id);
    } else {
      $db->Execute("UPDATE " . TABLE_REVIEWS . " SET customers_id = null WHERE customers_id = " . $customer_id);
    }

    $db->Execute("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = " . $customer_id);
    $db->Execute("delete from " . TABLE_CUSTOMERS . " where customers_id = " . $customer_id);
    $db->Execute("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = " . $customer_id);
    $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = " . $customer_id);
    $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = " . $customer_id);
    $db->Execute("delete from " . TABLE_PRODUCTS_NOTIFICATIONS . " where customers_id = " . $customer_id);
    $db->Execute("delete from " . TABLE_WHOS_ONLINE . " where customer_id = " . $customer_id);
    zen_record_admin_activity('Customer with customer ID ' . $customer_id . ' deleted.', 'warning');

    $zco_notifier->notify('ADMIN_CUSTOMER_DELETED', $customer_id);
  }

/**
 * Reset customer password (typically called via Admin)
 *
 * @param int $customer_id
 * @param string $new_password
 * @return bool
 */
function zen_reset_customer_password($customer_id, $new_password)
  {
    global $db;
    $sql = "SELECT customers_email_address, customers_firstname, customers_lastname
             FROM " . TABLE_CUSTOMERS . "
             WHERE customers_id = :customersID";
    $sql = $db->bindVars($sql, ':customersID', $customer_id, 'integer');
    $custinfo = $db->Execute($sql);
    if ($custinfo->RecordCount() == 0) return false;

    $sql = "UPDATE " . TABLE_CUSTOMERS . "
            SET customers_password = :password
            WHERE customers_id = :customersID";
    $sql = $db->bindVars($sql, ':customersID', $customer_id, 'integer');
    $sql = $db->bindVars($sql, ':password',zen_encrypt_password($new_password), 'string');
    $db->Execute($sql);
    $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
            SET    customers_info_date_account_last_modified = now()
            WHERE  customers_info_id = :customersID";
    $sql = $db->bindVars($sql, ':customersID', $customer_id, 'integer');
    $db->Execute($sql);

    $message = EMAIL_CUSTOMER_PWD_CHANGE_MESSAGE . "\n\n" . $new_password . "\n\n\n";
    $html_msg['EMAIL_MESSAGE_HTML'] = nl2br($message);
    zen_mail($custinfo->fields['customers_firstname'] . ' ' . $custinfo->fields['customers_lastname'], $custinfo->fields['customers_email_address'], EMAIL_CUSTOMER_PWD_CHANGE_SUBJECT , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'default');
    $userList = zen_get_users($_SESSION['admin_id']);
    $userDetails = $userList[0];
    $adminUser = $userDetails['id'] . '-' . $userDetails['name'] . ' ' . zen_get_ip_address();
    $message = sprintf(EMAIL_CUSTOMER_PWD_CHANGE_MESSAGE_FOR_ADMIN, $custinfo->fields['customers_firstname'] . ' ' . $custinfo->fields['customers_lastname'] . ' ' . $custinfo->fields['customers_email_address'], $adminUser) . "\n";
    $html_msg['EMAIL_MESSAGE_HTML'] = nl2br($message);
    zen_mail($userDetails['name'], $userDetails['email'], EMAIL_CUSTOMER_PWD_CHANGE_SUBJECT , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'default');
    return true;
  }

/**
 * Check that the specified email address is unique ... not used by any other customer than the one specified
 *
 * @param string $email_address
 * @param int $customer_id
 * @return bool
 */
function zen_check_customer_email_is_unique($email_address, $customer_id)
{
    global $db;
    $result = $db->Execute("select customers_email_address
                            from " . TABLE_CUSTOMERS . "
                            where customers_email_address = '" . zen_db_input($email_address) . "'
                            and customers_id != " . (int)$customer_id );
    return ($result->RecordCount() == 0);
}

/**
 * @return array
 */
function zen_get_customers_authorization_pulldown_array()
{
    return array(array('id' => '0', 'text' => CUSTOMERS_AUTHORIZATION_0), // approved
                 array('id' => '1', 'text' => CUSTOMERS_AUTHORIZATION_1), // pending approval, must be auth to browse
                 array('id' => '2', 'text' => CUSTOMERS_AUTHORIZATION_2), // pending approval, may browse no prices
                 array('id' => '3', 'text' => CUSTOMERS_AUTHORIZATION_3), // pending approval, may browse with price, but not buy
                 array('id' => '4', 'text' => CUSTOMERS_AUTHORIZATION_4), // banned
    );
}

/**
 * Get name and percentage details for specified group pricing id
 * @param int $group_id
 * @return array
 */
function zen_get_group_pricing_detail($group_id)
{
    global $db;
    $result = $db->Execute("select group_name, group_percentage from " . TABLE_GROUP_PRICING . " where group_id = " . (int)$group_id);
    return $result->fields;
}

/**
 * Build pulldown menu array for available group pricing choices
 * @return array
 */
function zen_get_group_pricing_pulldown()
{
    global $db;
    $group_array_query = $db->execute("select group_id, group_name, group_percentage from " . TABLE_GROUP_PRICING);
    $group_array[] = array('id' => 0, 'text' => TEXT_NONE);
    foreach ($group_array_query as $group) {
      $group_array[] = array('id' => $group['group_id'], 'text' => $group['group_name'] . '&nbsp;' . $group['group_percentage'] . '%');
    }
    return $group_array;
}

/**
 * Retrieve basic order summary data for specified customer
 * (Used in admin customer-details sidebar)
 *
 * @param int $customer_id
 * @return array
 */
function zen_get_customer_order_summary($customer_id)
{
    global $db;
    $result = $db->Execute("select o.orders_id, o.date_purchased, o.order_total, o.currency, o.currency_value, cgc.amount as gvbal
                            from " . TABLE_ORDERS . " o
                            left join " . TABLE_COUPON_GV_CUSTOMER . " cgc on (o.customers_id = cgc.customer_id)
                            where customers_id=" . (int)$customer_id . " order by date_purchased desc");
    if (sizeof($result) == 0) return ['number_of_orders' => 0];
    $data = $result->fields;
    $data['number_of_orders'] = sizeof($result);
    return $data;
}

function zen_admin_customer_pwd_reset($customer_id, $newpass, $newconfirmpass)
{
    global $messageStack;
    if ((int)$customer_id > 0 && isset($newpass) && $newpass != '' && isset($newconfirmpass) && $newconfirmpass != '') {
        $password_new = zen_db_prepare_input($newpass);
        $password_confirmation = zen_db_prepare_input($newconfirmpass);
        if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
            $messageStack->add_session(ERROR_PWD_TOO_SHORT . '(' . ENTRY_PASSWORD_MIN_LENGTH . ')', 'error');
            return false;
        }
        if ($password_new != $password_confirmation) {
            $messageStack->add_session(ERROR_PASSWORDS_NOT_MATCHING, 'error');
            return false;
        }
        zen_reset_customer_password($customer_id, $password_new);
        $messageStack->add_session(SUCCESS_PASSWORD_UPDATED, 'success');
        return true;
    }
    return false;

}
