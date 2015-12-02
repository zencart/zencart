<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: developers_tool_kit.php DrByte  Modified in v1.6.0 $
 */
  define('HEADING_TITLE', 'Developers Tool Kit');
  define('TABLE_CONFIGURATION_TABLE', 'Lookup CONSTANT Definitions');

  define('SUCCESS_PRODUCT_UPDATE_PRODUCTS_PRICE_SORTER', '<strong>Successful</strong> update for Products Price Sorter Values');

  define('ERROR_CONFIGURATION_KEY_NOT_FOUND', '<strong>Error:</strong> No matching Configuration Keys were found ...');
  define('ERROR_CONFIGURATION_KEY_NOT_ENTERED', '<strong>Error:</strong> No Configuration Key or Text was entered to search for ... Search was terminated');

  define('TEXT_INFO_PRODUCTS_PRICE_SORTER_UPDATE', '<strong>Update ALL Products Price Sorter</strong><br />to be able to sort by displayed prices: ');

  define('TEXT_CONFIGURATION_CONSTANT', '<strong>Look-up CONSTANT or Language File defines</strong>');
  define('TEXT_CONFIGURATION_KEY', 'Key or Name:');
  define('TEXT_INFO_CONFIGURATION_UPDATE', '<strong>NOTE:</strong> CONSTANTS are written in uppercase.<br />Language file, functions, classes, etc. lookups are performed when nothing has been found in the database tables, if selected in dropdown');

  define('TABLE_TITLE_KEY', '<strong>Key:</strong>');
  define('TABLE_TITLE_TITLE', '<strong>Title:</strong>');
  define('TABLE_TITLE_DESCRIPTION', '<strong>Description:</strong>');
  define('TABLE_TITLE_GROUP', '<strong>Group:</strong>');
  define('TABLE_TITLE_VALUE', '<strong>Value:</strong>');

  define('TEXT_LOOKUP_NONE', 'None');
  define('TEXT_INFO_SEARCHING', 'Searching ');
  define('TEXT_INFO_FILES_FOR', ' files ... for: ');
  define('TEXT_INFO_MATCHES_FOUND', 'Match Lines found: ');
  define('TEXT_INFO_FILENAME', 'FILENAME: ');

  define('TEXT_LANGUAGE_LOOKUPS', 'Language File Look-ups:');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_LANGUAGE', 'All Language Files for ' . strtoupper($_SESSION['language']) . ' - Catalog/Admin');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG', 'All Main Language files - Catalog (' . DIR_WS_CATALOG . DIR_WS_LANGUAGES . 'english.php /espanol.php etc.)');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG_TEMPLATE', 'All Current Selected Language Files - ' . DIR_WS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/*.php');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN', 'All Main Language files - Admin (' . DIR_WS_ADMIN . DIR_WS_LANGUAGES . 'english.php /espanol.php etc.)');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN_LANGUAGE', 'All Current Selected Language Files - Admin (' . DIR_WS_ADMIN . DIR_WS_LANGUAGES . $_SESSION['language'] . '/*.php)');
  define('TEXT_LANGUAGE_LOOKUP_CURRENT_ALL', 'All Current Selected Language files - Catalog/Admin');

  define('TEXT_FUNCTION_CONSTANT', '<strong>Look-up Functions or things in Function files</strong>');
  define('TEXT_FUNCTION_LOOKUPS', 'Function File Look-ups:');
  define('TEXT_FUNCTION_LOOKUP_CURRENT', 'All Function files - Catalog/Admin');
  define('TEXT_FUNCTION_LOOKUP_CURRENT_CATALOG', 'All Functions files - Catalog');
  define('TEXT_FUNCTION_LOOKUP_CURRENT_ADMIN', 'All Functions files - Admin');

  define('TEXT_CLASS_CONSTANT', '<strong>Look-up Classes or things in Classes files</strong>');
  define('TEXT_CLASS_LOOKUPS', 'Classes File Look-ups:');
  define('TEXT_CLASS_LOOKUP_CURRENT', 'All Classes files - Catalog/Admin');
  define('TEXT_CLASS_LOOKUP_CURRENT_CATALOG', 'All Classes files - Catalog');
  define('TEXT_CLASS_LOOKUP_CURRENT_ADMIN', 'All Classes files - Admin');

  define('TEXT_TEMPLATE_CONSTANT', '<strong>Look-up Template things</strong>');
  define('TEXT_TEMPLATE_LOOKUPS', 'Template File Look-ups:');
  define('TEXT_TEMPLATE_LOOKUP_CURRENT', 'All Template files - /templates sideboxes /pages etc.');
  define('TEXT_TEMPLATE_LOOKUP_CURRENT_TEMPLATES', 'All Template files - /templates');
  define('TEXT_TEMPLATE_LOOKUP_CURRENT_SIDEBOXES', 'All Template files - /sideboxes');
  define('TEXT_TEMPLATE_LOOKUP_CURRENT_PAGES', 'All Template files - /pages');

  define('TEXT_ALL_FILES_CONSTANT', '<strong>Look-up in all files</strong>');
  define('TEXT_ALL_FILES_LOOKUPS', 'All Files Look-ups:');
  define('TEXT_ALL_FILES_LOOKUP_CURRENT', 'All Files - Catalog/Admin');
  define('TEXT_ALL_FILES_LOOKUP_CURRENT_CATALOG', 'All Files - Catalog');
  define('TEXT_ALL_FILES_LOOKUP_CURRENT_ADMIN', 'All Files - Admin');

  define('TEXT_INFO_NO_EDIT_AVAILABLE','No edit available');
  define('TEXT_INFO_CONFIGURATION_HIDDEN', ' or, HIDDEN');

  define('TEXT_SEARCH_ALL_FILES', 'Search ALL files for: ');
  define('TEXT_SEARCH_DATABASE_TABLES', 'Search database configuration tables for: ');

  define('TEXT_ALL_FILESTYPE_LOOKUPS', 'File type');
  define('TEXT_ALL_FILES_LOOKUP_PHP', '.php only');
  define('TEXT_ALL_FILES_LOOKUP_PHPCSS', '.php and .css');
  define('TEXT_ALL_FILES_LOOKUP_CSS', '.css only');
  define('TEXT_ALL_FILES_LOOKUP_HTMLTXT', '.html and .txt');
  define('TEXT_ALL_FILES_LOOKUP_JS', '.js only');
  define('TEXT_ALL_FILES_LOOKUP_ALL_TYPES', 'Everything');

  define('TEXT_CASE_SENSITIVE', 'Case Sensitive?');
  define('TEXT_CONTEXT_LINES', 'Context lines: ');
  define('TEXT_SEARCH_LOOKUP_PLACEHOLDER', 'Enter search phrase or pattern');
  define('TEXT_SEARCH_KEY_PLACEHOLDER', 'Enter key name or phrase to search for');
  define('TEXT_SEARCH_PHRASE_PLACEHOLDER', 'Enter search phrase');
  define('TEXT_BUTTON_SEARCH', 'Search');
  define('TEXT_BUTTON_SEARCH_ALT', 'Execute Search');
  define('TEXT_BUTTON_REGEX_SEARCH', 'Grep');
  define('TEXT_BUTTON_REGEX_SEARCH_ALT', 'Search using Regex pattern');
  define('TEXT_ERROR_REGEX_FAIL', 'ALERT: An error occurred during search. If you were doing a regex/grep search, please inspect your regex pattern for syntax errors.');

  //Search Configuration Keys
  define('SEARCH_CFG_KEYS_HEADING_TITLE','<strong>Search in Configuration Settings/Keys</strong>');
  define('SEARCH_CFG_KEYS_SEARCH_BOX_TEXT', '<strong>Phrase to search:</strong> (This will search configuration setting names and descriptions, and also configuration_keys if exact match)');
  define('SEARCH_CFG_KEYS_TABLE_SECTION', 'Section');
  define('SEARCH_CFG_KEYS_TABLE_GROUP','Group');
  define('SEARCH_CFG_KEYS_TABLE_TITLE', 'Title');
  define('SEARCH_CFG_KEYS_TABLE_DESCRIPTION','Description');
  define('SEARCH_CFG_KEYS_TABLE_VALUE','Value');
  define('SEARCH_CFG_KEYS_TABLE_KEY_NAME', 'Key Name');
  define('SEARCH_CFG_KEYS_TABLE_EDIT','Edit');
  define('SEARCH_CFG_KEYS_NOT_FOUND_KEYS', 'No configuration key(s) found.');
  define('SEARCH_CFG_KEYS_FOUND_KEYS', 'configuration key(s) found.');
  define('SEARCH_CFG_KEYS_FORM_PLACEHOLDER', 'Enter words to find in configuration settings');
  define('SEARCH_CFG_KEYS_FORM_BUTTON_SEARCH_SORTED_BY_GROUP', 'Search');
  define('SEARCH_CFG_KEYS_FORM_BUTTON_SEARCH_SORTED_BY_KEY', 'Search (sorted by key)');
  define('SEARCH_CFG_KEYS_FORM_BUTTON_VIEW_ALL', 'View All (Every setting)');
  define('SEARCH_CFG_KEYS_FORM_BUTTON_RESET', 'Reset');
  define('TEXT_RESET_BUTTON_ALT', 'Clear all search fields, to start over.');
