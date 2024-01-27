<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Feature\TestSecurity;

use Symfony\Component\Panther\Client;
use Tests\Support\zcFeatureTestCase;

class ProtectedDirectoryTest extends zcFeatureTestCase
{

    public function testLaravelDirectory()
    {
        $this->browser->request('GET', HTTP_SERVER . '/laravel');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/laravel/index.php');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/laravel/config/app.php');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testCacheDirectory()
    {
        $this->browser->request('GET', HTTP_SERVER . '/cache');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/cache/index.php');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/cache/.htaccess');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());

    }

    public function testExtrasDirectory()
    {
        $this->browser->request('GET', HTTP_SERVER . '/extras');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/extras/index.html');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/extras/.htaccess');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testImagesDirectory()
    {
        $this->browser->request('GET', HTTP_SERVER . '/images');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/images/index.php');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/images/.htaccess');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testIncludesDirectory()
    {
        $this->browser->request('GET', HTTP_SERVER . '/includes');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/includes/index.php');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/includes/.htaccess');
        $response = $this->browser->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }
}
