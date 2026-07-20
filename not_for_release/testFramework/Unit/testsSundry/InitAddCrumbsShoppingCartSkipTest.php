<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921: init_add_crumbs.php used to look up every
 * registered "get term" (manufacturer, music genre, etc.) filter GET-param against the
 * database on every page, even pages like the shopping cart and checkout steps that never
 * use them -- letting bots trigger wasted queries by spoofing arbitrary query-string params.
 * It now only runs that lookup on pages recognized by
 * zen_page_uses_catalog_breadcrumb_lookups() as legitimately using it. That recognition
 * function itself issues one (cached, request-scoped) query against product_types, so these
 * tests assert on the *number* and *content* of queries rather than a blanket zero.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class InitAddCrumbsShoppingCartSkipTest extends zcUnitTestCase
{
    private string $initAddCrumbsFile;

    public function setUp(): void
    {
        parent::setUp();

        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', false);
        defined('HEADER_TITLE_CATALOG') || define('HEADER_TITLE_CATALOG', 'Catalog');
        defined('FILENAME_DEFAULT') || define('FILENAME_DEFAULT', 'index');
        defined('FILENAME_SHOPPING_CART') || define('FILENAME_SHOPPING_CART', 'shopping_cart');
        defined('FILENAME_CHECKOUT_SHIPPING') || define('FILENAME_CHECKOUT_SHIPPING', 'checkout_shipping');
        defined('FILENAME_SEARCH_RESULT') || define('FILENAME_SEARCH_RESULT', 'search_result');
        defined('FILENAME_SPECIALS') || define('FILENAME_SPECIALS', 'specials');
        defined('FILENAME_PRODUCTS_NEW') || define('FILENAME_PRODUCTS_NEW', 'products_new');
        defined('FILENAME_PRODUCTS_ALL') || define('FILENAME_PRODUCTS_ALL', 'products_all');
        defined('FILENAME_FEATURED_PRODUCTS') || define('FILENAME_FEATURED_PRODUCTS', 'featured_products');
        defined('FILENAME_PRODUCT_REVIEWS') || define('FILENAME_PRODUCT_REVIEWS', 'product_reviews');
        defined('FILENAME_PRODUCT_REVIEWS_INFO') || define('FILENAME_PRODUCT_REVIEWS_INFO', 'product_reviews_info');
        defined('FILENAME_PRODUCT_REVIEWS_WRITE') || define('FILENAME_PRODUCT_REVIEWS_WRITE', 'product_reviews_write');
        defined('TABLE_GET_TERMS_TO_FILTER') || define('TABLE_GET_TERMS_TO_FILTER', 'get_terms_to_filter');
        defined('TABLE_PRODUCT_TYPES') || define('TABLE_PRODUCT_TYPES', 'product_types');
        defined('HTTP_SERVER') || define('HTTP_SERVER', 'https://example.test');
        defined('HTTPS_SERVER') || define('HTTPS_SERVER', 'https://example.test');
        defined('ENABLE_SSL') || define('ENABLE_SSL', 'false');
        defined('DIR_WS_CATALOG') || define('DIR_WS_CATALOG', '/');
        defined('DIR_WS_HTTPS_CATALOG') || define('DIR_WS_HTTPS_CATALOG', '/');
        defined('SESSION_FORCE_COOKIE_USE') || define('SESSION_FORCE_COOKIE_USE', 'False');
        defined('SEARCH_ENGINE_FRIENDLY_URLS') || define('SEARCH_ENGINE_FRIENDLY_URLS', 'false');

        $GLOBALS['request_type'] = 'NONSSL';
        $GLOBALS['session_started'] = false;
        $GLOBALS['http_domain'] = 'example.test';
        $GLOBALS['https_domain'] = 'example.test';

        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . 'includes/functions/html_output.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_lookups.php';
        require_once DIR_FS_CATALOG . 'includes/classes/breadcrumb.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $GLOBALS['zco_notifier'] = new \notifier();

        $this->initAddCrumbsFile = DIR_FS_CATALOG . 'includes/init_includes/init_add_crumbs.php';

        $GLOBALS['db'] = $this->getMockBuilder(\queryFactory::class)->getMock();

        $GLOBALS['breadcrumb'] = $this->getMockBuilder(\Breadcrumb::class)->getMock();
    }

    public function testGetTermsLookupIsSkippedOnTheShoppingCartPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_SHOPPING_CART;
        $_GET['manufacturers_id'] = 8;

        // Only the recognition function's own product_types query should run --
        // never a get_terms_to_filter (or manufacturers) lookup.
        $GLOBALS['db']->expects($this->once())
            ->method('Execute')
            ->willReturnCallback([$this, 'routeQuery']);

        $this->requireInitAddCrumbs();
    }

    public function testGetTermsLookupIsSkippedOnCheckoutPages(): void
    {
        $GLOBALS['current_page'] = FILENAME_CHECKOUT_SHIPPING;
        $_GET['manufacturers_id'] = 8;

        $GLOBALS['db']->expects($this->once())
            ->method('Execute')
            ->willReturnCallback([$this, 'routeQuery']);

        $this->requireInitAddCrumbs();
    }

    public function testGetTermsLookupStillRunsOnAnOrdinaryPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_DEFAULT;
        $_GET['manufacturers_id'] = 8;

        // The product_types query (for recognition) plus the get_terms_to_filter query.
        $GLOBALS['db']->expects($this->exactly(2))
            ->method('Execute')
            ->willReturnCallback([$this, 'routeQuery']);

        $this->requireInitAddCrumbs();
    }

    /**
     * Routes a mocked Execute() call to the right canned result based on the table
     * named in the query, since zen_page_uses_catalog_breadcrumb_lookups() and the
     * get-terms lookup in init_add_crumbs.php both go through the same $db mock.
     */
    public function routeQuery(string $sql): \queryFactoryResult
    {
        if (str_contains($sql, TABLE_PRODUCT_TYPES)) {
            return $this->productTypesResultSet();
        }

        return $this->emptyResultSet();
    }

    private function productTypesResultSet(): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = [
            ['type_handler' => 'product'],
        ];

        return $result;
    }

    private function emptyResultSet(): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = [];

        return $result;
    }

    /**
     * A plain require() from inside a method only has access to that method's local
     * scope, but init_add_crumbs.php (like all init files) is written to run in the
     * including script's scope directly and references $breadcrumb/$db/$current_page as
     * bare globals -- so those need pulling into local scope here before requiring it.
     */
    private function requireInitAddCrumbs(): void
    {
        global $breadcrumb, $db, $current_page, $zco_notifier;

        require $this->initAddCrumbsFile;
    }
}
