<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Sun Mar 20 17:34:47 2016 -0500 New in v1.5.5 $
 */

/**
 * Class AdminRequestSanitizer
 */
class AdminRequestSanitizer extends base
{
    /**
     * @var
     */
    private $adminSanitizationConfig;
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
     * AdminRequestSanitizer constructor.
     * @param $adminSanitizationConfig
     * @param $adminSanitizerTypes
     * @param $doStrictSanitization
     */
    public function __construct($adminSanitizationConfig, $adminSanitizerTypes, $doStrictSanitization)
    {
        $this->adminSanitizerTypes = $adminSanitizerTypes;
        $this->adminSanitizationConfig = $adminSanitizationConfig;
        $this->doStrictSanitization = $doStrictSanitization;
        $this->getKeysAlreadySanitized = array();
        $this->postKeysAlreadySanitized = array();
        $this->initTypeGroupsFromConfig();
    }

    /**
     * @param $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     *
     */
    private function initTypeGroupsFromConfig()
    {
        foreach ($this->adminSanitizerTypes as $key => $value) {
            if (isset($this->adminSanitizationConfig[$key])) {
                continue;
            }
            $this->adminSanitizationConfig[$key] = array();
        }
    }

    /**
     *
     */
    public function runSanitizers()
    {
        $this->debugMessages[] = 'Running Admin Sanitizers';
        foreach ($this->adminSanitizerTypes as $key => $value) {
            if ($value['type'] === 'builtin' && $value['strict'] === false) {
                $this->processBuiltIn($key);
            }
            if ($value['type'] === 'builtin' && $value['strict'] === true && $this->doStrictSanitization) {
                $this->processBuiltIn($key);
            }
            if ($value['type'] === 'custom' && $value['strict'] === false) {
                $this->processCustom($key, $value);
            }
        }
        if ($this->debug) {
            $this->errorLog($this->debugMessages);
        }
    }

    /**
     * @param $sanitizerName
     */
    private function processBuiltIn($sanitizerName)
    {
        $method = 'filter' . self::camelize(strtolower($sanitizerName), true);
        if (method_exists($this, $method)) {
            $this->debugMessages[] = 'SANITIZER BUILTIN == ' . $method;
            call_user_func(array($this, $method));
        }
    }

    /**
     * @param $sanitizerName
     * @param $sanitizerValues
     */
    private function processCustom($sanitizerName, $sanitizerValues)
    {
        $func = $sanitizerValues['function'];
        $this->debugMessages[] = 'SANITIZER CUSTOM == ' . $sanitizerName;
        $func($this, $sanitizerName);
    }

    /**
     *
     */
    private function filterSimpleAlphanumPlus()
    {
        $saniList = $this->adminSanitizationConfig['SIMPLE_ALPHANUM_PLUS'];
        foreach ($saniList as $key) {
            if (isset($_GET[$key])) {
                $this->debugMessages[] = 'PROCESSING SIMPLE_ALPHANUM_PLUS == ' . $key;
                $this->getKeysAlreadySanitized[] = $key;
                $_GET[$key] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_GET[$key]);
                if (isset($_REQUEST[$key])) {
                    $_REQUEST[$key] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_REQUEST[$key]);
                }
            }
        }
    }

    /**
     *
     */
    private function filterConvertInt()
    {
        $saniList = $this->adminSanitizationConfig['CONVERT_INT'];
        foreach ($saniList as $key) {
            if (isset($_POST[$key])) {
                $this->debugMessages[] = 'PROCESSING CONVERT_INT (POST) == ' . $key;
                $_POST[$key] = (int)$_POST[$key];
                $this->postKeysAlreadySanitized[] = $key;
            }
            if (isset($_GET[$key])) {
                $this->debugMessages[] = 'PROCESSING CONVERT_INT (GET) == ' . $key;
                $_GET[$key] = (int)$_GET[$key];
                $this->getKeysAlreadySanitized[] = $key;

            }
        }
    }

    /**
     *
     */
    private function filterFileDirRegex()
    {
        $filedirRegex = '~[^0-9a-z' . preg_quote('.!@#$%^& ()`_+-~/' . '\\', '~') . ']~i';
        $saniList = $this->adminSanitizationConfig['FILE_DIR_REGEX'];
        foreach ($saniList as $key) {
            if (isset($_POST[$key])) {
                $this->debugMessages[] = 'PROCESSING FILE_DIR_REGEX == ' . $key;
                $_POST[$key] = preg_replace($filedirRegex, '', $_POST[$key]);
                $this->postKeysAlreadySanitized[] = $key;
            }
        }
    }

    /**
     *
     */
    private function filterAlphanumDashUnderscore()
    {
        $saniList = $this->adminSanitizationConfig['ALPHANUM_DASH_UNDERSCORE'];
        $alphaNumDashUnderscore = '/[^a-z0-9_-]/i';
        foreach ($saniList as $key) {
            if (isset($_POST[$key])) {
                $this->debugMessages[] = 'PROCESSING ALPHANUM_DASH_UNDERSCORE (POST) == ' . $key;
                $_POST[$key] = preg_replace($alphaNumDashUnderscore, '', $_POST[$key]);
                $this->postKeysAlreadySanitized[] = $key;
            }
            if (isset($_GET[$key])) {
                $this->debugMessages[] = 'PROCESSING ALPHANUM_DASH_UNDERSCORE (GET) == ' . $key;
                $_GET[$key] = preg_replace($alphaNumDashUnderscore, '', $_GET[$key]);
                $this->getKeysAlreadySanitized[] = $key;

            }
        }
    }

    /**
     *
     */
    private function filterWordsAndSymbolsRegex()
    {
        $saniList = $this->adminSanitizationConfig['WORDS_AND_SYMBOLS_REGEX'];
        $prodNameRegex = '~<\/?scri|on(load|mouse|error|read|key)(up|down)? ?=|[^(class|style)] ?= ?(\(|")|<!~i';
        foreach ($saniList as $key) {
            if (isset($_POST[$key])) {
                $this->debugMessages[] = 'PROCESSING WORDS_AND_SYMBOLS_REGEX (POST) == ' . $key;
                $_POST[$key] = preg_replace($prodNameRegex, '', $_POST[$key]);
                $this->postKeysAlreadySanitized[] = $key;
            }
            if (isset($_GET[$key])) {
                $this->debugMessages[] = 'PROCESSING WORDS_AND_SYMBOLS_REGEX (GET) == ' . $key;
                $_GET[$key] = reg_replace($prodNameRegex, '', $_GET[$key]);
                $this->getKeysAlreadySanitized[] = $key;

            }
        }
    }

    /**
     *
     */
    private function filterProductDescRegex()
    {
        $saniList = $this->adminSanitizationConfig['PRODUCT_DESC_REGEX'];
        $prodDescRegex = '~(load=|= ?\(|<![^-])~i';
        foreach ($saniList as $value) {
            $this->debugMessages[] = 'PROCESSING PRODUCT_DESC_REGEX == ' . $value;
            if (isset($_POST[$value])) {
                if (is_array($_POST[$value])) {
                    foreach ($_POST[$value] as $pKey => $pValue) {
                        $_POST[$value][$pKey] = preg_replace($prodDescRegex, '', $_POST[$value][$pKey]);
                        $this->postKeysAlreadySanitized[] = $value;
                    }
                } else {
                    $_POST[$value] = preg_replace($prodDescRegex, '', $_POST[$value]);
                    $this->postKeysAlreadySanitized[] = $value;
                }
            }
        }
    }

    /**
     *
     */
    private function filterMetaTags()
    {
        $saniList = $this->adminSanitizationConfig['META_TAGS'];
        foreach ($saniList as $value) {
            if (isset($_POST[$value])) {
                $this->debugMessages[] = 'PROCESSING META_TAGS == ' . $value;
                foreach ($_POST[$value] as $pKey => $pValue) {
                    $_POST[$value][$pKey] = htmlspecialchars($_POST[$value][$pKey], ENT_COMPAT, 'utf-8', false);
                    $this->postKeysAlreadySanitized[] = $value;
                }
            }
        }
    }

    /**
     *
     */
    private function filterSanitizeEmail()
    {
        $saniList = $this->adminSanitizationConfig['SANITIZE_EMAIL'];
        foreach ($saniList as $key) {
            if (isset($_POST[$key])) {
                $this->debugMessages[] = 'PROCESSING SANITIZE_EMAIL (POST) == ' . $key;
                $result = filter_var($_POST[$key], FILTER_SANITIZE_EMAIL);
                $_POST[$key] = $result;
                $this->postKeysAlreadySanitized[] = $key;
            }
            if (isset($_GET[$key])) {
                $this->debugMessages[] = 'PROCESSING SANITIZE_EMAIL (GET) == ' . $key;
                $result = filter_var($_GET[$key], FILTER_SANITIZE_EMAIL);
                $_GET[$key] = $result;

            }
        }
    }

    /**
     *
     */
    private function filterProductUrlRegex()
    {
        $saniList = $this->adminSanitizationConfig['PRODUCT_URL_REGEX'];
        $urlRegex = '~([^a-z0-9\'!#$&%@();:/=?_\~\[\]-]|[><])~i';
        foreach ($saniList as $value) {
            if (isset($_POST[$value])) {
                $this->debugMessages[] = 'PROCESSING PRODUCT_URL_REGEX == ' . $value;
                foreach ($_POST[$value] as $pKey => $pValue) {
                    $newValue = filter_var($_POST[$value][$pKey], FILTER_SANITIZE_URL);
                    if ($newValue === false) {
                        $newValue = preg_replace($urlRegex, '', $_POST[$value][$pKey]);
                    }
                    $_POST[$value][$pKey] = $newValue;
                    $this->postKeysAlreadySanitized[] = $value;
                }
            }
        }
    }

    /**
     *
     */
    private function filterCurrencyValueRegex()
    {
        $saniList = $this->adminSanitizationConfig['CURRENCY_VALUE_REGEX'];
        foreach ($saniList as $key) {
            if (isset($_POST[$key])) {
                $this->debugMessages[] = 'PROCESSING CURRENCY_VALUE_REGEX == ' . $key;
                $_POST[$key] = preg_replace('/[^a-z0-9_,\.\-]/i', '', $_POST[$key]);
                $this->postKeysAlreadySanitized[] = $key;
            }
        }
    }

    /**
     *
     */
    private function filterProductNameDeepRegex()
    {
        $saniList = $this->adminSanitizationConfig['PRODUCT_NAME_DEEP_REGEX'];
        $prodNameRegex = '~<\/?scri|on(load|mouse|error|read|key)(up|down)? ?=|[^(class|style)] ?= ?(\(|")|<!~i';
        foreach ($saniList as $value) {
            if (isset($_POST[$value])) {
                $this->debugMessages[] = 'PROCESSING PRODUCT_NAME_DEEP_REGEX == ' . $value;
                foreach ($_POST[$value] as $pKey => $pValue) {
                    $_POST[$value][$pKey] = preg_replace($prodNameRegex, '', $_POST[$value][$pKey]);
                    $this->postKeysAlreadySanitized[] = $value;
                }
            }
        }
    }

    /**
     *
     */
    private function filterStrictSanitizeValues()
    {
        $postToIgnore = $this->getPostKeysAlreadySanitized();
        $getToIgnore = $this->getGetKeysAlreadySanitized();
        $saniList = $this->adminSanitizationConfig['STRICT_SANITIZE_VALUES'];
        $postToIgnore = array_merge($postToIgnore, $saniList);
        $getToIgnore = array_merge($getToIgnore, $saniList);
        $this->traverseStricSanitize($_POST, $postToIgnore);
        $this->traverseStricSanitize($_GET, $getToIgnore);
    }

    /**
     * @param $item
     * @param $ignore
     * @param bool|false $inner
     * @return mixed
     */
    private function traverseStricSanitize(&$item, $ignore, $inner = false)
    {
        foreach ($item as $k => $v) {
            if ($inner || (!$inner && !in_array($k, $ignore))) {
                if (is_array($v)) {
                    $item[$k] = $this->traverseStricSanitize($v, $ignore, true);
                } else {
                    $item[$k] = htmlspecialchars($item[$k]);
                }
            }
            if (!$inner) {
                $this->debugMessages[] = 'PROCESSING STRICT_SANITIZE_VALUES == ' . $k;
                $this->postKeysAlreadySanitized[] = $k;
            }
        }
        return $item;
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
     * @param $groupName
     * @param $groupEntries
     */
    public function addSanitizationGroup($groupName, $groupEntries)
    {
        $group = $this->adminSanitizationConfig[$groupName];
        $group = array_merge($group, $groupEntries);
        $this->adminSanitizationConfig[$groupName] = $group;
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
