<?php

namespace Migrations;

use Tests\Services\Contracts\TestMigrationInterface;

class CreateValidMigrationTable implements TestMigrationInterface
{
    public static array $calls = [];

    public function up(): void
    {
        self::$calls[] = 'up';
    }

    public function down(): void
    {
        self::$calls[] = 'down';
    }
}
