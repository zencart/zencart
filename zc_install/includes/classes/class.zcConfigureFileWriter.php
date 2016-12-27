<?php
/**
 * file contains zcConfigureFileWriter class
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Tue Feb 16 15:03:47 2016 -0500 New in v1.5.5 $
 */
/**
 *
 * zcConfigureFileWriter class
 *
 */
class zcConfigureFileWriter
{
  public $errors = array();

  public function __construct($inputs)
  {
    $this->inputs = $inputs;
    $replaceVars = array();
    $replaceVars['INSTALLER_METHOD'] = (isset($inputs['installer_method'])) ? trim($inputs['installer_method']) : 'Zen Cart Installer';
    $replaceVars['DATE_NOW'] = date('D M d Y H:i:s');
    $replaceVars['CATALOG_HTTP_SERVER'] = trim($inputs['http_server_catalog'], '/ ');
    $replaceVars['CATALOG_HTTPS_SERVER'] = trim($inputs['https_server_catalog'], '/ ');
    $replaceVars['ENABLE_SSL_CATALOG'] = $inputs['enable_ssl_catalog'];
    $replaceVars['DIR_WS_CATALOG'] = preg_replace('~//~', '/', '/' . trim($inputs['dir_ws_http_catalog'], ' /\\') . '/');
    $replaceVars['DIR_WS_HTTPS_CATALOG'] = preg_replace('~//~', '/', '/' . trim($inputs['dir_ws_https_catalog'], ' /\\') . '/');
    $replaceVars['DIR_FS_CATALOG'] = rtrim($inputs['physical_path'], ' /\\') . '/';

    $replaceVars['DB_TYPE'] = trim($inputs['db_type']);
    if ($replaceVars['DB_TYPE'] == '') $replaceVars['DB_TYPE'] = 'mysql';

    $replaceVars['DB_PREFIX'] = trim($inputs['db_prefix']);

    $replaceVars['DB_CHARSET'] = trim($inputs['db_charset']);
    if ($replaceVars['DB_CHARSET'] == '') $replaceVars['DB_CHARSET'] = 'utf8';

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

    $this->processAllConfigureFiles();
  }
  protected function processAllConfigureFiles()
  {
    $tplFile = DIR_FS_INSTALL . 'includes/catalog-configure-template.php';
    $outputFile = rtrim($this->inputs['physical_path'], '/') . '/includes/configure.php';
    $result1 = $this->transformConfigureTplFile($tplFile, $outputFile);
    if ((int)$result1 == 0) logDetails('catalogConfig size: ' . (int)$result1 . ' (will be greater than 0 if file was written correctly)', 'store configure.php');

    // return a result indicating whether writing the file failed in some way: true=success;
    return $result1;
  }
  protected function transformConfigureTplFile($tplFile, $outputFile)
  {
    $tplOriginal = file_get_contents($tplFile);
    if ($tplOriginal === false) {
      $this->errors[] = sprintf(TEXT_ERROR_COULD_NOT_READ_CFGFILE_TEMPLATE, $tplFile);
      logDetails('Error: Could not read file: ' . $tplFile, 'reading configure.php template');
      return false;
    }
    foreach ($this->replaceVars as $varName => $varValue)
    {
      $tplOriginal = str_replace('%%_' . $varName . '_%%', $varValue, $tplOriginal);
    }
    $retval = file_put_contents($outputFile, $tplOriginal);
    if ($retval === false) {
      $this->errors[] = sprintf(TEXT_ERROR_COULD_NOT_WRITE_CONFIGFILE, $outputFile);
      logDetails('Error: Could not write configure.php file: ' . $outputFile, 'writing configure.php contents');
    }

    return $retval;
  }
}
