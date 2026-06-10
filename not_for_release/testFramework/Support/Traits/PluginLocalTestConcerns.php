<?php

namespace Tests\Support\Traits;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Zencart\DbRepositories\PluginControlRepository;
use Zencart\DbRepositories\PluginControlVersionRepository;
use Zencart\PluginManager\PluginManager;
use Zencart\PluginSupport\Installer;
use Zencart\PluginSupport\InstallerFactory;
use Zencart\PluginSupport\PluginErrorContainer;
use Zencart\PluginSupport\ScriptedInstallerFactory;
use Zencart\PluginSupport\SqlPatchInstaller;

trait PluginLocalTestConcerns
{
    protected function bootPluginLocalTest(string $testPath): array
    {
        $pluginRoot = $this->pluginLocalRoot($testPath);
        $metadata = $this->pluginLocalMetadata($pluginRoot);
        $bootstrap = $pluginRoot . '/tests/' . ($metadata['bootstrap'] ?? 'bootstrap.php');

        if (is_file($bootstrap)) {
            require_once $bootstrap;
        }

        return $metadata;
    }

    protected function installCurrentPluginToFilesystem(string $testPath): void
    {
        $pluginRoot = $this->pluginLocalRoot($testPath);
        $pluginName = basename(dirname($pluginRoot));
        $version = basename($pluginRoot);
        $this->installPluginFromLocalSource($pluginName, $version, $pluginRoot);
    }

    protected function installCurrentPluginThroughInstaller(string $testPath): void
    {
        global $db;

        $pluginRoot = $this->pluginLocalRoot($testPath);
        $pluginName = basename(dirname($pluginRoot));
        $version = basename($pluginRoot);

        $this->installCurrentPluginToFilesystem($testPath);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $_SESSION ??= [];
        }
        $_SESSION['language'] ??= 'english';
        $_SESSION['languages_code'] ??= 'en';

        $this->ensurePluginInstallerRuntime();

        $pluginManager = new PluginManager(
            new PluginControlRepository($db),
            new PluginControlVersionRepository($db)
        );
        $pluginManager->inspectAndUpdate();

        $errorContainer = new PluginErrorContainer();
        $pluginInstaller = new Installer(
            new SqlPatchInstaller($db, $errorContainer),
            new ScriptedInstallerFactory($db, $errorContainer),
            $errorContainer
        );
        $installer = (new InstallerFactory($db, $pluginInstaller, $errorContainer))->make($pluginName, $version);

        if (!$installer->processInstall($pluginName, $version)) {
            throw new RuntimeException(sprintf(
                'Unable to install plugin-local test plugin: %s %s',
                $pluginName,
                $version
            ));
        }
    }

    protected function ensurePluginInstallerRuntime(): void
    {
        global $db;

        if (!class_exists('queryFactory')) {
            require_once ROOTCWD . 'includes/classes/db/' . DB_TYPE . '/query_factory.php';
        }

        if ($db === null) {
            $db = new \queryFactory();
            $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, 'unused', false);
        }

        require_once ROOTCWD . 'includes/functions/database.php';
        require_once ROOTCWD . 'includes/functions/zen_define_default.php';
        require_once ROOTCWD . 'admin/includes/functions/admin_access.php';
        require_once ROOTCWD . 'admin/includes/classes/class.admin.zcObserverLogEventListener.php';

        $GLOBALS['zco_notifier'] ??= new class {
            public function notify(): void
            {
            }
        };

        $this->definePluginInstallerConstant('PLUGIN_INSTALL_SQL_FAILURE', 'one or more database errors occurred');
        $this->definePluginInstallerConstant('ERROR_REMOVE_FILES_CONTEXT', 'Invalid context supplied (%s), it must be either "catalog" or "admin".');
        $this->definePluginInstallerConstant('ERROR_UNKNOWN_FAILURE_INSTALL', 'install');
        $this->definePluginInstallerConstant('ERROR_UNKNOWN_FAILURE_UNINSTALL', 'un-install');
        $this->definePluginInstallerConstant('ERROR_UNKNOWN_FAILURE_UPGRADE', 'upgrade');
        $this->definePluginInstallerConstant('ERROR_UNKNOWN_FAILURE_DISABLE', 'disable');
        $this->definePluginInstallerConstant('ERROR_UNKNOWN_FAILURE_ENABLE', 'enable');
    }

    protected function definePluginInstallerConstant(string $name, string $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    protected function installPluginFromLocalSource(string $pluginName, string $version, string $sourceVersionDirectory): void
    {
        $destinationPluginDirectory = zc_test_config_plugin_directory(DIR_FS_CATALOG, $pluginName);
        $destinationVersionDirectory = rtrim($destinationPluginDirectory, '/') . '/' . $version;
        $sourceRealPath = realpath($sourceVersionDirectory);
        $destinationRealPath = realpath($destinationVersionDirectory);

        if ($sourceRealPath !== false && $destinationRealPath !== false && $sourceRealPath === $destinationRealPath) {
            return;
        }

        (new Filesystem())->mirror($sourceVersionDirectory, $destinationVersionDirectory);
    }

    protected function pluginLocalRoot(string $testPath): string
    {
        $path = is_dir($testPath) ? $testPath : dirname($testPath);
        $realPath = realpath($path) ?: $path;

        while ($realPath !== dirname($realPath)) {
            if (basename($realPath) === 'tests') {
                return dirname($realPath);
            }

            $realPath = dirname($realPath);
        }

        throw new RuntimeException(sprintf('Unable to locate plugin-local tests directory from path: %s', $testPath));
    }

    protected function pluginLocalMetadata(string $pluginRoot): array
    {
        $metadataPath = rtrim($pluginRoot, '/') . '/tests/plugin-test.php';
        $metadata = is_file($metadataPath) ? require $metadataPath : [];

        if (!is_array($metadata)) {
            throw new RuntimeException(sprintf('Plugin test metadata must return an array: %s', $metadataPath));
        }

        return array_merge([
            'plugin' => basename(dirname($pluginRoot)),
            'version' => basename($pluginRoot),
            'bootstrap' => 'bootstrap.php',
        ], $metadata);
    }
}
