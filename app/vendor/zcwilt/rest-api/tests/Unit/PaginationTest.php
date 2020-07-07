<?php

namespace Tests\Unit;

use Tests\Fixtures\Controllers\Api\ZcwiltUserController;
use Zcwilt\Api\ModelMakerFactory;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    public function testPerPageQuery()
    {
        $request = Request::create('/index', 'GET', [
            'per_page' => '5'
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 5);
        $this->assertTrue($response->to === 5);
    }
    public function testPerPageQueryNotSet()
    {
        $request = Request::create('/index', 'GET', [
            'per_page' => ''
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 15);
        $this->assertTrue($response->to === 15);
    }
    public function testNoPagination()
    {
        $request = Request::create('/index', 'GET', [
            'paginate' => 'no'
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) > 15);
        $this->assertTrue($response->to > 15);
    }
}
