<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=account_history.<br />
 * Displays all customers previous orders
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_account_history_default.php 2580 2005-12-16 07:31:21Z birdbrain $
 */
?>
<div class="centerColumn" id="accountHistoryDefault">

<h1 id="accountHistoryDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<?php
  if ($accountHasHistory === true) {
    foreach ($accountHistory as $history) {
?>
<fieldset>
<legend><?php echo TEXT_ORDER_NUMBER . $history['orders_id']; ?></legend>
<div class="notice forward"><?php echo TEXT_ORDER_STATUS . $history['orders_status_name']; ?></div>
<br class="clearBoth" />
    <div class="content back"><?php echo '<strong>' . TEXT_ORDER_DATE . '</strong> ' . zen_date_long($history['date_purchased']) . '<br /><strong>' . $history['order_type'] . '</strong> ' . zen_output_string_protected($history['order_name']); ?></div>
    <div class="content"><?php echo '<strong>' . TEXT_ORDER_PRODUCTS . '</strong> ' . $history['product_count'] . '<br /><strong>' . TEXT_ORDER_COST . '</strong> ' . strip_tags($history['order_total']); ?></div>
    <div class="content forward"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'order_id=' . $history['orders_id'], 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_VIEW_SMALL, BUTTON_VIEW_SMALL_ALT) . '</a>'; ?></div>
<br class="clearBoth" />
</fieldset>
<?php
    }
?>
<div class="navSplitPagesLinks forward"><?php echo TEXT_RESULT_PAGE . ' ' . $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></div>
<div class="navSplitPagesResult"><?php echo $history_split->display_count(TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></div>
<?php
  } else {
?>
<div class="centerColumn" id="noAcctHistoryDefault">
<?php echo TEXT_NO_PURCHASES; ?>
</div>
<?php
  }
?>

<div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

</div>