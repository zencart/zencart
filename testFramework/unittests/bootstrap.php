<?php

if (file_exists(__DIR__ . '/../../includes/local/configure.php')) {
  // load any local(user created) configure file.
  include(__DIR__ . '/../../includes/local/configure.php');
} elseif (file_exists(__DIR__ . '/../../includes/configure.php')) {
  // load the default configure
  include(__DIR__ . '/../../includes/configure.php');
} else {
  die("No valid configuration file");
}

$config = realpath(__DIR__ . '/../webtests/config/localconfig.php');
if (!$config) {
  $config = realpath(__DIR__ . '/../webtests/config/localconfig_EXAMPLE.php');
}
include_once $config;

spl_autoload_register(function($className) {
  $filepath = DIR_FS_CATALOG . DIR_WS_CLASSES . str_replace('\\', '/', $className) . '.php';
  if (file_exists($filepath)) {
    include_once $filepath;
  }
});
