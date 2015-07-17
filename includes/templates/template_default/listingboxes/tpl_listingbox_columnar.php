<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
//print_r($tplVars);
?>

<div class="centerBoxWrapper" id="id<?php echo $tplVars['listingBox']['cssElement']; ?>">
<h2 class="centerBoxHeading"><?php echo $tplVars['listingBox']['title']; ?></h2>
<?php if (isset($tplVars['listingBox']['paginator']) && $tplVars['listingBox']['paginator']['show'] && $tplVars['listingBox']['paginator']['showTop']) { ?>
<?php require($template->get_template_dir($tplVars['listingBox']['paginator']['scrollerTemplate'],DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$tplVars['listingBox']['paginator']['scrollerTemplate']); ?>
<?php } ?>
<?php if ($tplVars['listingBox']['hasFormattedItems']) { ?>
<?php for($row=0;$row<sizeof($tplVars['listingBox']['formattedItems']);$row++) { ?>
<?php     for($col=0;$col<sizeof($tplVars['listingBox']['formattedItems'][$row]);$col++) { ?>
<div class="centerBoxContents <?php echo $tplVars['listingBox']['cssElement']; ?> centeredContent back" style="width:<?php echo $tplVars['listingBox']['formattedItems'][$row][$col]['colWidth']; ?>%">
<?php  if ($tplVars['listingBox']['formattedItems'][$row][$col]['useImage']) { ?>
<a href="<?php echo zen_href_link ( zen_get_info_page ($tplVars['listingBox']['formattedItems'][$row][$col]['products_id'] ), 'cPath=' . $tplVars['listingBox']['formattedItems'][$row][$col]['productCpath'] . '&products_id=' . ( int ) $tplVars['listingBox']['formattedItems'][$row][$col]['products_id'] ); ?>">
<?php echo zen_image ( DIR_WS_IMAGES . $tplVars['listingBox']['formattedItems'][$row][$col]['products_image'], $tplVars['listingBox']['formattedItems'][$row][$col]['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT ); ?>
</a><br>
<?php } ?>
<a href="<?php echo zen_href_link ( zen_get_info_page ($tplVars['listingBox']['formattedItems'][$row][$col]['products_id'] ), 'cPath=' . $tplVars['listingBox']['formattedItems'][$row][$col]['productCpath'] . '&products_id=' . $tplVars['listingBox']['formattedItems'][$row][$col]['products_id'] ); ?>"><?php echo $tplVars['listingBox']['formattedItems'][$row][$col]['products_name']; ?></a><br /><?php echo $tplVars['listingBox']['formattedItems'][$row][$col]['displayPrice']; ?>
</div>
<?php } ?><br class="clearBoth" />
<?php } ?>
<?php } ?>
<?php if (isset($tplVars['listingBox']['paginator']) && $tplVars['listingBox']['paginator']['show']  && $tplVars['listingBox']['paginator']['showBottom']) { ?>
<?php require($template->get_template_dir($tplVars['listingBox']['paginator']['scrollerTemplate'],DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$tplVars['listingBox']['paginator']['scrollerTemplate']); ?>
<?php } ?>
</div>
