<?php
/**
 * File contains admin tests that require SSL
 *
 * NOTE. These should always be the last tests run
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testAdminSSLMode.php 19151 2011-07-18 19:37:34Z wilt $
 */
/**
 *
 * @package tests
 */
class testAdminSSLMode extends zcCommonTestResources
{
  function testRemoveGroupDiscountsAdmin()
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

  public function testSwitchAdminSSL()
  {
    $this->createAdminSSLOverride();
    $this->createCatalogSSLOverride();
    $this->open('https://' . DIR_WS_ADMIN);
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->assertTextPresent('Admin Login - Password Expired');
    $this->type('admin_name', WEBTEST_ADMIN_NAME_INSTALL);
    $this->type('old_pwd', WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->type('admin_pass', WEBTEST_ADMIN_PASSWORD_INSTALL_SSL);
    $this->type('admin_pass2', WEBTEST_ADMIN_PASSWORD_INSTALL_SSL);
    $this->clickAndWait("submit");
  }

}
