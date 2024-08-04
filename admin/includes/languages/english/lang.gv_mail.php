<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 27 Modified in v2.1.0-alpha1 $
*/

$define = [
    'HEADING_TITLE' => 'Send a ' . '%%TEXT_GV_NAME%%' . ' To Customers',
    'TEXT_FROM' => 'From:',
    'TEXT_TO' => 'Email To:',
    'TEXT_TO_CUSTOMERS' => 'To Customer Lists:',
    'TEXT_TO_EMAIL' => 'or To an Email Address:',
    'TEXT_TO_EMAIL_NAME' => 'Name (optional):',
    'TEXT_TO_EMAIL_INFO' => 'Choose a list from the above drop-down or use the following fields for sending a single email.',
    'TEXT_SUBJECT' => 'Subject:',
    'TEXT_AMOUNT' => '%%TEXT_GV_NAME%%' . ' Value:',
    'ERROR_GV_AMOUNT' => 'Enter a number using a decimal point for fractions eg.: 25.00.',
    'TEXT_AMOUNT_INFO' => '%%ERROR_GV_AMOUNT%%',
    'TEXT_HTML_MESSAGE' => 'HTML Message:',
    'TEXT_MESSAGE' => 'Text-Only Message:',
    'TEXT_MESSAGE_INFO' => '<p>Optionally include a specific message, inserted prior to the standard ' . '%%TEXT_GV_NAME%%' . ' email text.</p>',
    'NOTICE_EMAIL_SENT_TO' => 'Notice: %1$s email(s) sent to %2$s',
    'ERROR_NO_CUSTOMER_SELECTED' => 'Error: No Customer selected.',
    'ERROR_NO_AMOUNT_ENTERED' => 'Error: Certificate Value invalid.',
    'ERROR_NO_SUBJECT' => 'Error: no Email Subject entered.',
    'TEXT_GV_ANNOUNCE' => 'We\'re pleased to offer you a ' . '%%TEXT_GV_NAME%%' . ' for %s.',
    'TEXT_GV_TO_REDEEM_TEXT' => 'Use the following link to redeem the ' . '%%TEXT_GV_NAME%%' . "\n\n " . '%1$s%2$s' . "\n\n" . 'or visit ' . STORE_NAME . " at " . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "\n" . 'and enter the code %2$s on the Checkout-Payment page.',
    'TEXT_GV_TO_REDEEM_HTML' => '<a href="%1$s%2$s">Click here to redeem the ' . '%%TEXT_GV_NAME%%' . '</a> or visit <a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . '">' . STORE_NAME . '</a> and enter the code <strong>%2$s</strong> on the Checkout-Payment page.',
];

return $define;
