<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>
<?php if ($tplVars['pageDefinition']['action'] != 'list') { ?>
    <label class="col-sm-4 control-label" for="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>">
        <?php echo $tplVars['pageDefinition']['fields'][$field]['layout']['title']; ?>
    </label>
<?php } ?>

