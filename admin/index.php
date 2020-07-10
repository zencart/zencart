<?php

use Zencart\FileSystem\FileSystem;

require_once('includes/application_bootstrap.php');

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'home';
$cmd = ($cmd == 'index') ? 'home' : $cmd;

if (file_exists(basename($cmd . '.php'))) {
    require basename($cmd . '.php');
    exit();
}

$adminPage = FileSystem::getInstance()->findPluginAdminPage($installedPlugins, $cmd);

if (!isset($adminPage)) {
    require 'includes/application_top.php';
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
    exit(0);
}

require($adminPage);