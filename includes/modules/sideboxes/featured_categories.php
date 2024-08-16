<?php
/**
* featured sidebox - displays a random Featured Catelog
*
* @copyright Copyright 2003-2022 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
* based on featured and brands
*/

// test if box should display
$show_featured= true;

if ($show_featured == true) {
    $random_featured_categories_query = "SELECT c.categories_id, c.categories_image, cd.categories_name
                                        FROM (" . TABLE_CATEGORIES . " c
                                        LEFT JOIN " . TABLE_FEATURED_CATEGORIES . " fc on c.categories_id = fc.categories_id
                                        LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id )
                                        WHERE c.categories_id = fc.categories_id
                                        AND c.categories_id = cd.categories_id
                                        AND c.categories_status = 1
                                        AND fc.status = 1
                                        AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

    // randomly select ONE featured category from the list retrieved:
    //$random_featured_categories = zen_random_select($random_featured_categories_query);
    $random_featured_categories = $db->ExecuteRandomMulti($random_featured_categories_query, MAX_RANDOM_SELECT_FEATURED_CATEGORIES);

    if ($random_featured_categories->RecordCount() > 0)  {
        require $template->get_template_dir('tpl_featured_categories.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes') . '/tpl_featured_categories.php';
        $title =  BOX_HEADING_FEATURED_CATEGORIES;
        $title_link = FILENAME_FEATURED_CATEGORIES;
        require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default;
    }
}
?>

