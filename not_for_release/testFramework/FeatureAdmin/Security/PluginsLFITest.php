<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\Security;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group serial
 * @group custom-seeder
 * @group plugin-filesystem
 * @group shared-db-write
 */
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

        TestDb::update(
            'plugin_control',
            ['status' => 1, 'version' => 'v3.0.3'],
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
