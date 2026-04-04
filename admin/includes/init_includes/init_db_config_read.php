<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 Modified in v2.2.1 $
 */

use Zencart\DbRepositories\ConfigurationRepository;
use Zencart\DbRepositories\ProjectVersionRepository;
use Zencart\DbRepositories\ProductTypeLayoutRepository;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

global $db;

$configurationRepository = new ConfigurationRepository($db);
$authKey = $configurationRepository->getByKey('GLOBAL_AUTH_KEY');

if (($authKey['configuration_value'] ?? '') === '') {
    $hashable = hash('sha256', openssl_random_pseudo_bytes(64));
    $configurationRepository->updateValueByKey('GLOBAL_AUTH_KEY', $hashable);
}

$configurationRepository->loadConfigSettings();

// Determine the DATABASE patch level
$projectVersionRepository = new ProjectVersionRepository($db);
$versionInfo = $projectVersionRepository->getByKey('Zen-Cart Database');
define('PROJECT_DB_VERSION_MAJOR', $versionInfo['project_version_major']);
define('PROJECT_DB_VERSION_MINOR', $versionInfo['project_version_minor']);
define('PROJECT_DB_VERSION_PATCH1', $versionInfo['project_version_patch1']);
define('PROJECT_DB_VERSION_PATCH2', $versionInfo['project_version_patch2']);
define('PROJECT_DB_VERSION_PATCH1_SOURCE', $versionInfo['project_version_patch1_source']);
define('PROJECT_DB_VERSION_PATCH2_SOURCE', $versionInfo['project_version_patch2_source']);

$productTypeLayoutRepository = new ProductTypeLayoutRepository($db);
$productTypeLayoutRepository->loadConfigSettings();
