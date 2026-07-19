<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921: init_category_path.php used to derive $cPath
 * directly from a bot-supplied cPath GET param (or from products_id via a category-path
 * DB lookup) regardless of the current page -- so a bot spoofing cPath on the shopping
 * cart or a checkout page could still force category breadcrumb queries even after
 * init_add_crumbs.php's own get-terms/product-name lookups were skipped there. Pages
 * identified by zen_page_skips_catalog_breadcrumb_lookups() (shopping cart + checkout_*)
 * now ignore cPath/products_id entirely and never touch the database here.
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
        defined('FILENAME_CHECKOUT_SHIPPING') || define('FILENAME_CHECKOUT_SHIPPING', 'checkout_shipping');
        defined('FILENAME_CHECKOUT_SHIPPING_ADDRESS') || define('FILENAME_CHECKOUT_SHIPPING_ADDRESS', 'checkout_shipping_address');
        defined('FILENAME_CHECKOUT_PAYMENT') || define('FILENAME_CHECKOUT_PAYMENT', 'checkout_payment');
        defined('FILENAME_CHECKOUT_PAYMENT_ADDRESS') || define('FILENAME_CHECKOUT_PAYMENT_ADDRESS', 'checkout_payment_address');
        defined('FILENAME_CHECKOUT_CONFIRMATION') || define('FILENAME_CHECKOUT_CONFIRMATION', 'checkout_confirmation');
        defined('FILENAME_CHECKOUT_PROCESS') || define('FILENAME_CHECKOUT_PROCESS', 'checkout_process');
        defined('FILENAME_CHECKOUT_SUCCESS') || define('FILENAME_CHECKOUT_SUCCESS', 'checkout_success');
        defined('SHOW_CATEGORIES_ALWAYS') || define('SHOW_CATEGORIES_ALWAYS', '0');
        defined('TOPMOST_CATEGORY_PARENT_ID') || define('TOPMOST_CATEGORY_PARENT_ID', 0);

        require_once DIR_FS_CATALOG . 'includes/classes/traits/NotifierManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/traits/ObserverManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_lookups.php';

        $GLOBALS['zco_notifier'] = new \notifier();

        $this->initCategoryPathFile = DIR_FS_CATALOG . 'includes/init_includes/init_category_path.php';

        $GLOBALS['db'] = $this->getMockBuilder(\queryFactory::class)->getMock();
    }

    public function testASpoofedCPathIsIgnoredOnTheShoppingCartPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_SHOPPING_CART;
        $_GET['cPath'] = '1_4';
        $_GET['products_id'] = 5;

        $GLOBALS['db']->expects($this->never())->method('Execute');

        $this->requireInitCategoryPath();

        $this->assertSame('', $GLOBALS['cPath']);
        $this->assertSame([], $GLOBALS['cPath_array']);
    }

    public function testASpoofedProductsIdIsIgnoredOnACheckoutPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_CHECKOUT_PAYMENT;
        $_GET['products_id'] = 5;

        $GLOBALS['db']->expects($this->never())->method('Execute');

        $this->requireInitCategoryPath();

        $this->assertSame('', $GLOBALS['cPath']);
        $this->assertSame([], $GLOBALS['cPath_array']);
    }

    public function testAnExplicitCPathStillWorksOnAnOrdinaryPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_DEFAULT;
        $_GET['cPath'] = '1_4';

        // A directly-supplied cPath is taken as-is -- no DB lookup needed to derive it.
        $GLOBALS['db']->expects($this->never())->method('Execute');

        $this->requireInitCategoryPath();

        $this->assertSame('1_4', $GLOBALS['cPath']);
        $this->assertSame([1, 4], $GLOBALS['cPath_array']);
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
