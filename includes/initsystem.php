<?php
/**
 * initsystem.php
 *
 * loads and interprets the autoloader files
 *
 * @package initSystem
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Fri Jul 6 11:57:44 2012 -0400 Modified in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$base_dir = DIR_WS_INCLUDES . 'auto_loaders/';
if (file_exists(DIR_WS_INCLUDES . 'auto_loaders/overrides/' . $loader_file)) {
  $base_dir = DIR_WS_INCLUDES . 'auto_loaders/overrides/';
}
/**
 * load the default application_top autoloader file.
 */
if (file_exists($base_dir . $loader_file)) include($base_dir . $loader_file);
if ($loader_dir = dir(DIR_WS_INCLUDES . 'auto_loaders')) {
  while ($loader_file = $loader_dir->read()) {
    $matchPattern = '/^' . $loaderPrefix . '\./';
    if ((preg_match($matchPattern, $loader_file) > 0) && (preg_match('~^[^\._].*\.php$~i', $loader_file) > 0)) {
      if ($loader_file != $loaderPrefix . '.core.php') {
        $base_dir = DIR_WS_INCLUDES . 'auto_loaders/';
        if (file_exists(DIR_WS_INCLUDES . 'auto_loaders/overrides/' . $loader_file)) {
          $base_dir = DIR_WS_INCLUDES . 'auto_loaders/overrides/';
        }
        /**
         * load the application_top autoloader files.
         */
        include($base_dir . $loader_file);
      }
    }
  }
  $loader_dir->close();
}
