<?php

namespace Tests\Unit;

use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Zcwilt\Api\ParserFactory;
use Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Tests\Fixtures\Models\ZcwiltUser;

class ParserWhereInParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }
    public function testWhereInParserParseTestNoParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('whereIn');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('');
    }
    public function testWhereInParserParseTestWithParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('whereIn');
        $parser->parse('id:(1,2)');
        $tokenized = $parser->getTokenized();
        $this->assertTrue($tokenized['col'] === 'id');
        $this->assertTrue(is_array($tokenized['in']));
        $this->assertTrue($tokenized['in'][0] === '1');
        $this->assertTrue($tokenized['in'][1] === '2');
    }

    public function testWhereInParserParseTestInvalidParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('whereIn');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('id');
    }
    public function testWhereInParserParseTestInvalidTokenized()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('whereIn');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('id:(,)');
    }

    public function testWhereInParserWithDummyData()
    {
        $testResult = ZcWiltUser::whereIn('id', [1,2])->get()->toArray();
        Request::instance()->query->set('whereIn', 'id:(1,2)');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[1]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::whereIn('id', [1,2])->orderBY('id', 'ASC')->get()->toArray();
        Request::instance()->query->set('whereIn', 'id:(1,2)');
        Request::instance()->query->set('sort', '-id');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[1]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));
    }

    public function testWhereNotInParserWithDummyData()
    {
        $testResult = ZcWiltUser::whereNotIn('id', [1,2])->get()->toArray();
        Request::instance()->query->set('whereNotIn', 'id:(1,2)');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['id'] === $testResult[0]['id']);
        $this->assertTrue(count($result) === count($testResult));
    }

    public function testOrWhereInParserWithDummyData()
    {
        $testResult = ZcWiltUser::orWhereIn('id', [1,2])->get()->toArray();
        Request::instance()->query->set('orWhereIn', 'id:(1,2)');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[1]['age']);
        $this->assertTrue(count($result) === count($testResult));
    }
    public function testOrWhereNotInParserWithDummyData()
    {
        $testResult = ZcWiltUser::orWhereNotIn('id', [1,2])->get()->toArray();
        Request::instance()->query->set('orWhereNotIn', 'id:(1,2)');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['id'] === $testResult[0]['id']);
        $this->assertTrue(count($result) === count($testResult));
    }
}
