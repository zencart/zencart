<?php
/**
 * Checkout Shipping Page
 *
 * @package page
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Modified in v1.6.0 $
 */
// This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_CHECKOUT_SHIPPING');
  // Finally, destroy the session.
  // A RETURNING GUEST CUSTOMER SHOULD NOT BE ABLE TO CHECKOUT COMING FROM ORDER STATUS, SO KILL THE SESSION
  if ($_SESSION['ORDER_STATUS'] == 'True') {
      zen_session_destroy();
  }
  require_once(DIR_WS_CLASSES . 'http_client.php');

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() <= 0) {
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
  }

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id']) || !$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  } else {
    // validate customer
    if (zen_get_customer_validate_session($_SESSION['customer_id']) == false) {
      $_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_SHIPPING));
      zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }

// Validate Cart for checkout
  $_SESSION['valid_to_checkout'] = true;
  $_SESSION['cart']->get_products(true);
  if ($_SESSION['valid_to_checkout'] == false) {
    $messageStack->add('header', ERROR_CART_UPDATE, 'error');
    zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
  }

// Stock Check
  if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $qtyAvailable = zen_get_products_stock($products[$i]['id']);
      // compare against product inventory, and against mixed=YES
      if ($qtyAvailable - $products[$i]['quantity'] < 0 || $qtyAvailable - $_SESSION['cart']->in_cart_mixed($products[$i]['id']) < 0) {
        zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
        break;
      }
    }
  }

// if no shipping destination address was selected, use the customers own address as default
  if (!$_SESSION['sendto']) {
    $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
  } else {
// verify the selected shipping address
    $check_address_query = "SELECT count(*) AS total
                            FROM   " . TABLE_ADDRESS_BOOK . "
                            WHERE  customers_id = :customersID
                            AND    address_book_id = :addressBookID";

    $check_address_query = $db->bindVars($check_address_query, ':customersID', $_SESSION['customer_id'], 'integer');
    $check_address_query = $db->bindVars($check_address_query, ':addressBookID', $_SESSION['sendto'], 'integer');
    $check_address = $db->Execute($check_address_query);

    if ($check_address->fields['total'] != '1') {
      $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
      unset($_SESSION['shipping']);
    }
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
if (isset($_SESSION['cart']->cartID)) {
  if (!isset($_SESSION['cartID']) || $_SESSION['cart']->cartID != $_SESSION['cartID']) {
    $_SESSION['cartID'] = $_SESSION['cart']->cartID;
  }
} else {
  zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
  if ($order->content_type == 'virtual') {
    $_SESSION['shipping'] = array();
    $_SESSION['shipping']['id'] = 'free_free';
    $_SESSION['shipping']['title'] = 'free_free';
    $_SESSION['shipping']['cost'] = 0;
    $_SESSION['sendto'] = false;
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  $total_weight = $_SESSION['cart']->show_weight();
  $total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping;

  $pass = true;
  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
    $pass = false;

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
    }

    $free_shipping = false;
    if ( ($pass == true) && ($_SESSION['cart']->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
      $free_shipping = true;
    }
  } else {
    $free_shipping = false;
  }

  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

  if (isset($_SESSION['comments'])) {
    $comments = trim($_SESSION['comments']);
  }


// process the selected shipping method
  if ( isset($_POST['action']) && ($_POST['action'] == 'process') ) {
    if (zen_not_null($_POST['comments'])) {
      $_SESSION['comments'] = zen_output_string_protected(trim($_POST['comments']));
    }
    $comments = $_SESSION['comments'];
    $quote = array();

    if ( (zen_count_shipping_modules() > 0) || ($free_shipping == true) ) {
      if ( (isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_')) ) {
        /**
         * check to be sure submitted data hasn't been tampered with
         */
        if ($_POST['shipping'] == 'free_free' && ($order->content_type != 'virtual' && !$pass)) {
          $quote['error'] = 'Invalid input. Please make another selection.';
        }
        list($module, $method) = explode('_', $_POST['shipping']);
        if ( is_object($$module) || ($_POST['shipping'] == 'free_free') ) {
          if ($_POST['shipping'] == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
            $quote[0]['methods'][0]['icon'] = '';
          } else {
            $quote = $shipping_modules->quote($method, $module);
          }
          if (isset($quote[0]['error'])) {
            unset($_SESSION['shipping']);
          } else {
            if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
              $_SESSION['shipping'] = array('id' => $_POST['shipping'],
                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                'cost' => $quote[0]['methods'][0]['cost']);

              zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
          }
        } else {
          unset($_SESSION['shipping']);
        }
      }
    } else {
      unset($_SESSION['shipping']);

      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
  }

// get all available shipping quotes
  $quotes = $shipping_modules->quote();

  // check that the currently selected shipping method is still valid (in case a zone restriction has disabled it, etc)
  if (isset($_SESSION['shipping'])) {
    $checklist = array();
    foreach ($quotes as $key=>$val) {
      if ($val['methods'] != '') {
        foreach($val['methods'] as $key2=>$method) {
          $checklist[] = $val['id'] . '_' . $method['id'];
        }
      } else {
        // skip
      }
    }
    $checkval = $_SESSION['shipping']['id'];
    if (!in_array($checkval, $checklist) && $_SESSION['shipping']['id'] != 'free_free') {
      $messageStack->add('checkout_shipping', ERROR_PLEASE_RESELECT_SHIPPING_METHOD, 'error');
    }
  }

// If no shipping method has been selected, automatically select the cheapest method.
// If the module's status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
  if ((!isset($_SESSION['shipping']) || (!isset($_SESSION['shipping']['id']) || $_SESSION['shipping']['id'] == '') && zen_count_shipping_modules() >= 1)) $_SESSION['shipping'] = $shipping_modules->cheapest();

  // Should address-edit button be offered?
  $displayAddressEdit = (MAX_ADDRESS_BOOK_ENTRIES >= 2);

  // if shipping-edit button should be overridden, do so
  $editShippingButtonLink = zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL');
  if (isset($_SESSION['payment']) && method_exists(${$_SESSION['payment']}, 'alterShippingEditButton')) {
    $theLink = ${$_SESSION['payment']}->alterShippingEditButton();
    if ($theLink) {
      $editShippingButtonLink = $theLink;
      $displayAddressEdit = true;
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_CHECKOUT_SHIPPING');
