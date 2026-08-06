<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
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
use Tests\Support\Traits\CustomerDbStubConcerns;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class CustomerOrderStatisticsDeferralTest extends zcUnitTestCase
{
    use CustomerDbStubConcerns;

    public function setUp(): void
    {
        // Customer only loads order statistics admin-side, so this must be set before the
        // bootstrap defines it as false.
        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', true);

        parent::setUp();

        $this->installCustomerDbStub();
    }

    public function tearDown(): void
    {
        $this->removeCustomerDbStub();
        parent::tearDown();
    }

    public function testOrderStatisticsAreLoadedByDefault(): void
    {
        $data = (new \Customer(self::STUB_CUSTOMER_ID))->getData();

        $this->assertSame(self::STUB_CUSTOMER_ID, $data['customers_id']);
        $this->assertSame(2, $data['number_of_orders']);
        $this->assertEqualsWithDelta(75.0, $data['lifetime_value'], 0.001);
        $this->assertSame('2026-07-30 09:15:00', $data['last_order']['date_purchased']);

        $this->assertCount(2, $this->queriesReadingFrom(TABLE_ORDERS), 'The order count and lifetime-value queries should both run.');
    }

    public function testOrderStatisticsAreSkippedWhenDeferred(): void
    {
        $data = (new \Customer(self::STUB_CUSTOMER_ID, load_order_statistics: false))->getData();

        $this->assertSame([], $this->queriesReadingFrom(TABLE_ORDERS), 'No orders-table query should be issued when order statistics are deferred.');

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
        $data = (new \Customer(self::STUB_CUSTOMER_ID, load_order_statistics: false))->getData();

        $this->assertSame(self::STUB_CUSTOMER_ID, $data['customers_id']);
        $this->assertSame('Testy', $data['customers_firstname']);
        $this->assertSame('mctest@zencart.test', $data['customers_email_address']);
        $this->assertSame(3, $data['number_of_reviews']);
        $this->assertSame([], $data['addresses']);
        $this->assertArrayNotHasKey('customers_password', $data);
    }
}
