<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support\Traits;

/**
 * A recording $db stub for exercising Customer::load() without a database.
 *
 * All of load()'s lookups go through the same stub, so they are told apart by the table (or a
 * distinctive column) named in the SQL. Every statement is recorded, which is what lets these
 * tests assert on the queries that no longer run.
 */
trait CustomerDbStubConcerns
{
    protected const STUB_CUSTOMER_ID = 42;
    protected const STUB_ADDRESS_BOOK_ID = 7;

    /**
     * Every SQL statement handed to the $db stub, in order.
     *
     * @var string[]
     */
    protected array $queries = [];

    /**
     * Rows the address-book lookup should return. Defaults to none.
     *
     * @var array<int, array<string, string>>
     */
    protected array $addressBookRows = [];

    /**
     * Values overriding the canned customers-table row.
     *
     * @var array<string, string>
     */
    protected array $customerRowOverrides = [];

    protected function installCustomerDbStub(): void
    {
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
        $db->method('Execute')->willReturnCallback([$this, 'routeCustomerQuery']);
        $db->method('ExecuteNoCache')->willReturnCallback([$this, 'routeCustomerQuery']);
        $db->method('bindVars')->willReturnCallback(
            static fn (string $sql, string $bindVarString, $sqlValue, $type): string => $sql
        );

        $GLOBALS['db'] = $db;
    }

    protected function removeCustomerDbStub(): void
    {
        unset($GLOBALS['db'], $GLOBALS['currencies']);
    }

    public function routeCustomerQuery(string $sql, ...$ignored): \queryFactoryResult
    {
        $this->queries[] = $sql;

        return $this->makeResult(match (true) {
            str_contains($sql, TABLE_ADDRESS_BOOK) => $this->addressBookRows,
            str_contains($sql, 'number_of_reviews') => [['number_of_reviews' => '3']],
            str_contains($sql, 'COUNT(*) AS count') => [['count' => '2']],
            str_contains($sql, 'o.orders_id') => $this->orderRows(),
            str_contains($sql, TABLE_GROUP_PRICING) => [['group_name' => 'Wholesale', 'group_percentage' => '12.50']],
            str_contains($sql, TABLE_CUSTOMERS . ' c') => [$this->customerRow()],
            default => [],
        });
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function orderRows(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function customerRow(): array
    {
        return array_merge([
            'customers_id' => (string)self::STUB_CUSTOMER_ID,
            'customers_firstname' => 'Testy',
            'customers_lastname' => 'McTest',
            'customers_email_address' => 'mctest@zencart.test',
            'customers_password' => 'not-loaded-into-data',
            'customers_default_address_id' => (string)self::STUB_ADDRESS_BOOK_ID,
            'customers_group_pricing' => '0',
            'customers_authorization' => '0',
            'customers_newsletter' => '0',
            'number_of_logins' => '5',
        ], $this->customerRowOverrides);
    }

    /**
     * A single address-book row as the joined lookup in getFormattedAddressBookList() returns it.
     *
     * @return array<string, string>
     */
    protected function addressBookRow(): array
    {
        return [
            'address_book_id' => (string)self::STUB_ADDRESS_BOOK_ID,
            'customers_id' => (string)self::STUB_CUSTOMER_ID,
            'firstname' => 'Testy',
            'lastname' => 'McTest',
            'company' => '',
            'street_address' => '1 Test Way',
            'suburb' => '',
            'city' => 'Testville',
            'postcode' => '33101',
            'state' => '',
            'zone_id' => '18',
            'zone_name' => 'Florida',
            'zone_iso' => 'FL',
            'country_id' => '223',
            'country_name' => 'United States',
            'country_iso' => 'USA',
            'country_iso_2' => 'US',
            'address_format_id' => '2',
        ];
    }

    /**
     * @param array<int, array<string, string>> $rows
     */
    protected function makeResult(array $rows): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = $rows;
        $result->fields = $rows[0] ?? [];
        $result->EOF = ($rows === []);

        return $result;
    }

    /**
     * Recorded queries reading from the named table.
     *
     * @return string[]
     */
    protected function queriesReadingFrom(string $table): array
    {
        return array_values(array_filter(
            $this->queries,
            static fn (string $sql): bool => (bool)preg_match('/\bFROM\s+' . preg_quote($table, '/') . '\b/i', $sql)
        ));
    }
}
