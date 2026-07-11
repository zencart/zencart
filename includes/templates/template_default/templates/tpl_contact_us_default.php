<?php

declare(strict_types=1);
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=contact_us.
 * Displays contact us page form.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2023 May 23 Modified in v2.0.0-alpha1 $
 */
?>
<div class="centerColumn" id="contactUsDefault">

<?php
echo zen_draw_form('contact_us', zen_href_link(FILENAME_CONTACT_US, 'action=send', 'SSL'));

if (zen_config('CONTACT_US_STORE_NAME_ADDRESS') === '1') { ?>
<address><?= nl2br(zen_config('STORE_NAME_ADDRESS'), false) ?></address>
<?php }

if (isset($_GET['action']) && ($_GET['action'] == 'success')) { ?>
<div class="mainContent success"><?= TEXT_SUCCESS ?></div>
<div class="buttonRow"><?= zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>' ?></div>
<?php } else {
    if ($tplSetting->DEFINE_CONTACT_US_STATUS >= '1' && $tplSetting->DEFINE_CONTACT_US_STATUS <= '2') { ?>

<div id="contactUsNoticeContent" class="content">
<?php
/**
 * require html_define for the contact_us page
 */
  require $define_page;
?>
</div>
<?php }

if ($messageStack->size('contact') > 0) {
    echo $messageStack->output('contact');
} ?>

<fieldset id="contactUsForm">
<legend><?= HEADING_TITLE ?></legend>
<div class="alert forward"><?= FORM_REQUIRED_INFORMATION ?></div>
<br class="clearBoth">

<?php
// show dropdown if set
if (zen_config('CONTACT_US_LIST') !== '') { ?>
<label class="inputLabel" for="send-to"><?= SEND_TO_TEXT ?></label>
<?= zen_draw_pull_down_menu('send_to',  $send_to_array, $send_to_default, 'id="send-to" required size="' . count($send_to_array) . '"') . '<span class="alert">' . ENTRY_REQUIRED_SYMBOL . '</span>' ?>
<br class="clearBoth">
<?php
    }
?>

<label class="inputLabel" for="contactname"><?= ENTRY_NAME ?></label>
<?= zen_draw_input_field('contactname', $name, ' size="40" id="contactname" placeholder="' . ENTRY_REQUIRED_SYMBOL . '" required') ?>
<br class="clearBoth">

<label class="inputLabel" for="email-address"><?= ENTRY_EMAIL ?></label>
<?= zen_draw_input_field('email', ($email_address), ' size="40" id="email-address" autocomplete="off" placeholder="' . ENTRY_REQUIRED_SYMBOL . '" required', 'email') ?>
<br class="clearBoth">

<label class="inputLabel" for="telephone"><?= ENTRY_TELEPHONE_NUMBER ?></label>
<?= zen_draw_input_field('telephone', ($telephone), ' size="20" id="telephone" autocomplete="off"', 'tel') ?>
<br class="clearBoth">

<label for="enquiry"><?= ENTRY_ENQUIRY ?></label>
<?= zen_draw_textarea_field('enquiry', '30', '7', $enquiry, 'id="enquiry" placeholder="' . ENTRY_REQUIRED_SYMBOL . '" required') ?>

<?= zen_draw_input_field($antiSpamFieldName, '', ' size="40" id="CUAS" style="visibility:hidden; display:none;" autocomplete="off" aria-hidden="true"') ?>
</fieldset>

<div class="buttonRow forward"><?= zen_image_submit(BUTTON_IMAGE_SEND, BUTTON_SEND_ALT) ?></div>
<div class="buttonRow back"><?= zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>' ?></div>
<?php
  }
echo '</form>';
  ?>
</div>
