<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: database_setup.php 19537 2011-09-20 17:14:44Z drbyte $
 */
/**
 * defining language components for the page
 */
  define('SAVE_DATABASE_SETTINGS', 'Save Database Settings');//this comes before TEXT_MAIN
  define('TEXT_MAIN', "Next we need to know some information on your database settings.  Please carefully enter each setting in the appropriate box and press <em>Save Database Settings</em> to continue.'");
  define('TEXT_PAGE_HEADING', 'Zen Cart&reg; Setup - Database Setup');
  define('DATABASE_INFORMATION', 'Database Information');
  define('DATABASE_OPTIONAL_INFORMATION', 'Database - OPTIONAL Settings');
  define('DATABASE_OPTIONAL_INSTRUCTION', 'It is recommended to leave these settings as-is unless you have a specific reason for altering them.');
  define('DATABASE_TYPE', 'Database Type');
  define('DATABASE_TYPE_INSTRUCTION', 'Choose the database type to be used.');
  define('DATABASE_CHARSET', 'Database Character Set / Collation');
  define('DATABASE_CHARSET_INSTRUCTION', 'Choose the database collation to be used.');
  define('DATABASE_HOST', 'Database Host');
  define('DATABASE_HOST_INSTRUCTION', 'What is the database host?  The database host can be in the form of a host name, such as \'db1.myserver.com\', or as an IP-address, such as \'192.168.0.1\'.');
  define('DATABASE_USERNAME', 'Database Username');
  define('DATABASE_USERNAME_INSTRUCTION', 'What is the username used to connect to the database? An example username is \'root\'.');
  define('DATABASE_PASSWORD', 'Database Password');
  define('DATABASE_PASSWORD_INSTRUCTION', 'What is the password used to connect to the database?  The password is used together with the username, which forms your database user account.');
  define('DATABASE_NAME', 'Database Name');
  define('DATABASE_NAME_INSTRUCTION', 'What is the name of the database used to hold the data? An example database name is \'zencart\' or \'myaccount_zencart\'.');
  define('DATABASE_PREFIX', 'Store Identifier (Table-Prefix)');
  define('DATABASE_PREFIX_INSTRUCTION', 'What is the prefix you would like used for database tables?  Example: zen_ Leave empty if no prefix is needed.<br />You can use prefixes to allow more than one store to share the same database.');
  define('DATABASE_CREATE', 'Create Database?');
  define('DATABASE_CREATE_INSTRUCTION', 'Would you like Zen Cart&reg; to create the database?');
  define('DATABASE_CONNECTION', 'Persistent Connection');
  define('DATABASE_CONNECTION_INSTRUCTION', 'Would you like to enable persistent database connections?  Click \'no\' if you are unsure.');
  define('DATABASE_SESSION', 'Database Sessions');
  define('DATABASE_SESSION_INSTRUCTION', 'Do you want store your sessions in your database?  Click \'yes\' if you are unsure.');
  define('CACHE_TYPE', 'SQL Cache Method');
  define('CACHE_TYPE_INSTRUCTION', 'Select the method to use for SQL caching.');
  define('SQL_CACHE', 'Session/SQL Cache Directory');
  define('SQL_CACHE_INSTRUCTION', 'Enter the directory to use for file-based caching.');
  define('ONLY_UPDATE_CONFIG_FILES','Only Update Config Files');


  define('REASON_TABLE_ALREADY_EXISTS','Cannot create table %s because it already exists');
  define('REASON_TABLE_DOESNT_EXIST','Cannot drop table %s because it does not exist.');
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

