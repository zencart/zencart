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
 */
class AddressBookManagementReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCustomerCanViewNewAddressBookEntryPage(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic2');

        $this->getSslMainPage('address_book_process')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('New Address Book Entry')
            ->assertSee('Street Address:');
    }
}
