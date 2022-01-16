<?php
$define = [
    'MODULE_ORDER_TOTAL_COUPON_TITLE' => 'Discount Coupon',
    'MODULE_ORDER_TOTAL_COUPON_HEADER' => TEXT_GV_NAMES . '/Discount Coupon',
    'MODULE_ORDER_TOTAL_COUPON_DESCRIPTION' => 'Discount Coupon',
    'MODULE_ORDER_TOTAL_COUPON_TEXT_ENTER_CODE' => TEXT_GV_REDEEM,
    'MODULE_ORDER_TOTAL_COUPON_REDEEM_INSTRUCTIONS' => '<p>Please type your coupon code into the box next to  Redemption Code. Your coupon will be applied to the total and reflected in your cart after you click continue.</p><p>Please note: you may only use one coupon per order.</p>',
    'MODULE_ORDER_TOTAL_COUPON_TEXT_CURRENT_CODE' => 'Your Current Redemption Code: ',
    'TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER' => 'REMOVE',
    'TEXT_REMOVE_REDEEM_COUPON' => 'Discount Coupon Removed by Request!',
    'MODULE_ORDER_TOTAL_COUPON_INCLUDE_ERROR' => ' Setting Include tax = true, should only happen when recalculate = None',
];

$define['MODULE_ORDER_TOTAL_COUPON_REMOVE_INSTRUCTIONS'] = '<p>To remove a Discount Coupon from this order replace the coupon code with: ' . $define['TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER'] . '</p>';

return $define;
