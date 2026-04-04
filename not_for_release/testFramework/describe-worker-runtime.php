<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

require_once __DIR__ . '/Support/configs/runtime_config.php';

$catalogRoot = getenv('ZC_TEST_RUNTIME_ROOT');
if (!is_string($catalogRoot) || $catalogRoot === '') {
    $catalogRoot = zc_test_config_catalog_path();
}

$databaseBase = getenv('ZC_TEST_RUNTIME_DB_BASE');
if (!is_string($databaseBase) || $databaseBase === '') {
    $databaseBase = 'db_testing';
}

$pluginName = getenv('ZC_TEST_RUNTIME_PLUGIN');
if (!is_string($pluginName) || $pluginName === '') {
    $pluginName = 'ExamplePlugin';
}

$workerToken = zc_test_config_worker_token();

echo "Worker Runtime Description\n\n";
echo 'Catalog root: ' . rtrim($catalogRoot, '/') . "/\n";
echo 'Worker token: ' . ($workerToken ?? '(none)') . "\n";
echo 'Database: ' . zc_test_config_database_name($databaseBase) . "\n";
echo 'Progress file: ' . zc_test_config_progress_file($catalogRoot) . "\n";
echo 'Log directory: ' . zc_test_config_log_directory($catalogRoot) . "\n";
echo 'Store artifacts: ' . zc_test_config_artifact_directory($catalogRoot, 'store') . "\n";
echo 'Admin artifacts: ' . zc_test_config_artifact_directory($catalogRoot, 'admin') . "\n";
echo 'Plugin directory: ' . zc_test_config_plugin_directory($catalogRoot, $pluginName) . "\n";
