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

    public function testAdminSessionWithoutBaselineHashRequiresReauthentication(): void
    {
        TestDb::update(
            'admin',
            ['admin_pass' => password_hash('password', PASSWORD_DEFAULT)],
            'admin_id = :admin_id',
            [':admin_id' => 1]
        );

        $this->completeInitialAdminSetup();
        $this->removeSessionValue('zenAdminID', 'admin_password_hash');

        $response = $this->getAdmin('/admin/index.php?cmd=admin_account');
        $response->assertRedirect('cmd=login');

        $this->followAdminRedirect($response)
            ->assertOk()
            ->assertSee('Your session has expired because your password was changed. Please login again.');
    }

    private function removeSessionValue(string $sessionCookieName, string $sessionKey): void
    {
        $sessionId = $this->cookies[$sessionCookieName] ?? '';
        $this->assertNotSame('', $sessionId);
        $encodedSession = (string) TestDb::selectValue(
            'SELECT value FROM sessions WHERE sesskey = :session_id',
            [':session_id' => $sessionId]
        );
        $this->assertNotSame('', $encodedSession);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        session_id(bin2hex(random_bytes(8)));
        session_start();
        session_decode(base64_decode($encodedSession));
        unset($_SESSION[$sessionKey]);
        $updatedSession = session_encode();
        session_write_close();
        session_id('');

        TestDb::update(
            'sessions',
            ['value' => base64_encode($updatedSession)],
            'sesskey = :session_id',
            [':session_id' => $sessionId]
        );
    }
}
