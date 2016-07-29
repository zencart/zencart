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
        if ($this->getCouponBalanceCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL) < 300) {
            $this->purchaseGiftVoucherQueueOn(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD, 3);
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
        $this->byName('checkout_address')->submit();
        $this->byId('pmt-cod')->click();
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('39.99'); //net price
        $this->assertTextPresent('$2.50'); //shipping
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
        $this->byName('checkout_address')->submit();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('45.28');
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
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
        $this->byName('checkout_address')->submit();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('45.29');
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent('Please select a payment method for your order');
        $this->switchLowOrderFee('off');
    }

    public function testLowOrderFeeWithGVAlmostFullShippingTax()
    {
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byName('checkout_address')->submit();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('45.76');
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent('Please select a payment method for your order');
        $this->switchItemShippingTax('off');
        $this->switchLowOrderFee('off');
    }

    public function testLowOrderFeeWithGVFullShippingTax()
    {
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byName('checkout_address')->submit();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('50.77');
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('39.99'); //net price
        $this->assertTextPresent('$2.50'); //shippimg
        $this->assertTextPresent('$3.28'); //tax
        $this->assertTextPresent('$5.00'); //loworder fee
        $this->assertTextPresent('-$50.77'); //gift certificates
        $this->assertTextPresent('$0.00'); //total
        $this->byId('btn_submit')->click();
        $this->switchItemShippingTax('off');
        $this->switchLowOrderFee('off');
    }

    public function testLowOrderFeeWithGVFullShippingTaxInclusive()
    {
        $this->switchToTaxInclusive();
        $this->switchLowOrderFee('on');
        $this->switchItemShippingTax('on');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byName('checkout_address')->submit();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('50.77');
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('42.79'); //net price
        $this->assertTextPresent('$2.98'); //shippimg
        $this->assertTextPresent('$3.28'); //tax
        $this->assertTextPresent('$5.00'); //loworder fee
        $this->assertTextPresent('-$50.77'); //gift certificates
        $this->assertTextPresent('$0.00'); //total
        $this->byId('btn_submit')->click();
        $this->switchItemShippingTax('off');
        $this->switchLowOrderFee('off');
        $this->switchToTaxNonInclusive();
    }

    public function testLowOrderFeeWithGroupDiscount()
    {
        $this->switchLowOrderFee('on');
        $this->setCustomerGroupDiscount(WEBTEST_DEFAULT_CUSTOMER_EMAIL, 1);
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byName('checkout_address')->submit();
        $this->byId('pmt-cod')->click();
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('39.99'); //net price
        $this->assertTextPresent('$2.50'); //shipping
        $this->assertTextPresent('-$4.00'); //group discount
        $this->assertTextPresent('$2.52'); //tax
        $this->assertTextPresent('$5.00'); //loworder fee
        $this->assertTextPresent('46.01'); //total
        $this->byId('btn_submit')->click();
        $this->setCustomerGroupDiscount(WEBTEST_DEFAULT_CUSTOMER_EMAIL, 0);
        $this->switchLowOrderFee('off');
    }

    public function testLowOrderFeeWithGroupDiscountGVFull()
    {
        $this->switchLowOrderFee('on');
        $this->setCustomerGroupDiscount(WEBTEST_DEFAULT_CUSTOMER_EMAIL, 1);
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byName('checkout_address')->submit();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('45.29');
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent('Please select a payment method for your order');
        $this->setCustomerGroupDiscount(WEBTEST_DEFAULT_CUSTOMER_EMAIL, 0);
        $this->switchLowOrderFee('off');
    }

    public function testLowOrderFeeWithCouponGVFull()
    {
        $this->switchLowOrderFee('on');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->createCoupon('test10percent');
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byName('checkout_address')->submit();
        $this->byName('cot_gv')->clear();
        $this->byName('cot_gv')->value('46.01');
        $this->byName('dc_redeem_code')->value('test10percent');
        $this->byId('pmt-cod')->click();
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('39.99'); //net price
        $this->assertTextPresent('$2.50'); //shipping
        $this->assertTextPresent('-$4.00'); //coupon discount
        $this->assertTextPresent('$2.52'); //tax
        $this->assertTextPresent('$5.00'); //loworder fee
        $this->assertTextPresent('-$46.01'); //gv
        $this->byId('btn_submit')->click();
        $this->switchLowOrderFee('off');
    }
}
