<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2019 June 16 Modified in v1.5.7 $
 */

define('HEADING_TITLE','Products to Multiple Categories Link Manager');
define('HEADING_TITLE2','Categories / Products');

define('TEXT_INFO_PRODUCTS_TO_CATEGORIES_AVAILABLE', 'Categories with Products that are Available for Linking ...');

define('TABLE_HEADING_PRODUCTS_ID', 'Prod ID');
define('TABLE_HEADING_PRODUCT', 'Product Name');
define('TABLE_HEADING_MODEL', 'Model');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_HEADING_EDIT_PRODUCTS_TO_CATEGORIES', 'Edit Product Links');
define('TEXT_PRODUCTS_ID', 'Product ID# ');
define('TEXT_PRODUCTS_NAME', 'Product: ');
define('TEXT_PRODUCTS_MODEL', 'Model: ');
define('TEXT_PRODUCTS_PRICE', 'Price: ');
define('BUTTON_UPDATE_CATEGORY_LINKS', 'Update Category Links');
define('BUTTON_NEW_PRODUCTS_TO_CATEGORIES', 'Select Another Product by ID#');
define('BUTTON_CATEGORY_LISTING', 'Category Listing');
define('TEXT_SET_PRODUCTS_TO_CATEGORIES_LINKS', 'Show Product to Categories Links for: ');
define('TEXT_INFO_LINKED_TO_COUNT', '&nbsp;&nbsp;Current Number of Linked Categories: ');

define('HEADER_CATEGORIES_GLOBAL_CHANGES', 'Global Category Tools');

define('TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER_INTRO', 'This product is currently linked to the categories selected below.<br>To add/remove links, select/deselect the checkboxes as required and then click on the ' . BUTTON_UPDATE_CATEGORY_LINKS . ' button.<br />Further product/category actions are available using the ' . HEADER_CATEGORIES_GLOBAL_CHANGES . ' below.');

define('TEXT_INFO_MASTER_CATEGORY_CHANGE','A product has a Master Category ID (for pricing purposes) that can be considered as the category where the product actually resides. Additionally, a product can be linked (copied) to any number of other categories.<br>The Master Category ID can be changed by using this Master Category dropdown, that offers the currently linked categories as possible alternatives.<br>To set the Master Category ID to <strong>any</strong> category, use the "Move" option on the category listing page.');

define('TEXT_SET_MASTER_CATEGORIES_ID', '<strong>WARNING:</strong> You must set the MASTER CATEGORIES ID before changing Linked Categories');

// copy category to category linked
define('TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED_HEADING', 'Link (copy) Products from one Category to another Category');
define('TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED', 'Example: a Copy <strong>from</strong> REF Category #8 <strong>to</strong> target Category #22 will create linked copies of ALL the products in Category 8, in Category 22.');
define('TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED', 'Select ALL products from the REF Category: ');
define('TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED', 'Link (copy) to the TARGET Category: ');
define('BUTTON_COPY_CATEGORY_LINKED', 'Copy Products as Linked');

define('WARNING_PRODUCTS_LINK_TO_CATEGORY_REMOVED', 'WARNING: Product has been reset and is no longer part of this Category...');
define('WARNING_CATEGORY_REF_NOT_EXIST','<strong>REF</strong> Category ID#%u invalid (does not exist)');
define('WARNING_CATEGORY_TARGET_NOT_EXIST','<strong>TARGET</strong> Category ID#%u invalid (does not exist)');
define('WARNING_CATEGORY_IDS_DUPLICATED', 'Warning: same Category IDs (#%u)');
define('WARNING_CATEGORY_NO_PRODUCTS', '<strong>REF</strong> Category ID#%u invalid (contains no products)');
define('WARNING_CATEGORY_SUBCATEGORIES', '<strong>TARGET</strong> Category ID#%u invalid (contains subcategories)');
define('WARNING_NO_CATEGORIES_ID', 'Warning: no categories were selected ... no changes were made');
define('SUCCESS_COPY_LINKED', '%1$u product(s) copied (linked), from REF Category ID#%2$u to TARGET Category ID#%3$u');
define('WARNING_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED_MISSING', 'WARNING: Copy completed to Invalid Category to Link: ');

define('WARNING_COPY_FROM_IN_TO_LINKED', 'WARNING: No products copied (all products in Category ID#%1$u are already linked into Category ID#%2$u)');

// remove category to category linked
define('TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_LINKED_HEADING', 'Remove Linked Products from a Category');
define('TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_LINKED', 'Example: Using REF Category #8 and TARGET Category #22 will remove any linked Products from the target Category #22 that exist in the reference Category #8.');
define('TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED', 'Select ALL Products in the REF Category: ');
define('TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED', 'Remove Any Linked Products from the TARGET Category: ');
define('BUTTON_REMOVE_CATEGORY_LINKED', 'Remove Linked Products');

define('SUCCESS_REMOVE_LINKED', '%1$u linked product(s) removed from Category ID#%2$u');

define('WARNING_REMOVE_FROM_IN_TO_LINKED', 'WARNING: No changes made: no products in TARGET Category ID#%1$u are linked from REF Category ID#%2$u');

define('WARNING_MASTER_CATEGORIES_ID_CONFLICT', '<strong>WARNING: MASTER CATEGORIES ID CONFLICT!! </strong>');
define('TEXT_INFO_MASTER_CATEGORIES_ID_CONFLICT', '<strong>Master Categories ID is: </strong>');
define('TEXT_INFO_MASTER_CATEGORIES_ID_PURPOSE', 'NOTE: Master Category is used for pricing purposes where the product category affects the pricing on linked products, example: Sales<br />');
define('WARNING_MASTER_CATEGORIES_ID_CONFLICT_FIX', 'To fix this problem, you have been redirected to the first product of conflict. Re-assign the Master Categories ID so that it is no longer the Products Master Category ID for the Category that you are trying to remove it from and try again. When all conflicts have been corrected, you will then be able to complete the removal that you requested.');
define('TEXT_MASTER_CATEGORIES_ID_CONFLICT_FROM', ' Conflicting From Category: ');
define('TEXT_MASTER_CATEGORIES_ID_CONFLICT_TO', ' Conflicting To Category: ');
define('SUCCESS_MASTER_CATEGORIES_ID', 'Successful update of Product to Categories Links ...');
define('WARNING_MASTER_CATEGORIES_ID', 'WARNING: No Master Category is set!');

define('TEXT_PRODUCTS_ID_INVALID', 'WARNING: INVALID PRODUCTS ID OR NO PRODUCT SELECTED');
define('TEXT_PRODUCTS_ID_NOT_REQUIRED', 'Note: A Product does not need to be selected to use the ' . HEADER_CATEGORIES_GLOBAL_CHANGES . '. However, selecting a Product will display all the available Categories and their ID numbers.');

// reset all products to new master_categories_id
// copy category to category linked
define('TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER_HEADING', 'Reset the Master Category ID for ALL Products in a Category');
define('TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER', 'Example: Resetting Category 22 will reset ALL products in Category 22 to have a Master Category ID of 22.');
define('TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER', 'Reset the Master Category ID for All Products in Category: ');
define('BUTTON_RESET_CATEGORY_MASTER', 'Reset Master Categories ID');

define('SUCCESS_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER', 'All products in Category ID#%1$d have been reset to have Master Category ID#%1$d');

define('TEXT_CATEGORIES_NAME', 'Categories Name');