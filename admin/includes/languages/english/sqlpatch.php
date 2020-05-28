<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Apr 16 Modified in v1.5.7 $
 */

  define('HEADING_TITLE','SQL Query Executor');
  define('HEADING_WARNING','BE SURE TO BACKUP YOUR DATABASE AND VERIFY THAT BACKUP, BEFORE RUNNING SCRIPTS HERE');
  define('HEADING_WARNING2','If you are installing 3rd-party contributions, note that you do so at your own risk.<br>Zen Cart&reg; makes no warranty as to the safety of scripts supplied by 3rd-party contributors. Test on a development server before using on your live database!');
  define('HEADING_WARNING_INSTALLSCRIPTS', 'NOTE: Zen Cart database-upgrade scripts should NOT be run from this page.<br>Please upload the new <strong>zc_install</strong> folder and run the upgrade from there instead for better reliability.');
  define('TEXT_QUERY_RESULTS','Query Results:');
  define('TEXT_ENTER_QUERY_STRING','Enter the query<br>to be executed:&nbsp;&nbsp;<br><br>Ensure that each statement<br>ends with a semicolon ";"');
  define('TEXT_QUERY_FILENAME','Upload file:');
  define('ERROR_NOTHING_TO_DO','Error: Nothing to do - no query or query-file specified.');
  define('TEXT_CLOSE_WINDOW', '[ close window ]');
  define('SQLPATCH_HELP_TEXT','The SQLPATCH tool lets you install system patches by pasting SQL code directly into the textarea '.
                              'field here, or by uploading a supplied script (.SQL) file.<br>' .
                              'When preparing scripts to be used by this tool, DO NOT include a table prefix, as this tool will ' .
                              'automatically insert the required prefix for the active database, based on settings in the store\'s ' .
                              'admin/includes/configure.php file (DB_PREFIX definition).<br><br>' .
                              'The commands entered or uploaded may only begin with the following statements, and MUST be in UPPERCASE:'.
                              '<br><ul><li>DROP TABLE IF EXISTS</li><li>CREATE TABLE</li><li>INSERT INTO</li><li>INSERT IGNORE INTO</li><li>ALTER TABLE</li>' .
                              '<li>UPDATE (just a single table)</li><li>UPDATE IGNORE (just a single table)</li><li>DELETE FROM</li><li>DROP INDEX</li><li>CREATE INDEX</li>' .
                              '<li>SELECT </li></ul>' .
'<h2>Advanced Methods</h2>The following methods can be used to issue more complex statements.<br>
To run some blocks of code together so that they are treated as one command by MySQL, you need the "<code>#NEXT_X_ROWS_AS_ONE_COMMAND:xxx</code>" value set.  The parser will then treat X number of commands as one.<br>
If you are running this file via phpMyAdmin or an equivalent, the "#NEXT..." comment is ignored, and the script will process fine.<br>
<br><strong>NOTE: </strong>SELECT.... FROM... and LEFT JOIN statements need the "FROM" or "LEFT JOIN" to be on a line by itself in order for the parse script to add the table prefix.<br><br>
<em><strong>Examples:</strong></em>
<ul><li><code>#NEXT_X_ROWS_AS_ONE_COMMAND:4<br>
SET @t1=0;<br>
SELECT (@t1:=configuration_value) as t1 <br>
FROM configuration <br>
WHERE configuration_key = \'KEY_NAME_HERE\';<br>
UPDATE product_type_layout SET configuration_value = @t1 WHERE configuration_key = \'KEY_NAME_TO_CHECK_HERE\';<br>
DELETE FROM configuration WHERE configuration_key = \'KEY_NAME_HERE\';</code><br>&nbsp;</li>

<li><code>#NEXT_X_ROWS_AS_ONE_COMMAND:1<br>
INSERT INTO tablename <br>
(col1, col2, col3, col4)<br>
SELECT col_a, col_b, col_3, col_4<br>
FROM table2;</code><br>&nbsp;</li>

<li><code>#NEXT_X_ROWS_AS_ONE_COMMAND:1<br>
INSERT INTO table1 <br>
(col1, col2, col3, col4 )<br>
SELECT p.othercol_a, p.othercol_b, po.othercol_c, pm.othercol_d<br>
FROM table2 p, table3 pm<br>
LEFT JOIN othercol_f po<br>
ON p.othercol_f = po.othercol_f<br>
WHERE p.othercol_f = pm.othercol_f;</code><br>&nbsp;</li>
</ul>' );
  define('REASON_TABLE_ALREADY_EXISTS','Cannot create table %s because it already exists');
  define('REASON_TABLE_DOESNT_EXIST','Cannot drop table %s because it does not exist.');
  define('REASON_TABLE_NOT_FOUND','Cannot execute because table %s does not exist.');
  define('REASON_CONFIG_KEY_ALREADY_EXISTS','Cannot insert configuration_key "%s" because it already exists');
  define('REASON_COLUMN_ALREADY_EXISTS','Cannot ADD column %s because it already exists.');
  define('REASON_COLUMN_DOESNT_EXIST_TO_DROP','Cannot DROP column %s because it does not exist.');
  define('REASON_COLUMN_DOESNT_EXIST_TO_CHANGE','Cannot CHANGE column %s because it does not exist.');
  define('REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS','Cannot insert prod-type-layout configuration_key "%s" because it already exists');
  define('REASON_INDEX_DOESNT_EXIST_TO_DROP','Cannot drop index %s on table %s because it does not exist.');
  define('REASON_PRIMARY_KEY_DOESNT_EXIST_TO_DROP','Cannot drop primary key on table %s because it does not exist.');
  define('REASON_INDEX_ALREADY_EXISTS','Cannot add index %s to table %s because it already exists.');
  define('REASON_PRIMARY_KEY_ALREADY_EXISTS','Cannot add primary key to table %s because a primary key already exists.');
  define('REASON_NO_PRIVILEGES','User '.DB_SERVER_USERNAME.'@'.DB_SERVER.' does not have %s privileges to database '.DB_DATABASE.'.');
  
define('ERROR_RENAME_TABLE', 'RENAME TABLE command not supported by SQLpatch tool. Please use phpMyAdmin instead.');
define('ERROR_LINE_INCOMPLETE', 'Query incomplete: missing closing semicolon.');

define('TEXT_EXECUTE_SUCCESS', 'Success: %u statement(s) processed.');
define('ERROR_EXECUTE_FAILED', 'Query failed: %u statement(s) processed.');
define('ERROR_EXECUTE_IGNORED', 'Note: %u statements ignored. See database table "upgrade_exceptions" for additional details.');

define('TEXT_UPLOADQUERY_SUCCESS', 'Success: %u statement(s) processed via file upload');
define('ERROR_UPLOADQUERY_FAILED', 'Query failed: %u statement(s) processed via file upload');
define('ERROR_UPLOADQUERY_IGNORED', 'Note: %u statements ignored via file upload. See database table "upgrade_exceptions" for additional details.');
