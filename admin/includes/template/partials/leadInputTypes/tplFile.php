<?php
/**
 * Admin Lead Template  - file upload partial
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>

<div class="form-group">
    <?php require('includes/template/partials/' . $tplVars['pageDefinition']['inputLabelTemplate']); ?>
    <div
        class="input-group col-sm-6 <?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) {
            echo ' has-error ';
        }; ?>">
        <input id="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>"
               class="<?php echo $tplVars['pageDefinition']['action']; ?>LeadFilterInput
               } ?> <?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) {
                   echo ' error ';
               }; ?>"
               type="file"
               name="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>"
               value="<?php echo htmlspecialchars($tplVars['pageDefinition']['fields'][$field]['value']); ?>"
               size="<?php echo $tplVars['pageDefinition']['fields'][$field]['layout']['size']; ?>"
            <?php echo ($tplVars['pageDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
        >
        <?php if ($tplVars['pageDefinition']['fields'][$field]['layout']['uploadOptions']['mediaDirectorySelector']) { ?>
            <?php echo zen_draw_pull_down_menu($tplVars['pageDefinition']['fields'][$field]['field'] . '_file_select',
                $tplVars['pageDefinition']['fields'][$field]['layout']['uploadOptions']['imageDirectorySelectList']); ?>
        <?php } ?>
        <?php require('includes/template/' . $tplVars['pageDefinition']['fields'][$field]['layout']['uploadOptions']['mediaPreviewTemplate']); ?>
    </div>
</div>
