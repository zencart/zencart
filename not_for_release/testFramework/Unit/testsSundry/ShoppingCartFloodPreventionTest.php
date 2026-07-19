<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921: shoppingCart::add_cart() now refuses to add a
 * new distinct line item once the cart already holds self::MAX_CART_DISTINCT_LINE_ITEMS (200)
 * distinct items, guarding against bots flooding a session's cart with many fake products.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;
use shoppingCart;

#[RunTestsInSeparateProcesses]
class ShoppingCartFloodPreventionTest extends zcUnitTestCase
{
    // Non-empty, non-text-attribute params avoid zen_has_product_attributes()'s DB/notifier
    // lookup entirely (only triggered by add_cart() when $attributes is empty).
    private const ATTRIBUTES = [5 => 10];

    public function setUp(): void
    {
        parent::setUp();

        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', false);
        defined('QUANTITY_DECIMALS') || define('QUANTITY_DECIMALS', 0);
        defined('STOCK_ALLOW_CHECKOUT') || define('STOCK_ALLOW_CHECKOUT', 'true');
        defined('WARNING_CART_ITEM_LIMIT_REACHED') || define(
            'WARNING_CART_ITEM_LIMIT_REACHED',
            'Your shopping cart has reached the maximum number of different items allowed. Please remove an item before adding another.'
        );

        require_once DIR_FS_CATALOG . 'includes/classes/traits/NotifierManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/traits/ObserverManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_customers.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_products.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . 'includes/functions/password_funcs.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general_shared.php';
        require_once DIR_FS_CATALOG . 'includes/functions/database.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/shopping_cart.php';

        $GLOBALS['zco_notifier'] = new \notifier();

        // zen_get_products_stock() (called from update_quantity()/add_cart()'s new-item path)
        // otherwise pulls in a real Product/language/db chain -- short-circuit it via the
        // observer hook it already provides for exactly this purpose.
        $stockObserver = new StubZenGetProductsStockObserver();
        $GLOBALS['zco_notifier']->attach($stockObserver, ['ZEN_GET_PRODUCTS_STOCK']);

        $GLOBALS['db'] = $this->getMockBuilder(\queryFactory::class)->getMock();
        $GLOBALS['db']->method('prepare_input')->willReturnArgument(0);

        // Guest session -- zen_is_logged_in() returns false, so add_cart() never touches
        // the database (TABLE_CUSTOMERS_BASKET / TABLE_CUSTOMERS_BASKET_ATTRIBUTES inserts
        // are all gated on being logged in and not in guest checkout).
        unset($_SESSION['customer_id']);
    }

    public function testAddingADistinctProductUnderTheCapSucceeds(): void
    {
        $cart = new shoppingCart();
        $cart->contents = $this->fakeDistinctCartContents(199);

        $GLOBALS['messageStack'] = $this->getMockBuilder(\messageStack::class)
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['messageStack']->expects($this->never())->method('add_session');

        $newProductId = 90200;
        $cart->add_cart($newProductId, 1, self::ATTRIBUTES, false);

        $this->assertCount(
            200,
            $cart->contents,
            'Expected the 200th distinct product to be accepted (cap is a floor, not a ceiling).'
        );
        $this->assertArrayHasKey(\zen_get_uprid($newProductId, self::ATTRIBUTES), $cart->contents);
    }

    public function testAddingA201stDistinctProductIsRejectedWithAWarning(): void
    {
        $cart = new shoppingCart();
        $cart->contents = $this->fakeDistinctCartContents(200);

        $GLOBALS['messageStack'] = $this->getMockBuilder(\messageStack::class)
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['messageStack']->expects($this->once())
            ->method('add_session')
            ->with('header', WARNING_CART_ITEM_LIMIT_REACHED, 'caution');

        $newProductId = 90201;
        $cart->add_cart($newProductId, 1, self::ATTRIBUTES, false);

        $this->assertCount(
            200,
            $cart->contents,
            'Expected the 201st distinct product to be rejected -- cart size must stay at the cap.'
        );
        $this->assertArrayNotHasKey(\zen_get_uprid($newProductId, self::ATTRIBUTES), $cart->contents);
    }

    public function testUpdatingAnExistingProductsQuantityIsNotBlockedByTheCap(): void
    {
        $existingProductId = 90100;
        $existingUprid = \zen_get_uprid($existingProductId, self::ATTRIBUTES);

        $cart = new shoppingCart();
        $cart->contents = $this->fakeDistinctCartContents(199);
        $cart->contents[$existingUprid] = ['qty' => 1.0];

        $GLOBALS['messageStack'] = $this->getMockBuilder(\messageStack::class)
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['messageStack']->expects($this->never())->method('add_session');

        $cart->add_cart($existingProductId, 3, self::ATTRIBUTES, false);

        $this->assertCount(
            200,
            $cart->contents,
            'Expected updating an existing product\'s quantity not to add a new cart entry.'
        );
        $this->assertSame(
            3.0,
            $cart->contents[$existingUprid]['qty'],
            'Expected the existing product\'s quantity to be updated, not blocked by the distinct-product cap.'
        );
    }

    /** @return array<string, array{qty: float}> */
    private function fakeDistinctCartContents(int $count): array
    {
        $contents = [];
        for ($i = 1; $i <= $count; $i++) {
            $contents['fake-product-' . $i] = ['qty' => 1.0];
        }

        return $contents;
    }
}

/**
 * Short-circuits zen_get_products_stock() via its own observer hook, so update_quantity()
 * doesn't need a real Product/language/db dependency chain in these tests.
 */
class StubZenGetProductsStockObserver
{
    public function update($class, $eventID, &$param1 = null, &$param2 = null, &$param3 = null): void
    {
        if ($eventID !== 'ZEN_GET_PRODUCTS_STOCK') {
            return;
        }

        $param2 = 999; // products_quantity: plenty of stock
        $param3 = true; // quantity_handled
    }
}
