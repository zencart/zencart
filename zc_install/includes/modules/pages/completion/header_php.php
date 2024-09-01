<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */

// remove any stale progress-meter artifacts
if (file_exists(zcDatabaseInstaller::$initialProgressMeterFilename)) {
    unlink(zcDatabaseInstaller::$initialProgressMeterFilename);
}

$isUpgrade = false;
$adminLink = $catalogLink = '#';
$adminServer = $_POST['http_server_admin'] ?? '';
$catalogHttpServer = $_POST['http_server_catalog'] ?? '';
$dir_ws_http_catalog = $_POST['dir_ws_http_catalog'] ?? '';
$adminDir = $_POST['admin_directory'] ?? '';
if (!isset($_POST['admin_directory']) || !file_exists(DIR_FS_ROOT . $_POST['admin_directory'])) {
    $systemChecker = new systemChecker($adminDir);
    $adminDirectoryList = systemChecker::getAdminDirectoryList();
// die('admin list:<pre>'.print_r($adminDirectoryList, TRUE));
    if (count($adminDirectoryList) === 1) {
        $adminDir = $adminDirectoryList[0];
    }
    [
        $adminDir,
        $documentRoot,
        $adminServer,
        $catalogHttpServer,
        $catalogHttpUrl,
        $catalogHttpsServer,
        $catalogHttpsUrl,
        $dir_ws_http_catalog,
        $dir_ws_https_catalog,
    ] = getDetectedURIs($adminDir);
}
$adminLink = zen_output_string_protected($adminServer) . zen_output_string_protected($dir_ws_http_catalog) . zen_output_string_protected($adminDir);
$catalogLink = zen_output_string_protected($catalogHttpServer) . zen_output_string_protected($dir_ws_http_catalog);

if (isset($_POST['upgrade_mode']) && $_POST['upgrade_mode'] === 'yes') {
    $isUpgrade = true;
}
// only do the next step if there was real POST data, else bad info may be written to database
elseif (isset($_POST['http_server_admin']) && $_POST['http_server_admin'] !== '') {
    $isUpgrade = false;
    $options = $_POST;
    $options['dieOnErrors'] = true;
    $dbInstaller = new zcDatabaseInstaller($options);
    $result = $dbInstaller->getConnection();
    $extendedOptions = [];
    $dbInstaller->doCompletion($options);
}

// Update Nginx Conf Template
$ngx_temp = trim($dir_ws_http_catalog, "/");
$ngx_store = ($ngx_temp === "") ? "" : "/" . $ngx_temp;
$ngx_slash = ($ngx_temp === "") ? "/" : $ngx_store;
$ngx_admin = $ngx_store . '/' . trim($adminDir, "/");

$ngx_array = [
    "%%admin_folder%%" => $ngx_admin,
    "%%store_folder%%" => $ngx_store,
    "%%slash_folder%%" => $ngx_slash,
];

$ngx_input_file = "includes/nginx_conf/ngx_server_template.txt";
$ngx_output_file = "includes/nginx_conf/zencart_ngx_server.conf";
$fh = fopen($ngx_input_file, "r");
$ngx_content = fread($fh, filesize($ngx_input_file));
fclose($fh);
foreach ($ngx_array as $ngx_placeholder => $ngx_string) {
    $ngx_content = str_replace($ngx_placeholder, $ngx_string, $ngx_content);
}
$fh = fopen($ngx_output_file, "w");
fwrite($fh, $ngx_content);
fclose($fh);
