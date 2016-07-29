<?php
/**
 * whos_online functions
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: whos_online.php 6113 2007-04-04 06:11:02Z drbyte $
 */
/**
 * zen_update_whos_online
 */
function zen_update_whos_online() {
  global $db;

  if (isset($_SESSION['customer_id']) && $_SESSION['customer_id']) {
    $wo_customer_id = $_SESSION['customer_id'];

    $customer_query = "select customers_firstname, customers_lastname
                         from " . TABLE_CUSTOMERS . "
                         where customers_id = '" . (int)$_SESSION['customer_id'] . "'";

    $customer = $db->Execute($customer_query);

    $wo_full_name = $customer->fields['customers_lastname'] . ', ' . $customer->fields['customers_firstname'];
  } else {
    $wo_customer_id = '';
    $wo_full_name = '&yen;' . 'Guest';
  }

  $wo_session_id = zen_session_id();
  $wo_ip_address = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown');
  $wo_user_agent = substr(zen_db_prepare_input($_SERVER['HTTP_USER_AGENT']), 0, 254);

	$_SERVER['QUERY_STRING'] = (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') ? $_SERVER['QUERY_STRING'] : zen_get_all_get_params();
  if (isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
   } else {
    if (isset($_SERVER['QUERY_STRING'])) {
     $uri = $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
    } else {
     $uri = $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['argv'][0];
    }
  }
  if (substr($uri, -1)=='?') $uri = substr($uri,0,strlen($uri)-1);
  $wo_last_page_url = (zen_not_null($uri) ? substr($uri, 0, 254) : 'Unknown');

  $current_time = time();
  $xx_mins_ago = ($current_time - 900);

  // remove entries that have expired
  $sql = "delete from " . TABLE_WHOS_ONLINE . "
          where time_last_click < '" . $xx_mins_ago . "'";

  $db->Execute($sql);

  $stored_customer_query = "select count(*) as count
                              from " . TABLE_WHOS_ONLINE . "
                              where session_id = '" . zen_db_input($wo_session_id) . "' and ip_address='" . zen_db_input($wo_ip_address) . "'";

  $stored_customer = $db->Execute($stored_customer_query);

  if (empty($wo_session_id)) {
    $wo_full_name = '&yen;' . 'Spider';
  }

  if ($stored_customer->fields['count'] > 0) {
    $sql = "update " . TABLE_WHOS_ONLINE . "
              set customer_id = '" . (int)$wo_customer_id . "',
                  full_name = '" . zen_db_input($wo_full_name) . "',
                  ip_address = '" . zen_db_input($wo_ip_address) . "',
                  time_last_click = '" . zen_db_input($current_time) . "',
                  last_page_url = '" . zen_db_input($wo_last_page_url) . "',
                  host_address = '" . zen_db_input($_SESSION['customers_host_address']) . "',
                  user_agent = '" . zen_db_input($wo_user_agent) . "'
              where session_id = '" . zen_db_input($wo_session_id) . "' and ip_address='" . zen_db_input($wo_ip_address) . "'";

    $db->Execute($sql);

  } else {
    $sql = "insert into " . TABLE_WHOS_ONLINE . "
                (customer_id, full_name, session_id, ip_address, time_entry,
                 time_last_click, last_page_url, host_address, user_agent)
              values ('" . (int)$wo_customer_id . "', '" . zen_db_input($wo_full_name) . "', '"
                         . zen_db_input($wo_session_id) . "', '" . zen_db_input($wo_ip_address)
                         . "', '" . zen_db_input($current_time) . "', '" . zen_db_input($current_time)
                         . "', '" . zen_db_input($wo_last_page_url)
                         . "', '" . zen_db_input($_SESSION['customers_host_address'])
                         . "', '" . zen_db_input($wo_user_agent)
                         . "')";

    $db->Execute($sql);
  }
}

function whos_online_session_recreate($old_session, $new_session) {
  global $db;

  $sql = "UPDATE " . TABLE_WHOS_ONLINE . "
          SET session_id = :newSessionID 
          WHERE session_id = :oldSessionID";
  $sql = $db->bindVars($sql, ':newSessionID', $new_session, 'string'); 
  $sql = $db->bindVars($sql, ':oldSessionID', $old_session, 'string'); 
  $db->Execute($sql);
}

function zen_wo_get_status_for_sessionid($session_id, $inactive_threshold = WHOIS_TIMER_INACTIVE) {
  global $db;

  // longer than 2 minutes light color
  if ((int)$inactive_threshold < 1) $inactive_threshold = 120;
  $xx_mins_ago_long = (time() - (int)$inactive_threshold);

  $which_query = $db->Execute("select sesskey, value
                               from " . TABLE_SESSIONS . "
                               where sesskey= '" . $db->prepare_input($session_id) . "'");

  $who_query = $db->Execute("select session_id, time_entry, time_last_click, host_address, user_agent
                             from " . TABLE_WHOS_ONLINE . "
                             where session_id='" . $db->prepare_input($session_id) . "'");

  $session_data = base64_decode($which_query->fields['value']);
  
  switch (true) {
    case ($which_query->RecordCount() == 0):
    if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
      return 3;
    } else {
      return 2;
    }

    case (strstr($session_data,'"contents";a:0:')):
    if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
      return 3;
    } else {
      return 2;
    }

    case (!strstr($session_data,'"contents";a:0:')):
    if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
      return 1;
    } else {
      return 0;
    }

  }
}

function zen_wo_get_visitor_status_icon($status) {
  switch($status) {
    case 3:
      return zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif');
    case 2:
      return zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
    case 1:
      return zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif');
    case 0:
      return zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
  }
}

/**
 * repatriate $_SESSION data for the specified session id
 */
function zen_wo_get_session_data($session_id) {
    global $db;
    $result = $db->Execute("select value from " . TABLE_SESSIONS . "
                            WHERE sesskey = '" . $db->prepare_input($session_id) . "'");
    $session_data = trim($result->fields['value']);

    if ($session_data == '') return false;

    // -----bof accommodate suhosin-----
    $hardenedStatus = false;
    $suhosinExtension = extension_loaded('suhosin');
    $suhosinSetting = strtoupper(@ini_get('suhosin.session.encrypt'));

//    if (!$suhosinExtension) {
      if (strpos($session_data, 'cart|O') === 0) $session_data = base64_decode($session_data);
      if (strpos($session_data, 'cart|O') === 0) $session_data = '';
//    }

    // uncomment the following line if you have suhosin enabled and see errors on the cart-contents sidebar
    //$hardenedStatus = ($suhosinExtension == true || $suhosinSetting == 'On' || $suhosinSetting == 1) ? true : false;
    if ($session_data != '' && $hardenedStatus == true) $session_data = '';
    // -----eof accommodate suhosin-----

    if (strlen($session_data)) {
      $start_id = (int)strpos($session_data, 'customer_id|s');
      $start_currency = (int)strpos($session_data, 'currency|s');
      $start_country = (int)strpos($session_data, 'customer_country_id|s');
      $start_zone = (int)strpos($session_data, 'customer_zone_id|s');
      $start_cart = (int)strpos($session_data, 'cart|O');
      $end_cart = (int)strpos($session_data, '|', $start_cart+6);
      $end_cart = (int)strrpos(substr($session_data, 0, $end_cart), ';}');

      $session_data_id = substr($session_data, $start_id, (strpos($session_data, ';', $start_id) - $start_id + 1));
      $session_data_cart = substr($session_data, $start_cart, ($end_cart - $start_cart+2));
      $session_data_currency = substr($session_data, $start_currency, (strpos($session_data, ';', $start_currency) - $start_currency + 1));
      $session_data_country = substr($session_data, $start_country, (strpos($session_data, ';', $start_country) - $start_country + 1));
      $session_data_zone = substr($session_data, $start_zone, (strpos($session_data, ';', $start_zone) - $start_zone + 1));

      session_decode($session_data_id);
      session_decode($session_data_currency);
      session_decode($session_data_country);
      session_decode($session_data_zone);
      session_decode($session_data_cart);

      if (is_object($_SESSION['cart'])) {
        return $_SESSION['cart'];
      }
    }
    return false;
}
