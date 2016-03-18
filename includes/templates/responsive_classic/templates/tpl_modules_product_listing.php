<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: picaflor-azul Thu Mar 17 12:13:18 2016 -0500  Modified in v1.5.5 $
 */
 include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING));
?>


<div id="productListing" class="group">

<?php
  if ($listing_split->number_of_rows && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') ) {
?>
<div class="prod-list-wrap group">
  <div id="productsListingListingTopLinks" class="navSplitPagesLinks back"><?php echo TEXT_RESULT_PAGE . $listing_split->display_links($max_display_page_links, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page')), $paginateAsUL); ?></div>
  <div id="productsListingTopNumber" class="navSplitPagesResult back<?php echo $listing_split->number_of_pages == 1 ? ' navSplitEmpty3rdColumn' : ''; ?>"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
<?php
}
?>

<?php
// only show when there is something to submit and enabled
    if ($show_top_submit_button == true) {
?>
<?php if (PREV_NEXT_BAR_LOCATION == '2' && $listing_split->number_of_rows) { ?>
  <div class="prod-list-wrap group">
<?php } ?>
    <div class="forward button-top"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit1" name="submit1"'); ?></div>
<?php if (PREV_NEXT_BAR_LOCATION == '2' && $listing_split->number_of_rows) { ?>
  </div>
<?php } ?>

<?php
    } // show top submit
?>

<?php if ($listing_split->number_of_rows && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') ) { ?>
</div>
<?php } ?>


<?php
/**
 * load the list_box_content template to display the products
 */
  require($template->get_template_dir('tpl_tabular_display.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_tabular_display.php');
?>

<?php if ($listing_split->number_of_rows && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') ) { ?>
<div class="prod-list-wrap group">
  <div id="productsListingListingBottomLinks"  class="navSplitPagesLinks back"><?php echo TEXT_RESULT_PAGE . $listing_split->display_links($max_display_page_links, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page')), $paginateAsUL); ?></div>
  <div id="productsListingBottomNumber" class="navSplitPagesResult back<?php echo $listing_split->number_of_pages == 1 ? ' navSplitEmpty3rdColumn' : ''; ?>"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
<?php
  }
?>

<?php
// only show when there is something to submit and enabled
    if ($show_bottom_submit_button == true) {
?>

<?php if (PREV_NEXT_BAR_LOCATION == '1') { ?>
  <div class="prod-list-wrap group button-bottom">
<?php } ?>
    <div class="forward button-top"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"'); ?></div>
<?php if (PREV_NEXT_BAR_LOCATION == '1') { ?>
  </div>
<?php } ?>

<?php
    } // show_bottom_submit_button
?>
<?php if ($listing_split->number_of_rows && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') ) { ?>
</div>
<?php } ?>

</div>

<?php if ($how_many > 0 && PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 and $show_submit == true and $listing_split->number_of_rows > 0) { ?>
</form>
<?php } ?>
