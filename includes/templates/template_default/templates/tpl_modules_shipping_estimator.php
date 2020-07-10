<?php
/**
 * Module Template - for shipping-estimator display
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 May 07 Modified in v1.5.7 $
 */
?>
<div id="shippingEstimatorContent">
<?php echo zen_draw_form('estimator', zen_href_link($show_in . '#view', '', $request_type), 'post'); ?>
<?php if (is_array($selected_shipping)) {
    zen_draw_hidden_field('scid', $selected_shipping['id']);
} ?>
<?php echo zen_draw_hidden_field('action', 'submit'); ?>
<?php
  if($_SESSION['cart']->count_contents()) {
    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
?>
<h2><?php echo CART_SHIPPING_OPTIONS; ?></h2>


<?php if (!empty($totalsDisplay)) { ?>
<div class="cartTotalsDisplay important"><?php echo $totalsDisplay; ?></div>
<?php } ?>

<?php
    // only display addresses if more than 1
      if ($addresses->RecordCount() > 1){
?>
<label class="inputLabel" for="seAddressPulldown"><?php echo CART_SHIPPING_METHOD_ADDRESS; ?></label>
<?php echo zen_draw_pull_down_menu('address_id', $addresses_array, $selected_address, 'onchange="return shipincart_submit();" id="seAddressPulldown"'); ?>
<?php
      }
?>

<div class="bold back" id="seShipTo"><?php echo CART_SHIPPING_METHOD_TO; ?></div>
<address class="back"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?></address>
<br class="clearBoth" />
<?php
    } else {
?>
<h2><?php echo CART_SHIPPING_OPTIONS; ?></h2>
<?php if (!empty($totalsDisplay)) { ?>
<div class="cartTotalsDisplay important"><?php echo $totalsDisplay; ?></div>
<?php } ?>
<?php
      if($_SESSION['cart']->get_content_type() != 'virtual'){
?>

<label class="inputLabel" for="country"><?php echo ENTRY_COUNTRY; ?></label>
<?php echo zen_get_country_list('zone_country_id', $selected_country, 'id="country" onchange="update_zone(this.form);"'); ?>
<br class="clearBoth" />

<a name="view"></a>
<label class="inputLabel" for="stateZone" id="zoneLabel"><?php echo ENTRY_STATE; ?></label>
<?php echo zen_draw_pull_down_menu('zone_id', zen_prepare_country_zones_pull_down($selected_country), $state_zone_id, 'id="stateZone"');?>
<br class="clearBoth" id="stBreak" />
<label class="inputLabel" for="state" id="stateLabel"><?php echo (isset($state_field_label) ? $state_field_label : ''); ?></label>
<?php echo zen_draw_input_field('state', $selectedState, zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_state', '40') . ' id="state"') .'&nbsp;<span class="alert" id="stText">&nbsp;</span>'; ?>
<br class="clearBoth" />

<?php
        if(CART_SHIPPING_METHOD_ZIP_REQUIRED == "true"){
?>
<label class="inputLabel"><?php echo ENTRY_POST_CODE; ?></label>
<?php echo  zen_draw_input_field('zip_code', $zip_code, 'size="7"'); ?>
<br class="clearBoth" />
<?php
        }
?>
<div class="buttonRow forward"><?php echo  zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT); ?></div>
<br class="clearBoth" />
<?php
      }
    }
    if($_SESSION['cart']->get_content_type() == 'virtual'){
?>
<?php echo CART_SHIPPING_METHOD_FREE_TEXT .  ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS; ?>
<?php
    }elseif ($free_shipping==1) {
?>
<?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)); ?>
<?php
    }else{
?>
<table id="seQuoteResults">
<?php if (!zen_is_logged_in() || zen_in_guest_checkout()) { ?>
    <tr>
      <td colspan="2" class="seDisplayedAddressLabel">
        <?php echo CART_SHIPPING_QUOTE_CRITERIA; ?><br />
        <?php echo '<span class="seDisplayedAddressInfo">' . zen_get_zone_name($selected_country, $state_zone_id, '') . ($selectedState != '' ? ' ' . $selectedState : '') . ' ' . (isset($order->delivery['postcode']) ? $order->delivery['postcode'] : '') . ' ' . zen_get_country_name($order->delivery['country_id']) . '</span>'; ?>
      </td>
    </tr>
<?php } ?>
     <tr>
       <th scope="col" id="seProductsHeading"><?php echo CART_SHIPPING_METHOD_TEXT; ?></th>
       <th scope="col" id="seTotalHeading"><?php echo CART_SHIPPING_METHOD_RATES; ?></th>
     </tr>
<?php
      if (empty($extra)) {
        $extra = '';
      }
      for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
        $thisquoteid = '';
        if(isset($quotes[$i]['id']) && sizeof($quotes[$i]['methods'])==1 && isset($quotes[$i]['methods'][0]['id'])){
          // simple shipping method
          $thisquoteid = $quotes[$i]['id'].'_'.$quotes[$i]['methods'][0]['id'];
?>
     <tr class="<?php echo $extra; ?>">
<?php
          if(isset($quotes[$i]['error']) && $quotes[$i]['error']){
?>
         <td colspan="2"><?php echo $quotes[$i]['module']; ?>&nbsp;(<?php echo $quotes[$i]['error']; ?>)</td>
       </tr>
<?php
          }else{
            if($selected_shipping['id'] == $thisquoteid){
?>
         <td class="bold"><?php echo $quotes[$i]['module']; ?>&nbsp;(<?php echo $quotes[$i]['methods'][0]['title']; ?>)</td>
         <td class="cartTotalDisplay bold"><?php echo $currencies->format(zen_add_tax($quotes[$i]['methods'][0]['cost'], isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)); ?></td>
       </tr>
<?php
            }else{
?>
          <td><?php echo $quotes[$i]['module']; ?>&nbsp;(<?php echo $quotes[$i]['methods'][0]['title']; ?>)</td>
          <td class="cartTotalDisplay"><?php echo $currencies->format(zen_add_tax($quotes[$i]['methods'][0]['cost'], isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)); ?></td>
       </tr>
<?php
            }
          }
        } else {
          // shipping method with sub methods (multipickup) or none
          for ($j=0, $n2=(empty($quotes[$i]['methods']) ? 0 : sizeof($quotes[$i]['methods'])); $j<$n2; $j++) {
            $thisquoteid = '';
            if (isset($quotes[$i]['id']) && isset($quotes[$i]['methods'][$j]['id'])) {
                $thisquoteid = $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'];
            }
?>
    <tr class="<?php echo $extra; ?>">
<?php
            if(!empty($quotes[$i]['error'])){
?>
         <td colspan="2"><?php echo $quotes[$i]['module']; ?>&nbsp;(<?php echo $quotes[$i]['error']; ?>)</td>
       </tr>
<?php
            }else{
              if($selected_shipping['id'] == $thisquoteid){
?>
         <td class="bold"><?php echo $quotes[$i]['module']; ?>&nbsp;(<?php echo $quotes[$i]['methods'][$j]['title']; ?>)</td>
         <td class="cartTotalDisplay bold"><?php echo $currencies->format(zen_add_tax($quotes[$i]['methods'][$j]['cost'], isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)); ?></td>
       </tr>
<?php
              }else{
?>
        <td><?php echo $quotes[$i]['module']; ?>&nbsp;(<?php echo $quotes[$i]['methods'][$j]['title']; ?>)</td>
        <td class="cartTotalDisplay"><?php echo $currencies->format(zen_add_tax($quotes[$i]['methods'][$j]['cost'], isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)); ?></td>
      </tr>
<?php
              }
            }
          }
        }
      }
?>
</table>
<?php
   }
  }
?>
</form>
</div>
