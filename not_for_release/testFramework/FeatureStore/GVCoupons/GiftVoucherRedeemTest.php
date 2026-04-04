<?php

namespace Tests\FeatureStore\GVCoupons;

use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\LogFileConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class GiftVoucherRedeemTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;
    use LogFileConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    /**
     * @test
     * scenario GV 2
     */
    public function testGvRedeemFixedCustomer(): void
    {
        self::runCustomSeeder('CouponTableSeeder');
        $this->createCustomerAccountOrLogin('florida-basic1');

        $response = $this->visitGiftVoucherRedeem('VALID10')
            ->assertOk()
            ->assertSee('Congratulations, you have redeemed a Gift Certificate worth $10.00.');

        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }

    /**
     * @test
     * scenario GV 3
     */
    public function testPurchaseWithGiftVoucher(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '100.00', 'payment' => ''])
            ->assertOk();

        $response->assertSee('&#8209;$45.29');
    }

    /**
     * @test
     * scenario GV 4
     */
    public function testPurchaseCreditCoversFails(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $this->runGiftVoucherCheckout(['cot_gv' => '45.28', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');
    }

    /**
     * @test
     * scenario GV 5
     */
    public function testPurchaseCreditCoversFailsShippingTax(): void
    {
        $this->switchFlatShippingTax('on');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $this->runGiftVoucherCheckout(['cot_gv' => '45.28', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');
        $this->switchFlatShippingTax('off');
    }

    /**
     * @test
     * scenario GV 6
     */
    public function testPurchaseWithGiftVoucherSEK(): void
    {
        $this->setConfiguration('DEFAULT_CURRENCY', 'SEK');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $response = $this->runGiftVoucherCheckout(['cot_gv' => '1,5', 'payment' => ''])
            ->assertOk();

        $lookupSection = self::locateElementInPageSource('id="checkoutOrderTotals"', $response->content);
        $this->assertStringContainsString('SEK6,4259', $lookupSection);
        $this->setConfiguration('DEFAULT_CURRENCY', 'USD');
    }

    /**
     * @test
     * scenario GV 7
     */
    public function testSendGiftVoucherSEK(): void
    {
        $this->setConfiguration('DEFAULT_CURRENCY', 'SEK');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $confirmation = $this->submitGiftVoucherSendForm([
            'to_name' => 'Tom Bombadil',
            'email' => 'foo@example.com',
            'amount' => '20,50',
            'message' => 'This is a test message',
        ])->assertOk()
            ->assertSee('Send Gift Certificate Confirmation')
            ->assertSee('SEK20,50');

        $this->confirmGiftVoucherSend($confirmation)->assertOk();
        $this->setConfiguration('DEFAULT_CURRENCY', 'USD');
    }

    private function runGiftVoucherCheckout(array $paymentData)
    {
        $this->emptyCart();

        $cartResponse = $this->addProductToCart(3, '1_9')
            ->assertRedirect('main_page=shopping_cart');

        $this->followRedirect($cartResponse)
            ->assertOk()
            ->assertSee('Your Shopping Cart Contents');

        $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        return $this->continueCheckoutPayment($paymentData);
    }
}
