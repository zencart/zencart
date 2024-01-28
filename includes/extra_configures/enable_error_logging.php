<?php
/**
 * Debug Logging Configuration
 *
 * Sometimes it is difficult to debug PHP background activities, especially when most information cannot be safely output to the screen.
 * However, using the PHP error logging facility we can store all PHP errors to a file, and then review separately.
 * Zen Cart's debug details are stored at: /logs/myDEBUG-yyyymmdd-hhiiss-xxxxx.log
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 27 Modified in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    exit('Invalid Access');
}

$pages_to_debug = [];
/**
 * Specify the pages you wish to enable debugging for (ie: main_page=xxxxxxxx)
 * Using '*' will cause all pages to be enabled
 */
$pages_to_debug[] = '*';
//   $pages_to_debug[] = '';
//   $pages_to_debug[] = '';



/**
 * Error reporting level to log
 * Default: E_ALL & ~E_NOTICE
 */
$errors_to_log = E_ALL & ~E_NOTICE;


///// DO NOT EDIT BELOW THIS LINE /////
// This passes the updated settings above into the error handling configuration to override the defaults
zen_enable_error_logging($pages_to_debug, $errors_to_log);

