<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Two lookups in Customer::load() were issued more often than they needed to be, which the admin
 * customer listing pays for once per row:
 *
 * - getFormattedAddressBookList() already INNER JOINs the countries table, but then called
 *   zen_get_address_format_id() per address, re-querying countries for a column the join could
 *   have carried.
 * - getPricingGroupAssociation() queried group_pricing even when the customer has no pricing
 *   group, which -- since group_id is auto_increment and therefore never zero -- can only ever
 *   come back empty.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\Traits\CustomerDbStubConcerns;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class CustomerRedundantLookupTest extends zcUnitTestCase
{
    use CustomerDbStubConcerns;

    public function setUp(): void
    {
        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', true);

        parent::setUp();

        $this->installCustomerDbStub();
        $this->addressBookRows = [$this->addressBookRow()];
    }

    public function tearDown(): void
    {
        $this->removeCustomerDbStub();
        parent::tearDown();
    }

    public function testTheAddressFormatComesFromTheJoinRatherThanAPerAddressQuery(): void
    {
        $addresses = (new \Customer(self::STUB_CUSTOMER_ID))->getData('addresses');

        $this->assertCount(1, $addresses);
        $this->assertSame(2, $addresses[0]['format_id']);

        // The address-book query reads countries via a JOIN; zen_get_address_format_id() is the
        // only thing that would SELECT ... FROM countries directly.
        $this->assertSame(
            [],
            $this->queriesReadingFrom(TABLE_COUNTRIES),
            'The address format should come from the existing join, not a standalone countries lookup.'
        );
        $this->assertCount(1, $this->queriesReadingFrom(TABLE_ADDRESS_BOOK));
    }

    /**
     * The format id is used internally but was never part of the returned address row, and the
     * default address row is merged into the customer's own data -- so it must not leak in.
     */
    public function testTheReturnedAddressRowIsUnchanged(): void
    {
        $customer = new \Customer(self::STUB_CUSTOMER_ID);
        $addresses = $customer->getData('addresses');

        $this->assertArrayNotHasKey('address_format_id', $addresses[0]['address']);
        $this->assertArrayNotHasKey('address_format_id', $customer->getData());
        $this->assertSame('Florida', $addresses[0]['address']['state'], 'An empty state should still fall back to the zone name.');
    }

    public function testNoPricingGroupLookupWhenTheCustomerHasNoPricingGroup(): void
    {
        $data = (new \Customer(self::STUB_CUSTOMER_ID))->getData();

        $this->assertSame([], $this->queriesReadingFrom(TABLE_GROUP_PRICING));

        $this->assertSame('', $data['pricing_group_name']);
        $this->assertSame(0, $data['pricing_group_discount_percentage']);
    }

    public function testThePricingGroupIsStillLookedUpWhenTheCustomerHasOne(): void
    {
        $this->customerRowOverrides = ['customers_group_pricing' => '4'];

        $data = (new \Customer(self::STUB_CUSTOMER_ID))->getData();

        $this->assertCount(1, $this->queriesReadingFrom(TABLE_GROUP_PRICING));

        $this->assertSame('Wholesale', $data['pricing_group_name']);
        $this->assertSame('12.50', $data['pricing_group_discount_percentage']);
    }
}
