<?php

namespace Tests;

abstract class DatabaseTestCase extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }
}
