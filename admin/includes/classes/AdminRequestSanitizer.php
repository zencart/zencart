<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jun 04 Modified in v1.5.7 $
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
     * @var string
     */
    private $charset;

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
        $this->arrayName = '';
        $this->debugMessages[] = 'Incoming GET Request ' . print_r($_GET, true);
        $this->debugMessages[] = 'Incoming POST Request ' . print_r($_POST, true);
        $this->charset = (defined('CHARSET') ? CHARSET : 'utf-8');
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
                $this->arrayName = '';
                $this->runSpecificSanitizer($parameterName, $result);
            }
        }
        if ($this->doStrictSanitization) {
            $this->arrayName = '';
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
     * @param $parameterName
     * @param $parameterDefinition
     */
    private function runSpecificSanitizer($parameterName, $parameterDefinition)
    {
        if ($this->adminSanitizerTypes[$parameterDefinition['sanitizerType']]['type'] === 'builtin') {
            $this->processBuiltIn($parameterDefinition['sanitizerType'], $parameterName, $parameterDefinition);
        }
        if ($this->adminSanitizerTypes[$parameterDefinition['sanitizerType']]['type'] === 'custom') {
            $this->processCustom($parameterDefinition['sanitizerType'], $parameterName, $parameterDefinition);
        }
    }

    /**
     * @param array $parameterDefinitions
     * @return bool
     */
    private function findSanitizerFromContext($parameterDefinitions)
    {
        foreach ($parameterDefinitions as $parameterDefinition) {
            $result = false;
            if (!empty($parameterDefinition['pages'])) {
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
            if (!empty($parameterDefinition['pages'])) {
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
        $func($this, $parameterName, $parameterDefinition);
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
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING NULL ACTION(POST) == ' . $this->arrayName;
            $this->postKeysAlreadySanitized[] = $this->arrayName;
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
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING SIMPLE_ALPHANUM_PLUS(POST) == ' . $this->arrayName;
            $this->postKeysAlreadySanitized[] = $this->arrayName;
            $_POST[$parameterName] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_POST[$parameterName]);
        }
    }

    /**
     * @param $parameterName
     */
    private function filterConvertInt($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING CONVERT_INT (POST) == ' . $this->arrayName;
            $_POST[$parameterName] = (int)$_POST[$parameterName];
            $this->postKeysAlreadySanitized[] = $this->arrayName;
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
        $filedirRegex = '~[^0-9a-z' . preg_quote('.!@#$%&()_-~/`+^ ' . '\\', '~') . ']~i';
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING FILE_DIR_REGEX == ' . $this->arrayName;
            $_POST[$parameterName] = preg_replace($filedirRegex, '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $this->arrayName;
        }

    }

    /**
     * @param $parameterName
     */
    private function filterAlphanumDashUnderscore($parameterName)
    {
        $alphaNumDashUnderscore = '/[^a-z0-9_-]/i';
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING ALPHANUM_DASH_UNDERSCORE (POST) == ' . $this->arrayName;
            $_POST[$parameterName] = preg_replace($alphaNumDashUnderscore, '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $this->arrayName;
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
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING WORDS_AND_SYMBOLS_REGEX (POST) == ' . $this->arrayName;
            $_POST[$parameterName] = preg_replace($prodNameRegex, '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $this->arrayName;
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
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING PRODUCT_DESC_REGEX == ' . $parameterName;
            if (is_array($_POST[$parameterName])) {
                foreach ($_POST[$parameterName] as $pKey => $pValue) {
                    $currentArrayName = $this->setCurrentArrayName($pKey);
                    $_POST[$parameterName][$pKey] = preg_replace($prodDescRegex, '', $_POST[$parameterName][$pKey]);
                    $this->postKeysAlreadySanitized[] = $currentArrayName;
                }
            } else {
                $_POST[$parameterName] = preg_replace($prodDescRegex, '', $_POST[$parameterName]);
                $this->postKeysAlreadySanitized[] = $this->arrayName;
            }
        }
    }

    /**
     * @param $parameterName
     */
    private function filterMetaTags($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING META_TAGS == ' . $this->arrayName;
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $currentArrayName = $this->setCurrentArrayName($pKey);
                $_POST[$parameterName][$pKey] = htmlspecialchars($_POST[$parameterName][$pKey], ENT_COMPAT, $this->charset, false);
                $this->postKeysAlreadySanitized[] = $currentArrayName;
            }
        }
    }

    /**
     * @param $parameterName
     */
    private function filterSanitizeEmail($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING SANITIZE_EMAIL (POST) == ' . $this->arrayName;
            $_POST[$parameterName] = filter_var($_POST[$parameterName], FILTER_SANITIZE_EMAIL);
            $this->postKeysAlreadySanitized[] = $this->arrayName;
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
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING SANITIZE_EMAIL_AUDIENCE (POST) == ' . $this->arrayName;
            $_POST[$parameterName] = htmlspecialchars($_POST[$parameterName], ENT_COMPAT, $this->charset, true);
            $this->postKeysAlreadySanitized[] = $this->arrayName;
        }
    }

    /**
     * @param $parameterName
     */
    private function filterProductUrlRegex($parameterName)
    {
        $urlRegex = '~([^0-9a-z' . preg_quote("'.!@#$%&()_-~/;:=?[]", '~') . ']|[><])~i';
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING PRODUCT_URL_REGEX == ' . $this->arrayName;
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $currentArrayName = $this->setCurrentArrayname($pKey);
                $newValue = filter_var($_POST[$parameterName][$pKey], FILTER_SANITIZE_URL);
                if ($newValue === false) {
                    $newValue = preg_replace($urlRegex, '', $_POST[$parameterName][$pKey]);
                }
                $_POST[$parameterName][$pKey] = $newValue;
                $this->postKeysAlreadySanitized[] = $currentArrayName;
            }
        }
    }

    /**
     * @param $parameterName
     */
    private function filterFilePathOrUrlRegex($parameterName)
    {
        $regex = '~([^0-9a-z' . preg_quote("'.!@#$%&()_-~/;:=?[]`+^ " . '\\', '~') . ']|[><])~i';
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING PRODUCT_URL_REGEX == ' . $this->arrayName;
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $newValue = filter_var($_POST[$parameterName][$pKey], FILTER_SANITIZE_URL);
                if ($newValue === false) {
                    $newValue = preg_replace($regex, '', $_POST[$parameterName][$pKey]);
                }
                $_POST[$parameterName][$pKey] = $newValue;
                $this->postKeysAlreadySanitized[] = $this->arrayName;
            }
        }
    }

    /**
     * @param $parameterName
     */
    private function filterCurrencyValueRegex($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING CURRENCY_VALUE_REGEX == ' . $this->arrayName;
            $_POST[$parameterName] = preg_replace('/[^a-z0-9_,\.\-]/i', '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $this->arrayName;
        }
    }

    /**
     * @param $parameterName
     */
    private function filterFloatValueRegex($parameterName)
    {
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING FLOAT_VALUE_REGEX == ' . $this->arrayName;
            $_POST[$parameterName] = preg_replace('/[^0-9,\.\-\+]/', '', $_POST[$parameterName]);
            $this->postKeysAlreadySanitized[] = $this->arrayName;
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
        // Add the parameterName to the base arrayname.
        $currentArrayName = $this->setCurrentArrayName($parameterName);
        foreach ($requestPost[$parameterName] as $key => $value) {
            $this->arrayName = $currentArrayName; // set/reset $this->arrayName to the base for this iteration of the array.
            $hacked = $requestPost[$parameterName][$key];
            if (isset($parameterDefinition['params'][$parameterName])) {
                unset($requestPost[$parameterName][$key]);
                unset($_POST);
                $_POST[$parameterName] = $key;
                $type = $parameterDefinition['params'][$parameterName]['sanitizerType'];
                $params = isset($parameterDefinition['params'][$parameterName]['params']) ? $parameterDefinition['params'][$parameterName]['params'] : null;
                $newParameterDefinition = array('sanitizerType' => $type, 'params' => $params);
                //$this->arrayName = $currentArrayName; // Unnecessary as already set above.
                $this->runSpecificSanitizer($parameterName, $newParameterDefinition);
                // $this->arrayName = $currentArrayName; // Don't need here because set below.
//                $newKey = $_POST[$parameterName]; // Moved to below to reduce redundancy
//                $requestPost[$parameterName][$newKey] = $hacked; // Moved to below to reduce redundancy
            } else if ($this->doStrictSanitization) {
                unset($requestPost[$parameterName][$key]);
                unset($_POST);
                $_POST[$key] = $key;
                // $this->arrayName = $currentArrayName; // Unnecessary as already set above.
                $this->filterStrictSanitizeKeys();
                if (array_key_exists($key, $_POST)) {
                    $this->arrayName = $currentArrayName;
                    $currentPostKeysAlreadySanitized = $this->postKeysAlreadySanitized;
                    $this->filterStrictSanitizeValues();
                    unset($this->postKeysAlreadySanitized);
                    $this->postKeysAlreadySanitized = $currentPostKeysAlreadySanitized;
                    unset($currentPostKeysAlreadySanitized);
                    $this->postKeysAlreadySanitized[] = $this->setCurrentArrayName($key);
                    $temp_val = $_POST[$key];
                    unset($_POST[$key]);
                    $_POST[$parameterName] = $temp_val;
                    unset($temp_val);
                    $key = $_POST[$key];
//                    $newKey = $_POST[$parameterName]; // Moved to below to reduce redundancy
                    //$this->arrayName = $currentArrayName; // Don't need here because set below
//                    $requestPost[$parameterName][$newKey] = $hacked; // Moved to below to reduce redundancy
                } else {
                    continue; // Key is "unclean" and therefore should use the next key.
                }
            }
            $newKey = $_POST[$parameterName]; // Moved from above to reduce redundancy
            $requestPost[$parameterName][$newKey] = $hacked; // Moved from above to reduce redundancy
            
            $this->arrayName = $currentArrayName; // Set/Reset $this->arrayName to the base for this iteration of the array
            $newCurrentArrayName = $this->setCurrentArrayName($newKey);
            foreach ($hacked as $pkey => $pvalue) {
                $this->arrayName = $newCurrentArrayName;
                if (isset($parameterDefinition['params'][$pkey])) {
                    unset($requestPost[$parameterName][$newKey][$pkey]);
                    unset($_POST);
                    $_POST[$pkey] = $pvalue;
                    $type = $parameterDefinition['params'][$pkey]['sanitizerType'];
                    $params = isset($parameterDefinition['params'][$pkey]['params']) ? $parameterDefinition['params'][$pkey]['params'] : null;
                    $newParameterDefinition = array('sanitizerType' => $type, 'params' => $params);
                    //$this->arrayName = $newCurrentArrayName; // Unnecessary as set above
                    $this->runSpecificSanitizer($pkey, $newParameterDefinition);
//                    $this->arrayName = $newCurrentArrayName; // Unnecessary as set below or in next loop
                    $requestPost[$parameterName][$newKey][$pkey] = $_POST[$pkey];
                } else if ($this->doStrictSanitization) {
                    unset($requestPost[$parameterName][$newKey][$pkey]);
                    unset($_POST);
                    $_POST[$pkey] = $pvalue;
                    //$this->arrayName = $newCurrentArrayName; // Unnecessary as set above
                    $this->filterStrictSanitizeKeys();
                    if (array_key_exists($pkey, $_POST)) {
                        $this->filterStrictSanitizeValues();
//                        $this->arrayName = $newCurrentArrayName; // Unnecessary as set below or in next loop
                        $requestPost[$parameterName][$newKey][$pkey] = $_POST[$pkey];
                    }
                }
            }
        }
        $this->arrayName = $currentArrayName; // This is the base of the recent sanitization and what was sanitized
        $_POST = $requestPost;
    }

    /**
     * @param $parameterName
     * @param $parameterDefinition
     */
    private function filterSimpleArray($parameterName, $parameterDefinition)
    {
        $requestPost = $_POST;
        if (!isset($requestPost[$parameterName])) {
            return;
        }
        $this->debugMessages[] = 'PROCESSING SIMPLE_ARRAY == ' . $parameterName;

        $hacked = $requestPost[$parameterName];

        // Establish a base array name for the processing of the array keys.
        $currentArrayName = $this->setCurrentArrayName($parameterName);
        foreach ($hacked as $pkey => $pvalue) {
            $this->arrayName = $currentArrayName; // set/reset $this->arrayName back to the base for this iteration of the array.
            
            if (isset($parameterDefinition['params'][$pkey])) {
                unset($requestPost[$parameterName][$pkey]);
                unset($_POST);
                $_POST[$pkey] = $pvalue;
                $type = $parameterDefinition['params'][$pkey]['sanitizerType'];
                $params = isset($parameterDefinition['params'][$pkey]['params']) ? $parameterDefinition['params'][$pkey]['params'] : null;
                $newParameterDefinition = array('sanitizerType' => $type, 'params' => $params);
                // $this->arrayName = $currentArrayName; // Prepare for processing the key to the array. // Not needed because set above.
                $this->runSpecificSanitizer($pkey, $newParameterDefinition);
                $this->arrayName = $currentArrayName; // Restore the internal pointer back to the base array.
                $requestPost[$parameterName][$pkey] = $_POST[$pkey];
            } else if ($this->doStrictSanitization) {
                unset($requestPost[$parameterName][$pkey]);
                unset($_POST);
                $_POST[$pkey] = $pkey;
                $this->filterStrictSanitizeKeys();
                if (array_key_exists(/*$parameterName*/ $pkey, $_POST)) {
                    $this->arrayName = $currentArrayName;
                    $currentPostKeysAlreadySanitized = $this->postKeysAlreadySanitized;
                    $this->filterStrictSanitizeValues();
                    unset($this->postKeysAlreadySanitized);
                    $this->postKeysAlreadySanitized = $currentPostKeysAlreadySanitized;
                    unset($currentPostKeysAlreadySanitized);
                    $this->postKeysAlreadySanitized[] = $this->setCurrentArrayName($pkey);
                    $tempkey = $pkey;
                    $pkey = $_POST[$pkey];
                    unset($_POST[$tempkey]);
                    $_POST[$pkey] = $pkey;
                    $requestPost[$parameterName][$pkey] = $pvalue;
                }
            }
            if ($this->doStrictSanitization) {
                unset($_POST);
                $_POST[$pkey] = $requestPost[$parameterName][$pkey];
                unset($requestPost[$parameterName][$pkey]);
                $this->filterStrictSanitizeKeys();
                if (array_key_exists($pkey, $_POST)) {
                    $this->arrayName = $currentArrayName;
                    $currentPostKeysAlreadySanitized = $this->postKeysAlreadySanitized;
                    $this->filterStrictSanitizeValues();
//                    $this->arrayName = $currentArrayName; // Unnecessary as set below or in next loop.
                    unset($this->postKeysAlreadySanitized);
                    $this->postKeysAlreadySanitized = $currentPostKeysAlreadySanitized;
                    unset($currentPostKeysAlreadySanitized);
                    $this->postKeysAlreadySanitized[] = $this->setCurrentArrayName($pkey);
                    $requestPost[$parameterName][$pkey] = $_POST[$pkey];
                }
            }
        }

        $_POST = $requestPost;
        $this->postKeysAlreadySanitized[] = $this->arrayName = $currentArrayName;
    }

    /**
     * @param $parameterName
     */
    private function filterProductNameDeepRegex($parameterName)
    {
        $prodNameRegex = '~<\/?scri|on(load|mouse|error|read|key)(up|down)? ?=|[^(class|style)] ?= ?(\(|")|<!~i';
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING PRODUCT_NAME_DEEP_REGEX == ' . $parameterName;
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $currentArrayName = $this->setCurrentArrayName($pKey);
                $_POST[$parameterName][$pKey] = preg_replace($prodNameRegex, '', $_POST[$parameterName][$pKey]);
                $this->postKeysAlreadySanitized[] = $currentArrayName;
            }
        }
    }

    /**
     *
     */
    private function filterStrictSanitizeValues()
    {
        if (!$this->doStrictSanitization) {
            $this->addParamsToIgnore('STRICT_SANITIZE_VALUES');
        }
        $postToIgnore = $this->getPostKeysAlreadySanitized();
        $getToIgnore = $this->getGetKeysAlreadySanitized();
        $this->traverseStrictSanitize($_POST, $postToIgnore, false, 'post');
        $this->arrayName = '';
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
        // Establish a local base array name for further processing.
        $currentArrayName = $this->arrayName;
        foreach ($item as $k => $v) {
            // Append the key of this item to the arrayname that called the sanitizer.
            $this->arrayName = $currentArrayName; // Set/reset $this->arrayName to the base for this iteration of the array.
            $this->arrayName = $this->setCurrentArrayName($k);
            if ($inner || (!$inner && !in_array($this->arrayName, $ignore))) {
                if (is_array($v)) {
                    $item[$k] = $this->traverseStrictSanitize($v, $ignore, true, $type);
                } else {
                    if (!in_array($this->arrayName, $ignore)) {
                        $this->debugMessages[] = 'PROCESSING STRICT_SANITIZE_VALUES == ' . $this->arrayName;
                        $item[$k] = htmlspecialchars($item[$k], ENT_COMPAT, $this->charset, true);
                        if ($inner) {
                            if ($type == 'post') {
                                if (!in_array($this->arrayName, $ignore)) {
                                    $this->postKeysAlreadySanitized[] = $this->arrayName;
                                    $this->arrayName = $currentArrayName;
                                }
                            }
                        }
                    }
                }
            }
            if (!$inner) {
                if ($type == 'post') {
                    if (!in_array($this->arrayName, $this->postKeysAlreadySanitized)) {
                        $this->postKeysAlreadySanitized[] = $this->arrayName;
                        $this->arrayName = $currentArrayName;
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
    
    /**
     * @param string $parameterName      the sub-parameter (key) to be added to the $this->arrayname if $this->arrayname has already been defined as a non-empty string.
     * @return string                    the newly built arrayname to be assigned/evaluated as necessary.
     */
    private function setCurrentArrayName($parameterName)
    {
        $result = $parameterName; // Assign as base variable, assumed to not be an array, but instead a single name/string.
        
        // if the currentArray has already been built, then append the parameter to it.
        // This assumes that $this->arrayName is not an array but instead convertable to text.
        //   If $this->arrayName were an array, how should it be returned? with $parameterName attached to each element?
        //     Attached to the last element only?
        if (isset($this->arrayName) && $this->arrayName != '') {
            $result = $this->arrayName . '[' . $parameterName . ']';
        }
        
        return $result;
    }
}
