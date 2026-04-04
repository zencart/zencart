<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\InProcess\InProcessDatabaseSnapshot;

class InProcessDatabaseSnapshotTest extends TestCase
{
    public function testRestoreOrCreateRestoresExistingSnapshotWithoutRebootstrapping(): void
    {
        $snapshot = new class extends InProcessDatabaseSnapshot {
            public array $calls = [];

            protected function hasValidSnapshot(): bool
            {
                $this->calls[] = 'hasValidSnapshot';

                return true;
            }

            protected function restoreSnapshot(): void
            {
                $this->calls[] = 'restoreSnapshot';
            }

            protected function dropWorkingTables(): void
            {
                $this->calls[] = 'dropWorkingTables';
            }

            protected function dropSnapshotTables(): void
            {
                $this->calls[] = 'dropSnapshotTables';
            }

            protected function createSnapshot(): void
            {
                $this->calls[] = 'createSnapshot';
            }
        };

        $bootstrapped = false;
        $snapshot->restoreOrCreate(static function () use (&$bootstrapped): void {
            $bootstrapped = true;
        });

        $this->assertFalse($bootstrapped);
        $this->assertSame(['hasValidSnapshot', 'restoreSnapshot'], $snapshot->calls);
    }

    public function testRestoreOrCreateBuildsFreshSnapshotWhenMissing(): void
    {
        $snapshot = new class extends InProcessDatabaseSnapshot {
            public array $calls = [];

            protected function hasValidSnapshot(): bool
            {
                $this->calls[] = 'hasValidSnapshot';

                return false;
            }

            protected function restoreSnapshot(): void
            {
                $this->calls[] = 'restoreSnapshot';
            }

            protected function dropWorkingTables(): void
            {
                $this->calls[] = 'dropWorkingTables';
            }

            protected function dropSnapshotTables(): void
            {
                $this->calls[] = 'dropSnapshotTables';
            }

            protected function createSnapshot(): void
            {
                $this->calls[] = 'createSnapshot';
            }
        };

        $bootstrapped = false;
        $snapshot->restoreOrCreate(static function () use (&$bootstrapped): void {
            $bootstrapped = true;
        });

        $this->assertTrue($bootstrapped);
        $this->assertSame(
            ['hasValidSnapshot', 'dropWorkingTables', 'dropSnapshotTables', 'createSnapshot'],
            $snapshot->calls
        );
    }

    public function testHasValidSnapshotReturnsFalseWhenMetaTableIsMissing(): void
    {
        $snapshot = new class extends InProcessDatabaseSnapshot {
            public function hasSnapshot(): bool
            {
                return $this->hasValidSnapshot();
            }

            protected function tableExists(string $table): bool
            {
                return false;
            }
        };

        $this->assertFalse($snapshot->hasSnapshot());
    }

    public function testHasValidSnapshotReturnsFalseWhenFingerprintDoesNotMatch(): void
    {
        $snapshot = new class extends InProcessDatabaseSnapshot {
            public function hasSnapshot(): bool
            {
                return $this->hasValidSnapshot();
            }

            protected function tableExists(string $table): bool
            {
                return true;
            }

            protected function snapshotTables(): array
            {
                return ['__zc_test_snapshot__customers'];
            }

            protected function selectValue(string $sql, array $params = []): mixed
            {
                return 'stored-fingerprint';
            }

            protected function fingerprint(): string
            {
                return 'current-fingerprint';
            }
        };

        $this->assertFalse($snapshot->hasSnapshot());
    }

    public function testHasValidSnapshotReturnsTrueWhenFingerprintMatches(): void
    {
        $snapshot = new class extends InProcessDatabaseSnapshot {
            public function hasSnapshot(): bool
            {
                return $this->hasValidSnapshot();
            }

            protected function tableExists(string $table): bool
            {
                return true;
            }

            protected function snapshotTables(): array
            {
                return ['__zc_test_snapshot__customers'];
            }

            protected function selectValue(string $sql, array $params = []): mixed
            {
                return 'matching-fingerprint';
            }

            protected function fingerprint(): string
            {
                return 'matching-fingerprint';
            }
        };

        $this->assertTrue($snapshot->hasSnapshot());
    }
}
