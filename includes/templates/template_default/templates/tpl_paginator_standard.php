<?php
/**
 * Standard pagination template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
?>
<ul class="pagination">
<?php if (!$tplVars['listingBox']['pagination']['scroller']['flagHasPrevious']) { ?>
<li class="arrow unavailable"><a href="#">&laquo;</a></li>
<?php } else { ?>
<li class="arrow"><a href="<?php echo($tplVars['listingBox']['pagination']['scroller']['previousLink']) ?>">&laquo;</a></li>
<?php } ?>
<?php foreach($tplVars['listingBox']['pagination']['scroller']['linkList'] as $item) { ?>
<li <?php echo ($item['isCurrent']) ? 'class="current"' : ''; ?>><a href="<?php echo($item['itemLink']) ?>"><?php echo($item['itemNumber']) ?></a></li>
<?php } ?>
<?php if (!$tplVars['pagination']['scroller']['flagHasNext']) { ?>
<li class="arrow unavailable"><a href="#">&raquo;</a></li>
<?php } else { ?>
<li class="arrow"><a href="<?php echo($tplVars['listingBox']['pagination']['scroller']['nextLink']) ?>">&raquo;</a></li>
<?php } ?>
</ul>
<div class="navSplitPagesResult"><?php echo(sprintf($tplVars['listingBox']['pagination']['navLinkText'], $tplVars['listingBox']['pagination']['scroller']['fromItem'], $tplVars['listingBox']['pagination']['scroller']['toItem'], $tplVars['listingBox']['pagination']['totalItems'])) ?></div>


