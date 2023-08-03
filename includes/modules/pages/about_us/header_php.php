<?php
/**
 * About Us Page
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jun 05 New in v1.5.8-alpha $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ABOUT_US');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
$breadcrumb->add(NAVBAR_TITLE);

$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_ABOUT_US, 'false');

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ABOUT_US');
