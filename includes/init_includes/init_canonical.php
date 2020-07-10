<?php
/**
 * canonical link handling
 *
 * @package initSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Wed Dec 30 11:49:27 2015 -0500 Modified in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// cPath is excluded by default
$includeCPath = FALSE;

// EXCLUDE certain parameters which should not be included in canonical links:
$excludeParams = array('zenid', 'action', 'main_page', 'currency', 'typefilter', 'gclid', 'search_in_description', 'pto', 'pfrom',
                       'dto', 'dfrom', 'inc_subcat', 'notify', 'edit', 'act', 'method', 'type', 'ec_cancel', 'token', 'addr', 'goto',
                       'authcapt', 'delete', 'goback', 'gv_no', 'markflow', 'nocache', 'override', 'order', 'pos', 'referer', 'tx',
                       'products_tax_class_id', 'set_session_login');
// the following are listed one-per-line to allow for easy commenting-out in case a merchant wants to bypass these exclusions for canonical URL building
$excludeParams[] = 'disp_order';
$excludeParams[] = 'sort';
$excludeParams[] = 'alpha_filter_id';
$excludeParams[] = 'filter_id';
$excludeParams[] = 'utm_source';
$excludeParams[] = 'utm_medium';
$excludeParams[] = 'utm_content';
$excludeParams[] = 'utm_campaign';
$excludeParams[] = 'language';
$excludeParams[] = 'number_of_uploads';

// The following are additional whitelisted params used for sanitizing the generated canonical URL (to prevent rogue params from getting added to canonical maliciously)
$keepableParams = array('page', 'id', 'chapter', 'keyword', 'products_id', 'product_id', 'cPath', 'manufacturers_id', 'categories_id',
                        'order_id', 'faq_item', 'products_image_large_additional', 'cID', 'pid', 'pID', 'reviews_id', 'typefilter');
$keepableParams[] = 'record_company_id';
$keepableParams[] = 'music_genre_id';
$keepableParams[] = 'artists_id';

$zco_notifier->notify ('NOTIFY_INIT_CANONICAL_PARAM_WHITELIST', $current_page, $excludeParams, $keepableParams, $includeCPath);

// Go thru all GET params and prepare list of potentially-rogue keys to not include in generated canonical URL
$rogues = array();
foreach($_GET as $key => $val) {
  if (in_array($key, $excludeParams)) continue; // these will already be stripped, so skip
  if (in_array($key, $keepableParams)) continue; // these are part of navigation etc, so we don't want to strip these, so skip
  $excludeParams[] = $key;
  $rogues[$key] = $val; // this is here as an aid to finding false-positives. Simply uncomment the next line (if sizeof(rogues)) to cause rogues to be output in /logs/myDebug-xxxx.log for review
}
//if (sizeof($rogues)) error_log('Rogue $_GET params, from IP address: ' . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_REFERER'] != '' ? "\nReferrer: " . $_SERVER['HTTP_REFERER'] : '') . "\nURI=" . $_SERVER['REQUEST_URI'] . "\n" . print_r($rogues, true));


$canonicalLink = '';
switch (true) {
/**
 * SSL Pages get no special treatment, since they don't usually require being indexed uniquely differently from non-SSL pages
 */
  case ($request_type == 'SSL' && substr(HTTP_SERVER, 0, 5) != 'https'):
    $canonicalLink = '';
    break;
/**
 * for products (esp those linked to multiple categories):
 */
  case (strstr($current_page, '_info') && isset($_GET['products_id'])):
    $canonicalLink = zen_href_link($current_page, ($includeCPath ? 'cPath=' . zen_get_generated_category_path_rev(zen_get_products_category_id($_GET['products_id'])) . '&' : '') . 'products_id=' . $_GET['products_id'], 'NONSSL', false);
    break;
/**
 * for product listings (ie: "categories"):
 */
  case ($current_page == FILENAME_DEFAULT && isset($_GET['cPath'])):
    $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams), 'NONSSL', false);
// alternate way, depending on specialized site needs:
//    $canonicalLink = zen_href_link($current_page,'cPath=' . zen_get_generated_category_path_rev($current_category_id) , 'NONSSL', false);
    break;
/**
 * for music filters:
 */
  case ($current_page == FILENAME_DEFAULT && isset($_GET['typefilter']) && $_GET['typefilter'] != '' && ( (isset($_GET['music_genre_id']) && $_GET['music_genre_id'] != '' ) || (isset($_GET['record_company_id']) && $_GET['record_company_id'] != '' ) ) ):
    unset($excludeParams[array_search('typefilter', $excludeParams)]);
    $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams), 'NONSSL', false);
    break;
/**
 * home page
 * this translates index.php?main_page=index to just index.php (or whatever zen_href_link is doing)
 */
  case ($this_is_home_page):
    $canonicalLink = preg_replace('/(index.php)(\?)(main_page=)(' . FILENAME_DEFAULT . ')$/', '', zen_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
    break;
/**
 * for new/special/featured listings:
 */
  case (in_array($current_page, array(FILENAME_FEATURED_PRODUCTS, FILENAME_SPECIALS, FILENAME_PRODUCTS_NEW))):
/**
 * for products_all:
 */
  case ($current_page == FILENAME_PRODUCTS_ALL):
/**
 * for manufacturer listings:
 */
  case ($current_page == FILENAME_DEFAULT && isset($_GET['manufacturers_id'])):
/**
 * for ez-pages:
 */
  case ($current_page == FILENAME_EZPAGES && isset($_GET['id'])):
/**
 * all the above cases get treated here:
 */
    $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams), 'NONSSL', false);
    break;
/**
 * All others
 * uncomment the $canonicalLink = ''; line if you want no special handling for other pages
 */
  default:
    $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams), 'NONSSL', false);
    //$canonicalLink = '';
    $zco_notifier->notify ('NOTIFY_INIT_CANONICAL_DEFAULT', $current_page, $excludeParams, $canonicalLink);
}
$zco_notifier->notify ('NOTIFY_INIT_CANONICAL_FINAL', $current_page, $excludeParams, $canonicalLink);

unset($excludeParams, $includeCPath, $rogues);
