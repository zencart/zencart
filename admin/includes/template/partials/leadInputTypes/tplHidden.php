<?php
/**
 * Admin Lead Template  - hidden partial
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
       type="hidden"
       name="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
       value="<?php echo htmlspecialchars($tplVars['leadDefinition']['fields'][$field]['value']); ?>" size="<?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['size']; ?>"
       >
