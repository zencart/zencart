<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_page_registration.php 18695 2011-05-04 05:24:19Z drbyte $
 */

define('HEADING_TITLE', 'Admin Page Registration');
define('TEXT_PAGE_KEY', 'Page Key');
define('TEXT_LANGUAGE_KEY', 'Page Name');
define('TEXT_MAIN_PAGE', 'Page Filename');
define('TEXT_PAGE_PARAMS', 'Page Parameters');
define('TEXT_MENU_KEY', 'Menu');
define('TEXT_DISPLAY_ON_MENU', 'Display on Menu?');
define('TEXT_SORT_ORDER', 'Sort Order');

define('TEXT_EXAMPLE_PAGE_KEY', '(e.g. myModPageName)');
define('TEXT_EXAMPLE_LANGUAGE_KEY', '(e.g. BOX_MY_MOD_PAGE_NAME)');
define('TEXT_EXAMPLE_MAIN_PAGE', '(e.g. FILENAME_PAGE_NAME)');
define('TEXT_EXAMPLE_PAGE_PARAMS', '(e.g. option=1 or, more usually, leave blank)');
define('TEXT_SELECT_MENU', 'Select Menu');

define('ERROR_PAGE_KEY_NOT_ENTERED', 'Page key not entered. All admin pages must have a unique page key.');
define('ERROR_PAGE_KEY_ALREADY_EXISTS', 'Page key already exists. Page keys must be unique.');
define('ERROR_LANGUAGE_KEY_NOT_ENTERED', 'Language key not entered. All admin page must have a language key that defines the text on any menu link.');
define('ERROR_LANGUAGE_KEY_HAS_NOT_BEEN_DEFINED', 'The language key entered has not been defined. Please check that it has been spelt correctly.');
define('ERROR_MAIN_PAGE_NOT_ENTERED', 'The filename definition for the page has not been entered.');
define('ERROR_FILENAME_HAS_NOT_BEEN_DEFINED', 'The filename definition entered does not exist. Please check that it has been spelt correctly.');
define('ERROR_MENU_NOT_CHOSEN', 'Menu not chosen. You must associate the new page with a menu, even if it will not be displayed on that menu.');
define('SUCCESS_ADMIN_PAGE_REGISTERED', 'Your admin page has been registered.');
