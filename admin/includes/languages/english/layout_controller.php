<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: layout_controller.php 2014-07-28 drbyte $
 */

define('HEADING_TITLE', 'Column Boxes for template: ');

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
define('TEXT_INFO_LAYOUT_BOX_SORT_ORDER', 'Left/Right Column Sort Order:');
define('TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE', 'Single Column Sort Order:');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new box with its related data');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this box?');
define('TEXT_INFO_HEADING_NEW_BOX', 'New Box');
define('TEXT_INFO_HEADING_EDIT_BOX', 'Edit Box');
define('TEXT_INFO_HEADING_DELETE_BOX', 'Delete Box');
define('TEXT_INFO_DELETE_MISSING_LAYOUT_BOX','Delete missing box from Template listing: ');
define('TEXT_INFO_DELETE_MISSING_LAYOUT_BOX_NOTE','NOTE: This does not remove files and you can re-add the box at any time by adding it to the correct directory.<br /><br /><strong>Delete box name: </strong>');
define('TEXT_INFO_RESET_TEMPLATE_SORT_ORDER','Resets all boxes to the last-saved default settings. (Typically necessary when installing new templates.)');
define('TEXT_INFO_RESET_TEMPLATE_SORT_ORDER_NOTE','(This does not remove any of the boxes. It will only reset the current sort order.)');
define('TEXT_INFO_BOX_DETAILS','Box Details: ');
define('TEXT_INFO_SET_AS_DEFAULT','Save the above settings as the default.');
define('TEXT_INFO_THE_ABOVE_SETTINGS_ARE_FOR', ' (eg: this will copy the settings for the [<strong>%s</strong>] template to be the new defaults)');

define('TABLE_HEADING_ACTION', 'Action');

define('TABLE_HEADING_BOXES_PATH', 'Boxes Path: ');
define('TEXT_WARNING_NEW_BOXES_FOUND', 'WARNING: New boxes found: ');
define('TEXT_GOOD_BOX',' ');
define('TEXT_BAD_BOX','<span class="alert">MISSING</span><br />');

// Success messages
define('SUCCESS_BOX_DELETED','Update: Removed sidebox from template ');
define('SUCCESS_BOX_RESET','Update: All sideboxes reset for template ');
define('SUCCESS_BOX_UPDATED','Update: updated settings for sidebox ');
define('SUCCESS_BOX_SET_DEFAULTS','Update: Updated Defaults using settings from template ');

define('TEXT_ON',' ON ');
define('TEXT_OFF',' OFF ');
define('TEXT_LEFT',' LEFT ');
define('TEXT_RIGHT',' RIGHT ');
