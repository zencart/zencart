<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                                 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: layout_controller.php 3197 2006-03-17 21:40:58Z drbyte $
//

define('HEADING_TITLE', 'Column Boxes');

define('TABLE_HEADING_LAYOUT_BOX_NAME', 'Box File Name');
define('TABLE_HEADING_LAYOUT_BOX_STATUS', 'LEFT/RIGHT COLUMN<br />Status');
define('TABLE_HEADING_LAYOUT_BOX_STATUS_SINGLE', 'SINGLE COLUMN<br />Status');
define('TABLE_HEADING_LAYOUT_BOX_LOCATION', 'LEFT or RIGHT<br />COLUMN');
define('TABLE_HEADING_LAYOUT_BOX_SORT_ORDER', 'LEFT/RIGHT COLUMN<br />Sort Order');
define('TABLE_HEADING_LAYOUT_BOX_SORT_ORDER_SINGLE', 'SINGLE COLUMN<br />Sort Order');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_INFO_LAYOUT_BOX','Selected Box: ');
define('TEXT_INFO_LAYOUT_BOX_NAME', 'Box Name:');
define('TEXT_INFO_LAYOUT_BOX_LOCATION','Location: (Single Column ignores this setting)');
define('TEXT_INFO_LAYOUT_BOX_STATUS', 'Left/Right Column Status: ');
define('TEXT_INFO_LAYOUT_BOX_STATUS_SINGLE', 'Single Column Status: ');
define('TEXT_INFO_LAYOUT_BOX_STATUS_INFO','ON= 1 OFF=0');
define('TEXT_INFO_LAYOUT_BOX_SORT_ORDER', 'Left/Right Column Sort Order:');
define('TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE', 'Single Column Sort Order:');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new box with its related data');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this box?');
define('TEXT_INFO_HEADING_NEW_BOX', 'New Box');
define('TEXT_INFO_HEADING_EDIT_BOX', 'Edit Box');
define('TEXT_INFO_HEADING_DELETE_BOX', 'Delete Box');
define('TEXT_INFO_DELETE_MISSING_LAYOUT_BOX','Delete missing box from Template listing: ');
define('TEXT_INFO_DELETE_MISSING_LAYOUT_BOX_NOTE','NOTE: This does not remove files and you can re-add the box at anytime by adding it to the correct directory.<br /><br /><strong>Delete box name: </strong>');
define('TEXT_INFO_RESET_TEMPLATE_SORT_ORDER','Reset All Box Sort Order to match DEFAULT Sort Order for Template: ');
define('TEXT_INFO_RESET_TEMPLATE_SORT_ORDER_NOTE','This does not remove any of the boxes. It will only reset the current sort order');
define('TEXT_INFO_BOX_DETAILS','Box Details: ');

////////////////

define('HEADING_TITLE_LAYOUT_TEMPLATE', 'Site Template Layout');

define('TABLE_HEADING_LAYOUT_TITLE', 'Title');
define('TABLE_HEADING_LAYOUT_VALUE', 'Value');

define('TABLE_HEADING_BOXES_PATH', 'Boxes Path: ');
define('TEXT_WARNING_NEW_BOXES_FOUND', 'WARNING: New boxes found: ');

define('TEXT_MODULE_DIRECTORY', 'Site Layout Directory:');
define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');

// layout box text in includes/boxes/layout.php
define('BOX_HEADING_LAYOUT', 'Layout');
define('BOX_LAYOUT_COLUMNS', 'Column Controller');

// file exists
define('TEXT_GOOD_BOX',' ');
define('TEXT_BAD_BOX','<font color="ff0000"><b>MISSING</b></font><br />');


// Success message
define('SUCCESS_BOX_DELETED','Successfully removed from the template of the box: ');
define('SUCCESS_BOX_RESET','Successfully Reset all box settings to the Default settings for Template: ');
define('SUCCESS_BOX_UPDATED','Successfully Updated settings for box: ');

define('TEXT_ON',' ON ');
define('TEXT_OFF',' OFF ');
define('TEXT_LEFT',' LEFT ');
define('TEXT_RIGHT',' RIGHT ');

?>