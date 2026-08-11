<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * orders.order_total is stored in the store's default currency -- order.php writes it from the
 * cart's base-currency total, and both admin/orders.php and Customer::getOrderHistory() convert
 * it for display with $currencies->format($total, true, ...).
 *
 * Customer::getLifetimeValue() did neither of those things consistently: it multiplied each order
 * by that order's currency_value before summing (converting each one *out* of the common base
 * unit, then adding unlike currencies), and it formatted the last order's total without
 * converting, so a base-currency figure was shown wearing a foreign currency's symbol.
 *
 * On a single-currency store currency_value is 1.0000 and neither defect is visible, so these
 * tests deliberately use a customer with orders in two currencies.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class CustomerLifetimeValueCurrencyTest extends zcUnitTestCase
{
    private const CUSTOMER_ID = 42;

    /**
     * Exchange rate in effect when the EUR order was placed.
     */
    private const EUR_RATE = 0.7730;

    public function setUp(): void
    {
        // Lifetime value is only calculated admin-side, so this must be set before the
        // bootstrap defines it as false.
        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', true);

        parent::setUp();

        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $_SESSION = [];

        $GLOBALS['zco_notifier'] = new \notifier();

        // Mirrors currencies::rateAdjusted(): the exchange rate is applied only when the caller
        // asks for it, which is precisely what these tests need to observe.
        $GLOBALS['currencies'] = new class {
            public function format($number, $calculate_using_exchange_rate = true, $currency_code = '', $currency_value = ''): string
            {
                $rate = ($calculate_using_exchange_rate === true && !empty($currency_value)) ? (float)$currency_value : 1.0;

                return ($currency_code === 'EUR' ? '€' : '$') . number_format((float)$number * $rate, 2);
            }
        };

        $db = $this->createStub(\queryFactory::class);
        $db->method('Execute')->willReturnCallback([$this, 'routeQuery']);
        $db->method('ExecuteNoCache')->willReturnCallback([$this, 'routeQuery']);
        $db->method('bindVars')->willReturnCallback(
            static fn (string $sql, string $bindVarString, $sqlValue, $type): string => $sql
        );

        $GLOBALS['db'] = $db;
    }

    public function tearDown(): void
    {
        unset($GLOBALS['db'], $GLOBALS['currencies']);
        parent::tearDown();
    }

    /**
     * Both orders total 100.00 and 50.00 in the store's default currency. The first was placed
     * while the customer was browsing in EUR at 0.7730, so they were charged the equivalent of
     * EUR 77.30 -- but 100.00 is what the store took in its own currency.
     */
    public function testLifetimeValueSumsDefaultCurrencyTotalsWithoutReconverting(): void
    {
        $data = (new \Customer(self::CUSTOMER_ID))->getData();

        $this->assertEqualsWithDelta(150.0, $data['lifetime_value'], 0.001);
    }

    /**
     * The last order's total is shown to the admin in the currency the customer paid in, matching
     * Customer::getOrderHistory() and admin/orders.php.
     */
    public function testLastOrderTotalIsConvertedToTheCurrencyTheCustomerPaidIn(): void
    {
        $data = (new \Customer(self::CUSTOMER_ID))->getData();

        $this->assertSame('€77.30', $data['last_order']['order_total']);
        $this->assertSame('2026-07-30 09:15:00', $data['last_order']['date_purchased']);
    }

    /**
     * The unformatted value stays in the store's default currency, so callers doing their own
     * arithmetic are unaffected by the display change.
     */
    public function testLastOrderRawTotalRemainsInTheDefaultCurrency(): void
    {
        $data = (new \Customer(self::CUSTOMER_ID))->getData();

        $this->assertSame('100.0000', $data['last_order']['order_total_raw']);
        $this->assertSame('EUR', $data['last_order']['currency']);
    }

    /**
     * Routes a stubbed query to its canned result. All of Customer::load()'s lookups go through
     * the same stub, so they are told apart by the table (or a distinctive column) named.
     */
    public function routeQuery(string $sql, ...$ignored): \queryFactoryResult
    {
        return $this->makeResult(match (true) {
            str_contains($sql, TABLE_ADDRESS_BOOK) => [],
            str_contains($sql, 'number_of_reviews') => [['number_of_reviews' => '0']],
            str_contains($sql, 'COUNT(*) AS count') => [['count' => '2']],
            str_contains($sql, 'o.orders_id') => $this->orderRows(),
            str_contains($sql, TABLE_CUSTOMERS . ' c') => [$this->customerRow()],
            default => [],
        });
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function orderRows(): array
    {
        return [
            [
                'orders_id' => '1002',
                'date_purchased' => '2026-07-30 09:15:00',
                'order_total_raw' => '100.0000',
                'currency' => 'EUR',
                'currency_value' => (string)self::EUR_RATE,
                'language_code' => 'en',
            ],
            [
                'orders_id' => '1001',
                'date_purchased' => '2026-06-01 11:00:00',
                'order_total_raw' => '50.0000',
                'currency' => 'USD',
                'currency_value' => '1.000000',
                'language_code' => 'en',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function customerRow(): array
    {
        return [
            'customers_id' => (string)self::CUSTOMER_ID,
            'customers_firstname' => 'Testy',
            'customers_lastname' => 'McTest',
            'customers_email_address' => 'mctest@zencart.test',
            'customers_default_address_id' => '0',
            'customers_group_pricing' => '0',
            'customers_authorization' => '0',
            'customers_newsletter' => '0',
            'number_of_logins' => '5',
        ];
    }

    /**
     * @param array<int, array<string, string>> $rows
     */
    private function makeResult(array $rows): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = $rows;
        $result->fields = $rows[0] ?? [];
        $result->EOF = ($rows === []);

        return $result;
    }
}
