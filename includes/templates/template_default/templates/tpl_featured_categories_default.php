<?php
/**
* Page Template - Featured Categories listing
*
* @copyright Copyright 2003-2024 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: DrByte 2024 Aug 24 New in v2.1.0-alpha2 $
* based on tpl_brands_default
*/
?>
<div class="centerColumn" id="featuredCategoryDefault">

<h1 id="featuredCateoryDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<?php
$list_box_contents = [];
$row = 0;
$col = 0;

$col_width = floor(100 / SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS);

foreach ($listing as $record) {
    $lc_text = '<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . zen_get_generated_category_path_rev($record['categories_id'])) . '">'
             . zen_image(DIR_WS_IMAGES . $record['categories_image'], $record['categories_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
    $lc_text .= '<div class="categoryName">' . $record['categories_name'] . '</div>';
    $lc_text .= '</a>';

    $list_box_contents[$row][$col] = [
        'params' => 'class="centerBoxContentsFeatured centeredContent back"' . ' ' . 'style="width:' . $col_width . '%;"',
        'text' => $lc_text,
    ];

    $col++;
    if ($col >= SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS) {
        $col = 0;
        $row++;
    }
}

$title = '';
require $template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php';
?>
</div>

