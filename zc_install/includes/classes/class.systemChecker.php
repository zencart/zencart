<?php
/**
 * file contains systemChecker Class
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 29 Modified in v2.1.0-alpha2 $
 */

/**
 * systemChecker Class
 */
class systemChecker
{
    protected array $adminDirectoryList = [];
    protected array $errorList = [];
    protected array $extraRunLevels = [];
    protected ?string $localErrors;
    protected string $selectedAdminDir;
    protected zcConfigureFileReader $serverConfig;
    protected array $systemChecks;

    public function __construct($selectedAdminDir = 'UNSPECIFIED')
    {
        $this->adminDirectoryList = self::getAdminDirectoryList();
        $res = sfYaml::load(DIR_FS_INSTALL . 'includes/systemChecks.yml');
        $this->systemChecks = $res['systemChecks'];
        $this->extraRunLevels = [];

        if (file_exists(DIR_FS_ROOT . 'includes/local/configure.php')) {
            $this->extraRunLevels[] = 'localdev';
        }

        if ($selectedAdminDir === 'UNSPECIFIED' || empty($selectedAdminDir) || !file_exists(DIR_FS_ROOT . $selectedAdminDir)) {
            if (count($this->adminDirectoryList) === 1) {
                $selectedAdminDir = $this->adminDirectoryList[0];
            }
        }
        $this->selectedAdminDir = $selectedAdminDir;
    }

    public static function getAdminDirectoryList(): array
    {
        $adminDirectoryList = [];

        $ignoreArray = [
            '.',
            '..',
            'cache',
            'logs',
            'installer',
            'zc_install',
            'includes',
            'testFramework',
            'editors',
            'extras',
            'images',
            'docs',
            'pub',
            'email',
            'download',
            'media',
        ];
        $d = @dir(DIR_FS_ROOT);
        while (false !== ($entry = $d->read())) {
            if (is_dir(DIR_FS_ROOT . $entry) && !in_array($entry, $ignoreArray, false)) {
                // uses banner_manager.php as indicator that this tree is an "admin" dir
                if (file_exists(DIR_FS_ROOT . $entry . '/' . 'banner_manager.php')) {
                    $adminDirectoryList[] = $entry;
                }
            }
        }
        return $adminDirectoryList;
    }

    public function runTests($runLevel = 'always'): array
    {
        $runLevels = array_merge([$runLevel], $this->extraRunLevels);
        $this->errorList = [];
//echo print_r($this->systemChecks);
        foreach ($this->systemChecks as $systemCheckName => $systemCheck) {
//echo print_r($systemCheck);

            $server = strtolower($_SERVER['SERVER_SOFTWARE'] ?? 'unknown');

            // check for bypasses
            if (isset($systemCheck['skipWhen'])) {
                $parts = explode('=', $systemCheck['skipWhen']);
                $what = $parts[0];
                $when = strtolower($parts[1] ?? '');

                if ($what === 'server' && str_contains($when, 'apache') && str_starts_with($server, 'apache')) {
                    continue;
                }
                if ($what === 'server' && str_contains($when, 'nginx') && str_starts_with($server, 'nginx')) {
                    continue;
                }
                if ($what === 'server' && str_contains($when, 'litespeed') && str_starts_with($server, 'nginx')) {
                    continue;
                }
            } elseif (isset($systemCheck['onlyWhen'])) {
                $parts = explode('=', $systemCheck['onlyWhen']);
                $what = $parts[0];
                $when = strtolower($parts[1] ?? '');

                $skip = true;
                if ($what === 'server' && str_contains($when, 'apache') && str_starts_with($server, 'apache')) {
                    $skip = false;
                }
                if ($what === 'server' && str_contains($when, 'nginx') && str_starts_with($server, 'nginx')) {
                    $skip = false;
                }
                if ($what === 'server' && str_contains($when, 'litespeed') && str_starts_with($server, 'litespeed')) {
                    $skip = false;
                }
                if ($skip) {
                    continue;
                }
            }

            if (in_array($systemCheck['runLevel'], $runLevels, false)) {
                $resultCombined = true;
                $criticalError = false;
                foreach ($systemCheck['methods'] as $methodName => $methodDetail) {
                    $this->localErrors = null;
                    if (isset($methodDetail['method'])) {
                        $methodName = $methodDetail['method'];
                    }
                    $result = $this->{$methodName}($methodDetail['parameters'] ?? null);
                    $resultCombined &= $result;
                    if ($result === false && (isset($this->systemChecks[$systemCheckName]['criticalError']))) {
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
                if ($criticalError) {
                    break 1;
                }
            }
        }
        return $this->errorList;
    }

    public function log($result, $methodName, $methodDetail): void
    {
        $status = $result;
        if (is_bool($result)) {
            $status = ($result === true) ? 'PASSED' : 'FAILED';
        }
        if (VERBOSE_SYSTEMCHECKER === 'screen' || VERBOSE_SYSTEMCHECKER === true || VERBOSE_SYSTEMCHECKER === 'TRUE') {
            echo $methodName . "<br>";
            if (is_array($methodDetail['parameters'])) {
                foreach ($methodDetail['parameters'] as $key => $value) {
                    echo $key . " : " . $value . "<br>";
                }
            }
            echo $status . "<br>";
            echo "------------------<br><br>";
        }
        if (!in_array(VERBOSE_SYSTEMCHECKER, ['silent', 'none', 'off', 'OFF', 'NONE', 'SILENT'])) {
            $loggedString = isset($methodDetail['parameters']) ? substr(print_r($methodDetail['parameters'], true), 5) : '';
            logDetails($status . $loggedString, $methodName);
        }
    }

    public function getErrorList($condition = 'FAIL'): array
    {
        $result = false;
        $resultList = [];
        foreach ($this->errorList as $entry) {
            if ($entry['errorLevel'] === $condition) {
                $result = true;
                $resultList[] = $entry;
            }
        }
        return [$result, $resultList];
    }

    public function hasTables(): bool
    {
        $result = false;
        if ($this->hasSaneConfigFile()) {
            $parameters = [
                [
                    'checkType' => 'fieldSchema',
                    'tableName' => 'admin',
                    'fieldName' => 'admin_id',
                    'fieldCheck' => 'Type',
                    'expectedResult' => 'INT(11)',
                ],
            ];
            $result = $this->dbVersionChecker($parameters);
        }
        return $result;
    }

    public function hasSaneConfigFile(): bool
    {
        $result = false;
        if ($this->getServerConfig()->fileLoaded()) {
            $httpServerVal = $this->getServerConfig()->getDefine('HTTP_SERVER');
            $fsCatalogVal = $this->getServerConfig()->getDefine('DIR_FS_CATALOG');
            $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
            if ($httpServerVal !== '' && $fsCatalogVal !== '' && $dbUserVal !== '') {
                $result = true;
            }
        }
        $this->log(($result ? 'TRUE' : 'FALSE'), __METHOD__, []);
        return $result;
    }

    public function configFileExists(): bool
    {
        $this->checkWriteableAdminFile(['fileDir' => DIR_FS_ROOT . 'includes/configure.php', 'createFile' => true, 'changePerms' => '0664']);
        $this->checkWriteableFile(['fileDir' => DIR_FS_ROOT . 'includes/configure.php', 'createFile' => true, 'changePerms' => '0664']);
        return $this->getServerConfig()->fileExists();
    }

    public function getServerConfig(): ?zcConfigureFileReader
    {
        if (!isset($this->serverConfig)) {
            $configFile = DIR_FS_ROOT . 'includes/configure.php';
            $configFileLocal = DIR_FS_ROOT . 'includes/local/configure.php';
            if (file_exists($configFileLocal)) {
                $configFile = $configFileLocal;
            }
            $this->serverConfig = new zcConfigureFileReader($configFile);
        }
        return $this->serverConfig;
    }

    public function dbVersionChecker($parameters): bool
    {
        // queryFactory depends on mysqli_ functions
        if (!function_exists('mysqli_connect')) {
            return false;
        }

        if (!$this->getServerConfig()->fileLoaded()) {
            return false;
        }
        $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
        $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
        $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
        $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
        $dbPrefixVal = $this->getServerConfig()->getDefine('DB_PREFIX');

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
        if ($result === false) {
            return false;
        }
        $valid = true;
        foreach ($parameters as $parameter) {
            $method = 'dbVersionCheck' . ucfirst($parameter['checkType']);
            $result = $this->$method($db, $dbPrefixVal, $parameter);
//echo ($parameter['tableName'] ? $parameter['tableName'] . '-' : '') . $parameter['checkType'] . ': ' . var_export($result, true) . '<br>' . "\n";
            $valid = $valid && $result;
        }

//echo 'Valid: ' . var_export($valid, true) . '<br>' . "\n";
        return $valid;
    }

    public function hasUpdatedConfigFile(): bool
    {
        if ($this->getServerConfig()->fileLoaded()) {
            // if the new define added in v155 is present, then this deems the file to be already updated
            $sessionStorage = $this->getServerConfig()->getDefine('SESSION_STORAGE');
            if (isset($sessionStorage)) {
                return true;
            }
        }
        return false;
    }

    public function removeConfigureErrors(): array
    {
        $listFatalErrors = [];
        foreach ($this->errorList as $key => $value) {
            if ($key !== 'checkStoreConfigureFile' && $key !== 'checkAdminConfigureFile') {
                if ($value['errorLevel'] === 'FAIL') {
                    $listFatalErrors[$key] = $value;
                }
            }
        }
        $hasFatalErrors = count($listFatalErrors) > 0;
        return ([$hasFatalErrors, $listFatalErrors]);
    }

    public function getDbConfigOptions(): array
    {
        $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
        $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
        $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
        $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
        $dbPrefixVal = $this->getServerConfig()->getDefine('DB_PREFIX');
        $dbCharsetVal = $this->getServerConfig()->getDefine('DB_CHARSET');
        $dbTypeVal = $this->getServerConfig()->getDefine('DB_TYPE');

        return [
            'db_host' => $dbServerVal,
            'db_user' => $dbUserVal,
            'db_password' => $dbPasswordVal,
            'db_name' => $dbNameVal,
            'db_charset' => $dbCharsetVal,
            'db_prefix' => $dbPrefixVal,
            'db_type' => $dbTypeVal,
        ];
    }

    public function findCurrentDbVersion(): ?string
    {
        $version = null;
        foreach ($this->systemChecks as $systemCheckName => $systemCheck) {
            $version = null;
            if ($systemCheck['runLevel'] === 'dbVersion') {
                $resultCombined = true;
                if (!isset($systemCheck['methods'])) {
                    $systemCheck['methods'] = [];
                }
                foreach ($systemCheck['methods'] as $methodName => $methodDetail) {
                    if (isset($methodDetail['method'])) {
                        $methodName = $methodDetail['method'];
                    }
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
                if (isset($version)) {
                    break;
                }
            }
        }

        $this->log((!empty($version) ? $version : 'Cannot Be Determined'), __METHOD__, []);

//echo print_r($this->errorList);
        return $version;
    }

    public function dbVersionCheckFieldSchema($db, $dbPrefix, $parameters): bool
    {
        $sql = "SHOW FIELDS FROM " . $dbPrefix . $parameters['tableName'];
        $result = $db->Execute($sql);
        while (!$result->EOF) {
            // if found the specified field ...
            if ($result->fields['Field'] === $parameters['fieldName']) {
                // then return true if the test was simply "Exists"
                if ($parameters['fieldCheck'] === 'Exists') {
                    return true;
                }

                // else check that the field's type matches the fieldCheck test
                $expected = strtoupper($parameters['expectedResult']);
                if (strtoupper($result->fields[$parameters['fieldCheck']]) === $expected) {
                    return true;
                }
                // Accommodate MySQL 8.0.17+ case where "INT(11)" only returns "INT", except for TINYINT(1)
                $found = preg_replace('~INT\([\d]*\)~', 'INT', strtoupper($result->fields[$parameters['fieldCheck']]));
                $expected = preg_replace('~INT\([\d]*\)~', 'INT', $expected);
                if ($expected !== 'TINYINT(1)' && $found === $expected) {
                    return true;
                }
            }
            $result->MoveNext();
        }
        return false;
    }

    public function dbVersionCheckConfigKeyExists($db, $dbPrefix, $parameters): bool
    {
        $sql = "SELECT configuration_key FROM " . $dbPrefix . "configuration WHERE configuration_key = '" . $parameters['keyName'] . "'";
        $result = $db->Execute($sql, 1);

        return $result->RecordCount() > 0;
    }

    public function dbVersionCheckConfigValue($db, $dbPrefix, $parameters): bool
    {
        $sql = "SELECT configuration_title FROM " . $dbPrefix . "configuration WHERE configuration_key = '" . $parameters['fieldName'] . "'";
        $result = $db->Execute($sql);
        if ($result && isset($result->fields['configuration_title'])) {
            return $result->fields['configuration_title'] === $parameters['expectedResult'];
        }
        return false;
    }

    public function dbVersionCheckConfigDescription($db, $dbPrefix, $parameters): bool
    {
        $sql = "SELECT configuration_description FROM " . $dbPrefix . "configuration WHERE configuration_key = '" . $parameters['fieldName'] . "'";
        $result = $db->Execute($sql);
        if ($result && isset($result->fields['configuration_description'])) {
            // intentionally using == here
            return $result->fields['configuration_description'] == $parameters['expectedResult'];
        }
        return false;
    }

    public function checkFileExists($parameters): bool
    {
        return file_exists($parameters['fileDir']);
    }

    public function checkWriteableDir($parameters): bool
    {
        return is_writable($parameters['fileDir']);
    }

    public function checkWriteableFile($parameters): bool
    {
        if (isset($parameters['changePerms']) && $parameters['changePerms'] !== false) {
            if (file_exists($parameters['fileDir'])) {
                @chmod($parameters['fileDir'], octdec($parameters['changePerms']));
            } elseif ($fp = @fopen($parameters['fileDir'], 'c')) {
                fclose($fp);
                chmod($parameters['fileDir'], octdec($parameters['changePerms']));
            }
        }
        return is_writable($parameters['fileDir']);
    }

    public function checkWriteableAdminFile($parameters): bool
    {
        $file = DIR_FS_ROOT . $this->selectedAdminDir . '/' . $parameters['fileDir'];
        if (is_writable($file)) {
            return true;
        }
        if (!file_exists($file) && $fp = @fopen($file, 'c')) {
            fclose($fp);
        }
        if (file_exists($file)) {
            if (isset($parameters['changePerms']) && $parameters['changePerms'] !== false) {
                @chmod($file, octdec($parameters['changePerms']));
            }
            if (is_writable($file)) {
                return true;
            }
        }
        logDetails($file, 'ADMIN FILE TEST');
        return false;
    }

    public function checkExtension($parameters): bool
    {
        return extension_loaded($parameters['extension']);
    }

    public function checkFunctionExists($parameters): bool
    {
        return function_exists($parameters['functionName']);
    }

    public function checkPhpVersion($parameters): bool|int
    {
        return version_compare((string)PHP_VERSION, (string)$parameters['version'], (string)$parameters['versionTest']);
    }

    public function checkHtaccessSupport($parameters): bool
    {
        if (!function_exists('curl_init')) { // can't test if this fails
            return true;
        }

        if (false !== stripos($_SERVER['SERVER_SOFTWARE'], "nginx")) { // not relevant if nginx
            return true;
        }

        global $request_type;
        $tests = [];

        $testPath = preg_replace('~/zc_install.*$~', '/includes/filenames.php', $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']);
        // first element added to the $tests array is based on $request_type
        $tests[] = ($request_type === 'SSL' ? 'https://' : 'http://') . $testPath;
        // add inverse test as fallback
        $tests[] = ($request_type === 'SSL' ? 'http://' : 'https://') . $testPath;

        foreach ($tests as $test) {
            $resultCurl = self::curlGetUrl($test, false);
            if (isset($resultCurl['http_code']) && (int)$resultCurl['http_code'] === 403) {
                return true;
            }
            // test again with redirects enabled
            $resultCurl = self::curlGetUrl($test, true);
            if (isset($resultCurl['http_code']) && (int)$resultCurl['http_code'] === 403) {
                return true;
            }
        }

        return false;
    }

    public static function curlGetUrl($url, $follow_redirects = false): array
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => false,        // don't return headers
            CURLOPT_FOLLOWLOCATION => $follow_redirects,    // follow redirects
            CURLOPT_ENCODING => "",         // handle all encodings
            CURLOPT_AUTOREFERER => true,    // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 3,    // timeout on connect
            CURLOPT_TIMEOUT => 3,           // timeout on response
            CURLOPT_MAXREDIRS => 10,        // stop after 10 redirects
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        if ($header === false) {
            $header = [];
        }
        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;
        return $header;
    }

    public function checkInitialSession($parameters): bool
    {
        session_name($parameters['sessionName']);
        $result = @session_start();
        if (!$result) {
            return false;
        }
//    if (defined('SID') && constant('SID') != "")
//      return FALSE;
//    if (session_status() == PHP_SESSION_DISABLED)
//      return FALSE;
        $_SESSION['testSession'] = 'testSession';
        return true;
    }

    public function checkUpgradeDBConnection($parameters): bool
    {
        if (!function_exists('mysqli_connect')) {
            return false;
        }

        $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
        $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
        $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
        $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');

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

        return $result;
    }

    public function checkDBConnection($parameters): bool
    {
        if (! function_exists('mysqli_connect')) {
            return false;
        }
        $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
        $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
        $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
        $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');

        $db = new queryFactory();
        $result = @$db->simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
        if ((int)$db->error_number !== 2002) {
            return true;
        }
        return $result;
    }

    public function checkNewDBConnection($parameters): bool|queryFactoryResult
    {

        $db = new queryFactory();
        $result = $db->simpleConnect(zcRegistry::getValue('db_host'), zcRegistry::getValue('db_user'), zcRegistry::getValue('db_password'), zcRegistry::getValue('db_name'));
        if (!$result) {
            $this->localErrors = $db->error_number . ':' . $db->error_text;
            return false;
        }
        $result = $db->selectdb(zcRegistry::getValue('db_name'));
        if (!$result) {
            $sql = "CREATE DATABASE " . zcRegistry::getValue('db_name') . " CHARACTER SET " . zcRegistry::getValue('db_charset');
            $result = $db->Execute($sql);
            if ($result) { // success
                return true;
            }
            $this->localErrors = $db->error_number . ':' . $db->error_text;
        }
        return $result;
    }

    public function checkIniGet($parameters): bool
    {
        // NOTE: intentionally using loose comparison here:
        return @ini_get($parameters['inigetName']) == $parameters['expectedValue'];
    }

    public function checkLiveCurl($parameters): bool
    {
        if (!function_exists('curl_init')) {
            return false;
        }
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

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // This is intentionally set to FALSE within zc_install since this test is not about whether certificates are good.

        $result = curl_exec($ch);
        $errtext = curl_error($ch);
        $errnum = curl_errno($ch);
        $commInfo = @curl_getinfo($ch);
// error_log('CURL Connect: ' . $errnum . ' ' . $errtext . "\n" . print_r($commInfo, TRUE));
// error_log('CURL Response: ' . $result);
        curl_close($ch);
        if ($errnum !== 0 || trim($result) !== 'PASS') {
            return false;
        }

        return true;
    }

    public function checkHttpsRequest($parameters): bool
    {
        global $request_type;

        // Take full URI and ping https version of same to see if expected response comes back. If so, redirect install to https.
        // In case multiple-redirects happen on oddly-configured hosts, this can be bypassed by adding ?noredirect=1 to the URL
        if ($request_type !== 'SSL' && function_exists('curl_init') && !isset($_GET['noredirect'])) {
            $test_uri = 'https://' . str_replace(['http://', 'https://'], '', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            $resultCurl = self::curlGetUrl($test_uri);
            //error_log(print_r($resultCurl, true) . print_r($_SERVER, true));
            if (isset($resultCurl['http_code']) && $resultCurl['http_code'] === '200') {
                header('Location: ' . $test_uri);
                exit();
            }
        }
        // otherwise, return the https status so the inspector can report it along with suggestions
        return $request_type === 'SSL';
    }

    public function addRunlevel($runLevel): void
    {
        $this->extraRunLevels[] = $runLevel;
    }

    public function validateAdminCredentials($adminUser, $adminPassword): bool|int
    {
        $parameters = [
            [
                'checkType' => 'fieldSchema',
                'tableName' => 'admin',
                'fieldName' => 'admin_profile',
                'fieldCheck' => 'Type',
                'expectedResult' => 'INT(11)',
            ]
        ];
        $hasAdminProfiles = $this->dbVersionChecker($parameters);
        $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
        $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
        $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
        $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
        $dbPrefixVal = $this->getServerConfig()->getDefine('DB_PREFIX');

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
        if ($result === false) {
            return false;
        }

        $adminUser = $db->prepare_input($adminUser);
        $adminPassword = $db->prepare_input($adminPassword);

//    echo ($hasAdminProfiles) ? 'YES' : 'NO';
        if (!$hasAdminProfiles) {
            $sql = "SELECT admin_id, admin_name, admin_pass FROM " . $dbPrefixVal . "admin WHERE admin_name = '" . $adminUser . "'";
            $result = $db->Execute($sql);
            if ($result->EOF || $adminUser !== $result->fields['admin_name'] || !zen_validate_password($adminPassword, $result->fields['admin_pass'])) {
                return false;
            }
            return (int)$result->fields['admin_id'];
        }

// first check if the table has any superusers; if not, verify the user's password and assign them as a superuser
        $sql = "SELECT DISTINCT(admin_profile)
                FROM " . $dbPrefixVal . "admin
                ORDER BY admin_profile";
        $result = $db->Execute($sql);
        if ($result->EOF || ($result->RecordCount() === 1 && (int)$result->fields['admin_profile'] === 0)) {
            $sql = "SELECT admin_id, admin_name, admin_pass
                    FROM " . $dbPrefixVal . "admin
                    WHERE admin_name = '" . $adminUser . "'";
            $result = $db->Execute($sql);
            if (!$result->EOF && zen_validate_password($adminPassword, $result->fields['admin_pass'])) {
                $sql = "UPDATE " . $dbPrefixVal . "admin
                        SET admin_profile = 1
                        WHERE admin_id = " . $result->fields['admin_id'];
                $db->Execute($sql);
                return (int)$result->fields['admin_id'];
            }
        } else {
            $sql = "SELECT a.admin_id, a.admin_name, a.admin_pass, a.admin_profile
                    FROM " . $dbPrefixVal . "admin AS a
                    LEFT JOIN " . $dbPrefixVal . "admin_profiles AS ap ON a.admin_profile = ap.profile_id
                    WHERE a.admin_name = '" . $adminUser . "'
                    AND ap.profile_name = 'Superuser'";
            $result = $db->Execute($sql);
            if ($result->EOF || !zen_validate_password($adminPassword, $result->fields['admin_pass'])) {
                return false;
            }
            return (int)$result->fields['admin_id'];
        }
        return false;
    }

    function backupConfigureFiles($parameters): bool
    {
        return true;
    }

    function checkIsZCVersionCurrent(): bool
    {
        $new_version = TEXT_VERSION_CHECK_CURRENT; //set to "current" by default

        $url = NEW_VERSION_CHECKUP_URL . '?v=' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '&p=' . PHP_VERSION
            . '&a=' . $_SERVER['SERVER_SOFTWARE'] . '&r=' . urlencode($_SERVER['HTTP_HOST']) . '&m=zc_install';
        $lines = @file($url);

        // silently ignore if online check fails
        if (empty($lines)) {
            return true;
        }
        if (!in_array(trim($lines[0]), ['1', '2', '3'])) {
            return true;
        }

        //check for major/minor version info
        if ((trim($lines[0]) > PROJECT_VERSION_MAJOR) || (trim($lines[0]) === PROJECT_VERSION_MAJOR && trim($lines[1]) > PROJECT_VERSION_MINOR)) {
            $new_version = TEXT_VERSION_CHECK_NEW_VER . trim($lines[0]) . '.' . trim($lines[1]) . ' :: ' . $lines[2];
        }
        //check for patch version info
        // first confirm that we're at latest major/minor -- otherwise no need to check patches:
        if (trim($lines[0]) === PROJECT_VERSION_MAJOR && trim($lines[1]) === PROJECT_VERSION_MINOR) {
            //check to see if either patch needs to be applied
            if (trim($lines[3]) > (int)PROJECT_VERSION_PATCH1 || trim($lines[4]) > (int)PROJECT_VERSION_PATCH2) {
                // reset update message, since we WILL be advising of an available upgrade
                if ($new_version === TEXT_VERSION_CHECK_CURRENT) {
                    $new_version = '';
                }
                //check for patch #1
                if (trim($lines[3]) > (int)PROJECT_VERSION_PATCH1) {
                    // if ($new_version != '') $new_version .= '<br>';
                    $new_version .= (($new_version !== '') ? '<br>' : '') .
                        '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($lines[0]) . '.' . trim($lines[1]) .
                        ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($lines[3]) . '] :: ' . $lines[5] . '</span>';
                }
                if (trim($lines[4]) > (int)PROJECT_VERSION_PATCH2) {
                    // if ($new_version != '') $new_version .= '<br>';
                    $new_version .= (($new_version !== '') ? '<br>' : '') .
                        '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($lines[0]) . '.' . trim($lines[1]) .
                        ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($lines[4]) . '] :: ' . $lines[5] . '</span>';
                }
            }
        }

        $this->log('Present: ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '; Latest release online: ' . trim($lines[0]) . '.' . trim($lines[1]), __METHOD__, []);

        // prepare displayable download link
        if ($new_version !== '' && $new_version !== TEXT_VERSION_CHECK_CURRENT) {
            $new_version .= '<a href="' . $lines[6] . '" rel="noopener" target="_blank">' . TEXT_VERSION_CHECK_DOWNLOAD . '</a>';
            $this->localErrors = $new_version;
            return false;
        }
        return true;
    }

    /**
     * add current user IP to allowed-in-maintenance list
     */
    public function updateAdminIpList(): void
    {
        if (isset($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 4) {
            $checkip = $_SERVER['REMOTE_ADDR'];

            $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
            $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
            $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
            $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');
            $dbPrefixVal = $this->getServerConfig()->getDefine('DB_PREFIX');

            $db = new queryFactory();
            $db->simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
            $db->selectdb($dbNameVal);

            $sql = "select configuration_value from " . $dbPrefixVal . "configuration where configuration_key = 'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE'";
            $result = $db->Execute($sql);
            if (!str_contains($result->fields['configuration_value'], $checkip)) {
                $newip = $result->fields['configuration_value'] . ',' . $checkip;
                $sql = "UPDATE " . $dbPrefixVal . "configuration SET configuration_value = '" . $db->prepare_input($newip) . "'
                        WHERE configuration_key = 'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE'";
                $db->Execute($sql);
            }
        }
    }

    /**
     * Check installed Database version
     * The check is only validated if the information is available.
     * There are other checks that will fail so don't issue spurious error message
     */
    public function checkMysqlVersion($parameters): bool
    {
        if (!function_exists('mysqli_connect')) {
            // mysqli_connect not available don't fail test
            return true;
        }
        $dbServerVal = $this->getServerConfig()->getDefine('DB_SERVER');
        $dbNameVal = $this->getServerConfig()->getDefine('DB_DATABASE');
        $dbPasswordVal = $this->getServerConfig()->getDefine('DB_SERVER_PASSWORD');
        $dbUserVal = $this->getServerConfig()->getDefine('DB_SERVER_USERNAME');

        $db = new queryFactory();
        $result = $db->simpleConnect($dbServerVal, $dbUserVal, $dbPasswordVal, $dbNameVal);
        if ((int)$db->error_number === 2002) {
            // Cannot connect to database; don't fail check
            return true;
        }
        $version = $db->get_server_info();
        if ($version === 'UNKNOWN') {
            // versions not found don't fail check
            return true;
        }

        if (strripos($version, '-MariaDB') === false) {
            // mysql database check version
            $checkVersion = $parameters['mysqlVersion'];
        } else {
            // mariaDb Check version must remove -MariaDB from the version
            // as version compare treats -... as a lower version than N.N.N
            $version = substr($version, 0, strripos($version, '-MariaDB'));
            $checkVersion = $parameters['mariaDBVersion'];
        }
        return version_compare($version, $checkVersion) >= 0;
    }
}
