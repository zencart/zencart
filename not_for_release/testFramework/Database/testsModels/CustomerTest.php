<?php

namespace Tests\Database\testsModels;

use App\Models\Customer;
use Tests\Support\zcDatabaseTestCase;
use Tests\Support\zcFeatureTestCaseStore;

class CustomerTest extends zcDatabaseTestCase
{
    /**
     * @test
     */
    public function it_can_instantiate_model()
    {
        $model = new Customer();
        $this->assertIsObject($model);
        $this->assertInstanceOf(Customer::class, $model);
    }

    public function it_can_get_model()
    {
        $model = Customer::where;
    }

}
