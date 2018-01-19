<?php

/**
 * @package     paypal_incontext
 * @copyright   Copyright 2003-2016 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @copyright   Portions Copyright 2012-2016 mc12345678
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 Sat Aug 21 12:16:37 2016 -0500 Added in v1.6.0 $
 */
class paypalwpp_incontext extends base {

  
  /*
   * This is the observer for the PayPal return URIs when the PayPal payment is being processed at the end of the purchase.
   */
  function __construct() {
    
    $attachNotifier = array();

    $attachNotifier[] = 'NOTIFY_HEADER_START_CHECKOUT_SHIPPING';
    $attachNotifier[] = 'NOTIFY_HEADER_START_CHECKOUT_PAYMENT';

    $this->attach($this, $attachNotifier);
  }


  // Clear the paypal_ec_token at the PayPal return URL (RETURNURL) when the
  //  payment is cancelled at PayPal.  Without other process modifications, 
  //  this is seen as necessary so that at the revisit to the payment 
  //  confirmation page that PayPal Express In Context can again operate.
  //    $attachNotifier[] = 'NOTIFY_HEADER_START_CHECKOUT_SHIPPING';
  function updateNotifyHeaderStartCheckoutShipping(&$callingClass, $notifier) {
    if (isset($_GET['ec_cancel']) && $_GET['ec_cancel'] == '1' 
     && isset($_SESSION['paypal_ec_token']) && !empty($_SESSION['paypal_ec_token'])
     && defined('MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE') && MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE == 'InContext' 
     && defined('MODULE_PAYMENT_PAYPALWPP_MERCHANTID')    && MODULE_PAYMENT_PAYPALWPP_MERCHANTID    != '' 
     && defined('MODULE_PAYMENT_PAYPALWPP_STATUS')        && MODULE_PAYMENT_PAYPALWPP_STATUS        == 'True') {
      unset($_SESSION['paypal_ec_token']);
    }
  }

  // Clear the paypal_ec_token at the PayPal return URL (RETURNURL) when the
  //  payment is cancelled at PayPal.  Without other process modifications, 
  //  this is seen as necessary so that at the revisit to the payment 
  //  confirmation page that PayPal Express In Context can again operate.
  //    $attachNotifier[] = 'NOTIFY_HEADER_START_CHECKOUT_PAYMENT';
  function updateNotifyHeaderStartCheckoutPayment(&$callingClass, $notifier) {
    if (isset($_GET['ec_cancel']) && $_GET['ec_cancel'] == '1' 
     && isset($_SESSION['paypal_ec_token']) && !empty($_SESSION['paypal_ec_token'])
     && defined('MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE') && MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE == 'InContext' 
     && defined('MODULE_PAYMENT_PAYPALWPP_MERCHANTID')    && MODULE_PAYMENT_PAYPALWPP_MERCHANTID    != '' 
     && defined('MODULE_PAYMENT_PAYPALWPP_STATUS')        && MODULE_PAYMENT_PAYPALWPP_STATUS        == 'True') {
      unset($_SESSION['paypal_ec_token']);
    }
  }

  /*
   * Generic function that is activated when any notifier identified in the observer is called but is not found in one of the above previous specific update functions is encountered as a notifier.
   */
  function update(&$callingClass, $notifier, $paramsArray) {

    if ($notifier == 'NOTIFY_HEADER_START_CHECKOUT_SHIPPING') {
      $this->updateNotifyHeaderStartCheckoutShipping($callingClass, $notifier);
    }
  
    if ($notifier == 'NOTIFY_HEADER_START_CHECKOUT_PAYMENT') {
      $this->updateNotifyHeaderStartCheckoutPayment($callingClass, $notifier);
    }
    
  } //end update function - mc12345678
} //end class - mc12345678

