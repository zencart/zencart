<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: database_upgrade.php 19537 2011-09-20 17:14:44Z drbyte $
 */
/**
 * defining language components for the page
 */
  define('TEXT_PAGE_HEADING', 'Zen Cart&reg; Setup - Database Upgrade');
  define('UPDATE_DATABASE_NOW','Update Database Now');//this comes before TEXT_MAIN
  define('TEXT_MAIN', '<em>Warning: </em> This script should ONLY be used to upgrade your Zen Cart&reg; database schema through the versions listed below.  ' .
                      '<span class="emphasis"><strong>We HIGHLY RECOMMEND doing a full backup of your database prior to performing any upgrades on it!</strong></span>');
  define('TEXT_MAIN_2','<span class="emphasis">Please check the details below very carefully</span>. This information is taken from your configure.php settings.<br />' .
                      'Do not proceed unless you\'re sure they\'re correct, or else you risk corruption to your database.');

  define('DATABASE_INFORMATION', 'Database Information');
  define('DATABASE_TYPE', 'Database Type');
  define('DATABASE_HOST', 'Database Host');
  define('DATABASE_USERNAME', 'Database Username');
  define('DATABASE_PASSWORD', 'Database Password');
  define('DATABASE_NAME', 'Database Name');
  define('DATABASE_PREFIX', 'Database Table-Prefix');
  define('DATABASE_PRIVILEGES', 'Database Privileges');

  define('SNIFFER_PREDICTS','<em>Upgrade Sniffer</em> predicts: ');
  define('CHOOSE_UPGRADES','Please confirm your desired upgrade steps');
  define('TITLE_DATABASE_PREFIX_CHANGE','Change Database Table-Prefix');
  define('ERROR_PREFIX_CHANGE_NEEDED','<span class="errors">We were unable to locate the Zen Cart&reg; tables in your database.<br />Perhaps your database table-prefix has been specified incorrectly?</span><br />If modifying table prefixes doesn\'t solve your problem, you will need to manually compare your configure.php settings with your actual database, perhaps through phpMyAdmin or your webserver control panel.');
  define('TEXT_DATABASE_PREFIX_CHANGE','If you wish to change the database table prefixes, enter the new prefix below. <span class="emphasis">NOTE: please verify that the prefix name is not already used in your database</span>, as we do not check for such duplication. Using an already-existing table prefix will corrupt your database.');
  define('TEXT_DATABASE_PREFIX_CHANGE_WARNING','<span class="errors"><strong>WARNING: DO NOT ATTEMPT TO CHANGE TABLE PREFIXES IF YOU DO NOT HAVE A FULL AND DEPENDABLE RECENT BACKUP OF YOUR DATABASE CONTENTS. If something goes wrong in the process, you will need to recover from your backup. If this is cause for concern or uncertainty for you, then DO NOT attempt to rename your tables.</strong></span>');
  define('DATABASE_OLD_PREFIX','Old Table-Prefix');
  define('DATABASE_OLD_PREFIX_INSTRUCTION','Enter the OLD Table-Prefix');
  define('ENTRY_NEW_PREFIX','New Table-Prefix ');
  define('DATABASE_NEW_PREFIX_INSTRUCTION','Enter the NEW Table-Prefix');
  define('ENTRY_ADMIN_ID','Admin Username (from Zen Cart&reg; Admin area)');
  define('ENTRY_ADMIN_PASSWORD','Password');
  define('ADMIN_PASSSWORD_INSTRUCTION','Your Administrator username/password (the one that you use to access your shop Admin area) are required in order to make database changes. <em>(This is NOT your MySQL password)</em>');
  define('TITLE_SECURITY','Database Security');

  define('UPDATE_DATABASE_WARNING_DO_NOT_INTERRUPT','<span class="emphasis">After clicking below, DO NOT INTERRUPT. Please be patient during upgrade.</span>');
  define('SKIP_UPDATES','Done with Updates');


  define('REASON_TABLE_ALREADY_EXISTS','Cannot create table %s because it already exists');
  define('REASON_TABLE_DOESNT_EXIST','Cannot drop table %s because it does not exist.');
  define('REASON_TABLE_NOT_FOUND', 'Cannot ALTER or INSERT/REPLACE into table %s because it does not exist.');
  define('REASON_CONFIG_KEY_ALREADY_EXISTS','Cannot insert configuration_key "%s" because it already exists');
  define('REASON_COLUMN_ALREADY_EXISTS','Cannot ADD column %s because it already exists.');
  define('REASON_COLUMN_DOESNT_EXIST_TO_DROP','Cannot DROP column %s because it does not exist.');
  define('REASON_COLUMN_DOESNT_EXIST_TO_CHANGE','Cannot CHANGE column %s because it does not exist.');
  define('REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS','Cannot insert prod-type-layout configuration_key "%s" because it already exists');
  define('REASON_INDEX_DOESNT_EXIST_TO_DROP','Cannot drop index %s on table %s because it does not exist.');
  define('REASON_PRIMARY_KEY_DOESNT_EXIST_TO_DROP','Cannot drop primary key on table %s because it does not exist.');
  define('REASON_INDEX_ALREADY_EXISTS','Cannot add index %s to table %s because it already exists.');
  define('REASON_PRIMARY_KEY_ALREADY_EXISTS','Cannot add primary key to table %s because a primary key already exists.');
  define('REASON_NO_PRIVILEGES','User %s@%s does not have %s privileges to database.');
  define('REASON_CONFIGURATION_GROUP_KEY_ALREADY_EXISTS','Cannot insert configuration_group_title "%s" because it already exists');
  define('REASON_CONFIGURATION_GROUP_ID_ALREADY_EXISTS','Cannot insert configuration_group_id "%s" because it already exists');

