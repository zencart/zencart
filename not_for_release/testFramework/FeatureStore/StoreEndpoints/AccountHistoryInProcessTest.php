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
class AccountHistoryInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testGuestIsRedirectedToLoginForAccountHistory(): void
    {
        $this->getSslMainPage('account_history')
            ->assertRedirect('main_page=login');
    }

    public function testLoggedInCustomerWithNoOrdersSeesEmptyAccountHistory(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic2');

        $this->getSslMainPage('account_history')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Order History')
            ->assertSee('You have not yet made any purchases.');
    }

    public function testInvalidAccountHistoryInfoRedirectsToAccountHistory(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic1');

        $response = $this->getSsl('/index.php?main_page=account_history_info&order_id=999999');

        $response->assertRedirect('main_page=account_history');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Order History');
    }
}
