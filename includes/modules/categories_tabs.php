<?php
/**
 * categories_tabs.php module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 02 Modified in v2.1.0-beta1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
$link_class_active ??= 'category-top';
$link_class_inactive ??= 'category-top';
$span_wrapper_for_active ??= 'category-subs-selected';
$includeAllCategories ??= true;

// BS4 template overrides:
//$link_class_active = 'nav-item nav-link m-1 activeLink';
//$link_class_inactive = 'nav-item nav-link m-1';
//$span_wrap_active = '';
//$includeAllCategories = $zca_include_zero_product_categories ?? true;

$categories_tab_query =
    "SELECT c.sort_order, c.categories_id, cd.categories_name
       FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
      WHERE c.categories_id = cd.categories_id
        AND c.parent_id = " . (int)TOPMOST_CATEGORY_PARENT_ID . "
        AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
        AND c.categories_status = 1
    ORDER BY c.sort_order, cd.categories_name";
$results = $db->Execute($categories_tab_query);

$links_list = [];
$links_list_by_category = [];
$current_category_tab = (int)$cPath;

foreach ($results as $category) {
    // currently selected category
    if ($current_category_tab === (int)$category['categories_id']) {
        $new_style = $link_class_active;
        $categories_tab_current = $category['categories_name'];
        if ($span_wrapper_for_active !== '') {
            $categories_tab_current = '<span class="' . $span_wrapper_for_active . '">' . $categories_tab_current . '</span>';
        }
    } else {
        if (!$includeAllCategories) {
            $count = zen_products_in_category_count($category['categories_id']);
            if ($count === 0) {
                continue;
            }
        }
        $new_style = $link_class_inactive;
        $categories_tab_current = $category['categories_name'];
    }
    // create link to top level category
    $link = '<a class="' . $new_style . '" href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . (int)$category['categories_id']) . '">' . $categories_tab_current . '</a> ';
    $links_list[] = $link;
    // stuff category id into array for later querying; note: we add the 'c' prefix to avoid array renumbering of numeric values (it can be stripped later where used)
    $links_list_by_category['c' . $category['categories_id']] = $link;
}

