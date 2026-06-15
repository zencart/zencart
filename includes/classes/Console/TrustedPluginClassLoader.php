<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Console;

use Aura\Autoload\Loader;
use Throwable;

class TrustedPluginClassLoader
{
    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @var array<int, array<string, true>>
     */
    private static array $loadedAutoloaderFilesByLoader = [];

    public function __construct(private ?Loader $psr4Autoloader = null)
    {
    }

    /**
     * @param array<string, string> $trustedPlugins
     */
    public function bootstrapTrustedPlugins(array $trustedPlugins): void
    {
        $this->errors = [];
        $this->loadPluginBootstrapFiles($trustedPlugins);
        $this->registerPluginClassNamespaces($trustedPlugins);
        $this->loadPluginRootAutoloaders($trustedPlugins);
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array<string, string> $trustedPlugins
     */
    public function registerPluginClassNamespaces(array $trustedPlugins): void
    {
        if ($this->psr4Autoloader === null) {
            return;
        }

        foreach ($trustedPlugins as $uniqueKey => $version) {
            $namespaceAdmin = 'Zencart\\Plugins\\Admin\\' . ucfirst($uniqueKey);
            $namespaceCatalog = 'Zencart\\Plugins\\Catalog\\' . ucfirst($uniqueKey);
            $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $uniqueKey . '/' . $version . '/';

            $this->psr4Autoloader->addPrefix($namespaceAdmin, $filePath . 'admin/includes/classes/');
            $this->psr4Autoloader->addPrefix($namespaceCatalog, $filePath . 'catalog/includes/classes/');
        }
    }

    /**
     * @param array<string, string> $trustedPlugins
     */
    public function loadPluginRootAutoloaders(array $trustedPlugins): void
    {
        if ($this->psr4Autoloader === null) {
            return;
        }

        foreach ($trustedPlugins as $uniqueKey => $version) {
            $autoloadFile = DIR_FS_CATALOG . 'zc_plugins/' . $uniqueKey . '/' . $version . '/psr4Autoload.php';
            if (!file_exists($autoloadFile)) {
                continue;
            }

            try {
                self::loadPluginRootAutoloaderFile($autoloadFile, $this->psr4Autoloader);
            } catch (Throwable $exception) {
                $pluginReference = $uniqueKey . '/' . $version;
                $this->errors[] = sprintf(
                    'Failed loading plugin autoloader from %s: %s',
                    $pluginReference . '/psr4Autoload.php',
                    self::sanitizeErrorMessage($exception->getMessage(), dirname($autoloadFile), $pluginReference)
                );
            }
        }
    }

    /**
     * @param array<string, string> $trustedPlugins
     */
    public function loadPluginBootstrapFiles(array $trustedPlugins): void
    {
        foreach ($trustedPlugins as $uniqueKey => $version) {
            $pluginRoot = DIR_FS_CATALOG . 'zc_plugins/' . $uniqueKey . '/' . $version;
            $pluginReference = $uniqueKey . '/' . $version;

            $this->loadPluginBootstrapDirectory($pluginRoot . '/catalog/includes/extra_configures', $pluginReference);
            $this->loadPluginBootstrapDirectory($pluginRoot . '/catalog/includes/extra_datafiles', $pluginReference);
            $this->loadPluginBootstrapDirectory($pluginRoot . '/admin/includes/extra_configures', $pluginReference);
            $this->loadPluginBootstrapDirectory($pluginRoot . '/admin/includes/extra_datafiles', $pluginReference);
            $this->loadPluginBootstrapFile($pluginRoot . '/database_tables.php', $pluginReference);
            $this->loadPluginBootstrapFile($pluginRoot . '/filenames.php', $pluginReference);
        }
    }

    public static function loadPluginRootAutoloaderFile(string $autoloadFile, Loader $psr4Autoloader): void
    {
        $loaderId = spl_object_id($psr4Autoloader);
        $normalizedPath = str_replace('\\', '/', realpath($autoloadFile) ?: $autoloadFile);
        if (isset(self::$loadedAutoloaderFilesByLoader[$loaderId][$normalizedPath])) {
            return;
        }

        self::includePhpFile($autoloadFile, ['psr4Autoloader' => $psr4Autoloader]);
        self::$loadedAutoloaderFilesByLoader[$loaderId][$normalizedPath] = true;
    }

    /**
     * @param array<string, mixed> $scopeVariables
     */
    private static function includePhpFile(string $file, array $scopeVariables = []): mixed
    {
        extract($scopeVariables, \EXTR_SKIP);

        if (!is_file($file) || !is_readable($file)) {
            throw new \RuntimeException('PHP file is not readable: ' . $file);
        }

        $result = include $file;
        if ($result === false) {
            throw new \RuntimeException('PHP file failed to include: ' . $file);
        }

        return $result;
    }

    private function loadPluginBootstrapDirectory(string $directory, string $pluginReference): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $directoryHandle = @dir($directory);
        if ($directoryHandle === false) {
            return;
        }

        while (($file = $directoryHandle->read()) !== false) {
            if (preg_match('~^[^\._].*\.php$~i', $file) !== 1) {
                continue;
            }

            $this->loadPluginBootstrapFile($directory . '/' . $file, $pluginReference);
        }

        $directoryHandle->close();
    }

    private function loadPluginBootstrapFile(string $file, string $pluginReference): void
    {
        if (!file_exists($file)) {
            return;
        }

        try {
            self::includePhpFile($file);
        } catch (Throwable $exception) {
            $this->errors[] = sprintf(
                'Failed loading plugin bootstrap file from %s: %s',
                $pluginReference . '/' . ltrim(str_replace('\\', '/', substr($file, strlen(DIR_FS_CATALOG . 'zc_plugins/' . $pluginReference))), '/'),
                self::sanitizeErrorMessage($exception->getMessage(), dirname($file), $pluginReference)
            );
        }
    }

    private static function sanitizeErrorMessage(string $message, string $absolutePath, string $relativePath): string
    {
        $normalizedAbsolutePath = rtrim(str_replace('\\', '/', $absolutePath), '/');
        $normalizedRelativePath = rtrim(str_replace('\\', '/', $relativePath), '/');
        $absolutePathPattern = implode(
            '[\\\\/]',
            array_map(
                static fn(string $segment): string => preg_quote($segment, '~'),
                explode('/', $normalizedAbsolutePath)
            )
        );

        return (string)preg_replace_callback(
            '~' . $absolutePathPattern . '(?:[\\\\/][^:\s\'"]+)*~',
            static function (array $matches) use ($normalizedAbsolutePath, $normalizedRelativePath): string {
                $normalizedMatch = str_replace('\\', '/', $matches[0]);
                return $normalizedRelativePath . substr($normalizedMatch, strlen($normalizedAbsolutePath));
            },
            $message
        );
    }
}
