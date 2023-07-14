<?php

namespace Seeders\InitialSeeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class CounterTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('counter')->truncate();

        Capsule::table('counter')->insert(array(
            0 =>
                array(
                    'counter' => 1,
                    'startdate' => '20230629',
                ),
        ));


    }
}
