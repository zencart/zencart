<?php
/**
 * Common Template - tpl_columnar_display.php
 *
 * This file is used for generating columnar output where needed, based on the supplied array of table-cell contents.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 29 Modified in v1.5.8-alpha $
 */

$zco_notifier->notify('NOTIFY_TPL_COLUMNAR_DISPLAY_START', $current_page_base, $list_box_contents, $title);
?>

<div class="">

<?php if ($title) { ?>
<?php echo $title; ?>
<?php } ?>

<div class="">
<?php
if (is_array($list_box_contents)) {
    foreach ($list_box_contents as $row => $cols) {

        $r_params = 'class=""';
        if (isset($list_box_contents[$row]['params'])) {
            $r_params = $list_box_contents[$row]['params'];
        }
?>

<div <?php echo $r_params; ?>>
<?php
    foreach ($cols as $col) {
        if ($cols === 'params') {
            continue; // a $cols index named 'params' is only display-instructions ($r_params above) for the row, no data, so skip this iteration
        }

        if (!empty($col['wrap_with_classes'])) { 
            echo '<div class="' . $col['wrap_with_classes'] . '">';
        }

      $c_params = "";
      if (isset($col['params'])) $c_params .= ' ' . (string)$col['params'];
      if (isset($col['text'])) {
            echo '<div' . $c_params . '>' . $col['text'] .  '</div>';
        }

        if (!empty($col['wrap_with_classes'])) { 
            echo '</div>';
      }
      echo PHP_EOL;
    }
?>
</div>
<br class="clearBoth">

<?php
  }
}
?>
</div>
</div>

<?php $zco_notifier->notify('NOTIFY_TPL_COLUMNAR_DISPLAY_END', $current_page_base, $list_box_contents, $title);
