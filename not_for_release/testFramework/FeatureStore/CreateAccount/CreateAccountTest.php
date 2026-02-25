<?php

namespace Tests\FeatureStore\CreateAccount;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcFeatureTestCaseStore;

class CreateAccountTest extends zcFeatureTestCaseStore
{
    use CustomerAccountConcerns;
    public function testCreateAccountNoDropdown()
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customer = TestDb::selectOne(
            'SELECT customers_id, customers_firstname FROM customers WHERE customers_email_address = :email LIMIT 1',
            [':email' => $profile['email_address']]
        );
        $this->assertNotNull($customer);
        $this->assertEquals($profile['firstname'], $customer['customers_firstname']);

        $address = TestDb::selectOne(
            'SELECT entry_street_address, entry_zone_id FROM address_book WHERE customers_id = :customer_id ORDER BY address_book_id ASC LIMIT 1',
            [':customer_id' => (int) $customer['customers_id']]
        );
        $this->assertNotNull($address);
        $this->assertEquals($profile['street_address'], $address['entry_street_address']);
        $this->assertEquals(18, (int) $address['entry_zone_id']);
    }

    public function testCreateAccountWithDropdown()
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');
        $customer = TestDb::selectOne(
            'SELECT customers_id, customers_firstname FROM customers WHERE customers_email_address = :email LIMIT 1',
            [':email' => $profile['email_address']]
        );
        $this->assertNotNull($customer);
        $this->assertEquals($profile['firstname'], $customer['customers_firstname']);

        $address = TestDb::selectOne(
            'SELECT entry_street_address, entry_zone_id FROM address_book WHERE customers_id = :customer_id ORDER BY address_book_id ASC LIMIT 1',
            [':customer_id' => (int) $customer['customers_id']]
        );
        $this->assertNotNull($address);
        $this->assertEquals($profile['street_address'], $address['entry_street_address']);
        $this->assertEquals((int) $profile['zone_id'], (int) $address['entry_zone_id']);
    }
}
