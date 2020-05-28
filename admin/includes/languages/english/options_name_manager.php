<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Apr 30 Modified in v1.5.7 $
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

define('TEXT_WARNING_DUPLICATE_OPTION_NAME','Option ID#%1$u: Duplicate Option Name Added: "%2$s" (%3$s)');

define('TEXT_ORDER_BY','Order by');
define('TEXT_SORT_ORDER','Sort Order');

define('TABLE_HEADING_OPTION_TYPE', 'Option Type');
define('TABLE_HEADING_OPTION_NAME_SIZE','Size');
define('TABLE_HEADING_OPTION_NAME_MAX','Max');

define('TEXT_OPTION_NAME_COMMENTS','Comment (displayed next to Option Name)');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_PER_ROW', 'Attribute Images per Row');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE', 'Attribute Image Layout Style (for Checkbox/Radio Buttons only)');
define('TEXT_OPTION_ATTRIBUTE_LAYOUTS_EXAMPLE', 'View Examples');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_0', '0 - Selection + text, Images below Options');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_1', '1 - Select + Image + Option inline');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_2', '2 - Select + Option + Image wrapped');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_3', '3 - Select + Image + Option wrapped');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_4', '4 - Image + Option + Select as column');
define('TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_5', '5 - Select + Image + Option as column');
//text attributes only
define('TEXT_OPTION_NAME_ROWS', 'Rows');
define('TEXT_OPTION_NAME_SIZE','Display Size');
define('TEXT_OPTION_NAME_MAX','Maximum Length');
define('TEXT_OPTION_TYPE_TEXT_ATTRIBUTE_INFO', 'Note: ' . TEXT_OPTION_NAME_ROWS . ', ' . TEXT_OPTION_NAME_SIZE . ' and ' . TEXT_OPTION_NAME_MAX . ' are for Option Name Type "Text" only.');
define('TEXT_INSERT_NEW_OPTION_NAME', 'Add a new Option Name');

// Global Tools
define('TEXT_GLOBAL_TOOLS', 'Global Tools');
define('TEXT_CLICK_TO_SHOW_HIDE', 'click to show/hide');
define('TEXT_WARNING_BACKUP', 'Important: Always make a verified backup of your database before making global changes/using Global Tools');
define('TEXT_SELECT_OPTION_TYPES_ALLOWED', 'Note that Global Tools cannot be used with option name types "Text" or "File".');
define('TEXT_SELECT_PRODUCT', 'Select a Product');
define('TEXT_SELECT_CATEGORY', 'Select a Category');
define('TEXT_SELECT_OPTION', 'Select an Option Name');
define('TEXT_NAME', 'Name');

// Add
define('TEXT_INFO_OPTION_VALUES_ADD', '<strong>Note:</strong> for products that get updated (receive additional Option Values) using the <b>Add</b> tools, the sort order for the Option Values (attributes) will be reset to the <strong>default</strong> sort order for that Option name.');

define('TEXT_OPTION_VALUE_ADD_ALL', 'Update (add) all remaining Option Values to ALL products that use this Option Name');
define('TEXT_INFO_OPTION_VALUE_ADD_ALL', 'For ALL products that are using the selected Option Name (and so have at least one Option Value assigned), add ALL the other Option Values associated with the Option Name.');

define('TEXT_OPTION_VALUE_ADD_PRODUCT', 'Update (add) all remaining Option Values to ONE product that is using this Option Name');
define('TEXT_INFO_OPTION_VALUE_ADD_PRODUCT', 'For a product that is using the selected Option Name (and so has at least one Option Value assigned), add ALL the other Option Values associated with the Option Name.');

define('TEXT_OPTION_VALUE_ADD_CATEGORY', 'Update (add) all remaining Option Values to ALL products in a Category that are using this Option Name');
define('TEXT_INFO_OPTION_VALUE_ADD_CATEGORY', 'For products in ONE category only that are using the selected Option Name, add ALL the other Option Values associated with the Option Name.');
define('TEXT_SHOW_CATEGORY_PATH', 'Show category path');
define('TEXT_SHOW_CATEGORY_NAME', 'Show only category name');

// messageStack
define('SUCCESS_PRODUCT_OPTION_VALUE', 'Option Name "%1$s": Option Value "%2$s" added to product "%3$s".');
define('SUCCESS_PRODUCT_OPTIONS_VALUES_SORT_ORDER', 'Option Name "%1$s": product "%2$s" Option Values updated to the default sort order for Option Name "%1$s".');
define('SUCCESS_PRODUCTS_OPTIONS_VALUES', 'Option Name "%1$s": %2$u product(s) updated with additional Option Values.');

define('ERROR_PRODUCTS_OPTIONS_PRODUCTS', 'Warning: No matching product(s) found using Option Name "%s" (nothing was updated).');
define('ERROR_PRODUCTS_OPTIONS_VALUES', 'Warning: All matching product(s) already have all Option Values for Option Name "%s" (nothing was updated).');

// Delete
define('TEXT_COMMENT_OPTION_VALUE_DELETE_ALL', '<strong>NOTE:</strong> All Option Values will be deleted from matching/the selected product(s). This will not delete the Option Values defined for that Option Name.');
define('TEXT_OPTION_VALUE_DELETE_ALL', 'Delete all Option Values from ALL products using this Option Name');
define('TEXT_INFO_OPTION_VALUE_DELETE_ALL', 'For ALL products that are using the selected Option Name, remove all the Option Values/the Option Name.');

define('TEXT_OPTION_VALUE_DELETE_PRODUCT', 'Delete all Option Values from ONE product using this Option Name');
define('TEXT_INFO_OPTION_VALUE_DELETE_PRODUCT', 'For a product that is using the selected Option Name, remove ALL the Option Values/the Option Name.');

define('TEXT_OPTION_VALUE_DELETE_CATEGORY', 'Delete all Option Values from ONE Category of products for this Option Name');
define('TEXT_INFO_OPTION_VALUE_DELETE_CATEGORY', 'For products in ONE category only that are using the selected Option Name, remove all the Option Values/the Option Name.');

// messageStack
define('SUCCESS_PRODUCT_OPTION_VALUES_DELETED', 'Option Name "%1$s": all Option Values deleted from product "%2$s".');
define('SUCCESS_PRODUCTS_OPTIONS_VALUES_DELETED', 'Option Name "%1$s": all Option Values removed from %2$u product(s).');

// Copy
define('TEXT_OPTION_VALUE_COPY_ALL', 'Copy all Option Values to another Option Name');
define('TEXT_INFO_OPTION_VALUE_COPY_ALL', 'All Option Values from the selected Option Name will be copied (added) to another Option Name.');
define('TEXT_SELECT_OPTION_FROM', 'Copy from Option Name: ');
define('TEXT_SELECT_OPTION_TO', 'Copy to Option Name: ');

define('SUCCESS_OPTION_VALUE_COPIED', 'Option Value "%6$s" ID#%5$u copied from Option Name "%2$s" ID#%1$u to Option Name "%4$s" ID#%3$u.');
define('SUCCESS_OPTION_VALUES_COPIED', '%5$u Option Value(s) copied from Option Name "%2$s" ID#%1$u to Option Name "%4$s" ID#%3$u.');
define('ERROR_OPTION_VALUES_COPIED', 'Error: Cannot copy Option Values to the same Option Name ("%2$s" ID#%1$u to "%4$s" ID#%3$u)!');
define('ERROR_OPTION_VALUES_NONE', 'Error: Option Name "%2$s" ID#%1$u has no Option Values defined (nothing to copy)!');
