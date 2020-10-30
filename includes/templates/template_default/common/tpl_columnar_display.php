<?php
/**
 * Common Template - tpl_columnar_display.php
 *
 * This file is used for generating tabular output where needed, based on the supplied array of table-cell contents.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2020 Jul 27 Modified in v1.5.7a $
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
 for($row=0, $n=sizeof($list_box_contents); $row<$n; $row++) {
    $params = "";
    //if (isset($list_box_contents[$row]['params'])) $params .= ' ' . $list_box_contents[$row]['params'];
?>

<?php
    for($col=0, $j=sizeof($list_box_contents[$row]); $col<$j; $col++) {
      $r_params = "";
      if (isset($list_box_contents[$row][$col]['params'])) $r_params .= ' ' . (string)$list_box_contents[$row][$col]['params'];
     if (isset($list_box_contents[$row][$col]['text'])) {
?>
    <?php echo '<div' . $r_params . '>' . $list_box_contents[$row][$col]['text'] .  '</div>' . "\n"; ?>
<?php
      }
    }
?>
<br class="clearBoth" />
<?php
  }
}

$zco_notifier->notify('NOTIFY_TPL_COLUMNAR_DISPLAY_END', $current_page_base, $list_box_contents, $title);
