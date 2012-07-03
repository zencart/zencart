<?php
/**
 * categories_tabs.php module
 *
 * @package templateSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: categories_tabs.php 3018 2006-02-12 21:04:04Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$order_by = " order by c.sort_order, cd.categories_name ";

$categories_tab_query = "select c.categories_id, cd.categories_name from " .
TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                          where c.categories_id=cd.categories_id and c.parent_id= '0' and cd.language_id='" . (int)$_SESSION['languages_id'] . "' and c.categories_status='1'" .
$order_by;
$categories_tab = $db->Execute($categories_tab_query);

$links_list = array();
while (!$categories_tab->EOF) {

  // currently selected category
  if ((int)$cPath == $categories_tab->fields['categories_id']) {
    $new_style = 'category-top';
    $categories_tab_current = '<span class="category-subs-selected">' . $categories_tab->fields['categories_name'] . '</span>';
  } else {
    $new_style = 'category-top';
    $categories_tab_current = $categories_tab->fields['categories_name'];
  }

  // create link to top level category
  $links_list[] = '<a class="' . $new_style . '" href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . (int)$categories_tab->fields['categories_id']) . '">' . $categories_tab_current . '</a> ';
  $categories_tab->MoveNext();
}

?>