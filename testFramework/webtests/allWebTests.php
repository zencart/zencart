<?php
/**
 * File contains main code for loading tests
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * Load localised test config settings
 */
require_once 'support/CompatibilityTestCase.php';

define('CWD', getcwd());
echo "\n\nCWD: " . CWD;
$commandLineConfig = false;
if (isset($argc) && count($argc) > 0) {
    $configFile = $argv[0];
    if (file_exists($configFile)) {
        require_once($configFile);
        $commandLineConfig = true;
    }
}
if (!$commandLineConfig) {
    echo "\nSeeking config file: " . 'testFramework/config/localconfig_' . $_SERVER['USER'] . '.php' . "\n\n";
    if (isset($_SERVER['TRAVIS']) && $_SERVER['TRAVIS'] == 'true' && file_exists('testFramework/config/localconfig_travis.php')) {
        require_once('testFramework/config/localconfig_travis.php');
    } elseif (isset($_SERVER['USER']) && $_SERVER['USER'] != '' && file_exists('testFramework/config/localconfig_' . $_SERVER['USER'] . '.php')) {
        require_once('testFramework/config/localconfig_' . $_SERVER['USER'] . '.php');
    } elseif (file_exists('testFramework/config/localconfig_main.php')) {
        require_once('testFramework/config/localconfig_main.php');
    } else {
        die('COULD NOT FIND CONFIG FILE');
    }
}

$file_contents = file_get_contents(CWD . '/includes/dist-configure.php');
chmod(CWD . '/admin/includes/configure.php', 0777);
chmod(CWD . '/includes/configure.php', 0777);
$fp = fopen(CWD . '/includes/configure.php', 'w');
if ($fp) {
    fputs($fp, $file_contents);
    fclose($fp);
}
require_once 'support/connector.php';

/**
 * Set up test suite
 *
 */
class allTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zen Cart v1.6 Web Tests');
        $suite->addTestSuite('installerNoErrorsTest');
        $suite->addTestSuite('postInstallCatalogChecksTest');
        $suite->addTestSuite('setUpTaxZonesTest');
        $suite->addTestSuite('createCustomerAccountTest');
        $suite->addTestSuite('giftVoucherPurchasesTest');
        $suite->addTestSuite('createCouponsTest');
        $suite->addTestSuite('useCouponsInCatalogTest');
        $suite->addTestSuite('groupDiscountTest');
        $suite->addTestSuite('loworderfeeTest');

        return $suite;
    }
}
