<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */
//print_r($tplVars['listingBox']);
?>
<div class="centerBoxWrapper" id="id<?php echo $tplVars['listingBox']['className']; ?>">
<h2 class="centerBoxHeading"><?php echo $tplVars['listingBox']['title']; ?></h2>
<?php if ($tplVars['listingBox']['hasFormattedItems']) { ?>
<table width="100%">
<?php if (isset($tplVars['listingBox']['caption'])) { ?>
<caption><?php echo $tplVars['listingBox']['caption']; ?></caption>
<?php } ?>
<thead>
<tr>
<?php foreach ($tplVars['listingBox']['formatter']['headers'] as $header) { ?>
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
</div>
