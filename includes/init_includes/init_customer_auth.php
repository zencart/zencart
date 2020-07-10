<?php
/**
 * customer authorisation based on DOWN_FOR_MAINTENANCE and CUSTOMERS_APPROVAL_AUTHORIZATION settings
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Check if customer's session contains a valid customer_id. If not, then it could be that the administrator has deleted the customer (managing spam etc) so we'll log them out.
 */
if (zen_is_logged_in()) {
  $sql = "select customers_id from " . TABLE_CUSTOMERS . " where customers_id = " . (int)$_SESSION['customer_id'];
  $result = $db->Execute($sql);
  if ($result->RecordCount() == 0) {
    $_SESSION['cart']->reset(true);
    zen_session_destroy();
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
  }
}

$down_for_maint_flag = false;
/**
 * do not let people get to down for maintenance page if not turned on unless is admin in IP list
 */
if (DOWN_FOR_MAINTENANCE=='false' and $_GET['main_page'] == DOWN_FOR_MAINTENANCE_FILENAME && !zen_is_whitelisted_admin_ip()){
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
}
/**
 * see if DFM mode type is defined (strict means all pages blocked, relaxed means logoff/privacy/etc pages are usable)
 */
if (!defined('DOWN_FOR_MAINTENANCE_TYPE')) define('DOWN_FOR_MAINTENANCE_TYPE', 'relaxed');
/**
 * check to see if site is DFM, and set a flag for use later
 */
if (DOWN_FOR_MAINTENANCE == 'true') {
  if (!zen_is_whitelisted_admin_ip()){
    if ($_GET['main_page'] != DOWN_FOR_MAINTENANCE_FILENAME) $down_for_maint_flag = true;
  }
}
/**
 * recheck customer status for authorization
 */
if (zen_is_logged_in()) {
  $check_customer_query = "select customers_id, customers_authorization
                             from " . TABLE_CUSTOMERS . "
                             where customers_id = " . (int)$_SESSION['customer_id'];
  $check_customer = $db->Execute($check_customer_query);
  $_SESSION['customers_authorization'] = $check_customer->fields['customers_authorization'];

  if ($_SESSION['customers_authorization'] == '4') {
    // this account is banned
    $zco_notifier->notify('NOTIFY_LOGIN_BANNED');
    zen_session_destroy();
    zen_redirect(zen_href_link(FILENAME_LOGIN));
  }
  if ($_SESSION['customers_authorization'] != 0 && in_array($_GET['main_page'], array(FILENAME_CHECKOUT_SHIPPING, FILENAME_CHECKOUT_PAYMENT, FILENAME_CHECKOUT_CONFIRMATION))) {
    // this account is not valid for checkout
    global $messageStack;
    $messageStack->add_session('header', TEXT_AUTHORIZATION_PENDING_CHECKOUT, 'caution');
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
  }
}
/**
 * customer login status
 * 0 = normal shopping
 * 1 = Login to shop
 * 2 = Can browse but no prices
 *
 * customer authorization status
 * 0 = normal shopping
 * 1 = customer authorization to shop
 * 2 = customer authorization pending can browse but no prices
 */
switch (true) {
  /**
   * bypass redirects for these scripts, to processing regardless of store mode or cust auth mode
   */
  case (preg_match('|_handler\.php$|', $_SERVER['SCRIPT_NAME'])):
  case (preg_match('|ajax\.php$|', $_SERVER['SCRIPT_NAME'])):
  break;

  case ($down_for_maint_flag && DOWN_FOR_MAINTENANCE_TYPE == 'strict'):
    // if DFM is in strict mode, then block access to all pages:
    zen_redirect(zen_href_link(DOWN_FOR_MAINTENANCE_FILENAME));
  break;

  case ((DOWN_FOR_MAINTENANCE == 'true') && !in_array($_GET['main_page'], array(FILENAME_LOGOFF, FILENAME_PRIVACY, FILENAME_CONTACT_US, FILENAME_CONDITIONS, FILENAME_SHIPPING))):
    // on special pages, if DFM mode is "relaxed", allow access to these pages
    if ($down_for_maint_flag && DOWN_FOR_MAINTENANCE_TYPE == 'relaxed') {
      zen_redirect(zen_href_link(DOWN_FOR_MAINTENANCE_FILENAME));
    }
  break;

  case (in_array($_GET['main_page'], array(FILENAME_LOGOFF, FILENAME_PRIVACY, FILENAME_PASSWORD_FORGOTTEN, FILENAME_CONTACT_US, FILENAME_CONDITIONS, FILENAME_SHIPPING, FILENAME_UNSUBSCRIBE))):
    // on special pages, allow customers to access regardless of store mode or cust auth mode
  break;

/**
 * check store status before authorizations
 */
  case (STORE_STATUS != 0):
    break;
/**
 * if not down for maintenance check login status
 */
  case (CUSTOMERS_APPROVAL == '1' && !zen_is_logged_in()):
  /**
   * customer must be logged in to browse
   */
  if (!in_array($_GET['main_page'], array(FILENAME_LOGIN, FILENAME_CREATE_ACCOUNT))) {
    if (!isset($_GET['set_session_login'])) {
      $_GET['set_session_login'] = 'true';
      $_SESSION['navigation']->set_snapshot();
    }
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  break;
  case (CUSTOMERS_APPROVAL == '2' && !zen_is_logged_in()):
  /**
   * customer may browse but no prices
   */
  break;
  default:
  /**
   * proceed normally
   */
  break;
}

switch (true) {
  /**
   * bypass redirects for these scripts, to processing regardless of store mode or cust auth mode
   */
  case (preg_match('|_handler\.php$|', $_SERVER['SCRIPT_NAME'])):
  case (preg_match('|ajax\.php$|', $_SERVER['SCRIPT_NAME'])):
  break;

/**
 * check store status before authorizations
 */
  case (STORE_STATUS != 0):
    break;

  case (CUSTOMERS_APPROVAL_AUTHORIZATION == '1' && !zen_is_logged_in()):
  /**
   * customer must be logged in to browse
   */
  if (!in_array($_GET['main_page'], array(FILENAME_LOGIN, FILENAME_LOGOFF, FILENAME_CREATE_ACCOUNT, FILENAME_PASSWORD_FORGOTTEN, FILENAME_CONTACT_US, FILENAME_PRIVACY, DOWN_FOR_MAINTENANCE_FILENAME))) {
    if (!isset($_GET['set_session_login'])) {
      $_GET['set_session_login'] = 'true';
      $_SESSION['navigation']->set_snapshot();
    }
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  break;
  case (CUSTOMERS_APPROVAL_AUTHORIZATION == '2' && !zen_is_logged_in()):
  /**
   * customer may browse but no prices unless Authorized
   */
  /*
  if (!in_array($_GET['main_page'], array(FILENAME_LOGIN, FILENAME_CREATE_ACCOUNT))) {
   if (!isset($_GET['set_session_login'])) {
    $_GET['set_session_login'] = 'true';
    $_SESSION['navigation']->set_snapshot();
   }
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  */
  break;
  case (isset($_SESSION['customers_authorization']) && ((CUSTOMERS_APPROVAL_AUTHORIZATION == '1' && $_SESSION['customers_authorization'] != '0') || (int)$_SESSION['customers_authorization'] == 1)):
  /**
   * customer is pending approval
   * customer must be logged in to browse
   * customer is logged in and changed to must be authorized to browse
   */
  if (!in_array($_GET['main_page'], array(FILENAME_LOGIN, FILENAME_LOGOFF, FILENAME_CONTACT_US, FILENAME_PRIVACY))) {
    if ($_GET['main_page'] != CUSTOMERS_AUTHORIZATION_FILENAME) {
      zen_redirect(zen_href_link(preg_replace('/[^a-z_]/', '', CUSTOMERS_AUTHORIZATION_FILENAME)));
    }
  }
  break;
  case (CUSTOMERS_APPROVAL_AUTHORIZATION == '2' and $_SESSION['customers_authorization'] != '0'):
  /**
   * customer may browse but no prices
   */
  break;
  default:
  /**
   * proceed normally
   */
  break;
}

// -----
// If an admin is currently logged into the customer's account, let that admin know who s/he is shopping for.
//
if (isset($_SESSION['emp_admin_id'])) {
    $shopping_for_name = $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'];
    $severity = EMP_SHOPPING_FOR_MESSAGE_SEVERITY;
    if (!in_array($severity, array('success', 'caution', 'warning', 'error'))) {
        $severity = 'success';
    }
    $messageStack->add('header', sprintf(EMP_SHOPPING_FOR_MESSAGE, $shopping_for_name, $_SESSION['emp_customer_email_address']), $severity);
}
