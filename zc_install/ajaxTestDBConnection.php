<?php
/**
 * ajaxTestDBConnection.php
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', __DIR__ . '/');
define('DIR_FS_ROOT', realpath(__DIR__ . '/../') . '/');

require DIR_FS_INSTALL . 'includes/application_top.php';

$systemChecker = new systemChecker();

$error = true;
$errorList = [];
if (isset($_POST['db_name'])) {
    zcRegistry::setValue('db_host', $_POST['db_host']);
    zcRegistry::setValue('db_user', $_POST['db_user']);
    zcRegistry::setValue('db_password', $_POST['db_password']);
    zcRegistry::setValue('db_name', $_POST['db_name']);
    zcRegistry::setValue('db_charset', $_POST['db_charset']);
    $results = $systemChecker->runTests('database');
    if (count($results) !== 0) {
        $keys = array_keys($results);
        $errorList = $results[$keys[0]];
        $error = true;
    } else {
        $error = false;
    }
}
echo json_encode(['error' => $error, 'errorList' => $errorList]);
