<?php
/**
 * password_funcs functions
 *
 * @package functions
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Wed Aug 29 13:38:15 2012 +0100 Modified in v1.5.1 $
 */

////
// This function validates a plain text password with an encrpyted password
function zen_validate_password($plain, $encrypted)
{
  if (zen_not_null($plain) && zen_not_null($encrypted))
  {
    // split apart the hash / salt
    $stack = explode(':', $encrypted);

    if (sizeof($stack) != 2)
      return false;

    if (md5($stack[1] . $plain) == $stack[0])
    {
      return true;
    }
  }

  return false;
}
////
// This function makes a new password from a plaintext password.
function zen_encrypt_password($plain)
{
  $password = '';

  for ($i = 0; $i < 10; $i++)
  {
    $password .= zen_rand();
  }

  $salt = substr(md5($password), 0, 2);

  $password = md5($salt . $plain) . ':' . $salt;

  return $password;
}
////
function zen_create_random_value($length, $type = 'mixed')
{
  if (($type != 'mixed') && ($type != 'chars') && ($type != 'digits'))
    return false;

  $rand_value = '';
  while (strlen($rand_value) < $length)
  {
    if ($type == 'digits')
    {
      $char = zen_rand(0, 9);
    } else
    {
      $char = chr(zen_rand(0, 255));
    }
    if ($type == 'mixed')
    {
      if (preg_match('/^[a-z0-9]$/i', $char))
        $rand_value .= $char;
    } elseif ($type == 'chars')
    {
      if (preg_match('/^[a-z]$/i', $char))
        $rand_value .= $char;
    } elseif ($type == 'digits')
    {
      if (preg_match('/^[0-9]$/', $char))
        $rand_value .= $char;
    }
  }
  
  if ($type == 'mixed' && !preg_match('/^(?=.*[\w]+.*)(?=.*[\d]+.*)[\d\w]{' . $length . ',}$/', $rand_value))
  {
    $rand_value .= zen_rand(0, 9);
  }

  return $rand_value;
}
function zen_get_entropy($seed)
{
  $entropy = '';
  $fp = @fopen('/dev/urandom', 'rb');
  if ($fp !== FALSE)
  {
    $entropy .= @fread($fp, 16);
    //    echo "USING /dev/random" . "<br>";
    @fclose($fp);
  }
  // MS-Windows platform?
  if (@class_exists('COM'))
  {
    // http://msdn.microsoft.com/en-us/library/aa388176(VS.85).aspx
    try
    {
      $CAPI_Util = new COM('CAPICOM.Utilities.1');
      $entropy .= $CAPI_Util->GetRandom(16, 0);

      if ($entropy)
      {
        $entropy = md5($entropy, TRUE);
        //echo "USING WINDOWS" . "<br>";
      }
    } catch (Exception $ex)
    {
      // echo 'Exception: ' . $ex->getMessage();
    }
  }
  if (strlen($entropy) < 16)
  {
    $entropy = sha1_file(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'configure.php');
    $entropy .= microtime() . mt_rand() . $seed;
    //echo "USING FALLBACK" . "<br>";
  }
  return sha1($entropy);
}
function zen_create_PADSS_password($length = 8)
{
  $charsAlpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charsNum = '0123456789';
  $charsMixed = $charsAlpha . $charsNum;
  $password = "";
  for ($i = 0; $i < $length; $i++)
  {
    $addChar = substr($charsMixed, zen_pwd_rand(0, strlen($charsMixed) - 1), 1);
    while (strpos($password, $addChar))
    {
      $addChar = substr($charsMixed, zen_pwd_rand(0, strlen($charsMixed) - 1), 1);
    }
    $password .= $addChar;
  }
  if (!preg_match('/[0-9]/', $password))
  {
    $addChar = substr($charsNum, zen_pwd_rand(0, strlen($charsNum) - 1), 1);
    $addPos = zen_pwd_rand(0, strlen($password) - 1);
    $password[$addPos] = $addChar;
  }
  return $password;
}
function zen_pwd_rand($min = 0, $max = 10)
{
  static $seed;
  if (!isset($seed))
    $seed = zen_get_entropy(microtime());
  $random = zen_get_entropy($seed);
  $random .= zen_get_entropy($random);
  $random = sha1($random);
  $random = substr($random, 0, 8);
  $value = abs(hexdec($random));
  $value = $min + (($max - $min + 1) * ($value / (4294967295 + 1)));
  $value = abs(intval($value));
  return $value;
}
