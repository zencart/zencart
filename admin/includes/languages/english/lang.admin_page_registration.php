<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Jan 11 New in v1.5.8-alpha $
*/

$define = [
    'HEADING_TITLE' => 'Admin Page Registration',
    'TEXT_PAGE_KEY' => 'Page Key',
    'TEXT_LANGUAGE_KEY' => 'Page Name',
    'TEXT_MAIN_PAGE' => 'Page Filename',
    'TEXT_PAGE_PARAMS' => 'Page Parameters',
    'TEXT_MENU_KEY' => 'Menu',
    'TEXT_DISPLAY_ON_MENU' => 'Display on Menu?',
    'TEXT_EXAMPLE_PAGE_KEY' => '(e.g. myModPageName)',
    'TEXT_EXAMPLE_LANGUAGE_KEY' => '(e.g. BOX_MY_MOD_PAGE_NAME)',
    'TEXT_EXAMPLE_MAIN_PAGE' => '(e.g. FILENAME_PAGE_NAME)',
    'TEXT_EXAMPLE_PAGE_PARAMS' => '(e.g. option=1 or, more usually, leave blank)',
    'TEXT_SELECT_MENU' => 'Select Menu',
    'ERROR_PAGE_KEY_NOT_ENTERED' => 'Page key not entered. All admin pages must have a unique page key.',
    'ERROR_PAGE_KEY_ALREADY_EXISTS' => 'Page key already exists. Page keys must be unique.',
    'ERROR_LANGUAGE_KEY_NOT_ENTERED' => 'Language key not entered. All admin page must have a language key that defines the text on any menu link.',
    'ERROR_LANGUAGE_KEY_HAS_NOT_BEEN_DEFINED' => 'The language key entered has not been defined. Please check that it has been spelt correctly.',
    'ERROR_MAIN_PAGE_NOT_ENTERED' => 'The filename definition for the page has not been entered.',
    'ERROR_FILENAME_HAS_NOT_BEEN_DEFINED' => 'The filename definition entered does not exist. Please check that it has been spelt correctly.',
    'ERROR_MENU_NOT_CHOSEN' => 'Menu not chosen. You must associate the new page with a menu, even if it will not be displayed on that menu.',
    'SUCCESS_ADMIN_PAGE_REGISTERED' => 'Your admin page has been registered.',
];

return $define;
