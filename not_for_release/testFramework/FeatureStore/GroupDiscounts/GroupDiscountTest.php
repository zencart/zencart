<?php
namespace Tests\Feature;

use Tests\Support\helpers\ProfileManager;
use Tests\Support\zcFeatureTestCaseStore;

class GroupDiscountTest extends zcFeatureTestCaseStore
{
    /**
     * @test
     * scenario GD 1
     */
    public function testGroupDiscountsSimple()
    {

        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent());
        $this->assertStringContainsString('-$4.00', (string)$response->getContent());
        $this->assertStringContainsString('2.52', (string)$response->getContent());
        $this->assertStringContainsString('41.01', (string)$response->getContent());
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
    }

    /**
     * @test
     * scenario GD 2
     */
    public function testGroupDiscountsWithDiscountCoupon()
    {
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->createCoupon('test10percent');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', ['dc_redeem_code' => 'test10percent']);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('39.99', (string)$response->getContent());
        $this->assertStringContainsString('-$4.00', (string)$response->getContent());
        $this->assertStringContainsString('-$3.60', (string)$response->getContent());
        $this->assertStringContainsString('2.27', (string)$response->getContent());
        $this->assertStringContainsString('37.16', (string)$response->getContent());
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
    }

    /**
     * @test
     * scenario GD 3
     */
    public function testGroupDiscountsSimpleTaxInclusive()
    {
        $this->switchToTaxInclusive();
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('42.79', (string)$response->getContent());
        $this->assertStringContainsString('-$4.28', (string)$response->getContent());
        $this->assertStringContainsString('2.52', (string)$response->getContent());
        $this->assertStringContainsString('41.01', (string)$response->getContent());
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchToTaxNonInclusive();
    }

    /**
     * @test
     * scenario GD 4
     */
    public function testGroupDiscountsSimpleTaxInclusiveShippingTax()
    {
        $this->switchToTaxInclusive();
        $this->switchItemShippingTax('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('42.79', (string)$response->getContent());
        $this->assertStringContainsString('-$4.28', (string)$response->getContent());
        $this->assertStringContainsString('2.77', (string)$response->getContent());
        $this->assertStringContainsString('41.26', (string)$response->getContent());
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchItemShippingTax('off');
        $this->switchToTaxNonInclusive();
    }

    /**
     * @test
     * scenario GD 5
     */
    public function testGroupDiscountsSimpleTaxInclusiveShippingTaxSplitMode()
    {
        $this->switchToTaxInclusive();
        $this->switchItemShippingTax('on');
        $this->switchSplitTaxMode('on');
        $profile = ProfileManager::getProfile('florida-basic1');
        $this->createCustomerAccountOrLogin('florida-basic1');
        $this->setCustomerGroupDiscount($profile['email_address'], 1);
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=shopping_cart&action=empty_cart');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->browser->request('GET', HTTP_SERVER . '/index.php?main_page=checkout_shipping');
        $this->browser->submitForm('Continue', []);
        $this->browser->submitForm('Continue', []);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('42.79', (string)$response->getContent());
        $this->assertStringContainsString('2.75', (string)$response->getContent());
        $this->assertStringContainsString('-$4.28', (string)$response->getContent());
        $this->assertStringContainsString('2.52', (string)$response->getContent());
        $this->assertStringContainsString('0.25', (string)$response->getContent());
        $this->assertStringContainsString('41.26', (string)$response->getContent());
        $this->setCustomerGroupDiscount($profile['email_address'], 0);
        $this->switchSplitTaxMode('off');
        $this->switchItemShippingTax('off');
        $this->switchToTaxNonInclusive();
    }
}
