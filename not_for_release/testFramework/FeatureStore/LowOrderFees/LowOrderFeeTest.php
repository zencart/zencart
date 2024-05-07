<?php
namespace Tests\FeatureStore\LowOrderFees;

use Tests\Support\helpers\ProfileManager;
use Tests\Support\zcFeatureTestCaseStore;

class LowOrderFeeTest extends zcFeatureTestCaseStore
{
    /**
     * @test
     * scenario LOF 1
     */
    public function it_tests_a_simple_loworderfee(): void
    {
        $this->switchLowOrderFee('on');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent() );
        $this->browser->submitForm('Continue', []);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section); //sub-total
        $this->assertStringContainsString('2.50', $lookup_section); // shipping
        $this->assertStringContainsString('2.80', $lookup_section); // tax
        $this->assertStringContainsString('5.00', $lookup_section);// low order fee
        $this->assertStringContainsString('50.29', $lookup_section); // total
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section); //sub-total
        $this->assertStringContainsString('2.50', $lookup_section); // shipping
        $this->assertStringContainsString('2.80', $lookup_section); // tax
        $this->assertStringContainsString('5.00', $lookup_section);// low order fee
        $this->assertStringContainsString('50.29', $lookup_section); // total
        $this->browser->submitForm('Continue', ['cot_gv' => '45.28', 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Please select a payment method for your order', (string)$response->getContent() );
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section); //sub-total
        $this->assertStringContainsString('2.50', $lookup_section); // shipping
        $this->assertStringContainsString('2.80', $lookup_section); // tax
        $this->assertStringContainsString('5.00', $lookup_section);// low order fee
        $this->assertStringContainsString('50.29', $lookup_section); // total
        $this->browser->submitForm('Continue', ['cot_gv' => '45.29', 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Please select a payment method for your order', (string)$response->getContent() );
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section); //sub-total
        $this->assertStringContainsString('2.50', $lookup_section); // shipping
        $this->assertStringContainsString('3.05', $lookup_section); // tax
        $this->assertStringContainsString('5.00', $lookup_section);// low order fee
        $this->assertStringContainsString('50.54', $lookup_section); // total
        $this->browser->submitForm('Continue', ['cot_gv' => '45.76', 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Please select a payment method for your order', (string)$response->getContent() );
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section); //sub-total
        $this->assertStringContainsString('2.50', $lookup_section); // shipping
        $this->assertStringContainsString('3.05', $lookup_section); // tax
        $this->assertStringContainsString('5.00', $lookup_section);// low order fee
        $this->assertStringContainsString('50.54', $lookup_section); // total
        $this->browser->submitForm('Continue', ['cot_gv' => '50.54', 'payment' => '']);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section); //sub-total
        $this->assertStringContainsString('2.50', $lookup_section); // shipping item
        $this->assertStringContainsString('3.05', $lookup_section); // Tax
        $this->assertStringContainsString('5.00', $lookup_section); // low order fee
        $this->assertStringContainsString('-$50.54', $lookup_section); // gv used
        $this->assertStringContainsString('0.00', $lookup_section); // balance
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['cot_gv' => '50.54', 'payment' => '']);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('42.79', $lookup_section);
        $this->assertStringContainsString('2.75', $lookup_section);
        $this->assertStringContainsString('3.05', $lookup_section);
        $this->assertStringContainsString('5.00', $lookup_section);
        $this->assertStringContainsString('-$50.54', $lookup_section);
        $this->assertStringContainsString('0.00', $lookup_section);
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['payment' => '']);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section);
        $this->assertStringContainsString('2.50', $lookup_section);
        $this->assertStringContainsString('-$4.00', $lookup_section);
        $this->assertStringContainsString('2.52', $lookup_section);
        $this->assertStringContainsString('5.00', $lookup_section);
        $this->assertStringContainsString('46.01', $lookup_section);
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section);
        $this->assertStringContainsString('2.50', $lookup_section);
        $this->assertStringContainsString('-$4.00', $lookup_section);
        $this->assertStringContainsString('2.52', $lookup_section);
        $this->assertStringContainsString('5.00', $lookup_section);
        $this->assertStringContainsString('46.01', $lookup_section);
        $this->browser->submitForm('Continue', ['cot_gv' => 39.99, 'payment' => '']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Please select a payment method for your order', (string)$response->getContent() );
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
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent() );
        $this->browser->submitForm('Continue', ['cot_gv' => 46.01, 'payment' => '']);
        $response = (string)$this->browser->getResponse()->getContent();
        $lookup_section = self::locateElementInPageSource('id="orderTotals"', $response);
        $this->assertStringContainsString('39.99', $lookup_section);
        $this->assertStringContainsString('2.50', $lookup_section);
        $this->assertStringContainsString('-$4.00', $lookup_section);
        $this->assertStringContainsString('2.52', $lookup_section);
        $this->assertStringContainsString('5.00', $lookup_section);
        $this->assertStringContainsString('-$46.01', $lookup_section);
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchLowOrderFee('off');
    }
}
