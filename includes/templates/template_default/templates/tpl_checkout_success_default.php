<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=checkout_success.<br />
 * Displays confirmation details after order has been successfully processed.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_checkout_success_default.php 16435 2010-05-28 09:34:32Z drbyte $
 * @version $Id: Integrated COWOA v2.2 - 2007 - 2012
 */
?>
<div class="centerColumn" id="checkoutSuccess">
<!--bof -gift certificate- send or spend box-->
<?php
// only show when there is a GV balance
  if ($customer_has_gv_balance ) {
?>
<div id="sendSpendWrapper">
<?php require($template->get_template_dir('tpl_modules_send_or_spend.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_send_or_spend.php'); ?>
</div>
<?php
  }
?>
<!--eof -gift certificate- send or spend box-->

<h1 id="checkoutSuccessHeading"><?php echo HEADING_TITLE; ?></h1>
<div id="checkoutSuccessOrderNumber"><?php echo TEXT_YOUR_ORDER_NUMBER . $zv_orders_id; ?></div>
<!-- bof Order Steps (tableless) -->
<?php if($_SESSION['COWOA']) $COWOA=TRUE; ?>
<?php if($COWOA) {?>
    <div id="order_steps">
            <div class="order_steps_text">
            <span class="order_steps_text1_COWOA"><?php echo TEXT_ORDER_STEPS_BILLING; ?></span><span class="order_steps_text2_COWOA"><?php echo TEXT_ORDER_STEPS_1; ?></span><span class="order_steps_text3_COWOA"><?php echo TEXT_ORDER_STEPS_2; ?></span><span class="order_steps_text4_COWOA"><?php echo TEXT_ORDER_STEPS_3; ?></span><span id="active_step_text_COWOA"><?php echo zen_image($template->get_template_dir(ORDER_STEPS_IMAGE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . ORDER_STEPS_IMAGE, ORDER_STEPS_IMAGE_ALT); ?><br /><?php echo TEXT_ORDER_STEPS_4; ?></span>
            </div>
             <div class="order_steps_line_2">
          <span class="progressbar_active_COWOA">&nbsp;</span><span class="progressbar_active_COWOA">&nbsp;</span><span class="progressbar_active_COWOA">&nbsp;</span><span class="progressbar_active_COWOA">&nbsp;</span><span class="progressbar_active_COWOA">&nbsp;</span>
            </div>
    </div>
<?php } else {?>
    <div id="order_steps">
            <div class="order_steps_text">
            <span class="order_steps_text2"><?php echo TEXT_ORDER_STEPS_1; ?></span><span class="order_steps_text3"><?php echo TEXT_ORDER_STEPS_2; ?></span><span class="order_steps_text4"><?php echo TEXT_ORDER_STEPS_3; ?></span><span id="active_step_text"><?php echo zen_image($template->get_template_dir(ORDER_STEPS_IMAGE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . ORDER_STEPS_IMAGE, ORDER_STEPS_IMAGE_ALT); ?><br /><?php echo TEXT_ORDER_STEPS_4; ?></span>
            </div>
             <div class="order_steps_line_2">
                <span class="progressbar_active">&nbsp;</span><span class="progressbar_active">&nbsp;</span><span class="progressbar_active">&nbsp;</span><span class="progressbar_active">&nbsp;</span>
            </div>
    </div>
<?php } ?>
<!-- eof Order Steps (tableless) -->
<?php if (DEFINE_CHECKOUT_SUCCESS_STATUS >= 1 and DEFINE_CHECKOUT_SUCCESS_STATUS <= 2) { ?>
<div id="checkoutSuccessMainContent" class="content">
<?php
/**
 * require the html_defined text for checkout success
 */
  require($define_page);
?>
</div>
<?php } ?>
<!-- bof payment-method-alerts -->
<?php
if (isset($_SESSION['payment_method_messages']) && $_SESSION['payment_method_messages'] != '') {
?>
  <div class="content">
  <?php echo $_SESSION['payment_method_messages']; ?>
  </div>
<?php
}
?>
<!-- eof payment-method-alerts -->
<!--bof logoff-->
<!--Kill session if COWOA customer at checkout success-->
<div id="checkoutSuccessLogoff">
<?php
if ($_SESSION['COWOA'] and COWOA_LOGOFF == 'true') {
  zen_session_destroy();
} else {
  if (isset($_SESSION['customer_guest_id'])) {
    echo TEXT_CHECKOUT_LOGOFF_GUEST;
  } elseif (isset($_SESSION['customer_id'])) {
    echo TEXT_CHECKOUT_LOGOFF_CUSTOMER;
  }
?>
<div class="buttonRow forward"><a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'SSL'); ?>"><?php echo zen_image_button(BUTTON_IMAGE_LOG_OFF , BUTTON_LOG_OFF_ALT); ?></a></div>
<?php } ?>
</div>
<!--eof logoff-->
<br class="clearBoth" />
<!--bof -product notifications box-->
<?php
/**
 * The following creates a list of checkboxes for the customer to select if they wish to be included in product-notification
 * announcements related to products they've just purchased.
 **/
    if ($flag_show_products_notification == true && !($_SESSION['COWOA'])) {
?>
<fieldset id="csNotifications">
<legend><?php echo TEXT_NOTIFY_PRODUCTS; ?></legend>
<?php echo zen_draw_form('order', zen_href_link(FILENAME_CHECKOUT_SUCCESS, 'action=update', 'SSL')); ?>

<?php foreach ($notificationsArray as $notifications) { ?>
<?php echo zen_draw_checkbox_field('notify[]', $notifications['products_id'], true, 'id="notify-' . $notifications['counter'] . '"') ;?>
<label class="checkboxLabel" for="<?php echo 'notify-' . $notifications['counter']; ?>"><?php echo $notifications['products_name']; ?></label>
<br />
<?php } ?>
<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT); ?></div>
</form>
</fieldset>
<?php
    }
?>
<!--eof -product notifications box-->



<!--bof -product downloads module-->
<?php
  if (DOWNLOAD_ENABLED == 'true' and !($_SESSION['COWOA'])) require($template->get_template_dir('tpl_modules_downloads.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_downloads.php');
?>
<!--eof -product downloads module-->

<?php if(!($_SESSION['COWOA'])) { ?> <div id="checkoutSuccessOrderLink"><?php echo TEXT_SEE_ORDERS;?></div> <?php } ?>

<div id="checkoutSuccessContactLink"><?php echo TEXT_CONTACT_STORE_OWNER;?></div>

<h3 id="checkoutSuccessThanks" class="centeredContent"><?php echo TEXT_THANKS_FOR_SHOPPING; ?></h3>
</div>