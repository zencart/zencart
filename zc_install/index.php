<?php
/**
 * index.php -- This is the main controller file for the Zen Cart installer
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2021 Mar 05 Modified in v1.5.8-alpha $
 */

  // Actual version check is more strict; this is just to start the program
  // For true minimum, see includes/systemChecks.yml under checkPhpVersionMin
  if (PHP_VERSION_ID < 70100) { // only checking for 7.1 here as a basic check. zc_install itself doesn't need higher than this at this time
    die('Sorry, requires minimum PHP 7.3'); // the actual zc_install pages have a prettier and more informative explanation of requirements
  }

  define('IS_ADMIN_FLAG',false);

/* Debugging
 *  'silent': suppress all logging
 *  'screen': display-to-screen and also to the /logs/ folder  (synonyms: TRUE or 'TRUE' or 1)
 *  'file':   log-to-file-only   (synonyms: anything other than above options)
 */
  $debug_logging = 'file';

/*
 * Ensure that the include_path can handle relative paths, before we try to load any files
 */
  if (!strstr(ini_get('include_path'), '.')) ini_set('include_path', '.' . PATH_SEPARATOR . ini_get('include_path'));

/*
 * Initialize system core components
 */
  define('DIR_FS_INSTALL', __DIR__ . DIRECTORY_SEPARATOR);
  define('DIR_FS_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);

  require DIR_FS_INSTALL . 'includes/application_top.php';

  if ($controller == 'cli') {
    require DIR_FS_INSTALL . 'includes/cli_controller.php';
  } else {
    require DIR_FS_INSTALL . $page_directory . '/header_php.php';
    require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'common/html_header.php';
    require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'common/main_template_vars.php';
    require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'common/tpl_main_page.php';
  }
