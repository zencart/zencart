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
<?php require('includes/template/partials/' . $tplVars['pageDefinition']['inputLabelTemplate']); ?>
<div class=" col-sm-10 <?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) { echo ' error ';}; ?>">
    <input id="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>"
           class="form-control <?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) {
               echo ' error ';
           }; ?>"
           type="password"
           name="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>"
           value=""
           size="<?php echo $tplVars['pageDefinition']['fields'][$field]['layout']['size']; ?>"
        <?php if ($tplVars['pageDefinition']['fields'][$field]['validations']['pattern'] != "") {
            echo ' pattern="' . $tplVars['pageDefinition']['fields'][$field]['validations']['pattern'] . '"';
        } ?>
        <?php echo ($tplVars['pageDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
    >
</div>

<label class="col-sm-2 control-label" for="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']. '_confirm'; ?>">
    <?php echo $tplVars['pageDefinition']['fields'][$field]['layout']['title_confirm']; ?>
</label>

<div class=" col-sm-10 <?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) { echo ' error ';}; ?>">
    <input id="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>"
           class="form-control <?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) {
               echo ' error ';
           }; ?>"
           type="password"
           name="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']. '_confirm'; ?>"
           value=""
           size="<?php echo $tplVars['pageDefinition']['fields'][$field]['layout']['size']; ?>"
        <?php if ($tplVars['pageDefinition']['fields'][$field]['validations']['pattern'] != "") {
            echo ' pattern="' . $tplVars['pageDefinition']['fields'][$field]['validations']['pattern'] . '"';
        } ?>
        <?php echo ($tplVars['pageDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
    >
</div>
