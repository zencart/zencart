<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: password_forgotten.php 18784 2011-05-25 07:40:29Z drbyte $
 */

define('HEADING_TITLE', 'Reset Password');

define('TEXT_ADMIN_EMAIL', 'Admin Email Address: ');
define('TEXT_BUTTON_REQUEST_RESET', 'request reset');
define('TEXT_BUTTON_LOGIN', 'login');
define('TEXT_BUTTON_CANCEL', 'cancel');

define('ERROR_WRONG_EMAIL', 'You entered the wrong email address.');
define('ERROR_WRONG_EMAIL_NULL', 'Go away gooberbrain :-P');
define('MESSAGE_PASSWORD_SENT', 'A new password has been sent to the email address you entered.<br />Click "login" below to login with the new temporary password.');

define('TEXT_EMAIL_SUBJECT_PWD_RESET', 'Your Requested change');
define('TEXT_EMAIL_MESSAGE_PWD_RESET', 'A new password was requested from %s.' . "\n\n" . 'Your new temporary password is:' . "\n\n   %s\n\nYou will be asked to choose a new password before logging in.\n\nThis temporary password expires in 24 hours.\n\n\n");

