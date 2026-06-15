<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\TestFrameworkFilesystem;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\TrustedPluginClassLoader;

class TrustedPluginClassLoaderTest extends TestCase
{
    protected $preserveGlobalState = false;
    /**
     * @var string[]
     */
    private array $pluginRootsToRemove = [];

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';
    }

    protected function tearDown(): void
    {
        (new TestFrameworkFilesystem())->removePlugin('zenTestPlugin', 'v1.0.0', DIR_FS_CATALOG);
        foreach ($this->pluginRootsToRemove as $pluginRoot) {
            $this->removeDirectory(dirname($pluginRoot));
        }
        $this->pluginRootsToRemove = [];

        parent::tearDown();
    }

    /**
     * @runInSeparateProcess
     */
    public function testBootstrapTrustedPluginsLoadsPluginRootAutoloader(): void
    {
        (new TestFrameworkFilesystem())->installPlugin('zenTestPlugin', DIR_FS_CATALOG, DIR_FS_CATALOG);
        $pluginRoot = DIR_FS_CATALOG . 'zc_plugins/zenTestPlugin/v1.0.0';

        mkdir($pluginRoot . '/support', 0777, true);
        file_put_contents(
            $pluginRoot . '/support/LoaderFlag.php',
            <<<'PHP'
<?php

namespace ZenTestPlugin\Support;

class LoaderFlag
{
    public static function message(): string
    {
        return 'autoloaded';
    }
}
PHP
        );

        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            <<<'PHP'
<?php

/** @var \Aura\Autoload\Loader $psr4Autoloader */
$psr4Autoloader->addPrefix('ZenTestPlugin\\Support', __DIR__ . '/support/');
PHP
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $loader = new TrustedPluginClassLoader($psr4Autoloader);
        $loader->bootstrapTrustedPlugins(['zenTestPlugin' => 'v1.0.0']);

        $this->assertSame('autoloaded', \ZenTestPlugin\Support\LoaderFlag::message());
    }

    /**
     * @runInSeparateProcess
     */
    public function testBootstrapTrustedPluginsReportsAutoloaderErrorsWithoutThrowing(): void
    {
        $pluginKey = 'zenTestPluginError';
        $pluginRoot = $this->createPluginFixture($pluginKey);

        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\nthrow new RuntimeException('autoload failed');\n"
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $loader = new TrustedPluginClassLoader($psr4Autoloader);
        $loader->bootstrapTrustedPlugins([$pluginKey => 'v1.0.0']);

        $this->assertCount(1, $loader->getErrors());
        $this->assertStringContainsString(
            'Failed loading plugin autoloader from ' . $pluginKey . '/v1.0.0/psr4Autoload.php',
            $loader->getErrors()[0]
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testPluginRootAutoloaderIsLoadedOnlyOnceAcrossBootstrapAndDiscovery(): void
    {
        $pluginKey = 'zenTestPluginDedupe';
        $pluginRoot = $this->createPluginFixture($pluginKey);
        $markerFile = $pluginRoot . '/autoload-count.txt';

        mkdir($pluginRoot . '/Console/Commands', 0777, true);
        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\n\$count = file_exists(" . var_export($markerFile, true) . ") ? (int)file_get_contents(" . var_export($markerFile, true) . ") : 0;\nfile_put_contents(" . var_export($markerFile, true) . ", (string)(\$count + 1));\n"
        );
        file_put_contents(
            $pluginRoot . '/Console/Commands/DemoCommand.php',
            <<<'PHP'
<?php

namespace Zencart\Plugins\Console\ZenTestPluginDedupe\Commands;

use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class DemoCommand extends ConsoleCommand
{
    public function getName(): string
    {
        return 'zen-test:demo';
    }

    public function getDescription(): string
    {
        return 'demo';
    }

    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        return 0;
    }
}
PHP
        );
        file_put_contents(
            $pluginRoot . '/Console/commands.php',
            <<<'PHP'
<?php

return [
    \Zencart\Plugins\Console\ZenTestPluginDedupe\Commands\DemoCommand::class,
];
PHP
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $loader = new TrustedPluginClassLoader($psr4Autoloader);
        $loader->bootstrapTrustedPlugins([$pluginKey => 'v1.0.0']);

        $discovery = new \Zencart\Console\PluginCommandDiscovery(
            DIR_FS_CATALOG . 'zc_plugins',
            $psr4Autoloader,
            [$pluginKey => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertCount(1, $commands);
        $this->assertSame('1', file_get_contents($markerFile));
    }

    private function createPluginFixture(string $pluginKey): string
    {
        $pluginRoot = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/v1.0.0';
        mkdir($pluginRoot . '/Console', 0777, true);
        file_put_contents(
            $pluginRoot . '/manifest.php',
            <<<'PHP'
<?php

return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Fixture Plugin',
    'pluginDescription' => 'Fixture plugin',
    'pluginAuthor' => 'Zen Cart Development Team',
    'pluginId' => null,
    'zcVersions' => [],
    'changelog' => '',
    'github_repo' => '',
    'pluginGroups' => [],
];
PHP
        );

        $this->pluginRootsToRemove[] = $pluginRoot;

        return $pluginRoot;
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . '/' . $item;
            if (is_dir($itemPath)) {
                $this->removeDirectory($itemPath);
                continue;
            }

            unlink($itemPath);
        }

        rmdir($path);
    }
}
