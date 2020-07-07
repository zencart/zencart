<?php

namespace Tests\Unit;

use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Zcwilt\Api\ParserFactory;
use Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Tests\Fixtures\Models\ZcwiltUser;

class ParserSortParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    public function testSortParserParseTestNoParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('sort');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('');
    }

    public function testSortParserParseTestWithParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('sort');
        $parser->parse('z,-y');
        $tokenized = $parser->getTokenized();
        $this->assertTrue($tokenized[0]['field'] === 'z');
        $this->assertTrue($tokenized[0]['direction'] === 'ASC');
        $this->assertTrue($tokenized[1]['field'] === 'y');
        $this->assertTrue($tokenized[1]['direction'] === 'DESC');
    }

    public function testSortParserWithDummyData()
    {
        $testResult = ZcWiltUser::orderBy('age', 'DESC')->get()->toArray();
        Request::instance()->query->set('sort', '-age');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);

        $testResult = ZcWiltUser::orderBy('name', 'ASC')->get()->toArray();
        Request::instance()->query->set('sort', 'name');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['name'] === $testResult[0]['name']);
        $this->assertTrue($result[2]['age'] === $testResult[2]['age']);
    }
}
