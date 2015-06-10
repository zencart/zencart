<?php
/**
 * create the breadcrumb trail
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Modified in v1.6.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$breadcrumb->add(HEADER_TITLE_CATALOG, zen_href_link(FILENAME_DEFAULT));
/**
 * add category names or the manufacturer name to the breadcrumb trail
 */
if (!isset($robotsNoIndex)) $robotsNoIndex = false;
// might need isset(zcRequest::readGet('cPath')) later ... right now need $cPath or breaks breadcrumb from sidebox etc.
if (isset($cPath_array) && isset($cPath)) {
  for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
    $categories_query = "select categories_name
                           from " . TABLE_CATEGORIES_DESCRIPTION . "
                           where categories_id = '" . (int)$cPath_array[$i] . "'
                           and categories_id != ' . (int)TOPMOST_CATEGORY_PARENT_ID . '
                           and language_id = '" . (int)$_SESSION['languages_id'] . "'";
    $categories = $db->Execute($categories_query);

//echo 'I SEE ' . (int)$cPath_array[$i] . '<br>';
    if ($categories->RecordCount() > 0) {
      $breadcrumb->add($categories->fields['categories_name'], zen_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
    } elseif (SHOW_CATEGORIES_ALWAYS == 0) {
      // if invalid, set the robots noindex/nofollow for this page
      $robotsNoIndex = true;
      break;
    }
  }
}
/**
 * add get terms (e.g manufacturer, music genre, record company or other user defined selector) to breadcrumb
 */
$sql = "select *
        from " . TABLE_GET_TERMS_TO_FILTER;
$get_terms = $db->execute($sql);
foreach ($get_terms as $row) {
  if (isset($_GET[$row['get_term_name']])) {
    $sql = "select " . $row['get_term_name_field'] . "
            from " . constant($row['get_term_table']) . "
            where " . $row['get_term_name'] . " =  " . (int)$_GET[$row['get_term_name']];
    $get_term_breadcrumb = $db->execute($sql);
    if ($get_term_breadcrumb->RecordCount() > 0) {
      $breadcrumb->add($get_term_breadcrumb->fields[$row['get_term_name_field']], zen_href_link(FILENAME_DEFAULT, $row['get_term_name'] . "=" . $_GET[$row['get_term_name']]));
    }
  }
}
/**
 * add the products model to the breadcrumb trail
 * NOTE: for query optimization, this query is identical to the query used in the product pages' header_php and main_template_vars files so that it can benefit from caching performance benefits
 */
if (zcRequest::hasGet('products_id')) {
  $sql = "select p.*, pd.*
           from   " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
           where  p.products_status = '1'
           and    p.products_id = '" . (int)zcRequest::readGet('products_id') . "'
           and    pd.products_id = p.products_id
           and    pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
  $res = $db->Execute($sql);
  if ($res->RecordCount() == 0) {
    // invalid product id
    unset($_GET['products_id']);
    $robotsNoIndex = true;
    header('HTTP/1.1 404 Not Found');
  }
  if ($res->fields['products_name'] != '') {
    $breadcrumb->add($res->fields['products_name'], zen_href_link(zen_get_info_page(zcRequest::readGet('products_id')), 'cPath=' . $cPath . '&products_id=' . zcRequest::readGet('products_id')));
  }
}
