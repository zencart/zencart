<?php
/**
 * Admin Lead Template  - checkbox partial
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<input id="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
       class="<?php echo $tplVars['leadDefinition']['action']; ?>LeadFilterInput <?php if ($tplVars['leadDefinition']['fields'][$field]['autocomplete']) echo 'autocomplete'; ?>"
       style="width:auto"
       type="checkbox"
       name="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
       value="<?php echo zen_output_string_protected($tplVars['leadDefinition']['fields'][$field]['value']); ?>" size="<?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['size']; ?>"
       >
<?php if ($tplVars['leadDefinition']['action'] != 'list') { ?>
<small class="error"><?php echo $tplVars['leadDefinition']['fields'][$field]['validations']['errorText']; ?></small>
<?php   if ($tplVars['leadDefinition']['fields'][$field]['autocomplete']) { ?>
<?php     if (!isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['custom'])) { ?>
<?php       require('includes/template/javascript/select2DriverStandard.php'); ?>
<?php     } else { ?>
<?php       require('includes/template/javascript/'.$tplVars['leadDefinition']['fields'][$field]['autocomplete']['custom']); ?>
<?php     } ?>
<?php   } ?>
<?php } ?>
