<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 May 12 Modified in v1.5.7 $
 */


require DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . 'gv_name.php';
define('HEADING_TITLE', 'Send a ' . TEXT_GV_NAME . ' To Customers');

define('TEXT_FROM', 'From:');
define('TEXT_TO', 'Email To:');
define('TEXT_TO_CUSTOMERS', 'To Customer Lists:');
define('TEXT_TO_EMAIL', 'or To an Email Address:');
define('TEXT_TO_EMAIL_NAME', 'Name (optional):');
define('TEXT_TO_EMAIL_INFO', '<span class="smallText">Choose a list from the above drop-down or use the following fields for sending a single email.</span>');
define('TEXT_SUBJECT', 'Subject:');
define('TEXT_AMOUNT', TEXT_GV_NAME . ' Value:');
define('ERROR_GV_AMOUNT', '<span class="smallText">Enter a number using a decimal point for fractions eg.: 25.00.</span>');
define('TEXT_AMOUNT_INFO', '<span class="smallText">' . ERROR_GV_AMOUNT . '</span>');
define('TEXT_HTML_MESSAGE', 'HTML<br>Message:');
define('TEXT_MESSAGE', 'Text-Only<br>Message:');
define('TEXT_MESSAGE_INFO', '<p>Optionally include a specific message, inserted prior to the standard ' . TEXT_GV_NAME . ' email text.</p>');

define('NOTICE_EMAIL_SENT_TO', 'Notice: %1s email(s) sent to %2s');
define('ERROR_NO_CUSTOMER_SELECTED', 'Error: No Customer selected.');
define('ERROR_NO_AMOUNT_ENTERED', 'Error: Certificate Value invalid.');
define('ERROR_NO_SUBJECT', 'Error: no Email Subject entered.');

define('TEXT_GV_ANNOUNCE', 'We\'re pleased to offer you a ' . TEXT_GV_NAME . ' for %s.');
define('TEXT_GV_TO_REDEEM_TEXT', 'Use the following link to redeem the ' . TEXT_GV_NAME . "\n\n ". '%1$s%2$s' . "\n\n" . 'or visit ' . STORE_NAME . " at " . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "\n" . 'and enter the code %2$s on the Checkout-Payment page.');
define('TEXT_GV_TO_REDEEM_HTML', '<a href="%1$s%2$s">Click here to redeem the ' . TEXT_GV_NAME . '</a> or visit <a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . '">' . STORE_NAME . '</a> and enter the code <strong>%2$s</strong> on the Checkout-Payment page.');

