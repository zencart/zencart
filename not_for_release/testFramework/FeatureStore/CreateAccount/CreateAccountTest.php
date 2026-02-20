<?php

namespace Tests\FeatureStore\CreateAccount;

use Tests\Models\Customer;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcFeatureTestCaseStore;

class CreateAccountTest extends zcFeatureTestCaseStore
{
    use CustomerAccountConcerns;
    public function testCreateAccountNoDropdown()
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customer = Customer::where('customers_email_address', $profile['email_address'])->first();
        $this->assertEquals($profile['firstname'], $customer->customers_firstname);
        $ab = $customer->addressBooks->toArray();
        $this->assertEquals($profile['street_address'], $ab[0]['entry_street_address']);
        $this->assertEquals(18, $ab[0]['entry_zone_id']);
    }

    public function testCreateAccountWithDropdown()
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');
        $customer = Customer::where('customers_email_address', $profile['email_address'])->first();
        $this->assertEquals($profile['firstname'], $customer->customers_firstname);
        $ab = $customer->addressBooks->toArray();
        $this->assertEquals($profile['street_address'], $ab[0]['entry_street_address']);
        $this->assertEquals($profile['zone_id'], $ab[0]['entry_zone_id']);
    }
}
