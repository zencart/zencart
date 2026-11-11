<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcTemplateResolverTest;
use Zencart\LanguageLoader\BaseLanguageLoader;
use Zencart\ResourceLoaders\TemplateResolver;

/**
 * Pins the exact path strings, de-duplication and plugin filtering used by BaseLanguageLoader's
 * template-language lookups.
 */
#[RunTestsInSeparateProcesses]
class BaseLanguageLoaderPathHelpersTest extends zcTemplateResolverTest
{
    private string $root;
    private string $pluginsDir;

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/BaseLanguageLoader.php';

        $this->root = sys_get_temp_dir() . '/zencart-language-helpers-' . uniqid('', true);
        $this->pluginsDir = $this->root . '/zc_plugins/';
        mkdir($this->root . '/includes/templates/template_default', 0777, true);
        file_put_contents(
            $this->root . '/includes/templates/template_default/template_info.php',
            "<?php\n\$template_name = 'Template Default';\n"
        );

        $themeDir = $this->root . '/zc_plugins/ThemeP/v1.0.0/catalog/includes/templates/theme_x';
        mkdir($themeDir, 0777, true);
        file_put_contents($themeDir . '/template_info.php', "<?php\n\$template_name = 'Theme X';\n");
        file_put_contents(
            $this->root . '/zc_plugins/ThemeP/v1.0.0/manifest.php',
            "<?php\nreturn ['template' => ['key' => 'theme_x']];\n"
        );

        $this->instantiateQfr(['template_id' => '1']);
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->root);
        parent::tearDown();
    }

    public function testPluginRootsSkipTemplatePluginsAndIncompleteEntriesAndDuplicates(): void
    {
        $loader = $this->makeLoader('theme_x', [
            ['unique_key' => 'FeatA', 'version' => 'v1.0.0'],
            ['unique_key' => 'ThemeP', 'version' => 'v1.0.0', 'type' => 'template'],
            ['unique_key' => '', 'version' => 'v1.0.0'],
            ['version' => 'v1.0.0'],
            ['unique_key' => 'NoVersion', 'version' => ''],
            ['unique_key' => 'NoVersionKey'],
            ['unique_key' => 'FeatB', 'version' => 'v2.0.0', 'type' => 'plugin'],
            ['unique_key' => 'FeatA', 'version' => 'v1.0.0'],
        ]);

        $roots = $this->invoke($loader, 'getPluginTemplateLanguageRoots', 'theme_x');

        $this->assertSame([
            $this->pluginsDir . 'ThemeP/v1.0.0/catalog/includes/languages/',
            $this->pluginsDir . 'FeatA/v1.0.0/catalog/includes/languages/',
            $this->pluginsDir . 'FeatB/v2.0.0/catalog/includes/languages/',
        ], $roots);
    }

    public function testPluginRootsForCoreOrUnknownTemplatesOnlyListNonTemplatePlugins(): void
    {
        $loader = $this->makeLoader('template_default', [
            ['unique_key' => 'FeatA', 'version' => 'v1.0.0'],
            ['unique_key' => 'ThemeP', 'version' => 'v1.0.0', 'type' => 'template'],
        ]);

        $expected = [$this->pluginsDir . 'FeatA/v1.0.0/catalog/includes/languages/'];
        $this->assertSame($expected, $this->invoke($loader, 'getPluginTemplateLanguageRoots', 'template_default'));
        $this->assertSame($expected, $this->invoke($loader, 'getPluginTemplateLanguageRoots', 'unknown_template'));
    }

    public function testOverrideDirectoryAndPathAreBuiltWithSingleSlashes(): void
    {
        $loader = $this->makeLoader('template_default', []);

        $this->assertSame(
            '/root/langs/spanish/tpl',
            $this->invoke($loader, 'buildTemplateLanguageOverrideDirectory', '/root/langs//', 'spanish', 'tpl')
        );
        $this->assertSame(
            '/root/langs/spanish/extra/more/tpl',
            $this->invoke($loader, 'buildTemplateLanguageOverrideDirectory', '/root/langs', 'spanish', 'tpl', '/extra/more/')
        );
        $this->assertSame(
            '/root/langs/spanish',
            $this->invoke($loader, 'buildTemplateLanguageOverrideDirectory', '/root/langs/', 'spanish', '')
        );
        $this->assertSame(
            '/root/langs/spanish/extra',
            $this->invoke($loader, 'buildTemplateLanguageOverrideDirectory', '/root/langs/', 'spanish', '', 'extra')
        );
        $this->assertSame(
            '/root/langs/spanish/tpl/lang.page.php',
            $this->invoke($loader, 'buildTemplateLanguageOverridePath', '/root/langs/', 'spanish', 'tpl', 'lang.page.php')
        );
        $this->assertSame(
            '/root/langs/spanish/extra/tpl/lang.page.php',
            $this->invoke($loader, 'buildTemplateLanguageOverridePath', '/root/langs/', 'spanish', 'tpl', 'lang.page.php', 'extra')
        );
    }

    public function testOverrideCandidatesAndDirectoriesNormalizeInputsAndDeduplicate(): void
    {
        $loader = $this->makeLoader('template_default', [
            ['unique_key' => 'FeatA', 'version' => 'v1.0.0'],
        ]);
        $pluginRoot = $this->pluginsDir . 'FeatA/v1.0.0/catalog/includes/languages/';

        $this->assertSame(
            [
                '/core/langs/spanish/extra/tpl/lang.page.php',
                $pluginRoot . 'spanish/extra/tpl/lang.page.php',
            ],
            $this->invoke($loader, 'getTemplateLanguageOverrideCandidates', '/core/langs//', 'spanish', 'tpl', 'lang.page.php', '/extra/')
        );
        $this->assertSame(
            [
                '/core/langs/spanish/tpl/lang.page.php',
                $pluginRoot . 'spanish/tpl/lang.page.php',
            ],
            $this->invoke($loader, 'getTemplateLanguageOverrideCandidates', '/core/langs', 'spanish', 'tpl', 'lang.page.php')
        );
        $this->assertSame(
            [$pluginRoot . 'spanish/tpl/lang.page.php'],
            $this->invoke($loader, 'getTemplateLanguageOverrideCandidates', $pluginRoot, 'spanish', 'tpl', 'lang.page.php'),
            'a plugin root equal to the supplied root is listed once, re-indexed from zero'
        );

        $this->assertSame(
            [
                '/core/langs/spanish/extra/tpl',
                $pluginRoot . 'spanish/extra/tpl',
            ],
            $this->invoke($loader, 'getTemplateLanguageOverrideDirectories', '/core/langs//', 'spanish', 'tpl', '/extra/')
        );
        $this->assertSame(
            [
                '/core/langs/spanish/tpl',
                $pluginRoot . 'spanish/tpl',
            ],
            $this->invoke($loader, 'getTemplateLanguageOverrideDirectories', '/core/langs', 'spanish', 'tpl')
        );
        $this->assertSame(
            [$pluginRoot . 'spanish/tpl'],
            $this->invoke($loader, 'getTemplateLanguageOverrideDirectories', $pluginRoot, 'spanish', 'tpl')
        );

        $twoPlugins = $this->makeLoader('template_default', [
            ['unique_key' => 'FeatA', 'version' => 'v1.0.0'],
            ['unique_key' => 'FeatB', 'version' => 'v1.0.0'],
        ]);
        $this->assertSame(
            [
                $pluginRoot . 'spanish/tpl',
                $this->pluginsDir . 'FeatB/v1.0.0/catalog/includes/languages/spanish/tpl',
            ],
            $this->invoke($twoPlugins, 'getTemplateLanguageOverrideDirectories', $pluginRoot, 'spanish', 'tpl'),
            'duplicates are removed and the result is re-indexed from zero'
        );
    }

    public function testFindingAndListingOverrideFilesNormalizeRootAndExtraPath(): void
    {
        $file = $this->root . '/includes/languages/spanish/extra/template_default/lang.page.php';
        mkdir(dirname($file), 0777, true);
        file_put_contents($file, "<?php\nreturn [];\n");

        $loader = $this->makeLoader('template_default', []);
        $rootPath = $this->root . '/includes/languages';

        foreach ([$rootPath, $rootPath . '/', $rootPath . '//'] as $root) {
            foreach (['extra', '/extra', 'extra/', '/extra/'] as $extra) {
                $this->assertSame(
                    $file,
                    $this->invoke($loader, 'findTemplateLanguageOverrideFile', $root, 'spanish', 'lang.page.php', $extra),
                    "root '$root' extra '$extra'"
                );
                $this->assertSame(
                    [$file],
                    $this->invoke($loader, 'getTemplateLanguageOverrideFiles', $root, 'spanish', 'lang.page.php', $extra),
                    "root '$root' extra '$extra'"
                );
            }
        }

        $this->assertNull($this->invoke($loader, 'findTemplateLanguageOverrideFile', $rootPath, 'spanish', 'lang.missing.php', 'extra'));
        $this->assertSame([], $this->invoke($loader, 'getTemplateLanguageOverrideFiles', $rootPath, 'spanish', 'lang.missing.php', 'extra'));
    }

    public function testFirstLanguageFilesIgnoreTrailingSlashesOnTheLanguageDirectory(): void
    {
        $file = $this->root . '/includes/languages/lang.english.php';
        mkdir(dirname($file), 0777, true);
        file_put_contents($file, "<?php\nreturn [];\n");

        $loader = $this->makeLoader('template_default', []);

        foreach (['includes/languages', 'includes/languages/', 'includes/languages//'] as $languageDir) {
            $this->assertSame(
                [$file],
                $this->invoke($loader, 'getTemplateFirstLanguageFiles', $languageDir, 'lang.english.php'),
                "language dir '$languageDir'"
            );
        }
    }

    public function testInheritanceChainForLookupFallsBackToTheTemplateDirectory(): void
    {
        $withoutDefault = $this->makeLoader('lonely_template', [], false);
        $this->assertSame(['lonely_template'], $this->invoke($withoutDefault, 'getTemplateInheritanceChainForLookup'));
        $this->assertSame(['lonely_template'], $this->invoke($withoutDefault, 'getTemplateInheritanceChainForLookup', true));

        $withDefault = $this->makeLoader('theme_x', [
            ['unique_key' => 'ThemeP', 'version' => 'v1.0.0', 'type' => 'template'],
        ]);
        $this->assertSame(['theme_x', 'template_default'], $this->invoke($withDefault, 'getTemplateInheritanceChainForLookup'));
        $this->assertSame(['template_default', 'theme_x'], $this->invoke($withDefault, 'getTemplateInheritanceChainForLookup', true));
    }

    public function testByteOrderMarkInALanguageFileIsLogged(): void
    {
        $errorLog = $this->root . '/php-error.log';
        $bomFile = $this->root . '/bom.php';
        $cleanFile = $this->root . '/clean.php';
        file_put_contents($bomFile, "\xEF\xBB\xBF<?php\nreturn [];\n");
        file_put_contents($cleanFile, "<?php\nreturn [];\n");
        $previousLog = ini_set('error_log', $errorLog);

        try {
            $this->callWarnIfFileHasBom($cleanFile);
            $this->callWarnIfFileHasBom($this->root . '/does-not-exist.php');
            $this->assertFalse(is_file($errorLog) && trim((string)file_get_contents($errorLog)) !== '', 'nothing is logged for clean or missing files');

            $this->callWarnIfFileHasBom($bomFile);
            $this->assertStringContainsString('byte-order-mark', (string)file_get_contents($errorLog));
            $this->assertStringContainsString('without BOM: ' . $bomFile, (string)file_get_contents($errorLog));
        } finally {
            ini_set('error_log', $previousLog === false ? '' : $previousLog);
        }
    }

    private function callWarnIfFileHasBom(string $file): void
    {
        $call = \Closure::bind(
            static fn(string $f) => static::warnIfFileHasBom($f),
            null,
            BaseLanguageLoader::class
        );
        $call($file);
    }

    private function makeLoader(string $templateDir, array $plugins, bool $withTemplateDefault = true): BaseLanguageLoader
    {
        $resolverPlugins = [['unique_key' => 'ThemeP', 'version' => 'v1.0.0']];
        $templatesPath = $withTemplateDefault ? $this->root . '/includes/templates' : $this->root . '/no_templates';

        $loader = new PathHelpersTestableLoader($plugins, 'index', $templateDir, 'english');
        $loader->configure(
            $this->pluginsDir,
            new TemplateResolver($this->root, $templatesPath, $this->root . '/zc_plugins', $resolverPlugins)
        );

        return $loader;
    }

    private function invoke(BaseLanguageLoader $loader, string $method, mixed ...$arguments): mixed
    {
        $call = \Closure::bind(
            fn() => $this->$method(...$arguments),
            $loader,
            PathHelpersTestableLoader::class
        );

        return $call();
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

class PathHelpersTestableLoader extends BaseLanguageLoader
{
    public function configure(string $pluginsDir, TemplateResolver $templateResolver): void
    {
        $this->zcPluginsDir = $pluginsDir;
        $this->templateResolver = $templateResolver;
    }
}
