<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */

$dbCharset = array( array('id' => 'utf8', 'text' => TEXT_DATABASE_SETUP_CHARSET_OPTION_UTF8), array('id' => 'latin1', 'text' => TEXT_DATABASE_SETUP_CHARSET_OPTION_LATIN1));
$dbCharsetOptions = zen_get_select_options($dbCharset, isset($db_charset) ? $db_charset : 'utf8');
$sqlCacheType = array(array('id' => '', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_NONE),  array('id' => 'file', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_FILE), array('id' => 'database', 'text' => TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_DATABASE));
$sqlCacheTypeOptions = zen_get_select_options($sqlCacheType, isset($sql_cache_method) ? $sql_cache_method : '');
$db_host = isset($db_host) ? $db_host : 'localhost';
$db_name = isset($db_name) ? $db_name : 'zencart';
$sql_cache_dir = isset($sql_cache_dir) ? $sql_cache_dir : DIR_FS_ROOT . 'cache';
