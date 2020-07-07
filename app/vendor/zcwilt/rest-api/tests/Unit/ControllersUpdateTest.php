<?php

namespace Tests\Unit;

use Tests\Fixtures\Controllers\Api\ZcwiltUserController;
use Zcwilt\Api\ModelMakerFactory;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class ControllersUpdateTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }


    public function testControllerUpdate()
    {
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $request = Request::create('/index', 'PUT', [
        ]);
        $response = $controller->update(1001, $request);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->error->message === 'item does not exist');
        $response = $controller->update(1, $request);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->error->status_code === 400);
        $message = $response->error->message->email[0];
        $this->assertContains('The email field is required.', $message);
        $request = Request::create('/index', 'POST', [
            'email' => 'name1@gmail.com',
            'name' => 'Dirk Gently'
        ]);
        $response = $controller->update(1, $request);
        $response = json_decode($response->getContent());
        $message = $response->error->message->email[0];
        $this->assertContains('The email has already been taken.', $message);
        $request = Request::create('/index', 'POST', [
            'email' => 'dirk@holisticdetective.com',
            'name' => 'Dirk Gently'
        ]);

        $response = $controller->update(1, $request);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->data->id === 1);
        $this->assertTrue($response->data->name === 'Dirk Gently');
    }

    public function testUpdateByQuery()
    {
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $request = Request::create('/UpdateByQuery', 'PUT', [
            'where' => 'id:eq:2', 'fields' => ['name' => 'foobar']
        ]);
        $response = $controller->updateByQuery($request);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->data === 'affected rows = 1');
        $request = Request::create('/index', 'GET', [
            'where' => 'id:eq:2'
        ]);
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent());
        $this->assertTrue(count($response->data) === 1);
        $this->assertTrue($response->data[0]->name === 'foobar');

        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $request = Request::create('/UpdateByQuery', 'PUT', [
            'where' => 'id:eq:2', 'fields' => ['nam' => 'foobar']
        ]);
        $response = $controller->updateByQuery($request);
        $response = json_decode($response->getContent());
        $this->assertContains('SQLSTATE', $response->error->message);
    }
}
