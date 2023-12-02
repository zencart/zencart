<?php

namespace Tests\Feature\TestCreateAccount;

use App\Models\Configuration;
use App\Models\Customer;
use Tests\Support\ProfileManager;
use Tests\Support\zcFeatureTestCaseStore;

class CreateAccountTest extends zcFeatureTestCaseStore
{
    public function testCreateAccountNoDropdown()
    {
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=create_account');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->browser->submitForm('Submit the Information', $profile);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Your Account Has Been Created', (string)$response->getContent() );
        $customer = Customer::where('customers_email_address', $profile['email_address'])->first();
        $this->assertEquals($profile['firstname'], $customer->customers_firstname);
        $ab = $customer->addressBooks->toArray();
        $this->assertEquals($profile['street_address'], $ab[0]['entry_street_address']);
        $this->assertEquals(0, $ab[0]['entry_zone_id']);
    }

    public function testCreateAccountWithDropdown()
    {
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=create_account');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $profile = ProfileManager::getProfile('florida-basic2');
        $this->browser->submitForm('Submit the Information', $profile);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Your Account Has Been Created', (string)$response->getContent() );
        $customer = Customer::where('customers_email_address', $profile['email_address'])->first();
        $this->assertEquals($profile['firstname'], $customer->customers_firstname);
        $ab = $customer->addressBooks->toArray();
        $this->assertEquals($profile['street_address'], $ab[0]['entry_street_address']);
        $this->assertEquals($profile['zone_id'], $ab[0]['entry_zone_id']);
    }
}
