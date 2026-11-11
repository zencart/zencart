<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcTemplateResolverTest;
use Zencart\LanguageLoader\CatalogArraysLanguageLoader;
use Zencart\ResourceLoaders\TemplateResolver;

/**
 * Pins the exact file locations and precedence order used when
 * CatalogArraysLanguageLoader loads the page-specific language files.
 */
#[RunTestsInSeparateProcesses]
class CatalogArraysLanguageLoaderPathsTest extends zcTemplateResolverTest
{
    private const PLUGIN = 'UnitTestPathsLangPlugin';
    private const TEMPLATE_PLUGIN = 'UnitTestPathsLangTemplate';
    private const TEMPLATE_KEY = 'unit_test_paths_lang_template';
    private const PAGE = 'unit_test_paths_page';

    private string $pluginPath;
    private string $templatePluginPath;

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
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/BaseLanguageLoader.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/ArraysLanguageLoader.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/CatalogArraysLanguageLoader.php';

        $this->pluginPath = DIR_FS_CATALOG . 'zc_plugins/' . self::PLUGIN . '/v1.0.0/';
        $this->templatePluginPath = DIR_FS_CATALOG . 'zc_plugins/' . self::TEMPLATE_PLUGIN . '/v1.0.0/';
        $this->removeDirectory(DIR_FS_CATALOG . 'zc_plugins/' . self::PLUGIN);
        $this->removeDirectory(DIR_FS_CATALOG . 'zc_plugins/' . self::TEMPLATE_PLUGIN);

        $this->ensureDirectoryExists($this->templatePluginPath . 'catalog/includes/templates/' . self::TEMPLATE_KEY);
        file_put_contents(
            $this->templatePluginPath . 'manifest.php',
            sprintf(<<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Unit Test Paths Template',
    'template' => [
        'key' => '%s',
        'baseTemplate' => 'template_default',
        'infoFile' => 'catalog/includes/templates/%s/template_info.php',
    ],
];
PHP
            ,
            self::TEMPLATE_KEY,
            self::TEMPLATE_KEY
        ));
        file_put_contents(
            $this->templatePluginPath . 'catalog/includes/templates/' . self::TEMPLATE_KEY . '/template_info.php',
            "<?php\n\$template_name = 'Unit Test Paths Template';\n\$template_version = '1.0.0';\n"
            . "\$template_author = 'Zen Cart';\n\$template_description = 'Unit test';\n\$template_screenshot = 'screenshot.png';\n"
        );

        $this->instantiateQfr([
            'template_id' => '1',
            'template_dir' => self::TEMPLATE_KEY,
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
        $this->removeDirectory(DIR_FS_CATALOG . 'zc_plugins/' . self::PLUGIN);
        $this->removeDirectory(DIR_FS_CATALOG . 'zc_plugins/' . self::TEMPLATE_PLUGIN);
        parent::tearDown();
    }

    public function testBaseFileUsesSessionLanguageOverFallbackLanguage(): void
    {
        $this->writeDefines(DIR_WS_LANGUAGES . 'english/lang.' . self::PAGE . '.php', [
            'UTP_BASE_SHARED' => 'english',
            'UTP_BASE_ENGLISH_ONLY' => 'english-only',
        ]);
        $this->writeDefines(DIR_WS_LANGUAGES . 'spanish/lang.' . self::PAGE . '.php', [
            'UTP_BASE_SHARED' => 'spanish',
            'UTP_BASE_SPANISH_ONLY' => 'spanish-only',
        ]);

        $this->loadForView('spanish');

        $this->assertSame('spanish', constant('UTP_BASE_SHARED'));
        $this->assertSame('english-only', constant('UTP_BASE_ENGLISH_ONLY'));
        $this->assertSame('spanish-only', constant('UTP_BASE_SPANISH_ONLY'));
    }

    public function testBaseFileInFallbackLanguageIsLoadedWhenSessionLanguageMatches(): void
    {
        $this->writeDefines(DIR_WS_LANGUAGES . 'english/lang.' . self::PAGE . '.php', [
            'UTP_SAME_LANG_BASE' => 'english',
        ]);

        $this->loadForView('english');

        $this->assertSame('english', constant('UTP_SAME_LANG_BASE'));
    }

    public function testPluginBaseFilesAreLoadedFromLanguageAndDefaultDirectories(): void
    {
        $base = $this->pluginPath . 'catalog/includes/languages/';
        $this->writeDefines(DIR_WS_LANGUAGES . 'english/lang.' . self::PAGE . '.php', [
            'UTP_PLUGIN_SHARED' => 'core-english',
        ]);
        $this->writeDefines($base . 'english/lang.' . self::PAGE . '.php', [
            'UTP_PLUGIN_SHARED' => 'plugin-english',
            'UTP_PLUGIN_EN' => 'plugin-english',
        ]);
        $this->writeDefines($base . 'spanish/lang.' . self::PAGE . '.php', [
            'UTP_PLUGIN_SHARED' => 'plugin-spanish',
            'UTP_PLUGIN_ES' => 'plugin-spanish',
        ]);
        $this->writeDefines($base . 'english/default/lang.' . self::PAGE . '.php', [
            'UTP_PLUGIN_SHARED' => 'plugin-default-english',
            'UTP_PLUGIN_DEFAULT_EN' => 'plugin-default-english',
        ]);
        $this->writeDefines($base . 'spanish/default/lang.' . self::PAGE . '.php', [
            'UTP_PLUGIN_DEFAULT_ES' => 'plugin-default-spanish',
        ]);

        $this->loadForView('spanish');

        $this->assertSame('plugin-english', constant('UTP_PLUGIN_EN'));
        $this->assertSame('plugin-spanish', constant('UTP_PLUGIN_ES'));
        $this->assertSame('plugin-default-english', constant('UTP_PLUGIN_DEFAULT_EN'));
        $this->assertSame('plugin-default-spanish', constant('UTP_PLUGIN_DEFAULT_ES'));
        $this->assertSame('plugin-default-english', constant('UTP_PLUGIN_SHARED'), 'plugin default directory wins over plugin language directory, which wins over core');
    }

    public function testTemplateOverrideBaseFilesOverrideEarlierDefinitions(): void
    {
        $this->writeDefines(DIR_WS_LANGUAGES . 'spanish/lang.' . self::PAGE . '.php', [
            'UTP_TPL_SHARED' => 'core-spanish',
            'UTP_TPL_CORE_ONLY' => 'core-spanish',
        ]);
        $this->writeDefines(DIR_WS_LANGUAGES . 'spanish/template_default/lang.' . self::PAGE . '.php', [
            'UTP_TPL_SHARED' => 'template-default',
            'UTP_TPL_DEFAULT_ONLY' => 'template-default',
        ]);
        $this->writeDefines(DIR_WS_LANGUAGES . 'spanish/' . self::TEMPLATE_KEY . '/lang.' . self::PAGE . '.php', [
            'UTP_TPL_SHARED' => 'template-key',
            'UTP_TPL_KEY_ONLY' => 'template-key',
        ]);

        $this->loadForView('spanish');

        $this->assertSame('template-key', constant('UTP_TPL_SHARED'));
        $this->assertSame('core-spanish', constant('UTP_TPL_CORE_ONLY'));
        $this->assertSame('template-default', constant('UTP_TPL_DEFAULT_ONLY'));
        $this->assertSame('template-key', constant('UTP_TPL_KEY_ONLY'));
    }

    public function testExtraFilesAreLoadedFromEveryLocationInPrecedenceOrder(): void
    {
        $extra = 'lang.' . self::PAGE . '_extra.php';
        $base = $this->pluginPath . 'catalog/includes/languages/';

        $this->writeDefines(DIR_WS_LANGUAGES . 'english/' . $extra, [
            'UTP_EXTRA_SHARED' => 'core-english',
            'UTP_EXTRA_CORE_EN' => 'core-english',
        ]);
        $this->writeDefines(DIR_WS_LANGUAGES . 'spanish/' . $extra, [
            'UTP_EXTRA_SHARED' => 'core-spanish',
            'UTP_EXTRA_CORE_ES' => 'core-spanish',
        ]);
        $this->writeDefines($base . 'english/' . $extra, [
            'UTP_EXTRA_SHARED' => 'plugin-english',
            'UTP_EXTRA_PLUGIN_EN' => 'plugin-english',
        ]);
        $this->writeDefines($base . 'spanish/' . $extra, [
            'UTP_EXTRA_SHARED' => 'plugin-spanish',
            'UTP_EXTRA_PLUGIN_ES' => 'plugin-spanish',
        ]);
        $this->writeDefines($base . 'english/default/' . $extra, [
            'UTP_EXTRA_SHARED' => 'plugin-default-english',
            'UTP_EXTRA_PLUGIN_DEFAULT_EN' => 'plugin-default-english',
        ]);
        $this->writeDefines($base . 'spanish/default/' . $extra, [
            'UTP_EXTRA_PLUGIN_DEFAULT_ES' => 'plugin-default-spanish',
        ]);
        $this->writeDefines(DIR_WS_LANGUAGES . 'spanish/' . self::TEMPLATE_KEY . '/' . $extra, [
            'UTP_EXTRA_TEMPLATE' => 'template-spanish',
        ]);

        $this->loadForView('spanish');

        $this->assertSame('core-english', constant('UTP_EXTRA_CORE_EN'));
        $this->assertSame('core-spanish', constant('UTP_EXTRA_CORE_ES'));
        $this->assertSame('plugin-english', constant('UTP_EXTRA_PLUGIN_EN'));
        $this->assertSame('plugin-spanish', constant('UTP_EXTRA_PLUGIN_ES'));
        $this->assertSame('plugin-default-english', constant('UTP_EXTRA_PLUGIN_DEFAULT_EN'));
        $this->assertSame('plugin-default-spanish', constant('UTP_EXTRA_PLUGIN_DEFAULT_ES'));
        $this->assertSame('template-spanish', constant('UTP_EXTRA_TEMPLATE'));
        $this->assertSame('plugin-default-english', constant('UTP_EXTRA_SHARED'));
    }

    public function testExtraFilesDoNotIncludeTheBasePageFileOrOtherPages(): void
    {
        $this->writeDefines(DIR_WS_LANGUAGES . 'english/lang.' . self::PAGE . '.php', [
            'UTP_BASE_ONLY' => 'base',
        ]);
        $this->writeDefines(DIR_WS_LANGUAGES . 'english/lang.' . self::PAGE . '_more.php', [
            'UTP_MORE' => 'more',
        ]);
        $this->writeDefines(DIR_WS_LANGUAGES . 'english/lang.other_' . self::PAGE . '_more.php', [
            'UTP_OTHER_PAGE' => 'other',
        ]);

        $this->loadForView('english');

        $this->assertSame('base', constant('UTP_BASE_ONLY'));
        $this->assertSame('more', constant('UTP_MORE'));
        $this->assertFalse(defined('UTP_OTHER_PAGE'));
    }

    public function testSessionLanguageSameAsFallbackLoadsEachExtraLocationOnce(): void
    {
        $extra = 'lang.' . self::PAGE . '_extra.php';
        $base = $this->pluginPath . 'catalog/includes/languages/';
        $this->writeDefines(DIR_WS_LANGUAGES . 'english/' . $extra, ['UTP_SAME_CORE' => 'core']);
        $this->writeDefines($base . 'english/' . $extra, ['UTP_SAME_PLUGIN' => 'plugin']);
        $this->writeDefines($base . 'english/default/' . $extra, ['UTP_SAME_PLUGIN_DEFAULT' => 'plugin-default']);
        $this->writeDefines(DIR_WS_LANGUAGES . 'english/' . self::TEMPLATE_KEY . '/' . $extra, ['UTP_SAME_TEMPLATE' => 'template']);

        $this->loadForView('english');

        $this->assertSame('core', constant('UTP_SAME_CORE'));
        $this->assertSame('plugin', constant('UTP_SAME_PLUGIN'));
        $this->assertSame('plugin-default', constant('UTP_SAME_PLUGIN_DEFAULT'));
        $this->assertSame('template', constant('UTP_SAME_TEMPLATE'));
    }

    private function loadForView(string $language): void
    {
        $_SESSION['language'] = $language;

        $loader = new PathsTestableCatalogArraysLanguageLoader(
            [
                ['unique_key' => self::PLUGIN, 'version' => 'v1.0.0', 'type' => 'plugin'],
                ['unique_key' => self::TEMPLATE_PLUGIN, 'version' => 'v1.0.0', 'type' => 'template'],
            ],
            self::PAGE,
            self::TEMPLATE_KEY,
            'english'
        );
        $loader->loadLanguageForView();
    }

    private function writeDefines(string $file, array $defines): void
    {
        $directory = dirname($file);
        $missing = [];
        while (!is_dir($directory)) {
            array_unshift($missing, $directory);
            $directory = dirname($directory);
        }
        foreach ($missing as $dir) {
            mkdir($dir, 0777);
            $this->createdDirs[] = $dir;
        }

        file_put_contents($file, "<?php\nreturn " . var_export($defines, true) . ";\n");
        $this->createdFiles[] = $file;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
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

class PathsTestableCatalogArraysLanguageLoader extends CatalogArraysLanguageLoader
{
    public function __construct(array $pluginList, string $currentPage, string $templateDir, string $fallback = 'english')
    {
        parent::__construct($pluginList, $currentPage, $templateDir, $fallback);
        $this->mainLoader = new PathsTestLanguageFileTracker();
        $this->templateResolver = new TemplateResolver(
            DIR_FS_CATALOG,
            DIR_FS_CATALOG . 'includes/templates',
            DIR_FS_CATALOG . 'zc_plugins',
            $pluginList
        );
    }
}

class PathsTestLanguageFileTracker
{
    private array $loadedFiles = [];

    public function isFileAlreadyLoaded(string $definesFile): bool
    {
        return in_array($definesFile, $this->loadedFiles, true);
    }

    public function addLanguageFilesLoaded(string $definesFile): void
    {
        $this->loadedFiles[] = $definesFile;
    }
}
