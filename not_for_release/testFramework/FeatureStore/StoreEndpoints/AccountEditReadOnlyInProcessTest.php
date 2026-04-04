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
class AccountEditReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testLoggedInCustomerCanViewAccountEditPage(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic1');

        $this->getSslMainPage('account_edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('My Account Information')
            ->assertSee('firstname')
            ->assertSee('lastname');
    }
}
