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
class AdvancedSearchResultInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testLegacyAdvancedSearchResultsRedirectPreservesSearchParameters(): void
    {
        $response = $this->get('/index.php?main_page=advanced_search_result&keyword=Matrox&categories_id=4&search_in_description=1')
            ->assertRedirect('main_page=search_result');

        $this->followRedirect($response)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Search Results')
            ->assertSee('Matrox G200 MMS')
            ->assertSee('Matrox G400 32MB');
    }

    public function testAdvancedSearchResultCanNarrowResultsByCategory(): void
    {
        $this->visitSearchResults([
            'keyword' => 'Matrox',
            'categories_id' => 4,
            'search_in_description' => 1,
        ])->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Search Results')
            ->assertSee('Matrox G200 MMS')
            ->assertSee('Matrox G400 32MB');
    }

    public function testAdvancedSearchResultShowsNoMatchesForWrongCategory(): void
    {
        $response = $this->get('/index.php?main_page=advanced_search_result&keyword=Matrox&categories_id=1')
            ->assertRedirect('main_page=search_result');

        $page = $this->followRedirect($response);
        if ($page->isRedirect()) {
            $page = $this->followRedirect($page);
        }

        $page->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Zen Cart! : Search')
            ->assertSee('There is no product that matches the search criteria.');
    }
}
