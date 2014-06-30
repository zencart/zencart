<?php

ini_set('error_reporting', E_ALL);

$loader = require 'vendor/autoload.php';
if (!isset($loader)) {
    throw new RuntimeException('vendor/autoload.php could not be found.');
}

