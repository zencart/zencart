<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 20 Modified in v2.1.0-beta1 $
*/

$define = [
    'HEADING_TITLE' => 'Customers',
    'TABLE_HEADING_FIRSTNAME' => 'First Name',
    'TABLE_HEADING_LASTNAME' => 'Last Name',
    'TABLE_HEADING_ACCOUNT_CREATED' => 'Account Created',
    'TABLE_HEADING_LOGIN' => 'Last Login',
    'TABLE_HEADING_REGISTRATION_IP' => 'Registration IP',
    'TABLE_HEADING_PRICING_GROUP' => 'Pricing Group',
    'TABLE_HEADING_AUTHORIZATION_APPROVAL' => 'Authorized',
    'TABLE_HEADING_GV_AMOUNT' => 'GV Balance',
    'TEXT_DATE_ACCOUNT_CREATED' => 'Account Created:',
    'TEXT_DATE_ACCOUNT_LAST_MODIFIED' => 'Last Modified:',
    'TEXT_INFO_DATE_LAST_LOGON' => 'Last Login:',
    'TEXT_INFO_NUMBER_OF_LOGONS' => 'Number of Logins:',
    'TEXT_LAST_LOGIN_IP' => 'Last Login IP:',
    'TEXT_REGISTRATION_IP' => 'Registration IP:',
    'TEXT_INFO_COUNTRY' => 'Country:',
    'TEXT_INFO_NUMBER_OF_REVIEWS' => 'Number of Reviews:',
    'TEXT_DELETE_INTRO' => 'Are you sure you want to delete this customer?<br>"Forget Only" - Delete identifiable personal details from the customer record.<br>"Delete" - Delete the customer record from the database.',
    'TEXT_DELETE_REVIEWS' => 'Delete %s review(s)',
    'TEXT_INFO_HEADING_DELETE_CUSTOMER' => 'Delete Customer',
    'TEXT_INFO_NUMBER_OF_ORDERS' => 'Number of Orders:',
    'TEXT_INFO_LIFETIME_VALUE' => 'Customer Lifetime Value:',
    'TEXT_INFO_LAST_ORDER' => 'Last Order:',
    'TEXT_INFO_ORDERS_TOTAL' => 'Total:',
    'CUSTOMERS_REFERRAL' => 'Customer Referral<br>1st Discount Coupon',
    'TEXT_INFO_GV_AMOUNT' => 'GV Balance',
    'ENTRY_NONE' => 'None',
    'TABLE_HEADING_COMPANY' => 'Company',
    'TEXT_INFO_HEADING_RESET_CUSTOMER_PASSWORD' => 'Reset Customer Password',
    'TEXT_PWDRESET_INTRO' => 'To reset the password for this customer, enter a new password, and confirm it, below. The new password must conform to the normal password rules imposed on customers.',
    'TEXT_CUST_NEW_PASSWORD' => 'New Password:',
    'TEXT_CUST_CONFIRM_PASSWORD' => 'Confirm Password:',
    'ERROR_PWD_TOO_SHORT' => 'Error: password is shorter than the number of characters configured for this store.',
    'SUCCESS_PASSWORD_UPDATED' => 'Password updated.',
    'EMAIL_CUSTOMER_PWD_CHANGE_MESSAGE' => 'Your password has been changed by the store administrator. Your new password is: ',
    'EMAIL_CUSTOMER_PWD_CHANGE_SUBJECT' => 'Account password reset',
    'EMAIL_CUSTOMER_PWD_CHANGE_MESSAGE_FOR_ADMIN' => 'You have reset the password for a customer: ' . "\n" . '%1$s' . "\n\n" . 'Administrator ID: %2$s',
    'CUSTOMERS_AUTHORIZATION' => 'Customers Authorization Status',
    'CUSTOMERS_AUTHORIZATION_0' => 'Approved',
    'CUSTOMERS_AUTHORIZATION_1' => 'Pending Approval - Must be Authorized to Browse',
    'CUSTOMERS_AUTHORIZATION_2' => 'Pending Approval - May Browse No Prices',
    'CUSTOMERS_AUTHORIZATION_3' => 'Pending Approval - May browse with prices but may not buy',
    'CUSTOMERS_AUTHORIZATION_4' => 'Banned - Not allowed to login or shop',
    'ERROR_CUSTOMER_APPROVAL_CORRECTION1' => 'Warning: Your shop is set up for Approval with No Browse. The customer has been set to Pending Approval - No Browse',
    'ERROR_CUSTOMER_APPROVAL_CORRECTION2' => 'Warning: Your shop is set up for Approval with Browse no prices. The customer has been set to Pending Approval - Browse No Prices',
    'EMAIL_CUSTOMER_STATUS_CHANGE_MESSAGE' => 'Your customer status has been updated. Thank you for shopping with us. We look forward to your business.',
    'EMAIL_CUSTOMER_STATUS_CHANGE_SUBJECT' => 'Customer Status Updated',
    'ADDRESS_BOOK_TITLE' => 'Address Book Entries',
    'PRIMARY_ADDRESS' => '(primary address)',
    'TEXT_MAXIMUM_ENTRIES' => '<span class="coming"><strong>NOTE:</strong></span> A maximum of %s address book entries allowed.',
    'TEXT_INFO_ADDRESS_BOOK_COUNT' => ' | <a href="%1$s">%2$s Entries</a>',
    'TEXT_INFO_ADDRESS_BOOK_COUNT_SINGLE' => '',
    'EMP_BUTTON_PLACEORDER_ALT' => 'Place an order for this customer',
    'EMP_BUTTON_PLACEORDER' => 'Place Order',
    'TEXT_CUSTOMER_GROUPS' => 'Customer Groups',
    'TABLE_HEADING_WHOLESALE_LEVEL' => 'Wholesale Level',
    'TEXT_WHOLESALE_LEVEL' => 'Wholesale Level:',
    'HELPTEXT_WHOLESALE_LEVEL' => 'Enter 0 for "Retail" customers or a "Wholesale" pricing level. A customer can have either a wholesale pricing level or be part of a discount pricing group, but not both.',

    // -----
    // Added, since used by zen_prepare_country_zones_pull_down
    //
    'PLEASE_SELECT' => 'Please select',
    'TYPE_BELOW' => 'Type a choice below ...',
];

return $define;
