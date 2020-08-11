<?php
/**
 * brands header_php.php
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.5.8 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_BRANDS');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(BREADCRUMB_BRANDS, zen_href_link(FILENAME_BRANDS));

$category_depth = 'brands';
$typefilter = $_GET['typefilter'] = 'brands';

$listing_sql = "SELECT manufacturers_name, manufacturers_image, manufacturers_id, featured, (featured=1) as weighted
                FROM " . TABLE_MANUFACTURERS . " m
                ORDER BY weighted DESC, manufacturers_name";

$row = $col = $extra_row = 0;
$list_box_contents = [];
$brands = [];

$listing = $db->Execute($listing_sql);
foreach ($listing as $record) {
    $lc_text = '<a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $record['manufacturers_id']) . '">';

    $image = 'no_picture.gif';
    if ($record['manufacturers_image'] && file_exists(DIR_WS_IMAGES . $record['manufacturers_image'])) {
        $image = $record['manufacturers_image'];
    }
    $lc_text .= '<div class="brandImage">' . zen_image(DIR_WS_IMAGES . $image, $record['manufacturers_name'], 126, 126) . '</div>';

    // only show brand name if there's no image
    // if ($image == 'no_picture.gif') {
        $lc_text .= '<div class="brandName">' . $record['manufacturers_name'] . '</div>';
    // }

    $lc_text .= '</a>';

    $brands[(!empty($record['featured']) ? 'featured' : 'other')][$row][$col] =
        [
            'params' => 'class="brandCell centeredContent col130"',
            'text' => $lc_text,
        ];
    $col++;

    // max 6 cols for brands list
    if ($col >= 6) {
        $col = 0;
        $row++;
    }
}


// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_BRANDS');
