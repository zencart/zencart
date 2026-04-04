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
class AccountCommunicationPreferencesReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCustomerCanViewNewsletterPreferencesPage(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic1');

        $this->getSslMainPage('account_newsletters')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Newsletter Subscriptions')
            ->assertSee('General Newsletter');
    }

    public function testCustomerCanViewProductNotificationsPage(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic2');

        $this->getSslMainPage('account_notifications')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Product Notifications')
            ->assertSee('Product Notification');
    }
}
