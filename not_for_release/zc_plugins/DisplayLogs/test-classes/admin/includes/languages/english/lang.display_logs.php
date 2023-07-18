<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2023 Jan 16 New in v1.5.8a $
 */


$define = [
    'HEADING_TITLE' => 'Display Debug Log Files',
    'TABLE_HEADING_FILENAME' => 'Filename',
    'TABLE_HEADING_MODIFIED' => 'Date',
    'TABLE_HEADING_FILESIZE' => 'Size (b)',
    'TABLE_HEADING_DELETE' => 'Selected',
    'TABLE_HEADING_ACTION' => 'Action',
    'BUTTON_INVERT_SELECTED' => 'Invert Selection',
    'BUTTON_DELETE_SELECTED' => 'Delete Selected',
    'DELETE_SELECTED_ALT' => 'Delete all selected files',
    'BUTTON_DELETE_ALL' => 'Delete All',
    'DELETE_ALL_ALT' => 'Delete all files in the current view',
    'ICON_INFO_VIEW' => 'View the contents of this file',
    'DISPLAY_DEBUG_LOGS_ONLY' => 'Display debug-logs only?',
    'TEXT_HEADING_INFO' => 'File Contents',
    'TEXT_MOST_RECENT' => 'most recent',
    'TEXT_OLDEST' => 'oldest',
    'TEXT_SMALLEST' => 'smallest',
    'TEXT_LARGEST' => 'largest',
    'TEXT_INSTRUCTIONS' => '<p>The files may be sorted in ascending or descending order by clicking on the <em>Asc</em> or <em>Desc</em> column links.</p> <p>Click on an %7$s icon to view the contents of the associated file. Only the first %1$u bytes of the selected file will be read/displayed; if a file is &quot;over-sized&quot;, its <em>File Size</em> will be highlighted like <span class="bigfile">this</span>.</p><ul><li><strong>Delete All</strong> will delete all the files currently displayed.</li><li><strong>Delete Selected</strong> will delete only those files with selected checkboxes.</li><li><strong>Invert Selection</strong> will swap checked files for unchecked and vice versa. For example, if you want to delete all but one file, tick the selection for the file to be kept, then "Invert Selection" and finally "Delete Selected".</li></ul><p>Currently viewing the %2$s %3$u of %4$u log files having these prefixes:<br><code>%5$s</code><br>and <b>not</b> matching any (optional) user-defined prefixes: <code>%6$s</code>.</p>',
    'JS_MESSAGE_DELETE_ALL_CONFIRM' => 'Are you sure you want to delete these \'+n+\' files?',
    'JS_MESSAGE_DELETE_SELECTED_CONFIRM' => 'Are you sure you want to delete the \'+selected+\' selected file(s)?',
    'WARNING_NOT_SECURE' => '<span class="errorText">NOTE: You do not have SSL enabled. File contents you view from this page will not be encrypted and could present a security risk.</span>',
    'WARNING_NO_FILES_SELECTED' => 'No files were selected for deletion!',
    'WARNING_SOME_FILES_DELETED' => 'Warning: Only %u of %u log files were deleted; check permissions.',
    'SUCCESS_FILES_DELETED' => '%u log files were deleted.',
];

return $define;
