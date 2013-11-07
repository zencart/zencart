<?php
/**
 * Shipping Estimator module
 *
 * Customized by: Linda McGrath osCommerce@WebMakers.com to:
 * - Handles Free Shipping for orders over $total as defined in the Admin
 * - Shows Free Shipping on Virtual products
 *
 * @package modules
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * portions Copyright (c) 2003 Edwin Bekaert (edwin@ednique.com)
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: shipping_estimator.php 19954 2011-11-03 18:09:36Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (isset($_POST['zone_country_id'])) $_POST['zone_country_id'] = (int)$_POST['zone_country_id'];
if (isset($_POST['scid'])) $_POST['scid'] = preg_replace('/[^a-z_0-9\- ]/i', '', $_POST['scid']);

// load JS updater
if ($current_page_base != 'popup_shipping_estimator') {
  require(DIR_WS_MODULES . '/pages/popup_shipping_estimator/jscript_addr_pulldowns.php');
}
?>
<!-- shipping_estimator //-->

<?php
// Only do when something is in the cart
if ($_SESSION['cart']->count_contents() > 0) {
   $zip_code = (isset($_SESSION['cart_zip_code'])) ? $_SESSION['cart_zip_code'] : '';
   $zip_code = (isset($_POST['zip_code'])) ? strip_tags(addslashes($_POST['zip_code'])) : $zip_code;
   $state_zone_id = (isset($_SESSION['cart_zone'])) ? (int)$_SESSION['cart_zone'] : '';
   $state_zone_id = (isset($_POST['zone_id'])) ? (int)$_POST['zone_id'] : $state_zone_id;
   $selectedState = zen_output_string_protected($_POST['state']);
  // Could be placed in english.php
  // shopping cart quotes
  // shipping cost

  // deprecated; to be removed
  if (file_exists(DIR_WS_CLASSES . 'http_client.php')) require_once(DIR_WS_CLASSES . 'http_client.php'); // shipping in basket

/*
// moved below and altered to include Tare
  // totals info
  $totalsDisplay = '';
  switch (true) {
    case (SHOW_TOTALS_IN_CART == '1'):
    $totalsDisplay = TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() . TEXT_TOTAL_WEIGHT . $_SESSION['cart']->show_weight() . TEXT_PRODUCT_WEIGHT_UNIT . TEXT_TOTAL_AMOUNT . $currencies->format($_SESSION['cart']->show_total());
    break;
    case (SHOW_TOTALS_IN_CART == '2'):
    $totalsDisplay = TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() . ($_SESSION['cart']->show_weight() > 0 ? TEXT_TOTAL_WEIGHT . $_SESSION['cart']->show_weight() . TEXT_PRODUCT_WEIGHT_UNIT : '') . TEXT_TOTAL_AMOUNT . $currencies->format($_SESSION['cart']->show_total());
    break;
    case (SHOW_TOTALS_IN_CART == '3'):
    $totalsDisplay = TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() . TEXT_TOTAL_AMOUNT . $currencies->format($_SESSION['cart']->show_total());
    break;
  }
*/

  //if($_SESSION['cart']->get_content_type() !== 'virtual') {
  if ($_SESSION['customer_id']) {
    // user is logged in
    if (isset($_POST['address_id'])){
      // user changed address
      $sendto = $_POST['address_id'];
    }elseif ($_SESSION['cart_address_id']){
      // user once changed address
      $sendto = $_SESSION['cart_address_id'];
      //        $sendto = $_SESSION['customer_default_address_id'];
    }else{
      // first timer
      $sendto = $_SESSION['customer_default_address_id'];
    }
    $_SESSION['sendto'] = $sendto;
    // set session now
    $_SESSION['cart_address_id'] = $sendto;
    // set shipping to null ! multipickjup changes address to store address...
    $shipping='';
    // include the order class (uses the sendto !)
    require(DIR_WS_CLASSES . 'order.php');
    $order = new order;
  } else {
    // user not logged in !
    require(DIR_WS_CLASSES . 'order.php');
    $order = new order;
    if (isset($_POST['zone_country_id'])){
      // country is selected
      $_SESSION['country_info'] = zen_get_countries($_POST['zone_country_id'],true);
      $country_info = $_SESSION['country_info'];
      $order->delivery = array('postcode' => $zip_code,
                               'country' => array('id' => $_POST['zone_country_id'], 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                               'country_id' => $_POST['zone_country_id'],
                               //add state zone_id
                               'zone_id' => $state_zone_id,
                               'format_id' => zen_get_address_format_id($_POST['zone_country_id']));
      $_SESSION['cart_country_id'] = $_POST['zone_country_id'];
      //add state zone_id
      $_SESSION['cart_zone'] = $state_zone_id;
      $_SESSION['cart_zip_code'] = $zip_code;
    } elseif ($_SESSION['cart_country_id']){
      // session is available
      $_SESSION['country_info'] = zen_get_countries($_SESSION['cart_country_id'],true);
      $country_info = $_SESSION['country_info'];
      // fix here - check for error on $cart_country_id
      $order->delivery = array('postcode' => $_SESSION['cart_zip_code'],
                               'country' => array('id' => $_SESSION['cart_country_id'], 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                               'country_id' => $_SESSION['cart_country_id'],
                               'zone_id' => $state_zone_id,
                               'format_id' => zen_get_address_format_id($_SESSION['cart_country_id']));
    } else {
      // first timer
      $_SESSION['cart_country_id'] = STORE_COUNTRY;
      $_SESSION['country_info'] = zen_get_countries(STORE_COUNTRY,true);
      $country_info = $_SESSION['country_info'];
      $order->delivery = array(//'postcode' => '',
                               'country' => array('id' => STORE_COUNTRY, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                               'country_id' => STORE_COUNTRY,
                               'zone_id' => $state_zone_id,
                               'format_id' => zen_get_address_format_id($_POST['zone_country_id']));
    }
    // set the cost to be able to calculate free shipping
    $order->info = array('total' => $_SESSION['cart']->show_total(), // TAX ????
                         'currency' => $currency,
                         'currency_value'=> $currencies->currencies[$currency]['value']);
  }
  // weight and count needed for shipping !
  $total_weight = $_SESSION['cart']->show_weight();
  $shipping_estimator_display_weight = $total_weight;
  $total_count = $_SESSION['cart']->count_contents();
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping;
  $quotes = $shipping_modules->quote();
  //print_r($quotes);
  //die('here');
  $order->info['subtotal'] = $_SESSION['cart']->show_total();

  // set selections for displaying
  $selected_country = $order->delivery['country']['id'];
  $selected_address = $sendto;
  //}
  // eo shipping cost
  // check free shipping based on order $total
  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
      if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
      case 'international':
      if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
      case 'both':

      $pass = true; break;
      default:
      $pass = false; break;
    }
    $free_shipping = false;
    if ( ($pass == true) && ($_SESSION['cart']->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
      $free_shipping = true;
      include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
    }
  } else {
    $free_shipping = false;
  }
  // begin shipping cost
  if(!$free_shipping && $_SESSION['cart']->get_content_type() !== 'virtual'){
    if (zen_not_null($_POST['scid'])){
      list($module, $method) = explode('_', $_POST['scid']);
      $_SESSION['cart_sid'] = $_POST['scid'];
    }elseif ($_SESSION['cart_sid']){
      list($module, $method) = explode('_', $_SESSION['cart_sid']);
    }else{
      $module="";
      $method="";
    }

    if (zen_not_null($module)){
      foreach ($quotes as $key=>$value) {
        if ($value['id'] == $module) {
          $selected_quote[0] = $value;
          if (zen_not_null($method)) {
            foreach ($selected_quote[0]['methods'] as $qkey=>$qval) {
              if ($qval['id'] == $method) {
                $selected_quote[0]['methods'] = array($qval);
                continue;
              }
            }
          }
        }
      }

      if($selected_quote[0]['error'] || !zen_not_null($selected_quote[0]['methods'][0]['cost'])){
//        $selected_shipping = $shipping_modules->cheapest();
        $order->info['shipping_method'] = $selected_shipping['title'];
        $order->info['shipping_cost'] = $selected_shipping['cost'];
        $order->info['total']+= $selected_shipping['cost'];
      }else{
        $order->info['shipping_method'] = $selected_quote[0]['module'].' ('.$selected_quote[0]['methods'][0]['title'].')';
        $order->info['shipping_cost'] = $selected_quote[0]['methods'][0]['cost'];
        $order->info['total']+= $selected_quote[0]['methods'][0]['cost'];
        $selected_shipping['title'] = $order->info['shipping_method'];
        $selected_shipping['cost'] = $order->info['shipping_cost'];
        $selected_shipping['id'] = $selected_quote[0]['id'].'_'.$selected_quote[0]['methods'][0]['id'];
      }
    }else{
//      $selected_shipping = $shipping_modules->cheapest();
      $order->info['shipping_method'] = $selected_shipping['title'];
      $order->info['shipping_cost'] = $selected_shipping['cost'];
      $order->info['total']+= $selected_shipping['cost'];
    }
  }
  // virtual products need a free shipping
  if($_SESSION['cart']->get_content_type() == 'virtual') {
    $order->info['shipping_method'] = CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS;
    $order->info['shipping_cost'] = 0;
  }
  if($free_shipping) {
    $order->info['shipping_method'] = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
    $order->info['shipping_cost'] = 0;
  }

// set cheapest last
  $selected_shipping = $shipping_modules->cheapest();
  $shipping=$selected_shipping;
  if (SHOW_SHIPPING_ESTIMATOR_BUTTON == '1') {
    $show_in = FILENAME_POPUP_SHIPPING_ESTIMATOR;
  } else {
    $show_in = FILENAME_SHOPPING_CART;
  }
//  if(sizeof($quotes)) {
    if ($_SESSION['customer_id']) {
      $addresses = $db->execute("select address_book_id, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
      // only display addresses if more than 1
      if ($addresses->RecordCount() > 1){
        while (!$addresses->EOF) {
          $addresses_array[] = array('id' => $addresses->fields['address_book_id'], 'text' => zen_address_format(zen_get_address_format_id($addresses->fields['country_id']), $addresses->fields, 0, ' ', ' '));
          $addresses->MoveNext();
        }
      }
    } else {
      if($_SESSION['cart']->get_content_type() != 'virtual'){
        $state_array[] = array('id' => '', 'text' => PULL_DOWN_SHIPPING_ESTIMATOR_SELECT);
        $state_values = $db->Execute("select zone_name, zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$selected_country . "' order by zone_country_id DESC, zone_name");
        while (!$state_values->EOF) {
          $state_array[] = array('id' => $state_values->fields['zone_id'],
                                 'text' => $state_values->fields['zone_name']);
          $state_values->MoveNext();
        }
      }
    }
//  }

// This is done after quote-calcs in order to include Tare info accurately.  NOTE: tare values are *not* included in weights shown on-screen.
  $totalsDisplay = '';
  if (SHOW_SHIPPING_ESTIMATOR_BUTTON != 2) {
    switch (true) {
      case (SHOW_TOTALS_IN_CART == '1'):
      $totalsDisplay = TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() . TEXT_TOTAL_WEIGHT . $shipping_estimator_display_weight . TEXT_PRODUCT_WEIGHT_UNIT . TEXT_TOTAL_AMOUNT . $currencies->format($_SESSION['cart']->show_total());
      break;
      case (SHOW_TOTALS_IN_CART == '2'):
      $totalsDisplay = TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() . ($shipping_estimator_display_weight > 0 ? TEXT_TOTAL_WEIGHT . $shipping_estimator_display_weight . TEXT_PRODUCT_WEIGHT_UNIT : '') . TEXT_TOTAL_AMOUNT . $currencies->format($_SESSION['cart']->show_total());
      break;
      case (SHOW_TOTALS_IN_CART == '3'):
      $totalsDisplay = TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() . TEXT_TOTAL_AMOUNT . $currencies->format($_SESSION['cart']->show_total());
      break;
    }
  }

  if (!isset($tplVars['flagShippingPopUp']) || $tplVars['flagShippingPopUp'] !== true) {
/**
 * use the template tpl_modules_shipping_estimator.php to display the result
 *
**/
    require($template->get_template_dir('tpl_modules_shipping_estimator.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_shipping_estimator.php');
  }
} else { // Only do when something is in the cart
?>
<h2><?php echo CART_SHIPPING_OPTIONS; ?></h2>
<div class="cartTotalsDisplay important"><?php echo EMPTY_CART_TEXT_NO_QUOTE; ?></div>
<?php
}
?>
<script>update_zone(document.estimator); </script>
<!-- shipping_estimator_eof //-->