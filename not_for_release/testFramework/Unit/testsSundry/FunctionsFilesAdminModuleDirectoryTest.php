<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use Tests\Support\zcTemplateResolverTest;

/**
 * Covers zen_get_admin_module_from_directory()'s plugin-aware fallback: when a product
 * type's admin module file (collect_info.php, update_product.php, etc.) isn't shipped in
 * core's DIR_WS_MODULES, an installed plugin that registered the product type can serve
 * it directly from its own admin/includes/modules/<type_handler>/ directory, without the
 * file being physically copied into DIR_WS_MODULES.
 */
class FunctionsFilesAdminModuleDirectoryTest extends zcTemplateResolverTest
{
    private const TEST_PLUGIN = 'UnitTestAdminModulePlugin';
    private const TEST_VERSION = 'v1.0.0';
    private const TEST_HANDLER = 'unit_test_product_type';

    private string $pluginRoot;
    private string $pluginModuleDir;

    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_lookups.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_files.php';

        $this->pluginRoot = DIR_FS_CATALOG . 'zc_plugins/' . self::TEST_PLUGIN . '/' . self::TEST_VERSION . '/';
        $this->pluginModuleDir = $this->pluginRoot . 'admin/includes/modules/' . self::TEST_HANDLER . '/';

        $this->removeDirectory($this->pluginRoot);

        $GLOBALS['installedPlugins'] = [];
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->pluginRoot);
        unset($GLOBALS['installedPlugins']);
        parent::tearDown();
    }

    public function testFallsBackToPluginModuleDirectoryWhenCoreFileIsMissing(): void
    {
        $this->mockDbForTypeHandler(self::TEST_HANDLER);
        $this->registerTestPlugin();
        $this->writeModuleFixture('collect_info.php');

        $resolved = zen_get_admin_module_from_directory(1, 'collect_info.php');

        $this->assertSame($this->pluginModuleDir . 'collect_info.php', $resolved);
    }

    public function testDirOnlyReturnsPluginModuleDirectory(): void
    {
        $this->mockDbForTypeHandler(self::TEST_HANDLER);
        $this->registerTestPlugin();
        $this->writeModuleFixture('update_product.php');

        $resolved = zen_get_admin_module_from_directory(1, 'update_product.php', true);

        $this->assertSame($this->pluginModuleDir, $resolved);
    }

    public function testFallsThroughToDefaultCoreLocationWhenNoPluginProvidesTheFile(): void
    {
        $this->mockDbForTypeHandler(self::TEST_HANDLER);
        $this->registerTestPlugin();
        // Plugin directory is registered, but never gets the fixture file written into it.

        $resolved = zen_get_admin_module_from_directory(1, 'collect_info.php');

        $this->assertSame(DIR_WS_MODULES . 'collect_info.php', $resolved);
    }

    public function testFallsThroughToDefaultCoreLocationWhenNoPluginsAreInstalled(): void
    {
        $this->mockDbForTypeHandler(self::TEST_HANDLER);
        // $GLOBALS['installedPlugins'] stays empty.

        $resolved = zen_get_admin_module_from_directory(1, 'collect_info.php');

        $this->assertSame(DIR_WS_MODULES . 'collect_info.php', $resolved);
    }

    public function testIgnoresOtherInstalledPluginsThatDoNotOwnTheProductType(): void
    {
        $this->mockDbForTypeHandler(self::TEST_HANDLER);

        $otherPluginRoot = DIR_FS_CATALOG . 'zc_plugins/UnitTestUnrelatedPlugin/v1.0.0/';
        $this->removeDirectory($otherPluginRoot);
        $GLOBALS['installedPlugins'] = [
            ['unique_key' => 'UnitTestUnrelatedPlugin', 'version' => 'v1.0.0'],
        ];

        $resolved = zen_get_admin_module_from_directory(1, 'collect_info.php');

        $this->assertSame(DIR_WS_MODULES . 'collect_info.php', $resolved);

        $this->removeDirectory($otherPluginRoot);
    }

    public function testCoreFileTakesPriorityOverAPluginProvidedOne(): void
    {
        $this->mockDbForTypeHandler(self::TEST_HANDLER);
        $this->registerTestPlugin();
        $this->writeModuleFixture('collect_info.php');

        $coreOverrideDir = DIR_WS_MODULES . self::TEST_HANDLER . '/';
        @mkdir($coreOverrideDir, 0777, true);
        file_put_contents($coreOverrideDir . 'collect_info.php', "<?php\n");

        try {
            $resolved = zen_get_admin_module_from_directory(1, 'collect_info.php');

            $this->assertSame($coreOverrideDir . 'collect_info.php', $resolved);
        } finally {
            $this->removeDirectory($coreOverrideDir);
        }
    }

    /**
     * instantiateQfr() (from zcTemplateResolverTest) doesn't set the mock result's EOF
     * property, which defaults to true on the real queryFactoryResult class -- that would
     * make zen_get_handler_from_type()'s "not found" check always trip. Mock it directly
     * here instead, with EOF explicitly false.
     */
    private function mockDbForTypeHandler(string $typeHandler): void
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->fields = ['type_handler' => $typeHandler];
        $qfr->EOF = false;

        $GLOBALS['db'] = $this->getMockBuilder('queryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['db']->method('execute')->willReturn($qfr);
    }

    private function registerTestPlugin(): void
    {
        @mkdir($this->pluginRoot, 0777, true);
        $GLOBALS['installedPlugins'] = [
            ['unique_key' => self::TEST_PLUGIN, 'version' => self::TEST_VERSION],
        ];
    }

    private function writeModuleFixture(string $filename): void
    {
        @mkdir($this->pluginModuleDir, 0777, true);
        file_put_contents($this->pluginModuleDir . $filename, "<?php\n");
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($directory);
    }
}
