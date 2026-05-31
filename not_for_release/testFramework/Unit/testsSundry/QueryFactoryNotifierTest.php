<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class QueryFactoryNotifierTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
    }

    public function testExecuteNotifiesOnCacheHit(): void
    {
        $hadCache = array_key_exists('zc_cache', $GLOBALS);
        $originalCache = $hadCache ? $GLOBALS['zc_cache'] : null;

        try {
            $GLOBALS['zc_cache'] = new class {
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
                'affected_rows' => 0,
                'record_count' => 1,
            ], $db->notifications[0][1]);
        } finally {
            if ($hadCache) {
                $GLOBALS['zc_cache'] = $originalCache;
            } else {
                unset($GLOBALS['zc_cache']);
            }
        }
    }

    public function testNotifyQueryExecutedDerivesSuccessPayloadFields(): void
    {
        $db = new class extends \queryFactory {
            public array $notifications = [];

            public function affectedRows(): int
            {
                return 9;
            }

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

            public function probeNotification(string $sqlQuery, bool $success, bool|\mysqli_result $resource): array
            {
                $result = new \queryFactoryResult($this->link);
                $result->sql_query = $sqlQuery;
                $result->resource = $resource;
                $this->notifyQueryExecuted($result, 'Execute', 0.0, $success);

                return $this->notifications[array_key_last($this->notifications)][1];
            }

            public function probeRandomMultiNotification(array $rows): array
            {
                $result = new \queryFactoryResult($this->link);
                $result->sql_query = 'SELECT products_id FROM products';
                $result->result = $rows;
                $this->notifyQueryExecuted($result, 'ExecuteRandomMulti', 0.0, true, ['affected_rows' => 0]);

                return $this->notifications[array_key_last($this->notifications)][1];
            }
        };
        $db->link = mysqli_init();

        $writePayload = $db->probeNotification('/* observer-safe comment */ UPDATE products SET products_status = 1', true, true);
        $readPayload = $db->probeNotification('SELECT products_id FROM products', true, false);
        $failurePayload = $db->probeNotification('DELETE FROM products WHERE products_id = 1', false, false);
        $randomMultiPayload = $db->probeRandomMultiNotification([
            ['products_id' => 1],
            ['products_id' => 2],
        ]);

        $this->assertSame(9, $writePayload['affected_rows']);
        $this->assertSame(0, $readPayload['affected_rows']);
        $this->assertSame(0, $failurePayload['affected_rows']);
        $this->assertSame(2, $randomMultiPayload['record_count']);
    }
}
