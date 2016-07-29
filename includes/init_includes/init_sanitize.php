<?php
/**
 * sanitize the GET parameters
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id Modified in v1.6.0 $
 *
 * @todo move the array process to security class
 */
if (! defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$mainPage = $zcRequest->readGet('main_page', FILENAME_DEFAULT);
zcRequest::set('main_page', $mainPage);
$csrfBlackListLocal = array();
$csrfBlackList = (isset($csrfBlackListCustom)) ? array_merge($csrfBlackListLocal, $csrfBlackListCustom) : $csrfBlackListLocal;
if (! isset($_SESSION ['securityToken'])) {
  $_SESSION ['securityToken'] = md5(uniqid(rand(), true));
}
if ((zcRequest::hasGet('action') || zcRequest::hasPost('action')) && $_SERVER ['REQUEST_METHOD'] == 'POST') {
  if (!$session_started) {
      zen_redirect(zen_href_link(FILENAME_COOKIE_USAGE));
  }
  if (! in_array($mainPage, $csrfBlackList)) {
    if ((! isset($_SESSION ['securityToken']) || ! zcRequest::hasPost('securityToken')) || ($_SESSION ['securityToken'] !== zcRequest::readPost('securityToken'))) {
      zen_redirect(zen_href_link(FILENAME_TIME_OUT, '', $request_type));
    }
  }
}
/**
 * process all $_COOKIE terms
 */
if (isset($_COOKIE) && count($_COOKIE) > 0) {
  foreach ( $_COOKIE as $key => $value ) {
    unset($GLOBALS [$key]);
  }
}
/**
 * process all $_SESSION terms
 */
if (isset($_SESSION) && count($_SESSION) > 0) {
  foreach ( $_SESSION as $key => $value ) {
    unset($GLOBALS [$key]);
  }
}

/**
 * validate products_id for search engines and bookmarks, etc.
 */
if (isset($_GET ['products_id']) && isset($_SESSION ['check_valid']) && $_SESSION ['check_valid'] != 'false') {
  $check_valid = zen_products_id_valid($_GET ['products_id']);
  if (! $check_valid) {
    $mainPage = zen_get_info_page($_GET ['products_id']);
    /**
     * do not recheck redirect
     */
    $_SESSION ['check_valid'] = 'false';
    //zen_redirect(zen_href_link($mainPage, 'products_id=' . $_GET ['products_id']));
  }
} else {
  $_SESSION ['check_valid'] = 'true';
}
/**
 * We do some checks here to ensure zcRequest::readGet('main_page') has a sane value
 */

if (! is_dir(DIR_WS_MODULES . 'pages/' . $mainPage)) {
  if (MISSING_PAGE_CHECK == 'On' || MISSING_PAGE_CHECK == 'true') {
    $mainPage = 'index';
  } elseif (MISSING_PAGE_CHECK == 'Page Not Found') {
    header('HTTP/1.1 404 Not Found');
    $mainPage = FILENAME_PAGE_NOT_FOUND;
  }
}
$current_page = $current_page_base = $mainPage;
$code_page_directory = DIR_WS_MODULES . 'pages/' . $current_page_base;
$page_directory = $code_page_directory;
