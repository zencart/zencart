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
    $dir_ws_http_catalog,
] = getDetectedURIs();

$db_type = 'mysql';
