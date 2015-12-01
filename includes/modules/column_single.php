<?php
/**
 * column_single module 
 *
 * @package templateStructure
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: column_single.php 4274 2006-08-26 03:16:53Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// Check if there are boxes for the column
$column_box_default='tpl_box_default_single.php';
$column_single_display= $db->Execute("select layout_box_name from " . TABLE_LAYOUT_BOXES . " where (layout_box_location=0 or layout_box_location=1) and layout_box_status_single=1 and layout_template ='" . $template_dir . "'" . ' order by LPAD(layout_box_sort_order_single,11,"0")');
// safety row stop
$box_cnt=0;
while (!$column_single_display->EOF and $box_cnt < 100) {
  $box_cnt++;
  if ( file_exists(DIR_WS_MODULES . 'sideboxes/' . $column_single_display->fields['layout_box_name']) or file_exists(DIR_WS_MODULES . 'sideboxes/' . $template_dir . '/' . $column_single_display->fields['layout_box_name']) ) {
?>
<?php
//$column_box_spacer = 'column_box_spacer_single';
if (defined('BOX_WIDTH_SINGLE')) {
  $column_width = BOX_WIDTH_SINGLE;
} else {
  $column_width = BOX_WIDTH_LEFT;
}
if ( file_exists(DIR_WS_MODULES . 'sideboxes/' . $template_dir . '/' . $column_single_display->fields['layout_box_name']) ) {
  $box_id = zen_get_box_id($column_single_display->fields['layout_box_name']);
  require(DIR_WS_MODULES . 'sideboxes/' . $template_dir . '/' . $column_single_display->fields['layout_box_name']);
} else {
  $box_id = zen_get_box_id($column_single_display->fields['layout_box_name']);
  require(DIR_WS_MODULES . 'sideboxes/' . $column_single_display->fields['layout_box_name']);
}
  } // file_exists
  $column_single_display->MoveNext();
} // while column_single
$box_id = '';
?>