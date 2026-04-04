<?php

namespace Tests\Support\InProcess;

use PDO;
use RuntimeException;
use Tests\Support\Database\TestDb;

class InProcessDatabaseSnapshot
{
    private const META_TABLE = '__zc_test_snapshot_meta';
    private const SNAPSHOT_PREFIX = '__zc_test_snapshot__';

    public function restoreOrCreate(callable $bootstrap): void
    {
        if ($this->hasValidSnapshot()) {
            $this->restoreSnapshot();

            return;
        }

        $this->dropWorkingTables();
        $this->dropSnapshotTables();
        $bootstrap();
        $this->createSnapshot();
    }

    protected function createSnapshot(): void
    {
        $tables = $this->workingTables();
        if ($tables === []) {
            throw new RuntimeException('Cannot create a database snapshot without working tables.');
        }

        $pdo = $this->pdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                $snapshotTable = $this->snapshotTableName($table);
                $quotedSnapshot = $this->quoteIdentifier($snapshotTable);
                $quotedTable = $this->quoteIdentifier($table);

                $pdo->exec('DROP TABLE IF EXISTS ' . $quotedSnapshot);
                $pdo->exec(sprintf('CREATE TABLE %s LIKE %s', $quotedSnapshot, $quotedTable));
                $pdo->exec(sprintf('INSERT INTO %s SELECT * FROM %s', $quotedSnapshot, $quotedTable));
            }

            $this->storeFingerprint();
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    protected function restoreSnapshot(): void
    {
        $snapshotTables = $this->snapshotTables();
        if ($snapshotTables === []) {
            throw new RuntimeException('Snapshot restore was requested, but no snapshot tables were found.');
        }

        $this->dropWorkingTables();

        $pdo = $this->pdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($snapshotTables as $snapshotTable) {
                $workingTable = $this->workingTableName($snapshotTable);
                $quotedSnapshot = $this->quoteIdentifier($snapshotTable);
                $quotedWorking = $this->quoteIdentifier($workingTable);

                $pdo->exec('DROP TABLE IF EXISTS ' . $quotedWorking);
                $pdo->exec(sprintf('CREATE TABLE %s LIKE %s', $quotedWorking, $quotedSnapshot));
                $pdo->exec(sprintf('INSERT INTO %s SELECT * FROM %s', $quotedWorking, $quotedSnapshot));
            }
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    protected function hasValidSnapshot(): bool
    {
        if (!$this->tableExists(self::META_TABLE)) {
            return false;
        }

        if ($this->snapshotTables() === []) {
            return false;
        }

        $storedFingerprint = $this->selectValue(
            'SELECT fingerprint FROM ' . $this->quoteIdentifier(self::META_TABLE) . ' WHERE snapshot_key = :snapshot_key',
            [':snapshot_key' => 'baseline']
        );

        return is_string($storedFingerprint) && hash_equals($this->fingerprint(), $storedFingerprint);
    }

    protected function storeFingerprint(): void
    {
        $pdo = $this->pdo();
        $quotedMeta = $this->quoteIdentifier(self::META_TABLE);
        $pdo->exec(sprintf('CREATE TABLE %s (snapshot_key VARCHAR(64) PRIMARY KEY, fingerprint VARCHAR(64) NOT NULL)', $quotedMeta));

        $statement = $pdo->prepare(
            'INSERT INTO ' . $quotedMeta . ' (snapshot_key, fingerprint) VALUES (:snapshot_key, :fingerprint)'
        );
        $statement->execute([
            ':snapshot_key' => 'baseline',
            ':fingerprint' => $this->fingerprint(),
        ]);
    }

    protected function dropWorkingTables(): void
    {
        $this->dropTables($this->workingTables());
    }

    protected function dropSnapshotTables(): void
    {
        $tables = $this->snapshotTables();
        if ($this->tableExists(self::META_TABLE)) {
            $tables[] = self::META_TABLE;
        }

        $this->dropTables($tables);
    }

    protected function dropTables(array $tables): void
    {
        if ($tables === []) {
            return;
        }

        $pdo = $this->pdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                $pdo->exec('DROP TABLE IF EXISTS ' . $this->quoteIdentifier($table));
            }
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    protected function workingTables(): array
    {
        return array_values(array_filter(
            $this->allTables(),
            fn (string $table): bool => !$this->isSnapshotTable($table) && $table !== self::META_TABLE
        ));
    }

    protected function snapshotTables(): array
    {
        return array_values(array_filter(
            $this->allTables(),
            fn (string $table): bool => $this->isSnapshotTable($table)
        ));
    }

    protected function allTables(): array
    {
        $pdo = $this->pdo();
        $statement = $pdo->query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'');
        $rows = $statement->fetchAll(PDO::FETCH_NUM);

        return array_map(static fn (array $row): string => (string) $row[0], $rows);
    }

    protected function tableExists(string $table): bool
    {
        return in_array($table, $this->allTables(), true);
    }

    protected function isSnapshotTable(string $table): bool
    {
        return str_starts_with($table, self::SNAPSHOT_PREFIX);
    }

    protected function snapshotTableName(string $table): string
    {
        return self::SNAPSHOT_PREFIX . $table;
    }

    protected function workingTableName(string $snapshotTable): string
    {
        return substr($snapshotTable, strlen(self::SNAPSHOT_PREFIX));
    }

    protected function fingerprint(): string
    {
        $files = [
            ROOTCWD . 'zc_install/sql/install/mysql_zencart.sql',
            ROOTCWD . 'zc_install/sql/install/mysql_utf8.sql',
            ROOTCWD . 'zc_install/sql/demo/mysql_demo.sql',
            TESTCWD . 'Support/database/Seeders/InitialSetupSeeder.php',
        ];

        $hashSource = [];
        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new RuntimeException(sprintf('Snapshot fingerprint file not found: %s', $file));
            }

            $hashSource[] = $file . ':' . md5_file($file);
        }

        return hash('sha256', implode('|', $hashSource));
    }

    protected function pdo(): PDO
    {
        return TestDb::pdo();
    }

    protected function selectValue(string $sql, array $params = []): mixed
    {
        return TestDb::selectValue($sql, $params);
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
