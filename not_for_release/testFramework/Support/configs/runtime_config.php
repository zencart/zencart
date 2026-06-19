<?php

if (!function_exists('zc_test_config_database_name')) {
    /**
     * Allow local runners to override the feature-test database name while
     * keeping a sensible default for checked-in DDEV configs.
     */
    function zc_test_config_database_name(string $default): string
    {
        $databaseName = getenv('ZENCART_TEST_DB_NAME')
            ?: getenv('DB_DATABASE')
            ?: $default;

        return (string) $databaseName;
    }
}
