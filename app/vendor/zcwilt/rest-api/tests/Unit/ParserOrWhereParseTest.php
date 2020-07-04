<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Tests\Fixtures\Models\ZcwiltUser;

class ParserOrWhereParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    public function testOrWhereParserWithDummyData()
    {
        $testResult = ZcWiltUser::orWhere('id', '=', 2)->get()->toArray();
        Request::instance()->query->set('orWhere', 'id:eq:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === 1);

        $testResult = ZcWiltUser::orWhere('id', '!=', 2)->get()->toArray();
        Request::instance()->query->set('orWhere', 'id:noteq:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[1]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::orWhere('id', '<=', 2)->get()->toArray();
        Request::instance()->query->set('orWhere', 'id:lte:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[1]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::orWhere('id', '>=', 2)->get()->toArray();
        Request::instance()->query->set('orWhere', 'id:gte:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::orWhere('id', '>', 2)->get()->toArray();
        Request::instance()->query->set('orWhere', 'id:gt:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::orWhere('id', '<', 5)->get()->toArray();
        Request::instance()->query->set('orWhere', 'id:lt:5');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));
    }
}
