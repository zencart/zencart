<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\helpers\ProfileManager;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class AccountPasswordInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCustomerCanChangePasswordAndLoginWithNewPassword(): void
    {
        $profile = ProfileManager::getProfileForLogin('florida-basic2');
        $this->createCustomerAccountOrLogin('florida-basic2');
        $page = $this->getSslMainPage('account_password')->assertOk();

        $response = $this->postSslMainPage('account_password', array_merge(
            $page->formDefaults('account_password'),
            [
                'action' => 'process',
                'password_current' => $profile['password'],
                'password_new' => 'new-password-456',
                'password_confirmation' => 'new-password-456',
            ]
        ));

        $response->assertRedirect('main_page=account');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your password has been successfully updated.');

        $logoffResponse = $this->visitLogoff();
        if ($logoffResponse->isRedirect()) {
            $logoffResponse = $this->followRedirect($logoffResponse);
        }

        $logoffResponse
            ->assertOk()
            ->assertSee('Log Off');

        $this->submitLoginForm([
            'email_address' => $profile['email_address'],
            'password' => 'new-password-456',
        ])->assertRedirect();
    }
}
