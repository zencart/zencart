<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Jul 17 22:56:16 2012 -0400 Modified in v1.5.1 $
 */
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $_GET['start_date'] = (!isset($_GET['start_date']) ? date("m-d-Y",(time())) : $_GET['start_date']);
  $_GET['end_date'] = (!isset($_GET['end_date']) ? date("m-d-Y",(time())) : $_GET['end_date']);
  $_GET['referral_code'] = (!isset($_GET['referral_code']) ? '0' : $_GET['referral_code']);

  include(DIR_WS_CLASSES . 'order.php');
require('includes/admin_html_head.php');
?>
</head>
<body>
<!-- header //-->
<?php
  require(DIR_WS_INCLUDES . 'header.php');
?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td>

      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>

      <tr>
        <td><table border="0" cellspacing="0" cellpadding="0">
<?php
// select all customer_referrals
  $customers_referral_query = "select distinct customers_referral from " . TABLE_CUSTOMERS . " where customers_referral != ''";
  $customers_referral = $db->Execute($customers_referral_query);

  $customers_referrals = array();
  $customers_referrals_array = array();
  $customers_referrals[] = array('id' => '0',
                                 'text' => TEXT_REFERRAL_UNKNOWN);

  while (!$customers_referral->EOF) {
    $customers_referrals[] = array('id' => $customers_referral->fields['customers_referral'],
                                   'text' => $customers_referral->fields['customers_referral']);
    $customers_referral->MoveNext();
  }

?>
          <tr>
            <td><table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="main"><?php echo TEXT_INFO_SELECT_REFERRAL; ?></td>
                <td class="main">
                  <?php
                    echo zen_draw_form('new_date', FILENAME_STATS_CUSTOMERS_REFERRALS, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('referral_code', $customers_referrals, $_GET['referral_code'], 'onChange="this.form.submit();"') .
                     zen_hide_session_id() .
                     zen_draw_hidden_field('action', 'new_date') .
                     zen_draw_hidden_field('start_date', $_GET['start_date']) .
                     zen_draw_hidden_field('end_date', $_GET['end_date']);
                  ?>
                 &nbsp;&nbsp;</form>
                </td>
              </tr>
            </td></table>
          </tr>

          <tr>
            <td><table border="0" width="100%" cellspacing="2" cellpadding="2">
              <tr><?php echo zen_draw_form('search', FILENAME_STATS_CUSTOMERS_REFERRALS, '', 'get'); echo zen_draw_hidden_field('referral_code', $_GET['referral_code']); ?>
                <td class="main" align="right"><?php echo TEXT_INFO_START_DATE . ' ' . zen_draw_input_field('start_date', $_GET['start_date']); ?></td>
                <td class="main" align="right"><?php echo TEXT_INFO_END_DATE . ' ' . zen_draw_input_field('end_date', $_GET['end_date']) . zen_hide_session_id(); ?></td>
                <td class="main" align="right"><?php echo zen_image_submit('button_display.gif', IMAGE_DISPLAY); ?></td>
              </tr>
            </td></table></form>
          </tr>

<?php
// reverse date from m-d-y to y-m-d
    $date1 = explode("-", $_GET['start_date']);
    $m1 = $date1[0];
    $d1 = $date1[1];
    $y1 = $date1[2];

    $date2 = explode("-", $_GET['end_date']);
    $m2 = $date2[0];
    $d2 = $date2[1];
    $y2 = $date2[2];

    $sd = $y1 . '-' . $m1 . '-' . $d1 . ' 00:00:00';
    $ed = $y2. '-' . $m2 . '-' . $d2 .  ' 23:59:59';

//  $sd = $_GET['start_date'];
//  $ed = $_GET['end_date'];
  if ($_GET['referral_code'] == '0') {
    $customers_orders_query = "select c.customers_id, c.customers_referral, o.orders_id, o.date_purchased, o.order_total, o.coupon_code from " . TABLE_CUSTOMERS . " c, " . TABLE_ORDERS . " o where c.customers_id = o.customers_id and c.customers_referral= '' and (o.date_purchased >= :sd: and o.date_purchased <= :ed:) order by o.date_purchased, o.orders_id";
  } else {
    $customers_orders_query = "select c.customers_id, c.customers_referral, o.orders_id, o.date_purchased, o.order_total, o.coupon_code from " . TABLE_CUSTOMERS . " c, " . TABLE_ORDERS . " o where c.customers_id = o.customers_id and c.customers_referral=:refcode: and (o.date_purchased >= :sd: and o.date_purchased <= :ed:) order by o.date_purchased, o.orders_id";
  }
  $customers_orders_query = $db->bindVars($customers_orders_query, ':ed:', $ed, 'date');
  $customers_orders_query = $db->bindVars($customers_orders_query, ':sd:', $sd, 'date');
  $customers_orders_query = $db->bindVars($customers_orders_query, ':refcode:', $_GET['referral_code'], 'string');
  $customers_orders = $db->Execute($customers_orders_query);
?>
          <tr>
            <td><table border="0" width="100%" cellspacing="2" cellpadding="2">
<?php
//echo 'I see ' . $customers_orders->RecordCount() . '<br>' . $customers_orders_query . '<br><br>' . 'start ' . date($_GET['start_date']) . ' end ' . date($_GET['end_date']) . '<br>Referral: ' . $_GET['referral_code'] . ' ' . strlen($_GET['referral_code']) . '<br>';
  while (!$customers_orders->EOF) {
//    echo $customers_orders->fields['orders_id'] . ' ' . $customers_orders->fields['order_total'] . '<br />';
    $current_orders_id = $customers_orders->fields['orders_id'];

    $orders_total_query = "select * from " . TABLE_ORDERS_TOTAL . " where orders_id='" . $current_orders_id . "'";
    $orders_total = $db->Execute($orders_total_query);

    $order = new order($customers_orders->fields['orders_id']);
?>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" align="left"><?php echo zen_date_long($customers_orders->fields['date_purchased']); ?></td>
            <td class="main" align="left"><?php echo TEXT_ORDER_NUMBER; ?> <?php echo $customers_orders->fields['orders_id']; ?></td>
            <td class="main" align="left"><?php echo TEXT_COUPON_ID; ?> <?php echo $customers_orders->fields['coupon_code']; ?></td>
            <td class="main" align="left"><?php echo '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $customers_orders->fields['orders_id'] . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>'; ?></td>
          </tr>
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="2">
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <tr>' . "\n" .
           '                <td align="left" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Text">' . $order->totals[$i]['title'] . '</td>' . "\n" .
           '                <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Amount">' . $order->totals[$i]['text'] . '</td>' . "\n" .
           '              </tr>' . "\n";
    }
?>
            </table></td>
          </tr>
<?php
    $customers_orders->MoveNext();
  }
?>

<!--
              </tr>
            </td></table></form>
          </tr>
-->

        </table></td>
      </tr>

    </td>
  </tr>
</table>
