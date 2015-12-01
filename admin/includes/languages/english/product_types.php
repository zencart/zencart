<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
//  $Id: product_types.php 1122 2005-04-05 04:37:58Z drbyte $
//

define('HEADING_TITLE', 'Product Types');
define('HEADING_TITLE_LAYOUT', 'Product Type Info Page Layout options :: ');

define('TABLE_HEADING_PRODUCT_TYPES', 'Product Types');
define('TABLE_HEADING_PRODUCT_TYPES_ALLOW_ADD_TO_CART', 'Add<br />to Cart');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_CONFIGURATION_TITLE', 'Title');
define('TABLE_HEADING_CONFIGURATION_VALUE', 'Value');

define('TEXT_HEADING_NEW_PRODUCT_TYPE', 'New Product Type');
define('TEXT_HEADING_EDIT_PRODUCT_TYPE', 'Edit Product Type');
define('TEXT_HEADING_DELETE_PRODUCT_TYPE', 'Delete Product Type');

define('TEXT_PRODUCT_TYPES', 'Product Types:');
define('TEXT_PRODUCT_TYPES_HANDLER', 'Handler Page:');
define('TEXT_PRODUCT_TYPES_ALLOW_ADD_CART', 'This Product can be added to cart:');
define('TEXT_DATE_ADDED', 'Date Added:');
define('TEXT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_PRODUCTS', 'Products:');
define('TEXT_PRODUCTS_IMAGE_DIR', 'Upload to directory:');
define('TEXT_IMAGE_NONEXISTENT', 'IMAGE DOES NOT EXIST');
define('TEXT_MASTER_TYPE', 'This product type should be considered a sub-type of ');

define('TEXT_NEW_INTRO', 'Please fill out the following information for the new manufacturer');
define('TEXT_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_PRODUCT_TYPES_NAME', 'Product Type Name:');
define('TEXT_PRODUCT_TYPES_IMAGE', 'Product Type Default Image:');
define('TEXT_PRODUCT_TYPES_URL', 'Manufacturers URL:');

define('TEXT_DELETE_INTRO', 'Are you sure you want to delete this product type?');
define('TEXT_DELETE_IMAGE', 'Delete product type default image?');
define('TEXT_DELETE_PRODUCTS', 'Delete products from this product type? (including product reviews, products on special, upcoming products)');
define('TEXT_DELETE_WARNING_PRODUCTS', '<b>WARNING:</b> There are %s products still linked to this product type!');

define('ERROR_DIRECTORY_NOT_WRITEABLE', 'Error: I can not write to this directory. Please set the right user permissions on: %s');
define('ERROR_DIRECTORY_DOES_NOT_EXIST', 'Error: Directory does not exist: %s');

define('IMAGE_LAYOUT', 'Layout Settings');
?>