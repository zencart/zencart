<?php

namespace Tests\FeatureStore\GroupDiscounts;

use Tests\Support\helpers\ProfileManager;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\DiscountCouponConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class GroupDiscountTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;
    use DiscountCouponConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    /**
     * @test
     * scenario GD 1
     */
    public function testGroupDiscountsSimple(): void
    {
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);

        $response = $this->runGroupDiscountCheckout();

        $response->assertSee('39.99');
        $response->assertSee('&#8209;$4.00');
        $response->assertSee('2.52');
        $response->assertSee('41.01');

        $this->setCustomerGroupDiscount($profile['email_address'], 0);
    }

    /**
     * @test
     * scenario GD 2
     */
    public function testGroupDiscountsWithDiscountCoupon(): void
    {
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->createCoupon('test10percent');

        $response = $this->runGroupDiscountCheckout(['dc_redeem_code' => 'test10percent']);

        $response->assertSee('39.99');
        $response->assertSee('&#8209;$4.00');
        $response->assertSee('&#8209;$3.60');
        $response->assertSee('2.27');
        $response->assertSee('37.16');

        $this->setCustomerGroupDiscount($profile['email_address'], 0);
    }

    /**
     * @test
     * scenario GD 3
     */
    public function testGroupDiscountsSimpleTaxInclusive(): void
    {
        $this->switchToTaxInclusive();
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);

        $response = $this->runGroupDiscountCheckout();

        $response->assertSee('42.79');
        $response->assertSee('&#8209;$4.28');
        $response->assertSee('2.52');
        $response->assertSee('41.01');

        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchToTaxNonInclusive();
    }

    /**
     * @test
     * scenario GD 4
     */
    public function testGroupDiscountsSimpleTaxInclusiveShippingTax(): void
    {
        $this->switchToTaxInclusive();
        $this->switchItemShippingTax('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);

        $response = $this->runGroupDiscountCheckout();

        $response->assertSee('42.79');
        $response->assertSee('&#8209;$4.28');
        $response->assertSee('2.77');
        $response->assertSee('41.26');

        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchItemShippingTax('off');
        $this->switchToTaxNonInclusive();
    }

    /**
     * @test
     * scenario GD 5
     */
    public function testGroupDiscountsSimpleTaxInclusiveShippingTaxSplitMode(): void
    {
        $this->switchToTaxInclusive();
        $this->switchItemShippingTax('on');
        $this->switchSplitTaxMode('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);

        $response = $this->runGroupDiscountCheckout();

        $response->assertSee('42.79');
        $response->assertSee('2.75');
        $response->assertSee('&#8209;$4.28');
        $response->assertSee('2.52');
        $response->assertSee('0.25');
        $response->assertSee('41.26');

        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchSplitTaxMode('off');
        $this->switchItemShippingTax('off');
        $this->switchToTaxNonInclusive();
    }

    private function runGroupDiscountCheckout(array $paymentData = [])
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

        return $this->continueCheckoutPayment($paymentData)
            ->assertOk();
    }
}
