<?php
/**
 * Admin Lead Template  - language text partial
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<div class="form-group">
    <?php require('includes/template/partials/' . $tplVars['leadDefinition']['inputLabelTemplate']); ?>
    <div class="input-group col-sm-6">
        <input id="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
               class=" form-control <?php echo $tplVars['leadDefinition']['action']; ?>LeadFilterInput  <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) { echo ' error ';}; ?>"
               type="text"
               name="<?php echo $tplVars['leadDefinition']['fields'][$field]['field'].'['.$languageKey .']'; ?>"
               value="<?php echo htmlspecialchars($entry); ?>" size="<?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['size']; ?>"
            <?php if ($tplVars['leadDefinition']['fields'][$field]['validations']['pattern'] !="") echo ' pattern="' . $tplVars['leadDefinition']['fields'][$field]['validations']['pattern'] . '"'; ?>
            <?php echo ($tplVars['leadDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
        >
        <div class="input-group-addon"><?php echo $tplVars['languages'][$languageKey]['name']; ?></div>
    </div>
</div>
