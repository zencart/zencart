<?php
/**
 * ajaxValidateAdminCredentials.php
 * @package Installer
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Tue Sep 11 15:53:41 2018 -0400 Modified in v1.5.6 $
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', __DIR__ . '/');
define('DIR_FS_ROOT', realpath(__DIR__ . '/../') . '/');

require(DIR_FS_INSTALL . 'includes/application_top.php');

$error          = FALSE;
$postParams     = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$systemChecker  = new systemChecker();
$adminCandidate = $systemChecker->validateAdminCredentials(
  trim($postParams['admin_user']),
  trim($postParams['admin_password'])
);

if (!is_int($adminCandidate)) {
  $error = !$adminCandidate;
  $adminCandidate = '';
}

echo json_encode(compact('error', 'adminCandidate'));
