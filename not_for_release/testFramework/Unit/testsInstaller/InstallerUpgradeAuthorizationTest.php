<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsInstaller;

use Tests\Support\zcUnitTestCase;

class InstallerUpgradeAuthorizationTest extends zcUnitTestCase
{
    private array $versionArray = [
        '1.5.8' => ['required' => '1.5.7'],
        '2.0.0' => ['required' => '1.5.8'],
        '2.1.0' => ['required' => '2.0.0'],
        '2.2.0' => ['required' => '2.1.0'],
    ];

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'zc_install/includes/functions/general.php';
        $_SESSION = [];
    }

    public function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testUpgradeVersionsForDbVersionReturnsRemainingBatch(): void
    {
        $this->assertSame(
            ['2.0.0', '2.1.0', '2.2.0'],
            zc_install_upgrade_versions_for_db_version('1.5.8', $this->versionArray)
        );
    }

    public function testUpgradeVersionsForUnknownDbVersionReturnsNoBatch(): void
    {
        $this->assertSame([], zc_install_upgrade_versions_for_db_version('1.5.6', $this->versionArray));
    }

    public function testMissingAuthorizationFails(): void
    {
        $this->assertFalse(zc_install_is_upgrade_request_authorized('missing', '2.0.0'));
    }

    public function testValidAuthorizationAllowsOnlyAllowedVersions(): void
    {
        $nonce = zc_install_create_upgrade_authorization(1, '1.5.8', $this->versionArray);

        $this->assertNotSame('', $nonce);
        $this->assertTrue(zc_install_is_upgrade_request_authorized($nonce, '2.0.0'));
        $this->assertFalse(zc_install_is_upgrade_request_authorized($nonce, '1.5.8'));
    }

    public function testInvalidNonceFails(): void
    {
        zc_install_create_upgrade_authorization(1, '1.5.8', $this->versionArray);

        $this->assertFalse(zc_install_is_upgrade_request_authorized('invalid', '2.0.0'));
    }

    public function testExpiredAuthorizationFails(): void
    {
        $nonce = zc_install_create_upgrade_authorization(1, '1.5.8', $this->versionArray);
        $_SESSION[ZC_INSTALL_UPGRADE_AUTH_SESSION_KEY]['expires_at'] = time() - 1;

        $this->assertFalse(zc_install_is_upgrade_request_authorized($nonce, '2.0.0'));
        $this->assertArrayNotHasKey(ZC_INSTALL_UPGRADE_AUTH_SESSION_KEY, $_SESSION);
    }

    public function testAuthorizationDoesNotConsumeVersionsSoFailedStepsCanBeRetried(): void
    {
        $nonce = zc_install_create_upgrade_authorization(1, '1.5.8', $this->versionArray);

        $this->assertTrue(zc_install_is_upgrade_request_authorized($nonce, '2.0.0'));
        $this->assertTrue(zc_install_is_upgrade_request_authorized($nonce, '2.0.0'));
        $this->assertTrue(zc_install_is_upgrade_request_authorized($nonce, '2.1.0'));
    }
}
