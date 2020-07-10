<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=create-account_success.<br />
 * Displays confirmation that a new account has been created.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_create_account_success_default.php 4886 2006-11-05 09:01:18Z drbyte $
 */
?>
<div class="centerColumn" id="createAcctSuccess">
<h1 id="createAcctSuccessHeading"><?php echo HEADING_TITLE; ?></h1>

<div id="createAcctSuccessMainContent" class="content"><?php echo TEXT_ACCOUNT_CREATED; ?></div>

<fieldset>
<legend><?php echo PRIMARY_ADDRESS_TITLE; ?></legend>
<?php
/**
 * Used to loop thru and display address book entries
 */
  foreach ($addressArray as $addresses) {
?>
<h3 class="addressBookDefaultName"><?php echo zen_output_string_protected($addresses['firstname'] . ' ' . $addresses['lastname']); ?></h3>

<address><?php echo zen_address_format($addresses['format_id'], $addresses['address'], true, ' ', '<br />'); ?></address>

<div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses['address_book_id'], 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a> <a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $addresses['address_book_id'], 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_DELETE, BUTTON_DELETE_ALT) . '</a>'; ?></div>
<br class="clearBoth">
<?php
  }
?>
</fieldset>


<div class="buttonRow forward"><?php echo '<a href="' . $origin_href . '">' . zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) . '</a>'; ?></div>
</div>
