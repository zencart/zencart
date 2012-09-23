<?php
/**
 * File contains discount coupon tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testUseCoupons.php 19146 2011-07-18 18:38:41Z wilt $
 */
/**
 *
 * @package tests
 */
class testUseCoupons extends zcCommonTestResources
{
  function testNonInclusiveCouponsExcludeShipping()
  {
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*'); //net price
    $this->assertTextPresent('glob:*-$50.00*'); //coupon discount
    $this->assertTextPresent('glob:*31.50*'); //tax
    $this->assertTextPresent('glob:*681.49*'); //total
//    $this->captureEntirePageScreenshot(SCREENSHOT_PATH . 'testNonInclusiveCoupons.png');
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    
    
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test100percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*'); //net price
    $this->assertTextPresent('glob:*-$499.99*'); //coupon discount
    $this->assertTextPresent('glob:*$200.00*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    
    
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10fixed');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*'); //net price
    $this->assertTextPresent('glob:*-$10.00*'); //coupon discount
    $this->assertTextPresent('glob:*$34.30*'); //tax
    $this->assertTextPresent('glob:*724.29*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    
    
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percentrestricted');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*579.97*'); //net price
    $this->assertTextPresent('glob:*-$8.00*'); //coupon discount
    $this->assertTextPresent('glob:*40.04*'); //tax
    $this->assertTextPresent('glob:*812.01*'); //total
    $this->clickAndWait('btn_submit');
  }

  
  function testInclusiveCouponsExcludeShipping()
  {
    $this->switchToTaxInclusive();
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
//    $this->captureEntirePageScreenshot(SCREENSHOT_PATH . 'testInclusiveCoupons.png');
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*534.99*'); //net price
    $this->assertTextPresent('glob:*-$53.50*'); //coupon discount
    $this->assertTextPresent('glob:*31.50*'); //tax
    $this->assertTextPresent('glob:*681.49*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test100percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*534.99*'); //net price
    $this->assertTextPresent('glob:*-$534.99*'); //coupon discount
    $this->assertTextPresent('glob:*$200.00*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10fixed');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*534.99*'); //net price
    $this->assertTextPresent('glob:*-$10.65*'); //coupon discount
    $this->assertTextPresent('glob:*$34.35*'); //tax
    $this->assertTextPresent('glob:*724.34*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percentrestricted');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*620.57*'); //net price
    $this->assertTextPresent('glob:*-$8.56*'); //coupon discount
    $this->assertTextPresent('glob:*40.04*'); //tax
    $this->assertTextPresent('glob:*812.01*'); //total
    $this->clickAndWait('btn_submit');
  }
  function testNonInclusiveCouponsPlusShippingTax()
  {
    $this->switchItemShippingTax('on');
    $this->switchToTaxNonInclusive();
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-item-item");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*'); //net price
    $this->assertTextPresent('glob:*100.00*'); //shipping
    $this->assertTextPresent('glob:*-$50.00*'); //coupon discount
    $this->assertTextPresent('glob:*38.50*'); //tax
    $this->assertTextPresent('glob:*588.49*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-item-item");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test100percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*'); //net price
    $this->assertTextPresent('glob:*-$499.99*'); //coupon discount
    $this->assertTextPresent('glob:*100.00*'); //shipping
    $this->assertTextPresent('glob:*$7.00*'); //tax
    $this->assertTextPresent('glob:*$107.00*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-item-item");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10fixed');
   $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*'); //net price
    $this->assertTextPresent('glob:*-$10.00*'); //coupon discount
    $this->assertTextPresent('glob:*$41.30*'); //tax
    $this->assertTextPresent('glob:*100.00*'); //shipping
    $this->assertTextPresent('glob:*631.29*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
  }
  function testMinimumOrderRestrictedCoupon()
  {
    $this->switchItemShippingTax('on');
    $this->switchToTaxNonInclusive();
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=3_10&products_id=11&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-flat-flat");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percentrestrictedminimum');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
//    $this->captureEntirePageScreenshot(SCREENSHOT_PATH . 'testMinimumOrderRestrictedCoupon1.png');
    $this->assertTextPresent('You must spend at least*');
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=3_10&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percentrestrictedminimum');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
//    $this->captureEntirePageScreenshot(SCREENSHOT_PATH . 'testMinimumOrderRestrictedCoupon2.png');
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
  }
  function testInclusiveCouponsPlusShippingTax()
  {
    $this->switchItemShippingTax('on');
    $this->switchToTaxInclusive();
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->click("ship-item-item");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*534.99*'); //net price
    $this->assertTextPresent('glob:*107.00*'); //shipping
    $this->assertTextPresent('glob:*-$53.50*'); //coupon discount
    $this->assertTextPresent('glob:*38.50*'); //tax
    $this->assertTextPresent('glob:*588.49*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->click("ship-item-item");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test100percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*42.79*'); //net price
    $this->assertTextPresent('glob:*-$42.79*'); //coupon discount
    $this->assertTextPresent('glob:*107.00*'); //shipping
    $this->assertTextPresent('glob:*$7.00*'); //tax
    $this->assertTextPresent('glob:*$107.00*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->click("ship-item-item");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10fixed');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*534.99*'); //net price
    $this->assertTextPresent('glob:*-$10.65*'); //coupon discount
    $this->assertTextPresent('glob:*$41.35*'); //tax
    $this->assertTextPresent('glob:*107.00*'); //shipping
    $this->assertTextPresent('glob:*631.34*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
  }
  function testFreeShippingCoupon()
  {
    $this->switchItemShippingTax('off');
    $this->switchToTaxNonInclusive();
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->click("ship-item-item");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'testFreeShipping');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*'); //net price
    $this->assertTextPresent('glob:*100.00*'); //shipping
    $this->assertTextPresent('glob:*-$100.00*'); //coupon discount
    $this->assertTextPresent('glob:*35.00'); //tax
    $this->assertTextPresent('glob:*534.99*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);

    $this->switchItemShippingTax('on');
    $this->switchToTaxNonInclusive();
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->click("ship-item-item");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'testFreeShipping');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*'); //net price
    $this->assertTextPresent('glob:*100.00*'); //shipping
    $this->assertTextPresent('glob:*-$100.00*'); //coupon discount
    $this->assertTextPresent('glob:*35.00'); //tax
    $this->assertTextPresent('glob:*534.99*'); //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
  }
}