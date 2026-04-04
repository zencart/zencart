<?php

namespace Tests\FeatureStore\TaxCalculations\TestStoreTaxCalculationsNoShippingTax;

use Symfony\Component\DomCrawler\Crawler;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class BasicTaxStoreNoShippingTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->setConfiguration('STORE_PRODUCT_TAX_BASIS', 'Store');
    }

    /**
     * @test
     * scenario BTC 5
     */
    public function testBasicCheckoutFloridaCustomer(): void
    {
        $this->runBasicCheckoutAssertions('florida-basic1', ['69.99', '2.50', '4.90', '77.39']);
    }

    /**
     * @test
     * scenario BTC 6
     */
    public function testBasicCheckoutNonFloridaCustomer(): void
    {
        $this->runBasicCheckoutAssertions('US-not-florida-basic', ['69.99', '2.50', '72.49']);
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
