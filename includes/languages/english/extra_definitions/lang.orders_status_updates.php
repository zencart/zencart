<?php
$define = [
    'OSH_EMAIL_SEPARATOR' => '------------------------------------------------------',
    'OSH_EMAIL_TEXT_SUBJECT' => 'Order Update',
    'OSH_EMAIL_TEXT_ORDER_NUMBER' => 'Order Number:',
    'OSH_EMAIL_TEXT_INVOICE_URL' => 'Order Details:',
    'OSH_EMAIL_TEXT_DATE_ORDERED' => 'Date Ordered:',
    'OSH_EMAIL_TEXT_COMMENTS_UPDATE' => '<em>The comments for your order are: </em>',
    'OSH_EMAIL_TEXT_STATUS_UPDATED' => 'Your order\'s status has been updated:' . "\n",
    'OSH_EMAIL_TEXT_STATUS_NO_CHANGE' => 'Your order\'s status has not changed:' . "\n",
    'OSH_EMAIL_TEXT_STATUS_LABEL' => '<strong>Current status: </strong> %s' . "\n\n",
    'OSH_EMAIL_TEXT_STATUS_CHANGE' => '<strong>Old status:</strong> %1$s, <strong>New status:</strong> %2$s' . "\n\n",
    'OSH_EMAIL_TEXT_STATUS_PLEASE_REPLY' => 'Please reply to this email if you have any questions.' . "\n",
    'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT' => '[ORDERS STATUS]',
];

return $define;