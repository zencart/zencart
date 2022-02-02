<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
*/

$define = [
    'HEADING_TITLE' => 'SQL Query Executor',
    'HEADING_WARNING' => 'BE SURE TO BACKUP YOUR DATABASE AND VERIFY THAT BACKUP, BEFORE RUNNING SCRIPTS HERE',
    'HEADING_WARNING2' => 'If you are installing 3rd-party contributions, note that you do so at your own risk.<br>Zen Cart&reg; makes no warranty as to the safety of scripts supplied by 3rd-party contributors. Test on a development server before using on your live database!',
    'HEADING_WARNING_INSTALLSCRIPTS' => 'NOTE: Zen Cart database-upgrade scripts should NOT be run from this page.<br>Please upload the new <strong>zc_install</strong> folder and run the upgrade from there instead for better reliability.',
    'TEXT_QUERY_RESULTS' => 'Query Results:',
    'TEXT_ENTER_QUERY_STRING' => 'Enter the query<br>to be executed:&nbsp;&nbsp;<br><br>Ensure that each statement<br>ends with a semicolon ";"',
    'TEXT_QUERY_FILENAME' => 'Upload file:',
    'ERROR_NOTHING_TO_DO' => 'Error: Nothing to do - no query or query-file specified.',
    'SQLPATCH_HELP_TEXT' => 'The SQLPATCH tool lets you install system patches by pasting SQL code directly into the textarea ',
    'REASON_TABLE_ALREADY_EXISTS' => 'Cannot create table %s because it already exists',
    'REASON_TABLE_DOESNT_EXIST' => 'Cannot drop table %s because it does not exist.',
    'REASON_TABLE_NOT_FOUND' => 'Cannot execute because table %s does not exist.',
    'REASON_CONFIG_KEY_ALREADY_EXISTS' => 'Cannot insert configuration_key "%s" because it already exists',
    'REASON_COLUMN_ALREADY_EXISTS' => 'Cannot ADD column %s because it already exists.',
    'REASON_COLUMN_DOESNT_EXIST_TO_DROP' => 'Cannot DROP column %s because it does not exist.',
    'REASON_COLUMN_DOESNT_EXIST_TO_CHANGE' => 'Cannot CHANGE column %s because it does not exist.',
    'REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS' => 'Cannot insert prod-type-layout configuration_key "%s" because it already exists',
    'REASON_INDEX_DOESNT_EXIST_TO_DROP' => 'Cannot drop index %s on table %s because it does not exist.',
    'REASON_PRIMARY_KEY_DOESNT_EXIST_TO_DROP' => 'Cannot drop primary key on table %s because it does not exist.',
    'REASON_INDEX_ALREADY_EXISTS' => 'Cannot add index %s to table %s because it already exists.',
    'REASON_PRIMARY_KEY_ALREADY_EXISTS' => 'Cannot add primary key to table %s because a primary key already exists.',
    'REASON_NO_PRIVILEGES' => 'User ' . DB_SERVER_USERNAME . '@' . DB_SERVER . ' does not have %s privileges to database ' . DB_DATABASE . '.',
    'ERROR_RENAME_TABLE' => 'RENAME TABLE command not supported by SQLpatch tool. Please use phpMyAdmin instead.',
    'ERROR_LINE_INCOMPLETE' => 'Query incomplete: missing closing semicolon.',
    'TEXT_EXECUTE_SUCCESS' => 'Success: %u statement(s) processed.',
    'ERROR_EXECUTE_FAILED' => 'Query failed: %u statement(s) processed.',
    'ERROR_EXECUTE_IGNORED' => 'Note: %u statements ignored. See database table "upgrade_exceptions" for additional details.',
    'TEXT_UPLOADQUERY_SUCCESS' => 'Success: %u statement(s) processed via file upload',
    'ERROR_UPLOADQUERY_FAILED' => 'Query failed: %u statement(s) processed via file upload',
    'ERROR_UPLOADQUERY_IGNORED' => 'Note: %u statements ignored via file upload. See database table "upgrade_exceptions" for additional details.',
];

return $define;
