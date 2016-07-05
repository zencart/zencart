<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Mon Dec 28 17:31:37 2015 -0500 Modified in v1.5.5 $
 */

  define('HEADING_TITLE', 'Store Manager');
  define('TABLE_CONFIGURATION_TABLE', 'Lookup CONSTANT Definitions');

  define('SUCCESS_PRODUCT_UPDATE_SORT_ALL', '<strong>Successful</strong> update for Attributes Sort Order');
  define('SUCCESS_PRODUCT_UPDATE_PRODUCTS_PRICE_SORTER', '<strong>Successful</strong> update for Products Price Sorter Values');
  define('SUCCESS_PRODUCT_UPDATE_PRODUCTS_VIEWED', '<strong>Successful</strong> reset of Products Viewed to 0');
  define('SUCCESS_PRODUCT_UPDATE_PRODUCTS_ORDERED', '<strong>Successful</strong> reset of Products Ordered to 0');
  define('SUCCESS_UPDATE_ALL_MASTER_CATEGORIES_ID', '<strong>Successful</strong> reset of all Master Categories for Linked Products');
  define('SUCCESS_UPDATE_COUNTER', '<strong>Successful</strong> Counter Updated to: ');

  define('ERROR_CONFIGURATION_KEY_NOT_FOUND', '<strong>Error:</strong> No matching Configuration Keys were found ...');
  define('ERROR_CONFIGURATION_KEY_NOT_ENTERED', '<strong>Error:</strong> No Configuration Key or Text was entered to search for ... Search was terminated');

  define('TEXT_INFO_COUNTER_UPDATE', '<strong>Update Hit Counter</strong><br />to a new value: ');
  define('TEXT_INFO_PRODUCTS_PRICE_SORTER_UPDATE', '<strong>Update ALL Products Price Sorter</strong><br />to be able to sort by displayed prices: ');
  define('TEXT_INFO_PRODUCTS_VIEWED_UPDATE', '<strong>Reset ALL Products Viewed</strong><br />Reset Product Viewed Counts to 0: ');
  define('TEXT_INFO_PRODUCTS_ORDERED_UPDATE', '<strong>Reset ALL Products Ordered</strong><br />Reset Product Ordered Counts to 0: ');
  define('TEXT_INFO_MASTER_CATEGORIES_ID_UPDATE', '<strong>Reset ALL Products Master Categories ID</strong><br />to be used for Linked Products and Pricing: ');

  define('TEXT_NEW_ORDERS_ID', 'New Order ID');
  define('TEXT_INFO_SET_NEXT_ORDER_NUMBER', '<strong>Set next order number</strong><br />NOTE: You cannot set the order number to a value lower than any existing order already in the database.');
  define('TEXT_MSG_NEXT_ORDER', 'The next order number has been set to %s');
  define('TEXT_MSG_NEXT_ORDER_MAX', 'Due to existing order data, the next order number is currently: %s');
  define('TEXT_MSG_NEXT_ORDER_TOO_LARGE', 'Due to database limitations, you cannot set the next order number higher than 2000000000. Please choose a lower value.');

  define('TEXT_CONFIGURATION_CONSTANT', '<strong>Look-up CONSTANT or Language File defines</strong>');
  define('TEXT_CONFIGURATION_KEY', 'Key or Name:');
  define('TEXT_INFO_CONFIGURATION_UPDATE', '<strong>NOTE:</strong> CONSTANTS are written in uppercase.<br />Language file lookups may be an alternative search when nothing has been found in the database tables.');


  define('TEXT_CONFIGURATION_CONSTANT_FILES', '<strong>Look-up in Language File defines</strong>');
  define('TEXT_CONFIGURATION_KEY_FILES', 'Look up text:');
  define('TEXT_INFO_CONFIGURATION_UPDATE_FILES', '<strong>NOTE:</strong> Language file lookups maybe upper or lower case');

  define('TABLE_TITLE_KEY', '<strong>Key:</strong>');
  define('TABLE_TITLE_TITLE', '<strong>Title:</strong>');
  define('TABLE_TITLE_DESCRIPTION', '<strong>Description:</strong>');
  define('TABLE_TITLE_GROUP', '<strong>Group:</strong>');
  define('TABLE_TITLE_VALUE', '<strong>Value:</strong>');

  define('TEXT_LANGUAGE_LOOKUPS', 'Language File Look-ups:');
  define('TEXT_LANGUAGE_LOOKUP_NONE', 'None');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_LANGUAGE', 'All Language Files for ' . strtoupper($_SESSION['language']) . ' - Catalog/Admin');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG', 'All Main Language files - Catalog (' . DIR_WS_CATALOG . DIR_WS_LANGUAGES . 'english.php /espanol.php etc.)');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG_TEMPLATE', 'All Current Selected Language Files - ' . DIR_WS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/*.php');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN', 'All Main Language files - Admin (' . DIR_WS_ADMIN . DIR_WS_LANGUAGES . 'english.php /espanol.php etc.)');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN_LANGUAGE', 'All Current Selected Language Files - Admin (' . DIR_WS_ADMIN . DIR_WS_LANGUAGES . $_SESSION['language'] . '/*.php)');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_ALL', 'All Current Selected Language files - Catalog/Admin');

  define('TEXT_INFO_NO_EDIT_AVAILABLE','No edit available');
  define('TEXT_INFO_CONFIGURATION_HIDDEN', ' or, HIDDEN');

  define('TEXT_INFO_DATABASE_OPTIMIZE', '<strong>Optimize Database</strong> to remove wasted space from deleted records.<br />May be optionally run monthly or weekly on a busy database.<br />(Best to run during non-busy times.)');
  define('TEXT_INFO_OPTIMIZING_DATABASE_TABLES', 'Database table optimization in progress. This may take a few minutes. Please wait. The previous menu will re-appear when finished ... ');
  define('SUCCESS_DB_OPTIMIZE', 'Database Optimization - Tables Processed: ');

  define('TEXT_INFO_PURGE_DEBUG_LOG_FILES', '<strong>Cleanup Debug Log Files</strong><br /><strong>CAUTION: </strong>Zen Cart records PHP error messages for debugging purposes, and many payment modules can be set to log debug data to diagnose communication problems. <br />Clicking this purge option will *permanently* remove *ALL* debug logs associated with PHP errors and payment modules from the /logs/ folder.');
  define('SUCCESS_CLEAN_DEBUG_FILES', 'Debug Log Files Purged');
