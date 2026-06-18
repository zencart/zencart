<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\helpers\ProfileManager;
use Tests\Support\zcInProcessFeatureTestCaseStore;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
#[\PHPUnit\Framework\Attributes\Group('customer-account-write')]
class AccountSessionInvalidationInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCustomerSessionIsInvalidatedAfterOutOfBandPasswordChange(): void
    {
        $profile = ProfileManager::getProfileForLogin('florida-basic2');
        $this->createCustomerAccountOrLogin('florida-basic2');
        $customerId = (int) TestDb::selectValue(
            'SELECT customers_id FROM customers WHERE customers_email_address = :email_address',
            [':email_address' => $profile['email_address']]
        );

        TestDb::insert('customers_basket', [
            'customers_id' => $customerId,
            'products_id' => '3',
            'customers_basket_quantity' => 2,
            'customers_basket_date_added' => date('Ymd'),
        ]);

        TestDb::update(
            'customers',
            ['customers_password' => password_hash('updated-password-789', PASSWORD_DEFAULT)],
            'customers_email_address = :email_address',
            [':email_address' => $profile['email_address']]
        );

        $response = $this->getSslMainPage('account');
        $response->assertRedirect('main_page=login');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your session has expired because your password was changed. Please login again.');

        $savedCartRows = (int) TestDb::selectValue(
            'SELECT COUNT(*) FROM customers_basket WHERE customers_id = :customer_id',
            [':customer_id' => $customerId]
        );

        $this->assertSame(1, $savedCartRows);
    }
}
