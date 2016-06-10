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
    <?php require('includes/template/partials/' . $tplVars['leadDefinition']['inputLabelTemplate']); ?>
    <div
        class="input-group col-sm-6 <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) {
            echo ' has-error ';
        }; ?>">
        <input id="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
               class="<?php echo $tplVars['leadDefinition']['action']; ?>LeadFilterInput <?php if ($tplVars['leadDefinition']['fields'][$field]['autocomplete']) {
                   echo 'autocomplete';
               } ?> <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) {
                   echo ' error ';
               }; ?>"
               type="file"
               name="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
               value="<?php echo htmlspecialchars($tplVars['leadDefinition']['fields'][$field]['value']); ?>"
               size="<?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['size']; ?>"
            <?php echo ($tplVars['leadDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
        >
        <?php if (isset($tplVars['leadDefinition']['fields'][$field]['layout']['uploadOptions']['imageDirectorySelector']) && $tplVars['leadDefinition']['fields'][$field]['layout']['uploadOptions']['imageDirectorySelector']) { ?>
            <?php echo zen_draw_pull_down_menu($tplVars['leadDefinition']['fields'][$field]['field'] . '_file_select',
                $tplVars['leadDefinition']['fields'][$field]['layout']['uploadOptions']['imageDirectorySelectList']); ?>
        <?php } ?>

        <?php echo '&nbsp;' . htmlspecialchars($tplVars['leadDefinition']['fields'][$field]['value']); ?>
        <br><?php echo zen_info_image($tplVars['leadDefinition']['fields'][$field]['value'], '', 100); ?><br>
    </div>
</div>
