<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use Tests\Support\zcTemplateResolverTest;
use Zencart\FileSystem\FileSystem;
use Zencart\PageLoader\PageLoader;
use Zencart\ResourceLoaders\TemplateResolver;

class PageLoaderTemplateResolutionTest extends zcTemplateResolverTest
{
    private const BASE_THEME_PLUGIN = 'UnitTestPageLoaderBaseTheme';
    private const CHILD_THEME_PLUGIN = 'UnitTestPageLoaderChildTheme';
    private const OVERLAY_PLUGIN = 'UnitTestPageLoaderTemplateOverlay';
    private const BASE_TEMPLATE_KEY = 'unit_test_page_loader_base_theme';
    private const CHILD_TEMPLATE_KEY = 'page_loader_child_theme';

    private string $baseThemePluginPath;
    private string $childThemePluginPath;
    private string $overlayPluginPath;
    private string $baseThemeCssFixture;

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

        $this->baseThemePluginPath = DIR_FS_CATALOG . 'zc_plugins/' . self::BASE_THEME_PLUGIN . '/v1.0.0/';
        $this->childThemePluginPath = DIR_FS_CATALOG . 'zc_plugins/' . self::CHILD_THEME_PLUGIN . '/v1.0.0/';
        $this->overlayPluginPath = DIR_FS_CATALOG . 'zc_plugins/' . self::OVERLAY_PLUGIN . '/v1.0.0/';
        $this->baseThemeCssFixture = $this->baseThemePluginPath . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/css/zz_test_base.css';

        $this->removeDirectory($this->baseThemePluginPath);
        $this->removeDirectory($this->childThemePluginPath);
        $this->removeDirectory($this->overlayPluginPath);

        $this->ensureDirectoryExists($this->baseThemePluginPath . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/css');
        $this->ensureDirectoryExists($this->baseThemePluginPath . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/common');
        $this->ensureDirectoryExists($this->childThemePluginPath . 'catalog/includes/templates/' . self::CHILD_TEMPLATE_KEY . '/css');
        $this->ensureDirectoryExists($this->childThemePluginPath . 'catalog/includes/templates/' . self::CHILD_TEMPLATE_KEY . '/common');
        $this->ensureDirectoryExists($this->overlayPluginPath . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/common');
        $this->ensureDirectoryExists($this->overlayPluginPath . 'catalog/includes/templates/default/css');

        file_put_contents(
            $this->baseThemePluginPath . 'manifest.php',
            sprintf(<<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Unit Test Base Theme',
    'template' => [
        'key' => '%s',
        'baseTemplate' => 'template_default',
        'infoFile' => 'catalog/includes/templates/%s/template_info.php',
    ],
];
PHP
            ,
            self::BASE_TEMPLATE_KEY,
            self::BASE_TEMPLATE_KEY
        ));
        file_put_contents(
            $this->baseThemePluginPath . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/template_info.php',
            <<<'PHP'
<?php
$template_name = 'Unit Test Base Theme';
$template_version = '1.0.0';
$template_author = 'Zen Cart';
$template_description = 'Unit test plugin-backed base theme';
$template_screenshot = 'screenshot.png';
PHP
        );
        file_put_contents(
            $this->baseThemePluginPath . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/common/html_header.php',
            "<?php\n"
        );
        file_put_contents(
            $this->childThemePluginPath . 'manifest.php',
            sprintf(<<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Unit Test Child Theme',
    'template' => [
        'key' => '%s',
        'baseTemplate' => '%s',
        'infoFile' => 'catalog/includes/templates/%s/template_info.php',
    ],
];
PHP
            ,
            self::CHILD_TEMPLATE_KEY,
            self::BASE_TEMPLATE_KEY,
            self::CHILD_TEMPLATE_KEY
        ));
        file_put_contents(
            $this->childThemePluginPath . 'catalog/includes/templates/' . self::CHILD_TEMPLATE_KEY . '/template_info.php',
            <<<'PHP'
<?php
$template_name = 'Child Theme';
$template_version = '1.0.0';
$template_author = 'Zen Cart';
$template_description = 'Unit test child theme';
$template_screenshot = 'screenshot.png';
PHP
        );
        file_put_contents($this->childThemePluginPath . 'catalog/includes/templates/' . self::CHILD_TEMPLATE_KEY . '/css/zz_test_child.css', '/* child */');

        file_put_contents(
            $this->overlayPluginPath . 'catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/common/tpl_overlay_unit_test.php',
            "<?php\n"
        );
        file_put_contents(
            $this->overlayPluginPath . 'catalog/includes/templates/default/css/zz_test_overlay.css',
            '/* overlay */'
        );
        file_put_contents($this->baseThemeCssFixture, '/* base */');
        $this->instantiateQfr([
            'template_id' => '1',
            'template_dir' => self::BASE_THEME_PLUGIN,
            'template_language' => 0,
            'template_settings' => null,
        ]);
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->baseThemePluginPath);
        $this->removeDirectory($this->childThemePluginPath);
        $this->removeDirectory($this->overlayPluginPath);
        parent::tearDown();
    }

    public function testGetTemplateDirectoryFallsBackToBaseTemplateForChildTheme(): void
    {
        $pageLoader = PageLoader::getInstance();
        $pageLoader->init($this->getInstalledPlugins(), 'index', new FileSystem(), $this->makeTemplateResolver());

        $directory = $pageLoader->getTemplateDirectory('html_header.php', self::CHILD_TEMPLATE_KEY, 'index', 'common');

        $this->assertSame(
            'zc_plugins/' . self::BASE_THEME_PLUGIN . '/v1.0.0/catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/common',
            $directory
        );
    }

    public function testGetTemplateDirectoryFindsNamedOverlayBeforeDefaultFallback(): void
    {
        $pageLoader = PageLoader::getInstance();
        $pageLoader->init($this->getInstalledPlugins(), 'index', new FileSystem(), $this->makeTemplateResolver());

        $directory = $pageLoader->getTemplateDirectory('tpl_overlay_unit_test.php', DIR_WS_TEMPLATES . self::BASE_TEMPLATE_KEY . '/', 'index', 'common');

        $this->assertSame(
            'zc_plugins/' . self::OVERLAY_PLUGIN . '/v1.0.0/catalog/includes/templates/' . self::BASE_TEMPLATE_KEY . '/common',
            $directory
        );
    }

    public function testGetTemplatePartMergesChildBaseAndOverlayAssets(): void
    {
        $pageLoader = PageLoader::getInstance();
        $pageLoader->init($this->getInstalledPlugins(), 'index', new FileSystem(), $this->makeTemplateResolver());

        $files = $pageLoader->getTemplatePart('includes/templates/' . self::CHILD_TEMPLATE_KEY . '/css', '/^zz_test_/', '.css');

        $this->assertSame(
            ['zz_test_base.css', 'zz_test_child.css', 'zz_test_overlay.css'],
            $files
        );
    }

    private function getInstalledPlugins(): array
    {
        return [
            ['unique_key' => self::BASE_THEME_PLUGIN, 'version' => 'v1.0.0'],
            ['unique_key' => self::CHILD_THEME_PLUGIN, 'version' => 'v1.0.0'],
            ['unique_key' => self::OVERLAY_PLUGIN, 'version' => 'v1.0.0'],
        ];
    }

    private function makeTemplateResolver(): TemplateResolver
    {
        return new TemplateResolver(
            DIR_FS_CATALOG,
            DIR_FS_CATALOG . 'includes/templates',
            DIR_FS_CATALOG . 'zc_plugins',
            $this->getInstalledPlugins()
        );
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
