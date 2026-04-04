<?php

namespace Tests\FeatureStore\CreateAccount;

use Tests\Support\Database\TestDb;
use Tests\Support\helpers\ProfileManager;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class CreateAccountTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCreateAccountNoDropdown(): void
    {
        $profile = ProfileManager::getProfile('florida-basic1');

        $response = $this->submitCreateAccountForm($profile)
            ->assertRedirect('main_page=create_account_success');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your Account Has Been Created!');

        $this->assertCustomerWasCreated($profile, 18);
    }

    public function testCreateAccountWithDropdown(): void
    {
        $profile = ProfileManager::getProfile('florida-basic2');

        $response = $this->submitCreateAccountForm($profile)
            ->assertRedirect('main_page=create_account_success');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your Account Has Been Created!');

        $this->assertCustomerWasCreated($profile, (int) $profile['zone_id']);
    }

    private function assertCustomerWasCreated(array $profile, int $expectedZoneId): void
    {
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
        $this->assertEquals($expectedZoneId, (int) $address['entry_zone_id']);
    }
}
