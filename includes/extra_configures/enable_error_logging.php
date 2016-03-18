<?php
/**
 * Very simple error logging to file
 *
 * Sometimes it is difficult to debug PHP background activities, especially when most information cannot be safely output to the screen.
 * However, using the PHP error logging facility we can store all PHP errors to a file, and then review separately.
 * Using this method, the debug details are stored at: /logs/myDEBUG-999999-00000000.log
 * Credits to @lat9 for adding backtrace functionality
 *
 * @package debug
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Oct 17 20:09:58 2015 -0400 Modified in v1.5.5 $
 */

function zen_debug_error_handler ($errno, $errstr, $errfile, $errline) {
  if (!(error_reporting() && $errno)) {
    return;
  }
  ob_start();
  if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
  } else {
    debug_print_backtrace();
  }
  $backtrace = ob_get_contents();
  ob_end_clean();
  // The following line removes the call to this zen_debug_error_handler function (as it's not relevant)
  $backtrace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $backtrace, 1);  
  error_log('Request URI: ' . $_SERVER['REQUEST_URI'] . ', IP address: ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'not set') . "\n" . $backtrace);
  
  return false;  // Let PHP's built-in error handler do its thing.
}

if (!defined('DIR_FS_LOGS')) {
  $val = realpath(dirname(DIR_FS_SQL_CACHE . '/') . '/logs');
  if (is_dir($val) && is_writable($val)) {
    define('DIR_FS_LOGS', $val);
  }
  else {
    define('DIR_FS_LOGS', DIR_FS_SQL_CACHE);
  }
}
/**
 * Specify the pages you wish to enable debugging for (ie: main_page=xxxxxxxx)
 * Using '*' will cause all pages to be enabled
 */
  $pages_to_debug[] = '*';
//   $pages_to_debug[] = '';
//   $pages_to_debug[] = '';

/**
 * The path where the debug log file will be located
 * Default value is: DIR_FS_LOGS . '/myDEBUG-999999-00000000.log'
 * ... which puts it in the /logs/ folder:   /logs/myDEBUG-999999-00000000.log  (where 999999 is a random number, and 00000000 is the server's timestamp)
 *    (or if you don't have a /logs/ folder, it will use the /cache/ folder instead)
 */
  $debug_logfile_path = DIR_FS_LOGS . '/myDEBUG-' . time() . '-' . mt_rand(1000,999999) . '.log';

/**
 * Error reporting level to log
 * Default: E_ALL ^E_NOTICE
 */
  $errors_to_log = (version_compare(PHP_VERSION, 5.3, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE : version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT : E_ALL & ~E_NOTICE);


///// DO NOT EDIT BELOW THIS LINE /////

//////////////////// DEBUG HANDLING //////////////////////////////////
  if (in_array('*', $pages_to_debug) || in_array($current_page_base, $pages_to_debug)) {
    @ini_set('log_errors', 1);          // store to file
    @ini_set('log_errors_max_len', 0);  // unlimited length of message output
    @ini_set('display_errors', 0);      // do not output errors to screen/browser/client
    @ini_set('error_log', $debug_logfile_path);  // the filename to log errors into
    @ini_set('error_reporting', $errors_to_log ); // log only errors according to defined rules
    set_error_handler('zen_debug_error_handler', $errors_to_log);
  }

  if (defined('IS_CLI') && IS_CLI == 'VERBOSE') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
  }
