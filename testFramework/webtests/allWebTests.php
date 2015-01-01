<?php
/**
 * File contains main code for loading tests
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * @package tests
 */
/**
 * Load localised test config settings
 */
define('CWD', getcwd());
echo "\n\nCWD: " . CWD;
$commandLineConfig = false;
if (isset($argc) &&  count($argc) > 0) {
  $configFile = $argv[0];
  if (file_exists($configFile)) {
    require_once($configFile);
    $commandLineConfig = true;
  }
}
// echo "\n_SERVER:" . print_r($_SERVER, true);
// sleep(5);
if (!$commandLineConfig){
  echo "\nSeeking config file: " . 'testFramework/config/localconfig_' . $_SERVER['USER'] . '.php' . "\n\n";
  if (isset($_SERVER['TRAVIS']) && $_SERVER['TRAVIS'] == 'true' && file_exists('testFramework/config/localconfig_travis.php'))
  {
   require_once ('testFramework/config/localconfig_travis.php');
  }
  elseif (isset($_SERVER['USER']) && $_SERVER['USER'] != '' && file_exists('testFramework/config/localconfig_' . $_SERVER['USER'] . '.php'))
  {
    require_once ('testFramework/config/localconfig_' . $_SERVER['USER'] . '.php');
  } elseif (file_exists('testFramework/config/localconfig_main.php'))
  {
    require_once ('testFramework/config/localconfig_main.php');
  } else
  {
    die('COULD NOT FIND CONFIG FILE');
  }
}
/**
 * Load class files for test suites
 */
require_once 'zcCommonTestResources.php';
require_once 'testInstall.php';
require_once 'testGiftVouchers.php';
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
require_once 'testGiftVouchers.php';

/**
 * Set up test suite
 *
 */
class allTests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite('Zen Cart v1.6 Web Tests');
    $suite->addTestSuite('testInstall');
    $suite->addTestSuite('testCreateAccount');
    $suite->addTestSuite('testCreateCoupons');
    $suite->addTestSuite('testUseCoupons');
    $suite->addTestSuite('testGroupDiscount');
    $suite->addTestSuite('testCouponOrders');
    $suite->addTestSuite('testSundryCartOrders');
    $suite->addTestSuite('testGiftVouchers');
    $suite->addTestSuite('testAdminSSLMode');
    $suite->addTestSuite('testTwoFactorAuthenticationHooks');
    $suite->addTestSuite('testCompoundTaxes');
    return $suite;
  }

}
