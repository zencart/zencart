<?php
/**
 * Page Template
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jul 15 Modified in v1.5.7 $
 */
?>
<div class="centerColumn" id="reviewsWrite">

<h1 id="reviewsWriteHeading"><?php echo $products_name . $products_model; ?></h1>

<div id="reviews-write-wrapper">
<?php echo zen_draw_form('product_reviews_write', zen_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, 'action=process&products_id=' . $_GET['products_id'], 'SSL'), 'post', 'onsubmit="return checkForm(product_reviews_write);"'); ?>

<div id="pinfo-left" class="group">
<!--bof Main Product Image -->
<?php
  if (zen_not_null($products_image)) {
?>
  <div id="reviewWriteMainImage" class="centeredContent back"><?php
/**
 * display the main product image
 */
   require($template->get_template_dir('/tpl_modules_main_product_image.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_main_product_image.php'); ?>
</div>
<?php
  }
?>
<!--eof Main Product Image-->

<h2 id="reviewsWritePrice"><?php echo $products_price; ?></h2>

<div id="reviewsWriteProductPageLink" class="buttonRow"><?php echo '<a href="' . zen_href_link(zen_get_info_page($_GET['products_id']), zen_get_all_get_params()) . '">' . zen_image_button(BUTTON_IMAGE_GOTO_PROD_DETAILS , BUTTON_GOTO_PROD_DETAILS_ALT) . '</a>'; ?></div>
<div class="buttonRow reviews-page"><?php echo '<a href="' . zen_href_link(FILENAME_REVIEWS) . '">' . zen_image_button(BUTTON_IMAGE_REVIEWS, BUTTON_REVIEWS_ALT) . '</a>'; ?></div>

</div>

<div id="reviews-right">

<h3 id="reviewsWriteReviewer" class=""><?php echo SUB_TITLE_FROM . '&nbsp;&nbsp;', zen_output_string_protected($customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname']); ?></h3>
<br class="clearBoth" />

<?php if ($messageStack->size('review_text') > 0) echo $messageStack->output('review_text'); ?>

<div id="reviewsWriteReviewsRate" class="center"><?php echo SUB_TITLE_RATING; ?></div>

<div class="ratingRow">
<?php echo zen_draw_radio_field('rating', '1', '', 'id="rating-1"'); ?>
<?php echo '<label class="" for="rating-1">' . zen_image($template->get_template_dir(OTHER_IMAGE_REVIEWS_RATING_STARS_ONE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . OTHER_IMAGE_REVIEWS_RATING_STARS_ONE, OTHER_REVIEWS_RATING_STARS_ONE_ALT) . '</label> '; ?>

<?php echo zen_draw_radio_field('rating', '2', '', 'id="rating-2"'); ?>
<?php echo '<label class="" for="rating-2">' . zen_image($template->get_template_dir(OTHER_IMAGE_REVIEWS_RATING_STARS_TWO, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . OTHER_IMAGE_REVIEWS_RATING_STARS_TWO, OTHER_REVIEWS_RATING_STARS_TWO_ALT) . '</label>'; ?>

<?php echo zen_draw_radio_field('rating', '3', '', 'id="rating-3"'); ?>
<?php echo '<label class="" for="rating-3">' . zen_image($template->get_template_dir(OTHER_IMAGE_REVIEWS_RATING_STARS_THREE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . OTHER_IMAGE_REVIEWS_RATING_STARS_THREE, OTHER_REVIEWS_RATING_STARS_THREE_ALT) . '</label>'; ?>

<?php echo zen_draw_radio_field('rating', '4', '', 'id="rating-4"'); ?>
<?php echo '<label class="" for="rating-4">' . zen_image($template->get_template_dir(OTHER_IMAGE_REVIEWS_RATING_STARS_FOUR, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . OTHER_IMAGE_REVIEWS_RATING_STARS_FOUR, OTHER_REVIEWS_RATING_STARS_FOUR_ALT) . '</label>'; ?>

<?php echo zen_draw_radio_field('rating', '5', '', 'id="rating-5"'); ?>
<?php echo '<label class="" for="rating-5">' . zen_image($template->get_template_dir(OTHER_IMAGE_REVIEWS_RATING_STARS_FIVE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . OTHER_IMAGE_REVIEWS_RATING_STARS_FIVE, OTHER_REVIEWS_RATING_STARS_FIVE_ALT) . '</label>'; ?>
</div>


<label id="textAreaReviews" for="review-text"><?php echo SUB_TITLE_REVIEW; ?></label>

<?php echo zen_draw_textarea_field('review_text', 60, 5, '', 'id="review-text"'); ?>
<?php echo zen_draw_input_field($antiSpamFieldName, '', ' size="60" id="RAS" style="visibility:hidden; display:none;" autocomplete="off"'); ?>


<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT); ?></div>

<div id="reviewsWriteReviewsNotice" class="notice clearBoth"><?php echo TEXT_NO_HTML . (REVIEWS_APPROVAL == '1' ? '<br />' . TEXT_APPROVAL_REQUIRED: ''); ?></div>

</form>
</div>

</div>
</div>
