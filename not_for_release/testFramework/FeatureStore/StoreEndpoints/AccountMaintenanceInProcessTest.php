<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 * @group customer-account-write
 */
class AccountMaintenanceInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testLoggedInCustomerCanReachAccountPasswordPage(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic1');

        $this->getSslMainPage('account_password')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('My Password')
            ->assertSee('Change Password');
    }

    public function testLoggedInCustomerCanReachAddressBookPage(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');

        $this->getSslMainPage('address_book')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('My Personal Address Book')
            ->assertSee('Address Book Entries')
            ->assertSee($profile['street_address']);
    }
}
