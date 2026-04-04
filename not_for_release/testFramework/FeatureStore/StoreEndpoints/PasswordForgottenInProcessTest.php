<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\helpers\ProfileManager;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class PasswordForgottenInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testPasswordForgottenCreatesResetTokenForKnownCustomer(): void
    {
        $profile = ProfileManager::getProfile('florida-basic1');

        $createAccount = $this->submitCreateAccountForm($profile)
            ->assertRedirect('main_page=create_account_success');
        $this->followRedirect($createAccount)
            ->assertOk()
            ->assertSee('Your Account Has Been Created!');

        $customerId = (int) TestDb::selectValue(
            'SELECT customers_id FROM customers WHERE customers_email_address = :email LIMIT 1',
            [':email' => $profile['email_address']]
        );

        $this->assertGreaterThan(0, $customerId);

        $logoff = $this->visitLogoff();
        $logoffPage = $logoff->isRedirect() ? $this->followRedirect($logoff) : $logoff;
        $logoffPage->assertOk()
            ->assertSee('Log Off');

        $response = $this->submitPasswordForgottenForm([
            'email_address' => $profile['email_address'],
        ])->assertRedirect('main_page=login');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Thank you. If that email address is in our system, we will send password recovery instructions');

        $tokenRecord = TestDb::selectOne(
            'SELECT customer_id, token
               FROM customer_password_reset_tokens
              WHERE customer_id = :customer_id
              ORDER BY created_at DESC
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($tokenRecord);
        $this->assertSame((string) $customerId, (string) $tokenRecord['customer_id']);
        $this->assertNotSame('', (string) $tokenRecord['token']);
    }
}
