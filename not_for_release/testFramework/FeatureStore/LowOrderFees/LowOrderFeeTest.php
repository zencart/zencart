<?php
namespace Tests\Feature;

use Tests\Support\helpers\ProfileManager;
use Tests\Support\zcFeatureTestCaseStore;

class LowOrderFeeTest extends zcFeatureTestCaseStore
{
    public function lowOrderFeeSetupGV($profile)
    {
        if ($this->getCouponBalanceCustomer($profile['email_address']) < 300) {
            $this->addGiftVoucherBalance($profile['email_address'], 1000);
        }
    }
    /** @test **/
    public function it_tests_a_simple_loworderfee()
    {
        $this->switchLowOrderFee('on');
        $this->createCustomerAccount('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent() );
        $this->browser->submitForm('Continue', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent() ); //sub-total
        $this->assertStringContainsString('2.50', (string)$response->getContent() ); // shipping
        $this->assertStringContainsString('2.80', (string)$response->getContent() ); // tax
        $this->assertStringContainsString('5.00', (string)$response->getContent() );// low order fee
        $this->assertStringContainsString('50.29', (string)$response->getContent() ); // total
//        $this->assertEquals(200, $response->getStatusCode());
//        $form = $browser->selectButton('Continue')->form();
//        var_dump($form->getValues());
////$form['shipping_cod'] = 'foo';
//        $this->assertTrue(true);
    }

        /** @test **/
    public function it_test_a_loworderfee_with_almost_full_GV()
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->lowOrderFeeSetupGV($profile);
        $this->loginCustomer('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => '45.28', 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Please select a payment method for your order', (string)$response->getContent() );
        $this->switchLowOrderFee('off');
    }
    /** @test **/
    public function it_tests_lowOrderFee_with_full_GV()
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->lowOrderFeeSetupGV($profile);
        $this->loginCustomer('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => '45.29', 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Please select a payment method for your order', (string)$response->getContent() );
        $this->switchLowOrderFee('off');
    }
    /** @test **/
    public function it_tests_loworderfee_with_almost_full_GV_and_shippingTax()
    {
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->lowOrderFeeSetupGV($profile);
        $this->loginCustomer('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => '45.76', 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Please select a payment method for your order', (string)$response->getContent() );
        $this->switchLowOrderFee('off');
        $this->switchItemShippingTax('off');
    }

    /** @test **/
    public function it_tests_loworderfee_with_full_GV_and_shipping_tax()
    {
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->lowOrderFeeSetupGV($profile);
        $this->loginCustomer('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => '50.46', 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent() ); //sub-total
        $this->assertStringContainsString('2.50', (string)$response->getContent() ); // shipping item
        $this->assertStringContainsString('2.97', (string)$response->getContent() ); // Tax
        $this->assertStringContainsString('5.00', (string)$response->getContent() ); // low order fee
        $this->assertStringContainsString('-$50.46', (string)$response->getContent() ); // gv used
        $this->assertStringContainsString('0.00', (string)$response->getContent() ); // balance
        $this->switchLowOrderFee('off');
        $this->switchItemShippingTax('off');
    }

    /** @test **/
    public function it_tests_loworderfee_with_full_GV_shipping_tax_inclusive()
    {
        $this->switchToTaxInclusive();
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->lowOrderFeeSetupGV($profile);
        $this->loginCustomer('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => '50.47', 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('42.79', (string)$response->getContent() );
        $this->assertStringContainsString('2.68', (string)$response->getContent() );
        $this->assertStringContainsString('2.98', (string)$response->getContent() );
        $this->assertStringContainsString('5.00', (string)$response->getContent() );
        $this->assertStringContainsString('-$50.47', (string)$response->getContent() );
        $this->assertStringContainsString('0.00', (string)$response->getContent() );
        $this->switchLowOrderFee('off');
        $this->switchItemShippingTax('off');
        $this->switchToTaxNonInclusive();
    }

    /** @test **/
    public function it_tests_loworderfee_with_group_discount()
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->loginCustomer('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent() );
        $this->assertStringContainsString('2.50', (string)$response->getContent() );
        $this->assertStringContainsString('-$4.00', (string)$response->getContent() );
        $this->assertStringContainsString('2.52', (string)$response->getContent() );
        $this->assertStringContainsString('5.00', (string)$response->getContent() );
        $this->assertStringContainsString('46.01', (string)$response->getContent() );
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchLowOrderFee('off');
    }

    /** @test **/
    public function it_tests_loworderfee_with_group_discount_and_insufficient_GV()
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->loginCustomer('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => 45.29, 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Please select a payment method for your order', (string)$response->getContent() );
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchLowOrderFee('off');
    }

    /** @test **/
    public function it_tests_loworderfee_with_group_discount_and_full_GV()
    {
        $this->switchLowOrderFee('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->loginCustomer('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => 46.01, 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent() );
        $this->assertStringContainsString('2.50', (string)$response->getContent() );
        $this->assertStringContainsString('-$4.00', (string)$response->getContent() );
        $this->assertStringContainsString('2.52', (string)$response->getContent() );
        $this->assertStringContainsString('5.00', (string)$response->getContent() );
        $this->assertStringContainsString('-$46.01', (string)$response->getContent() );
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchLowOrderFee('off');
    }
}
