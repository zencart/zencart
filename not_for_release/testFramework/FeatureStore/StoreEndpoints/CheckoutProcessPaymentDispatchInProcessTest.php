<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * checkout_process must not persist an order unless a payment module was actually dispatched
 * for it, or the order owes nothing.  $_SESSION['payment'] is customer-supplied: the payment
 * class records in selected_module only a name it could resolve to an installed, enabled
 * module, and when it could not, payment::before_process() returns silently -- nothing is
 * charged and nothing complains.
 *
 * The fixture installs a second payment module so that the store has two selectable modules.
 * With only one, payment::__construct() rewrites $_SESSION['payment'] to that module's name
 * (the "only one payment module" default) without setting selected_module, which relabels the
 * forged order instead of leaving the empty payment_method the original report describes.
 */
#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class CheckoutProcessPaymentDispatchInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    protected const COD_CONFIG_KEYS = [
        'MODULE_PAYMENT_COD_STATUS' => 'True',
        'MODULE_PAYMENT_COD_ZONE' => '0',
        'MODULE_PAYMENT_COD_SORT_ORDER' => '0',
        'MODULE_PAYMENT_COD_ORDER_STATUS_ID' => '0',
    ];

    protected ?string $installedModulesBackup = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->installSecondPaymentModule();
    }

    protected function tearDown(): void
    {
        $this->removeSecondPaymentModule();
        parent::tearDown();
    }

    /**
     * The reported attack: a payment-module name the store does not have installed.
     */
    public function testUninstalledPaymentModuleNameCannotCreateAnOrder(): void
    {
        $this->assertForgedPaymentNameCreatesNoOrder('florida-basic1', 'not_a_real_payment_module');
    }

    /**
     * The same hole reached through the variable-variable check that checkout_confirmation
     * uses to decide a payment module is present: 'currencies' is an in-scope object on every
     * storefront page, so it satisfies `is_object(${$_SESSION['payment']})` and is not a
     * payment module.
     */
    public function testUnrelatedGlobalObjectNameCannotCreateAnOrder(): void
    {
        $this->assertForgedPaymentNameCreatesNoOrder('florida-basic2', 'currencies');
    }

    /**
     * The other half of the invariant, which no existing test covered: an order that owes
     * nothing has no payment module to dispatch and must still be allowed through. A guard
     * that only asked whether a payment module was dispatched would break this.
     */
    public function testFullyCreditCoveredOrderStillCompletes(): void
    {
        $profile = $this->createCustomerAccountOrLogin('US-not-florida-basic');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        $this->assertNotNull($customerId);

        $this->addGiftVoucherBalance($profile['email_address'], 1000);

        $this->addProductToCart(25)
            ->assertRedirect('main_page=shopping_cart');

        $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        $this->continueCheckoutPayment(['cot_gv' => '1000.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Order Confirmation');

        $successResponse = $this->confirmCheckoutOrder()
            ->assertRedirect('main_page=checkout_success');

        $this->followRedirect($successResponse)
            ->assertOk()
            ->assertSee('Your Order Number is:');

        $order = TestDb::selectOne(
            'SELECT orders_id, order_total
               FROM orders
              WHERE customers_id = :customer_id
              ORDER BY orders_id DESC
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($order, 'A fully credit-covered order should still be created.');
        $this->assertSame(
            0.0,
            round((float) $order['order_total'], 2),
            'A fully credit-covered order should be persisted with a zero total.'
        );
    }

    /**
     * The non-malicious route, from lat9/one_page_checkout#505 (@dbltoe): a genuinely selected
     * module that disables itself *between* confirmation and checkout_process. On his store a
     * Square WebPay OAuth token had expired, the module called
     * disableDueToInvalidAccessToken() mid-checkout, and checkout_process then ran with the
     * module's global gone -- isPaymentModuleMethodPresent() returned false for before_process,
     * and the order was written unpaid with the GV/DC fallback label on a store where ot_gv and
     * ot_coupon were not installed. @cstdenis reports the same via paypalr.
     *
     * Simulated here by disabling the selected module in the configuration table after the
     * customer has legitimately chosen it. The module name stays in MODULE_PAYMENT_INSTALLED, so
     * payment::$selected_module is still set; only the registered instance is missing. A guard
     * keyed on selected_module alone would not catch this.
     */
    public function testPaymentModuleDisabledMidCheckoutCannotCreateAnOrder(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        $this->assertNotNull($customerId);

        $this->addProductToCart(25)
            ->assertRedirect('main_page=shopping_cart');

        $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        $confirmation = $this->continueCheckoutPayment(['payment' => 'cod'])
            ->assertOk()
            ->assertSee('Order Confirmation');

        /**
         * Take the token from the confirmation page now, before the module is disabled. Loading
         * any page afterwards would re-run payment::__construct() and, with cod disabled, leave
         * moneyorder the only selectable module -- which the "only one payment module" default
         * at payment.php:142-146 would then write into the session, repairing it and testing
         * nothing. The real flow is the same: the customer is sat on the confirmation page when
         * the module dies.
         */
        $token = $confirmation->securityToken();
        $this->assertNotNull($token);

        /**
         * The module self-disables mid-checkout. It stays listed in MODULE_PAYMENT_INSTALLED --
         * that is what makes selected_module resolve while the instance does not.
         */
        TestDb::update(
            'configuration',
            ['configuration_value' => 'False'],
            'configuration_key = :key',
            [':key' => 'MODULE_PAYMENT_COD_STATUS']
        );
        $this->assertSame(
            'False',
            TestDb::selectValue(
                "SELECT configuration_value FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_COD_STATUS'"
            ),
            'Precondition failed: the mid-checkout disablement did not apply.'
        );
        $this->assertStringContainsString(
            'cod.php',
            (string) TestDb::selectValue(
                "SELECT configuration_value FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_INSTALLED'"
            ),
            'Precondition failed: the module must stay installed for this to exercise route (b).'
        );

        $ordersBefore = $this->countOrders($customerId);

        $this->postSsl('/index.php?main_page=checkout_process', ['securityToken' => $token]);

        $newest = TestDb::selectOne(
            'SELECT orders_id, order_total, payment_method, payment_module_code
               FROM orders
              WHERE customers_id = :customer_id
              ORDER BY orders_id DESC
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertSame(
            $ordersBefore,
            $this->countOrders($customerId),
            sprintf(
                'checkout_process persisted an order after the selected payment module disabled '
                . 'itself mid-checkout. Newest order row: %s',
                json_encode($newest)
            )
        );
    }

    /**
     * A stock store installs freecharger.php and enables it with no payment zone, and the
     * module gates itself on the billing geo zone only -- update_status() never looks at the
     * order total. The payment class hides it from the selection list on a paid cart, but the
     * constructor's "was this module selected" branch does not exclude it, so a forged name
     * resolves to a real installed, enabled, registered module. "A module was dispatched" is
     * therefore satisfied on an order that owes 77.39 and collected nothing.
     */
    public function testFreechargerCannotBeForcedOntoAPaidOrder(): void
    {
        $installed = (string) TestDb::selectValue(
            "SELECT configuration_value FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_INSTALLED'"
        );
        $this->assertStringContainsString(
            'freecharger.php',
            $installed,
            'Fixture precondition failed: freecharger is expected to be installed on a stock store.'
        );
        $this->assertSame(
            'True',
            TestDb::selectValue(
                "SELECT configuration_value FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_FREECHARGER_STATUS'"
            ),
            'Fixture precondition failed: freecharger is expected to be enabled on a stock store.'
        );

        $this->assertForgedPaymentNameCreatesNoOrder('florida-basic2', 'freecharger');
    }

    protected function assertForgedPaymentNameCreatesNoOrder(string $profileName, string $forgedPaymentName): void
    {
        $profile = $this->createCustomerAccountOrLogin($profileName);
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        $this->assertNotNull($customerId);

        $this->addProductToCart(25)
            ->assertRedirect('main_page=shopping_cart');

        $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        /**
         * checkout_confirmation writes $_POST['payment'] into the session before validating it,
         * and a validation failure only redirects, so the forged value survives in the session.
         */
        $paymentPage = $this->visitCheckoutPayment()->assertOk();
        $this->postSsl(
            '/index.php?main_page=checkout_confirmation',
            array_merge($paymentPage->formDefaults('checkout_payment'), ['payment' => $forgedPaymentName])
        );

        $ordersBefore = $this->countOrders($customerId);

        /**
         * Post straight at checkout_process, bypassing the confirmation page entirely.
         * The security token is session-wide, so any page supplies a valid one.
         */
        $token = $this->visitCheckoutPayment()->assertOk()->securityToken();
        $this->assertNotNull($token);

        $this->postSsl('/index.php?main_page=checkout_process', ['securityToken' => $token]);

        $newest = TestDb::selectOne(
            'SELECT orders_id, order_total, payment_method, payment_module_code
               FROM orders
              WHERE customers_id = :customer_id
              ORDER BY orders_id DESC
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertSame(
            $ordersBefore,
            $this->countOrders($customerId),
            sprintf(
                'checkout_process persisted an order with no payment module dispatched '
                . '(forged $_SESSION[\'payment\'] = %s). Newest order row: %s',
                $forgedPaymentName,
                json_encode($newest)
            )
        );
    }

    protected function countOrders(int $customerId): int
    {
        return (int) TestDb::selectValue(
            'SELECT COUNT(*) FROM orders WHERE customers_id = :customer_id',
            [':customer_id' => $customerId]
        );
    }

    protected function installSecondPaymentModule(): void
    {
        $this->installedModulesBackup = (string) TestDb::selectValue(
            "SELECT configuration_value FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_INSTALLED'"
        );

        foreach (static::COD_CONFIG_KEYS as $key => $value) {
            TestDb::pdo()->prepare(
                'DELETE FROM configuration WHERE configuration_key = :key'
            )->execute([':key' => $key]);

            TestDb::insert('configuration', [
                'configuration_title' => $key,
                'configuration_key' => $key,
                'configuration_value' => $value,
                'configuration_description' => 'Installed by CheckoutProcessPaymentDispatchInProcessTest',
                'configuration_group_id' => 6,
                'sort_order' => 0,
                'date_added' => date('Y-m-d H:i:s'),
            ]);
        }

        $modules = array_filter(explode(';', $this->installedModulesBackup));
        if (!in_array('cod.php', $modules, true)) {
            $modules[] = 'cod.php';
        }

        TestDb::update(
            'configuration',
            ['configuration_value' => implode(';', $modules)],
            'configuration_key = :key',
            [':key' => 'MODULE_PAYMENT_INSTALLED']
        );

        $this->assertSame(
            'True',
            TestDb::selectValue(
                "SELECT configuration_value FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_COD_STATUS'"
            ),
            'Fixture precondition failed: the second payment module was not installed.'
        );
    }

    protected function removeSecondPaymentModule(): void
    {
        foreach (array_keys(static::COD_CONFIG_KEYS) as $key) {
            TestDb::pdo()->prepare(
                'DELETE FROM configuration WHERE configuration_key = :key'
            )->execute([':key' => $key]);
        }

        if ($this->installedModulesBackup !== null) {
            TestDb::update(
                'configuration',
                ['configuration_value' => $this->installedModulesBackup],
                'configuration_key = :key',
                [':key' => 'MODULE_PAYMENT_INSTALLED']
            );
            $this->installedModulesBackup = null;
        }
    }
}
