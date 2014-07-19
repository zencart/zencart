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
//  $Id: downloads_manager.php 1105 2005-04-04 22:05:35Z birdbrain $
//

define('HEADING_TITLE','Downloads Manager');
define('TABLE_HEADING_ATTRIBUTES_ID', 'Attr ID');
define('TABLE_HEADING_PRODUCTS_ID', 'Prod ID');
define('TABLE_HEADING_PRODUCT', 'Product Name');
define('TABLE_HEADING_MODEL', 'Model');
define('TABLE_HEADING_OPT_NAME', 'Option Name');
define('TABLE_HEADING_OPT_VALUE', 'Option Value Name');
define('TABLE_TEXT_FILENAME', 'Filename');
define('TABLE_TEXT_MAX_DAYS', 'Days');
define('TABLE_TEXT_MAX_COUNT', 'Count');
define('TABLE_HEADING_ACTION', 'Action');

define('TABLE_HEADING_OPT_PRICE', 'Price');
define('TABLE_HEADING_OPT_PRICE_PREFIX', 'Prefix');

define('TEXT_PRODUCTS_NAME', 'Product: ');
define('TEXT_PRODUCTS_MODEL', 'Model: ');

define('TEXT_INFO_HEADING_EDIT_PRODUCTS_DOWNLOAD', 'EDITING DOWNLOAD INFORMATION');
define('TEXT_INFO_HEADING_DELETE_PRODUCTS_DOWNLOAD', 'CONFIRM DELETION OF DOWNLOAD');
define('TEXT_INFO_EDIT_INTRO', 'Edit the Download information:');
define('TEXT_DELETE_INTRO', 'The following filename will be removed from the database. This will not delete the file from the server:');

define('TEXT_INFO_FILENAME', 'Filename: ');
define('TEXT_INFO_MAX_DAYS', 'Max Days: ');
define('TEXT_INFO_MAX_COUNT', 'Max Downloads: ');

define('TEXT_INFO_FILENAME_MISSING','&nbsp;Missing filename');
define('TEXT_INFO_FILENAME_GOOD','&nbsp;Valid filename');
?>