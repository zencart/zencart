<?php
/**
 * document_categories sidebox - displays the categories sidebox containing ONLY "document" products (product type = 3)
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Oct 17 21:23:07 2015 -0400 Modified in v1.5.5 $
 */

    $main_category_tree = new category_tree;
    $row = 0;
    $box_categories_array = array();

// don't build a tree when no categories
    $check_categories = $db->Execute("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCT_TYPES . " pt, " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc where pt.type_master_type = 3 and ptc.product_type_id = pt.type_id and c.categories_id = ptc.category_id and c.categories_status=1 limit 1");
    if ($check_categories->RecordCount() > 0) {
      $box_categories_array = $main_category_tree->zen_category_tree(3);
      require($template->get_template_dir('tpl_document_categories.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_document_categories.php');

      $title = BOX_HEADING_DOCUMENT_CATEGORIES;
      $title_link = false;

      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }

