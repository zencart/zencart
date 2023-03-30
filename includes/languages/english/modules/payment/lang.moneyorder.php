<?php
$define = [
    'MODULE_PAYMENT_MONEYORDER_TEXT_TITLE' => 'Check/Money Order',
    'MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION' => 'Customers can mail in their payment. Their order confirmation email will ask them to: <br><br>Please make your check or money order payable to:<br>' . (defined('MODULE_PAYMENT_MONEYORDER_PAYTO') ? MODULE_PAYMENT_MONEYORDER_PAYTO : '<br>(your store name)') . '<br><br>Mail your payment to:<br>' . nl2br(STORE_NAME_ADDRESS) . '<br><br>' . 'Your order will not ship until we receive payment.',
];
if (defined('MODULE_PAYMENT_MONEYORDER_STATUS')) {
    $define['MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER'] = 'Please make your check or money order payable to:' . "\n\n" . MODULE_PAYMENT_MONEYORDER_PAYTO . "\n\n" . 'Mail your payment to:' . "\n" . STORE_NAME_ADDRESS . "\n\n" . 'Your order will not ship until we receive payment.';
}

return $define;
