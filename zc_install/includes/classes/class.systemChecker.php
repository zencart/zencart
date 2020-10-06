<?php
/**
 * file contains systemChecker Class
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 18 Modified in v1.5.7 $
 */

/**
 * systemChecker Class
 */
class systemChecker
{
    public function __construct($selectedAdminDir = 'UNSPECIFIED')
    {
        $this->adminDirectoryList = self::getAdminDirectoryList();
        $res = sfYaml::load(DIR_FS_INSTALL . 'includes/systemChecks.yml');
        $this->systemChecks = $res['systemChecks'];
        $this->extraRunLevels = array();

        if (file_exists(DIR_FS_ROOT . 'includes/local/configure.php')) {
            $this->extraRunLevels[] = 'localdev';
        }

        if ($selectedAdminDir == 'UNSPECIFIED' || $selectedAdminDir == '' || !file_exists(DIR_FS_ROOT . $selectedAdminDir)) {
            if (count($this->adminDirectoryList) == 1) $selectedAdminDir = $this->adminDirectoryList[0];
        }
        $this->selectedAdminDir = $selectedAdminDir;
    }

    public function runTests($runLevel = 'always')
    {
        $runLevels = array_merge(array($runLevel), $this->extraRunLevels);
        $this->errorList = array();
//echo print_r($this->systemChecks);
        foreach ($this->systemChecks as $systemCheckName => $systemCheck) {
//echo print_r($systemCheck);
            if (in_array($systemCheck['runLevel'], $runLevels)) {
                $resultCombined = TRUE;
                $criticalError = false;
                foreach ($systemCheck['methods'] as $methodName => $methodDetail) {
                    $this->localErrors = NULL;
                    if (isset($methodDetail['method'])) $methodName = $methodDetail['method'];
                    $result = $this->{$methodName}(isset($methodDetail['parameters']) ? $methodDetail['parameters'] : null);
                    $resultCombined &= $result;
                    if ($result == false && (isset($this->systemChecks[$systemCheckName]['criticalError']))) {
                        $criticalError = true;
                    }
                    $this->log($result, $methodName, $methodDetail);
                    if (!$result) {
                        if (isset($methodDetail['localErrorText'])) {
                            $systemCheck['extraErrors'][] = $methodDetail['localErrorText'];
                        } elseif (isset($this->localErrors)) {
                            $systemCheck['extraErrors'][] = $this->localErrors;
                        }
                    }
                }
                if (!$resultCombined) {
                    $this->errorList[$systemCheckName] = $systemCheck;
                }
                if ($criticalError) break 1;
            }
        }
        return $this->errorList;
    }

    public function getErrorList($condition = 'FAIL')
    {
        $result = FALSE;
        $resultList = array();
        foreach ($this->errorList as $entry) {
            if ($entry['errorLevel'] == $condition) {
                $result = TRUE;
                $resultList[] = $entry;
            }
        }
        return array($result, $resultList);
    }

    public function hasTables()
    {
        $result = FALSE;
        if ($this->hasSaneConfigFile()) {
            $parameters = array(array('checkType' => 'fieldSchema', 'tableName' => 'admin', 'fieldName' => 'admin_id', 'fieldCheck' => 'Type', 'expectedResult' => 'INT(11)'));
            $result = $this->dbVersionChecker($parameters);
        }
        return $result;
    }

    public function hasSaneConfigFile()
    {
        $result = FALSE;
        if ($this->getServerConfig()->fileLoaded()) {
            $httpServerVal = $this->getServerConfig()->getDefine('HTTP_SERVER');
            $fsCatalogVal = $this->getServerConfig()->getDefine('DIR_FS_CATALOG');
            $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
            if ($httpServerVal != "" && $fsCatalogVal != "" && $dbUserVal != "") {
                $result = TRUE;
            }
        }
        return $result;
    }

    public function hasUpdatedConfigFile()
    {
        $result = FALSE;
        if ($this->getServerConfig()->fileLoaded()) {

            // if the new define added in v155 is present, then this deems the file to be already updated
            $sessionStorage = $this->getServerConfig()->getDefine('SESSION_STORAGE');
            if (isset($sessionStorage)) {
                $result = TRUE;
            }
        }
        return $result;
    }

    public function removeConfigureErrors()
    {
        $listFatalErrors = array();
        foreach ($this->errorList as $key => $value) {
            if ($key != 'checkStoreConfigureFile' && $key != 'checkAdminConfigureFile') {
                if ($value['errorLevel'] == 'FAIL') $listFatalErrors[$key] = $value;
            }
        }
        $hasFatalErrors = (count($listFatalErrors) > 0) ? TRUE : FALSE;
        return (array($hasFatalErrors, $listFatalErrors));
    }

    public function getDbConfigOptions()
    {
        $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
        $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
        $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
        $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
        $dbPrefixVal = $this->getServerConfig()->getDefine('DB_PREFIX');
        $dbCharsetVal = $this->getServerConfig()->getDefine('DB_CHARSET');
        $dbTypeVal = $this->getServerConfig()->getDefine('DB_TYPE');
        $retVal = array('db_host' => $dbServerVal, 'db_user' => $dbUserVal, 'db_password' => $dbPasswordVal, 'db_name' => $dbNameVal, 'db_charset' => $dbCharsetVal, 'db_prefix' => $dbPrefixVal, 'db_type' => $dbTypeVal);
        return $retVal;
    }

    public function getServerConfig()
    {
        if (!isset($this->serverConfig)) {
            $configFile = DIR_FS_ROOT . 'includes/configure.php';
            $configFileLocal = DIR_FS_ROOT . 'includes/local/configure.php';
            if (file_exists($configFileLocal)) $configFile = $configFileLocal;
            $this->serverConfig = new zcConfigureFileReader($configFile);
        }
        return $this->serverConfig;
    }

    public function findCurrentDbVersion()
    {
        foreach ($this->systemChecks as $systemCheckName => $systemCheck) {
            $version = NULL;
            if ($systemCheck['runLevel'] == 'dbVersion') {
                $resultCombined = TRUE;
                if (!isset($systemCheck['methods'])) $systemCheck['methods'] = array();
                foreach ($systemCheck['methods'] as $methodName => $methodDetail) {
                    if (isset($methodDetail['method'])) $methodName = $methodDetail['method'];
                    $result = $this->{$methodName}($methodDetail['parameters']);
                    $resultCombined &= $result;
                    if (!$result) {
                        if (isset($methodDetail['localErrorText'])) {
                            $systemCheck['extraErrors'][] = $methodDetail['localErrorText'];
                        }
                    } else {
                        $version = $systemCheck['version'];
                        break;
                    }
                }
                if (!$resultCombined) {
                    $this->errorList[] = $systemCheck;
                }
                if (isset($version)) break;
            }
        }
//echo print_r($this->errorList);
        return $version;
    }

    public function dbVersionChecker($parameters)
    {
        if (function_exists('mysqli_connect')) {
            if (!$this->getServerConfig()->fileLoaded()) return FALSE;
            $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
            $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
            $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
            $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
            $dbPrefixVal = $this->getServerConfig()->getDefine('DB_PREFIX');
            require_once(DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
            $db = new queryFactory();
            $result = $db->simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
            if (!$result) {
                $systemCheck['extraErrors'][] = $db->error_number . ':' . $db->error_text;
            } else {
                $result = $db->selectdb($dbNameVal);
            }
            if (!$result) {
                $systemCheck['extraErrors'][] = $db->error_number . ':' . $db->error_text;
            }
            if ($result == false) return $result;
            $valid = TRUE;
            foreach ($parameters as $parameter) {
                $method = 'dbVersionCheck' . ucfirst($parameter['checkType']);
                $result = $this->$method($db, $dbPrefixVal, $parameter);
//echo ($parameter['tableName'] ? $parameter['tableName'] . '-' : '') . $parameter['checkType'] . ': ' . var_export($result, true) . '<br>' . "\n";
                $valid = $valid && $result;
            }
//echo 'Valid: ' . var_export($valid, true) . '<br>' . "\n";
        } else {
            $valid = false;
        }
        return $valid;
    }

    public function dbVersionCheckFieldSchema($db, $dbPrefix, $parameters)
    {
        $sql = "show fields from " . $dbPrefix . $parameters['tableName'];
        $result = $db->execute($sql);
        while (!$result->EOF) {
            // if found the specified field ...
            if ($result->fields['Field'] == $parameters['fieldName']) {
                // then return true if the test was simply "Exists", or check that the field's type matches the fieldCheck test
                if ($parameters['fieldCheck'] == 'Exists' || strtoupper($result->fields[$parameters['fieldCheck']]) == $parameters['expectedResult']) {
                    return true;
                }
            }
            $result->MoveNext();
        }
        return false;
    }

    public function dbVersionCheckConfigValue($db, $dbPrefix, $parameters)
    {
        $retVal = FALSE;
        $sql = "select configuration_title from " . $dbPrefix . "configuration where configuration_key = '" . $parameters['fieldName'] . "'";
        $result = $db->execute($sql);
        if ($result && isset($result->fields['configuration_title'])) {
            $retVal = ($result->fields['configuration_title'] == $parameters['expectedResult']) ? TRUE : FALSE;
        }
        return $retVal;
    }

    public function dbVersionCheckConfigDescription($db, $dbPrefix, $parameters)
    {
        $retVal = FALSE;
        $sql = "select configuration_description from " . $dbPrefix . "configuration where configuration_key = '" . $parameters['fieldName'] . "'";
        $result = $db->execute($sql);
        if ($result && isset($result->fields['configuration_description'])) {
            $retVal = ($result->fields['configuration_description'] == $parameters['expectedResult']) ? TRUE : FALSE;
        }
        return $retVal;
    }

    public function checkFileExists($parameters)
    {
        return file_exists($parameters['fileDir']);
    }

    public function checkWriteableDir($parameters)
    {
        return is_writeable($parameters['fileDir']);
    }

    public function checkWriteableFile($parameters)
    {
        if (isset($parameters['changePerms']) && $parameters['changePerms'] !== FALSE) {
            if (file_exists($parameters['fileDir'])) {
                @chmod($parameters['fileDir'], octdec($parameters['changePerms']));
            } else {
                if ($fp = @fopen($parameters['fileDir'], 'c')) {
                    fclose($fp);
                    chmod($parameters['fileDir'], octdec($parameters['changePerms']));
                }
            }
        }
        return (is_writeable($parameters['fileDir']));
    }

    public function checkWriteableAdminFile($parameters)
    {
        if (is_writeable(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'])) return TRUE;
        if (!file_exists(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'])) {
            if ($fp = @fopen(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'], 'c')) {
                fclose($fp);
            }
        }
        if (file_exists(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'])) {
            if (isset($parameters['changePerms']) && $parameters['changePerms'] !== FALSE) {
                @chmod(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'], octdec($parameters['changePerms']));
            }
            if (is_writeable(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'])) return TRUE;
        }
        logDetails(DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'], 'ADMIN FILE TEST');
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
        if (!function_exists('curl_init')) {
            return true;
        }

        if (preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE'])) {
            return true;
        }

        global $request_type;
        $tests = [];

        $testPath = preg_replace('~/zc_install.*$~', '/includes/filenames.php', $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']);
        // first element added to the $tests array is based on $request_type
        $tests[] = ($request_type == 'SSL' ? 'https://' : 'http://') . $testPath;
        // add inverse test as fallback
        $tests[] = ($request_type == 'SSL' ? 'http://' : 'https://') . $testPath;

        foreach ($tests as $test) {
            $resultCurl = self::curlGetUrl($test, false);
            if (isset($resultCurl['http_code']) && $resultCurl['http_code'] == '403') {
                return true;
            }
            // test again with redirects enabled
            $resultCurl = self::curlGetUrl($test, true);
            if (isset($resultCurl['http_code']) && $resultCurl['http_code'] == '403') {
                return true;
            }
        }

        return false;
    }

    public function checkInitialSession($parameters)
    {
        session_name($parameters['sessionName']);
        $result = @session_start();
        if (!$result)
            return FALSE;
//    if (defined('SID') && constant('SID') != "")
//      return FALSE;
//    if (session_status() == PHP_SESSION_DISABLED)
//      return FALSE;
        $_SESSION['testSession'] = 'testSession';
        return TRUE;
    }

    public function checkUpgradeDBConnection($parameters)
    {
        if (function_exists('mysqli_connect')) {
            $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
            $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
            $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
            $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
            require_once(DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
            $db = new queryFactory();
            $result = $db->simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
            if (!$result) {
                $this->localErrors = $db->error_number . ':' . $db->error_text;
            } else {
                $result = $db->selectdb($dbNameVal);
            }
            if (!$result) {
                $this->localErrors = $db->error_number . ':' . $db->error_text;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    public function checkDBConnection($parameters)
    {
        if (function_exists('mysqli_connect')) {
            $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
            $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
            $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
            $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
            require_once(DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
            $db = new queryFactory();
            $result = @$db->simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
            if ($db->error_number != '2002') {
                $result = TRUE;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    public function checkNewDBConnection($parameters)
    {
        require_once(DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
        $db = new queryFactory();
        $result = $db->simpleConnect(zcRegistry::getValue('db_host'), zcRegistry::getValue('db_user'), zcRegistry::getValue('db_password'), zcRegistry::getValue('db_name'));
        if (!$result) {
            $this->localErrors = $db->error_number . ':' . $db->error_text;
        } else {
            $result = $db->selectdb(zcRegistry::getValue('db_name'));
            if (!$result) {
                $sql = "CREATE DATABASE " . zcRegistry::getValue('db_name') . " CHARACTER SET " . zcRegistry::getValue('db_charset');
                $result = $db->execute($sql);
                if ($result) {
                    return TRUE;
                } else {
                    $this->localErrors = $db->error_number . ':' . $db->error_text;
                }
            }
        }
        return $result;
    }

    public function checkIniGet($parameters)
    {
        $result = @ini_get($parameters['inigetName']);
        return ($result != $parameters['expectedValue']) ? FALSE : TRUE;
    }

    public function checkLiveCurl($parameters)
    {
        if (function_exists('curl_init')) {
            $url = (!preg_match('~^http?s:.*~i', $parameters['testUrl'])) ? 'http://' . $parameters['testUrl'] : $parameters['testUrl'];
            $data = $parameters['testData'];
            $ch = curl_init();
// error_log('CURL Test URL: ' . $url);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
//       curl_setopt($ch, CURLOPT_POST, 1);
//       curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 11);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // This is intentionally set to FALSE within zc_install since this test is not about whether certificates are good.
            $result = curl_exec($ch);
            $errtext = curl_error($ch);
            $errnum = curl_errno($ch);
            $commInfo = @curl_getinfo($ch);
// error_log('CURL Connect: ' . $errnum . ' ' . $errtext . "\n" . print_r($commInfo, TRUE));
// error_log('CURL Response: ' . $result);
            curl_close($ch);
            if ($errnum != 0 || trim($result) != 'PASS') {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }

    public function checkHttpsRequest($parameters)
    {
        global $request_type;

        // Take full URI and ping https version of same to see if expected response comes back. If so, redirect install to https.
        // In case multiple-redirects happen on oddly-configured hosts, this can be bypassed by adding ?noredirect=1 to the URL
        if ($request_type != 'SSL' && function_exists('curl_init') && !isset($_GET['noredirect'])) {
            $test_uri = 'https://' . str_replace(array('http://', 'https://'), '', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            $resultCurl = self::curlGetUrl($test_uri);
            //error_log(print_r($resultCurl, true) . print_r($_SERVER, true));
            if (isset($resultCurl['http_code']) && $resultCurl['http_code'] == '200') {
                header('Location: ' . $test_uri);
                exit();
            }
        }
        // otherwise, return the https status so the inspector can report it along with suggestions
        return ($request_type == 'SSL') ? TRUE : FALSE;
    }

    public function addRunlevel($runLevel)
    {
        $this->extraRunLevels[] = $runLevel;
    }

    public function validateAdminCredentials($adminUser, $adminPassword)
    {
        $parameters = array(array('checkType' => 'fieldSchema', 'tableName' => 'admin', 'fieldName' => 'admin_profile', 'fieldCheck' => 'Type', 'expectedResult' => 'INT(11)'));
        $hasAdminProfiles = $this->dbVersionChecker($parameters);
        $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
        $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
        $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
        $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
        $dbPrefixVal = $this->getServerConfig()->getDefine('DB_PREFIX');
        require_once(DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
        $db = new queryFactory();
        $result = $db->simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
        if (!$result) {
            $systemCheck['extraErrors'][] = $db->error_number . ':' . $db->error_text;
        } else {
            $result = $db->selectdb($dbNameVal);
        }
        if (!$result) {
            $systemCheck['extraErrors'][] = $db->error_number . ':' . $db->error_text;
        }
        if ($result == false) return $result;
//    echo ($hasAdminProfiles) ? 'YES' : 'NO';
        if (!$hasAdminProfiles) {
            $sql = "select admin_id, admin_name, admin_pass from " . $dbPrefixVal . "admin where admin_name = '" . $adminUser . "'";
            $result = $db->execute($sql);
            if ($result->EOF || $adminUser != $result->fields['admin_name'] || !zen_validate_password($adminPassword, $result->fields['admin_pass'])) {
                return false;
            } else {
                return $result->fields['admin_id'];
            }
        } else {
            // first check if the table has any superusers; if not, verify the user's password and assign them as a superuser
            $sql = "select distinct(admin_profile)
              from " . $dbPrefixVal . "admin
              order by admin_profile";
            $result = $db->execute($sql);
            if ($result->EOF || ($result->RecordCount() == 1 && $result->fields['admin_profile'] == 0)) {
                $sql = "select admin_id, admin_name, admin_pass
              from " . $dbPrefixVal . "admin
              where admin_name = '" . $adminUser . "'";
                $result = $db->execute($sql);
                if (!$result->EOF && zen_validate_password($adminPassword, $result->fields['admin_pass'])) {
                    $sql = "update " . $dbPrefixVal . "admin
                            set admin_profile = 1
                            where admin_id = " . $result->fields['admin_id'];
                    $db->execute($sql);
                    return $result->fields['admin_id'];
                }
            } else {

                $sql = "select a.admin_id, a.admin_name, a.admin_pass, a.admin_profile
                from " . $dbPrefixVal . "admin as a
                left join " . $dbPrefixVal . "admin_profiles as ap on a.admin_profile = ap.profile_id
                where a.admin_name = '" . $adminUser . "'
                and ap.profile_name = 'Superuser'";
                $result = $db->execute($sql);
                if ($result->EOF || !zen_validate_password($adminPassword, $result->fields['admin_pass'])) {
                    return false;
                }
                return $result->fields['admin_id'];
            }
        }
        return false;
    }

    function curlGetUrl($url, $follow_redirects = false)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => $follow_redirects,    // follow redirects
            CURLOPT_ENCODING => "",       // handle all encodings
            CURLOPT_AUTOREFERER => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 3,        // timeout on connect
            CURLOPT_TIMEOUT => 3,        // timeout on response
            CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;
        return $header;
    }

    static function getAdminDirectoryList()
    {
        $adminDirectoryList = array();

        $ignoreArray = array('.', '..', 'cache', 'logs', 'installer', 'zc_install', 'includes', 'testFramework', 'editors', 'extras', 'images', 'docs', 'pub', 'email', 'download', 'media');
        $d = @dir(DIR_FS_ROOT);
        while (false !== ($entry = $d->read())) {
            if (is_dir(DIR_FS_ROOT . $entry) && !in_array($entry, $ignoreArray)) {
                if (file_exists(DIR_FS_ROOT . $entry . '/' . 'banner_manager.php')) {
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
        if (VERBOSE_SYSTEMCHECKER == 'screen' || VERBOSE_SYSTEMCHECKER === TRUE || VERBOSE_SYSTEMCHECKER == 'TRUE') {
            echo $methodName . "<br>";
            if (is_array($methodDetail['parameters'])) {
                foreach ($methodDetail['parameters'] as $key => $value) {
                    echo $key . " : " . $value . "<br>";
                }
            }
            echo (($result == 1) ? 'PASSED' : 'FAILED') . "<br>";
            echo "------------------<br><br>";
        }
        if (!in_array(VERBOSE_SYSTEMCHECKER, array('silent', 'none', 'off', 'OFF', 'NONE', 'SILENT'))) {
            logDetails((($result == 1) ? 'PASSED' : 'FAILED') .
                (isset($methodDetail['parameters']) ? substr(print_r($methodDetail['parameters'], TRUE), 5) : ''),
                $methodName);
        }
    }

    function checkIsZCVersionCurrent()
    {
        $new_version = TEXT_VERSION_CHECK_CURRENT; //set to "current" by default
        $lines = @file(NEW_VERSION_CHECKUP_URL . '?v=' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '&p=' . PHP_VERSION . '&a=' . $_SERVER['SERVER_SOFTWARE'] . '&r=' . urlencode($_SERVER['HTTP_HOST']) . '&m=zc_install');
        if (empty($lines)) return true;

        //check for major/minor version info
        if ((trim($lines[0]) > PROJECT_VERSION_MAJOR) || (trim($lines[0]) == PROJECT_VERSION_MAJOR && trim($lines[1]) > PROJECT_VERSION_MINOR)) {
            $new_version = TEXT_VERSION_CHECK_NEW_VER . trim($lines[0]) . '.' . trim($lines[1]) . ' :: ' . $lines[2];
        }
        //check for patch version info
        // first confirm that we're at latest major/minor -- otherwise no need to check patches:
        if (trim($lines[0]) == PROJECT_VERSION_MAJOR && trim($lines[1]) == PROJECT_VERSION_MINOR) {
            //check to see if either patch needs to be applied
            if (trim($lines[3]) > intval(PROJECT_VERSION_PATCH1) || trim($lines[4]) > intval(PROJECT_VERSION_PATCH2)) {
                // reset update message, since we WILL be advising of an available upgrade
                if ($new_version == TEXT_VERSION_CHECK_CURRENT) $new_version = '';
                //check for patch #1
                if (trim($lines[3]) > intval(PROJECT_VERSION_PATCH1)) {
                    // if ($new_version != '') $new_version .= '<br />';
                    $new_version .= (($new_version != '') ? '<br />' : '') .
                        '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($lines[0]) . '.' . trim($lines[1]) . ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($lines[3]) . '] :: ' . $lines[5] . '</span>';
                }
                if (trim($lines[4]) > intval(PROJECT_VERSION_PATCH2)) {
                    // if ($new_version != '') $new_version .= '<br />';
                    $new_version .= (($new_version != '') ? '<br />' : '') .
                        '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($lines[0]) . '.' . trim($lines[1]) . ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($lines[4]) . '] :: ' . $lines[5] . '</span>';
                }
            }
        }
        // prepare displayable download link
        if ($new_version != '' && $new_version != TEXT_VERSION_CHECK_CURRENT) {
            $new_version .= '<a href="' . $lines[6] . '" rel="noopener" target="_blank">' . TEXT_VERSION_CHECK_DOWNLOAD . '</a>';
            $this->localErrors = $new_version;
            return FALSE;
        }
        return TRUE;
    }

    /**
     * add current user IP to allowed-in-maintenance list
     */
    public function updateAdminIpList()
    {
        if (isset($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 4) {
            $checkip = $_SERVER['REMOTE_ADDR'];

            $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
            $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
            $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
            $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
            $dbPrefixVal = $this->getServerConfig()->getDefine('DB_PREFIX');
            require_once(DIR_FS_ROOT . 'includes/classes/db/mysql/query_factory.php');
            $db = new queryFactory();
            $db->simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
            $db->selectdb($dbNameVal);

            $sql = "select configuration_value from " . $dbPrefixVal . "configuration where configuration_key = 'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE'";
            $result = $db->Execute($sql);
            if (!strstr($result->fields['configuration_value'], $checkip)) {
                $newip = $result->fields['configuration_value'] . ',' . $checkip;
                $sql = "update " . $dbPrefixVal . "configuration set configuration_value = '" . $db->prepare_input($newip) . "' where configuration_key = 'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE'";
                $db->Execute($sql);
            }
        }
    }
}
