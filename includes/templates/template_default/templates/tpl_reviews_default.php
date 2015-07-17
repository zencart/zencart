<?php
/**
 * Page Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: modified in v1.6.0 $
 */
?>
<div class="centerColumn" id="reviewsDefault">

<h1 id="reviewsDefaultHeading"><?php echo HEADING_TITLE ?></h1>

<?php
  if ($tplVars['listingBox']['paginator']['totalItems'] > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>
<?php require($template->get_template_dir($tplVars['listingBox']['paginator']['scrollerTemplate'],DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$tplVars['listingBox']['paginator']['scrollerTemplate']); ?>

<?php
    }

//    $reviews = $db->Execute($reviews_split->sql_query);
    foreach ($tplVars['listingBox']['paginator']['resultList'] as $reviews) {
?>
<hr />

<div class="smallProductImage back"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews['products_id'] . '&reviews_id=' . $reviews['reviews_id']) . '">' . zen_image(DIR_WS_IMAGES . $reviews['products_image'], $reviews['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>'; ?></div>

<div class="forward">
<div class="buttonRow"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews['products_id'] . '&reviews_id=' . $reviews->fields['reviews_id']) . '">' . zen_image_button(BUTTON_IMAGE_READ_REVIEWS , BUTTON_READ_REVIEWS_ALT) . '</a>'; ?></div>
<div class="buttonRow"><?php echo '<a href="' . zen_href_link(zen_get_info_page($reviews['products_id']), 'products_id=' . $reviews['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_GOTO_PROD_DETAILS , BUTTON_GOTO_PROD_DETAILS_ALT) . '</a>'; ?></div>
</div>

<h2><?php echo $reviews->fields['products_name']; ?></h2>

<div class="rating"><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $reviews['reviews_rating'] . '.gif', sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating'])), sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating']); ?></div>

<div class="content"><?php echo zen_break_string(nl2br(zen_output_string_protected(stripslashes($reviews['reviews_text']))), 60, '-<br />') . ((strlen($reviews->fields['reviews_text']) >= 100) ? '...' : ''); ?></div>

<div class="bold"><?php echo sprintf(TEXT_REVIEW_DATE_ADDED, zen_date_short($reviews['date_added'])); ?>&nbsp;<?php echo sprintf(TEXT_REVIEW_BY, zen_output_string_protected($reviews['customers_name'])); ?></div>
<br class="clearBoth" />
<?php
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
  if (($tplVars['listingBox']['paginator']['totalItems'] > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
<hr />
      <?php require($template->get_template_dir($tplVars['listingBox']['paginator']['scrollerTemplate'],DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$tplVars['listingBox']['paginator']['scrollerTemplate']); ?>

      <br class="clearBoth" />
<?php
  }
?>

</div>
