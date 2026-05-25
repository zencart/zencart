<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcTemplateResolverTest;

class FunctionsTemplatesTest extends zcTemplateResolverTest
{
    private string $fixtureRoot;

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_templates.php';

        $this->fixtureRoot = sys_get_temp_dir() . '/zencart-functions-templates-' . uniqid('', true);
        mkdir($this->fixtureRoot . '/includes/templates/template_default/templates', 0777, true);
        mkdir($this->fixtureRoot . '/includes/templates/responsive_classic/templates', 0777, true);
        mkdir($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme/templates', 0777, true);

        $this->writeTemplateInfo(
            $this->fixtureRoot . '/includes/templates/template_default/template_info.php',
            'Template Default'
        );
        $this->writeTemplateInfo(
            $this->fixtureRoot . '/includes/templates/responsive_classic/template_info.php',
            'Responsive Classic'
        );
        $this->writeTemplateInfo(
            $this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme/template_info.php',
            'Child Theme'
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
        'baseTemplate' => 'responsive_classic',
        'infoFile' => 'catalog/includes/templates/child_theme/template_info.php',
    ],
];
PHP
        );

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

    public function testTemplateSearchDirectoriesFollowInheritanceChain(): void
    {
        $resolver = new \Zencart\ResourceLoaders\TemplateResolver(
            $this->fixtureRoot,
            $this->fixtureRoot . '/includes/templates',
            $this->fixtureRoot . '/zc_plugins',
            $this->getInstalledPlugins()
        );
        $directories = zen_get_template_search_directories('child_theme', ['templates'], true, $resolver);

        $this->assertSame([
            $this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme/templates/',
            $this->fixtureRoot . '/includes/templates/responsive_classic/templates/',
            $this->fixtureRoot . '/includes/templates/template_default/templates/',
        ], $directories);
    }

    public function testTemplateLanguageOverrideDirectoriesFollowInheritanceChain(): void
    {
        $resolver = new \Zencart\ResourceLoaders\TemplateResolver(
            $this->fixtureRoot,
            $this->fixtureRoot . '/includes/templates',
            $this->fixtureRoot . '/zc_plugins',
            $this->getInstalledPlugins()
        );

        $directories = zen_get_template_language_override_directories(
            'child_theme',
            $this->fixtureRoot . '/includes/languages',
            'english',
            'extra_definitions',
            true,
            $resolver
        );

        $this->assertSame([
            $this->fixtureRoot . '/includes/languages/english/extra_definitions/child_theme/',
            $this->fixtureRoot . '/includes/languages/english/extra_definitions/responsive_classic/',
            $this->fixtureRoot . '/includes/languages/english/extra_definitions/template_default/',
        ], $directories);
    }

    public function testTemplateFirstLanguageDirectoriesFollowInheritanceChain(): void
    {
        $resolver = new \Zencart\ResourceLoaders\TemplateResolver(
            $this->fixtureRoot,
            $this->fixtureRoot . '/includes/templates',
            $this->fixtureRoot . '/zc_plugins',
            $this->getInstalledPlugins()
        );

        $directories = zen_get_template_first_language_directories(
            'child_theme',
            $this->fixtureRoot . '/includes/languages',
            true,
            $resolver
        );

        $this->assertSame([
            $this->fixtureRoot . '/includes/languages/child_theme/',
            $this->fixtureRoot . '/includes/languages/responsive_classic/',
            $this->fixtureRoot . '/includes/languages/template_default/',
        ], $directories);
    }

    public function testTemplateCatalogOverrideDirectoriesIncludePluginAndInheritedCorePaths(): void
    {
        $resolver = new \Zencart\ResourceLoaders\TemplateResolver(
            $this->fixtureRoot,
            $this->fixtureRoot . '/includes/templates',
            $this->fixtureRoot . '/zc_plugins',
            $this->getInstalledPlugins()
        );

        $directories = zen_get_template_catalog_override_directories(
            'child_theme',
            'includes/modules/sideboxes',
            true,
            $resolver
        );

        $this->assertSame([
            'zc_plugins/ChildTheme/v1.0.0/catalog/includes/modules/sideboxes/child_theme/',
            'includes/modules/sideboxes/responsive_classic/',
            'includes/modules/sideboxes/template_default/',
        ], $directories);
    }

    public function testTemplateInitFilePathResolvesPluginTemplateFilesystemPath(): void
    {
        file_put_contents(
            $this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme/template_init.php',
            "<?php\n"
        );

        $resolver = new \Zencart\ResourceLoaders\TemplateResolver(
            $this->fixtureRoot,
            $this->fixtureRoot . '/includes/templates',
            $this->fixtureRoot . '/zc_plugins',
            $this->getInstalledPlugins()
        );

        $this->assertSame(
            $this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme/template_init.php',
            zen_get_template_init_file_path('child_theme', $resolver)
        );
    }

    public function testTemplateScreenshotWebPathResolvesPluginTemplateWebPath(): void
    {
        $resolver = new \Zencart\ResourceLoaders\TemplateResolver(
            $this->fixtureRoot,
            $this->fixtureRoot . '/includes/templates',
            $this->fixtureRoot . '/zc_plugins',
            $this->getInstalledPlugins()
        );

        $this->assertSame(
            '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme/images/screenshot.png',
            zen_get_template_screenshot_web_path('child_theme', $resolver)
        );
    }
/*
 * Removing for now (PR #12), since these tests look only at the file system, but
 * the zen_resolve_template_key now takes only a single, optional parameter of the
 * TemplateResolver class and returns either the discovered, selected template or
 * template_default if that template's not found in the file system.
 */
/*
    public function testResolveTemplateKeyFallsBackToTemplateDefaultForMissingTemplate(): void
    {
        $resolver = new \Zencart\ResourceLoaders\TemplateResolver(
            $this->fixtureRoot,
            $this->fixtureRoot . '/includes/templates',
            $this->fixtureRoot . '/zc_plugins'
        );

        $this->assertSame('child_theme', zen_resolve_template_key('child_theme', $resolver));
        $this->assertSame('template_default', zen_resolve_template_key('missing_theme', $resolver));
    }
*/
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

    private function getInstalledPlugins(): array
    {
        return [
            ['unique_key' => 'ChildTheme', 'version' => 'v1.0.0'],
        ];
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
