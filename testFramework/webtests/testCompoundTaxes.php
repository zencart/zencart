<?php
/**
 * File contains my account order tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testAdminOrders.php 19154 2011-07-18 21:17:18Z wilt $
 */
/**
 *
 * @package tests
 */
class testCompoundTaxes extends zcCommonTestResources
{
  public function testCompoundTaxCheckoutSplitModeOnDifferentPriority()
  {
    $this->setupCompoundTaxes();
    $this->setTaxPriorityDifferent();
    $this->switchToTaxNonInclusive();
    $this->switchSplitTaxMode('on');
    $this->switchFlatShippingTax('off');
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_CANADA_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_CANADA_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-flat-flat");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*');
    //net price
    $this->assertTextPresent('glob:*5.00*');
    //shipping
    $this->assertTextPresent('glob:*15.00');
    //tax
    $this->assertTextPresent('glob:*41.20');
    //tax
    $this->assertTextPresent('glob:*561.19*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->switchFlatShippingTax('off');
    $this->switchSplitTaxMode('off');
  }

  public function testCompoundTaxCheckoutSplitModeOffDifferentPriority()
  {
    $this->setupCompoundTaxes();
    $this->setTaxPriorityDifferent();
    $this->switchSplitTaxMode('off');
    $this->switchFlatShippingTax('off');
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_CANADA_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_CANADA_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->click("ship-flat-flat");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*');
    //net price
    $this->assertTextPresent('glob:*5.00*');
    //shipping
    $this->assertTextPresent('glob:*56.20');
    //tax
    $this->assertTextPresent('glob:*561.19*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->switchFlatShippingTax('off');
    $this->switchSplitTaxMode('off');
  }

  public function testCompoundTaxCheckoutSplitModeOffSamePriority()
  {
    $this->setupCompoundTaxes();
    $this->setTaxPrioritySame();
    $this->switchSplitTaxMode('off');
    $this->switchFlatShippingTax('off');
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_CANADA_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_CANADA_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-flat-flat");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*');
    //net price
    $this->assertTextPresent('glob:*5.00*');
    //shipping
    $this->assertTextPresent('glob:*55.00');
    //tax
    $this->assertTextPresent('glob:*559.99*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->switchFlatShippingTax('off');
    $this->switchSplitTaxMode('off');
  }

  public function testCompoundTaxCheckoutSplitModeOnSamePriority()
  {
    $this->setupCompoundTaxes();
    $this->setTaxPrioritySame();
    $this->switchSplitTaxMode('on');
    $this->switchFlatShippingTax('off');
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_CANADA_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_CANADA_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=27&action=buy_now');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->click("ship-flat-flat");
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Confirmation*');
    $this->assertTextPresent('glob:*499.99*');
    //net price
    $this->assertTextPresent('glob:*5.00*');
    //shipping
    $this->assertTextPresent('glob:*15.00');
    //tax
    $this->assertTextPresent('glob:*40.00');
    //tax
    $this->assertTextPresent('glob:*559.99*');
    //total
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    $this->switchFlatShippingTax('off');
    $this->switchSplitTaxMode('off');
  }

}
