<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 10 Modified in v1.5.7 $
 */

  define('MODULE_ORDER_TOTAL_COUPON_TITLE', 'Discount Coupon');
  define('MODULE_ORDER_TOTAL_COUPON_HEADER', TEXT_GV_NAMES . '/Discount Coupon');
  define('MODULE_ORDER_TOTAL_COUPON_DESCRIPTION', 'Discount Coupon');
  define('MODULE_ORDER_TOTAL_COUPON_TEXT_ENTER_CODE', TEXT_GV_REDEEM);
  define('MODULE_ORDER_TOTAL_COUPON_REDEEM_INSTRUCTIONS', '<p>Please type your coupon code into the box next to  Redemption Code. Your coupon will be applied to the total and reflected in your cart after you click continue.</p><p>Please note: you may only use one coupon per order.</p>');
  define('MODULE_ORDER_TOTAL_COUPON_TEXT_CURRENT_CODE', 'Your Current Redemption Code: ');
  define('TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER', 'REMOVE');
  define('MODULE_ORDER_TOTAL_COUPON_REMOVE_INSTRUCTIONS', '<p>To remove a Discount Coupon from this order replace the coupon code with: ' . TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER . '</p>');
  define('TEXT_REMOVE_REDEEM_COUPON', 'Discount Coupon Removed by Request!');
  define('MODULE_ORDER_TOTAL_COUPON_INCLUDE_ERROR', ' Setting Include tax = true, should only happen when recalculate = None');
