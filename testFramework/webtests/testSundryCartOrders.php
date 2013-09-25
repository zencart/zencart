<?php
/**
 * File contains tests for sundry cart orders
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testSundryCartOrders.php 19149 2011-07-18 19:16:33Z wilt $
 */
/**
 *
 * @package tests
 */
class testSundryCartOrders extends zcCommonTestResources
{
  function testResetGroupDiscountsAdmin()
  {
    $this->open('http://' . DIR_WS_ADMIN);
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->open('http://' . DIR_WS_ADMIN . 'customers.php?page=1&cID=2&action=edit');
    $this->waitForPageToLoad(10000);
    $this->select('customers_group_pricing', 'value=0');
    $this->click("//input[@type='image']");
  }
  public function testCurrencyRoundingTaxExclusive()
  {
    $this->switchToTaxNonInclusive();
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=document_product_info&cPath=63&products_id=171');
    $this->waitForPageToLoad(10000);
    $this->type('css=input[name=cart_quantity]', '127');
    $this->clickAndWait('css=input[type=image]');
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Sample of Document Product Type*');
    $this->assertTextPresent('glob:*0.93*');
    $this->assertTextPresent('glob:*Amount: $118.11*');
    $this->assertTextPresent('glob:*Sub-Total: $118.11*');
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*118.11*');
    $this->assertTextPresent('glob:*Free Shipping*');
    $this->assertTextPresent('glob:*Payment Information*');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*118.11*');
    $this->assertTextPresent('glob:*8.27*');
    $this->assertTextPresent('glob:*Free Shipping*');
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);

  }
  // public function testCurrencyRoundingTaxInclusive()
  // {
    // $this->switchToTaxInclusive();
    // $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    // $this->waitForPageToLoad(10000);
    // $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    // $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    // $this->submit('login');
    // $this->waitForPageToLoad(10000);
    // $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    // $this->waitForPageToLoad(10000);
    // $this->open('http://' . BASE_URL . 'index.php?main_page=document_product_info&cPath=63&products_id=171');
    // $this->waitForPageToLoad(10000);
    // $this->type('css=input[name=cart_quantity]', '127');
    // $this->clickAndWait('css=input[type=image]');
    // $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart');
    // $this->waitForPageToLoad(10000);
    // $this->assertTextPresent('glob:*Sample of Document Product Type*');
    // $this->assertTextPresent('glob:*$1.00*');
    // $this->assertTextPresent('glob:*Amount: $127.00*');
    // $this->assertTextPresent('glob:*Sub-Total: $127.00*');
    // $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    // $this->waitForPageToLoad(10000);
    // $this->assertTextPresent('glob:*127.00*');
    // $this->assertTextPresent('glob:*Free Shipping*');
    // $this->assertTextPresent('glob:*Payment Information*');
    // $this->submit('checkout_payment');
    // $this->waitForPageToLoad(10000);
    // $this->assertTextPresent('glob:*126.38*');
    // $this->assertTextPresent('glob:*8.27*');
    // $this->assertTextPresent('glob:*Free Shipping*');
    // $this->clickAndWait('btn_submit');
    // $this->waitForPageToLoad(30000);
// 
  // }
  // public function testSpitTaxCheckout()
  // {
    // $this->switchSplitTaxMode('on');
    // $this->switchFlatShippingTax('on');
    // $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    // $this->waitForPageToLoad(10000);
    // $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    // $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    // $this->submit('login');
    // $this->waitForPageToLoad(10000);
    // $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    // $this->waitForPageToLoad(10000);
    // $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    // $this->waitForPageToLoad(10000);
    // $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    // $this->waitForPageToLoad(10000);
    // $this->click("ship-flat-flat");
    // $this->submit('checkout_address');
    // $this->waitForPageToLoad(10000);
    // $this->click('pmt-moneyorder');
    // $this->submit('checkout_payment');
    // $this->waitForPageToLoad(10000);
    // $this->assertTextPresent('glob:*Confirmation*');
    // $this->assertTextPresent('glob:*534.99*'); //net price
    // $this->assertTextPresent('glob:*5.95*'); //shipping
    // $this->assertTextPresent('glob:*35.00'); //tax
    // $this->assertTextPresent('glob:*0.95'); //tax
    // $this->assertTextPresent('glob:*540.94*'); //total
    // $this->clickAndWait('btn_submit');
    // $this->waitForPageToLoad(30000);
    // $this->switchFlatShippingTax('off');
    // $this->switchSplitTaxMode('off');
  // }
// 
  // public function testAddToCartMusicTypeWithoutRedirect()
  // {
    // $this->switchAddToCartRedirect('false');
    // $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    // $this->waitForPageToLoad(10000);
    // $this->open('http://' . BASE_URL . 'index.php?main_page=product_music_info&cPath=62&products_id=166');
    // $this->waitForPageToLoad(10000);
    // $this->type('css=input[name=cart_quantity]', '1');
    // $this->clickAndWait('css=input[type=image]');
    // $this->assertTextPresent('glob:*RTBHUNTER*');
    // $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    // $this->waitForPageToLoad(10000);
    // $this->switchAddToCartRedirect('true');
  // }
}
