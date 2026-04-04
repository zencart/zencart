<?php

namespace Seeders;

use Tests\Services\Contracts\TestSeederInterface;

class ValidTestSeeder implements TestSeederInterface
{
    public static array $receivedParameters = [];

    public function run(array $parameters = []): void
    {
        self::$receivedParameters = $parameters;
    }
}
