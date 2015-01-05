<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=order_status.<br />
 * Displays information related to a single specific order
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Integrated COWOA v2.2 - 2007 - 2012
 */
?>  <!-- TPL_ORDER_STATUS_DEFAULT.PHP -->
<div class="centerColumn" id="accountHistInfo">

<h1 id="orderHistoryHeading"><?php echo HEADING_TITLE; ?></h1>
<br />
<?php
if (isset($_POST['action']) && $_POST['action'] == "process" && ($errorInvalidID || $errorInvalidEmail || $errorNoMatch)) {
?>
<div class="messageStackWarning larger">
<?php
  if($errorInvalidID) echo ERROR_INVALID_ORDER;
  if($errorInvalidEmail) echo ERROR_INVALID_EMAIL;
  if($errorNoMatch) echo ERROR_NO_MATCH;
?>
</div>
<?php } ?>
<?php if (isset($order)) { ?>

  <table border="0" width="100%" cellspacing="0" cellpadding="0" summary="Itemized listing of previous order, includes number ordered, items and prices">
  <h2 id="orderHistoryDetailedOrder"><?php echo SUB_HEADING_TITLE . ORDER_HEADING_DIVIDER . sprintf(HEADING_ORDER_NUMBER, $_POST['order_id']); ?></h2>
  <div class="forward"><?php echo HEADING_ORDER_DATE . ' ' . zen_date_long($order->info['date_purchased']); ?></div>
  <br class="clearBoth" />
      <tr class="tableHeading">
          <th scope="col" id="myAccountQuantity"><?php echo HEADING_QUANTITY; ?></th>
          <th scope="col" id="myAccountProducts"><?php echo HEADING_PRODUCTS; ?></th>
  <?php
    if (sizeof($order->info['tax_groups']) > 1) {
  ?>
          <th scope="col" id="myAccountTax"><?php echo HEADING_TAX; ?></th>
  <?php
   }
  ?>
          <th scope="col" id="myAccountTotal"><?php echo HEADING_TOTAL; ?></th>
      </tr>
  <?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    ?>
      <tr>
          <td class="accountQuantityDisplay"><?php echo  $order->products[$i]['qty'] . QUANTITY_SUFFIX; ?></td>
          <td class="accountProductDisplay"><?php echo  $order->products[$i]['name'];

      if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
        echo '<ul class="orderAttribsList">';
        for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
          echo '<li>' . $order->products[$i]['attributes'][$j]['option'] . TEXT_OPTION_DIVIDER . nl2br($order->products[$i]['attributes'][$j]['value']) . '</li>';
        }
          echo '</ul>';
      }
  ?>
          </td>
  <?php
      if (sizeof($order->info['tax_groups']) > 1) {
  ?>
          <td class="accountTaxDisplay"><?php echo zen_display_tax_value($order->products[$i]['tax']) . '%' ?></td>
  <?php
      }
  ?>
          <td class="accountTotalDisplay"><?php echo $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') ?></td>
      </tr>
  <?php
    }
  ?>
  </table>
  <hr />
  <div id="orderTotals">
  <?php
    for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
  ?>
       <div class="amount larger forward"><?php echo $order->totals[$i]['text'] ?></div>
       <div class="lineTitle larger forward"><?php echo $order->totals[$i]['title'] ?></div>
  <br class="clearBoth" />
  <?php
    }
  ?>

  </div>

  <?php
  /**
   * Used to display any downloads associated with the cutomers account
   */
    if (DOWNLOAD_ENABLED == 'true') require($template->get_template_dir('tpl_modules_os_downloads.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_os_downloads.php');
  ?>


  <?php
  /**
   * Used to loop thru and display order status information
   */
  if (sizeof($statusArray)) {
  ?>

  <table border="0" width="100%" cellspacing="0" cellpadding="0" id="myAccountOrdersStatus" summary="Table contains the date, order status and any comments regarding the order">
  <caption><h2 id="orderHistoryStatus"><?php echo HEADING_ORDER_HISTORY; ?></h2></caption>
      <tr class="tableHeading">
          <th scope="col" id="myAccountStatusDate"><?php echo TABLE_HEADING_STATUS_DATE; ?></th>
          <th scope="col" id="myAccountStatus"><?php echo TABLE_HEADING_STATUS_ORDER_STATUS; ?></th>
          <th scope="col" id="myAccountStatusComments"><?php echo TABLE_HEADING_STATUS_COMMENTS; ?></th>
         </tr>
  <?php
    foreach ($statusArray as $statuses) {
  ?>
      <tr>
          <td><?php echo zen_date_short($statuses['date_added']); ?></td>
          <td><?php echo $statuses['orders_status_name']; ?></td>
          <td><?php echo (empty($statuses['comments']) ? '&nbsp;' : nl2br(zen_output_string_protected($statuses['comments']))); ?></td>
      </tr>
  <?php
    }
  ?>
  </table>
  <?php } ?>

  <hr />
  <div id="myAccountShipInfo" class="floatingBox back">


  <?php
      if (zen_not_null($order->info['shipping_method'])) {
  ?>
  <h4><?php echo HEADING_SHIPPING_METHOD; ?></h4>
  <div><?php echo $order->info['shipping_method']; ?></div>
  <?php } else { // temporary just remove these 4 lines ?>
  <div>WARNING: Missing Shipping Information</div>
  <?php
      }
  ?>
  </div>

  <div id="myAccountPaymentInfo" class="floatingBox forward">
  <h4><?php echo HEADING_PAYMENT_METHOD; ?></h4>
  <div><?php echo $order->info['payment_method']; ?></div>
  </div>
  <br class="clearBoth" />
  <br />
<?php } ?>

<?php
echo zen_draw_form('order_status', zen_href_link(FILENAME_ORDER_STATUS, '', 'SSL'), 'post') . zen_draw_hidden_field('action', 'process');
?>
<fieldset>
<legend><?php echo HEADING_TITLE; ?></legend>

<?php echo TEXT_LOOKUP_INSTRUCTIONS; ?>
<br /><br />

<label class="inputLabel"><?php echo ENTRY_ORDER_NUMBER; ?></label>
<?php echo zen_draw_input_field('order_id', (int)$_GET['order_id'], 'size="10" id="order_id"'); ?>
<br />
<br />
<label class="inputLabel"><?php echo ENTRY_EMAIL; ?></label>
<?php echo zen_draw_input_field('query_email_address', '', 'size="35" id="query_email_address"'); ?>
<br />

<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT); ?></div>
</fieldset>






</form>
<!--bof logoff-->
<!--Kills session after COWOA customer looks at order status-->
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
<?php } ?>
<!--eof logoff-->
</div>