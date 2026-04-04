<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class CatalogLandingPagesInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    /**
     * @dataProvider catalogLandingPageProvider
     */
    public function testCatalogLandingPagesRenderInProcess(string $page, string $expectedText): void
    {
        $this->getMainPage($page)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee($expectedText);
    }

    public static function catalogLandingPageProvider(): array
    {
        return [
            'specials' => ['specials', 'Specials'],
            'featured_products' => ['featured_products', 'Featured Products'],
            'products_new' => ['products_new', 'New Products'],
            'brands' => ['brands', 'Shop By Brand'],
        ];
    }
}
