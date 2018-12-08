<?php
/**
 * ajax front controller (admin version)
 *
 * NOTE: "Assumes" that the admin directory is a direct subdirectory off the store's file-system!
 *
 * @package core
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 Fri Oct 26 10:04:06 2018 -0400 New in v1.5.6 $
 */
// -----
// Let the "base" ajax.php processing "know" that this request came from the admin,
// so that the admin version of the application_top.php processing will be loaded.
//
$zc_ajax_base_dir = basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
require '../ajax.php';
