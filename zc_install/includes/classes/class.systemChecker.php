<?php
/**
 * file contains systemChecker Class
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: 
 *
 */
/**
 * 
 * systemChecker Class
 *
 */
class systemChecker
{
  public function __construct($selectedAdminDir)
  {
    $this->adminDirectoryList = self::getAdminDirectoryList();
    $res = sfYaml::load(DIR_FS_INSTALL . 'includes/systemChecks.yml');
    $this -> systemChecks = $res['systemChecks'];
    $this->selectedAdminDir = $selectedAdminDir;
    $this->extraRunLevels = array();
  }

  public function runTests($runLevel = 'always')
  {
    $runLevels = array_merge(array($runLevel), $this->extraRunLevels);
    $this -> errorList = array();
    foreach ($this->systemChecks as $systemCheckName => $systemCheck)
    {
//      print_r($systemCheck);
      if (in_array($systemCheck['runLevel'], $runLevels))
      {
        $resultCombined = TRUE;
        foreach ($systemCheck['methods'] as $methodName => $methodDetail)
        {
          $this->localErrors = NULL;
          if (isset($methodDetail['method'])) $methodName = $methodDetail['method'];
          $result = $this -> {$methodName}($methodDetail['parameters']);
          $resultCombined &= $result;
          $this->log($result, $methodName, $methodDetail);
          if (!$result)
          {
            if (isset($methodDetail['localErrorText']))
            {
              $systemCheck['extraErrors'][] = $methodDetail['localErrorText'];
            } elseif (isset($this->localErrors))
            {
              $systemCheck['extraErrors'][] = $this->localErrors;
            }
          }
        }
        if (!$resultCombined)
        {
          $this -> errorList[$systemCheckName] = $systemCheck;
        }
      }
    }
    return $this -> errorList;
  }

  public function getErrorList($condition = 'FAIL')
  {
    $result = FALSE;
    $resultList = array();
    foreach ($this->errorList as $entry)
    {
      if ($entry['errorLevel'] == $condition)
      {
        $result = TRUE;
        $resultList[] = $entry;
      }
    }
    return array($result, $resultList);
  }
  public function hasSaneConfigFile()
  {
    $result = FALSE;
    if (file_exists(DIR_FS_ROOT . 'includes/configure.php'))
    {
      $lines = @file(DIR_FS_ROOT . 'includes/configure.php');
      if (!is_array($lines) || count($lines) == 0 ) return FALSE;
      $httpServerVal = $this->getConfigureDefine('HTTP_SERVER', $lines);
      $fsCatalogVal = $this->getConfigureDefine('DIR_FS_CATALOG', $lines);
      $dbPasswordVal = $this->getConfigureDefine('DB_SERVER_PASSWORD', $lines);
      if ($httpServerVal != "" && $fsCatalogVal != "" && $dbPasswordVal != "")
      {
        $result = TRUE;
      }
    }
    return $result;
  }
  public function hasUpdatedConfigFile()
  {
    $result = FALSE;
    if (file_exists(DIR_FS_ROOT . 'includes/configure.php'))
    {
      $lines = @file(DIR_FS_ROOT . 'includes/configure.php');
      if (!is_array($lines) || count($lines) == 0 ) return FALSE;
      $sessionStorage = $this->getConfigureDefine('SESSION_STORAGE', $lines);
      if (isset($sessionStorage))
      {
        $result = TRUE;
      }
    }
    return $result;
  }
  public function removeConfigureErrors()
  {
    $listFatalErrors = array();
    foreach ($this->errorList as $key => $value)
    {
      if ($key != 'checkStoreConfigureFile' && $key != 'checkAdminConfigureFile')
      {
        if ($value['errorLevel'] == 'FAIL') $listFatalErrors[$key] = $value;
      }
    }
    $hasFatalErrors = (count($listFatalErrors) > 0) ? TRUE : FALSE;
    return (array($hasFatalErrors, $listFatalErrors));
  }
  public function getDbConfigOptions()
  {
    $lines = file(DIR_FS_ROOT . 'includes/configure.php');
    $dbServerVal = $this->getConfigureDefine('DB_SERVER', $lines);
    $dbNameVal = $this->getConfigureDefine('DB_DATABASE', $lines);
    $dbPasswordVal = $this->getConfigureDefine('DB_SERVER_PASSWORD', $lines);
    $dbUserVal = $this->getConfigureDefine('DB_SERVER_USERNAME', $lines);
    $dbPrefixVal = $this->getConfigureDefine('DB_PREFIX', $lines);
    $dbCharsetVal = $this->getConfigureDefine('DB_CHARSET', $lines);
    $dbTypeVal = $this->getConfigureDefine('DB_TYPE', $lines);
    $sqlCacheDirVal = $this->getConfigureDefine('SQL_CACHE_DIR', $lines);
    $retVal = array('db_host'=>$dbServerVal, 'db_user'=>$dbUserVal, 'db_password'=>$dbPasswordVal, 'db_name'=>$dbNameVal, 'db_charset'=>$dbCharsetVal, 'db_prefix'=>$dbPrefixVal, 'db_type'=>$dbTypeVal, 'sql_cache_dir'=>$dbSqlCacheDirVal);
    return $retVal;    
  }
  public function getServerConfigOptions()
  {
    $lines = file(DIR_FS_ROOT . 'includes/configure.php');
    return $retVal;    
  }
  public function getConfigureDefine($searchDefine, $lines)
  {
    $retVal = NULL;
    foreach ($lines as $line)
    {
      if (substr(trim($line),0,2) != '//')
      {
        $def_string=array();
        $def_string=explode("'",$line);
        //define('CONSTANT','value');
        //[1]=TABLE_CONSTANT
        //[2]=,
        //[3]=value
        //[4]=);
        //[5]=
        if (isset($def_string[1]) && strtoupper($def_string[1]) == $searchDefine ) {
          $retVal = $def_string[3];
          continue;
        }
      }
    }
    return $retVal;
  }
  public function findCurrentDbVersion() 
  {
    foreach ($this->systemChecks as $systemCheckName => $systemCheck)
    {
      $version = NULL;
      if ($systemCheck['runLevel'] == 'dbVersion')
      {
        $resultCombined = TRUE;
        foreach ($systemCheck['methods'] as $methodName => $methodDetail)
        {
          if (isset($methodDetail['method'])) $methodName = $methodDetail['method'];
          $result = $this -> {$methodName}($methodDetail['parameters']);
          $resultCombined &= $result;
          if (!$result)
          {
            if (isset($methodDetail['localErrorText']))
            {
              $systemCheck['extraErrors'][] = $methodDetail['localErrorText'];
            } 
          } else 
          {
            $version = $systemCheck['version'];
            break;
          }
        }
        if (!$resultCombined)
        {
          $this -> errorList[] = $systemCheck;
        }
        if (isset($version)) break;
      }
    }
//    print_r($this->errorList);
    return $version;
  }
  public function dbVersionChecker($parameters)
  {
    $lines = @file(DIR_FS_ROOT . 'includes/configure.php');
    if (!is_array($lines) || count($lines) == 0) return FALSE;
    $dbServerVal = $this->getConfigureDefine('DB_SERVER', $lines);
    $dbNameVal = $this->getConfigureDefine('DB_DATABASE', $lines);
    $dbPasswordVal = $this->getConfigureDefine('DB_SERVER_PASSWORD', $lines);
    $dbUserVal = $this->getConfigureDefine('DB_SERVER_USERNAME', $lines);
    $dbPrefixVal = $this->getConfigureDefine('DB_PREFIX', $lines);
    require_once (DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
    $db = new queryFactory();
    $result = $db -> simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
    if (!$result)
    {
      $systemCheck['extraErrors'][] = $db -> error_number . ':' . $db -> error_text;
    } else
    {
      $result = $db -> selectdb($dbNameVal, $db -> link);
    }
    if (!$result)
    {
      $systemCheck['extraErrors'][] = $db -> error_number . ':' . $db -> error_text;
    }
    if ($result == false) return $result;
    $result = FALSE;
    foreach ($parameters as $parameter)
    {
      $method = 'dbVersionCheck' . ucfirst($parameter['checkType']);
      $res = $this->$method($db, $dbprefix, $parameter);
//      echo $parameter['checkType'] . $res . '<br>';
      $result |= $res;
    }
    return $result;
  }
  public function dbVersionCheckFieldSchema($db, $dbPrefix, $parameters)
  {
    $retVal = FALSE;
    $sql = "show fields from " . $dbPrefix . $parameters['tableName'];
    $result = $db->execute($sql);
    if ($result)
    {
      while (!$result->EOF && !$retVal) 
      {
        if  ($result->fields['Field'] == $parameters['fieldName'] && strtoupper($result->fields[$parameters['fieldCheck']]) == $parameters['expectedResult']) 
        {
          $retVal = TRUE;
        }  
        $result->MoveNext();
      }
    }
    return $retVal;
  }
  public function dbVersionCheckConfigValue($db, $dbPrefix, $parameters)
  {
    $retVal = FALSE;
    $sql = "select configuration_title from " . $dbPrefix . "configuration where configuration_key = '" . $parameters['fieldName'] . "'";
    $result = $db->execute($sql);
    if ($result)
    {
      $retVal  = ($result->fields['configuration_title'] == $parameters['expectedResult']) ? TRUE : FALSE; 
    }
    return $retVal;
  }
  public function dbVersionCheckConfigDescription($db, $dbPrefix, $parameters)
  {
    $retVal = FALSE;
    $sql = "select configuration_description from " . $dbPrefix . "configuration where configuration_key = '" . $parameters['fieldName'] . "'";
    $result = $db->execute($sql);
    if ($result)
    {
      $retVal  = ($result->fields['configuration_description'] == $parameters['expectedResult']) ? TRUE : FALSE; 
    }
    return $retVal;
  }
  public function checkWriteableDir($parameters)
  {
    return is_writeable($parameters['fileDir']);
  }

  public function checkWriteableFile($parameters)
  {
    if (isset($parameters['changePerms']) &&  $parameters['changePerms'] !== FALSE)
    {
      if (file_exists($parameters['fileDir']))
      {
        @chmod($parameters['fileDir'], $parameters['changePerms']);
      } else 
      {
      	if ($fp = @fopen($parameters['fileDir'], 'c'))
      	{
      	  fclose($fp);
      	  chmod($parameters['fileDir'], $parameters['changePerms']);
      	}
      }
    }
    return (is_writeable($parameters['fileDir']));
  }
  public function checkWriteableAdminFile($parameters)
  {
    if (is_writeable(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'])) return TRUE;
    if (isset($parameters['changePerms']) &&  $parameters['changePerms'] !== FALSE)
    {
      if (file_exists(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir']))
      {
        @chmod(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'], $parameters['changePerms']);
      } else
      {
        if ($fp = @fopen(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'], 'c'))
        {
          fclose($fp);
          chmod(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'], $parameters['changePerms']);
        }
      }
      if (is_writeable(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'])) return TRUE;
    }
    return FALSE;
  }
  public function checkExtension($parameters)
  {
    return (extension_loaded($parameters['extension']));
  }
  public function checkFunctionExists($parameters)
  {
    return (function_exists($parameters['functionName']));
  }
  
  public function checkPhpVersion($parameters)
  {
    $result = version_compare(PHP_VERSION, $parameters['version'], $parameters['versionTest']);
    return $result;
  }

  public function checkHtaccessSupport($parameters)
  {
    $testPath = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
    $testPath = 'http://' . substr($testPath, 0, strpos($testPath, '/zc_install')) . '/includes/filenames.php';
    if (function_exists('curl_init'))
    {
      $resultCurl = self::curlGetUrl($testPath);
      if (isset($resultCurl['http_code']) && $resultCurl['http_code'] == '403')
      {
        $result = TRUE;
      } else 
      {
        $result = FALSE;
      }
      
    } else
    {
      $result = TRUE;
    }
    return $result;
  }

  public function checkInitialSession($parameters)
  {
    session_name($parameters['sessionName']);
    $result = @session_start();
    if (!$result)
      RETURN FALSE;
    if (defined(SID) && SID != "")
      return FALSE;
//    if (session_status() == PHP_SESSION_DISABLED)
//      return FALSE;
    $_SESSION['testSession'] = 'testSession';
    return TRUE;
  }

  public function checkUpgradeDBConnection($parameters)
  {
    $lines = file(DIR_FS_ROOT . 'includes/configure.php');
    $dbServerVal = $this->getConfigureDefine('DB_SERVER', $lines);
    $dbNameVal = $this->getConfigureDefine('DB_DATABASE', $lines);
    $dbPasswordVal = $this->getConfigureDefine('DB_SERVER_PASSWORD', $lines);
    $dbUserVal = $this->getConfigureDefine('DB_SERVER_USERNAME', $lines);
    require_once (DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
    $db = new queryFactory();
    $result = $db -> simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
    if (!$result)
    {
      $this->localErrors = $db -> error_number . ':' . $db -> error_text;
    } else
    {
      $result = $db -> selectdb($dbNameVal, $db -> link);
    }
    if (!$result)
    {
      $this->localErrors = $db -> error_number . ':' . $db -> error_text;
    }
    return $result;
  }
  public function checkDBConnection($parameters)
  {
    $lines = file(DIR_FS_ROOT . 'includes/configure.php');
    $dbServerVal = $this->getConfigureDefine('DB_SERVER', $lines);
    $dbNameVal = $this->getConfigureDefine('DB_DATABASE', $lines);
    $dbPasswordVal = $this->getConfigureDefine('DB_SERVER_PASSWORD', $lines);
    $dbUserVal = $this->getConfigureDefine('DB_SERVER_USERNAME', $lines);
    require_once (DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
    $db = new queryFactory();
    $result = $db -> simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
    if ($db->error_number != '2002')
    {
      $result = TRUE;
    }
    return $result;
  }
  public function checkNewDBConnection($parameters)
  {
    require_once (DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
    $db = new queryFactory();
    $result = $db -> simpleConnect(zcRegistry::getValue('db_host'), zcRegistry::getValue('db_user'), zcRegistry::getValue('db_password'), zcRegistry::getValue('db_name'));
    if (!$result)
    {
      $this->localErrors = $db -> error_number . ':' . $db -> error_text;
    } else
    {
      $result = $db -> selectdb(zcRegistry::getValue('db_name'), $db -> link);
      if (!$result)
      {
        $sql = "CREATE DATABASE " . zcRegistry::getValue('db_name') . " CHARACTER SET " . zcRegistry::getValue('db_charset');
       $result = $db -> execute($sql);
       if ($result)
        {
          return TRUE;
       } else
        {
          $this->localErrors = $db -> error_number . ':' . $db -> error_text;
       }
      }
    }
    return $result;
  }
  public function checkRegisterGlobals($parameters)
  {
    $register_globals = ini_get("register_globals");
    if ($register_globals == '' || $register_globals =='0' || strtoupper($register_globals) =='OFF') {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  public function checkIniGet($parameters)
  {
    $result = @ini_get($parameters['inigetName']);
    return ($result != $parameters['expectedValue']) ? FALSE : TRUE;
  }
  public function checkLiveCurl($parameters)
  {
    $url = 'http://' . $parameters['testUrl'];
    $data = $parameters['testData'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 11);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); /* compatibility for SSL communications on some Windows servers (IIS 5.0+) */
    $result = curl_exec($ch);
    $errtext = curl_error($ch);
    $errnum = curl_errno($ch);
    $commInfo = @curl_getinfo($ch);
    curl_close ($ch);
    if ($errnum != 0 || trim($result) != 'PASS') 
    {
      return FALSE;
    } else 
    {
      return TRUE;
    }
  }
  public function checkHttpsRequest($parameters)
  {
    global $request_type;
    return ($request_type == 'SSL') ? TRUE : FALSE;
  }
  public function addRunlevel($runLevel)
  {
    $this->extraRunLevels[] = $runLevel;
  }
  public function validateAdminCredentials($adminUser, $adminPassword)
  {
    $parameters = array(array('checkType'=>'fieldSchema', 'tableName'=>'admin', 'fieldName'=>'admin_profile', 'fieldCheck'=>'Type', 'expectedResult'=>'INT(11)'));
    $hasAdminProfiles = $this->dbVersionChecker($parameters);
    $lines = file(DIR_FS_ROOT . 'includes/configure.php');
    $dbServerVal = $this->getConfigureDefine('DB_SERVER', $lines);
    $dbNameVal = $this->getConfigureDefine('DB_DATABASE', $lines);
    $dbPasswordVal = $this->getConfigureDefine('DB_SERVER_PASSWORD', $lines);
    $dbUserVal = $this->getConfigureDefine('DB_SERVER_USERNAME', $lines);
    $dbPrefixVal = $this->getConfigureDefine('DB_PREFIX', $lines);
    require_once (DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
    $db = new queryFactory();
    $result = $db -> simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
    if (!$result)
    {
      $systemCheck['extraErrors'][] = $db -> error_number . ':' . $db -> error_text;
    } else
    {
      $result = $db -> selectdb($dbNameVal, $db -> link);
    }
    if (!$result)
    {
      $systemCheck['extraErrors'][] = $db -> error_number . ':' . $db -> error_text;
    }
    if ($result == FALSE) return $result;
//    echo ($hasAdminProfiles) ? 'YES' : 'NO';
    if (!$hasAdminProfiles)
    {
      $sql = "select admin_id, admin_name, admin_pass from " . $dbPrefixVal . "admin where admin_name = '" . $adminUser . "'";
      $result = $db->execute($sql);
    	if ($result->EOF || $adminUser != $result->fields['admin_name'] || !zen_validate_password($adminPassword, $result->fields['admin_pass'])) 
      {
        return FALSE;
      } else 
      {  
        return $result->fields['admin_id'];
      }
    } else 
    {
    	$sql = "select a.admin_id, a.admin_name, a.admin_pass, a.admin_profile  
    			    from " . $dbPrefixVal . "admin as a 
    			    left join " . $dbPrefixVal . "admin_profiles as ap on a.admin_profile = ap.profile_id  		
    			    where a.admin_name = '" . $adminUser . "' 
    			    and ap.profile_name = 'Superuser'";
    	$result = $db->execute($sql);
    	if ($result->EOF || $adminUser != $result->fields['admin_name'] || !zen_validate_password($adminPassword, $result->fields['admin_pass'])) 
    	{
    	  return FALSE;	
    	} else 
    	{
    		return TRUE;
    	}
    }
  }
  function curlGetUrl( $url )
  {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => false,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );
  
    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );
  
    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
  }
  function getAdminDirectoryList()
  {
    $adminDirectoryList = array();
    
    $ignoreArray = array('.', '..', 'cache', 'logs', 'installer', 'zc_install', 'includes', 'testFramework', 'editors', 'extras', 'images', 'docs', 'pub', 'email', 'download', 'media');
    $d = @dir(DIR_FS_ROOT);
    while (false !== ($entry = $d->read())) {
      if (is_dir(DIR_FS_ROOT . $entry) && !in_array($entry, $ignoreArray))
      {
        if (file_exists(DIR_FS_ROOT . $entry . '/' . 'banner_manager.php'))
        {
          $adminDirectoryList[] = $entry;
        }
      }
    }
    return $adminDirectoryList;
  }
  function backupConfigureFiles($parameters)
  {
    return TRUE;
  }
  function log($result, $methodName, $methodDetail)
  {
    if (defined('VERBOSE_SYSTEMCHECKER') && VERBOSE_SYSTEMCHECKER)
    {
      echo $methodName . "<br>";
      foreach ($methodDetail['parameters'] as $key=>$value)
      {
        echo $key . " : " . $value . "<br>";
      }
      echo (($result == 1) ? 'PASSED' : 'FAILED') . "<br>";
      echo "------------------<br><br>";
    }
  }  
}
