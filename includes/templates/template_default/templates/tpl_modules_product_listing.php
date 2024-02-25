<?php
/**
 * Module Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 31 Modified in v2.0.0-beta1 $
 */
include DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING);
?>
<div id="productListing">
<?php
// only show when there is something to submit and enabled
    if ($show_top_submit_button) {
?>
<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit1" name="submit1"'); ?></div>
<br class="clearBoth">
<?php
    } // show top submit
?>

<?php if ( $listing_split->number_of_rows > 0 && (PREV_NEXT_BAR_LOCATION === '1' || PREV_NEXT_BAR_LOCATION === '3') ) {
?>
<div id="productsListingTopNumber" class="navSplitPagesResult back"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
<div id="productsListingListingTopLinks" class="navSplitPagesLinks forward"><?php echo TEXT_RESULT_PAGE . $listing_split->display_links($max_display_page_links, zen_get_all_get_params(['page', 'info', 'x', 'y', 'main_page']), $paginateAsUL); ?></div>
<br class="clearBoth">
<?php
}
?>

<?php
/**
 * load the list_box_content template to display the products
 */
if (in_array($product_listing_layout_style, ['columns', 'fluid'])) {
  require $template->get_template_dir('tpl_columnar_display.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_columnar_display.php';
} else {
  require $template->get_template_dir('tpl_tabular_display.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_tabular_display.php';
}
?>

<?php if ( $listing_split->number_of_rows > 0 && (PREV_NEXT_BAR_LOCATION === '2' || PREV_NEXT_BAR_LOCATION === '3') ) {
?>
<div id="productsListingBottomNumber" class="navSplitPagesResult back"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
<div  id="productsListingListingBottomLinks" class="navSplitPagesLinks forward"><?php echo TEXT_RESULT_PAGE . $listing_split->display_links($max_display_page_links, zen_get_all_get_params(['page', 'info', 'x', 'y']), $paginateAsUL); ?></div>
<br class="clearBoth">
<?php
  }
?>

<?php
// only show when there is something to submit and enabled
    if ($show_bottom_submit_button) {
?>
<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"'); ?></div>
<br class="clearBoth">
<?php
    } // show_bottom_submit_button
?>
</div>

<?php
// if ($show_top_submit_button || $show_bottom_submit_button || (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 && $show_submit && $listing_split->number_of_rows > 0)) {
  if ($show_top_submit_button || $show_bottom_submit_button) {
?>
</form>
<?php } ?>
