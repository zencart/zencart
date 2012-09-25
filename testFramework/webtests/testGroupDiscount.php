<?php
/**
 * File contains main group discount tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testGroupDiscount.php 19019 2011-07-05 22:09:36Z wilt $
 */
/**
 *
 * @package tests
 */
class testGroupDiscount extends zcCommonTestResources
{
  function testGroupDiscountsAdmin()
  {
    $this->open('http://' . BASE_URL . 'admin/');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->open('http://' . BASE_URL . 'admin/customers.php?page=1&cID=2&action=edit');
    $this->waitForPageToLoad(10000);
    $this->select('customers_group_pricing', 'value=1');
    $this->click("//input[@type='image']");
  }

  function testGroupDiscountsDo()
  {
    $this->switchToTaxNonInclusive();
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Order Confirmation*');
    $this->assertTextPresent('glob:*39.99*');
    //net price
    $this->assertTextPresent('glob:*-$4.00*');
    //group discount
    $this->assertTextPresent('glob:*2.52*');
    //tax
    $this->assertTextPresent('glob:*238.51*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);

    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Order Confirmation*');
    $this->assertTextPresent('glob:*39.99*');
    //net price
    $this->assertTextPresent('glob:*-$4.00*');
    //coupon discount
    $this->assertTextPresent('glob:*-$3.60*');
    //group discount
    $this->assertTextPresent('glob:*2.27*');
    //tax
    $this->assertTextPresent('glob:*234.66*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);

    $this->switchToTaxInclusive();

    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Order Confirmation*');
    $this->assertTextPresent('glob:*42.79*');
    //net price
    $this->assertTextPresent('glob:*-$4.28*');
    //group discount
    $this->assertTextPresent('glob:*2.52*');
    //tax
    $this->assertTextPresent('glob:*238.51*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);

    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-storepickup-storepickup");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('dc_redeem_code', 'test10percent');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Order Confirmation*');
    $this->assertTextPresent('glob:*42.79*');
    //net price
    $this->assertTextPresent('glob:*-$4.28*');
    //coupon discount
    $this->assertTextPresent('glob:*-$3.85*');
    //group discount
    $this->assertTextPresent('glob:*2.27*');
    //tax
    $this->assertTextPresent('glob:*234.66*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);

    $this->switchFlatShippingTax('on');
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-flat-flat");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Order Confirmation*');
    $this->assertTextPresent('glob:*42.79*');
    //net price
    $this->assertTextPresent('glob:*$5.95*');
    //shipping
    $this->assertTextPresent('glob:*-$4.28*');
    //group discount
    $this->assertTextPresent('glob:*3.47*');
    //tax
    $this->assertTextPresent('glob:*44.46*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->switchFlatShippingTax('off');

    $this->switchFlatShippingTax('on');
    $this->switchSplitTaxMode('on');
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-flat-flat");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Order Confirmation*');
    $this->assertTextPresent('glob:*42.79*');
    //net price
    $this->assertTextPresent('glob:*$5.95*');
    //shipping
    $this->assertTextPresent('glob:*-$4.28*');
    //group discount
    $this->assertTextPresent('glob:*2.52*');
    //tax product
    $this->assertTextPresent('glob:*0.95*');
    //tax shipping
    $this->assertTextPresent('glob:*44.46*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->switchFlatShippingTax('off');
    $this->switchSplitTaxMode('off');

    $this->switchToTaxNonInclusive();

  }

}
