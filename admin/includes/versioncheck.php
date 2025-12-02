<?php
/**
 * Check if new versions available via the Zen Cart ping server
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Dec 1 New in v2.2.0 $
 *
 * @since ZC v1.2.0
 * Note from v1.2.0 to 2.1.0 this code was formerly in /admin/includes/header.php
 * but has been moved here to separate version-checking logic from page-header logic,
 * allowing it to be placed elsewhere, so that any server delays do not impact page load time.
 */

// check if version-check has been specifically requested via GET param
$version_check_requested = isset($_GET['vcheck']) && $_GET['vcheck'] !== '';

// ignore version-check if INI file setting has been set
$version_from_ini = '';
$version_ini_sysinfo = '';
$version_ini_index_sysinfo = '';
if (!isset($version_check_sysinfo)) {
    $version_check_sysinfo = false;
}
if (!isset($version_check_index)) {
    $version_check_index = false;
}

// INI file settings override
$skip_file = DIR_FS_ADMIN . 'includes/local/skip_version_check.ini';
if (file_exists($skip_file) && $lines = @file($skip_file)) {
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), 'version_check=')) {
            $version_from_ini = strtolower(substr(trim(str_replace('version_check=', '', $line)), 0, 3));
        }
        if (str_starts_with(trim($line), 'display_update_link_only_on_sysinfo_page=')) {
            $version_ini_sysinfo = strtolower(trim(str_replace('display_update_link_only_on_sysinfo_page=', '', $line)));
        }
        if (str_starts_with(trim($line), 'display_update_link_on_index_and_sysinfo_page=')) {
            $version_ini_index_sysinfo = strtolower(trim(str_replace('display_update_link_only_on_sysinfo_page=', '', $line)));
        }
    }
}

$doVersionCheck = false;
$versionCheckError = false;
$hasPatches = 0;

// ignore version check if not enabled or if not on main page or sysinfo page
if ((SHOW_VERSION_UPDATE_IN_HEADER === 'true'
        && $version_from_ini !== 'off'
        && ($version_check_sysinfo === true || $version_check_index === true)
        && $zv_db_patch_ok === true)
    || $version_check_requested === true
) {
    $doVersionCheck = true;
    $versionServer = new VersionServer();
    $newinfo = $versionServer->getProjectVersion();
    $new_version = TEXT_VERSION_CHECK_CURRENT; //set to "current" by default

    if (empty($newinfo) || isset($newinfo['error'])) {
        $isCurrent = true;
        $versionCheckError = true;
    } else {
        $isCurrent = $versionServer->isProjectCurrent($newinfo);
    }

    if (!$isCurrent) {
        $new_version = TEXT_VERSION_CHECK_NEW_VER . trim($newinfo['versionMajor']) . '.' . trim($newinfo['versionMinor']) . ' :: ' . $newinfo['versionDetail'];
    }
    if ($isCurrent) {
        $hasPatches = $versionServer->hasProjectPatches($newinfo);
    }

    if ($isCurrent && $hasPatches && $new_version === TEXT_VERSION_CHECK_CURRENT) {
        $new_version = '';
    }

    // Handle patch notices
    if ($isCurrent && $hasPatches !== 2 && $hasPatches) {
        $new_version .= (($new_version !== '') ? '<br>' : '') . '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($newinfo['versionMajor']) . '.' . trim($newinfo['versionMinor']) . ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($newinfo['versionPatch1']) . '] :: ' . $newinfo['versionPatchDetail'] . '</span>';
    }
    if ($isCurrent && $hasPatches > 1) {
        $new_version .= (($new_version !== '') ? '<br>' : '') . '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($newinfo['versionMajor']) . '.' . trim($newinfo['versionMinor']) . ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($newinfo['versionPatch2']) . '] :: ' . $newinfo['versionPatchDetail'] . '</span>';
    }

    // Prepare download link
    if ($new_version !== '' && $new_version !== TEXT_VERSION_CHECK_CURRENT) {
        $new_version .= '<br><a href="' . $newinfo['versionDownloadURI'] . '" rel="noopener" target="_blank"><input type="button" class="btn btn-success" value="' . TEXT_VERSION_CHECK_DOWNLOAD . '"/></a>';
    }
}

// If we are not doing a version check now, or if there was an error doing it, prepare the "check for updated version" button/link
if (!$doVersionCheck || $versionCheckError) {
    $new_version = '';
    if ($versionCheckError) {
        $new_version = ERROR_CONTACTING_PROJECT_VERSION_SERVER . '<br>';
    }

    // display the "check for updated version" button.  The button link should be the current admin page and all GET params.
    $url = zen_href_link(basename($PHP_SELF), zen_get_all_get_params(['vcheck']), 'SSL');
    $url .= (str_contains($url, '?') ? '&amp;' : '?') . 'vcheck=yes';

    if ($zv_db_patch_ok === true || $version_check_sysinfo === true) {
        $new_version .= '<a href="' . $url . '" role="button" class="btn btn-link">' . TEXT_VERSION_CHECK_BUTTON . '</a>';
    }
}

// As generated above, $new_version now contains either an update notice (needed or not needed) with download button, or a link/button to check for updates.
// EXAMPLE USE:
//if ($new_version) {
//    echo $new_version;
//    echo '<br>';
//    echo '(' . TEXT_CURRENT_VER_IS . ' v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . (PROJECT_VERSION_PATCH1 != '' ? 'p' . PROJECT_VERSION_PATCH1 : '') . ')';
//}
//
// In footer.php we place it into a jQuery snippet to insert into the #versionCheckAlert placeholder in the admin header.
