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
//print_r($tplVars['listingBox']);
?>
<div class="centerBoxWrapper" id="id<?php echo $tplVars['listingBox']['className']; ?>">
<h2 class="centerBoxHeading"><?php echo $tplVars['listingBox']['title']; ?></h2>
<?php
    if (isset($tplVars['listingBox']['showForm']) && $tplVars['listingBox']['showForm']) {
      echo zen_draw_form('multiple_products_cart_quantity', zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product'), 'post', 'enctype="multipart/form-data"');
    }
?>
<?php if (isset($tplVars['listingBox']['pagination']) && $tplVars['listingBox']['pagination']['show'] && $tplVars['listingBox']['pagination']['showPaginatorTop']) { ?>
<div><?php require($template->get_template_dir($tplVars['listingBox']['paginatorScrollerTemplate'],DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$tplVars['listingBox']['paginatorScrollerTemplate']); ?></div>
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
<table width="100%">
<?php if (isset($tplVars['listingBox']['caption'])) { ?>
<caption><?php echo $tplVars['listingBox']['caption']; ?></caption>
<?php } ?>
<thead>
<tr>
<?php foreach ($tplVars['listingBox']['headers'] as $header) { ?>
<th <?php echo $header['col_params']; ?>><?php echo $header['title']; ?></th>
<?php } ?>
</tr>
</thead>
<tbody>
<?php foreach ($tplVars['listingBox']['formattedItems'] as $item) { ?>
<tr>
<?php foreach ($item as $colEntry) { ?>
<td <?php echo $colEntry['col_params']; ?>><?php echo $colEntry['value']; ?></td>
<?php } ?>
</tr>
<?php } ?>
</tbody>
</table>
<?php } ?>
<?php if (isset($tplVars['listingBox']['showBottomSubmit']) && $tplVars['listingBox']['showBottomSubmit']) { ?>
  <div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"'); ?></div>
<br class="clearBoth">
<?php } ?>

<?php if (isset($tplVars['listingBox']['pagination']) && $tplVars['listingBox']['pagination']['show'] && $tplVars['listingBox']['pagination']['showPaginatorTop']) { ?>
<div><?php require($template->get_template_dir($tplVars['listingBox']['paginatorScrollerTemplate'],DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$tplVars['listingBox']['paginatorScrollerTemplate']); ?></div>
<?php } ?>

<?php if (isset($tplVars['listingBox']['showForm']) && $tplVars['listingBox']['showForm']) { ?>
</form>
<?php } ?>

</div>
