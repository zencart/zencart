<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
 */

$dbCharset = array( array('id' => 'utf8', 'text' => TEXT_DATABASE_SETUP_CHARSET_OPTION_UTF8),
                    array('id' => 'latin1', 'text' => TEXT_DATABASE_SETUP_CHARSET_OPTION_LATIN1));
$dbCharsetOptions = zen_get_select_options($dbCharset, isset($db_charset) ? $db_charset : 'utf8');
$sqlCacheType = array(array('id' => 'none', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_NONE),
                      array('id' => 'file', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_FILE),
                      array('id' => 'database', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_DATABASE));
$sqlCacheTypeOptions = zen_get_select_options($sqlCacheType, isset($sql_cache_method) ? $sql_cache_method : '');
$db_name_fallback = $configReader->getDefine('DB_DATABASE');
if (empty($db_name_fallback)) $db_name_fallback = 'zencart';
$db_user_fallback = $configReader->getDefine('DB_SERVER_USERNAME');
if (empty($db_user_fallback)) $db_user_fallback = 'zencart';
$db_password_fallback = $configReader->getDefine('DB_SERVER_PASSWORD');
if (empty($db_password_fallback)) $db_password_fallback = 'zencart';
$db_host = isset($db_host) ? $db_host : 'localhost';
$db_name = isset($db_name) ? $db_name : $db_name_fallback;
if (defined('DEVELOPER_MODE') && DEVELOPER_MODE === true) {
  $db_user = (isset($db_user)) ? $db_user : $db_user_fallback;
  $db_password = (isset($db_password)) ? $db_password : $db_password_fallback;
} else if ($db_user_fallback != 'zencart') {
    $db_user = $db_user_fallback;
    $db_password = $db_password_fallback;
}
$db_prefix = isset($db_prefix) ? $db_prefix : '';

// attempt to intelligently manage user-adjusted subdirectory values if they are different from detected defaults
if ($_POST['http_server_catalog'] != $_POST['detected_http_server_catalog']) $_POST['dir_ws_http_catalog'] = rtrim(str_replace($_POST['http_server_catalog'], '', $_POST['http_url_catalog']), '/') .'/';
if ($_POST['https_server_catalog'] != $_POST['detected_https_server_catalog']) $_POST['dir_ws_https_catalog'] = rtrim(str_replace($_POST['https_server_catalog'], '', $_POST['https_url_catalog']), '/') .'/';

