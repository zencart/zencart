<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Jan 11 New in v1.5.8-alpha $
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

    'TEXT_INSTRUCTIONS' => 'If your device has a mouse, you can drag and drop a sidebox to change its column-location or sort-order within its associated column locations. Otherwise, use the up- and down-arrow icons to change a sidebox\'s location or sort-order. Use an <i class="fa-solid fa-xmark"></i> icon to quickly move an active sidebox to its inactive group.',
    'BUTTON_SHOW_HIDE_NOTES' => 'Show/Hide Notes',
    'TEXT_NOTES' => 'Notes:',
    'TEXT_NOTE1' => 'Once you have moved a sidebox, a button is displayed which, when clicked, saves all the changes you have made.',
    'TEXT_NOTE2' => 'Sidebox sort-orders are calculated when you save your choices, no need to provide them.',
    'TEXT_NOTE3' => 'All inactive sideboxes are saved with the same sort-order, so that they display in alphabetic order.',
    'TEXT_NOTE4' => 'Moving a sidebox within an inactive sidebox group is not considered a change!',
    'TEXT_NOTE5' => 'If you have made changes and navigate away from this tool, your browser will let you know that you have unsaved changes.',

    'TEXT_COLUMN_DISABLED' => 'Column Globally Disabled',
    'TEXT_DISABLED_MESSAGE' => 'Changes to this column will be saved, but will not be displayed on the storefront.  See the associated setting in Configuration :: Layout Settings.',
    'TEXT_HEADING_LEFT_RIGHT_COLUMNS' => 'Left/Right Column Locations',
    'TEXT_HEADING_ACTIVE_LEFT' => 'Active Left Sideboxes',
    'TEXT_HEADING_ACTIVE_RIGHT' => 'Active Right Sideboxes',
    'TEXT_HEADING_INACTIVE_LEFT_RIGHT' => 'Inactive Left/Right Sideboxes',
    'TEXT_HEADING_SINGLE_COLUMN' => 'Single-Column Locations',
    'TEXT_HEADING_ACTIVE_SINGLE' => 'Active Single-Column Sideboxes',
    'TEXT_HEADING_INACTIVE_SINGLE' => 'Inactive Single-Column Sideboxes',

    'TEXT_MOVE_BOX_UP' => 'Move %1$s up in the %2$s column locations.',
    'TEXT_MOVE_BOX_DOWN' => 'Move %1$s down in the %2$s column locations.',
    'TEXT_MOVE_BOX_UNUSED' => 'Move %1$s to the %2$s column unused location.',
        'TEXT_MOVE_LEFT_RIGHT_COLUMN' => 'left/right',  //- Used as %2$s value in above three phrases
        'TEXT_MOVE_SINGLE_COLUMN' => 'single',          //- Used as %2$s value in the above three phrases
    'BUTTON_SAVE_CHANGES' => 'Save Changes',
    'SUCCESS_BOX_UPDATED' => 'Settings have been updated.',
];

return $define;
