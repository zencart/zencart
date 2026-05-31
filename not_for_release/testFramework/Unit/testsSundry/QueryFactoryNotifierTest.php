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
        global $zc_cache;
        $originalCache = $zc_cache ?? null;

        try {
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
                'affected_rows' => 0,
                'record_count' => 1,
            ], $db->notifications[0][1]);
        } finally {
            $zc_cache = $originalCache;
        }
    }

    public function testExecuteNotifiesOnFailure(): void
    {
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

            public function notifyFailure(string $sqlQuery, string $method, int $errorNumber, string $errorText, array $extra = []): void
            {
                $result = new \queryFactoryResult($this->link);
                $result->sql_query = $sqlQuery;
                $result->is_cached = false;
                $this->countFailure();
                $this->error_number = $errorNumber;
                $this->error_text = $errorText;
                $this->notifyQueryExecuted($result, $method, 0.0, false, $extra);
            }

            protected function countFailure(): void
            {
                $reflection = new \ReflectionClass(\queryFactory::class);
                $countQueries = $reflection->getProperty('count_queries');
                $countQueries->setAccessible(true);
                $countQueries->setValue($this, 1);
            }
        };
        $db->link = mysqli_init();

        $db->notifyFailure('SELECT broken FROM imaginary_table', 'Execute', 1234, 'forced execute failure');

        $this->assertCount(1, $db->notifications);
        $this->assertSame([
            'sql' => 'SELECT broken FROM imaginary_table',
            'method' => 'Execute',
            'success' => false,
            'query_time' => 0.0,
            'query_count' => 1,
            'total_query_time' => 0.0,
            'is_cached' => false,
            'error_number' => 1234,
            'error_text' => 'forced execute failure',
            'enable_caching' => false,
            'cache_seconds' => 0,
            'remove_from_query_cache' => false,
            'record_count' => 0,
            'affected_rows' => 0,
        ], $db->notifications[0][1]);
    }

    public function testExecuteRandomMultiNotifiesOnFailure(): void
    {
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

            public function notifyFailure(string $sqlQuery, string $method, int $errorNumber, string $errorText, array $extra = []): void
            {
                $result = new \queryFactoryResult($this->link);
                $result->sql_query = $sqlQuery;
                $result->is_cached = false;
                $this->countFailure();
                $this->error_number = $errorNumber;
                $this->error_text = $errorText;
                $this->notifyQueryExecuted($result, $method, 0.0, false, $extra);
            }

            protected function countFailure(): void
            {
                $reflection = new \ReflectionClass(\queryFactory::class);
                $countQueries = $reflection->getProperty('count_queries');
                $countQueries->setAccessible(true);
                $countQueries->setValue($this, 1);
            }
        };
        $db->link = mysqli_init();

        $db->notifyFailure('SELECT broken FROM imaginary_table', 'ExecuteRandomMulti', 4321, 'forced random failure', [
            'enable_caching' => false,
            'remove_from_query_cache' => true,
        ]);

        $this->assertCount(1, $db->notifications);
        $this->assertSame([
            'sql' => 'SELECT broken FROM imaginary_table',
            'method' => 'ExecuteRandomMulti',
            'success' => false,
            'query_time' => 0.0,
            'query_count' => 1,
            'total_query_time' => 0.0,
            'is_cached' => false,
            'error_number' => 4321,
            'error_text' => 'forced random failure',
            'enable_caching' => false,
            'cache_seconds' => 0,
            'remove_from_query_cache' => true,
            'record_count' => 0,
            'affected_rows' => 0,
        ], $db->notifications[0][1]);
    }
}
