<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

/**
 * Class useCouponsInCatalogTest
 */
class useCouponsInCatalogTest extends CommonTestResources
{
    function testNonInclusiveCouponsExcludeShipping()
    {
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-storepickup-storepickup0')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10percent');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('499.99'); //net price
        $this->assertTextPresent('-$50.00'); //coupon discount
        $this->assertTextPresent('31.50'); //tax
        $this->assertTextPresent('481.49'); //total
        $this->byId('btn_submit')->click();

        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-storepickup-storepickup0')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test100percent');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('499.99'); //net price
        $this->assertTextPresent('-$499.99'); //coupon discount
        $this->assertTextPresent('0.00'); //total
        $this->byId('btn_submit')->click();

        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-storepickup-storepickup0')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10fixed');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('499.99'); //net price
        $this->assertTextPresent('-$10.00'); //coupon discount
        $this->assertTextPresent('34.30'); //tax
        $this->assertTextPresent('524.29'); //total
        $this->byId('btn_submit')->click();

        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-storepickup-storepickup0')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10percentrestricted');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('579.97'); //coupon discount
        $this->assertTextPresent('-$8.00'); //coupon discount
        $this->assertTextPresent('40.04'); //tax
        $this->assertTextPresent('612.01'); //total
        $this->byId('btn_submit')->click();

    }

    function testInclusiveCouponsExcludeShipping()
    {
        $this->switchToTaxInclusive();
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10percent');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('534.99'); //net price

        $this->assertTextPresent('-$53.50'); //coupon discount
        $this->assertTextPresent('31.50'); //tax
        $this->assertTextPresent('483.99'); //total
        $this->byId('btn_submit')->click();


        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test100percent');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('534.99'); //net price
        $this->assertTextPresent('-$534.9*'); //coupon discount
        $this->assertTextPresent('2.50'); //total
        $this->byId('btn_submit')->click();

        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10fixed');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('534.99'); //net price
        $this->assertTextPresent('-$10.65'); //coupon discount
        $this->assertTextPresent('$34.35'); //tax
        $this->assertTextPresent('526.84'); //total
        $this->byId('btn_submit')->click();

        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10percentrestricted');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('620.57'); //net price
        $this->assertTextPresent('7.50'); //shippimg
        $this->assertTextPresent('-$8.56'); //coupon
        $this->assertTextPresent('40.04'); //tax
        $this->assertTextPresent('619.51'); //total
        $this->byId('btn_submit')->click();
    }

    function testNonInclusiveCouponsPlusShippingTax()
    {
        $this->switchItemShippingTax('on');
        $this->switchToTaxNonInclusive();
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10percent');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('499.99'); //net price
        $this->assertTextPresent('2.50'); //shipping
        $this->assertTextPresent('-$50.00'); //coupon discount
        $this->assertTextPresent('31.67'); //tax
        $this->assertTextPresent('484.16'); //total
        $this->byId('btn_submit')->click();



        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test100percent');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('499.99'); //net price
        $this->assertTextPresent('-$499.99'); //coupon discount
        $this->assertTextPresent('2.50'); //shipping
        $this->assertTextPresent('0.17'); //tax
        $this->assertTextPresent('2.67'); //total
        $this->byId('btn_submit')->click();


        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10fixed');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('499.99'); //net price
        $this->assertTextPresent('-$10.00'); //coupon discount
        $this->assertTextPresent('34.37'); //tax
        $this->assertTextPresent('2.50'); //shipping
        $this->assertTextPresent('526.96*'); //total
        $this->byId('btn_submit')->click();
    }


    function testMinimumOrderRestrictedCoupon()
    {
        $this->switchItemShippingTax('on');
        $this->switchToTaxNonInclusive();
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=3_10&products_id=11&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10percentrestrictedminimum');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('You must spend');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=3_10&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10percentrestrictedminimum');
        $this->byCss('input[type="image"]')->click();
        $this->byId('btn_submit')->click();
    }


    function testInclusiveCouponsPlusShippingTax()
    {
        $this->switchItemShippingTax('on');
        $this->switchToTaxInclusive();
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10percent');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('Confirmation');
        $this->assertTextPresent('534.99'); //net price
        $this->assertTextPresent('2.68'); //shipping
        $this->assertTextPresent('-$53.50'); //coupon discount
        $this->assertTextPresent('31.67'); //tax
        $this->assertTextPresent('484.16'); //total
        $this->byId('btn_submit')->click();


        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test100percent');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('42.79'); //net price
        $this->assertTextPresent('-$42.79'); //coupon discount
        $this->assertTextPresent('2.68'); //shipping
        $this->assertTextPresent('0.17'); //tax
        $this->assertTextPresent('2.67'); //total
        $this->byId('btn_submit')->click();


        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('test10fixed');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('534.99'); //net price
        $this->assertTextPresent('-$10.65'); //coupon discount
        $this->assertTextPresent('34.52'); //tax
        $this->assertTextPresent('2.68'); //shipping
        $this->assertTextPresent('527.01'); //total
        $this->byId('btn_submit')->click();
    }
    function testFreeShippingCoupon()
    {
        $this->switchItemShippingTax('off');
        $this->switchToTaxNonInclusive();
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('testFreeShipping');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('499.99'); //net price
        $this->assertTextPresent('2.50');//shipping
        $this->assertTextPresent('-$2.50'); //coupon discount
        $this->assertTextPresent('35.00'); //tax
        $this->assertTextPresent('534.99'); //total
        $this->byId('btn_submit')->click();

        $this->switchItemShippingTax('on');
        $this->switchToTaxNonInclusive();
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-item-item')->click();
        $this->byCss('input[type="image"]')->click();
        $this->byName('dc_redeem_code')->value('testFreeShipping');
        $this->byCss('input[type="image"]')->click();
        $this->assertTextPresent('499.99'); //net price
        $this->assertTextPresent('2.50'); //shipping
        $this->assertTextPresent('-$2.50'); //coupon discount
        $this->assertTextPresent('35.00'); //tax
        $this->assertTextPresent('534.99'); //total
        $this->byId('btn_submit')->click();
    }
}
