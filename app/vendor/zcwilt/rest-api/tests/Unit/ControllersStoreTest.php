<?php

namespace Tests\Unit;

use Tests\Fixtures\Controllers\Api\ZcwiltUserController;
use Zcwilt\Api\ModelMakerFactory;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class ControllersStoreTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    public function testControllerStoreFails()
    {
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $request = Request::create('/index', 'POST', [
            'email' => 'dirk@holisticdetective.com',
            'name' => 'Dirk Gently'
        ]);
        $response = $controller->store($request);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->error->status_code === 400);
        $message = $response->error->message;
        $this->assertContains('SQLSTATE', $message);
        $request = Request::create('/index', 'POST', [
        ]);
        $response = $controller->store($request);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->error->status_code === 400);
        $message = $response->error->message->email[0];
        $this->assertContains('The email field is required.', $message);
    }

    public function testControllerStorePasses()
    {
        $controller = new ZcwiltUserController(new ModelMakerFactory());
        $request = Request::create('/index', 'POST', [
            'email' => 'dirk@holisticdetective.com',
            'name' => 'Dirk Gently',
            'age' => 38
        ]);
        $response = $controller->store($request);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->data->age === 38);
    }
}
