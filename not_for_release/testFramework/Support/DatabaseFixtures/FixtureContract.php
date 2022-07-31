<?php

namespace Tests\Support\DatabaseFixtures;

interface FixtureContract
{
    public function createTable($connection);
    public function seeder();
}
