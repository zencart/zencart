<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 May 15 Modified in v1.5.6b $
 */
/**
 * File contains just application_top code
 *
 * Initializes common classes & methods. Controlled by an array which describes
 * the elements to be initialised and the order in which that happens.
 *
 * @package admin
 */
require_once('includes/application_bootstrap.php');
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
  require(DIR_FS_CATALOG . 'includes/autoload_func.php');

