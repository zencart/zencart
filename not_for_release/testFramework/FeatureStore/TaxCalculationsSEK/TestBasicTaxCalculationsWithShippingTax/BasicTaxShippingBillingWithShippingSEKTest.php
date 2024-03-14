<?php

namespace Tests\FeatureStore\TaxCalculations\TestBasicTaxCalculationsWithShippingTax;

use Tests\Support\zcFeatureTestCaseStore;

class BasicTaxShippingBillingWithShippingSEKTest extends zcFeatureTestCaseStore
{
    private static $ready = false;
    public function setUp(): void
    {

       parent::setUp(); // TODO: Change the autogenerated stub
        if (static::$ready) {
            return;
        }
        static::$ready = true;
        $this->setConfiguration('MODULE_SHIPPING_ITEM_TAX_CLASS', 1);
        $this->setConfiguration('DEFAULT_CURRENCY', 'SEK');
    }

    /**
     * @test
     * scenario BTC SEK 2
     */
    public function testBasicCheckoutFloridaCustomer()
    {
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=product_info&products_id=25');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Microsoft', (string)$response->getContent() );
        $this->browser->submitForm('Add to Cart', [
            'cart_quantity' => '1',
            'products_id' => '25',
            ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Your Shopping Cart Contents', (string)$response->getContent() );
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=checkout_shipping');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Delivery Information', (string)$response->getContent() );
        $this->browser->submitForm('Continue', [
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Payment Information', (string)$response->getContent() );
        $this->browser->submitForm('Continue', [
        ]);
        $this->assertStringContainsString('12,2483', (string)$response->getContent() );
        $this->assertStringContainsString('0,4375', (string)$response->getContent() );
        $this->assertStringContainsString('0,8880', (string)$response->getContent() );
        $this->assertStringContainsString('SEK13,5738SEK', (string)$response->getContent() );
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Order Confirmation', (string)$response->getContent() );
        $this->assertStringContainsString('12,2483', (string)$response->getContent() );
        $this->assertStringContainsString('0,4375', (string)$response->getContent() );
        $this->assertStringContainsString('0,8880', (string)$response->getContent() );
        $this->assertStringContainsString('SEK13,5738SEK', (string)$response->getContent() );
        $this->browser->submitForm('btn_submit_x', [
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Your Order Number is:', (string)$response->getContent() );
    }

    /**
     * @test
     * scenario BTC SEK 4
     */
    public function testBasicCheckoutNonFloridaCustomer()
    {
        $this->createCustomerAccountOrLogin('US-not-florida-basic');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=product_info&products_id=25');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Microsoft', (string)$response->getContent() );
        $this->browser->submitForm('Add to Cart', [
            'cart_quantity' => '1',
            'products_id' => '25',
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Your Shopping Cart Contents', (string)$response->getContent() );
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=checkout_shipping');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Delivery Information', (string)$response->getContent() );
        $this->browser->submitForm('Continue', [
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Payment Information', (string)$response->getContent() );
        $this->browser->submitForm('Continue', [
        ]);
        $this->assertStringContainsString('12,2483', (string)$response->getContent() );
        $this->assertStringContainsString('0,4375', (string)$response->getContent() );
        $this->assertStringContainsString('SEK12,6858SEK', (string)$response->getContent() );
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Order Confirmation', (string)$response->getContent() );
        $this->assertStringContainsString('12,2483', (string)$response->getContent() );
        $this->assertStringContainsString('0,4375', (string)$response->getContent() );
        $this->assertStringContainsString('SEK12,6858SEK', (string)$response->getContent() );
        $this->browser->submitForm('btn_submit_x', [
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Your Order Number is:', (string)$response->getContent() );
    }
}
