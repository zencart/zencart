<?php

namespace Tests\Services\Contracts;

interface TestSeederInterface
{
    public function run(array $parameters = []): void;
}
