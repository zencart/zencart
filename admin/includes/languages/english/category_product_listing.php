<?php

/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jul 16 Modified in v1.5.6c $
 */
define('HEADING_TITLE', 'Categories / Products');
define('HEADING_TITLE_GOTO', 'Go To:');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_CATEGORIES_PRODUCTS', 'Categories / Products');
define('TABLE_HEADING_MODEL', 'Model');

define('TABLE_HEADING_PRICE', 'Price/Special/Sale');
define('TABLE_HEADING_QUANTITY', 'Quantity');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_CATEGORIES_SORT_ORDER', 'Sort');
define('TABLE_HEADING_ACTION', 'Action');
define('TEXT_PRODUCTS_STATUS_ON_OF', ' of ');
define('TEXT_PRODUCTS_STATUS_ACTIVE', ' active ');
define('TEXT_CATEGORIES', 'Categories:');
define('TEXT_PRODUCTS', 'Products:');
define('TEXT_INFO_HEADING_DELETE_CATEGORY', 'Delete Category');
define('TEXT_DELETE_CATEGORY_INTRO', 'Are you sure you want to delete this category?');
define('TEXT_DELETE_CATEGORY_INTRO_LINKED_PRODUCTS', '<strong>Warning:</strong> Linked Products whose Master Categories ID are deleted will not price properly. You should first assure that when deleting a Category that contains Linked Products that you reset the Product(s) Master Categories ID to another Category before removing a Category');
define('TEXT_INFO_HEADING_MOVE_CATEGORY', 'Move Category');
define('TEXT_MOVE_CATEGORIES_INTRO', 'Please select which category you wish <b>%s</b> to reside in');
define('TEXT_MOVE', 'Move <b>%s</b> to:');
define('TEXT_INFO_HEADING_DELETE_PRODUCT', 'Delete Product');
define('TEXT_DELETE_PRODUCT_INTRO', 'Are you sure you want to permanently delete this product?<br /><br /><strong>Warning:</strong> On Linked Products<br />1 Make sure the Master Category has been changed if you are deleting the Product from the Master Category<br />2 Check the checkbox for the Category to Delete the Product from');
define('TEXT_INFO_HEADING_MOVE_PRODUCT', 'Move Product');
define('TEXT_MOVE_PRODUCTS_INTRO', 'Please select which category you wish <b>%s</b> to reside in');
define('TEXT_INFO_CURRENT_CATEGORIES', 'Current Categories: ');
define('TEXT_INFO_HEADING_COPY_TO', 'Copy To');
define('TEXT_INFO_COPY_TO_INTRO', 'Please choose a new category you wish to copy this product to');
define('TEXT_INFO_CURRENT_PRODUCT', 'Current Product: ');
define('TEXT_HOW_TO_COPY', 'Copy Method:');
define('TEXT_COPY_AS_LINK','Link this product into another category as selected above');
define('TEXT_COPY_AS_DUPLICATE','Create a Duplicate product in the category selected above');
define('TEXT_COPY_METATAGS','Copy Metatags to Duplicate?');
define('TEXT_COPY_LINKED_CATEGORIES','Copy Linked Categories to Duplicate?');
define('TEXT_COPY_AS_DUPLICATE_METATAGS', 'Metatags for Language ID#%u copied from Product ID#%u to duplicate Product ID#%u');
define('TEXT_COPY_AS_DUPLICATE_CATEGORIES', 'Linked Category ID#%u copied from Product ID#%u to duplicate Product ID#%u');
define('TEXT_COPY_AS_DUPLICATE_DISCOUNTS', 'Discounts copied from Product ID#%u to duplicate Product ID#%u');
define('TEXT_INFO_HEADING_ATTRIBUTE_FEATURES', 'Attributes Changes for Products ID# ');
define('TEXT_PRODUCTS_ATTRIBUTES_INFO', 'Attribute Features For:');
define('TEXT_PRODUCT_ATTRIBUTES_DOWNLOADS', 'Downloads: ');
define('TEXT_INFO_ATTRIBUTES_FEATURES_DELETE', 'Delete <strong>ALL</strong> Product Attributes for:<br />');
define('TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_PRODUCT', 'Copy Attributes to another <strong>product</strong> from:<br />');
define('TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_CATEGORY', 'Copy Attributes to another <strong>category</strong> from:<br />');
define('TEXT_COPY_ATTRIBUTES_CONDITIONS', '<strong>How should existing product attributes be handled?</strong>');
define('TEXT_COPY_ATTRIBUTES_DELETE', '<strong>Delete</strong> first, then copy new attributes');
define('TEXT_COPY_ATTRIBUTES_UPDATE', '<strong>Update</strong> with new settings/prices, then add new ones');
define('TEXT_COPY_ATTRIBUTES_IGNORE', '<strong>Ignore</strong> and add only new attributes');
define('TEXT_ATTRIBUTE_COPY_INSERTING', '<strong>Inserting New Attribute from </strong>');
define('ICON_ATTRIBUTES', 'Attribute Features');

// Products and Attribute Copy Options
define('TEXT_COPY_ATTRIBUTES_ONLY', 'Only used for Duplicate Products ...');
define('TEXT_COPY_ATTRIBUTES', 'Copy Product Attributes to Duplicate?');
define('TEXT_COPY_ATTRIBUTES_YES', 'Yes');
define('TEXT_COPY_ATTRIBUTES_NO', 'No');

// Products and Discount Copy Options
define('TEXT_COPY_DISCOUNTS_ONLY', 'Only used for Duplicate Products with Quantity Discounts ...');
define('TEXT_COPY_DISCOUNTS', 'Copy Product Quantity Discounts to Duplicate?');
define('TEXT_COPY_DISCOUNTS_YES', 'Yes');
define('TEXT_COPY_DISCOUNTS_NO', 'No');

// From categories.php in 1.5.5
// categories status
define('TEXT_INFO_HEADING_STATUS_CATEGORY', 'Change Category Status for:');
define('TEXT_CATEGORIES_STATUS_INTRO', 'Change <strong>this</strong> category\'s status to: ');
define('TEXT_CATEGORIES_STATUS_OFF', 'Disabled');
define('TEXT_CATEGORIES_STATUS_ON', 'Enabled');
define('TEXT_PRODUCTS_STATUS_INFO', 'Change ALL included products status to:');
define('TEXT_PRODUCTS_STATUS_OFF', 'Disabled');
define('TEXT_PRODUCTS_STATUS_ON', 'Enabled');
define('TEXT_PRODUCTS_STATUS_NOCHANGE', 'Unchanged');
define('TEXT_CATEGORIES_STATUS_WARNING', '<strong>WARNING ...</strong><br />Note: Disabling products (whether they are linked or master products) in a category will result in ALL instances of that product being disabled in ALL categories.');
define('TEXT_SUBCATEGORIES_STATUS_INFO', 'Change ALL subcategories status to:');
define('TEXT_SUBCATEGORIES_STATUS_OFF', 'Disabled');
define('TEXT_SUBCATEGORIES_STATUS_ON', 'Enabled');
define('TEXT_SUBCATEGORIES_STATUS_NOCHANGE', 'Unchanged');

define('WARNING_PRODUCTS_IN_TOP_INFO', 'WARNING: You have Products in the Top Level Category. This will cause pricing to not work properly in the Catalog. Products found: ');

define('TEXT_COPY_MEDIA_MANAGER', 'Copy media?');
