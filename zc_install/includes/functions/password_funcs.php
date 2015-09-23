<?php
/**
 * password_funcs functions
 *
 * @package functions
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version
 */

require_once (__DIR__ . '/../../../includes/classes/class.zcPassword.php');

/**
 * Validates a plain text password with the encrpyted password
 *
 * If the encrypted password begins with '$2y$', the PHP 5.5 compatible hashing
 * functions will be used.  Otherwise, a legacy md5-style hash is assumed.
 *
 * @param  string $plain     the plain-text password
 * @param  string $encrypted the encrypted password
 * @return bool
 */
function zen_validate_password($plain, $encrypted) {
  if (!zen_not_null($plain) || !zen_not_null($encrypted)) {
    return false;
  }

  if (strpos($encrypted, '$2y$') === 0) {
    return zcPassword::getInstance(PHP_VERSION)->validatePassword($plain, $encrypted);
  }

  // split apart the hash / salt
  $stack = explode(':', $encrypted);
  if (sizeof($stack) == 2) {
    return (md5($stack[1] . $plain) == $stack[0]);
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
/**
 * Returns entropy using a hash of various available methods for obtaining
 * random data. The default hash method is "sha1" and the default size is 32.
 *
 * @param string $hash the hash method to use while generating the hash.
 * @param int $size the size of random data to use while generating the hash.
 * @return string the randomized salt
 */
function zen_get_entropy($hash = 'sha1', $size = 32)
{
  $data = null;
  if (!in_array($hash, hash_algos())) $hash = 'sha1';
  if (!is_int($size)) $size = (int) $size;

  // Use openssl if available
  if (function_exists('openssl_random_pseudo_bytes'))
  {
    //echo('Attempting to create entropy using openssl');
    $entropy = openssl_random_pseudo_bytes($size, $strong);
    if ($strong) $data = $entropy;
    unset($strong, $entropy);
  }

  // Use mcrypt with /dev/urandom if available
  if ($data === null && function_exists('mcrypt_create_iv'))
  {
    //echo('Attempting to create entropy using mcrypt');
    $entropy = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
    if ($entropy !== FALSE) $data = $entropy;
    unset($entropy);
  }

  if ($data === null)
  {
    // Fall back to using /dev/urandom if available
    $fp = @fopen('/dev/urandom', 'rb');
    if ($fp !== FALSE)
    {
      //echo('Attempting to create entropy using /dev/urandom');
      $entropy = @fread($fp, $size);
      @fclose($fp);
      if (strlen($entropy) == $size) $data = $entropy;
      unset($fp, $entropy);
    }
  }

  // Final fallback (mixture of various methods)
  if ($data === null)
  {
    //echo('Attempting to create entropy using FINAL FALLBACK');
    $filename = DIR_FS_ROOT . 'includes/configure.php';
    $stat = @stat($filename);
    if ($stat === FALSE)
    {
      $stat = array(
        'microtime' => microtime()
      );
    }
    $stat['mt_rand'] = mt_rand();
    $stat['file_hash'] = hash_file($hash, $filename, TRUE);

    // Attempt to get a random value on windows
    // http://msdn.microsoft.com/en-us/library/aa388176(VS.85).aspx
    if (@class_exists('COM'))
    {
      try
      {
        $CAPI_Util = new COM('CAPICOM.Utilities.1');
        $entropy = $CAPI_Util->GetRandom($size, 0);

        if ($entropy)
        {
          //echo('Adding random data to entrohy using CAPICOM.Utilities');
          $stat['CAPICOM_Utilities_random'] = md5($entropy, TRUE);
        }
        unset($CAPI_Util, $entropy);
      } catch (Exception $ex) { }
    }

    //echo('Adding random data to entropy using file information and contents');
    @shuffle($stat);
    foreach ($stat as $value)
    {
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
