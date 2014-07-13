<?php
/**
 * password_compat functions
*
* @package functions
* @copyright Copyright 2003-2014 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version GIT: $Id: Author: Ian Wilson  Wed Feb 19 15:57:35 2014 +0000 New in v1.5.3 $
*/
if (! defined('PASSWORD_DEFAULT')) {

  define('PASSWORD_BCRYPT', 1);
  define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);

  if (! function_exists('password_hash')) {
    function password_hash($password, $algo = null)
    {
      return zen_encrypt_password_new($password);
    }
  }
  if (! function_exists('password_verify')) {
    function password_verify($plain, $encrypted)
    {
      if (zen_not_null($plain) && zen_not_null($encrypted)) {
        $stack = explode(':', $encrypted);
        if (sizeof($stack) != 2)
          return false;
        if (zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5($plain, $encrypted) === true) {
          return true;
        } elseif (zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256($plain, $encrypted) === true) {
          return true;
        }
      }
      return false;
    }
  }
  if (! function_exists('password_needs_rehash')) {
    function password_needs_rehash($hash, $algo = null)
    {
      $tmp = explode(':', $hash);
      if (count($tmp) == 2 && strlen($tmp [1]) == 2) {
        return true;
      } else {
        return false;
      }
    }
  }
}