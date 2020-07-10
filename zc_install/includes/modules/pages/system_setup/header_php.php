<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Mon Dec 28 20:10:58 2015 +0000 Modified in v1.5.5 $
 */
list($adminDir, $documentRoot, $adminServer, $catalogHttpServer, $catalogHttpUrl, $catalogHttpsServer, $catalogHttpsUrl, $dir_ws_http_catalog, $dir_ws_https_catalog) = getDetectedURIs();
$db_type = 'mysql';
$enableSslCatalog = '';
if ($request_type == 'SSL') {
    $enableSslCatalog = 'checked = checked';
}
