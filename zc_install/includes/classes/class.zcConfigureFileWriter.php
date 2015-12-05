<?php
/**
 * file contains zcConfigureFileWriter class
 * @package Installer
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
/**
 *
 * zcConfigureFileWriter class
 *
 */
class zcConfigureFileWriter
{
  public function __construct($inputs)
  {
    $this->inputs = $inputs;
    $replaceVars = array();
    $replaceVars['INSTALLER_METHOD'] = (isset($inputs['installer_method'])) ? trim($inputs['installer_method']) : 'Zen Cart Installer';
    $replaceVars['DATE_NOW'] = date('D M y H:i:s');
    $replaceVars['CATALOG_HTTP_SERVER'] = trim($inputs['http_server_catalog'], '/ ');
    $replaceVars['CATALOG_HTTPS_SERVER'] = trim($inputs['https_server_catalog'], '/ ');
    $replaceVars['ENABLE_SSL_CATALOG'] = isset($inputs['enable_ssl_catalog']) ? 'true' : 'false' ;
    $replaceVars['DIR_WS_CATALOG'] = preg_replace('~//~', '/', '/' . trim($inputs['dir_ws_http_catalog'], ' /\\') . '/');
    $replaceVars['DIR_WS_HTTPS_CATALOG'] = preg_replace('~//~', '/', '/' . trim($inputs['dir_ws_https_catalog'], ' /\\') . '/');
    $replaceVars['DIR_FS_CATALOG'] = rtrim($inputs['physical_path'], ' /\\') . '/';
    $replaceVars['DB_TYPE'] = trim($inputs['db_type']);
    $replaceVars['DB_PREFIX'] = trim($inputs['db_prefix']);
    $replaceVars['DB_CHARSET'] = trim($inputs['db_charset']);
    $replaceVars['DB_SERVER'] = trim($inputs['db_host']);
    $replaceVars['DB_SERVER_USERNAME'] = trim($inputs['db_user']);
    $replaceVars['DB_SERVER_PASSWORD'] = trim($inputs['db_password']);
    $replaceVars['DB_DATABASE'] = trim($inputs['db_name']);
    $replaceVars['SQL_CACHE_METHOD'] = trim($inputs['sql_cache_method']);
    $replaceVars['HTTP_SERVER_ADMIN'] = trim($inputs['http_server_admin']);
    $replaceVars['SESSION_STORAGE'] = 'reserved for future use';

    $this->replaceVars = $replaceVars;
    $adminDir = $inputs['adminDir'];

// die('<pre>' . print_r($inputs, true));

    $this->processAllConfigureFiles($adminDir);
  }
  protected function processAllConfigureFiles($adminDir)
  {
    $tplFile = DIR_FS_INSTALL . 'includes/catalog-dist-configure.php';
    $outputFile = rtrim($this->inputs['physical_path'], '/') . '/includes/configure.php';
    $result = $this->transformConfigureTplFile($tplFile, $outputFile);
// logDetails('catalog size: ' . $result . ' (will be greater than 0 if file was written correctly)', 'store configure.php');

    $tplFile = DIR_FS_INSTALL . 'includes/admin-dist-configure.php';
    $outputFile = rtrim($this->inputs['physical_path'], '/') . '/'. $adminDir . '/includes/configure.php';
    $result = $this->transformConfigureTplFile($tplFile, $outputFile);
// logDetails('admin size: ' . $result . ' (will be greater than 0 if file was written correctly)', 'admin configure.php');

  }
  protected function transformConfigureTplFile($tplFile, $outputFile)
  {
    $tplOriginal = @file_get_contents($tplFile);
    foreach ($this->replaceVars as $varName => $varValue)
    {
      $tplOriginal = str_replace('%%_' . $varName . '_%%', $varValue, $tplOriginal);
    }
    $retval = file_put_contents($outputFile, $tplOriginal);

    return $retval;
  }
}
