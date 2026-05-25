<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use Tests\Support\zcTemplateResolverTest;
use Zencart\LanguageLoader\BaseLanguageLoader;

class BaseLanguageLoaderTemplateChainTest extends zcTemplateResolverTest
{
    private string $fixtureRoot;

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/BaseLanguageLoader.php';

        $this->fixtureRoot = sys_get_temp_dir() . '/zencart-language-loader-' . uniqid('', true);
        mkdir($this->fixtureRoot . '/includes/templates/template_default', 0777, true);
        mkdir($this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/catalog/includes/templates/base_theme', 0777, true);
        mkdir($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme', 0777, true);
        mkdir($this->fixtureRoot . '/includes/languages/english/template_default', 0777, true);
        mkdir($this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/catalog/includes/languages/base_theme', 0777, true);
        mkdir($this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/catalog/includes/languages/english/base_theme', 0777, true);
        mkdir($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/languages/english/child_theme', 0777, true);
        $this->writeTemplateInfo($this->fixtureRoot . '/includes/templates/template_default/template_info.php', 'Template Default');
        $this->writeTemplateInfo($this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/catalog/includes/templates/base_theme/template_info.php', 'Base Theme');
        $this->writeTemplateInfo($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme/template_info.php', 'Child Theme');
        file_put_contents(
            $this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/manifest.php',
            <<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Base Theme',
    'template' => [
        'key' => 'base_theme',
        'baseTemplate' => 'template_default',
        'infoFile' => 'catalog/includes/templates/base_theme/template_info.php',
    ],
];
PHP
        );
        file_put_contents(
            $this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/manifest.php',
            <<<'PHP'
<?php
return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Child Theme',
    'template' => [
        'key' => 'child_theme',
        'baseTemplate' => 'base_theme',
        'infoFile' => 'catalog/includes/templates/child_theme/template_info.php',
    ],
];
PHP
        );

        file_put_contents($this->fixtureRoot . '/includes/languages/english/template_default/lang.example.php', "<?php\nreturn ['A' => 'default'];\n");
        file_put_contents($this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/catalog/includes/languages/english/base_theme/lang.example.php', "<?php\nreturn ['A' => 'plugin base'];\n");
        file_put_contents($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/languages/english/child_theme/lang.example.php', "<?php\nreturn ['A' => 'plugin child'];\n");
        file_put_contents($this->fixtureRoot . '/includes/languages/lang.english.php', "<?php\nreturn ['A' => 'default main'];\n");
        file_put_contents($this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/catalog/includes/languages/base_theme/lang.english.php', "<?php\nreturn ['A' => 'plugin base main'];\n");

        $this->instantiateQfr([
            'template_id' => '1',
            'template_dir' => 'child_theme',
            'template_language' => 0,
            'template_settings' => null,
        ]);
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->fixtureRoot);
        parent::tearDown();
    }

    public function testChildPluginTemplateResolvesItsOwnLanguageOverrideFile(): void
    {
        $loader = new TestableBaseLanguageLoader($this->fixtureRoot, 'child_theme');

        $file = $loader->publicFindTemplateLanguageOverrideFile(
            $this->fixtureRoot . '/includes/languages',
            'english',
            'lang.example.php'
        );

        $this->assertSame($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/languages/english/child_theme/lang.example.php', $file);
    }

    public function testChildPluginTemplateLoadsParentPluginLanguageOverridesInInheritanceOrder(): void
    {
        $loader = new TestableBaseLanguageLoader($this->fixtureRoot, 'child_theme');

        $files = $loader->publicGetTemplateLanguageOverrideFiles(
            $this->fixtureRoot . '/includes/languages',
            'english',
            'lang.example.php'
        );

        $this->assertSame([
            $this->fixtureRoot . '/includes/languages/english/template_default/lang.example.php',
            $this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/catalog/includes/languages/english/base_theme/lang.example.php',
            $this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/languages/english/child_theme/lang.example.php',
        ], $files);
    }

    public function testChildPluginTemplateLoadsParentPluginRootLanguageFiles(): void
    {
        $loader = new TestableBaseLanguageLoader($this->fixtureRoot, 'child_theme');

        $files = $loader->publicGetTemplateFirstLanguageFiles(
            'includes/languages',
            'lang.english.php'
        );

        $this->assertSame([
            $this->fixtureRoot . '/includes/languages/lang.english.php',
            $this->fixtureRoot . '/zc_plugins/BaseTheme/v1.0.0/catalog/includes/languages/base_theme/lang.english.php',
        ], $files);
    }

    private function writeTemplateInfo(string $path, string $templateName): void
    {
        file_put_contents(
            $path,
            <<<PHP
<?php
\$template_name = '{$templateName}';
\$template_version = '1.0.0';
\$template_author = 'Zen Cart';
\$template_description = '{$templateName} description';
\$template_screenshot = 'screenshot.png';
PHP
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

class TestableBaseLanguageLoader extends BaseLanguageLoader
{
    private const INSTALLED_PLUGINS = [
        ['unique_key' => 'BaseTheme', 'version' => 'v1.0.0', 'type' => 'template'],
        ['unique_key' => 'ChildTheme', 'version' => 'v1.0.0', 'type' => 'template'],
    ];

    public function __construct(string $catalogRoot, string $templateDir)
    {
        if (!defined('DIR_FS_CATALOG')) {
            define('DIR_FS_CATALOG', $catalogRoot . '/');
        }
        parent::__construct(self::INSTALLED_PLUGINS, 'index', $templateDir, 'english');
        $this->zcPluginsDir = $catalogRoot . '/zc_plugins/';
        $this->templateResolver = new \Zencart\ResourceLoaders\TemplateResolver(
            $catalogRoot,
            $catalogRoot . '/includes/templates',
            $catalogRoot . '/zc_plugins',
            self::INSTALLED_PLUGINS
        );
    }

    public function publicFindTemplateLanguageOverrideFile(string $rootPath, string $language, string $fileName, string $extraPath = ''): ?string
    {
        return $this->findTemplateLanguageOverrideFile($rootPath, $language, $fileName, $extraPath);
    }

    public function publicGetTemplateLanguageOverrideFiles(string $rootPath, string $language, string $fileName, string $extraPath = ''): array
    {
        return $this->getTemplateLanguageOverrideFiles($rootPath, $language, $fileName, $extraPath);
    }

    public function publicGetTemplateFirstLanguageFiles(string $rootPath, string $fileName): array
    {
        return $this->getTemplateFirstLanguageFiles($rootPath, $fileName);
    }
}
