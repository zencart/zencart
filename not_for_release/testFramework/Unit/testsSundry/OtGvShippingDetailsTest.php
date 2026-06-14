<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class TestableOtGvShippingDetails extends \ot_gv
{
    public function fetchShippingTaxDetails(): array
    {
        return $this->get_shipping_tax_details();
    }

    public function fetchOrderTotal(): array
    {
        return $this->get_order_total();
    }
}

class OtGvShippingDetailsTest extends zcUnitTestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_customers.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_taxes.php';
        require_once DIR_FS_CATALOG . 'includes/modules/order_total/ot_gv.php';

        if (!defined('DISPLAY_PRICE_WITH_TAX')) {
            define('DISPLAY_PRICE_WITH_TAX', 'false');
        }
        if (!defined('TEXT_UNKNOWN_TAX_RATE')) {
            define('TEXT_UNKNOWN_TAX_RATE', 'Unknown Tax');
        }

        $_SESSION['languages_id'] = 1;
        $_SESSION['shipping'] = ['id' => 'flat_flat'];
        unset($_SESSION['shipping_tax_description']);
        $_SESSION['cart'] = $this->getMockBuilder(\shoppingCart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $_SESSION['cart']->method('get_products')->willReturn([]);

        $GLOBALS['flat'] = new \stdClass();
        $GLOBALS['flat']->tax_class = 2;
        $GLOBALS['flat']->tax_basis = 'Shipping';

        $GLOBALS['order'] = new \stdClass();
        $GLOBALS['order']->info = [
            'total' => 48.29,
            'tax' => 3.30,
            'shipping_cost' => 5.00,
            'shipping_tax' => 0.0,
            'tax_groups' => [
                'FL TAX 7.0%' => 2.80,
                'SHIPPING TAX 10%' => 0.50,
            ],
        ];
        $GLOBALS['order']->billing = [
            'country' => ['id' => 223],
            'zone_id' => 18,
        ];
        $GLOBALS['order']->delivery = [
            'country' => ['id' => 223],
            'zone_id' => 18,
        ];

        $rateResult = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $rateResult->method('RecordCount')->willReturn(1);
        $this->mockIterator($rateResult, [
            ['tax_rate' => 10.0],
        ]);

        $descriptionResult = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $descriptionResult->method('RecordCount')->willReturn(1);
        $this->mockIterator($descriptionResult, [
            ['tax_description' => 'SHIPPING TAX 10%'],
        ]);

        $GLOBALS['db'] = $this->getMockBuilder('queryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['db']->method('Execute')->willReturnOnConsecutiveCalls($rateResult, $descriptionResult);
    }

    public function testGetOrderTotalRecomputesShippingTaxDetailsWhenSessionDescriptionIsMissing(): void
    {
        $reflection = new \ReflectionClass(TestableOtGvShippingDetails::class);
        /** @var TestableOtGvShippingDetails $module */
        $module = $reflection->newInstanceWithoutConstructor();
        $module->include_tax = 'true';
        $module->include_shipping = 'false';

        $shippingTaxDetails = $module->fetchShippingTaxDetails();
        $this->assertSame(0.50, round($shippingTaxDetails['amount'], 2));
        $this->assertSame('SHIPPING TAX 10%', $shippingTaxDetails['description']);

        $orderTotal = $module->fetchOrderTotal();
        $this->assertSame(42.79, round($orderTotal['total'], 2));
        $this->assertSame(0.0, (float) $orderTotal['tax_groups']['SHIPPING TAX 10%']);
        $this->assertSame(2.80, $orderTotal['tax_groups']['FL TAX 7.0%']);
    }
}
