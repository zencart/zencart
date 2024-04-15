<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\zcFeatureTestCaseStore;

class StoreTest extends zcFeatureTestCaseStore
{
    protected array $quickTestMap = [
        'products_all' => ['strings' => ['Zen Cart! : All Products']],
        'about_us' => ['strings' => ['Zen Cart! : About Us']],
        'shippinginfo' => ['strings' => ['Zen Cart! : Shipping &amp; Returns']],
        'privacy' => ['strings' => ['Zen Cart! : Privacy Notice']],
        'conditions' => ['strings' => ['Zen Cart! : Conditions of Use']],
        'contact_us' => ['strings' => ['Zen Cart! : Contact Us']],
        'site_map' => ['strings' => ['Zen Cart! : Site Map']],
        'gv_faq' => ['strings' => ['Zen Cart! : Gift Certificate FAQ']],
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
            foreach ( $contentTest['strings'] as $contentString) {
                $this->assertStringContainsString($contentString, (string)$response->getContent() );
            }
        }
    }
}
