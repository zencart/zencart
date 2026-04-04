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
class PasswordResetReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testPasswordResetShowsInvalidTokenMessage(): void
    {
        $response = $this->getSsl('/index.php?main_page=password_reset&reset_token=definitely-invalid')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Your password cannot be reset at this time. Invalid or expired token.');

        self::assertStringNotContainsString('name="password_new"', $response->content);
    }
}
