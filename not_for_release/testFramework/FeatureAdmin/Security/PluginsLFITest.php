<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\Security;

use Symfony\Component\Panther\Client;
use Tests\Models\PluginControl;
use Tests\Support\zcFeatureTestCaseAdmin;

class PluginsLFITest extends zcFeatureTestCaseAdmin
{

    public function testPluginLFI()
    {
        // note probably need to make the login a separate method
        // would be nice if we could use Laravel actingAs
        $this->runCustomSeeder('StoreWizardSeeder');
        $this->runCustomSeeder('DisplayLogsSeeder');
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Admin Login', (string)$response->getContent() );
        $this->browser->submitForm('Submit', [
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Admin Home', (string)$response->getContent() );
        // need to hit the plugin manager end point to get the scanned modules into the database, if not already there.
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=plugin_manager');
        // set the display logs to be installed
        $pm = PluginControl::where('name', 'Display Logs')->update(['status' => 1, 'version' => 'v3.0.3']);
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=display_logs');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Admin Display Logs', (string)$response->getContent() );

        $dir = 'includes/';
        touch($dir . 'security_test.php');
        file_put_contents($dir . 'security_test.php', "<?php\ndie('lfi-vulnerable');\n");
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=../../../../includes/security_test');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringNotContainsString('lfi-vulnerable', (string)$response->getContent());
        $this->assertStringContainsString('Admin Home', (string)$response->getContent() );
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=display_logs');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Admin Display Logs', (string)$response->getContent() );
        unlink($dir . 'security_test.php');
    }
}
