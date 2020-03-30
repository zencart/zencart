<?php

use Zencart\FileSystem\FileSystem;

require_once('includes/application_bootstrap.php');

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'home';
$cmd = ($cmd == 'index') ? 'home' : $cmd;

if (file_exists(basename($cmd . '.php'))) {
    require basename($cmd . '.php');
    exit();
}

//$adminPage = FileSystem::getInstance()->findPluginAdminPage($installedPlugins, $cmd);

if (!isset($adminPage)) {
    die('could not find plugin page');
    exit(1);
}

require($adminPage);