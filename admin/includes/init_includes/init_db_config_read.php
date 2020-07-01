<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 12 Modified in v1.5.7 $
 */

use App\Model\Configuration;
use App\Model\ProjectVersion;
use App\Model\ProductTypeLayout;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$config = new Configuration;
$authkey = $config->where('configuration_key', 'GLOBAL_AUTH_KEY')->value('configuration_value');
if (empty($authkey)) {
    $hashable = hash('sha256', openssl_random_pseudo_bytes(64));
    $config->update(['configuration_value' => $hashable])->where('configuration_key', 'GLOBAL_AUTH_KEY');
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
