<?php

namespace Tests\Support;

use notifier;

class UnitTestBootstrap
{
    public static function initialize(): void
    {
        self::defineBaseConstants();
        self::configureEnvironment();
        self::loadLocalSetup();
        self::definePathConstants();
        self::loadCoreFiles();
        self::initializeNotifier();
        self::defineServerConstants();
        self::defineSessionConstants();
        self::defineMiscConstants();
        self::loadSessionStubs();
    }

    private static function defineBaseConstants(): void
    {
        self::defineIfMissing('ZENCART_TESTFRAMEWORK_RUNNING', true);
        self::defineIfMissing('IS_ADMIN_FLAG', false);
        self::defineIfMissing('TESTCWD', realpath(__DIR__ . '/../') . '/');
        self::defineIfMissing('DIR_FS_CATALOG', realpath(__DIR__ . '/../../..') . '/');
        self::defineIfMissing('DIR_FS_INCLUDES', DIR_FS_CATALOG . 'includes/');
        self::defineIfMissing('CWD', DIR_FS_INCLUDES . '../');
    }

    private static function configureEnvironment(): void
    {
        if (strpos((string) @ini_get('include_path'), '.') === false) {
            @ini_set('include_path', '.' . PATH_SEPARATOR . (string) @ini_get('include_path'));
        }

        date_default_timezone_set('UTC');
    }

    private static function loadLocalSetup(): void
    {
        if (file_exists(TESTCWD . 'localTestSetup.php')) {
            require_once TESTCWD . 'localTestSetup.php';
        }
    }

    private static function definePathConstants(): void
    {
        self::defineIfMissing('DIR_WS_CATALOG', '/');
        self::defineIfMissing('DIR_WS_ADMIN', '/admin/');
        self::defineIfMissing('DIR_FS_ADMIN', DIR_FS_CATALOG . 'admin/');
        self::defineIfMissing('DIR_WS_HTTPS_CATALOG', '/ssl/');
    }

    private static function loadCoreFiles(): void
    {
        require_once DIR_FS_INCLUDES . 'defined_paths.php';
        require_once DIR_FS_INCLUDES . 'database_tables.php';
        require_once DIR_FS_INCLUDES . 'filenames.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'traits/NotifierManager.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'traits/ObserverManager.php';
        require_once DIR_FS_CATALOG . 'includes/functions/php_polyfills.php';
        require_once DIR_FS_CATALOG . 'includes/functions/zen_define_default.php';
    }

    private static function initializeNotifier(): void
    {
        if (!array_key_exists('zco_notifier', $GLOBALS)) {
            $GLOBALS['zco_notifier'] = new notifier();
        }
    }

    private static function defineServerConstants(): void
    {
        self::defineIfMissing('HTTP_SERVER', 'http://zencart-git.local');
        self::defineIfMissing('HTTPS_SERVER', 'https://zencart-git.local');
        self::defineIfMissing('HTTP_CATALOG_SERVER', 'http://zencart-git.local');
        self::defineIfMissing('HTTPS_CATALOG_SERVER', 'https://zencart-git.local');
    }

    private static function defineSessionConstants(): void
    {
        self::defineIfMissing('SESSION_FORCE_COOKIE_USE', 'False');
        self::defineIfMissing('SESSION_USE_FQDN', 'True');
    }

    private static function defineMiscConstants(): void
    {
        self::defineIfMissing('CONNECTION_TYPE_UNKNOWN', 'Unknown Connection \'%s\' Found: %s');
    }

    private static function loadSessionStubs(): void
    {
        require_once TESTCWD . 'Support/helpers/unit_test_session_stubs.php';
    }

    private static function defineIfMissing(string $name, mixed $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}
