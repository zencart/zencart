<?php
/**
 * Page Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: picaflor-azul Sat Jan 9 13:13:41 2016 -0500 New in v1.5.5 $
 */
?>
<div class="centerColumn" id="newProductsDefault">

<h1 id="newProductsDefaultHeading"><?php echo HEADING_TITLE; ?></h1>


<div id="filter-wrapper" class="group">

<?php
/**
 * display the product order dropdown
 */
require($template->get_template_dir('/tpl_modules_listing_display_order.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_listing_display_order.php'); ?>
</div>




<?php
  if (PRODUCT_NEW_LISTING_MULTIPLE_ADD_TO_CART > 0 and $show_submit == true and $products_new_split->number_of_rows > 0) {
?>

<?php
    if ($show_top_submit_button == true or $show_bottom_submit_button == true) {
      echo zen_draw_form('multiple_products_cart_quantity', zen_href_link(FILENAME_PRODUCTS_NEW, zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product', 'SSL'), 'post', 'enctype="multipart/form-data"');
    }
  }
?>



<?php
  $openGroupWrapperDiv = false;
  if (($products_new_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) {
    $openGroupWrapperDiv = true;
?>
<div class="prod-list-wrap group">
<div id="newProductsDefaultListingTopLinks" class="back navSplitPagesLinks"><?php echo TEXT_RESULT_PAGE . $products_new_split->display_links($max_display_page_links, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page')), $paginateAsUL); ?></div>
<div id="newProductsDefaultListingTopNumber" class="navSplitPagesResult back"><?php echo $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW); ?></div>
<?php
  }
?>


<?php
  if ($show_top_submit_button == true) {
// only show when there is something to submit
?>
<?php
if (PREV_NEXT_BAR_LOCATION == '2') {
  echo '<div class="prod-list-wrap group">';
}
?>
<div class="button-top forward"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit1" name="submit1"'); ?></div>

<?php
    if  (PREV_NEXT_BAR_LOCATION == '2') {
         echo '</div>';
       }
?>
<?php
  } // top submit button
?>


<?php
if ($openGroupWrapperDiv) {
  echo '</div>';
}
?>




<?php
/**
 * display the new products
 */
require($template->get_template_dir('/tpl_modules_products_new_listing.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_products_new_listing.php'); ?>



<?php
if ($show_bottom_submit_button == false && PREV_NEXT_BAR_LOCATION == '1') {
  // nothing
} else {
  echo '<div class="prod-list-wrap group">';
}
?>

<?php
  if (($products_new_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
<div id="newProductsDefaultListingBottomLinks" class="navSplitPagesLinks back"><?php echo TEXT_RESULT_PAGE . $products_new_split->display_links($max_display_page_links, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page')), $paginateAsUL); ?></div>
  <div id="newProductsDefaultListingBottomNumber" class="navSplitPagesResult back"><?php echo $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW); ?></div>
<?php
  }
?>

<?php
  if ($show_bottom_submit_button == true) {
// only show when there is something to submit
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
if ($show_bottom_submit_button == false && PREV_NEXT_BAR_LOCATION == '1') {
  // nothing
} else {
  echo '</div>';
}
?>

<?php
// only end form if form is created
    if ($show_top_submit_button == true or $show_bottom_submit_button == true) {
?>
</form>
<?php } // end if form is made ?>
</div>


