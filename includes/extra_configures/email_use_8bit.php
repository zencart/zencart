<?php
/** 
 * used only to override the email encoding method for backward compatibility. 
 *
 * This file may NOT be required, depending on your host mailserver configuration.
 *
 * @package constants
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: email_use_8bit.php 4843 2006-10-26 06:07:44Z drbyte $
 */
/**
 * specify the email encoding method to be used for sending emails
 * If unspecified, the default is 7bit.
 * Possible meaningful options are: "8bit" (older style), "7bit" (preferred), and "quoted-printable".
 *
 * To use 7bit, simply delete this file, or change the following to 7bit:
 */
  define('EMAIL_ENCODING_METHOD', '8bit');
?>