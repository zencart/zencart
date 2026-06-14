<?php

namespace Tests\FeatureStore\GVCoupons;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\LogFileConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class GiftVoucherRedeemTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;
    use LogFileConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->setGiftVoucherCoverageOptions(includeTax: false, includeShipping: true);
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None');
        $this->setConfiguration('DEFAULT_CURRENCY', 'USD');
        $this->switchToTaxNonInclusive();
        $this->switchFlatShippingTax('off');
        $this->switchItemShippingTax('off');
    }

    /**
     * scenario GV 2
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function testGvRedeemFixedCustomer(): void
    {
        self::runCustomSeeder('CouponTableSeeder');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $this->visitGiftVoucherRedeem('VALID10')
            ->assertOk()
            ->assertSee('Congratulations, you have redeemed a Gift Certificate worth $10.00.');

        $coupon = TestDb::selectOne(
            'SELECT coupon_id, coupon_active
               FROM coupons
              WHERE coupon_code = :coupon_code
              LIMIT 1',
            [':coupon_code' => 'VALID10']
        );
        $redeemTrack = TestDb::selectOne(
            'SELECT coupon_id, customer_id
               FROM coupon_redeem_track
              WHERE customer_id = :customer_id
              ORDER BY unique_id DESC
              LIMIT 1',
            [':customer_id' => $customerId]
        );
        $balance = TestDb::selectValue(
            'SELECT amount
               FROM coupon_gv_customer
              WHERE customer_id = :customer_id
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($coupon);
        $this->assertSame('N', (string) $coupon['coupon_active']);
        $this->assertNotNull($redeemTrack);
        $this->assertSame((string) $coupon['coupon_id'], (string) $redeemTrack['coupon_id']);
        $this->assertSame((string) $customerId, (string) $redeemTrack['customer_id']);
        $this->assertSame('10.0000', (string) $balance);

        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }

    /**
     * scenario GV 3
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucher(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: true, includeShipping: true);
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        $this->setExactGiftVoucherBalance($profile, 1000);

        $this->assertNotNull($customerId);

        $confirmationPage = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Order Confirmation');

        $confirmationPage->assertSee('&#8209;$45.29');
        $confirmationPage->assertSee('0.00');

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
        $balance = TestDb::selectValue(
            'SELECT amount
               FROM coupon_gv_customer
              WHERE customer_id = :customer_id
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($order, 'Expected checkout flow to create an order.');
        $this->assertSame('0.0000', number_format((float) $order['order_total'], 4, '.', ''));
        $this->assertSame('954.7100', number_format((float) $balance, 4, '.', ''));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherExcludesTaxByDefault(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: false, includeShipping: true);
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$42.49', '2.80']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherExcludesShippingWhenConfigured(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: true, includeShipping: false);
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$42.79', '2.50']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherExcludesShippingTaxWhenShippingIsTaxable(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: true, includeShipping: false);
        $this->switchItemShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$42.79', '2.75']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherExcludesShippingTaxWhenShippingIsTaxableAndDisplayIsTaxInclusive(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: true, includeShipping: false);
        $this->switchToTaxInclusive();
        $this->switchItemShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$42.79', '2.75']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherExcludesTaxAndTaxableShippingWhenDisplayIsTaxInclusive(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: false, includeShipping: false);
        $this->switchToTaxInclusive();
        $this->switchItemShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$39.99', '5.55']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherExcludesTaxAndShippingWhenConfigured(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: false, includeShipping: false);
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$39.99', '5.30']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherStandardTaxRecalculationUsesExcludedTaxBase(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: false, includeShipping: true);
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_CALC_TAX', 'Standard');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '10.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$10.00', '34.63']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedShippingTax(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: true, includeShipping: false);
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_CALC_TAX', 'Standard');
        $this->switchItemShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '10.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$10.00', '34.89']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedShippingTaxWhenDisplayIsTaxInclusive(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: true, includeShipping: false);
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_CALC_TAX', 'Standard');
        $this->switchToTaxInclusive();
        $this->switchItemShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '10.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$10.00', '34.89']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherExcludesFlatShippingTaxWhenDisplayIsTaxInclusive(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: true, includeShipping: false);
        $this->switchToTaxInclusive();
        $this->switchFlatShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''], ['shipping' => 'flat_flat'])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$42.79', '5.50']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedFlatShippingTaxWhenDisplayIsTaxInclusive(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: true, includeShipping: false);
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_CALC_TAX', 'Standard');
        $this->switchToTaxInclusive();
        $this->switchFlatShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '10.00', 'payment' => ''], ['shipping' => 'flat_flat'])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$10.00', '37.64']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testSubmittingRedeemWithoutVoucherCodeShowsErrorWithoutLogs(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic1');

        $response = $this->runGiftVoucherCheckout(['submit_redeem_x' => '1', 'payment' => ''])
            ->assertOk()
            ->assertSee('You did not enter a Redemption Code.');

        $this->assertCheckoutTotalsContain($response->content, ['45.29']);

        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testSubmittingInvalidRedeemCodeShowsErrorWithoutLogs(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic1');

        $response = $this->runGiftVoucherCheckout(['gv_redeem_code' => 'NOTREAL123', 'payment' => ''])
            ->assertOk()
            ->assertSee('Invalid Gift Certificate Redemption Code');

        $this->assertCheckoutTotalsContain($response->content, ['45.29']);

        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testSubmittingAlreadyRedeemedVoucherCodeShowsErrorWithoutLogs(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $couponId = $this->createGiftVoucherCoupon('VALID10', '10.0000');

        TestDb::insert('coupon_redeem_track', [
            'coupon_id' => $couponId,
            'customer_id' => $customerId,
            'redeem_date' => date('Y-m-d H:i:s'),
            'redeem_ip' => '127.0.0.1',
        ]);

        $response = $this->runGiftVoucherCheckout(['gv_redeem_code' => 'VALID10', 'payment' => ''])
            ->assertOk()
            ->assertSee('Invalid Gift Certificate Redemption Code');

        $this->assertCheckoutTotalsContain($response->content, ['45.29']);

        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }

    /**
     * scenario GV 4
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseCreditCoversFails(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: false, includeShipping: true);
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $this->runGiftVoucherCheckout(['cot_gv' => '45.28', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');
    }

    /**
     * scenario GV 5
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseCreditCoversFailsShippingTax(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: false, includeShipping: true);
        $this->switchFlatShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $this->runGiftVoucherCheckout(['cot_gv' => '45.28', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');
        $this->switchFlatShippingTax('off');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherCreditNoteTaxRecalculationAdjustsTaxLine(): void
    {
        $this->setGiftVoucherCoverageOptions(includeTax: false, includeShipping: true);
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_CALC_TAX', 'Credit Note');
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_TAX_CLASS', '1');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '10.00', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->assertCheckoutTotalsContain($response->content, ['&#8209;$10.00', '35.29']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testPartialGiftVoucherApplicationPersistsReducedBalanceAfterOrder(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        $this->setExactGiftVoucherBalance($profile, 1000);

        $this->assertNotNull($customerId);

        $confirmationPage = $this->runGiftVoucherCheckout(['cot_gv' => '10.00', 'payment' => 'moneyorder'])
            ->assertOk()
            ->assertSee('Order Confirmation');

        $confirmationPage->assertSee('&#8209;$10.00');
        $confirmationPage->assertSee('35.29');

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
        $balance = TestDb::selectValue(
            'SELECT amount
               FROM coupon_gv_customer
              WHERE customer_id = :customer_id
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($order, 'Expected checkout flow to create an order.');
        $this->assertSame('35.2893', number_format((float) $order['order_total'], 4, '.', ''));
        $this->assertSame('990.0000', number_format((float) $balance, 4, '.', ''));
    }

    /**
     * scenario GV 6
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function testPurchaseWithGiftVoucherSEK(): void
    {
        $this->setConfiguration('DEFAULT_CURRENCY', 'SEK');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setExactGiftVoucherBalance($profile, 1000);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '1,5', 'payment' => ''])
            ->assertOk();

        $lookupSection = self::locateElementInPageSource('id="checkoutOrderTotals"', $response->content);
        $this->assertStringContainsString('SEK6,4259', $lookupSection);
        $this->setConfiguration('DEFAULT_CURRENCY', 'USD');
    }

    /**
     * scenario GV 7
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function testSendGiftVoucherSEK(): void
    {
        $this->setConfiguration('DEFAULT_CURRENCY', 'SEK');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        $this->setExactGiftVoucherBalance($profile, 1000);

        $this->assertNotNull($customerId);

        $confirmation = $this->submitGiftVoucherSendForm([
            'to_name' => 'Tom Bombadil',
            'email' => 'foo@example.com',
            'amount' => '20,50',
            'message' => 'This is a test message',
        ])->assertOk()
            ->assertSee('Send Gift Certificate Confirmation')
            ->assertSee('SEK20,50');

        $this->confirmGiftVoucherSend($confirmation)->assertOk();

        $coupon = TestDb::selectOne(
            'SELECT coupon_id, coupon_type, coupon_amount, coupon_active
               FROM coupons
              WHERE coupon_type = :coupon_type
              ORDER BY coupon_id DESC
              LIMIT 1',
            [':coupon_type' => 'G']
        );
        $emailTrack = TestDb::selectOne(
            'SELECT coupon_id, customer_id_sent, emailed_to
               FROM coupon_email_track
              WHERE customer_id_sent = :customer_id
              ORDER BY unique_id DESC
              LIMIT 1',
            [':customer_id' => $customerId]
        );
        $balance = TestDb::selectValue(
            'SELECT amount
               FROM coupon_gv_customer
              WHERE customer_id = :customer_id
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($coupon);
        $this->assertSame('G', $coupon['coupon_type']);
        $this->assertSame('117.1429', number_format((float) $coupon['coupon_amount'], 4, '.', ''));
        $this->assertSame('Y', (string) $coupon['coupon_active']);
        $this->assertNotNull($emailTrack);
        $this->assertSame((string) $coupon['coupon_id'], (string) $emailTrack['coupon_id']);
        $this->assertSame((string) $customerId, (string) $emailTrack['customer_id_sent']);
        $this->assertSame('foo@example.com', (string) $emailTrack['emailed_to']);
        $this->assertSame('882.8571', number_format((float) $balance, 4, '.', ''));

        $this->setConfiguration('DEFAULT_CURRENCY', 'USD');
    }

    private function runGiftVoucherCheckout(array $paymentData, array $shippingData = [])
    {
        $this->emptyCart();

        $cartResponse = $this->addProductToCart(3, '1_9')
            ->assertRedirect('main_page=shopping_cart');

        $this->followRedirect($cartResponse)
            ->assertOk()
            ->assertSee('Your Shopping Cart Contents');

        $this->continueCheckoutShipping($shippingData)
            ->assertOk()
            ->assertSee('Payment Information');

        return $this->continueCheckoutPayment($paymentData);
    }

    private function setExactGiftVoucherBalance(array $profile, float $amount): void
    {
        $this->addGiftVoucherBalance($profile['email_address'], $amount);
    }

    private function createGiftVoucherCoupon(string $couponCode, string $amount): int
    {
        return (int) TestDb::insert('coupons', [
            'coupon_type' => 'G',
            'coupon_code' => $couponCode,
            'coupon_amount' => $amount,
            'coupon_minimum_order' => '0.0000',
            'coupon_start_date' => date('Y-m-d H:i:s'),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
            'uses_per_coupon' => 1,
            'uses_per_user' => 1,
            'coupon_active' => 'Y',
            'date_created' => date('Y-m-d H:i:s'),
            'date_modified' => date('Y-m-d H:i:s'),
        ]);
    }

    private function setGiftVoucherCoverageOptions(bool $includeTax, bool $includeShipping): void
    {
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_INC_TAX', $includeTax ? 'true' : 'false');
        $this->setConfiguration('MODULE_ORDER_TOTAL_GV_INC_SHIPPING', $includeShipping ? 'true' : 'false');
    }

    private function assertCheckoutTotalsContain(string $content, array $expectedValues): void
    {
        $lookupSection = self::locateElementInPageSource('id="checkoutOrderTotals"', $content);
        foreach ($expectedValues as $expectedValue) {
            $this->assertStringContainsString($expectedValue, $lookupSection);
        }
    }
}
