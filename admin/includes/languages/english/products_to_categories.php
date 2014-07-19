<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: products_to_categories.php 18698 2011-05-04 14:50:06Z wilt $
 */

define('HEADING_TITLE','Products to Multiple Categories Link Manager ...');
define('HEADING_TITLE2','Categories / Products');

define('TEXT_INFO_PRODUCTS_TO_CATEGORIES_AVAILABLE', 'Categories with Products that are Available for Linking ...');

define('TABLE_HEADING_PRODUCTS_ID', 'Prod ID');
define('TABLE_HEADING_PRODUCT', 'Product Name');
define('TABLE_HEADING_MODEL', 'Model');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_HEADING_EDIT_PRODUCTS_TO_CATEGORIES', 'EDITING PRODUCT TO CATEGORIES INFORMATION');
define('TEXT_PRODUCTS_ID', 'Product ID# ');
define('TEXT_PRODUCTS_NAME', 'Product: ');
define('TEXT_PRODUCTS_MODEL', 'Model: ');
define('TEXT_PRODUCTS_PRICE', 'Price: ');
define('BUTTON_UPDATE_CATEGORY_LINKS', 'Update Category Links');
define('BUTTON_NEW_PRODUCTS_TO_CATEGORIES', 'Select Product to Link');
define('TEXT_SET_PRODUCTS_TO_CATEGORIES_LINKS', 'Set Product to Categories Links for: ');
define('TEXT_INFO_LINKED_TO_COUNT', '&nbsp;&nbsp;Current Number of Linked Categories: ');

define('TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER_INTRO',
'The Products to Categories Linker is designed to quickly link the current Product to one or to many Categories.<br />You may also Link all of the Products in a Category to another Category or Remove Linked Products from a Category that are in another Category. (See below for additional instructions)');

define('TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER',
'For pricing purposes, each Product requires a Master Category, regardless of how many Categories it may be linked. This can be set via the Master Category Dropdown.<br />
The Product is currently linked to the Category or Categories that are checked above. To add a new Category or Categories simply check the checkbox next to the Category name. To remove an existing linked Category or Categories, simply uncheck the checkbox next to the Category name.<br />
When you have checked all of the Categories that you want this Product to be linked to, press the ' . BUTTON_UPDATE_CATEGORY_LINKS . '<br />'
);

define('HEADER_CATEGORIES_GLOBAL_CHANGES', 'Global Category Link Changes and Master Category ID Reset');

define('TEXT_SET_MASTER_CATEGORIES_ID', '<strong>WARNING:</strong> You must set the MASTER CATEGORIES ID before changing Linked Categories');

// copy category to category linked
define('TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED', '<strong>Copy ALL Products in a Category as LINKED Products to another Category ...</strong><br />Example: Using 8 and 22 would Link ALL Products in Category 8 to Category 22');
define('TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED', 'Select All Products in Category: ');
define('TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED', 'Link to Category: ');
define('BUTTON_COPY_CATEGORY_LINKED', 'Copy Products as Linked ');

define('WARNING_PRODUCTS_LINK_TO_CATEGORY_REMOVED', 'WARNING: Product has been reset and is no longer part of this Category ...');
define('WARNING_COPY_LINKED', 'WARNING: ');
define('WARNING_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED', 'Invalid Category to link Products From: ');
define('WARNING_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED', 'Invalid Category to link Products To: ');
define('WARNING_NO_CATEGORIES_ID', 'Warning: no categories were selected ... no changes were made');
define('SUCCESS_COPY_LINKED', 'Successful update of Products as Linked ... ');
define('SUCCESS_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED', 'Valid Category to link Products From: ');
define('SUCCESS_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED', 'Valid Category to link Products To: ');
define('WARNING_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED_MISSING', 'WARNING: Copy completed to Invalid Category to Link: ');

define('WARNING_COPY_FROM_IN_TO_LINKED', '<strong>WARNING: No changes made products are already linked ... </strong>');

// remove category to category linked
define('TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_LINKED', '<strong>Remove ALL Products in a Category that are LINKED Products to another Category ...</strong><br />Example: Using 8 and 22 would Unlink ALL Products in Category 8 from Category 22');
define('TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED', 'Select All Products in Category: ');
define('TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED', 'Remove from being Linked to Category: ');
define('BUTTON_REMOVE_CATEGORY_LINKED', 'Remove Products as Linked ');

define('WARNING_REMOVE_LINKED', 'WARNING: ');
define('WARNING_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED', 'Invalid Category to remove link Products From: ');
define('WARNING_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED', 'Invalid Category to remove link Products To: ');
define('SUCCESS_REMOVE_LINKED', 'Successful removal of Products as Linked ... ');
define('SUCCESS_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED', 'Valid Category to remove linked Products From: ');
define('SUCCESS_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED', 'Valid Category to remove linked Products To: ');

define('WARNING_REMOVE_FROM_IN_TO_LINKED', '<strong>WARNING: No changes made no products were linked ... </strong>');

define('WARNING_MASTER_CATEGORIES_ID_CONFLICT', '<strong>WARNING: MASTER CATEGORIES ID CONFLICT!! </strong>');
define('TEXT_INFO_MASTER_CATEGORIES_ID_CONFLICT', '<strong>Master Categories ID is: </strong>');
define('TEXT_INFO_MASTER_CATEGORIES_ID_PURPOSE', 'NOTE: Master Category is used for pricing purposes where the product category affects the pricing on linked products, example: Sales<br />');
define('WARNING_MASTER_CATEGORIES_ID_CONFLICT_FIX', 'To fix this problem, you have been redirected to the first product of conflict. Re-assign the Master Categories ID so that it is no longer the Products Master Category ID for the Category that you are trying to remove it from and try again. When all conflicts have been corrected, you will then be able to complete the removal that you requested.');
define('TEXT_MASTER_CATEGORIES_ID_CONFLICT_FROM', ' Conflicting From Category: ');
define('TEXT_MASTER_CATEGORIES_ID_CONFLICT_TO', ' Conflicting To Category: ');
define('SUCCESS_MASTER_CATEGORIES_ID', 'Successful update of Product to Categories Links ...');
define('WARNING_MASTER_CATEGORIES_ID', 'WARNING: No Master Category is set!');

define('TEXT_PRODUCTS_ID_INVALID', 'WARNING: INVALID PRODUCTS ID OR NO PRODUCT SELECT');
define('TEXT_PRODUCTS_ID_NOT_REQUIRED', 'Note: A Product ID does not need to be set to use Link All Products from one Category to another Category.<br />However, setting a valid Products ID will display all the available Categories and their ID numbers.');

// reset all products to new master_categories_id
// copy category to category linked
define('TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER', '<strong>Reset ALL Products in the selected Category to use the selected Category as the new Master Categories ID ...</strong><br />Example: Resetting Category 22 would set ALL Products in Category 22 to use Category 22 as the Master Category ID');
define('TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER', 'Reset the Master Categories ID for All Products in Category: ');
define('BUTTON_RESET_CATEGORY_MASTER', 'Reset Master Categories ID');

define('WARNING_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER', 'WARNING: Invalid Category selected ...');
define('SUCCESS_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER', 'Successful update of all Products to new Master Categories ID for Category: ');
