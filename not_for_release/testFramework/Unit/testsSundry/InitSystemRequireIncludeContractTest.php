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
 * Pins the contract for the require and include auto_loader types.
 *
 * Both used loadFile exactly as written, for core and plugin entries alike, behind an
 * empty `if ($entry['loaderType'] === 'plugin')` block that had stood unimplemented
 * since v1.5.7. The block is removed and the behaviour it appeared to promise is
 * recorded here instead: these two types do not resolve anything, so an entry has to
 * supply a path that is usable as-is.
 *
 * A plugin needing a file from its own tree should use the class or init_script types,
 * which do resolve, or the Zencart\Plugins namespaces registered at bootstrap.
 */
class InitSystemRequireIncludeContractTest extends TestCase
{
    private const TEST_PLUGIN = 'UnitTestContractPlugin';
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

    private function initSystem(): InitSystem
    {
        return new InitSystem(
            'catalog',
            'config',
            new FileSystem(),
            new PluginManager(
                $this->createStub(PluginControlRepository::class),
                $this->createStub(PluginControlVersionRepository::class)
            ),
            [self::TEST_PLUGIN => ['unique_key' => self::TEST_PLUGIN, 'version' => self::TEST_VERSION]]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function runEntry(string $autoType, string $loadFile, string $loaderType): array
    {
        return $this->initSystem()->processLoaderList([
            200 => [[
                'autoType' => $autoType,
                'loaderType' => $loaderType,
                'loadFile' => $loadFile,
                'forceLoad' => false,
                'pluginInfo' => ['unique_key' => self::TEST_PLUGIN],
            ]],
        ]);
    }

    private function createPluginFile(string $relativeName): string
    {
        $path = DIR_FS_CATALOG . 'zc_plugins/' . self::TEST_PLUGIN . '/' . self::TEST_VERSION
            . '/catalog/' . $relativeName;
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, "<?php\n// unit test fixture\n");
        return $path;
    }

    /**
     * A path that is usable as-is is queued unchanged, whichever loaderType it came
     * from. This is what the removed branch would have had to preserve.
     */
    #[DataProvider('autoTypeProvider')]
    public function testFullPathIsQueuedUnchangedForBothLoaderTypes(string $autoType, string $expectedActionType): void
    {
        $path = $this->createPluginFile('some_included_file.php');

        foreach (['core', 'plugin'] as $loaderType) {
            $actions = $this->runEntry($autoType, $path, $loaderType);

            $this->assertCount(1, $actions, $loaderType . ' should queue one action');
            $this->assertSame($expectedActionType, $actions[0]['type']);
            $this->assertSame($path, $actions[0]['filePath'], $loaderType . ' path should be unchanged');
        }
    }

    /**
     * The behaviour the empty branch looked like it was going to add, and never did:
     * a plugin-relative loadFile is not resolved against the plugin directory, so it
     * finds nothing and queues nothing.
     */
    #[DataProvider('autoTypeProvider')]
    public function testPluginRelativePathIsNotResolved(string $autoType, string $expectedActionType): void
    {
        $this->createPluginFile('some_included_file.php');

        $this->assertSame([], $this->runEntry($autoType, 'some_included_file.php', 'plugin'));
    }

    public static function autoTypeProvider(): array
    {
        return [
            'require' => ['require', 'require'],
            'include' => ['include', 'include'],
        ];
    }
}
