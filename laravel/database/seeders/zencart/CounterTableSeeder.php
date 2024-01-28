<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CounterTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('counter')->truncate();

        DB::table('counter')->insert(array(
            0 =>
                array(
                    'counter' => 1,
                    'startdate' => '20230629',
                ),
        ));


    }
}
