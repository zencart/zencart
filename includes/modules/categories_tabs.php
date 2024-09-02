<?php
/**
 * categories_tabs.php module
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 28 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$order_by = " ORDER BY c.sort_order, cd.categories_name";

$sql = "SELECT c.sort_order, c.categories_id, cd.categories_name
        FROM " . TABLE_CATEGORIES . " c
        LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id'] . ")
        WHERE c.parent_id= " . (int)TOPMOST_CATEGORY_PARENT_ID . "
        AND c.categories_status=1" .
        $order_by;
$categories_tab = $db->Execute($sql);

$links_list = [];
foreach ($categories_tab as $category_tab) {

  // currently selected category
  if ((int)$cPath == $category_tab['categories_id']) {
    $new_style = 'category-top';
    $categories_tab_current = '<span class="category-subs-selected">' . $category_tab['categories_name'] . '</span>';
  } else {
    $new_style = 'category-top';
    $categories_tab_current = $category_tab['categories_name'];
  }

  // create link to top level category
  $links_list[] = '<a class="' . $new_style . '" href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . (int)$category_tab['categories_id']) . '">' . $categories_tab_current . '</a> ';
}
