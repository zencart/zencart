<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;
use Zencart\DbRepositories\PluginControlRepository;
use Zencart\DbRepositories\PluginControlVersionRepository;
use Zencart\FileSystem\FileSystem;
use Zencart\InitSystem\InitSystem;
use Zencart\PluginManager\PluginManager;

/**
 * Covers the directory an auto_loader entry resolves to for a zc_plugin.
 *
 * A plugin's classPath addresses files inside its own tree, in whichever context is
 * loading it, so it is a relative fragment. Anything absolute or containing '..' is
 * refused rather than concatenated, because the result would name a location outside
 * the plugin that a caller would then include from.
 */
class InitSystemPluginDirectoryTest extends TestCase
{
    private const TEST_PLUGIN = 'UnitTestDirectoryPlugin';
    private const TEST_VERSION = 'v1.0.0';

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
    }

    protected function tearDown(): void
    {
        $root = DIR_FS_CATALOG . 'zc_plugins/' . self::TEST_PLUGIN;
        if (is_dir($root)) {
            (new FileSystem())->deleteDirectory($root);
        }
        parent::tearDown();
    }

    private function pluginRoot(): string
    {
        return DIR_FS_CATALOG . 'zc_plugins/' . self::TEST_PLUGIN . '/' . self::TEST_VERSION . '/';
    }

    private function initSystem(string $context): InitSystem
    {
        $pluginManager = new PluginManager(
            $this->createStub(PluginControlRepository::class),
            $this->createStub(PluginControlVersionRepository::class)
        );

        $installedPlugins = [
            self::TEST_PLUGIN => [
                'unique_key' => self::TEST_PLUGIN,
                'version' => self::TEST_VERSION,
            ],
        ];

        return new class ($context, 'config', new FileSystem(), $pluginManager, $installedPlugins) extends InitSystem {
            public function pluginDirectoryFor(string $filePath, string $pluginName): ?string
            {
                return $this->findPluginDirectory($filePath, $pluginName);
            }
        };
    }

    // ------------------------------------------------------ relative resolution

    public function testRelativeClassPathResolvesInsideTheAdminTree(): void
    {
        $this->assertSame(
            $this->pluginRoot() . 'admin/' . DIR_WS_CLASSES,
            $this->initSystem('admin')->pluginDirectoryFor(DIR_WS_CLASSES, self::TEST_PLUGIN)
        );
    }

    public function testRelativeClassPathResolvesInsideTheCatalogTree(): void
    {
        $this->assertSame(
            $this->pluginRoot() . 'catalog/' . DIR_WS_CLASSES,
            $this->initSystem('catalog')->pluginDirectoryFor(DIR_WS_CLASSES, self::TEST_PLUGIN)
        );
    }

    public function testInitScriptDirectoryResolvesInsideThePlugin(): void
    {
        $this->assertSame(
            $this->pluginRoot() . 'admin/' . DIR_WS_INCLUDES . 'init_includes/',
            $this->initSystem('admin')->pluginDirectoryFor(DIR_WS_INCLUDES . 'init_includes/', self::TEST_PLUGIN)
        );
    }

    public function testEmptyPathResolvesToThePluginContextRoot(): void
    {
        $this->assertSame(
            $this->pluginRoot() . 'admin/',
            $this->initSystem('admin')->pluginDirectoryFor('', self::TEST_PLUGIN)
        );
    }

    // ------------------------------------------------------------- refused paths

    /**
     * A refused path yields no directory at all. Returning the plugin's context root
     * instead would let the caller load whatever file of the same name happened to sit
     * there, which is the silent-wrong-file outcome this is meant to prevent.
     *
     * @param string $marker '@catalog@' / '@admin@' are substituted here rather than
     *                       in the provider, which runs before setUpBeforeClass().
     */
    #[DataProvider('refusedPathProvider')]
    public function testPathsOutsideThePluginAreRefused(string $marker): void
    {
        $path = str_replace(
            ['@catalog@', '@admin@'],
            [DIR_FS_CATALOG, DIR_FS_ADMIN],
            $marker
        );

        $this->assertNull(
            $this->initSystem('admin')->pluginDirectoryFor($path, self::TEST_PLUGIN),
            $path . ' should have been refused'
        );
    }

    /**
     * The specific outcome the null return exists to prevent: a refused classPath must
     * not fall through to a same-named file sitting in the plugin's context root.
     */
    public function testRefusedClassPathDoesNotLoadASameNamedFileFromThePluginRoot(): void
    {
        $decoy = $this->pluginRoot() . 'admin/observers';
        mkdir($decoy, 0777, true);
        file_put_contents($decoy . '/UnitTestDirectoryObserver.php', "<?php\n// decoy\n");

        $this->assertNull(
            $this->resolveClassEntry('admin', ['classPath' => '/etc/'])
        );
    }

    public function testUnknownPluginKeyIsRefused(): void
    {
        $this->assertNull(
            $this->initSystem('admin')->pluginDirectoryFor(DIR_WS_CLASSES, 'NoSuchPlugin')
        );
    }

    public static function refusedPathProvider(): array
    {
        return [
            'absolute catalog classPath' => ['@catalog@' . 'includes/classes/'],
            'absolute admin classPath' => ['@admin@' . 'includes/classes/'],
            'absolute path outside the store' => ['/etc/'],
            'relative traversal' => ['../../../etc/'],
            'traversal below a real directory' => ['includes/../../../../etc/'],
            'traversal at the end' => ['includes/classes/..'],
            'backslash traversal' => ['..\\..\\etc\\'],
            'backslash-rooted path' => ['\\etc\\'],
            'windows drive-rooted, backslash' => ['C:\\xampp\\htdocs\\'],
            'windows drive-rooted, forward slash' => ['C:/xampp/htdocs/'],
        ];
    }

    /**
     * A directory whose name merely begins with dots is not traversal and must
     * still resolve.
     */
    public function testDotPrefixedDirectoryNameIsNotMistakenForTraversal(): void
    {
        $this->assertSame(
            $this->pluginRoot() . 'admin/includes/..hidden/',
            $this->initSystem('admin')->pluginDirectoryFor('includes/..hidden/', self::TEST_PLUGIN)
        );
    }

    // ------------------------------------------- end-to-end through the loader

    /**
     * The cases above call findPluginDirectory() directly. These drive the real
     * loader, so they also cover what processAutoTypeClass() hands it -- notably the
     * value used when classPath is omitted entirely.
     *
     * @param array<string, mixed> $entryExtras
     */
    private function resolveClassEntry(string $context, array $entryExtras = []): ?string
    {
        $loaderList = [
            200 => [
                array_merge([
                    'autoType' => 'class',
                    'loaderType' => 'plugin',
                    'loadFile' => 'observers/UnitTestDirectoryObserver.php',
                    'forceLoad' => false,
                    'pluginInfo' => ['unique_key' => self::TEST_PLUGIN],
                ], $entryExtras),
            ],
        ];

        foreach ($this->initSystem($context)->processLoaderList($loaderList) as $action) {
            if (($action['type'] ?? null) === 'include') {
                return $action['filePath'];
            }
        }
        return null;
    }

    private function createPluginClassFile(string $side): void
    {
        $dir = $this->pluginRoot() . $side . '/' . DIR_WS_CLASSES . 'observers';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/UnitTestDirectoryObserver.php', "<?php\n// unit test fixture\n");
    }

    /**
     * Omitting classPath means "my own tree, current context". Sending the absolute
     * non-plugin default instead would resolve admin-side plugin classes elsewhere.
     */
    public function testOmittedClassPathLoadsFromThePluginAdminTree(): void
    {
        $this->createPluginClassFile('admin');

        $this->assertSame(
            $this->pluginRoot() . 'admin/' . DIR_WS_CLASSES . 'observers/UnitTestDirectoryObserver.php',
            $this->resolveClassEntry('admin')
        );
    }

    public function testOmittedClassPathLoadsFromThePluginCatalogTree(): void
    {
        $this->createPluginClassFile('catalog');

        $this->assertSame(
            $this->pluginRoot() . 'catalog/' . DIR_WS_CLASSES . 'observers/UnitTestDirectoryObserver.php',
            $this->resolveClassEntry('catalog')
        );
    }

    public function testExplicitRelativeClassPathMatchesOmission(): void
    {
        $this->createPluginClassFile('admin');

        $this->assertSame(
            $this->resolveClassEntry('admin'),
            $this->resolveClassEntry('admin', ['classPath' => DIR_WS_CLASSES])
        );
    }

    /**
     * An absolute classPath is refused, so no include is queued at all. Previously it
     * produced a path with the store root embedded twice, which could never be found
     * and which left the following classInstantiate to fatal on a missing class.
     */
    public function testAbsoluteClassPathQueuesNoInclude(): void
    {
        $this->createPluginClassFile('admin');

        $this->assertNull(
            $this->resolveClassEntry('admin', ['classPath' => DIR_FS_CATALOG . DIR_WS_CLASSES])
        );
    }

    // ------------------------------------------------------------- init_script

    /**
     * @return array{0: ?string, 1: string[]} the queued require path, and any warnings
     */
    private function resolveInitScript(string $loadFile): array
    {
        $warnings = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$warnings): bool {
            $warnings[] = $errstr;
            return true;
        });

        try {
            $actions = $this->initSystem('admin')->processLoaderList([
                200 => [[
                    'autoType' => 'init_script',
                    'loaderType' => 'plugin',
                    'loadFile' => $loadFile,
                    'forceLoad' => false,
                    'pluginInfo' => ['unique_key' => self::TEST_PLUGIN],
                ]],
            ]);
        } finally {
            restore_error_handler();
        }

        foreach ($actions as $action) {
            if (($action['type'] ?? null) === 'require') {
                return [$action['filePath'], $warnings];
            }
        }
        return [null, $warnings];
    }

    private function createPluginInitScript(string $name): string
    {
        $dir = $this->pluginRoot() . 'admin/' . DIR_WS_INCLUDES . 'init_includes';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/' . $name, "<?php\n// unit test fixture\n");
        return $dir . '/' . $name;
    }

    /**
     * loadFile is appended after the directory is validated, so a traversal there
     * escapes the plugin just as effectively as one in classPath.
     */
    #[DataProvider('escapingLoadFileProvider')]
    public function testTraversalInClassLoadFileIsRefused(string $loadFile): void
    {
        $this->createPluginClassFile('admin');

        $this->assertNull($this->resolveClassEntry('admin', ['loadFile' => $loadFile]));
    }

    /**
     * The fixture directory has to exist: file_exists() will not resolve a '..'
     * through a directory that is absent, so without it the traversal would fail to
     * resolve and the test would pass without exercising the check.
     */
    #[DataProvider('escapingLoadFileProvider')]
    public function testTraversalInInitScriptLoadFileIsRefused(string $loadFile): void
    {
        $this->createPluginInitScript('init_unit_test.php');

        [$queued, ] = $this->resolveInitScript($loadFile);

        $this->assertNull($queued);
    }

    /**
     * The first two cases resolve to a file that genuinely exists outside the plugin
     * -- six levels up from either base directory is the store root -- so without the
     * check the entry would be found and queued. The rest would fail to resolve
     * anyway; they are here so the validator is pinned rather than the filesystem.
     */
    public static function escapingLoadFileProvider(): array
    {
        $toStoreRoot = str_repeat('../', 6);

        return [
            'climbs out to a real file' => [$toStoreRoot . 'includes/defined_paths.php'],
            'climbs out below a real directory' => ['observers/../' . $toStoreRoot . 'includes/defined_paths.php'],
            'backslash traversal' => ['..\\..\\..\\..\\includes\\defined_paths.php'],
            'absolute' => ['/etc/passwd'],
        ];
    }

    /**
     * classPath and loadFile are validated together, not separately. Each half here is
     * harmless on its own, but concatenating '.' with './manifest.php' gives
     * '../manifest.php', which climbs out of the context directory into the plugin
     * version root -- and from there into the other context's tree.
     */
    public function testIndividuallyValidFragmentsThatComposeIntoTraversalAreRefused(): void
    {
        $this->createPluginClassFile('admin');
        file_put_contents($this->pluginRoot() . 'manifest.php', "<?php\n// decoy\n");

        $this->assertNull(
            $this->resolveClassEntry('admin', ['classPath' => '.', 'loadFile' => './manifest.php'])
        );
    }

    /**
     * file_exists() is true for a directory, so an empty or directory-valued loadFile
     * would be queued and then fatal on the require/include.
     */
    public function testDirectoryValuedLoadFileIsNotQueued(): void
    {
        $this->createPluginClassFile('admin');

        $this->assertNull($this->resolveClassEntry('admin', ['loadFile' => 'observers']));
        $this->assertNull($this->resolveClassEntry('admin', ['loadFile' => '']));
    }

    public function testDirectoryValuedInitScriptLoadFileIsNotQueued(): void
    {
        $this->createPluginInitScript('init_unit_test.php');

        [$queued, ] = $this->resolveInitScript('');

        $this->assertNull($queued);
    }

    /**
     * A core init_script keeps failing fast: continuing without one would leave the
     * request half-initialised, which is worse than stopping.
     */
    public function testMissingCoreInitScriptIsStillQueued(): void
    {
        $actions = $this->initSystem('admin')->processLoaderList([
            200 => [[
                'autoType' => 'init_script',
                'loaderType' => 'core',
                'loadFile' => 'init_does_not_exist.php',
                'forceLoad' => false,
            ]],
        ]);

        $this->assertCount(1, $actions);
        $this->assertSame('require', $actions[0]['type']);
    }

    public function testInitScriptThatExistsIsQueued(): void
    {
        $expected = $this->createPluginInitScript('init_unit_test.php');

        [$queued, $warnings] = $this->resolveInitScript('init_unit_test.php');

        $this->assertSame($expected, $queued);
        $this->assertSame([], $warnings);
    }

    /**
     * A missing init_script previously queued a require regardless, which the
     * autoloader loop turned into a fatal during bootstrap.
     */
    public function testMissingInitScriptIsNotQueuedAndWarns(): void
    {
        [$queued, $warnings] = $this->resolveInitScript('init_does_not_exist.php');

        $this->assertNull($queued);
        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('init_does_not_exist.php', $warnings[0]);
        $this->assertStringContainsString('was not found', $warnings[0]);
    }
}
