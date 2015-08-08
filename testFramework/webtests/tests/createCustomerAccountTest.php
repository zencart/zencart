<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

/**
 * Class createCustomerAccountTest
 */
class createCustomerAccountTest extends CommonTestResources
{
    public function testCreateAccountUS()
    {
        $this->url('http://' . BASE_URL);
        $this->byLinkText('Log In')->click();
        $this->assertTextPresent('Welcome, Please Sign In');
        $this->byId('gender-male')->click();
        $this->byId('firstname')->value('Tom');
        $this->byId('lastname')->value('Bombadil');
        $this->byId('street-address')->value('999 Some Street');
        $this->byId('city')->value('Miami');
        $this->byId('state')->value('Florida');
        $this->byId('postcode')->value('33133');
        $this->byId('telephone')->value('+441202010109382');
        $this->select($this->byId('country'))->selectOptionByValue(223);
        $this->byId('dob')->value('05/21/1970');
        $this->byId('email-address')->value(WEBTEST_DEFAULT_CUSTOMER_EMAIL);
        $this->byId('password-new')->value(WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->byId('password-confirm')->value(WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->byCss('#createAccountForm > div.buttonRow.forward > input[type="image"]')->click();
        $this->assertTextPresent('Your Account Has Been Created');
    }

    public function testCreateAccountUK()
    {
        $this->url('http://' . BASE_URL);
        $this->byLinkText('Log In')->click();
        $this->assertTextPresent('Welcome, Please Sign In');
        $this->byId('gender-male')->click();
        $this->byId('firstname')->value('UK Tom');
        $this->byId('lastname')->value('Bombadil');
        $this->byId('street-address')->value('999 Some Street');
        $this->byId('city')->value('Newcastle');
        $this->byId('state')->value('Tyne & Wear');
        $this->byId('postcode')->value('NE11AA');
        $this->byId('telephone')->value('+441202010109382');
        $this->select($this->byId('country'))->selectOptionByValue(222);
        $this->byId('dob')->value('05/21/1970');
        $this->byId('email-address')->value(WEBTEST_UK_CUSTOMER_EMAIL);
        $this->byId('password-new')->value(WEBTEST_UK_CUSTOMER_PASSWORD);
        $this->byId('password-confirm')->value(WEBTEST_UK_CUSTOMER_PASSWORD);
        $this->byCss('#createAccountForm > div.buttonRow.forward > input[type="image"]')->click();
        $this->assertTextPresent('Your Account Has Been Created');
    }

    public function testCreateAccountCanada()
    {
        $this->url('http://' . BASE_URL);
        $this->byLinkText('Log In')->click();
        $this->assertTextPresent('Welcome, Please Sign In');
        $this->byId('gender-male')->click();
        $this->byId('firstname')->value('Canada Tom');
        $this->byId('lastname')->value('Bombadil');
        $this->byId('street-address')->value('999 Some Street');
        $this->byId('city')->value('Toronto');
        $this->byId('state')->value('Ontario');
        $this->byId('postcode')->value('NE11AA');
        $this->byId('telephone')->value('+441202010109382');
        $this->select($this->byId('country'))->selectOptionByValue(38);
        $this->byId('dob')->value('05/21/1970');
        $this->byId('email-address')->value(WEBTEST_CANADA_CUSTOMER_EMAIL);
        $this->byId('password-new')->value(WEBTEST_CANADA_CUSTOMER_PASSWORD);
        $this->byId('password-confirm')->value(WEBTEST_CANADA_CUSTOMER_PASSWORD);
        $this->byCss('#createAccountForm > div.buttonRow.forward > input[type="image"]')->click();
        $this->assertTextPresent('Your Account Has Been Created');
    }
}
