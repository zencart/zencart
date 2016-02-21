<?php
/**
 * Admin Lead Template  - text partial
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<div <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) { echo ' class="error" ';}; ?>>
<input id="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
       class="<?php echo $tplVars['leadDefinition']['action']; ?>LeadFilterInput <?php if ($tplVars['leadDefinition']['fields'][$field]['autocomplete']) echo 'autocomplete'; ?> <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) { echo ' error ';}; ?>"
       style="width:auto"
       type="text"
       name="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
       value="<?php echo htmlspecialchars($tplVars['leadDefinition']['fields'][$field]['value'], ENT_COMPAT, CHARSET, TRUE); ?>" size="<?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['size']; ?>"
       <?php if ($tplVars['leadDefinition']['fields'][$field]['validations']['pattern'] !="") echo ' pattern="' . $tplVars['leadDefinition']['fields'][$field]['validations']['pattern'] . '"'; ?>
       <?php echo ($tplVars['leadDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
       >
<?php if ($tplVars['leadDefinition']['action'] != 'list') { ?>
<small
class="error"><?php echo $tplVars['leadDefinition']['fields'][$field]['validations']['errorText']; ?></small>
<?php   if ($tplVars['leadDefinition']['fields'][$field]['autocomplete']) { ?>
<?php     if (!isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['custom'])) { ?>
<?php       require('includes/template/javascript/select2DriverStandard.php'); ?>
<?php     } else { ?>
<?php       require('includes/template/javascript/'.$tplVars['leadDefinition']['fields'][$field]['autocomplete']['custom']); ?>
<?php     } ?>
<?php   } ?>
<?php } ?>
</div>
