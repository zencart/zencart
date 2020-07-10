<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=address_book_process.<br />
 * Allows customer to add a new address book entry
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Oct 17 22:01:06 2015 -0400 Modified in v1.5.5 $
 */
?>
<div class="centerColumn" id="addressBookProcessDefault">
<?php if (!isset($_GET['delete'])) echo zen_draw_form('addressbook', zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, (isset($_GET['edit']) ? 'edit=' . $_GET['edit'] : ''), 'SSL'), 'post', 'onsubmit="return check_form(addressbook);"'); ?>
      

<h1 id="addressBookProcessDefaultHeading"><?php if (isset($_GET['edit'])) { echo HEADING_TITLE_MODIFY_ENTRY; } elseif (isset($_GET['delete'])) { echo HEADING_TITLE_DELETE_ENTRY; } else { echo HEADING_TITLE_ADD_ENTRY; } ?></h1>
    
<?php if ($messageStack->size('addressbook') > 0) echo $messageStack->output('addressbook'); ?>    

<?php
  if (isset($_GET['delete'])) {
?>
<div class="alert"><?php echo DELETE_ADDRESS_DESCRIPTION; ?></div>

<address><?php echo zen_address_label($_SESSION['customer_id'], $_GET['delete'], true, ' ', '<br />'); ?></address>
<br class="clearBoth" />
 

<div class="buttonRow forward">
<?php echo zen_draw_form('delete_address', zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'action=deleteconfirm', 'SSL'), 'post'); ?>
<?php echo zen_draw_hidden_field('delete', $_GET['delete']); ?>
<?php echo zen_image_submit(BUTTON_IMAGE_DELETE, BUTTON_DELETE_ALT); ?>
</form>
</div>
<div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
<?php
  } else {
?>
<?php
/**
 * Used to display address book entry form
 */
?>
<?php   require($template->get_template_dir('tpl_modules_address_book_details.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_address_book_details.php'); ?>

<br class="clearBoth" />
<?php
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
?>
<div class="buttonRow forward"><?php echo zen_draw_hidden_field('action', 'update') . zen_draw_hidden_field('edit', (int)$_GET['edit']) . zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT); ?></div>
<div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
    
<?php
    } else {
?>
<div class="buttonRow forward"><?php echo zen_draw_hidden_field('action', 'process') . zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT); ?></div>
<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
<?php
    }
  }
?>
<?php if (!isset($_GET['delete'])) echo '</form>'; ?>
</div>