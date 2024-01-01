<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;
use Tests\Fixtures\Models\Dummy;
use Tests\Fixtures\Models\User;

class ScopeTests extends DatabaseTestCase
{
    /** @test */
    public function it_returns_a_scoped_query()
    {
        $user = (new User())->teenager()->count();
        $response = $this->get("/user?scope[]=teenager");
        $response->assertStatus(200);
        $responseResult = count(json_decode($response->getContent(), true)['data']);
        $this->assertEquals($user, $responseResult);
    }

    /** @test */
    public function it_returns_an_error_for_invalid_scope()
    {
        $response = $this->get("/user?scope[]=teenager&scope[]=invalid");
        $response->assertStatus(400);
        $responseResult = json_decode($response->getContent())->errors;
        $this->assertEquals('Can\'t find scope: invalid', $responseResult);
    }
}
