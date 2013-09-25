<?php
/**
 * File contains framework test cases
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
 /**
 * Load testing framework  (only needed on OLDER versions of PHPUnit.  With v3.5 and newer, the following 2 lines can be deleted:
 */
$bypassWarning = TRUE; // bypass PHPUnit/Framework warning error (works on edited localhost code ... will have to customize Bamboo to do the same if the next line can't be removed
if (file_exists('PHPUnit/Framework.php') && !file_exists('PHPUnit/Autoload.php')) require_once 'PHPUnit/Framework.php';
/**
 * Set up some prerequisites
 *
 */
define('TESTCWD', realpath(dirname(__FILE__)) . '/');
define('DIR_FS_CATALOG', realpath(dirname(__FILE__) . '/../../../'));
define('DIR_FS_INCLUDES', realpath(dirname(__FILE__) . '/../../../') . '/includes/');
define('CWD', DIR_FS_INCLUDES . '../');
define('DIR_WS_CLASSES', '/includes/classes/');
if (strpos(@ini_get('include_path'), '.') === false)
{
  @ini_set('include_path', '.' . PATH_SEPARATOR . @ini_get('include_path'));
}
/**
 * optional code
 */
if (file_exists(TESTCWD . 'localTestSetup.php')) require_once TESTCWD . 'localTestSetup.php';



/**
 * set up suite
 */
class utTestSuite {
    public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite('ZC Unit Tests');
    $suite->addTestSuite('testLibrary');
    return $suite;
  }
}

/**
 * Testing Library
 *
 */
class testLibrary extends PHPUnit_Framework_TestCase
{

  public function testExistsApplicationTop()
  {
    $this->assertEquals(file_exists(DIR_FS_INCLUDES . 'application_top.php'), TRUE);
  }

  public function skippedTestExecuteApplicationTop()
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
   * test whether email RFC tests are valid
   *
   */
  public function testEmailRfcValidation()
  {
    /**
     * set up prerequisites needed in order to use the function_email.php functions.
     */
    define('IS_ADMIN_FLAG', FALSE);
    require (DIR_FS_CATALOG . '/includes/classes/class.base.php');
    require (DIR_FS_CATALOG . '/includes/classes/class.notifier.php');
    global $zco_notifier;
    $zco_notifier = new notifier();
    require (DIR_FS_CATALOG . '/includes/functions/functions_email.php');

    /**
     * Set up test of email addresses to validate
     */
    $toTestAsValid = $toTestAsInvalid = array();
    $toTestAsValid[] = 'l3tt3rsAndNumb3rs@domain.com';
    $toTestAsValid[] = 'has-dash@domain.com';
    $toTestAsValid[] = "hasApostrophe.o'leary@domain.org";
    $toTestAsValid[] = 'uncommonTLD@domain.museum';
    $toTestAsValid[] = 'uncommonTLD@domain.travel';
    $toTestAsValid[] = 'uncommonTLD@domain.mobi';
    $toTestAsValid[] = 'countryCodeTLD@domain.uk';
    $toTestAsValid[] = 'countryCodeTLD@domain.rw';
    $toTestAsValid[] = 'lettersInDomain@911.com';
    $toTestAsValid[] = 'underscore_inLocal@domain.net';
    $toTestAsValid[] = 'IPInsteadOfDomain@127.0.0.1';
    $toTestAsValid[] = 'IPAndPort@127.0.0.1:25';
    $toTestAsValid[] = 'subdomain@sub.domain.com';
    $toTestAsValid[] = 'local@dash-inDomain.com';
    $toTestAsValid[] = 'dot.inLocal@foo.com';
    $toTestAsValid[] = 'a@singleLetterLocal.org';
    $toTestAsValid[] = 'singleLetterDomain@x.org';
    $toTestAsValid[] = "&*=?^+{}'~@validCharsInLocal.net";
    $toTestAsValid[] = 'foor@bar.newTLD';

    $toTestAsInvalid[] = 'missingDomain@.com';
    $toTestAsInvalid[] = '@missingLocal.org';
    $toTestAsInvalid[] = 'missingatSign.net';
    $toTestAsInvalid[] = 'missingDot@com';
    $toTestAsInvalid[] = 'two@@signs.com';
    $toTestAsInvalid[] = 'colonButMissingPort@127.0.0.1:';
    $toTestAsInvalid[] = '';
    $toTestAsInvalid[] = 'IPaddressRangeTooHigh@256.0.256.1';
    $toTestAsInvalid[] = 'invalidIP@127.0.0.1.26';
    $toTestAsInvalid[] = '.localStartsWithDot@domain.com';
    $toTestAsInvalid[] = 'localEndsWithDot.@domain.com';
    $toTestAsInvalid[] = 'two..consecutiveDots@domain.com';
    $toTestAsInvalid[] = 'domainStartsWithDash@-domain.com';
    $toTestAsInvalid[] = 'domainEndsWithDash@domain-.com';
    $toTestAsInvalid[] = 'numbersInTLD@domain.c0m';
    $toTestAsInvalid[] = 'missingTLD@domain.';
    $toTestAsInvalid[] = '! "#$%(),/;<>[]`|@invalidCharsInLocal.org';
    $toTestAsInvalid[] = 'invalidCharsInDomain@! "#$%(),/;<>_[]`|.org';
    $toTestAsInvalid[] = 'local@SecondLevelDomainNamesAreInvalidIfTheyAreLongerThan64Charactersss.org';
    $toTestAsInvalid[] = 'Ηλεκτρον�ργίουbc@domain.com.cy';

    foreach ($toTestAsValid as $emailAddress)
    {
      $result = zen_validate_email($emailAddress);
      $this->assertEquals($result, TRUE, 'This email failed but should be valid: ' . $emailAddress);
    }
    foreach ($toTestAsInvalid as $emailAddress)
    {
      $result = zen_validate_email($emailAddress);
      $this->assertEquals($result, FALSE, 'This email passed but should be invalid: ' . $emailAddress);
    }
  }


  /**
   * Test password entropy / duplication risks
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