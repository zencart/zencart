<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsInstaller;

use Tests\Support\zcUnitTestCase;

class InstallerAdminSetupValidationTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'zc_install/includes/functions/general.php';
    }

    public function testEmptyDirectRequestFailsClosed(): void
    {
        $errors = zc_install_validate_admin_setup_request([]);

        $this->assertArrayHasKey('adminDir', $errors);
        $this->assertArrayHasKey('action', $errors);
        $this->assertArrayHasKey('physical_path', $errors);
    }

    public function testEmptyDirectEndpointDoesNotDiscloseDirectoryFields(): void
    {
        $response = $this->runInstallerEndpoint([PHP_BINARY, 'zc_install/ajaxAdminSetup.php']);

        $this->assertTrue($response['error']);
        $this->assertArrayNotHasKey('adminDir', $response);
        $this->assertArrayNotHasKey('adminNewDir', $response);
        $this->assertArrayNotHasKey('changedDir', $response);
    }

    public function testUnsafeAdminDirectoryFails(): void
    {
        $post = $this->validDirectorySetupPost();
        $post['adminDir'] = '../admin';

        $errors = zc_install_validate_admin_setup_request($post);

        $this->assertArrayHasKey('adminDir', $errors);
    }

    public function testValidDirectorySetupRequestPassesWithoutAdminUserFields(): void
    {
        $errors = zc_install_validate_admin_setup_request($this->validDirectorySetupPost());

        $this->assertSame([], $errors);
        $this->assertSame(
            ZC_INSTALL_ADMIN_SETUP_MODE_DIRECTORY,
            zc_install_admin_setup_request_mode($this->validDirectorySetupPost())
        );
    }

    public function testAdminUserSetupRequiresAdminFields(): void
    {
        $errors = zc_install_validate_admin_setup_request([
            'adminDir' => 'admin',
            'admin_user' => '',
            'admin_email' => 'store@example.com',
            'admin_email2' => 'other@example.com',
        ]);

        $this->assertArrayHasKey('admin_user', $errors);
        $this->assertArrayHasKey('admin_email2', $errors);
    }

    public function testValidAdminUserSetupPassesWithoutDatabaseFields(): void
    {
        $errors = zc_install_validate_admin_setup_request([
            'adminDir' => 'renamed-admin',
            'admin_user' => 'StoreOwner',
            'admin_email' => 'store@example.com',
            'admin_email2' => 'store@example.com',
        ]);

        $this->assertSame([], $errors);
        $this->assertSame(
            ZC_INSTALL_ADMIN_SETUP_MODE_ADMIN_USER,
            zc_install_admin_setup_request_mode([
                'adminDir' => 'renamed-admin',
                'admin_user' => 'StoreOwner',
                'admin_email' => 'store@example.com',
                'admin_email2' => 'store@example.com',
            ])
        );
    }

    public function testValidAdminUserEndpointDoesNotDiscloseDirectoryFields(): void
    {
        $code = <<<'PHP'
$_POST = [
    'adminDir' => 'admin',
    'admin_user' => 'StoreOwner',
    'admin_email' => 'store@example.com',
    'admin_email2' => 'store@example.com',
];
include 'zc_install/ajaxAdminSetup.php';
PHP;
        $response = $this->runInstallerEndpoint([PHP_BINARY, '-r', $code]);

        $this->assertFalse($response['error']);
        $this->assertArrayNotHasKey('adminDir', $response);
        $this->assertArrayNotHasKey('adminNewDir', $response);
        $this->assertArrayNotHasKey('changedDir', $response);
    }

    private function validDirectorySetupPost(): array
    {
        return [
            'action' => 'process',
            'adminDir' => 'admin',
            'physical_path' => DIR_FS_CATALOG,
            'http_server_admin' => 'http://example.test',
            'http_server_catalog' => 'http://example.test',
            'db_type' => 'mysql',
            'db_host' => '127.0.0.1',
            'db_user' => 'root',
            'db_name' => 'zencart',
            'sql_cache_method' => 'none',
        ];
    }

    private function runInstallerEndpoint(array $command): array
    {
        $process = proc_open(
            $command,
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            DIR_FS_CATALOG
        );
        $this->assertIsResource($process);

        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $this->assertSame(0, $exitCode, $errorOutput);
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded, $output);

        return $decoded;
    }
}
