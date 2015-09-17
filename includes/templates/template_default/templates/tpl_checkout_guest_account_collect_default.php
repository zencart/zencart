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
 * @version $Id: Integrated COWOA v2.2 - 2007 - 2012
 * @version $Id: Modified in V1.6.0 $
 */
//print_r($tplVars['addressEntries']);
?>
<div class="centerColumn" id="createAcctDefault">
<!-- bof Order Steps (tableless) -->
<?php require($template->get_template_dir($checkoutStepsTemplate,DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'.$checkoutStepsTemplate); ?>
<!-- eof Order Steps (tableless) -->

<h4 id="createAcctDefaultLoginLink"><?php echo sprintf(TEXT_ORIGIN_LOGIN, zen_href_link(FILENAME_LOGIN, zen_get_all_get_params(array('action')), 'SSL')); ?></h4>


    <?php if ($messageStack->size('no_account') > 0) {
        echo $messageStack->output('no_account');
    } ?>



    <form id="guest_account" name="guest_account" method="post" action="<?php echo zen_href_link(FILENAME_CHECKOUT_FLOW, zen_get_all_get_params(), 'SSL'); ?>" >
    <input type="hidden" name="action" value="process" >
    <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>" >

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


<?php if ($tplVars['addressEntries']['company']['show']) { ?>
    <fieldset>
        <legend><?php echo CATEGORY_COMPANY; ?></legend>
        <div class="row">
            <div class="small-3 columns">
            </div>
            <div class="small-9 columns error">
                <input type="text" name="company" id="company" value="<?php echo $tplVars['addressEntries']['company']['value']; ?>" >
            </div>
        </div>
    </fieldset>
<?php } ?>
    <fieldset>
        <legend><?php echo TABLE_HEADING_ADDRESS_DETAILS; ?></legend>
        <?php if ($tplVars['addressEntries']['gender']['show']) { ?>
            <div class="row">
                <div class="small-3 columns">

                </div>
                <div class="small-9 columns">
                    <label class="radioButtonLabel" for="gender-male"><?php echo MALE; ?></label>
                    <input type="radio" name="gender" id="gender-male" value="m" <?php echo ($tplVars['addressEntries']['gender']['value'] == 'm' ? 'checked' : '') ?>>
                    <label class="radioButtonLabel" for="gender-female"><?php echo FEMALE; ?></label>
                    <input type="radio" name="gender" id="gender-female" value="f" <?php echo ($tplVars['addressEntries']['gender']['value'] == 'f' ? 'checked' : '') ?>>
                </div>
            </div>
        <?php } ?>
        <?php if ($tplVars['addressEntries']['firstname']['show']) { ?>
        <div class="row">
            <div class="small-3 columns">
                <label class="inline" for="firstname"><?php echo ENTRY_FIRST_NAME; ?></label>
            </div>
            <div class="small-9 columns">
                <input type="text" name="firstname" id="firstname" value="<?php echo $tplVars['addressEntries']['firstname']['value']; ?>">
            </div>
        </div>
        <?php } ?>
        <?php if ($tplVars['addressEntries']['lastname']['show']) { ?>
        <div class="row">
            <div class="small-3 columns">
                <label class="inline" for="lastname"><?php echo ENTRY_LAST_NAME; ?></label>
            </div>
            <div class="small-9 columns">
                <input type="text" name="lastname" id="lastname" value="<?php echo $tplVars['addressEntries']['lastname']['value']; ?>">
            </div>
        </div>
        <?php } ?>
        <?php if ($tplVars['addressEntries']['street-address']['show']) { ?>
        <div class="row">
            <div class="small-3 columns">
                <label class="inline" for="street-address"><?php echo ENTRY_STREET_ADDRESS; ?></label>
            </div>
            <div class="small-9 columns">
                <input type="text" name="street-address" id="street-address" value="<?php echo $tplVars['addressEntries']['street-address']['value']; ?>">
            </div>
        </div>
        <?php } ?>
        <?php if ($tplVars['addressEntries']['suburb']['show']) { ?>
            <div class="row">
                <div class="small-3 columns">
                    <label class="inline" for="suburb"><?php echo ENTRY_SUBURB; ?></label>
                </div>
                <div class="small-9 columns">
                    <input type="text" name="suburb" id="suburb" value="<?php echo $tplVars['addressEntries']['suburb']['value']; ?>">
                </div>
            </div>
        <?php } ?>
        <?php if ($tplVars['addressEntries']['city']['show']) { ?>
        <div class="row">
            <div class="small-3 columns">
                <label class="inline" for="city"><?php echo ENTRY_CITY; ?></label>
            </div>
            <div class="small-9 columns">
                <input type="text" name="city" id="city" value="<?php echo $tplVars['addressEntries']['city']['value']; ?>">
            </div>
        </div>
        <?php } ?>


        <?php if ($tplVars['addressEntries']['state']['show']) { ?>
            <?php if ($tplVars['addressEntries']['state']['showPullDown']) { ?>
                <div class="row">
                    <div class="small-3 columns">
                        <label class="inlime" for="state" id="stateLabel"><?php echo ENTRY_STATE; ?></label>
                    </div>
                    <div class="small-9 columns">
                        <?php echo zen_draw_pull_down_menu('zone_id', zen_prepare_country_zones_pull_down($tplVars['addressEntries']['zone_country_id']['value']), $tplVars['addressEntries']['state']['zone_id'], 'id="stateZone"'); ?>
                    </div>
                </div>
            <?php } ?>

        <div class="row">
            <div class="small-3 columns">
                <label class="inlime" for="state" id="stateLabel"><?php echo $tplVars['addressEntries']['state']['label']; ?></label>
            </div>
            <div class="small-9 columns">
                <input type="text" name="state" id="state" value="<?php echo $tplVars['addressEntries']['state']['value']; ?>">
            </div>
         </div>
        <?php } ?>


        <?php if ($tplVars['addressEntries']['postcode']['show']) { ?>
        <div class="row">
            <div class="small-3 columns">
                <label class="inline" for="postcode"><?php echo ENTRY_POST_CODE; ?></label>
            </div>
            <div class="small-9 columns">
                <input type="text" name="postcode" id="postcode" value="<?php echo $tplVars['addressEntries']['postcode']['value']; ?>">
            </div>
        </div>
        <?php } ?>


        <?php if ($tplVars['addressEntries']['zone_country_id']['show']) { ?>
        <div class="row">
            <div class="small-3 columns">
                <label class="inline" for="country"><?php echo ENTRY_COUNTRY; ?></label>
            </div>
            <div class="small-9 columns">
                <?php echo zen_get_country_list('zone_country_id', $tplVars['addressEntries']['zone_country_id']['value'],
                        'id="country" ' . ($flag_show_pulldown_states == true ? 'onchange="update_zone(this.form);"' : '')); ?>

            </div>
        </div>
        <?php } ?>
    </fieldset>


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

    <?php if ($tplVars['addressEntries']['customers_referral']['show']) { ?>
    <fieldset>
        <legend><?php echo TABLE_HEADING_REFERRAL_DETAILS; ?></legend>
        <div class="row">
            <div class="small-3 columns">
                <label class="inline" for="customers_referral><?php echo ENTRY_CUSTOMERS_REFERRAL; ?></label>
            </div>
            <div class="small-9 columns">
                <input type="text" name="customers_referral" id="customers_referral" value="<?php echo $addressEntries['customers_referral']['value']; ?>">
            </div>
        </div>
    </fieldset>
    <?php } ?>

<div id="checkoutButtons">
  <div id="checkoutButton" class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_CONTINUE_CHECKOUT, BUTTON_CONTINUE_ALT); ?></div>
  <div class="buttonRow back"><?php echo '<strong>' . TITLE_CONTINUE_CHECKOUT_PROCEDURE . '</strong><br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></div>
</div>
</div>
</form>
</div>
