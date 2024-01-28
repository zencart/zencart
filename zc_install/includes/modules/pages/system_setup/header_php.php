<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */
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
] = getDetectedURIs();

$db_type = 'mysql';

$enableSslCatalog = '';
if ($request_type === 'SSL') {
    $enableSslCatalog = 'checked = checked';
}
