<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * An in-memory stand-in for the `template_select` table, exposed through a mocked queryFactory.
 *
 * It understands only the statements TemplateSelect issues. Like MySQL, affectedRows() reports
 * rows whose value actually changed. Every statement executed is recorded in $executedSql.
 */
trait InMemoryTemplateSelectDatabase
{
    /** @var array<int, array{template_id: string, template_dir: string, template_language: string, template_settings: ?string}> */
    private array $rows = [];
    private int $nextId = 1;
    private int $lastAffectedRows = 0;
    private int $lastInsertId = 0;

    /** @var string[] */
    private array $executedSql = [];

    private function makeMockDb(): \queryFactory
    {
        $db = $this->getMockBuilder(\queryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db->method('bindVars')->willReturnCallback(
            function (string $sql, string $token, $value, string $rule): string {
                if ($rule === 'integer') {
                    $replacement = (string)(int)$value;
                } elseif ($rule === 'passthru') {
                    $replacement = (string)$value;
                } elseif ($rule === 'string' && preg_match('/NULL/', (string)$value)) {
                    $replacement = 'NULL';
                } elseif ($rule === 'string' || $rule === 'stringIgnoreNull') {
                    $replacement = "'" . addslashes((string)$value) . "'";
                } elseif ($value === 'NULL') {
                    $replacement = 'NULL';
                } else {
                    $replacement = "'" . addslashes((string)$value) . "'";
                }
                return str_replace($token, $replacement, $sql);
            }
        );

        $db->method('insert_ID')->willReturnCallback(fn (): int => $this->lastInsertId);
        $db->method('affectedRows')->willReturnCallback(fn (): int => $this->lastAffectedRows);
        $db->method('Execute')->willReturnCallback(function (string $sql): \queryFactoryResult {
            $this->executedSql[] = $sql;
            return $this->handleQuery($sql);
        });

        return $db;
    }

    private function handleQuery(string $sql): \queryFactoryResult
    {
        if (stripos($sql, 'plugin_control') !== false) {
            return $this->makeQueryResult([]);
        }

        if (stripos($sql, 'DELETE FROM') !== false) {
            preg_match('/WHERE template_id = (\d+)/', $sql, $matches);
            $id = (int)$matches[1];
            $this->lastAffectedRows = isset($this->rows[$id]) ? 1 : 0;
            unset($this->rows[$id]);
            return $this->makeQueryResult([]);
        }

        if (stripos($sql, 'INSERT INTO') !== false) {
            // Support:
            // VALUES ('dir', 5)
            // VALUES ('dir', 5, NULL)
            // VALUES ('dir', 5, '{"A":"1"}')
            if (!preg_match(
                "/VALUES\s*\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*(-?\d+)(?:\s*,\s*(NULL|'(?:[^'\\\\]|\\\\.)*'))?\s*\)/is",
                $sql,
                $matches
            )) {
                $this->lastAffectedRows = 0;
                return $this->makeQueryResult([]);
            }

            $id = ++$this->nextId;
            $settingsToken = $matches[3] ?? null;
            $settingsValue = null;
            if ($settingsToken !== null && strtoupper($settingsToken) !== 'NULL') {
                $settingsValue = stripslashes(trim($settingsToken, "'"));
            }

            $this->rows[$id] = [
                'template_id' => (string)$id,
                'template_dir' => stripslashes($matches[1]),
                'template_language' => (string)(int)$matches[2],
                'template_settings' => $settingsValue,
            ];
            $this->lastInsertId = $id;
            $this->lastAffectedRows = 1;
            return $this->makeQueryResult([]);
        }

        if (stripos($sql, 'SET template_settings') !== false) {
            preg_match("/SET template_settings = (NULL|'.*?')\s+WHERE template_id = (\d+)/s", $sql, $matches);
            $id = (int)$matches[2];
            $newValue = ($matches[1] === 'NULL') ? null : trim($matches[1], "'");
            // Mirrors real MySQL: affectedRows() counts rows whose value actually
            // *changed*, not rows merely matched by the WHERE clause.
            // A no-op save (ie: identical value) reports 0 even though the row exists.
            $this->lastAffectedRows = 0;
            if (isset($this->rows[$id])) {
                if ($this->rows[$id]['template_settings'] !== $newValue) {
                    $this->lastAffectedRows = 1;
                }
                $this->rows[$id]['template_settings'] = $newValue;
            }
            return $this->makeQueryResult([]);
        }

        if (stripos($sql, 'SET template_dir') !== false) {
            preg_match("/SET template_dir = '([^']*)'\s+WHERE template_id = (\d+)/s", $sql, $matches);
            $id = (int)$matches[2];
            $newValue = $matches[1];
            // See note above: a no-op save (identical value) reports 0 too.
            $this->lastAffectedRows = 0;
            if (isset($this->rows[$id])) {
                if ($this->rows[$id]['template_dir'] !== $newValue) {
                    $this->lastAffectedRows = 1;
                }
                $this->rows[$id]['template_dir'] = $newValue;
            }
            return $this->makeQueryResult([]);
        }

        // The initial "SELECT * FROM template_select" issued by the constructor.
        return $this->makeQueryResult(array_values($this->rows));
    }

    private function makeQueryResult(array $rows): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->result = $rows;
        $result->is_cached = true;
        $result->cursor = 0;
        $result->fields = $rows[0] ?? [];
        $result->EOF = ($rows === []);

        return $result;
    }
}
