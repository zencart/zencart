<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Admin-side, the Customer class loads order statistics (order count, lifetime value and the
 * "last order" details) for every customer it is constructed for. A listing page such as
 * admin/customers.php builds one Customer per row but only displays those statistics for the
 * single selected customer, so every other row paid for two orders-table queries it never used.
 *
 * Customer::__construct() now takes $load_order_statistics; these tests assert on the actual
 * queries issued, since the point of the flag is the queries that no longer run.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class CustomerOrderStatisticsDeferralTest extends zcUnitTestCase
{
    private const CUSTOMER_ID = 42;

    /**
     * Every SQL statement handed to the $db stub, in order.
     *
     * @var string[]
     */
    private array $queries = [];

    public function setUp(): void
    {
        // Customer only loads order statistics admin-side, so this must be set before the
        // bootstrap defines it as false.
        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', true);

        parent::setUp();

        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $_SESSION = [];
        $this->queries = [];

        $GLOBALS['zco_notifier'] = new \notifier();
        $GLOBALS['currencies'] = new class {
            public function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = ''): string
            {
                return '$' . number_format((float)$number, 2);
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

    public function testOrderStatisticsAreLoadedByDefault(): void
    {
        $data = (new \Customer(self::CUSTOMER_ID))->getData();

        $this->assertSame(self::CUSTOMER_ID, $data['customers_id']);
        $this->assertSame(2, $data['number_of_orders']);
        $this->assertEqualsWithDelta(75.0, $data['lifetime_value'], 0.001);
        $this->assertSame('2026-07-30 09:15:00', $data['last_order']['date_purchased']);

        $this->assertCount(2, $this->ordersTableQueries(), 'The order count and lifetime-value queries should both run.');
    }

    public function testOrderStatisticsAreSkippedWhenDeferred(): void
    {
        $data = (new \Customer(self::CUSTOMER_ID, load_order_statistics: false))->getData();

        $this->assertSame([], $this->ordersTableQueries(), 'No orders-table query should be issued when order statistics are deferred.');

        $this->assertArrayNotHasKey('number_of_orders', $data);
        $this->assertArrayNotHasKey('lifetime_value', $data);
        $this->assertArrayNotHasKey('last_order', $data);
    }

    /**
     * Deferring the statistics must not cost the caller any of the ordinary customer data --
     * that is what keeps admin/customers.php on the Customer class instead of a hand-rolled query.
     */
    public function testTheRestOfTheCustomerRecordIsStillLoadedWhenDeferred(): void
    {
        $data = (new \Customer(self::CUSTOMER_ID, load_order_statistics: false))->getData();

        $this->assertSame(self::CUSTOMER_ID, $data['customers_id']);
        $this->assertSame('Testy', $data['customers_firstname']);
        $this->assertSame('mctest@zencart.test', $data['customers_email_address']);
        $this->assertSame(3, $data['number_of_reviews']);
        $this->assertSame([], $data['addresses']);
        $this->assertArrayNotHasKey('customers_password', $data);
    }

    /**
     * Routes a stubbed query to its canned result. All of Customer::load()'s lookups go through
     * the same $db stub, so they are told apart by the table (or distinctive column) named.
     */
    public function routeQuery(string $sql, ...$ignored): \queryFactoryResult
    {
        $this->queries[] = $sql;

        return $this->makeResult(match (true) {
            str_contains($sql, TABLE_ADDRESS_BOOK) => [],
            str_contains($sql, 'number_of_reviews') => [['number_of_reviews' => '3']],
            str_contains($sql, 'COUNT(*) AS count') => [['count' => '2']],
            str_contains($sql, 'o.orders_id') => [
                [
                    'orders_id' => '1002',
                    'date_purchased' => '2026-07-30 09:15:00',
                    'order_total_raw' => '50.0000',
                    'currency' => 'USD',
                    'currency_value' => '1.000000',
                    'language_code' => 'en',
                ],
                [
                    'orders_id' => '1001',
                    'date_purchased' => '2026-06-01 11:00:00',
                    'order_total_raw' => '25.0000',
                    'currency' => 'USD',
                    'currency_value' => '1.000000',
                    'language_code' => 'en',
                ],
            ],
            str_contains($sql, TABLE_CUSTOMERS . ' c') => [$this->customerRow()],
            default => [],
        });
    }

    private function customerRow(): array
    {
        return [
            'customers_id' => (string)self::CUSTOMER_ID,
            'customers_firstname' => 'Testy',
            'customers_lastname' => 'McTest',
            'customers_email_address' => 'mctest@zencart.test',
            'customers_password' => 'not-loaded-into-data',
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

    /**
     * @return string[]
     */
    private function ordersTableQueries(): array
    {
        return array_values(array_filter(
            $this->queries,
            static fn (string $sql): bool => (bool)preg_match('/\bFROM\s+' . preg_quote(TABLE_ORDERS, '/') . '\b/i', $sql)
        ));
    }
}
