<?php
/**
 * File contains framework test cases
 *
 * @package tests
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * Testing Library
 */
class testLibraryMain extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    require_once('zcCommonTestResources.php');
  }

  public function testExistsApplicationTop()
  {
    $this->assertEquals(file_exists(DIR_FS_INCLUDES . 'application_top.php'), TRUE);
  }
  /**
   * test whether email RFC tests are valid
   */
  public function testEmailRfcValidation()
  {
    /**
     * set up prerequisites needed in order to use the function_email.php functions.
     */
    global $zco_notifier;
    $zco_notifier = new notifier();
    require (DIR_FS_CATALOG . '/includes/functions/functions_email.php');

    /**
     * Set up test of email addresses to validate
     */
    $toTestAsValid = $toTestAsInvalid = array();
    $toTestAsValid [] = 'l3tt3rsAndNumb3rs@domain.com';
    $toTestAsValid [] = 'has-dash@domain.com';
    $toTestAsValid [] = "hasApostrophe.o'leary@domain.org";
    $toTestAsValid [] = 'uncommonTLD@domain.museum';
    $toTestAsValid [] = 'uncommonTLD@domain.travel';
    $toTestAsValid [] = 'uncommonTLD@domain.mobi';
    $toTestAsValid [] = 'countryCodeTLD@domain.uk';
    $toTestAsValid [] = 'countryCodeTLD@domain.rw';
    $toTestAsValid [] = 'lettersInDomain@911.com';
    $toTestAsValid [] = 'underscore_inLocal@domain.net';
    $toTestAsValid [] = 'IPInsteadOfDomain@127.0.0.1';
    $toTestAsValid [] = 'IPAndPort@127.0.0.1:25';
    $toTestAsValid [] = 'subdomain@sub.domain.com';
    $toTestAsValid [] = 'local@dash-inDomain.com';
    $toTestAsValid [] = 'dot.inLocal@foo.com';
    $toTestAsValid [] = 'a@singleLetterLocal.org';
    $toTestAsValid [] = 'singleLetterDomain@x.org';
    $toTestAsValid [] = "&*=?^+{}'~@validCharsInLocal.net";
    $toTestAsValid [] = 'foor@bar.newTLD';

    $toTestAsInvalid [] = 'missingDomain@.com';
    $toTestAsInvalid [] = '@missingLocal.org';
    $toTestAsInvalid [] = 'missingatSign.net';
    $toTestAsInvalid [] = 'missingDot@com';
    $toTestAsInvalid [] = 'two@@signs.com';
    $toTestAsInvalid [] = 'colonButMissingPort@127.0.0.1:';
    $toTestAsInvalid [] = '';
    $toTestAsInvalid [] = 'IPaddressRangeTooHigh@256.0.256.1';
    $toTestAsInvalid [] = 'invalidIP@127.0.0.1.26';
    $toTestAsInvalid [] = '.localStartsWithDot@domain.com';
    $toTestAsInvalid [] = 'localEndsWithDot.@domain.com';
    $toTestAsInvalid [] = 'two..consecutiveDots@domain.com';
    $toTestAsInvalid [] = 'domainStartsWithDash@-domain.com';
    $toTestAsInvalid [] = 'domainEndsWithDash@domain-.com';
    $toTestAsInvalid [] = 'numbersInTLD@domain.c0m';
    $toTestAsInvalid [] = 'missingTLD@domain.';
    $toTestAsInvalid [] = '! "#$%(),/;<>[]`|@invalidCharsInLocal.org';
    $toTestAsInvalid [] = 'invalidCharsInDomain@! "#$%(),/;<>_[]`|.org';
    $toTestAsInvalid [] = 'local@SecondLevelDomainNamesAreInvalidIfTheyAreLongerThan64Charactersss.org';
    $toTestAsInvalid [] = 'Ηλεκτρον�ργίουbc@domain.com.cy';

    foreach ( $toTestAsValid as $emailAddress ) {
      $result = zen_validate_email($emailAddress);
      $this->assertEquals($result, TRUE, 'This email failed but should be valid: ' . $emailAddress);
    }
    foreach ( $toTestAsInvalid as $emailAddress ) {
      $result = zen_validate_email($emailAddress);
      $this->assertEquals($result, FALSE, 'This email passed but should be invalid: ' . $emailAddress);
    }
  }

  /**
   * Test password entropy / duplication risks
   */
  public function testPasswordGeneration()
  {
    $passwordList = array();
    $loopCount = 10000;
    if (defined('BIG_LOOPS_BYPASS'))
      $loopCount = 100;
    for($i = 0; $i < $loopCount; $i ++) {
      $password = zen_create_PADSS_password();
      if (isset($passwordList [$password])) {
        $this->fail('Duplicate Password ');
      }
      $passwordList [$password] = $password;
    }
  }
}