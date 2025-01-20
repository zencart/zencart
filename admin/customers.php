<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 13 Modified in v2.1.0 $
 */
require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();
$group_array = [];

// Override instructions in:
// https://docs.zen-cart.com/user/admin/site_specific_overrides/
if (!isset($show_registration_ip_in_listing)) {
    $show_registration_ip_in_listing = false;
}
$action = $_GET['action'] ?? '';
$customers_id = (int)($_POST['cID'] ?? $_GET['cID'] ?? 0);

if (!isset($_GET['page'])) {
    $_GET['page'] = '';
}
if (!isset($_GET['list_order'])) {
    $_GET['list_order'] = '';
}

$error = false;
$processed = false;

if (!empty($action)) {
    switch ($action) {
        case 'list_addresses':
            $customer = new Customer($_GET['cID']);
            $addressArray = $customer->getData('addresses');
            break;
        case 'status':
            if (isset($_POST['current_status']) && ctype_digit($_POST['current_status'])) {
                if ($_POST['current_status'] === CUSTOMERS_APPROVAL_AUTHORIZATION) {
                    if (CUSTOMERS_APPROVAL_AUTHORIZATION === '1' || CUSTOMERS_APPROVAL_AUTHORIZATION === '2') {
                        $customers_authorization = 0;
                    } else {
                        $customers_authorization = 4;
                    }

                    $customer = new Customer($customers_id);
                    $old = $customer->getData('customers_authorization');
                    $custinfo = $customer->setCustomerAuthorizationStatus($customers_authorization);
                    if ((int)CUSTOMERS_APPROVAL_AUTHORIZATION > 0 && (int)$_POST['current_status'] > 0 && $old != $customers_authorization) {
                        $message = EMAIL_CUSTOMER_STATUS_CHANGE_MESSAGE;
                        $html_msg['EMAIL_MESSAGE_HTML'] = EMAIL_CUSTOMER_STATUS_CHANGE_MESSAGE;
                        zen_mail(
                            $custinfo['customers_firstname'] . ' ' . $custinfo['customers_lastname'],
                            $custinfo['customers_email_address'],
                            EMAIL_CUSTOMER_STATUS_CHANGE_SUBJECT,
                            $message,
                            STORE_NAME,
                            EMAIL_FROM,
                            $html_msg,
                            'default'
                        );
                    }
                    zen_record_admin_activity(
                        'Customer-approval-authorization set customer auth status to 0 for customer ID ' . $customers_id,
                        'info'
                    );
                } else {
                    $customer = new Customer($customers_id);
                    $customer->setCustomerAuthorizationStatus(CUSTOMERS_APPROVAL_AUTHORIZATION);
                    zen_record_admin_activity(
                        'Customer-approval-authorization set customer auth status to ' . CUSTOMERS_APPROVAL_AUTHORIZATION . ' for customer ID ' . (int)$customers_id,
                        'info'
                    );
                }
                zen_redirect(
                    zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(['action']), 'NONSSL')
                );
            }
            $action = '';
            break;
        case 'update':
            if ($customers_id === 0) {
                zen_redirect(zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(['cID', 'action'])));
            }

            $customers_firstname = zen_db_prepare_input(zen_sanitize_string($_POST['customers_firstname']));
            $customers_lastname = zen_db_prepare_input(zen_sanitize_string($_POST['customers_lastname']));
            $customers_email_address = zen_db_prepare_input($_POST['customers_email_address']);
            $customers_telephone = zen_db_prepare_input($_POST['customers_telephone']);
            $customers_fax = zen_db_prepare_input($_POST['customers_fax'] ?? '');
            $customers_newsletter = zen_db_prepare_input($_POST['customers_newsletter']);
            $customers_group_pricing = (int)($_POST['customers_group_pricing'] ?? 0); //- Not present if wholesale pricing is selected!
            $customers_email_format = zen_db_prepare_input($_POST['customers_email_format']);
            $customers_gender = zen_db_prepare_input($_POST['customers_gender'] ?? '');
            $customers_dob = zen_db_prepare_input($_POST['customers_dob'] ?? '0001-01-01 00:00:00');

            $customers_authorization = (int)$_POST['customers_authorization'];
            $customers_referral = zen_db_prepare_input($_POST['customers_referral']);
            $customers_whole = (int)($_POST['customers_whole'] ?? 0); //- Not present if Wholesale Pricing isn't enabled for the site or a group-pricing group's selected!

            if (CUSTOMERS_APPROVAL_AUTHORIZATION === '2' && $customers_authorization === 1) {
                $customers_authorization = 2;
                $messageStack->add_session(ERROR_CUSTOMER_APPROVAL_CORRECTION2, 'caution');
            }

            if (CUSTOMERS_APPROVAL_AUTHORIZATION === '1' && $customers_authorization === 2) {
                $customers_authorization = 1;
                $messageStack->add_session(ERROR_CUSTOMER_APPROVAL_CORRECTION1, 'caution');
            }

            $default_address_id = (int)$_POST['default_address_id'];
            $entry_street_address = zen_db_prepare_input($_POST['entry_street_address']);
            $entry_suburb = zen_db_prepare_input($_POST['entry_suburb'] ?? '');
            $entry_postcode = zen_db_prepare_input($_POST['entry_postcode']);
            $entry_city = zen_db_prepare_input($_POST['entry_city']);
            $entry_country_id = (int)$_POST['entry_country_id'];
            $entry_company = zen_db_prepare_input($_POST['entry_company'] ?? '');
            $entry_state = zen_db_prepare_input($_POST['entry_state'] ?? '');
            $entry_zone_id = (int)($_POST['entry_zone_id'] ?? 0);

            if (ACCOUNT_GENDER === 'true' && empty($customers_gender)) {
                $error = true;
                $entry_gender_error = true;
            } else {
                $entry_gender_error = false;
            }

            if (ACCOUNT_DOB === 'true') {
                if (checkdate(
                    (int)substr(zen_date_raw($customers_dob), 4, 2),
                    (int)substr(zen_date_raw($customers_dob), 6, 2),
                    (int)substr(zen_date_raw($customers_dob), 0, 4)
                )) {
                    $entry_date_of_birth_error = false;
                } else {
                    $error = true;
                    $entry_date_of_birth_error = true;
                }
            } else {
                $customers_dob = '0001-01-01 00:00:00';
            }

            $entry_email_address_check_error = false;
            if (!zen_validate_email($customers_email_address)) {
                $error = true;
                $entry_email_address_check_error = true;
            }

            $entry_email_address_exists = !zen_check_email_address_not_already_used(
                $customers_email_address,
                $customers_id
            );
            if ($entry_email_address_exists) {
                $error = true;
            }

            $entry_state_error = false;
            if (ACCOUNT_STATE === 'true') {
                $entry_state_has_zones = count(zen_get_country_zones((int)$entry_country_id)) > 0;
                if ($entry_state_has_zones) {
                    $zone_query = $db->Execute(
                        "SELECT zone_id
                           FROM " . TABLE_ZONES . "
                           WHERE zone_country_id = " . (int)$entry_country_id . "
                             AND zone_id = " . (int)$entry_zone_id . "
                           LIMIT 1"
                    );

                    if ($zone_query->EOF) {
                        $error = true;
                        $entry_state_error = true;
                    }
                }
            }

            $zco_notifier->notify('NOTIFY_ADMIN_CUSTOMERS_UPDATE_VALIDATE', [], $error);

            if ($error === false) {
                $sql_data_array = [
                    [
                        'fieldName' => 'customers_firstname',
                        'value' => $customers_firstname,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_lastname',
                        'value' => $customers_lastname,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_email_address',
                        'value' => $customers_email_address,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_telephone',
                        'value' => $customers_telephone,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_fax',
                        'value' => $customers_fax,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_group_pricing',
                        'value' => $customers_group_pricing,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_newsletter',
                        'value' => $customers_newsletter,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_email_format',
                        'value' => $customers_email_format,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_authorization',
                        'value' => $customers_authorization,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_referral',
                        'value' => $customers_referral,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'customers_whole',
                        'value' => $customers_whole,
                        'type' => 'stringIgnoreNull'
                    ],
                ];

                if (ACCOUNT_GENDER === 'true') {
                    $sql_data_array[] = [
                        'fieldName' => 'customers_gender',
                        'value' => $customers_gender,
                        'type' => 'stringIgnoreNull'
                    ];
                }
                if (ACCOUNT_DOB === 'true') {
                    $sql_data_array[] = [
                        'fieldName' => 'customers_dob',
                        'value' => ($customers_dob === '0001-01-01 00:00:00') ?
                            '0001-01-01 00:00:00' : zen_date_raw($customers_dob),
                        'type' => 'date'
                    ];
                }

                $zco_notifier->notify('NOTIFY_ADMIN_CUSTOMERS_CUSTOMER_UPDATE', $customers_id, $sql_data_array);
                $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = " . (int)$customers_id . " LIMIT 1");

                $db->Execute(
                    "UPDATE " . TABLE_CUSTOMERS_INFO . "
                      SET customers_info_date_account_last_modified = now()
                      WHERE customers_info_id = " . (int)$customers_id . "
                      LIMIT 1"
                );

                if ($entry_zone_id > 0) {
                    $entry_state = '';
                }

                $sql_data_array = [
                    [
                        'fieldName' => 'entry_firstname',
                        'value' => $customers_firstname,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'entry_lastname',
                        'value' => $customers_lastname,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'entry_street_address',
                        'value' => $entry_street_address,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'entry_postcode',
                        'value' => $entry_postcode,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'entry_city',
                        'value' => $entry_city,
                        'type' => 'stringIgnoreNull'
                    ],
                    [
                        'fieldName' => 'entry_country_id',
                        'value' => $entry_country_id,
                        'type' => 'integer'
                    ],
                ];

                if (ACCOUNT_COMPANY === 'true') {
                    $sql_data_array[] = [
                        'fieldName' => 'entry_company',
                        'value' => $entry_company,
                        'type' => 'stringIgnoreNull'
                    ];
                }
                if (ACCOUNT_SUBURB === 'true') {
                    $sql_data_array[] = [
                        'fieldName' => 'entry_suburb',
                        'value' => $entry_suburb,
                        'type' => 'stringIgnoreNull'
                    ];
                }

                if (ACCOUNT_STATE === 'true') {
                    if ($entry_zone_id > 0) {
                        $sql_data_array[] = [
                            'fieldName' => 'entry_zone_id',
                            'value' => $entry_zone_id,
                            'type' => 'integer'
                        ];
                        $sql_data_array[] = [
                            'fieldName' => 'entry_state',
                            'value' => '',
                            'type' => 'string'
                        ];
                    } else {
                        $sql_data_array[] = [
                            'fieldName' => 'entry_zone_id',
                            'value' => 0,
                            'type' => 'integer'
                        ];
                        $sql_data_array[] = [
                            'fieldName' => 'entry_state',
                            'value' => $entry_state,
                            'type' => 'string'
                        ];
                    }
                }

                $zco_notifier->notify(
                    'NOTIFY_ADMIN_CUSTOMERS_B4_ADDRESS_UPDATE',
                    ['customers_id' => $customers_id, 'address_book_id' => $default_address_id],
                    $sql_data_array
                );

                $db->perform(
                    TABLE_ADDRESS_BOOK,
                    $sql_data_array,
                    'update',
                    "customers_id = " . (int)$customers_id . " AND address_book_id = " . (int)$default_address_id . " LIMIT 1"
                );

                if (isset($_POST['customer_groups']) && is_array($_POST['customer_groups'])) {
                    zen_sync_customer_group_assignments($customers_id, $_POST['customer_groups']);
                }

                zen_record_admin_activity('Customer record updated for customer ID ' . (int)$customers_id, 'notice');

                // -----
                // The following, seemingly duplicate, notifications enable an auto-loaded admin observer to successfully
                // bind to the notification using the 'NOTIFY_ADMIN_CUSTOMER_UPDATE' version.  The other notification is kept
                // for downward compatibility with existing plugins' observers.
                //
                $zco_notifier->notify(
                    'NOTIFY_ADMIN_CUSTOMER_UPDATE',
                    $customers_id,
                    $default_address_id,
                    $sql_data_array
                );
                $zco_notifier->notify(
                    'ADMIN_CUSTOMER_UPDATE',
                    $customers_id,
                    $default_address_id,
                    $sql_data_array
                );

                zen_redirect(
                    zen_href_link(
                        FILENAME_CUSTOMERS,
                        zen_get_all_get_params(['cID', 'action']) . 'cID=' . $customers_id,
                        'NONSSL'
                    )
                );
            } elseif ($error === true) {
                $cInfo = new objectInfo($_POST);
                $cInfo->company = $cInfo->entry_company;
                $cInfo->street_address = $cInfo->entry_street_address;
                $cInfo->suburb = $cInfo->entry_suburb;
                $cInfo->postcode = $cInfo->entry_postcode;
                $cInfo->city =  $cInfo->entry_city;
                $cInfo->state = $cInfo->entry_state;
                $cInfo->customers_default_address_id = $default_address_id;
                $processed = true;
            }
            break;
        case 'pwdresetconfirm':
            if ($customers_id > 0 && isset($_POST['newpassword']) && $_POST['newpassword'] !== '' && isset($_POST['newpasswordConfirm']) && $_POST['newpasswordConfirm'] !== '') {
                $password_new = zen_db_prepare_input($_POST['newpassword']);
                $password_confirmation = zen_db_prepare_input($_POST['newpasswordConfirm']);
                $error = false;
                if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add_session(ERROR_PWD_TOO_SHORT . '(' . ENTRY_PASSWORD_MIN_LENGTH . ')', 'error');
                } elseif ($password_new !== $password_confirmation) {
                    $error = true;
                    $messageStack->add_session(ERROR_PASSWORDS_NOT_MATCHING, 'error');
                }
                if ($error === false) {
                    $customer = new Customer($customers_id);
                    $custinfo = $customer->getData();
                    if (empty($custinfo)) {
                        die('ERROR: customer ID not specified. This error should never happen.');
                    }
                    $customer->setPassword($password_new);

                    $message = EMAIL_CUSTOMER_PWD_CHANGE_MESSAGE . "\n\n" . $password_new . "\n\n\n";
                    $html_msg['EMAIL_MESSAGE_HTML'] = nl2br($message);
                    zen_mail(
                        $custinfo['customers_firstname'] . ' ' . $custinfo['customers_lastname'],
                        $custinfo['customers_email_address'],
                        EMAIL_CUSTOMER_PWD_CHANGE_SUBJECT,
                        $message,
                        STORE_NAME,
                        EMAIL_FROM,
                        $html_msg,
                        'default'
                    );
                    $userList = zen_get_users($_SESSION['admin_id']);
                    $userDetails = $userList[0];
                    $adminUser = $userDetails['id'] . '-' . $userDetails['name'] . ' ' . zen_get_ip_address();
                    $message = sprintf(
                        EMAIL_CUSTOMER_PWD_CHANGE_MESSAGE_FOR_ADMIN,
                        $custinfo['customers_firstname'] . ' ' . $custinfo['customers_lastname'] . ' ' . $custinfo['customers_email_address'],
                        $adminUser
                    ) . "\n";
                    $html_msg['EMAIL_MESSAGE_HTML'] = nl2br($message);
                    zen_mail(
                        $userDetails['name'],
                        $userDetails['email'],
                        EMAIL_CUSTOMER_PWD_CHANGE_SUBJECT,
                        $message,
                        STORE_NAME,
                        EMAIL_FROM,
                        $html_msg,
                        'default'
                    );

                    $messageStack->add_session(SUCCESS_PASSWORD_UPDATED, 'success');
                }
                zen_redirect(
                    zen_href_link(
                        FILENAME_CUSTOMERS,
                        zen_get_all_get_params(['cID', 'action']) . 'cID=' . $customers_id
                    )
                );
            }
            break;
        case 'deleteconfirm':
            $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_CUSTOMERS_DELETE_CONFIRM', ['customers_id' => $customers_id]);
            $customer = new Customer($customers_id);
            $delete_reviews = (isset($_POST['delete_reviews']) && $_POST['delete_reviews'] === 'on');
            $forget_only = (isset($_POST['delete_type_forget']) && $_POST['delete_type_forget'] === 'forget');
            $customer->delete($delete_reviews, $forget_only);
            zen_redirect(zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(['cID', 'action']), 'NONSSL'));
            break;
        case 'edit':
            if ($customers_id === 0) {
                zen_redirect(zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(['cID', 'action'])));
            }
        default:    //- Fall-through from above if $customers_id appears to be valid.
            $customer = new Customer($customers_id);
            $cInfo = new objectInfo($customer->getData());
            break;
    }
}
?>
<!doctype html>
    <html <?php echo HTML_PARAMS; ?>>
    <head>
        <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
<?php
if ($action === 'edit' || $action === 'update') {
?>
        <script>
            function check_form() {
                var error = 0;
                var error_message = '<?php echo JS_ERROR; ?>';

<?php
    if (ACCOUNT_GENDER === 'true') {
?>
                if (document.customers.customers_gender[0].checked || document.customers.customers_gender[1].checked) {
                } else {
                    error_message = error_message + '<?php echo JS_GENDER; ?>';
                    error = 1;
                }
<?php
}
?>

                if (document.customers.elements['entry_country_id'].type != 'hidden') {
                    if (document.customers.entry_country_id.value == 0) {
                        error_message = error_message + '<?php echo JS_COUNTRY; ?>';
                        error = 1;
                    }
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
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
        <!-- body_text //-->
        <h1><?php echo HEADING_TITLE; ?></h1>
<?php
if ($action === 'edit' || $action === 'update') {
    $newsletter_array = [
        ['id' => '1', 'text' => ENTRY_NEWSLETTER_YES],
        ['id' => '0', 'text' => ENTRY_NEWSLETTER_NO]
    ];

    echo zen_draw_form(
        'customers',
        FILENAME_CUSTOMERS,
        zen_get_all_get_params(['action']) . 'action=update',
        'post',
        'onsubmit="return check_form(customers);" class="form-horizontal"',
        true
    );
    echo zen_draw_hidden_field('default_address_id', $cInfo->customers_default_address_id);
    echo zen_draw_hidden_field('cID', $customers_id);
    echo zen_hide_session_id();
?>
        <div class="row formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></div>
        <div class="formArea">
<?php
    if (ACCOUNT_GENDER === 'true') {
?>
            <div class="form-group">
                <div class="col-sm-3">
                    <p class="control-label"><?php echo ENTRY_GENDER; ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                    <label class="radio-inline"><?php
                        echo zen_draw_radio_field(
                                'customers_gender',
                                'm',
                                false,
                                $cInfo->customers_gender
                            ) . MALE; ?>
                    </label>
                    <label class="radio-inline"><?php
                        echo zen_draw_radio_field(
                                'customers_gender',
                                'f',
                                false,
                                $cInfo->customers_gender
                            ) . FEMALE; ?>
                    </label>
                    <?php echo ($error == true && $entry_gender_error == true) ? '&nbsp;' . ENTRY_GENDER_ERROR : ''; ?>
                </div>
            </div>
<?php
    }

    $customers_authorization_array = [
        ['id' => '0', 'text' => CUSTOMERS_AUTHORIZATION_0],
        ['id' => '1', 'text' => CUSTOMERS_AUTHORIZATION_1],
        ['id' => '2', 'text' => CUSTOMERS_AUTHORIZATION_2],
        ['id' => '3', 'text' => CUSTOMERS_AUTHORIZATION_3],
        ['id' => '4', 'text' => CUSTOMERS_AUTHORIZATION_4], // banned
    ];
?>
            <div class="form-group">
                <?php
                echo zen_draw_label(
                    CUSTOMERS_AUTHORIZATION,
                    'customers_authorization',
                    'class="col-sm-3 control-label"'
                ); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_pull_down_menu(
                        'customers_authorization',
                        $customers_authorization_array,
                        $cInfo->customers_authorization,
                        'class="form-control" id="customers_authorization"'
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php
                echo zen_draw_label(ENTRY_FIRST_NAME, 'customers_firstname', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'customers_firstname',
                        htmlspecialchars(
                            $cInfo->customers_firstname,
                            ENT_COMPAT,
                            CHARSET,
                            true
                        ),
                        zen_set_field_length(
                            TABLE_CUSTOMERS,
                            'customers_firstname',
                            50
                        ) . ' class="form-control" id="customers_firstname" minlength="' . ENTRY_FIRST_NAME_MIN_LENGTH . '"',
                        true
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php
                echo zen_draw_label(ENTRY_LAST_NAME, 'customers_lastname', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'customers_lastname',
                        htmlspecialchars(
                            $cInfo->customers_lastname,
                            ENT_COMPAT,
                            CHARSET,
                            true
                        ),
                        zen_set_field_length(
                            TABLE_CUSTOMERS,
                            'customers_lastname',
                            50
                        ) . ' class="form-control" id="customers_lastname" minlength="' . ENTRY_LAST_NAME_MIN_LENGTH . '"',
                        true
                    ); ?>
                </div>
            </div>
<?php
    if (ACCOUNT_DOB === 'true') {
?>
            <div class="form-group">
                <?php
                echo zen_draw_label(ENTRY_DATE_OF_BIRTH, 'customers_dob', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'customers_dob',
                        ((empty($cInfo->customers_dob) || $cInfo->customers_dob <= '0001-01-01' || $cInfo->customers_dob === '0001-01-01 00:00:00') ? '' :
                            (($action === 'edit') ? zen_date_short($cInfo->customers_dob) : $cInfo->customers_dob)
                        ),
                        'maxlength="10" class="form-control" id="customers_dob" minlength="' . ENTRY_DOB_MIN_LENGTH . '"',
                        (ACCOUNT_DOB === 'true' && (int)ENTRY_DOB_MIN_LENGTH !== 0)
                    );
                    echo ($error === true && $entry_date_of_birth_error === true) ? '&nbsp;' . ENTRY_DATE_OF_BIRTH_ERROR : '';?>
                </div>
            </div>
<?php
    }
?>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_EMAIL_ADDRESS, 'customers_email_address', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'customers_email_address',
                        htmlspecialchars(
                            $cInfo->customers_email_address,
                            ENT_COMPAT,
                            CHARSET,
                            true
                        ),
                        zen_set_field_length(
                            TABLE_CUSTOMERS,
                            'customers_email_address',
                            50
                        ) . ' class="form-control" id="customers_email_address" minlength="' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . '"',
                        true
                        );
                        echo ($error === true && $entry_email_address_check_error === true) ? '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR : ''; ?>
                </div>
            </div>
 <?php
    // -----
    // If a plugin has additional fields to add to the form, it supplies that information here.
    // Additional fields are specified as a simple array of arrays,
    // with each array element identifying a new input element:
    //
    // $additional_fields = [
    //      [
    //          'label' => 'The text to include for the field label',
    //          'fieldname' => 'label "for" attribute, must match id of input field'
    //          'input' => 'The form-related portion of the field',
    //      ],
    //      ...
    // ];
    //
    $additional_fields = [];
    $zco_notifier->notify('NOTIFY_ADMIN_CUSTOMERS_CUSTOMER_EDIT', $cInfo, $additional_fields);
    if (!empty($additional_fields)) {
        foreach ($additional_fields as $current_field) {
?>
            <div class="form-group">
                <?php echo zen_draw_label($current_field['label'], $current_field['fieldname'], 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6"><?php echo $current_field['input']; ?></div>
            </div>
<?php
        }
    }
?>
        </div>
<?php
    if (ACCOUNT_COMPANY === 'true') {
?>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?>
        </div>
        <div class="row formAreaTitle">
            <?php echo CATEGORY_COMPANY; ?>
        </div>
        <div class="formArea">
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_COMPANY, 'entry_company', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'entry_company',
                        htmlspecialchars(($cInfo->company ?? ''), ENT_COMPAT, CHARSET, true),
                        zen_set_field_length(
                            TABLE_ADDRESS_BOOK,
                            'entry_company',
                            50
                        ) . ' class="form-control" id="entry_company" minlength="' . ENTRY_COMPANY_MIN_LENGTH . '"'
                    ); ?>
                </div>
            </div>
        </div>
<?php
    }
?>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?>
        </div>
        <div class="row formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></div>
        <div class="formArea">
            <div class="form-group">
                <?php
                echo zen_draw_label(ENTRY_STREET_ADDRESS, 'entry_street_address', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'entry_street_address',
                        htmlspecialchars($cInfo->street_address ?? '', ENT_COMPAT, CHARSET, true),
                        zen_set_field_length(
                            TABLE_ADDRESS_BOOK,
                            'entry_street_address',
                            50
                        ) . ' class="form-control" id="entry_street_address" minlength="' . ENTRY_STREET_ADDRESS_MIN_LENGTH . '"',
                        true
                    ); ?>
                </div>
            </div>
<?php
    if (ACCOUNT_SUBURB === 'true') {
?>
            <div class="form-group">
                <?php
                echo zen_draw_label(ENTRY_SUBURB, 'entry_suburb', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'entry_suburb',
                        htmlspecialchars((string)$cInfo->suburb ?? '', ENT_COMPAT, CHARSET, true),
                        zen_set_field_length(
                            TABLE_ADDRESS_BOOK,
                            'entry_suburb',
                            50
                        ) . ' class="form-control" id="entry_suburb"'
                    ); ?>
                </div>
            </div>
<?php
    }
?>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_POST_CODE, 'entry_postcode', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'entry_postcode',
                        htmlspecialchars($cInfo->postcode ?? '', ENT_COMPAT, CHARSET, true),
                        zen_set_field_length(
                            TABLE_ADDRESS_BOOK,
                            'entry_postcode',
                            10
                        ) . ' class="form-control" id="entry_postcode" minlength="' . ENTRY_POSTCODE_MIN_LENGTH . '"',
                        true
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php
                echo zen_draw_label(ENTRY_CITY, 'entry_city', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'entry_city',
                        htmlspecialchars($cInfo->city ?? '', ENT_COMPAT, CHARSET, true),
                        zen_set_field_length(
                            TABLE_ADDRESS_BOOK,
                            'entry_city',
                            50
                        ) . ' class="form-control" id="entry_city" minlength="' . ENTRY_CITY_MIN_LENGTH . '"',
                        true
                    ); ?>
                </div>
            </div>
<?php
    if (ACCOUNT_STATE === 'true') {
?>
            <div class="form-group">
                <?php
                echo zen_draw_label(ENTRY_STATE, 'entry_state', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
<?php
        echo zen_draw_pull_down_menu(
            'entry_zone_id',
            zen_prepare_country_zones_pull_down((int)$cInfo->entry_country_id),
            (int)$cInfo->entry_zone_id,
            'class="form-control" id="entry_zone_id"'
        );

        echo zen_draw_input_field(
            'entry_state',
            htmlspecialchars(
                $cInfo->entry_state ?? '',
                ENT_COMPAT,
                CHARSET,
                true
            ),
            'class="form-control" id="entry_state" minlength="' . ENTRY_STATE_MIN_LENGTH . '"'
        );
?>
                </div>
            </div>
<?php
    }
?>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_COUNTRY, 'entry_country_id', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_pull_down_menu(
                        'entry_country_id',
                        zen_get_countries_for_admin_pulldown(),
                        $cInfo->entry_country_id,
                        'class="form-control" id="entry_country_id"'
                    ); ?>
                </div>
            </div>
        </div>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?>
        </div>
        <div class="row formAreaTitle"><?php echo CATEGORY_CONTACT; ?></div>
        <div class="formArea">
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_TELEPHONE_NUMBER, 'customers_telephone', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'customers_telephone',
                        htmlspecialchars(
                            $cInfo->customers_telephone,
                            ENT_COMPAT,
                            CHARSET,
                            true
                        ),
                        zen_set_field_length(
                            TABLE_CUSTOMERS,
                            'customers_telephone',
                            15
                        ) . ' class="form-control" id="customers_telephone" minlength="' . ENTRY_TELEPHONE_MIN_LENGTH . '"',
                        true
                    ); ?>
                </div>
            </div>
<?php
    if (ACCOUNT_FAX_NUMBER === 'true') {
?>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_FAX_NUMBER, 'customers_fax', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
<?php
        if ($processed === true) {
            echo $cInfo->customers_fax . zen_draw_hidden_field('customers_fax');
        } else {
            echo zen_draw_input_field(
                'customers_fax',
                htmlspecialchars(
                    (string)$cInfo->customers_fax,
                    ENT_COMPAT,
                    CHARSET,
                    true
                ),
                zen_set_field_length(
                    TABLE_CUSTOMERS,
                    'customers_fax',
                    15
                ) . ' class="form-control" id="customers_fax"'
            );
        }
?>
                </div>
            </div>
<?php
    }
?>
        </div>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?>
        </div>
        <div class="row formAreaTitle"><?php echo CATEGORY_OPTIONS; ?></div>
        <div class="formArea">
            <div class="form-group">
                <div class="col-sm-3">
                    <p class="control-label"><?php echo ENTRY_EMAIL_PREFERENCE; ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
<?php
    if ($processed === true) {
        if ($cInfo->customers_email_format) {
            echo $customers_email_format . zen_draw_hidden_field('customers_email_format');
        }
    } else {
        $email_pref_text = ($cInfo->customers_email_format === 'TEXT');
        $email_pref_html = !$email_pref_text;
?>
                    <label class="radio-inline"><?php
                        echo zen_draw_radio_field(
                                'customers_email_format',
                                'HTML',
                                $email_pref_html
                            ) . ENTRY_EMAIL_HTML_DISPLAY; ?>
                    </label>
                    <label class="radio-inline"><?php
                        echo zen_draw_radio_field(
                                'customers_email_format',
                                'TEXT',
                                $email_pref_text
                            ) . ENTRY_EMAIL_TEXT_DISPLAY; ?>
                    </label>
<?php
    }
?>
                </div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_NEWSLETTER, 'customers_newsletter', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
<?php
    if ($processed === true) {
        if ($cInfo->customers_newsletter === 1) {
            echo ENTRY_NEWSLETTER_YES;
        } else {
            echo ENTRY_NEWSLETTER_NO;
        }
        echo zen_draw_hidden_field('customers_newsletter');
    } else {
        echo zen_draw_pull_down_menu(
            'customers_newsletter',
            $newsletter_array,
            ($cInfo->customers_newsletter === 1) ? 1 : 0,
            'class="form-control" id="customers_newsletter"'
        );
    }
?>
                </div>
            </div>
<?php
    if (WHOLESALE_PRICING_CONFIG !== 'false') {
?>
            <div class="form-group">
                <?php echo zen_draw_label(TEXT_WHOLESALE_LEVEL, 'customers-whole', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'customers_whole',
                        $cInfo->customers_whole,
                        ' class="form-control" id="customers-whole" min="0"',
                        false,
                        'number'
                    ); ?>
                    <span class="help-block" id="whole-help"><?php echo HELPTEXT_WHOLESALE_LEVEL; ?></span>
                </div>
            </div>
<?php
    }
?>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_PRICING_GROUP, 'customers_group_pricing', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
<?php
    if ($processed === true) {
        if ($cInfo->customers_group_pricing) {
            $group_query = $db->Execute(
                "SELECT group_name, group_percentage
                   FROM " . TABLE_GROUP_PRICING . "
                  WHERE group_id = " . (int)$cInfo->customers_group_pricing,
                  1
            );
            echo $group_query->fields['group_name'] . '&nbsp;' . $group_query->fields['group_percentage'] . '%';
        } else {
            echo ENTRY_NONE;
        }
        echo zen_draw_hidden_field('customers_group_pricing', $cInfo->customers_group_pricing);
    } else {
        $group_array_query = $db->Execute(
            "SELECT group_id, group_name, group_percentage
               FROM " . TABLE_GROUP_PRICING
        );
        $group_array[] = [
            'id' => 0,
            'text' => TEXT_NONE
        ];
        foreach ($group_array_query as $item) {
            $group_array[] = [
                'id' => $item['group_id'],
                'text' => $item['group_name'] . '&nbsp;' . $item['group_percentage'] . '%'
            ];
        }
        echo zen_draw_pull_down_menu(
            'customers_group_pricing',
            $group_array,
            $cInfo->customers_group_pricing,
            'class="form-control" id="customers_group_pricing"'
        );
    }
?>
                </div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(CUSTOMERS_REFERRAL, 'customers_referral', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                    <?php
                    echo zen_draw_input_field(
                        'customers_referral',
                        htmlspecialchars(
                            $cInfo->customers_referral,
                            ENT_COMPAT,
                            CHARSET,
                            true
                        ),
                        zen_set_field_length(
                            TABLE_CUSTOMERS,
                            'customers_referral',
                            15
                        ) . ' class="form-control" id="customers_referral"'
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-3">
                    <p class="control-label"><?php echo TEXT_CUSTOMER_GROUPS; ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                    <div class="row">
                        <div class="col-sm-4">
                            <input type="hidden" name="customer_groups[]" value="0">
<?php
    $groups_already_in = zen_groups_customer_belongs_to($cInfo->customers_id);
    foreach (zen_get_all_customer_groups() as $group) {
?>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="customer_groups[]" value="<?php
                                    echo $group['id']; ?>" <?php
                                    if (array_key_exists($group['id'], $groups_already_in)) {
                                        echo 'checked';
                                    } ?>>
                                    <?php
                                    echo $group['text']; ?>
                                </label>
                            </div>
<?php
    }
?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?>
        </div>
        <div class="row text-right">
            <button type="submit" class="btn btn-primary">
                <?php echo IMAGE_UPDATE; ?>
            </button>
            <a href="<?php
            echo zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(['action'])); ?>"
               class="btn btn-default"><?php
                echo IMAGE_CANCEL; ?></a>
        </div>
        <?php echo '</form>'; ?>
<?php
} elseif ($action === 'list_addresses') {
?>
        <div class="row">
            <fieldset>
                <legend><?php
                    echo ADDRESS_BOOK_TITLE; ?></legend>
                <div class="alert forward"><?php
                    echo sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES); ?></div>
                <br class="clearBoth">
<?php
    /**
     * Used to loop thru and display address book entries
     */
    foreach ($addressArray as $addresses) {
?>
                    <h3 class="addressBookDefaultName"><?php
                        echo zen_output_string_protected(
                            $addresses['firstname'] . ' ' . $addresses['lastname']
                        );
                        echo ((int)$addresses['address_book_id'] === zen_get_customers_address_primary((int)$_GET['cID'])) ?
                            '&nbsp;' . PRIMARY_ADDRESS : ''; ?>
                    </h3>
                    <address><?php
                        echo zen_address_format(
                            $addresses['format_id'],
                            $addresses['address'],
                            true,
                            ' ',
                            '<br>'
                        ); ?>
                    </address>

                    <br class="clearBoth">
<?php
    }
?>
                    <div class="buttonRow forward">
                        <a href="<?php
                            echo zen_href_link(
                                FILENAME_CUSTOMERS,
                                zen_get_all_get_params(['action']),
                                'NONSSL'
                            ); ?>" class="btn btn-default" role="button">
                            <?php echo IMAGE_BACK; ?>
                        </a>
                    </div>
                </fieldset>
            </div>
<?php
} else {
    // -----
    // For a "Password Reset" action, the "New Password" field should
    // get the autofocus, not the search-input.
    //
    $no_searchbox_autofocus = ($action === 'pwreset');
?>
            <div class="col-sm-offset-8 col-sm-4">
                <?php include DIR_WS_MODULES . 'search_box.php'; ?>
            </div>
<?php
// Sort Listing
    switch ($_GET['list_order']) {
        case 'id-asc':
            $disp_order = "ci.customers_info_date_account_created";
            break;
        case 'firstname':
            $disp_order = "c.customers_firstname";
            break;
        case 'firstname-desc':
            $disp_order = "c.customers_firstname DESC";
            break;
        case 'group-asc':
            $disp_order = "c.customers_group_pricing";
            break;
        case 'group-desc':
            $disp_order = "c.customers_group_pricing DESC";
            break;
        case 'lastname':
            $disp_order = "c.customers_lastname, c.customers_firstname";
            break;
        case 'lastname-desc':
            $disp_order = "c.customers_lastname DESC, c.customers_firstname";
            break;
        case 'company':
            $disp_order = "a.entry_company";
            break;
        case 'company-desc':
            $disp_order = "a.entry_company DESC";
            break;
        case 'login-asc':
            $disp_order = "ci.customers_info_date_of_last_logon";
            break;
        case 'login-desc':
            $disp_order = "ci.customers_info_date_of_last_logon DESC";
            break;
        case 'approval-asc':
            $disp_order = "c.customers_authorization";
            break;
        case 'approval-desc':
            $disp_order = "c.customers_authorization DESC";
            break;
        case 'gv_balance-asc':
            $disp_order = "cgc.amount, c.customers_lastname, c.customers_firstname";
            break;
        case 'gv_balance-desc':
            $disp_order = "cgc.amount DESC, c.customers_lastname, c.customers_firstname";
            break;
        case 'wholesale-asc':
            $disp_order = 'c.customers_whole, c.customers_lastname, c.customers_firstname';
            break;
        case 'wholesale-desc':
            $disp_order = 'c.customers_whole DESC, c.customers_lastname, c.customers_firstname';
            break;
        default:
            $disp_order = "ci.customers_info_date_account_created DESC";
            break;
    }
?>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
                    <table class="table table-hover" role="listbox">
                        <thead>
                        <tr class="dataTableHeadingRow">
                            <th class="dataTableHeadingContent text-right">
                                <?php echo TABLE_HEADING_ID; ?>
                            </th>
                            <th class="dataTableHeadingContent">
                                &nbsp;
                            </th>
                            <th class="dataTableHeadingContent">
                                <?php echo (($_GET['list_order'] === 'lastname' || $_GET['list_order'] === 'lastname-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_LASTNAME . '</span>' :
                                    TABLE_HEADING_LASTNAME); ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=lastname',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'lastname') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=lastname-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'lastname-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                                </a>
                            </th>
                            <th class="dataTableHeadingContent">
                                <?php echo ($_GET['list_order'] === 'firstname' || $_GET['list_order'] === 'firstname-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_FIRSTNAME . '</span>' :
                                    TABLE_HEADING_FIRSTNAME; ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=firstname',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'firstname') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=firstname-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'firstname-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                                </a>
                            </th>
<?php
    if (ACCOUNT_COMPANY === 'true') {
?>
                            <th class="dataTableHeadingContent">
                                <?php echo ($_GET['list_order'] === 'company' || $_GET['list_order'] === 'company-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_COMPANY . '</span>' :
                                    TABLE_HEADING_COMPANY; ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=company',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'company') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=company-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'company-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                                </a>
                            </th>
<?php
    }
    if ($show_registration_ip_in_listing) {
?>
                            <th class="dataTableHeadingContent">
                                <?php echo TABLE_HEADING_REGISTRATION_IP; ?>
                            </th>
<?php
    }
    // -----
    // If a plugin has additional columns to add to the display, it attaches to both this "listing header" and (see below)
    // the "listing data" notifications.
    //
    // For the header "insert", the observer sets the $additional_headings to include a simple array of arrays.  Each
    // entry contains the information for one heading column in the format:
    //
    // $additional_headings = [
    //      [
    //          'content' => 'The content for the column',
    //          'class' => 'Any additional class for the display',
    //          'parms' => 'Any additional parameters for the display',
    //      ],
    //      ...
    // ];
    //
    // The 'content' element is required; the 'class' and 'parms' are optional.
    //
    $additional_headings = [];
    $additional_heading_count = 0;
    $zco_notifier->notify('NOTIFY_ADMIN_CUSTOMERS_LISTING_HEADER', [], $additional_headings);
    if (is_array($additional_headings) && count($additional_headings) !== 0) {
        $additional_heading_count = count($additional_headings);
        foreach ($additional_headings as $heading_data) {
            $additional_class = (isset($heading_data['class'])) ? (' ' . $heading_data['class']) : '';
            $additional_parms = (isset($heading_data['parms'])) ? (' ' . $heading_data['parms']) : '';
            $heading_content = $heading_data['content'];
?>
                            <th class="dataTableHeadingContent<?php echo $additional_class; ?>"<?php echo $additional_parms; ?>>
                                <?php echo $heading_content; ?>
                            </th>
<?php
        }
    }
?>
                            <th class="dataTableHeadingContent">
                                <?php echo ($_GET['list_order'] === 'id-asc' || $_GET['list_order'] === 'id-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_ACCOUNT_CREATED . '</span>' :
                                    TABLE_HEADING_ACCOUNT_CREATED; ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=id-asc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'id-asc') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=id-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'id-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                                </a>
                            </th>

                            <th class="dataTableHeadingContent">
                                <?php echo ($_GET['list_order'] === 'login-asc' || $_GET['list_order'] === 'login-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_LOGIN . '</span>' :
                                    TABLE_HEADING_LOGIN; ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=login-asc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'login-asc') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=login-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'login-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                                </a>
                            </th>
<?php
    if (WHOLESALE_PRICING_CONFIG !== 'false') {
?>
                            <th class="dataTableHeadingContent">
                                <?php echo ($_GET['list_order'] === 'wholesale-asc' || $_GET['list_order'] === 'wholesale-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_WHOLESALE_LEVEL . '</span>' :
                                    TABLE_HEADING_WHOLESALE_LEVEL; ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=wholesale-asc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'wholesale-asc') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=wholesale-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'wholesale-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                                </a>
                            </th>
<?php
    }
?>
                            <th class="dataTableHeadingContent">
                                <?php echo ($_GET['list_order'] === 'group-asc' || $_GET['list_order'] === 'group-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_PRICING_GROUP . '</span>' :
                                    TABLE_HEADING_PRICING_GROUP; ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=group-asc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'group-asc') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=group-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'group-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                                </a>
                            </th>

<?php
    if (defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS === 'true') {
?>
                            <th class="dataTableHeadingContent text-right">
                                <?php echo ($_GET['list_order'] === 'gv_balance-asc' or $_GET['list_order'] === 'gv_balance-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_GV_AMOUNT . '</span>' :
                                    TABLE_HEADING_GV_AMOUNT; ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=gv_balance-asc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'gv_balance-asc') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']
                                    ) . 'list_order=gv_balance-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'gv_balance-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                               </a>
                            </th>
<?php
    }
?>
                            <th class="dataTableHeadingContent text-center">
                                <?php echo ($_GET['list_order'] === 'approval-asc' || $_GET['list_order'] === 'approval-desc') ?
                                    '<span class="SortOrderHeader">' . TABLE_HEADING_AUTHORIZATION_APPROVAL . '</span>' :
                                    TABLE_HEADING_AUTHORIZATION_APPROVAL; ?>
                                <br>
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=approval-asc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'approval-asc') ?
                                        '<span class="SortOrderHeader">' . TEXT_ASC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_ASC . '</span>'; ?>
                                </a>&nbsp;
                                <a href="<?php
                                echo zen_href_link(
                                    basename($PHP_SELF),
                                    zen_get_all_get_params(['list_order', 'page']) . 'list_order=approval-desc',
                                    'NONSSL'
                                ); ?>">
                                    <?php echo ($_GET['list_order'] === 'approval-desc') ?
                                        '<span class="SortOrderHeader">' . TEXT_DESC . '</span>' :
                                        '<span class="SortOrderHeaderLink">' . TEXT_DESC . '</span>'; ?>
                                </a>
                            </th>

                            <th class="dataTableHeadingContent text-right">
                                <?php echo TABLE_HEADING_ACTION; ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
<?php
    $search = '';
    if (!empty($_GET['search'])) {
        $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
        $keyword_search_fields = [
            'c.customers_lastname',
            'c.customers_firstname',
            'c.customers_email_address',
            'c.customers_telephone',
            'c.customers_id',
            'a.entry_company',
            'a.entry_street_address',
            'a.entry_city',
            'a.entry_postcode',
        ];
        $search = zen_build_keyword_where_clause($keyword_search_fields, trim($keywords), true);
    }
    $new_fields = '';

    $zco_notifier->notify(
        'NOTIFY_ADMIN_CUSTOMERS_LISTING_NEW_FIELDS',
        [],
        $new_fields,
        $disp_order
    );

    $customers_query_raw =
        "SELECT c.customers_id " . $new_fields . ", cgc.amount
           FROM " . TABLE_CUSTOMERS . " c
                LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON c.customers_id= ci.customers_info_id
                LEFT JOIN " . TABLE_ADDRESS_BOOK . " a ON c.customers_id = a.customers_id AND c.customers_default_address_id = a.address_book_id
                LEFT JOIN " . TABLE_COUPON_GV_CUSTOMER . " cgc ON c.customers_id = cgc.customer_id
                    " . $search . "
          ORDER BY " . $disp_order;

    // Split Page
    // reset page when page is unknown
    if ((empty($_GET['page']) || $_GET['page'] === '1') && !empty($_GET['cID'])) {
        $check_page = $db->Execute($customers_query_raw);
        $check_count = 0;
        if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER) {
            foreach ($check_page as $item) {
                $check_count++;
                if ($item['customers_id'] === $_GET['cID']) {
                    break;
                }
            }
            $_GET['page'] = round(
                (($check_count / MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER) + (fmod_round(
                        $check_count,
                        MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER
                    ) != 0 ? .5 : 0)),
                0
            );
    //    zen_redirect(zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $_GET['cID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'NONSSL'));
        } else {
            $_GET['page'] = '1';
        }
    }

    $customers_split = new splitPageResults(
        $_GET['page'],
        MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER,
        $customers_query_raw,
        $customers_query_numrows
    );
    $customers = $db->Execute($customers_query_raw);
    foreach ($customers as $result) {
        $cust = new Customer($result['customers_id']);
        $customer = $cust->getData();
        if ((!isset($_GET['cID']) || (int)$_GET['cID'] === $customer['customers_id']) && !isset($cInfo)) {
            $cInfo = new objectInfo($customer);
        }

        if (isset($cInfo) && is_object($cInfo) && ($customer['customers_id'] === (int)$cInfo->customers_id)) {
?>
                            <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php
                                echo zen_href_link(
                                    FILENAME_CUSTOMERS,
                                    zen_get_all_get_params(['cID', 'action']
                                    ) . 'cID=' . $cInfo->customers_id . '&action=edit'
                                ); ?>'" role="option" aria-selected="true">
<?php
        } else {
?>
                            <tr class="dataTableRow" onclick="document.location.href = '<?php
                                echo zen_href_link(
                                    FILENAME_CUSTOMERS,
                                    zen_get_all_get_params(['cID', 'action']) . 'cID=' . $customer['customers_id']
                                ); ?>'" role="option" aria-selected="false">
<?php
        }

        $zc_address_book_count = count($customer['addresses']);
?>
                                <td class="dataTableContent text-right"><?php echo $customer['customers_id']; ?></td>
                                <td class="dataTableContent"><?php
                                    echo ($zc_address_book_count === 1) ? TEXT_INFO_ADDRESS_BOOK_COUNT_SINGLE : sprintf(
                                        TEXT_INFO_ADDRESS_BOOK_COUNT,
                                        zen_href_link(
                                            FILENAME_CUSTOMERS,
                                            zen_get_all_get_params(['cID', 'action']
                                            ) . 'cID=' . $customer['customers_id'] . '&action=list_addresses'
                                        ),
                                        $zc_address_book_count
                                    ); ?>
                                </td>
                                <td class="dataTableContent"><?php echo $customer['customers_lastname']; ?></td>
                                <td class="dataTableContent"><?php echo $customer['customers_firstname']; ?></td>
<?php
        if (ACCOUNT_COMPANY === 'true') {
?>
                                <td class="dataTableContent"><?php echo zen_output_string_protected($customer['company'] ?? ''); ?></td>
<?php
        }
        if ($show_registration_ip_in_listing) {
?>
                                <td class="dataTableContent"><?php echo $customer['registration_ip']; ?></td>
<?php
        }

        // -----
        // If a plugin has additional columns to add to the display, it attaches to both this "listing element" and (see above)
        // the "listing heading" notifications.
        //
        // For the element "insert", the observer sets the $additional_headings to include a simple array of arrays.  Each
        // entry contains the information for one element column in the format:
        //
        // $additional_columns = [
        //      [
        //          'content' => 'The content for the column',
        //          'class' => 'Any additional class for the display',
        //          'parms' => 'Any additional parameters for the display',
        //      ],
        //      ...
        // ];
        //
        // The 'content' element is required; the 'class' and 'parms' are optional.
        //
        $additional_columns = [];
        $zco_notifier->notify(
            'NOTIFY_ADMIN_CUSTOMERS_LISTING_ELEMENT',
            array_merge($result, $customer),
            $additional_columns,
            $customer
        );
        if (is_array($additional_columns) && count($additional_columns) !== 0) {
            if (count($additional_columns) !== $additional_heading_count) {
                trigger_error(
                    "Mismatched additional column heading ($additional_heading_count) and column element (" . count(
                        $additional_columns
                    ) . ") counts detected for the Customers listing.",
                    E_USER_WARNING
                );
            }
            foreach ($additional_columns as $column_data) {
                $additional_class = (isset($column_data['class'])) ? (' ' . $column_data['class']) : '';
                $additional_parms = (isset($column_data['parms'])) ? (' ' . $column_data['parms']) : '';
                $element_content = $column_data['content'];
?>
                                <td class="dataTableContent<?php echo $additional_class; ?>"<?php echo $additional_parms; ?>>
                                    <?php echo $element_content; ?>
                                </td>
<?php
            }
        }
?>
                                <td class="dataTableContent">
                                    <?php echo zen_date_short($customer['date_account_created']); ?>
                                </td>
                                <td class="dataTableContent">
                                    <?php echo zen_date_short($customer['date_of_last_login']); ?>
                                </td>
<?php
        if (WHOLESALE_PRICING_CONFIG !== 'false') {
?>
                                <td class="dataTableContent text-center">
                                    <?php echo $customer['customers_whole']; ?>
                                </td>
<?php
        }
?>
                                <td class="dataTableContent">
                                    <?php echo $customer['pricing_group_name']; ?>
                                </td>
<?php
        if (defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS === 'true') {
?>
                                <td class="dataTableContent text-right">
                                    <?php echo $currencies->format($customer['gv_balance']); ?>
                                </td>
<?php
        }
?>
                                <td class="dataTableContent text-center">
                                <?php
                                    echo zen_draw_form(
                                        'set_status_' . $customer['customers_id'],
                                        FILENAME_CUSTOMERS,
                                        zen_get_all_get_params(['action', 'cID']) . 'action=status&cID=' . $customer['customers_id']
                                    ); ?>
                                    <button type="submit" class="btn btn-status">
<?php
        if ($customer['customers_authorization'] === 0) {
?>
                                        <i class="fa-solid fa-square txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
<?php
        } else {
?>
                                        <i class="fa-solid fa-square txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
<?php
}
?>
                                    </button>
                                    <?php echo zen_draw_hidden_field('current_status', (string)$customer['customers_authorization']); ?>
                                <?php echo '</form>'; ?>
                                </td>
                                <td class="dataTableContent text-right">
<?php
        if (isset($cInfo) && is_object($cInfo) && ($customer['customers_id'] === (int)$cInfo->customers_id)) {
                                    echo zen_icon('caret-right', '', '2x', true);
        } else {
?>
                                    <a href="<?php
                                        echo zen_href_link(
                                            FILENAME_CUSTOMERS,
                                                zen_get_all_get_params(['cID']) . 'cID=' . $customer['customers_id'],
                                                'NONSSL'
                                        ); ?>" title="<?php echo IMAGE_ICON_INFO; ?>" role="button">
                                        <?php echo zen_icon('circle-info', '', '2x', true, false) ?>
                                    </a>
<?php
        }
?>
                                </td>
                            </tr>
<?php
    }
?>
                        </tbody>
                    </table>
                </div>

                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
<?php
    $heading = [];
    $contents = [];

    switch ($action) {
        case 'confirm':
            $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</h4>'];

            $contents = [
                'form' =>
                zen_draw_form(
                    'customers',
                    FILENAME_CUSTOMERS,
                    zen_get_all_get_params(['cID', 'action', 'search']) . 'action=deleteconfirm',
                    'post',
                    '',
                    true
                ) .
                zen_draw_hidden_field('cID', $cInfo->customers_id)
            ];
            $contents[] = [
                'text' =>
                    TEXT_DELETE_INTRO . '<br><br>' .
                    '<b>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</b>'
            ];
            if (isset($cInfo->number_of_reviews) && $cInfo->number_of_reviews > 0) {
                $contents[] = [
                    'text' =>
                        '<br>' .
                        zen_draw_checkbox_field('delete_reviews', 'on', true) .
                        ' ' .
                        sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews)
                ];
            }
            $contents[] = [
                'align' => 'text-center',
                'text' =>
                    '<br>' .
                    '<button type="submit" name="delete_type_forget" value="forget" class="btn btn-primary">' . IMAGE_FORGET_ONLY . '</button>' .
                    ' ' .
                    '<button type="submit" name="delete_type_full" value="delete" class="btn btn-danger">' . IMAGE_DELETE . '</button>' .
                    ' ' .
                    '<a href="' .
                        zen_href_link(
                            FILENAME_CUSTOMERS,
                            zen_get_all_get_params(['cID', 'action']) . 'cID=' . $cInfo->customers_id,
                            'NONSSL'
                        ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL .
                    '</a>'
            ];
            break;
        case 'pwreset':
            $heading[] = [
                'text' => '<h4>' . TEXT_INFO_HEADING_RESET_CUSTOMER_PASSWORD . '</h4>'
            ];
            $contents = [
                'form' =>
                    zen_draw_form(
                        'customers',
                        FILENAME_CUSTOMERS,
                        zen_get_all_get_params(['cID', 'action']) . 'action=pwdresetconfirm',
                        'post',
                        'id="pReset" class="form-horizontal"',
                        true
                    ) .
                    zen_draw_hidden_field('cID', $cInfo->customers_id)
            ];
            $contents[] = [
                'text' =>
                    TEXT_PWDRESET_INTRO .
                    '<br><br>' .
                    '<strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>'
            ];
            $contents[] = [
                'text' =>
                    '<br>' .
                    zen_draw_label(TEXT_CUST_NEW_PASSWORD, 'newpassword', 'class="control-label"') .
                    zen_draw_input_field(
                        'newpassword',
                        '',
                        'maxlength="40" autofocus="autofocus" autocomplete="off" id="newpassword" class="form-control"',
                        false,
                        'text',
                        false
                    )
            ];
            $contents[] = [
                'text' =>
                    '<br>' .
                    zen_draw_label(TEXT_CUST_CONFIRM_PASSWORD, 'newpasswordConfirm', 'class="control-label"') .
                    zen_draw_input_field(
                        'newpasswordConfirm',
                        '',
                        'maxlength="40" autocomplete="off" id="newpasswordConfirm" class="form-control"',
                        false,
                        'text',
                        false
                    )
            ];
            $contents[] = [
                'align' => 'text-center',
                'text' =>
                    '<br>' .
                    '<button type="submit" class="btn btn-warning">' . IMAGE_RESET_PWD . '</button>' .
                    ' ' .
                    '<a href="' .
                        zen_href_link(
                            FILENAME_CUSTOMERS,
                            zen_get_all_get_params(['cID', 'action']) . 'cID=' . $cInfo->customers_id
                        ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL .
                    '</a>'
            ];
            break;
        default:
            if (isset($_GET['search'])) {
                $_GET['search'] = zen_output_string_protected($_GET['search']);
            }
            if (isset($cInfo) && is_object($cInfo)) {
                $customer = new Customer($cInfo->customers_id);

                $heading[] = [
                    'text' =>
                        '<h4>' .
                            TABLE_HEADING_ID . ' ' . $cInfo->customers_id . ' ' .
                            $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname .
                        '</h4>' .
                        '<br>' .
                        $cInfo->customers_email_address
                ];

                $contents[] = [
                    'align' => 'text-center',
                    'text' =>
                        '<a href="' .
                            zen_href_link(
                                FILENAME_CUSTOMERS,
                                zen_get_all_get_params(['cID', 'action']) . 'cID=' . $cInfo->customers_id . '&action=edit',
                                'NONSSL'
                            ) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT .
                        '</a>' .
                        ' ' .
                        '<a href="' .
                            zen_href_link(
                                FILENAME_CUSTOMERS,
                                zen_get_all_get_params(['cID', 'action']) . 'cID=' . $cInfo->customers_id . '&action=confirm',
                                'NONSSL'
                            ) . '" class="btn btn-warning" role="button">' . IMAGE_DELETE .
                        '</a>'
                ];
                $contents[] = [
                    'align' => 'text-center',
                    'text' =>
                        ($cInfo->number_of_orders > 0 ?
                            '<a href="' .
                                zen_href_link(
                                    FILENAME_ORDERS,
                                    'cID=' . $cInfo->customers_id,
                                    'NONSSL'
                                ) . '" class="btn btn-default" role="button">' . IMAGE_ORDERS .
                            '</a>' :
                            ''
                        ) .
                        ' ' .
                        '<a href="' .
                            zen_href_link(
                                FILENAME_MAIL,
                                'origin=customers.php&customer=' . $cInfo->customers_email_address . '&cID=' . $cInfo->customers_id,
                                'NONSSL'
                            ) . '" class="btn btn-default" role="button">' . IMAGE_EMAIL .
                        '</a>'
                ];
                $contents[] = [
                    'align' => 'text-center',
                    'text' =>
                        '<a href="' .
                            zen_href_link(
                                FILENAME_CUSTOMERS,
                                zen_get_all_get_params(['cID', 'action']) . 'cID=' . $cInfo->customers_id . '&action=pwreset'
                            ) .
                            '" class="btn btn-warning" role="button">' . IMAGE_RESET_PWD .
                        '</a>'
                ];

                // -----
                // Give an observer the opportunity to provide an override to the "Place Order" button.
                //
                $place_order_override = false;
                $zco_notifier->notify(
                    'NOTIFY_ADMIN_CUSTOMERS_PLACE_ORDER_BUTTON',
                    $cInfo,
                    $contents,
                    $place_order_override
                );
                if ($place_order_override === false && zen_admin_authorized_to_place_order()) {
                    $login_form_start =
                        '<form rel="noopener" target="_blank" name="login" action="' .
                        zen_catalog_href_link(FILENAME_LOGIN, '', 'SSL') .
                        '" method="post">';
                    $hiddenFields = zen_draw_hidden_field('email_address', $cInfo->customers_email_address);
                    if (defined('EMP_LOGIN_AUTOMATIC') && EMP_LOGIN_AUTOMATIC === 'true' && ENABLE_SSL_CATALOG === 'true') {
                        $secret = zen_update_customers_secret($cInfo->customers_id);
                        $timestamp = time();
                        $hmacpostdata = [
                            'cid' => $cInfo->customers_id,
                            'aid' => $_SESSION['admin_id'],
                            'email_address' => $cInfo->customers_email_address,
                            'timestamp' => $timestamp,
                        ];
                        $hmacUri = zen_create_hmac_uri($hmacpostdata, $secret);
                        $login_form_start = '<form id="loginform" rel="noopener" target="_blank" name="login" action="' .
                            zen_catalog_href_link(
                                FILENAME_LOGIN,
                                $hmacUri . '&action=process',
                                'SSL'
                            ) . '" method="post">';
                        $hiddenFields .=
                            zen_draw_hidden_field('aid', $_SESSION['admin_id']) .
                            zen_draw_hidden_field('cid', $cInfo->customers_id) .
                            zen_draw_hidden_field('timestamp', $timestamp);
                    }
                    $contents[] = [
                        'align' => 'text-center',
                        'text' =>
                            $login_form_start .
                            $hiddenFields .
                            '<input class="btn btn-primary" type="submit" value="' . EMP_BUTTON_PLACEORDER . '" title="' . EMP_BUTTON_PLACEORDER_ALT . '">' .
                            '</form>'
                    ];
                }

                $zco_notifier->notify('NOTIFY_ADMIN_CUSTOMERS_MENU_BUTTONS', $cInfo, $contents);

                $contents[] = [
                    'text' =>
                        '<br>' .
                        TEXT_DATE_ACCOUNT_CREATED . ' ' .
                        zen_date_short($cInfo->date_account_created)
                ];
                if (!empty($cInfo->registration_ip)) {
                    $whois_url = 'https://whois.domaintools.com/' . $cInfo->registration_ip;
                    $lookup_link = ' <a href="' . $whois_url . '" rel="noreferrer noopener" target="_blank">';
                    $contents[] = [
                        'text' => '<br>' . TEXT_REGISTRATION_IP . ' ' . $lookup_link . $cInfo->registration_ip . '</a>'
                    ];
                }
                $contents[] = [
                    'text' =>
                        '<br>' .
                        TEXT_DATE_ACCOUNT_LAST_MODIFIED . ' ' .
                        zen_date_short($cInfo->date_account_last_modified)
                ];
                $contents[] = [
                    'text' =>
                        '<br>' .
                        TEXT_INFO_DATE_LAST_LOGON . ' ' .
                        zen_date_short($cInfo->date_of_last_login)
                ];
                if (!empty($cInfo->last_login_ip)) {
                    $contents[] = [
                        'text' =>
                            '<br>' .
                            TEXT_LAST_LOGIN_IP . ' ' .
                            $cInfo->last_login_ip
                    ];
                }
                $contents[] = [
                    'text' =>
                        '<br>' .
                        TEXT_INFO_NUMBER_OF_LOGONS . ' ' .
                        $cInfo->number_of_logins
                ];

                $contents[] = [
                    'text' =>
                        '<br>' .
                        TEXT_INFO_GV_AMOUNT . ' ' .
                        $currencies->format($cInfo->gv_balance)
                ];

                $text = '<br>' .
                    TEXT_INFO_NUMBER_OF_ORDERS . ' ' .
                    $cInfo->number_of_orders;
                if ($cInfo->number_of_orders > 0) {
                    $text .= ' [ ';
                    foreach ($customer->getOrderHistory(5) as $order) {
                        $text .= '<a href="' . zen_href_link(
                            FILENAME_ORDERS,
                            'cID=' . $cInfo->customers_id . '&oID=' . $order['orders_id'] . '&action=edit',
                            'NONSSL'
                        ) . '" title="Purchased: ' . zen_date_short($order['date_purchased']) . ', status ' . $order['orders_status_name'] . '">' . $order['orders_id'] . '</a> ';
                    }
                    if ($cInfo->number_of_orders > 5) {
                        $text .= ' ... ';
                    }
                    $text .= ' ]';
                }
                $contents[] = [
                    'text' => $text
                ];

                if (!empty($cInfo->lifetime_value)) {
                    $contents[] = [
                        'text' =>
                            TEXT_INFO_LIFETIME_VALUE . ' ' .
                            $currencies->format($cInfo->lifetime_value)
                    ];
                    $contents[] = [
                        'text' =>
                            TEXT_INFO_LAST_ORDER . ' ' .
                            zen_date_short($cInfo->last_order['date_purchased']) .
                            '<br>' .
                            TEXT_INFO_ORDERS_TOTAL . ' ' .
                            $cInfo->last_order['order_total']
                    ];
                }
                $contents[] = [
                    'text' =>
                        '<br>' .
                        TEXT_INFO_COUNTRY . ' ' .
                        $cInfo->country_iso
                ];
                $contents[] = [
                    'text' =>
                        '<br>' .
                        TEXT_INFO_NUMBER_OF_REVIEWS . ' ' .
                        $cInfo->number_of_reviews
                ];
                $contents[] = [
                    'text' =>
                        '<br>' .
                        CUSTOMERS_REFERRAL . ' ' .
                        zen_output_string_protected($cInfo->customers_referral)
                ];
            }
            break;
    }
    $zco_notifier->notify('NOTIFY_ADMIN_CUSTOMERS_MENU_BUTTONS_END', $cInfo ?? new stdClass, $contents);

    if (!empty($heading) && !empty($contents)) {
        $box = new box();
        echo $box->infoBox($heading, $contents);
    }
?>
                </div>
            </div>
            <div class="row">
                <table class="table">
                    <tr>
                        <td>
                            <?php echo $customers_split->display_count(
                                $customers_query_numrows,
                                MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER,
                                $_GET['page'],
                                TEXT_DISPLAY_NUMBER_OF_CUSTOMERS
                            ); ?>
                        </td>
                        <td class="text-right">
                            <?php echo $customers_split->display_links(
                                $customers_query_numrows,
                                MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER,
                                MAX_DISPLAY_PAGE_LINKS,
                                $_GET['page'],
                                zen_get_all_get_params(['page', 'info', 'x', 'y', 'cID'])
                            ); ?>
                        </td>
                    </tr>
<?php
    if (!empty($_GET['search'])) {
?>
                    <tr>
                        <td colspan="2" class="text-right">
                            <a href="<?php echo zen_href_link(FILENAME_CUSTOMERS); ?>" class="btn btn-default" role="button">
                                <?php echo IMAGE_RESET; ?>
                            </a>
                        </td>
                    </tr>
<?php
    }
?>
                </table>
            </div>
<?php
}
?>
        <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
    <script>
        $(function () {
            $("#loginform").submit(function (event) {
                $("#emp-timestamp").val(Date.now() / 1000);
            });
        });
    </script>
    </body>
    </html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
