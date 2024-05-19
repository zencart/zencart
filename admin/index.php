<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */

use Zencart\FileSystem\FileSystem;

require_once('includes/application_bootstrap.php');

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'home';
$cmd = ($cmd == 'index') ? 'home' : $cmd;

if (file_exists(basename($cmd . '.php'))) {
    require basename($cmd . '.php');
    exit();
}

$adminPage = (new FileSystem)->findPluginAdminPage($installedPlugins, $cmd);

if (!isset($adminPage)) {
    require 'includes/application_top.php';
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
    exit(0);
}

require($adminPage);
