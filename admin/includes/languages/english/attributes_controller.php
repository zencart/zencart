<?php
/**
 * @package admin
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: attributes_controller.php 15883 2010-04-11 16:41:26Z wilt $
 */

define('HEADING_TITLE', 'CATEGORIES: ');

define('HEADING_TITLE_OPT', 'Product Options');
define('HEADING_TITLE_VAL', 'Option Values');
define('HEADING_TITLE_ATRIB', 'Attributes Controller');
define('HEADING_TITLE_ATRIB_SELECT','Please select a Category to display the Product Attributes of ...');

define('TEXT_PRICES_AND_WEIGHTS', 'Prices and Weights');
define('TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR', 'Price Factor: ');
define('TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET', 'Offset: ');
define('TABLE_HEADING_ATTRIBUTES_PRICE_ONETIME', 'One Time:');

define('TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_ONETIME', 'One Time Factor: ');
define('TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET_ONETIME', 'Offset: ');

define('TABLE_HEADING_ATTRIBUTES_QTY_PRICES', 'Attributes Qty Price Discount:');
define('TABLE_HEADING_ATTRIBUTES_QTY_PRICES_ONETIME', 'Onetime Attributes Qty Price Discount:');

define('TABLE_HEADING_ATTRIBUTES_PRICE_WORDS', 'Price Per Word:');
define('TABLE_HEADING_ATTRIBUTES_PRICE_WORDS_FREE', '- Free Words:');
define('TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS', 'Price Per Letter:');
define('TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS_FREE', '- Free Letters:');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_PRODUCT', 'Product Name');
define('TABLE_HEADING_OPT_NAME', 'Option Name');
define('TABLE_HEADING_OPT_VALUE', 'Option Value');
define('TABLE_HEADING_OPT_PRICE', 'Price');
define('TABLE_HEADING_OPT_PRICE_PREFIX', 'Prefix');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_DOWNLOAD', 'Downloadable products:');
define('TABLE_TEXT_FILENAME', 'Filename:');
define('TABLE_TEXT_MAX_DAYS', 'Expiry days: (0 = unlimited)');
define('TABLE_TEXT_MAX_COUNT', 'Maximum download count:');
define('TABLE_HEADING_OPT_DISCOUNTED','Discount');
define('TABLE_HEADING_PRICE_BASE_INCLUDED','Base');
define('TABLE_HEADING_PRICE_TOTAL','Total|Disc: Onetime:');
define('TEXT_WARNING_OF_DELETE', 'This option has products and values linked to it - it is not safe to delete it.');
define('TEXT_OK_TO_DELETE', 'This option has no products and values linked to it - it is safe to delete it.');
define('TEXT_OPTION_ID', 'Option ID');
define('TEXT_OPTION_NAME', 'Option Name');

define('ATTRIBUTE_WARNING_DUPLICATE','Duplicate Attribute - Attribute was not added'); // attributes duplicate warning
define('ATTRIBUTE_WARNING_DUPLICATE_UPDATE','Duplicate Attribute Exists - Attribute was not changed'); // attributes duplicate warning
define('ATTRIBUTE_WARNING_INVALID_MATCH','Attribute Option and Option Value Do NOT Match - Attribute was not added'); // miss matched option and options value
define('ATTRIBUTE_WARNING_INVALID_MATCH_UPDATE','Attribute Option and Option Value Do NOT Match - Attribute was not changed'); // miss matched option and options value
define('ATTRIBUTE_POSSIBLE_OPTIONS_NAME_WARNING_DUPLICATE','Possible Duplicate Options Name Added'); // Options Name Duplicate warning
define('ATTRIBUTE_POSSIBLE_OPTIONS_VALUE_WARNING_DUPLICATE','Possible Duplicate Options Value Added'); // Options Value Duplicate warning

define('PRODUCTS_ATTRIBUTES_EDITING','EDITING'); // title
define('PRODUCTS_ATTRIBUTES_DELETE','DELETING'); // title
define('PRODUCTS_ATTRIBUTES_ADDING','ADDING NEW ATTRIBUTES'); // title
define('TEXT_DOWNLOADS_DISABLED','NOTE: Downloads are disabled');

define('TABLE_TEXT_MAX_DAYS_SHORT', 'Days:');
define('TABLE_TEXT_MAX_COUNT_SHORT', 'Max:');

  define('TABLE_HEADING_OPTION_SORT_ORDER','Sort Order');
  define('TABLE_HEADING_OPTION_VALUE_SORT_ORDER','Default Order');
  define('TEXT_SORT',' Order: ');

  define('TABLE_HEADING_OPT_WEIGHT_PREFIX','Prefix');
  define('TABLE_HEADING_OPT_WEIGHT','Weight');
  define('TABLE_HEADING_OPT_SORT_ORDER','Order');
  define('TABLE_HEADING_OPT_DEFAULT','Default');

  define('TABLE_HEADING_OPT_TYPE', 'Option Type'); //CLR 031203 add option type column
  define('TABLE_HEADING_OPTION_VALUE_SIZE','Size');
  define('TABLE_HEADING_OPTION_VALUE_MAX','Max');
  define('TABLE_HEADING_OPTION_VALUE_ROWS','Rows');
  define('TABLE_HEADING_OPTION_VALUE_COMMENTS','Comments');

  define('TEXT_OPTION_VALUE_COMMENTS','Comments: ');
  define('TEXT_OPTION_VALUE_SIZE','Display Size: ');
  define('TEXT_OPTION_VALUE_MAX','Maximum length: ');

  define('TEXT_ATTRIBUTES_IMAGE','Attributes Image Swatch:');
  define('TEXT_ATTRIBUTES_IMAGE_DIR','Attributes Image Directory:');

  define('TEXT_ATTRIBUTES_FLAGS','Attribute<br />Flags:');
  define('TEXT_ATTRIBUTES_DISPLAY_ONLY', 'Used For<br />Display Purposes Only:');
  define('TEXT_ATTRIBUTES_IS_FREE', 'Attribute is Free<br />When Product is Free:');
  define('TEXT_ATTRIBUTES_DEFAULT', 'Default Attribute<br />to be Marked Selected:');
  define('TEXT_ATTRIBUTE_IS_DISCOUNTED', 'Apply Discounts Used<br />by Product Special/Sale:');
  define('TEXT_ATTRIBUTE_PRICE_BASE_INCLUDED','Include in Base Price<br />When Priced by Attributes');
  define('TEXT_ATTRIBUTES_REQUIRED','Attribute Required<br />for Text:');

  define('LEGEND_BOX','Legend:');
  define('LEGEND_KEYS','OFF/ON');
  define('LEGEND_ATTRIBUTES_DISPLAY_ONLY', 'Display Only');
  define('LEGEND_ATTRIBUTES_IS_FREE', 'Free');
  define('LEGEND_ATTRIBUTES_DEFAULT', 'Default');
  define('LEGEND_ATTRIBUTE_IS_DISCOUNTED', 'Discounted');
  define('LEGEND_ATTRIBUTE_PRICE_BASE_INCLUDED','Base Price');
  define('LEGEND_ATTRIBUTES_REQUIRED','Required');
  define('LEGEND_ATTRIBUTES_IMAGES','Images');
  define('LEGEND_ATTRIBUTES_DOWNLOAD','Valid/Invalid<br />filename');

  define('TEXT_ATTRIBUTES_UPDATE_SORT_ORDER','TO DEFAULT ORDER');
  define('TEXT_PRODUCTS_LISTING','Products Listing for: ');
  define('TEXT_NO_PRODUCTS_SELECTED','No Product Selected');
  define('TEXT_NO_ATTRIBUTES_DEFINED','No Attributes Defined for Product ID#');

  define('TEXT_PRODUCTS_ID','Products ID#');
  define('TEXT_ATTRIBUTES_DELETE','DELETE ALL');
  define('TEXT_ATTRIBUTES_COPY_PRODUCTS','Copy to Product');
  define('TEXT_ATTRIBUTES_COPY_CATEGORY','Copy to Category');

  define('TEXT_INFO_HEADING_ATTRIBUTE_FEATURES','Attributes Changes for Products ID# ');
  define('TEXT_INFO_ATTRIBUTES_FEATURES_DELETE','Delete <strong>ALL</strong> Product Attributes for:<br />');
  define('TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO','Copy Attributes to another Product or to an entire Category from:<br />');

  define('TEXT_ATTRIBUTES_COPY_TO_PRODUCTS','PRODUCT');
  define('TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_PRODUCT','Copy Attributes to another <strong>Product</strong> from ID#');
  define('TEXT_INFO_ATTRIBUTES_FEATURE_COPY_TO','Select the Product to copy all attributes to:');

  define('TEXT_ATTRIBUTES_COPY_TO_CATEGORY','CATEGORY');
  define('TEXT_INFO_ATTRIBUTES_FEATURE_CATEGORIES_COPY_TO','Select the Category to copy all attributes to:');
  define('TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_CATEGORY','Copy Attributes to all Products in <strong>Category</strong> from Product ID#');

  define('TEXT_COPY_ATTRIBUTES_CONDITIONS','<strong>How should existing product attributes be handled?</strong>');
  define('TEXT_COPY_ATTRIBUTES_DELETE','<strong>Delete</strong> first, then copy new attributes');
  define('TEXT_COPY_ATTRIBUTES_UPDATE','<strong>Update</strong> with new settings/prices, then add new ones');
  define('TEXT_COPY_ATTRIBUTES_IGNORE','<strong>Ignore</strong> and add only new attributes');

  define('SUCCESS_PRODUCT_UPDATE_SORT','Successful Attribute Sort Order Update for ID# ');
  define('SUCCESS_PRODUCT_UPDATE_SORT_NONE','No Attributes to Update Sort Order for ID# ');
  define('SUCCESS_ATTRIBUTES_DELETED','Attributes successfully deleted');
  define('SUCCESS_ATTRIBUTES_UPDATE','Attributes successfully updated');

  define('WARNING_PRODUCT_COPY_TO_CATEGORY_NONE','No Category selected for copy');
  define('TEXT_PRODUCT_IN_CATEGORY_NAME',' - in Category: ');

  define('TEXT_DELETE_ALL_ATTRIBUTES','Are you sure you want to delete all attributes for ID# ');

  define('TEXT_ATTRIBUTE_COPY_SKIPPING','<strong>Skipping New Attribute </strong>');
  define('TEXT_ATTRIBUTE_COPY_INSERTING','<strong>Inserting New Attribute from </strong>');
  define('TEXT_ATTRIBUTE_COPY_UPDATING','<strong>Updating from Attribute </strong>');

// preview
  define('TEXT_ATTRIBUTES_PREVIEW','PREVIEW ATTRIBUTES');
  define('TEXT_ATTRIBUTES_PREVIEW_DISPLAY','PREVIEW ATTRIBUTES DISPLAY FOR ID#');
  define('TEXT_PRODUCT_OPTIONS', '<strong>Please Choose:</strong>');

  define('TEXT_ATTRIBUTES_INSERT_INFO', '<strong>Define the Attribute Settings then press Insert to apply</strong>');
  define('TEXT_PRICED_BY_ATTRIBUTES', 'Priced by Attributes');
  define('TEXT_PRODUCTS_PRICE', 'Products Price: ');
  define('TEXT_SPECIAL_PRICE', 'Special Price: ');
  define('TEXT_SALE_PRICE', 'Sale Price: ');
  define('TEXT_FREE', 'FREE');
  define('TEXT_CALL_FOR_PRICE', 'Call for Price');
  define('TEXT_SAVE_CHANGES','UPDATE AND SAVE CHANGES:');

  define('TEXT_INFO_ID', 'ID#');
  define('TEXT_INFO_ALLOW_ADD_TO_CART_NO', 'No adding to cart');

  define('TEXT_DELETE_ATTRIBUTES_OPTION_NAME_VALUES', 'Confirm deletion of ALL of the Product Option Values for Option Name ...');
  define('TEXT_INFO_PRODUCT_NAME', '<strong>Product Name: </strong>');
  define('TEXT_INFO_PRODUCTS_OPTION_NAME', '<strong>Option Name: </strong>');
  define('TEXT_INFO_PRODUCTS_OPTION_ID', '<strong>ID#</strong>');
  define('SUCCESS_ATTRIBUTES_DELETED_OPTION_NAME_VALUES', 'Successful deletion of all Option Values for Option Name: ');
  