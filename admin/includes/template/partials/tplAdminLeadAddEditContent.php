<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
?>
<section>
    <div class="panel">
        <?php echo zen_draw_form('AdminLeadAddEditContentForm', $_GET['cmd'],
            zen_get_all_get_params(array('action')) . 'action=' . $tplVars['leadDefinition'] ['formAction'], 'post',
            ' data-abide ' . $tplVars['leadDefinition']['enctype'] . 'class="form-horizontal"') ?>
        <fieldset>
            <legend><?php echo $tplVars['legendTitle']; ?></legend>

            <?php foreach ($tplVars['leadDefinition']['editMap'] as $field) { ?>
                <?php if (isset($tplVars['leadDefinition']['fields'][$field]['layout']['type'])) { ?>
                    <div class="form-group">
                        <div class="col-sm-9">
                            <?php if (isset($tplVars['leadDefinition']['fields'][$field]['language'])) { ?>
                                <?php require('includes/template/partials/tplLanguageTextWrapper.php'); ?>
                            <?php } else { ?>
                                <?php require('includes/template/partials/leadInputTypes/tpl' . ucfirst($tplVars['leadDefinition']['fields'][$field]['layout']['type']) . '.php'); ?>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
            <?php if (isset($tplVars['hiddenFields'])) { ?>
                <?php foreach ($tplVars['hiddenFields'] as $hiddenField) { ?>
                    <input type="hidden" name="<?php echo $hiddenField['field']; ?>" value="<?php echo $hiddenField['value']; ?>">
                <?php } ?>
            <?php } ?>
        </fieldset>
        <input type="submit" class="btn btn-default" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_SUBMIT; ?>" tabindex="10">
        <a href="<?php echo $tplVars['leadDefinition'] ['cancelButtonAction']; ?>">
            <input type="button" class="btn" id="btncancel" name="btncancel" value="<?php echo TEXT_CANCEL; ?>" tabindex="11">
        </a>
        </form>
    </div>
</section>
