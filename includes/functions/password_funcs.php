<?php
/**
 * password_funcs functions
 *
 * @package functions
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version
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
  // Use mcrypt with /dev/urandom if available
  if(function_exists('mcrypt_create_iv') && (
    // There is a bug in Windows + IIS in older versions of PHP
    strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' ||
    version_compare(PHP_VERSION, '5.3.7', '>=')
  ))
  {
    //echo('Attempting to create entrophy using mcrypt');
    $entropy = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
    if($entropy !== FALSE) return sha1($entropy);
  }

  // Fall back to raw /dev/urandom (does not work on windows)
  $fp = @fopen('/dev/urandom', 'rb');
  if ($fp !== FALSE)
  {
    //echo('Attempting to create entrophy using /dev/urandom');
    $entropy = @fread($fp, 32);
    @fclose($fp);
    if(strlen($entropy) == 32) return sha1($entrophy);
  }

  // This can be a bit slow, but try if available
  if (function_exists('openssl_random_pseudo_bytes')) {
    //echo('Attempting to create entrophy using openssl');
    $entropy = openssl_random_pseudo_bytes(32, $strong);
    if($strong) {
      unset($strong);
      return sha1($entropy);
    }
    unset($strong);
  }

  // The rest of the code works together as the final fallback. This may seem
  // kludgy, and it is, but will contain some random data unique to each installation.
  $entrophy = '';
  //echo('Attempting to create entrophy using FALLBACK');

  // MS-Windows platform?
  if (@class_exists('COM'))
  {
    // http://msdn.microsoft.com/en-us/library/aa388176(VS.85).aspx
    try
    {
      //echo('Adding random data to entrophy using CAPICOM.Utilities');
      $CAPI_Util = new COM('CAPICOM.Utilities.1');
      $entropy .= $CAPI_Util->GetRandom(16, 0);
      unset($CAPI_Util);
      // No return, should add with some more random data
    }
    catch (Exception $ex)
    {
      //echo('Exception: ' . $ex->getMessage());
    }
  }

  // Use the file statistics such as atime, mtime, inode, etc.
  $filename = DIR_FS_ROOT . 'includes/configure.php';
  if(is_readable($filename))
  {
    //echo('Adding random data to entrophy using file information and contents');
    $stat = @stat($filename);
    if($stat !== FALSE) {
      foreach($stat as $value) {
        $entropy .= $value;
      }
    }
    unset($stat);
    $entrophy .= sha1_file($filename, TRUE);
  }
  unset($filename);
  //echo('Adding random data to entrophy using time, mt_rand, and the seed');
  $entropy .= microtime() . mt_rand() . $seed;
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
