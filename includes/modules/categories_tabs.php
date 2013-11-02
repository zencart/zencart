<?php
/**
 * categories_tabs.php module
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: categories_tabs.php 3018 2006-02-12 21:04:04Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$links_list = array();

if (!defined('TOP_MOST_CATEGORY_PARENT_ID')) define('TOP_MOST_CATEGORY_PARENT_ID', 0);

// 0 is the top-most category level. Use an observer class to intercept this if changes are desired.
$parent_category_id = TOP_MOST_CATEGORY_PARENT_ID;

$zco_notifier->notify('NOTIFY_MODULE_CATEGORIES_TABS_START', CATEGORIES_TABS_STATUS, $links_list, $parent_category_id);

if (CATEGORIES_TABS_STATUS == '1') {

  $order_by = " order by c.sort_order, cd.categories_name ";

  $categories_tab_query = "select c.categories_id, cd.categories_name from " .
                            TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                            where c.categories_id=cd.categories_id and c.parent_id=" . (int)$parent_category_id . "
                            and cd.language_id=" . (int)$_SESSION['languages_id'] . "
                            and c.categories_status = 1" .
                            $order_by;
  $result = $db->Execute($categories_tab_query);

  while (!$result->EOF) {
    $this_cat_id = $href_cPath = (int)$result->fields['categories_id'];
    if ($parent_category_id != TOP_MOST_CATEGORY_PARENT_ID) $href_cPath = str_replace('cPath=', '', zen_get_path($this_cat_id));
    $href = zen_href_link(FILENAME_DEFAULT, 'cPath=' . $href_cPath);
    $current = (bool)((int)$cPath == $result->fields['categories_id']);
    $link_text = $name = $result->fields['categories_name'];
    // adjust markup if this one is the currently-selected category
    if ($current) {
      $link_text = '<span class="category-subs-selected">' . $link_text . '</span>';
    }

    $more = '';
    $li_class = 'cat' . $this_cat_id;
    $a_class = 'category-top cat' . $this_cat_id;
    $zco_notifier->notify('NOTIFY_MODULE_CATEGORIES_TABS_LINKBUILDING', $this_cat_id, $link_text, $href, $name, $current, $li_class, $a_class, $more);

    $links_list[] = array('category' => $this_cat_id,
                          'name' => $name,
                          'current' => $current,
                          'href' => $href,
                          'text' => $link_text,
                          'li-class' => $li_class,
                          'a-class' => $a_class,
                          'more' => $more,
                         );
    $result->MoveNext();
  }
  unset($more, $link_text, $current, $href, $href_cPath, $name);
}
$zco_notifier->notify('NOTIFY_MODULE_CATEGORIES_TABS_LINKS_LIST', CATEGORIES_TABS_STATUS, $links_list);
$hasCatTabLinks = (sizeof($links_list) >= 1);
