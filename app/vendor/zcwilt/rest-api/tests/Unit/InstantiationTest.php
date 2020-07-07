<?php
namespace Tests\Unit;

use Zcwilt\Api\ApiQueryParser;
use Zcwilt\Api\ParserFactory;
use Tests\TestCase;

class InstantiationTest extends TestCase
{
    public function testSimple()
    {
        $api = new ApiQueryParser(new ParserFactory());
        $queryParts = $api->getQueryParts();
        $this->assertTrue(is_array($queryParts));
        $this->assertTrue(count($queryParts) === 0);
        $parserFactory = $api->getParserFactory();
        $this->assertTrue($parserFactory instanceof ParserFactory);
    }
}
