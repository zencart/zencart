<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcTemplateResolverTest;
use Zencart\FileSystem\FileSystem;
use Zencart\PageLoader\PageLoader;
use Zencart\ResourceLoaders\TemplateResolver;

/**
 * Pins the directory search order and path handling of PageLoader's template lookups.
 * PageLoader is a singleton that caches search directories, so each test runs in its own process.
 */
#[RunTestsInSeparateProcesses]
class PageLoaderSearchOrderTest extends zcTemplateResolverTest
{
    private const BASE_PLUGIN = 'UnitTestOrderBaseTheme';
    private const CHILD_PLUGIN = 'UnitTestOrderChildTheme';
    private const OVERLAY_PLUGIN = 'UnitTestOrderOverlay';
    private const BASE_KEY = 'unit_test_order_base';
    private const CHILD_KEY = 'unit_test_order_child';
    private const PAGE = 'unit_test_order_page';
    private const FILE = 'zz_order.php';

    /** @var string[] */
    private array $createdFiles = [];

    /** @var string[] */
    private array $createdDirs = [];

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/traits/Singleton.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/PageLoader.php';

        $this->removePlugins();

        $this->writeThemePlugin(self::BASE_PLUGIN, self::BASE_KEY, 'template_default');
        $this->writeThemePlugin(self::CHILD_PLUGIN, self::CHILD_KEY, self::BASE_KEY);

        $this->instantiateQfr([
            'template_id' => '1',
            'template_dir' => self::CHILD_KEY,
            'template_language' => 0,
            'template_settings' => null,
        ]);
    }

    public function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            @unlink($file);
        }
        foreach (array_reverse($this->createdDirs) as $dir) {
            @rmdir($dir);
        }
        $this->removePlugins();
        parent::tearDown();
    }

    public function testTemplateDirectorySearchOrderIsChainThenOverlayThenDefaultForPageThenSubDirectory(): void
    {
        $locations = [];
        foreach ([self::PAGE, 'common'] as $subDir) {
            $locations[] = $this->pluginTemplateDir(self::CHILD_PLUGIN, self::CHILD_KEY, $subDir);
            $locations[] = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, self::CHILD_KEY, $subDir);
            $locations[] = $this->pluginTemplateDir(self::BASE_PLUGIN, self::BASE_KEY, $subDir);
            $locations[] = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, self::BASE_KEY, $subDir);
            $locations[] = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, 'default', $subDir);
            $locations[] = 'includes/templates/template_default/' . $subDir;
        }
        foreach ($locations as $location) {
            $this->placeFile($location, self::FILE);
        }

        $pageLoader = $this->makePageLoader();
        foreach ($locations as $location) {
            $this->assertSame(
                $location,
                $pageLoader->getTemplateDirectory(self::FILE, self::CHILD_KEY, self::PAGE, 'common')
            );
            unlink(DIR_FS_CATALOG . $location . '/' . self::FILE);
        }
    }

    public function testTemplateDirectoryFallsBackToTemplateDefaultSubDirectoryWhenFileIsNotFound(): void
    {
        $pageLoader = $this->makePageLoader();

        $this->assertSame(
            DIR_WS_TEMPLATES . 'template_default/common',
            $pageLoader->getTemplateDirectory('zz_does_not_exist.php', self::CHILD_KEY, self::PAGE, '/common/')
        );
    }

    public function testTemplateDirectoryStripsSlashesFromFileName(): void
    {
        $location = $this->pluginTemplateDir(self::CHILD_PLUGIN, self::CHILD_KEY, 'common');
        $this->placeFile($location, self::FILE);

        $pageLoader = $this->makePageLoader();

        $this->assertSame(
            $location,
            $pageLoader->getTemplateDirectory('/' . self::FILE, self::CHILD_KEY, self::PAGE, 'common')
        );
    }

    public function testTemplateDirectoryAcceptsTemplateIdentifiedInAnyDirectoryForm(): void
    {
        $location = $this->pluginTemplateDir(self::CHILD_PLUGIN, self::CHILD_KEY, 'common');
        $this->placeFile($location, self::FILE);

        $pageLoader = $this->makePageLoader();

        $forms = [
            self::CHILD_KEY,
            self::CHILD_KEY . '/',
            DIR_WS_TEMPLATES . self::CHILD_KEY,
            DIR_WS_TEMPLATES . self::CHILD_KEY . '/',
            DIR_FS_CATALOG . 'includes/templates/' . self::CHILD_KEY . '/',
            'includes\\templates\\' . self::CHILD_KEY . '\\',
            '/some/other/place/' . self::CHILD_KEY,
        ];
        foreach ($forms as $form) {
            $this->assertSame(
                $location,
                $pageLoader->getTemplateDirectory(self::FILE, $form, self::PAGE, 'common'),
                'template directory form: ' . $form
            );
        }
    }

    public function testDefaultTemplateKeyIgnoresChildThemeDirectories(): void
    {
        $this->placeFile($this->pluginTemplateDir(self::CHILD_PLUGIN, self::CHILD_KEY, 'common'), self::FILE);
        $overlay = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, 'default', 'common');
        $this->placeFile($overlay, self::FILE);

        $pageLoader = $this->makePageLoader();

        foreach (['', '/', 'template_default', DIR_WS_TEMPLATES . 'template_default/'] as $form) {
            $this->assertSame(
                $overlay,
                $pageLoader->getTemplateDirectory(self::FILE, $form, self::PAGE, 'common'),
                'template directory form: "' . $form . '"'
            );
        }
    }

    public function testTemplateSubDirectoryTrailingSlashesAreIgnored(): void
    {
        $location = $this->pluginTemplateDir(self::BASE_PLUGIN, self::BASE_KEY, 'common');
        $this->placeFile($location, self::FILE);

        $pageLoader = $this->makePageLoader();

        $this->assertSame(
            $location,
            $pageLoader->getTemplateDirectory(self::FILE, self::CHILD_KEY, self::PAGE, '/common/')
        );
    }

    public function testTemplatePartFromTemplatePathIsUniqueSortedAndFiltered(): void
    {
        $childCss = $this->pluginTemplateDir(self::CHILD_PLUGIN, self::CHILD_KEY, 'css');
        $baseCss = $this->pluginTemplateDir(self::BASE_PLUGIN, self::BASE_KEY, 'css');
        $overlayCss = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, 'default', 'css');
        $this->placeFile($childCss, 'zz_b.css');
        $this->placeFile($childCss, 'zz_shared.css');
        $this->placeFile($baseCss, 'zz_shared.css');
        $this->placeFile($baseCss, 'zz_a.css');
        $this->placeFile($baseCss, 'zz_a.txt');
        $this->placeFile($baseCss, 'other.css');
        $this->placeFile($overlayCss, 'zz_c.css');
        $this->ensureDirectory(DIR_FS_CATALOG . $baseCss . '/zz_dir.css');

        $files = $this->makePageLoader()->getTemplatePart(
            'includes/templates/' . self::CHILD_KEY . '/css/',
            '/^zz_/',
            '.css'
        );

        $this->assertSame(['zz_a.css', 'zz_b.css', 'zz_c.css', 'zz_shared.css'], $files);
    }

    public function testTemplatePartFromPlainDirectoryIncludesPluginCopies(): void
    {
        $coreDir = 'includes/extra_datafiles/unit_test_order_dir';
        $pluginDir = 'zc_plugins/' . self::OVERLAY_PLUGIN . '/v1.0.0/catalog/' . $coreDir;
        $this->placeFile($coreDir, 'zz_core.php');
        $this->placeFile($coreDir, 'zz_core.txt');
        $this->placeFile($coreDir, 'other.php');
        $this->placeFile($pluginDir, 'zz_plugin.php');
        $this->placeFile($pluginDir, 'zz_alpha.php');

        $files = $this->makePageLoader()->getTemplatePart($coreDir . '/', '/^zz_/');

        $this->assertSame(['zz_alpha.php', 'zz_core.php', 'zz_plugin.php'], $files);
    }

    public function testTemplatePluginDirFindsOverlayDirectoryAndHonoursPluginFilter(): void
    {
        $location = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, self::BASE_KEY, 'common');
        $this->placeFile($location, self::FILE);

        $pageLoader = $this->makePageLoader();

        $this->assertSame($location . '/', $pageLoader->getTemplatePluginDir(self::FILE, 'common'));
        $this->assertSame($location . '/', $pageLoader->getTemplatePluginDir('/' . self::FILE, 'common', self::OVERLAY_PLUGIN));
        $this->assertSame($location . '/', $pageLoader->getTemplatePluginDir(self::FILE, 'common', ''));
        $this->assertSame($location . '/', $pageLoader->getTemplatePluginDir(self::FILE, 'common', null));
        $this->assertFalse($pageLoader->getTemplatePluginDir(self::FILE, 'common', self::BASE_PLUGIN));
        $this->assertFalse($pageLoader->getTemplatePluginDir('zz_missing.php', 'common'));
        $this->assertFalse($pageLoader->getTemplatePluginDir(self::FILE, 'no_such_dir'));
    }

    public function testCoreTemplateDirectoriesAreResolvedRelativeToTheCatalogRoot(): void
    {
        $key = 'unit_test_order_core';
        $this->placeFile('includes/templates/' . $key, 'template_info.php');
        file_put_contents(
            DIR_FS_CATALOG . 'includes/templates/' . $key . '/template_info.php',
            "<?php\n\$template_name = 'Core';\n"
        );
        $this->placeFile('includes/templates/' . $key . '/common', self::FILE);

        $directory = $this->makePageLoader()->getTemplateDirectory(self::FILE, $key, self::PAGE, 'common');

        $this->assertSame('includes/templates/' . $key . '/common', $directory);
    }

    public function testTemplatePluginDirIgnoresPluginsWithoutATemplatesDirectory(): void
    {
        $location = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, self::BASE_KEY, 'common');
        $this->placeFile($location, self::FILE);

        $pageLoader = PageLoader::getInstance();
        $pageLoader->init(
            [
                ['unique_key' => 'UnitTestOrderPlainPlugin', 'version' => 'v1.0.0'],
                ['unique_key' => self::OVERLAY_PLUGIN, 'version' => 'v1.0.0'],
            ],
            self::PAGE,
            new FileSystem(),
            new TemplateResolver(
                DIR_FS_CATALOG,
                DIR_FS_CATALOG . 'includes/templates',
                DIR_FS_CATALOG . 'zc_plugins',
                $this->getInstalledPlugins()
            )
        );

        $this->assertSame($location . '/', $pageLoader->getTemplatePluginDir(self::FILE, 'common'));
    }

    public function testTemplatePluginDirChecksEveryTemplateTargetInAPlugin(): void
    {
        $first = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, 'aaa_target', 'common');
        $second = $this->pluginTemplateDir(self::OVERLAY_PLUGIN, 'zzz_target', 'common');
        $this->ensureDirectory(DIR_FS_CATALOG . $first);
        $this->ensureDirectory(DIR_FS_CATALOG . $second);

        $pageLoader = $this->makePageLoader();

        foreach ([$first, $second] as $holder) {
            $this->placeFile($holder, self::FILE);
            $this->assertSame($holder . '/', $pageLoader->getTemplatePluginDir(self::FILE, 'common'), $holder);
            unlink(DIR_FS_CATALOG . $holder . '/' . self::FILE);
        }
    }

    private function makePageLoader(): PageLoader
    {
        $pageLoader = PageLoader::getInstance();
        $pageLoader->init(
            $this->getInstalledPlugins(),
            self::PAGE,
            new FileSystem(),
            new TemplateResolver(
                DIR_FS_CATALOG,
                DIR_FS_CATALOG . 'includes/templates',
                DIR_FS_CATALOG . 'zc_plugins',
                $this->getInstalledPlugins()
            )
        );

        return $pageLoader;
    }

    private function getInstalledPlugins(): array
    {
        return [
            ['unique_key' => self::BASE_PLUGIN, 'version' => 'v1.0.0'],
            ['unique_key' => self::CHILD_PLUGIN, 'version' => 'v1.0.0'],
            ['unique_key' => self::OVERLAY_PLUGIN, 'version' => 'v1.0.0'],
        ];
    }

    private function pluginTemplateDir(string $plugin, string $target, string $subDir): string
    {
        return 'zc_plugins/' . $plugin . '/v1.0.0/catalog/includes/templates/' . $target . '/' . $subDir;
    }

    private function placeFile(string $relativeDir, string $fileName): void
    {
        $this->ensureDirectory(DIR_FS_CATALOG . $relativeDir);
        $file = DIR_FS_CATALOG . $relativeDir . '/' . $fileName;
        file_put_contents($file, "<?php\n");
        $this->createdFiles[] = $file;
    }

    private function ensureDirectory(string $directory): void
    {
        $missing = [];
        while (!is_dir($directory)) {
            array_unshift($missing, $directory);
            $directory = dirname($directory);
        }
        foreach ($missing as $dir) {
            mkdir($dir, 0777);
            $this->createdDirs[] = $dir;
        }
    }

    private function writeThemePlugin(string $plugin, string $key, string $baseTemplate): void
    {
        $root = DIR_FS_CATALOG . 'zc_plugins/' . $plugin . '/v1.0.0/';
        $this->ensureDirectory($root . 'catalog/includes/templates/' . $key);
        file_put_contents(
            $root . 'manifest.php',
            sprintf(<<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Unit Test Order Theme',
    'template' => [
        'key' => '%s',
        'baseTemplate' => '%s',
        'infoFile' => 'catalog/includes/templates/%s/template_info.php',
    ],
];
PHP
            ,
            $key,
            $baseTemplate,
            $key
        ));
        file_put_contents(
            $root . 'catalog/includes/templates/' . $key . '/template_info.php',
            "<?php\n\$template_name = 'Unit Test Order Theme';\n\$template_version = '1.0.0';\n"
            . "\$template_author = 'Zen Cart';\n\$template_description = 'Unit test';\n\$template_screenshot = 'screenshot.png';\n"
        );
    }

    private function removePlugins(): void
    {
        foreach ([self::BASE_PLUGIN, self::CHILD_PLUGIN, self::OVERLAY_PLUGIN] as $plugin) {
            $this->removeDirectory(DIR_FS_CATALOG . 'zc_plugins/' . $plugin);
        }
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
