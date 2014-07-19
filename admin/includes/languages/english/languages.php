<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: languages.php 1105 2005-04-04 22:05:35Z birdbrain $
//

define('HEADING_TITLE', 'Languages');

define('TABLE_HEADING_LANGUAGE_NAME', 'Language');
define('TABLE_HEADING_LANGUAGE_CODE', 'Code');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_INFO_LANGUAGE_NAME', 'Name:');
define('TEXT_INFO_LANGUAGE_CODE', 'Code:');
define('TEXT_INFO_LANGUAGE_IMAGE', 'Image:');
define('TEXT_INFO_LANGUAGE_DIRECTORY', 'Directory:');
define('TEXT_INFO_LANGUAGE_SORT_ORDER', 'Sort Order:');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new language with its related data');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this language?');
define('TEXT_INFO_HEADING_NEW_LANGUAGE', 'New Language');
define('TEXT_INFO_HEADING_EDIT_LANGUAGE', 'Edit Language');
define('TEXT_INFO_HEADING_DELETE_LANGUAGE', 'Delete Language');

define('ERROR_REMOVE_DEFAULT_LANGUAGE', 'Error: The default language can not be removed. Please set another language as default, and try again.');
define('ERROR_DUPLICATE_LANGUAGE_CODE', 'Error: A language with that code has already been defined.');
?>