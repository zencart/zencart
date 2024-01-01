<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;
use Tests\Fixtures\Models\User;

class GetTests extends DatabaseTestCase
{

    /** @test */
    public function gets_a_single_item()
    {
        $response = $this->get("/user/1");
        $response->assertStatus(200);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->id === 1);
    }


    /** @test */
    public function gets_an_invalid_item()
    {
        $response = $this->get("/user/1001");
        $response->assertStatus(404);
        $content = json_decode($response->getContent());
        $message = $content->error;
        $this->assertTrue($message === 'Item does not exist');
    }

    /** @test */
    public function gets_paginated_items()
    {
        $response = $this->get("/user");
        $response->assertStatus(200);
        $content = json_decode($response->getContent(),  true);
        //dd($content['data']);
        $this->assertEquals(10, count($content['data']));
    }

    /** @test */
    public function gets_items_using_query_parameters()
    {
        $user = new User();
        $modelResult = $user->whereBetween('id', [1,20])->whereBetween('age', [20,40])->count();
        $response = $this->get("/user?whereBetween[]=id:1:20&whereBetween[]=age:20:40");
        $response->assertStatus(200);
        $responseResult = count(json_decode($response->getContent(), true)['data']);
        $this->assertEquals($modelResult, $responseResult);
    }

    /** @test */
    public function it_returns_an_error_for_invalid_query()
    {
        $response = $this->get("/user?whereBetwee[]=id:1:20&whereBetween[]=age:20:40");
        $response->assertStatus(400);
        $responseResult = json_decode($response->getContent())->errors;
        $this->assertEquals('Can\'t find parser class for method: whereBetwee', $responseResult);

        $response = $this->get("/user?where[]=id:1");
        $response->assertStatus(400);
        $responseResult = json_decode($response->getContent())->errors;
        $this->assertEquals('separated parser expects 3 parameters, found 2 parameters', $responseResult);
    }

    /** @test */
    public function it_parses_a_urlencoded_query()
    {
        $response = $this->get("/user?whereBetween%5b%5d=id:1:20");
        $response->assertStatus(200);
    }
}
