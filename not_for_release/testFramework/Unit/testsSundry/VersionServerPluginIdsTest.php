<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Coverage for VersionServer::normalizePluginIds(), the network-free part of
 * VersionServer::getPluginVersion(): the comma-separated id argument must never produce a
 * request for id 0 (a stray comma or blank segment), and the "[Batch]" / "[N]" marker in the
 * User-Agent must reflect how many ids are actually being sent.
 */

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\zcUnitTestCase;
use VersionServer;

class VersionServerPluginIdsTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'admin/includes/classes/VersionServer.php';
    }

    /**
     * normalizePluginIds() is protected; expose it through an anonymous subclass rather than
     * widening its visibility in production code.
     */
    private static function normalize(string $ids): array
    {
        $subject = new class extends VersionServer {
            public static function exposeNormalizePluginIds(string $ids): array
            {
                return self::normalizePluginIds($ids);
            }
        };

        return $subject::exposeNormalizePluginIds($ids);
    }

    public static function pluginIdInputProvider(): array
    {
        return [
            'single id' => ['170', [170], '[170]'],
            'multiple ids' => ['170,171', [170, 171], '[Batch]'],
            'trailing comma, single id' => ['170,', [170], '[170]'],
            'trailing comma, multiple ids' => ['170,171,', [170, 171], '[Batch]'],
            'leading comma' => [',170', [170], '[170]'],
            'whitespace around ids' => [' 170 , 171 ', [170, 171], '[Batch]'],
            'empty segment mid-list' => ['170,,171', [170, 171], '[Batch]'],
            'non-numeric segment among valid ids' => ['170,abc,171', [170, 171], '[Batch]'],
            'non-numeric segment alone' => ['abc', [], '[0]'],
            'explicit zero is dropped' => ['170,0', [170], '[170]'],
            'empty string' => ['', [], '[0]'],
            'lone comma' => [',', [], '[0]'],
            'only whitespace and commas' => [' , , ', [], '[0]'],
        ];
    }

    #[DataProvider('pluginIdInputProvider')]
    public function testNormalizePluginIds(string $input, array $expectedIds, string $expectedType): void
    {
        $this->assertSame(
            ['ids' => $expectedIds, 'type' => $expectedType],
            self::normalize($input)
        );
    }

    /**
     * getPluginVersion() must bail out before touching curl (or the DB-backed
     * getZcVersioninfo()) when nothing valid remains after normalization. If it did not, this
     * test would error on the undefined zen_get_system_information() long before any request
     * was made.
     */
    #[DataProvider('nothingToQueryProvider')]
    public function testGetPluginVersionReturnsFalseWhenNoValidIdRemains(mixed $input): void
    {
        $this->assertFalse((new VersionServer())->getPluginVersion($input));
    }

    public static function nothingToQueryProvider(): array
    {
        return [
            'lone comma' => [','],
            'non-numeric' => ['abc'],
            'zero as string' => ['0'],
            'zero and blanks' => ['0,,'],
            'integer zero' => [0],
            'null' => [null],
        ];
    }
}
