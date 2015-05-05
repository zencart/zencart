<?php
/**
 * Standard pagination template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
//print_r($tplVars['listingBox']['paginator']);
?>
<ul class="pagination">
<?php if ($tplVars['listingBox']['paginator']['flagHasPrevious']) { ?>
<li class="arrow"><a href="<?php echo($tplVars['listingBox']['paginator']['previousLink']) ?>">&laquo;</a></li>
<?php } ?>
<?php foreach($tplVars['listingBox']['paginator']['linkList'] as $item) { ?>
<li <?php echo ($item['isCurrent']) ? 'class="current"' : ''; ?>><a href="<?php echo($item['itemLink']) ?>"><?php echo($item['itemNumber']) ?></a></li>
<?php } ?>
<?php if ($tplVars['listingBox']['paginator']['flagHasNext']) { ?>
<li class="arrow"><a href="<?php echo($tplVars['listingBox']['paginator']['nextLink']) ?>">&raquo;</a></li>
<?php } ?>
</ul>
<div class="navSplitPagesResult"><?php echo(sprintf($tplVars['listingBox']['paginator']['navLinkText'], $tplVars['listingBox']['paginator']['fromItem'], $tplVars['listingBox']['paginator']['toItem'], $tplVars['listingBox']['paginator']['totalItems'])) ?></div>


