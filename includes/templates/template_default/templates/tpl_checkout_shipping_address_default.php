<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=checkout_shipping_adresss.<br />
 * Allows customer to change the shipping address.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Feb 15 Modified in v1.5.7 $
 */
?>
<div class="centerColumn" id="checkoutShipAddressDefault">

<h1 id="checkoutShipAddressDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<?php if ($messageStack->size('checkout_address') > 0) echo $messageStack->output('checkout_address'); ?>

<?php
  if ($process == false || $error == true) {
?>

<h2 id="checkoutShipAddressDefaultAddress"><?php echo TITLE_SHIPPING_ADDRESS; ?></h2>

     <address class="back"><?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'); ?></address>
    <div class="instructions"><?php if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) echo TEXT_CREATE_NEW_SHIPPING_ADDRESS; ?></div>
<br class="clearBoth" />

<?php
     if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
        echo zen_draw_form('checkout_address', zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), 'post', 'class="group"'); 
/**
 * require template to display new address form
 */
  require($template->get_template_dir('tpl_modules_checkout_new_address.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_checkout_new_address.php');
?>
<div class="buttonRow forward"><?php echo zen_draw_hidden_field('action', 'submit') . zen_image_submit(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT); ?></div>
</form>

<?php
    }
    if ($addresses_count > 1) {
?>
<?php echo zen_draw_form('checkout_address_book', zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), 'post', 'class="group"'); ?>

<fieldset>
<legend><?php echo TABLE_HEADING_ADDRESS_BOOK_ENTRIES; ?></legend>
<?php
      require($template->get_template_dir('tpl_modules_checkout_address_book.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_checkout_address_book.php');
?>
</fieldset>
<div class="buttonRow forward"><?php echo zen_draw_hidden_field('action', 'submit') . zen_image_submit(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT); ?></div>
</form>
<?php
     }
  }

?>

<div class="buttonRow back"><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE . '<br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></div>

<?php
  if ($process == true) {
?>
  <div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

<?php
  }
?>
</div>
