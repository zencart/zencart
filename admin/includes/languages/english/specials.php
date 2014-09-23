<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */

define('HEADING_TITLE', 'Specials');

define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_PRODUCTS_MODEL','Model');
define('TABLE_HEADING_PRODUCTS_PRICE', 'Products Price/Special/Sale');
define('TABLE_HEADING_PRODUCTS_PERCENTAGE','Percentage');
define('TABLE_HEADING_AVAILABLE_DATE', 'Available');
define('TABLE_HEADING_EXPIRES_DATE','Expires');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_SPECIALS_PRODUCT', 'Product:');
define('TEXT_SPECIALS_SPECIAL_PRICE', 'Special Price:');

define('TEXT_PRE_ADD_SPECIAL_PRICE_RANGE_FROM', 'Product pricerange:');
define('TEXT_PRE_ADD_SPECIAL_PRICE_RANGE_TO', 'to:');

define('TEXT_SPECIALS_EXPIRES_DATE', 'Expiry Date:');
define('TEXT_SPECIALS_AVAILABLE_DATE', 'Available Date:');
define('TEXT_SPECIALS_PRICE_TIP', '<b>Specials Notes:</b><ul><li>You can enter a percentage to deduct in the Specials Price field, for example: <b>20%</b></li><li>If you enter a new price, the decimal separator must be a \'.\' (decimal-point), example: <b>49.99</b></li><li>Leave the expiry date empty for no expiration</li></ul>');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_NEW_PRICE', 'New Price:');
define('TEXT_INFO_ORIGINAL_PRICE', 'Original Price:');
define('TEXT_INFO_DISPLAY_PRICE', 'Display Price:<br />');
define('TEXT_INFO_AVAILABLE_DATE', 'Available On:');
define('TEXT_INFO_EXPIRES_DATE', 'Expires At:');
define('TEXT_INFO_STATUS_CHANGE', 'Status Change:');
define('TEXT_IMAGE_NONEXISTENT', 'No Image Exists');

define('TEXT_INFO_HEADING_DELETE_SPECIALS', 'Delete Special');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete the special products price?');

define('SUCCESS_SPECIALS_PRE_ADD', 'Successful: Pre-Add of Special ... please update the price and dates ...');
define('WARNING_SPECIALS_PRE_ADD_EMPTY', 'Warning: No Product ID specified ... nothing was added ...');
define('WARNING_SPECIALS_PRE_ADD_DUPLICATE', 'Warning: Product ID already on Special ... nothing was added ...');
define('WARNING_SPECIALS_PRE_ADD_BAD_PRODUCTS_ID', 'Warning: Product ID is invalid ... nothing was added ...');
define('TEXT_INFO_HEADING_PRE_ADD_SPECIALS', 'Manually add new Special by Product ID');
define('TEXT_INFO_PRE_ADD_INTRO', 'On large databases, you may Manually Add a Special by the Product ID<br /><br />This is best used when the page takes too long to render and trying to select a Product from the dropdown becomes difficult due to too many Products from which to choose.');
define('TEXT_PRE_ADD_PRODUCTS_ID', 'Please enter the Product ID to be Pre-Added: ');
define('TEXT_INFO_MANUAL', 'Product ID to be Manually Added as a Special');

define('TEXT_INFO_CATEGORY', 'Global Category Change');
define('TEXT_INFO_HEADING_PRE_ADD_SPECIALS_CATEGORY', 'GLOBAL CATEGORY CHANGE');
define('TEXT_INFO_PRE_ADD_INTRO_CATEGORY', 'What changes do you want on all Products in this Category?');
define('TEXT_INFO_MANUAL_CATEGORY', 'Set Special on all Products in a Category');
define('TEXT_PRE_ADD_CATEGORY_ID', 'Category ID to set ALL Products on Special:');

define('TEXT_INFO_INCLUDE_SUBCATEGORIES', 'Include Products in ALL subcategories?');
define('TEXT_SKIP_SUBCATEGORIES', 'Yes, include ALL subcategory Products');
define('TEXT_SKIP_SUBCATEGORIES_NO', 'No, skip ALL subcategory Products');

define('TEXT_INFO_INCLUDE_INACTIVE', 'Include inactive Products?');
define('TEXT_SKIP_INACTIVE', 'Yes, include ALL inactive Products');
define('TEXT_SKIP_INACTIVE_NO', 'No, skip ALL inactive Products');

define('TEXT_INFO_SKIP_SPECIALS', 'Skip changes to products already on Special?');
define('TEXT_SKIP_SPECIALS_TRUE', 'Yes, skip changes to existing specials');
define('TEXT_SKIP_SPECIALS_FALSE', 'No, change all products to new special');
define('TEXT_PRE_ADD_SPECIAL_PRICE','Special Price:');
define('TEXT_PRE_ADD_SPECIAL_START_DATE', 'Start Date:');
define('TEXT_PRE_ADD_SPECIAL_END_DATE', 'End Date:');
define('SPECIALS_DATE_ERROR', '&nbsp;<span class="errorText">(eg. 05/21/1970)</span>');
define('ERROR_NOTHING_SELECTED_CATEGORY', 'Category ID or Price not set, nothing was changed');
define('ERROR_NOTHING_SELECTED_CATEGORY_SUB', 'No Products located in Category: %s');

define('TEXT_INFO_MANUAL_CATEGORY_REMOVE', 'Remove specials from all products in Category');
define('TEXT_INFO_HEADING_PRE_REMOVE_SPECIALS_CATEGORY', 'GLOBAL CATEGORY REMOVE SPECIALS');
define('TEXT_INFO_PRE_REMOVE_INTRO_CATEGORY', 'All Specials will be removed from Category');
define('TEXT_PRE_REMOVE_CATEGORY_ID', 'Category ID to delete ALL Products on Special');

define('TEXT_INFO_MANUFACTURER', 'Global Manufacturer Change');
define('TEXT_INFO_HEADING_PRE_ADD_SPECIALS_MANUFACTURER', 'GLOBAL MANUFACTURER CHANGE');
define('TEXT_INFO_PRE_ADD_INTRO_MANUFACTURER', 'What changes do you want on all Products for this Manufacturer?');
define('TEXT_INFO_MANUAL_MANUFACTURER', 'Set Special on all Products of a Manufacturer');
define('TEXT_PRE_ADD_MANUFACTURER_ID', 'Manufacturer ID to set ALL Products on Special:');
define('ERROR_NOTHING_SELECTED_MANUFACTURER', 'Manufacturer ID or Price not set, nothing was changed');
define('ERROR_NOTHING_SELECTED_MANUFACTURER_SUB', 'No Products located for Manufacturer: %s');

define('TEXT_INFO_MANUAL_MANUFACTURER_REMOVE', 'Remove specials from all products in Manufacturer');
define('TEXT_INFO_HEADING_PRE_REMOVE_SPECIALS_MANUFACTURER', 'GLOBAL MANUFACTURER REMOVE SPECIALS');
define('TEXT_INFO_PRE_REMOVE_INTRO_MANUFACTURER', 'All Specials will be removed from Manufacturer');
define('TEXT_PRE_REMOVE_MANUFACTURER_ID', 'Manufacturer ID to delete ALL Products on Special');

define('SUCCESS_SPECIALS_UPDATED_CATEGORY', 'Specials updated for Category: ');
define('SUCCESS_SPECIALS_REMOVED_CATEGORY', 'Specials removed for Category: ');
define('SUCCESS_SPECIALS_UPDATED_MANUFACTURER', 'Specials updated for Manufacturer: ');
define('SUCCESS_SPECIALS_REMOVED_MANUFACTURER', 'Specials removed for Manufacturer: ');

define('SUCCESS_SPECIALS_UPDATED_MANUFACTURER', 'Specials updated for Manufacturer: ');
define('SUCCESS_SPECIALS_PRICE_SET', 'Specials set to: ');