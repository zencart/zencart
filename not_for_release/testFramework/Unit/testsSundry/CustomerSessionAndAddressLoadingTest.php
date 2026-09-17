<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Coverage for the Customer class owning the session teardown that login() sets up, and for
 * the on-demand address-book loading used by callers that only need the base customer record.
 */

namespace Tests\Unit\testsSundry;

use Customer;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class CustomerSessionAndAddressLoadingTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        defined('TABLE_WHOS_ONLINE') || define('TABLE_WHOS_ONLINE', 'whos_online');

        require_once DIR_FS_CATALOG . 'includes/classes/traits/NotifierManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/traits/ObserverManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/Customer.php';

        $GLOBALS['zco_notifier'] = new \notifier();
        // forceLogout()'s whos_online DELETE is the only query these tests reach; a stub absorbs it.
        $GLOBALS['db'] = $this->createStub(\queryFactory::class);
    }

    public function testForceLogoutRemovesEverythingLoginRegistered(): void
    {
        $_SESSION = [
            'customer_id' => 42,
            'customers_email_address' => 'a@example.com',
            'customer_first_name' => 'Ada',
            'customer_last_name' => 'Lovelace',
            'customer_default_address_id' => 7,
            'customer_country_id' => 223,
            'customer_zone_id' => 12,
            'customers_authorization' => 0,
            'customer_password_hash' => 'abc',
            'cart' => 'untouched',
        ];

        $customer = new StubLoadedCustomer(42);
        $this->assertTrue($customer->forceLogout());

        foreach ([
            'customer_id', 'customers_email_address', 'customer_first_name', 'customer_last_name',
            'customer_default_address_id', 'customer_country_id', 'customer_zone_id',
            'customers_authorization', 'customer_password_hash',
        ] as $key) {
            $this->assertArrayNotHasKey($key, $_SESSION, "forceLogout() left $key in the session");
        }
        $this->assertSame('untouched', $_SESSION['cart'], 'forceLogout() must not touch unrelated session data');
    }

    public function testForceLogoutIsANoOpForADifferentCustomer(): void
    {
        $_SESSION = ['customer_id' => 99, 'customer_first_name' => 'Someone Else'];

        $customer = new StubLoadedCustomer(42);
        $this->assertFalse($customer->forceLogout());
        $this->assertSame(99, $_SESSION['customer_id']);
        $this->assertSame('Someone Else', $_SESSION['customer_first_name']);
    }

    public function testAddressesLoadEagerlyByDefault(): void
    {
        $_SESSION = [];
        $customer = new StubLoadedCustomer(42);

        $this->assertSame(1, $customer->addressLoads, 'default construction should load addresses once');
        $this->assertSame([], $customer->getData('addresses'));
        $this->assertSame(1, $customer->addressLoads, 'getData() must not reload already-loaded addresses');
    }

    public function testAddressesLoadOnDemandWhenDeferred(): void
    {
        $_SESSION = [];
        $customer = new StubLoadedCustomer(42, load_addresses: false);

        $this->assertSame(0, $customer->addressLoads, 'deferred construction must not touch the address book');
        $this->assertSame(42, $customer->getData('customers_id'));
        $this->assertSame(0, $customer->addressLoads, 'reading a base field must not trigger the address load');

        $this->assertSame([], $customer->getData('addresses'));
        $this->assertSame(1, $customer->addressLoads, 'first address request loads on demand');
        $customer->getData();
        $customer->getData('country_id');
        $this->assertSame(1, $customer->addressLoads, 'subsequent requests reuse the loaded data');
    }

    public function testAddressesLoadForTheCustomerPassedToLoadNotTheInstanceId(): void
    {
        // login/header_php.php does `new Customer()` and then `login($id)`: the instance has no
        // customer_id of its own, so the address load has to follow the id given to load().
        $_SESSION = [];
        $customer = new StubLoadedCustomer();
        $this->assertSame(0, $customer->addressLoads);

        $customer->loadForTest(42);
        $this->assertSame(1, $customer->addressLoads);
        $this->assertSame(42, $customer->addressLoadedFor);
        $this->assertSame([], $customer->getData('addresses'));
        $this->assertSame(1, $customer->addressLoads, 'a loaded address list must not be fetched again');
    }

    public function testDeferredLoadingIsSafeWhenThereIsNoCustomerRecord(): void
    {
        $_SESSION = [];
        $customer = new StubMissingCustomer(42, load_addresses: false);

        $this->assertNull($customer->getData('customers_id'));
        $this->assertNull($customer->getData('addresses'));
        $this->assertSame(0, $customer->addressLoads);
    }
}

/**
 * A Customer whose base record "exists" without a database: load() populates the fields the
 * class reads, and the address-book lookup is counted instead of queried.
 */
class StubLoadedCustomer extends Customer
{
    public int $addressLoads = 0;
    public ?int $addressLoadedFor = null;

    public function loadForTest(int $customer_id): bool
    {
        return $this->load($customer_id);
    }

    protected function load(?int $customer_id = null): bool
    {
        $this->addresses_loaded = false;
        $this->loaded_customer_id = null;
        $this->data = [
            'customers_id' => $customer_id,
            'customers_default_address_id' => 7,
        ];
        $this->loaded_customer_id = $customer_id;
        if ($this->load_addresses) {
            $this->loadAddresses();
        }
        return true;
    }

    protected function loadAddresses(): void
    {
        $this->addresses_loaded = true;
        if (empty($this->loaded_customer_id) || empty($this->data)) {
            return;
        }
        $this->addressLoads++;
        $this->addressLoadedFor = $this->loaded_customer_id;
        $this->data['addresses'] = [];
    }
}

class StubMissingCustomer extends StubLoadedCustomer
{
    protected function load(?int $customer_id = null): bool
    {
        $this->data = [];
        return false;
    }
}
