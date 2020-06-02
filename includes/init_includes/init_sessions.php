<?php
/**
 * session handling
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Mar 11 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * sanity check in case zenid has been incorrectly supplied as an htmlencoded param name
 */
if (!isset($_GET['zenid']) && isset($_GET['amp;zenid'])) {
  $_GET['zenid'] = $_GET['amp;zenid'];
  unset($_GET['amp;zenid']);
} else if (isset($_GET['amp;zenid'])) {
  unset($_GET['amp;zenid']);
}

/**
 * require the session handling functions
 */
require(DIR_WS_FUNCTIONS . 'sessions.php');
/**
 * set the session name and save path
 */
zen_session_name('zenid');
zen_session_save_path(SESSION_WRITE_DIRECTORY);
/**
 * set the session cookie parameters
 */
$path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (defined('SESSION_USE_ROOT_COOKIE_PATH') && SESSION_USE_ROOT_COOKIE_PATH  == 'True') $path = '/';
$path = (defined('CUSTOM_COOKIE_PATH')) ? CUSTOM_COOKIE_PATH : $path;
$domainPrefix = (!defined('SESSION_ADD_PERIOD_PREFIX') || SESSION_ADD_PERIOD_PREFIX == 'True') ? '.' : '';
if (filter_var($cookieDomain, FILTER_VALIDATE_IP)) $domainPrefix = '';
$secureFlag = ((ENABLE_SSL == 'true' && substr(HTTP_SERVER, 0, 6) == 'https:' && substr(HTTPS_SERVER, 0, 6) == 'https:') || (ENABLE_SSL == 'false' && substr(HTTP_SERVER, 0, 6) == 'https:')) ? TRUE : FALSE;

session_set_cookie_params(0, $path, (zen_not_null($cookieDomain) ? $domainPrefix . $cookieDomain : ''), $secureFlag, TRUE);

/**
 * set the session ID if it exists
 */
if (isset($_POST[zen_session_name()])) {
  zen_session_id($_POST[zen_session_name()]);
} elseif ( ($request_type == 'SSL') && isset($_GET[zen_session_name()]) ) {
  zen_session_id($_GET[zen_session_name()]);
}
/**
 * Sanitize the IP address, and resolve any proxies.
 */
$ipAddressArray = explode(',', zen_get_ip_address());
$ipAddress = (sizeof($ipAddressArray) > 0) ? $ipAddressArray[0] : '.';
$_SERVER['REMOTE_ADDR'] = $ipAddress;
/**
 * start the session
 */
$session_started = false;
if (SESSION_FORCE_COOKIE_USE == 'True') {
  setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, $path, (zen_not_null($cookieDomain) ? $domainPrefix . $cookieDomain : ''), $secureFlag);

  if (isset($_COOKIE['cookie_test'])) {
    zen_session_start();
    $session_started = true;
  }
} elseif (SESSION_BLOCK_SPIDERS == 'True') {
  $user_agent = '';
  if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
  }
  $spider_flag = false;
  if (zen_not_null($user_agent)) {
    $spiders = file(DIR_WS_INCLUDES . 'spiders.txt');
    for ($i=0, $n=sizeof($spiders); $i<$n; $i++) {
      if (zen_not_null($spiders[$i]) && substr($spiders[$i], 0, 4) != '$Id:') {
        if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
          $spider_flag = true;
          break;
        }
      }
    }
  }
  if ($spider_flag == false) {
    zen_session_start();
    $session_started = true;
  } else {
    if (isset($_GET['zenid']) && $_GET['zenid'] != '') {
      $tmp = (isset($_GET['main_page']) && $_GET['main_page'] != '') ? $_GET['main_page'] : FILENAME_DEFAULT;
      @header("HTTP/1.1 301 Moved Permanently");
      @zen_redirect(@zen_href_link($tmp, @zen_get_all_get_params(array('zenid')), $request_type, FALSE));
      unset($tmp);
      die();
    }
  }
} else {
  zen_session_start();
  $session_started = true;
}
unset($spiders);
/**
 * set host_address once per session to reduce load on server
 */
if (!isset($_SESSION['customers_host_address'])) {
  if (SESSION_IP_TO_HOST_ADDRESS == 'true' || !defined('OFFICE_IP_TO_HOST_ADDRESS')) {
    $_SESSION['customers_host_address']= @gethostbyaddr($_SERVER['REMOTE_ADDR']);
  } else {
    $_SESSION['customers_host_address'] = OFFICE_IP_TO_HOST_ADDRESS;
  }
}
/**
 * verify the ssl_session_id if the feature is enabled
 */
if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == 'true') && ($session_started == true) ) {
  $ssl_session_id = $_SERVER['SSL_SESSION_ID'];
  if (empty($_SESSION['SSL_SESSION_ID'])) {
    $_SESSION['SSL_SESSION_ID'] = $ssl_session_id;
  }
  if ($_SESSION['SSL_SESSION_ID'] != $ssl_session_id) {
    zen_session_destroy();
    zen_redirect(zen_href_link(FILENAME_SSL_CHECK));
  }
}
/**
 * verify the browser user agent if the feature is enabled
 */
if (SESSION_CHECK_USER_AGENT == 'True') {
  $http_user_agent = $_SERVER['HTTP_USER_AGENT'];
  if (empty($_SESSION['SESSION_USER_AGENT'])) {
    $_SESSION['SESSION_USER_AGENT'] = $http_user_agent;
  }
  if ($_SESSION['SESSION_USER_AGENT'] != $http_user_agent) {
    zen_session_destroy();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
}
/**
 * verify the IP address if the feature is enabled
 */
if (SESSION_CHECK_IP_ADDRESS == 'True') {
  $ip_address = zen_get_ip_address();
  if (empty($_SESSION['SESSION_IP_ADDRESS'])) {
    $_SESSION['SESSION_IP_ADDRESS'] = $ip_address;
  }
  if ($_SESSION['SESSION_IP_ADDRESS'] != $ip_address) {
    zen_session_destroy();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
}
