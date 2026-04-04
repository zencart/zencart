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
class AccountEditInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testLoggedInCustomerCanUpdateAccountDetails(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');
        $page = $this->getSslMainPage('account_edit')->assertOk();

        $response = $this->postSslMainPage('account_edit', array_merge(
            $page->formDefaults('account_edit'),
            [
                'action' => 'process',
                'firstname' => 'UpdatedFirst',
                'lastname' => 'UpdatedLast',
                'email_address' => $profile['email_address'],
                'telephone' => '5551234567',
                'fax' => '',
                'email_format' => 'TEXT',
            ]
        ));

        $response->assertRedirect('main_page=account');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your account has been successfully updated.');

        $customer = TestDb::selectOne(
            'SELECT customers_firstname, customers_lastname, customers_telephone FROM customers WHERE customers_email_address = :email LIMIT 1',
            [':email' => $profile['email_address']]
        );

        $this->assertNotNull($customer);
        $this->assertSame('UpdatedFirst', $customer['customers_firstname']);
        $this->assertSame('UpdatedLast', $customer['customers_lastname']);
        $this->assertSame('5551234567', $customer['customers_telephone']);
    }
}
