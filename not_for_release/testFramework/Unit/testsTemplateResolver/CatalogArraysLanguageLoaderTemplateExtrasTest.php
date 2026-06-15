<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use Tests\Support\zcTemplateResolverTest;
use Zencart\LanguageLoader\CatalogArraysLanguageLoader;
use Zencart\ResourceLoaders\TemplateResolver;

class CatalogArraysLanguageLoaderTemplateExtrasTest extends zcTemplateResolverTest
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    private const TEMPLATE_PLUGIN = 'UnitTestCatalogTemplateLang';
    private const TEMPLATE_KEY = 'unit_test_catalog_template_lang';
    private const PAGE_NAME = 'unit_test_catalog_template_page';
    private const EXTRA_DEFINE = 'UNIT_TEST_TEMPLATE_PLUGIN_EXTRA_DEFINE';

    private string $pluginPath;
    private string $coreLanguageFile;

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

        $this->pluginPath = DIR_FS_CATALOG . 'zc_plugins/' . self::TEMPLATE_PLUGIN . '/v1.0.0/';
        $this->coreLanguageFile = DIR_FS_CATALOG . 'includes/languages/english/lang.' . self::PAGE_NAME . '.php';

        $this->removeDirectory($this->pluginPath);
        @unlink($this->coreLanguageFile);

        $this->ensureDirectoryExists($this->pluginPath . 'catalog/includes/templates/' . self::TEMPLATE_KEY);
        $this->ensureDirectoryExists($this->pluginPath . 'catalog/includes/languages/english/' . self::TEMPLATE_KEY);
        $this->ensureDirectoryExists(dirname($this->coreLanguageFile));

        file_put_contents(
            $this->pluginPath . 'manifest.php',
            sprintf(<<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Unit Test Catalog Template Language',
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
            $this->pluginPath . 'catalog/includes/templates/' . self::TEMPLATE_KEY . '/template_info.php',
            <<<'PHP'
<?php
$template_name = 'Unit Test Catalog Template Language';
$template_version = '1.0.0';
$template_author = 'Zen Cart';
$template_description = 'Unit test template plugin for catalog language extras';
$template_screenshot = 'screenshot.png';
PHP
        );

        file_put_contents(
            $this->coreLanguageFile,
            "<?php\nreturn ['UNIT_TEST_TEMPLATE_PLUGIN_BASE_DEFINE' => 'base'];\n"
        );

        file_put_contents(
            $this->pluginPath . 'catalog/includes/languages/english/' . self::TEMPLATE_KEY . '/lang.' . self::PAGE_NAME . '_plugin_extra.php',
            "<?php\nreturn ['" . self::EXTRA_DEFINE . "' => 'plugin template extra'];\n"
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
        $this->removeDirectory($this->pluginPath);
        @unlink($this->coreLanguageFile);
        parent::tearDown();
    }

    public function testLoadLanguageForViewIncludesPluginTemplateSpecificExtraFiles(): void
    {
        $_SESSION['language'] = 'english';

        $loader = new TestableCatalogArraysLanguageLoader(
            $this->getInstalledPlugins(),
            self::PAGE_NAME,
            self::TEMPLATE_KEY,
            'english'
        );

        $loader->loadLanguageForView();

        $this->assertTrue(defined(self::EXTRA_DEFINE));
        $this->assertSame('plugin template extra', constant(self::EXTRA_DEFINE));
    }

    private function getInstalledPlugins(): array
    {
        return [
            ['unique_key' => self::TEMPLATE_PLUGIN, 'version' => 'v1.0.0', 'type' => 'template'],
        ];
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        mkdir($directory, 0777, true);
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

class TestableCatalogArraysLanguageLoader extends CatalogArraysLanguageLoader
{
    public function __construct(array $pluginList, string $currentPage, string $templateDir, string $fallback = 'english')
    {
        parent::__construct($pluginList, $currentPage, $templateDir, $fallback);
        $this->mainLoader = new TestLanguageFileTracker();
        $this->templateResolver = new TemplateResolver(
            DIR_FS_CATALOG,
            DIR_FS_CATALOG . 'includes/templates',
            DIR_FS_CATALOG . 'zc_plugins',
            $pluginList
        );
    }
}

class TestLanguageFileTracker
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
