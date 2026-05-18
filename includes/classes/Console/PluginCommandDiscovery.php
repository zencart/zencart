<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console;

use DirectoryIterator;
use Throwable;

class PluginCommandDiscovery
{
    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @since ZC v3.0.0
     */
    public function __construct(
        private string $pluginRootPath,
        private ?\Aura\Autoload\Loader $autoloader = null,
        private ?array $allowedPluginVersions = null
    ) {
    }

    /**
     * @since ZC v3.0.0
     *
     * @return ConsoleCommand[]
     */
    public function discover(): array
    {
        $commands = [];
        $this->errors = [];

        if (!is_dir($this->pluginRootPath)) {
            return [];
        }

        foreach (new DirectoryIterator($this->pluginRootPath) as $pluginDirectory) {
            if ($pluginDirectory->isDot() || !$pluginDirectory->isDir()) {
                continue;
            }

            foreach (new DirectoryIterator($pluginDirectory->getPathname()) as $versionDirectory) {
                if ($versionDirectory->isDot() || !$versionDirectory->isDir()) {
                    continue;
                }

                $versionPath = $versionDirectory->getPathname();
                if (!file_exists($versionPath . '/manifest.php')) {
                    continue;
                }

                if (!$this->isAllowedPluginVersion($pluginDirectory->getFilename(), $versionDirectory->getFilename())) {
                    continue;
                }

                $this->registerPluginConsoleNamespace($pluginDirectory->getFilename(), $versionPath);
                $commands = array_merge(
                    $commands,
                    $this->loadCommandsFromVersion(
                        $pluginDirectory->getFilename(),
                        $versionDirectory->getFilename(),
                        $versionPath
                    )
                );
            }
        }

        return $commands;
    }

    /**
     * @since ZC v3.0.0
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @since ZC v3.0.0
     */
    private function registerPluginConsoleNamespace(string $pluginKey, string $versionPath): void
    {
        if ($this->autoloader === null) {
            return;
        }

        $consolePath = $versionPath . '/Console/';
        if (!is_dir($consolePath)) {
            return;
        }

        $namespace = 'Zencart\\Plugins\\Console\\' . $this->normalizePluginNamespace($pluginKey);
        $this->autoloader->addPrefix($namespace, $consolePath);
    }

    /**
     * @since ZC v3.0.0
     *
     * @return ConsoleCommand[]
     */
    private function loadCommandsFromVersion(string $pluginKey, string $pluginVersion, string $versionPath): array
    {
        $commandFile = $versionPath . '/Console/commands.php';
        if (!file_exists($commandFile)) {
            return [];
        }

        $definitionReference = $pluginKey . '/' . $pluginVersion . '/Console/commands.php';

        try {
            $definitions = require $commandFile;
        } catch (Throwable $exception) {
            $this->errors[] = sprintf(
                'Failed loading plugin commands from %s: %s',
                $definitionReference,
                $exception->getMessage()
            );
            return [];
        }

        if (!is_array($definitions)) {
            $this->errors[] = 'Plugin command definition file must return an array: ' . $definitionReference;
            return [];
        }

        $commands = [];
        foreach ($definitions as $definition) {
            try {
                $commands[] = $this->resolveCommandDefinition($definition);
            } catch (Throwable $exception) {
                $this->errors[] = sprintf(
                    'Invalid plugin command definition in %s: %s',
                    $definitionReference,
                    $exception->getMessage()
                );
            }
        }

        return $commands;
    }

    /**
     * @since ZC v3.0.0
     */
    private function isAllowedPluginVersion(string $pluginKey, string $pluginVersion): bool
    {
        if ($this->allowedPluginVersions === null) {
            return true;
        }

        return ($this->allowedPluginVersions[$pluginKey] ?? null) === $pluginVersion;
    }

    /**
     * @since ZC v3.0.0
     */
    private function resolveCommandDefinition(mixed $definition): ConsoleCommand
    {
        if ($definition instanceof ConsoleCommand) {
            return $definition;
        }

        if (is_string($definition) && class_exists($definition) && is_subclass_of($definition, ConsoleCommand::class)) {
            return new $definition();
        }

        throw new \InvalidArgumentException('Definitions must be ConsoleCommand instances or ConsoleCommand class names.');
    }

    /**
     * @since ZC v3.0.0
     */
    private function normalizePluginNamespace(string $pluginKey): string
    {
        $segments = preg_split('/[^a-zA-Z0-9]+/', $pluginKey) ?: [];
        $segments = array_filter($segments, static fn ($segment) => $segment !== '');

        if ($segments === []) {
            return 'Plugin';
        }

        return implode('', array_map(static fn ($segment) => ucfirst((string) $segment), $segments));
    }
}
