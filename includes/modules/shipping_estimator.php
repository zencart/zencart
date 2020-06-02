<?php
/**
 * Shipping Estimator module
 *
 * Customized by: Linda McGrath osCommerce@WebMakers.com to:
 * - Handles Free Shipping for orders over $total as defined in the Admin
 * - Shows Free Shipping on Virtual products
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * portions Copyright (c) 2003 Edwin Bekaert (edwin@ednique.com)
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 09 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (isset($_POST['zone_country_id'])) {
    $_POST['zone_country_id'] = (int)$_POST['zone_country_id'];
}
if (isset($_POST['scid'])) {
    $_POST['scid'] = preg_replace('/[^a-z_0-9\- ]/i', '', $_POST['scid']);
}

// load JS updater
if ($current_page_base != 'popup_shipping_estimator') {
    require DIR_WS_MODULES . '/pages/popup_shipping_estimator/jscript_addr_pulldowns.php';
}
?>
<!-- shipping_estimator //-->

<script>
function shipincart_submit(){
  document.estimator.submit();
  return false;
}
</script>

<?php
// Only do when something is in the cart
if ($_SESSION['cart']->count_contents() > 0) {
    $zip_code = (isset($_SESSION['cart_zip_code'])) ? $_SESSION['cart_zip_code'] : '';
    $zip_code = (isset($_POST['zip_code'])) ? strip_tags(addslashes($_POST['zip_code'])) : $zip_code;
    $state_zone_id = (isset($_SESSION['cart_zone'])) ? (int)$_SESSION['cart_zone'] : '';
    $state_zone_id = (isset($_POST['zone_id'])) ? (int)$_POST['zone_id'] : $state_zone_id;
    $selectedState = (isset($_POST['state']) ? zen_output_string_protected($_POST['state']) : '');
    // Could be placed in english.php
    // shopping cart quotes
    // shipping cost

    // deprecated; to be removed
    if (file_exists(DIR_WS_CLASSES . 'http_client.php')) {
        require_once DIR_WS_CLASSES . 'http_client.php'; // shipping in basket
    }

    $sendto = 0;

    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
        // user is logged in
        if (isset($_POST['address_id'])) {
            // user changed address
            $sendto = $_POST['address_id'];
        } elseif (!empty($_SESSION['sendto'])) {
            // user has previously selected a destination address
            $sendto = $_SESSION['sendto'];
        } elseif (!empty($_SESSION['cart_address_id'])) {
            // user once changed address
            $sendto = $_SESSION['cart_address_id'];
        } else {
            // first timer
            $sendto = $_SESSION['customer_default_address_id'];
        }
        $_SESSION['sendto'] = $sendto;
        // set session now
        $_SESSION['cart_address_id'] = $sendto;
        // set shipping to null ! multipickup changes address to store address...
        $shipping = '';
        // include the order class (uses the sendto !)
        require DIR_WS_CLASSES . 'order.php';
        $order = new order;
    } else {
        // user not logged in !
        require DIR_WS_CLASSES . 'order.php';
        $order = new order;
        if (!empty($_POST['zone_country_id'])) {
            // country is selected
            $_SESSION['country_info'] = zen_get_countries($_POST['zone_country_id'],true);
            $country_info = $_SESSION['country_info'];
            $order->delivery = array(
                'postcode' => $zip_code,
                'country' => array(
                    'id' => $_POST['zone_country_id'], 
                    'title' => $country_info['countries_name'],
                    'iso_code_2' => $country_info['countries_iso_code_2'], 
                    'iso_code_3' =>  $country_info['countries_iso_code_3'],
                ),
                'country_id' => $_POST['zone_country_id'],
                //add state zone_id
                'zone_id' => $state_zone_id,
                'format_id' => zen_get_address_format_id($_POST['zone_country_id']),
            );
            $_SESSION['cart_country_id'] = $_POST['zone_country_id'];
            //add state zone_id
            $_SESSION['cart_zone'] = $state_zone_id;
            $_SESSION['cart_zip_code'] = $zip_code;
        } elseif (!empty($_SESSION['cart_country_id'])) {
            // session is available
            $_SESSION['country_info'] = zen_get_countries($_SESSION['cart_country_id'],true);
            $country_info = $_SESSION['country_info'];
            // fix here - check for error on $cart_country_id
            $order->delivery = array(
                'postcode' => $zip_code,
                'country' => array(
                    'id' => $_SESSION['cart_country_id'], 
                    'title' => $country_info['countries_name'], 
                    'iso_code_2' => $country_info['countries_iso_code_2'], 
                    'iso_code_3' =>  $country_info['countries_iso_code_3'],
                ),
                'country_id' => $_SESSION['cart_country_id'],
                'zone_id' => $state_zone_id,
                'format_id' => zen_get_address_format_id($_SESSION['cart_country_id']),
            );
        } else {
            // first timer
            $_SESSION['cart_country_id'] = STORE_COUNTRY;
            $_SESSION['country_info'] = zen_get_countries(STORE_COUNTRY,true);
            $country_info = $_SESSION['country_info'];
            $order->delivery = array(
                //'postcode' => '',
                'country' => array(
                    'id' => STORE_COUNTRY, 
                    'title' => $country_info['countries_name'], 
                    'iso_code_2' => $country_info['countries_iso_code_2'], 
                    'iso_code_3' =>  $country_info['countries_iso_code_3'],
                ),
                'country_id' => STORE_COUNTRY,
                'zone_id' => $state_zone_id,
                'format_id' => zen_get_address_format_id(isset($_POST['zone_country_id']) ? $_POST['zone_country_id'] : 0),
            );
        }
        // set the cost to be able to calculate free shipping
        $order->info = array(
            'total' => $_SESSION['cart']->show_total(), // TAX ????
            'currency' => isset($currency) ? $currency : DEFAULT_CURRENCY,
            'currency_value'=> isset($currency) && isset($currencies->currencies[$currency]['value']) ? $currencies->currencies[$currency]['value'] : 1
        );
    }
    // weight and count needed for shipping !
    $total_weight = $_SESSION['cart']->show_weight();
    $shipping_estimator_display_weight = $total_weight;
    $total_count = $_SESSION['cart']->count_contents();
    require DIR_WS_CLASSES . 'shipping.php';
    $shipping_modules = new shipping;
    // some shipping modules need subtotal to be set.
    $order->info['subtotal'] = $_SESSION['cart']->show_total();
    $quotes = $shipping_modules->quote();

    // set selections for displaying
    $selected_country = $order->delivery['country']['id'];
    $selected_address = $sendto;
    // eo shipping cost
    // check free shipping based on order $total
    $free_shipping = $pass = false;
    if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
        switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
            case 'national':
                if ($order->delivery['country_id'] == STORE_COUNTRY) {
                    $pass = true; 
                }
                break;
            case 'international':
                if ($order->delivery['country_id'] != STORE_COUNTRY) {
                    $pass = true;
                }
                break;
            case 'both':
                $pass = true; 
                break;
            default:
                $pass = false; 
                break;
        }
        if ($pass && $_SESSION['cart']->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
            $free_shipping = true;
            include zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/', 'ot_shipping.php', 'false');
        }
    } 
    // begin shipping cost
    if (!$free_shipping && $_SESSION['cart']->get_content_type() !== 'virtual') {
        if (!empty($_POST['scid'])){
            list($module, $method) = explode('_', $_POST['scid']);
            $_SESSION['cart_sid'] = $_POST['scid'];
        } elseif (!empty($_SESSION['cart_sid'])) {
            list($module, $method) = explode('_', $_SESSION['cart_sid']);
        } else {
            $module = '';
            $method = '';
        }

        if (zen_not_null($module)) {
            foreach ($quotes as $key => $value) {
                if (!isset($value['id'])) {
                    continue;
                }
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

            if (isset($selected_quote[0]['error']) && $selected_quote[0]['error'] || !zen_not_null($selected_quote[0]['methods'][0]['cost'])) {
                $order->info['shipping_method'] = isset($selected_shipping['title']) ? $selected_shipping['title'] : '';
                $order->info['shipping_cost'] = isset($selected_shipping['cost']) ? $selected_shipping['cost'] : 0;
                $order->info['total']+= isset($selected_shipping['cost']) ? $selected_shipping['cost'] : 0;
            } else {
                $order->info['shipping_method'] = $selected_quote[0]['module'].' ('.$selected_quote[0]['methods'][0]['title'].')';
                $order->info['shipping_cost'] = $selected_quote[0]['methods'][0]['cost'];
                $order->info['total']+= $selected_quote[0]['methods'][0]['cost'];
                $selected_shipping['title'] = $order->info['shipping_method'];
                $selected_shipping['cost'] = $order->info['shipping_cost'];
                $selected_shipping['id'] = $selected_quote[0]['id'].'_'.$selected_quote[0]['methods'][0]['id'];
            }
        } else {
            $order->info['shipping_method'] = isset($selected_shipping['title']) ? $selected_shipping['title'] : '';
            $order->info['shipping_cost'] = isset($selected_shipping['cost']) ? $selected_shipping['cost'] : 0;
            $order->info['total']+= isset($selected_shipping['cost']) ? $selected_shipping['cost'] : 0;
        }
    }
    // virtual products need a free shipping
    if ($_SESSION['cart']->get_content_type() == 'virtual') {
        $order->info['shipping_method'] = CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS;
        $order->info['shipping_cost'] = 0;
    }
    if ($free_shipping) {
        $order->info['shipping_method'] = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
        $order->info['shipping_cost'] = 0;
    }

    // set cheapest last
    $selected_shipping = $shipping_modules->cheapest();
    $shipping = $selected_shipping;
    if (SHOW_SHIPPING_ESTIMATOR_BUTTON == '1') {
        $show_in = FILENAME_POPUP_SHIPPING_ESTIMATOR;
    } else {
        $show_in = FILENAME_SHOPPING_CART;
    }
    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
        $addresses = $db->execute("SELECT address_book_id, entry_city AS city, entry_postcode AS postcode, entry_state AS state, entry_zone_id AS zone_id, entry_country_id AS country_id FROM " . TABLE_ADDRESS_BOOK . " WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "'");
        // only display addresses if more than 1
        if ($addresses->RecordCount() > 1) {
            while (!$addresses->EOF) {
                $addresses_array[] = array('id' => $addresses->fields['address_book_id'], 'text' => zen_address_format(zen_get_address_format_id($addresses->fields['country_id']), $addresses->fields, 0, ' ', ' '));
                $addresses->MoveNext();
            }
        }
    } else {
        if ($_SESSION['cart']->get_content_type() != 'virtual') {
            $state_array = array();
            $state_array[] = array('id' => '', 'text' => PULL_DOWN_SHIPPING_ESTIMATOR_SELECT);
            $state_values = $db->Execute("SELECT zone_name, zone_id FROM " . TABLE_ZONES . " WHERE zone_country_id = '" . (int)$selected_country . "' ORDER BY zone_country_id DESC, zone_name");
            while (!$state_values->EOF) {
                $state_array[] = array(
                    'id' => $state_values->fields['zone_id'],
                    'text' => $state_values->fields['zone_name']
                );
                $state_values->MoveNext();
            }
        }
    }

    // This is done after quote-calcs in order to include Tare info accurately.  
    // NOTE: tare values are *not* included in weights shown on-screen.
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
        // display the result with template tpl_modules_shipping_estimator.php 
        require $template->get_template_dir('tpl_modules_shipping_estimator.php', DIR_WS_TEMPLATE, $current_page_base,'templates') . '/' . 'tpl_modules_shipping_estimator.php';
    }
} else { // Only do when something is in the cart
?>
<h2><?php echo CART_SHIPPING_OPTIONS; ?></h2>
<div class="cartTotalsDisplay important"><?php echo EMPTY_CART_TEXT_NO_QUOTE; ?></div>
<?php
}
?>
<script type="text/javascript">update_zone(document.estimator); </script>
<!-- shipping_estimator_eof //-->
