<?php
/**
 * @package admin
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: product.php 15883 2010-04-11 16:41:26Z wilt $
 */

define('HEADING_TITLE', 'Categories / Products');
define('HEADING_TITLE_GOTO', 'Go To:');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_CATEGORIES_PRODUCTS', 'Categories / Products');
define('TABLE_HEADING_CATEGORIES_SORT_ORDER', 'Sort');

define('TABLE_HEADING_PRICE','Price/Special/Sale');
define('TABLE_HEADING_QUANTITY','Quantity');

define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_STATUS', 'Status');

define('TEXT_CATEGORIES', 'Categories:');
define('TEXT_SUBCATEGORIES', 'Subcategories:');
define('TEXT_PRODUCTS', 'Products:');
define('TEXT_PRODUCTS_PRICE_INFO', 'Price:');
define('TEXT_PRODUCTS_TAX_CLASS', 'Tax Class:');
define('TEXT_PRODUCTS_AVERAGE_RATING', 'Average Rating:');
define('TEXT_PRODUCTS_QUANTITY_INFO', 'Quantity:');
define('TEXT_DATE_ADDED', 'Date Added:');
define('TEXT_DATE_AVAILABLE', 'Date Available:');
define('TEXT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_IMAGE_NONEXISTENT', 'IMAGE DOES NOT EXIST');
define('TEXT_NO_CHILD_CATEGORIES_OR_PRODUCTS', 'Please insert a new category or product in this level.');
define('TEXT_PRODUCT_MORE_INFORMATION', 'For more information, please visit this products <a href="http://%s" target="blank">webpage</a>.');
define('TEXT_PRODUCT_DATE_ADDED', 'This product was added to our catalog on %s.');
define('TEXT_PRODUCT_DATE_AVAILABLE', 'This product will be in stock on %s.');

define('TEXT_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_EDIT_CATEGORIES_ID', 'Category ID:');
define('TEXT_EDIT_CATEGORIES_NAME', 'Category Name:');
define('TEXT_EDIT_CATEGORIES_IMAGE', 'Category Image:');
define('TEXT_EDIT_SORT_ORDER', 'Sort Order:');

define('TEXT_INFO_COPY_TO_INTRO', 'Please choose a new category you wish to copy this product to');
define('TEXT_INFO_CURRENT_CATEGORIES', 'Current Categories: ');

define('TEXT_INFO_HEADING_NEW_CATEGORY', 'New Category');
define('TEXT_INFO_HEADING_EDIT_CATEGORY', 'Edit Category');
define('TEXT_INFO_HEADING_DELETE_CATEGORY', 'Delete Category');
define('TEXT_INFO_HEADING_MOVE_CATEGORY', 'Move Category');
define('TEXT_INFO_HEADING_DELETE_PRODUCT', 'Delete Product');
define('TEXT_INFO_HEADING_MOVE_PRODUCT', 'Move Product');
define('TEXT_INFO_HEADING_COPY_TO', 'Copy To');

define('TEXT_DELETE_CATEGORY_INTRO', 'Are you sure you want to delete this category?');
define('TEXT_DELETE_PRODUCT_INTRO', 'Are you sure you want to permanently delete this product?<br /><br /><strong>Warning:</strong> On Linked Products<br />1 Make sure the Master Category has been changed if you are deleting the Product from the Master Category<br />2 Check the checkbox for the Category to Delete the Product from');

define('TEXT_DELETE_WARNING_CHILDS', '<b>WARNING:</b> There are %s (child-)categories still linked to this category!');
define('TEXT_DELETE_WARNING_PRODUCTS', '<b>WARNING:</b> There are %s products still linked to this category!');

define('TEXT_MOVE_PRODUCTS_INTRO', 'Please select which category you wish <b>%s</b> to reside in');
define('TEXT_MOVE_CATEGORIES_INTRO', 'Please select which category you wish <b>%s</b> to reside in');
define('TEXT_MOVE', 'Move <b>%s</b> to:');

define('TEXT_NEW_CATEGORY_INTRO', 'Please fill out the following information for the new category');
define('TEXT_CATEGORIES_NAME', 'Category Name:');
define('TEXT_CATEGORIES_IMAGE', 'Category Image:');
define('TEXT_SORT_ORDER', 'Sort Order:');

define('TEXT_PRODUCTS_STATUS', 'Products Status:');
define('TEXT_PRODUCTS_VIRTUAL', 'Product is Virtual:');
define('TEXT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING', 'Always Free Shipping:');
define('TEXT_PRODUCTS_QTY_BOX_STATUS', 'Products Quantity Box Shows:');
define('TEXT_PRODUCTS_DATE_AVAILABLE', 'Date Available:');
define('TEXT_PRODUCT_AVAILABLE', 'In Stock');
define('TEXT_PRODUCT_NOT_AVAILABLE', 'Out of Stock');
define('TEXT_PRODUCT_IS_VIRTUAL', 'Yes, Skip Shipping Address');
define('TEXT_PRODUCT_NOT_VIRTUAL', 'No, Shipping Address Required');
define('TEXT_PRODUCT_IS_ALWAYS_FREE_SHIPPING', 'Yes, Always Free Shipping');
define('TEXT_PRODUCT_NOT_ALWAYS_FREE_SHIPPING', 'No, Normal Shipping Rules');
define('TEXT_PRODUCT_SPECIAL_ALWAYS_FREE_SHIPPING', 'Special, Product/Download Combo Requires a Shipping Address');
define('TEXT_PRODUCTS_SORT_ORDER', 'Sort Order:');

define('TEXT_PRODUCTS_QTY_BOX_STATUS_ON', 'Yes, Show Quantity Box');
define('TEXT_PRODUCTS_QTY_BOX_STATUS_OFF', 'No, Do not show Quantity Box');

define('TEXT_PRODUCTS_MANUFACTURER', 'Products Manufacturer:');
define('TEXT_PRODUCTS_NAME', 'Products Name:');
define('TEXT_PRODUCTS_DESCRIPTION', 'Products Description:');
define('TEXT_PRODUCTS_QUANTITY', 'Products Quantity:');
define('TEXT_PRODUCTS_MODEL', 'Products Model:');
define('TEXT_PRODUCTS_IMAGE', 'Products Image:');
define('TEXT_PRODUCTS_IMAGE_DIR', 'Upload to directory:');
define('TEXT_PRODUCTS_URL', 'Products URL:');
define('TEXT_PRODUCTS_URL_WITHOUT_HTTP', '<small>(without http://)</small>');
define('TEXT_PRODUCTS_PRICE_NET', 'Products Price (Net):');
define('TEXT_PRODUCTS_PRICE_GROSS', 'Products Price (Gross):');
define('TEXT_PRODUCTS_WEIGHT', 'Products Shipping Weight:');

define('EMPTY_CATEGORY', 'Empty Category');

define('TEXT_HOW_TO_COPY', 'Copy Method:');
define('TEXT_COPY_AS_LINK', 'Link product');
define('TEXT_COPY_AS_DUPLICATE', 'Duplicate product');

// Products and Attribute Copy Options
  define('TEXT_COPY_ATTRIBUTES_ONLY','Only used for Duplicate Products ...');
  define('TEXT_COPY_ATTRIBUTES','Copy Product Attributes to Duplicate?');
  define('TEXT_COPY_ATTRIBUTES_YES','Yes');
  define('TEXT_COPY_ATTRIBUTES_NO','No');

// Products and Discount Copy Options
  define('TEXT_COPY_DISCOUNTS_ONLY','Only used for Duplicate Products with Discounts ...');
  define('TEXT_COPY_DISCOUNTS','Copy Product Discounts to Duplicate?');
  define('TEXT_COPY_DISCOUNTS_YES','Yes');
  define('TEXT_COPY_DISCOUNTS_NO','No');

  define('TEXT_INFO_CURRENT_PRODUCT', 'Current Product: ');
  define('TABLE_HEADING_MODEL', 'Model');

  define('TEXT_INFO_HEADING_ATTRIBUTE_FEATURES','Attributes Changes for Products ID# ');
  define('TEXT_INFO_ATTRIBUTES_FEATURES_DELETE','Delete <strong>ALL</strong> Product Attributes for:<br />');
  define('TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO','Copy Attributes to another Product or to an entire Category from:<br />');

  define('TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_PRODUCT','Copy Attributes to another <strong>product</strong> from:<br />');
  define('TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_CATEGORY','Copy Attributes to another <strong>category</strong> from:<br />');

  define('TEXT_COPY_ATTRIBUTES_CONDITIONS','<strong>How should existing product attributes be handled?</strong>');
  define('TEXT_COPY_ATTRIBUTES_DELETE','<strong>Delete</strong> first, then copy new attributes');
  define('TEXT_COPY_ATTRIBUTES_UPDATE','<strong>Update</strong> with new settings/prices, then add new ones');
  define('TEXT_COPY_ATTRIBUTES_IGNORE','<strong>Ignore</strong> and add only new attributes');

  define('SUCCESS_ATTRIBUTES_DELETED','Attributes successfully deleted');
  define('SUCCESS_ATTRIBUTES_UPDATE','Attributes successfully updated');

  define('ICON_ATTRIBUTES','Attribute Features');

  define('TEXT_CATEGORIES_IMAGE_DIR','Upload to directory:');

  define('TEXT_PRODUCTS_QTY_BOX_STATUS_PREVIEW','Warning: Does not show Quantity Box, Default to Qty 1');
  define('TEXT_PRODUCTS_QTY_BOX_STATUS_EDIT','Warning: Does not show Quantity Box, Default to Qty 1');

  define('TEXT_PRODUCT_OPTIONS', '<strong>Please Choose:</strong>');
  define('TEXT_PRODUCTS_ATTRIBUTES_INFO','Attribute Features For:');
  define('TEXT_PRODUCT_ATTRIBUTES_DOWNLOADS','Downloads: ');

  define('TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES','Product Priced by Attributes:');
  define('TEXT_PRODUCT_IS_PRICED_BY_ATTRIBUTE','Yes');
  define('TEXT_PRODUCT_NOT_PRICED_BY_ATTRIBUTE','No');
  define('TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_PREVIEW','*Display price will include lowest group attributes prices plus price');
  define('TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_EDIT','*Display price will include lowest group attributes prices plus price');

  define('TEXT_PRODUCTS_QUANTITY_MIN_RETAIL','Product Qty Minimum:');
  define('TEXT_PRODUCTS_QUANTITY_UNITS_RETAIL','Product Qty Units:');
  define('TEXT_PRODUCTS_QUANTITY_MAX_RETAIL','Product Qty Maximum:');

  define('TEXT_PRODUCTS_QUANTITY_MAX_RETAIL_EDIT','0 = Unlimited, 1 = No Qty Boxes');

  define('TEXT_PRODUCTS_MIXED','Product Qty Min/Unit Mix:');

  define('PRODUCTS_PRICE_IS_FREE_TEXT', 'Product is Free');
  define('TEXT_PRODUCT_IS_FREE','Product is Free:');
  define('TEXT_PRODUCTS_IS_FREE_PREVIEW','*Product is marked as FREE');
  define('TEXT_PRODUCTS_IS_FREE_EDIT','*Product is marked as FREE');

  define('TEXT_PRODUCT_IS_CALL','Product is Call for Price:');
  define('TEXT_PRODUCTS_IS_CALL_PREVIEW','*Product is marked as CALL FOR PRICE');
  define('TEXT_PRODUCTS_IS_CALL_EDIT','*Product is marked as CALL FOR PRICE');

  define('TEXT_ATTRIBUTE_COPY_SKIPPING','<strong>Skipping New Attribute </strong>');
  define('TEXT_ATTRIBUTE_COPY_INSERTING','<strong>Inserting New Attribute from </strong>');
  define('TEXT_ATTRIBUTE_COPY_UPDATING','<strong>Updating from Attribute </strong>');

// meta tags
  define('TEXT_META_TAG_TITLE_INCLUDES','<strong>Mark What the Product\'s Meta Tag Title Should Include:</strong>');
  define('TEXT_PRODUCTS_METATAGS_PRODUCTS_NAME_STATUS','<strong>Product Name:</strong>');
  define('TEXT_PRODUCTS_METATAGS_TITLE_STATUS','<strong>Title:</strong>');
  define('TEXT_PRODUCTS_METATAGS_MODEL_STATUS','<strong>Model:</strong>');
  define('TEXT_PRODUCTS_METATAGS_PRICE_STATUS','<strong>Price:</strong>');
  define('TEXT_PRODUCTS_METATAGS_TITLE_TAGLINE_STATUS','<strong>Title/Tagline:</strong>');
  define('TEXT_META_TAGS_TITLE','<strong>Meta Tag Title:</strong>');
  define('TEXT_META_TAGS_KEYWORDS','<strong>Meta Tag Keywords:</strong>');
  define('TEXT_META_TAGS_DESCRIPTION','<strong>Meta Tag Description:</strong>');
  define('TEXT_META_EXCLUDED', '<span class="alert">EXCLUDED</span>');
