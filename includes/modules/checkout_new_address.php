<?php
/**
 * checkout_new_address.php
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Oct 29 Modified in v1.5.7 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_MODULE_START_CHECKOUT_NEW_ADDRESS');

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Set some defaults
 */
  $process = false;
  $zone_name = '';
  $entry_state_has_zones = '';
  $error_state_input = false;
  $state = '';
  $zone_id = 0;
  $error = false;

if (isset($_POST['action']) && ($_POST['action'] == 'submit')) {
  // process a new address
  if (!empty($_POST['firstname']) && !empty($_POST['lastname']) && !empty($_POST['street_address'])) {
    $process = true;
    if (ACCOUNT_GENDER == 'true') $gender = zen_db_prepare_input($_POST['gender']);
    if (ACCOUNT_COMPANY == 'true') $company = zen_db_prepare_input($_POST['company']);
    $firstname = zen_db_prepare_input($_POST['firstname']);
    $lastname = zen_db_prepare_input($_POST['lastname']);
    $street_address = zen_db_prepare_input($_POST['street_address']);
    if (ACCOUNT_SUBURB == 'true') $suburb = zen_db_prepare_input($_POST['suburb']);
    $postcode = zen_db_prepare_input($_POST['postcode']);
    $city = zen_db_prepare_input($_POST['city']);
    if (ACCOUNT_STATE == 'true') {
      $state = (isset($_POST['state'])) ? zen_db_prepare_input($_POST['state']) : '';
      if (isset($_POST['zone_id'])) {
        $zone_id = zen_db_prepare_input($_POST['zone_id']);
      } else {
        $zone_id = false;
      }
    }
    $country = zen_db_prepare_input($_POST['zone_country_id']);
    if (ACCOUNT_GENDER == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;
        $messageStack->add('checkout_address', ENTRY_GENDER_ERROR);
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('checkout_address', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('checkout_address', ENTRY_LAST_NAME_ERROR);
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('checkout_address', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;
      $messageStack->add('checkout_address', ENTRY_CITY_ERROR);
    }

    if (ACCOUNT_STATE == 'true') {
      $check_query = "SELECT count(*) AS total
                      FROM " . TABLE_ZONES . "
                      WHERE zone_country_id = :zoneCountryID";
      $check_query = $db->bindVars($check_query, ':zoneCountryID', $country, 'integer');
      $check = $db->Execute($check_query);
      $entry_state_has_zones = ($check->fields['total'] > 0);
      if ($entry_state_has_zones == true) {
      $zone_query = "SELECT distinct zone_id, zone_name, zone_code
                       FROM " . TABLE_ZONES . "
                       WHERE zone_country_id = :zoneCountryID
                       AND " .
                     ((trim($state) != '' && $zone_id == 0) ? "(upper(zone_name) like ':zoneState%' OR upper(zone_code) like '%:zoneState%') OR " : "") .
                      "zone_id = :zoneID
                       ORDER BY zone_code ASC, zone_name";

        $zone_query = $db->bindVars($zone_query, ':zoneCountryID', $country, 'integer');
        $zone_query = $db->bindVars($zone_query, ':zoneState', strtoupper($state), 'noquotestring');
        $zone_query = $db->bindVars($zone_query, ':zoneID', $zone_id, 'integer');
        $zone = $db->Execute($zone_query);

      //look for an exact match on zone ISO code
      $found_exact_iso_match = ($zone->RecordCount() == 1);
      if ($zone->RecordCount() > 1) {
        while (!$zone->EOF && !$found_exact_iso_match) {
          if (strtoupper($zone->fields['zone_code']) == strtoupper($state) ) {
            $found_exact_iso_match = true;
            continue;
          }
          $zone->MoveNext();
        }
      }

      if ($found_exact_iso_match) {
        $zone_id = $zone->fields['zone_id'];
        $zone_name = $zone->fields['zone_name'];
      } else {
        $error = true;
        $error_state_input = true;
        $messageStack->add('checkout_address', ENTRY_STATE_ERROR_SELECT);
      }
    } else {
      if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
        $error = true;
        $error_state_input = true;
        $messageStack->add('checkout_address', ENTRY_STATE_ERROR);
      }
    }
  }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;
      $messageStack->add('checkout_address', ENTRY_POST_CODE_ERROR);
    }

    if ( (is_numeric($country) == false) || ($country < 1) ) {
      $error = true;
      $messageStack->add('checkout_address', ENTRY_COUNTRY_ERROR);
    }
    
    $zco_notifier->notify('NOTIFY_MODULE_CHECKOUT_NEW_ADDRESS_VALIDATION', array(), $error);

    if ($error == false) {
      $sql_data_array = array(array('fieldName'=>'customers_id', 'value'=>$_SESSION['customer_id'], 'type'=>'integer'),
                              array('fieldName'=>'entry_firstname', 'value'=>$firstname, 'type'=>'stringIgnoreNull'),
                              array('fieldName'=>'entry_lastname','value'=>$lastname, 'type'=>'stringIgnoreNull'),
                              array('fieldName'=>'entry_street_address','value'=>$street_address, 'type'=>'stringIgnoreNull'),
                              array('fieldName'=>'entry_postcode', 'value'=>$postcode, 'type'=>'stringIgnoreNull'),
                              array('fieldName'=>'entry_city', 'value'=>$city, 'type'=>'stringIgnoreNull'),
                              array('fieldName'=>'entry_country_id', 'value'=>$country, 'type'=>'integer')
      );

      if (ACCOUNT_GENDER == 'true') $sql_data_array[] = array('fieldName'=>'entry_gender', 'value'=>$gender, 'type'=>'enum:m|f');
      if (ACCOUNT_COMPANY == 'true') $sql_data_array[] = array('fieldName'=>'entry_company', 'value'=>$company, 'type'=>'stringIgnoreNull');
      if (ACCOUNT_SUBURB == 'true') $sql_data_array[] = array('fieldName'=>'entry_suburb', 'value'=>$suburb, 'type'=>'stringIgnoreNull');
      if (ACCOUNT_STATE == 'true') {
        if ($zone_id > 0) {
          $sql_data_array[] = array('fieldName'=>'entry_zone_id', 'value'=>$zone_id, 'type'=>'integer');
          $sql_data_array[] = array('fieldName'=>'entry_state', 'value'=>'', 'type'=>'stringIgnoreNull');
        } else {
          $sql_data_array[] = array('fieldName'=>'entry_zone_id', 'value'=>0, 'type'=>'integer');
          $sql_data_array[] = array('fieldName'=>'entry_state', 'value'=>$state, 'type'=>'stringIgnoreNull');
        }
      }
      $db->perform(TABLE_ADDRESS_BOOK, $sql_data_array);
      $address_book_id = $db->Insert_ID();
      $zco_notifier->notify('NOTIFY_MODULE_CHECKOUT_ADDED_ADDRESS_BOOK_RECORD', array_merge(array('address_id' => $address_book_id ), $sql_data_array));
      switch($addressType) {
        case 'billto':
        $_SESSION['billto'] = $address_book_id;
        $_SESSION['payment'] = '';
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        break;
        case 'shipto':
        $_SESSION['sendto'] = $address_book_id;
        unset($_SESSION['shipping']);
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        break;
      }
    }
  } elseif (isset($_POST['address'])) {
    switch($addressType) {
      case 'billto':
      $reset_payment = false;
      if ($_SESSION['billto']) {
        if ($_SESSION['billto'] != $_POST['address']) {
          if ($_SESSION['payment']) {
            $reset_payment = true;
          }
        }
      }
      $_SESSION['billto'] = $_POST['address'];

      $check_address_query = "SELECT count(*) AS total
                              FROM " . TABLE_ADDRESS_BOOK . "
                              WHERE customers_id = :customersID
                              AND address_book_id = :addressBookID";

      $check_address_query = $db->bindVars($check_address_query, ':customersID', $_SESSION['customer_id'], 'integer');
      $check_address_query = $db->bindVars($check_address_query, ':addressBookID', $_SESSION['billto'], 'integer');
      $check_address = $db->Execute($check_address_query);

      if ($check_address->fields['total'] == '1') {
        if ($reset_payment == true) $_SESSION['payment'] = '';
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      } else {
        $_SESSION['billto'] = '';
      }
      // no addresses to select from - customer decided to keep the current assigned address
      break;
      case 'shipto':
      $reset_shipping = false;
      if ($_SESSION['sendto']) {
        if ($_SESSION['sendto'] != $_POST['address']) {
          if (isset($_SESSION['shipping'])) {
            $reset_shipping = true;
          }
        }
      }
     $_SESSION['sendto'] = $_POST['address'];
      $check_address_query = "SELECT count(*) AS total
                              FROM " . TABLE_ADDRESS_BOOK . "
                              WHERE customers_id = :customersID
                              AND address_book_id = :addressBookID";

      $check_address_query = $db->bindVars($check_address_query, ':customersID', $_SESSION['customer_id'], 'integer');
      $check_address_query = $db->bindVars($check_address_query, ':addressBookID', $_SESSION['sendto'], 'integer');
      $check_address = $db->Execute($check_address_query);
      if ($check_address->fields['total'] == '1') {
        if ($reset_shipping == true) unset($_SESSION['shipping']);
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
      } else {
        $_SESSION['sendto'] = '';
      }
      break;
    }
  } else {
    switch($addressType) {
      case 'billto':
      $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      break;
      case 'shipto':
      $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
      break;
    }
  }
}


/*
 * Set flags for template use:
 */
  $selected_country = (isset($_POST['zone_country_id']) && $_POST['zone_country_id'] != '') ? $country : SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY;
  $flag_show_pulldown_states = ((($process == true || $entry_state_has_zones == true) && $zone_name == '') || ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN == 'true' || $error_state_input) ? true : false;
  $state = ($flag_show_pulldown_states) ? $state : $zone_name;
  $state_field_label = ($flag_show_pulldown_states) ? '' : ENTRY_STATE;

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_MODULE_END_CHECKOUT_NEW_ADDRESS');
