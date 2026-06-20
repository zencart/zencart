<?php
/**
 * application_testing.php
 * Carry out some actions if we are using test framework
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2023 Jul 13 New in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (
    (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] === 'Symfony BrowserKit')
    || getenv('ZENCART_TESTFRAMEWORK_RUNNING') === '1'
) {
    define('ZENCART_TESTFRAMEWORK_RUNNING', true);
}

if (!defined('ZENCART_TESTFRAMEWORK_RUNNING')) {
    return;
}
require_once __DIR__ . '/configs/config_resolver.php';
$configuredUser = getenv('ZENCART_TESTFRAMEWORK_CONFIG_USER') ?: '';
$user = $configuredUser !== '' ? $configuredUser : ($_SERVER['USER'] ?? $_SERVER['MY_USER'] ?? 'runner');
if (isset($_SERVER['IS_DDEV_PROJECT']) || getenv('IS_DDEV_PROJECT')) {
    $user = 'ddev';
}
$prefix = (IS_ADMIN_FLAG === true) ? '..' : '.';
$context = (IS_ADMIN_FLAG === true) ? 'admin' : 'store';
$basePath = $prefix . '/not_for_release/testFramework/Support/configs/';
$candidates = [$user, 'ddev', 'runner'];
$branchFamily = zc_test_framework_detect_branch_family();
echo 'This branch family = ' . ($branchFamily ?? 'none') . PHP_EOL;
$config = zc_test_framework_resolve_config_file($basePath, $candidates, $context);
if ($config === null) {
    $branchHint = $branchFamily === null ? '' : ' for branch family "' . $branchFamily . '"';
    die('No test config file found in ' . $basePath . ' for context "' . $context . '"' . $branchHint);
}
if (!defined('ZC_ADMIN_TWO_FACTOR_AUTHENTICATION_SERVICE')) {
    define('ZC_ADMIN_TWO_FACTOR_AUTHENTICATION_SERVICE', '');
}
require($config);
