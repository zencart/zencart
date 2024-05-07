<?php

namespace Tests\FeatureStore\GVCoupons;

use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcFeatureTestCaseStore;

class GiftVoucherRedeemTest extends zcFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    /**
     * @test
     * scenario GV 1
     */
    public function testGvRedeemGuestNoGVNum(): void
    {
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=gv_redeem');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('To redeem a Gift Voucher you must create an account.', (string)$response->getContent() );
        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }

    /**
     * @test
     * scenario GV 2
     */
    public function testGvRedeemFixedCustomer(): void
    {
        self::runCustomSeeder('CouponTableSeeder');
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=gv_redeem&gv_no=VALID10');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Congratulations, you have redeemed a Gift Certificate worth $10.00.', (string)$response->getContent() );
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => 100.00, 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('-$45.29', (string)$response->getContent() );
    }

    /**
     * @test
     * scenario GV 4
     */
    public function testPurchaseCreditCoversFails(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $this->updateGVBalance($profile);
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => 45.28, 'payment' => '']);
        $response = (string)$this->browser->getResponse()->getContent();
        // TODO: It's possible this might be "found" merely because it's part of a JS error string in the HTML, not because it's properly rendered by the test actions...
        $this->assertStringContainsString('Please select a payment method for your order', $response);
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => 45.28, 'payment' => '']);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response, 4000);
        $this->assertStringContainsString('Please select a payment method for your order', $lookup_section);
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => '1,5', 'payment' => '']);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="checkoutOrderTotals"', $response);
        $this->assertStringContainsString('SEK6,4259', $lookup_section);
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=gv_send');
        $this->browser->submitForm('Send Now', ['to_name' => 'Tom Bombadil', 'email' => 'foo@example.com', 'amount' => '20,50', 'message' => 'This is a test message']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Send Gift Certificate Confirmation', (string)$response->getContent() );
        $this->assertStringContainsString('SEK20,50', (string)$response->getContent() );
        $this->browser->submitForm('Send Gift Certificate', []);
        $this->setConfiguration('DEFAULT_CURRENCY', 'USD');
    }
}
