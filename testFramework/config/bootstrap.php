<?php

ini_set('error_reporting', E_ALL);
$vendorUrl =  __DIR__ . '/../../vendor/autoload.php';
if (getenv('HABITAT') == 'true') {
    $vendorUrl =  'vendor/autoload.php';
}

$loader = require $vendorUrl;
if (!isset($loader)) {
    throw new RuntimeException('vendor/autoload.php could not be found.');
}