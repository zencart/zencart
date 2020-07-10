<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=account_password.<br />
 * Allows customer to change their password
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: rbarbour zcadditions.com Fri Feb 26 00:03:33 2016 -0500 Modified in v1.5.5 $
 */
?>
<div class="centerColumn" id="accountPassword">
<?php echo zen_draw_form('account_password', zen_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'), 'post', 'onsubmit="return check_form(account_password);"') . zen_draw_hidden_field('action', 'process'); ?>

<fieldset>
<legend><?php echo HEADING_TITLE; ?></legend>
<div class="alert forward"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
<br class="clearBoth" />

<?php if ($messageStack->size('account_password') > 0) echo $messageStack->output('account_password'); ?>

<label class="inputLabel" for="password-current"><?php echo ENTRY_PASSWORD_CURRENT; ?></label>
<?php echo zen_draw_password_field('password_current','','id="password-current" autocomplete="off" placeholder="' . ENTRY_PASSWORD_CURRENT_TEXT . '" required'); ?>
<br class="clearBoth" />

<label class="inputLabel" for="password-new"><?php echo ENTRY_PASSWORD_NEW; ?></label>
<?php echo zen_draw_password_field('password_new','','id="password-new" autocomplete="off" placeholder="' . ENTRY_PASSWORD_NEW_TEXT . '" required'); ?>
<br class="clearBoth" />

<label class="inputLabel" for="password-confirm"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></label>
<?php echo zen_draw_password_field('password_confirmation','','id="password-confirm" autocomplete="off" placeholder="' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '" required'); ?>
<br class="clearBoth" />
</fieldset>

 <div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT); ?></div>
 <div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

</form>
</div>
