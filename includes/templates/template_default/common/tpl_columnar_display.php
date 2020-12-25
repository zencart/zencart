<?php
/**
 * Common Template - tpl_columnar_display.php
 *
 * This file is used for generating tabular output where needed, based on the supplied array of table-cell contents.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.5.8 $
 */

$zco_notifier->notify('NOTIFY_TPL_COLUMNAR_DISPLAY_START', $current_page_base, $list_box_contents, $title);

?>
<?php
  if ($title) {
  ?>
<?php echo $title; ?>
<?php
 }
 ?>
<?php
if (is_array($list_box_contents)) {
    foreach ($list_box_contents as $row => $cols) {
        $params = '';
    //if (isset($list_box_contents[$row]['params'])) $params .= ' ' . $list_box_contents[$row]['params'];
?>

<?php
    foreach ($cols as $col) {
      $r_params = "";
      if (isset($col['params'])) $r_params .= ' ' . (string)$col['params'];
      if (isset($col['text'])) {
?>
    <?php echo '<div' . $r_params . '>' . $col['text'] .  '</div>' . "\n"; ?>
<?php
      }
    }
?>
<br class="clearBoth">
<?php
  }
}

$zco_notifier->notify('NOTIFY_TPL_COLUMNAR_DISPLAY_END', $current_page_base, $list_box_contents, $title);
