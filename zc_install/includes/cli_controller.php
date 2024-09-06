<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */

if (!file_exists(DIR_FS_INSTALL . 'includes/custom_settings.php')) {
    echo 'Error: could not find the zc_install/includes/custom_settings.php file.' . "\n\n";
    exit(1);
}

require DIR_FS_INSTALL . 'includes/custom_settings.php';
if (!isset($zc_settings) || !is_array($zc_settings)) {
    echo 'Error: $zc_settings array not found in custom_settings.php';
    exit(1);
}
$isUpgrade = false;

$otherConfigErrors = false;
$hasUpgradeErrors = false;
$selectedAdminDir = file_exists(DIR_FS_ROOT . $zc_settings['adminDir']) ? $zc_settings['adminDir'] : 'admin';
$systemChecker = new systemChecker($selectedAdminDir);
$dbVersion = $systemChecker->findCurrentDbVersion();
$currentDbVersion = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
$isCurrentDb = $dbVersion === $currentDbVersion;
$hasSaneConfigFile = $systemChecker->hasSaneConfigFile();
$hasTables = $systemChecker->hasTables();
$hasUpdatedConfigFile = $systemChecker->hasUpdatedConfigFile();
$errorList = $systemChecker->runTests();
[$hasFatalErrors, $listFatalErrors] = $systemChecker->getErrorList();
[$hasWarnErrors, $listWarnErrors] = $systemChecker->getErrorList('WARN');
if (isset($listFatalErrors[0]['methods'])) {
    $res = key($listFatalErrors[0]['methods']);
    if ($res === 'CheckWriteableAdminFile') {
        $otherConfigErrors = true;
    }
}
$adminDirectoryList = systemChecker::getAdminDirectoryList();
$hasMultipleAdmins = false;
$selectedAdminDir = file_exists(DIR_FS_ROOT . $zc_settings['adminDir']) ? $zc_settings['adminDir'] : 'admin';
if (count($adminDirectoryList) > 1) {
    $hasMultipleAdmins = true;
    echo 'Multiple Admin Folders Found:';
    foreach ($adminDirectoryList as $directory) {
        echo $directory;
        if ($directory === $zc_settings['adminDir']) {
            echo ' (selected)';
        }
        echo "\n";
    }
} else {
    $selectedAdminDir = $adminDirectoryList[0];
    echo 'Selected Admin Folder: ' . $selectedAdminDir . "\n";
}
// do auto-detections
[
    $adminDir,
    $documentRoot,
    $adminServer,
    $catalogHttpServer,
    $catalogHttpUrl,
    $catalogHttpsServer,
    $catalogHttpsUrl,
    $dir_ws_http_catalog,
    $dir_ws_https_catalog,
] = getDetectedURIs();
$db_type = 'mysql';
$db_charset = 'utf8mb4';
$db_prefix = '';
$sql_cache_method = 'none'; // 'file', 'database'
$db_host = $zc_settings['db_host'] ?? 'localhost';
$db_name = $zc_settings['db_name'] ?? 'zencart';
$db_user = $zc_settings['db_user'] ?? '';
$db_password = $zc_settings['db_password'] ?? '';

$admin_password = zen_create_PADSS_password();

if (isset($_POST['http_server_catalog'])) {
    require DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileWriter.php';
    $result = new zcConfigureFileWriter($_POST);
}


require DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php';
if ($isUpgrade === false) {
    $options = $_POST;
    $dbInstaller = new zcDatabaseInstaller($options);
    $result = $dbInstaller->getConnection();
    $extendedOptions = [];
    $dbInstaller->doCompletion($options);
}
