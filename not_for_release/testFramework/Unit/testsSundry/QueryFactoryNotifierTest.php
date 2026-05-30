<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class QueryFactoryNotifierTest extends zcUnitTestCase
{
    public function testExecuteNotifiesOnCacheHit(): void
    {
        global $zc_cache;

        $zc_cache = new class {
            public function sql_cache_exists(string $sqlQuery, int $cacheSeconds): bool
            {
                return true;
            }

            public function sql_cache_read(string $sqlQuery): array|false
            {
                return [['id' => 7, 'name' => 'cached row']];
            }
        };

        $db = new class extends \queryFactory {
            public array $notifications = [];

            public function notify(
                string $eventID,
                mixed $param1 = [],
                mixed &$param2 = null,
                mixed &$param3 = null,
                mixed &$param4 = null,
                mixed &$param5 = null,
                mixed &$param6 = null,
                mixed &$param7 = null,
                mixed &$param8 = null,
                mixed &$param9 = null
            ): void
            {
                $this->notifications[] = [$eventID, $param1];
            }
        };
        $db->link = mysqli_init();

        $result = $db->Execute('SELECT id, name FROM products', false, true, 60);

        $this->assertTrue($result->is_cached);
        $this->assertSame(['id' => 7, 'name' => 'cached row'], $result->fields);
        $this->assertCount(1, $db->notifications);
        $this->assertSame('NOTIFY_QUERY_FACTORY_EXECUTE_END', $db->notifications[0][0]);
        $this->assertSame([
            'sql' => 'SELECT id, name FROM products',
            'method' => 'Execute',
            'success' => true,
            'query_time' => 0.0,
            'query_count' => 0,
            'total_query_time' => 0.0,
            'is_cached' => true,
            'error_number' => 0,
            'error_text' => '',
            'enable_caching' => true,
            'cache_seconds' => 60,
            'remove_from_query_cache' => false,
            'record_count' => 1,
            'affected_rows' => 0,
        ], $db->notifications[0][1]);
    }
}
