<?php
/**
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

/**
 * Class giftVoucherPurchasesTest
 */
class giftVoucherPurchasesTest extends CommonTestResources
{
    function testPurchaseGiftVoucherQueueOn()
    {
        $this->purchaseGiftVoucherQueueOn(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    }

    function testPurchaseWithGiftVoucher()
    {
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byCss('input[type=image]')->click();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('100');
        $this->byCss('input[type=image]')->click();
        $this->assertTextPresent('-$45.29');
        $this->byId('btn_submit')->click();
    }

    function testPurchaseCreditCovers()
    {
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byCss('input[type=image]')->click();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('45.28');
        $this->byCss('input[type=image]')->click();
        $this->assertTextPresent('Please select a payment method for your order');
    }

    function testPurchaseCreditCoversShippingTax()
    {
        $this->switchFlatShippingTax('on');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-flat-flat')->click();
        $this->byCss('input[type=image]')->click();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('48.73');
        $this->byCss('input[type=image]')->click();
        $this->assertTextPresent('Please select a payment method for your order');
        $this->switchFlatShippingTax('off');
    }

    function testPurchaseWithGiftVoucherSEK()
    {
        $this->setConfigurationValue('DEFAULT_CURRENCY', 'SEK');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byCss('input[type=image]')->click();
        $this->byId('pmt-cod')->click();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('20,50');
        $this->byCss('input[type=image]')->click();
        $this->assertTextPresent('SEK24,79');
        $this->byId('btn_submit')->click();
        $this->setConfigurationValue('DEFAULT_CURRENCY', 'USD');
    }

    function testSendGiftVoucherSEK()
    {
        $this->setConfigurationValue('DEFAULT_CURRENCY', 'SEK');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('https://' . BASE_URL . 'index.php?main_page=gv_send');
        $this->byId('to-name')->clear();
        $this->byId('to-name')->value('Tom Bombadil');
        $this->byId('email-address')->clear();
        $this->byId('email-address')->value(WEBTEST_DEFAULT_CUSTOMER_EMAIL);
        $this->byId('amount')->clear();
        $this->byId('amount')->value('20,50');
        $this->byId('message-area')->clear();
        $this->byid('message-area')->value('this is a test message');
        $this->byCss('input[type=image]')->click();

        $this->assertTextPresent('Send Gift Certificate Confirmation');
        $this->assertTextPresent('SEK20,50');
        $this->byCss('input[type=image]')->click();
        $this->setConfigurationValue('DEFAULT_CURRENCY', 'USD');
    }
}
