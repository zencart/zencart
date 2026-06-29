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
            eval('function zen_in_guest_checkout(): bool { return false; }');
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
}

class DiscountCouponRaceDb
{
    public array $couponDetails;
    public array $redemptions = [];
    public int $getLockCalls = 0;
    private ?string $heldLockName = null;

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
            if ($this->heldLockName !== null && $this->heldLockName === $requestedLock) {
                return new DiscountCouponRaceResult(['got_lock' => 0], 1);
            }

            $this->heldLockName = $requestedLock;

            return new DiscountCouponRaceResult(['got_lock' => 1], 1);
        }

        if (str_contains($sql, 'SELECT RELEASE_LOCK(')) {
            $this->heldLockName = null;
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
