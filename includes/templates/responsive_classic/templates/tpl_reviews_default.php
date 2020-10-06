<?php
/**
 * Page Template
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 23 Modified in v1.5.7 $
 */
?>


<script type="text/javascript">
  $(function() {
    $(document).ready(function() {
      $('.grids').matchHeight();
    });
  });
</script>


<div class="centerColumn" id="reviewsDefault">

<h1 id="reviewsDefaultHeading"><?php echo HEADING_TITLE ?></h1>

<?php
  if ($reviews_split->number_of_rows > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>
<div class="prod-list-wrap group">
<div id="reviewsDefaultListingTopNumber" class="navSplitPagesResult back"><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></div>
<div id="reviewsDefaultListingTopLinks" class="navSplitPagesLinks forward"><?php echo TEXT_RESULT_PAGE . $reviews_split->display_links($max_display_page_links, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page')), $paginateAsUL); ?></div>
</div>
<?php
    }

    $reviews = $db->Execute($reviews_split->sql_query);
    while (!$reviews->EOF) {
?>

<div class="reviews-wrapper group">
<div class="smallProductImage back"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews->fields['products_id'] . '&reviews_id=' . $reviews->fields['reviews_id']) . '">' . zen_image(DIR_WS_IMAGES . $reviews->fields['products_image'], $reviews->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>'; ?></div>

<div class="reviews-middle group back">
<h2><?php echo $reviews->fields['products_name']; ?></h2>

<div class="rating"><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $reviews->fields['reviews_rating'] . '.png', sprintf(TEXT_OF_5_STARS, $reviews->fields['reviews_rating'])), sprintf(TEXT_OF_5_STARS, $reviews->fields['reviews_rating']); ?></div>

<div class="content"><?php echo zen_trunc_string(nl2br(zen_output_string_protected(stripslashes($reviews->fields['reviews_text']))), MAX_PREVIEW); ?></div>

<div><?php echo sprintf(TEXT_REVIEW_DATE_ADDED, zen_date_short($reviews->fields['date_added'])); ?>&nbsp;<?php echo sprintf(TEXT_REVIEW_BY, zen_output_string_protected($reviews->fields['customers_name'])); ?></div>
</div>

<div class="back reviews-buttons">
<div class="buttonRow"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews->fields['products_id'] . '&reviews_id=' . $reviews->fields['reviews_id']) . '">' . zen_image_button(BUTTON_IMAGE_READ_REVIEWS , BUTTON_READ_REVIEWS_ALT) . '</a>'; ?></div>
<div class="buttonRow"><?php echo '<a href="' . zen_href_link(zen_get_info_page($reviews->fields['products_id']), 'products_id=' . $reviews->fields['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_GOTO_PROD_DETAILS , BUTTON_GOTO_PROD_DETAILS_ALT) . '</a>'; ?></div>
</div>

</div>


<?php
      $reviews->MoveNext();
    }
?>


<?php
  } else {
?>
<div id="reviewsDefaultNoReviews" class="content"><?php echo TEXT_NO_REVIEWS; ?></div>
<?php
  }
?>


<?php
  if (($reviews_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
<div class="prod-list-wrap group">
<div id="reviewsDefaultListingBottomNumber" class="navSplitPagesResult back"><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></div>
<div id="reviewsDefaultListingBottomLinks" class="navSplitPagesLinks forward"><?php echo TEXT_RESULT_PAGE . $reviews_split->display_links($max_display_page_links, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page')), $paginateAsUL); ?></div>
</div>
<?php
  }
?>

</div>
