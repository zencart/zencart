<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */

$dbCharset = array( array('id' => 'utf8', 'text' => TEXT_DATABASE_SETUP_CHARSET_OPTION_UTF8),
                    array('id' => 'latin1', 'text' => TEXT_DATABASE_SETUP_CHARSET_OPTION_LATIN1));
$dbCharsetOptions = zen_get_select_options($dbCharset, isset($db_charset) ? $db_charset : 'utf8');
$sqlCacheType = array(array('id' => 'none', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_NONE),
                      array('id' => 'file', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_FILE),
                      array('id' => 'database', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_DATABASE));
$sqlCacheTypeOptions = zen_get_select_options($sqlCacheType, isset($sql_cache_method) ? $sql_cache_method : '');
$db_host = isset($db_host) ? $db_host : 'localhost';
$db_name = isset($db_name) ? $db_name : 'zencart';
if (defined('DEVELOPER_MODE') && DEVELOPER_MODE === true) {
  $db_user = (isset($db_user)) ? $db_user : 'zencart';
  $db_password = (isset($db_password)) ? $db_password : 'zencart';
}
$db_prefix = ($db_prefix) ?: '';

// attempt to intelligently manage user-adjusted subdirectory values if they are different from detected defaults
if ($_POST['http_server_catalog'] != $_POST['detected_http_server_catalog']) $_POST['dir_ws_http_catalog'] = rtrim(str_replace($_POST['http_server_catalog'], '', $_POST['http_url_catalog']), '/') .'/';
if ($_POST['https_server_catalog'] != $_POST['detected_https_server_catalog']) $_POST['dir_ws_https_catalog'] = rtrim(str_replace($_POST['https_server_catalog'], '', $_POST['https_url_catalog']), '/') .'/';

