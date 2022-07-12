<?php
/**
 * Module Template
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 25 Modified in v1.5.8-alpha $
 */
 ?>
<?php
/**
 * Displays order-totals modules' output
 */
  for ($i=0; $i<$size; $i++) { ?>
<div id="<?php echo str_replace('_', '', $GLOBALS[$class]->code); ?>">
    <div class="totalBox larger forward"><?php echo $GLOBALS[$class]->output[$i]['text']; ?></div>
    <div class="lineTitle larger forward"><?php echo $GLOBALS[$class]->output[$i]['title']; ?></div>
</div>
<br class="clearBoth">
<?php } ?>
