<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Wed May 11 17:34:47 2016 -0500 New in v1.5.5 $
 */

/**
 * Class AdminRequestSanitizer
 */
class AdminRequestSanitizer extends base
{
    /**
     * @var
     */
    private $doStrictSanitization;
    /**
     * @var array
     */
    private $getKeysAlreadySanitized;
    /**
     * @var array
     */
    private $postKeysAlreadySanitized;
    /**
     * @var
     */
    private $adminSanitizerTypes;
    /**
     * @var bool
     */
    private $debug = false;
    /**
     * @var array
     */
    private $debugMessages = array();
    /**
     * @var
     */
    private static $instance;

    /**
     * @var string
     */
    private $currentPage;
    /**
     * @var array
     */
    private $requestParameterList;

    /**
     * @return AdminRequestSanitizer
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new AdminRequestSanitizer();
        }
        return self::$instance;
    }

    /**
     * AdminRequestSanitizer constructor.
     */
    public function __construct()
    {
        global $PHP_SELF;
        $this->currentPage = basename($PHP_SELF, '.php');
        $this->requestParameterList = array();
        $this->adminSanitizerTypes = array();
        $this->doStrictSanitization = false;
        $this->getKeysAlreadySanitized = array();
        $this->postKeysAlreadySanitized = array();
        $this->debugMessages[] = 'Incoming GET Request ' . print_r($_GET, true);
        $this->debugMessages[] = 'Incoming POST Request ' . print_r($_POST, true);
    }

    /**
     * @param $sanitizerType
     * @param $sanitizerEntries
     */
    public function addSimpleSanitization($sanitizerType, $sanitizerEntries)
    {
        foreach ($sanitizerEntries as $sanitizerEntry) {
            $entryParameters = array('sanitizerType' => $sanitizerType, 'method' => 'both');
            $this->addRequestParameter($sanitizerEntry, $entryParameters);
        }
    }

    /**
     * @param $sanitizationEntries
     */
    public function addComplexSanitization($sanitizationEntries)
    {
        foreach ($sanitizationEntries as $requestParameter => $sanitizationEntry) {
            $this->addRequestParameter($requestParameter, $sanitizationEntry);
        }
    }

    /**
     * @param $sanitizertypes
     */
    public function addSanitizerTypes($sanitizertypes)
    {
        foreach ($sanitizertypes as $key => $sanitizertype) {
            $this->adminSanitizerTypes[$key] = $sanitizertype;
        }
    }

    /**
     * @return array
     */
    public function getGetKeysAlreadySanitized()
    {
        return $this->getKeysAlreadySanitized;
    }

    /**
     * @return array
     */
    public function getPostKeysAlreadySanitized()
    {
        return $this->postKeysAlreadySanitized;
    }

    /**
     * @param $parameterName
     */
    public function setPostKeyAlreadySanitized($parameterName)
    {
        $this->postKeysAlreadySanitized[] = $parameterName;
    }

    /**
     * @param $parameterName
     */
    public function setGetKeyAlreadySanitized($parameterName)
    {
        $this->getKeysAlreadySanitized[] = $parameterName;
    }

    /**
     * @param $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param $doStrictSanitize
     */
    public function setDoStrictSanitization($doStrictSanitize)
    {
        $this->doStrictSanitization = $doStrictSanitize;
    }

    /**
     *
     */
    public function runSanitizers()
    {
        $this->debugMessages[] = 'Running Admin Sanitizers';
        foreach ($this->requestParameterList as $parameterName => $parameterDefinitions) {
            $result = $this->findSanitizerFromContext($parameterDefinitions);
            if (!$result) {
                $result = $this->findSanitizerFromRequestMethod($parameterName, $parameterDefinitions);
            }
            if ($result) {
                $this->runSpecificSanitizer($parameterName, $result);
            }
        }
        if ($this->doStrictSanitization) {
            $this->filterStrictSanitizeKeys();
            $this->filterStrictSanitizeValues();
        }
        $this->debugMessages[] = 'Outgoing GET Request ' . print_r($_GET, true);
        $this->debugMessages[] = 'Outgoing POST Request ' . print_r($_POST, true);
        if ($this->debug) {
            $this->errorLog($this->debugMessages);
        }
    }

    /**
     * @param $paramaterName
     * @param $parameterDefinition
     */
    private function runSpecificSanitizer($paramaterName, $parameterDefinition)
    {
        if ($this->adminSanitizerTypes[$parameterDefinition['sanitizerType']]['type'] === 'builtin') {
            $this->processBuiltIn($parameterDefinition['sanitizerType'], $paramaterName, $parameterDefinition);
        }
        if ($this->adminSanitizerTypes[$parameterDefinition['sanitizerType']]['type'] === 'custom') {
            $this->processCustom($parameterDefinition['sanitizerType'], $paramaterName, $parameterDefinition);
        }
    }

    /**
     * @param $parameterDefinitions
     * @return bool
     */
    private function findSanitizerFromContext($parameterDefinitions)
    {
        foreach ($parameterDefinitions as $parameterDefinition) {
            $result = false;
            if (count($parameterDefinition['pages'])) {
                if (in_array($this->currentPage, $parameterDefinition['pages'])) {
                    $result = $parameterDefinition;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param $parameterName
     * @param $parameterDefinitions
     * @return bool]
     */
    private function findSanitizerFromRequestMethod($parameterName, $parameterDefinitions)
    {
        foreach ($parameterDefinitions as $parameterDefinition) {
            $result = false;
            if (count($parameterDefinition['pages'])) {
                continue;
            }
            if ($this->parameterExistsForMethod($parameterName, $parameterDefinition)) {
                $result = $parameterDefinition;
                break;
            }
        }
        return $result;
    }

    /**
     * @param $parameterName
     * @param $parameterDefinition
     * @return bool
     */
    private function parameterExistsForMethod($parameterName, $parameterDefinition)
    {
        $hasGet = isset($_GET[$parameterName]) ? true : false;
        $hasPost = isset($_POST[$parameterName]) ? true : false;
        if ($parameterDefinition['method'] == 'both' && ($hasGet || $hasPost)) {
            return true;
        }
        if ($parameterDefinition['method'] == 'get' && $hasGet) {
            return true;
        }
        if ($parameterDefinition['method'] == 'post' && $hasPost) {
            return true;
        }
        return false;
    }

    /**
     * @param $requestParameter
     * @param $parameterDetail
     */
    private function addRequestParameter($requestParameter, $parameterDetail)
    {
        $pages = isset($parameterDetail['pages']) ? $parameterDetail['pages'] : null;
        $params = isset($parameterDetail['params']) ? $parameterDetail['params'] : null;
        $this->requestParameterList[$requestParameter][] = array(
            'sanitizerType' => $parameterDetail['sanitizerType'],
            'method' => $parameterDetail['method'],
            'pages' => $pages,
            'params' => $params
        );
    }

    /**
     * @param $sanitizerName
     */
    private function processBuiltIn($sanitizerName, $parameterName, $parameterDefinition)
    {
        $method = 'filter' . self::camelize(strtolower($sanitizerName), true);
        if (method_exists($this, $method)) {
            call_user_func(array($this, $method), $parameterName, $parameterDefinition);
        }
    }

    /**
     * @param $sanitizerName
     * @param $parameterName
     * @param $parameterDefinition
     */
    private function processCustom($sanitizerName, $parameterName, $parameterDefinition)
    {
        $func = $this->adminSanitizerTypes[$parameterDefinition['sanitizerType']]['function'];
        $this->debugMessages[] = 'SANITIZER CUSTOM == ' . $sanitizerName;
        $func($this, $parameterName);
    }

    /**
     * @param $parameterName
     */
    private function filterNullAction($parameterName)
    {
        if (isset($_GET[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING NULL ACTION(GET) == ' . $parameterName;
            $this->getKeysAlreadySanitized[] = $parameterName;
        }
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING NULL ACTION(POST) == ' . $parameterName;
            $this->postKeysAlreadySanitized[] = $parameterName;
        }

    }

    /**
     * @param $parameterName
     */
    private function filterSimpleAlphanumPlus($parameterName)
    {
        if (isset($_GET[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING SIMPLE_ALPHANUM_PLUS(GET) == ' . $parameterName;
            $this->getKeysAlreadySanitized[] = $parameterName;
            $_GET[$parameterName] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_GET[$parameterName]);
        }
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING SIMPLE_ALPHANUM_PLUS(POST) == ' . $parameterName;
            $this->postKeysAlreadySanitized[] = $parameterName;
            $_POST[$parameterName] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_POST[$parameterName]);
        }
    }

    /**
     * @param $parameterName
     */
    private function filterConvertInt($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING CONVERT_INT (POST) == ' . $parameterName;
            $_POST[$parameterName] = (int)$_POST[$parameterName];
            $this->postKeysAlreadySanitized[] = $parameterName;
        }
        if (isset($_GET[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING CONVERT_INT (GET) == ' . $parameterName;
            $_GET[$parameterName] = (int)$_GET[$parameterName];
            $this->getKeysAlreadySanitized[] = $parameterName;

        }
    }

    /**
     * @param $parameterName
     */
    private function filterFileDirRegex($parameterName)
    {
        $filedirRegex = '~[^0-9a-z' . preg_quote('.!@#$%^& ()`_+-~/' . '\\', '~') . ']~i';
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING FILE_DIR_REGEX == ' . $parameterName;
            $_POST[$parameterName] = preg_replace($filedirRegex, '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $parameterName;
        }

    }

    /**
     * @param $parameterName
     */
    private function filterAlphanumDashUnderscore($parameterName)
    {
        $alphaNumDashUnderscore = '/[^a-z0-9_-]/i';
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING ALPHANUM_DASH_UNDERSCORE (POST) == ' . $parameterName;
            $_POST[$parameterName] = preg_replace($alphaNumDashUnderscore, '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $parameterName;
        }
        if (isset($_GET[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING ALPHANUM_DASH_UNDERSCORE (GET) == ' . $parameterName;
            $_GET[$parameterName] = preg_replace($alphaNumDashUnderscore, '', $_GET[$parameterName]);
            $this->getKeysAlreadySanitized[] = $parameterName;

        }
    }

    /**
     * @param $parameterName
     */
    private function filterWordsAndSymbolsRegex($parameterName)
    {
        $prodNameRegex = '~<\/?scri|on(load|mouse|error|read|key)(up|down)? ?=|[^(class|style)] ?= ?(\(|")|<!~i';
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING WORDS_AND_SYMBOLS_REGEX (POST) == ' . $parameterName;
            $_POST[$parameterName] = preg_replace($prodNameRegex, '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $parameterName;
        }
        if (isset($_GET[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING WORDS_AND_SYMBOLS_REGEX (GET) == ' . $parameterName;
            $_GET[$parameterName] = preg_replace($prodNameRegex, '', $_GET[$parameterName]);
            $this->getKeysAlreadySanitized[] = $parameterName;
        }
    }

    /**
     * @param $parameterName
     */
    private function filterProductDescRegex($parameterName)
    {
        $prodDescRegex = '~(load=|= ?\(|<![^-])~i';
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING PRODUCT_DESC_REGEX == ' . $parameterName;
            if (is_array($_POST[$parameterName])) {
                foreach ($_POST[$parameterName] as $pKey => $pValue) {
                    $_POST[$parameterName][$pKey] = preg_replace($prodDescRegex, '', $_POST[$parameterName][$pKey]);
                    $this->postKeysAlreadySanitized[] = $parameterName;
                }
            } else {
                $_POST[$parameterName] = preg_replace($prodDescRegex, '', $_POST[$parameterName]);
                $this->postKeysAlreadySanitized[] = $parameterName;
            }
        }
    }

    /**
     * @param $parameterName
     */
    private function filterMetaTags($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING META_TAGS == ' . $parameterName;
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $_POST[$parameterName][$pKey] = htmlspecialchars($_POST[$parameterName][$pKey], ENT_COMPAT, 'utf-8', false);
                $this->postKeysAlreadySanitized[] = $parameterName;
            }
        }
    }

    /**
     * @param $parameterName
     */
    private function filterSanitizeEmail($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING SANITIZE_EMAIL (POST) == ' . $parameterName;
            $result = filter_var($_POST[$parameterName], FILTER_SANITIZE_EMAIL);
            $_POST[$parameterName] = $result;
            $this->postKeysAlreadySanitized[] = $parameterName;
        }
        if (isset($_GET[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING SANITIZE_EMAIL (GET) == ' . $parameterName;
            $result = filter_var($_GET[$parameterName], FILTER_SANITIZE_EMAIL);
            $_GET[$parameterName] = $result;
            $this->getKeysAlreadySanitized[] = $parameterName;
        }
    }

    /**
     * @param $parameterName
     */
    private function filterSanitizeEmailAudience($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING SANITIZE_EMAIL_AUDIENCE (POST) == ' . $parameterName;
            $result = htmlspecialchars($_POST[$parameterName]);
            $_POST[$parameterName] = $result;
            $this->postKeysAlreadySanitized[] = $parameterName;
        }
    }

    /**
     * @param $parameterName
     */
    private function filterProductUrlRegex($parameterName)
    {
        $urlRegex = '~([^a-z0-9\'!#$&%@();:/=?_\~\[\]-]|[><])~i';
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING PRODUCT_URL_REGEX == ' . $parameterName;
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $newValue = filter_var($_POST[$parameterName][$pKey], FILTER_SANITIZE_URL);
                if ($newValue === false) {
                    $newValue = preg_replace($urlRegex, '', $_POST[$parameterName][$pKey]);
                }
                $_POST[$parameterName][$pKey] = $newValue;
                $this->postKeysAlreadySanitized[] = $parameterName;
            }
        }
    }

    /**
     * @param $parameterName
     */
    private function filterCurrencyValueRegex($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING CURRENCY_VALUE_REGEX == ' . $parameterName;
            $_POST[$parameterName] = preg_replace('/[^a-z0-9_,\.\-]/i', '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $parameterName;
        }
    }

    /**
     * @param $parameterName
     */
    private function filterFloatValueRegex($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING FLOAT_VALUE_REGEX == ' . $parameterName;
            $_POST[$parameterName] = preg_replace('/[^0-9,\.\-\+]/', '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $parameterName;
        }
    }

    /**
     * @param $parameterName
     * @param $parameterDefinition
     */
    private function filterMultiDimensional($parameterName, $parameterDefinition)
    {
        $requestPost = $_POST;
        if (!isset($requestPost[$parameterName])) {
            return;
        }
        foreach ($requestPost[$parameterName] as $key => $value) {
            $hacked = $requestPost[$parameterName][$key];
            if (isset($parameterDefinition['params'][$parameterName])) {
                unset($requestPost[$parameterName][$key]);
                unset($_POST);
                $_POST[$parameterName] = $key;
                $type = $parameterDefinition['params'][$parameterName]['sanitizerType'];
                $params = isset($parameterDefinition['params'][$parameterName]['params']) ? $parameterDefinition['params'][$parameterName]['params'] : null;
                $newParameterDefinition = array('sanitizerType' => $type, 'params' => $params);
                $this->runSpecificSanitizer($parameterName, $newParameterDefinition);
                $newKey = $_POST[$parameterName];
                $requestPost[$parameterName][$newKey] = $hacked;
            }
            foreach ($hacked as $pkey => $pvalue) {
                if (isset($parameterDefinition['params'][$pkey])) {
                    unset($requestPost[$parameterName][$newKey][$pkey]);
                    unset($_POST);
                    $_POST[$pkey] = $pvalue;
                    $type = $parameterDefinition['params'][$pkey]['sanitizerType'];
                    $params = isset($parameterDefinition['params'][$pkey]['params']) ? $parameterDefinition['params'][$pkey]['params'] : null;
                    $newParameterDefinition = array('sanitizerType' => $type, 'params' => $params);
                    $this->runSpecificSanitizer($pkey, $newParameterDefinition);
                    $requestPost[$parameterName][$newKey][$pkey] = $_POST[$pkey];
                }
            }

        }
        $_POST = $requestPost;
    }

    /**
     * @param $parameterName
     */
    private function filterProductNameDeepRegex($parameterName)
    {
        $prodNameRegex = '~<\/?scri|on(load|mouse|error|read|key)(up|down)? ?=|[^(class|style)] ?= ?(\(|")|<!~i';
        if (isset($_POST[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING PRODUCT_NAME_DEEP_REGEX == ' . $parameterName;
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $_POST[$parameterName][$pKey] = preg_replace($prodNameRegex, '', $_POST[$parameterName][$pKey]);
                $this->postKeysAlreadySanitized[] = $parameterName;
            }
        }
    }

    /**
     *
     */
    private function filterStrictSanitizeValues()
    {
        $this->addParamsToIgnore('STRICT_SANITIZE_VALUES');
        $postToIgnore = $this->getPostKeysAlreadySanitized();
        $getToIgnore = $this->getGetKeysAlreadySanitized();
        $this->traverseStrictSanitize($_POST, $postToIgnore, false, 'post');
        $this->traverseStrictSanitize($_GET, $getToIgnore, false, 'get');
    }

    /**
     * @param $item
     * @param $ignore
     * @param bool|false $inner
     * @return mixed
     */
    private function traverseStrictSanitize(&$item, $ignore, $inner, $type)
    {
        foreach ($item as $k => $v) {
            if ($inner || (!$inner && !in_array($k, $ignore))) {
                if (is_array($v)) {
                    $item[$k] = $this->traverseStrictSanitize($v, $ignore, true, $type);
                } else {
                    $this->debugMessages[] = 'PROCESSING STRICT_SANITIZE_VALUES == ' . $k;
                    $item[$k] = htmlspecialchars($item[$k]);
                }
            }
            if (!$inner) {
                if ($type == 'post') {
                    if (!in_array($k, $this->postKeysAlreadySanitized)) {
                        $this->postKeysAlreadySanitized[] = $k;
                    }
                }
                if ($type == 'get') {
                    if (!in_array($k, $this->getKeysAlreadySanitized)) {
                        $this->getKeysAlreadySanitized[] = $k;
                    }
                }
            }
        }
        return $item;
    }

    /**
     * @param $group
     */
    private function addParamsToIgnore($group)
    {
        foreach ($this->requestParameterList as $key => $details) {
            foreach ($details as $detail) {
                if ($detail['sanitizerType'] == $group) {
                    if ($detail['method'] == 'both') {
                        $this->addKeyAlreadySanitized('post', $key);
                        $this->addKeyAlreadySanitized('get', $key);
                    }
                    if ($detail['method'] == 'get') {
                        $this->addKeyAlreadySanitized('get', $key);
                    }
                    if ($detail['method'] == 'post') {
                        $this->addKeyAlreadySanitized('post', $key);
                    }
                }
            }
        }
    }

    /**
     *
     */
    private function filterStrictSanitizeKeys()
    {
        if (isset($_POST)) {
            foreach ($_POST as $key => $value) {
                if (preg_match('~[>/<]~', $key)) {
                    unset($_POST[$key]);
                }
            }
        }
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if (preg_match('~[>/<]~', $key)) {
                    unset($_GET[$key]);
                }
            }
        }
    }

    /**
     * @param $type
     * @param $key
     */
    private function addKeyAlreadySanitized($type, $key)
    {
        if ($type == 'post' && !in_array($key, $this->postKeysAlreadySanitized)) {
            $this->postKeysAlreadySanitized[] = $key;
        }
        if ($type == 'get' && !in_array($key, $this->getKeysAlreadySanitized)) {
            $this->getKeysAlreadySanitized[] = $key;
        }
    }

    /**
     * @param array $errorMessages
     */
    private function errorLog($errorMessages = array())
    {
        $logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;
        $message = date('M-d-Y h:i:s') .
            "\n=================================\n\n";
        foreach ($errorMessages as $errorMessage) {
            $message .= $errorMessage . "\n\n";
        }
        $file = $logDir . '/' . 'Sanitize_Debug_' . time() . '.log';
        if ($fp = @fopen($file, 'a')) {
            fwrite($fp, $message);
            fclose($fp);
        }
    }
}
