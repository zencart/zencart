<?php

namespace Tests\Services\Contracts;

interface TestMigrationInterface
{
    public function up(): void;

    public function down(): void;
}
