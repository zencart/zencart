<?php
/**
 * @package admin
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista Apr 21 2020 New in v1.5.7 $
*/

define('HEADING_TITLE', 'Multiple Product Copy/Move/Delete');

// SELECTIONS page 1
define('TEXT_COPY_AS_LINK', 'Copy Products as Linked ');
define('TEXT_COPY_AS_DUPLICATE', 'Copy Products as Duplicates (new products)');
define('TEXT_COPY_AS_DUPLICATE_ENABLE', 'Enable the new products');
define('TEXT_COPY_ATTRIBUTES', 'Copy Attributes');
define('TEXT_COPY_METATAGS', 'Copy Meta Tags');
define('TEXT_COPY_LINKED_CATEGORIES', 'Copy Linked Categories');
define('TEXT_COPY_DISCOUNTS', 'Copy Quantity Discounts');
define('TEXT_COPY_SPECIALS', 'Copy Special Prices');
define('TEXT_COPY_FEATURED', 'Copy Featured settings');
define('TEXT_ALL_CATEGORIES', 'All Categories'); // this constant declared earlier to be used subsequently

define('TEXT_MOVE_TO', 'Move Products');
define('TEXT_MOVE_PRODUCTS_INFO_SEARCH_CATEGORY', '<p>When the search is restricted to a Search Category:<br>Linked Products will be unlinked from that current category and linked to the Target Category.<br>Products in their Master Category will have their Master Category ID changed to that of the Target Category.</p>');

define('TEXT_MOVE_PRODUCTS_INFO_SEARCH_GLOBAL', '<p>When the search is <strong>not</strong> restricted ("' . TEXT_ALL_CATEGORIES . '"):<br>All selected products will have their Master Category ID changed to that of the Target Category. Product links will be unchanged.');
define('TEXT_TARGET_CATEGORY', 'Target Category (for Copy/Move):');

define('TEXT_COPY_AS_DELETE_SPECIALS', 'Delete Specials from Products');
define('TEXT_COPY_AS_DELETE_LINKED', 'Delete Linked Products');
define('TEXT_COPY_AS_DELETE_ALL', 'Delete Any Products');
define('TEXT_COPY_AS_DELETE_ALL_INFO', 'This option allows the multiple permanent deletion of products. Selection of any product (whether linked/master) will delete <span style="text-decoration: underline">ALL INSTANCES</span> (both master and linked) of that product. USE WITH CARE and ensure you have a verified backup of your database first!');

// Search Criteria
define('TEXT_ENTER_CRITERIA', 'Search/Filter Criteria:');
define('TEXT_PRODUCTS_CATEGORY', 'Search in Category:');
define('TEXT_INCLUDE_SUBCATS', ' include subcategories');
define('TEXT_ENTER_SEARCH_KEYWORDS', 'Find products containing the following keywords (in product\'s Model, Name and Manufacturer name) ');
define('TEXT_SEARCH_DESCRIPTIONS', 'Also search in product Descriptions ');
define('TEXT_PRODUCTS_MANUFACTURER', 'Manufacturer ');
define('TEXT_ALL_MANUFACTURERS', 'All Manufacturers');
define('ENTRY_MIN_PRICE', 'Store (displayed) Price &gt;= ');
define('ENTRY_MAX_PRICE', 'Store (displayed) Price &lt;= ');
define('ENTRY_MAX_PRODUCT_QUANTITY', 'Product Quantity &lt;= ');
define('ENTRY_SHOW_IMAGES', 'Show images? ');
define('ENTRY_AUTO_CHECK', 'Automatically select all matching products? ');
define('ENTRY_RESULTS_ORDER_BY', 'Order results by ');
//constant name suffix TEXT_ORDER_BY_?? auto-defined by option name
define('TEXT_ORDER_BY_ID', 'Product ID');
define('TEXT_ORDER_BY_MANUFACTURER', 'Manufacturer');
define('TEXT_ORDER_BY_MODEL', 'Model');
define('TEXT_ORDER_BY_NAME', 'Name');
define('TEXT_ORDER_BY_PRICE', 'Price');
define('TEXT_ORDER_BY_QUANTITY', 'Quantity');
define('TEXT_ORDER_BY_STATUS', 'Status');

define('TEXT_TIPS', '<h2>Notes:</h2>
<h3>Searching:</h3>
<ul><li>Products currently existing in the Target Category are automatically excluded from the search results.</li>
<li>Search keywords may be left blank if either a Category or a Manufacturer is selected,  or one of the Store Price fields has a value.</li>
<li>You may search using only <strong>one</strong> of the Store Price fields if you want to find all products with prices greater/less than a specific amount.</li>
<li>The decimal separator in the Store Price entries <strong>must</strong> be a \'.\' (decimal-point), example: <b>49.99</b></li></ul>
<h3>Copy as Duplicate (new) Products:</h3>
<ul><li>Attributes can be optionally copied. But for Downloads, the Download filename is NOT copied.</li>
<li>Reviews are NOT copied.</li></ul>
<h3>Deleting Products</h3>
<h4>Delete Products permanently from ALL Categories:</h4>
<ul><li>Deletions are permanent and cannot be undone</li>
<li>If the product\'s Main Image is unique, it will be deleted, as will the Main Image Medium and Large Image. Additional Images and Additional Large Images will NOT be removed</li></ul>
<h4>Delete Linked Products from ONE Category:</h4>
<ul><li>Deleting from ONE Category will unlink a Product from that Category.</li>
<li>If that category is a product\'s master_categories_id, the product will not be deleted.</li></ul>');

//RESULTS page 2
define('TEXT_PRODUCTS_FOUND', '%u matching product(s) found.');

// Search Critera summary
define('TEXT_SEARCH_RESULT_CATEGORY', 'Search category: %s');
define('TEXT_SEARCH_RESULT_KEYWORDS', 'Search keywords: "%s"');
define('TEXT_SEARCH_RESULT_MANUFACTURER', 'Search manufacturer: %s');
define('TEXT_SEARCH_RESULT_MIN_PRICE', 'Search price > %s');
define('TEXT_SEARCH_RESULT_MAX_PRICE', 'Search price < %s');
define('TEXT_SEARCH_RESULT_QUANTITY', 'Search quantity < %u');
define('TEXT_SEARCH_RESULT_TARGET', 'Target Category: "%2$s" ID#%1$u');
define('TEXT_EXISTING_PRODUCTS_NOT_SHOWN', 'Only matching products <strong>not already present</strong> in the Target Category are listed.');
define('TABLE_HEADING_SELECT', 'Selected');
define('TEXT_TOGGLE_ALL', 'toggle all');
define('TABLE_HEADING_PRODUCTS_ID', 'ID');
define('TABLE_HEADING_IMAGE', 'Image');
define('TABLE_HEADING_STATUS', 'Status');
define('IMAGE_ICON_STATUS_ON_EDIT_PRODUCT', 'product is enabled -> Edit Product');
define('IMAGE_ICON_STATUS_OFF_EDIT_PRODUCT', 'product is disabled -> Edit Product');

define('TABLE_HEADING_CATEGORY', 'In Category');
define('TABLE_HEADING_LINKED_MASTER', 'Linked %1$s<br>Master %2$s');
define('TABLE_HEADING_MASTER_CATEGORY', 'Master Category');
define('IMAGE_ICON_MASTER', 'Product in Master Category');
define('IMAGE_ICON_LINKED_EDIT_LINKS', 'product is linked -> Edit in Link Manager');
define('IMAGE_ICON_NOT_LINKED_EDIT_LINKS', 'product is not linked -> Edit in Link Manager');

define('TEXT_PRODUCT_MASTER_CATEGORY_CHANGE', 'move this product/change master category');
define('TEXT_PRODUCT_SPECIAL_EDIT', 'edit this Special Price');

define('TABLE_HEADING_NAME', 'Name');
define('TABLE_HEADING_PRICE', 'Store Price');
define('TABLE_HEADING_QUANTITY', 'Quantity');
define('TABLE_HEADING_MFG', 'Manufacturer');

define('IMAGE_ICON_EDIT_LINKS', 'Edit Link/Master Category');
//define('IMAGE_ICON_CATEGORY_LINKED', 'linked category: %2$s ID#%1$u . Edit in Link Manager');

define('BUTTON_RETRY', 'Modify Search');
define('BUTTON_CATEGORY_LISTING_SEARCH', 'Product Listing - Search Category');
define('BUTTON_CATEGORY_LISTING_TARGET', 'Product Listing - Target Category');

//RESULTS
define('TEXT_DELETE_LINKED', 'Category "%2$s" ID#%1$u');
define('TEXT_DELETE_LINKED_INFO', '');
define('TEXT_INCLUDED_SUBCATS', 'Included subcategories:');
define('TEXT_DISABLED', 'disabled');
//CONFIRM page 3
define('BUTTON_NEW_SEARCH', 'New Search');

//Confirm Copy Linked
define('TEXT_PRODUCTS_COPIED_TO', '%1$u product(s) copied to Category "%3$s" ID#%2$u ');

//Confirm Copy Duplicates
//these four constants used in copy_product_confirm
define('TEXT_COPY_AS_DUPLICATE_ATTRIBUTES', 'Attributes copied from Product ID#%1$u to duplicate Product ID#%2$u');
define('TEXT_COPY_AS_DUPLICATE_METATAGS', 'Metatags for Language ID#%1$u copied from Product ID#%2$u to duplicate Product ID#%3$u');
define('TEXT_COPY_AS_DUPLICATE_CATEGORIES', 'Linked Category ID#%1$u copied from Product ID#%2$u to duplicate Product ID#%3$u');
define('TEXT_COPY_AS_DUPLICATE_DISCOUNTS', 'Discounts copied from Product ID#%1$u to duplicate Product ID#%2$u');

define('TEXT_COPY_AS_DUPLICATE_SPECIALS', 'Special price copied from Product ID#%1$u to duplicate Product ID#%2$u');
define('TEXT_COPY_AS_DUPLICATE_FEATURED', 'Featured settings copied from Product ID#%1$u to duplicate Product ID#%2$u');

//Confirm Move
define('TEXT_PRODUCTS_MOVED_TO', '%1$u product(s) moved to Category ID#%2$u "%3$s"');

//Confirm Delete Specials
define('TEXT_SPECIALS_DELETED_FROM', 'Special price(s) deleted from %u product(s).');

//Confirm Delete
define('TEXT_PRODUCTS_DELETED', '%u product(s) deleted.');

// Errors
define('ERROR_ILLEGAL_OPTION', 'Invalid option/no option set.');
define('ERROR_NO_TARGET_CATEGORY', 'No Target Category selected!');
define('ERROR_TARGET_CATEGORY_HAS_SUBCATEGORY', 'Copy/Move not allowed: Target Category "%2$s" ID#%1$u contains a subcategory');
define('ERROR_SEARCH_CATEGORY_HAS_SUBCATEGORY', 'Copy/Move not allowed: Search Category "%2$s" ID#%1$u contains a subcategory');
define('ERROR_SAME_CATEGORIES', 'The Search and Target Categories are the same: "%2$s" ID#%1$u!');
define('ERROR_NO_PRODUCTS_IN_CATEGORY', 'No products found in category "%2$s" ID#%1$u');
define('ERROR_OR_SUBS', ', or subcategories.');
define('ERROR_INVALID_KEYWORDS', 'Invalid keywords');
define('ERROR_NO_PRODUCTS_FOUND', 'No products found in "%2$s" ID#%1$u');
define('ERROR_SEARCH_CRITERIA_REQUIRED', 'No Search critera set! Set a Search category / keyword / manufacturer / price field.');
define('ERROR_ARRAY_COUNTS', 'Array of products found and array count not equal.');
define('ERROR_NO_SELECTION', 'No products selected. At least one product from the list must be selected!');
define('ERROR_CHECKBOXES_NOT_ARRAY', 'Selected checkboxes not an array.');
define('ERROR_CHECKBOX_ID', 'A selected checkbox references product ID#%u. This is not a found product ID!');
define('ERROR_COPY_DUPLICATE_NO_DUP_ID', 'The duplicate/new product ID was not returned from "copy_product_confirm.php" for copy-duplicate of product ID#%1$u to category ID#%2$u.');
define('TEXT_NO_MATCHING_PRODUCTS_FOUND', 'No products were found that matched the search criteria or all matching products already exist in the target category.');
