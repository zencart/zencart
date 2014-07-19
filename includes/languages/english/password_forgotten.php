<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: password_forgotten.php 3086 2006-03-01 00:40:57Z drbyte $
 */

define('NAVBAR_TITLE_1', 'Login');
define('NAVBAR_TITLE_2', 'Password Forgotten');

define('HEADING_TITLE', 'Forgotten Password');

define('TEXT_MAIN', 'Enter your email address below and we\'ll send you an email message containing your new password.');

define('TEXT_NO_EMAIL_ADDRESS_FOUND', 'Error: The Email Address was not found in our records; please try again.');

define('EMAIL_PASSWORD_REMINDER_SUBJECT', STORE_NAME . ' - New Password');
define('EMAIL_PASSWORD_REMINDER_BODY', 'A new password was requested from ' . $_SERVER['REMOTE_ADDR']  . '.' . "\n\n" . 'Your new password to \'' . STORE_NAME . '\' is:' . "\n\n" . '   %s' . "\n\nAfter you have logged in using the new password, you may change it by going to the 'My Account' area.");

define('SUCCESS_PASSWORD_SENT', 'A new password has been sent to your email address.');
?>