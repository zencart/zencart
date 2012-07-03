<?php
/**
 * password_funcs functions 
 *
 * @package functions
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: password_funcs.php 2618 2005-12-20 00:35:47Z drbyte $
 */

////
// This function validates a plain text password with an encrpyted password
  function zen_validate_password($plain, $encrypted) {
    if (zen_not_null($plain) && zen_not_null($encrypted)) {
// split apart the hash / salt
      $stack = explode(':', $encrypted);

      if (sizeof($stack) != 2) return false;

      if (md5($stack[1] . $plain) == $stack[0]) {
        return true;
      }
    }

    return false;
  }

////
// This function makes a new password from a plaintext password. 
  function zen_encrypt_password($plain) {
    $password = '';

    for ($i=0; $i<10; $i++) {
      $password .= zen_rand();
    }

    $salt = substr(md5($password), 0, 2);

    $password = md5($salt . $plain) . ':' . $salt;

    return $password;
  }
?>