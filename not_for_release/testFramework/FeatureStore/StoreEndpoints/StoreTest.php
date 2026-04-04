<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\InProcess\FeatureResponse;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class StoreTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    protected array $quickTestMap = [
        'products_all' => ['strings' => ['Zen Cart! : All Products']],
        'about_us' => ['strings' => ['Zen Cart! : About Us']],
        'shippinginfo' => ['strings' => ['Zen Cart! : Shipping &amp; Returns']],
        'privacy' => ['strings' => ['Zen Cart! : Privacy Notice']],
        'conditions' => ['strings' => ['Zen Cart! : Conditions of Use']],
        'contact_us' => ['strings' => ['Zen Cart! : Contact Us'], 'ssl' => true],
        'site_map' => ['strings' => ['Zen Cart! : Site Map']],
        'gv_faq' => ['strings' => ['Zen Cart! : Gift Certificate FAQ']],
    ];

    public function testSimpleStore(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront');

        $this->getMainPage('products_all')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Zen Cart! : All Products');
    }

    public function testQuickLinks(): void
    {
        foreach ($this->quickTestMap as $page => $contentTest) {
            $response = $this->requestPage($page, $contentTest);
            $response->assertOk()->assertHeader('X-ZC-InProcess-Runner', 'storefront');

            foreach ($contentTest['strings'] as $contentString) {
                $response->assertSee($contentString);
            }
        }
    }

    private function requestPage(string $page, array $contentTest): FeatureResponse
    {
        if (($contentTest['ssl'] ?? false) === true) {
            return $this->getSslMainPage($page);
        }

        return $this->getMainPage($page);
    }
}
