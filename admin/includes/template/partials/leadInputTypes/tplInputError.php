<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>
<?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) { ?>
    <span class="help-block has-error"><?php echo $tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']][0]; ?></span>
<?php } ?>
