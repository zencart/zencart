<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 23 Modified in v1.5.7 $
 */
?>
<div class="centerColumn" id="askAQuestion">

<?php echo zen_draw_form('ask_a_question', zen_href_link(FILENAME_ASK_A_QUESTION, 'action=send&pid=' . (int)$_GET['pid'], 'SSL')); ?>

<?php if (CONTACT_US_STORE_NAME_ADDRESS== '1') { ?>
<address><?php echo nl2br(STORE_NAME_ADDRESS); ?></address>
<?php } ?>
<h1><?php echo HEADING_TITLE . $product_details['products_name']; ?></h1>


<?php
  if (isset($_GET['action']) && ($_GET['action'] == 'success')) {
?>

<div class="mainContent success"><?php echo TEXT_SUCCESS; ?></div>

<div class="buttonRow"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

<?php
  } else {
?>

<?php echo '<a href="' . zen_href_link(zen_get_info_page((int)$_GET['pid']), 'products_id=' . (int)$_GET['pid'], 'SSL') . '">' . zen_image(DIR_WS_IMAGES . $product_details['products_image'], $product_details['products_name'], IMAGE_PRODUCT_LISTING_WIDTH, IMAGE_PRODUCT_LISTING_HEIGHT) . '</a>'; ?>

<div id="contactUsNoticeContent" class="content">
<?php
/**
 * require html_define for the contact_us page
 */
  require($define_page);
?>
</div>

<?php if ($messageStack->size('contact') > 0) echo $messageStack->output('contact'); ?>

<fieldset id="contactUsForm">
<legend><?php echo FORM_TITLE; ?></legend>
<div class="alert forward"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
<br class="clearBoth">

<?php
// show dropdown if set
    if (!empty(CONTACT_US_LIST)){
?>
<label class="inputLabel" for="send-to"><?php echo SEND_TO_TEXT; ?></label>
<?php echo zen_draw_pull_down_menu('send_to',  $send_to_array, 0, 'id="send-to"') . '<span class="alert">' . ENTRY_REQUIRED_SYMBOL . '</span>'; ?>
<br class="clearBoth">
<?php
    }
?>

<label class="inputLabel" for="contactname"><?php echo ENTRY_NAME; ?></label>
<?php echo zen_draw_input_field('contactname', $name, ' size="40" id="contactname" placeholder="' . ENTRY_REQUIRED_SYMBOL . '" autofocus required'); ?>
<br class="clearBoth">

<label class="inputLabel" for="email-address"><?php echo ENTRY_EMAIL; ?></label>
<?php echo zen_draw_input_field('email', ($email_address), ' size="40" id="email-address" autocomplete="off" placeholder="' . ENTRY_REQUIRED_SYMBOL . '" required', 'email'); ?>
<br class="clearBoth">

<label class="inputLabel" for="telephone"><?php echo ENTRY_TELEPHONE; ?></label>
<?php echo zen_draw_input_field('telephone', ($telephone), ' size="20" id="telephone" autocomplete="off" placeholder="' . ENTRY_REQUIRED_SYMBOL . '" required', 'telephone'); ?>
<br class="clearBoth">

<label for="enquiry"><?php echo ENTRY_ENQUIRY; ?></label>
<?php echo zen_draw_textarea_field('enquiry', '30', '7', $enquiry, 'id="enquiry" placeholder="' . ENTRY_REQUIRED_SYMBOL . '" required'); ?>

<?php echo zen_draw_input_field('should_be_empty', '', ' size="40" id="CUAS" style="visibility:hidden; display:none;" autocomplete="off"'); ?>

</fieldset>

<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_SEND, BUTTON_SEND_ALT); ?></div>
<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
<?php
  }
?>
</form>
</div>

