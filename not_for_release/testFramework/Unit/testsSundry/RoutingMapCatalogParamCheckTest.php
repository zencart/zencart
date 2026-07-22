<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921's follow-up discussion: application_top.php's
 * very-early inoculation block now rejects (406, before any session/DB/file loads
 * beyond this one) a request that supplies a catalog-only filter param (manufacturers_id,
 * etc.) for a page confirmed to never legitimately read it -- e.g. the original
 * forum-reported bot pattern, shopping_cart?manufacturers_id=8&products_id=1.
 *
 * zen_request_has_disallowed_catalog_param() (includes/routing_map.php) is a deny-list
 * of known-bad targets -- shopping_cart matched explicitly, every checkout-flow page
 * (core and third-party one-page/guest-checkout addons alike) matched by the
 * 'checkout_' prefix -- not an allow-list of known-good pages -- deliberately, since
 * this runs before plugins load and has no way to let a plugin register its own
 * catalog-style page. A page that doesn't match, including any plugin page or any
 * core page nobody's added yet, is always allowed through.
 *
 * It's also deliberately self-contained (no framework, no constants, no DB) because it
 * runs before includes/configure.php is even loaded -- see application_top.php's
 * inoculation block.
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
     * 200. Under the deny-list model they're simply never listed, so they're allowed
     * by default -- no special-casing needed, unlike the old allow-list.
     */
    public function testBrandsAndFeaturedCategoriesAreNotRejectedForTypefilter(): void
    {
        $this->assertFalse(\zen_request_has_disallowed_catalog_param(['main_page' => 'brands', 'typefilter' => 'x']));
        $this->assertFalse(\zen_request_has_disallowed_catalog_param(['main_page' => 'featured_categories', 'typefilter' => 'x']));
    }

    /**
     * Every catalog "listing" page must NOT be rejected -- guards against a typo
     * accidentally adding one of these to the deny-list.
     */
    public function testCatalogListingPagesAreNotRejected(): void
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

    /**
     * The whole point of the deny-list model: a page this check has never heard of
     * -- standing in for any plugin-added page -- must never be rejected, even
     * when it carries a restricted key. An allow-list would 406 this permanently,
     * with no way for the page to ever opt in (plugins aren't loaded yet). See
     * GitHub issue #7924's follow-up discussion.
     */
    public function testAnUnrecognizedPluginLikePageIsNeverRejected(): void
    {
        $get = ['main_page' => 'some_plugins_custom_catalog_page', 'manufacturers_id' => '8', 'sort' => '2a'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * Documents the accepted trade-off: core utility pages not on the deny-list
     * (login, contact_us, account, ...) no longer get this specific early-reject
     * optimization. That's intentional -- growing the deny-list to cover every
     * non-catalog core page would reintroduce the same false-positive risk the
     * deny-list model exists to avoid. These pages simply fall through to normal
     * processing, exactly as if this file didn't exist.
     */
    public function testCoreUtilityPagesNotOnTheDenyListAreNotRejectedEither(): void
    {
        foreach (['login', 'contact_us', 'account'] as $page) {
            $get = ['main_page' => $page, 'manufacturers_id' => '8'];
            $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
        }
    }

    public function testANonCatalogPageWithNoFilterParamsIsNotRejected(): void
    {
        $get = ['main_page' => 'shopping_cart', 'action' => 'add_product'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * A missing/empty main_page must be treated the same as init_sanitize.php treats
     * it -- an empty main_page defaults to the index page (FILENAME_DEFAULT), so
     * index.php?manufacturers_id=8 (no main_page at all) is a real, legitimate URL
     * shape that renders as the index/manufacturer listing today. Must not be rejected.
     */
    public function testMissingMainPageWithAFilterParamIsNotRejected(): void
    {
        $get = ['manufacturers_id' => '8'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    public function testEmptyMainPageWithAFilterParamIsNotRejected(): void
    {
        $get = ['main_page' => '', 'manufacturers_id' => '8'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
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
     * excluded from $catalogFilterKeys entirely and left to the downstream DB-driven
     * check instead. Tested against a page that IS on the deny-list (shopping_cart) --
     * not product_info, which was never going to be checked anyway -- to actually
     * exercise the exclusion rather than pass trivially.
     */
    public function testCPathAndProductsIdAreDeliberatelyExcluded(): void
    {
        $get = ['main_page' => 'shopping_cart', 'products_id' => '1', 'cPath' => '1_4'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * Product-detail "_info" pages are never on the deny-list (open-ended, DB-driven
     * set -- see class docblock), so they're never rejected by this early check
     * regardless of which key is supplied. They're gated downstream instead, once
     * the DB is available, by zen_page_uses_catalog_breadcrumb_lookups().
     */
    public function testProductInfoPagesAreNeverRejectedHere(): void
    {
        $get = ['main_page' => 'product_info', 'products_id' => '1', 'cPath' => '1_4', 'manufacturers_id' => '8'];

        $this->assertFalse(\zen_request_has_disallowed_catalog_param($get));
    }

    /**
     * Unlike product_info, checkout pages ARE on the deny-list -- so, since the
     * earlier PR extended this same reasoning to checkout, a checkout page carrying
     * a manufacturers_id-class key (never legitimately used there) IS rejected here,
     * even though cPath/products_id specifically remain deferred downstream (see
     * testCPathAndProductsIdAreDeliberatelyExcluded).
     */
    public function testCheckoutPagesAreRejectedForManufacturersIdClassKeysButNotCPathOrProductsId(): void
    {
        foreach (['checkout_shipping', 'checkout_payment'] as $page) {
            $this->assertTrue(
                \zen_request_has_disallowed_catalog_param(['main_page' => $page, 'manufacturers_id' => '8']),
                "Expected '$page' to reject manufacturers_id."
            );
            $this->assertFalse(
                \zen_request_has_disallowed_catalog_param(['main_page' => $page, 'products_id' => '1', 'cPath' => '1_4']),
                "Expected '$page' to still allow products_id/cPath (deferred downstream)."
            );
        }
    }

    /**
     * shopping_cart plus every core checkout-flow page, with a representative
     * filter key, must actually be rejected -- guards against the prefix logic
     * (or the explicit shopping_cart check) silently breaking.
     */
    public function testEachKnownNonCatalogPageIsRejected(): void
    {
        $knownNonCatalogPages = [
            'shopping_cart',
            'checkout_shipping', 'checkout_shipping_address',
            'checkout_payment', 'checkout_payment_address',
            'checkout_confirmation', 'checkout_process', 'checkout_success',
        ];

        foreach ($knownNonCatalogPages as $page) {
            $get = ['main_page' => $page, 'manufacturers_id' => '8'];
            $this->assertTrue(
                \zen_request_has_disallowed_catalog_param($get),
                "Expected '$page' to reject manufacturers_id."
            );
        }
    }

    /**
     * Also match a 'checkout_' prefix match to accommodate both core and third-party
     * one-page-checkout addon pages which follow the same naming convention:
     * all get the same protection automatically, with no maintenance burden per addon.
     */
    public function testThirdPartyCheckoutAddonPagesAreAlsoRejected(): void
    {
        $knownAddonCheckoutPages = [
            'checkout_one', 'checkout_one_confirmation', 'checkout_one_send_welcome_email',
        ];

        foreach ($knownAddonCheckoutPages as $page) {
            $get = ['main_page' => $page, 'manufacturers_id' => '8'];
            $this->assertTrue(
                \zen_request_has_disallowed_catalog_param($get),
                "Expected '$page' to reject manufacturers_id."
            );
        }
    }

    /**
     * Documents the accepted trade-off of the prefix match, called out explicitly
     * in routing_map.php's docblock: ANY page starting with 'checkout_' is denied here,
     * even one from an unrelated plugin that has nothing to do with the checkout flow.
     * Judged unlikely enough in practice to accept, in exchange for not having to enumerate
     * every checkout-flow page (core and third-party) by hand.
     * If this test starts failing a real plugin's build, that's the trade-off
     * materializing, not a bug in this function.
     */
    public function testAnyCheckoutPrefixedPageIsRejectedEvenIfUnrelatedToCheckout(): void
    {
        $get = ['main_page' => 'checkout_totally_unrelated_plugin_page', 'manufacturers_id' => '8'];

        $this->assertTrue(\zen_request_has_disallowed_catalog_param($get));
    }
}
