<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Request;
use Tests\DatabaseTestCase;

class PutTests extends DatabaseTestCase
{
    /** @test */
    public function updates_a_current_item()
    {
        $newID = $this->createEntry();
        $response = $this->put("/user/" . $newID, [
            'id' => $newID,
            'email' => 'dirk@holisticdetective.com',
            'name' => 'Dirk Gently',
            'age' => 45
        ]);
        $response->assertStatus(200);
        $response = json_decode($response->getContent());
        $this->assertEquals('1', $response->affected_rows);
    }

    /** @test */
    public function updates_a_nonexistent_item()
    {
        $response = $this->put("/user/1001", [
            'email' => 'dirk@holisticdetective.com',
            'name' => 'Dirk Gently',
            'age' => 45
        ]);
        $response->assertStatus(200);
        $response = json_decode($response->getContent());
        $this->assertEquals('0', $response->affected_rows);
    }

    /** @test */
    public function updates_with_a_existing_email()
    {
        $newID = $this->createEntry();
        $response = $this->put("/user/1", [
            'email' => 'dirk@holisticdetective.com',
            'name' => 'Dirk Gently',
            'age' => 38
        ]);
        $response = json_decode($response->getContent());
        $message = $response->errors->email;
        $this->assertStringContainsString('The email has already been taken.', $message[0]);
    }

    /** @test */
    public function updates_using_a_where_clause()
    {
        $this->createEntry();
        $response = $this->put("/user?where[]=email:eq:dirk@holisticdetective.com", [
            'age' => 45,
        ]);
        $response = json_decode($response->getContent());
        $this->assertEquals('1', $response->affected_rows);
    }

    protected function createEntry()
    {
        $response = $this->post("/user", [
            'email' => 'dirk@holisticdetective.com',
            'name' => 'Dirk Gently',
            'age' => 38,
        ]);
        $newID = json_decode($response->getContent())->id;
        return $newID;
    }
}
