<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\Security;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[\PHPUnit\Framework\Attributes\Group('serial')]
#[\PHPUnit\Framework\Attributes\Group('custom-seeder')]
#[\PHPUnit\Framework\Attributes\Group('plugin-filesystem')]
#[\PHPUnit\Framework\Attributes\Group('shared-db-write')]
class PluginsLFITest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testPluginLFI()
    {
        // note probably need to make the login a separate method
        $this->runCustomSeeder('StoreWizardSeeder');
        $this->runCustomSeeder('DisplayLogsSeeder');

        $this->visitAdminHome()
            ->assertOk()
            ->assertSee('Admin Login');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');

        $this->visitAdminCommand('plugin_manager')->assertOk();

        $displayLogsPluginRoot = ROOTCWD . 'zc_plugins/DisplayLogs';
        $this->assertDirectoryExists($displayLogsPluginRoot, 'Display Logs plugin directory not found.');

        $displayLogsVersions = [];
        foreach (new \DirectoryIterator($displayLogsPluginRoot) as $pluginEntry) {
            if (!$pluginEntry->isDir() || $pluginEntry->isDot()) {
                continue;
            }

            $manifestPath = $pluginEntry->getPathname() . '/manifest.php';
            if (!is_file($manifestPath)) {
                continue;
            }

            $displayLogsVersions[] = [
                'directory_version' => $pluginEntry->getBasename(),
                'manifest_path' => $manifestPath,
            ];
        }

        $this->assertNotSame([], $displayLogsVersions, 'Display Logs manifest not found.');

        usort($displayLogsVersions, static function (array $left, array $right): int {
            return version_compare($right['directory_version'], $left['directory_version']);
        });

        $displayLogsManifest = require $displayLogsVersions[0]['manifest_path'];
        $displayLogsVersion = (string) ($displayLogsManifest['pluginVersion'] ?? '');

        $this->assertNotSame('', $displayLogsVersion, 'Display Logs manifest did not provide a pluginVersion.');

        TestDb::update(
            'plugin_control',
            [
                'status' => 1,
                'version' => $displayLogsVersion,
            ],
            'name = :name',
            [':name' => 'Display Logs']
        );

        $this->visitAdminCommand('display_logs')
            ->assertOk()
            ->assertSee('Admin Display Logs');

        $dir = 'includes/';
        touch($dir . 'security_test.php');
        file_put_contents($dir . 'security_test.php', "<?php\ndie('lfi-vulnerable');\n");

        try {
            $response = $this->visitAdminCommand('../../../../includes/security_test')
                ->assertRedirect();

            $response = $this->followAdminRedirect($response)->assertOk();

            $this->assertStringNotContainsString('lfi-vulnerable', $response->content);
            $response->assertSee('Admin Home');

            $this->visitAdminCommand('display_logs')
                ->assertOk()
                ->assertSee('Admin Display Logs');
        } finally {
            @unlink($dir . 'security_test.php');
        }
    }
}
