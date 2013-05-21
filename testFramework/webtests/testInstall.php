<?php
/**
 * File contains zc_install tests and some general preliminary test-environment setup scripts
 *
 * @package tests
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
require_once 'zcCommonTestResources.php';
/**
 *
 * @package tests
 */
class testInstall extends zcCommonTestResources
{
  public function testInstallDo()
  {
    if (file_exists(DIR_FS_ADMIN . 'includes/local/configure.php'))
      unlink(DIR_FS_ADMIN . 'includes/local/configure.php');
    if (file_exists(DIR_FS_CATALOG . 'includes/local/configure.php'))
      unlink(DIR_FS_CATALOG . 'includes/local/configure.php');

    $this->open('http://' . BASE_URL);
    $this->waitForPageToLoad(10000);
    $this->assertTitle('System Setup Required');
    $this->open('http://' . BASE_URL . 'zc_install/');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*System Inspection*');
    $this->clickAndWait('submit');
//    $this->assertTextPresent('glob:*License Confirmation*');
//    $this->click('agree');
//    $this->clickAndWait('submit');
//    $this->assertTextPresent('glob:*System Inspection*');
//    $this->clickAndWait('submit');
//    $this->assertTextPresent('glob:*Database Setup*');
//    $this->type('db_host', DB_HOST);
//    $this->type('db_username', DB_USER);
//    $this->type('db_pass', DB_PASS);
//    $this->type('db_name', DB_DBNAME);
//    $this->type('db_prefix', DB_PREFIX);
//    $this->click('submit');
//    $this->waitForPageToLoad(50000);
//    $this->assertTextPresent('glob:*System Setup*');
//    $this->clickAndWait('submit');
//    $this->assertTextPresent('glob:*Store Setup*');
//    $this->type('store_name', WEBTEST_STORE_NAME);
//    $this->type('store_owner', WEBTEST_STORE_OWNER);
//    $this->type('store_owner_email', WEBTEST_STORE_OWNER_EMAIL);
//    $this->select('store_zone', 'value=18');
//    $this->click('demo_install_yes');
//    $this->clickAndWait('submit');
//    $this->assertTextPresent('glob:*Administrator Account Setup*');
//    $this->type('admin_username', WEBTEST_ADMIN_NAME_INSTALL);
//    $this->type('admin_pass', WEBTEST_ADMIN_PASSWORD_INSTALL);
//    $this->type('admin_pass_confirm', WEBTEST_ADMIN_PASSWORD_INSTALL);
//    $this->type('admin_email', WEBTEST_ADMIN_EMAIL);
//    $this->clickAndWait('submit');
//    $this->assertTextPresent('glob:*Setup Finished*');
  }
}
