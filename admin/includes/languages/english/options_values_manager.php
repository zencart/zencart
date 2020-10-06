<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 07 Modified in v1.5.7 $
 */

define('HEADING_TITLE_OPT', 'Product Options');
define('HEADING_TITLE_VAL', 'Option Values');
define('HEADING_TITLE_ATRIB', 'Products Attributes');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_PRODUCT', 'Product Name');
define('TABLE_HEADING_OPT_NAME', 'Option Name');
define('TABLE_HEADING_OPT_VALUE', 'Option Value');
define('TABLE_HEADING_OPT_PRICE', 'Price');
define('TABLE_HEADING_OPT_PRICE_PREFIX', 'Prefix');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_DOWNLOAD', 'Downloadable products:');
define('TABLE_TEXT_FILENAME', 'Filename:');
define('TABLE_TEXT_MAX_DAYS', 'Expiry days:');
define('TABLE_TEXT_MAX_COUNT', 'Maximum download count:');

define('TEXT_WARNING_OF_DELETE', '<span class="alert">This option has products and values linked to it - it is not safe to delete it.<br />NOTE: Any associated Download files for this Option Value will not be removed from the server.</span>');
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
  define('TABLE_HEADING_OPT_SORT_ORDER','Sort Order');

  define('TABLE_HEADING_OPT_TYPE', 'Option Type'); //CLR 031203 add option type column
  define('TABLE_HEADING_OPTION_VALUE_SIZE','Size');
  define('TABLE_HEADING_OPTION_VALUE_MAX','Max');

  define('TEXT_OPTION_VALUE_COMMENTS','Comments: ');
  define('TEXT_OPTION_VALUE_SIZE','Display Size: ');
  define('TEXT_OPTION_VALUE_MAX','Maximum length: ');

  define('TEXT_ATTRIBUTES_IMAGE','Attributes Image Swatch:');
  define('TEXT_ATTRIBUTES_IMAGE_DIR','Attributes Image Directory:');

  define('TEXT_ATTRIBUTES_FLAGS','Attribute<br />Flags:');
  define('TEXT_ATTRIBUTES_DISPLAY_ONLY', 'Used For<br />Display Purposes Only:');
  define('TEXT_ATTRIBUTES_IS_FREE', 'Attribute is Free<br />When Product is Free:');
  define('TEXT_ATTRIBUTES_DEFAULT', 'Default Attribute<br />to be Marked Selected:');
  define('TEXT_ATTRIBUTE_IS_DISCOUNTED', 'Apply Same Discounts<br />Used by Product:');
  define('TEXT_ATTRIBUTE_PRICE_BASE_INCLUDED','Include in Base Price<br />When Priced by Attributes');

  define('TEXT_PRODUCT_OPTIONS_INFO','Edit Product Options for additional settings');

// Option Names/Values copier from one to another
  define('TEXT_OPTION_VALUE_COPY_ALL', '<strong>Copy to ALL Products where Option Name and Value ...</strong>');
  define('TEXT_INFO_OPTION_VALUE_COPY_ALL', 'Select an Option Name and Value that currently exists on a product or products that you then want to copy another Option Name and Value to for all products with this existing Option Name and Value');
  define('TEXT_SELECT_OPTION_FROM', 'Option Name to match:');
  define('TEXT_SELECT_OPTION_VALUES_FROM', 'Option Value to match:');
  define('TEXT_SELECT_OPTION_TO', 'Option Name to add:');
  define('TEXT_SELECT_OPTION_VALUES_TO', 'Option Value to add:');
  define('TEXT_SELECT_OPTION_VALUES_TO_CATEGORIES_ID', 'Leave blank for ALL Products or<br />enter a Category ID for Products to update');

// Option Name/Value to Option Name for Category with Product defaults
  define('TEXT_OPTION_VALUE_COPY_OPTIONS_TO', '<strong>Copy Option Name/Value to Products with existing Option Name ...</strong>');
  define('TEXT_INFO_OPTION_VALUE_COPY_OPTIONS_TO', 'Select an Option Name and Value that currently exists on a product or products to add to all products or to only the products in the selected category that have the selected Option Name.
                                                   <br /><strong>Example:</strong> Add Option Name: Color Option Value: Red to all Products with Option Name: Size
                                                   <br /><strong>Example:</strong> Add Option Name: Color Option Value: Green with default values from Products ID: 34 to all Products with Option Name: Size
                                                   <br /><strong>Example:</strong> Add Option Name: Color Option Value: Green with default values from Products ID: 34 to all Products with Option Name: Size for Categories ID: 65
        ');
  define('TEXT_SELECT_OPTION_TO_ADD_TO', 'Option Name to add to:');
  define('TEXT_SELECT_OPTION_FROM_ADD', 'Option Name to add:');
  define('TEXT_SELECT_OPTION_VALUES_FROM_ADD', 'Option Value to add:');
  define('TEXT_SELECT_OPTION_FROM_PRODUCTS_ID', 'Default New Attribute Values from Product ID# or leave blank for no default values:');
  define('TEXT_COPY_ATTRIBUTES_CONDITIONS','<strong>How should existing product attributes be handled?</strong>');
  define('TEXT_COPY_ATTRIBUTES_DELETE','<strong>Delete</strong> first, then copy new attributes');
  define('TEXT_COPY_ATTRIBUTES_UPDATE','<strong>Update</strong> existing attributes with new settings/prices');
  define('TEXT_COPY_ATTRIBUTES_IGNORE','<strong>Ignore</strong> existing attributes and add only new attributes');

  define('TEXT_INFO_FROM', ' from: ');
  define('TEXT_INFO_TO', ' to: ');
  define('ERROR_OPTION_VALUES_COPIED', 'Error: Duplicate Option Name and Option Value');
  define('ERROR_OPTION_VALUES_COPIED_MISMATCH', 'Error: Mismatched Option Name and Option Value selected');
  define('ERROR_OPTION_VALUES_NONE', 'Error: Nothing found to copy');
  define('SUCCESS_OPTION_VALUES_COPIED', 'Successful copy! ');
  define('ERROR_OPTION_VALUES_COPIED_MISMATCH_PRODUCTS_ID', 'Error: Missing Option Name/Value for Products ID#');

  define('TEXT_OPTION_VALUE_DELETE_ALL', '<strong>Delete Matching Attribute from ALL Products where Option Name and Value ...</strong>');
  define('TEXT_INFO_OPTION_VALUE_DELETE_ALL', 'Select an Option Name and Value that currently exists on a product or products that you want deleted from ALL Products or from ALL Products within one Category');
  define('TEXT_SELECT_DELETE_OPTION_FROM', 'Option Name to match:');
  define('TEXT_SELECT_DELETE_OPTION_VALUES_FROM', 'Option Value to match:');

  define('ERROR_OPTION_VALUES_DELETE_MISMATCH', 'Error: Mismatched Option Name and Option Value selected');

  define('SUCCESS_OPTION_VALUES_DELETE', 'Successful: Deletion of: ');
  define('LABEL_FILTER', 'Select Option Value to filter');
  define('TEXT_DISPLAY_NUMBER_OF_OPTION_VALUES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> Option Values)');
  define('TEXT_SHOW_ALL', 'Show All');
