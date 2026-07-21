<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921's follow-up discussion: application_top.php's
 * very-early inoculation block now rejects (406, before any session/DB/file loads
 * beyond this one) a request that supplies a catalog-only filter param (cPath,
 * manufacturers_id, etc.) for a page that never legitimately reads it -- e.g. the
 * original forum-reported bot pattern, shopping_cart?manufacturers_id=8&products_id=1.
 *
 * zen_request_has_disallowed_catalog_param() (includes/routing_map.php) is deliberately
 * self-contained (no framework, no constants, no DB) because it runs before
 * includes/configure.php is even loaded -- see application_top.php's inoculation block.
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class RoutingMapCatalogParamCheckTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/routing_map.php';
    }

    /**
     * Every catalog-filter key, fired at a page that never legitimately uses it.
     * This is the actual bug: a bot supplying these params on, say, the shopping
     * cart page to force wasted breadcrumb/category lookups downstream.
     */
    public function testEachCatalogFilterKeyIsRejectedOnANonCatalogPage(): void
    {
        // NOTE: cPath and products_id are NOT in this list -- see
        // testCPathAndProductsIdAreDeliberatelyExcluded() for why.
        $catalogFilterKeys = [
            'manufacturers_id', 'music_genre_id', 'record_company_id',
            'disp_order', 'sort', 'typefilter', 'filter_id', 'alpha_filter_id',
            'keyword', 'dfrom', 'pfrom', 'dto', 'pto', 'search_in_description', 'inc_subcat',
            'categories_id', 'sale_category', 'reviews_id',
        ];

        foreach ($catalogFilterKeys as $key) {
            $get = ['main_page' => 'shopping_cart', $key => '1'];
            $this->assertTrue(
                \zen_request_has_disallowed_catalog_param($get),
                "Expected '$key' on shopping_cart to be rejected."
            );
        }
    }

    /**
     * The exact URL pattern reported in the forum thread behind #7921.
     */
    public function testTheOriginalForumReportedBotPatternIsRejected(): void
    {
        $get = [
            'main_page' => 'shopping_cart',
            'disp_order' => '8',
            'manufacturers_id' => '8',
            'page' => '6',
            'products_id' => '1',
        ];

        $this->assertTrue(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * A real manufacturer-click-through / legacy-URL redirect must not be rejected --
     * the redirect page's whole job is to accept manufacturers_id/record_company_id
     * and 301 the visitor onward.
     */
    public function testALegitimateManufacturerClickThroughRedirectIsNotRejected(): void
    {
        $get = ['main_page' => 'redirect', 'action' => 'manufacturer', 'manufacturers_id' => '5'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * An old bookmarked search URL, forwarded via the advanced_search_result redirect
     * stub, must not be rejected -- its whole purpose is backward compatibility for
     * exactly this kind of link.
     */
    public function testALegacyBookmarkedAdvancedSearchUrlIsNotRejected(): void
    {
        $get = ['main_page' => 'advanced_search_result', 'keyword' => 'widget', 'manufacturers_id' => '5'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * brands/featured_categories both read $_GET['typefilter'] today (even though
     * they then discard/never act on it), so a request carrying it currently renders
     * 200. They must stay in the allow-list to avoid turning that into a 406 for no
     * functional gain.
     */
    public function testBrandsAndFeaturedCategoriesAreNotRejectedForTypefilter(): void
    {
        $this->assertFalse(\zen_request_has_disallowed_catalog_param(['main_page' => 'brands', 'typefilter' => 'x']));
        $this->assertFalse(\zen_request_has_disallowed_catalog_param(['main_page' => 'featured_categories', 'typefilter' => 'x']));
    }

    /**
     * Every recognized catalog page, with a representative filter key, must NOT
     * be rejected -- guards against a typo silently removing a page from the list.
     */
    public function testEachCatalogPageIsAllowedToUseFilterParams(): void
    {
        $catalogPages = [
            'index', 'search', 'search_result', 'advanced_search', 'advanced_search_result',
            'specials', 'products_new', 'products_all', 'featured_products',
            'product_reviews', 'product_reviews_info', 'product_reviews_write',
            'redirect', 'brands', 'featured_categories',
        ];

        foreach ($catalogPages as $page) {
            $get = ['main_page' => $page, 'manufacturers_id' => '8', 'cPath' => '1_4'];
            $this->assertFalse(
                \zen_request_has_disallowed_catalog_param($get),
                "Expected '$page' to be allowed to use catalog filter params."
            );
        }
    }

    public function testANonCatalogPageWithNoFilterParamsIsNotRejected(): void
    {
        $get = ['main_page' => 'shopping_cart', 'action' => 'add_product'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    public function testMissingMainPageWithAFilterParamIsRejected(): void
    {
        $get = ['manufacturers_id' => '8'];

        $this->assertTrue(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * pID/pid look like catalog candidates but are used by shopping_cart's own
     * remove/update links, ask_a_question, and the popup_image pages -- they must
     * stay out of the restricted list, or the cart's own UI breaks.
     */
    public function testDeliberatelyExcludedKeysAreNeverRejected(): void
    {
        $deliberatelyExcludedKeys = ['pID', 'pid', 'page', 'action', 'cID', 'id'];

        foreach ($deliberatelyExcludedKeys as $key) {
            $get = ['main_page' => 'shopping_cart', $key => '1'];
            $this->assertFalse(
                \zen_request_has_disallowed_catalog_param($get),
                "Expected '$key' to remain unrestricted (see routing_map.php's excluded-keys notes)."
            );
        }
    }

    /**
     * cPath and products_id ARE catalog-filter params, but unlike the others they're
     * also legitimate on the open-ended, DB-driven set of product-detail "_info" pages
     * (product_info, product_music_info, ... including plugin-added product types).
     * This static, pre-DB check can't safely enumerate that set, so both keys are
     * excluded here and left to the downstream DB-driven check instead. Getting this
     * wrong would 406 every normal product-page view -- this is the regression this
     * test guards against.
     */
    public function testCPathAndProductsIdAreDeliberatelyExcluded(): void
    {
        $get = ['main_page' => 'product_info', 'products_id' => '1', 'cPath' => '1_4'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * Product-type "_info" pages and checkout pages are intentionally NOT covered
     * by this early static check -- they're gated downstream (once the DB is
     * available) by zen_page_uses_catalog_breadcrumb_lookups(). Confirms this
     * function doesn't (incorrectly) reject them itself.
     */
    public function testProductInfoAndCheckoutPagesAreNotRejectedHere(): void
    {
        $pagesHandledDownstreamInstead = ['product_info', 'checkout_shipping', 'checkout_payment'];

        foreach ($pagesHandledDownstreamInstead as $page) {
            $get = ['main_page' => $page, 'products_id' => '1', 'cPath' => '1_4'];
            $this->assertFalse(
                \zen_request_has_disallowed_catalog_param($get),
                "Expected '$page' to be left to the downstream DB-driven check, not rejected here."
            );
        }
    }
}
