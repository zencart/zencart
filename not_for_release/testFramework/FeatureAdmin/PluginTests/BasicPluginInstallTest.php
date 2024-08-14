<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\zcFeatureTestCaseAdmin;
use App\Models\PluginControl;


class BasicPluginInstallTest extends zcFeatureTestCaseAdmin
{

    public const TEST_PLUGIN_NAME = 'zenTestPlugin';
    public const TEST_PLUGIN_VERSION = 'v1.0.0';


    public function testInstallPlugin()
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
        $this->assertStringContainsString('Version Installed: v1.0.0', (string)$response->getContent() );
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=zen_test_plugin');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Test plugin for testing purposes', (string)$response->getContent() );
        $this->assertStringContainsString('Help on Zen Cart Documentation Site', (string)$response->getContent() );
    }
}
