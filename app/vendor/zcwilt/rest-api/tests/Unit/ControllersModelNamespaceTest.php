<?php

namespace Tests\Unit;

use Tests\Fixtures\Controllers\Api\ZcwiltDummyController;
use Tests\Fixtures\Controllers\Api\ZcwiltDummy1Controller;
use Tests\Fixtures\Controllers\Api\ZcwiltDummy2Controller;
use Zcwilt\Api\ModelMakerFactory;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;
use Zcwilt\Api\Exceptions\InvalidModelException;

class ControllersModelNamespaceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    public function testControllerAppNamespace()
    {
        $request = Request::create('/index', 'GET', [
            'where' => 'id:eq:2'
        ]);
        $controller = new ZcwiltDummyController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 0);
    }

    public function testControllerAppModelNamespace()
    {
        $request = Request::create('/index', 'GET', [
            'where' => 'id:eq:2'
        ]);
        $controller = new ZcwiltDummy1Controller(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 0);
    }

    public function testControllerInvalidModel()
    {
        $this->expectException(InvalidModelException::class);
        new ZcwiltDummy2Controller(new ModelMakerFactory());
    }
}
