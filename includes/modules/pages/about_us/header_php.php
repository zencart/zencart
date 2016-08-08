<?php
/**
 * About Us Page
 * 
 * @package page
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php  $
 */

$zco_notifier->notify('NOTIFY_HEADER_START_ABOUTUS');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE);

// default to loading a template-specific define-page
$action = 'require';
$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_ABOUT_US, 'false');

$zco_notifier->notify('NOTIFY_HEADER_END_ABOUTUS');
