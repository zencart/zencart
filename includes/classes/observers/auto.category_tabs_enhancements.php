<?php
/**
 * @package plugins
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Designed for v1.6.0  $
 */

class zcObserverCategoryTabsEnhancements extends base {

  function __construct() {
    $this->attach($this, array('NOTIFY_MODULE_CATEGORIES_TABS_START', 'NOTIFY_MODULE_CATEGORIES_TABS_LINKBUILDING', 'NOTIFY_MODULE_CATEGORIES_TABS_LINKS_LIST'));
  }

  function updateNotifyModuleCategoriesTabsStart(&$class, $eventID, $status, &$links_list, &$parent_category_id)
  {
    global $current_category_id, $cPath, $cPath_array;

    // To make category-tabs be sensitive to the current page when browsing within a given subcategory, uncomment the following section:
//     if (zen_has_category_subcategories($current_category_id)) {
//       $parent_category_id = $current_category_id;
//     } else {
//       $parent_category_id = $cPath_array[(sizeof($cPath_array)-2)];
//     }


    // optionally pre-populate the $links_list array. Be sure to observe the correct format.
/*
    // EXAMPLE
    $links_list[] = array('category' => '',
                          'name' => 'TEST',
                          'current' => FALSE,
                          'href' => 'index.php',
                          'text' => 'text',
                          'li-class' => 'cat000',
                          'a-class' => 'category-top cat000',
                          'more' => '',
                        );
*/
  }

  function updateNotifyModuleCategoriesTabsLinkbuilding(&$class, $eventID, $this_cat_id, &$link_text, &$href, &$name, &$current, &$li_class, &$a_class, &$more)
  {
    global $current_page_base, $current_category_id;
/*
    // To change what happens when building specific tabs, adjust the incoming variables, keeping in mind how they're used as shown here:
    $links_list[] = array('category' => $result->fields['categories_id'],
                          'name' => $name,
                          'current' => $current,
                          'href' => $href,
                          'text' => $link_text,
                          'li-class' => $li_class,
                          'a-class' => $a_class,
                          'more' => $more,
                          );
*/
    // add your conditional logic to update those variables below this line. Note: You can't update $links_list directly here, only the other variables passed to this function using &.



  }

  function updateNotifyModuleCategoriesTabsLinksList(&$class, $eventID, $status, &$links_list)
  {
    global $current_page_base;
    // Optionally add additional links to the beginning/end of the $links_list array. Be sure to observe the same format as the existing entries in the array.

/*
    // Example for adding Contact-Us link to Category-Tabs list:
    $span_class = ($current_page_base == 'contact_us') ? 'category-subs-selected' : 'category-subs-unselected';
    $link_text = '<span class="' . $span_class . '">' . BOX_INFORMATION_CONTACT . '</span></a> ';
    $links_list[] = array('category' => '',
                          'name' => BOX_INFORMATION_CONTACT,
                          'current' => ($current_page_base == 'contact_us'),
                          'href' => zen_href_link(FILENAME_CONTACT_US),
                          'text' => $link_text,
                          'li-class' => '',
                          'a-class' => 'category-top',
                          'more' => '',
                          );
*/

  }
}
