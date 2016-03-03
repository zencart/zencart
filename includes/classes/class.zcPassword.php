<?php
/**
 * File contains just the zcPassword class
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Tue Feb 9 16:18:42 2016 -0500 Modified in v1.5.5 $
 */
/**
 * class zcPassword
 *
 * helper class for managing password hashing for different PHP versions
 *
 * Updates admin/customer tables on successful login
 * For php < 5.3.7 uses custom code to create hashes using SHA256 and longer salts
 * For php >= 5.3.7 and < 5.5.0 uses https://github.com/ircmaxell/PHP-PasswordLib
 * For php >= 5.5.0 uses inbuilt php functions
 *
 * @package classes
 */
class zcPassword extends base
{
  /**
   *
   * @var $instance object
   */
  protected static $instance = null;
  /**
   * enforce singleton
   *
   * @param string $phpVersion
   */
  public static function getInstance($phpVersion)
  {
    if (! self::$instance) {
      $class = __CLASS__;
      self::$instance = new $class($phpVersion);
    }
    return self::$instance;
  }
  /**
   * constructor
   *
   * @param string $phpVersion
   */
  public function __construct($phpVersion = PHP_VERSION)
  {
    if (version_compare($phpVersion, '5.3.7', '<')) {
      require_once (realpath(dirname(__FILE__)) . '/../functions/password_compat.php');
    } elseif (version_compare($phpVersion, '5.5.0', '<')) {
      require_once (realpath(dirname(__FILE__)) . '/vendors/password_compat-master/lib/password.php');
    }
  }
  /**
   * Determine the password type
   *
   * Legacy passwords were hash:salt with a salt of length 2
   * php < 5.3.7 updated passwords are hash:salt with salt of length > 2
   * php >= 5.3.7 passwords are BMCF format
   *
   * @param string $encryptedPassword
   * @return string
   */
  function detectPasswordType($encryptedPassword)
  {
    $type = 'unknown';
    $tmp = explode(':', $encryptedPassword);
    if (count($tmp) == 2) {
      if (strlen($tmp [1]) > 2) {
        $type = 'compatSha256';
      } elseif (strlen($tmp [1]) == 2) {
        $type = 'oldMd5';
      }
    }
    return $type;
  }
  /**
   * validate a password where format is unknown
   *
   * @param string $plain
   * @param string $encrypted
   * @return boolean
   */
  public function validatePassword($plain, $encrypted)
  {
    $type = $this->detectPasswordType($encrypted);
    if ($type != 'unknown') {
      $method = 'validatePassword' . ucfirst($type);
      return $this->{$method}($plain, $encrypted);
    }
    $result = password_verify($plain, $encrypted);
    return $result;
  }
  /**
   * validate a legacy md5 type password
   *
   * @param string $plain
   * @param string $encrypted
   * @return boolean
   */
  public function validatePasswordOldMd5($plain, $encrypted)
  {
    if (zen_not_null($plain) && zen_not_null($encrypted)) {
      $stack = explode(':', $encrypted);
      if (sizeof($stack) != 2)
        return false;
      if (md5($stack [1] . $plain) == $stack [0]) {
        return true;
      }
    }
    return false;
  }
  /**
   * validate a SHA256 type password
   *
   * @param string $plain
   * @param string $encrypted
   * @return boolean
   */
  public function validatePasswordCompatSha256($plain, $encrypted)
  {
    if (zen_not_null($plain) && zen_not_null($encrypted)) {
      $stack = explode(':', $encrypted);
      if (sizeof($stack) != 2)
        return false;
      if (hash('sha256', $stack [1] . $plain) == $stack [0]) {
        return true;
      }
    }
    return false;
  }
  /**
   * Update a logged in Customer password.
   * e.g. when customer wants to change password
   *
   * @param string $plain
   * @param integer $customerId
   * @return string
   */
  public function updateLoggedInCustomerPassword($plain, $customerId)
  {
    $this->confirmDbSchema('customer');
    global $db;
    $updatedPassword = password_hash($plain, PASSWORD_DEFAULT);
    $sql = "UPDATE " . TABLE_CUSTOMERS . "
              SET customers_password = :password:
              WHERE customers_id = :customersId:";

    $sql = $db->bindVars($sql, ':customersId:', $_SESSION ['customer_id'], 'integer');
    $sql = $db->bindVars($sql, ':password:', $updatedPassword, 'string');
    $db->Execute($sql);
    return $updatedPassword;
  }
  /**
   * Update a not logged in Customer password.
   * e.g. login/timeout
   *
   * @param string $plain
   * @param string $emailAddress
   * @return string
   */
  public function updateNotLoggedInCustomerPassword($plain, $emailAddress)
  {
    $this->confirmDbSchema('customer');
    global $db;
    $updatedPassword = password_hash($plain, PASSWORD_DEFAULT);
    $sql = "UPDATE " . TABLE_CUSTOMERS . "
              SET customers_password = :password:
              WHERE customers_email_address = :emailAddress:";

    $sql = $db->bindVars($sql, ':emailAddress:', $emailAddress, 'string');
    $sql = $db->bindVars($sql, ':password:', $updatedPassword, 'string');
    $db->Execute($sql);
    return $updatedPassword;
  }
  /**
   * Update a not logged in Admin password.
   *
   * @param string $plain
   * @param string $admin
   * @return string
   */
  public function updateNotLoggedInAdminPassword($plain, $admin)
  {
    $this->confirmDbSchema('admin');
    global $db;
    $updatedPassword = password_hash($plain, PASSWORD_DEFAULT);
    $sql = "UPDATE " . TABLE_ADMIN . "
              SET admin_pass = :password:
              WHERE admin_name = :adminName:";

    $sql = $db->bindVars($sql, ':adminName:', $admin, 'string');
    $sql = $db->bindVars($sql, ':password:', $updatedPassword, 'string');
    $db->Execute($sql);
    return $updatedPassword;
  }
  /**
   * Ensure db schema has been updated to support the required password lengths
   * @param string $mode
   */
  public function confirmDbSchema($mode = '') {
    global $db;
    if ($mode == '' || $mode == 'admin') {
      $sql = "ALTER TABLE " . TABLE_ADMIN . " MODIFY admin_pass VARCHAR( 255 ) NOT NULL DEFAULT ''";
      $db->Execute($sql);
      $sql = "ALTER TABLE " . TABLE_ADMIN . " MODIFY prev_pass1 VARCHAR( 255 ) NOT NULL DEFAULT ''";
      $db->Execute($sql);
      $sql = "ALTER TABLE " . TABLE_ADMIN . " MODIFY prev_pass2 VARCHAR( 255 ) NOT NULL DEFAULT ''";
      $db->Execute($sql);
      $sql = "ALTER TABLE " . TABLE_ADMIN . " MODIFY prev_pass3 VARCHAR( 255 ) NOT NULL DEFAULT ''";
      $db->Execute($sql);
      $sql = "ALTER TABLE " . TABLE_ADMIN . " MODIFY reset_token VARCHAR( 255 ) NOT NULL DEFAULT ''";
      $db->Execute($sql);
    }
    if ($mode == '' || $mode == 'customer') {
      $found = false;
      $sql = "show fields from " . TABLE_CUSTOMERS;
      $result = $db->Execute($sql);
      while (!$result->EOF && !$found) {
        if ($result->fields['Field'] == 'customers_password' && strtoupper($result->fields['Type']) == 'VARCHAR(255)') {
          $found = true;
        }
        $result->MoveNext();
      }
      if (!$found) {
        $sql = "ALTER TABLE " . TABLE_CUSTOMERS . " MODIFY customers_password VARCHAR( 255 ) NOT NULL DEFAULT ''";
        $db->Execute($sql);
      }
    }
    return;
  }
}
