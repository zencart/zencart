<?php
/**
 * Common Template - tpl_tabular_display.php
 *
 * This file is used for generating tabular output where needed, based on the supplied array of table-cell contents.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.5.8 $
 */

$zco_notifier->notify('NOTIFY_TPL_TABULAR_DISPLAY_START', $current_page_base, $list_box_contents);

//print_r($list_box_contents);
  $cell_scope = (!isset($cell_scope) || empty($cell_scope)) ? 'col' : $cell_scope;
  $cell_title = (!isset($cell_title) || empty($cell_title)) ? 'list' : $cell_title;

?>
<table id="<?php echo 'cat' . $cPath . 'Table'; ?>" class="tabTable">
<?php
  foreach ($list_box_contents as $row => $cols) {
    $r_params = '';
    $c_params = '';
    if (isset($list_box_contents[$row]['params'])) $r_params .= ' ' . $list_box_contents[$row]['params'];
?>
  <tr <?php echo $r_params; ?>>
<?php
    foreach ($cols as $col) {
      $c_params = '';
      $cell_type = ($row==0) ? 'th' : 'td';
      if (isset($col['params'])) $c_params .= ' ' . $col['params'];
      if (isset($col['align']) && $col['align'] != '') $c_params .= ' align="' . $col['align'] . '"';
      if ($cell_type=='th') $c_params .= ' scope="' . $cell_scope . '" id="' . $cell_title . 'Cell' . $row . '-' . $col.'"';
      if (isset($col['text'])) {
?>
   <?php echo '<' . $cell_type . $c_params . '>'; ?><?php echo $col['text'] ?><?php echo '</' . $cell_type . '>'  . "\n"; ?>
<?php
      }
    }
?>
  </tr>
<?php
  }
?>
</table>
<?php
$zco_notifier->notify('NOTIFY_TPL_TABULAR_DISPLAY_END', $current_page_base, $list_box_contents);
