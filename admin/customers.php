<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:  Modified in v1.6.0 $
 */

  require('includes/application_top.php');

  $currencies = new currencies();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $customers_id = zen_db_prepare_input($_GET['cID']);
  if (isset($_POST['cID'])) $customers_id = zen_db_prepare_input($_POST['cID']);
  if (!isset($_GET['list_order'])) $_GET['list_order'] = '';

  $error = false;
  $processed = false;

  if (zen_not_null($action)) {
    switch ($action) {
      case 'list_addresses':
        $addresses_query = "SELECT address_book_id, entry_firstname as firstname, entry_lastname as lastname,
                            entry_company as company, entry_street_address as street_address,
                            entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                            entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id
                    FROM   " . TABLE_ADDRESS_BOOK . "
                    WHERE  customers_id = :customersID
                    ORDER BY firstname, lastname";

        $addresses_query = $db->bindVars($addresses_query, ':customersID', $_GET['cID'], 'integer');
        $addresses = $db->Execute($addresses_query);
        $addressArray = array();
        foreach ($addresses as $address) { 
          $format_id = zen_get_address_format_id($address['country_id']);

          $addressArray[] = array('firstname'=>$address['firstname'],
                                  'lastname'=>$address['lastname'],
                                  'address_book_id'=>$address['address_book_id'],
                                  'format_id'=>$format_id,
                                  'address'=>$address);
        }

// @TODO - use modal box instead
?>
<fieldset>
<legend><?php echo ADDRESS_BOOK_TITLE; ?></legend>
<div class="alert forward"><?php echo sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES); ?></div>
<br class="clearBoth" />
<?php
/**
 * Used to loop thru and display address book entries
 */
  foreach ($addressArray as $address) {
?>
<h3 class="addressBookDefaultName"><?php echo zen_output_string_protected($address['firstname'] . ' ' . $address['lastname']); ?><?php if ($address['address_book_id'] == zen_get_customers_address_primary($_GET['cID'])) echo '&nbsp;' . PRIMARY_ADDRESS ; ?></h3>
<address><?php echo zen_address_format($address['format_id'], $address['address'], true, ' ', '<br />'); ?></address>

<br class="clearBoth" />
<?php } // end list ?>
<div class="buttonRow forward"><?php echo '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, 'action=list_addresses_done' . '&cID=' . $_GET['cID'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?>
</fieldset>
<?php
        die();
        break;
      case 'list_addresses_done':
        $action = '';
        zen_redirect(zen_admin_href_link(FILENAME_CUSTOMERS, 'cID=' . (int)$_GET['cID'] . '&page=' . $_GET['page']));
        break;
      case 'status':
        if (isset($_POST['current']) && is_numeric($_POST['current']))
        {
          if ($_POST['current'] == CUSTOMERS_APPROVAL_AUTHORIZATION) {
            $sql = "update " . TABLE_CUSTOMERS . " set customers_authorization=0 where customers_id='" . (int)$customers_id . "'";
            $custinfo = $db->Execute("select customers_email_address, customers_firstname, customers_lastname
                                      from " . TABLE_CUSTOMERS . "
                                      where customers_id = '" . (int)$customers_id . "'");
            if ((int)CUSTOMERS_APPROVAL_AUTHORIZATION > 0 && (int)$_POST['current'] > 0 && $custinfo->RecordCount() > 0) {
              $message = EMAIL_CUSTOMER_STATUS_CHANGE_MESSAGE;
              $html_msg['EMAIL_MESSAGE_HTML'] = EMAIL_CUSTOMER_STATUS_CHANGE_MESSAGE ;
              zen_mail($custinfo->fields['customers_firstname'] . ' ' . $custinfo->fields['customers_lastname'], $custinfo->fields['customers_email_address'], EMAIL_CUSTOMER_STATUS_CHANGE_SUBJECT , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'default');
            }
            zen_record_admin_activity('Customer-approval-authorization set customer auth status to 0 for customer ID ' . (int)$customers_id, 'info');
            $zco_notifier->notify('ADMIN_CUSTOMER_AUTHORIZATION_CHANGE', 0, $customers_id);
          } else {
            $sql = "update " . TABLE_CUSTOMERS . " set customers_authorization='" . CUSTOMERS_APPROVAL_AUTHORIZATION . "' where customers_id='" . (int)$customers_id . "'";
            zen_record_admin_activity('Customer-approval-authorization set customer auth status to ' . CUSTOMERS_APPROVAL_AUTHORIZATION . ' for customer ID ' . (int)$customers_id, 'info');
            $zco_notifier->notify('ADMIN_CUSTOMER_AUTHORIZATION_CHANGE', CUSTOMERS_APPROVAL_AUTHORIZATION, $customers_id);
          }
          $db->Execute($sql);
          $action = '';
          zen_redirect(zen_admin_href_link(FILENAME_CUSTOMERS, 'cID=' . (int)$customers_id . '&page=' . $_GET['page']));
        }
        $action = '';
        break;
      case 'update':
        $customers_firstname = zen_db_prepare_input(zen_sanitize_string($_POST['customers_firstname']));
        $customers_lastname = zen_db_prepare_input(zen_sanitize_string($_POST['customers_lastname']));
        $customers_email_address = zen_db_prepare_input($_POST['customers_email_address']);
        $customers_telephone = zen_db_prepare_input($_POST['customers_telephone']);
        $customers_fax = zen_db_prepare_input($_POST['customers_fax']);
        $customers_newsletter = zen_db_prepare_input($_POST['customers_newsletter']);
        $customers_group_pricing = (int)zen_db_prepare_input($_POST['customers_group_pricing']);
        $customers_email_format = zen_db_prepare_input($_POST['customers_email_format']);
        $customers_gender = zen_db_prepare_input($_POST['customers_gender']);
        $customers_dob = (empty($_POST['customers_dob']) ? zen_db_prepare_input('0001-01-01 00:00:00') : zen_db_prepare_input($_POST['customers_dob']));

        $customers_authorization = zen_db_prepare_input($_POST['customers_authorization']);
        $customers_referral= zen_db_prepare_input($_POST['customers_referral']);

        if (CUSTOMERS_APPROVAL_AUTHORIZATION == 2 and $customers_authorization == 1) {
          $customers_authorization = 2;
          $messageStack->add_session(ERROR_CUSTOMER_APPROVAL_CORRECTION2, 'caution');
        }

        if (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 and $customers_authorization == 2) {
          $customers_authorization = 1;
          $messageStack->add_session(ERROR_CUSTOMER_APPROVAL_CORRECTION1, 'caution');
        }

        $default_address_id = zen_db_prepare_input($_POST['default_address_id']);
        $entry_street_address = zen_db_prepare_input($_POST['entry_street_address']);
        $entry_suburb = zen_db_prepare_input($_POST['entry_suburb']);
        $entry_postcode = zen_db_prepare_input($_POST['entry_postcode']);
        $entry_city = zen_db_prepare_input($_POST['entry_city']);
        $entry_country_id = zen_db_prepare_input($_POST['entry_country_id']);

        $entry_company = zen_db_prepare_input($_POST['entry_company']);
        $entry_state = zen_db_prepare_input($_POST['entry_state']);
        if (isset($_POST['entry_zone_id'])) $entry_zone_id = zen_db_prepare_input($_POST['entry_zone_id']);

        if (strlen($customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
          $error = true;
          $entry_firstname_error = true;
        } else {
          $entry_firstname_error = false;
        }

        if (strlen($customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
          $error = true;
          $entry_lastname_error = true;
        } else {
          $entry_lastname_error = false;
        }

        if (ACCOUNT_DOB == 'true') {
          if (ENTRY_DOB_MIN_LENGTH >0) {
            if (checkdate(substr(zen_format_date_raw($customers_dob, 'raw'), 4, 2), substr(zen_format_date_raw($customers_dob, 'raw'), 6, 2), substr(zen_format_date_raw($customers_dob, 'raw'), 0, 4))) {
              $entry_date_of_birth_error = false;
            } else {
              $error = true;
              $entry_date_of_birth_error = true;
            }
          }
        } else {
            $customers_dob = '0001-01-01 00:00:00';
        }

        if (strlen($customers_email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
          $error = true;
          $entry_email_address_error = true;
        } else {
          $entry_email_address_error = false;
        }

        if (!zen_validate_email($customers_email_address)) {
          $error = true;
          $entry_email_address_check_error = true;
        } else {
          $entry_email_address_check_error = false;
        }

        if (strlen($entry_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
          $error = true;
          $entry_street_address_error = true;
        } else {
          $entry_street_address_error = false;
        }

        if (strlen($entry_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
          $error = true;
          $entry_post_code_error = true;
        } else {
          $entry_post_code_error = false;
        }

        if (strlen($entry_city) < ENTRY_CITY_MIN_LENGTH) {
          $error = true;
          $entry_city_error = true;
        } else {
          $entry_city_error = false;
        }

        if ($entry_country_id == false) {
          $error = true;
          $entry_country_error = true;
        } else {
          $entry_country_error = false;
        }

        if (ACCOUNT_STATE == 'true') {
          if ($entry_country_error == true) {
            $entry_state_error = true;
          } else {
            $zone_id = 0;
            $entry_state_error = false;
            $check_value = $db->Execute("select count(*) as total
                                         from " . TABLE_ZONES . "
                                         where zone_country_id = '" . (int)$entry_country_id . "'");

            $entry_state_has_zones = ($check_value->fields['total'] > 0);
            if ($entry_state_has_zones == true) {
              $zone_query = $db->Execute("select zone_id
                                          from " . TABLE_ZONES . "
                                          where zone_country_id = '" . (int)$entry_country_id . "'
                                          and zone_name = '" . zen_db_input($entry_state) . "'");

              if ($zone_query->RecordCount() > 0) {
                $entry_zone_id = $zone_query->fields['zone_id'];
              } else {
                $error = true;
                $entry_state_error = true;
              }
            } else {
              if (strlen($entry_state) < (int)ENTRY_STATE_MIN_LENGTH) {
                $error = true;
                $entry_state_error = true;
              }
            }
         }
      }

      if (strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
        $error = true;
        $entry_telephone_error = true;
      } else {
        $entry_telephone_error = false;
      }

      $check_email = $db->Execute("select customers_email_address
                                   from " . TABLE_CUSTOMERS . "
                                   where customers_email_address = '" . zen_db_input($customers_email_address) . "'
                                   and customers_id != '" . (int)$customers_id . "'");

      if ($check_email->RecordCount() > 0) {
        $error = true;
        $entry_email_address_exists = true;
      } else {
        $entry_email_address_exists = false;
      }

      if ($error == false) {

        $sql_data_array = array(array('fieldName'=>'customers_firstname', 'value'=>$customers_firstname, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_lastname', 'value'=>$customers_lastname, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_email_address', 'value'=>$customers_email_address, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_telephone', 'value'=>$customers_telephone, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_fax', 'value'=>$customers_fax, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_group_pricing', 'value'=>$customers_group_pricing, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_newsletter', 'value'=>$customers_newsletter, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_email_format', 'value'=>$customers_email_format, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_authorization', 'value'=>$customers_authorization, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'customers_referral', 'value'=>$customers_referral, 'type'=>'stringIgnoreNull'),
                                );

        if (ACCOUNT_GENDER == 'true') $sql_data_array[] = array('fieldName'=>'customers_gender', 'value'=>$customers_gender, 'type'=>'stringIgnoreNull');
        if (ACCOUNT_DOB == 'true')  $sql_data_array[] = array('fieldName'=>'customers_dob', 'value'=>($customers_dob == '0001-01-01 00:00:00' ? '0001-01-01 00:00:00' : zen_format_date_raw($customers_dob)), 'type'=>'date');

        $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customers_id . "'");

        $db->Execute("update " . TABLE_CUSTOMERS_INFO . "
                      set customers_info_date_account_last_modified = now()
                      where customers_info_id = '" . (int)$customers_id . "'");

        if ($entry_zone_id > 0) $entry_state = '';

        $sql_data_array = array(array('fieldName'=>'entry_firstname', 'value'=>$customers_firstname, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'entry_lastname', 'value'=>$customers_lastname, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'entry_street_address', 'value'=>$entry_street_address, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'entry_postcode', 'value'=>$entry_postcode, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'entry_city', 'value'=>$entry_city, 'type'=>'stringIgnoreNull'),
                                array('fieldName'=>'entry_country_id', 'value'=>$entry_country_id, 'type'=>'integer'),
        );

        if (ACCOUNT_COMPANY == 'true') $sql_data_array[] = array('fieldName'=>'entry_company', 'value'=>$entry_company, 'type'=>'stringIgnoreNull');
        if (ACCOUNT_SUBURB == 'true') $sql_data_array[] = array('fieldName'=>'entry_suburb', 'value'=>$entry_suburb, 'type'=>'stringIgnoreNull');

        if (ACCOUNT_STATE == 'true') {
          if ($entry_zone_id > 0) {
            $sql_data_array[] = array('fieldName'=>'entry_zone_id', 'value'=>$entry_zone_id, 'type'=>'integer');
            $sql_data_array[] = array('fieldName'=>'entry_state', 'value'=>'', 'type'=>'stringIgnoreNull');
          } else {
            $sql_data_array[] = array('fieldName'=>'entry_zone_id', 'value'=>0, 'type'=>'integer');
            $sql_data_array[] = array('fieldName'=>'entry_state', 'value'=>$entry_state, 'type'=>'stringIgnoreNull');
          }
        }

        $db->perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int)$customers_id . "' and address_book_id = '" . (int)$default_address_id . "'");
        zen_record_admin_activity('Customer record updated for customer ID ' . (int)$customers_id, 'notice');
        $zco_notifier->notify('ADMIN_CUSTOMER_UPDATE', (int)$customers_id, $default_address_id, $sql_data_array);
        zen_redirect(zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $customers_id));

        } else if ($error == true) {
          $cInfo = new objectInfo($_POST);
          $processed = true;
        }

        break;
      case 'pwdresetconfirm':
        if ((int)$customers_id > 0 && isset($_POST['newpassword']) && $_POST['newpassword'] != '' && isset($_POST['newpasswordConfirm']) && $_POST['newpasswordConfirm'] != '') {
          if (zen_admin_demo()) {
            $_GET['action']= '';
            $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
            zen_redirect(zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action'))));
          }
          $password_new = zen_db_prepare_input($_POST['newpassword']);
          $password_confirmation = zen_db_prepare_input($_POST['newpasswordConfirm']);
          $error = FALSE;
          if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
            $error = true;
            $messageStack->add_session(ERROR_PWD_TOO_SHORT . '(' . ENTRY_PASSWORD_MIN_LENGTH . ')', 'error');
          } elseif ($password_new != $password_confirmation) {
            $error = true;
            $messageStack->add_session(ERROR_PASSWORDS_NOT_MATCHING, 'error');
          }
          if ($error == FALSE) {
            $sql = "SELECT customers_email_address, customers_firstname, customers_lastname
                     FROM " . TABLE_CUSTOMERS . "
                     WHERE customers_id = :customersID";
            $sql = $db->bindVars($sql, ':customersID', $customers_id, 'integer');
            $custinfo = $db->Execute($sql);
            if ($custinfo->RecordCount() == 0) die('ERROR: customer ID not specified. This error should never happen.');

            $sql = "UPDATE " . TABLE_CUSTOMERS . "
                    SET customers_password = :password
                    WHERE customers_id = :customersID";
            $sql = $db->bindVars($sql, ':customersID', $customers_id, 'integer');
            $sql = $db->bindVars($sql, ':password',zen_encrypt_password($password_new), 'string');
            $db->Execute($sql);
            $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                    SET    customers_info_date_account_last_modified = now()
                    WHERE  customers_info_id = :customersID";
            $sql = $db->bindVars($sql, ':customersID',$customers_id, 'integer');
            $db->Execute($sql);

            $message = EMAIL_CUSTOMER_PWD_CHANGE_MESSAGE . "\n\n" . $password_new . "\n\n\n";
            $html_msg['EMAIL_MESSAGE_HTML'] = nl2br($message);
            zen_mail($custinfo->fields['customers_firstname'] . ' ' . $custinfo->fields['customers_lastname'], $custinfo->fields['customers_email_address'], EMAIL_CUSTOMER_PWD_CHANGE_SUBJECT , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'default');
            $userList = zen_get_users($_SESSION['admin_id']);
            $userDetails = $userList[0];
            $adminUser = $userDetails['id'] . '-' . $userDetails['name'] . ' ' . zen_get_ip_address();
            $message = sprintf(EMAIL_CUSTOMER_PWD_CHANGE_MESSAGE_FOR_ADMIN, $custinfo->fields['customers_firstname'] . ' ' . $custinfo->fields['customers_lastname'] . ' ' . $custinfo->fields['customers_email_address'], $adminUser) . "\n";
            $html_msg['EMAIL_MESSAGE_HTML'] = nl2br($message);
            zen_mail($userDetails['name'], $userDetails['email'], EMAIL_CUSTOMER_PWD_CHANGE_SUBJECT , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'default');

            $messageStack->add_session(SUCCESS_PASSWORD_UPDATED, 'success');
          }
          zen_redirect(zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $customers_id));
        }
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action'))));
        }
        $customers_id = zen_db_prepare_input($_POST['cID']);

        if (isset($_POST['delete_reviews']) && ($_POST['delete_reviews'] == 'on')) {
          $reviews = $db->Execute("select reviews_id
                                   from " . TABLE_REVIEWS . "
                                   where customers_id = '" . (int)$customers_id . "'");
          foreach ($reviews as $review) {
            $db->Execute("delete from " . TABLE_REVIEWS_DESCRIPTION . "
                          where reviews_id = '" . (int)$review['reviews_id'] . "'");
          }

          $db->Execute("delete from " . TABLE_REVIEWS . "
                        where customers_id = '" . (int)$customers_id . "'");
        } else {
          $db->Execute("update " . TABLE_REVIEWS . "
                        set customers_id = null
                        where customers_id = '" . (int)$customers_id . "'");
        }

        $db->Execute("delete from " . TABLE_ADDRESS_BOOK . "
                      where customers_id = '" . (int)$customers_id . "'");

        $db->Execute("delete from " . TABLE_CUSTOMERS . "
                      where customers_id = '" . (int)$customers_id . "'");

        $db->Execute("delete from " . TABLE_CUSTOMERS_INFO . "
                      where customers_info_id = '" . (int)$customers_id . "'");

        $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET . "
                      where customers_id = '" . (int)$customers_id . "'");

        $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                      where customers_id = '" . (int)$customers_id . "'");

        $db->Execute("delete from " . TABLE_WHOS_ONLINE . "
                      where customer_id = '" . (int)$customers_id . "'");

        zen_record_admin_activity('Customer with customer ID ' . (int)$customers_id . ' deleted.', 'warning');

        $zco_notifier->notify('ADMIN_CUSTOMER_DELETED', (int)$customers_id);
        zen_redirect(zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action'))));
        break;
      default:
        $customers = $db->Execute("select c.customers_id, c.customers_gender, c.customers_firstname,
                                          c.customers_lastname, c.customers_dob, c.customers_email_address,
                                          a.entry_company, a.entry_street_address, a.entry_suburb,
                                          a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id,
                                          a.entry_country_id, c.customers_telephone, c.customers_fax,
                                          c.customers_newsletter, c.customers_default_address_id,
                                          c.customers_email_format, c.customers_group_pricing,
          c.is_guest_account, c.customers_authorization, c.customers_referral
                                  from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a
                                  on c.customers_default_address_id = a.address_book_id
                                  where a.customers_id = c.customers_id
                                  and c.customers_id = '" . (int)$customers_id . "'");

        $cInfo = new objectInfo($customers->fields);
    }
  }
require('includes/admin_html_head.php');

?>
<?php
  if ($action == 'edit' || $action == 'update') {
// @TODO - convert to jQuery Validator
?>
<script type="text/javascript">

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var customers_firstname = document.customers.customers_firstname.value;
  var customers_lastname = document.customers.customers_lastname.value;
<?php if (ACCOUNT_COMPANY == 'true') echo 'var entry_company = document.customers.entry_company.value;' . "\n"; ?>
<?php if (ACCOUNT_DOB == 'true') echo 'var customers_dob = document.customers.customers_dob.value;' . "\n"; ?>
  var customers_email_address = document.customers.customers_email_address.value;
  var entry_street_address = document.customers.entry_street_address.value;
  var entry_postcode = document.customers.entry_postcode.value;
  var entry_city = document.customers.entry_city.value;
  var customers_telephone = document.customers.customers_telephone.value;

<?php if (ACCOUNT_GENDER == 'true') { ?>
  if (document.customers.customers_gender[0].checked || document.customers.customers_gender[1].checked) {
  } else {
    error_message = error_message + "<?php echo JS_GENDER; ?>";
    error = 1;
  }
<?php } ?>

  if (customers_firstname == "" || customers_firstname.length < <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_FIRST_NAME; ?>";
    error = 1;
  }

  if (customers_lastname == "" || customers_lastname.length < <?php echo ENTRY_LAST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_LAST_NAME; ?>";
    error = 1;
  }

<?php if (ACCOUNT_DOB == 'true' && ENTRY_DOB_MIN_LENGTH !='') { ?>
  if (customers_dob == "" || customers_dob.length < <?php echo ENTRY_DOB_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_DOB; ?>";
    error = 1;
  }
<?php } ?>

  if (customers_email_address == "" || customers_email_address.length < <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_EMAIL_ADDRESS; ?>";
    error = 1;
  }

  if (entry_street_address == "" || entry_street_address.length < <?php echo ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_ADDRESS; ?>";
    error = 1;
  }

  if (entry_postcode == "" || entry_postcode.length < <?php echo ENTRY_POSTCODE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_POST_CODE; ?>";
    error = 1;
  }

  if (entry_city == "" || entry_city.length < <?php echo ENTRY_CITY_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_CITY; ?>";
    error = 1;
  }

<?php
  if (ACCOUNT_STATE == 'true') {
?>
  if (document.customers.elements['entry_state'].type != "hidden") {
    if (document.customers.entry_state.value == '' || document.customers.entry_state.value.length < <?php echo ENTRY_STATE_MIN_LENGTH; ?> ) {
       error_message = error_message + "<?php echo JS_STATE; ?>";
       error = 1;
    }
  }
<?php
  }
?>

  if (document.customers.elements['entry_country_id'].type != "hidden") {
    if (document.customers.entry_country_id.value == 0) {
      error_message = error_message + "<?php echo JS_COUNTRY; ?>";
      error = 1;
    }
  }

  minTelephoneLength = <?php echo (int)ENTRY_TELEPHONE_MIN_LENGTH; ?>;
  if (minTelephoneLength > 0 && customers_telephone.length < minTelephoneLength) {
    error_message = error_message + "<?php echo JS_TELEPHONE; ?>";
    error = 1;
  }

  if (error == 1) {
    alert(error_message);
    return false;
  } else {
    return true;
  }
}
</script>
<?php
  }
?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ($action == 'edit' || $action == 'update') {
    $newsletter_array = array(array('id' => '1', 'text' => ENTRY_NEWSLETTER_YES),
                              array('id' => '0', 'text' => ENTRY_NEWSLETTER_NO));
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
?>
      <tr>
        <td class="formAreaTitle"><?php echo ACCOUNT_TYPE_SECTION_HEADING; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php if ($cInfo->is_guest_account) echo GUEST_STATUS_TRUE; else echo GUEST_STATUS_FALSE; ?></td>
          </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
?>
      <tr><?php echo zen_draw_form('customers', FILENAME_CUSTOMERS, zen_get_all_get_params(array('action')) . 'action=update', 'post', 'onsubmit="return check_form(customers);"', true) . zen_draw_hidden_field('default_address_id', $cInfo->customers_default_address_id);
           echo zen_hide_session_id(); ?>
        <td class="formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
<?php
    if (ACCOUNT_GENDER == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_GENDER; ?></td>
            <td class="main">
<?php
    if ($error == true && $entry_gender_error == true) {
      echo zen_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . zen_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . ENTRY_GENDER_ERROR;
    } else {
      echo zen_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . zen_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender) . '&nbsp;&nbsp;' . FEMALE;
    }
?></td>
          </tr>
<?php
    }
?>

<?php
  $customers_authorization_array = array(array('id' => '0', 'text' => CUSTOMERS_AUTHORIZATION_0),
                                array('id' => '1', 'text' => CUSTOMERS_AUTHORIZATION_1),
                                array('id' => '2', 'text' => CUSTOMERS_AUTHORIZATION_2),
                                array('id' => '3', 'text' => CUSTOMERS_AUTHORIZATION_3),
                                array('id' => '4', 'text' => CUSTOMERS_AUTHORIZATION_4), // banned
                                );
?>
          <tr>
            <td class="main"><?php echo CUSTOMERS_AUTHORIZATION; ?></td>
            <td class="main">
              <?php echo zen_draw_pull_down_menu('customers_authorization', $customers_authorization_array, $cInfo->customers_authorization); ?>
            </td>
          </tr>

          <tr>
            <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_firstname_error == true) {
      echo zen_draw_input_field('customers_firstname', htmlspecialchars($cInfo->customers_firstname, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_firstname', 50)) . '&nbsp;' . ENTRY_FIRST_NAME_ERROR;
    } else {
      echo $cInfo->customers_firstname . zen_draw_hidden_field('customers_firstname');
    }
  } else {
    echo zen_draw_input_field('customers_firstname', htmlspecialchars($cInfo->customers_firstname, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_firstname', 50), true);
  }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_lastname_error == true) {
      echo zen_draw_input_field('customers_lastname', htmlspecialchars($cInfo->customers_lastname, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_lastname', 50)) . '&nbsp;' . ENTRY_LAST_NAME_ERROR;
    } else {
      echo $cInfo->customers_lastname . zen_draw_hidden_field('customers_lastname');
    }
  } else {
    echo zen_draw_input_field('customers_lastname', htmlspecialchars($cInfo->customers_lastname, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_lastname', 50), true);
  }
?></td>
          </tr>
<?php
    if (ACCOUNT_DOB == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
            <td class="main">

<?php
    if ($error == true) {
      if ($entry_date_of_birth_error == true) {
        echo zen_draw_input_field('customers_dob', ($cInfo->customers_dob == '0001-01-01 00:00:00' ? '' : zen_date_short($cInfo->customers_dob)), 'maxlength="10"') . '&nbsp;' . ENTRY_DATE_OF_BIRTH_ERROR;
      } else {
        echo $cInfo->customers_dob . ($customers_dob == '0001-01-01 00:00:00' ? 'N/A' : zen_draw_hidden_field('customers_dob'));
      }
    } else {
      echo zen_draw_input_field('customers_dob', ($customers_dob == '0001-01-01 00:00:00' ? '' : zen_date_short($cInfo->customers_dob)), 'maxlength="10"', true);
    }
?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_email_address_error == true) {
      echo zen_draw_input_field('customers_email_address', htmlspecialchars($cInfo->customers_email_address, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_email_address', 50)) . '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR;
    } elseif ($entry_email_address_check_error == true) {
      echo zen_draw_input_field('customers_email_address', htmlspecialchars($cInfo->customers_email_address, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_email_address', 50)) . '&nbsp;' . ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
    } elseif ($entry_email_address_exists == true) {
      echo zen_draw_input_field('customers_email_address', htmlspecialchars($cInfo->customers_email_address, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_email_address', 50)) . '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
    } else {
      echo $customers_email_address . zen_draw_hidden_field('customers_email_address');
    }
  } else {
    echo zen_draw_input_field('customers_email_address', htmlspecialchars($cInfo->customers_email_address, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_email_address', 50), true);
  }
?></td>
          </tr>
        </table></td>
      </tr>
<?php
    if (ACCOUNT_COMPANY == 'true') {
?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_COMPANY; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_COMPANY; ?></td>
            <td class="main">
<?php
    if ($error == true) {
      if ($entry_company_error == true) {
        echo zen_draw_input_field('entry_company', htmlspecialchars($cInfo->entry_company, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_company', 50)) . '&nbsp;' . ENTRY_COMPANY_ERROR;
      } else {
        echo $cInfo->entry_company . zen_draw_hidden_field('entry_company');
      }
    } else {
      echo zen_draw_input_field('entry_company', htmlspecialchars($cInfo->entry_company, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_company', 50));
    }
?></td>
          </tr>
        </table></td>
      </tr>
<?php
    }
?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_STREET_ADDRESS; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_street_address_error == true) {
      echo zen_draw_input_field('entry_street_address', htmlspecialchars($cInfo->entry_street_address, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_street_address', 50)) . '&nbsp;' . ENTRY_STREET_ADDRESS_ERROR;
    } else {
      echo $cInfo->entry_street_address . zen_draw_hidden_field('entry_street_address');
    }
  } else {
    echo zen_draw_input_field('entry_street_address', htmlspecialchars($cInfo->entry_street_address, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_street_address', 50), true);
  }
?></td>
          </tr>
<?php
    if (ACCOUNT_SUBURB == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_SUBURB; ?></td>
            <td class="main">
<?php
    if ($error == true) {
      if ($entry_suburb_error == true) {
        echo zen_draw_input_field('suburb', htmlspecialchars($cInfo->entry_suburb, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_suburb', 50)) . '&nbsp;' . ENTRY_SUBURB_ERROR;
      } else {
        echo $cInfo->entry_suburb . zen_draw_hidden_field('entry_suburb');
      }
    } else {
      echo zen_draw_input_field('entry_suburb', htmlspecialchars($cInfo->entry_suburb, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_suburb', 50));
    }
?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_POST_CODE; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_post_code_error == true) {
      echo zen_draw_input_field('entry_postcode', htmlspecialchars($cInfo->entry_postcode, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_postcode', 10)) . '&nbsp;' . ENTRY_POST_CODE_ERROR;
    } else {
      echo $cInfo->entry_postcode . zen_draw_hidden_field('entry_postcode');
    }
  } else {
    echo zen_draw_input_field('entry_postcode', htmlspecialchars($cInfo->entry_postcode, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_postcode', 10), true);
  }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CITY; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_city_error == true) {
      echo zen_draw_input_field('entry_city', htmlspecialchars($cInfo->entry_city, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_city', 50)) . '&nbsp;' . ENTRY_CITY_ERROR;
    } else {
      echo $cInfo->entry_city . zen_draw_hidden_field('entry_city');
    }
  } else {
    echo zen_draw_input_field('entry_city', htmlspecialchars($cInfo->entry_city, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_city', 50), true);
  }
?></td>
          </tr>
<?php
    if (ACCOUNT_STATE == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_STATE; ?></td>
            <td class="main">
<?php
    $entry_state = zen_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state);
    if ($error == true) {
      if ($entry_state_error == true) {
        if ($entry_state_has_zones == true) {
          $zones_array = array();
          $zones_values = $db->Execute("select zone_name
                                        from " . TABLE_ZONES . "
                                        where zone_country_id = '" . zen_db_input($cInfo->entry_country_id) . "'
                                        order by zone_name");

          foreach ($zones_values as $zone) {
            $zones_array[] = array('id' => $zone['zone_name'], 'text' => $zone['zone_name']);
          }
          echo zen_draw_pull_down_menu('entry_state', $zones_array) . '&nbsp;' . ENTRY_STATE_ERROR;
        } else {
          echo zen_draw_input_field('entry_state', htmlspecialchars(zen_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state), ENT_COMPAT, CHARSET, TRUE)) . '&nbsp;' . ENTRY_STATE_ERROR;
        }
      } else {
        echo $entry_state . zen_draw_hidden_field('entry_zone_id') . zen_draw_hidden_field('entry_state');
      }
    } else {
      echo zen_draw_input_field('entry_state', htmlspecialchars(zen_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state), ENT_COMPAT, CHARSET, TRUE));
    }

?></td>
         </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_COUNTRY; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_country_error == true) {
      echo zen_draw_pull_down_menu('entry_country_id', zen_get_countries_for_pulldown(), $cInfo->entry_country_id) . '&nbsp;' . ENTRY_COUNTRY_ERROR;
    } else {
      echo zen_get_country_name($cInfo->entry_country_id, $_SESSION['languages_id']) . zen_draw_hidden_field('entry_country_id');
    }
  } else {
    echo zen_draw_pull_down_menu('entry_country_id', zen_get_countries_for_pulldown(), $cInfo->entry_country_id);
  }
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_CONTACT; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_telephone_error == true) {
      echo zen_draw_input_field('customers_telephone', htmlspecialchars($cInfo->customers_telephone, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_telephone', 15)) . '&nbsp;' . ENTRY_TELEPHONE_NUMBER_ERROR;
    } else {
      echo $cInfo->customers_telephone . zen_draw_hidden_field('customers_telephone');
    }
  } else {
    echo zen_draw_input_field('customers_telephone', htmlspecialchars($cInfo->customers_telephone, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_telephone', 15), true);
  }
?></td>
          </tr>
<?php
  if (ACCOUNT_FAX_NUMBER == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
            <td class="main">
<?php
  if ($processed == true) {
    echo $cInfo->customers_fax . zen_draw_hidden_field('customers_fax');
  } else {
    echo zen_draw_input_field('customers_fax', htmlspecialchars($cInfo->customers_fax, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_fax', 15));
  }
?></td>
          </tr>
<?php } ?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_OPTIONS; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">

      <tr>
        <td class="main"><?php echo ENTRY_EMAIL_PREFERENCE; ?></td>
        <td class="main">
<?php
if ($processed == true) {
  if ($cInfo->customers_email_format) {
    echo $customers_email_format . zen_draw_hidden_field('customers_email_format');
  }
} else {
    $email_pref_text = ($cInfo->customers_email_format == 'TEXT') ? true : false;
  $email_pref_html = !$email_pref_text;
  echo zen_draw_radio_field('customers_email_format', 'HTML', $email_pref_html) . '&nbsp;' . ENTRY_EMAIL_HTML_DISPLAY . '&nbsp;&nbsp;&nbsp;' . zen_draw_radio_field('customers_email_format', 'TEXT', $email_pref_text) . '&nbsp;' . ENTRY_EMAIL_TEXT_DISPLAY ;
}
?></td>
      </tr>
          <tr>
            <td class="main"><?php echo ENTRY_NEWSLETTER; ?></td>
            <td class="main">
<?php
  if ($processed == true) {
    if ($cInfo->customers_newsletter == '1') {
      echo ENTRY_NEWSLETTER_YES;
    } else {
      echo ENTRY_NEWSLETTER_NO;
    }
    echo zen_draw_hidden_field('customers_newsletter');
  } else {
    echo zen_draw_pull_down_menu('customers_newsletter', $newsletter_array, (($cInfo->customers_newsletter == '1') ? '1' : '0'));
  }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_PRICING_GROUP; ?></td>
            <td class="main">
<?php
  if ($processed == true) {
    if ($cInfo->customers_group_pricing) {
      $group_query = $db->Execute("select group_name, group_percentage from " . TABLE_GROUP_PRICING . " where group_id = '" . (int)$cInfo->customers_group_pricing . "'");
      echo $group_query->fields['group_name'].'&nbsp;'.$group_query->fields['group_percentage'].'%';
    } else {
      echo ENTRY_NONE;
    }
    echo zen_draw_hidden_field('customers_group_pricing', $cInfo->customers_group_pricing);
  } else {
    $group_array_query = $db->execute("select group_id, group_name, group_percentage from " . TABLE_GROUP_PRICING);
    $group_array[] = array('id'=>0, 'text'=>TEXT_NONE);
    foreach ($group_array_query as $group) { 
      $group_array[] = array('id'=>$group['group_id'], 'text'=>$group['group_name'].'&nbsp;'.$group['group_percentage'].'%');
    }
    echo zen_draw_pull_down_menu('customers_group_pricing', $group_array, $cInfo->customers_group_pricing);
  }
?></td>
          </tr>

          <tr>
            <td class="main"><?php echo CUSTOMERS_REFERRAL; ?></td>
            <td class="main">
              <?php echo zen_draw_input_field('customers_referral', htmlspecialchars($cInfo->customers_referral, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMERS, 'customers_referral', 15)); ?>
            </td>
          </tr>
        </table></td>
      </tr>

      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td align="right" class="main"><?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('action'))) .'">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr></form>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo zen_draw_form('search', FILENAME_CUSTOMERS, '', 'get', '', true); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right">
<?php
// show reset search
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      echo '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
    }
    echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search') . zen_hide_session_id();
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      $keywords = zen_db_prepare_input($_GET['search']);
      echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . zen_output_string_protected($keywords);
    }
?>
            </td>
          </form></tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
<?php
// Sort Listing
          switch ($_GET['list_order']) {
              case "id-asc":
              $disp_order = "ci.customers_info_date_account_created";
              break;
              case "firstname":
              $disp_order = "c.customers_firstname";
              break;
              case "firstname-desc":
              $disp_order = "c.customers_firstname DESC";
              break;
              case "group-asc":
              $disp_order = "c.customers_group_pricing";
              break;
              case "group-desc":
              $disp_order = "c.customers_group_pricing DESC";
              break;
              case "lastname":
              $disp_order = "c.customers_lastname, c.customers_firstname";
              break;
              case "lastname-desc":
              $disp_order = "c.customers_lastname DESC, c.customers_firstname";
              break;
              case "company":
              $disp_order = "a.entry_company";
              break;
              case "company-desc":
              $disp_order = "a.entry_company DESC";
              break;
              case "login-asc":
              $disp_order = "ci.customers_info_date_of_last_logon";
              break;
              case "login-desc":
              $disp_order = "ci.customers_info_date_of_last_logon DESC";
              break;
              case "approval-asc":
              $disp_order = "c.customers_authorization";
              break;
              case "approval-desc":
              $disp_order = "c.customers_authorization DESC";
              break;
              case "gv_balance-asc":
              $disp_order = "cgc.amount, c.customers_lastname, c.customers_firstname";
              break;
              case "gv_balance-desc":
              $disp_order = "cgc.amount DESC, c.customers_lastname, c.customers_firstname";
              break;
              case "account-type-asc":
              $disp_order = "c.is_guest_account";
              break;
              case "account-type-desc":
              $disp_order = "c.is_guest_account DESC";
              break;
              default:
              $disp_order = "ci.customers_info_date_account_created DESC";
          }
?>
             <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="center" valign="top">
                  <?php echo TABLE_HEADING_ID; ?>
                </td>
                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo (($_GET['list_order']=='lastname' or $_GET['list_order']=='lastname-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_LASTNAME . '</span>' : TABLE_HEADING_LASTNAME); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=lastname'); ?>"><?php echo ($_GET['list_order']=='lastname' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=lastname-desc'); ?>"><?php echo ($_GET['list_order']=='lastname-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>
                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo (($_GET['list_order']=='firstname' or $_GET['list_order']=='firstname-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_FIRSTNAME . '</span>' : TABLE_HEADING_FIRSTNAME); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=firstname'); ?>"><?php echo ($_GET['list_order']=='firstname' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=firstname-desc'); ?>"><?php echo ($_GET['list_order']=='firstname-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>
                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo (($_GET['list_order']=='company' or $_GET['list_order']=='company-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_COMPANY . '</span>' : TABLE_HEADING_COMPANY); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=company'); ?>"><?php echo ($_GET['list_order']=='company' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=company-desc'); ?>"><?php echo ($_GET['list_order']=='company-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>
                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo (($_GET['list_order']=='id-asc' or $_GET['list_order']=='id-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_ACCOUNT_CREATED . '</span>' : TABLE_HEADING_ACCOUNT_CREATED); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=id-asc'); ?>"><?php echo ($_GET['list_order']=='id-asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=id-desc'); ?>"><?php echo ($_GET['list_order']=='id-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>

                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo (($_GET['list_order']=='login-asc' or $_GET['list_order']=='login-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_LOGIN . '</span>' : TABLE_HEADING_LOGIN); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=login-asc'); ?>"><?php echo ($_GET['list_order']=='login-asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=login-desc'); ?>"><?php echo ($_GET['list_order']=='login-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>

                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo (($_GET['list_order']=='group-asc' or $_GET['list_order']=='group-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_PRICING_GROUP . '</span>' : TABLE_HEADING_PRICING_GROUP); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=group-asc'); ?>"><?php echo ($_GET['list_order']=='group-asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=group-desc'); ?>"><?php echo ($_GET['list_order']=='group-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>

<?php if (MODULE_ORDER_TOTAL_GV_STATUS == 'true') { ?>
                <td class="dataTableHeadingContent" align="left" valign="top" width="75">
                  <?php echo (($_GET['list_order']=='gv_balance-asc' or $_GET['list_order']=='gv_balance-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_GV_AMOUNT . '</span>' : TABLE_HEADING_GV_AMOUNT); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=gv_balance-asc'); ?>"><?php echo ($_GET['list_order']=='gv_balance-asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=gv_balance-desc'); ?>"><?php echo ($_GET['list_order']=='gv_balance-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>
<?php } ?>

                <td class="dataTableHeadingContent" align="center" valign="top">
                  <?php echo (($_GET['list_order']=='approval-asc' or $_GET['list_order']=='approval-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_AUTHORIZATION_APPROVAL . '</span>' : TABLE_HEADING_AUTHORIZATION_APPROVAL); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=approval-asc'); ?>"><?php echo ($_GET['list_order']=='approval-asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=approval-desc'); ?>"><?php echo ($_GET['list_order']=='approval-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>

                <td class="dataTableHeadingContent" align="center">
                  <?php echo (($_GET['list_order']=='account-type-asc' or $_GET['list_order']=='account-type-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_ACCOUNT_TYPE . '</span>' : TABLE_HEADING_ACCOUNT_TYPE); ?><br>
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=account-type-asc'); ?>"><?php echo ($_GET['list_order']=='account-type-asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>&nbsp;
                  <a href="<?php echo zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('list_order','page')) . 'list_order=account-type-desc'); ?>"><?php echo ($_GET['list_order']=='account-type-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>
                <td class="dataTableHeadingContent" align="right" valign="top"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $search = '';
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
      $search = "where c.customers_lastname like '%" . $keywords . "%' or c.customers_firstname like '%" . $keywords . "%' or c.customers_email_address like '%" . $keywords . "%' or c.customers_telephone rlike ':keywords:' or a.entry_company rlike ':keywords:' or a.entry_street_address rlike ':keywords:' or a.entry_city rlike ':keywords:' or a.entry_postcode rlike ':keywords:'";
      $search = $db->bindVars($search, ':keywords:', $keywords, 'regexp');
    }
    $new_fields=', c.customers_telephone, a.entry_company, a.entry_street_address, a.entry_city, a.entry_postcode, c.customers_authorization, c.customers_referral, c.is_guest_account';
    $customers_query_raw = "select c.customers_id, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_group_pricing, a.entry_country_id, a.entry_company, ci.customers_info_date_of_last_logon, ci.customers_info_date_account_created " . $new_fields . ",
    cgc.amount
    from " . TABLE_CUSTOMERS . " c
    left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id= ci.customers_info_id
    left join " . TABLE_ADDRESS_BOOK . " a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id " . "
    left join " . TABLE_COUPON_GV_CUSTOMER . " cgc on c.customers_id = cgc.customer_id " .
    $search . " order by $disp_order";

// Split Page
// reset page when page is unknown
if (($_GET['page'] == '' or $_GET['page'] == '1') and $_GET['cID'] != '') {
  $check_pages = $db->Execute($customers_query_raw);
  $check_count=1;
  if ($check_pages->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER) {
    foreach ($check_pages as $check_page) {
      if ($check_page['customers_id'] == $_GET['cID']) {
        break;
      }
      $check_count++;
    }
    $_GET['page'] = round((($check_count/MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER)+(fmod_round($check_count,MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER) !=0 ? .5 : 0)),0);
//    zen_redirect(zen_admin_href_link(FILENAME_CUSTOMERS, 'cID=' . $_GET['cID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
  } else {
    $_GET['page'] = 1;
  }
}

    $customers_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, $customers_query_raw, $customers_query_numrows);
    $customers = $db->Execute($customers_query_raw);
    foreach ($customers as $customer) {
      $sql = "select customers_info_date_account_created as date_account_created,
                                   customers_info_date_account_last_modified as date_account_last_modified,
                                   customers_info_date_of_last_logon as date_last_logon,
                                   customers_info_number_of_logons as number_of_logons
                            from " . TABLE_CUSTOMERS_INFO . "
                            where customers_info_id = '" . $customer['customers_id'] . "'";
      $info = $db->Execute($sql);

      // if no record found, create one to keep database in sync
      if ($info->RecordCount() == 0) {
        $insert_sql = "insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id)
                       values ('" . (int)$customer['customers_id'] . "')";
        $db->Execute($insert_sql);
        $info = $db->Execute($sql);
      }

      if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $customer['customers_id']))) && !isset($cInfo)) {
        $country = $db->Execute("select countries_name
                                 from " . TABLE_COUNTRIES_NAME . "
                                 where countries_id = '" . (int)$customer['entry_country_id'] . "'
                                 and language_id = " . $_SESSION['languages_id']);

        $reviews = $db->Execute("select count(*) as number_of_reviews
                                 from " . TABLE_REVIEWS . " where customers_id = '" . (int)$customer['customers_id'] . "'");

        $customer_info = array_merge($country->fields, $info->fields, $reviews->fields);

        $cInfo_array = array_merge($customer, $customer_info);
        $cInfo = new objectInfo($cInfo_array);
      }

        $group_query = $db->Execute("select group_name, group_percentage from " . TABLE_GROUP_PRICING . " where
                                     group_id = '" . $customer['customers_group_pricing'] . "'");

        if ($group_query->RecordCount() < 1) {
          $group_name_entry = TEXT_NONE;
        } else {
          $group_name_entry = $group_query->fields['group_name'];
        }

      if (isset($cInfo) && is_object($cInfo) && ($customer['customers_id'] == $cInfo->customers_id)) {
        echo '          <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $customer['customers_id']) . '\'">' . "\n";
      }

      $zc_address_book_count_list = zen_get_customers_address_book($customer['customers_id']);
      $zc_address_book_count = $zc_address_book_count_list->RecordCount();
?>
                <td class="dataTableContent" align="right"><?php echo $customer['customers_id'] . ($zc_address_book_count == 1 ? TEXT_INFO_ADDRESS_BOOK_COUNT . $zc_address_book_count : '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, 'action=list_addresses' . '&cID=' . $customer['customers_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . TEXT_INFO_ADDRESS_BOOK_COUNT . $zc_address_book_count . '</a>'); ?></td>
                <td class="dataTableContent"><?php echo $customer['customers_lastname']; ?></td>
                <td class="dataTableContent"><?php echo $customer['customers_firstname']; ?></td>
                <td class="dataTableContent"><?php echo $customer['entry_company']; ?></td>
                <td class="dataTableContent"><?php echo zen_date_short($info->fields['date_account_created']); ?></td>
                <td class="dataTableContent"><?php echo zen_date_short($customer['customers_info_date_of_last_logon']); ?></td>
                <td class="dataTableContent"><?php echo $group_name_entry; ?></td>
<?php if (MODULE_ORDER_TOTAL_GV_STATUS == 'true') { ?>
                <td class="dataTableContent" align="right"><?php echo $currencies->format($customer['amount']); ?></td>
<?php } ?>
                <td class="dataTableContent" align="center">
                <?php if ($customer['customers_authorization'] == 4) { ?>
                <?php echo zen_image(DIR_WS_IMAGES . 'icon_red_off.gif', IMAGE_ICON_STATUS_OFF); ?>
                <?php } else {
                    echo zen_draw_form('setstatus', FILENAME_CUSTOMERS, 'action=status&cID=' . $customer['customers_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));?>
                  <?php if ($customer['customers_authorization'] == 0) { ?>
                    <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_green_on.gif" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" />
                  <?php } else { ?>
                    <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_red_on.gif" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" />
                  <?php } ?>
                    <input type="hidden" name="current" value="<?php echo $customer['customers_authorization']; ?>" />
                    </form>
                <?php } ?>
                </td>
<?php if ($customer['is_guest_account'] == 1) { ?>
                <td class="dataTableContent" align="center"><?php echo TEXT_GUEST; ?>&nbsp;</td>
<?php }else{ ?>
                <td class="dataTableContent" align="center"><?php echo TEXT_STANDARD;} ?>&nbsp;</td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($customer['customers_id'] == $cInfo->customers_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID')) . 'cID=' . $customer['customers_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $customers_split->display_count($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
                    <td class="smallText" align="right"><?php echo $customers_split->display_links($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></td>
                  </tr>
<?php
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
?>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
                  </tr>
<?php
    }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'confirm':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</b>');

      $contents = array('form' => zen_draw_form('customers', FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'search')) . 'action=deleteconfirm', 'post', '', true) . zen_draw_hidden_field('cID', $cInfo->customers_id));
      $contents[] = array('text' => TEXT_DELETE_INTRO . '<br><br><b>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</b>');
      if (isset($cInfo->number_of_reviews) && ($cInfo->number_of_reviews) > 0) $contents[] = array('text' => '<br />' . zen_draw_checkbox_field('delete_reviews', 'on', true) . ' ' . sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews));
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'pwreset':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_RESET_CUSTOMER_PASSWORD . '</b>');
      $contents = array('form' => zen_draw_form('customers', FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'search')) . 'action=pwdresetconfirm', 'post', 'id="pReset"', true) . zen_draw_hidden_field('cID', $cInfo->customers_id));
      $contents[] = array('text' => TEXT_PWDRESET_INTRO . '<br><br><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
      $contents[] = array('text' => '<br>' . TEXT_CUST_NEW_PASSWORD . '<br>' . zen_draw_input_field('newpassword', '', 'maxlength="40" autofocus="autofocus" autocomplete="off" id="newpassword"', false, 'text', false));
      $contents[] = array('text' => '<br>' . TEXT_CUST_CONFIRM_PASSWORD . '<br>' . zen_draw_input_field('newpasswordConfirm', '', 'maxlength="40" autocomplete="off" id="newpasswordConfirm"', false, 'text', false));
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_reset_pwd.gif', IMAGE_RESET_PWD) . ' <a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      $contents[] = array('text' => '<script>
$().ready(function() {
  $("#pReset").validate({
    submitHandler: function(form) {
      form.submit();
    },
    rules: {
      newpassword: {
          required: true,
          minlength: ' . ENTRY_PASSWORD_MIN_LENGTH . ' },
      newpasswordConfirm: {
          required: true,
          equalTo: "#newpassword"
      }
    },
    messages: {
    }
  });
});
</script>');
      break;
    default:
      if (isset($_GET['search'])) $_GET['search'] = zen_output_string_protected($_GET['search']);
      if (isset($cInfo) && is_object($cInfo)) {
        $customers_orders = $db->Execute("select o.orders_id, o.date_purchased, o.order_total, o.currency, o.currency_value,
                                          cgc.amount
                                          from " . TABLE_ORDERS . " o
                                          left join " . TABLE_COUPON_GV_CUSTOMER . " cgc on o.customers_id = cgc.customer_id
                                          where customers_id='" . $cInfo->customers_id . "' order by date_purchased desc");

        $heading[] = array('text' => '<b>' . TABLE_HEADING_ID . $cInfo->customers_id . ' ' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</b>' . '<br>' . $cInfo->customers_email_address);

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'search')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'search')) . 'cID=' . $cInfo->customers_id . '&action=confirm') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a><br />' . ($customers_orders->RecordCount() != 0 ? '<a href="' . zen_admin_href_link(FILENAME_ORDERS, 'cID=' . $cInfo->customers_id) . '">' . zen_image_button('button_orders.gif', IMAGE_ORDERS) . '</a>' : '') . ' <a href="' . zen_admin_href_link(FILENAME_MAIL, 'origin=customers.php&mode=NONSSL&customer=' . $cInfo->customers_email_address.'&cID=' . $cInfo->customers_id) . '">' . zen_image_button('button_email.gif', IMAGE_EMAIL) . '</a>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'search')) . 'cID=' . $cInfo->customers_id . '&action=pwreset') . '">' . zen_image_button('button_reset_pwd.gif', IMAGE_RESET_PWD) . '</a>');
        $zco_notifier->notify('NOTIFY_ADMIN_CUSTOMERS_MENU_BUTTONS', $cInfo, $contents);

        $contents[] = array('text' => '<br />' . TEXT_DATE_ACCOUNT_CREATED . ' ' . zen_date_short($cInfo->date_account_created));
        $contents[] = array('text' => '<br />' . TEXT_DATE_ACCOUNT_LAST_MODIFIED . ' ' . zen_date_short($cInfo->date_account_last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_LAST_LOGON . ' '  . zen_date_short($cInfo->date_last_logon));
        $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_OF_LOGONS . ' ' . $cInfo->number_of_logons);

        $customer_gv_balance = zen_user_has_gv_account($cInfo->customers_id);
        $contents[] = array('text' => '<br />' . TEXT_INFO_GV_AMOUNT . ' ' . $currencies->format($customer_gv_balance));

        $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_OF_ORDERS . ' ' . $customers_orders->RecordCount());
        if ($customers_orders->RecordCount() != 0) {
          $contents[] = array('text' => TEXT_INFO_LAST_ORDER . ' ' . zen_date_short($customers_orders->fields['date_purchased']) . '<br />' . TEXT_INFO_ORDERS_TOTAL . ' ' . $currencies->format($customers_orders->fields['order_total'], true, $customers_orders->fields['currency'], $customers_orders->fields['currency_value']));
        }
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY . ' ' . $cInfo->countries_name);
        $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_OF_REVIEWS . ' ' . $cInfo->number_of_reviews);
        $contents[] = array('text' => '<br />' . CUSTOMERS_REFERRAL . ' ' . $cInfo->customers_referral);
      }
      break;
  }
  $zco_notifier->notify('NOTIFY_ADMIN_CUSTOMERS_MENU_BUTTONS_END', (isset($cInfo) ? $cInfo : new stdClass), $contents);

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
