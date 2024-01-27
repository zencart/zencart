<?php
/**
 * manufacturers sidebox - displays a list of manufacturers so customer can choose to filter on their products only
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: highburyeye 2023 Sep 25 Modified in v2.0.0-alpha1 $
 */
// only check products if requested - this may slow down the processing of the manufacturers sidebox
if ((int)PRODUCTS_MANUFACTURERS_STATUS === 1) {
    $manufacturer_sidebox_query =
        "SELECT DISTINCT m.manufacturers_id, m.manufacturers_name
                    FROM " . TABLE_MANUFACTURERS . " m
                            LEFT JOIN " . TABLE_PRODUCTS . " p ON m.manufacturers_id = p.manufacturers_id
                   WHERE p.products_status = 1
                   ORDER BY manufacturers_name";
} else {
    $manufacturer_sidebox_query =
        "SELECT m.manufacturers_id, m.manufacturers_name
           FROM " . TABLE_MANUFACTURERS . " m
           ORDER BY manufacturers_name";
}

$manufacturer_sidebox = $db->Execute($manufacturer_sidebox_query);

if (!$manufacturer_sidebox->EOF) {
    // -----
    // Display a list, noting that the empty ('') selection will not be enabled (via jQuery)
    // if this is the initial display without a previous selection.
    //
    $manufacturer_sidebox_array = [];
    $default_selection = (isset($_GET['manufacturers_id'])) ? (int)$_GET['manufacturers_id'] : '';
    if (!isset($_GET['manufacturers_id']) || $_GET['manufacturers_id'] === '' ) {
        $required = ' required';
        $manufacturer_sidebox_array[] = ['id' => '', 'text' => PULL_DOWN_ALL];
    } else {
        $required = '';
        $manufacturer_sidebox_array[] = ['id' => '', 'text' => PULL_DOWN_MANUFACTURERS];
    }

    foreach ($manufacturer_sidebox as $sidebox_element) {
        $manufacturer_sidebox_name = $sidebox_element['manufacturers_name'];
        if (strlen($manufacturer_sidebox_name) > (int)MAX_DISPLAY_MANUFACTURER_NAME_LEN) {
            $manufacturer_sidebox_name = substr($manufacturer_sidebox_name, 0, (int)MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..';
        }
        $manufacturer_sidebox_array[] = [
            'id' => $sidebox_element['manufacturers_id'],
            'text' => zen_output_string($manufacturer_sidebox_name, false, true),
        ];
    }
    require $template->get_template_dir('tpl_manufacturers_select.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_manufacturers_select.php';

    $title = BOX_HEADING_MANUFACTURERS;
    $title_link = false;
    require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
}
