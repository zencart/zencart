<?php
/**
 * column_left module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 08 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$column_box_default='tpl_box_default_left.php';
// Check if there are boxes for the column
$column_left_display= $db->Execute("SELECT layout_box_name FROM " . TABLE_LAYOUT_BOXES . " WHERE layout_box_location = 0 AND layout_box_status= '1' AND layout_template ='" . $template_dir . "'" . ' ORDER BY layout_box_sort_order');
// safety row stop
$box_cnt=0;
$column_width = (int)BOX_WIDTH_LEFT;
while (!$column_left_display->EOF and $box_cnt < 100) {
  $box_cnt++;
  $box_file = DIR_WS_MODULES . zen_get_module_sidebox_directory($column_left_display->fields['layout_box_name']); 
  if (file_exists($box_file)) {
      $box_id = zen_get_box_id($column_left_display->fields['layout_box_name']);
      include($box_file); 
  }
  $column_left_display->MoveNext();
} // while column_left
$box_id = ''; 
