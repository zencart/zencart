<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class PasswordForgottenReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testPasswordForgottenPageCanBeRenderedInProcess(): void
    {
        $this->visitPasswordForgotten()
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Forgotten Password');
    }

    public function testPasswordForgottenShowsValidationErrorForEmptyEmail(): void
    {
        $response = $this->submitPasswordForgottenForm()
            ->assertRedirect('main_page=password_forgotten');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Forgotten Password')
            ->assertSee('Is your email address correct?');
    }

    public function testPasswordForgottenRedirectsToLoginAfterSubmission(): void
    {
        $response = $this->submitPasswordForgottenForm(
            [
                'email_address' => 'test@example.com',
            ]
        )->assertRedirect('main_page=login');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Thank you. If that email address is in our system, we will send password recovery instructions');
    }
}
