<?php
/**
 * File contains Gift Voucher tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testCreateCoupons.php 19030 2011-07-06 12:27:13Z wilt $
 */
/**
 *
 * @package tests
 */
class testGiftVouchers extends zcCommonTestResources
{
  function testPurchaseGiftVoucherQueueOn()
  {
    $temp = $this->switchConfigurationValue('MODULE_ORDER_TOTAL_GV_QUEUE', 'true');
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=document_product_info&cPath=21&products_id=32');
    $this->waitForPageToLoad(10000);
    $this->type('css=input[name=cart_quantity]', '100');
    $this->clickAndWait('css=input[type=image]');
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Gift Certificate *');
    $this->assertTextPresent('glob:*100.00*');
    $this->assertTextPresent('glob:*Amount: $10,000.00*');
    $this->assertTextPresent('glob:*Sub-Total: $10,000.00*');
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*10,000.00*');
    $this->assertTextPresent('glob:*Free Shipping*');
    $this->assertTextPresent('glob:*Payment Information*');
    //    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*10,000.00*');
    $this->assertTextPresent('glob:*Free Shipping*');
    $this->clickAndWait('btn_submit');
    $this->waitForPageToLoad(30000);
    
    $this->switchConfigurationValue('MODULE_ORDER_TOTAL_GV_QUEUE', $temp);
  }
  function testAdminReleaseGVQueue()
  {
    $this->open('http://' . BASE_URL . 'admin/');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->open('http://' . BASE_URL . 'admin/gv_queue.php');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*10,000.00*');
    $this->open('http://' . BASE_URL . 'admin/gv_queue.php?action=release&gid=1&page=1');
    $this->waitForPageToLoad(10000);
    $this->clickAndWait('css=input[type=image]');
    $this->assertTextPresent('No Gift Certificate to release*');   
  }
  function testPurchaseWithGiftVoucher()
  {
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=document_product_info&cPath=1_9&products_id=3');
    $this->waitForPageToLoad(10000);
    $this->clickAndWait('css=input[type=image]');
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('cot_gv', '100');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*-$47.79*');
  }
  function testPurchaseWithGiftVoucherSEK()
  {
    $temp = $this->switchConfigurationValue('DEFAULT_CURRENCY', 'SEK');    
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=document_product_info&cPath=1_9&products_id=3');
    $this->waitForPageToLoad(10000);
    $this->clickAndWait('css=input[type=image]');
    $this->open('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
    $this->waitForPageToLoad(10000);
    $this->submit('checkout_address');
    $this->waitForPageToLoad(10000);
    $this->type('cot_gv', '20,50');
    $this->click('pmt-moneyorder');
    $this->submit('checkout_payment');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*-SEK20,50*');
    $this->assertTextPresent('glob:*SEK27,29*');
    $this->switchConfigurationValue('DEFAULT_CURRENCY', $temp);    
  }
  function testSendGiftVoucherSEK()
  {
    $temp = $this->switchConfigurationValue('DEFAULT_CURRENCY', 'SEK');    
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);
    $this->open('http://' . BASE_URL . 'index.php?main_page=gv_send');
    $this->waitForPageToLoad(10000);
    $this->type('to-name', 'Tom Bombadil');
    $this->type('email-address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('amount', '20,50');
    $this->type('message-area', 'this is a test message');
    $this->clickAndWait('css=input[type=image]');
    $this->assertTextPresent('glob:*Send Gift Certificate Confirmation*');
    $this->assertTextPresent('glob:*SEK20,50*');
    $this->clickAndWait('css=input[type=image]');
    $this->switchConfigurationValue('DEFAULT_CURRENCY', $temp);        
  }
}
