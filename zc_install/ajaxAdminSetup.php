<?php

/**
 * ajaxAdminSetup.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', __DIR__ . '/');
define('DIR_FS_ROOT', realpath(__DIR__ . '/../') . '/');

require DIR_FS_INSTALL . 'includes/application_top.php';

$error = false;
$errorList = [];
$response = [];

$requestMode = zc_install_admin_setup_request_mode($_POST);
$errorList = zc_install_validate_admin_setup_request($_POST);
if (!empty($errorList)) {
    echo json_encode([
        'error' => true,
        'errorList' => $errorList,
    ]);
    die();
}

if ($requestMode === ZC_INSTALL_ADMIN_SETUP_MODE_ADMIN_USER) {
    echo json_encode(['error' => false]);
    die();
}

// prepare directory-related responses
$adminDir = zc_install_normalize_admin_directory($_POST['adminDir']) ?? 'admin';
$wordlist = file(DIR_FS_INSTALL . 'includes/wordlist.csv');
$max = count($wordlist) - 1;
$word1 = trim($wordlist[zen_pwd_rand(0, $max)]);
$pos = zen_pwd_rand(0, 4);
$word1[$pos] = strtoupper($word1[$pos]);
$word3 = trim($wordlist[zen_pwd_rand(0, $max)]);
$pos = zen_pwd_rand(0, 4);
$word3[$pos] = strtoupper($word3[$pos]);
$word2 = zen_create_random_value(3, 'chars');
$adminNewDir = $adminDir;
$result = false;
if ($adminDir === 'admin' && (!defined('DEVELOPER_MODE') || DEVELOPER_MODE === false)) {
    $adminNewDir = $word1 . '-' . $word2 . '-' . $word3;
    $result = @rename(DIR_FS_ROOT . $adminDir, DIR_FS_ROOT . $adminNewDir);
    if ($result === false) {
        $adminNewDir = $adminDir;
    } else {
        $adminDir = $adminNewDir;
    }
}
$response['error'] = false;
$response['changedDir'] = (int)$result;
$response['adminNewDir'] = $adminNewDir;
$response['adminDir'] = $adminDir;

echo json_encode($response);
