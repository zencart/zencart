<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *  $Id: featured.php ajeh  Modified in v1.6.0 $
 */

define('HEADING_TITLE', 'Featured Products');

define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_PRODUCTS_PRICE', 'Products Price/Special/Sale');
define('TABLE_HEADING_PRODUCTS_PERCENTAGE','Percentage');
define('TABLE_HEADING_AVAILABLE_DATE', 'Available');
define('TABLE_HEADING_EXPIRES_DATE','Expires');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_FEATURED_PRODUCT', 'Product:');
define('TEXT_FEATURED_EXPIRES_DATE', 'Expiry Date:');
define('TEXT_FEATURED_AVAILABLE_DATE', 'Available Date:');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_NEW_PRICE', 'New Price:');
define('TEXT_INFO_ORIGINAL_PRICE', 'Original Price:');
define('TEXT_INFO_PERCENTAGE', 'Percentage:');
define('TEXT_INFO_AVAILABLE_DATE', 'Available On:');
define('TEXT_INFO_EXPIRES_DATE', 'Expires At:');
define('TEXT_INFO_STATUS_CHANGE', 'Status Change:');
define('TEXT_IMAGE_NONEXISTENT', 'No Image Exists');

define('TEXT_INFO_HEADING_DELETE_FEATURED', 'Delete Featured');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete the featured product?');

define('SUCCESS_FEATURED_PRE_ADD', 'Successful: Pre-Add of Featured ... please update the dates ...');
define('WARNING_FEATURED_PRE_ADD_EMPTY', 'Warning: No Product ID specified ... nothing was added ...');
define('WARNING_FEATURED_PRE_ADD_DUPLICATE', 'Warning: Product ID already Featured ... nothing was added ...');
define('WARNING_FEATURED_PRE_ADD_BAD_PRODUCTS_ID', 'Warning: Product ID is invalid ... nothing was added ...');
define('TEXT_INFO_HEADING_PRE_ADD_FEATURED', 'Manually add new Featured by Product ID');
define('TEXT_INFO_PRE_ADD_INTRO', 'On large databases, you may Manually Add a Featured by the Product ID<br /><br />This is best used when the page takes too long to render and trying to select a Product from the dropdown becomes difficult due to too many Products from which to choose.');
define('TEXT_PRE_ADD_PRODUCTS_ID', 'Please enter the Product ID to be Pre-Added: ');
define('TEXT_INFO_MANUAL', 'Product ID to be Manually Added as a Featured');

define('TEXT_SORT_FEATURED_TITLE_INFO', 'Sort Featured by:');
define('TEXT_SORT_PRODUCTS_ID', 'Products ID#');
define('TEXT_SORT_MODEL_NAME', 'Model #, Product Name');
define('TEXT_SORT_NAME_MODEL', 'Product Name, Model #');
define('TEXT_SORT_AVAILABLE_DESC_NAME', 'Available Desc, Product Name');
define('TEXT_SORT_AVAILABLE_ASC_NAME', 'Available Asc, Product Name');
define('TEXT_SORT_EXPIRE_DESC_NAME', 'Expires Desc, Product Name');
define('TEXT_SORT_EXPIRE_ASC_NAME', 'Expires Asc, Product Name');
define('TEXT_SORT_STATUS_NAME_DESC_NAME', 'Status Desc, Product Name');
define('TEXT_SORT_STATUS_NAME_ASC_NAME', 'Status Asc, Product Name');
