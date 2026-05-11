<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

require_once __DIR__ . '/Support/configs/runtime_config.php';
require_once __DIR__ . '/Support/TestConfigResolver.php';

$catalogRoot = getenv('ZC_TEST_RUNTIME_ROOT');
if (!is_string($catalogRoot) || $catalogRoot === '') {
    $catalogRoot = zc_test_config_catalog_path();
}

$databaseBase = getenv('ZC_TEST_RUNTIME_DB_BASE');
if (!is_string($databaseBase) || $databaseBase === '') {
    $databaseBase = getenv('ZC_TEST_DB_BASE_NAME');
}
if (!is_string($databaseBase) || $databaseBase === '') {
    $databaseBase = 'db_testing';
}

$pluginName = getenv('ZC_TEST_RUNTIME_PLUGIN');
if (!is_string($pluginName) || $pluginName === '') {
    $pluginName = 'ExamplePlugin';
}

$workerToken = zc_test_config_worker_token();
$configBasePath = __DIR__ . '/Support/configs/';
$shellUser = \Tests\Support\TestConfigResolver::detectShellUser();
$mainConfigProfile = \Tests\Support\TestConfigResolver::resolveConfigProfile('main', $configBasePath);
$storeConfigProfile = \Tests\Support\TestConfigResolver::resolveConfigProfile('store', $configBasePath);
$adminConfigProfile = \Tests\Support\TestConfigResolver::resolveConfigProfile('admin', $configBasePath);
$mainConfigPath = \Tests\Support\TestConfigResolver::resolveConfigPath('main', $configBasePath);
$storeConfigPath = \Tests\Support\TestConfigResolver::resolveConfigPath('store', $configBasePath);
$adminConfigPath = \Tests\Support\TestConfigResolver::resolveConfigPath('admin', $configBasePath);

echo "Worker Runtime Description\n\n";
echo 'Catalog root: ' . rtrim($catalogRoot, '/') . "/\n";
echo 'Detected shell user: ' . $shellUser . "\n";
echo 'Main config profile: ' . $mainConfigProfile . "\n";
echo 'Main config: ' . $mainConfigPath . "\n";
echo 'Store config profile: ' . $storeConfigProfile . "\n";
echo 'Store config: ' . $storeConfigPath . "\n";
echo 'Admin config profile: ' . $adminConfigProfile . "\n";
echo 'Admin config: ' . $adminConfigPath . "\n";
echo 'Worker token: ' . ($workerToken ?? '(none)') . "\n";
echo 'Database: ' . zc_test_config_database_name($databaseBase) . "\n";
echo 'Progress file: ' . zc_test_config_progress_file($catalogRoot) . "\n";
echo 'Log directory: ' . zc_test_config_log_directory($catalogRoot) . "\n";
echo 'Store artifacts: ' . zc_test_config_artifact_directory($catalogRoot, 'store') . "\n";
echo 'Admin artifacts: ' . zc_test_config_artifact_directory($catalogRoot, 'admin') . "\n";
echo 'Plugin directory: ' . zc_test_config_plugin_directory($catalogRoot, $pluginName) . "\n";
