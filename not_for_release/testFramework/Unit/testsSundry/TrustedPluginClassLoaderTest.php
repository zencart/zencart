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
}
