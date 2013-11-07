<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: shopping_cart.php 3183 2006-03-14 07:58:59Z birdbrain $
 */

define('TEXT_INFORMATION', 'You may proceed with your purchase by clicking the Checkout button below. Shipping and Taxes and Discounts will be handled on subsequent pages.');

define('NAVBAR_TITLE', 'The Shopping Cart');
define('HEADING_TITLE', 'Your Shopping Cart Contents');
define('HEADING_TITLE_EMPTY', 'Your Shopping Cart');
define('TEXT_INFORMATION', 'You may want to add some instructions for using the shopping cart here. (defined in includes/languages/english/shopping_cart.php)');
define('TABLE_HEADING_REMOVE', 'Remove');
define('TABLE_HEADING_QUANTITY', 'Qty.');
define('TABLE_HEADING_MODEL', 'Model');
define('TABLE_HEADING_PRICE','Unit');
define('TEXT_CART_EMPTY', 'Your Shopping Cart is empty.');
define('SUB_TITLE_SUB_TOTAL', 'Sub-Total:');
define('SUB_TITLE_TOTAL', 'Total:');

define('OUT_OF_STOCK_CANT_CHECKOUT', 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock or there are not enough in stock to fill your order.<br />Please change the quantity of products marked with (' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '). Thank you');
define('OUT_OF_STOCK_CAN_CHECKOUT', 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock.<br />Items not in stock will be placed on backorder.');

define('TEXT_TOTAL_ITEMS', 'Total Items: ');
define('TEXT_TOTAL_WEIGHT', '&nbsp;&nbsp;Weight: ');
define('TEXT_TOTAL_AMOUNT', '&nbsp;&nbsp;Amount: ');

define('TEXT_VISITORS_CART', '<a class="help" href="javascript:session_win();"><i class="icon-help-circled"></i></a>');
define('TEXT_OPTION_DIVIDER', '&nbsp;-&nbsp;');
