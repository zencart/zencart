<?php
/**
 * File contains my account order tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testCouponOrders.php 19103 2011-07-13 18:10:46Z wilt $
 */
/**
 *
 * @package tests
 */
class testCouponOrders extends zcCommonTestResources
{
  public function testCouponOrdersDo()
  {
    $this->open('http://' . BASE_URL . 'index.php?main_page=login');
    $this->waitForPageToLoad(10000);
    $this->type('email_address', WEBTEST_DEFAULT_CUSTOMER_EMAIL);
    $this->type('password', WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
    $this->submit('login');
    $this->waitForPageToLoad(10000);

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=1');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*test10percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=2');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*test100percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=3');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*test10fixed*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=4');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*test10percentrestricted*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=5');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*test10percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=6');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*test100percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=7');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*test10fixed*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=8');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*test10percentrestricted*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=9');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Per Item*');
    $this->assertTextPresent('glob:*test10percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=10');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Per Item*');
    $this->assertTextPresent('glob:*test100percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=11');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Per Item*');
    $this->assertTextPresent('glob:*test10fixed*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=12');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Flat Rate*');
    $this->assertTextPresent('glob:*test10percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=13');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Per Item*');
    $this->assertTextPresent('glob:*test10percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=14');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Per Item*');
    $this->assertTextPresent('glob:*test100percent*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=15');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Per Item*');
    $this->assertTextPresent('glob:*test10fixed*');

    $this->open('http://' . BASE_URL . 'index.php?main_page=account_history_info&order_id=18');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Store Pickup*');
    $this->assertTextPresent('glob:*Group Discount*');

  }

}
