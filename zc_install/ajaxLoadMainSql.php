<?php
/**
 * ajaxLoadMainSql.php
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', realpath(dirname(__FILE__) . '/') . '/');
define('DIR_FS_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

require(DIR_FS_INSTALL . 'includes/application_top.php');

$error = FALSE;

$db_type = 'mysql';


require_once(DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');
$options = array('db_host'=>$_POST['db_host'], 'db_user'=>$_POST['db_user'], 'db_password'=>$_POST['db_password'], 'db_name'=>$_POST['db_name'], 'db_charset'=>$_POST['db_charset'], 'db_prefix'=>$_POST['db_prefix'], 'db_type'=>$db_type, 'sql_cache_dir'=>$_POST['sql_cache_dir']);
$dbInstaller = new zcDatabaseInstaller($options);
$result = $dbInstaller->getConnection();
$extendedOptions = array('doJsonProgressLogging'=>TRUE, 'doJsonProgressLoggingFileName'=>DIR_FS_ROOT . 'logs/progress.json', 'id'=>'main', 'message'=>TEXT_CREATING_DATABASE);
$file = DIR_FS_INSTALL . 'sql/install/mysql_zencart.sql';
$error = $dbInstaller->parseSqlFile($file, $extendedOptions);
if (!$error)
{
  if (file_exists(DIR_FS_INSTALL . 'sql/install/mysql_' . $_POST['db_charset'] . '.sql'))
  {
    $extendedOptions = array('doJsonProgressLogging'=>TRUE, 'doJsonProgressLoggingFileName'=>DIR_FS_ROOT . 'logs/progress.json', 'id'=>'main', 'message'=>TEXT_LOADING_CHARSET_SPECIFIC);
    $file = DIR_FS_INSTALL . 'sql/install/mysql_' . $_POST['db_charset'] . '.sql';
    $error = $dbInstaller->parseSqlFile($file, $extendedOptions);
  }
}
if (!$error)
{
  if (isset($_POST['demoData']))
  {
    $extendedOptions = array('doJsonProgressLogging'=>TRUE, 'doJsonProgressLoggingFileName'=>DIR_FS_ROOT . 'logs/progress.json', 'id'=>'main', 'message'=>TEXT_LOADING_DEMO_DATA);
    $file = DIR_FS_INSTALL . 'sql/demo/mysql_demo.sql';
    $error = $dbInstaller->parseSqlFile($file, $extendedOptions);
  }
}
if (!$error)
{
  $error = $dbInstaller->updateConfigFiles();
}
echo json_encode(array('error'=>$error, 'file'=>$file));
