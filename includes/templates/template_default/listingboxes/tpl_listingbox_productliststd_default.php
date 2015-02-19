<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */
//print_r($tplVars);
?>

<div class="centerBoxWrapper" id="centerBoxWrapper<?php echo $tplVars['listingBox']['className']; ?>">
  <h2 class="centerBoxHeading"><?php echo $tplVars['listingBox']['title']; ?></h2>
<?php
    if (isset($tplVars['listingBox']['showForm']) && $tplVars['listingBox']['showForm']) {
      echo zen_draw_form('multiple_products_cart_quantity', zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product'), 'post', 'enctype="multipart/form-data"');
    }
?>
<?php if (isset($tplVars['listingBox']['pagination']) && $tplVars['listingBox']['pagination']['show'] && $tplVars['listingBox']['pagination']['showTop']) { ?>
<?php require($template->get_template_dir($tplVars['listingBox']['pagination']['scrollerTemplate'],DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$tplVars['listingBox']['pagination']['scrollerTemplate']); ?>
<?php } ?>
<?php
// only show button when there is something to submit
  if (isset($tplVars['listingBox']['showTopSubmit']) && $tplVars['listingBox']['showTopSubmit']) {
?>
<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit1" name="submit1"'); ?></div>
<br class="clearBoth">
<?php
  } // end show top button
?>
<?php if ($tplVars['listingBox']['hasFormattedItems']) { ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
      <td colspan="3"><hr /></td>
    </tr>
<?php foreach ($tplVars['listingBox']['formattedItems'] as $item) { ?>
          <tr>
            <td width="<?php echo $item['imagelistingWidth'] + 10; ?>" valign="top" class="main" align="center">

<?php foreach($item['displayProductColOne'] as $productEntity) {  ?>
  <?php echo $productEntity; ?>
<?php } ?>

            </td>
            <td colspan="2" valign="top" class="main">
<?php foreach($item['displayProductColTwo'] as $productEntity) {  ?>
  <?php echo $productEntity; ?>
<?php } ?>
            </td>
          </tr>
<?php if ($item['displayProductsDescription'] != '') { ?>
          <tr>
            <td colspan="3" valign="top" class="main">
              <?php
                echo $item['displayProductsDescription'];
              ?>
            </td>
          </tr>
<?php } ?>
          <tr>
            <td colspan="3"><hr /></td>
          </tr>

<?php } ?>

</table>
<?php } ?>

<?php if (isset($tplVars['listingBox']['showBottomSubmit']) && $tplVars['listingBox']['showBottomSubmit']) { ?>
  <div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"'); ?></div>
<br class="clearBoth">
<?php } ?>
<?php if (isset($tplVars['listingBox']['pagination']) && $tplVars['listingBox']['pagination']['show'] && $tplVars['listingBox']['pagination']['showBottom']) { ?>
<?php require($template->get_template_dir($tplVars['listingBox']['pagination']['scrollerTemplate'],DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$tplVars['listingBox']['pagination']['scrollerTemplate']); ?>
<?php } ?>
<?php if (isset($tplVars['listingBox']['showForm']) && $tplVars['listingBox']['showForm']) { ?>
</form>
<?php } ?>

</div>
