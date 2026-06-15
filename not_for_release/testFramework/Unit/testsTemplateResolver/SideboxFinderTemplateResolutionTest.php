<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use Tests\Support\zcTemplateResolverTest;
use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\SideboxFinder;
use Zencart\ResourceLoaders\TemplateResolver;

class SideboxFinderTemplateResolutionTest extends zcTemplateResolverTest
{
    private string $fixtureRoot;

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/SideboxFinder.php';

        $this->fixtureRoot = sys_get_temp_dir() . '/zencart-sidebox-finder-' . uniqid('', true);
        mkdir($this->fixtureRoot . '/includes/templates/template_default', 0777, true);
        mkdir($this->fixtureRoot . '/includes/templates/responsive_classic', 0777, true);
        mkdir($this->fixtureRoot . '/includes/modules/sideboxes/template_default', 0777, true);
        mkdir($this->fixtureRoot . '/includes/modules/sideboxes/responsive_classic', 0777, true);
        mkdir($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme', 0777, true);
        mkdir($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/modules/sideboxes/child_theme', 0777, true);

        $this->writeTemplateInfo($this->fixtureRoot . '/includes/templates/template_default/template_info.php', 'Template Default');
        $this->writeTemplateInfo($this->fixtureRoot . '/includes/templates/responsive_classic/template_info.php', 'Responsive Classic');
        $this->writeTemplateInfo($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/templates/child_theme/template_info.php', 'Child Theme');

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

        file_put_contents($this->fixtureRoot . '/includes/modules/sideboxes/template_default/base_box.php', "<?php\n");
        file_put_contents($this->fixtureRoot . '/includes/modules/sideboxes/responsive_classic/base_override_box.php', "<?php\n");
        file_put_contents($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/modules/sideboxes/child_theme/child_box.php', "<?php\n");
        file_put_contents($this->fixtureRoot . '/includes/modules/sideboxes/responsive_classic/shared_box.php', "<?php\n");
        file_put_contents($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/modules/sideboxes/child_theme/shared_box.php', "<?php\n");

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

    public function testFindFromFilesystemIncludesInheritedAndPluginTemplateSideboxes(): void
    {
        $finder = $this->makeFinder();

        $sideboxes = $finder->findFromFilesystem([], 'child_theme');

        $this->assertArrayHasKey('base_box.php', $sideboxes);
        $this->assertArrayHasKey('base_override_box.php', $sideboxes);
        $this->assertArrayHasKey('child_box.php', $sideboxes);
        $this->assertSame('ChildTheme/v1.0.0', $sideboxes['child_box.php']);
    }

    public function testSideboxPathFallsBackThroughTemplateInheritance(): void
    {
        $finder = $this->makeFinder();

        $path = $finder->sideboxPath(['layout_box_name' => 'base_override_box.php', 'plugin_details' => ''], 'child_theme', true);

        $this->assertSame($this->fixtureRoot . '/includes/modules/sideboxes/responsive_classic/', $path);
    }

    public function testSideboxPathFindsPluginTemplateSpecificDirectory(): void
    {
        $finder = $this->makeFinder();

        $path = $finder->sideboxPath(['layout_box_name' => 'child_box.php', 'plugin_details' => 'ChildTheme/v1.0.0'], 'child_theme', true);

        $this->assertSame($this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/modules/sideboxes/child_theme/', $path);
    }

    public function testSideboxPathPrefersChildTemplateOverrideOverInheritedParent(): void
    {
        $finder = $this->makeFinder();

        $path = $finder->sideboxPath(['layout_box_name' => 'shared_box.php', 'plugin_details' => 'ChildTheme/v1.0.0'], 'child_theme', true);

        $this->assertSame(
            $this->fixtureRoot . '/zc_plugins/ChildTheme/v1.0.0/catalog/includes/modules/sideboxes/child_theme/',
            $path
        );
    }

    private function makeFinder(): SideboxFinder
    {
        $resolver = new TemplateResolver(
            $this->fixtureRoot,
            $this->fixtureRoot . '/includes/templates',
            $this->fixtureRoot . '/zc_plugins',
            $this->getInstalledPlugins()
        );

        return new SideboxFinder(new FileSystem(), $resolver, $this->fixtureRoot);
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
