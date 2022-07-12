<?php
/** 
 * used only to override the email encoding method for backward compatibility. 
 *
 * This file may NOT be required, depending on your host mailserver configuration.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
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