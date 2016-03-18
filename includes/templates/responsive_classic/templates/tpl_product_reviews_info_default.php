<?php
/**
 * Page Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: picaflor-azul Sun Dec 13 16:32:43 2015 -0500 New in v1.5.5 $
 */
?>
<div class="centerColumn" id="reviewsInfoDefault">

<?php
  if (zen_not_null($products_image)) {
   	/**
     * require the image display code
     */
?>
<div id="reviewsInfoDefaultProductImage" class="centeredContent back"><?php require($template->get_template_dir('/tpl_modules_main_product_image.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_main_product_image.php'); ?></div>
<?php
        }
?>
<div class="forward">
<div class="buttonRow">
<?php
  // more info in place of buy now
  if (zen_has_product_attributes($review_info->fields['products_id'] )) {
    //   $link = '<p>' . '<a href="' . zen_href_link(zen_get_info_page($review_info->fields['products_id']), 'products_id=' . $review_info->fields['products_id'] ) . '">' . MORE_INFO_TEXT . '</a>' . '</p>';
    $link = '';
  } else {
    $link= '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action', 'reviews_id')) . 'action=buy_now') . '">' . zen_image_button(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT) . '</a>';
  }

  $the_button = $link;
  $products_link = '';
  echo zen_get_buy_now_button($review_info->fields['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($review_info->fields['products_id']);
?>
</div>
<div id="reviewsInfoDefaultProductPageLink" class="buttonRow"><?php echo '<a href="' . zen_href_link(zen_get_info_page($_GET['products_id']), zen_get_all_get_params(array('reviews_id'))) . '">' . zen_image_button(BUTTON_IMAGE_GOTO_PROD_DETAILS , BUTTON_GOTO_PROD_DETAILS_ALT) . '</a>'; ?></div>

<div id="reviewsInfoDefaultReviewsListingLink" class="buttonRow"><?php echo ($reviews_counter > 1 ? '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('reviews_id'))) . '">' . zen_image_button(BUTTON_IMAGE_MORE_REVIEWS , BUTTON_MORE_REVIEWS_ALT) . '</a>' : ''); ?></div>

<div class="buttonRow"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, zen_get_all_get_params(array('reviews_id'))) . '">' . zen_image_button(BUTTON_IMAGE_WRITE_REVIEW, BUTTON_WRITE_REVIEW_ALT) . '</a>'; ?></div>
</div>

<h1 id="reviewsInfoDefaultHeading"><?php echo $products_name . $products_model; ?></h1>

<h2 id="reviewsInfoDefaultPrice" class=""><?php echo $products_price; ?></h2>

<div class="reviews-wrapper clearBoth">
<h3 class="rating"><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $review_info->fields['reviews_rating'] . '.png', sprintf(TEXT_OF_5_STARS, $review_info->fields['reviews_rating'])), sprintf(TEXT_OF_5_STARS, $review_info->fields['reviews_rating']); ?></h3>

<div id="reviewsInfoDefaultMainContent" class="content"><?php echo zen_break_string(nl2br(zen_output_string_protected(stripslashes($review_info->fields['reviews_text']))), 60); ?></div>
<div id="reviewsInfoDefaultDate"><?php echo sprintf(TEXT_REVIEW_DATE_ADDED, zen_date_short($review_info->fields['date_added'])); ?>&nbsp;<?php echo sprintf(TEXT_REVIEW_BY, zen_output_string_protected($review_info->fields['customers_name'])); ?></div>

</div>
</div>
