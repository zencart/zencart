<?php


namespace Tests\Unit;

use Restive\ParserFactory;
use Tests\Fixtures\Models\User;
use Restive\ApiQueryParser;
use Restive\ComponentFactory;
use Restive\Http\Requests\Request;
use Tests\Fixtures\App\Http\Controllers\Api\UserController;
use Tests\TestCase;

class ApiControllerMethodTests extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }

    /** @test */
    public function gets_a_list_of_all_users()
    {
        $request = Request::create('/users', 'GET');
        $controller = new UserController(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent(), true)['data'];
        $query = User::query()->paginate(10);
        $this->assertEquals(count($response), count($query));
    }


    /** @test */
    public function gets_a_list_of_all_users_with_age_restriction()
    {
        $request = Request::create('/users?whereBetween[]=age:1:30', 'GET');
        $controller = new UserController(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
        $response = $controller->index($request);
        $response = json_decode($response->getContent(), true)['data'];
        $query = User::query()->whereBetween('age', [1,30])->paginate(10);
        $this->assertEquals(count($response),count($query));
    }

    /** @test */
    public function gets_a_single_item()
    {
        $request = Request::create('/users/1', 'GET');
        $controller = new UserController(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
        $response = $controller->show($request, 1);
        $response = json_decode($response->getContent(), true);
        $this->assertEquals(1, $response['id']);
    }

    /** @test */
    public function stores_a_new_user()
    {
        $request = Request::create('/users/1', 'post', ['name' => 'Test User 1', 'email' => 'testemail1@gmail.com', 'age' => 99]);
        $controller = new UserController(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
        $response = $controller->store($request, 1);
        $response = json_decode($response->getContent(), true);
        $this->assertEquals('Test User 1', $response['name']);
    }

    /** @test */
    public function updates_a_user()
    {
        $request = Request::create('/users/1', 'post', ['name' => 'Test User 1', 'email' => 'testemail1@gmail.com', 'age' => 99]);
        $controller = new UserController(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
        $response = $controller->update($request, 1);
        $response = json_decode($response->getContent(), true);
        $this->assertEquals(1, $response['affected_rows']);
    }

    /** @test */
    public function destroys_a_user()
    {
        $request = Request::create('/users/1', 'delete');
        $controller = new UserController(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
        $response = $controller->destroy($request, 1);
        $response = json_decode($response->getContent(), true);
        $this->assertEquals(1, $response[0]['id']);
    }
}