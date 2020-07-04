<?php

namespace Tests\Unit;

use Tests\Fixtures\Models\ZcwiltUser;
use Zcwilt\Api\ApiQueryParser;
use Zcwilt\Api\ParserFactory;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class BuildQueryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    public function testBasic()
    {
        $testResult = ZcWiltUser::all();
        $api = new ApiQueryParser(new ParserFactory());
        $api->parseRequest(Request::instance());
        $api->buildParsers();
        $query = $api->buildQuery(new ZcwiltUser);
        $result = $query->get()->toArray();
        $this->assertTrue(count($result) === count($testResult));
    }
}
