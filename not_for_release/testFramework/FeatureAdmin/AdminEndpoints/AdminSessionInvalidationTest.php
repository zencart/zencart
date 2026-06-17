<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class AdminSessionInvalidationTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminSessionIsInvalidatedAfterOutOfBandPasswordChange(): void
    {
        $this->completeInitialAdminSetup();

        TestDb::update(
            'admin',
            ['admin_pass' => password_hash('Adminpass123', PASSWORD_DEFAULT)],
            'admin_id = :admin_id',
            [':admin_id' => 1]
        );

        $response = $this->getAdmin('/admin/index.php?cmd=admin_account');
        $response->assertRedirect('cmd=login');

        $this->followAdminRedirect($response)
            ->assertOk()
            ->assertSee('Your session has expired because your password was changed. Please login again.');
    }
}
