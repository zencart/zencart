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
class AdvancedSearchInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testLegacyAdvancedSearchPageRedirectsToSearch(): void
    {
        $this->get('/index.php?main_page=advanced_search')
            ->assertRedirect('main_page=search');
    }

    public function testSearchPageRendersAdvancedCriteriaControls(): void
    {
        $this->visitSearch()
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Choose Your Search Terms')
            ->assertSee('Limit to Category:')
            ->assertSee('Search by Price Range')
            ->assertSee('Search by Date Added');
    }

    public function testLegacyAdvancedSearchRedirectPreservesSearchParameters(): void
    {
        $response = $this->get('/index.php?main_page=advanced_search&keyword=music&pfrom=1&pto=20')
            ->assertRedirect('main_page=search');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Choose Your Search Terms')
            ->assertSee('value="music"')
            ->assertSee('value="1"')
            ->assertSee('value="20"');
    }
}
