<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:
 */

if (!defined('IS_ADMIN_FLAG')) {
    require('includes/application_bootstrap.php');
}
/**
 * Prepare init-system
 */
$autoLoadConfig = array();
if (isset($loaderPrefix)) {
  $loaderPrefix = preg_replace('/[^a-z_]/', '', $loaderPrefix);
} else {
  $loaderPrefix = 'config';
}
$loader_file = $loaderPrefix . '.core.php';
require('includes/initsystem.php');
/**
 * load the autoloader interpreter code.
 */
  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . '/autoload_func.php');
