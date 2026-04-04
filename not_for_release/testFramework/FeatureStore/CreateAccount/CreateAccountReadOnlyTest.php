<?php

namespace Tests\FeatureStore\CreateAccount;

use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class CreateAccountReadOnlyTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCreateAccountPageCanBeRendered(): void
    {
        $this->visitCreateAccount()
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Create an Account')
            ->assertSee('firstname')
            ->assertSee('lastname')
            ->assertSee('email_address');
    }
}
