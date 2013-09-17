<?php
/**
 * Test Library
 *
 * @package tests
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

/**
 * Testing Library
 */
class testLibrary extends PHPUnit_Framework_TestCase
{

  /**
   * @group library
   */
  public function testExistsApplicationTop()
  {
    $this->assertTrue(file_exists(DIR_FS_INCLUDES . 'application_top.php'));
  }

  /**
   * @group library
   */
  public function testExecuteApplicationTop()
  {
    /**
     * must set nocache, since it can't seem to properly set $zc_cache as a working object
     */
    $_GET['nocache'] = TRUE;
    /**
     * Tell it we're a bot so it doesn't try to start sessions (cuz it can't)
     */
    $_SERVER['HTTP_USER_AGENT'] = 'bot';
    /**
     * init var used for testing success ... this will be set to a real value when application_top is done
     */
    $customers_ip_address = '';
    /**
     * Must skip this test for now :(
     */
    $this->markTestSkipped('Cannot fully test application_top, because cannot start session without causing Segmentation Fault');

    include (DIR_FS_CATALOG . '/includes/application_top.php');
    $this->assertNotEquals($customers_ip_address, '', 'IP Address not set.');
  }

  /**
   * @group library
   * @dataProvider getValidEmail
   */
  public function testValidEmailRfcValidation($emailAddress)
  {
    defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', FALSE);
    require_once (DIR_FS_CATALOG . '/includes/classes/class.base.php');
    require_once (DIR_FS_CATALOG . '/includes/classes/class.notifier.php');
    global $zco_notifier;
    $zco_notifier = new notifier();
    require_once (DIR_FS_CATALOG . '/includes/functions/functions_email.php');

    $result = zen_validate_email($emailAddress);
    $this->assertTrue($result, 'This email failed but should be valid: ' . $emailAddress);
  }

  public function getValidEmail()
  {
    return array(
      array('l3tt3rsAndNumb3rs@domain.com'),
      array('has-dash@domain.com'),
      array("hasApostrophe.o'leary@domain.org"),
      array('uncommonTLD@domain.museum'),
      array('uncommonTLD@domain.travel'),
      array('uncommonTLD@domain.mobi'),
      array('countryCodeTLD@domain.uk'),
      array('countryCodeTLD@domain.rw'),
      array('lettersInDomain@911.com'),
      array('underscore_inLocal@domain.net'),
      array('IPInsteadOfDomain@127.0.0.1'),
      array('IPAndPort@127.0.0.1:25'),
      array('subdomain@sub.domain.com'),
      array('local@dash-inDomain.com'),
      array('dot.inLocal@foo.com'),
      array('a@singleLetterLocal.org'),
      array('singleLetterDomain@x.org'),
      array("&*=?^+{}'~@validCharsInLocal.net"),
      array('foor@bar.newTLD'),
    );
  }

  /**
   * @group library
   * @dataProvider getInvalidEmail
   */
  public function testInvalidEmailRfcValidation($emailAddress)
  {
    defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', FALSE);
    require_once (DIR_FS_CATALOG . '/includes/classes/class.base.php');
    require_once (DIR_FS_CATALOG . '/includes/classes/class.notifier.php');
    global $zco_notifier;
    $zco_notifier = new notifier();
    require_once (DIR_FS_CATALOG . '/includes/functions/functions_email.php');

    $result = zen_validate_email($emailAddress);
    $this->assertFalse($result, 'This email passed but should be invalid: ' . $emailAddress);
  }

  public function getInvalidEmail()
  {
    return array(
      array('missingDomain@.com'),
      array('@missingLocal.org'),
      array('missingatSign.net'),
      array('missingDot@com'),
      array('two@@signs.com'),
      array('colonButMissingPort@127.0.0.1:'),
      array(''),
      array('IPaddressRangeTooHigh@256.0.256.1'),
      array('invalidIP@127.0.0.1.26'),
      array('.localStartsWithDot@domain.com'),
      array('localEndsWithDot.@domain.com'),
      array('two..consecutiveDots@domain.com'),
      array('domainStartsWithDash@-domain.com'),
      array('domainEndsWithDash@domain-.com'),
      array('numbersInTLD@domain.c0m'),
      array('missingTLD@domain.'),
      array('! "#$%(),/;<>[]`|@invalidCharsInLocal.org'),
      array('invalidCharsInDomain@! "#$%(),/;<>_[]`|.org'),
      array('local@SecondLevelDomainNamesAreInvalidIfTheyAreLongerThan64Charactersss.org'),
      array('Ηλεκτρον�ργίουbc@domain.com.cy'),
    );
  }


  /**
   * Test password entropy / duplication risks
   *
   * @group library
   */
  public function testPasswordGeneration()
  {
    require_once (DIR_FS_CATALOG . '/includes/functions/password_funcs.php');
    $passwordList = array();
    $loopCount = 10000;
    if (defined('BIG_LOOPS_BYPASS')) $loopCount = 100;
    for($i=0;$i<$loopCount;$i++)
    {
      $password = zen_create_PADSS_password();
      if (isset($passwordList[$password]))
      {
        $this->fail('Duplicate Password ');
      }
      $passwordList[$password] = $password;
    }
  }
}