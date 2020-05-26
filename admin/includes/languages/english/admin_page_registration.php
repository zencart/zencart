<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_page_registration.php 18695 2011-05-04 05:24:19Z drbyte $
 */

define('HEADING_TITLE', 'Register New Admin Page');
define('TEXT_INFO', 'Define the parameters required to add a menu item to an admin menu group, e.g. for a link to a custom/plugin page.');
define('TEXT_PAGE_KEY', 'Page Key');
define('TEXT_LANGUAGE_KEY', 'Language constant  for Page Name');
define('TEXT_MAIN_PAGE', 'Filename constant');
define('TEXT_PAGE_PARAMS', 'Page Parameters');
define('TEXT_MENU_KEY', 'Add to Menu Group');
define('TEXT_DISPLAY_ON_MENU', 'Visible on Menu?');
define('TEXT_SORT_ORDER', 'Menu Listing Sort Order');

define('TEXT_EXAMPLE_PAGE_KEY', '(a unique key, e.g. myModPageName)');
define('TEXT_EXAMPLE_LANGUAGE_KEY', '(a constant, e.g. BOX_MY_MOD_PAGE_NAME, located in a file in each admin language subdirectory "/extra_defines")');
define('TEXT_EXAMPLE_MAIN_PAGE', '(a constant, e.g. FILENAME_PAGE_NAME, located in a file in admin subdirectory "includes/extra_datafiles")');
define('TEXT_EXAMPLE_PAGE_PARAMS', '(e.g. option=1 or, more usually, leave blank)');
define('TEXT_EXAMPLE_SORT_ORDER', '(a number defining the item position in the group menu. Leave blank to add menu item to the end of the current list.)');
define('TEXT_SELECT_MENU', 'Select Menu');

define('ERROR_PAGE_KEY_NOT_ENTERED', 'Page key not entered: all admin pages must have a unique page key.');
define('ERROR_PAGE_KEY_ALREADY_EXISTS', 'Page key already exists: page keys must be unique.');
define('ERROR_LANGUAGE_KEY_NOT_ENTERED', 'Page Name (Language Key constant) not entered: the admin page must have a constant defined for the page name on the menu.');
define('ERROR_LANGUAGE_KEY_HAS_NOT_BEEN_DEFINED', 'The Page Name (Language Key) entered has not been defined/is not found: please check that it has been spelt correctly.');
define('ERROR_MAIN_PAGE_NOT_ENTERED', 'The filename constant name for the page has not been entered.');
define('ERROR_FILENAME_HAS_NOT_BEEN_DEFINED', 'The filename constant name does not exist/is not found: please check that it has been spelt correctly.');
define('ERROR_MENU_NOT_CHOSEN', 'Menu Group not chosen. You must associate the new page with a menu group, even if it will not be visible on that menu.');
define('SUCCESS_ADMIN_PAGE_REGISTERED', 'Your admin page has been registered.');
