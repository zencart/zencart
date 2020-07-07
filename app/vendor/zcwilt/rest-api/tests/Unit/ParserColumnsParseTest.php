<?php

namespace Tests\Unit;

use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Zcwilt\Api\ParserFactory;
use Tests\TestCase;
use Illuminate\Support\Facades\Request;

class ParserColumnsParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }
    public function testColumnsParserParseTestNoParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('columns');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('');
    }
    public function testColumnsParserParseTestMissingParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('columns');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('  , ');
    }

    public function testColumnsParserParseTestWithParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('columns');
        $parser->parse(',id,name');
        $tokenized = $parser->getTokenized();
        $this->assertTrue($tokenized[0]['field'] === 'id');
        $this->assertTrue($tokenized[1]['field'] === 'name');
    }
    public function testColumnsParserWithDummyData()
    {
        Request::instance()->query->set('columns', 'id, name ,');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result[0]) === 2);
        Request::instance()->query->set('columns', 'id,name,email');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result[0]) === 3);
    }
}
