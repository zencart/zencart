<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: products_price_manager.php 18695 2011-05-04 05:24:19Z drbyte $
 */

define('HEADING_TITLE', 'Products Price Manager');
define('HEADING_TITLE_PRODUCT_SELECT','Please select a Category with Products to display the Pricing Information of ...');

define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_PRODUCTS_PRICE', 'Products Price/Special/Sale');
define('TABLE_HEADING_PRODUCTS_PERCENTAGE','Percentage');
define('TABLE_HEADING_AVAILABLE_DATE', 'Available');
define('TABLE_HEADING_EXPIRES_DATE','Expires');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_PRODUCT_INFO', 'Product Info:');
define('TEXT_PRODUCTS_PRICE_INFO', 'Product Price Info:');
define('TEXT_PRICE', 'Price');
define('TEXT_PRODUCT_AVAILABLE_DATE', 'Available Date:');
define('TEXT_PRODUCTS_STATUS', 'Products Status:');
define('TEXT_PRODUCT_AVAILABLE', 'In Stock');
define('TEXT_PRODUCT_NOT_AVAILABLE', 'Out of Stock');

define('TEXT_PRODUCT_INFO_NONE', 'Please select a Product from above ...');
  define('TEXT_PRODUCT_IS_FREE','Product is Free:');
  define('TEXT_PRODUCTS_IS_FREE_EDIT','<br />*Product marked FREE');
  define('TEXT_PRODUCT_IS_CALL','Call for Price:');
  define('TEXT_PRODUCTS_IS_CALL_EDIT','<br />*Product marked CALL FOR PRICE');
  define('TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES','Priced by Attributes:');
  define('TEXT_PRODUCT_IS_PRICED_BY_ATTRIBUTE','Yes');
  define('TEXT_PRODUCT_NOT_PRICED_BY_ATTRIBUTE','No');
  define('TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_EDIT','<br />*Display price will include lowest group attributes prices plus price');
  define('TEXT_PRODUCTS_MIXED','Qty Min/Unit Mix:');
  define('TEXT_PRODUCTS_MIXED_DISCOUNT_QUANTITY', 'Discount Qty Applies to Mixed Attributes');

  define('TEXT_PRODUCTS_QUANTITY_MIN_RETAIL','Qty Min:');
  define('TEXT_PRODUCTS_QUANTITY_UNITS_RETAIL','Qty Units:');
  define('TEXT_PRODUCTS_QUANTITY_MAX_RETAIL','Qty Max:');
  define('TEXT_PRODUCTS_QUANTITY_MAX_RETAIL_EDIT','0= Unlimited<br />1= No Qty Box/Max Value');

define('TEXT_FEATURED_PRODUCT_INFO', 'Featured Product Info:');
define('TEXT_FEATURED_PRODUCT', 'Product:');
define('TEXT_FEATURED_EXPIRES_DATE', 'Expiry Date:');
define('TEXT_FEATURED_AVAILABLE_DATE', 'Available Date:');
define('TEXT_FEATURED_PRODUCTS_STATUS', 'Featured Status:');
define('TEXT_FEATURED_PRODUCT_AVAILABLE', 'Active');
define('TEXT_FEATURED_PRODUCT_NOT_AVAILABLE', 'Inactive');
define('TEXT_FEATURED_DISABLED', '<strong>NOTE: Featured Product Info is currently disabled, expired or not yet active</strong>');
define('TEXT_FEATURED_CONFIRM_DELETE', 'Please confirm that you want to delete the Featured status associated with this product');

define('TEXT_SPECIALS_PRODUCT', 'Product:');
define('TEXT_SPECIALS_SPECIAL_PRICE', 'Special Price:');
define('TEXT_SPECIALS_EXPIRES_DATE', 'Expiry Date:');
define('TEXT_SPECIALS_AVAILABLE_DATE', 'Available Date:');
define('TEXT_SPECIALS_PRICE_TIP', '<b>Specials Notes:</b><ul><li>You can enter a percentage to deduct in the Specials Price field, for example: <b>20%</b></li><li>If you enter a new price, the decimal separator must be a \'.\' (decimal-point), example: <b>49.99</b></li><li>Leave the expiry date empty for no expiration</li></ul>');
define('TEXT_SPECIALS_PRODUCT_INFO', 'Special Price Info:');
define('TEXT_SPECIALS_PRODUCTS_STATUS', 'Specials Status:');
define('TEXT_SPECIALS_PRODUCT_AVAILABLE', 'Active');
define('TEXT_SPECIALS_PRODUCT_NOT_AVAILABLE', 'Inactive');
define('TEXT_SPECIALS_NO_GIFTS','No Specials on GV');
define('TEXT_SPECIAL_DISABLED', '<strong>NOTE: Special Product Info is currently disabled, expired or not yet active</strong>');
define('TEXT_SPECIALS_CONFIRM_DELETE', 'Please confirm that you want to delete the Special associated with this product');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_NEW_PRICE', 'New Price:');
define('TEXT_INFO_ORIGINAL_PRICE', 'Original Price:');
define('TEXT_INFO_PERCENTAGE', 'Percentage:');
define('TEXT_INFO_AVAILABLE_DATE', 'Available On:');
define('TEXT_INFO_EXPIRES_DATE', 'Expires At:');
define('TEXT_INFO_STATUS_CHANGE', 'Status Change:');
define('TEXT_IMAGE_NONEXISTENT', 'No Image Exists');

define('TEXT_INFO_HEADING_DELETE_FEATURED', 'Delete Featured');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete the featured product?');

  define('TEXT_ATTRIBUTES_INSERT_INFO', '<strong>Define the Attribute Settings then press Insert to apply</strong>');
  define('TEXT_PRICED_BY_ATTRIBUTES', 'Priced by Attributes');
  define('TEXT_PRODUCTS_PRICE', 'Products Price: ');
  define('TEXT_SPECIAL_PRICE', 'Special Price: ');
  define('TEXT_SALE_PRICE', 'Sale Price: ');
  define('TEXT_FREE', 'FREE');
  define('TEXT_CALL_FOR_PRICE', 'Call for Price');

define('TEXT_ADD_ADDITIONAL_DISCOUNT', 'Add ' . DISCOUNT_QTY_ADD . ' Blank Qty Discounts:');
define('TEXT_BLANKS_INFO','All 0 Quantity Discounts will be removed when Updated');
define('TEXT_INFO_NO_DISCOUNTS', 'No Quantity Discounts have been defined');
define('TEXT_PRODUCTS_DISCOUNT_QTY_TITLE', 'Discount Levels');
define('TEXT_PRODUCTS_DISCOUNT','Discount');
define('TEXT_PRODUCTS_DISCOUNT_QTY','Minimum Qty');
define('TEXT_PRODUCTS_DISCOUNT_PRICE','Discount Value');
define('TEXT_PRODUCTS_DISCOUNT_TYPE','Type');

define('TEXT_PRODUCTS_DISCOUNT_PRICE_EACH','Calculate Price:');
define('TEXT_PRODUCTS_DISCOUNT_PRICE_EXTENDED','Extended Price:');
define('TEXT_PRODUCTS_DISCOUNT_PRICE_EACH_TAX','Calculate<br />Price: &nbsp; Taxed:');
define('TEXT_PRODUCTS_DISCOUNT_PRICE_EXTENDED_TAX','Extended<br />Price: &nbsp; Taxed:');

define('TEXT_EACH','ea.');
define('TEXT_EXTENDED','total');

define('TEXT_DISCOUNT_TYPE_INFO', 'Product Discount Info');
define('TEXT_DISCOUNT_TYPE','Discount Type:');
define('TEXT_DISCOUNT_TYPE_FROM', 'Discount Priced from:');

define('DISCOUNT_TYPE_DROPDOWN_0','None');
define('DISCOUNT_TYPE_DROPDOWN_1','Percentage');
define('DISCOUNT_TYPE_DROPDOWN_2','Actual Price');
define('DISCOUNT_TYPE_DROPDOWN_3','Amount off');

define('DISCOUNT_TYPE_FROM_DROPDOWN_0','Price');
define('DISCOUNT_TYPE_FROM_DROPDOWN_1','Special');

define('TEXT_UPDATE_COMMIT','Update and Commit all changes from current screen display');

define('TEXT_PRODUCTS_TAX_CLASS', 'Tax Class:');

define('TEXT_INFO_MASTER_CATEGORIES_ID_WARNING', '<strong>Warning:</strong> The Product Master Category ID# %s does not match Current Category ID# %s and Product is Not Linked!');
define('TEXT_INFO_MASTER_CATEGORIES_CURRENT', ' Current Category ID# %s matches the Master Category ID# %s');
define('TEXT_INFO_MASTER_CATEGORIES_ID_UPDATE_TO_CURRENT', 'Update Master Categories ID# %s to match Current Category ID# %s');

define('PRODUCT_WARNING_UPDATE', 'Please make any changes then press Update to save');
define('PRODUCT_UPDATE_SUCCESS', 'Successful Update of Product Changes!');
define('PRODUCT_WARNING_UPDATE_CANCEL', 'Changes were Cancelled and not saved ...');
define('TEXT_INFO_EDIT_CAUTION', '<strong>Click to begin Editing ...</strong>');
define('TEXT_INFO_PREVIEW_ONLY', 'Preview Only ... Current Price Status ... Preview Only');
define('TEXT_INFO_UPDATE_REMINDER', '<strong>Edit Product Information then Update to save</strong>');
