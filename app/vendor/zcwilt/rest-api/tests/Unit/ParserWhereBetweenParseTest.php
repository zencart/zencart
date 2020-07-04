<?php

namespace Tests\Unit;

use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Zcwilt\Api\ParserFactory;
use Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Tests\Fixtures\Models\ZcwiltUser;

class ParserWhereBetweenParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }
    public function testWhereBetweenParserParseTestNoParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('whereBetween');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('');
    }
    public function testWhereBetweenParserParseTestWithParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('whereBetween');
        $parser->parse('id:4:20');
        $tokenized = $parser->getTokenized();
        $this->assertTrue($tokenized[0] === 'id');
        $this->assertTrue($tokenized[1] === '4');
        $this->assertTrue($tokenized[2] === '20');
    }
    public function testWhereBetweenParserParseTestInvalidParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('whereBetween');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('id');
    }

    public function testWhereBetweenParserWithDummyData()
    {
        $testResult = ZcWiltUser::whereBetween('age', [10,45])->get()->toArray();
        Request::instance()->query->set('whereBetween', 'age:10:45');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[1]['age']);
    }

    public function testWhereNotBetweenParserWithDummyData()
    {
        $testResult = ZcWiltUser::whereNotBetween('age', [10,45])->get()->toArray();
        Request::instance()->query->set('whereNotBetween', 'age:10:45');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['id'] === $testResult[0]['id']);
        $this->assertTrue(count($result) === count($testResult));
    }

    public function testOrWhereBetweenParserWithDummyData()
    {
        $testResult = ZcWiltUser::orWhereBetween('age', [10,45])->get()->toArray();
        Request::instance()->query->set('orWhereBetween', 'age:10:45');
        $result  = $this->getRequestResults();
//        dump($testResult[0]);
//        dump($result);
        $this->assertTrue(count($result) === count($testResult));
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[1]['age']);
    }
    public function testOrWhereNotBetweenParserWithDummyData()
    {
        $testResult = ZcWiltUser::orWhereNotBetween('age', [10,45])->get()->toArray();
        Request::instance()->query->set('orWhereNotBetween', 'age:10:45');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
    }
}
