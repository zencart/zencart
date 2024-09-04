<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 24 Modified in v2.1.0-alpha2 $
 */

$systemChecker = new systemChecker();
$dbVersion = $systemChecker->findCurrentDbVersion();
logDetails($dbVersion ?? 'Unable to detect; perhaps too old?', 'Version detected in database_upgrade/header_php.php');

// load $versionArray details:
$versionArray = require DIR_FS_INSTALL . 'includes/version_upgrades.php';

if (empty($versionArray)) {
    die('ERROR: Cannot find zc_install/includes/version_upgrades.php, or its content is invalid.');
}

$upgradeableVersions = array_keys($versionArray);
$key = array_search($dbVersion, $upgradeableVersions, true);
$newArray = array_slice($upgradeableVersions, $key + 1);

if (empty($dbVersion)) {
    $newArray = [];
}

// add current IP to the view-in-maintenance-mode list
$systemChecker->updateAdminIpList();


// remove any stale progress-meter artifacts
if (file_exists(zcDatabaseInstaller::$initialProgressMeterFilename)) {
    unlink(zcDatabaseInstaller::$initialProgressMeterFilename);
}
