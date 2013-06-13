<?php
/**
 * index.php -- This is the main controller file for the Zen Cart installer
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
  define('IS_ADMIN_FLAG',false);

/* Debugging
 *  'silent': suppress all logging
 *  'screen': display-to-screen and also to the /logs/ folder  (synonyms: TRUE or 'TRUE' or 1)
 *  'file':   log-to-file-only   (synonyms: anything other than above options)
 */
  $mode = 'file';
  if (isset($_GET['v']) && in_array($_GET['v'], array('screen', '1', 'true', 'TRUE'))) $mode = 'screen';
  define('VERBOSE_SYSTEMCHECKER', $mode);

/*
 * Ensure that the include_path can handle relative paths, before we try to load any files
 */
  if (!strstr(ini_get('include_path'), '.')) ini_set('include_path', '.' . PATH_SEPARATOR . ini_get('include_path'));

/*
 * Bypass PHP file caching systems if active, since it interferes with files changed by zc_install
 */
//@TODO

/*
 * Initialize system core components
 */
  define('DIR_FS_INSTALL', realpath(dirname(__FILE__) . '/') . '/');
  define('DIR_FS_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

  require(DIR_FS_INSTALL . 'includes/application_top.php');

  require(DIR_FS_INSTALL . $page_directory . '/header_php.php');
  require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'common/html_header.php');
  require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'common/main_template_vars.php');
  require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'common/tpl_main_page.php');


