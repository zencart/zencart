<?php
/**
 * Session processing specific to PayPal Website Payments Standard IPN handling
 *
 * @package initSystem
 * @copyright Copyright 2003-2009 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_paypal_ipn_sessions.php 14422 2009-09-13 04:42:03Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * Begin processing. Add notice to log if logging enabled.
 */
  ipn_debug_email('IPN PROCESSING INITIATED. ' . "\n" . '*** Originating IP: ' . $_SERVER['REMOTE_ADDR'] . '  ' . (SESSION_IP_TO_HOST_ADDRESS == 'true' ? @gethostbyaddr($_SERVER['REMOTE_ADDR']) : '') . ($_SERVER['HTTP_USER_AGENT'] == '' ? '' : "\n" . '*** Browser/User Agent: ' . $_SERVER['HTTP_USER_AGENT']));

// need to see if we are in test mode. If so then the data is going to come in as a GET string
  if (defined('MODULE_PAYMENT_PAYPAL_TESTING') && MODULE_PAYMENT_PAYPAL_TESTING == 'Test') {
    foreach ($_GET as $key=>$value) {
      $_POST[$key] = $value;
    }
  }
  if (!$_POST) {
    ipn_debug_email('IPN FATAL ERROR :: No POST data available -- Most likely initiated by browser and not PayPal.' . "\n\n\n" . '     *** The rest of this log report can most likely be ignored !! ***' . "\n\n\n\n");
     //if ($show_all_errors) echo 'No POST data. This is not a real IPN transaction. Any "Undefined" errors below can be ignored ...<br />';
  }


  $session_post = isset($_POST['custom']) ? $_POST['custom'] : '=';
  $session_stuff = explode('=', $session_post);
  $ipnFoundSession = true;
  if (!$isECtransaction && !isset($_POST['parent_txn_id']) && ipn_get_stored_session($session_stuff) === false) {
    ipn_debug_email('IPN ERROR :: No saved Website Payments Standard session data available. Must be an Express Checkout or Direct Pay transaction.' . "\n" . 'Could be a test notification, or the incoming IPN notification is not actually a bonafide PayPal transaction.' . "\n" . 'NOTE: It is likely that all the following log content is meaningless or irrelevant.');
    $ipnFoundSession = false;
  }
