<?php
/**
 * Page Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_password_forgotten_default.php drbyte  Modified in v1.6.0 $
 */
?>
<div class="centerColumn" id="passwordForgotten">
<?php echo zen_draw_form('password_forgotten', zen_href_link(FILENAME_PASSWORD_FORGOTTEN, 'action=process', 'SSL')); ?>

<?php if ($messageStack->size('password_forgotten') > 0) echo $messageStack->output('password_forgotten'); ?>

<fieldset>
<legend><?php echo HEADING_TITLE; ?></legend>

<div id="passwordForgottenMainContent" class="content"><?php echo TEXT_MAIN; ?></div>

<div class="alert forward"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
<br class="clearBoth" />

<label for="email-address"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
<?php echo zen_draw_input_field('email_address', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_email_address', '40') . ' id="email-address" autocomplete="off"') . '&nbsp;' . (zen_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="alert">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?>
<br class="clearBoth" />
</fieldset>

<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT); ?></div>
<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

</form>
</div>