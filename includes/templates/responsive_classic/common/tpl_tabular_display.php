<?php
/**
 * Common Template - tpl_tabular_display.php
 *
 * This file is used for generating tabular output where needed, based on the supplied array of table-cell contents.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2021 Jan 15 Modified in v1.5.8-alpha $
 */
$zco_notifier->notify('NOTIFY_TPL_TABULAR_DISPLAY_START', $current_page_base, $list_box_contents);

$cell_scope = (empty($cell_scope)) ? 'col' : $cell_scope;
$cell_title = (empty($cell_title)) ? 'list' : $cell_title;
?>
<div id="<?php echo 'cat' . $cPath . 'List'; ?>" class="tabTable">
<?php
foreach ($list_box_contents as $row => $cols) {
    $r_params = '';
    if (isset($list_box_contents[$row]['params'])) {
        $r_params .= ' ' . $list_box_contents[$row]['params'];
    }
?>
    <div<?php echo $r_params; ?>>
<?php
    foreach ($cols as $num => $col) {
        $c_params = '';
        $cell_type = ($row == 0) ? 'li' : 'div';
        if (isset($col['params'])) {
            $c_params .= ' ' . $col['params'];
        }
        if (!empty($col['align'])) {
            $c_params .= ' align="' . $col['align'] . '"';
        }
//        if ($cell_type == 'th') {
//            $c_params .= ' scope="' . $cell_scope . '" id="' . $cell_title . 'Cell' . $row . '-' . $num.'"';
//        }
        if (isset($col['text'])) {
            echo $col['text'] . "\n";
        }
    }
?>
    </div>
<?php
}
?>
</div>
<?php
$zco_notifier->notify('NOTIFY_TPL_TABULAR_DISPLAY_END', $current_page_base, $list_box_contents);

