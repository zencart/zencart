<?php

namespace Tests\FeatureStore\TaxCalculationsSEK\TestBasicTaxCalculationsWithShippingTax;

use Symfony\Component\DomCrawler\Crawler;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class BasicTaxShippingBillingWithShippingSEKTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->setConfiguration('MODULE_SHIPPING_ITEM_TAX_CLASS', 1);
        $this->setConfiguration('DEFAULT_CURRENCY', 'SEK');
    }

    /**
     * @test
     * scenario BTC SEK 2
     */
    public function testBasicCheckoutFloridaCustomer(): void
    {
        $this->runBasicCheckoutAssertions('florida-basic1', [
            \PHP_VERSION_ID < 80400 ? '12,2483' : '12,2482',
            '0,4375',
            '0,8880',
            'SEK13,5738SEK',
        ]);
    }

    /**
     * @test
     * scenario BTC SEK 4
     */
    public function testBasicCheckoutNonFloridaCustomer(): void
    {
        $this->runBasicCheckoutAssertions('US-not-florida-basic', [
            \PHP_VERSION_ID < 80400 ? '12,2483' : '12,2482',
            '0,4375',
            \PHP_VERSION_ID < 80400 ? 'SEK12,6858SEK' : 'SEK12,6857SEK',
        ]);
    }

    private function runBasicCheckoutAssertions(string $profileName, array $expectedTotals): void
    {
        $this->createCustomerAccountOrLogin($profileName);

        $this->visitProduct(25)
            ->assertOk()
            ->assertSee('Microsoft');

        $cartResponse = $this->addProductToCart(25)
            ->assertRedirect('main_page=shopping_cart');

        $this->followRedirect($cartResponse)
            ->assertOk()
            ->assertSee('Your Shopping Cart Contents');

        $paymentPage = $this->continueCheckoutShipping()
            ->assertOk()
            ->assertSee('Payment Information');

        $paymentCrawler = new Crawler($paymentPage->content);
        foreach ($expectedTotals as $expectedTotal) {
            $this->assertStringContainsString($expectedTotal, $paymentCrawler->filter('#checkoutOrderTotals')->text());
        }

        $confirmationPage = $this->continueCheckoutPayment()
            ->assertOk()
            ->assertSee('Order Confirmation');

        $confirmationCrawler = new Crawler($confirmationPage->content);
        $this->assertStringContainsString('Order Confirmation', $confirmationCrawler->filter('#checkoutConfirmDefaultHeading')->text());
        foreach ($expectedTotals as $expectedTotal) {
            $this->assertStringContainsString($expectedTotal, $confirmationCrawler->filter('#orderTotals')->text());
        }

        $successResponse = $this->confirmCheckoutOrder()
            ->assertRedirect('main_page=checkout_success');

        $successPage = $this->followRedirect($successResponse)
            ->assertOk();

        $successCrawler = new Crawler($successPage->content);
        $this->assertStringContainsString('Your Order Number is:', $successCrawler->filter('#checkoutSuccessOrderNumber')->text());
    }
}
