<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Replaces the global $db with a mocked queryFactory that answers Execute() calls from a script.
 *
 * Register responses with respondTo(); the first registered pattern that matches the SQL wins, and
 * a statement with no matching pattern returns an empty result. Every statement is recorded, with
 * the extra Execute() arguments (limit, cache flag, cache lifetime), in $executedQueries.
 */
trait ScriptedDatabase
{
    /** @var array<int, array{pattern: string, rows: array}> */
    private array $queryResponses = [];

    /** @var array<int, array{sql: string, limit: mixed, cache: mixed, ttl: mixed}> */
    private array $executedQueries = [];

    private function useScriptedDatabase(): void
    {
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';

        $db = $this->getMockBuilder(\queryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('Execute')->willReturnCallback(
            function (string $sql, $limit = null, $cache = false, $ttl = 0): \queryFactoryResult {
                $this->executedQueries[] = ['sql' => $sql, 'limit' => $limit, 'cache' => $cache, 'ttl' => $ttl];
                foreach ($this->queryResponses as $response) {
                    if (preg_match($response['pattern'], $sql) === 1) {
                        return $this->makeScriptedResult($response['rows']);
                    }
                }
                return $this->makeScriptedResult([]);
            }
        );
        $GLOBALS['db'] = $db;
    }

    private function respondTo(string $pattern, array $rows): void
    {
        $this->queryResponses[] = ['pattern' => $pattern, 'rows' => $rows];
    }

    /**
     * @return string[]
     */
    private function sqlMatching(string $pattern): array
    {
        $matches = array_filter(
            $this->executedQueries,
            static fn(array $query): bool => preg_match($pattern, $query['sql']) === 1
        );

        return array_values(array_map(static fn(array $query): string => $query['sql'], $matches));
    }

    private function makeScriptedResult(array $rows): \queryFactoryResult
    {
        $rows = array_values($rows);
        $result = new \queryFactoryResult(null);
        $result->result = $rows;
        $result->is_cached = true;
        $result->cursor = 0;
        $result->fields = $rows[0] ?? [];
        $result->EOF = ($rows === []);

        return $result;
    }
}
