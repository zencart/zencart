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
$systemChecker = new systemChecker();
$adminCandidate = $systemChecker->validateAdminCredentials(
    trim(stripslashes($_POST['admin_user'] ?? '')),
    trim(stripslashes($_POST['admin_password'] ?? ''))
);

if (!is_int($adminCandidate)) {
    $error = !$adminCandidate;
    $adminCandidate = '';
}

echo json_encode(compact('error', 'adminCandidate'));
