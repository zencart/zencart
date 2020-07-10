<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 10 Modified in v1.5.7 $
 */

define('HEADING_TITLE', 'Send ' . TEXT_GV_NAME);
define('HEADING_TITLE_CONFIRM_SEND', 'Send ' . TEXT_GV_NAME . ' Confirmation');
define('HEADING_TITLE_COMPLETED', TEXT_GV_NAME . ' Sent');
define('NAVBAR_TITLE', 'Send ' . TEXT_GV_NAME);
define('EMAIL_SUBJECT', 'Message from ' . STORE_NAME);
define('HEADING_TEXT','Please enter the name, email address and amount of the ' . TEXT_GV_NAME . ' you wish to send. For more information, please see our <a href="' . zen_href_link(FILENAME_GV_FAQ, '', 'NONSSL').'">' . GV_FAQ . '.</a>');
define('ENTRY_NAME', 'Recipient\'s Name:');
define('ENTRY_EMAIL', 'Recipient Email:');
define('ENTRY_MESSAGE', 'Your Message:');
define('ENTRY_AMOUNT', 'Amount to Send:');
define('ERROR_ENTRY_TO_NAME_CHECK', 'We did not get the Recipient\'s Name. Please fill it in below. ');
define('ERROR_ENTRY_AMOUNT_CHECK', 'The ' . TEXT_GV_NAME . ' amount does not appear to be correct. Please try again.');
define('ERROR_ENTRY_EMAIL_ADDRESS_CHECK', 'Is the email address correct? Please try again.');
define('MAIN_MESSAGE', 'You are sending a ' . TEXT_GV_NAME . ' worth %s to %s,  whose email address is %s. If these details are not correct, you may edit your message by clicking the <strong>edit</strong> button.<br /><br />The message you are sending is:<br /><br />');
define('SECONDARY_MESSAGE', 'Dear %s,<br /><br />' . 'You have been sent a ' . TEXT_GV_NAME . ' worth %s by %s');
define('PERSONAL_MESSAGE', '%s says:');
define('TEXT_SUCCESS', 'Congratulations, your ' . TEXT_GV_NAME . ' has been sent.');
define('TEXT_SEND_ANOTHER', 'Would you like to send another ' . TEXT_GV_NAME . '?');
define('TEXT_AVAILABLE_BALANCE',  'Gift Certificate Account');

define('EMAIL_GV_TEXT_SUBJECT', 'A gift from %s');
define('EMAIL_SEPARATOR', '----------------------------------------------------------------------------------------');
define('EMAIL_GV_TEXT_HEADER', 'Congratulations, You have received a ' . TEXT_GV_NAME . ' worth %s');
define('EMAIL_GV_FROM', 'This ' . TEXT_GV_NAME . ' has been sent to you by %s');
define('EMAIL_GV_MESSAGE', 'with a message saying: ');
define('EMAIL_GV_SEND_TO', 'Hi, %s');
define('EMAIL_GV_REDEEM', 'To redeem this ' . TEXT_GV_NAME . ', please click on the link below. Please also write down the ' . TEXT_GV_REDEEM . ': %s  just in case you have problems.');
define('EMAIL_GV_LINK', 'To redeem please click here');
define('EMAIL_GV_FIXED_FOOTER', 'If you have problems redeeming the ' . TEXT_GV_NAME . ' using the automated link above, ' . "\n" .
                                'you can also enter the ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' during the checkout process at our store.');
define('EMAIL_GV_SHOP_FOOTER', '');
