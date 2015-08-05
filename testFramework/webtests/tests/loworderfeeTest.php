<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

/**
 * Class loworderfeeTest
 */
class loworderfeeTest extends CommonTestResources
{

    public function testLowOrderFeeSetupGV()
    {
        $this->switchItemShippingTax('off');
        if ($this->getCouponBalanceCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL) < 200) {
            $this->purchaseGiftVoucherQueueOn(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD, 2);
        }
    }

    public function testLowOrderFeeSimple()
    {
        $this->switchLowOrderFee('on');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byId('pmt-cod')->click();
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('39.99'); //net price
        $this->assertTextPresent('$2.50'); //shippimg
        $this->assertTextPresent('$2.80'); //tax
        $this->assertTextPresent('$5.00'); //loworder fee
        $this->assertTextPresent('50.29'); //total
        $this->byId('btn_submit')->click();
        $this->switchLowOrderFee('off');
    }

    public function testLowOrderFeeWithGVAlmostFull()
    {
        $this->switchLowOrderFee('on');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('45.28');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('Please select a payment method for your order');
        $this->switchLowOrderFee('off');
    }

    public function testLowOrderFeeWithGVFull()
    {
        $this->switchLowOrderFee('on');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('45.29');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('Please select a payment method for your order');
        $this->switchLowOrderFee('off');
    }
}
