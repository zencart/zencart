<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class CounterHistoryTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('counter_history')->truncate();

        Capsule::table('counter_history')->insert(array(
            0 =>
                array(
                    'counter' => 1,
                    'session_counter' => 1,
                    'startdate' => '20230629',
                ),
        ));


    }
}
