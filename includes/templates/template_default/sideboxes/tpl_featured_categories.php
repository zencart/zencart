<?php

/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 25 Modified in v1.5.8-alpha $
 */
$content = "";
$content .= '<div class="sideBoxContent centeredContent">';
$featured_category_box_counter = 0;
while (!$random_featured_categories->EOF) {
    $data = (new Category((int)$random_featured_categories->fields['categories_id']))->withDefaultLanguage()->getData();
    $featured_category_box_counter++;
    $content .= "\n" . '  <div class="sideBoxContentItem">';
    $content .= '<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath='.  $data["categories_id"]). '">'
        . zen_image(DIR_WS_IMAGES . $data['categories_image'], $data['categories_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
    $content .= '<br>' . $data['categories_name'] . '</a>';
    $content .= '</div>';
    $random_featured_categories->MoveNextRandom();
}
$content .= '</div>' . "\n";
