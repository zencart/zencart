#!/usr/bin/php
<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currency_cron.php $
 */
if (PHP_SAPI != 'cli' && isset($_SERVER["REMOTE_ADDR"]) && ($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"])){
  echo ' ERROR: Permission denied.';
  exit(1);
};

// define('DB_SOCKET', '/tmp/mysql.sock');

// define('STRICT_ERROR_REPORTING', TRUE); // commented out for normal use
// define('DEBUG_AUTOLOAD', TRUE);         // commented out for normal use

define('IS_CLI', 'VERBOSE'); // options: VERBOSE will cause it to output informational messages. Anything else will suppress status messages other than caught errors.

chdir( __DIR__ );
$loaderPrefix = 'currency_cron';
$_SERVER['REMOTE_ADDR'] = 'cron';
$result = require('includes/application_top.php');
if ($result == FALSE)  die("Error: application_top not found.\nMake sure you have placed the currency_cron.php file in your (renamed) Admin folder.\n\n");
$_SERVER['HTTP_USER_AGENT'] = 'Zen Cart update';
// $_SERVER['REMOTE_ADDR'] = DB_SERVER;
if (function_exists('zen_update_currencies'))
{
  if (PHP_SAPI != 'cli') echo '<br><pre>';
  if (IS_CLI == 'VERBOSE') echo 'Updating currencies... ' . "\n";
  zen_update_currencies(IS_CLI == 'VERBOSE');
  if (IS_CLI == 'VERBOSE') echo 'Done.' . "\n\n";
  exit(0);
} else {
  echo "Error: Function not found: zen_update_currencies().\nMake sure you have placed the currency_cron.php file in your (renamed) Admin folder.\n\n";
  exit(1);
}
