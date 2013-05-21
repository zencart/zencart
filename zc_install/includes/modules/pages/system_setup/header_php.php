<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
$adminDir = (isset($_POST['adminDir'])) ? zen_output_string_protected($_POST['adminDir']) : 'admin';
$documentRoot = zen_get_document_root();
$httpServer = zen_get_http_server();
$request_type = (((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1'))) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_BY']) && strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL') !== false) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') !== false || strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), str_replace('https://', '', HTTPS_SERVER)) !== false)) ||
                 (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:') ||
                 (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] == '1' || strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on')) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'ssl' || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')) ||
                 (isset($_SERVER['HTTP_SSLSESSIONID']) && $_SERVER['HTTP_SSLSESSIONID'] != '') ||
                 (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) ? 'SSL' : 'NONSSL';

$adminServer = ($request_type == 'SSL') ? 'https://' : 'http://';
$adminServer .= $httpServer;
$adminUrl = $adminServer . $_SERVER['SCRIPT_NAME'];
$adminUrl = substr($adminUrl, 0, strpos($adminUrl, '/zc_install')) . '/' . $adminDir;
$catalogHttpServer = 'http://' . $httpServer;
$catalogHttpUrl = 'http://' . $httpServer  . $_SERVER['SCRIPT_NAME'];
$catalogHttpUrl = substr($catalogHttpUrl, 0, strpos($catalogHttpUrl, '/zc_install'));
$catalogHttpsServer = 'https://' . $httpServer;
$catalogHttpsUrl = 'https://' . $httpServer  . $_SERVER['SCRIPT_NAME'];
$catalogHttpsUrl = substr($catalogHttpsUrl, 0, strpos($catalogHttpsUrl, '/zc_install'));
$adminPhysicalPath = $documentRoot . '/' . $adminDir;
$virtual_path = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$dir_ws_http_catalog = str_replace($catalogHttpServer, '', $catalogHttpUrl) .'/';
$dir_ws_https_catalog = str_replace($catalogHttpsServer, '', $catalogHttpsUrl) . '/';
$db_type = 'mysql';
