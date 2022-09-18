<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2021 Apr 02 New in v1.5.8-alpha $
*/


$define = [
    'HEADING_TITLE' => 'Display Debug Log Files',
    'TABLE_HEADING_FILENAME' => 'File Name',
    'TABLE_HEADING_MODIFIED' => 'Date Modified',
    'TABLE_HEADING_FILESIZE' => 'File Size (bytes)',
    'TABLE_HEADING_DELETE' => 'Delete?',
    'TABLE_HEADING_ACTION' => 'Action',
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
    'TEXT_INSTRUCTIONS' => '<br><br>The files can be sorted in either ascending or descending order (based on either the last-modified date or the file-size) by clicking on one of the <em>Asc</em> or <em>Desc</em> links. Click on an %7$s icon to view the contents of the associated file.  Only the first %1$u bytes of the selected file will be read; if a file is &quot;over-sized&quot;, its <em>File Size</em> will be highlighted like <span class="bigfile">this</span>.<br><br>Clicking the <strong>delete all</strong> button will delete all files currently being viewed; clicking <strong>delete selected</strong> will delete only those files with checked checkboxes.<br><br>Currently viewing the %2$s %3$u of %4$u log files with these <code>%5$s</code> prefixes and <b>not</b> matching these <code>%6$s</code>.<br>',
    'JS_MESSAGE_DELETE_ALL_CONFIRM' => 'Are you sure you want to delete these \'+n+\' files?',
    'JS_MESSAGE_DELETE_SELECTED_CONFIRM' => 'Are you sure you want to delete the \'+selected+\' selected file(s)?',
    'WARNING_NOT_SECURE' => '<span class="errorText">NOTE: You do not have SSL enabled. File contents you view from this page will not be encrypted and could present a security risk.</span>',
    'WARNING_NO_FILES_SELECTED' => 'No files were selected for deletion!',
    'WARNING_SOME_FILES_DELETED' => 'Warning: Only %u of %u log files were deleted; check permissions.',
    'SUCCESS_FILES_DELETED' => '%u log files were deleted.',
];

return $define;
