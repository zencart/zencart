<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
*/

$define = [
    'HEADING_TITLE' => 'Store Manager',
    'TABLE_CONFIGURATION_TABLE' => 'Lookup CONSTANT Definitions',
    'SUCCESS_PRODUCT_UPDATE_SORT_ALL' => '<strong>Successful</strong> update for Attributes Sort Order',
    'SUCCESS_PRODUCT_UPDATE_PRODUCTS_PRICE_SORTER' => '<strong>Successful</strong> update for Products Price Sorter Values',
    'SUCCESS_PRODUCT_UPDATE_PRODUCTS_VIEWED' => '<strong>Successful</strong> reset of Products Viewed to 0',
    'SUCCESS_PRODUCT_UPDATE_PRODUCTS_ORDERED' => '<strong>Successful</strong> reset of Products Ordered to 0',
    'SUCCESS_UPDATE_ALL_MASTER_CATEGORIES_ID' => '<strong>Successful</strong> reset of all Master Categories for Linked Products',
    'SUCCESS_UPDATE_COUNTER' => '<strong>Successful</strong> Counter Updated to: ',
    'ERROR_CONFIGURATION_KEY_NOT_FOUND' => '<strong>Error:</strong> No matching Configuration Keys were found ...',
    'ERROR_CONFIGURATION_KEY_NOT_ENTERED' => '<strong>Error:</strong> No Configuration Key or Text was entered to search for ... Search was terminated',
    'TEXT_INFO_COUNTER_UPDATE' => '<strong>Update Hit Counter</strong><br>to a new value: ',
    'TEXT_INFO_PRODUCTS_PRICE_SORTER_UPDATE' => '<strong>Update ALL Products Price Sorter</strong><br>to be able to sort by displayed prices: ',
    'TEXT_INFO_PRODUCTS_VIEWED_UPDATE' => '<strong>Reset ALL Products Viewed</strong><br>Reset Product Viewed Counts to 0: ',
    'TEXT_INFO_PRODUCTS_ORDERED_UPDATE' => '<strong>Reset ALL Products Ordered</strong><br>Reset Product Ordered Counts to 0: ',
    'TEXT_INFO_MASTER_CATEGORIES_ID_UPDATE' => '<strong>Reset ALL Products Master Categories ID</strong><br>to be used for Linked Products and Pricing: ',
    'TEXT_NEW_ORDERS_ID' => 'New Order ID',
    'TEXT_INFO_SET_NEXT_ORDER_NUMBER' => '<strong>Set next order number</strong><br>NOTE: You cannot set the order number to a value lower than any existing order already in the database.',
    'TEXT_MSG_NEXT_ORDER' => 'The next order number has been set to %s',
    'TEXT_MSG_NEXT_ORDER_MAX' => 'Due to existing order data, the next order number is currently: %s',
    'TEXT_MSG_NEXT_ORDER_TOO_LARGE' => 'Due to database limitations, you cannot set the next order number higher than 2000000000. Please choose a lower value.',
    'TEXT_CONFIGURATION_CONSTANT' => '<strong>Look-up CONSTANT or Language File defines</strong>',
    'TEXT_CONFIGURATION_KEY' => 'Key or Name:',
    'TEXT_INFO_CONFIGURATION_UPDATE' => '<strong>NOTE:</strong> CONSTANTS are written in uppercase.<br>Language file lookups may be an alternative search when nothing has been found in the database tables.',
    'TABLE_TITLE_KEY' => '<strong>Key:</strong>',
    'TABLE_TITLE_TITLE' => '<strong>Title:</strong>',
    'TABLE_TITLE_DESCRIPTION' => '<strong>Description:</strong>',
    'TABLE_TITLE_GROUP' => '<strong>Group:</strong>',
    'TABLE_TITLE_VALUE' => '<strong>Value:</strong>',
    'TEXT_LANGUAGE_LOOKUPS' => 'Language File Look-ups:',
    'TEXT_LANGUAGE_LOOKUP_CURRENT_LANGUAGE' => 'All Language Files for ' . strtoupper($_SESSION['language']) . ' - Catalog/Admin',
    'TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG' => 'All Main Language files - Catalog (' . DIR_WS_CATALOG . DIR_WS_LANGUAGES . 'english.php /espanol.php etc.)',
    'TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG_TEMPLATE' => 'All Current Selected Language Files - ' . DIR_WS_CATALOG . DIR_WS_LANGUAGES . 'language' . '/*.php',
    'TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN' => 'All Main Language files - Admin (' . DIR_WS_ADMIN . DIR_WS_LANGUAGES . 'english.php /espanol.php etc.)',
    'TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN_LANGUAGE' => 'All Current Selected Language Files - Admin (' . DIR_WS_ADMIN . DIR_WS_LANGUAGES . 'language' . '/*.php)',
    'TEXT_LANGUAGE_LOOKUP_CURRENT_ALL' => 'All Current Selected Language files - Catalog/Admin',
    'TEXT_INFO_NO_EDIT_AVAILABLE' => 'No edit available',
    'TEXT_INFO_CONFIGURATION_HIDDEN' => ' or, HIDDEN',
    'TEXT_INFO_DATABASE_OPTIMIZE' => '<strong>Optimize Database</strong> to remove wasted space from deleted records.<br>May be optionally run monthly or weekly on a busy database.<br>(Best to run during non-busy times.)',
    'TEXT_INFO_OPTIMIZING_DATABASE_TABLES' => 'Database table optimization in progress. This may take a few minutes. Please wait. The previous menu will re-appear when finished ... ',
    'SUCCESS_DB_OPTIMIZE' => 'Database Optimization - Tables Processed: ',
    'TEXT_INFO_PURGE_DEBUG_LOG_FILES' => '<strong>Cleanup Debug Log Files</strong><br><strong>CAUTION: </strong>Zen Cart records PHP error messages for debugging purposes, and many payment modules can be set to log debug data to diagnose communication problems. <br>Clicking this purge option will *permanently* remove *ALL* debug logs associated with PHP errors and payment modules from the /logs/ folder.',
    'SUCCESS_CLEAN_DEBUG_FILES' => 'Debug Log Files Purged',
];

return $define;
