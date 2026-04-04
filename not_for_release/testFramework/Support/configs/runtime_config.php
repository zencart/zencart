<?php

if (!function_exists('zc_test_config_catalog_path')) {
    function zc_test_config_catalog_path(): string
    {
        $workspace = getenv('GITHUB_WORKSPACE');
        if (is_string($workspace) && $workspace !== '') {
            return rtrim(str_replace('\\', '/', $workspace), '/') . '/';
        }

        $root = realpath(dirname(__DIR__, 4));

        return rtrim(str_replace('\\', '/', $root ?: dirname(__DIR__, 4)), '/') . '/';
    }
}

if (!function_exists('zc_test_config_worker_token')) {
    function zc_test_config_worker_token(): ?string
    {
        $worker = getenv('ZC_TEST_WORKER');
        if (!is_string($worker) || $worker === '') {
            $worker = getenv('TEST_TOKEN');
        }

        if (!is_string($worker) || $worker === '') {
            return null;
        }

        $normalizedWorker = preg_replace('/[^A-Za-z0-9_]+/', '_', trim($worker));
        $normalizedWorker = trim((string)$normalizedWorker, '_');

        return $normalizedWorker === '' ? null : $normalizedWorker;
    }
}

if (!function_exists('zc_test_config_database_name')) {
    function zc_test_config_database_name(string $defaultDatabase): string
    {
        $override = getenv('ZC_TEST_DB_DATABASE');
        if (is_string($override) && $override !== '') {
            return $override;
        }

        $workerToken = zc_test_config_worker_token();
        if ($workerToken === null) {
            return $defaultDatabase;
        }

        return $defaultDatabase . '_' . $workerToken;
    }
}

if (!function_exists('zc_test_config_progress_file')) {
    function zc_test_config_progress_file(string $rootPath): string
    {
        $workerToken = zc_test_config_worker_token();
        if ($workerToken === null) {
            return rtrim($rootPath, '/') . '/progress.json';
        }

        return rtrim($rootPath, '/') . '/progress_' . $workerToken . '.json';
    }
}

if (!function_exists('zc_test_config_artifact_directory')) {
    function zc_test_config_artifact_directory(string $rootPath, string $context): string
    {
        $basePath = rtrim($rootPath, '/') . '/not_for_release/testFramework/logs/console/' . trim($context, '/') . '/';
        $workerToken = zc_test_config_worker_token();
        if ($workerToken === null) {
            return $basePath;
        }

        return $basePath . $workerToken . '/';
    }
}

if (!function_exists('zc_test_config_log_directory')) {
    function zc_test_config_log_directory(string $rootPath): string
    {
        $basePath = rtrim($rootPath, '/') . '/logs';
        $workerToken = zc_test_config_worker_token();
        if ($workerToken === null) {
            return $basePath;
        }

        return $basePath . '/' . $workerToken;
    }
}

if (!function_exists('zc_test_config_plugin_directory')) {
    function zc_test_config_plugin_directory(string $catalogPath, string $pluginName): string
    {
        $basePath = rtrim($catalogPath, '/') . '/zc_plugins/';
        $workerToken = zc_test_config_worker_token();
        if ($workerToken === null) {
            return $basePath . $pluginName;
        }

        return $basePath . $workerToken . '/' . $pluginName;
    }
}
