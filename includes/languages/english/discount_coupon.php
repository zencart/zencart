<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * $Id: discount_coupon.php 14712 2009-10-28 22:05:08Z ajeh $
 */

define('NAVBAR_TITLE', 'Discount Coupon');
define('HEADING_TITLE', 'Discount Coupon');

define('TEXT_INFORMATION', '');
define('TEXT_COUPON_FAILED', '<span class="alert important">%s</span> does not appear to be a valid Coupon Redemption Code. Please try typing it in again.');

define('TEXT_CLOSE_WINDOW', 'Close Window [x]');
define('TEXT_COUPON_HELP_HEADER', '<p class="bold">The Discount Coupon Redemption Code you have entered is for ');
define('TEXT_COUPON_HELP_NAME', '\'%s\'. </p>');
define('TEXT_COUPON_HELP_FIXED', '');
define('TEXT_COUPON_HELP_MINORDER', '<p>You need to spend %s to use this coupon, on qualifying products.</p>');
define('TEXT_COUPON_HELP_FREESHIP', '');
define('TEXT_COUPON_HELP_DESC', '<p><span class="bold">Discount Offer:</span> %s</p><p class="smallText">Certain other restrictions may apply. Please see below for other details.</p>');
define('TEXT_COUPON_HELP_DATE', '<p>The coupon is valid between %s and %s</p>');
define('TEXT_COUPON_HELP_RESTRICT', '<p class="biggerText bold">Discount Coupon Restrictions</p>');
define('TEXT_COUPON_HELP_CATEGORIES', '<p class="bold">Category Restrictions:</p>');
define('TEXT_COUPON_HELP_PRODUCTS', '<p class="bold">Product Restrictions:</p>');

define('TEXT_NO_CAT_TOP_ONLY_DENY', '<p>This coupon has specific Product Restrictions.</p>');
define('TEXT_NO_CAT_RESTRICTIONS', '<p>This coupon is valid for all categories.</p>');
define('TEXT_NO_PROD_RESTRICTIONS', '<p>This coupon is valid for all products.</p>');
define('TEXT_NO_PROD_SALES', '<p>This coupon is not valid for products on sale.</p>');
define('TEXT_CAT_ALLOWED', ' (Valid for this category)');
define('TEXT_CAT_DENIED', ' (Not allowed on this category)');
define('TEXT_PROD_ALLOWED', ' (Valid for this product)');
define('TEXT_PROD_DENIED', ' (Not allowed product)');
// gift certificates cannot be purchased with Discount Coupons
define('TEXT_COUPON_GV_RESTRICTION','<p class="smallText">Discount Coupons may not be applied towards the purchase of ' . TEXT_GV_NAMES . '. Limit 1 coupon per order.</p>');

define('TEXT_DISCOUNT_COUPON_ID_INFO', 'Look-up Discount Coupon ... ');
define('TEXT_DISCOUNT_COUPON_ID', 'Your Code: ');

define('TEXT_COUPON_GV_RESTRICTION_ZONES', 'Billing Address Restrictions apply.');
