<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=account.<br />
 * Displays previous orders and options to change various Customer Account settings
 *
 * @package templateSystem
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jan 14 Modified in v1.5.6b $
 */
?>
<?php 
  if (!isset($display_as_mobile)) $display_as_mobile = ($detect->isMobile() && !$detect->isTablet() || $_SESSION['layoutType'] == 'mobile' or  $detect->isTablet() || $_SESSION['layoutType'] == 'tablet'); 
?>
<div class="centerColumn group" id="accountDefault">

<h1 id="accountDefaultHeading"><?php echo HEADING_TITLE; ?></h1>
<?php if ($messageStack->size('account') > 0) echo $messageStack->output('account'); ?>

<?php
    if (zen_count_customer_orders() > 0) {
  ?>
<p class="forward"><?php echo '<a class="show-all" href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . OVERVIEW_SHOW_ALL_ORDERS . '</a>'; ?></p>
<br class="clearBoth" />
<h2 id="previous-orders"><?php echo OVERVIEW_PREVIOUS_ORDERS; ?></h2>
<table id="prevOrders">
    <tr class="tableHeading">
    <th scope="col"><?php echo TABLE_HEADING_DATE; ?></th>
    <th scope="col"><?php echo TABLE_HEADING_ORDER_NUMBER; ?></th>
    <th scope="col"><?php echo TABLE_HEADING_SHIPPED_TO; ?></th>
    <th scope="col"><?php echo TABLE_HEADING_STATUS; ?></th>
    <th scope="col"><?php echo TABLE_HEADING_TOTAL; ?></th>
    <th scope="col"><?php echo TABLE_HEADING_VIEW; ?></th>
  </tr>
<?php
  foreach($ordersArray as $orders) {
?>
  <tr>
    <td class="accountOrderDate"><?php if ($display_as_mobile) { echo '<b class="hide">' . TABLE_HEADING_DATE . '&#58;&nbsp;&nbsp;</b>'; }?><?php echo zen_date_short($orders['date_purchased']); ?></td>
    <td class="accountOrderId"><?php if ($display_as_mobile) { echo '<b class="hide">' . TABLE_HEADING_ORDER_NUMBER . '&#58;&nbsp;&nbsp;</b>'; }?><?php echo TEXT_NUMBER_SYMBOL . $orders['orders_id']; ?></td>
    <td class="accountOrderAddress"><?php if ($display_as_mobile) { echo '<b class="hide">' . TABLE_HEADING_SHIPPED_TO . '&#58;&nbsp;&nbsp;</b>'; }?><address><?php echo zen_output_string_protected($orders['order_name']) . '<br />' . $orders['order_country']; ?></address></td>
    <td class="accountOrderStatus"><?php if ($display_as_mobile) { echo '<b class="hide">' . TABLE_HEADING_STATUS . '&#58;&nbsp;&nbsp;</b>'; }?><?php echo $orders['orders_status_name']; ?></td>
    <td class="accountOrderTotal alignRight"><?php if ($display_as_mobile) { echo '<b class="hide">' . TABLE_HEADING_TOTAL . '&#58;&nbsp;&nbsp;</b>'; }?><?php echo $orders['order_total']; ?></td>
    <td class="accountOrderViewButton alignRight"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders['orders_id'], 'SSL') . '"> ' . zen_image_button(BUTTON_IMAGE_VIEW_SMALL, BUTTON_VIEW_SMALL_ALT) . '</a>'; ?></td>
  </tr>

<?php
  }
?>
</table>
<?php
  }
?>
<br class="clearBoth" />
<div id="accountLinksWrapper" class="back">
<h2><?php echo MY_ACCOUNT_TITLE; ?></h2>
<ul id="myAccountGen" class="list">
<li><?php echo ' <a href="' . zen_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . MY_ACCOUNT_INFORMATION . '</a>'; ?></li>
<li><?php echo ' <a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK . '</a>'; ?></li>
<li><?php echo ' <a href="' . zen_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL') . '">' . MY_ACCOUNT_PASSWORD . '</a>'; ?></li>
</ul>


<?php
  if ((int)ACCOUNT_NEWSLETTER_STATUS > 0 or CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS !='0') {
?>
<h2><?php echo EMAIL_NOTIFICATIONS_TITLE; ?></h2>
<ul id="myAccountNotify" class="list">
<?php
  if ((int)ACCOUNT_NEWSLETTER_STATUS > 0) {
?>
<li><?php echo ' <a href="' . zen_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_NEWSLETTERS . '</a>'; ?></li>
<?php } //endif newsletter unsubscribe ?>
<?php
  if (CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS == '1') {
?>
<li><?php echo ' <a href="' . zen_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_PRODUCTS . '</a>'; ?></li>

<?php } //endif product notification ?>
</ul>

<?php } // endif don't show unsubscribe or notification ?>
</div>

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
<br class="clearBoth" />
</div>
