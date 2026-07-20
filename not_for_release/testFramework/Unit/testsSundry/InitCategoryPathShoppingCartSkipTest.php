<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921: init_category_path.php used to derive $cPath
 * directly from a bot-supplied cPath GET param (or from products_id via a category-path
 * DB lookup) regardless of the current page -- so a bot spoofing cPath on the shopping
 * cart or a checkout page could still force category breadcrumb queries even after
 * init_add_crumbs.php's own get-terms/product-name lookups were skipped there. Only
 * pages recognized by zen_page_uses_catalog_breadcrumb_lookups() as legitimately using
 * catalog GET params now derive $cPath from them at all; every other page ignores
 * cPath/products_id entirely. That recognition function itself issues one (cached,
 * request-scoped) query against product_types, so these tests assert on the *number*
 * and *content* of queries rather than a blanket zero.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class InitCategoryPathShoppingCartSkipTest extends zcUnitTestCase
{
    private string $initCategoryPathFile;

    public function setUp(): void
    {
        parent::setUp();

        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', false);
        defined('FILENAME_DEFAULT') || define('FILENAME_DEFAULT', 'index');
        defined('FILENAME_SHOPPING_CART') || define('FILENAME_SHOPPING_CART', 'shopping_cart');
        defined('FILENAME_CHECKOUT_PAYMENT') || define('FILENAME_CHECKOUT_PAYMENT', 'checkout_payment');
        defined('FILENAME_SEARCH_RESULT') || define('FILENAME_SEARCH_RESULT', 'search_result');
        defined('FILENAME_SPECIALS') || define('FILENAME_SPECIALS', 'specials');
        defined('FILENAME_PRODUCTS_NEW') || define('FILENAME_PRODUCTS_NEW', 'products_new');
        defined('FILENAME_PRODUCTS_ALL') || define('FILENAME_PRODUCTS_ALL', 'products_all');
        defined('FILENAME_FEATURED_PRODUCTS') || define('FILENAME_FEATURED_PRODUCTS', 'featured_products');
        defined('FILENAME_PRODUCT_REVIEWS') || define('FILENAME_PRODUCT_REVIEWS', 'product_reviews');
        defined('FILENAME_PRODUCT_REVIEWS_INFO') || define('FILENAME_PRODUCT_REVIEWS_INFO', 'product_reviews_info');
        defined('FILENAME_PRODUCT_REVIEWS_WRITE') || define('FILENAME_PRODUCT_REVIEWS_WRITE', 'product_reviews_write');
        defined('TABLE_PRODUCT_TYPES') || define('TABLE_PRODUCT_TYPES', 'product_types');
        defined('SHOW_CATEGORIES_ALWAYS') || define('SHOW_CATEGORIES_ALWAYS', '0');
        defined('TOPMOST_CATEGORY_PARENT_ID') || define('TOPMOST_CATEGORY_PARENT_ID', 0);

        require_once DIR_FS_CATALOG . 'includes/classes/traits/NotifierManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/traits/ObserverManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_lookups.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $GLOBALS['zco_notifier'] = new \notifier();

        $this->initCategoryPathFile = DIR_FS_CATALOG . 'includes/init_includes/init_category_path.php';

        $GLOBALS['db'] = $this->getMockBuilder(\queryFactory::class)->getMock();
    }

    public function testASpoofedCPathIsIgnoredOnTheShoppingCartPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_SHOPPING_CART;
        $_GET['cPath'] = '1_4';
        $_GET['products_id'] = 5;

        // Only the recognition function's own product_types query should run --
        // never a category-path or product-path lookup.
        $GLOBALS['db']->expects($this->once())
            ->method('Execute')
            ->willReturnCallback([$this, 'routeQuery']);

        $this->requireInitCategoryPath();

        $this->assertSame('', $GLOBALS['cPath']);
        $this->assertSame([], $GLOBALS['cPath_array']);
    }

    public function testASpoofedProductsIdIsIgnoredOnACheckoutPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_CHECKOUT_PAYMENT;
        $_GET['products_id'] = 5;

        $GLOBALS['db']->expects($this->once())
            ->method('Execute')
            ->willReturnCallback([$this, 'routeQuery']);

        $this->requireInitCategoryPath();

        $this->assertSame('', $GLOBALS['cPath']);
        $this->assertSame([], $GLOBALS['cPath_array']);
    }

    public function testAnExplicitCPathStillWorksOnAnOrdinaryPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_DEFAULT;
        $_GET['cPath'] = '1_4';

        // The product_types query (for recognition) -- but a directly-supplied cPath
        // is taken as-is with no further lookup needed to derive it.
        $GLOBALS['db']->expects($this->once())
            ->method('Execute')
            ->willReturnCallback([$this, 'routeQuery']);

        $this->requireInitCategoryPath();

        $this->assertSame('1_4', $GLOBALS['cPath']);
        $this->assertSame([1, 4], $GLOBALS['cPath_array']);
    }

    /**
     * Routes a mocked Execute() call to the right canned result based on the table
     * named in the query, since zen_page_uses_catalog_breadcrumb_lookups() and
     * init_category_path.php's own lookups both go through the same $db mock.
     */
    public function routeQuery(string $sql): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = str_contains($sql, TABLE_PRODUCT_TYPES)
            ? [['type_handler' => 'product']]
            : [];

        return $result;
    }

    /**
     * A plain require() from inside a method only has access to that method's local
     * scope, but init_category_path.php (like all init files) is written to run in the
     * including script's scope directly and references $current_page/$db as bare
     * globals -- so those need pulling into local scope here before requiring it.
     */
    private function requireInitCategoryPath(): void
    {
        global $db, $current_page, $zco_notifier;

        require $this->initCategoryPathFile;

        $GLOBALS['cPath'] = $cPath;
        $GLOBALS['cPath_array'] = $cPath_array;
    }
}
