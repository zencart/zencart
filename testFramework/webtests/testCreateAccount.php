<?php
/**
 * File contains create account tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testCreateAccount.php 18983 2011-06-26 18:41:27Z wilt $
 */
/**
 *
 * @package tests
 */
class testCreateAccount extends zcCommonTestResources
{
  public function testCreateAccountDo()
  {
    $this->open('http://' . BASE_URL);
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!, The Art of E-commerce');
    $this->assertTextPresent('glob:*'. WEBTEST_STORE_NAME . '*');
    $this->clickAndWait('link=Log In');
    $this->assertTextPresent('glob:*Welcome, Please Sign In*');
    $this->click('gender-male');
    $this->type('firstname', 'Tom');
    $this->type('lastname', 'Bombadil');
    $this->type('street_address', '999 Some Street');
    $this->type('city', 'Miami');
    $this->type('state', 'Florida');
    $this->type('postcode', '33133');
    $this->select('zone_country_id', 'value=223');
    $this->type('telephone', '+441202010109382');
    $this->type('dob', '05/21/1970');
    $this->type("document.forms[3].elements['email_address']", WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type("document.forms[3].elements['password']", WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->type('confirmation', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
//    $this->captureEntirePageScreenshot(SCREENSHOT_PATH . 'testCreateAccountDo.png');
    $this->submit("document.forms[3]");
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Your Account Has Been Created*');
  }
  public function testCreateAccountUKDo()
  {
    $this->open('http://' . BASE_URL);
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!, The Art of E-commerce');
    $this->assertTextPresent('glob:*'. WEBTEST_STORE_NAME . '*');
    $this->clickAndWait('link=Log In');
    $this->assertTextPresent('glob:*Welcome, Please Sign In*');
    $this->click('gender-male');
    $this->type('firstname', 'UK Tom');
    $this->type('lastname', 'Bombadil');
    $this->type('street_address', '999 Some Street');
    $this->type('city', 'Newcastle');
    $this->type('state', 'Tyne & Wear');
    $this->type('postcode', '33133');
    $this->select('zone_country_id', 'value=222');
    $this->type('telephone', '+441202010109382');
    $this->type('dob', '05/21/1970');
    $this->type("document.forms[3].elements['email_address']", WEBTEST_UK_CUSTOMER_EMAIL);
    $this->type("document.forms[3].elements['password']", WEBTEST_UK_CUSTOMER_PASSWORD);
    $this->type('confirmation', WEBTEST_UK_CUSTOMER_PASSWORD);
//    $this->captureEntirePageScreenshot(SCREENSHOT_PATH . 'testCreateAccountUKDo.png');
    $this->submit("document.forms[3]");
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Your Account Has Been Created*');
  }
  public function testCreateAccountCanadaDo()
  {
    $this->open('http://' . BASE_URL);
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!, The Art of E-commerce');
    $this->assertTextPresent('glob:*'. WEBTEST_STORE_NAME . '*');
    $this->clickAndWait('link=Log In');
    $this->assertTextPresent('glob:*Welcome, Please Sign In*');
    $this->click('gender-male');
    $this->type('firstname', 'Canada Tom');
    $this->type('lastname', 'Bombadil');
    $this->type('street_address', '999 Some Street');
    $this->type('city', 'Toronto');
    $this->type('state', 'Ontario');
    $this->type('postcode', '33133');
    $this->select('zone_country_id', 'value=38');
    $this->type('telephone', '+441202010109382');
    $this->type('dob', '05/21/1970');
    $this->type("document.forms[3].elements['email_address']", WEBTEST_CANADA_CUSTOMER_EMAIL);
    $this->type("document.forms[3].elements['password']", WEBTEST_CANADA_CUSTOMER_PASSWORD);
    $this->type('confirmation', WEBTEST_CANADA_CUSTOMER_PASSWORD);
    $this->submit("document.forms[3]");
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Your Account Has Been Created*');
  }
  public function testCreateAccountFailDo()
  {
    $this->open('http://' . BASE_URL);
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!, The Art of E-commerce');
    $this->assertTextPresent('glob:*'. WEBTEST_STORE_NAME . '*');
    $this->clickAndWait('link=Log In');
    $this->assertTextPresent('glob:*Welcome, Please Sign In*');
    $this->click('gender-male');
    $this->type('firstname', 'Tom');
    $this->type('lastname', 'Bombadil');
    $this->type('street_address', '999 Some Street');
    $this->type('city', 'Miami');
    $this->type('state', 'Floritzia');
    $this->type('postcode', '33133');
    $this->select('zone_country_id', 'value=223');
    $this->type('telephone', '+441202010109382');
    $this->type('dob', '05/21/1970');
    $this->type("document.forms[3].elements['email_address']", WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type("document.forms[3].elements['password']", WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
//    $this->captureEntirePageScreenshot(SCREENSHOT_PATH . 'testCreateAccountFailDo.png');
    $this->type('confirmation', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit("document.forms[3]");
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Please select a state*');

    $this->open('http://' . BASE_URL);
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!, The Art of E-commerce');
    $this->assertTextPresent('glob:*'. WEBTEST_STORE_NAME . '*');
    $this->clickAndWait('link=Log In');
    $this->assertTextPresent('glob:*Welcome, Please Sign In*');
    $this->click('gender-male');
    $this->type('lastname', 'Wilson');
    $this->type('street_address', '999 Some Street');
    $this->type('city', 'Miami');
    $this->type('state', 'Floritzia');
    $this->type('postcode', '33133');
    $this->select('zone_country_id', 'value=223');
    $this->type('telephone', '+441202010109382');
    $this->type('dob', '05/21/1970');
    $this->type("document.forms[3].elements['email_address']", WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type("document.forms[3].elements['password']", WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->type('confirmation', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
//    $this->captureEntirePageScreenshot(SCREENSHOT_PATH . 'testCreateAccountDoAlert.png');
    $this->submit("document.forms[3]");
    $this->assertAlert();
  }
}
