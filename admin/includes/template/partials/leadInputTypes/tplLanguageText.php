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
    <?php require('includes/template/partials/' . $tplVars['pageDefinition']['inputLabelTemplate']); ?>
    <div class="input-group col-sm-6">
        <input id="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>"
               class=" form-control <?php echo $tplVars['pageDefinition']['action']; ?>LeadFilterInput  <?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) { echo ' error ';}; ?>"
               type="text"
               name="<?php echo $tplVars['pageDefinition']['fields'][$field]['field'].'['.$languageKey .']'; ?>"
               value="<?php echo htmlspecialchars($entry); ?>" size="<?php echo $tplVars['pageDefinition']['fields'][$field]['layout']['size']; ?>"
            <?php if ($tplVars['pageDefinition']['fields'][$field]['validations']['pattern'] !="") echo ' pattern="' . $tplVars['pageDefinition']['fields'][$field]['validations']['pattern'] . '"'; ?>
            <?php echo ($tplVars['pageDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
        >
        <?php require('includes/template/partials/' . $tplVars['pageDefinition']['errorTemplate']); ?>
        <div class="input-group-addon"><?php echo $tplVars['pageDefinition']['languages'][$languageKey]['name']; ?></div>
    </div>
</div>
