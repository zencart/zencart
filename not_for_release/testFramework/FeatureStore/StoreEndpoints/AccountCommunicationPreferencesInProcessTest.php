<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class AccountCommunicationPreferencesInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCustomerCanUpdateNewsletterSubscription(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $page = $this->getSslMainPage('account_newsletters')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Newsletter Subscriptions')
            ->assertSee('General Newsletter');

        $response = $this->postSslMainPage('account_newsletters', array_merge(
            $page->formDefaults('account_newsletter'),
            [
                'action' => 'process',
                'newsletter_general' => '0',
            ]
        ));

        $response->assertRedirect('main_page=account_newsletters');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your newsletter subscriptions have been updated.');

        $newsletterSetting = TestDb::selectValue(
            'SELECT customers_newsletter FROM customers WHERE customers_id = :customer_id LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertSame('0', (string) $newsletterSetting);
    }

    public function testCustomerCanRemoveExistingProductNotification(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        TestDb::insert('products_notifications', [
            'products_id' => 2,
            'customers_id' => $customerId,
            'date_added' => '2026-01-01 00:00:00',
        ]);

        $page = $this->getSslMainPage('account_notifications')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Product Notifications')
            ->assertSee('Product Notifications')
            ->assertSee('Product Notification');

        $response = $this->postSslMainPage('account_notifications', array_merge(
            $page->formDefaults('account_notifications'),
            [
                'action' => 'process',
            ]
        ));

        $response->assertRedirect('main_page=account');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your product notifications have been updated.');

        $remainingNotifications = TestDb::selectValue(
            'SELECT COUNT(*) FROM products_notifications WHERE customers_id = :customer_id',
            [':customer_id' => $customerId]
        );

        $this->assertSame('0', (string) $remainingNotifications);
    }

    public function testCustomerCanEnableGlobalProductNotifications(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        TestDb::update(
            'customers_info',
            ['global_product_notifications' => 0],
            'customers_info_id = :customer_id',
            [':customer_id' => $customerId]
        );

        $page = $this->getSslMainPage('account_notifications')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Product Notifications')
            ->assertSee('Receive notifications');

        $response = $this->postSslMainPage('account_notifications', array_merge(
            $page->formDefaults('account_notifications'),
            [
                'action' => 'process',
                'product_global' => '1',
            ]
        ));

        $response->assertRedirect('main_page=account');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your product notifications have been updated.');

        $globalSetting = TestDb::selectValue(
            'SELECT global_product_notifications FROM customers_info WHERE customers_info_id = :customer_id LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertSame('1', (string) $globalSetting);
    }
}
