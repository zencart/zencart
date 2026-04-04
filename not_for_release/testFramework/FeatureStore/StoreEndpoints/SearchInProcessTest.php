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
class SearchInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testSearchResultsPageCanBeRenderedInProcess(): void
    {
        $this->visitSearch()
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Zen Cart! : Search')
            ->assertSee('Choose Your Search Terms');
    }

    public function testSearchResultsShowNoProductsMessageForUnknownKeyword(): void
    {
        $response = $this->searchFor('definitely-no-matching-product')
            ->assertRedirect('main_page=search');

        $this->followRedirect($response)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Zen Cart! : Search')
            ->assertSee('There is no product that matches the search criteria.');
    }

    public function testLegacyAdvancedSearchResultsRedirectToSearchResults(): void
    {
        $this->get('/index.php?main_page=advanced_search_result&keyword=music')
            ->assertRedirect('main_page=search_result');
    }
}
