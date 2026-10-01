<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Coverage for the version comparison in plugin_version_check_for_updates() (includes/functions/plugin_support.php).
 *
 * The installed side of the comparison is the plugin's version directory name ("v1.0.1" for most
 * encapsulated plugins), while the plugin-library side is whatever the contributor typed when
 * publishing ("1.0.1", "v1.0.1", "Version 1.0", ...). The check must report a newer listing whether
 * or not either side carries a "v", and must stay quiet when the two describe the same version
 * (zencart/zencart#7984).
 *
 * Every case runs the real function against a canned version-server reply, in the same
 * double-encoded JSON shape the server actually sends (a JSON string containing JSON).
 * VersionServer is replaced with a stub aliased into the global namespace, which is only possible
 * while the real class has not been loaded, hence the per-test process isolation.
 */

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Support\zcUnitTestCase;

// Data providers and process isolation are declared both as docblock annotations (PHPUnit 9.6,
// this branch's composer.lock) and as attributes (PHPUnit 10+, master); each version ignores the
// form it does not understand, so the file ports between branches unchanged.
class PluginVersionCheckTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/functions/plugin_support.php';
        require_once DIR_FS_CATALOG . 'includes/version.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general_shared.php';
    }

    /**
     * Each case is (library listing, installed version, expected latest_plugin_version or false).
     * The installed strings are version directory names; the listing strings are shapes seen in
     * the live plugin library. The listing is offered for the running Zen Cart version unless the
     * case says otherwise.
     */
    public static function updateCheckProvider(): array
    {
        return [
            'bare listing, v directory, newer' => ['1.0.1', 'v1.0.0', '1.0.1'],
            'bare listing, v directory, same' => ['1.0.1', 'v1.0.1', false],
            'v listing, bare directory, newer' => ['v1.0.1', '1.0.0', 'v1.0.1'],
            'v listing, bare directory, same' => ['v1.0.1', '1.0.1', false],
            'v on both sides, newer' => ['v2.1.2', 'v2.1.1', 'v2.1.2'],
            'v on both sides, older listing' => ['v2.1.1', 'v2.1.2', false],
            'uppercase V listing' => ['V1.0.1', 'v1.0.0', 'V1.0.1'],
            'two-digit component is not compared as text' => ['v1.0.10', 'v1.0.9', 'v1.0.10'],
            'listing typed as prose' => ['Version 1.1', 'v1.0.0', 'Version 1.1'],
            'listing with a word glued to the number' => ['Ver5.6.0', 'v5.5.0', 'Ver5.6.0'],
            'listing with a dash after the v' => ['V-1.1', 'v1.0', 'V-1.1'],
            'listing with a revision suffix' => ['1.0.1-r8256', 'v1.0.0', '1.0.1-r8256'],
            'listing with fewer parts, same version' => ['v1.0', 'v1.0.0', false],
            'letter-suffixed listing counts as the same version' => ['1.5.8a', 'v1.5.8', false],
            'listing with no digits is never newer' => ['Value', 'v1.0.0', false],
            'no installed version means anything is newer' => ['1.0.1', '', '1.0.1'],
            'newer, but not offered for this Zen Cart version' => ['1.0.1', 'v1.0.0', false, ['v1.5.8']],
            'unknown plugin id' => [null, 'v1.0.0', false],
        ];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider updateCheckProvider
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[DataProvider('updateCheckProvider')]
    public function testCheckForUpdatesAgainstVersionServerReplies(?string $listed, string $installed, false|string $expectedLatest, ?array $zcVersions = null): void
    {
        $this->assertFalse(
            class_exists('VersionServer', false),
            'The real VersionServer is already loaded; this test needs process isolation to stub it.'
        );

        $records = [];
        if ($listed !== null) {
            $records[] = [
                'id' => '2447',
                'latest_plugin_version' => $listed,
                'zcversions' => $zcVersions ?? ['v' . zen_get_zcversion()],
            ];
        }
        self::stubVersionServer(json_encode(json_encode($records)));

        $result = plugin_version_check_for_updates(2447, $installed);

        if ($expectedLatest === false) {
            $this->assertFalse($result);
            return;
        }
        $this->assertIsArray($result);
        $this->assertSame($expectedLatest, $result['latest_plugin_version']);
    }

    private static function stubVersionServer(string $cannedResponse): void
    {
        $stub = new class {
            public static string $response = '';

            public function getPluginVersion(mixed $ids): bool|string
            {
                return self::$response;
            }
        };
        $stub::$response = $cannedResponse;
        class_alias(get_class($stub), 'VersionServer');
    }
}
