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

    /**
     * @test
     */
    public function it_can_get_model()
    {
        $model = Customer::find(1);
        $this->assertEquals('Bill', $model->customers_firstname);
    }
    /**
     * @test
     */
    public function it_cant_get_model()
    {
        $model = Customer::find(99);
        $this->assertNull($model);
    }

    /**
     * @test
     */
    public function it_can_get_addresses()
    {
        $model = Customer::with('addressBooks')->find(1);
        $this->assertEquals('JustaDemo', $model->addressBooks[0]->entry_company);
    }

    /**
     * @test
     */
    public function it_can_get_customerinfo()
    {
        $model = Customer::with('customerInfo')->find(1);
        $this->assertEquals(0, $model->customerInfo->customers_info_number_of_logons);
    }

}
