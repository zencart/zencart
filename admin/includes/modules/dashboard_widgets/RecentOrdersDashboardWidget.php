<?php
/**
 * @package admin
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.5.7 $
 */

// to disable this module, uncomment the following "return" statement so the rest of this file is ignored
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

          $sql = "SELECT op.products_quantity AS qty, op.products_name AS name, op.products_model AS model, opa.products_options AS product_option, opa.products_options_values AS product_value
                  FROM " . TABLE_ORDERS_PRODUCTS . " op
                  LEFT OUTER JOIN " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa ON op.orders_products_id=opa.orders_products_id
                  WHERE op.orders_id = " . (int)$order['orders_id'];

          $orderProducts = $db->Execute($sql, false, true, 1800);
          $product_details = '';
          foreach($orderProducts as $product) {
              $product_details .= $product['qty'] . ' x ' . $product['name'] . ' (' . $product['model'] . ')' . "\n";
              if (!empty($product['product_option'])) {
                  $product_details .= '&nbsp;&nbsp;- ' . $product['product_option'] . ': ' . zen_output_string_protected($product['product_value']) . "\n";
              }
              $product_details .= '<hr>'; // add HR
          }
          $product_details = rtrim($product_details);
          $product_details = preg_replace('~<hr>$~', '', $product_details); // remove last HR
          $product_details = nl2br($product_details);
        ?>
        <tr>
          <td>
            <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'oID=' . $order['orders_id'] . '&origin=' . FILENAME_DEFAULT); ?>" class="contentlink">
                <?php echo $order['orders_id'] . ' - ' . substr($order['customers_name'], 0, 20); ?>
            </a>
          </td>
          <td class="text-left">
              <a tabindex="0" class="btn btn-xs btn-link orderProductsPopover" role="button" data-toggle="popover"
                 data-trigger="focus"
                 data-placement="left"
                 title="<?php echo TEXT_PRODUCT_POPUP_TITLE; ?>"
                 data-content="<?php echo zen_output_string($product_details, array('"' => '&quot;', "'" => '&#39;', '<br />' => '<br>')); ?>"
              >
                  <?php echo TEXT_PRODUCT_POPUP_BUTTON; ?>
              </a>
          </td>
          <td class="text-right"><?php echo $amt; ?></td>
          <td class="text-right"><?php echo zen_date_short($order['date_purchased']); ?></td>
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

