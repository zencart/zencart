<?php
/**
 * PHPUnit test bootstrap script
 *
 * @package   tests
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   $Id$
 */

error_reporting(E_ALL | E_STRICT);

/**
 * Load testing framework  (only needed on OLDER versions of PHPUnit)
 * With v3.5 and newer, the following lines can be deleted:
 */
// bypass PHPUnit/Framework warning error (works on edited localhost code ...)
$bypassWarning = TRUE;
// will have to customize Bamboo to do the same if the next line can't be removed
if (file_exists('PHPUnit/Framework.php') && !file_exists('PHPUnit/Autoload.php')) require_once 'PHPUnit/Framework.php';

/**
 * Set up some prerequisites
 */
defined('TESTCWD')          || define('TESTCWD', __DIR__ . '/');
defined('DIR_FS_CATALOG')   || define('DIR_FS_CATALOG', realpath(__DIR__ . '/../../'));
defined('DIR_FS_INCLUDES')  || define('DIR_FS_INCLUDES', DIR_FS_CATALOG . '/includes/');
defined('CWD')              || define('CWD', DIR_FS_CATALOG);
defined('DIR_WS_CLASSES')   || define('DIR_WS_CLASSES', '/includes/classes/');

/**
 * Set include path
 */
if (strpos(@ini_get('include_path'), '.') === false) {
  @ini_set('include_path', '.' . PATH_SEPARATOR . @ini_get('include_path'));
}

/**
 * optional code
 */
if (file_exists(TESTCWD . 'localTestSetup.php')) require_once TESTCWD . 'localTestSetup.php';
