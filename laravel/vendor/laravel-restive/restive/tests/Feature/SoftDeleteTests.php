<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;
use Tests\Fixtures\Models\Dummy;
use Tests\Fixtures\Models\User;

class SoftDeleteTests extends DatabaseTestCase
{
    /** @test */
    public function gets_items_with_trashed_items()
    {
        $model = new User();
        $testResult = $model->get();
        $this->delete("/user/1");
        $response = $this->get("/user?paginate=no&withTrashed=true");
        $data = json_decode($response->getContent());
        $this->assertEquals(count($testResult->toArray()), count($data));
    }

    /** @test */
    public function gets_only_trashed_items()
    {
        $this->delete("/user/1");
        $response = $this->get("/user?paginate=no&onlyTrashed=true");
        $data = json_decode($response->getContent());
        $this->assertTrue(1 === count($data));
    }

    /** @test */
    public function gets_show_only_trashed_when_not_supported()
    {
        $response = $this->get("/dummy?paginate=no&onlyTrashed=true");
        $response = json_decode($response->getContent());
        $this->assertEquals($response->errors, 'Model does not support soft deletes');
    }

    /** @test */
    public function gets_show_with_trashed_when_not_supported()
    {
        $response = $this->get("/dummy?paginate=no&withTrashed=true");
        $response = json_decode($response->getContent());
        $this->assertTrue($response->errors=== 'Model does not support soft deletes');
    }

    /** @test */
    public function restores_a_single_entity()
    {
        $this->delete("/user/1");
        $response = $this->get("/user/1?withTrashed=true&restore=true");
        $data = json_decode($response->getContent());
        $this->assertEquals(1, $data->id);
        $response = $this->get("/user/1");
        $data = json_decode($response->getContent());
        $this->assertEquals($data->id, 1);
    }

    /** @test */
    public function force_deletes_a_single_item()
    {
        $user = new User();
        $countBefore = $user->all()->count();
        $response = $this->delete("/user/1?force=true");
        $countAfter = $user->withTrashed()->count();
        $response->assertStatus(200);
        $this->assertTrue($countBefore != $countAfter);
    }
}
