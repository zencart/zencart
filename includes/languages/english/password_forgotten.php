<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sun Oct 18 01:50:54 2015 -0400 Modified in v1.5.5 $
 */

define('NAVBAR_TITLE_1', 'Login');
define('NAVBAR_TITLE_2', 'Password Forgotten');

define('HEADING_TITLE', 'Forgotten Password');

define('TEXT_MAIN', 'Enter your email address below and we\'ll send you an email message containing your new password.');

define('EMAIL_PASSWORD_REMINDER_SUBJECT', STORE_NAME . ' - New Password');
define('EMAIL_PASSWORD_REMINDER_BODY', 'A new password was requested from ' . $_SERVER['REMOTE_ADDR']  . '.' . "\n\n" . 'Your new password to \'' . STORE_NAME . '\' is:' . "\n\n" . '   %s' . "\n\nAfter you have logged in using the new password, you may change it by going to the 'My Account' area.");

define('SUCCESS_PASSWORD_SENT', 'Thank you. If that email address is in our system, we will send password recovery instructions to that email address.');
