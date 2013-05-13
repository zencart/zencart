<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: checkout_confirmation.php 4067 2006-08-06 07:26:21Z drbyte $
 * @version $Id: Integrated COWOA v2.2 - 2007 - 2012
 */
if($_SESSION['COWOA']) $COWOA=TRUE;

define('NAVBAR_TITLE_1', 'Checkout');
define('NAVBAR_TITLE_2', 'Confirmation');

if($COWOA)
define('HEADING_TITLE', 'Step 4 of 5 - Order Confirmation');
else
define('HEADING_TITLE', 'Step 3 of 3 - Order Confirmation');

define('HEADING_BILLING_ADDRESS', 'Billing/Payment Information');
define('HEADING_DELIVERY_ADDRESS', 'Delivery/Shipping Information');
define('HEADING_SHIPPING_METHOD', 'Shipping Method:');
define('HEADING_PAYMENT_METHOD', 'Payment Method:');
define('HEADING_PRODUCTS', 'Shopping Cart Contents');
define('HEADING_TAX', 'Tax');
define('HEADING_ORDER_COMMENTS', 'Special Instructions or Order Comments');
// no comments entered
define('NO_COMMENTS_TEXT', 'None');
define('TITLE_CONTINUE_CHECKOUT_PROCEDURE', '<strong>Final Step</strong>');
define('TEXT_CONTINUE_CHECKOUT_PROCEDURE', '- continue to confirm your order. Thank you!');

define('OUT_OF_STOCK_CAN_CHECKOUT', 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock.<br />Items not in stock will be placed on backorder.');
// eof