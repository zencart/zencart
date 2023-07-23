<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;
use Tests\Fixtures\Models\User;

class DeleteTests extends DatabaseTestCase
{
    /** @test */
    public function deletes_a_single_resource()
    {
        $response = $this->delete("/user/1");
        $response->assertStatus(200);
        $data = json_decode($response->getContent());
        $this->assertEquals($data[0]->id, 1);
    }


    /** @test */
    public function deletes_a_resource_using_where()
    {
        $user = new User();
        $countBefore = $user->all()->count();
        $response = $this->delete("/user?where[]=id:eq:2");
        $countAfter = $user->all()->count();
        $data = json_decode($response->getContent());
        $this->assertEquals($data[0]->id, 2);
        $this->assertEquals($countAfter, $countBefore-1);
    }

    /** @test */
    public function deletes_multiple_entries()
    {
        $user = new User();
        $countBefore = $user->all()->count();
        $response = $this->delete("/user?whereBetween=age:13:19");
        $countAfter = $user->all()->count();
        $data = json_decode($response->getContent());
        $deletedCount = count($data);
        $trashedCount = $user->onlyTrashed()->count();
        $this->assertEquals($countAfter, $countBefore-$deletedCount);
        $this->assertEquals($deletedCount, $trashedCount);

    }

    /** @test */
    public function deletes_a_nonexistent_resource_using_where()
    {
        $response = $this->delete("/user?where=id:eq:1001");
        $data = json_decode($response->getContent());
        $this->assertTrue(count($data) === 0);
    }

    /** @test */
    public function deletes_a__resource_using_invalid_parser()
    {
        $response = $this->delete("/user?foo=id:eq:1001");
        $response->assertStatus(400);
    }

}
