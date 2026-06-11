<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

/**
 * Class AdminRequestSanitizer
 *
 * @since ZC v1.5.5
 */
class AdminRequestSanitizer extends base
{
    private bool $doStrictSanitization = false;
    private array $getKeysAlreadySanitized = [];
    private array $postKeysAlreadySanitized = [];
    private array $adminSanitizerTypes = [];
    private bool $debug = false;
    private array $debugMessages = [];
    private static self $instance;
    private array $requestParameterList = [];
    private string $currentPage;
    private string $charset;
    private string $arrayName = '';

    /**
     * @since ZC v1.5.5a
     */
    public static function getInstance(): self
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
        $this->debugMessages[] = 'Incoming GET Request ' . print_r($_GET, true);
        $this->debugMessages[] = 'Incoming POST Request ' . print_r($_POST, true);
        $this->charset = (defined('CHARSET') ? CHARSET : 'utf-8');
    }

    /**
     * @since ZC v1.5.5a
     */
    public function addSimpleSanitization($sanitizerType, $sanitizerEntries): void
    {
        foreach ($sanitizerEntries as $sanitizerEntry) {
            $entryParameters = ['sanitizerType' => $sanitizerType, 'method' => 'both'];
            $this->addRequestParameter($sanitizerEntry, $entryParameters);
        }
    }

    /**
     * @param $sanitizationEntries
     * @since ZC v1.5.5a
     */
    public function addComplexSanitization($sanitizationEntries): void
    {
        foreach ($sanitizationEntries as $requestParameter => $sanitizationEntry) {
            $this->addRequestParameter($requestParameter, $sanitizationEntry);
        }
    }

    /**
     * @since ZC v1.5.5a
     */
    public function addSanitizerTypes($sanitizertypes): void
    {
        foreach ($sanitizertypes as $key => $sanitizertype) {
            $this->adminSanitizerTypes[$key] = $sanitizertype;
        }
    }

    /**
     * @since ZC v1.5.5
     */
    public function getGetKeysAlreadySanitized(): array
    {
        return $this->getKeysAlreadySanitized;
    }

    /**
     * @since ZC v1.5.5
     */
    public function getPostKeysAlreadySanitized(): array
    {
        return $this->postKeysAlreadySanitized;
    }

    /**
     * @since ZC v1.5.5a
     */
    public function setPostKeyAlreadySanitized($parameterName): void
    {
        $this->postKeysAlreadySanitized[] = $parameterName;
    }

    /**
     * @since ZC v1.5.5a
     */
    public function setGetKeyAlreadySanitized($parameterName): void
    {
        $this->getKeysAlreadySanitized[] = $parameterName;
    }

    /**
     * @since ZC v1.5.5
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @since ZC v1.5.5a
     */
    public function getDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @since ZC v1.5.5a
     */
    public function setDoStrictSanitization(bool $doStrictSanitize): void
    {
        $this->doStrictSanitization = $doStrictSanitize;
    }

    /**
     *
     * @since ZC v1.5.5
     */
    public function runSanitizers(): void
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
     * @since ZC v1.5.5a
     */
    private function runSpecificSanitizer($parameterName, $parameterDefinition): void
    {
        if ($this->adminSanitizerTypes[$parameterDefinition['sanitizerType']]['type'] === 'builtin') {
            $this->processBuiltIn($parameterDefinition['sanitizerType'], $parameterName, $parameterDefinition);
        }
        if ($this->adminSanitizerTypes[$parameterDefinition['sanitizerType']]['type'] === 'custom') {
            $this->processCustom($parameterDefinition['sanitizerType'], $parameterName, $parameterDefinition);
        }
    }

    /**
     * @since ZC v1.5.5a
     */
    private function findSanitizerFromContext(array $parameterDefinitions): array|false
    {
        $result = false;
        foreach ($parameterDefinitions as $parameterDefinition) {
            if (empty($parameterDefinition['pages'])) {
                continue;
            }
            if (!in_array($this->currentPage, $parameterDefinition['pages'], true)) {
                continue;
            }
            $result = $parameterDefinition;
            break;
        }
        return $result;
    }

    /**
     * @since ZC v1.5.5a
     */
    private function findSanitizerFromRequestMethod($parameterName, $parameterDefinitions): array|false
    {
        $result = false;
        foreach ($parameterDefinitions as $parameterDefinition) {
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
     * @since ZC v1.5.5a
     */
    private function parameterExistsForMethod($parameterName, $parameterDefinition): bool
    {
        $hasGet = isset($_GET[$parameterName]);
        $hasPost = isset($_POST[$parameterName]);
        if ($parameterDefinition['method'] === 'both' && ($hasGet || $hasPost)) {
            return true;
        }
        if ($parameterDefinition['method'] === 'get' && $hasGet) {
            return true;
        }
        if ($parameterDefinition['method'] === 'post' && $hasPost) {
            return true;
        }
        return false;
    }

    /**
     * @since ZC v1.5.5a
     */
    private function addRequestParameter($requestParameter, $parameterDetail): void
    {
        $pages = $parameterDetail['pages'] ?? null;
        $params = $parameterDetail['params'] ?? null;
        $this->requestParameterList[$requestParameter][] = [
            'sanitizerType' => $parameterDetail['sanitizerType'],
            'method' => $parameterDetail['method'],
            'pages' => $pages,
            'params' => $params,
        ];
    }

    /**
     * @since ZC v1.5.5
     */
    private function processBuiltIn($sanitizerName, $parameterName, $parameterDefinition): void
    {
        $method = 'filter' . self::camelize(strtolower($sanitizerName), true);
        if (method_exists($this, $method)) {
            $this->$method($parameterName, $parameterDefinition);
        }
    }

    /**
     * @since ZC v1.5.5
     */
    private function processCustom($sanitizerName, $parameterName, $parameterDefinition): void
    {
        $func = $this->adminSanitizerTypes[$parameterDefinition['sanitizerType']]['function'];
        $this->debugMessages[] = 'SANITIZER CUSTOM == ' . $sanitizerName;
        $func($this, $parameterName, $parameterDefinition);
    }

    /**
     * @since ZC v1.5.5a
     */
    private function filterNullAction($parameterName): void
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
     * @since ZC v1.5.5
     */
    private function filterSimpleAlphanumPlus($parameterName): void
    {
        if (isset($_GET[$parameterName])) {
            $this->debugMessages[] = 'PROCESSING SIMPLE_ALPHANUM_PLUS(GET) == ' . $parameterName;
            $this->getKeysAlreadySanitized[] = $parameterName;
            if (!is_int($_GET[$parameterName])) {
                $_GET[$parameterName] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_GET[$parameterName]);
            }
        }
        if (isset($_POST[$parameterName])) {
            // Add the parameterName to the base arrayname.
            $this->arrayName = $this->setCurrentArrayName($parameterName);
            $this->debugMessages[] = 'PROCESSING SIMPLE_ALPHANUM_PLUS(POST) == ' . $this->arrayName;
            $this->postKeysAlreadySanitized[] = $this->arrayName;
            if (!is_int($_POST[$parameterName])) {
                $_POST[$parameterName] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_POST[$parameterName]);
            }
        }
    }

    /**
     * @since ZC v1.5.5
     */
    private function filterConvertInt($parameterName): void
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
     * @since ZC v1.5.5
     */
    private function filterFileDirRegex($parameterName): void
    {
        $filedirRegex = '~[^0-9a-z' . preg_quote('.!@#$%&()_-~/`+^ ' . '\\', '~') . ']~i';
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING FILE_DIR_REGEX == ' . $this->arrayName;
        $_POST[$parameterName] = preg_replace($filedirRegex, '', $_POST[$parameterName]);
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.5
     */
    private function filterAlphanumDashUnderscore($parameterName): void
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
     * @since ZC v1.5.5
     */
    private function filterWordsAndSymbolsRegex($parameterName): void
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
     * @since ZC v1.5.5
     */
    private function filterProductDescRegex($parameterName): void
    {
        $prodDescRegex = '~(load=|= ?\(|<![^-])~i';
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING PRODUCT_DESC_REGEX == ' . $parameterName;
        if (is_array($_POST[$parameterName])) {
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $currentArrayName = $this->setCurrentArrayName($pKey);
                $_POST[$parameterName][$pKey] = preg_replace($prodDescRegex, '', $_POST[$parameterName][$pKey]);
                $this->postKeysAlreadySanitized[] = $currentArrayName;
            }
            return;
        }
        $_POST[$parameterName] = preg_replace($prodDescRegex, '', $_POST[$parameterName]);
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.5
     */
    private function filterMetaTags($parameterName): void
    {
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING META_TAGS == ' . $this->arrayName;
        if (is_array($_POST[$parameterName])) {
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $currentArrayName = $this->setCurrentArrayName($pKey);
                $_POST[$parameterName][$pKey] = htmlspecialchars($_POST[$parameterName][$pKey], ENT_COMPAT, $this->charset, false);
                $this->postKeysAlreadySanitized[] = $currentArrayName;
            }
            return;
        }
        $_POST[$parameterName] = htmlspecialchars($_POST[$parameterName], ENT_COMPAT, $this->charset, false);
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.5
     */
    private function filterSanitizeEmail($parameterName): void
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
     * @since ZC v1.5.5a
     */
    private function filterSanitizeEmailAudience($parameterName): void
    {
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING SANITIZE_EMAIL_AUDIENCE (POST) == ' . $this->arrayName;
        $_POST[$parameterName] = htmlspecialchars($_POST[$parameterName], ENT_COMPAT, $this->charset, true);
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.5
     */
    private function filterProductUrlRegex($parameterName): void
    {
        $urlRegex = '~([^0-9a-z' . preg_quote("'.!@#$%&()_-~/;:=?[]", '~') . ']|[><])~i';
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING PRODUCT_URL_REGEX == ' . $this->arrayName;
        if (is_array($_POST[$parameterName])) {
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $currentArrayName = $this->setCurrentArrayName($pKey);
                $newValue = filter_var($_POST[$parameterName][$pKey], FILTER_SANITIZE_URL);
                if ($newValue === false) {
                    $newValue = preg_replace($urlRegex, '', $_POST[$parameterName][$pKey]);
                }
                $_POST[$parameterName][$pKey] = $newValue;
                $this->postKeysAlreadySanitized[] = $currentArrayName;
            }
            return;
        }
        // Perform similar sanitization for $_POST of non-array value.
        $newValue = filter_var($_POST[$parameterName], FILTER_SANITIZE_URL);
        if ($newValue === false) {
            $newValue = preg_replace($urlRegex, '', $_POST[$parameterName]);
        }
        $_POST[$parameterName] = $newValue;
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.6
     */
    private function filterFilePathOrUrlRegex($parameterName): void
    {
        $regex = '~([^0-9a-z' . preg_quote("'.!@#$%&()_-~/;:=?[]`+^ " . '\\', '~') . ']|[><])~i';
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING FILE_PATH_OR_URL_REGEX == ' . $this->arrayName;
        if (is_array($_POST[$parameterName])) {
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $newValue = filter_var($_POST[$parameterName][$pKey], FILTER_SANITIZE_URL);
                if ($newValue === false) {
                    $newValue = preg_replace($regex, '', $_POST[$parameterName][$pKey]);
                }
                $_POST[$parameterName][$pKey] = $newValue;
                $this->postKeysAlreadySanitized[] = $this->arrayName;
            }
            return;
        }
        $newValue = filter_var($_POST[$parameterName], FILTER_SANITIZE_URL);
        if ($newValue === false) {
            $newValue = preg_replace($regex, '', $_POST[$parameterName]);
        }
        $_POST[$parameterName] = $newValue;
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.5
     */
    private function filterCurrencyValueRegex($parameterName): void
    {
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING CURRENCY_VALUE_REGEX == ' . $this->arrayName;
        $_POST[$parameterName] = preg_replace('/[^a-z0-9_,\.\-]/i', '', $_POST[$parameterName]);
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.5a
     */
    private function filterFloatValueRegex($parameterName): void
    {
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING FLOAT_VALUE_REGEX == ' . $this->arrayName;
        $_POST[$parameterName] = preg_replace('/[^0-9,\.\-\+]/', '', $_POST[$parameterName]);
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.5a
     */
    private function filterMultiDimensional($parameterName, $parameterDefinition): void
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
                $params = $parameterDefinition['params'][$parameterName]['params'] ?? null;
                $newParameterDefinition = ['sanitizerType' => $type, 'params' => $params];
                //$this->arrayName = $currentArrayName; // Unnecessary as already set above.
                $this->runSpecificSanitizer($parameterName, $newParameterDefinition);
                // $this->arrayName = $currentArrayName; // Don't need here because set below.
                // $newKey = $_POST[$parameterName]; // Moved to below to reduce redundancy
                // $requestPost[$parameterName][$newKey] = $hacked; // Moved to below to reduce redundancy
            } elseif ($this->doStrictSanitization) {
                unset($requestPost[$parameterName][$key]);
                unset($_POST);
                $_POST[$key] = $key;
                // $this->arrayName = $currentArrayName; // Unnecessary as already set above.
                $this->filterStrictSanitizeKeys();
                if (!array_key_exists($key, $_POST)) {
                    continue; // Key is "unclean" and therefore should use the next key.
                }
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
                // $newKey = $_POST[$parameterName]; // Moved to below to reduce redundancy
                // $this->arrayName = $currentArrayName; // Don't need here because set below
                // $requestPost[$parameterName][$newKey] = $hacked; // Moved to below to reduce redundancy
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
                    $params = $parameterDefinition['params'][$pkey]['params'] ?? null;
                    $newParameterDefinition = ['sanitizerType' => $type, 'params' => $params];
                    // $this->arrayName = $newCurrentArrayName; // Unnecessary as set above
                    $this->runSpecificSanitizer($pkey, $newParameterDefinition);
                    // $this->arrayName = $newCurrentArrayName; // Unnecessary as set below or in next loop
                    $requestPost[$parameterName][$newKey][$pkey] = $_POST[$pkey];
                } elseif ($this->doStrictSanitization) {
                    unset($requestPost[$parameterName][$newKey][$pkey]);
                    unset($_POST);
                    $_POST[$pkey] = $pvalue;
                    // $this->arrayName = $newCurrentArrayName; // Unnecessary as set above
                    $this->filterStrictSanitizeKeys();
                    if (array_key_exists($pkey, $_POST)) {
                        $this->filterStrictSanitizeValues();
                        // $this->arrayName = $newCurrentArrayName; // Unnecessary as set below or in next loop
                        $requestPost[$parameterName][$newKey][$pkey] = $_POST[$pkey];
                    }
                }
            }
        }
        $this->arrayName = $currentArrayName; // This is the base of the recent sanitization and what was sanitized
        $_POST = $requestPost;
    }

    /**
     * @since ZC v1.5.6
     */
    private function filterSimpleArray($parameterName, $parameterDefinition): void
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
                $params = $parameterDefinition['params'][$pkey]['params'] ?? null;
                $newParameterDefinition = ['sanitizerType' => $type, 'params' => $params];
                // $this->arrayName = $currentArrayName; // Prepare for processing the key to the array. // Not needed because set above.
                $this->runSpecificSanitizer($pkey, $newParameterDefinition);
                $this->arrayName = $currentArrayName; // Restore the internal pointer back to the base array.
                $requestPost[$parameterName][$pkey] = $_POST[$pkey];
            } elseif ($this->doStrictSanitization) {
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
                    // $this->arrayName = $currentArrayName; // Unnecessary as set below or in next loop.
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
     * @since ZC v1.5.5
     */
    private function filterProductNameDeepRegex($parameterName): void
    {
        $prodNameRegex = '~<\/?scri|on(load|mouse|error|read|key)(up|down)? ?=|[^(class|style)] ?= ?(\(|")|<!~i';
        if (!isset($_POST[$parameterName])) {
            return;
        }
        // Add the parameterName to the base arrayname.
        $this->arrayName = $this->setCurrentArrayName($parameterName);
        $this->debugMessages[] = 'PROCESSING PRODUCT_NAME_DEEP_REGEX == ' . $parameterName;
        if (is_array($_POST[$parameterName])) {
            foreach ($_POST[$parameterName] as $pKey => $pValue) {
                $currentArrayName = $this->setCurrentArrayName($pKey);
                $_POST[$parameterName][$pKey] = preg_replace($prodNameRegex, '', $_POST[$parameterName][$pKey]);
                $this->postKeysAlreadySanitized[] = $currentArrayName;
            }
            return;
        }
        //$currentArrayName = $this->setCurrentArrayName($pKey);
        $_POST[$parameterName] = preg_replace($prodNameRegex, '', $_POST[$parameterName]);
        $this->postKeysAlreadySanitized[] = $this->arrayName;
    }

    /**
     * @since ZC v1.5.5
     */
    private function filterStrictSanitizeValues(): void
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
     * @since ZC v1.5.5
     */
    private function traverseStrictSanitize(array &$item, array $ignore, bool $inner, string $type): array
    {
        // Establish a local base array name for further processing.
        $currentArrayName = $this->arrayName;
        foreach ($item as $k => $v) {
            // Append the key of this item to the arrayname that called the sanitizer.
            $this->arrayName = $currentArrayName; // Set/reset $this->arrayName to the base for this iteration of the array.
            $this->arrayName = $this->setCurrentArrayName($k);
            if ($inner || (!$inner && !in_array($this->arrayName, $ignore, false))) {
                if (is_array($v)) {
                    $item[$k] = $this->traverseStrictSanitize($v, $ignore, true, $type);
                } else {
                    if (!in_array($this->arrayName, $ignore, false)) {
                        $this->debugMessages[] = 'PROCESSING STRICT_SANITIZE_VALUES == ' . $this->arrayName;
                        if (!is_int($item[$k])) {
                            $item[$k] = htmlspecialchars($item[$k], ENT_COMPAT, $this->charset, true);
                        }
                        if ($inner) {
                            if ($type === 'post') {
                                if (!in_array($this->arrayName, $ignore, false)) {
                                    $this->postKeysAlreadySanitized[] = $this->arrayName;
                                    $this->arrayName = $currentArrayName;
                                }
                            }
                        }
                    }
                }
            }
            if (!$inner) {
                if ($type === 'post') {
                    if (!in_array($this->arrayName, $this->postKeysAlreadySanitized, false)) {
                        $this->postKeysAlreadySanitized[] = $this->arrayName;
                        $this->arrayName = $currentArrayName;
                    }
                }
                if ($type === 'get') {
                    if (!in_array($k, $this->getKeysAlreadySanitized, false)) {
                        $this->getKeysAlreadySanitized[] = $k;
                    }
                }
            }
        }
        return $item;
    }

    /**
     * @since ZC v1.5.5b
     */
    private function addParamsToIgnore(string $group): void
    {
        foreach ($this->requestParameterList as $key => $details) {
            foreach ($details as $detail) {
                if ($detail['sanitizerType'] === $group) {
                    if ($detail['method'] === 'both') {
                        $this->addKeyAlreadySanitized('post', $key);
                        $this->addKeyAlreadySanitized('get', $key);
                    }
                    if ($detail['method'] === 'get') {
                        $this->addKeyAlreadySanitized('get', $key);
                    }
                    if ($detail['method'] === 'post') {
                        $this->addKeyAlreadySanitized('post', $key);
                    }
                }
            }
        }
    }

    /**
     *
     * @since ZC v1.5.5
     */
    private function filterStrictSanitizeKeys(): void
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
     * @since ZC v1.5.5b
     */
    private function addKeyAlreadySanitized(string $type, string $key): void
    {
        if ($type === 'post' && !in_array($key, $this->postKeysAlreadySanitized, true)) {
            $this->postKeysAlreadySanitized[] = $key;
        }
        if ($type === 'get' && !in_array($key, $this->getKeysAlreadySanitized, true)) {
            $this->getKeysAlreadySanitized[] = $key;
        }
    }

    /**
     * @since ZC v1.5.5
     */
    private function errorLog(array $errorMessages = []): void
    {
        $logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;
        $message = date('M-d-Y h:i:s')
            . "\n=================================\n\n";
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
     * @param string|int $parameterName the sub-parameter (key) to be added to the $this->arrayname if $this->arrayname has already been defined as a non-empty string.
     * @return string the newly built arrayname to be assigned/evaluated as necessary.
     * @since ZC v1.5.6
     */
    private function setCurrentArrayName(string|int $parameterName): string
    {
        $result = $parameterName; // Assign as base variable, assumed to not be an array, but instead a single name/string.

        // if the currentArray has already been built, then append the parameter to it.
        // This assumes that $this->arrayName is not an array but instead convertable to text.
        //   If $this->arrayName were an array, how should it be returned? with $parameterName attached to each element?
        //     Attached to the last element only?
        if (isset($this->arrayName) && $this->arrayName !== '') {
            $result = $this->arrayName . '[' . $parameterName . ']';
        }

        return $result;
    }
}
