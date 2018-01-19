<?php
/**
 * session handling
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
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
if (zcRequest::hasPost(zen_session_name())) {
  zen_session_id(zcRequest::readPost(zen_session_name()));
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
  $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
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
    // if a spider has a zenid in the URL, redirect it back without the zenid
    if (isset($_GET['zenid']) && $_GET['zenid'] != '') {
      $tmp = zcRequest::readGet('main_page', FILENAME_DEFAULT);
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
 * set host_address (once per session to reduce load on server, since gethostbyaddr() can be slow on many servers)
 */
if (!isset($_SESSION['customers_host_address'])) {
  $_SESSION['customers_host_address'] = (SESSION_IP_TO_HOST_ADDRESS == 'true') ? @gethostbyaddr($_SERVER['REMOTE_ADDR']) : $_SERVER['REMOTE_ADDR'];
}
