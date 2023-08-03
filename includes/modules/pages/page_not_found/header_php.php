<?php
/**
 * page_not_found header_php.php 
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_PAGE_NOT_FOUND');

// tell the browser that this page is showing as a result of a 404 error:
header('HTTP/1.1 404 Not Found');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// include template-specific file name defines
$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_PAGE_NOT_FOUND, 'false');

$breadcrumb->add(NAVBAR_TITLE);

require DIR_WS_CLASSES . 'site_map.php';
$zen_SiteMapTree = new zen_SiteMapTree;

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_PAGE_NOT_FOUND');
?>