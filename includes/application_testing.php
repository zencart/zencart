<?php
/**
 * application_testing.php
 * Carry out some actions if we are using test framework
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if  (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] === 'Symfony BrowserKit') {
    define('ZENCART_TESTFRAMEWORK_RUNNING', true);
}

if (!defined('ZENCART_TESTFRAMEWORK_RUNNING')) {
    return;
}
$user = $_SERVER['USER'] ?? $_SERVER['MY_USER'] ?? 'runner';
$prefix = (IS_ADMIN_FLAG == true) ? '..' : '.';
$context = (IS_ADMIN_FLAG == true) ? 'admin' : 'store';
$config = $prefix . '/not_for_release/testFramework/Support/configs/' . $user . '.' . $context . '.configure.php';
if (!file_exists($config)) {
  die($config . ' does not exist');
}
require($config);


