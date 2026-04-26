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

if (!function_exists('zc_test_config_admin_directory')) {
    function zc_test_config_admin_directory(?string $catalogPath = null): array
    {
        $catalogPath = rtrim(str_replace('\\', '/', $catalogPath ?? zc_test_config_catalog_path()), '/') . '/';
        $defaultPath = $catalogPath . 'admin/';
        if (is_file($defaultPath . 'includes/application_bootstrap.php')) {
            return [
                'basename' => 'admin',
                'path' => $defaultPath,
                'web_path' => '/admin/',
            ];
        }

        $entries = scandir(rtrim($catalogPath, '/')) ?: [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $candidatePath = $catalogPath . $entry . '/';
            if (!is_dir($candidatePath)) {
                continue;
            }

            if (is_file($candidatePath . 'includes/application_bootstrap.php')) {
                return [
                    'basename' => $entry,
                    'path' => $candidatePath,
                    'web_path' => '/' . $entry . '/',
                ];
            }
        }

        return [
            'basename' => 'admin',
            'path' => $defaultPath,
            'web_path' => '/admin/',
        ];
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

        $baseName = getenv('ZC_TEST_DB_BASE_NAME');
        if (is_string($baseName) && $baseName !== '') {
            $defaultDatabase = $baseName;
        }

        $workerToken = zc_test_config_worker_token();
        if ($workerToken === null) {
            return $defaultDatabase;
        }

        return $defaultDatabase . '_' . $workerToken;
    }
}

if (!function_exists('zc_test_config_bool')) {
    function zc_test_config_bool(string $envName, bool $default): bool
    {
        $value = getenv($envName);
        if (!is_string($value) || $value === '') {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $parsed ?? $default;
    }
}

if (!function_exists('zc_test_config_value')) {
    function zc_test_config_value(string $envName, mixed $default): mixed
    {
        $value = getenv($envName);

        return is_string($value) && $value !== '' ? $value : $default;
    }
}

if (!function_exists('zc_test_config_mailserver_options')) {
    function zc_test_config_mailserver_options(): array
    {
        return [
            'use-mailserver' => zc_test_config_bool('ZC_TEST_USE_MAILSERVER', false),
            'mailserver-port' => (int) zc_test_config_value('ZC_TEST_MAILSERVER_PORT', 1025),
            'mailserver-host' => zc_test_config_value('ZC_TEST_MAILSERVER_HOST', 'localhost'),
            'mailserver-user' => zc_test_config_value('ZC_TEST_MAILSERVER_USER', 'ddev'),
            'mailserver-password' => zc_test_config_value('ZC_TEST_MAILSERVER_PASSWORD', 'mailpit'),
        ];
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
