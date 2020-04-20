<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2020 April 14 Modified in v1.5.7 $
 */
define('HEADING_TITLE', 'Option Name Manager');
define('TEXT_ATTRIBUTES_CONTROLLER', 'Attributes Controller');

define('TEXT_WARNING_TEXT_OPTION_NAME_RESTORED', 'Warning: The Option Value TEXT ID#0 was found to be missing from the database table "' . TABLE_PRODUCTS_OPTIONS_VALUES . '". This may have been due to an incorrectly coded plugin.<br>The value has been restored correctly.');
define('TABLE_HEADING_PRODUCT', 'Product Name');
define('TABLE_HEADING_OPTION_NAME', 'Option Name');
define('TABLE_HEADING_OPTION_VALUE', 'Option Value');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_PRODUCT_OPTIONS_INFO','<strong>Note: Edit the Option Name for additional settings</strong>');

define('TEXT_WARNING_OF_DELETE', 'This Option Name is used by the product(s) listed below: it cannot be deleted until all the Option Values (attributes) associated with this Option Name have been removed from these products (this may be done using the Global Tools below)');
define('TEXT_OK_TO_DELETE', 'This Option Name is not used by any product - it is safe to delete it.<br><strong>Warning:</strong> this will delete both the Option Name AND all the Option Values associated with that Option Name.');

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
  define('TEXT_OPTION_VALUE_ROWS', 'Rows: ');
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

  define('TEXT_PRODUCT_OPTIONS_INFO','<strong>NOTE: Edit Product Options Name for additional settings</strong>');

// updates
define('ERROR_PRODUCTS_OPTIONS_VALUES', 'WARNING: No Products found ... Nothing was updated');

define('TEXT_SELECT_PRODUCT', ' Select a Product');
define('TEXT_SELECT_CATEGORY', ' Select a Category');
define('TEXT_SELECT_OPTION', 'Select an Option Name');

// add
define('TEXT_OPTION_VALUE_ADD_ALL', '<br /><strong>Add ALL Option Values to ALL products for Option Name</strong>');
define('TEXT_INFO_OPTION_VALUE_ADD_ALL', 'Update ALL existing products that have at least ONE Option Value and Add ALL Option Values in an Option Name');
define('SUCCESS_PRODUCTS_OPTIONS_VALUES', 'Successful Update of Options ');

define('TEXT_OPTION_VALUE_ADD_PRODUCT', '<br /><strong>Add ALL Option Values to ONE products for Option Name</strong>');
define('TEXT_INFO_OPTION_VALUE_ADD_PRODUCT', 'Update ONE product that has at least ONE Option Value and Add ALL Option Values in an Option Name');

define('TEXT_OPTION_VALUE_ADD_CATEGORY', '<br /><strong>Add ALL Option Values to ONE Category of products for Option Name</strong>');
define('TEXT_INFO_OPTION_VALUE_ADD_CATEGORY', 'Update ONE Category of products, when the product has at least ONE Option Value and Add ALL Option Values in an Option Name');

define('TEXT_COMMENT_OPTION_VALUE_ADD_ALL', '<strong>NOTE:</strong> Sort order will be set to the default Option Value Sort Order for these products');

// delete
define('TEXT_OPTION_VALUE_DELETE_ALL', '<br /><strong>Delete ALL Option Values to ALL products for Option Name</strong>');
define('TEXT_INFO_OPTION_VALUE_DELETE_ALL', 'Update ALL existing products that have at least ONE Option Value and Delete ALL Option Values in an Option Name');

define('TEXT_OPTION_VALUE_DELETE_PRODUCT', '<br /><strong>Delete ALL Option Values to ONE products for Option Name</strong>');
define('TEXT_INFO_OPTION_VALUE_DELETE_PRODUCT', 'Update ONE product that has at least ONE Option Value and Delete ALL Option Values in an Option Name');

define('TEXT_OPTION_VALUE_DELETE_CATEGORY', '<br /><strong>Delete ALL Option Values to ONE Category of products for Option Name</strong>');
define('TEXT_INFO_OPTION_VALUE_DELETE_CATEGORY', 'Update ONE Category of products, when the product has at least ONE Option Value and Delete ALL Option Values in an Option Name');

define('TEXT_COMMENT_OPTION_VALUE_DELETE_ALL', '<strong>NOTE:</strong> All Option Name Option Values will be deleted for selected product(s). This will not delete the Option Value settings.');

define('TEXT_OPTION_VALUE_COPY_ALL', '<strong>Copy ALL Option Values to another Option Name</strong>');
define('TEXT_INFO_OPTION_VALUE_COPY_ALL', 'All Option Values will be copied from one Option Name to another Option Name');
define('TEXT_SELECT_OPTION_FROM', 'Copy from Option Name: ');
define('TEXT_SELECT_OPTION_TO', 'Copy All Option Values to Option Name: ');
define('SUCCESS_OPTION_VALUES_COPIED', 'Successful copy! ');
define('ERROR_OPTION_VALUES_COPIED', 'Error - Cannot copy Option Values to the same Option Name! ');
define('ERROR_OPTION_VALUES_NONE', 'Error - Copy from Option Name has 0 Values Defined. Nothing was copied! ');
define('TEXT_WARNING_BACKUP', 'Warning: Always make proper backups of your database before making global changes');

define('TEXT_OPTION_ATTRIBUTE_IMAGES_PER_ROW', 'Attribute Images per Row: ');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE', 'Attribute Style for Radio Buttons/Checkbox: ');
define('TEXT_OPTION_ATTIBUTE_MAX_LENGTH', '<strong>NOTE: Rows, Display Size and Max Length are for Text Attributes Only:</strong><br />');
define('TEXT_OPTION_IMAGE_STYLE', '<strong>Image Styles:</strong>');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_0', '0= Images Below Option Names');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_1', '1= Element, Image and Option Value');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_2', '2= Element, Image and Option Name Below');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_3', '3= Option Name Below Element and Image');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_4', '4= Element Below Image and Option Name');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_5', '5= Element Above Image and Option Name');
