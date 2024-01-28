<?php
/**
 * create the breadcrumb trail
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Aug 03 Modified in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$breadcrumb->add(HEADER_TITLE_CATALOG, zen_href_link(FILENAME_DEFAULT));

/**
 * add category names or the manufacturer name to the breadcrumb trail
 */
$robotsNoIndex = $robotsNoIndex ?? false;

// might need isset($_GET['cPath']) later ... right now need $cPath or breaks breadcrumb from sidebox etc.
if (isset($cPath_array, $cPath)) {
    for ($i = 0, $n = count($cPath_array); $i < $n; $i++) {
        $categories_query =
            "SELECT categories_name
               FROM " . TABLE_CATEGORIES_DESCRIPTION . "
              WHERE categories_id = " . (int)$cPath_array[$i] . "
                AND language_id = " . (int)$_SESSION['languages_id'];
        $categories = $db->Execute($categories_query, 1);

        if (!$categories->EOF) {
            $breadcrumb->add($categories->fields['categories_name'], zen_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i + 1)))));
        } elseif (SHOW_CATEGORIES_ALWAYS === '0') {
            // if invalid, set the robots noindex/nofollow for this page
            $robotsNoIndex = true;
            break;
        }
    }
}

/**
 * add get terms (e.g manufacturer, music genre, record company or other user defined selector) to breadcrumb
 */
$sql =
    "SELECT *
       FROM " . TABLE_GET_TERMS_TO_FILTER;
$get_terms = $db->Execute($sql);
foreach ($get_terms as $next_get_term) {
    $next_get_term_name = $next_get_term['get_term_name'];
    if (isset($_GET[$next_get_term_name])) {
        $sql =
            "SELECT " . $next_get_term['get_term_name_field'] . "
               FROM " . constant($next_get_term['get_term_table']) . "
              WHERE " . $next_get_term_name . " = " . (int)$_GET[$next_get_term_name];
        $get_term_breadcrumb = $db->Execute($sql, 1);

        if (!$get_term_breadcrumb->EOF) {
            // -----
            // Enable a watching observer to modify the parameters to a breadcrumb link.
            //
            $link_parameters = $next_get_term_name . '=' . $_GET[$next_get_term_name];
            $zco_notifier->notify('NOTIFY_INIT_ADD_CRUMBS_GET_TERMS_LINK_PARAMETERS', $next_get_term, $link_parameters);

            $breadcrumb->add($get_term_breadcrumb->fields[$next_get_term['get_term_name_field']], zen_href_link(FILENAME_DEFAULT, $link_parameters));
        }
    }
}

/**
 * add the products name to the breadcrumb trail
 */
if (isset($_GET['products_id'])) {
    $productname_query =
        "SELECT products_name
           FROM " . TABLE_PRODUCTS_DESCRIPTION . "
          WHERE products_id = " . (int)$_GET['products_id'] . "
            AND language_id = " . (int)$_SESSION['languages_id'];
    $productname = $db->Execute($productname_query, 1);

    if (!$productname->EOF) {
        $breadcrumb->add($productname->fields['products_name'], zen_href_link(zen_get_info_page($_GET['products_id']), 'cPath=' . $cPath . '&products_id=' . $_GET['products_id']));
    }
}
