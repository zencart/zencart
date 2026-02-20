<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\PluginTests;

use Tests\Support\zcFeatureTestCaseAdmin;


class BasicPluginInstallTest extends zcFeatureTestCaseAdmin
{
    public const TEST_PLUGIN_NAME = 'zenTestPlugin';
    public const TEST_PLUGIN_VERSION = 'v1.0.0';

    public function testInstallPlugin(): void
    {
        $this->browserAdminLogin();
        $this->installPluginToFilesystem(self::TEST_PLUGIN_NAME, self::TEST_PLUGIN_VERSION);
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager');
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager&page=1&colKey=zenTestPlugin&action=install');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Test plugin for testing purposes', (string)$response->getContent() );
        $this->browser->submitForm('Install', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Version Installed: v1.0.0', \strip_tags((string)$response->getContent()) );
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=zen_test_plugin');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Test plugin for testing purposes', (string)$response->getContent() );
        $this->assertStringContainsString('Help on Zen Cart Documentation Site', (string)$response->getContent() );
    }

    public function testDisableEnablePlugin(): void
    {
        $this->browserAdminLogin();
        $this->installPluginToFilesystem(self::TEST_PLUGIN_NAME, self::TEST_PLUGIN_VERSION);
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager');
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager&page=1&colKey=zenTestPlugin&action=install');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Test plugin for testing purposes', (string)$response->getContent() );
        $this->browser->submitForm('Install', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Version Installed: v1.0.0', \strip_tags((string)$response->getContent()) );
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager&page=1&colKey=zenTestPlugin');
        $this->browser->clickLink('Disable', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Are you sure you want to disable this plugin?', \strip_tags((string)$response->getContent()) );
        $this->browser->submitForm('Disable', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Plugin disabled successfully', \strip_tags((string)$response->getContent()) );
        $this->browser->clickLink('Enable', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Are you sure you want to enable this plugin?', \strip_tags((string)$response->getContent()) );
        $this->browser->submitForm('Enable', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Plugin enabled successfully', \strip_tags((string)$response->getContent()) );
    }

    public function testUninstallPlugin(): void
    {
        $this->browserAdminLogin();
        $this->installPluginToFilesystem(self::TEST_PLUGIN_NAME, self::TEST_PLUGIN_VERSION);
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager');
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager&page=1&colKey=zenTestPlugin&action=install');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Test plugin for testing purposes', (string)$response->getContent() );
        $this->browser->submitForm('Install', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Version Installed: v1.0.0', \strip_tags((string)$response->getContent()) );

        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager&page=1&colKey=zenTestPlugin');
        $this->browser->clickLink('Un-Install', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Are you sure you want to uninstall this plugin?', \strip_tags((string)$response->getContent()) );
        $this->browser->submitForm('Un-Install', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Plugin un-installed successfully', \strip_tags((string)$response->getContent()) );
    }
}
