<?php
/**
 * CLI bootstrap for Zen Cart console commands.
 *
 * This intentionally avoids any admin/page/session bootstrap so commands can
 * start from a small, predictable runtime and opt into heavier services later.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (PHP_SAPI !== 'cli') {
    if (!headers_sent()) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
    }

    echo "Not found.\n";
    exit(1);
}

if (!defined('ZENCART_CONSOLE_RUNNING')) {
    define('ZENCART_CONSOLE_RUNNING', true);
}

if (!defined('IS_ADMIN_FLAG')) {
    define('IS_ADMIN_FLAG', false);
}

$catalogRoot = preg_replace('#/includes/$#', '/', realpath(__DIR__) . '/');
$includesRoot = $catalogRoot . 'includes/';

date_default_timezone_set(date_default_timezone_get());

if (!defined('DIR_FS_CATALOG')) {
    define('DIR_FS_CATALOG', $catalogRoot);
}

if (!defined('DIR_FS_INCLUDES')) {
    define('DIR_FS_INCLUDES', DIR_FS_CATALOG . 'includes/');
}

if (!defined('DIR_FS_ADMIN')) {
    define('DIR_FS_ADMIN', DIR_FS_CATALOG . 'admin/');
}

if (!defined('DIR_WS_CATALOG')) {
    define('DIR_WS_CATALOG', '/');
}

if (!defined('DIR_WS_ADMIN')) {
    define('DIR_WS_ADMIN', '/admin/');
}

require_once DIR_FS_INCLUDES . 'defined_paths.php';
require_once DIR_FS_INCLUDES . 'functions/php_polyfills.php';
require_once DIR_FS_INCLUDES . 'functions/zen_define_default.php';
require_once DIR_FS_INCLUDES . 'classes/vendors/AuraAutoload/src/Loader.php';

$psr4Autoloader = new \Aura\Autoload\Loader();
$psr4Autoloader->register();

require DIR_FS_INCLUDES . 'psr4Autoload.php';

if (!function_exists('zc_cli_get_db_context')) {
    /**
     * @return array{db: null|\queryFactory, warnings: string[]}
     */
    function zc_cli_get_db_context(): array
    {
        $warnings = [];

        $configureFiles = [
            DIR_FS_CATALOG . 'includes/local/configure.php',
            DIR_FS_CATALOG . 'includes/configure.php',
        ];

        $configureFileFound = false;
        foreach ($configureFiles as $configureFile) {
            if (file_exists($configureFile)) {
                $configureFileFound = true;
                break;
            }
        }

        if (!$configureFileFound) {
            $warnings[] = 'Plugin command discovery disabled: store database configuration is unavailable.';
            return ['db' => null, 'warnings' => $warnings];
        }

        if (!function_exists('mysqli_connect')) {
            $warnings[] = 'Plugin command discovery disabled: the MySQL connector for PHP is unavailable.';
            return ['db' => null, 'warnings' => $warnings];
        }

        foreach ($configureFiles as $configureFile) {
            if (file_exists($configureFile)) {
                $previousErrorReporting = error_reporting();
                error_reporting($previousErrorReporting & ~E_WARNING);
                require_once $configureFile;
                error_reporting($previousErrorReporting);
                break;
            }
        }

        if (!defined('DB_TYPE') || !defined('DB_SERVER') || !defined('DB_SERVER_USERNAME') || !defined('DB_SERVER_PASSWORD') || !defined('DB_DATABASE')) {
            $warnings[] = 'Plugin command discovery disabled: store database configuration is unavailable.';
            return ['db' => null, 'warnings' => $warnings];
        }

        require_once DIR_FS_INCLUDES . 'database_tables.php';
        require_once DIR_FS_INCLUDES . 'classes/class.base.php';
        require_once DIR_FS_INCLUDES . 'classes/db/' . DB_TYPE . '/query_factory.php';

        $db = new \queryFactory();
        if (!defined('USE_PCONNECT')) {
            define('USE_PCONNECT', 'false');
        }

        if (!$db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false)) {
            $warnings[] = 'Plugin command discovery disabled: unable to connect to the store database.';
            return ['db' => null, 'warnings' => $warnings];
        }

        return ['db' => $db, 'warnings' => $warnings];
    }
}

if (!function_exists('zc_cli_get_plugin_repository_context')) {
    /**
     * @return array{repository: null|\Zencart\DbRepositories\PluginControlRepository, warnings: string[]}
     */
    function zc_cli_get_plugin_repository_context(): array
    {
        $context = zc_cli_get_db_context();

        return [
            'repository' => $context['db'] === null ? null : new \Zencart\DbRepositories\PluginControlRepository($context['db']),
            'warnings' => $context['warnings'],
        ];
    }
}

if (!function_exists('zc_cli_resolve_trusted_plugin_versions')) {
    /**
     * @return array{plugins: array<string, string>, warnings: string[]}
     */
    function zc_cli_resolve_trusted_plugin_versions(?\Zencart\DbRepositories\PluginControlRepository $repository = null, array $warnings = []): array
    {
        if ($repository === null) {
            $context = zc_cli_get_plugin_repository_context();
            $repository = $context['repository'];
            $warnings = $context['warnings'];
        }

        if ($repository === null) {
            return ['plugins' => [], 'warnings' => $warnings];
        }

        return [
            'plugins' => (new \Zencart\Console\TrustedPluginVersionResolver($repository))->resolveEnabledPluginVersions(),
            'warnings' => $warnings,
        ];
    }
}

return $psr4Autoloader;
