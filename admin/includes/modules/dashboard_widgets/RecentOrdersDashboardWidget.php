<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 18 New in v1.5.7 $
 */

if (!zen_is_superuser() && !check_page(FILENAME_ORDERS, '')) return;

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

$maxRows = 25;

$sql = "SELECT o.orders_id as orders_id, o.customers_name as customers_name, o.customers_id,
                   o.date_purchased as date_purchased, o.currency, o.currency_value,
                   ot.class, ot.text as order_total, ot.value as order_value
            FROM " . TABLE_ORDERS . " o
            LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON (o.orders_id = ot.orders_id AND class = 'ot_total')
            ORDER BY orders_id DESC";
$orders = $db->Execute($sql, (int)$maxRows, true, 1800);

require_once(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php';

?>
<div class="panel panel-default reportBox">
    <div class="panel-heading header"><?php echo BOX_ENTRY_NEW_ORDERS; ?> </div>
    <table class="table table-striped table-condensed">
        <?php

        foreach ($orders as $order) {
          $order['customers_name'] = str_replace('N/A', '', $order['customers_name']);

          $amt = $currencies->format($order['order_value'], false);
          if ($order['currency'] != DEFAULT_CURRENCY) {
            $amt .= ' (' . $order['order_total'] . ')';
          }

          $order_pop = new order($order['orders_id']);
          $product_details = $order_pop->order_products_popover($order['orders_id']);

        ?>
        <tr>
          <td>
            <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'oID=' . $order['orders_id'] . '&origin=' . FILENAME_DEFAULT); ?>" class="contentlink">
                <?php echo $order['orders_id'] . ' - ' . substr($order['customers_name'], 0, 20); ?>
            </a>
          </td>
          <td class="text-right" title="<?php echo zen_output_string($product_details, array('"' => '&quot;', "'" => '&#39;', '<br />' => '', '<hr>' => "----\n")); ?>">
            <?php echo $amt; ?>
          </td>
          <td class="text-right"><?php echo zen_date_short($order['date_purchased']); ?></td>
          <td class="text-center">
              <a tabindex="0" class="btn btn-xs btn-link orderProductsPopover" role="button" data-toggle="popover"
                 data-trigger="focus"
                 data-placement="left"
                 title="<?php echo TEXT_PRODUCT_POPUP_TITLE; ?>"
                 data-content="<?php echo zen_output_string($product_details, array('"' => '&quot;', "'" => '&#39;', '<br />' => '<br>')); ?>"
              >
                  <?php echo TEXT_PRODUCT_POPUP_BUTTON; ?>
              </a>
          </td>
        </tr>
      <?php } ?>
    </table>
</div>
<!--  enable popovers-->
<script>
    jQuery(function () {
        jQuery('[data-toggle="popover"]').popover({html:true,sanitize: true})
    })
</script>

