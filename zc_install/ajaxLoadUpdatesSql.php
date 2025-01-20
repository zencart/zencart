<?php

/**
 * ajaxLoadUpdatesSql.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 01 Modified in v2.1.0 $
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', __DIR__ . '/');
define('DIR_FS_ROOT', realpath(__DIR__ . '/../') . '/');

require DIR_FS_INSTALL . 'includes/application_top.php';

// load $versionArray details:
$versionArray = require DIR_FS_INSTALL . 'includes/version_upgrades.php';

$error = false;
$errorList = [];
$db_type = 'mysql';
$systemChecker = new systemChecker();
$dbVersion = $systemChecker->findCurrentDbVersion();
$postedVersion = sanitize_version($_POST['version']);
$updateVersion = str_replace(['version-', '_'], ['', '.'], $postedVersion);
$versionInfo = $versionArray[$updateVersion];

$batchSize = $_POST['batchSize'] ?? 0;
$batchInstance = $_POST['batchInstance'] ?? 0;

if ($versionInfo['required'] !== $dbVersion) {
    $error = true;
    if (empty($versionInfo['required'])) {
        $versionInfo['required'] = '[ ERROR: NOT READY FOR UPGRADES YET. NOTIFY DEV TEAM!] ';
    }
    $errorList[] = sprintf(TEXT_COULD_NOT_UPDATE_BECAUSE_ANOTHER_VERSION_REQUIRED, $updateVersion, $dbVersion, $versionInfo['required']);
}
if ($error) {
    echo json_encode(['error' => $error, 'version' => $updateVersion, 'errorList' => $errorList]);
    die();
}

$options = $systemChecker->getDbConfigOptions();
$dbInstaller = new zcDatabaseInstaller($options);
$connected = $dbInstaller->getConnection();

// Run zero-date cleanup on first upgrade step only
if ($batchInstance <= 1 || $batchSize <= 1) {
    $extendedOptions = [
        'doJsonProgressLogging' => true,
        'doJsonProgressLoggingFileName' => zcDatabaseInstaller::$initialProgressMeterFilename,
        'id' => 'main',
        'message' => 'Processing zero-date cleanups',
    ];
    $errDates = $dbInstaller->runZeroDateSql($extendedOptions);
    if (is_int($errDates)) {
        echo json_encode(['error' => $errDates, 'version' => 'dates-cleanup', 'errorList' => 'see zcInstall-DEBUG log files']);
        die();
    }
}

$file = DIR_FS_INSTALL . 'sql/updates/' . $db_type . '_upgrade_zencart_' . str_replace('.', '', $updateVersion) . '.sql';
$extendedOptions = [
    'doJsonProgressLogging' => true,
    'doJsonProgressLoggingFileName' => zcDatabaseInstaller::$initialProgressMeterFilename,
    'id' => 'main',
    'message' => sprintf(TEXT_UPGRADING_TO_VERSION, $updateVersion),
];
logDetails($file, 'Running upgrade SQL');
$connected = $dbInstaller->getConnection();
$errorUpg = $dbInstaller->parseSqlFile($file, $extendedOptions);
if (is_int($errorUpg)) {
    $errorList[] = $errorUpg;
    echo json_encode(['error' => $errorUpg, 'version' => $updateVersion, 'errorList' => $errorList]);
    die();
}

// Plugins
$pluginsfolder = DIR_FS_INSTALL . 'sql/plugins/updates/';
// get all *.sql files in alpha order
$sql_files = glob($pluginsfolder . '*.sql');
if ($sql_files !== false) {
    foreach ($sql_files as $file) {
        $extendedOptions = [
            'doJsonProgressLogging' => true,
            'doJsonProgressLoggingFileName' => zcDatabaseInstaller::$initialProgressMeterFilename,
            'id' => 'main',
            'message' => TEXT_LOADING_PLUGIN_UPGRADES . ' ' . $file,
        ];
        logDetails('processing file ' . $file);
        $error = $dbInstaller->parseSqlFile($file, $extendedOptions);
        if (is_int($error)) {
            $errorList[] = $error;
            echo json_encode(['error' => $error, 'version' => substr($file, -30), 'errorList' => $errorList]);
            die();
        }
    }
}

echo json_encode(['error' => $error, 'version' => $updateVersion, 'errorList' => $errorList]);

function sanitize_version($version)
{
    $sanitizedString = preg_replace('/[^a-zA-Z0-9_-]/', '', $version);
    return $sanitizedString;
}
