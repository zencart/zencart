<?php

declare(strict_types=1);

use Tests\Support\TestConfigResolver;

$rootDir = $argv[1] ?? dirname(__DIR__, 2);
$rootDir = rtrim(str_replace('\\', '/', (string) $rootDir), '/');

$configBasePath = $rootDir . '/not_for_release/testFramework/Support/configs/';

require_once $configBasePath . 'runtime_config.php';
require_once $rootDir . '/not_for_release/testFramework/Support/TestConfigResolver.php';

// Resolve the profile's default database name without worker suffixes.
putenv('ZC_TEST_WORKER');
putenv('TEST_TOKEN');

$configPath = TestConfigResolver::resolveConfigPath('store', $configBasePath);
require $configPath;

$dbServer = defined('DB_SERVER') ? (string) DB_SERVER : '';
$dbPort = defined('DB_PORT') ? (string) DB_PORT : '';
$dbUser = defined('DB_SERVER_USERNAME') ? (string) DB_SERVER_USERNAME : '';
$dbPassword = defined('DB_SERVER_PASSWORD') ? (string) DB_SERVER_PASSWORD : '';
$dbName = defined('DB_DATABASE') ? (string) DB_DATABASE : '';

if ($dbPort === '') {
    if (preg_match('/^\[([^\]]+)\]:(\d+)$/', $dbServer, $matches) === 1) {
        $dbServer = $matches[1];
        $dbPort = $matches[2];
    } elseif (preg_match('/^([^:]+):(\d+)$/', $dbServer, $matches) === 1) {
        $dbServer = $matches[1];
        $dbPort = $matches[2];
    }
}

echo $dbServer . PHP_EOL;
echo $dbPort . PHP_EOL;
echo $dbUser . PHP_EOL;
echo $dbPassword . PHP_EOL;
echo $dbName . PHP_EOL;
