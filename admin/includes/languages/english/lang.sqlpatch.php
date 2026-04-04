<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 27 Modified in v2.1.0-alpha1 $
*/

$define = [
    'HEADING_TITLE' => 'SQL Query Executor',
    'HEADING_INFO' => 'The SQL Query Executor allows you to run SQL queries directly on the database by pasting a script into the textarea or uploading a text file containing the script. It is intended for the manual installation of fields for Plugins and your own corrections/additions.',
    'HEADING_WARNING_INSTALLSCRIPTS' => 'This tool should <b>NOT</b> be used to execute Zen Cart database-upgrade scripts: use the Zen Cart Installer as per the documentation.',
    'HEADING_WARNING' => '<p>BEFORE you perform ANY database operation using this tool, ensure you have a VERIFIED backup of your database and you know how to restore it.<br>If you are installing 3rd-party modifications/Plugins, note that you do so at your own risk. Zen Cart&reg; makes no warranty as to the safety of scripts supplied by 3rd-party contributors.</p><p>Always test every script on a DEVELOPMENT server before using on your live shop!</p>',
    'TEXT_QUERY_RESULTS' => 'Query Results:',
    'TEXT_ENTER_QUERY_STRING' => 'Enter the query<br>to be executed:&nbsp;&nbsp;<br><br>Ensure that each statement<br>ends with a semicolon ";"',
    'TEXT_QUERY_FILENAME' => 'Upload file:',
    'ERROR_NOTHING_TO_DO' => 'Error: Nothing to do - no query or query-file specified.',
    'SQLPATCH_HELP_TEXT' => 'The SQL Query Executor allows you to run SQL queries directly by pasting a script into the textarea or uploading a text file containing the script.',
    'REASON_TABLE_ALREADY_EXISTS' => 'Cannot create table %s because it already exists',
    'REASON_TABLE_DOESNT_EXIST' => 'Cannot drop table %s because it does not exist.',
    'REASON_TABLE_NOT_FOUND' => 'Cannot execute because table %s does not exist.',
    'REASON_CONFIG_KEY_ALREADY_EXISTS' => 'Cannot insert configuration_key "%s" because it already exists',
    'REASON_COLUMN_ALREADY_EXISTS' => 'Cannot ADD column %s because it already exists.',
    'REASON_COLUMN_DOESNT_EXIST_TO_DROP' => 'Cannot DROP column %s because it does not exist.',
    'REASON_COLUMN_DOESNT_EXIST_TO_CHANGE' => 'Cannot CHANGE column %s because it does not exist.',
    'REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS' => 'Cannot insert prod-type-layout configuration_key "%s" because it already exists',
    'REASON_INDEX_DOESNT_EXIST_TO_DROP' => 'Cannot drop index %1$s on table %2$s because it does not exist.',
    'REASON_PRIMARY_KEY_DOESNT_EXIST_TO_DROP' => 'Cannot drop primary key on table %s because it does not exist.',
    'REASON_INDEX_ALREADY_EXISTS' => 'Cannot add index %1$s to table %2$s because it already exists.',
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
