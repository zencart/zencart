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
 * The false-positive half of the no-payment-module guard: a store with exactly one payment
 * module *available to the session* must still be able to take an order.
 *
 * What matters is availability, not the installed count. tpl_checkout_payment_default.php:120-125
 * switches from a radio to a hidden field when sizeof($payment_modules->selection()) is 1, and
 * payment.php:142-146 keys on zen_count_payment_modules(), which counts only modules whose
 * global is registered and enabled (functions_general.php:37-55) -- availability again, despite
 * reading MODULE_PAYMENT_INSTALLED. The more common way to reach this state is several modules
 * installed with one available: the stock store installs freecharger.php and moneyorder.php and
 * excludes freecharger from a priced cart, so every other checkout test already traverses it.
 * This fixture reduces the installed list instead, as a second instance of the same condition.
 *
 * The configuration is the one most at risk from any guard keyed on payment::$selected_module,
 * because the session value can be supplied by the hidden field or repaired by payment.php:142-146
 * rather than chosen, and the repair does not set selected_module. Asserts the order is created
 * and attributed to the real module.
 */
#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class CheckoutProcessSinglePaymentModuleInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    protected ?string $installedModulesBackup = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->installedModulesBackup = (string) TestDb::selectValue(
            "SELECT configuration_value FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_INSTALLED'"
        );

        TestDb::update(
            'configuration',
            ['configuration_value' => 'moneyorder.php'],
            'configuration_key = :key',
            [':key' => 'MODULE_PAYMENT_INSTALLED']
        );

        $this->assertSame(
            'moneyorder.php',
            TestDb::selectValue(
                "SELECT configuration_value FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_INSTALLED'"
            ),
            'Fixture precondition failed: the store was not reduced to a single payment module.'
        );
    }

    protected function tearDown(): void
    {
        if ($this->installedModulesBackup !== null) {
            TestDb::update(
                'configuration',
                ['configuration_value' => $this->installedModulesBackup],
                'configuration_key = :key',
                [':key' => 'MODULE_PAYMENT_INSTALLED']
            );
            $this->installedModulesBackup = null;
        }

        parent::tearDown();
    }

    public function testSingleInstalledPaymentModuleStillTakesAnOrder(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        $this->assertNotNull($customerId);

        $this->addProductToCart(25)
            ->assertRedirect('main_page=shopping_cart');

        $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        /**
         * Post exactly what the rendered page offers rather than an assumed payload. With one
         * selectable module the template emits a *hidden* field rather than a radio
         * (tpl_checkout_payment_default.php:120-125), so a browser posts the module's name.
         * Note that payment.php:142-146's comment -- "no radio button selection possible ...
         * which will be empty" -- no longer describes this template.
         */
        $paymentPage = $this->visitCheckoutPayment()->assertOk();
        $defaults = $paymentPage->formDefaults('checkout_payment');
        $this->assertSame(
            'moneyorder',
            $defaults['payment'] ?? null,
            'The lone payment module should be posted by a hidden field, not left unselected.'
        );

        $this->continueCheckoutPayment($defaults)
            ->assertOk()
            ->assertSee('Order Confirmation');

        $successResponse = $this->confirmCheckoutOrder()
            ->assertRedirect('main_page=checkout_success');

        $this->followRedirect($successResponse)
            ->assertOk()
            ->assertSee('Your Order Number is:');

        $order = TestDb::selectOne(
            'SELECT orders_id, order_total, payment_method, payment_module_code
               FROM orders
              WHERE customers_id = :customer_id
              ORDER BY orders_id DESC
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull(
            $order,
            'A single-payment-module store must still be able to create an order.'
        );
        $this->assertSame(
            'moneyorder',
            $order['payment_module_code'],
            'The order should be attributed to the store\'s only payment module.'
        );
        $this->assertGreaterThan(
            0.009,
            round((float) $order['order_total'], 4),
            'This must be a priced order, or it would not exercise the guard at all.'
        );
    }
}
