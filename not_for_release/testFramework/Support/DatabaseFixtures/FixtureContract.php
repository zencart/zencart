<?php

namespace Tests\Support\DatabaseFixtures;

interface FixtureContract
{
    public function createTable();
    public function seeder();
}
