<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

class PostTests extends DatabaseTestCase
{
    /** @test */
    public function post_user_fails_missing_age_parameter()
    {
        $response = $this->post("/user", [
            'name' => 'Dirk Gently',
            'email' => 'email@test.com',
        ]);
        $response->assertStatus(422);
        $response = json_decode($response->getContent());
        $this->assertEquals('The age field is required.', $response->errors->age[0]);
    }

    /** @test */
    public function post_user_fails_missing_email_parameter()
    {
        $response = $this->post("/user", [
            'name' => 'Dirk Gently',
            'email' => null
        ]);
        $response->assertStatus(422);
        $response = json_decode($response->getContent());
        $this->assertEquals('The age field is required.', $response->errors->age[0]);
    }

    /** @test */
    public function post_user_with_correct_parameters()
    {
        $response = $this->post("/user", [
            'email' => 'dirk@holisticdetective.com',
            'name' => 'Dirk Gently',
            'age' => 38
        ]);
        $response->assertStatus(201);
        $response = json_decode($response->getContent());
        $this->assertTrue($response->age === 38);

    }
}
