<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Fri Jan 8 00:33:36 2016 -0500 Modified in v1.5.5 $
 */

?>
<div id="productQuantityDiscounts">
<?php
  if ($zc_hidden_discounts_on) {
?>
  <table id="quantityDiscountsDetails">
    <tr>
      <td colspan="1" class="alignCenter">
      <?php echo TEXT_HEADER_DISCOUNTS_OFF; ?>
      </td>
    </tr>
    <tr>
      <td colspan="1" class="alignCenter">
      <?php echo $zc_hidden_discounts_text; ?>
      </td>
    </tr>
  </table>
<?php } else { ?>
  <table id="quantityDiscountsDetails">
    <tr>
      <td colspan="<?php echo $columnCount+1; ?>" class="alignCenter">
<?php
  switch ($products_discount_type) {
    case '1':
      echo TEXT_HEADER_DISCOUNT_PRICES_PERCENTAGE;
      break;
    case '2':
      echo TEXT_HEADER_DISCOUNT_PRICES_ACTUAL_PRICE;
      break;
    case '3':
      echo TEXT_HEADER_DISCOUNT_PRICES_AMOUNT_OFF;
      break;
  }
?>
      </td>
    </tr>

    <tr>
      <td class="alignCenter"><?php echo $show_qty . '<br />' . $currencies->display_price($show_price, zen_get_tax_rate($products_tax_class_id)); ?></td>

<?php
  foreach($quantityDiscounts as $key=>$quantityDiscount) {
?>
<td class="alignCenter"><?php echo $quantityDiscount['show_qty'] . '<br />' . $currencies->display_price($quantityDiscount['discounted_price'], zen_get_tax_rate($products_tax_class_id)); ?></td>
<?php
    $disc_cnt++;
    if ($discount_col_cnt == $disc_cnt && !($key == sizeof($quantityDiscount))) {
      $disc_cnt=0;
?>
  </tr><tr>
<?php
    }
  }
?>
<?php
  if ($disc_cnt < $columnCount) {
?>
    <td class="alignCenter" colspan="<?php echo ($columnCount+1 - $disc_cnt)+1; ?>"> &nbsp; </td>
<?php } ?>
    </tr>
<?php
  if (zen_has_product_attributes($products_id_current)) {
?>
    <tr>
      <td colspan="<?php echo $columnCount+1; ?>" class="alignCenter">
        <?php echo TEXT_FOOTER_DISCOUNT_QUANTITIES; ?>
      </td>
    </tr>
<?php } ?>
  </table>
<?php } // hide discounts ?>
</div>