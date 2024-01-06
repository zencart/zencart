<?php
declare(strict_types=1);

/**
 * ajaxLoadUpdatesSql.php
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jul 08 Modified in v1.5.8 $
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', __DIR__ . '/');
define('DIR_FS_ROOT', realpath(__DIR__ . '/../') . '/');

require(DIR_FS_INSTALL . 'includes/application_top.php');

$error = false;
$errorList = [];
$db_type = 'mysql';
$updateList = [
    '1.2.7' => ['required' => '1.2.6'],
    '1.3.0' => ['required' => '1.2.7'],
    '1.3.5' => ['required' => '1.3.0'],
    '1.3.6' => ['required' => '1.3.5'],
    '1.3.7' => ['required' => '1.3.6'],
    '1.3.8' => ['required' => '1.3.7'],
    '1.3.9' => ['required' => '1.3.8'],
    '1.5.0' => ['required' => '1.3.9'],
    '1.5.1' => ['required' => '1.5.0'],
    '1.5.2' => ['required' => '1.5.1'],
    '1.5.3' => ['required' => '1.5.2'],
    '1.5.4' => ['required' => '1.5.3'],
    '1.5.5' => ['required' => '1.5.4'],
    '1.5.6' => ['required' => '1.5.5'],
    '1.5.7' => ['required' => '1.5.6'],
    '1.5.8' => ['required' => '1.5.7'],
    '2.0.0' => ['required' => '1.5.8'],
];

$systemChecker = new systemChecker();
$dbVersion = $systemChecker->findCurrentDbVersion();
$updateVersion = str_replace('version-', '', $_POST['version']);
$updateVersion = str_replace('_', '.', $updateVersion);
$versionInfo = $updateList[$updateVersion];

// $errorList[] = "I have $dbVersion. POST=" . $_POST['version'] . ' which asks for updateVersion=' . $updateVersion . '; therefore versionRequired=' . $versionInfo[required];

if ($versionInfo['required'] !== $dbVersion) {
    $error = true;
    if (empty($versionInfo['required'])) {
        $versionInfo['required'] = '[ ERROR: NOT READY FOR UPGRADES YET. NOTIFY DEV TEAM!] ';
    }
    $errorList[] = sprintf(TEXT_COULD_NOT_UPDATE_BECAUSE_ANOTHER_VERSION_REQUIRED, $updateVersion, $dbVersion, $versionInfo['required']);
}
if ($error) {
    echo json_encode(['error' => $error, 'version' => $_POST['version'], 'errorList' => $errorList]);
    die();
}

require_once(DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');
$file = DIR_FS_INSTALL . 'sql/updates/' . $db_type . '_upgrade_zencart_' . str_replace('.', '', $updateVersion) . '.sql';
$options = $systemChecker->getDbConfigOptions();
$dbInstaller = new zcDatabaseInstaller($options);
$result = $dbInstaller->getConnection();
$errDates = $dbInstaller->runZeroDateSql($options);
$errorUpg = $dbInstaller->parseSqlFile($file);
if ($error) {
    echo json_encode(['error' => $error, 'version' => $_POST['version'], 'errorList' => $errorList]);
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
            'doJsonProgressLoggingFileName' => DEBUG_LOG_FOLDER . '/progress.json',
            'id' => 'main',
            'message' => TEXT_LOADING_PLUGIN_UPGRADES . ' ' . $file,
        ];
        logDetails('processing file ' . $file);
        $errorUpg = $dbInstaller->parseSqlFile($file, $extendedOptions);
    }
}

echo json_encode(['error' => $error, 'version' => $_POST['version'], 'errorList' => $errorList]);
