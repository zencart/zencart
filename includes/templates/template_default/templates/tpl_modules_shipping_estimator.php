<?php
/**
 * Module Template - for shipping-estimator display
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2024 May 31 Modified in v2.1.0-alpha1 $
 */
?>
<div id="shippingEstimatorContent">
<?= zen_draw_form('estimator', zen_href_link($show_in . '#seView', '', $request_type), 'post') ?>
<?php if (is_array($selected_shipping)) {
    echo zen_draw_hidden_field('scid', $selected_shipping['id']);
} ?>
<?= zen_draw_hidden_field('action', 'submit') ?>
<?php
  if ($_SESSION['cart']->count_contents() !== 0) {
    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
?>
<h2><?= CART_SHIPPING_OPTIONS ?></h2>

<?php if (!empty($totalsDisplay)) { ?>
<div class="cartTotalsDisplay important"><?= $totalsDisplay ?></div>
<?php } ?>

<?php
    // only display addresses if more than 1
      if ($addresses->RecordCount() > 1){
?>
<label class="inputLabel" for="seAddressPulldown"><?= CART_SHIPPING_METHOD_ADDRESS ?></label>
<?= zen_draw_pull_down_menu('address_id', $addresses_array, $selected_address, 'onchange="return shipincart_submit();" id="seAddressPulldown"') ?>
<?php
      }
?>

<div class="bold back" id="seShipTo"><?= CART_SHIPPING_METHOD_TO ?></div>
<address class="back"><?= zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>') ?></address>
<br class="clearBoth">
<?php
    } else {
?>
<h2><?= CART_SHIPPING_OPTIONS ?></h2>
<?php if (!empty($totalsDisplay)) { ?>
<div class="cartTotalsDisplay important"><?= $totalsDisplay ?></div>
<?php } ?>
<?php
      if ($_SESSION['cart']->get_content_type() != 'virtual') {
          $flag_show_pulldown_states = (ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN === 'true');
?>

<label class="inputLabel" for="country"><?= ENTRY_COUNTRY ?></label>
<?= zen_get_country_list('zone_country_id', $selected_country, 'id="country"' . (($flag_show_pulldown_states) ? ' onchange="update_zone(this.form);"' : '')) ?>
<br class="clearBoth">

<a id="seView"></a>
<label class="inputLabel" for="stateZone" id="zoneLabel"><?= ENTRY_STATE ?></label>
<?php
          if ($flag_show_pulldown_states) {
?>
<?= zen_draw_pull_down_menu('zone_id', zen_prepare_country_zones_pull_down($selected_country), $state_zone_id, 'id="stateZone"') ?>
<br class="clearBoth" id="stBreak">
<?php
          }
?>
<label class="inputLabel" for="state" id="stateLabel"><?= ($state_field_label ?? '') ?></label>
<?= zen_draw_input_field('state', $selectedState, zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_state', '40') . ' id="state"') .'&nbsp;<span class="alert" id="stText">&nbsp;</span>' ?>
<br class="clearBoth">

<?php
        if (CART_SHIPPING_METHOD_ZIP_REQUIRED === 'true'){
?>
<label class="inputLabel" for="postcode"><?= ENTRY_POST_CODE ?></label>
<?= zen_draw_input_field('postcode', $postcode, 'size="7" id="postcode"') ?>
<br class="clearBoth">
<?php
        }
?>
<div class="buttonRow forward"><?= zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT) ?></div>
<br class="clearBoth">
<?php
      }
    }
    if ($_SESSION['cart']->get_content_type() === 'virtual') {
?>
<?= CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS ?>
<?php
    } elseif ($free_shipping == 1) {
?>
<?= sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) ?>
<?php
    } else {
?>
<table id="seQuoteResults">
<?php if (!zen_is_logged_in() || zen_in_guest_checkout()) { ?>
    <tr>
      <td colspan="2" class="seDisplayedAddressLabel">
        <?= CART_SHIPPING_QUOTE_CRITERIA ?><br>
        <?= '<span class="seDisplayedAddressInfo">' .
            zen_get_zone_name((int)$selected_country, (int)$state_zone_id, '') .
            ($selectedState != '' ? ' ' . $selectedState : '') . ' ' .
            ($order->delivery['postcode'] ?? '') . ' ' .
            zen_get_country_name($order->delivery['country_id']) .
            '</span>' ?>
      </td>
    </tr>
<?php } ?>
     <tr>
       <th scope="col" id="seProductsHeading"><?= CART_SHIPPING_METHOD_TEXT ?></th>
       <th scope="col" id="seTotalHeading"><?= CART_SHIPPING_METHOD_RATES ?></th>
     </tr>
<?php
      for ($i=0, $n=count($quotes); $i<$n; $i++) {
        $thisquoteid = '';
        if (isset($quotes[$i]['id']) && count($quotes[$i]['methods']) === 1 && isset($quotes[$i]['methods'][0]['id'])){
          // simple shipping method
          $thisquoteid = $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][0]['id'];
?>
     <tr<?= (empty($extra) ? '' : ' class="' . $extra . '"') ?>>
<?php
          if (isset($quotes[$i]['error']) && $quotes[$i]['error']){
?>
         <td colspan="2"><?= $quotes[$i]['module'] ?>&nbsp;(<?= $quotes[$i]['error'] ?>)</td>
<?php
          } elseif ($selected_shipping['id'] === $thisquoteid){
?>
         <td class="bold"><?= $quotes[$i]['module'] ?>&nbsp;(<?= $quotes[$i]['methods'][0]['title'] ?>)</td>
         <td class="cartTotalDisplay bold"><?= $currencies->format(zen_add_tax($quotes[$i]['methods'][0]['cost'], $quotes[$i]['tax'] ?? 0)) ?></td>
<?php
            } else {
?>
          <td><?= $quotes[$i]['module'] ?>&nbsp;(<?= $quotes[$i]['methods'][0]['title'] ?>)</td>
          <td class="cartTotalDisplay"><?= $currencies->format(zen_add_tax($quotes[$i]['methods'][0]['cost'], $quotes[$i]['tax'] ?? 0)) ?></td>
<?php
            } ?>
    </tr>
<?php     } else {
          // shipping method with sub methods (multipickup) or none
          for ($j=0, $n2=(empty($quotes[$i]['methods']) ? 0 : count($quotes[$i]['methods'])); $j<$n2; $j++) {
            $thisquoteid = '';
            if (isset($quotes[$i]['id']) && isset($quotes[$i]['methods'][$j]['id'])) {
                $thisquoteid = $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'];
            }
?>
       <tr<?= (empty($extra) ? '' : ' class="' . $extra . '"') ?>>
<?php
            if (!empty($quotes[$i]['error'])){
?>
         <td colspan="2"><?= $quotes[$i]['module'] ?>&nbsp;(<?= $quotes[$i]['error'] ?>)</td>
<?php
            } elseif ($selected_shipping['id'] === $thisquoteid){
?>
         <td class="bold"><?= $quotes[$i]['module'] ?>&nbsp;(<?= $quotes[$i]['methods'][$j]['title'] ?>)</td>
         <td class="cartTotalDisplay bold"><?= $currencies->format(zen_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax'] ?? 0)) ?></td>
<?php
              } else {
?>
        <td><?= $quotes[$i]['module'] ?>&nbsp;(<?= $quotes[$i]['methods'][$j]['title'] ?>)</td>
        <td class="cartTotalDisplay"><?= $currencies->format(zen_add_tax($quotes[$i]['methods'][$j]['cost'],$quotes[$i]['tax'] ?? 0)) ?></td>
<?php
              } ?>
       </tr>
<?php      }
        }
      }
?>
</table>
<?php
      if (empty($quotes)) {
?>
      <div id="noShippingAvailable" class="alert important">
         <?= TEXT_NO_SHIPPING_AVAILABLE_ESTIMATOR ?>
      </div>
<?php
      }
   }
  }
echo '</form>'; ?>
</div>
