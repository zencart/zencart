<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\PluginTests;

use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group serial
 * @group custom-seeder
 * @group plugin-filesystem
 */
class BasicPluginInstallTest extends zcInProcessFeatureTestCaseAdmin
{
    public const TEST_PLUGIN_NAME = 'zenTestPlugin';
    public const TEST_PLUGIN_VERSION = 'v1.0.0';

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testInstallPlugin(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');
        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');

        $this->installPluginToFilesystem(self::TEST_PLUGIN_NAME, self::TEST_PLUGIN_VERSION);
        $this->visitAdminCommand('plugin_manager')->assertOk();

        $response = $this->visitAdminCommand('plugin_manager&page=1&colKey=zenTestPlugin&action=install')
            ->assertOk()
            ->assertSee('Test plugin for testing purposes');

        $response = $this->submitAdminForm($response, 'plugininstall')
            ->assertOk();

        $response->assertSee('Version Installed:</strong> v1.0.0');

        $response = $this->visitAdminCommand('zen_test_plugin')
            ->assertOk();

        $response->assertSee('Test plugin for testing purposes');
        $response->assertSee('Help on Zen Cart Documentation Site');
        $response->assertSee('Test plugin footer-injection for observer testing');
    }

    public function testDisableEnablePlugin(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');
        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk();

        $this->installPluginToFilesystem(self::TEST_PLUGIN_NAME, self::TEST_PLUGIN_VERSION);
        $this->visitAdminCommand('plugin_manager')->assertOk();
        $response = $this->visitAdminCommand('plugin_manager&page=1&colKey=zenTestPlugin&action=install')
            ->assertOk();
        $this->submitAdminForm($response, 'plugininstall')
            ->assertOk()
            ->assertSee('Version Installed:</strong> v1.0.0');

        $response = $this->visitAdminCommand('plugin_manager&page=1&colKey=zenTestPlugin&action=disable')
            ->assertOk()
            ->assertSee('Are you sure you want to disable this plugin?');

        $response = $this->submitAdminForm($response, 'pluginuninstall')
            ->assertOk();

        $response->assertSee('action=enable');

        $response = $this->visitAdminCommand('plugin_manager&page=1&colKey=zenTestPlugin&action=enable')
            ->assertOk()
            ->assertSee('Are you sure you want to enable this plugin?');

        $response = $this->submitAdminForm($response, 'pluginuninstall')
            ->assertOk();

        $response->assertSee('action=disable');
    }

    public function testUninstallPlugin(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');
        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk();

        $this->installPluginToFilesystem(self::TEST_PLUGIN_NAME, self::TEST_PLUGIN_VERSION);
        $this->visitAdminCommand('plugin_manager')->assertOk();
        $response = $this->visitAdminCommand('plugin_manager&page=1&colKey=zenTestPlugin&action=install')
            ->assertOk();
        $this->submitAdminForm($response, 'plugininstall')
            ->assertOk()
            ->assertSee('Version Installed:</strong> v1.0.0');

        $response = $this->visitAdminCommand('plugin_manager&page=1&colKey=zenTestPlugin&action=uninstall')
            ->assertOk()
            ->assertSee('Are you sure you want to uninstall this plugin?');

        $response = $this->submitAdminForm($response, 'pluginuninstall')
            ->assertOk();

        $response->assertSee('action=install');
    }
}
