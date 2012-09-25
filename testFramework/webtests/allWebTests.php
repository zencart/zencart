<?php
/**
 * File contains main code for loading tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: allWebTests.php 19153 2011-07-18 21:16:21Z wilt $
 */
/**
 * @package tests
 */
/**
 * Load localised test config settings
 */
if (isset($_SERVER['USER']) && $_SERVER['USER'] != '' && file_exists('config/localconfig_' . $_SERVER['USER'] . '.php'))
{
  require_once ('config/localconfig_' . $_SERVER['USER'] . '.php');
} elseif (file_exists('config/localconfig_main.php'))
{
  require_once ('config/localconfig_main.php');
} elseif (file_exists('../../not_for_release/config/localconfig_main.php'))
{
  require_once ('../../not_for_release/config/localconfig_main.php');
}
/**
 * Load class files for test suites
 */
require_once 'zcCommonTestResources.php';
require_once 'zcPayPalTestLibrary.php';
require_once 'testInstall.php';
require_once 'testCreateAccount.php';
require_once 'testCreateCoupons.php';
require_once 'testUseCoupons.php';
require_once 'testCouponOrders.php';
require_once 'testAdminOrders.php';
require_once 'testGroupDiscount.php';
require_once 'testSundryCartOrders.php';
require_once 'testAdminSSLMode.php';
require_once 'testTwoFactorAuthenticationHooks.php';
require_once 'testCompoundTaxes.php';

/**
 * Set up test suite
 *
 */
class allTests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite('Zen Cart v1.5 Web Tests');
    $suite->addTestSuite('testInstall');
    $suite->addTestSuite('testCreateAccount');
    $suite->addTestSuite('testCreateCoupons');
    $suite->addTestSuite('testUseCoupons');
    $suite->addTestSuite('testGroupDiscount');
    $suite->addTestSuite('testCouponOrders');
    $suite->addTestSuite('testSundryCartOrders');
    $suite->addTestSuite('testAdminSSLMode');
    $suite->addTestSuite('testTwoFactorAuthenticationHooks');
    $suite->addTestSuite('testCompoundTaxes');
    return $suite;
  }

}
