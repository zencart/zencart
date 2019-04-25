<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Tue Oct 2 19:29:10 2018 +0200 Modified in v1.5.6 $
 */
require('includes/application_top.php');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$oID = zen_db_prepare_input($_GET['oID']);

include DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php';
$order = new order($oID);

// prepare order-status pulldown list
$orders_statuses = array();
$orders_status_array = array();
$orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                               FROM " . TABLE_ORDERS_STATUS . "
                               WHERE language_id = " . (int)$_SESSION['languages_id']);
foreach ($orders_status as $order_status) {
  $orders_statuses[] = array(
    'id' => $order_status['orders_status_id'],
    'text' => $order_status['orders_status_name'] . ' [' . $order_status['orders_status_id'] . ']');
  $orders_status_array[$order_status['orders_status_id']] = $order_status['orders_status_name'];
}

$show_customer = false;
if ($order->billing['name'] != $order->delivery['name']) {
  $show_customer = true;
}
if ($order->billing['street_address'] != $order->delivery['street_address']) {
  $show_customer = true;
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
  </head>
  <body>
    <div class="container">
      <!-- body_text //-->
      <table class="table">
        <tr>
          <td class="pageHeading"><?php echo nl2br(STORE_NAME_ADDRESS); ?></td>
          <td class="pageHeading" align="right"><?php echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT); ?></td>
        </tr>
      </table>
      <div><?php echo zen_draw_separator(); ?></div>
      <table class="table">
          <?php
          if ($show_customer == true) {
            ?>
          <tr>
            <td class="main" colspan="2"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><?php echo zen_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?></td>
          </tr>
        <?php } ?>
        <tr>
          <td style="border: none">
            <table>
              <tr>
                <td class="main"><b><?php echo ENTRY_SOLD_TO; ?></b></td>
              </tr>
              <tr>
                <td class="main"><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main">
                    <?php echo ENTRY_TELEPHONE_NUMBER . ' ' . $order->customer['telephone']; ?>
                </td>
              </tr>
              <tr>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
              </tr>
            </table>
          </td>
          <td style="border: none">
            <table>
              <tr>
                <td class="main"><b><?php echo ENTRY_SHIP_TO; ?></b></td>
              </tr>
              <tr>
                <td class="main"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <table>
        <tr>
          <td class="main"><strong><?php echo ENTRY_ORDER_ID; ?></strong></td>
          <td class="main"><?php echo $oID; ?></td>
        </tr>
        <tr>
          <td class="main"><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong></td>
          <td class="main"><?php echo zen_date_long($order->info['date_purchased']); ?></td>
        </tr>
        <tr>
          <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
          <td class="main"><?php echo $order->info['payment_method']; ?></td>
        </tr>
      </table>
      <div><?php echo zen_draw_separator('pixel_trans.gif', '', '10'); ?></div>
      <table class="table table-striped">
        <thead>
          <tr class="dataTableHeadingRow">
            <th class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
            <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
          </tr>
        </thead>
        <tbody>
            <?php
            for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
              ?>
            <tr class="dataTableRow">
              <td class="dataTableContent text-right"><?php echo $order->products[$i]['qty']; ?>&nbsp;x</td>
              <td class="dataTableContent"><?php echo $order->products[$i]['name']; ?>
                  <?php
                  if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
                    ?>
                  <ul>
                      <?php
                      for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
                        ?>
                      <li>
                        <small><i>
                                <?php echo $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value'])); ?>
                          </i></small>
                      </li>
                      <?php
                    }
                    ?>
                  </ul>
                  <?php
                }
                ?>
              </td>
              <td class="dataTableContent"><?php echo $order->products[$i]['model']; ?></td>
            </tr>
            <?php
          }
          ?>
        </tbody>
      </table>
      <?php if (ORDER_COMMENTS_PACKING_SLIP > 0) { ?>
        <table class="table table-condensed" style="width:25%;">
          <thead>
            <tr>
              <th class="text-center"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></th>
              <th class="text-center"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></th>
              <th class="text-center"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></th>
            </tr>
          </thead>
          <tbody>
              <?php
              $orders_history = $db->Execute("SELECT orders_status_id, date_added, customer_notified, comments
                                            FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                                            WHERE orders_id = " . zen_db_input($oID) . "
                                            AND customer_notified >= 0
                                            ORDER BY date_added");

              if ($orders_history->RecordCount() > 0) {
                $count_comments = 0;
                foreach ($orders_history as $order_history) {
                  $count_comments++;
                  ?>
                <tr>
                  <td class="text-center"><?php echo zen_datetime_short($order_history['date_added']); ?></td>
                  <td><?php echo $orders_status_array[$order_history['orders_status_id']]; ?></td>
                  <td><?php echo ($order_history['comments'] == '' ? TEXT_NONE : nl2br(zen_db_output($order_history['comments']))); ?>&nbsp;</td>
                </tr>
                <?php
                if (ORDER_COMMENTS_PACKING_SLIP == 1 && $count_comments >= 1) {
                  break;
                }
              }
            } else {
              ?>
              <tr>
                <td colspan="3"><?php echo TEXT_NO_ORDER_HISTORY; ?></td>
              </tr>
              <?php
            }
            ?>
          </tbody>
        </table>
      <?php } // order comments ?>
    </div>

    <!-- body_text_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
