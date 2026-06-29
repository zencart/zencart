<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcDiscountCouponTest;

class DiscountCouponRaceConditionTest extends zcDiscountCouponTest
{
    public function setUp(): void
    {
        parent::setUp();

        require_once TESTCWD . 'Support/functionsDiscountCoupons.php';
        require_once TESTCWD . 'Support/StubCouponValidation.php';
        require_once DIR_FS_CATALOG . 'includes/modules/order_total/ot_coupon.php';
        require_once DIR_FS_CATALOG . 'includes/classes/shopping_cart.php';
        require_once DIR_FS_CATALOG . 'includes/classes/currencies.php';

        if (!function_exists('zen_is_logged_in')) {
            eval('function zen_is_logged_in(): bool { return !empty($_SESSION["customer_id"]); }');
        }
        if (!function_exists('zen_in_guest_checkout')) {
            eval('function zen_in_guest_checkout(): bool { return !empty($_SESSION["guest_checkout"]); }');
        }
        if (!function_exists('zen_db_input')) {
            eval('function zen_db_input($string) { return addslashes((string)$string); }');
        }

        $_SESSION['currency'] = 'USD';
        $GLOBALS['current_page_base'] = FILENAME_CHECKOUT_PROCESS;
        $GLOBALS['messageStack'] = new DiscountCouponRaceMessageStack();

        defined('MODULE_ORDER_TOTAL_COUPON_HEADER') || define('MODULE_ORDER_TOTAL_COUPON_HEADER', '');
        defined('MODULE_ORDER_TOTAL_COUPON_TITLE') || define('MODULE_ORDER_TOTAL_COUPON_TITLE', '');
        defined('MODULE_ORDER_TOTAL_COUPON_DESCRIPTION') || define('MODULE_ORDER_TOTAL_COUPON_DESCRIPTION', '');
        defined('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER') || define('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '');
        defined('MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING') || define('MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', '');
        defined('MODULE_ORDER_TOTAL_COUPON_INC_TAX') || define('MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false');
        defined('MODULE_ORDER_TOTAL_COUPON_CALC_TAX') || define('MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard');
        defined('MODULE_ORDER_TOTAL_COUPON_TAX_CLASS') || define('MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '');
        defined('DISPLAY_PRICE_WITH_TAX') || define('DISPLAY_PRICE_WITH_TAX', 'false');
        defined('TEXT_INVALID_USES_COUPON') || define('TEXT_INVALID_USES_COUPON', 'Coupon %1$s has reached its %2$s-use limit.');
        defined('TEXT_INVALID_USES_USER_COUPON') || define('TEXT_INVALID_USES_USER_COUPON', 'Coupon %1$s has reached your %2$s-use limit.');

        $GLOBALS['currencies'] = $this->getMockBuilder('currencies')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['currencies']->method('get_decimal_places')->willReturn(2);

        $_SESSION['cart'] = $this->getMockBuilder('shoppingCart')
            ->disableOriginalConstructor()
            ->getMock();
        $_SESSION['cart']->method('get_products')->willReturn([
            [
                'id' => 27,
                'category' => 5,
                'name' => 'Packard LaserJet 1100Xi Linked',
                'model' => 'HPLJ1100XI',
                'image' => 'hewlett_packard/lj1100xi.gif',
                'price' => 499.99,
                'quantity' => 1,
                'weight' => 45,
                'final_price' => 499.99,
                'onetime_charges' => 0,
                'tax_class_id' => 1,
                'attributes' => '',
                'attributes_values' => '',
                'products_priced_by_attribute' => 0,
                'product_is_free' => 0,
                'products_discount_type' => 0,
                'products_discount_type_from' => 0,
                'products_virtual' => 0,
                'product_is_always_free_shipping' => 0,
                'products_quantity_order_min' => 1,
                'products_quantity_order_units' => 1,
                'products_quantity_order_max' => 0,
                'products_quantity_mixed' => 0,
                'products_mixed_discount_quantity' => 1,
            ],
        ]);
    }

    public function testConcurrentCheckoutCannotReceiveDiscountWhileCouponLockIsHeld(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'race-test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 1,
            'uses_per_user' => 0,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);

        $requestOneOrder = (object)['info' => $this->getBaseOrderInfo()];
        $GLOBALS['order'] = $requestOneOrder;
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';

        $couponOne = new ot_coupon();
        $couponOne->include_shipping = 'false';
        $couponOne->process();

        $this->assertEquals(452.49, $requestOneOrder->info['total']);

        $requestTwoOrder = (object)['info' => $this->getBaseOrderInfo()];
        $GLOBALS['order'] = $requestTwoOrder;
        $_SESSION['customer_id'] = 202;
        $_SESSION['cc_id'] = '1';

        $couponTwo = new ot_coupon();
        $couponTwo->include_shipping = 'false';
        $couponTwo->process();

        $this->assertEquals(
            502.49,
            $requestTwoOrder->info['total'],
            'A concurrent checkout must not receive the coupon discount while another request holds the finalization lock.'
        );
        $this->assertTrue($couponTwo->shouldAbortCheckoutProcess());
        $this->assertContains('checkout_confirmation', $GLOBALS['messageStack']->stacks);

        $GLOBALS['insert_id'] = 1001;
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';
        $couponOne->apply_credit();

        $this->assertCount(1, $GLOBALS['db']->redemptions);

        $GLOBALS['insert_id'] = 1002;
        $_SESSION['customer_id'] = 202;
        $_SESSION['cc_id'] = '1';
        $couponTwo->apply_credit();

        $this->assertCount(1, $GLOBALS['db']->redemptions);
    }

    public function testConcurrentCheckoutAbortSurvivesSecondProcessPass(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'two-pass-race-test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 1,
            'uses_per_user' => 0,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);

        $GLOBALS['order'] = (object)['info' => $this->getBaseOrderInfo()];
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';

        $couponOne = new ot_coupon();
        $couponOne->include_shipping = 'false';
        $couponOne->process();

        $requestTwoOrder = (object)['info' => $this->getBaseOrderInfo()];
        $GLOBALS['order'] = $requestTwoOrder;
        $_SESSION['customer_id'] = 202;
        $_SESSION['cc_id'] = '1';

        $couponTwo = new ot_coupon();
        $couponTwo->include_shipping = 'false';
        $couponTwo->process();

        $this->assertTrue($couponTwo->shouldAbortCheckoutProcess());
        $this->assertArrayNotHasKey('cc_id', $_SESSION);
        $this->assertEquals(502.49, $requestTwoOrder->info['total']);

        $requestTwoSecondPassOrder = (object)['info' => $this->getBaseOrderInfo()];
        $GLOBALS['order'] = $requestTwoSecondPassOrder;
        $couponTwo->process();

        $this->assertTrue($couponTwo->shouldAbortCheckoutProcess());
        $this->assertEquals(
            502.49,
            $requestTwoSecondPassOrder->info['total'],
            'The abort flag must survive the second checkout_process totals pass so checkout redirects before charging a different total.'
        );
    }

    public function testUnlimitedUseCouponDoesNotTakeAdvisoryLockDuringCheckoutProcess(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'unlimited-test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 0,
            'uses_per_user' => 0,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);
        $GLOBALS['order'] = (object)['info' => $this->getBaseOrderInfo()];
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';

        $coupon = new ot_coupon();
        $coupon->include_shipping = 'false';
        $coupon->process();

        $this->assertEquals(452.49, $GLOBALS['order']->info['total']);
        $this->assertSame(0, $GLOBALS['db']->getLockCalls);

        $GLOBALS['insert_id'] = 1001;
        $coupon->apply_credit();

        $this->assertCount(1, $GLOBALS['db']->redemptions);
        $this->assertSame(0, $GLOBALS['db']->getLockCalls);
    }

    public function testPerUserOnlyCouponDoesNotSerializeDifferentCustomers(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'per-user-test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 0,
            'uses_per_user' => 1,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);

        $requestOneOrder = (object)['info' => $this->getBaseOrderInfo()];
        $GLOBALS['order'] = $requestOneOrder;
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';

        $couponOne = new ot_coupon();
        $couponOne->include_shipping = 'false';
        $couponOne->process();

        $requestTwoOrder = (object)['info' => $this->getBaseOrderInfo()];
        $GLOBALS['order'] = $requestTwoOrder;
        $_SESSION['customer_id'] = 202;
        $_SESSION['cc_id'] = '1';

        $couponTwo = new ot_coupon();
        $couponTwo->include_shipping = 'false';
        $couponTwo->process();

        $this->assertEquals(452.49, $requestOneOrder->info['total']);
        $this->assertEquals(452.49, $requestTwoOrder->info['total']);
        $this->assertFalse($couponTwo->shouldAbortCheckoutProcess());
        $this->assertContains('zc_coupon_redeem_1_customer_101', $GLOBALS['db']->getLockNames);
        $this->assertContains('zc_coupon_redeem_1_customer_202', $GLOBALS['db']->getLockNames);

        $GLOBALS['insert_id'] = 1001;
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';
        $couponOne->apply_credit();

        $GLOBALS['insert_id'] = 1002;
        $_SESSION['customer_id'] = 202;
        $_SESSION['cc_id'] = '1';
        $couponTwo->apply_credit();

        $this->assertCount(2, $GLOBALS['db']->redemptions);
    }

    public function testGuestCheckoutWithUsesPerUserSetUsesSharedGuestIdentityLock(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'guest-user-test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 0,
            'uses_per_user' => 1,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);

        unset($_SESSION['customer_id']);
        $_SESSION['guest_checkout'] = true;

        $requestOneOrder = (object)['info' => $this->getBaseOrderInfo()];
        $GLOBALS['order'] = $requestOneOrder;
        $_SESSION['cc_id'] = '1';

        $couponOne = new ot_coupon();
        $couponOne->include_shipping = 'false';
        $couponOne->process();

        $requestTwoOrder = (object)['info' => $this->getBaseOrderInfo()];
        $GLOBALS['order'] = $requestTwoOrder;
        $_SESSION['cc_id'] = '1';

        $couponTwo = new ot_coupon();
        $couponTwo->include_shipping = 'false';
        $couponTwo->process();

        $this->assertEquals(452.49, $requestOneOrder->info['total']);
        $this->assertEquals(502.49, $requestTwoOrder->info['total']);
        $this->assertTrue($couponTwo->shouldAbortCheckoutProcess());
        $this->assertContains('zc_coupon_redeem_1_customer_0', $GLOBALS['db']->getLockNames);
    }

    public function testGuestCheckoutCanOverridePerUserLockIdentity(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'guest-lock-override-test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 0,
            'uses_per_user' => 1,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);

        unset($_SESSION['customer_id']);
        $_SESSION['guest_checkout'] = true;
        $_SESSION['cc_id'] = '1';
        $GLOBALS['order'] = (object)['info' => $this->getBaseOrderInfo()];

        $coupon = new DiscountCouponRaceGuestAwareCoupon(98765);
        $coupon->include_shipping = 'false';
        $coupon->process();

        $this->assertContains('zc_coupon_redeem_1_customer_98765', $GLOBALS['db']->getLockNames);
    }

    public function testPerUserOnlyLockTimeoutQueuesPerUserMessage(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'per-user-timeout',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 0,
            'uses_per_user' => 1,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);

        $GLOBALS['order'] = (object)['info' => $this->getBaseOrderInfo()];
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';

        $couponOne = new ot_coupon();
        $couponOne->include_shipping = 'false';
        $couponOne->process();

        $GLOBALS['order'] = (object)['info' => $this->getBaseOrderInfo()];
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';

        $couponTwo = new ot_coupon();
        $couponTwo->include_shipping = 'false';
        $couponTwo->process();

        $this->assertTrue($couponTwo->shouldAbortCheckoutProcess());
        $messages = array_column($GLOBALS['messageStack']->messages, 'message');
        $this->assertContains('Coupon per-user-timeout has reached your 1-use limit.', $messages);
    }

    public function testApplyCreditDoesNotRedeemWhenFinalizationLockCannotBeReacquired(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'apply-credit-lock-test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 1,
            'uses_per_user' => 0,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);
        $GLOBALS['order'] = (object)['info' => $this->getBaseOrderInfo()];
        $_SESSION['customer_id'] = 101;
        $_SESSION['cc_id'] = '1';

        $coupon = new ot_coupon();
        $coupon->include_shipping = 'false';
        $coupon->process();

        $this->setProtectedProperty($coupon, 'heldRedemptionLockName', null);

        $GLOBALS['insert_id'] = 1001;
        $coupon->apply_credit();

        $this->assertCount(0, $GLOBALS['db']->redemptions);
        $this->assertSame('', $_SESSION['cc_id']);
    }

    public function testGuestCheckoutApplyCreditTracksRedemptionAsCustomerZero(): void
    {
        $couponDetails = [
            'coupon_id' => 1,
            'coupon_code' => 'guest-apply-credit-test',
            'coupon_total' => 0,
            'coupon_minimum_order' => 0,
            'coupon_amount' => 10,
            'coupon_type' => 'P',
            'coupon_product_count' => 0,
            'coupon_calc_base' => 0,
            'uses_per_coupon' => 0,
            'uses_per_user' => 1,
            'coupon_start_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $GLOBALS['db'] = new DiscountCouponRaceDb($couponDetails);
        unset($_SESSION['customer_id']);
        $_SESSION['guest_checkout'] = true;
        $_SESSION['cc_id'] = '1';
        $GLOBALS['order'] = (object)['info' => $this->getBaseOrderInfo()];

        $coupon = new ot_coupon();
        $coupon->include_shipping = 'false';
        $coupon->process();

        $GLOBALS['insert_id'] = 1001;
        $coupon->apply_credit();

        $this->assertCount(1, $GLOBALS['db']->redemptions);
        $this->assertSame(0, $GLOBALS['db']->redemptions[0]['customer_id']);
    }

    public function testCheckoutProcessRedirectsBackToConfirmationWhenCouponAbortFlagIsSet(): void
    {
        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', false);
        defined('FILENAME_TIME_OUT') || define('FILENAME_TIME_OUT', 'timeout');
        defined('FILENAME_DEFAULT') || define('FILENAME_DEFAULT', 'index');
        defined('FILENAME_LOGIN') || define('FILENAME_LOGIN', 'login');
        defined('FILENAME_CHECKOUT_SHIPPING') || define('FILENAME_CHECKOUT_SHIPPING', 'checkout_shipping');
        defined('FILENAME_CHECKOUT_CONFIRMATION') || define('FILENAME_CHECKOUT_CONFIRMATION', 'checkout_confirmation');

        if (!function_exists('zen_get_module_directory')) {
            eval('function zen_get_module_directory($filename) { return $filename; }');
        }
        if (!function_exists('zen_get_customer_validate_session')) {
            eval('function zen_get_customer_validate_session($customerId) { return true; }');
        }
        if (!function_exists('zen_request_has_valid_csrf_token')) {
            eval('function zen_request_has_valid_csrf_token() { return true; }');
        }
        if (!function_exists('zen_session_destroy')) {
            eval('function zen_session_destroy() { }');
        }
        if (!function_exists('zen_href_link')) {
            eval('function zen_href_link($page, $params = "", $connection = "NONSSL") { return $page; }');
        }
        if (!function_exists('zen_redirect')) {
            eval('function zen_redirect($url) { throw new DiscountCouponRaceRedirectException($url); }');
        }

        $stubRoot = $this->makeCheckoutProcessStubRoot();
        $this->writeCheckoutProcessStubFiles($stubRoot);

        $checkoutProcessScript = $this->writeCheckoutProcessHarness($stubRoot);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['customer_id'] = 202;
        $_SESSION['payment'] = 'cod';
        $_SESSION['shipping'] = 'flat_flat';
        $_SESSION['cartID'] = 'cart-1';
        $_SESSION['cart'] = new DiscountCouponRaceCheckoutCart();
        $_SESSION['cart']->cartID = 'cart-1';

        $GLOBALS['zco_notifier'] = new DiscountCouponRaceNotifier();
        $GLOBALS['messageStack'] = new DiscountCouponRaceMessageStack();
        $GLOBALS['order'] = null;
        $GLOBALS['ot_coupon'] = new DiscountCouponRaceAbortCoupon();
        $GLOBALS['cod'] = (object)['code' => 'cod'];
        $zco_notifier = $GLOBALS['zco_notifier'];
        $ot_coupon = $GLOBALS['ot_coupon'];
        $credit_covers = false;

        try {
            require $checkoutProcessScript;
            $this->fail('checkout_process.php should have redirected back to checkout confirmation.');
        } catch (DiscountCouponRaceRedirectException $exception) {
            $this->assertSame(FILENAME_CHECKOUT_CONFIRMATION, $exception->url);
            $this->assertFalse($GLOBALS['ot_coupon']->shouldAbortCheckoutProcess());
        } finally {
            unset(
                $GLOBALS['zco_notifier'],
                $GLOBALS['order'],
                $GLOBALS['ot_coupon'],
                $GLOBALS['cod']
            );
        }
    }

    protected function getBaseOrderInfo(): array
    {
        return [
            'tax_groups' => [],
            'tax' => 0,
            'total' => 502.49,
            'shipping_cost' => 2.50,
            'shipping_tax' => 0,
        ];
    }

    protected function setProtectedProperty(object $object, string $propertyName, mixed $value): void
    {
        $reflection = new ReflectionProperty($object, $propertyName);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }

    protected function makeCheckoutProcessStubRoot(): string
    {
        $root = sys_get_temp_dir() . '/zc-checkout-process-stubs-' . uniqid('', true);
        mkdir($root, 0777, true);
        mkdir($root . '/classes', 0777, true);
        mkdir($root . '/modules', 0777, true);

        return $root;
    }

    protected function writeCheckoutProcessStubFiles(string $root): void
    {
        file_put_contents($root . '/modules/require_languages.php', "<?php\n");
        file_put_contents($root . '/classes/payment.php', <<<'PHP'
<?php
class payment
{
    public function __construct($code)
    {
    }

    public function checkCreditCovered(): void
    {
    }

    public function before_process(): void
    {
    }

    public function clear_payment(): void
    {
    }
}
PHP);
        file_put_contents($root . '/classes/order.php', <<<'PHP'
<?php
class order
{
    public array $billing = ['firstname' => 'Test'];
    public array $products = [['id' => 1, 'model' => 'TEST']];
    public array $info = [
        'payment_method' => 'cod',
        'payment_module_code' => 'cod',
        'coupon_code' => '',
        'currency' => 'USD',
        'currency_value' => 1,
        'shipping_method' => 'Flat Rate',
        'order_status' => 1,
    ];

    public function create($orderTotals): int
    {
        return 1001;
    }

    public function create_add_products($insertId): void
    {
    }

    public function send_order_email($insertId): void
    {
    }
}
PHP);
        file_put_contents($root . '/classes/shipping.php', <<<'PHP'
<?php
class shipping
{
    public function __construct($shippingCode)
    {
    }
}
PHP);
        file_put_contents($root . '/classes/order_total.php', <<<'PHP'
<?php
class order_total
{
    public function pre_confirmation_check(): array
    {
        return [];
    }

    public function process(): array
    {
        global $ot_coupon;
        $ot_coupon->process();
        return [];
    }

    public function clear_posts(): void
    {
    }
}
PHP);
    }

    protected function writeCheckoutProcessHarness(string $root): string
    {
        $source = file_get_contents(DIR_FS_CATALOG . 'includes/modules/checkout_process.php');
        $moduleRequire = "require '" . addslashes($root . "/modules/") . "' . zen_get_module_directory('require_languages.php');";
        $source = str_replace("require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');", $moduleRequire, $source);
        $source = str_replace("require DIR_WS_CLASSES . 'payment.php';", "require '" . addslashes($root . "/classes/payment.php") . "';", $source);
        $source = str_replace("require DIR_WS_CLASSES . 'order.php';", "require '" . addslashes($root . "/classes/order.php") . "';", $source);
        $source = str_replace("require DIR_WS_CLASSES . 'shipping.php';", "require '" . addslashes($root . "/classes/shipping.php") . "';", $source);
        $source = str_replace("require DIR_WS_CLASSES . 'order_total.php';", "require '" . addslashes($root . "/classes/order_total.php") . "';", $source);

        $harnessPath = $root . '/checkout_process_harness.php';
        file_put_contents($harnessPath, $source);

        return $harnessPath;
    }
}

class DiscountCouponRaceDb
{
    public array $couponDetails;
    public array $redemptions = [];
    public int $getLockCalls = 0;
    public array $getLockNames = [];
    private array $heldLockNames = [];

    public function __construct(array $couponDetails)
    {
        $this->couponDetails = $couponDetails;
    }

    public function Execute(string $sql, $limit = null): DiscountCouponRaceResult
    {
        if (str_contains($sql, 'SELECT GET_LOCK(')) {
            $this->getLockCalls++;
            preg_match("/SELECT GET_LOCK\\('(.*)', \\d+\\)/", $sql, $matches);
            $requestedLock = stripslashes($matches[1] ?? '');
            $this->getLockNames[] = $requestedLock;
            if (isset($this->heldLockNames[$requestedLock])) {
                return new DiscountCouponRaceResult(['got_lock' => 0], 1);
            }

            $this->heldLockNames[$requestedLock] = true;

            return new DiscountCouponRaceResult(['got_lock' => 1], 1);
        }

        if (str_contains($sql, 'SELECT RELEASE_LOCK(')) {
            preg_match("/SELECT RELEASE_LOCK\\('(.*)'\\)/", $sql, $matches);
            $releasedLock = stripslashes($matches[1] ?? '');
            unset($this->heldLockNames[$releasedLock]);
            return new DiscountCouponRaceResult(['released' => 1], 1);
        }

        if (str_contains($sql, 'FROM ' . TABLE_COUPONS . ' WHERE coupon_id = 1')) {
            return new DiscountCouponRaceResult($this->couponDetails, 1);
        }

        if (str_contains($sql, 'SELECT count(coupon_id) as total_uses_of_coupon')) {
            return new DiscountCouponRaceResult(['total_uses_of_coupon' => count($this->redemptions)], 1);
        }

        if (str_contains($sql, 'FROM ' . TABLE_COUPON_REDEEM_TRACK) && str_contains($sql, 'AND customer_id =')) {
            preg_match('/AND customer_id = (\d+)/', $sql, $customerMatches);
            $customerId = (int)($customerMatches[1] ?? 0);
            $matching = array_values(array_filter(
                $this->redemptions,
                static fn(array $redemption): bool => $redemption['customer_id'] === $customerId
            ));

            return new DiscountCouponRaceResult(
                empty($matching) ? [] : ['coupon_id' => $matching[0]['coupon_id']],
                count($matching),
                empty($matching)
            );
        }

        if (str_contains($sql, 'INSERT INTO ' . TABLE_COUPON_REDEEM_TRACK)) {
            preg_match("/VALUES \\('(\\d+)', now\\(\\), '.*', '(\\d+)', '(\\d+)'\\)/", $sql, $matches);
            $this->redemptions[] = [
                'coupon_id' => (int)($matches[1] ?? 0),
                'customer_id' => (int)($matches[2] ?? 0),
                'order_id' => (int)($matches[3] ?? 0),
            ];

            return new DiscountCouponRaceResult([], 1);
        }

        throw new RuntimeException('Unhandled SQL in test double: ' . $sql);
    }
}

class DiscountCouponRaceResult
{
    public array $fields;
    public bool $EOF;
    private int $recordCount;

    public function __construct(array $fields, int $recordCount, bool $eof = false)
    {
        $this->fields = $fields;
        $this->recordCount = $recordCount;
        $this->EOF = $eof;
    }

    public function RecordCount(): int
    {
        return $this->recordCount;
    }
}

class DiscountCouponRaceMessageStack
{
    public array $messages = [];
    public array $stacks = [];

    public function add_session(string $stack, string $message, string $type): void
    {
        $this->stacks[] = $stack;
        $this->messages[] = compact('stack', 'message', 'type');
    }
}

class DiscountCouponRaceGuestAwareCoupon extends ot_coupon
{
    public function __construct(private int $guestLockCustomerId)
    {
        parent::__construct();
    }

    protected function resolveGuestCouponRedemptionLockCustomerId(array $coupon_details): int
    {
        return $this->guestLockCustomerId;
    }
}

class DiscountCouponRaceAbortCoupon
{
    private bool $abortCheckoutProcess = true;

    public function process(): void
    {
    }

    public function shouldAbortCheckoutProcess(): bool
    {
        return $this->abortCheckoutProcess;
    }

    public function clearCheckoutProcessAbort(): void
    {
        $this->abortCheckoutProcess = false;
    }
}

class DiscountCouponRaceCheckoutCart
{
    public string $cartID = '';

    public function reset(bool $resetDatabase): void
    {
    }
}

class DiscountCouponRaceNotifier
{
    public function notify(string $eventId, ...$params): void
    {
    }
}

class DiscountCouponRaceRedirectException extends RuntimeException
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
