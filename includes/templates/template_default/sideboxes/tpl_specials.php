<?php

/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 24 Modified in v2.1.0-alpha1 $
 */
$content = "";
$content .= '<div class="sideBoxContent centeredContent">';
$specials_box_counter = 0;
while (!$random_specials_sidebox_product->EOF) {
    $data = array_merge($random_specials_sidebox_product->fields, (new Product((int)$random_specials_sidebox_product->fields['products_id']))->withDefaultLanguage()->getData());
    $specials_box_counter++;
    $specials_box_price = zen_get_products_display_price($data['products_id']);
    $content .= "\n" . '  <div class="sideBoxContentItem">';
    $content .= '<a href="' . zen_href_link(zen_get_info_page($data["products_id"]), 'cPath=' . zen_get_generated_category_path_rev($data["master_categories_id"]) . '&products_id=' . $data["products_id"]) . '">'
        . zen_image(DIR_WS_IMAGES . $data['products_image'], $data['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
    $content .= '<br>' . $data['products_name'] . '</a>';
    $content .= '<div>' . $specials_box_price . '</div>';
    $content .= '</div>';
    $random_specials_sidebox_product->MoveNextRandom();
}
$content .= '</div>' . "\n";
