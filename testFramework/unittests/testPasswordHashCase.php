<?php
/**
 * File contains tests for password_hash function
 *
 * @package tests
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once('support/zcCatalogTestCase.php');
/**
 * Unit Tests for password hashing rules
 */
class testPasswordHash extends zcCatalogTestCase
{
  public function setUp()
  {
    parent::setUp();

    $pass = zcPassword::getInstance(PHP_VERSION);
  }
  public function testPasswordHashFunctionsExist()
  {
    $this->assertTrue(class_exists('zcPassword', false));
    $this->assertTrue(function_exists('password_hash'));
    $this->assertTrue(function_exists('password_verify'));
    $this->assertTrue(function_exists('password_needs_rehash'));
  }
  public function testPasswordHashResult()
  {
    $result = password_hash('testpass1', PASSWORD_DEFAULT);
    $tmp = explode(':', $result);
    if (version_compare(PHP_VERSION, '5.3.7', '<')) {
      $this->assertTrue(count($tmp) == 2 && strlen($tmp [0]) > 2);
    } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
      $this->assertTrue($result [0] == '$');
    } else {
      $this->assertTrue($result [0] == '$');
    }
  }
  public function testPasswordVerify()
  {
    if (version_compare(PHP_VERSION, '5.3.7', '<')) {
      $this->assertTrue(password_verify('password', 'd95e8fa7f20a009372eb3477473fcd34:1c'));
      $this->assertTrue(password_verify('testpass1', 'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751'));
    } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
      $this->assertTrue(password_verify('testpass1', '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC'));
    } else {
      $this->assertTrue(password_verify('testpass1', '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC'));
    }
  }
  public function testPasswordNeedsRehash()
  {
    if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
    } else {
      $hash = 'd95e8fa7f20a009372eb3477473fcd34:1c';
      $this->assertTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
      $hash = 'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751';
      $this->assertTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
      $hash = '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC';
      $this->assertTrue(! password_needs_rehash($hash, PASSWORD_DEFAULT));
    }
  }
  public function testDetectPasswordType()
  {
    $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('d95e8fa7f20a009372eb3477473fcd34:1c');
    $this->assertTrue($result == 'oldMd5');
    $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
    $this->assertTrue($result == 'compatSha256');
    $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
    $this->assertTrue($result == 'unknown');
  }
  public function testPasswordValidate()
  {
    $result = zcPassword::getInstance(PHP_VERSION)->validatePassword('password', 'd95e8fa7f20a009372eb3477473fcd34:1c');
    $this->assertTrue($result == true);
    $result = zcPassword::getInstance(PHP_VERSION)->validatePassword('testpass1', 'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
    $this->assertTrue($result == true);
    $result = zcPassword::getInstance(PHP_VERSION)->validatePassword('testpass1', '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
    if (version_compare(PHP_VERSION, '5.3.7', '<')) {
      $this->assertTrue($result == false);
    } else {
      $this->assertTrue($result == true);
    }
  }
  public function testValidatePasswordOldMd5()
  {
    $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5('password', 'd95e8fa7f20a009372eb3477473fcd34:1c');
    $this->assertTrue($result == true);
    $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5('testpass1', 'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
    $this->assertTrue($result == false);
    $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5('testpass1', '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
    $this->assertTrue($result == false);
  }
  public function testValidatePasswordCompatSha256()
  {
    $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256('password', 'd95e8fa7f20a009372eb3477473fcd34:1c');
    $this->assertTrue($result == false);
    $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256('testpass1', 'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
    $this->assertTrue($result == true);
    $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256('testpass1', '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
    $this->assertTrue($result == false);
  }
}
