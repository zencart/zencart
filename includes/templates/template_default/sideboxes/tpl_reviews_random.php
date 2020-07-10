<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Oct 17 21:00:46 2015 -0400 Modified in v1.5.5 $
 */
  $content = "";
  $review_box_counter = 0;
  while (!$random_review_sidebox_product->EOF) {
    $review_box_counter++;
    $content .= '<div class="' . str_replace('_', '-', $box_id . 'Content') . ' sideBoxContent centeredContent">';
    $content .= '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $random_review_sidebox_product->fields['products_id'] . '&reviews_id=' . $random_review_sidebox_product->fields['reviews_id']) . '">' . zen_image(DIR_WS_IMAGES . $random_review_sidebox_product->fields['products_image'], $random_review_sidebox_product->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<br />' . nl2br(zen_trunc_string(zen_output_string_protected(stripslashes($random_review_sidebox_product->fields['reviews_text'])), 60)) . '</a><br /><br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $random_review_sidebox_product->fields['reviews_rating'] . '.gif' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $random_review_sidebox_product->fields['reviews_rating']));
    $content .= '</div>';
    $random_review_sidebox_product->MoveNextRandom();
  }
