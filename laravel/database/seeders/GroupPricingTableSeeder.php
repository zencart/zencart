<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class GroupPricingTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('group_pricing')->truncate();

        Capsule::table('group_pricing')->insert(array(
            0 =>
                array(
                    'date_added' => '2004-04-29 00:21:04',
                    'group_id' => 1,
                    'group_name' => 'Group 10',
                    'group_percentage' => '10.00',
                    'last_modified' => NULL,
                ),
        ));


    }
}
