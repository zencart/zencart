<?php

ini_set('error_reporting', E_ALL);

$loader = require __DIR__ . '/../../vendor/autoload.php';
if (!isset($loader)) {
    throw new RuntimeException('vendor/autoload.php could not be found.');
}

