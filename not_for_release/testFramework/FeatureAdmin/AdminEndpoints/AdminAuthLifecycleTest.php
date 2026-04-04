<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminAuthLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testPasswordForgottenPageIsReachable(): void
    {
        $this->getAdmin('/admin/index.php?cmd=password_forgotten')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Reset Password')
            ->assertSee('Admin Email Address');
    }

    public function testAdminAccountEmailCanBeUpdated(): void
    {
        $this->completeInitialAdminSetup();

        $page = $this->getAdmin('/admin/index.php?cmd=admin_account&action=edit')
            ->assertOk()
            ->assertSee('Admin Account');

        $response = $this->submitAdminForm($page, 'users', [
            'action' => 'update',
            'email' => 'updated-admin@example.com',
        ]);

        $response->assertOk()
            ->assertSee('Admin Account');

        $adminEmail = TestDb::selectValue(
            'SELECT admin_email FROM admin WHERE admin_id = 1 LIMIT 1'
        );

        $this->assertSame('updated-admin@example.com', (string) $adminEmail);
    }

    public function testPasswordForgottenSubmitReturnsToLoginRoute(): void
    {
        $page = $this->getAdmin('/admin/index.php?cmd=password_forgotten')
            ->assertOk()
            ->assertSee('Reset Password');

        $response = $this->postAdmin('/admin/password_forgotten.php', array_merge(
            $page->formDefaults('loginForm'),
            [
                'action' => 'update',
                'admin_email' => 'admin@localhost',
            ]
        ));

        $response->assertRedirect('cmd=login');
    }

    public function testAdminAccountPasswordValidationMessageIsShownForMismatch(): void
    {
        $this->completeInitialAdminSetup();
        $originalHash = (string) TestDb::selectValue(
            'SELECT admin_pass FROM admin WHERE admin_id = 1 LIMIT 1'
        );

        $page = $this->getAdmin('/admin/index.php?cmd=admin_account&action=password')
            ->assertOk()
            ->assertSee('Admin Account');

        $response = $this->submitAdminForm($page, 'users', [
            'action' => 'reset',
            'password' => 'Adminpass123',
            'confirm' => 'Mismatch123',
        ]);

        $response->assertOk()
            ->assertSee('Admin Account');

        $currentHash = (string) TestDb::selectValue(
            'SELECT admin_pass FROM admin WHERE admin_id = 1 LIMIT 1'
        );

        $this->assertSame($originalHash, $currentHash);
        $this->assertTrue(password_verify('password', $currentHash));
    }

    public function testAdminAccountPasswordCanBeUpdated(): void
    {
        $this->completeInitialAdminSetup();

        $page = $this->getAdmin('/admin/index.php?cmd=admin_account&action=password')
            ->assertOk()
            ->assertSee('Admin Account');

        $response = $this->submitAdminForm($page, 'users', [
            'action' => 'reset',
            'password' => 'Adminpass123',
            'confirm' => 'Adminpass123',
        ]);

        $response->assertOk()
            ->assertSee('Admin Account');

        $hashedPassword = TestDb::selectValue(
            'SELECT admin_pass FROM admin WHERE admin_id = 1 LIMIT 1'
        );

        $this->assertTrue(password_verify('Adminpass123', (string) $hashedPassword));
    }

    protected function completeInitialAdminSetup(): void
    {
        $this->visitAdminHome()
            ->assertOk()
            ->assertSee('Admin Login');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ])->assertOk()
            ->assertSee('Admin Home');
    }
}
