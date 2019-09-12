<?php
/**
 * password_funcs functions
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Tue Oct 13 15:36:50 2015 -0400 Modified in v1.5.5 $
 */
// //
// This function validates a plain text password with an encrpyted password
function zen_validate_password($plain, $encrypted, $userRef = NULL)
{
  $zcPassword = zcPassword::getInstance(PHP_VERSION);
  return $zcPassword->validatePassword($plain, $encrypted);
}

/**
 * This function makes a new password from a plaintext password.
 * if php >= 5.5.0 we use inbuilt password_hash function.
 * otherwise we use zen_encrypt_password_new to create a salted sha256 password.
 * @param $plain
 * @return string
 */
function zen_encrypt_password($plain)
{
    if (function_exists('password_hash')) {
        $password = password_hash($plain, PASSWORD_DEFAULT);
    } else {
        $password = zen_encrypt_password_new($plain);
    }
    return $password;
}

/**
 * this function makes a sha256 password from a plaintext password.
 * @param $plain
 * @return string
 */
function zen_encrypt_password_new($plain)
{
  $password = '';
  for($i = 0; $i < 40; $i ++) {
    $password .= zen_rand();
  }
  $salt = hash('sha256', $password);
  $password = hash('sha256', $salt . $plain) . ':' . $salt;
  return $password;
}
// //
function zen_create_random_value($length, $type = 'mixed')
{
  if (($type != 'mixed') && ($type != 'chars') && ($type != 'digits'))
    return false;

  $rand_value = '';
  while ( strlen($rand_value) < $length ) {
    if ($type == 'digits') {
      $char = zen_rand(0, 9);
    } else {
      $char = chr(zen_rand(0, 255));
    }
    if ($type == 'mixed') {
      if (preg_match('/^[a-z0-9]$/i', $char))
        $rand_value .= $char;
    } elseif ($type == 'chars') {
      if (preg_match('/^[a-z]$/i', $char))
        $rand_value .= $char;
    } elseif ($type == 'digits') {
      if (preg_match('/^[0-9]$/', $char))
        $rand_value .= $char;
    }
  }

  if ($type == 'mixed' && ! preg_match('/^(?=.*[\w]+.*)(?=.*[\d]+.*)[\d\w]{' . $length . ',}$/', $rand_value)) {
    $rand_value .= zen_rand(0, 9);
  }

  return $rand_value;
}
/**
 * Returns entropy using a hash of various available methods for obtaining
 * random data.
 * The default hash method is "sha1" and the default size is 32.
 *
 * @param string $hash
 *          the hash method to use while generating the hash.
 * @param int $size
 *          the size of random data to use while generating the hash.
 * @return string the randomized salt
 */
function zen_get_entropy($hash = 'sha1', $size = 32)
{
  $data = null;
  if (! in_array($hash, hash_algos()))
    $hash = 'sha1';
  if (! is_int($size))
    $size = (int)$size;

    // Use openssl if available
  if (function_exists('openssl_random_pseudo_bytes')) {
    // echo('Attempting to create entropy using openssl');
    $entropy = openssl_random_pseudo_bytes($size, $strong);
    if ($strong)
      $data = $entropy;
    unset($strong, $entropy);
  }

  // Use mcrypt with /dev/urandom if available
  if ($data === null && function_exists('mcrypt_create_iv') && (
    // There is a bug in Windows + IIS in older versions of PHP
    (
strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' || version_compare(PHP_VERSION, '5.3.7', '>='))))
  {
    // echo('Attempting to create entropy using mcrypt');
    $entropy = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
    if ($entropy !== FALSE)
      $data = $entropy;
    unset($entropy);
  }

  if ($data === null) {
    // Fall back to using /dev/urandom if available
    $fp = @fopen('/dev/urandom', 'rb');
    if ($fp !== FALSE) {
      // echo('Attempting to create entropy using /dev/urandom');
      $entropy = @fread($fp, $size);
      @fclose($fp);
      if (strlen($entropy) == $size)
        $data = $entropy;
      unset($fp, $entropy);
    }
  }

  // Final fallback (mixture of various methods)
  if ($data === null) {
    // echo('Attempting to create entropy using FINAL FALLBACK');
    if (!defined('DIR_FS_ROOT')) define('DIR_FS_ROOT', DIR_FS_CATALOG);
    $filename = DIR_FS_ROOT . 'includes/configure.php';
    $stat = @stat($filename);
    if ($stat === FALSE) {
      $stat = array(
          'microtime' => microtime()
      );
    }
    $stat ['mt_rand'] = mt_rand();
    $stat ['file_hash'] = hash_file($hash, $filename, TRUE);

    // Attempt to get a random value on windows
    // http://msdn.microsoft.com/en-us/library/aa388176(VS.85).aspx
    if (@class_exists('COM')) {
      try {
        $CAPI_Util = new COM('CAPICOM.Utilities.1');
        $entropy = $CAPI_Util->GetRandom($size, 0);

        if ($entropy) {
          // echo('Adding random data to entropy using CAPICOM.Utilities');
          $stat ['CAPICOM_Utilities_random'] = md5($entropy, TRUE);
        }
        unset($CAPI_Util, $entropy);
      } catch ( Exception $ex ) {
      }
    }

    // echo('Adding random data to entropy using file information and contents');
    @shuffle($stat);
    foreach ( $stat as $value ) {
      $data .= $value;
    }
    unset($filename, $value, $stat);
  }

  return hash($hash, $data);
}
function zen_create_PADSS_password($length = 8)
{
  $charsAlpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charsNum = '0123456789';
  $charsMixed = $charsAlpha . $charsNum;
  $password = "";
  for($i = 0; $i < $length; $i ++) {
    $addChar = substr($charsMixed, zen_pwd_rand(0, strlen($charsMixed) - 1), 1);
    while ( strpos($password, $addChar) ) {
      $addChar = substr($charsMixed, zen_pwd_rand(0, strlen($charsMixed) - 1), 1);
    }
    $password .= $addChar;
  }
  if (! preg_match('/[0-9]/', $password)) {
    $addChar = substr($charsNum, zen_pwd_rand(0, strlen($charsNum) - 1), 1);
    $addPos = zen_pwd_rand(0, strlen($password) - 1);
    $password [$addPos] = $addChar;
  }
  return $password;
}
function zen_pwd_rand($min = 0, $max = 10)
{
  static $seed;
  if (! isset($seed))
    $seed = zen_get_entropy();
  $random = hash('sha1', zen_get_entropy() . $seed);
  $random .= hash('sha1', zen_get_entropy() . $random);
  $random = hash('sha1', $random);
  $random = substr($random, 0, 8);
  $value = abs(hexdec($random));
  $value = $min + (($max - $min + 1) * ($value / (4294967295 + 1)));
  $value = abs(intval($value));
  return $value;
}
