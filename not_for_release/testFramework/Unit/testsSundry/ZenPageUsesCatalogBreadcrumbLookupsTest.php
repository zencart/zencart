<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921: zen_page_uses_catalog_breadcrumb_lookups() is an
 * allow-list (not a deny-list) of pages that legitimately build a breadcrumb from
 * catalog-filter GET params (products_id, manufacturers_id, cPath, etc.), so
 * init_add_crumbs.php / init_category_path.php can gate the associated database lookups.
 * A page not recognized here -- including any new core or plugin page nobody has
 * thought to audit yet -- is safe by default.
 *
 * Product-detail pages vary by product type (product_info, product_music_info, ...),
 * including types a plugin registers, so that part of the list is derived from
 * TABLE_PRODUCT_TYPES at runtime rather than hardcoded. A plugin adding a non-product-type
 * catalog page can still extend the list via the NOTIFY_CATALOG_BREADCRUMB_LOOKUP_PAGES
 * observer, without being able to remove any core entry.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class ZenPageUsesCatalogBreadcrumbLookupsTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', false);
        defined('FILENAME_DEFAULT') || define('FILENAME_DEFAULT', 'index');
        defined('FILENAME_SEARCH_RESULT') || define('FILENAME_SEARCH_RESULT', 'search_result');
        defined('FILENAME_SPECIALS') || define('FILENAME_SPECIALS', 'specials');
        defined('FILENAME_PRODUCTS_NEW') || define('FILENAME_PRODUCTS_NEW', 'products_new');
        defined('FILENAME_PRODUCTS_ALL') || define('FILENAME_PRODUCTS_ALL', 'products_all');
        defined('FILENAME_FEATURED_PRODUCTS') || define('FILENAME_FEATURED_PRODUCTS', 'featured_products');
        defined('FILENAME_PRODUCT_REVIEWS') || define('FILENAME_PRODUCT_REVIEWS', 'product_reviews');
        defined('FILENAME_PRODUCT_REVIEWS_INFO') || define('FILENAME_PRODUCT_REVIEWS_INFO', 'product_reviews_info');
        defined('FILENAME_PRODUCT_REVIEWS_WRITE') || define('FILENAME_PRODUCT_REVIEWS_WRITE', 'product_reviews_write');
        defined('FILENAME_SHOPPING_CART') || define('FILENAME_SHOPPING_CART', 'shopping_cart');
        defined('FILENAME_CHECKOUT_SHIPPING') || define('FILENAME_CHECKOUT_SHIPPING', 'checkout_shipping');
        defined('FILENAME_LOGIN') || define('FILENAME_LOGIN', 'login');
        defined('FILENAME_ACCOUNT') || define('FILENAME_ACCOUNT', 'account');
        defined('FILENAME_CONTACT_US') || define('FILENAME_CONTACT_US', 'contact_us');
        defined('TABLE_PRODUCT_TYPES') || define('TABLE_PRODUCT_TYPES', 'product_types');

        // Mirrors lat9/one_page_checkout registering a non-product-type catalog page.
        defined('FILENAME_A_PLUGIN_CATALOG_PAGE') || define('FILENAME_A_PLUGIN_CATALOG_PAGE', 'a_plugin_catalog_page');

        require_once DIR_FS_CATALOG . 'includes/classes/traits/NotifierManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/traits/ObserverManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_lookups.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $GLOBALS['zco_notifier'] = new \notifier();

        $GLOBALS['db'] = $this->getMockBuilder(\queryFactory::class)->getMock();
    }

    public function testCoreCatalogListingAndReviewPagesAreRecognized(): void
    {
        $GLOBALS['db']->method('Execute')->willReturn($this->productTypesResultSet());

        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_DEFAULT));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_SEARCH_RESULT));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_SPECIALS));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_PRODUCTS_NEW));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_PRODUCTS_ALL));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_FEATURED_PRODUCTS));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_PRODUCT_REVIEWS));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_PRODUCT_REVIEWS_INFO));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_PRODUCT_REVIEWS_WRITE));
    }

    public function testProductTypeInfoPagesAreRecognizedViaTheDatabase(): void
    {
        $GLOBALS['db']->method('Execute')->willReturn($this->productTypesResultSet());

        // These aren't hardcoded anywhere -- they only match because the mocked
        // product_types query (see productTypesResultSet()) reports these type_handlers.
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups('product_info'));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups('product_music_info'));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups('document_general_info'));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups('document_product_info'));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups('product_free_shipping_info'));
    }

    public function testNonCatalogPagesAreNotRecognized(): void
    {
        $GLOBALS['db']->method('Execute')->willReturn($this->productTypesResultSet());

        $this->assertFalse(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_SHOPPING_CART));
        $this->assertFalse(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_SHIPPING));
        $this->assertFalse(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_LOGIN));
        $this->assertFalse(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_ACCOUNT));
        $this->assertFalse(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_CONTACT_US));
    }

    public function testTheProductTypesQueryOnlyRunsOnceRegardlessOfCallCount(): void
    {
        // A local static in the function itself must prevent a second query even
        // though this function gets called from more than one init_include per request.
        $GLOBALS['db']->expects($this->once())->method('Execute')->willReturn($this->productTypesResultSet());

        \zen_page_uses_catalog_breadcrumb_lookups(FILENAME_SHOPPING_CART);
        \zen_page_uses_catalog_breadcrumb_lookups(FILENAME_DEFAULT);
        \zen_page_uses_catalog_breadcrumb_lookups('product_info');
    }

    public function testAPluginCanRegisterAnAdditionalCatalogPageWithoutRemovingCoreEntries(): void
    {
        $GLOBALS['db']->method('Execute')->willReturn($this->productTypesResultSet());

        $observer = new PluginCatalogPageStubObserver();
        $GLOBALS['zco_notifier']->attach($observer, ['NOTIFY_CATALOG_BREADCRUMB_LOOKUP_PAGES']);

        // The plugin's own page is now recognized too...
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_A_PLUGIN_CATALOG_PAGE));

        // ...and every core entry is still intact.
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_DEFAULT));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups('product_info'));

        // An unrelated page remains unrecognized.
        $this->assertFalse(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_SHOPPING_CART));
    }

    public function testAMisbehavingObserverCannotRemoveCoreEntries(): void
    {
        $GLOBALS['db']->method('Execute')->willReturn($this->productTypesResultSet());

        // $catalogPages (the core list) is passed to observers by value, not by
        // reference -- so even an observer that tries to clear/reassign it can only
        // affect its own local copy, never the caller's list.
        $observer = new MisbehavingCatalogPageStubObserver();
        $GLOBALS['zco_notifier']->attach($observer, ['NOTIFY_CATALOG_BREADCRUMB_LOOKUP_PAGES']);

        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups(FILENAME_DEFAULT));
        $this->assertTrue(\zen_page_uses_catalog_breadcrumb_lookups('product_info'));
    }

    /** @return array<int, array{type_handler: string}> */
    private function productTypesResultSet(): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = [
            ['type_handler' => 'product'],
            ['type_handler' => 'product_music'],
            ['type_handler' => 'document_general'],
            ['type_handler' => 'document_product'],
            ['type_handler' => 'product_free_shipping'],
        ];

        return $result;
    }
}

/**
 * Stands in for a plugin extending the core catalog-page list via
 * the NOTIFY_CATALOG_BREADCRUMB_LOOKUP_PAGES observer's by-reference $additionalPages
 * ($param2).
 */
class PluginCatalogPageStubObserver
{
    public function update($class, $eventID, &$param1 = null, &$param2 = null): void
    {
        if ($eventID !== 'NOTIFY_CATALOG_BREADCRUMB_LOOKUP_PAGES') {
            return;
        }

        $param2[] = FILENAME_A_PLUGIN_CATALOG_PAGE;
    }
}

/**
 * Attempts to empty out $catalogPages ($param1) -- since that parameter is passed by
 * value, this can only ever mutate the observer's local copy, not the caller's list.
 */
class MisbehavingCatalogPageStubObserver
{
    public function update($class, $eventID, &$param1 = null, &$param2 = null): void
    {
        if ($eventID !== 'NOTIFY_CATALOG_BREADCRUMB_LOOKUP_PAGES') {
            return;
        }

        $param1 = [];
    }
}
