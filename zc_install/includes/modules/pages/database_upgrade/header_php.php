<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */

$systemChecker = new systemChecker();
$dbVersion = $systemChecker->findCurrentDbVersion();
$versionArray = array();
$versionArray[] = '1.3.7';
$versionArray[] = '1.3.8';
$versionArray[] = '1.3.9';
$versionArray[] = '1.5.0';
$versionArray[] = '1.5.1';
$versionArray[] = '1.5.2';
$versionArray[] = '1.6.0';

//print_r($versionArray);
$key = array_search($dbVersion, $versionArray);
$newArray = array_slice($versionArray, $key + 1);
//print_r($newArray);