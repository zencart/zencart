<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=account_notifications.<br />
 * Allows customer to manage product notifications
 *
 * @package templateSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_account_notifications_default.php 3206 2006-03-19 04:04:09Z birdbrain $
 */
?>
<div class="centerColumn" id="accountNotifications">
<?php echo zen_draw_form('account_notifications', zen_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL')) . zen_draw_hidden_field('action', 'process'); ?>

<h1 id="accountNotificationsHeading"><?php echo HEADING_TITLE; ?></h1>

<div class="notice"><?php echo MY_NOTIFICATIONS_DESCRIPTION; ?></div>

<fieldset>
<legend><?php echo GLOBAL_NOTIFICATIONS_TITLE; ?></legend>

<?php echo zen_draw_checkbox_field('product_global', '1', (($global->fields['global_product_notifications'] == '1') ? true : false), 'id="globalnotify"'); ?>
<label class="checkboxLabel" for="globalnotify"><?php echo GLOBAL_NOTIFICATIONS_DESCRIPTION; ?></label>
<br class="clearBoth" />
</fieldset>

<?php
  if ($flag_global_notifications != '1') {
?>
<fieldset>
<legend><?php echo NOTIFICATIONS_TITLE; ?></legend>

<?php
    if ($flag_products_check) {
?>
<div class="notice"><?php echo NOTIFICATIONS_DESCRIPTION; ?></div>
<?php
/**
 * Used to loop thru and display product notifications
 */
  foreach ($notificationsArray as $notifications) { 
?>
<?php echo zen_draw_checkbox_field('notify[]', $notifications['products_id'], true, 'id="notify-' . $notifications['counter'] . '"'); ?>
<label class="checkboxLabel" for="<?php echo 'notify-' . $notifications['counter']; ?>"><?php echo $notifications['products_name']; ?></label>
<br />
<?php
  }
?>
</fieldset>

<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT); ?></div>
<div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

<?php
    } else {
?>
<div class="notice"><?php echo NOTIFICATIONS_NON_EXISTING; ?></div>
</fieldset>
<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT); ?></div>
<div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
<?php
    }
?>

<?php
  }
?>

</form>    
</div>
