<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Customer::getOrderHistory() used to run a COUNT(*) against orders_products once per order in
 * its result loop. That is paid on admin/customers.php (which asks for 5 orders for the selected
 * customer) and on the storefront account and account_history pages.
 *
 * The counts are now gathered for the whole page in a single grouped query. These tests assert on
 * the number of statements issued as well as the values, since the point of the change is the
 * queries that no longer run.
 */

declare(strict_types=1);

namespace Tests\Unit\testsSundry;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class CustomerOrderHistoryProductCountTest extends zcUnitTestCase
{
    private const CUSTOMER_ID = 42;

    /**
     * Line-item counts the orders_products lookup should report, keyed by orders_id. Order 1001
     * is deliberately absent: an order with no line-items returns no row from a grouped count.
     *
     * @var array<int, int>
     */
    private array $lineItemCounts = [1003 => 4, 1002 => 1];

    /**
     * @var string[]
     */
    private array $queries = [];

    /**
     * Rows the order-history lookup should return. Overridden by one test below.
     *
     * @var array<int, array<string, string>>|null
     */
    private ?array $orderHistoryRows = null;

    public function setUp(): void
    {
        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', true);

        parent::setUp();

        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $_SESSION = ['languages_id' => 1];
        $this->queries = [];

        $GLOBALS['zco_notifier'] = new \notifier();
        $GLOBALS['currencies'] = new class {
            public function format($number, $calculate_using_exchange_rate = true, $currency_code = '', $currency_value = ''): string
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

    public function testTheLineItemCountsAreGatheredInASingleQuery(): void
    {
        $customer = new \Customer(self::CUSTOMER_ID);
        $this->queries = [];

        $customer->getOrderHistory();

        $this->assertCount(
            1,
            $this->queriesReadingFrom(TABLE_ORDERS_PRODUCTS),
            'Three orders should still cost exactly one orders_products query.'
        );
    }

    public function testEachOrderReportsItsOwnLineItemCount(): void
    {
        $history = (new \Customer(self::CUSTOMER_ID))->getOrderHistory();

        $this->assertCount(3, $history);
        $this->assertSame([1003, 1002, 1001], array_column($history, 'orders_id'));
        $this->assertSame([4, 1, 0], array_column($history, 'product_count'));
    }

    /**
     * An order with no matching orders_products rows is absent from a grouped count, so the
     * lookup result has to be treated as sparse rather than positional.
     */
    public function testAnOrderWithNoLineItemsReportsZero(): void
    {
        $history = (new \Customer(self::CUSTOMER_ID))->getOrderHistory();

        $emptyOrder = array_values(array_filter($history, static fn (array $o): bool => $o['orders_id'] === 1001));

        $this->assertCount(1, $emptyOrder);
        $this->assertSame(0, $emptyOrder[0]['product_count']);
    }

    /**
     * A customer with no orders must not issue a lookup with an empty IN() list.
     */
    public function testNoLookupIsIssuedForACustomerWithNoOrders(): void
    {
        $customer = new \Customer(self::CUSTOMER_ID);
        $this->orderHistoryRows = [];
        $this->queries = [];

        $history = $customer->getOrderHistory();

        $this->assertSame([], $history);
        $this->assertSame([], $this->queriesReadingFrom(TABLE_ORDERS_PRODUCTS));
    }

    public function routeQuery(string $sql, ...$ignored): \queryFactoryResult
    {
        $this->queries[] = $sql;

        return $this->makeResult(match (true) {
            str_contains($sql, TABLE_ORDERS_PRODUCTS) => $this->lineItemCountRows($sql),
            str_contains($sql, TABLE_ADDRESS_BOOK) => [],
            str_contains($sql, 'number_of_reviews') => [['number_of_reviews' => '0']],
            str_contains($sql, 'COUNT(*) AS count') => [['count' => '0']],
            str_contains($sql, 'o.delivery_name') => $this->orderHistoryRows(),
            str_contains($sql, TABLE_CUSTOMERS . ' c') => [$this->customerRow()],
            default => [],
        });
    }

    /**
     * Mimics a grouped count: only orders named in the IN() list and having line-items come back.
     *
     * @return array<int, array<string, string>>
     */
    private function lineItemCountRows(string $sql): array
    {
        $rows = [];
        foreach ($this->lineItemCounts as $orders_id => $count) {
            if (preg_match('/\b' . $orders_id . '\b/', $sql)) {
                $rows[] = ['orders_id' => (string)$orders_id, 'count' => (string)$count];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function orderHistoryRows(): array
    {
        if ($this->orderHistoryRows !== null) {
            return $this->orderHistoryRows;
        }

        $rows = [];
        foreach ([1003, 1002, 1001] as $orders_id) {
            $rows[] = [
                'orders_id' => (string)$orders_id,
                'date_purchased' => '2026-07-30 09:15:00',
                'delivery_name' => 'Testy McTest',
                'delivery_country' => 'United States',
                'billing_name' => 'Testy McTest',
                'billing_country' => 'United States',
                'order_total' => '50.0000',
                'currency' => 'USD',
                'currency_value' => '1.000000',
                'orders_status' => '2',
                'orders_status_name' => 'Processing',
                'language_code' => 'en',
            ];
        }

        return $rows;
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
     * @return string[]
     */
    private function queriesReadingFrom(string $table): array
    {
        return array_values(array_filter(
            $this->queries,
            static fn (string $sql): bool => (bool)preg_match('/\bFROM\s+' . preg_quote($table, '/') . '\b/i', $sql)
        ));
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
