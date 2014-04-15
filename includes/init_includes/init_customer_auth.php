<?php
/**
 * customer authorisation based on DOWN_FOR_MAINTENANCE and CUSTOMERS_APPROVAL_AUTHORIZATION settings
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$down_for_maint_flag = false;
/**
 * do not let people get to down for maintenance page if not turned on unless is admin in IP list
 */
if (DOWN_FOR_MAINTENANCE=='false' and zcRequest::readGet('main_page') == DOWN_FOR_MAINTENANCE_FILENAME && !strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])){
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
  if (!strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])){
    if (zcRequest::readGet('main_page') != DOWN_FOR_MAINTENANCE_FILENAME) $down_for_maint_flag = true;
  }
}
/**
 * recheck customer status for authorization
 */
if (CUSTOMERS_APPROVAL_AUTHORIZATION > 0 && ($_SESSION['customer_id'] != '' and $_SESSION['customers_authorization'] != '0')) {
  $check_customer_query = "select customers_id, customers_authorization
                             from " . TABLE_CUSTOMERS . "
                             where customers_id = '" . $_SESSION['customer_id'] . "'";
  $check_customer = $db->Execute($check_customer_query);
  $_SESSION['customers_authorization'] = $check_customer->fields['customers_authorization'];
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
  case ($down_for_maint_flag && DOWN_FOR_MAINTENANCE_TYPE == 'strict'):
    // if DFM is in strict mode, then block access to all pages:
    zen_redirect(zen_href_link(DOWN_FOR_MAINTENANCE_FILENAME));
  break;

  case ((DOWN_FOR_MAINTENANCE == 'true') && !in_array(zcRequest::readGet('main_page'), array(FILENAME_LOGOFF, FILENAME_PRIVACY, FILENAME_CONTACT_US, FILENAME_CONDITIONS, FILENAME_SHIPPING))):
    // on special pages, if DFM mode is "relaxed", allow access to these pages
    if ($down_for_maint_flag && DOWN_FOR_MAINTENANCE_TYPE == 'relaxed') {
      zen_redirect(zen_href_link(DOWN_FOR_MAINTENANCE_FILENAME));
    }
  break;

  case (in_array(zcRequest::readGet('main_page'), array(FILENAME_LOGOFF, FILENAME_PRIVACY, FILENAME_PASSWORD_FORGOTTEN, FILENAME_CONTACT_US, FILENAME_CONDITIONS, FILENAME_SHIPPING, FILENAME_UNSUBSCRIBE))):
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
  case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
  /**
   * customer must be logged in to browse
   */
  if (!in_array(zcRequest::readGet('main_page'), array(FILENAME_LOGIN, FILENAME_CREATE_ACCOUNT))) {
    if (!isset($_GET['set_session_login'])) {
      $_GET['set_session_login'] = 'true';
      $_SESSION['navigation']->set_snapshot();
    }
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  break;
  case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
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
 * check store status before authorizations
 */
  case (STORE_STATUS != 0):
    break;
  case (CUSTOMERS_APPROVAL_AUTHORIZATION == '1' and $_SESSION['customer_id'] == ''):
  /**
   * customer must be logged in to browse
   */
//  if (!in_array(zcRequest::readGet('main_page'), array(FILENAME_LOGIN, FILENAME_CREATE_ACCOUNT))) {
  if (!in_array(zcRequest::readGet('main_page'), array(FILENAME_LOGIN, FILENAME_LOGOFF, FILENAME_CREATE_ACCOUNT, FILENAME_PASSWORD_FORGOTTEN, FILENAME_CONTACT_US, FILENAME_PRIVACY))) {
    if (!isset($_GET['set_session_login'])) {
      $_GET['set_session_login'] = 'true';
      $_SESSION['navigation']->set_snapshot();
    }
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  break;
  case (CUSTOMERS_APPROVAL_AUTHORIZATION == '2' and $_SESSION['customer_id'] == ''):
  /**
   * customer may browse but no prices unless Authorized
   */
  /*
  if (!in_array(zcRequest::readGet('main_page'), array(FILENAME_LOGIN, FILENAME_CREATE_ACCOUNT))) {
   if (!isset($_GET['set_session_login'])) {
    $_GET['set_session_login'] = 'true';
    $_SESSION['navigation']->set_snapshot();
   }
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  */
  break;
  case (CUSTOMERS_APPROVAL_AUTHORIZATION == '1' and $_SESSION['customers_authorization'] != '0'):
  /**
   * customer is pending approval
   * customer must be logged in to browse
   */
  if (!in_array(zcRequest::readGet('main_page'), array(FILENAME_LOGIN, FILENAME_LOGOFF, FILENAME_CONTACT_US, FILENAME_PRIVACY))) {
  if (zcRequest::readGet('main_page') != CUSTOMERS_AUTHORIZATION_FILENAME) {
    zen_redirect(zen_href_link(CUSTOMERS_AUTHORIZATION_FILENAME));
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
?>