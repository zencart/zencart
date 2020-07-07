<?php
namespace Tests\Unit;

use Zcwilt\Api\ApiQueryParser;
use Zcwilt\Api\ParserFactory;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class ParseRequestTest extends TestCase
{
    public function testSimple()
    {
        $api = new ApiQueryParser(new ParserFactory());
        Request::instance()->query->set('page', 1);
        Request::instance()->query->set('per_page', 1);
        Request::instance()->query->set('where', 'foo');
        Request::instance()->query->set('sort', 'bar');
        $api->parseRequest(Request::instance());
        $this->assertTrue($api instanceof ApiQueryParser);
        $parsedKeys = $api->getParsedKeys();
        $this->assertTrue(count($parsedKeys) === 2);
        $this->assertTrue($parsedKeys['where'] === 'foo');
        $this->assertTrue($parsedKeys['sort'] === 'bar');
    }
}
