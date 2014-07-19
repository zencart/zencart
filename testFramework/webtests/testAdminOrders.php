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
class testAdminOrders extends zcCommonTestResources
{
  public function testAdminOrders()
  {
    $this->open('https://' . BASE_URL . 'admin/');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_SSL);
    $this->clickAndWait("submit");
    $this->open('https://' . BASE_URL . 'admin/orders.php?page=1&oID=25&action=edit');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*$0.93*');
    $this->assertTextPresent('glob:*$127.00*');
    $this->assertTextPresent('glob:*$8.31*');
    $this->assertTextPresent('glob:*7%*');
    $this->assertTextPresent('glob:*Check/Money Order*');
    $this->assertTextPresent('glob:*Miami, Florida 33133*');
  }

}
