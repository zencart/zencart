<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=create_account.<br />
 * Displays Create Account form.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Modified in V1.6.0 $
 */
//print_r($tplVars['addressEntries']);
?>
<div class="centerColumn" id="createAcctDefault">

<!-- bof Order Steps (tableless) -->
<?php require($template->get_template_dir($checkoutStepsTemplate,DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$checkoutStepsTemplate); ?>
<!-- eof Order Steps (tableless) -->

<h4 id="createAcctDefaultLoginLink"><?php echo sprintf(TEXT_ORIGIN_LOGIN, zen_href_link(FILENAME_LOGIN, zen_get_all_get_params(array('action')), 'SSL')); ?></h4>

<?php echo zen_draw_form('no_account', zen_href_link(FILENAME_CHECKOUT_FLOW, zen_get_all_get_params(), 'SSL'), 'post', 'onsubmit="return check_form(no_account);"') . '<div>' . zen_draw_hidden_field('action', 'process') . zen_draw_hidden_field('email_pref_html', 'email_format'); ?>


<?php if ($messageStack->size('no_account') > 0) echo $messageStack->output('no_account'); ?>

<br class="clearBoth" />
    <?php if ($tplVars['addressEntries']['privacy_conditions']['show']) { ?>
        <fieldset>
            <legend><?php echo TABLE_HEADING_PRIVACY_CONDITIONS; ?></legend>
            <div class="row">
                <div class="small-3 columns">
                    <?php echo TEXT_PRIVACY_CONDITIONS_DESCRIPTION;?>
                </div>
                <div class="small-9 columns">
                    <label for="privacy"><?php echo TEXT_PRIVACY_CONDITIONS_CONFIRM;?></label>
                    <input type="checkbox" name="privacy_conditions" id="privacy" <?php echo ($tplVars['addressEntries']['privacy_conditions']['value'] == 'on' ? 'checked' : '') ?>>
                </div>
            </div>
        </fieldset>
    <?php } ?>

    <?php if ($tplVars['addressEntries']['term_conditions']['show']) { ?>
        <fieldset>
            <legend><?php echo TABLE_HEADING_CONDITIONS; ?></legend>
            <div class="row">
                <div class="small-3 columns">
                    <?php echo TEXT_CONDITIONS_DESCRIPTION;?>
                </div>
                <div class="small-9 columns">
                    <label for="privacy"><?php echo TEXT_CONDITIONS_CONFIRM;?></label>
                    <input type="checkbox" name="term_conditions" id="privacy" <?php echo ($tplVars['addressEntries']['term_conditions']['value'] == 'on' ? 'checked' : '') ?>>
                </div>
            </div>
        </fieldset>
    <?php } ?>

    <fieldset>
        <legend><?php echo TABLE_HEADING_PHONE_FAX_DETAILS; ?></legend>
        <?php if ($tplVars['addressEntries']['email-address']['show']) { ?>
            <div class="row">
                <div class="small-3 columns">
                    <label class="inline" for="email-address"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
                </div>
                <div class="small-9 columns">
                    <input type="text" name="email-address" id="email-address" value="<?php echo$tplVars['addressEntries']['email-address']['value']; ?>">
                </div>
            </div>
        <?php } ?>
        <?php if ($tplVars['addressEntries']['telephone']['show']) { ?>
            <div class="row">
                <div class="small-3 columns">
                    <label class="inline" for="telephone"><?php echo ENTRY_TELEPHONE_NUMBER; ?></label>
                </div>
                <div class="small-9 columns">
                    <input type="text" name="telephone" id="telephone" value="<?php echo$tplVars['addressEntries']['telephone']['value']; ?>">
                </div>
            </div>
        <?php } ?>
        <?php if ($tplVars['addressEntries']['fax']['show']) { ?>
            <div class="row">
                <div class="small-3 columns">
                    <label class="inline" for="fax"><?php echo ENTRY_FAX_NUMBER; ?></label>
                </div>
                <div class="small-9 columns">
                    <input type="text" name="fax" id="fax" value="<?php echo$tplVars['addressEntries']['fax']['value']; ?>">
                </div>
            </div>
        <?php } ?>
    </fieldset>
    <fieldset>
        <legend><?php echo ENTRY_EMAIL_PREFERENCE; ?></legend>
        <?php if ($tplVars['addressEntries']['newsletter']['show']) { ?>
            <div class="row">
                <div class="small-3 columns">
                </div>
                <div class="small-9 columns">
                    <label for="newsletter"><?php echo ENTRY_NEWSLETTER; ?></label>
                    <input type="checkbox" name="newsletter" id="newsletter" <?php echo ($tplVars['addressEntries']['newsletter']['value'] == 'on' ? 'checked' : '') ?>>
                </div>
            </div>
        <?php } ?>
        <?php if ($tplVars['addressEntries']['email_format']['show']) { ?>
            <div class="row">
                <div class="small-3 columns">
                </div>
                <div class="small-9 columns">
                    <label for="email-format-html"><?php echo ENTRY_EMAIL_HTML_DISPLAY; ?></label>
                    <input type="radio" name="email_format" id="email-format-html" value="HTML" <?php echo ($tplVars['addressEntries']['email_format']['value'] == 'HTML' ? 'checked' : '') ?>>
                    <label for="email-format-text"><?php echo ENTRY_EMAIL_TEXT_DISPLAY; ?></label>
                    <input type="radio" name="email_format" id="email-format-text" value="TEXT" <?php echo ($tplVars['addressEntries']['email_format']['value'] == 'TEXT' ? 'checked' : '') ?>>
                </div>
            </div>
        <?php } ?>
    </fieldset>

<div id="checkoutButtons">
  <div id="checkoutButton" class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_CONTINUE_CHECKOUT, BUTTON_CONTINUE_ALT); ?></div>
  <div class="buttonRow back"><?php echo '<strong>' . TITLE_CONTINUE_CHECKOUT_PROCEDURE . '</strong><br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></div>
</div>


</div>
</form>
</div>
