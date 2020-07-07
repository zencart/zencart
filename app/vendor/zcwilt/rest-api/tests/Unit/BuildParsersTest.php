<?php
namespace Tests\Unit;

use Zcwilt\Api\ApiQueryParser;
use Zcwilt\Api\ParserFactory;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;
use Zcwilt\Api\Exceptions\UnknownParserException;

class BuildParsersTest extends TestCase
{
    public function testFailInvalidParser()
    {
        $api = new ApiQueryParser(new ParserFactory());
        Request::instance()->query->set('bar', 'foo');
        $api->parseRequest(Request::instance());
        $this->expectException(UnknownParserException::class);
        $api->buildParsers();
    }

    public function testPassParser()
    {
        $api = new ApiQueryParser(new ParserFactory());
        Request::instance()->query->set('sort', 'foo');
        $api->parseRequest(Request::instance());
        $api->buildParsers();
        $tokenized = $api->getQueryParts()[0]->getTokenized()[0];
        $this->assertTrue($tokenized['field'] === 'foo');
        $this->assertTrue($tokenized['direction'] === 'ASC');
    }
    public function testParserWithArrays()
    {
        $api = new ApiQueryParser(new ParserFactory());
        $request = Request::create('/index', 'GET', [
            'where' => ['id:eq:2', 'id:eq:3']
        ]);
        $api->parseRequest($request);
        $api->buildParsers();
        $tokenized = $api->getQueryParts();
        $this->assertTrue($tokenized[0]->getTokenized()[2] === '2');
        $this->assertTrue($tokenized[1]->getTokenized()[2] === '3');
    }
}
