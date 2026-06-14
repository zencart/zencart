<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class OtGvShippingDetailsTest extends zcUnitTestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    private function createResult(array $rows): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = $rows;
        $result->EOF = empty($rows);
        $result->fields = $rows[0] ?? [];

        return $result;
    }

    public function setUp(): void
    {
        if (!defined('DISPLAY_PRICE_WITH_TAX')) {
            define('DISPLAY_PRICE_WITH_TAX', 'false');
        }
        parent::setUp();

        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_customers.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_taxes.php';
        require_once DIR_FS_CATALOG . 'includes/modules/order_total/ot_gv.php';

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

        $rateResult = $this->createResult([
            ['tax_rate' => 10.0],
        ]);

        $descriptionResult = $this->createResult([
            ['tax_description' => 'SHIPPING TAX 10%'],
        ]);

        $emptyResult = $this->createResult([]);

        $GLOBALS['db'] = $this->getMockBuilder('queryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['db']->method('Execute')->willReturnCallback(
            static function (string $sql) use ($rateResult, $descriptionResult, $emptyResult) {
                if (str_contains($sql, 'sum(tax_rate) AS tax_rate')) {
                    return $rateResult;
                }

                if (str_contains($sql, 'trd.tax_description')) {
                    return $descriptionResult;
                }

                return $emptyResult;
            }
        );
    }

    public function testGetOrderTotalRecomputesShippingTaxDetailsWhenSessionDescriptionIsMissing(): void
    {
        $module = new class extends \ot_gv {
            public function __construct()
            {
            }

            public function fetchShippingTaxDetails(): array
            {
                return $this->get_shipping_tax_details();
            }

            public function fetchOrderTotal(): array
            {
                return $this->get_order_total();
            }
        };
        $module->include_tax = 'true';
        $module->include_shipping = 'false';

        $shippingTaxDetails = $module->fetchShippingTaxDetails();
        $this->assertSame(0.50, round($shippingTaxDetails['amount'], 2));
        $this->assertSame('SHIPPING TAX 10%', $shippingTaxDetails['description']);

        $orderTotal = $module->fetchOrderTotal();
        $expectedTotal = 48.29 - 5.00;
        if (DISPLAY_PRICE_WITH_TAX !== 'true') {
            $expectedTotal -= 0.50;
        }
        $this->assertSame($expectedTotal, round($orderTotal['total'], 2));
        $this->assertSame(0.0, (float) $orderTotal['tax_groups']['SHIPPING TAX 10%']);
        $this->assertSame(2.80, $orderTotal['tax_groups']['FL TAX 7.0%']);
    }
}
