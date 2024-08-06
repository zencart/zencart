<?php
/**
 * featured sidebox - displays a random Featured Catelog
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: TMCSherpa 2024 Aug 05 Modified in v2.1.0-alpha1 $
 */

// test if box should display
  $show_featured= true;

  if ($show_featured == true) {
    $random_featured_categories_query = "select p.categories_id, p.categories_image, pd.categories_name
                           from (" . TABLE_CATEGORIES . " p
                           left join " . TABLE_FEATURED_CATEGORIES . " f on p.categories_id = f.categories_id
                           left join " . TABLE_CATEGORIES_DESCRIPTION . " pd on p.categories_id = pd.categories_id )
                           where p.categories_id = f.categories_id
                           and p.categories_id = pd.categories_id
                           and p.categories_status = 1
                           and f.status = 1
                           and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

    // randomly select ONE featured category from the list retrieved:
    //$random_featured_categories = zen_random_select($random_featured_categories_query);
    $random_featured_categories = $db->ExecuteRandomMulti($random_featured_categories_query, MAX_RANDOM_SELECT_FEATURED_CATEGORIES);

    if ($random_featured_categories->RecordCount() > 0)  {
      require($template->get_template_dir('tpl_featured_categories.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_featured_categories.php');
      $title =  BOX_HEADING_FEATURED_CATEGORIES;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
?>
