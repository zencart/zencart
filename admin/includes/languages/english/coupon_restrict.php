<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Aug 10 Modified in v1.5.7 $
 */

define('HEADING_TITLE', 'Discount Coupons Product/Category Restrictions');
define('HEADING_TITLE_CATEGORY', 'Category Restrictions');
define('HEADING_TITLE_PRODUCT', 'Product Restrictions');

define('SUB_HEADING_COUPON_NAME', 'Restrictions for the coupon named &quot;%1$s&quot; [%2$u].');  //-%1$s = coupon-name, %2$u = coupon_id

define('TABLE_HEADING_CATEGORY_ID', 'Category ID');
define('TABLE_HEADING_CATEGORY_NAME', 'Category Name');
define('TABLE_HEADING_PRODUCT_NAME', 'Product Name');
define('TABLE_HEADING_PRODUCT_ID', 'Product ID');
define('TABLE_HEADING_RESTRICT', 'Restriction');
define('TABLE_HEADING_RESTRICT_REMOVE', 'Remove');
define('IMAGE_REMOVE', 'Remove this restriction');
define('TEXT_ALL_CATEGORIES', 'All Categories');

define('MAX_DISPLAY_RESTRICT_ENTRIES', 20);
define('TEXT_ALL_PRODUCTS_ADD', 'Add All Category Products');
define('TEXT_ALL_PRODUCTS_REMOVE', 'Remove All Category Products');
define('TEXT_INFO_ADD_DENY_ALL', '<strong>For Add all Category Products, only Products not already set for restrictions will be added.<br />
                    For Delete all Category Products, only Products that are specified Deny or Allow will be removed.</strong>');

define('ERROR_DISCOUNT_COUPON_DEFINED_CATEGORY', 'Category Not Completed');
define('ERROR_DISCOUNT_COUPON_DEFINED_PRODUCT', 'Product Not Completed');

define('HEADER_MANUFACTURER_NAME', '<br> -- OR -- <br>' . 'Manufacturer: ');
define('TEXT_ALL_MANUFACTURERS_ADD', 'Add All Manufacturer Products');
define('TEXT_ALL_MANUFACTURERS_REMOVE', 'Remove All Manufacturer Products');

define('TABLE_HEADING_STATUS', 'Status');

define('ERROR_RESET_CATEGORY_MANUFACTURER', 'Category and Manufacturer filters reset. Use filters individually.');

define('TEXT_PULLDOWN_ALLOW', 'Allow');
define('TEXT_PULLDOWN_DENY', 'Deny');
define('TEXT_SUBMIT_CATEGORY_ADD', 'Add');
define('TEXT_SUBMIT_PRODUCT_UPDATE', 'Update');
define('TEXT_STATUS_TOGGLE', 'Toggle');
define('TEXT_STATUS_TOGGLE_TITLE', 'Click here to toggle the restriction\'s status');
define('TEXT_ALLOWED', 'Product or category is allowed');
define('TEXT_DENIED', 'Product or category is not allowed');

define('TEXT_NO_CATEGORY_RESTRICTIONS', 'No current category restrictions');
define('TEXT_NO_PRODUCT_RESTRICTIONS', 'No current product restrictions');
