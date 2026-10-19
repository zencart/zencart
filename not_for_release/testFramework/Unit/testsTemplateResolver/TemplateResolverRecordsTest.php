<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcTemplateResolverTest;
use Zencart\PluginManager\PluginManager;
use Zencart\ResourceLoaders\TemplateResolver;

/**
 * Exercises how TemplateResolver builds its template records, inheritance chains and plugin
 * filtering from fixture directories created under the system temp directory.
 */
#[RunTestsInSeparateProcesses]
class TemplateResolverRecordsTest extends zcTemplateResolverTest
{
    private string $root;

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $this->root = sys_get_temp_dir() . '/zencart-template-records-' . uniqid('', true);
        mkdir($this->root . '/includes/templates', 0777, true);
        mkdir($this->root . '/zc_plugins', 0777, true);
        $GLOBALS['db'] = null;
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->root);
        parent::tearDown();
    }

    public function testCoreTemplateInfoFieldsAreExposedOnTheRecord(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writeCoreTemplate('full', <<<'PHP'
$template_name = 'Full Template';
$template_version = '2.5';
$template_author = 'Someone';
$template_description = 'Everything set';
$template_screenshot = 'shot.png';
$template_base = 'template_default';
$uses_single_column_layout_settings = true;
$uses_mobile_sidebox_settings = false;
PHP, true);

        $record = $this->makeResolver()->getTemplateRecord('full');

        $this->assertSame('Full Template', $record['name']);
        $this->assertSame('2.5', $record['version']);
        $this->assertSame('Someone', $record['author']);
        $this->assertSame('Everything set', $record['description']);
        $this->assertSame('shot.png', $record['screenshot']);
        $this->assertSame('template_default', $record['base_template']);
        $this->assertTrue($record['uses_single_column_layout_settings']);
        $this->assertFalse($record['uses_mobile_sidebox_settings']);
        $this->assertTrue($record['has_template_settings']);
    }

    public function testCoreTemplateInfoDefaultsWhenOnlyANameIsGiven(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writeCoreTemplate('minimal', "\$template_name = 'Minimal';");

        $record = $this->makeResolver()->getTemplateRecord('minimal');

        $this->assertSame('Minimal', $record['name']);
        $this->assertNull($record['version']);
        $this->assertNull($record['author']);
        $this->assertNull($record['description']);
        $this->assertNull($record['screenshot']);
        $this->assertSame('template_default', $record['base_template']);
        $this->assertFalse($record['uses_single_column_layout_settings']);
        $this->assertTrue($record['uses_mobile_sidebox_settings']);
        $this->assertFalse($record['has_template_settings']);
    }

    public function testBaseTemplateVariablePrecedenceInTemplateInfo(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writeCoreTemplate('legacy_base', "\$base_template = 'parent_a';");
        $this->writeCoreTemplate('both_bases', "\$template_base = 'parent_b';\n\$base_template = 'parent_a';");
        $this->writeCoreTemplate('parent_a');
        $this->writeCoreTemplate('parent_b');

        $resolver = $this->makeResolver();

        $this->assertSame('parent_a', $resolver->getBaseTemplate('legacy_base'));
        $this->assertSame('parent_b', $resolver->getBaseTemplate('both_bases'));
    }

    public function testCoreTemplateRecordPathsUseNormalizedCatalogRoot(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writeCoreTemplate('paths_template');

        $resolver = new TemplateResolver(
            $this->root . '/',
            null,
            null,
            []
        );

        $record = $resolver->getTemplateRecord('paths_template');

        $this->assertSame($this->root . '/', $record['template_base_fs']);
        $this->assertSame($this->root . '/includes/templates/paths_template/', $record['template_path']);
        $this->assertSame('includes/templates/paths_template/', $record['template_catalog_path']);
        $this->assertSame('/includes/templates/paths_template/', $record['template_web_path']);
        $this->assertSame($this->root . '/includes/templates/paths_template/template_settings.php', $record['template_settings_path']);
        $this->assertSame('paths_template', $record['template_key']);
        $this->assertSame('core', $record['template_source']);
        $this->assertFalse($record['is_plugin_template']);
        $this->assertFalse($resolver->isPluginTemplate('paths_template'));
        $this->assertSame($this->root . '/', $resolver->getTemplateBasePath('paths_template'));
    }

    public function testFilesAndDirectoriesWithoutTemplateInfoAreIgnored(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writeCoreTemplate('real_one');
        mkdir($this->root . '/includes/templates/no_info', 0777, true);
        file_put_contents($this->root . '/includes/templates/stray_file', 'x');

        $templates = $this->makeResolver()->getSelectableTemplates(true);

        $this->assertSame(['real_one', 'template_default'], $this->sortedKeys($templates));
    }

    public function testSelectableTemplatesOptionallyIncludeTemplateDefault(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writeCoreTemplate('other');

        $resolver = $this->makeResolver();

        $this->assertSame(['other'], $this->sortedKeys($resolver->getSelectableTemplates()));
        $this->assertSame(['other', 'template_default'], $this->sortedKeys($resolver->getSelectableTemplates(true)));
    }

    public function testMissingTemplatesOrPluginsDirectoryIsTolerated(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginTemplate('ThemePlugin', 'themed');

        $noPluginsRoot = new TemplateResolver(
            $this->root,
            $this->root . '/includes/templates',
            $this->root . '/no_such_plugins_dir',
            $this->plugins('ThemePlugin')
        );
        $this->assertSame(['template_default'], $this->sortedKeys($noPluginsRoot->getSelectableTemplates(true)));

        $noCoreRoot = new TemplateResolver(
            $this->root,
            $this->root . '/no_such_templates_dir',
            $this->root . '/zc_plugins',
            $this->plugins('ThemePlugin')
        );
        $this->assertSame(['themed'], $this->sortedKeys($noCoreRoot->getSelectableTemplates(true)));
    }

    public function testDefaultCoreAndPluginLocationsAreDerivedFromTheCatalogRoot(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginTemplate('ThemePlugin', 'themed');

        $resolver = new TemplateResolver($this->root, null, null, $this->plugins('ThemePlugin'));

        $this->assertSame(['template_default', 'themed'], $this->sortedKeys($resolver->getSelectableTemplates(true)));
    }

    public function testInstalledPluginsComeFromThePluginManagerWhenNoneAreGiven(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginTemplate('ThemePlugin', 'themed');

        $pluginManager = $this->getMockBuilder(PluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->method('getInstalledPlugins')->willReturn(['ThemePlugin' => ['version' => 'v1.0.0']]);

        $resolver = new TemplateResolver($this->root, null, null, null, $pluginManager);

        $this->assertSame(['template_default', 'themed'], $this->sortedKeys($resolver->getSelectableTemplates(true)));
    }

    public function testAnInjectedPluginManagerIsNotReplacedWhenADatabaseIsAvailable(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginTemplate('ThemePlugin', 'themed');
        $this->instantiateQfr(['template_id' => '1']);

        $pluginManager = $this->getMockBuilder(PluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects($this->atLeastOnce())
            ->method('getInstalledPlugins')
            ->willReturn(['ThemePlugin' => ['version' => 'v1.0.0']]);

        $resolver = new TemplateResolver($this->root, null, null, null, $pluginManager);

        $this->assertSame(['template_default', 'themed'], $this->sortedKeys($resolver->getSelectableTemplates(true)));
    }

    public function testNoPluginTemplatesWhenThereAreNoInstalledPluginsOrPluginManager(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginTemplate('ThemePlugin', 'themed');

        $resolver = new TemplateResolver($this->root, null, null, null);

        $this->assertSame(['template_default'], $this->sortedKeys($resolver->getSelectableTemplates(true)));
    }

    public function testInheritanceChainStopsOnCyclesUnknownBasesAndSelfReferences(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writeCoreTemplate('cycle_a', "\$template_base = 'cycle_b';");
        $this->writeCoreTemplate('cycle_b', "\$template_base = 'cycle_a';");
        $this->writeCoreTemplate('ghost_base', "\$template_base = 'does_not_exist';");
        $this->writeCoreTemplate('self_base', "\$template_base = 'self_base';");

        $resolver = $this->makeResolver();

        $this->assertSame(['cycle_a', 'cycle_b', 'template_default'], $resolver->getTemplateInheritanceChain('cycle_a'));
        $this->assertSame(['ghost_base', 'template_default'], $resolver->getTemplateInheritanceChain('ghost_base'));
        $this->assertSame(['self_base', 'template_default'], $resolver->getTemplateInheritanceChain('self_base'));
        $this->assertNull($resolver->getTemplateRecord('self_base')['base_template']);
        $this->assertSame(['template_default'], $resolver->getTemplateInheritanceChain('unknown_template'));
        $this->assertSame(['template_default'], $resolver->getTemplateInheritanceChain(''));
    }

    public function testInheritanceChainWithoutTemplateDefaultInstalled(): void
    {
        $this->writeCoreTemplate('lonely');

        $resolver = $this->makeResolver();

        $this->assertSame(['lonely'], $resolver->getTemplateInheritanceChain('lonely'));
        $this->assertSame([], $resolver->getTemplateInheritanceChain('unknown_template'));
    }

    public function testUnknownTemplateKeysReturnSafeDefaults(): void
    {
        $this->writeCoreTemplate('template_default');

        $resolver = $this->makeResolver();

        $this->assertNull($resolver->getTemplateRecord('nope'));
        $this->assertNull($resolver->getTemplateFilesystemPath('nope'));
        $this->assertNull($resolver->getTemplateCatalogPath('nope'));
        $this->assertNull($resolver->getTemplateWebPath('nope'));
        $this->assertSame('', $resolver->getTemplateBasePath('nope'));
        $this->assertSame('template_default', $resolver->getBaseTemplate('nope'));
        $this->assertFalse($resolver->isPluginTemplate('nope'));
        $this->assertFalse($resolver->isActiveTemplate('nope'));
        $this->assertFalse($resolver->isActiveTemplate('template_default'));
    }

    public function testOnlyValidTemplateManifestsProduceTemplateRecords(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginManifest('NoManifest', null);
        $this->writePluginManifest('ScalarManifest', "return 'nope';");
        $this->writePluginManifest('NoTemplate', "return ['pluginName' => 'x'];");
        $this->writePluginManifest('TemplateNotArray', "return ['template' => 'x'];");
        $this->writePluginManifest('EmptyTemplate', "return ['template' => []];");
        $this->writePluginManifest('NoKey', "return ['template' => ['baseTemplate' => 'template_default']];");
        $this->writePluginManifest('EmptyKey', "return ['template' => ['key' => '']];");
        $this->writePluginManifest('MissingInfo', "return ['template' => ['key' => 'missing_info']];");
        $this->writePluginTemplate('GoodPlugin', 'good_theme');

        $resolver = $this->makeResolver($this->plugins(
            'NoManifest',
            'ScalarManifest',
            'NoTemplate',
            'TemplateNotArray',
            'EmptyTemplate',
            'NoKey',
            'EmptyKey',
            'MissingInfo',
            'GoodPlugin'
        ));

        $this->assertSame(['good_theme', 'template_default'], $this->sortedKeys($resolver->getSelectableTemplates(true)));
    }

    public function testPluginTemplateRecordHonoursCustomInfoAndSettingsFiles(): void
    {
        $this->writeCoreTemplate('template_default');
        $versionPath = $this->root . '/zc_plugins/CustomPlugin/v1.0.0';
        mkdir($versionPath . '/catalog/custom/info', 0777, true);
        mkdir($versionPath . '/catalog/custom/settings', 0777, true);
        file_put_contents($versionPath . '/catalog/custom/info/info.php', "<?php\n\$template_name = 'Custom';\n");
        file_put_contents($versionPath . '/catalog/custom/settings/s.php', "<?php\n");
        $this->writePluginManifest('CustomPlugin', <<<'PHP'
return [
    'template' => [
        'key' => 'custom_theme',
        'baseTemplate' => 'custom_theme',
        'infoFile' => '/catalog/custom/info/info.php',
        'settingsFile' => '/catalog/custom/settings/s.php',
    ],
];
PHP);

        $resolver = $this->makeResolver($this->plugins('CustomPlugin'));
        $record = $resolver->getTemplateRecord('custom_theme');

        $this->assertSame('Custom', $record['name']);
        $this->assertSame($versionPath . '/catalog/custom/info/', $record['template_path']);
        $this->assertSame('zc_plugins/CustomPlugin/v1.0.0/catalog/custom/info/', $record['template_catalog_path']);
        $this->assertSame('/zc_plugins/CustomPlugin/v1.0.0/catalog/custom/info/', $record['template_web_path']);
        $this->assertSame($versionPath . '/catalog/custom/settings/s.php', $record['template_settings_path']);
        $this->assertTrue($record['has_template_settings']);
        $this->assertSame($versionPath . '/catalog/', $record['template_base_fs']);
        $this->assertSame('custom_theme', $record['template_key']);
        $this->assertSame('plugin', $record['template_source']);
        $this->assertSame('CustomPlugin', $record['plugin_key']);
        $this->assertSame('v1.0.0', $record['plugin_version']);
        $this->assertSame('custom_theme', $record['manifest']['template']['key']);
        $this->assertNull($record['base_template'], 'a template naming itself as its base has no base');
        $this->assertTrue($resolver->isPluginTemplate('custom_theme'));
    }

    public function testPluginTemplateRecordDefaultsInfoAndSettingsLocations(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginTemplate('DefaultsPlugin', 'defaults_theme');

        $resolver = $this->makeResolver($this->plugins('DefaultsPlugin'));
        $record = $resolver->getTemplateRecord('defaults_theme');

        $templatePath = $this->root . '/zc_plugins/DefaultsPlugin/v1.0.0/catalog/includes/templates/defaults_theme/';
        $this->assertSame($templatePath, $record['template_path']);
        $this->assertSame($templatePath . 'template_settings.php', $record['template_settings_path']);
        $this->assertFalse($record['has_template_settings']);
        $this->assertSame('template_default', $record['base_template']);
    }

    public function testInstalledPluginListIsNormalizedBeforeUse(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginTemplate('KeyedPlugin', 'keyed_theme');
        $this->writePluginTemplate('ListedPlugin', 'listed_theme');
        $this->writePluginTemplate('NoVersionPlugin', 'no_version_theme');

        $resolver = $this->makeResolver([
            'KeyedPlugin' => ['version' => 'v1.0.0'],
            'scalar_entry' => 'not an array',
            'NoVersionPlugin' => ['unique_key' => 'NoVersionPlugin'],
            ['unique_key' => 'ListedPlugin', 'version' => 'v1.0.0'],
            ['version' => 'v1.0.0'],
        ]);

        $this->assertSame(
            ['keyed_theme', 'listed_theme', 'template_default'],
            $this->sortedKeys($resolver->getSelectableTemplates(true))
        );
    }

    public function testRecordsAreCachedPerCatalogRootPluginsRootAndInstalledPlugins(): void
    {
        $this->writeCoreTemplate('template_default');
        $this->writePluginTemplate('ThemePlugin', 'themed');
        mkdir($this->root . '/other_plugins', 0777, true);

        $withPlugin = $this->makeResolver($this->plugins('ThemePlugin'));
        $withoutPlugin = $this->makeResolver([]);
        $otherPluginsRoot = new TemplateResolver(
            $this->root,
            $this->root . '/includes/templates',
            $this->root . '/other_plugins',
            $this->plugins('ThemePlugin')
        );

        $this->assertSame(['template_default', 'themed'], $this->sortedKeys($withPlugin->getSelectableTemplates(true)));
        $this->assertSame(['template_default'], $this->sortedKeys($withoutPlugin->getSelectableTemplates(true)));
        $this->assertSame(['template_default'], $this->sortedKeys($otherPluginsRoot->getSelectableTemplates(true)));
        $this->assertSame(['template_default', 'themed'], $this->sortedKeys($withPlugin->getSelectableTemplates(true)));
    }

    private function makeResolver(?array $plugins = []): TemplateResolver
    {
        return new TemplateResolver(
            $this->root,
            $this->root . '/includes/templates',
            $this->root . '/zc_plugins',
            $plugins
        );
    }

    private function plugins(string ...$keys): array
    {
        return array_map(
            static fn(string $key): array => ['unique_key' => $key, 'version' => 'v1.0.0'],
            $keys
        );
    }

    private function sortedKeys(array $templates): array
    {
        $keys = array_keys($templates);
        sort($keys);
        return $keys;
    }

    private function writeCoreTemplate(string $key, string $infoBody = '', bool $withSettings = false): void
    {
        $dir = $this->root . '/includes/templates/' . $key;
        mkdir($dir, 0777, true);
        $body = $infoBody === '' ? "\$template_name = '" . $key . "';" : $infoBody;
        file_put_contents($dir . '/template_info.php', "<?php\n" . $body . "\n");
        if ($withSettings) {
            file_put_contents($dir . '/template_settings.php', "<?php\n");
        }
    }

    private function writePluginManifest(string $plugin, ?string $returnStatement): void
    {
        $versionPath = $this->root . '/zc_plugins/' . $plugin . '/v1.0.0';
        if (!is_dir($versionPath)) {
            mkdir($versionPath, 0777, true);
        }
        if ($returnStatement !== null) {
            file_put_contents($versionPath . '/manifest.php', "<?php\n" . $returnStatement . "\n");
        }
    }

    private function writePluginTemplate(string $plugin, string $key): void
    {
        $this->writePluginManifest($plugin, "return ['template' => ['key' => '" . $key . "']];");
        $dir = $this->root . '/zc_plugins/' . $plugin . '/v1.0.0/catalog/includes/templates/' . $key;
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/template_info.php', "<?php\n\$template_name = '" . $key . "';\n");
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
