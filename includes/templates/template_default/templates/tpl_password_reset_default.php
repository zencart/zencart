<?php
/**
 * Loaded automatically by index.php?main_page=password_reset.<br />
 * Allows customer to change their password via a requested reset_token
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Jan 15 New in v2.2.0 $
 *
 *  NOTE: changes made to this file should also be made in tpl_account_password_default.php where relevant
 */

?>
<div class="centerColumn" id="passwordReset">
    <h1><?= HEADING_TITLE ?></h1>
    <?php
    if ($messageStack->size('reset_password') > 0) {
        echo $messageStack->output('reset_password');
    } ?>

    <?php
    if (!$token_error) {
    ?>
    <?= zen_draw_form('account_password', zen_href_link(FILENAME_PASSWORD_RESET, '', 'SSL'), 'post', 'onsubmit="return check_form(account_password);"') ?>
    <?= zen_draw_hidden_field('action', 'process') ?>
    <?= zen_draw_hidden_field('reset_token', $reset_token) ?>

    <fieldset>
        <div class="alert forward"><?= FORM_REQUIRED_INFORMATION ?></div>
        <br class="clearBoth">

        <label class="inputLabel" for="password-new"><?= ENTRY_PASSWORD_NEW ?></label>
        <?= zen_draw_password_field('password_new', '', 'id="password-new" autocomplete="new-password" placeholder="' . ENTRY_PASSWORD_NEW_TEXT . '" required') ?>
        <br class="clearBoth">

        <label class="inputLabel" for="password-confirm"><?= ENTRY_PASSWORD_CONFIRMATION ?></label>
        <?= zen_draw_password_field('password_confirmation', '', 'id="password-confirm" autocomplete="new-password" placeholder="' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '" required') ?>
        <br class="clearBoth">

    </fieldset>
    <div class="buttonRow forward"><?= zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT) ?></div>
    <div class="buttonRow back"><?= '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>' ?></div>
    </form>
<?php
}
?>
</div>
