<?php

namespace Tests\Unit;

use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Zcwilt\Api\Exceptions\ParserInvalidParameterException;
use Zcwilt\Api\ParserFactory;
use Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Tests\Fixtures\Models\ZcwiltUser;

class ParserWhereParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }
    public function testWhereParserParseTestNoParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('where');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('');
    }
    public function testWhereParserParseTestWithParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('where');
        $parser->parse('id:eq:1');
        $tokenized = $parser->getTokenized();
        $this->assertTrue($tokenized[0] === 'id');
        $this->assertTrue($tokenized[1] === 'eq');
        $this->assertTrue($tokenized[2] === '1');
    }

    public function testWhereParserParseTestInvalidParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('where');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('id:eq');
    }
    public function testWhereParserParseTestInvalidOperator()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('where');
        $this->expectException(ParserInvalidParameterException::class);
        $parser->parse('id:foo:1');
    }

    public function testWhereParserWithDummyData()
    {
        $testResult = ZcWiltUser::where('id', '=', 2)->get()->toArray();
        Request::instance()->query->set('where', 'id:eq:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('id', '!=', 2)->get()->toArray();
        Request::instance()->query->set('where', 'id:noteq:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[1]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('id', '<=', 2)->get()->toArray();
        Request::instance()->query->set('where', 'id:lte:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue($result[1]['age'] === $testResult[1]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('id', '>=', 2)->get()->toArray();
        Request::instance()->query->set('where', 'id:gte:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('id', '>', 2)->get()->toArray();
        Request::instance()->query->set('where', 'id:gt:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('id', '<', 2)->get()->toArray();
        Request::instance()->query->set('where', 'id:lt:2');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('name', 'LIKE', 'name%')->get()->toArray();
        Request::instance()->query->set('where', 'name:lk:name%');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('name', 'LIKE', '%nam%')->get()->toArray();
        Request::instance()->query->set('where', 'name:lk:%nam%');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('email', 'LIKE', '%com')->get()->toArray();
        Request::instance()->query->set('where', 'email:lk:%com');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('name', 'NOT LIKE', 'name%')->get()->toArray();
        Request::instance()->query->set('where', 'name:nlk:name%');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('name', 'NOT LIKE', '%nam%')->get()->toArray();
        Request::instance()->query->set('where', 'name:nlk:%nam%');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::where('email', 'NOT LIKE', '%com')->get()->toArray();
        Request::instance()->query->set('where', 'email:nlk:%com');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));
    }
}
