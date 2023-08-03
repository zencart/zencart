<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jul 07 Modified in v1.5.8-alpha $
 */

use App\Models\Configuration;
use App\Models\ProjectVersion;
use App\Models\ProductTypeLayout;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$config = new Configuration;
$authkey = $config->where('configuration_key', 'GLOBAL_AUTH_KEY')->first();

if ($authkey->configuration_value === '') {
    $hashable = hash('sha256', openssl_random_pseudo_bytes(64));
    $authkey->update(['configuration_value' => $hashable]);
}

$config->loadConfigSettings();

// Determine the DATABASE patch level
$projectVersion = new ProjectVersion;
$versionInfo = $projectVersion->where('project_version_key', 'Zen-Cart Database')->first();
define('PROJECT_DB_VERSION_MAJOR', $versionInfo['project_version_major']);
define('PROJECT_DB_VERSION_MINOR', $versionInfo['project_version_minor']);
define('PROJECT_DB_VERSION_PATCH1', $versionInfo['project_version_patch1']);
define('PROJECT_DB_VERSION_PATCH2', $versionInfo['project_version_patch2']);
define('PROJECT_DB_VERSION_PATCH1_SOURCE', $versionInfo['project_version_patch1_source']);
define('PROJECT_DB_VERSION_PATCH2_SOURCE', $versionInfo['project_version_patch2_source']);

$config = new ProductTypeLayout;
$config->loadConfigSettings();
