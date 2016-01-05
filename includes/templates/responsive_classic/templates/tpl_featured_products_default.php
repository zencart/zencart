<?php
/**
 * Page Template - Featured Products listing
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: picaflor-azul Modified in v1.5.5 $
 */
?>
<div class="centerColumn" id="featuredDefault">

<h1 id="featuredDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<div id="filter-wrapper" class="group">
 <?php
  /**
   * require code to display the list-display-order dropdown
   */
  require($template->get_template_dir('/tpl_modules_listing_display_order.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_listing_display_order.php'); 
?>
</div>

<?php
if (PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART > 0 and $show_submit == true and $featured_products_split->number_of_rows > 0) {
?>

<?php
  if (PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART > 0 and $show_submit == true and $featured_products_split->number_of_rows > 0) {
    if ($show_top_submit_button == true or $show_bottom_submit_button == true) {
      echo zen_draw_form('multiple_products_cart_quantity', zen_href_link(FILENAME_FEATURED_PRODUCTS, zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product'), 'post', 'enctype="multipart/form-data"');
    }
  }
}
?>



<?php

  if (($featured_products_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
<div class="prod-list-wrap group">
<div id="featuredProductsListingTopLinks" class="navSplitPagesLinks back"><?php echo TEXT_RESULT_PAGE . ' ' . $featured_products_split->display_links(($isMobile ? MAX_DISPLAY_PAGE_LINKS_MOBILE : MAX_DISPLAY_PAGE_LINKS), zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page')), $isMobile); ?></div>

<div id="featuredProductsListingTopNumber" class="navSplitPagesResult back"><?php echo $featured_products_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_FEATURED_PRODUCTS); ?></div>
<?php
  }
?>



<?php
// only show button when there is something to submit
  if ($show_top_submit_button == true) {
?>
<?php
      if (PREV_NEXT_BAR_LOCATION == '2') {
	echo '<div class="prod-list-wrap group">';
      }
?>
<div class="forward button-top"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit1" name="submit1"'); ?></div>

<?php
    if  (PREV_NEXT_BAR_LOCATION == '2') {
       echo '</div>';
     }
?>

<?php
  } // end show top button
?>

<?php

    if ($show_top_submit_button == '2' && PREV_NEXT_BAR_LOCATION == '2' or $show_top_submit_button == '0' && PREV_NEXT_BAR_LOCATION == '2') {
  
    }
    else {
      echo '</div>';
    }
?>


<?php
/**
 * display the featured products
 */
require($template->get_template_dir('/tpl_modules_products_featured_listing.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_products_featured_listing.php'); ?>



<?php
    if ($show_bottom_submit_button == false && PREV_NEXT_BAR_LOCATION == '1') {
  
    }
    else {
      echo '<div class="prod-list-wrap group">';
    }
?>

<?php
  if (($featured_products_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
<div id="featuredProductsListingBottomLinks" class="navSplitPagesLinks back"><?php echo TEXT_RESULT_PAGE . ' ' . $featured_products_split->display_links(($isMobile ? MAX_DISPLAY_PAGE_LINKS_MOBILE : MAX_DISPLAY_PAGE_LINKS), zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page')), $isMobile); ?></div>

<div id="featuredProductsListingBottomNumber" class="navSplitPagesResult back"><?php echo $featured_products_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_FEATURED_PRODUCTS); ?></div>
<?php
  }
?>

<?php
  if ($show_bottom_submit_button == true) {
// only show button when there is something to submit
?>

<?php
      if (PREV_NEXT_BAR_LOCATION == '1') {
	echo '<div class="prod-list-wrap group button-bottom">';
      }
?>
<div class="forward button-top"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"'); ?></div>

<?php
 if (PREV_NEXT_BAR_LOCATION == '1') {
echo '</div>';
     }
?>

<?php
    }  // bottom submit button
?>


<?php
  // if ($show_top_submit_button == true or $show_bottom_submit_button == true or (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 and $show_submit == true and $listing_split->number_of_rows > 0)) {
if ($show_top_submit_button == true or $show_bottom_submit_button == true) {
?>
</form>
<?php } ?>

<?php if ($show_top_submit_button == true && $show_bottom_submit_button == true && PREV_NEXT_BAR_LOCATION == '2') {

  } else {
?>

</div>

<?php } ?>

<?php
if ($show_top_submit_button == true && PREV_NEXT_BAR_LOCATION == '2') {
  echo '</div>';
  } ?>


<?php
if ($show_bottom_submit_button == false && PREV_NEXT_BAR_LOCATION == '1' or $show_bottom_submit_button == false && PREV_NEXT_BAR_LOCATION == '2') {
  
}
else {
  echo '</div>';
}
?>
