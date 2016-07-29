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
<?php require('includes/template/partials/' . $tplVars['leadDefinition']['inputLabelTemplate']); ?>
<div class=" col-sm-10 <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) { echo ' error ';}; ?>">
    <input id="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
           class="form-control <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) {
               echo ' error ';
           }; ?>"
           type="password"
           name="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
           value=""
           size="<?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['size']; ?>"
        <?php if ($tplVars['leadDefinition']['fields'][$field]['validations']['pattern'] != "") {
            echo ' pattern="' . $tplVars['leadDefinition']['fields'][$field]['validations']['pattern'] . '"';
        } ?>
        <?php echo ($tplVars['leadDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
    >
</div>

<label class="col-sm-2 control-label" for="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']. '_confirm'; ?>">
    <?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['title_confirm']; ?>
</label>

<div class=" col-sm-10 <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) { echo ' error ';}; ?>">
    <input id="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>"
           class="form-control <?php if (isset($tplVars['validationErrors'][$tplVars['leadDefinition']['fields'][$field]['field']])) {
               echo ' error ';
           }; ?>"
           type="password"
           name="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']. '_confirm'; ?>"
           value=""
           size="<?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['size']; ?>"
        <?php if ($tplVars['leadDefinition']['fields'][$field]['validations']['pattern'] != "") {
            echo ' pattern="' . $tplVars['leadDefinition']['fields'][$field]['validations']['pattern'] . '"';
        } ?>
        <?php echo ($tplVars['leadDefinition']['fields'][$field]['validations']['required']) ? ' required ' : ''; ?>
    >
</div>
