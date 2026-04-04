<?php
/**
 * tpl_brands_default
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 24 Modified in v2.1.0-alpha2 $
 */
?>
<div id="indexBrandsList" class="centerColumn">
    <h1 id="indexBrandsList-pageHeading" class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
<?php
// -----
// Display a message if no brands (aka manufacturers) are defined for the current store.
//
if (empty($brands['featured']) && empty($brands['other'])) {
?>
    <p><?php echo NO_BRANDS_AVAILABLE; ?></p>
<?php
} else {
    // -----
    // Display the list of featured brands, so long as at least one exists.
    //
    if (!empty($brands['featured'])) {
?>
    <div class="featuredBrands">
        <h2><?php echo FEATURED_BRANDS; ?></h2>
<?php
        $list_box_contents = [];
        $row = 0;
        $col = 0;
        $col_width = floor(100 / BRANDS_MAX_COLUMNS);

        foreach ($brands['featured'] as $record) {
            $lc_text = '<a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $record['manufacturers_id']) . '">';
            $lc_text .= '<div class="brandImage">' . zen_image(DIR_WS_IMAGES . $record['manufacturers_image'], $record['manufacturers_name'], BRANDS_IMAGE_WIDTH, BRANDS_IMAGE_HEIGHT) . '</div>';
            $lc_text .= '<div class="brandName">' . $record['manufacturers_name'] . '</div>';
            $lc_text .= '</a>';

            $list_box_contents[$row][$col] = [
                'params' => 'class="brandCell centeredContent"' . ' ' . 'style="width:' . $col_width . '%;"',
                'text' => $lc_text,
            ];

            $col++;
            if ($col >= BRANDS_MAX_COLUMNS) {
                $col = 0;
                $row++;
            }
        }

        $title = '';
        require $template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php';
?>
    </div>
    <div class="clearBoth"></div>
<?php
    }

    // -----
    // Display the list of 'other' brands, so long as at least one exists.
    //
    if (!empty($brands['other'])) {
?>
    <div class="otherBrands">
        <h2><?php echo OTHER_BRANDS; ?></h2>
<?php
        $list_box_contents = [];
        $row = 0;
        $col = 0;
        $col_width = floor(100 / BRANDS_MAX_COLUMNS);

        foreach ($brands['other'] as $record) {
            $lc_text = '<a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $record['manufacturers_id']) . '">';
            $lc_text .= '<div class="brandImage">' . zen_image(DIR_WS_IMAGES . $record['manufacturers_image'], $record['manufacturers_name'], BRANDS_IMAGE_WIDTH, BRANDS_IMAGE_HEIGHT) . '</div>';
            $lc_text .= '<div class="brandName">' . $record['manufacturers_name'] . '</div>';
            $lc_text .= '</a>';

            $list_box_contents[$row][$col] = [
                'params' => 'class="brandCell centeredContent back"' . ' ' . 'style="width:' . $col_width . '%;"',
                'text' => $lc_text,
            ];

            $col++;
            if ($col >= BRANDS_MAX_COLUMNS) {
                $col = 0;
                $row++;
            }
        }

        $title = '';
        require $template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php';
?>
    </div>
<?php
    }
}
?>
</div>
