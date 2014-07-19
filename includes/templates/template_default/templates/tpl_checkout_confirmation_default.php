<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=checkout_confirmation.<br />
 * Displays final checkout details, cart, payment and shipping info details.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_checkout_confirmation_default.php 6247 2007-04-21 21:34:47Z wilt $
 * @version $Id: Integrated COWOA v2.2 - 2007 - 2012
 */
?>
<div class="centerColumn" id="checkoutConfirmDefault">

<!-- bof Order Steps (tableless) -->
<?php echo ORDER_REVIEW; ?>
<?php if($COWOA) {?>
  <div id="order_steps">
    <div class="order_steps_text">
      <span class="order_steps_text1_COWOA"><?php echo TEXT_ORDER_STEPS_BILLING; ?></span><span class="order_steps_text2_COWOA"><?php echo TEXT_ORDER_STEPS_1; ?></span><span class="order_steps_text3_COWOA"><?php echo TEXT_ORDER_STEPS_2; ?></span><span id="active_step_text_COWOA"><?php echo zen_image($template->get_template_dir(ORDER_STEPS_IMAGE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . ORDER_STEPS_IMAGE, ORDER_STEPS_IMAGE_ALT); ?><br /><?php echo TEXT_ORDER_STEPS_3; ?></span><span class="order_steps_text4_COWOA"><?php echo TEXT_ORDER_STEPS_4; ?></span>
    </div>
    <div class="order_steps_line_2">
      <span class="progressbar_active_COWOA">&nbsp;</span><span class="progressbar_active_COWOA">&nbsp;</span><span class="progressbar_active_COWOA">&nbsp;</span><span class="progressbar_active_COWOA">&nbsp;</span><span class="progressbar_inactive_COWOA">&nbsp;</span>
    </div>
  </div>
<?php } else {?>
  <div id="order_steps">
    <div class="order_steps_text">
      <span class="order_steps_text2"><?php echo TEXT_ORDER_STEPS_1; ?></span><span class="order_steps_text3"><?php echo TEXT_ORDER_STEPS_2; ?></span><span id="active_step_text"><?php echo zen_image($template->get_template_dir(ORDER_STEPS_IMAGE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . ORDER_STEPS_IMAGE, ORDER_STEPS_IMAGE_ALT); ?><br /><?php echo TEXT_ORDER_STEPS_3; ?></span><span class="order_steps_text4"><?php echo TEXT_ORDER_STEPS_4; ?></span>
    </div>
    <div class="order_steps_line_2">
      <span class="progressbar_active">&nbsp;</span><span class="progressbar_active">&nbsp;</span><span class="progressbar_active">&nbsp;</span><span class="progressbar_inactive">&nbsp;</span>
    </div>
  </div>
<?php } ?>
<!-- eof Order Steps (tableless) -->

<?php if ($messageStack->size('redemptions') > 0) echo $messageStack->output('redemptions'); ?>
<?php if ($messageStack->size('checkout_confirmation') > 0) echo $messageStack->output('checkout_confirmation'); ?>
<?php if ($messageStack->size('checkout') > 0) echo $messageStack->output('checkout');



// @TODO -- fix inherited COWOA bug where the following line prevents order-confirmations from showing when cart contents is $0 such as all-free-items
 if ($_SESSION['cart']->show_total() != 0) {  ?>

<div id="checkoutShipto" class="back">
<h4 id="checkoutConfirmDefaultBillingAddress"><?php echo HEADING_BILLING_ADDRESS; ?></h4>
<?php if (!$flagDisablePaymentAddressChange) { ?>
<div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></div>
<?php } ?>

<address><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?></address>

<?php
  $class =& $_SESSION['payment'];
?>

<h4 id="checkoutConfirmDefaultPayment"><?php echo HEADING_PAYMENT_METHOD; ?></h4>
<p id="checkoutConfirmDefaultPaymentTitle"><?php echo $GLOBALS[$class]->title; ?></p>

<?php
  if (is_array($payment_modules->modules)) {
    if ($confirmation = $payment_modules->confirmation()) {
?>


<div class="important"><?php echo $confirmation['title']; ?></div>
<?php
    }
?>
<div class="important">
<?php
      for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
?>
<div class="back"><?php echo $confirmation['fields'][$i]['title']; ?></div>
<div ><?php echo $confirmation['fields'][$i]['field']; ?></div>
<?php
     }
?>
      </div>
<?php
  }
?>

<br class="clearBoth" />
</div>

<?php
  if ($_SESSION['sendto'] != false) {
?>
<div id="checkoutShipto" class="forward">
<h4 id="checkoutConfirmDefaultShippingAddress"><?php echo HEADING_DELIVERY_ADDRESS; ?></h4>
<div class="buttonRow forward"><?php echo '<a href="' . $editShippingButtonLink . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></div>

<address><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?></address>

<?php
    if ($order->info['shipping_method']) {
?>
<h4 id="checkoutConfirmDefaultShipment"><?php echo HEADING_SHIPPING_METHOD; ?></h4>
<p id="checkoutConfirmDefaultShipmentTitle"><?php echo $order->info['shipping_method']; ?></p>

<?php
    }
?>
</div>
<?php

 }
?>
<br class="clearBoth" />
<hr />
<?php
// always show comments
//  if ($order->info['comments']) {
?>

<h4 id="checkoutConfirmDefaultHeadingComments"><?php echo HEADING_ORDER_COMMENTS; ?></h4>

<div class="buttonRow forward"><?php echo  '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></div>
<div><?php echo (empty($order->info['comments']) ? NO_COMMENTS_TEXT : nl2br(zen_output_string_protected($order->info['comments'])) . zen_draw_hidden_field('comments', $order->info['comments'])); ?></div>
<br class="clearBoth" />
<?php
//  }
?>
<hr />

<h4 id="checkoutConfirmDefaultHeadingCart"><?php echo HEADING_PRODUCTS; ?></h4>

<div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_SHOPPING_CART, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></div>
<br class="clearBoth" />

<?php  if ($flagAnyOutOfStock) { ?>
<?php    if (STOCK_ALLOW_CHECKOUT == 'true') {  ?>
<div class="messageStackError"><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></div>
<?php    } else { ?>
<div class="messageStackError"><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></div>
<?php    } //endif STOCK_ALLOW_CHECKOUT ?>
<?php  } //endif flagAnyOutOfStock ?>


      <table border="0" width="100%" cellspacing="0" cellpadding="0" id="cartContentsDisplay">
        <thead>
        <tr class="cartTableHeading">
        <th scope="col" id="ccQuantityHeading" width="30"><?php echo TABLE_HEADING_QUANTITY; ?></th>
        <th scope="col" id="ccProductsHeading"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
<?php
  // If there are tax groups, display the tax columns for price breakdown
  if (sizeof($order->info['tax_groups']) > 1) {
?>
          <th scope="col" id="ccTaxHeading"><?php echo HEADING_TAX; ?></th>
<?php
  }
?>
          <th scope="col" id="ccTotalHeading"><?php echo TABLE_HEADING_TOTAL; ?></th>
        </tr>
        </thead>
        <tbody>
<?php // now loop thru all products to display quantity and price ?>
<?php for ($i=0, $n=sizeof($order->products); $i<$n; $i++) { ?>
        <tr class="<?php echo $order->products[$i]['rowClass']; ?>">
          <td  class="cartQuantity"><?php echo $order->products[$i]['qty']; ?>&nbsp;x</td>
          <td class="cartProductDisplay"><?php echo $order->products[$i]['name']; ?>
          <?php  echo $stock_check[$i]; ?>

<?php // if there are attributes, loop thru them and display one per line
    if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0 ) {
    echo '<ul class="cartAttribsList">';
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
?>
      <li><?php echo $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value'])); ?></li>
<?php
      } // end loop
      echo '</ul>';
    } // endif attribute-info
?>
        </td>

<?php // display tax info if exists ?>
<?php if (sizeof($order->info['tax_groups']) > 1)  { ?>
        <td class="cartTotalDisplay">
          <?php echo zen_display_tax_value($order->products[$i]['tax']); ?>%</td>
<?php    }  // endif tax info display  ?>
        <td class="cartTotalDisplay">
          <?php echo $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']);
          if ($order->products[$i]['onetime_charges'] != 0 ) echo '<br /> ' . $currencies->display_price($order->products[$i]['onetime_charges'], $order->products[$i]['tax'], 1);
?>
        </td>
      </tr>
<?php  }  // end for loopthru all products ?>
      </tbody>
      </table>
      <hr />

<?php
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    $order_totals = $order_total_modules->process();
?>
<div id="orderTotals"><?php $order_total_modules->output(); ?></div>
<?php
  }
?>

<?php
  echo zen_draw_form('checkout_confirmation', $form_action_url, 'post', 'id="checkout_confirmation" onsubmit="submitonce();"');

  if (is_array($payment_modules->modules)) {
    echo $payment_modules->process_button();
  }
?>
<hr />
<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_CONFIRM_ORDER, BUTTON_CONFIRM_ORDER_ALT, 'name="btn_submit" id="btn_submit"') ;?></div>
</form>
<div class="buttonRow back"><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE . '<br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></div>

</div>
<?php
  }
?>