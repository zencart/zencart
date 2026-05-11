<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class AdminAccountPagesTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminAccountPageIsReachableAfterInitialSetup(): void
    {
        $this->completeInitialAdminSetup();

        $this->visitAdminCommand('admin_account')
            ->assertOk()
            ->assertSee('Admin Account')
            ->assertSee('Pwd Last Change');
    }
}
