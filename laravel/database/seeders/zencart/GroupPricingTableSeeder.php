<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupPricingTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('group_pricing')->truncate();

        DB::table('group_pricing')->insert(array(
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
