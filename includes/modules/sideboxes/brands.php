<?php
/**
 * brands sidebox - displays a list of manufacturers so customer can choose to filter on those products only
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Aug 11 New in v1.5.8-alpha $
 */

// test if brands sidebox should show
if ($current_page_base === 'brands') {
    return;
}
if ($current_page_base === 'index' && !empty($_GET['manufacturers_id'])) {
    return;
}

// retrieve with featured items first
$sql = "SELECT manufacturers_name, manufacturers_image, manufacturers_id, featured, (featured=1) as weighted
        FROM " . TABLE_MANUFACTURERS . " m
        ORDER BY weighted DESC, manufacturers_name";
$results = $db->Execute($sql);

if ($results->RecordCount() == 0) return;

$brands_array = array();
foreach ($results as $result) {
    $brands_array[] = [
        'id' => $result['manufacturers_id'],
        'text' => zen_output_string($result['manufacturers_name'], false, true),
        'image' => $result['manufacturers_image'],
        'featured' => $result['featured'],
    ];
}

require($template->get_template_dir('tpl_brands.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_brands.php');
$title = BOX_HEADING_BRANDS;
$title_link = FILENAME_BRANDS;
require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default);
