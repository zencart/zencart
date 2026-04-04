<?php

use Tests\Services\DatabaseBootstrapper;
use Tests\Support\Database\TestDb;
use Tests\Support\InProcess\InProcessDatabaseSnapshot;
use Tests\Support\TestConfigResolver;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This bootstrap may only be run from CLI.\n");
    exit(1);
}

$context = $argv[1] ?? 'store';

define('ZENCART_TESTFRAMEWORK_RUNNING', true);
define('TESTCWD', realpath(__DIR__ . '/../../') . '/');
define('ROOTCWD', realpath(__DIR__ . '/../../../../') . '/');
define('TEXT_PROGRESS_FINISHED', '');

require ROOTCWD . 'vendor/autoload.php';
require_once TESTCWD . 'Support/configs/runtime_config.php';

$configBasePath = TESTCWD . 'Support/configs/';
$mainConfigs = TestConfigResolver::loadConfig('main', $configBasePath);
TestConfigResolver::loadConfig($context, $configBasePath);

TestDb::resetConnection();
TestDb::pdo();

if (!defined('DIR_FS_ROOT')) {
    define('DIR_FS_ROOT', ROOTCWD);
}
if (!defined('DIR_FS_LOGS')) {
    define('DIR_FS_LOGS', zc_test_config_log_directory(ROOTCWD));
}
if (!is_dir(DIR_FS_LOGS)) {
    mkdir(DIR_FS_LOGS, 0777, true);
}
if (!defined('DEBUG_LOG_FOLDER')) {
    define('DEBUG_LOG_FOLDER', DIR_FS_LOGS);
}
if (!defined('IS_ADMIN_FLAG')) {
    define('IS_ADMIN_FLAG', $context === 'admin');
}

(new InProcessDatabaseSnapshot())->restoreOrCreate(
    static fn () => (new DatabaseBootstrapper())->run($mainConfigs)
);
