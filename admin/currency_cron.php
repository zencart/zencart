#!/usr/bin/php
<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 23 Modified in v1.5.7 $
 */
// uncomment the following line to disable this script execution in the case of an emergency malfunction when you can't access the server cron settings to kill the scheduled cron job:
// exit(1);

// This is intended to prevent unauthorized execution via a browser
$is_browser = (isset($_SERVER['HTTP_HOST']) || PHP_SAPI != 'cli');
if ($is_browser && isset($_SERVER["REMOTE_ADDR"]) && ($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"])){
  echo ' ERROR: Permission denied.';
  exit(1);
};

// Some servers' PHP configuration doesn't know where to find the mysql socket correctly (evidenced by getting errors about mysqli and mysql.sock, esp when running cron or command-line scripts, such as this one)
// uncomment the following line ONLY if your server's configuration requires it and you don't already have this in your configure.php file
// define('DB_SOCKET', '/tmp/mysql.sock');

// define('STRICT_ERROR_REPORTING', TRUE); // commented out for normal use
// define('DEBUG_AUTOLOAD', TRUE);         // commented out for normal use

define('IS_CLI', 'VERBOSE'); // options: VERBOSE will cause it to output informational messages. NONE or anything else will suppress status messages other than caught errors.

// Set timezone if passed as "TZ=Continent/City" (since often the PHP CLI doesn't know the same timezone as an apache vhost, and thus may not honor the vhost-specific date.timezone setting) (Yes, PHP 5.4+ ignores the TZ environment variable, but this uses it and takes it a step further for forward compatibility)
if (isset($_SERVER["argc"]) && $_SERVER["argc"] > 1) {
  for($i=1;$i<$_SERVER["argc"];$i++) {
    list($key, $val) = explode('=', $_SERVER["argv"][$i]);
    if ($key == 'TZ') {
      putenv($_SERVER["argv"][$i]);
      date_default_timezone_set($val);
    }
    if (in_array($_SERVER["argv"][$i], array('help', '?', '-help', '--help', '-?', '-usage', 'usage'))) {
      echo 'Zen Cart(tm) Currency Updater cron script.' . "\n\n";
      echo 'Usage: Create a cron job on your server, and give it the following command line:' . "\n";
      echo '       php /full/path/to/currency_cron.php' . "\n";
      echo '       php /full/path/to/currency_cron.php TZ=America/Chicago' . "\n";
      echo '       php /full/path/to/currency_cron.php -help' . "\n\n";
      echo "- May optionally add TZ=Continent/City to specify a PHP-recognized timezone \n  if your store/domain is set to a timezone other than the server default.\n";
      echo "- NOTE: Script expects to be found in your store's (renamed) admin folder. \n  Moving it to another folder will break it.\n";
      echo "- Recommend running *infrequently*, as running too often is usually unnecessary.\n  Suggest once or twice per week, or maybe once or twice per day.\n  Hourly is fine, but is rarely necessary.\n";
      echo "\n\n";
      exit(0);
    }
  }
}

// setup
chdir( dirname(__FILE__) );
$loaderPrefix = 'currency_cron';
$_SERVER['REMOTE_ADDR'] = 'cron';
$_SERVER['REQUEST_URI'] = 'cron';
$result = require('includes/application_top.php');
if ($result == FALSE)  die("Error: application_top not found.\nMake sure you have placed the currency_cron.php file in your (renamed) Admin folder.\n\n");
$_SERVER['HTTP_USER_AGENT'] = 'Zen Cart update';

// main execution area
if (function_exists('zen_update_currencies'))
{
  if (IS_CLI == 'VERBOSE' && $is_browser) echo '<br><pre>' . "\n";
  if (IS_CLI == 'VERBOSE') echo 'Updating currencies... ' . "\n";
  zen_update_currencies(IS_CLI == 'VERBOSE');
  if (IS_CLI == 'VERBOSE') echo 'Done.' . "\n\n";
  exit(0); // returns 0 status code, which means successful
} else {
  echo "Error: Function not found: zen_update_currencies().\nMake sure you have placed the currency_cron.php file in your (renamed) Admin folder.\n\n";
  exit(1);
}
