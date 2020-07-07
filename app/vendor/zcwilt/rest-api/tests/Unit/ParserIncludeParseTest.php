<?php

namespace Tests\Unit;

use Zcwilt\Api\ApiQueryParser;
use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Zcwilt\Api\ParserFactory;
use Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Tests\Fixtures\Controllers\Api\ZcwiltUserController;
use Zcwilt\Api\ModelMakerFactory;
use Tests\Fixtures\Models\ZcwiltUser;

class ParserIncludeParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    public function testIncludesParserParseTestNoParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('includes');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('');
    }

    public function testIncludesParserParseTestWithParams()
    {
        $api = new ApiQueryParser(new ParserFactory());
        Request::instance()->query->set('includes', 'foo,bar');
        $api->parseRequest(Request::instance());
        $api->buildParsers();
        $tokenized = $api->getQueryParts()[0]->getTokenized()[0];
        $this->assertTrue($tokenized['field'] === 'foo');
        $tokenized = $api->getQueryParts()[0]->getTokenized()[1];
        $this->assertTrue($tokenized['field'] === 'bar');
    }

    public function testIncludesParserWithDummyData()
    {
        $testResult = ZcWiltUser::with('posts')->get()->toArray();
        Request::instance()->query->set('includes', 'posts');
        $result  = $this->getRequestResults();
        $this->assertTrue(count($result) === count($testResult));
        $this->assertTrue(count($result[0]['posts']) === count($testResult[0]['posts']));
    }

    public function testIncludesParserWithDummyDataInvalidWith()
    {
        Request::instance()->query->set('includes', 'foos');
        $this->expectException(RelationNotFoundException::class);
        $this->getRequestResults();
    }

    public function testControllerIndexWithIncludesParserPass()
    {
        $testResult = ZcWiltUser::with('posts')->where('id', '=', 2)->get()->toArray();
        $request = Request::create('/index', 'GET', [
            'where' => 'id:eq:2', 'includes' => 'posts'
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 1);
        $this->assertTrue(count($response->data[0]->posts) === count($testResult[0]['posts']));
    }

    public function testControllerIndexWithIncludesParserFail()
    {
        $request = Request::create('/index', 'GET', [
            'where' => 'id:eq:1', 'includes' => 'foo'
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $message = $response->error->message;
        $this->assertContains('Call to undefined relationship', $message);
    }
}
