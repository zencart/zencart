<?php

namespace Tests\Support\Traits;

use Zencart\DbRepositories\PluginControlRepository;
use Zencart\DbRepositories\PluginControlVersionRepository;
use Zencart\PluginManager\PluginManager;
use Zencart\PluginSupport\Installer;
use Zencart\PluginSupport\InstallerFactory;
use Zencart\PluginSupport\PluginErrorContainer;
use Zencart\PluginSupport\PluginStatus;
use Zencart\PluginSupport\ScriptedInstallerFactory;
use Zencart\PluginSupport\SqlPatchInstaller;
use Tests\Support\TestConfigResolver;
use Tests\Support\TestFrameworkFilesystem;


trait GeneralConcerns
{
    private ?TestFrameworkFilesystem $testFrameworkFilesystem = null;
    /**
     * @var array<string, string>
     */
    private array $managedPluginFixtures = [];

    public static function detectUser()
    {
        return TestConfigResolver::detectUser();
    }

    public static function loadConfigureFile($context)
    {
        if ($context !== 'main' && defined('HTTP_SERVER') && defined('DB_TYPE')) {
            return;
        }

        return TestConfigResolver::loadConfig($context, TESTCWD . 'Support/configs/');
    }


    public static function loadMigrationAndSeeders($mainConfigs = [])
    {
        self::databaseSetup(); //setup Capsule
        self::runDatabaseLoader($mainConfigs);
    }

    public static function locateElementInPageSource(string $element_lookup_text, string $page_source, int $length = 1500): string
    {
        $position = strpos($page_source, $element_lookup_text);
        // if not found, return whole $page_source; but if found, only return a portion of the page
        return ($position === false) ? $page_source : substr($page_source, $position, $length);
    }

    /**
     * @param $page
     * @return mixed
     * @todo refactor - use zen_href_link
     */
    protected function buildStoreLink($page)
    {
        $URI = HTTP_SERVER . '/index.php?main_page='.$page;
        return $URI;
    }
    protected function buildAdminLink($page)
    {
        $adminPath = defined('DIR_WS_ADMIN') ? DIR_WS_ADMIN : '/admin/';
        $URI = HTTP_SERVER . '/' . trim($adminPath, '/') . '/index.php?cmd='.$page;
        return $URI;
    }


    protected function browserAdminLogin()
    {
        $this->runCustomSeeder('StoreWizardSeeder');
        if (!method_exists($this, 'submitAdminLogin')) {
            throw new \LogicException('Admin login helper requires submitAdminLogin support.');
        }

        $response = $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ]);

        if (is_object($response) && method_exists($response, 'assertOk')) {
            $response->assertOk();
        }
    }

    // PLUGIN STUFF

    protected function installPluginToFilesystem(string $pluginName, string $version): void
    {
        $this->filesystemHelper()->installPlugin($pluginName, DIR_FS_CATALOG, ROOTCWD);
        $this->managedPluginFixtures[$pluginName] = $version;
    }

    protected function removePlugin(string $pluginName, string $version): void
    {
        $this->filesystemHelper()->removePlugin($pluginName, $version, DIR_FS_CATALOG);
        unset($this->managedPluginFixtures[$pluginName]);
    }

    protected function filesystemHelper(): TestFrameworkFilesystem
    {
        if (!$this->testFrameworkFilesystem instanceof TestFrameworkFilesystem) {
            $this->testFrameworkFilesystem = new TestFrameworkFilesystem();
        }

        return $this->testFrameworkFilesystem;
    }

    protected function cleanupManagedPlugins(): void
    {
        if ($this->managedPluginFixtures === []) {
            return;
        }

        foreach ($this->managedPluginFixtures as $pluginName => $version) {
            $this->bestEffortUninstallManagedPlugin($pluginName, $version);
            $this->filesystemHelper()->removePlugin($pluginName, $version, DIR_FS_CATALOG);
        }

        $this->syncPluginManagerState();
        $this->managedPluginFixtures = [];
    }

    private function bestEffortUninstallManagedPlugin(string $pluginName, string $version): void
    {
        if (!$this->canManagePluginRuntime()) {
            return;
        }

        global $db;

        $plugin = (new PluginControlRepository($db))->getAll()[$pluginName] ?? null;
        if (!is_array($plugin)) {
            return;
        }

        $installedVersion = (string)($plugin['version'] ?? '');
        $status = (int)($plugin['status'] ?? PluginStatus::NOT_INSTALLED);
        if ($installedVersion === '' || $status === PluginStatus::NOT_INSTALLED) {
            return;
        }

        $_SESSION ??= [];
        $_SESSION['language'] ??= 'english';
        $_SESSION['languages_code'] ??= 'en';

        $this->definePluginInstallerConstant('ERROR_UNKNOWN_FAILURE', '%s');
        $this->definePluginInstallerConstant('ERROR_UNKNOWN_FAILURE_UNINSTALL', 'un-install');

        $errorContainer = new PluginErrorContainer();
        $pluginInstaller = new Installer(
            new SqlPatchInstaller($db, $errorContainer),
            new ScriptedInstallerFactory($db, $errorContainer),
            $errorContainer
        );

        try {
            $installer = (new InstallerFactory($db, $pluginInstaller, $errorContainer))->make($pluginName, $installedVersion);
            $installer->processUninstall($pluginName, $installedVersion);
        } catch (\Throwable) {
            // Cleanup should not fail the test teardown; plugin-manager state is resynced after file removal.
        }
    }

    private function syncPluginManagerState(): void
    {
        if (!$this->canManagePluginRuntime()) {
            return;
        }

        global $db;

        $_SESSION ??= [];
        $_SESSION['languages_code'] ??= 'en';

        (new PluginManager(
            new PluginControlRepository($db),
            new PluginControlVersionRepository($db)
        ))->inspectAndUpdate();
    }

    private function canManagePluginRuntime(): bool
    {
        global $db;

        return defined('DIR_FS_CATALOG')
            && defined('TABLE_PLUGIN_CONTROL')
            && $db !== null;
    }

    private function definePluginInstallerConstant(string $name, string $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}
