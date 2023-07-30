<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class OrdersStatusTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('orders_status')->truncate();

        Capsule::table('orders_status')->insert(array(
            0 =>
                array(
                    'language_id' => 1,
                    'orders_status_id' => 1,
                    'orders_status_name' => 'Pending',
                    'sort_order' => 0,
                ),
            1 =>
                array(
                    'language_id' => 1,
                    'orders_status_id' => 2,
                    'orders_status_name' => 'Processing',
                    'sort_order' => 10,
                ),
            2 =>
                array(
                    'language_id' => 1,
                    'orders_status_id' => 3,
                    'orders_status_name' => 'Delivered',
                    'sort_order' => 20,
                ),
            3 =>
                array(
                    'language_id' => 1,
                    'orders_status_id' => 4,
                    'orders_status_name' => 'Update',
                    'sort_order' => 30,
                ),
        ));


    }
}
