<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Features\TestStoreFeatures;

use Tests\Support\zcFeatureTestCaseStore;

class StoreTest extends zcFeatureTestCaseStore
{
    protected array $quickTestMap = [
        'products_all' => 'Zen Cart! : All Products',
        'about_us' => 'Zen Cart! : About Us',
        'shippinginfo' => 'Zen Cart! : Shipping &amp; Returns',
        'privacy' => 'Zen Cart! : Privacy Notice',
        'conditions' => 'Zen Cart! : Conditions of Use',
        'contact_us' => 'Zen Cart! : Contact Us',
        'site_map' => 'Zen Cart! : Site Map',
        'gv_faq' => 'Zen Cart! : Gift Certificate FAQ',
    ];

    public function testSimpleStore()
    {
        $request = $this->browser->request('GET', HTTP_SERVER);
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER  .'/index.php?main_page=products_all');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testQuickLinks()
    {
        foreach ($this->quickTestMap as $page => $contentTest) {
            $pageURI = $this->buildStoreLink($page);
            $this->browser->request('GET', $pageURI);
            $response = $this->browser->getResponse();
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertStringContainsString($contentTest, (string)$response->getContent() );
        }

    }
}
