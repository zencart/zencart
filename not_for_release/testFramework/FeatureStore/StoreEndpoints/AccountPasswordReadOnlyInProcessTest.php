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
class AccountPasswordReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testWrongCurrentPasswordShowsValidationMessage(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic1');
        $page = $this->getSslMainPage('account_password')->assertOk();

        $this->postSslMainPage('account_password', array_merge(
            $page->formDefaults('account_password'),
            [
                'action' => 'process',
                'password_current' => 'not-the-right-password',
                'password_new' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]
        ))->assertOk()
            ->assertSee('Your Current Password did not match the password in our records. Please try again.');
    }
}
