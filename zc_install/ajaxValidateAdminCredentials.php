<?php
/**
 * ajaxValidateAdminCredentials.php
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 27 Modified in v2.1.0-alpha2 $
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', __DIR__ . '/');
define('DIR_FS_ROOT', realpath(__DIR__ . '/../') . '/');

require DIR_FS_INSTALL . 'includes/application_top.php';

$error = false;
$upgradeAuthNonce = '';
$systemChecker = new systemChecker();
$adminCandidate = $systemChecker->validateAdminCredentials(
    trim(stripslashes($_POST['admin_user'] ?? '')),
    trim(stripslashes($_POST['admin_password'] ?? ''))
);

if (!is_int($adminCandidate)) {
    $error = !$adminCandidate;
    $adminCandidate = '';
} elseif (!zc_install_start_installer_session()) {
    $error = true;
    $adminCandidate = '';
} else {
    $versionArray = require DIR_FS_INSTALL . 'includes/version_upgrades.php';
    $upgradeAuthNonce = zc_install_create_upgrade_authorization(
        $adminCandidate,
        $systemChecker->findCurrentDbVersion(),
        $versionArray
    );
    if ($upgradeAuthNonce === '') {
        $error = true;
        $adminCandidate = '';
    }
}

echo json_encode(compact('error', 'adminCandidate', 'upgradeAuthNonce'));
