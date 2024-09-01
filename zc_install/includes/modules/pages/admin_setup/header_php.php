<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */

// remove any stale progress-meter artifacts
if (file_exists(zcDatabaseInstaller::$initialProgressMeterFilename)) {
    unlink(zcDatabaseInstaller::$initialProgressMeterFilename);
}

$changedDir = (bool)($_POST['changedDir'] ?? false);
$adminDir = $_POST['adminDir'] ?? 'admin';
$adminNewDir = $_POST['adminNewDir'] ?? 'admin';

if (defined('DEVELOPER_MODE') && DEVELOPER_MODE === true) {
    $admin_password = 'developer1';
} else {
    $admin_password = zen_create_PADSS_password();
}

if (isset($_POST['upgrade_mode']) && $_POST['upgrade_mode'] === 'yes') {
    $isUpgrade = true;
} elseif (isset($_POST['http_server_catalog'])) {
    $isUpgrade = false;
    require DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileWriter.php';
    $result = new zcConfigureFileWriter($_POST);

    $errors = $result->errors;
}
