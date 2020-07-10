<?php
/**
 * column_single module 
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 08 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// Check if there are boxes for the column
$column_box_default='tpl_box_default_single.php';
$column_single_display= $db->Execute("SELECT layout_box_name FROM " . TABLE_LAYOUT_BOXES . " WHERE (layout_box_location=0 OR layout_box_location=1) AND layout_box_status_single=1 AND layout_template ='" . $template_dir . "'" . ' ORDER BY LPAD(layout_box_sort_order_single,11,"0")');
// safety row stop
$box_cnt=0;
if (defined('BOX_WIDTH_SINGLE')) {
  $column_width = (int)BOX_WIDTH_SINGLE;
} else {
  $column_width = (int)BOX_WIDTH_LEFT;
}
while (!$column_single_display->EOF and $box_cnt < 100) {
  $box_cnt++;
  $box_file = DIR_WS_MODULES . zen_get_module_sidebox_directory($column_single_display->fields['layout_box_name']); 
  if (file_exists($box_file)) {
    $box_id = zen_get_box_id($column_single_display->fields['layout_box_name']);
    require($box_file); 
  }
  $column_single_display->MoveNext();
} // while column_single
$box_id = '';
