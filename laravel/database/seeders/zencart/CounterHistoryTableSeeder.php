<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CounterHistoryTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('counter_history')->truncate();

        DB::table('counter_history')->insert(array(
            0 =>
                array(
                    'counter' => 1,
                    'session_counter' => 1,
                    'startdate' => '20230629',
                ),
        ));


    }
}
