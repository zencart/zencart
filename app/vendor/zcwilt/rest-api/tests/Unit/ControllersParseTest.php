<?php

namespace Tests\Unit;

use Tests\Fixtures\Controllers\Api\ZcwiltUserController;
use Zcwilt\Api\ModelMakerFactory;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class ControllersParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    public function testControllerIndexBadParser()
    {
        $request = Request::create('/index', 'GET', [
            'title' => 'foo',
            'text' => 'bar',
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $this->assertTrue($response->getStatusCode() === 400);
        $this->assertTrue(json_decode($response->getContent())->error->message === "Can't find parser class Zcwilt\Api\Parsers\ParserTitle");
    }

    public function testControllerIndexNoParser()
    {
        $request = Request::create('/index', 'GET', [
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 15); //default pagination = 15
    }
    
    public function testControllerIndexWithWhereParser()
    {
        $request = Request::create('/index', 'GET', [
            'where' => 'id:eq:2'
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 1);
        $this->assertTrue($response->data[0]->id === 2);
    }
    public function testControllerIndexWithWhereInParser()
    {
        $request = Request::create('/index', 'GET', [
            'whereIn' => 'id:(1,2)'
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 2);
        $this->assertTrue($response->data[0]->id === 1);
    }
}
