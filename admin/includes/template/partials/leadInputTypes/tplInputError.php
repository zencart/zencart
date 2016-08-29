<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 04/05/16
 * Time: 11:10
 */
?>
<?php if (isset($tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']])) { ?>
    <span class="help-block has-error"><?php echo $tplVars['validationErrors'][$tplVars['pageDefinition']['fields'][$field]['field']][0]; ?></span>
<?php } ?>
