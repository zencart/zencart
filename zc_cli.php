<?php
/**
 * Zen Cart console entry point.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

$psr4Autoloader = require __DIR__ . '/includes/application_cli_bootstrap.php';
$dbContext = zc_cli_get_db_context();
$pluginRepositoryContext = zc_cli_get_plugin_repository_context();
$trustedPluginContext = zc_cli_resolve_trusted_plugin_versions(
    $pluginRepositoryContext['repository'],
    $pluginRepositoryContext['warnings']
);

$input = new \Zencart\Console\ConsoleInput($_SERVER['argv'] ?? []);
$output = new \Zencart\Console\ConsoleOutput();
$pluginDiscovery = new \Zencart\Console\PluginCommandDiscovery(
    __DIR__ . '/zc_plugins',
    $psr4Autoloader,
    $trustedPluginContext['plugins']
);
$pluginListProvider = static function () use ($pluginRepositoryContext): ?array {
    return $pluginRepositoryContext['repository']?->getAll();
};
$versionProvider = static function () use ($dbContext): array {
    if ($dbContext['db'] === null) {
        return [];
    }

    $repository = new \Zencart\DbRepositories\ProjectVersionRepository($dbContext['db']);

    return [
        'Zen-Cart Main' => $repository->getByKey('Zen-Cart Main'),
        'Zen-Cart Database' => $repository->getByKey('Zen-Cart Database'),
    ];
};
$configurationProvider = static function (string $key) use ($dbContext): ?array {
    if ($dbContext['db'] === null) {
        return null;
    }

    return (new \Zencart\DbRepositories\ConfigurationRepository($dbContext['db']))->getByKey($key);
};
$kernel = new \Zencart\Console\ConsoleKernel(
    null,
    $pluginDiscovery,
    $trustedPluginContext['warnings'],
    $pluginListProvider,
    $versionProvider,
    $configurationProvider
);

exit($kernel->run($input, $output));
