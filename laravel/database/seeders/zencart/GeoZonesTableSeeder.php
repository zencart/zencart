<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeoZonesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('geo_zones')->truncate();

        DB::table('geo_zones')->insert(array(
            0 =>
                array(
                    'date_added' => '2023-06-29 12:14:04',
                    'geo_zone_description' => 'Florida local sales tax zone',
                    'geo_zone_id' => 1,
                    'geo_zone_name' => 'Florida',
                    'last_modified' => NULL,
                ),
        ));


    }
}
