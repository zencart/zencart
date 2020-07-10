<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=adress_book.<br />
 * Allows customer to manage entries in their address book
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_address_book_default.php 5369 2006-12-23 10:55:52Z drbyte $
 */
?>
<div class="centerColumn" id="addressBookDefault">

<h1 id="addressBookDefaultHeading"><?php echo HEADING_TITLE; ?></h1>
 
<?php if ($messageStack->size('addressbook') > 0) echo $messageStack->output('addressbook'); ?> 
      
<h2 id="addressBookDefaultPrimary"><?php echo PRIMARY_ADDRESS_TITLE; ?></h2>
<address class="back"><?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['customer_default_address_id'], true, ' ', '<br />'); ?></address>
<div class="instructions"><?php echo PRIMARY_ADDRESS_DESCRIPTION; ?></div>
<br class="clearBoth" />

<fieldset>
<legend><?php echo ADDRESS_BOOK_TITLE; ?></legend>
<div class="alert forward"><?php echo sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES); ?></div>
<br class="clearBoth" />
<?php
/**
 * Used to loop thru and display address book entries
 */
  foreach ($addressArray as $addresses) {
?>
<h3 class="addressBookDefaultName"><?php echo zen_output_string_protected($addresses['firstname'] . ' ' . $addresses['lastname']); ?><?php if ($addresses['address_book_id'] == $_SESSION['customer_default_address_id']) echo '&nbsp;' . PRIMARY_ADDRESS ; ?></h3>

<address><?php echo zen_address_format($addresses['format_id'], $addresses['address'], true, ' ', '<br />'); ?></address>

<div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses['address_book_id'], 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a> <a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $addresses['address_book_id'], 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_DELETE_SMALL, BUTTON_DELETE_SMALL_ALT) . '</a>'; ?></div>
<br class="clearBoth" />
<?php
  }
?>
</fieldset>

<?php
  if (zen_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) {
?>
   <div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_ADD_ADDRESS, BUTTON_ADD_ADDRESS_ALT) . '</a>'; ?></div>
<?php
  }
?>
<div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
<br class="clearBoth" />
</div>
