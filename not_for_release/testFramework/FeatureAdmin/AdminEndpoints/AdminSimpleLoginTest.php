<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminSimpleLoginTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testSimpleAdmin()
    {
        $this->visitAdminHome()
            ->assertOk()
            ->assertSee('Admin Login');

        $response = $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk();

        $response->assertSee('Initial Setup Wizard');
    }

    public function testInitialLogin()
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

        $response = $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ])->assertOk();

        $response->assertSee('Admin Home');
    }

    public function testAdminLoginAcceptsHeaderOnlyCsrfToken(): void
    {
        $page = $this->visitAdminHome()
            ->assertOk()
            ->assertSee('Admin Login');

        $formData = $page->formDefaults('loginForm');
        $securityToken = $formData['securityToken'] ?? null;

        $this->assertNotNull($securityToken);
        unset($formData['securityToken']);

        $response = $this->postAdmin(
            $page->formAction('loginForm') ?? '/admin/index.php',
            array_merge($formData, [
                'admin_name' => 'Admin',
                'admin_pass' => 'password',
            ]),
            ['HTTP_X_CSRF_TOKEN' => $securityToken]
        );

        $response = $response->isRedirect() ? $this->followAdminRedirect($response) : $response;
        $response->assertOk();

        $response->assertSee('Initial Setup Wizard');
    }

}
