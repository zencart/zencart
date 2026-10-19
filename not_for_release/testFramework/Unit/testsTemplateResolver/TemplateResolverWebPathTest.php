<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcTemplateResolverTest;
use Zencart\ResourceLoaders\TemplateResolver;

/**
 * Template web paths are prefixed with DIR_WS_CATALOG. The test bootstrap defines that constant as '/',
 * so each test here runs in its own process and defines a sub-directory value before the bootstrap runs.
 */
#[RunTestsInSeparateProcesses]
class TemplateResolverWebPathTest extends zcTemplateResolverTest
{
    private string $root;

    public function setUp(): void
    {
        // The constant must exist before parent::setUp() runs the bootstrap, which only defines it if missing.
        define('DIR_WS_CATALOG', str_contains($this->name(), 'WithoutTrailingSlash') ? '/shop' : '/shop/');

        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $this->root = sys_get_temp_dir() . '/zencart-template-webpath-' . uniqid('', true);
        mkdir($this->root . '/includes/templates/core_theme', 0777, true);
        file_put_contents($this->root . '/includes/templates/core_theme/template_info.php', "<?php\n\$template_name = 'Core';\n");
        $pluginTemplateDir = $this->root . '/zc_plugins/ThemeP/v1.0.0/catalog/includes/templates/plugin_theme';
        mkdir($pluginTemplateDir, 0777, true);
        file_put_contents($pluginTemplateDir . '/template_info.php', "<?php\n\$template_name = 'Plugin';\n");
        file_put_contents(
            $this->root . '/zc_plugins/ThemeP/v1.0.0/manifest.php',
            "<?php\nreturn ['template' => ['key' => 'plugin_theme']];\n"
        );
        $this->instantiateQfr(['template_id' => '1']);
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->root);
        parent::tearDown();
    }

    public function testWebPathsAreRootedAtTheCatalogSubDirectory(): void
    {
        $resolver = $this->makeResolver();

        $this->assertSame('/shop/', DIR_WS_CATALOG);
        $this->assertSame('/shop/includes/templates/core_theme/', $resolver->getTemplateWebPath('core_theme'));
        $this->assertSame(
            '/shop/zc_plugins/ThemeP/v1.0.0/catalog/includes/templates/plugin_theme/',
            $resolver->getTemplateWebPath('plugin_theme')
        );
    }

    public function testWebPathsWithoutTrailingSlashOnTheCatalogSubDirectory(): void
    {
        $resolver = $this->makeResolver();

        $this->assertSame('/shop', DIR_WS_CATALOG);
        $this->assertSame('/shop/includes/templates/core_theme/', $resolver->getTemplateWebPath('core_theme'));
        $this->assertSame(
            '/shop/zc_plugins/ThemeP/v1.0.0/catalog/includes/templates/plugin_theme/',
            $resolver->getTemplateWebPath('plugin_theme')
        );
    }

    private function makeResolver(): TemplateResolver
    {
        return new TemplateResolver(
            $this->root,
            $this->root . '/includes/templates',
            $this->root . '/zc_plugins',
            [['unique_key' => 'ThemeP', 'version' => 'v1.0.0']]
        );
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
