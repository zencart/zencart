<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcTemplateResolverTest;
use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\SideboxFinder;
use Zencart\ResourceLoaders\TemplateResolver;

/**
 * Pins SideboxFinder's directory precedence and returned path strings using a temporary catalog tree.
 */
#[RunTestsInSeparateProcesses]
class SideboxFinderPathsTest extends zcTemplateResolverTest
{
    private const FEATURE_PLUGIN = 'FeatP';
    private const THEME_PLUGIN = 'ThemeP';

    private string $root;
    private string $featureRelative;
    private string $themeRelative;

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/SideboxFinder.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';

        $this->root = sys_get_temp_dir() . '/zencart-sidebox-paths-' . uniqid('', true);
        $this->featureRelative = 'zc_plugins/' . self::FEATURE_PLUGIN . '/v1.0.0/catalog/includes/modules/sideboxes/';
        $this->themeRelative = 'zc_plugins/' . self::THEME_PLUGIN . '/v1.0.0/catalog/includes/modules/sideboxes/ptheme/';

        foreach (['template_default', 'tpl'] as $key) {
            $this->writeFile('includes/templates/' . $key . '/template_info.php', "<?php\n\$template_name = '" . $key . "';\n");
        }
        $this->writeFile('zc_plugins/' . self::THEME_PLUGIN . '/v1.0.0/manifest.php', <<<'PHP'
<?php
return ['template' => ['key' => 'ptheme', 'baseTemplate' => 'tpl']];
PHP);
        $this->writeFile(
            'zc_plugins/' . self::THEME_PLUGIN . '/v1.0.0/catalog/includes/templates/ptheme/template_info.php',
            "<?php\n\$template_name = 'ptheme';\n"
        );

        $this->writeFile('includes/modules/sideboxes/a.php', "<?php\n");
        $this->writeFile('includes/modules/sideboxes/b.php', "<?php\n");
        $this->writeFile('includes/modules/sideboxes/e.php', "<?php\n");
        $this->writeFile('includes/modules/sideboxes/tpl/b.php', "<?php\n");
        $this->writeFile($this->themeRelative . 'b.php', "<?php\n");
        $this->writeFile($this->themeRelative . 'd.php', "<?php\n");
        $this->writeFile($this->featureRelative . 'a.php', "<?php\n");
        $this->writeFile($this->featureRelative . 'c.php', "<?php\n");

        $this->instantiateQfr(['template_id' => '1']);
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->root);
        parent::tearDown();
    }

    public function testFindFromFilesystemLayersBaseThenPluginThenTemplateOverrides(): void
    {
        $sideboxes = $this->makeFinder()->findFromFilesystem($this->plugins(), 'ptheme');

        ksort($sideboxes);
        $this->assertSame(
            [
                'a.php' => '',
                'b.php' => self::THEME_PLUGIN . '/v1.0.0',
                'c.php' => self::FEATURE_PLUGIN . '/v1.0.0',
                'd.php' => self::THEME_PLUGIN . '/v1.0.0',
                'e.php' => '',
            ],
            $sideboxes
        );
    }

    public function testFindFromFilesystemUsesCoreTemplateOverrideWhenTemplateIsACoreTemplate(): void
    {
        $sideboxes = $this->makeFinder()->findFromFilesystem([], 'tpl');

        ksort($sideboxes);
        $this->assertSame(['a.php' => '', 'b.php' => '', 'e.php' => ''], $sideboxes);
    }

    public function testSideboxPathPrefersTheChildTemplateAndReturnsRelativeOrFullPaths(): void
    {
        $finder = $this->makeFinder();
        $info = ['layout_box_name' => 'b.php'];

        $this->assertSame($this->themeRelative, $finder->sideboxPath($info, 'ptheme'));
        $this->assertSame($this->root . '/' . $this->themeRelative, $finder->sideboxPath($info, 'ptheme', true));
        $this->assertSame('includes/modules/sideboxes/tpl/', $finder->sideboxPath($info, 'tpl'));
        $this->assertSame($this->root . '/includes/modules/sideboxes/tpl/', $finder->sideboxPath($info, 'tpl', true));
    }

    public function testSideboxPathFindsPluginSideboxesWhenNoTemplateOverrideExists(): void
    {
        $finder = $this->makeFinder();
        $info = ['layout_box_name' => 'c.php', 'plugin_details' => self::FEATURE_PLUGIN . '/v1.0.0'];

        $this->assertSame(self::FEATURE_PLUGIN . '/v1.0.0/', $finder->sideboxPath($info, 'ptheme'));
        $this->assertSame($this->root . '/' . $this->featureRelative, $finder->sideboxPath($info, 'ptheme', true));
    }

    public function testSideboxPathFallsBackToTheBaseDirectory(): void
    {
        $finder = $this->makeFinder();

        $this->assertSame('sideboxes/', $finder->sideboxPath(['layout_box_name' => 'a.php'], 'ptheme'));
        $this->assertSame(
            $this->root . '/includes/modules/sideboxes/',
            $finder->sideboxPath(['layout_box_name' => 'a.php'], 'ptheme', true)
        );
        $this->assertSame(
            'sideboxes/',
            $finder->sideboxPath(['layout_box_name' => 'e.php', 'plugin_details' => self::FEATURE_PLUGIN . '/v1.0.0'], 'ptheme'),
            'a sidebox missing from its named plugin falls through to the base directory'
        );
        $this->assertFalse($finder->sideboxPath(['layout_box_name' => 'missing.php'], 'ptheme'));
    }

    private function makeFinder(): SideboxFinder
    {
        return new SideboxFinder(
            new FileSystem(),
            new TemplateResolver($this->root, $this->root . '/includes/templates', $this->root . '/zc_plugins', $this->plugins()),
            $this->root . '//'
        );
    }

    private function plugins(): array
    {
        return [
            ['unique_key' => self::FEATURE_PLUGIN, 'version' => 'v1.0.0'],
            ['unique_key' => self::THEME_PLUGIN, 'version' => 'v1.0.0'],
        ];
    }

    private function writeFile(string $relativePath, string $contents): void
    {
        $file = $this->root . '/' . $relativePath;
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $contents);
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
