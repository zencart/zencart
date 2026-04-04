<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 18 Modified in v2.1.0-alpha2 $
*/

$define = [
    'HEADING_TITLE' => 'Editing Sideboxes for template: ',
    'TEXT_CURRENTLY_VIEWING' => 'Currently Viewing: ',
    'TEXT_THIS_IS_PRIMARY_TEMPLATE' => ' (Main)',
    'TABLE_HEADING_BOXES_PATH' => 'Boxes Path: ',
    'TEXT_WARNING_NEW_BOXES_FOUND' => 'WARNING: New boxes found: ',
    'TEXT_ORIGINAL_DEFAULTS' => '[Original/Master Zen Cart Defaults]',
    'TEXT_CAUTION_EDITING_NOT_LIVE_TEMPLATE' => 'CAUTION: You are editing settings for a template that is not the main template used by customers.',

    'TEXT_HEADING_MISSING_BOXES' => 'Missing Boxes',
    'BUTTON_REMOVE_SELECTED' => 'Remove Selected',
    'TEXT_NO_BOXES_TO_REMOVE' => 'No missing sideboxes were selected for removal.',
    'BUTTON_REMOVE_BOXES' => 'Remove Boxes',
    'BUTTON_CLOSE' => 'Close',

    'TEXT_INFO_HEADING_DELETE_BOX' => 'Remove Missing Sideboxes',
    'TEXT_INFO_DELETE_MISSING_LAYOUT_BOX_NOTE' => 'NOTE: This does not remove files and you can re-add the boxes at anytime by adding them to the correct directory.<br><br><strong>Boxes to remove: </strong> ',
    'SUCCESS_BOX_DELETED' => 'These boxes were removed: ',

    'TEXT_RESET_SETTINGS' => 'Reset Settings',
    'TEXT_INFO_RESET_TEMPLATE_SORT_ORDER' => 'Reset box status/sort settings: ',
    'TEXT_INFO_RESET_TEMPLATE_SORT_ORDER_NOTE' => 'This does not remove any boxes. It will only reset the status/sort-order of boxes matching boxes in the other template.',
    'TEXT_SETTINGS_COPY_FROM' => 'Copy status/sort settings FROM: ',
    'TEXT_SETTINGS_COPY_TO' => ' TO: ',
    'SUCCESS_BOX_RESET' => 'Settings for [%1$s] have been reset to current settings from [%2$s].',
    'TEXT_ERROR_INVALID_RESET_SUBMISSION' => 'ERROR: Invalid reset choice',

    'TEXT_INSTRUCTIONS' => 'If your device has a mouse, you can drag and drop a box to change its column-location or sort-order within its active column locations. Otherwise, use the up- and down-arrow icons to change a box\'s location or sort-order. Use an <i class="fa-solid fa-xmark"></i> icon to quickly move an active box to its inactive group.',
    'BUTTON_SHOW_NOTES' => 'Show Notes',
    'BUTTON_HIDE_NOTES' => 'Hide Notes',
    'TEXT_NOTES' => 'Notes:',
    'TEXT_NOTE1_OPT' => 'The display of these (%1$s) boxes on the storefront are <em>very dependent</em> on the %2$s template. Check with the template author for details!',
    'TEXT_NOTE1' => 'Once you have moved a box, a button is displayed which, when clicked, saves all the changes you have made.',
    'TEXT_NOTE2' => 'Box sort-orders are calculated when you save your choices, no need to provide them.',
    'TEXT_NOTE3' => 'All inactive boxes are saved with the same sort-order, so that they display in alphabetic order.',
    'TEXT_NOTE4' => 'Moving a box within an its inactive group is not considered a change!',
    'TEXT_NOTE5' => 'If you have made changes and navigate away from this tool, your browser will let you know that you have unsaved changes.',

    'TEXT_COLUMN_DISABLED' => 'Column Globally Disabled',
    'TEXT_DISABLED_MESSAGE' => 'Changes to this column will be saved, but will not be displayed on the storefront.  See the associated setting in Configuration :: Layout Settings.',
    'TEXT_HEADING_MAIN_PAGE_BOXES' => 'Main-Page Boxes',
    'TEXT_HEADING_ACTIVE_LEFT' => 'Active Left-Column Boxes',
    'TEXT_HEADING_ACTIVE_RIGHT' => 'Active Right-Column Boxes',
    'TEXT_HEADING_INACTIVE_LEFT_RIGHT' => 'Inactive Main-Page Boxes',
    'TEXT_HEADING_HEADER_BOXES' => 'Header Boxes',
    'TEXT_HEADING_FOOTER_BOXES' => 'Footer Boxes',
    'TEXT_HEADING_MOBILE_BOXES' => 'Mobile-Menu Boxes',
    'TEXT_HEADING_ACTIVE_BOXES' => 'Active Boxes',
    'TEXT_HEADING_INACTIVE_BOXES' => 'Inactive Boxes',
    'BUTTON_SHOW' => 'Show',
    'BUTTON_HIDE' => 'Hide',

    'TEXT_MOVE_BOX_UP' => 'Move %1$s up in the %2$s boxes.',
    'TEXT_MOVE_BOX_DOWN' => 'Move %1$s down in the %2$s boxes.',
    'TEXT_MOVE_BOX_UNUSED' => 'Move %1$s to the inactive %2$s boxes.',
        'TEXT_MOVE_MAIN_PAGE_COLUMN' => 'main-page',  //- Used as %2$s value in above three phrases
        'TEXT_MOVE_HEADER_COLUMN' => 'header',        //- Used as %2$s value in the above three phrases
        'TEXT_MOVE_FOOTER_COLUMN' => 'footer',        //- Used as %2$s value in the above three phrases
        'TEXT_MOVE_MOBILE_COLUMN' => 'mobile-menu',   //- Used as %2$s value in the above three phrases
    'BUTTON_SAVE_CHANGES' => 'Save Changes',
    'SUCCESS_BOX_UPDATED' => 'Settings have been updated.',
];

return $define;
