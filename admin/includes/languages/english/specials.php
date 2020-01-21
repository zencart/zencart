<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: specials.php 4533 2006-09-17 17:21:10Z ajeh $
//

define('HEADING_TITLE', 'Specials');

define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_PRODUCTS_PRICE', 'Products Price/Special/Sale');
define('TABLE_HEADING_PRODUCTS_PERCENTAGE','Percentage');
define('TABLE_HEADING_AVAILABLE_DATE', 'Available');
define('TABLE_HEADING_EXPIRES_DATE','Expires');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');
define('TEXT_ADD_SPECIAL_SELECT', 'Add Special by Selection');
define('TEXT_ADD_SPECIAL_PID', 'Add Special by Product ID');
define('TEXT_SEARCH_SPECIALS', 'Search current Specials');

define('TEXT_SPECIALS_PRODUCT', 'Product:');
define('TEXT_SPECIALS_SPECIAL_PRICE', 'Special Price:');
define('TEXT_SPECIALS_EXPIRES_DATE', 'Date Special Expires:');
define('TEXT_SPECIALS_AVAILABLE_DATE', 'Date Special Available:');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_NEW_PRICE', 'Special Price:');
define('TEXT_INFO_ORIGINAL_PRICE', 'Original Price:');
define('TEXT_INFO_DISPLAY_PRICE', 'Currently Displayed Price:');
define('TEXT_INFO_AVAILABLE_DATE', 'Available From:');
define('TEXT_INFO_EXPIRES_DATE', 'Expires:');
define('TEXT_INFO_STATUS_CHANGED', 'Status Changed:');
define('TEXT_IMAGE_NONEXISTENT', '(no image defined)');

define('TEXT_INFO_HEADING_DELETE_SPECIALS', 'Delete Special');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete the Special Price for this product?');

define('SUCCESS_SPECIALS_PRE_ADD', 'Successful: Pre-Add of Special ... please update the price and dates ...');
define('WARNING_SPECIALS_PRE_ADD_EMPTY', 'Warning: No Product ID specified ... nothing was added ...');
define('WARNING_SPECIALS_PRE_ADD_DUPLICATE', 'Warning: Product ID already on Special ... nothing was added ...');
define('WARNING_SPECIALS_PRE_ADD_BAD_PRODUCTS_ID', 'Warning: Product ID is invalid ... nothing was added ...');
define('TEXT_INFO_HEADING_PRE_ADD_SPECIALS', 'Add Special Price by Product ID');
define('TEXT_INFO_PRE_ADD_INTRO', 'You may add a Special Price by Product ID. This method may be appropriate for shops with many products, if the selection page takes too long to render / selecting a product from the dropdown becomes unwieldy.');
define('TEXT_PRE_ADD_PRODUCTS_ID', 'Enter the Product ID: ');
define('TEXT_INFO_MANUAL', 'Product ID to be Manually Added as a Special');

define('TEXT_SPECIALS_PRICE_NOTES', '<b>Notes:</b><ul><li>Special Price may be a new price (ex-tax). The decimal separator must be a "." (decimal-point), eg: <b>49.99</b>.</li><li>Special Price may be a percentage discount, eg: <b>20%</b>.</li><li>Dates are not obligatory. Leave the expiry date empty for no expiration.</li><li>When dates are set, the status of the Special Price is automatically enabled/disabled accordingly.</li><li>' . TEXT_INFO_PRE_ADD_INTRO . '</li></ul>');
