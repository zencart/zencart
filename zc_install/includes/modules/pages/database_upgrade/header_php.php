<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */

$systemChecker = new systemChecker();
$dbVersion = $systemChecker->findCurrentDbVersion();
logDetails($dbVersion, 'Version detected in database_upgrade/header_php.php');

$versionArray = [];
$versionArray[] = '1.2.6';
$versionArray[] = '1.2.7';
$versionArray[] = '1.3.0';
$versionArray[] = '1.3.5';
$versionArray[] = '1.3.6';
$versionArray[] = '1.3.7';
$versionArray[] = '1.3.8';
$versionArray[] = '1.3.9';
$versionArray[] = '1.5.0';
$versionArray[] = '1.5.1';
$versionArray[] = '1.5.2';
$versionArray[] = '1.5.3';
$versionArray[] = '1.5.4';
$versionArray[] = '1.5.5';
$versionArray[] = '1.5.6';
$versionArray[] = '1.5.7';
$versionArray[] = '1.5.8';
$versionArray[] = '2.0.0';
$versionArray[] = '2.1.0';

//print_r($versionArray);
$key = array_search($dbVersion, $versionArray, true);
$newArray = array_slice($versionArray, $key + 1);
//print_r($newArray);


// add current IP to the view-in-maintenance-mode list
$systemChecker->updateAdminIpList();
