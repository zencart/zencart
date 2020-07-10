<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Fri Jan 8 00:33:36 2016 -0500 Modified in v1.5.5 $
 */
?>
<body id="popupAtrribsQuantityPricesHelp">
<p class="button forward"><?php echo '<a href="javascript:window.close()">' . TEXT_CURRENT_CLOSE_WINDOW . '</a>'; ?></p>
<div class="popupattributeqty">
<h2 class="pageHeading"><?php echo TEXT_ATTRIBUTES_QTY_PRICES_HELP ?></h2>
<?php
$show_onetime= 'false';
// attributes_qty_price
      if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
        $options_order_by= ' order by LPAD(popt.products_options_sort_order,11,"0")';
      } else {
        $options_order_by= ' order by popt.products_options_name';
      }

      $sql = "select distinct popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
                              popt.products_options_type, popt.products_options_length, popt.products_options_comment, popt.products_options_size
              from        " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
              where           patrib.products_id='" . (int)$_GET['products_id'] . "'
              and             patrib.options_id = popt.products_options_id
              and             popt.language_id = '" . (int)$_SESSION['languages_id'] . "' " .
              $options_order_by;

      $products_options_names_lookup = $db->Execute($sql);

      while (!$products_options_names_lookup->EOF) {

        if ( PRODUCTS_OPTIONS_SORT_BY_PRICE =='1' ) {
          $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pov.products_options_values_name';
        } else {
          $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pa.options_values_price';
        }

        $sql = "select    pov.products_options_values_id,
                          pov.products_options_values_name,
                          pa.*
                from      " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                where     pa.products_id = '" . (int)$_GET['products_id'] . "'
                and       pa.options_id = '" . (int)$products_options_names_lookup->fields['products_options_id'] . "'
                and       pa.options_values_id = pov.products_options_values_id
                and       pov.language_id = '" . (int)$_SESSION['languages_id'] . "' " .
                $order_by;

        $products_options_lookup = $db->Execute($sql);

        while (!$products_options_lookup->EOF) {
          $cnt_qty_prices= 0;

          // set for attributes_qty_prices_onetime
          if ($products_options_lookup->fields['attributes_qty_prices_onetime'] != '') {
            $show_onetime= 'true';
          }

          if ($products_options_lookup->fields['attributes_qty_prices'] != '') {
            $attribute_quantity= '';
            $attribute_quantity_price= '';
            $attribute_table_cost = preg_split("/[:,]/" , $products_options_lookup->fields['attributes_qty_prices']);
            $size = sizeof($attribute_table_cost);
            for ($i=0, $n=$size; $i<$n; $i+=2) {
//                $attribute_quantity .= '<td class="alignCenter">' . (($i <= 1 and $attribute_table_cost[$i] != 1) ? '1-' . $attribute_table_cost[$i] : $attribute_table_cost[$i] . '+') . '</td>';
                $zc_disp_qty = '';
                switch (true) {
                  case ($i+2==$n):
                    $zc_disp_qty = $attribute_table_cost[$i-2]+1 . '+';
                    break;
                  case ($i <= 1 and $attribute_table_cost[$i] == 1):
                    $zc_disp_qty = '1';
                    break;
                  case ($i <= 1 and $attribute_table_cost[$i] != 1):
                    $zc_disp_qty = '1-' . $attribute_table_cost[$i];
                    break;
                  case ($i > 1 and $attribute_table_cost[$i-2]+1 != $attribute_table_cost[$i]):
                    $zc_disp_qty = $attribute_table_cost[$i-2]+1 . '-' . $attribute_table_cost[$i];
                    break;
                  case ($i > 1 and $attribute_table_cost[$i-2]+1 == $attribute_table_cost[$i]):
                    $zc_disp_qty = $attribute_table_cost[$i];
                    break;
                }
//                $attribute_quantity .= '<td class="alignCenter">' . (($i <= 1 and $attribute_table_cost[$i] != 1) ? '1-' . $attribute_table_cost[$i] : $attribute_table_cost[$i-2]+1 . '-' . $attribute_table_cost[$i]) . '</td>';
                $attribute_quantity .= '<td class="alignCenter">' . $zc_disp_qty . '</td>';
                $attribute_quantity_price .= '<td class="alignRight">' . $currencies->display_price($attribute_table_cost[$i+1], zen_get_tax_rate($_GET['products_tax_class_id'])) . '</td>';
                $cnt_qty_prices++;
            }
            echo '<table class="tableBorder1">';
            echo '  <tr><td colspan="' . ($cnt_qty_prices + 1) . '">' . $products_options_names_lookup->fields['products_options_name'] . ' ' . $products_options_lookup->fields['products_options_values_name'] . '</td></tr>';
            echo '  <tr>';
            echo '    <td>' . TABLE_ATTRIBUTES_QTY_PRICE_QTY . '</td>' . $attribute_quantity;
            echo '  </tr>';
            echo '  <tr>';
            echo '    <td>' . TABLE_ATTRIBUTES_QTY_PRICE_PRICE . '</td>' . $attribute_quantity_price;
            echo '  </tr>';
            echo '</table><br>';
          }
          $products_options_lookup->MoveNext();
        }
        $products_options_names_lookup->MoveNext();
      }
?>
</div>


<?php
  if ($show_onetime == 'true') {
?>

<h2 class="pageHeading"><?php echo TEXT_ATTRIBUTES_QTY_PRICES_ONETIME_HELP ?></h2>
<div class="popupQtyOneTime">
<?php
// attributes_qty_price_onetime
      if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
        $options_order_by= ' order by LPAD(popt.products_options_sort_order,11,"0")';
      } else {
        $options_order_by= ' order by popt.products_options_name';
      }

      $sql = "select distinct popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
                              popt.products_options_type, popt.products_options_length, popt.products_options_comment, popt.products_options_size
              from        " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
              where           patrib.products_id='" . (int)$_GET['products_id'] . "'
              and             patrib.options_id = popt.products_options_id
              and             popt.language_id = '" . (int)$_SESSION['languages_id'] . "' " .
              $options_order_by;

      $products_options_names_lookup = $db->Execute($sql);

      while (!$products_options_names_lookup->EOF) {

        if ( PRODUCTS_OPTIONS_SORT_BY_PRICE =='1' ) {
          $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pov.products_options_values_name';
        } else {
          $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pa.options_values_price';
        }

        $sql = "select    pov.products_options_values_id,
                          pov.products_options_values_name,
                          pa.*
                from      " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                where     pa.products_id = '" . (int)$_GET['products_id'] . "'
                and       pa.options_id = '" . (int)$products_options_names_lookup->fields['products_options_id'] . "'
                and       pa.options_values_id = pov.products_options_values_id
                and       pov.language_id = '" . (int)$_SESSION['languages_id'] . "' " .
                $order_by;

        $products_options_lookup = $db->Execute($sql);

        while (!$products_options_lookup->EOF) {
          $cnt_qty_prices= 0;
          if ($products_options_lookup->fields['attributes_qty_prices_onetime'] != '') {
            $attribute_quantity= '';
            $attribute_quantity_price= '';
            $attribute_table_cost = preg_split("/[:,]/" , $products_options_lookup->fields['attributes_qty_prices_onetime']);
            $size = sizeof($attribute_table_cost);
            for ($i=0, $n=$size; $i<$n; $i+=2) {
                $attribute_quantity .= '<td class="alignCenter">' . $attribute_table_cost[$i] . '</td>';
//                $attribute_quantity_price .= '<td class="alignRight">' . $attribute_table_cost[$i+1] . '</td>';
                $attribute_quantity_price .= '<td class="alignRight">' . $currencies->display_price($attribute_table_cost[$i+1], zen_get_tax_rate($_GET['products_tax_class_id'])) . '</td>';
                $cnt_qty_prices++;
            }
            echo '<table class="tableBorder1">';
            echo '  <tr><td colspan="' . ($cnt_qty_prices + 1) . '">' . $products_options_names_lookup->fields['products_options_name'] . ' ' . $products_options_lookup->fields['products_options_values_name'] . '</td></tr>';
            echo '  <tr>';
            echo '    <td>' . TABLE_ATTRIBUTES_QTY_PRICE_QTY . '</td>' . $attribute_quantity;
            echo '  </tr>';
            echo '  <tr>';
            echo '    <td>' . TABLE_ATTRIBUTES_QTY_PRICE_PRICE . '</td>' . $attribute_quantity_price;
            echo '  </tr>';
            echo '</table>';
          }
          $products_options_lookup->MoveNext();
        }
          $products_options_names_lookup->MoveNext();
      }

?>
</div>
<?php } // show onetime ?>

<p class="button forward"><?php echo '<a href="javascript:window.close()">' . TEXT_CURRENT_CLOSE_WINDOW . '</a>'; ?></p>
</body>
