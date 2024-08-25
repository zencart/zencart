<?php

/**
 * ajaxLoadUpdatesSql.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2024 Jan 20 Modified in v2.0.0-alpha1 $
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

$file = DIR_FS_INSTALL . 'sql/updates/' . $db_type . '_upgrade_zencart_' . str_replace('.', '', $updateVersion) . '.sql';
$extendedOptions = [
    'doJsonProgressLogging' => true,
    'doJsonProgressLoggingFileName' => zcDatabaseInstaller::$initialProgressMeterFilename,
    'id' => 'main',
    'message' => sprintf(TEXT_UPGRADING_TO_VERSION, $updateVersion),
];
logDetails($file, 'Running upgrade SQL');
$result = $dbInstaller->getConnection();
$errDates = $dbInstaller->runZeroDateSql($options);
$errorUpg = $dbInstaller->parseSqlFile($file, $extendedOptions);
if ($error) {
    echo json_encode(['error' => $error, 'version' => $updateVersion, 'errorList' => $errorList]);
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
        $errorUpg = $dbInstaller->parseSqlFile($file, $extendedOptions);
    }
}

echo json_encode(['error' => $error, 'version' => $updateVersion, 'errorList' => $errorList]);

function sanitize_version($version)
{
    $sanitizedString = preg_replace('/[^a-zA-Z0-9_-]/', '', $version);
    return $sanitizedString;
}
