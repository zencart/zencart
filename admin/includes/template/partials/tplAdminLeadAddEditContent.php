<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
?>
<div class="large-10 columns">
    <div class="panel">
        <?php echo zen_draw_form('AdminLeadAddEditContentForm', $_GET['cmd'],
            zen_get_all_get_params(array('action')) . 'action=' . $tplVars['leadDefinition'] ['formAction'], 'post',
            ' data-abide ' . $tplVars['leadDefinition']['enctype']) ?>
        <fieldset>
            <legend><?php echo $tplVars['legendTitle']; ?></legend>
            <?php foreach ($tplVars['leadDefinition']['editMap'] as $field) { ?>
                <?php if (isset($tplVars['leadDefinition']['fields'][$field]['layout']['type'])) { ?>
                    <div class="row">
                        <div class="small-3 columns">
                            <?php if ($tplVars['leadDefinition']['fields'][$field]['layout']['type'] != 'hidden') { ?>
                                <label class="inline" for="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>">
                                    <?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['title']; ?>
                                </label>
                            <?php } ?>
                        </div>
                        <div class="small-9 columns">
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
        <input type="submit" class="radius button" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_SUBMIT; ?>"
               tabindex="10">
        </form>
    </div>
</div>
