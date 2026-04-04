<?php

/**
 * ajaxTestSystemSetup.php
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

if (empty($_POST['agreeLicense']) || $_POST['agreeLicense'] !== 'agree') {
    $error = true;
    $errorList['agreeLicense'] = TEXT_FORM_VALIDATION_AGREE_LICENSE;
}

//physical path tests
if (!file_exists($_POST['physical_path'] . '/includes/vers' . 'ion.php')) {
    $error = true;
    $errorList['physical_path'] = TEXT_SYSTEM_SETUP_ERROR_CATALOG_PHYSICAL_PATH;
}

echo json_encode(['error' => $error, 'errorList' => $errorList]);
