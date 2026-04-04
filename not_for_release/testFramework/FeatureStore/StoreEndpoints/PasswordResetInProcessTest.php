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
class PasswordResetInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testPasswordResetShowsValidationErrorForMismatchedConfirmation(): void
    {
        $reset = $this->createCustomerAndIssueResetToken();

        $this->submitPasswordResetForm($reset['token'], [
            'password_new' => 'new-password-123',
            'password_confirmation' => 'different-password-123',
        ])->assertOk()
            ->assertSee('The Password Confirmation must match your new Password.');
    }

    public function testPasswordResetUpdatesPasswordClearsTokenAndAllowsLogin(): void
    {
        $reset = $this->createCustomerAndIssueResetToken();

        $response = $this->submitPasswordResetForm($reset['token'], [
            'password_new' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect('main_page=login');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your password has been successfully updated. Please login using your email address and the password you have just created.');

        $newHash = (string) TestDb::selectValue(
            'SELECT customers_password FROM customers WHERE customers_id = :customer_id LIMIT 1',
            [':customer_id' => $reset['customer_id']]
        );
        $tokenCount = (int) TestDb::selectValue(
            'SELECT COUNT(*) FROM customer_password_reset_tokens WHERE customer_id = :customer_id',
            [':customer_id' => $reset['customer_id']]
        );

        $this->assertNotSame($reset['old_hash'], $newHash);
        $this->assertTrue(password_verify('new-password-123', $newHash));
        $this->assertSame(0, $tokenCount);

        $login = $this->submitLoginForm([
            'email_address' => $reset['email_address'],
            'password' => 'new-password-123',
        ])->assertRedirect();

        $this->followRedirect($login)
            ->assertOk();
    }

    protected function createCustomerAndIssueResetToken(): array
    {
        $profile = $this->uniqueProfile('florida-basic1');

        $createAccount = $this->submitCreateAccountForm($profile)
            ->assertRedirect('main_page=create_account_success');
        $this->followRedirect($createAccount)
            ->assertOk()
            ->assertSee('Your Account Has Been Created!');

        $customer = TestDb::selectOne(
            'SELECT customers_id, customers_password
               FROM customers
              WHERE customers_email_address = :email
              LIMIT 1',
            [':email' => $profile['email_address']]
        );

        $this->assertNotNull($customer);

        $logoff = $this->visitLogoff();
        $logoffPage = $logoff->isRedirect() ? $this->followRedirect($logoff) : $logoff;
        $logoffPage->assertOk()
            ->assertSee('Log Off');

        $forgotten = $this->submitPasswordForgottenForm([
            'email_address' => $profile['email_address'],
        ])->assertRedirect('main_page=login');

        $this->followRedirect($forgotten)
            ->assertOk()
            ->assertSee('Thank you. If that email address is in our system, we will send password recovery instructions');

        $token = (string) TestDb::selectValue(
            'SELECT token
               FROM customer_password_reset_tokens
              WHERE customer_id = :customer_id
              ORDER BY created_at DESC
              LIMIT 1',
            [':customer_id' => $customer['customers_id']]
        );

        $this->assertNotSame('', $token);

        return [
            'customer_id' => (int) $customer['customers_id'],
            'email_address' => $profile['email_address'],
            'old_hash' => (string) $customer['customers_password'],
            'token' => $token,
        ];
    }

    protected function submitPasswordResetForm(string $token, array $data = [])
    {
        $page = $this->getSsl('/index.php?main_page=password_reset&reset_token=' . urlencode($token))
            ->assertOk();

        $formAction = $page->formAction('account_password') ?? '/index.php?main_page=password_reset';

        return $this->postSsl(
            $this->normalizeRelativeUri($formAction),
            array_merge($page->formDefaults('account_password'), $data)
        );
    }

    protected function uniqueProfile(string $profileName): array
    {
        $profile = ProfileManager::getProfile($profileName);

        [$localPart, $domainPart] = explode('@', $profile['email_address'], 2);
        $profile['email_address'] = sprintf('%s+reset%s@%s', $localPart, uniqid('', true), $domainPart);

        return $profile;
    }
}
