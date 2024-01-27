<?php
/**
 * brands sidebox - displays a list of manufacturers so customer can choose to filter on those products only
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Sep 24 Modified in v2.0.0-alpha1 $
 */

// test if brands sidebox should show
if ($current_page_base === FILENAME_BRANDS) {
    return;
}
if ($current_page_base === FILENAME_DEFAULT && !empty($_GET['manufacturers_id'])) {
    return;
}

if ((int)PRODUCTS_MANUFACTURERS_STATUS === 1) {
    // retrieve with featured manufacturers first
    $sql =
        "SELECT DISTINCT m.manufacturers_name, m.manufacturers_image, m.manufacturers_id, m.featured, (m.featured=1) AS weighted
           FROM " . TABLE_MANUFACTURERS . " m
                LEFT JOIN " . TABLE_PRODUCTS . " p
                    ON m.manufacturers_id = p.manufacturers_id
          WHERE p.products_status = 1
          ORDER BY weighted DESC, manufacturers_name";
} else {
    // retrieve with featured manufacturers first
    $sql =
        "SELECT m.manufacturers_name, m.manufacturers_image, m.manufacturers_id, m.featured, (m.featured=1) AS weighted
           FROM " . TABLE_MANUFACTURERS . " m
           ORDER BY weighted DESC, manufacturers_name";
}
$results = $db->Execute($sql);

if ($results->EOF) {
    return;
}

$brands_array = [];
foreach ($results as $result) {
    $brands_array[] = [
        'id' => $result['manufacturers_id'],
        'text' => zen_output_string($result['manufacturers_name'], false, true),
        'image' => $result['manufacturers_image'],
        'featured' => $result['featured'],
    ];
}

require $template->get_template_dir('tpl_brands.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_brands.php';
$title = BOX_HEADING_BRANDS;
$title_link = FILENAME_BRANDS;
require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
