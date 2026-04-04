<?php
$define = [
    'HEADING_TITLE' => 'Send ' . TEXT_GV_NAME,
    'HEADING_TITLE_CONFIRM_SEND' => 'Send ' . TEXT_GV_NAME . ' Confirmation',
    'HEADING_TITLE_COMPLETED' => TEXT_GV_NAME . ' Sent',
    'NAVBAR_TITLE' => 'Send ' . TEXT_GV_NAME,
    'EMAIL_SUBJECT' => 'Message from ' . STORE_NAME,
    'HEADING_TEXT' => 'Please enter the name, email address and amount of the ' . TEXT_GV_NAME . ' you wish to send. For more information, please see our <a href="' . zen_href_link(FILENAME_GV_FAQ) . '">' . GV_FAQ . '.</a>',
    'ENTRY_MESSAGE' => 'Your Message:',
    'ENTRY_AMOUNT' => 'Amount to Send:',
    'ERROR_ENTRY_TO_NAME_CHECK' => 'We did not get the Recipient\'s Name. Please fill it in below. ',
    'ERROR_ENTRY_AMOUNT_CHECK' => 'The ' . TEXT_GV_NAME . ' amount does not appear to be correct. Please try again.',
    'ERROR_ENTRY_EMAIL_ADDRESS_CHECK' => 'Is the email address correct? Please try again.',
    'MAIN_MESSAGE' => 'You are sending a ' . TEXT_GV_NAME . ' worth %1$s to %2$s,  whose email address is %3$s. If these details are not correct, you may edit your message by clicking the <strong>edit</strong> button.<br><br>The message you are sending is:<br><br>',
    'SECONDARY_MESSAGE' => 'Dear %1$s,<br><br>' . 'You have been sent a ' . TEXT_GV_NAME . ' worth %2$s by %3$s',
    'PERSONAL_MESSAGE' => '%s says:',
    'TEXT_SUCCESS' => 'Congratulations, your ' . TEXT_GV_NAME . ' has been sent.',
    'TEXT_SEND_ANOTHER' => 'Would you like to send another ' . TEXT_GV_NAME . '?',
    'EMAIL_GV_TEXT_SUBJECT' => 'A gift from %s',
    'EMAIL_SEPARATOR' => '----------------------------------------------------------------------------------------',
    'EMAIL_GV_TEXT_HEADER' => 'Congratulations, You have received a ' . TEXT_GV_NAME . ' worth %s',
    'EMAIL_GV_FROM' => 'This ' . TEXT_GV_NAME . ' has been sent to you by %s',
    'EMAIL_GV_MESSAGE' => 'with a message saying: ',
    'EMAIL_GV_SEND_TO' => 'Hi, %s',
    'EMAIL_GV_REDEEM' => 'To redeem this ' . TEXT_GV_NAME . ', please click on the link below. Please also write down the ' . TEXT_GV_REDEEM . ': %s  just in case you have problems.',
    'EMAIL_GV_LINK' => 'To redeem please click here',
    'EMAIL_GV_FIXED_FOOTER' => 'If you have problems redeeming the ' . TEXT_GV_NAME . ' using the automated link above, ' . "\n" .
        'you can also enter the ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' during the checkout process at our store.',
    'EMAIL_GV_SHOP_FOOTER' => '',
];

return $define;
