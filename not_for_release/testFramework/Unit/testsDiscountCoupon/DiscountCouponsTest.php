<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcDiscountCouponTest;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Unit Tests for discount coupons
 */
#[RunTestsInSeparateProcesses]
class DiscountCouponsTest extends zcDiscountCouponTest
{
    public ot_coupon $coupon;

    public function setUp(): void
    {
        parent::setUp();
        require_once(TESTCWD . 'Support/functionsDiscountCoupons.php');
        require_once(TESTCWD . 'Support/StubCouponValidation.php');
        CouponValidation::$productsNotValidForSales = [];
        require_once DIR_FS_CATALOG . 'includes/modules/order_total/ot_coupon.php';
        require_once DIR_FS_CATALOG . 'includes/classes/shopping_cart.php';
        require_once DIR_FS_CATALOG . 'includes/classes/currencies.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        $_SESSION['currency'] = 'USD';
        $_SESSION['cc_id'] = '1';
        defined('MODULE_ORDER_TOTAL_COUPON_HEADER') || define('MODULE_ORDER_TOTAL_COUPON_HEADER', '');
        defined('MODULE_ORDER_TOTAL_COUPON_TITLE') || define('MODULE_ORDER_TOTAL_COUPON_TITLE', '');
        defined('MODULE_ORDER_TOTAL_COUPON_DESCRIPTION') || define('MODULE_ORDER_TOTAL_COUPON_DESCRIPTION', '');
        defined('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER') || define('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '');
        defined('MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING') || define('MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', '');
        defined('MODULE_ORDER_TOTAL_COUPON_INC_TAX') || define('MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false');
        defined('MODULE_ORDER_TOTAL_COUPON_CALC_TAX') || define('MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard');
        defined('MODULE_ORDER_TOTAL_COUPON_TAX_CLASS') || define('MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '');

        $GLOBALS['currencies'] = $this->getMockBuilder('currencies')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['currencies']->method('get_decimal_places')->willReturn(2);

        $GLOBALS['order'] = $this->getMockBuilder('order')
            ->disableOriginalConstructor()
            ->getMock();
        $_SESSION['cart'] = $this->getMockBuilder('shoppingCart')
            ->disableOriginalConstructor()
            ->getMock();
        $products[] = [
            'id' => 27,
            'category' => 5,
            'name' => ' Packard LaserJet 1100Xi Linked',
            'model' => 'HPLJ1100XI',
            'image' => 'hewlett_packard/lj1100xi.gif',
            'price' => 499.99,
            'quantity' => 1,
            'weight' => 45,
            'final_price' => 499.99,
            'onetime_charges' => 0,
            'tax_class_id' => 1,
            'attributes' => '',
            'attributes_values' => '',
            'products_priced_by_attribute' => 0,
            'product_is_free' => 0,
            'products_discount_type' => 0,
            'products_discount_type_from' => 0,
            'products_virtual' => 0,
            'product_is_always_free_shipping' => 0,
            'products_quantity_order_min' => 1,
            'products_quantity_order_units' => 1,
            'products_quantity_order_max' => 0,
            'products_quantity_mixed' => 0,
            'products_mixed_discount_quantity' => 1,
        ];
        $_SESSION['cart']->method('get_products')->willReturn($products);
    }

    /**
     * 10% coupon - include shipping = true - no tax calculations
     */
    public function testPercentageOffCoupon1(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 502.49,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(50.25, $result[0]['value']);
        $this->assertEquals(452.24, $GLOBALS['order']->info['total']);
        $this->assertEquals(2.50, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * 10% coupon - include shipping = false - no tax calculations
     */
    public function testPercentageOffCoupon2(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 502.49,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(50, $result[0]['value']);
        $this->assertEquals(452.49, $GLOBALS['order']->info['total']);
        $this->assertEquals(2.50, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * 100% coupon - include shipping = true - no tax calculations
     */
    public function testPercentageOffCoupon3(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 502.49,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 100,
            'coupon_type' => 'P',
            'coupon_calc_base' => 0,
            'coupon_product_count' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(502.49, $result[0]['value']);
        $this->assertEquals(0, $GLOBALS['order']->info['total']);
        $this->assertEquals(2.50, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * 100% coupon - include shipping = false - no tax calculations
     */
    public function testPercentageOffCoupon4(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 502.49,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 100,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(499.99, $result[0]['value']);
        $this->assertEquals(2.50, $GLOBALS['order']->info['total']);
        $this->assertEquals(2.50, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * Fixed coupon - include shipping = false - no tax calculations
     */
    public function testFixedOffCoupon1(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 502.49,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 501,
            'coupon_type' => 'F',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(499.99, $result[0]['value']);
        $this->assertEquals(2.50, $GLOBALS['order']->info['total']);
        $this->assertEquals(2.50, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * Fixed coupon - include shipping = true - no tax calculations
     */
    public function testFixedOffCoupon2(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 502.49,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 501,
            'coupon_type' => 'F',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(501, $result[0]['value']);
        $this->assertEquals(1.49, round($GLOBALS['order']->info['total'], 4));
        $this->assertEquals(2.50, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * Fixed coupon - include shipping = true - tax calculations
     */
    public function testFixedOffCoupon3(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => ['FL TAX 7.0%' => 34.9993],
            'tax' => 34.9993,
            'total' => 537.4893,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 501,
            'coupon_type' => 'F',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(501, $result[0]['value']);
        $this->assertEquals(1.5893, round($GLOBALS['order']->info['total'], 4));
        $this->assertEquals(2.50, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * Fixed coupon - include shipping = false - tax calculations
     */
    public function testFixedOffCoupon4(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => ['FL TAX 7.0%' => 34.9993],
            'tax' => 34.9993,
            'total' => 537.4893,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 501,
            'coupon_type' => 'F',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(499.99, $result[0]['value']);
        $this->assertEquals(2.4992999999999483, $GLOBALS['order']->info['total']);
        $this->assertEquals(2.50, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * Fixed coupon + Free Shipping - include shipping = false - no tax calculations
     */
    public function testFixedOffCoupon5(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 502.49,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 400,
            'coupon_type' => 'O',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(402.50, $result[0]['value']);
        $this->assertEquals(99.99, round($GLOBALS['order']->info['total'], 4));
        $this->assertEquals(0, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * Fixed coupon + Free Shipping - include shipping = true - no tax calculations
     */
    public function testFixedOffCoupon6(): void
    {
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 33.25,
            'shipping_cost' => 5.75,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 40,
            'coupon_type' => 'O',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(39.00, $result[0]['value']);
        $this->assertEquals(0, $GLOBALS['order']->info['total']);
        $this->assertEquals(0, $GLOBALS['order']->info['shipping_cost']);
    }

    /**
     * Replace the cart mock with one holding the given [id, final_price, quantity] rows
     */
    protected function setCartProducts(array $rows): void
    {
        $products = [];
        $quantities = [];
        foreach ($rows as [$id, $price, $qty]) {
            $products[] = [
                'id' => $id,
                'final_price' => $price,
                'quantity' => $qty,
                'tax_class_id' => 0,
            ];
            $quantities[$id] = $qty;
        }
        $_SESSION['cart'] = $this->getMockBuilder('shoppingCart')
            ->disableOriginalConstructor()
            ->getMock();
        $_SESSION['cart']->method('get_products')->willReturn($products);
        $_SESSION['cart']->method('get_quantity')->willReturnCallback(fn($id) => $quantities[$id] ?? 0);
    }

    /**
     * Keep the coupon row for the coupon lookup; all other queries (tax rates/descriptions) return no rows
     */
    protected function useEmptyResultsForNonCouponQueries(): void
    {
        require_once DIR_FS_CATALOG . 'includes/functions/functions_customers.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_taxes.php';
        defined('TEXT_UNKNOWN_TAX_RATE') || define('TEXT_UNKNOWN_TAX_RATE', 'Unknown Tax');
        /**
         * zen_get_tax_rate() and Customer::isTaxExempt() read these as bare constants on this
         * branch rather than through zen_config(), so they have to exist before the first call.
         * All values are the install defaults; the mocked db returns no tax rows, so the rate is 0.
         */
        defined('WHOLESALE_PRICING_CONFIG') || define('WHOLESALE_PRICING_CONFIG', 'false');
        defined('STORE_COUNTRY') || define('STORE_COUNTRY', '223');
        defined('STORE_ZONE') || define('STORE_ZONE', '18');
        defined('STORE_PRODUCT_TAX_BASIS') || define('STORE_PRODUCT_TAX_BASIS', 'Shipping');
        /** zen_get_tax_description() reads this unconditionally */
        $_SESSION['languages_id'] = 1;
        $couponResult = $GLOBALS['db']->Execute('');
        $emptyResult = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $emptyResult->EOF = true;
        $emptyResult->fields = [];
        $emptyResult->method('RecordCount')->willReturn(0);
        $GLOBALS['db'] = $this->getMockBuilder('queryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['db']->method('execute')->willReturnCallback(
            fn($sql) => str_contains((string)$sql, TABLE_COUPONS) ? $couponResult : $emptyResult
        );
    }

    /**
     * Fixed amount per product, coupon not valid for sale items:
     * only the non-sale product counts towards the per-product discount
     */
    public function testFixedOffPerProductSkipsSaleItems(): void
    {
        $this->setCartProducts([[27, 20.00, 1], [28, 8.00, 10]]);
        CouponValidation::$productsNotValidForSales = [28];
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 102.50,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 5,
            'coupon_type' => 'F',
            'coupon_product_count' => 1,
            'coupon_calc_base' => 0,
        ]);
        $this->useEmptyResultsForNonCouponQueries();
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(5.00, $result[0]['value']);
        $this->assertEquals(97.50, round($GLOBALS['order']->info['total'], 4));
    }

    /**
     * Fixed amount per product larger than the product price:
     * the discount is capped at the qualifying products' value
     */
    public function testFixedOffPerProductCappedAtQualifyingTotal(): void
    {
        $this->setCartProducts([[27, 4.00, 3]]);
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 14.50,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 5,
            'coupon_type' => 'F',
            'coupon_product_count' => 1,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(12.00, $result[0]['value']);
        $this->assertEquals(2.50, round($GLOBALS['order']->info['total'], 4));
    }

    /**
     * Fixed amount per product + Free Shipping, amount larger than the product price:
     * the product part is capped at the qualifying products' value, shipping is added on top
     */
    public function testFixedOffFreeShippingPerProductCappedAtQualifyingTotal(): void
    {
        $this->setCartProducts([[27, 4.00, 3]]);
        $GLOBALS['order']->info = [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 14.50,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr([
            'coupon_id' => '1',
            'coupon_code' => 'test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 5,
            'coupon_type' => 'O',
            'coupon_product_count' => 1,
            'coupon_calc_base' => 0,
        ]);
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process();
        $result = $this->coupon->output;
        $this->assertEquals(14.50, $result[0]['value']);
        $this->assertEquals(0, round($GLOBALS['order']->info['total'], 4));
        $this->assertEquals(0, $GLOBALS['order']->info['shipping_cost']);
    }
}
