<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcTemplateResolverTest;
use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\HtmlIncludesFinder;

/**
 * Pins the search order, returned paths and caching of HtmlIncludesFinder. Fixtures are written
 * below the real catalog because the finder resolves everything from DIR_FS_CATALOG.
 */
#[RunTestsInSeparateProcesses]
class HtmlIncludesFinderPathsTest extends zcTemplateResolverTest
{
    private const PLUGIN = 'UnitTestHtmlIncPlugin';

    private string $languages;
    private string $pluginLanguages;

    /** @var string[] */
    private array $createdFiles = [];

    /** @var string[] */
    private array $createdDirs = [];

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/HtmlIncludesFinder.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';

        $this->languages = DIR_FS_CATALOG . DIR_WS_LANGUAGES;
        $this->pluginLanguages = DIR_FS_CATALOG . 'zc_plugins/' . self::PLUGIN . '/v1.0.0/catalog/includes/languages/';
        $this->removeDirectory(DIR_FS_CATALOG . 'zc_plugins/' . self::PLUGIN);

        $this->instantiateQfr(['template_id' => '1']);
    }

    public function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            @unlink($file);
        }
        foreach (array_reverse($this->createdDirs) as $dir) {
            @rmdir($dir);
        }
        $this->removeDirectory(DIR_FS_CATALOG . 'zc_plugins/' . self::PLUGIN);
        parent::tearDown();
    }

    public function testFindAllUsesFirstFoundFileInLanguageThenFallbackOrder(): void
    {
        $this->place($this->languages . 'spanish/html_includes/template_default/', 'zz_tpl.php');
        $this->place($this->pluginLanguages . 'spanish/html_includes/template_default/', 'zz_tpl.php');
        $this->place($this->pluginLanguages . 'spanish/html_includes/template_default/', 'zz_plugin_tpl.php');
        $this->place($this->languages . 'spanish/html_includes/', 'zz_base.php');
        $this->place($this->languages . 'english/html_includes/', 'zz_base.php');
        $this->place($this->pluginLanguages . 'spanish/html_includes/', 'zz_plugin_base.php');
        $this->place($this->languages . 'english/html_includes/template_default/', 'zz_fallback_tpl.php');
        $this->place($this->pluginLanguages . 'english/html_includes/', 'zz_fallback_plugin.php');
        $this->place($this->languages . 'english/html_includes/', 'zz_fallback_base.php');

        $files = array_filter($this->makeFinder()->findAll(), static fn(string $name): bool => str_starts_with($name, 'zz_'), ARRAY_FILTER_USE_KEY);
        ksort($files);

        $this->assertSame(
            [
                'zz_base.php' => $this->languages . 'spanish/html_includes/',
                'zz_fallback_base.php' => $this->languages . 'english/html_includes/',
                'zz_fallback_plugin.php' => $this->pluginLanguages . 'english/html_includes/',
                'zz_fallback_tpl.php' => $this->languages . 'english/html_includes/template_default/',
                'zz_plugin_base.php' => $this->pluginLanguages . 'spanish/html_includes/',
                'zz_plugin_tpl.php' => $this->pluginLanguages . 'spanish/html_includes/template_default/',
                'zz_tpl.php' => $this->languages . 'spanish/html_includes/template_default/',
            ],
            $files
        );
    }

    public function testFindReturnsFullOrCatalogRelativePathsOrFalse(): void
    {
        $this->place($this->languages . 'spanish/html_includes/', 'zz_find.php');

        $finder = $this->makeFinder();

        $this->assertSame($this->languages . 'spanish/html_includes/zz_find.php', $finder->find('zz_find.php'));
        $this->assertSame($this->languages . 'spanish/html_includes/zz_find.php', $finder->find('zz_find.php', true));
        $this->assertSame(DIR_WS_LANGUAGES . 'spanish/html_includes/zz_find.php', $finder->find('zz_find.php', false));
        $this->assertFalse($finder->find('zz_missing.php'));
    }

    public function testResultsAreCachedUntilTheFallbackLanguageChanges(): void
    {
        $this->place($this->languages . 'spanish/html_includes/', 'zz_first.php');

        $finder = $this->makeFinder();
        $this->assertArrayHasKey('zz_first.php', $finder->findAll());

        $this->place($this->languages . 'spanish/html_includes/', 'zz_added.php');
        $this->assertArrayNotHasKey('zz_added.php', $finder->findAll(), 'second call is served from the cache');

        $finder->setFallback('english');
        $this->assertArrayNotHasKey('zz_added.php', $finder->findAll(), 'an unchanged fallback keeps the cache');

        $this->place($this->languages . 'german/html_includes/', 'zz_german.php');
        $finder->setFallback('german');
        $files = $finder->findAll();
        $this->assertArrayHasKey('zz_added.php', $files, 'a new fallback rebuilds the results');
        $this->assertArrayHasKey('zz_german.php', $files);
    }

    public function testCacheKeyDistinguishesPluginVersions(): void
    {
        $versionTwo = DIR_FS_CATALOG . 'zc_plugins/' . self::PLUGIN . '/v2.0.0/catalog/includes/languages/';
        $this->place($this->pluginLanguages . 'spanish/html_includes/', 'zz_v1.php');
        $this->place($versionTwo . 'spanish/html_includes/', 'zz_v2.php');

        $first = $this->makeFinder('v1.0.0')->findAll();
        $second = $this->makeFinder('v2.0.0')->findAll();

        $this->assertArrayHasKey('zz_v1.php', $first);
        $this->assertArrayNotHasKey('zz_v2.php', $first);
        $this->assertArrayHasKey('zz_v2.php', $second);
        $this->assertArrayNotHasKey('zz_v1.php', $second);
    }

    public function testCacheKeyIsBuiltFromLanguageFallbackTemplateAndPlugins(): void
    {
        $finder = $this->makeFinder();
        $call = \Closure::bind(
            fn() => [$this->getCacheKey(), $this->getTemplateInheritanceChain()],
            $finder,
            HtmlIncludesFinderPathsTestSubclass::class
        );

        [$key, $chain] = $call();

        $this->assertSame('spanish|english|template_default|' . self::PLUGIN . ':v1.0.0', $key);
        $this->assertSame(['template_default'], $chain);
    }

    private function makeFinder(string $pluginVersion = 'v1.0.0'): HtmlIncludesFinder
    {
        return new HtmlIncludesFinderPathsTestSubclass(
            new FileSystem(),
            [['unique_key' => self::PLUGIN, 'version' => $pluginVersion]],
            'spanish',
            'template_default'
        );
    }

    private function place(string $directory, string $fileName): void
    {
        $missing = [];
        $current = rtrim($directory, '/');
        while (!is_dir($current)) {
            array_unshift($missing, $current);
            $current = dirname($current);
        }
        foreach ($missing as $dir) {
            mkdir($dir, 0777);
            $this->createdDirs[] = $dir;
        }

        $file = rtrim($directory, '/') . '/' . $fileName;
        file_put_contents($file, "<?php\n");
        $this->createdFiles[] = $file;
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

class HtmlIncludesFinderPathsTestSubclass extends HtmlIncludesFinder
{
}
