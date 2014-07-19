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
// $Id: define_pages_editor.php 1969 2005-09-13 06:57:21Z drbyte $
//

define('HEADING_TITLE', 'Define Pages Editor for: ');
define('NAVBAR_TITLE', 'Define Pages Editor');

define('TEXT_INFO_EDIT_PAGE', 'Select a page to edit:');

define('TEXT_INFO_MAIN_PAGE', 'Main Page');

define('TEXT_INFO_SHIPPINGINFO', 'Shipping and Returns');
define('TEXT_INFO_PRIVACY', 'Privacy');
define('TEXT_INFO_CONDITIONS', 'Conditions of Use');
define('TEXT_INFO_CONTACT_US', 'Contact Us');
define('TEXT_INFO_CHECKOUT_SUCCESS', 'Checkout Success');

define('TEXT_INFO_PAGE_2', 'Page 2');
define('TEXT_INFO_PAGE_3', 'Page 3');
define('TEXT_INFO_PAGE_4', 'Page 4');

define('TEXT_FILE_DOES_NOT_EXIST', 'File does not exist: %s');

define('ERROR_FILE_NOT_WRITEABLE', 'Error: I can not write to this file. Please set the right user permissions on: %s');

define('TEXT_INFO_SELECT_FILE', 'Select a file to edit ...');
define('TEXT_INFO_EDITING', 'Editing file:');

define('TEXT_INFO_CAUTION','Note: you should always edit the files located in your current template override directory, Example: /languages/' . $_SESSION['language'] . '/html_includes/' . $template_dir . '<br />Be sure to make backups after changing your files.');
?>