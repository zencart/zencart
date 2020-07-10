<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 10 Modified in v1.5.7 $
 */

define('TEXT_CLOSE_WINDOW', 'Close Window [x]');
define('TEXT_COUPON_HELP_HEADER', '<strong>The Discount Coupon Redemption Code you have entered is for</strong> ');
define('TEXT_COUPON_HELP_NAME', '<br /><br />Coupon Name : %s');
define('TEXT_COUPON_HELP_FIXED', '<br /><br />The coupon is worth %s discount against your order');
define('TEXT_COUPON_HELP_MINORDER', '<br /><br />You need to spend %s to use this coupon');
define('TEXT_COUPON_HELP_FREESHIP', '<br /><br />This coupon gives you free shipping on your order');
define('TEXT_COUPON_HELP_DESC', '<br /><br />Coupon Description : %s');
define('TEXT_COUPON_HELP_DATE', '<br /><br />The coupon is valid between %s and %s');
define('TEXT_COUPON_HELP_RESTRICT', '<br /><br />Product/Category Restrictions');
define('TEXT_COUPON_HELP_CATEGORIES', 'Category');
define('TEXT_COUPON_HELP_PRODUCTS', 'Product');

define('TEXT_ALLOWED', ' (Allowed)');
define('TEXT_DENIED', ' (Denied)');

define('TEXT_NO_CAT_TOP_ONLY_DENY', '<p>This coupon has specific Product Restrictions.</p>');
define('TEXT_NO_CAT_RESTRICTIONS', '<p>This coupon is valid for all categories.</p>');
define('TEXT_NO_PROD_RESTRICTIONS', '<p>This coupon is valid for all products.</p>');
define('TEXT_NO_PROD_SALES', '<p>This coupon is not valid for products on sale.</p>');

// gift certificates cannot be purchased with Discount Coupons
define('TEXT_COUPON_GV_RESTRICTION','Discount Coupons may not be applied towards the purchase of ' . TEXT_GV_NAMES . '.');

define('TEXT_COUPON_GV_RESTRICTION_ZONES', 'Billing Address Restrictions apply.');
