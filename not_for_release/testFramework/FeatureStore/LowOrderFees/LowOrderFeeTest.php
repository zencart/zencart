<?php

namespace Tests\FeatureStore\LowOrderFees;

use Tests\Support\helpers\ProfileManager;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\LowOrderFeeConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class LowOrderFeeTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;
    use LowOrderFeeConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    /**
     * @test
     * scenario LOF 1
     */
    public function it_tests_a_simple_loworderfee(): void
    {
        $this->switchLowOrderFee('on');
        $this->createCustomerAccountOrLogin('florida-basic1');

        $response = $this->runLowOrderFeeCheckout();

        $response->assertSee('39.99');
        $response->assertSee('2.50');
        $response->assertSee('2.80');
        $response->assertSee('5.00');
        $response->assertSee('50.29');

        $this->switchLowOrderFee('off');
    }

    /**
     * @test
     * scenario LOF 2
     */
    public function it_test_a_loworderfee_with_almost_full_GV(): void
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $response = $this->runLowOrderFeeCheckout();

        $response->assertSee('39.99');
        $response->assertSee('2.50');
        $response->assertSee('2.80');
        $response->assertSee('5.00');
        $response->assertSee('50.29');

        $this->continueCheckoutPayment(['cot_gv' => '45.28', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->switchLowOrderFee('off');
    }

    /**
     * @test
     * scenario LOF 3
     */
    public function it_tests_lowOrderFee_with_full_GV(): void
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $response = $this->runLowOrderFeeCheckout();

        $response->assertSee('39.99');
        $response->assertSee('2.50');
        $response->assertSee('2.80');
        $response->assertSee('5.00');
        $response->assertSee('50.29');

        $this->continueCheckoutPayment(['cot_gv' => '45.29', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->switchLowOrderFee('off');
    }

    /**
     * @test
     * scenario LOF 4
     */
    public function it_tests_loworderfee_with_almost_full_GV_and_shippingTax(): void
    {
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $response = $this->runLowOrderFeeCheckout();

        $response->assertSee('39.99');
        $response->assertSee('2.50');
        $response->assertSee('3.05');
        $response->assertSee('5.00');
        $response->assertSee('50.54');

        $this->continueCheckoutPayment(['cot_gv' => '45.76', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->switchLowOrderFee('off');
        $this->switchItemShippingTax('off');
    }

    /**
     * @test
     * scenario LOF 5
     */
    public function it_tests_loworderfee_with_full_GV_and_shipping_tax(): void
    {
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $response = $this->runLowOrderFeeCheckout();

        $response->assertSee('39.99');
        $response->assertSee('2.50');
        $response->assertSee('3.05');
        $response->assertSee('5.00');
        $response->assertSee('50.54');

        $confirmation = $this->continueCheckoutPayment(['cot_gv' => '50.54', 'payment' => ''])
            ->assertOk()
            ->assertSee('Order Confirmation');

        $confirmation->assertSee('39.99');
        $confirmation->assertSee('2.50');
        $confirmation->assertSee('3.05');
        $confirmation->assertSee('5.00');
        $confirmation->assertSee('&#8209;$50.54');
        $confirmation->assertSee('0.00');

        $this->switchLowOrderFee('off');
        $this->switchItemShippingTax('off');
    }

    /**
     * @test
     * scenario LOF 6
     */
    public function it_tests_loworderfee_with_full_GV_shipping_tax_inclusive(): void
    {
        $this->switchToTaxInclusive();
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);

        $confirmation = $this->runLowOrderFeeCheckout(['cot_gv' => '50.54', 'payment' => '']);

        $confirmation->assertSee('42.79');
        $confirmation->assertSee('2.75');
        $confirmation->assertSee('3.05');
        $confirmation->assertSee('5.00');
        $confirmation->assertSee('&#8209;$50.54');
        $confirmation->assertSee('0.00');

        $this->switchLowOrderFee('off');
        $this->switchItemShippingTax('off');
        $this->switchToTaxNonInclusive();
    }

    /**
     * @test
     * scenario LOF 7
     */
    public function it_tests_loworderfee_with_group_discount(): void
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);
        $this->setCustomerGroupDiscount($profile['email_address'], 1);

        $confirmation = $this->runLowOrderFeeCheckout(['payment' => ''])
            ->assertOk();

        $confirmation->assertSee('39.99');
        $confirmation->assertSee('2.50');
        $confirmation->assertSee('&#8209;$4.00');
        $confirmation->assertSee('2.52');
        $confirmation->assertSee('5.00');
        $confirmation->assertSee('46.01');

        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchLowOrderFee('off');
    }

    /**
     * @test
     * scenario LOF 8
     */
    public function it_tests_loworderfee_with_group_discount_and_insufficient_GV(): void
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);
        $this->setCustomerGroupDiscount($profile['email_address'], 1);

        $response = $this->runLowOrderFeeCheckout();

        $response->assertSee('39.99');
        $response->assertSee('2.50');
        $response->assertSee('&#8209;$4.00');
        $response->assertSee('2.52');
        $response->assertSee('5.00');
        $response->assertSee('46.01');

        $this->continueCheckoutPayment(['cot_gv' => '39.99', 'payment' => ''])
            ->assertOk()
            ->assertSee('Please select a payment method for your order');

        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchLowOrderFee('off');
    }

    /**
     * @test
     * scenario LOF 9
     */
    public function it_tests_loworderfee_with_group_discount_and_full_GV(): void
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);
        $this->setCustomerGroupDiscount($profile['email_address'], 1);

        $paymentPage = $this->runLowOrderFeeCheckout();
        $paymentPage->assertSee('39.99');

        $confirmation = $this->continueCheckoutPayment(['cot_gv' => '46.01', 'payment' => ''])
            ->assertOk();

        $confirmation->assertSee('39.99');
        $confirmation->assertSee('2.50');
        $confirmation->assertSee('&#8209;$4.00');
        $confirmation->assertSee('2.52');
        $confirmation->assertSee('5.00');
        $confirmation->assertSee('&#8209;$46.01');

        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchLowOrderFee('off');
    }

    private function runLowOrderFeeCheckout(array $paymentData = [])
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

        if ($paymentData !== []) {
            return $this->continueCheckoutPayment($paymentData)->assertOk();
        }

        return $this->visitCheckoutPayment()
            ->assertOk()
            ->assertSee('Payment Information');
    }
}
