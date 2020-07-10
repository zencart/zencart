<?php
/**
 * Header code file for the Address Book Process page
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Nov 16 Modified in v1.5.7 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ADDRESS_BOOK_PROCESS');

if (!zen_is_logged_in()) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

/**
 * Process deletes
 */
if (isset($_GET['action']) && ($_GET['action'] == 'deleteconfirm') && isset($_POST['delete']) && is_numeric($_POST['delete'])) 
{
  $sql = "DELETE FROM " . TABLE_ADDRESS_BOOK . "
          WHERE  address_book_id = :delete
          AND    customers_id = :customersID";

  $sql = $db->bindVars($sql, ':customersID', $_SESSION['customer_id'], 'integer');
  $sql = $db->bindVars($sql, ':delete', $_POST['delete'], 'integer');
  $db->Execute($sql);

  $zco_notifier->notify('NOTIFY_HEADER_ADDRESS_BOOK_DELETION_DONE');

  $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'success');

  zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
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
/**
 * Process new/update actions
 */
if (isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update'))) {
  $process = true;

  if (ACCOUNT_GENDER == 'true') $gender = zen_db_prepare_input($_POST['gender']);
  if (ACCOUNT_COMPANY == 'true') $company = zen_db_prepare_input($_POST['company']);
  $firstname = zen_db_prepare_input(zen_sanitize_string($_POST['firstname']));
  $lastname = zen_db_prepare_input(zen_sanitize_string($_POST['lastname']));
  $street_address = zen_db_prepare_input($_POST['street_address']);
  if (ACCOUNT_SUBURB == 'true') $suburb = zen_db_prepare_input($_POST['suburb']);
  $postcode = zen_db_prepare_input($_POST['postcode']);
  $city = zen_db_prepare_input($_POST['city']);


  /**
   * error checking when updating or adding an entry
   */
  if (ACCOUNT_STATE == 'true') {
    $state = (isset($_POST['state'])) ? zen_db_prepare_input($_POST['state']) : FALSE;
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
      $messageStack->add('addressbook', ENTRY_GENDER_ERROR);
    }
  }

  if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
    $error = true;
    $messageStack->add('addressbook', ENTRY_FIRST_NAME_ERROR);
  }

  if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
    $error = true;
    $messageStack->add('addressbook', ENTRY_LAST_NAME_ERROR);
  }

  if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
    $error = true;
    $messageStack->add('addressbook', ENTRY_STREET_ADDRESS_ERROR);
  }

  if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
    $error = true;
    $messageStack->add('addressbook', ENTRY_CITY_ERROR);
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
        $messageStack->add('addressbook', ENTRY_STATE_ERROR_SELECT);
      }
    } else {
      if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
        $error = true;
        $error_state_input = true;
        $messageStack->add('addressbook', ENTRY_STATE_ERROR);
      }
    }
  }

  if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
    $error = true;
    $messageStack->add('addressbook', ENTRY_POST_CODE_ERROR);
  }

  if (!is_numeric($country)) {
    $error = true;
    $messageStack->add('addressbook', ENTRY_COUNTRY_ERROR);
  }

  // -----
  // Give an observer the opportunity to check the data submitted and identify
  // an additional error.
  //
  $zco_notifier->notify('NOTIFY_ADDRESS_BOOK_PROCESS_VALIDATION', array(), $error);

  if ($error == false) {
    $sql_data_array= array(array('fieldName'=>'entry_firstname', 'value'=>$firstname, 'type'=>'stringIgnoreNull'),
                           array('fieldName'=>'entry_lastname', 'value'=>$lastname, 'type'=>'stringIgnoreNull'),
                           array('fieldName'=>'entry_street_address', 'value'=>$street_address, 'type'=>'stringIgnoreNull'),
                           array('fieldName'=>'entry_postcode', 'value'=>$postcode, 'type'=>'stringIgnoreNull'),
                           array('fieldName'=>'entry_city', 'value'=>$city, 'type'=>'stringIgnoreNull'),
                           array('fieldName'=>'entry_country_id', 'value'=>$country, 'type'=>'integer'));

    if (ACCOUNT_GENDER == 'true') $sql_data_array[] = array('fieldName'=>'entry_gender', 'value'=>$gender, 'type'=>'enum:m|f');
    if (ACCOUNT_COMPANY == 'true') $sql_data_array[] = array('fieldName'=>'entry_company', 'value'=>$company, 'type'=>'stringIgnoreNull');
    if (ACCOUNT_SUBURB == 'true') $sql_data_array[] = array('fieldName'=>'entry_suburb', 'value'=>$suburb, 'type'=>'stringIgnoreNull');
    if (ACCOUNT_STATE == 'true') {
      if ($zone_id > 0) {
        $sql_data_array[] = array('fieldName'=>'entry_zone_id', 'value'=>$zone_id, 'type'=>'integer');
        $sql_data_array[] = array('fieldName'=>'entry_state', 'value'=>'', 'type'=>'stringIgnoreNull');
      } else {
        $sql_data_array[] = array('fieldName'=>'entry_zone_id', 'value'=>'0', 'type'=>'integer');
        $sql_data_array[] = array('fieldName'=>'entry_state', 'value'=>$state, 'type'=>'stringIgnoreNull');
      }
    }

    if ($_POST['action'] == 'update') {
      $where_clause = "address_book_id = :edit and customers_id = :customersID";
      $where_clause = $db->bindVars($where_clause, ':customersID', $_SESSION['customer_id'], 'integer');
      $where_clause = $db->bindVars($where_clause, ':edit', $_GET['edit'], 'integer');
      $db->perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', $where_clause);

      $zco_notifier->notify('NOTIFY_MODULE_ADDRESS_BOOK_UPDATED_ADDRESS_BOOK_RECORD', array_merge(array('address_book_id' => $_GET['edit'], 'customers_id' => $_SESSION['customer_id']), $sql_data_array));

      // re-register session variables
      if ( (isset($_POST['primary']) && ($_POST['primary'] == 'on')) || ($_GET['edit'] == $_SESSION['customer_default_address_id']) ) {
        $_SESSION['customer_first_name'] = $firstname;
        $_SESSION['customer_last_name'] = $lastname;
        $_SESSION['customer_country_id'] = $country;
        $_SESSION['customer_zone_id'] = (($zone_id > 0) ? (int)$zone_id : '0');
        $_SESSION['customer_default_address_id'] = (int)$_GET['edit'];

        $sql_data_array = array(array('fieldName'=>'customers_firstname', 'value'=>$firstname, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_lastname', 'value'=>$lastname, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_default_address_id', 'value'=>$_GET['edit'], 'type'=>'integer'));

        if (ACCOUNT_GENDER == 'true') $sql_data_array[] = array('fieldName'=>'customers_gender', 'value'=>$gender, 'type'=>'enum:m|f');
        $where_clause = "customers_id = :customersID";
        $where_clause = $db->bindVars($where_clause, ':customersID', $_SESSION['customer_id'], 'integer');
        $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', $where_clause);
        $zco_notifier->notify('NOTIFY_MODULE_ADDRESS_BOOK_UPDATED_CUSTOMER_RECORD', array_merge(array('customers_id' => $_SESSION['customer_id']), $sql_data_array));
      }
    } else {

      $sql_data_array[] = array('fieldName'=>'customers_id', 'value'=>$_SESSION['customer_id'], 'type'=>'integer');
      $db->perform(TABLE_ADDRESS_BOOK, $sql_data_array);

      $new_address_book_id = $db->Insert_ID();
      $zco_notifier->notify('NOTIFY_MODULE_ADDRESS_BOOK_ADDED_ADDRESS_BOOK_RECORD', array_merge(array('address_id' => $new_address_book_id), $sql_data_array));


      // register session variables
      if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) {
        $_SESSION['customer_first_name'] = $firstname;
        $_SESSION['customer_last_name'] = $lastname;
        $_SESSION['customer_country_id'] = $country;
        $_SESSION['customer_zone_id'] = (($zone_id > 0) ? (int)$zone_id : '0');
        //if (isset($_POST['primary']) && ($_POST['primary'] == 'on'))
        $_SESSION['customer_default_address_id'] = $new_address_book_id;

        $sql_data_array = array(array('fieldName'=>'customers_firstname', 'value'=>$firstname, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_lastname', 'value'=>$lastname, 'type'=>'stringIgnoreNull'));

        if (ACCOUNT_GENDER == 'true') $sql_data_array[] = array('fieldName'=>'customers_gender', 'value'=>$gender, 'type'=>'stringIgnoreNull');
        //if (isset($_POST['primary']) && ($_POST['primary'] == 'on'))
        $sql_data_array[] = array('fieldName'=>'customers_default_address_id', 'value'=>$new_address_book_id, 'type'=>'integer');

        $where_clause = "customers_id = :customersID";
        $where_clause = $db->bindVars($where_clause, ':customersID', $_SESSION['customer_id'], 'integer');
        $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', $where_clause);
        $zco_notifier->notify('NOTIFY_MODULE_ADDRESS_BOOK_UPDATED_PRIMARY_CUSTOMER_RECORD', array_merge(array('address_id' => $new_address_book_id, 'customers_id' => $_SESSION['customer_id']), $sql_data_array));
      }
    }

    $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'success');

    zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  }
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
  $entry_query = "SELECT entry_gender, entry_company, entry_firstname, entry_lastname,
                         entry_street_address, entry_suburb, entry_postcode, entry_city,
                         entry_state, entry_zone_id, entry_country_id
                  FROM   " . TABLE_ADDRESS_BOOK . "
                  WHERE  customers_id = :customersID
                  AND    address_book_id = :addressBookID";

  $entry_query = $db->bindVars($entry_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $entry_query = $db->bindVars($entry_query, ':addressBookID', $_GET['edit'], 'integer');
  $entry = $db->Execute($entry_query);

  if ($entry->RecordCount()<=0) {
    $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

    zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  }
  if (!isset($zone_name) || (int)$zone_name == 0) $zone_name = zen_get_zone_name($entry->fields['entry_country_id'], $entry->fields['entry_zone_id'], $entry->fields['entry_state']);
  if (!isset($zone_id) || (int)$zone_id == 0) $zone_id = $entry->fields['entry_zone_id'];

} elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  if ($_GET['delete'] == $_SESSION['customer_default_address_id']) {
    $messageStack->add_session('addressbook', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');

    zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  } else {
    $check_query = "SELECT count(*) AS total
                    FROM " . TABLE_ADDRESS_BOOK . "
                    WHERE address_book_id = :addressBookID
                    AND customers_id = :customersID";

    $check_query = $db->bindVars($check_query, ':customersID', $_SESSION['customer_id'], 'integer');
    $check_query = $db->bindVars($check_query, ':addressBookID', $_GET['delete'], 'integer');
    $check = $db->Execute($check_query);

    if ($check->fields['total'] < 1) {
      $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }
  }
} else {
  $entry_query = "SELECT entry_country_id
                  FROM   " . TABLE_ADDRESS_BOOK . " a, " . TABLE_CUSTOMERS . " c
                  WHERE  a.customers_id = :customersID
                  AND  a.customers_id = c.customers_id
                  AND    a.address_book_id = c.customers_default_address_id";

  $entry_query = $db->bindVars($entry_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $entry = $db->Execute($entry_query);
  
  $entry->fields['entry_gender'] = 'm';
  $entry->fields['entry_firstname'] = '';
  $entry->fields['entry_lastname'] = '';
  $entry->fields['entry_company'] = '';
  $entry->fields['entry_street_address'] = '';
  $entry->fields['entry_suburb'] = '';
  $entry->fields['entry_city'] = '';
  $entry->fields['entry_state'] = '';
  $entry->fields['entry_zone_id'] = 0;
  $entry->fields['entry_postcode'] = '';
}
/*
 * Set flags for template use:
 */
if (!isset($_GET['delete'])) {
  if ($process == false) {
    $selected_country = $entry->fields['entry_country_id'];
  } else {
    $selected_country = (isset($_POST['zone_country_id']) && $_POST['zone_country_id'] != '') ? $country : SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY;
    $entry->fields['entry_country_id'] = $selected_country;
  }
  $flag_show_pulldown_states = ((($process == true || $entry_state_has_zones == true) && $zone_name == '') || ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN == 'true' || $error_state_input) ? true : false;
  $state = ($flag_show_pulldown_states && $state != FALSE) ? $state : $zone_name;
  $state_field_label = ($flag_show_pulldown_states) ? '' : ENTRY_STATE;
}


if (!isset($_GET['delete']) && !isset($_GET['edit'])) {
  if (zen_count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
    $messageStack->add_session('addressbook', ERROR_ADDRESS_BOOK_FULL);
    zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  }
}

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2, zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
  $breadcrumb->add(NAVBAR_TITLE_MODIFY_ENTRY);
} elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $breadcrumb->add(NAVBAR_TITLE_DELETE_ENTRY);
} else {
  $breadcrumb->add(NAVBAR_TITLE_ADD_ENTRY);
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ADDRESS_BOOK_PROCESS');
