<?php
/**
 * product_info_prices.php
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: dbltoe 2022 Nov 10 Modified in v1.5.8a $
 */


if ($debug_on == '1') {
  echo 'Looking at ' . (int)$_GET['products_id'] . '<br>';
  echo 'Base Price ' . zen_get_products_base_price((int)$_GET['products_id']) . '<br>';
  echo 'Actual Price ' . zen_get_products_actual_price((int)$_GET['products_id']) . '<br>';
  echo 'Special Price ' . zen_get_products_special_price((int)$_GET['products_id'], true) . '<br>';
  echo 'Sale Maker Discount Type ' . zen_get_products_sale_discount_type((int)$_GET['products_id']) . '<br>';
  echo 'Discount Calc ' . zen_get_discount_calc((int)$_GET['products_id']) . '<br>';
  echo 'Discount Calc Attr $100 $75 $50 $25 ' . zen_get_discount_calc((int)$_GET['products_id'], true, 100) . ' | ' . zen_get_discount_calc((int)$_GET['products_id'], true, 75) . ' | ' . zen_get_discount_calc((int)$_GET['products_id'], true, 50) . ' | ' . zen_get_discount_calc((int)$_GET['products_id'], true, 25) . '<br>';

  echo '<br> Start of page - product <br>' .
  zen_get_show_product_switch($products_id_current, 'weight') . '<br>' .
  zen_get_show_product_switch($products_id_current, 'weight_attributes') . '<br>' .
  zen_get_show_product_switch($products_id_current, 'date_added') . '<br>' .
  zen_get_show_product_switch($products_id_current, 'quantity') . '<br>' .
  zen_get_show_product_switch($products_id_current, 'model') . '<br>' .
  SHOW_PRODUCT_INFO_WEIGHT_ATTRIBUTES . '<br>' .
  SHOW_PRODUCT_INFO_WEIGHT . '<br>' .
  SHOW_PRODUCT_INFO_MANUFACTURER . '<br>' .
  SHOW_PRODUCT_INFO_QUANTITY . '<br>' .
  '<br>';
}
?>
<?php
if (false) {
echo 'Looking at ' . (int)$_GET['products_id'] . '<br>';
echo 'Base Price ' . zen_get_products_base_price((int)$_GET['products_id']) . '<br>';
echo 'Actual Price ' . zen_get_products_actual_price((int)$_GET['products_id']) . '<br>';
echo 'Special Price ' . zen_get_products_special_price((int)$_GET['products_id'], true) . '<br>';
echo 'Sale Maker Discount Type ' . zen_get_products_sale_discount_type((int)$_GET['products_id']) . '<br>';
echo 'Discount Calc ' . zen_get_discount_calc((int)$_GET['products_id']) . '<br>';
echo 'Discount Calc Attr $100 $75 $50 $25 ' . zen_get_discount_calc((int)$_GET['products_id'], true, 100) . ' | ' . zen_get_discount_calc((int)$_GET['products_id'], true, 75) . ' | ' . zen_get_discount_calc((int)$_GET['products_id'], true, 50) . ' | ' . zen_get_discount_calc((int)$_GET['products_id'], true, 25) . '<br>';
}
?>