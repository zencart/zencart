<?php
/**
 * canonical link handling
 *
 * @package initSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_canonical.php 18697 2011-05-04 14:35:20Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// cPath is excluded by default
$includeCPath = FALSE;

// EXCLUDE certain parameters which should not be included in canonical links:
$excludeParams = array('zenid', 'action', 'main_page', 'currency', 'typefilter', 'gclid', 'search_in_description', 'pto', 'pfrom', 'dto', 'dfrom', 'inc_subcat');
$excludeParams[] = 'disp_order';
$excludeParams[] = 'page';
$excludeParams[] = 'sort';
$excludeParams[] = 'alpha_filter_id';
$excludeParams[] = 'filter_id';
$excludeParams[] = 'utm_source';
$excludeParams[] = 'utm_medium';
$excludeParams[] = 'utm_content';
$excludeParams[] = 'utm_campaign';
$excludeParams[] = 'language';

$canonicalLink = '';
switch (TRUE) {
/**
 * SSL Pages get no special treatment, since they're not normally indexed
 */
  case ($request_type == 'SSL'):
    $canonicalLink = '';
    break;
/**
 * for products (esp those linked to multiple categories):
 */
  case (strstr($current_page, '_info') && isset($_GET['products_id'])):
    $canonicalLink = zen_href_link($current_page, ($includeCPath ? 'cPath=' . zen_get_generated_category_path_rev(zen_get_products_category_id($_GET['products_id'])) . '&' : '') . 'products_id=' . $_GET['products_id'], 'NONSSL', false);
    break;
/**
 * for product listings:
 */
  case ($current_page == 'index' && isset($_GET['cPath'])):
    $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams), 'NONSSL', false);
    break;
/**
 * for music products:
 */
  case ($current_page == 'index' && isset($_GET['typefilter']) && $_GET['typefilter'] != '' && ( (isset($_GET['music_genre_id']) && $_GET['music_genre_id'] != '' ) || (isset($_GET['record_company_id']) && $_GET['record_company_id'] != '' ) ) ):
    unset($excludeParams[array_search('typefilter', $excludeParams)]);
    $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams), 'NONSSL', false);
    break;
/**
 * home page
 */
  case ($this_is_home_page):
    $canonicalLink = preg_replace('/(index.php)(\?)(main_page=)(index)$/', '', zen_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
    break;
/**
 * for new/special/featured listings:
 */
  case (in_array($current_page, array('featured_products', 'specials', 'products_new'))):
/**
 * for products_all:
 */
  case ($current_page == 'products_all'):
/**
 * for manufacturer listings:
 */
  case ($current_page == 'index' && isset($_GET['manufacturers_id'])):
/**
 * for ez-pages:
 */
  case ($current_page == 'page' && isset($_GET['id'])):
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
}
unset($excludeParams, $includeCPath);
