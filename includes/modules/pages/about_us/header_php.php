<?php
/**
 * About Us Page
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.5.8 $
 */


require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE);

$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_ABOUT_US, 'false');
