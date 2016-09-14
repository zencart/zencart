<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>
<?php if ($tplVars["messageStack"]->size > 0) { ?>
    <div class="messageStack-header noprint">
        <?php echo $tplVars["messageStack"]->output(); ?>
    </div>
<?php } ?>
